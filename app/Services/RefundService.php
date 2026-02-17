<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\MembershipPayment;
use App\Models\Membership;
use App\Models\InventorySupply;
use App\Models\RefundLog;
use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use Carbon\Carbon;

class RefundService
{
    /**
     * Process a product payment refund
     *
     * @param int $paymentId
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function refundProductPayment($paymentId, array $options = [])
    {
        return DB::transaction(function () use ($paymentId, $options) {
            // Fetch payment with items
            $payment = Payment::with('items')->findOrFail($paymentId);

            // Validate refund eligibility
            $this->validateProductRefund($payment, $options);

            // Calculate refund details
            $refundAmount = $options['refund_amount'] ?? $payment->total_amount;
            $isFullRefund = $refundAmount >= $payment->total_amount;
            $refundType = $isFullRefund ? 'full' : 'partial';

            // Process inventory restoration
            $inventoryValueRestored = 0;
            $itemsCount = 0;

            foreach ($payment->items as $item) {
                $quantityToRefund = $options['items'][$item->id]['quantity'] ?? $item->quantity;
                
                // Validate quantity
                if ($quantityToRefund > ($item->quantity - $item->refunded_quantity)) {
                    throw new Exception("Cannot refund more than purchased quantity for {$item->product_name}");
                }

                // Restore inventory
                $inventory = InventorySupply::find($item->inventory_supply_id);
                if ($inventory) {
                    $quantityBefore = $inventory->stock_qty;
                    $inventory->increment('stock_qty', $quantityToRefund);
                    $inventory->refresh();

                    // Log inventory adjustment
                    InventoryAdjustment::create([
                        'inventory_supply_id' => $inventory->id,
                        'product_name' => $item->product_name,
                        'product_number' => $inventory->product_number,
                        'adjustment_type' => 'refund',
                        'quantity_before' => $quantityBefore,
                        'quantity_adjusted' => $quantityToRefund,
                        'quantity_after' => $inventory->stock_qty,
                        'unit_cost' => $item->unit_price,
                        'total_value' => $item->unit_price * $quantityToRefund,
                        'adjustable_type' => Payment::class,
                        'adjustable_id' => $payment->id,
                        'reference_number' => $payment->receipt_number,
                        'reason' => $options['reason'] ?? 'Product refund',
                        'adjusted_by' => Auth::user()->name ?? 'System',
                        'approved_by' => Auth::user()->name ?? 'System',
                    ]);

                    $inventoryValueRestored += ($item->unit_price * $quantityToRefund);
                }

                // Update payment item
                $item->increment('refunded_quantity', $quantityToRefund);
                $item->increment('refunded_amount', $item->unit_price * $quantityToRefund);
                $item->update(['is_refunded' => $item->refunded_quantity >= $item->quantity]);
                
                $itemsCount++;
            }

            // Update payment record
            $payment->update([
                'is_refunded' => $isFullRefund,
                'refund_status' => $refundType,
                'refunded_amount' => $payment->refunded_amount + $refundAmount,
                'refunded_at' => now(),
                'refund_reason' => $options['reason'] ?? null,
                'refunded_by' => Auth::user()->name ?? 'Admin',
            ]);

            // Create refund log
            $refundLog = RefundLog::create([
                'refundable_type' => Payment::class,
                'refundable_id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'transaction_type' => 'product',
                'original_amount' => $payment->total_amount,
                'refund_amount' => $refundAmount,
                'refund_type' => $refundType,
                'customer_name' => $payment->customer_name,
                'refund_reason' => $options['reason'] ?? null,
                'processed_by' => Auth::user()->name ?? 'Admin',
                'status' => 'completed',
                'inventory_value_restored' => $inventoryValueRestored,
                'items_count' => $itemsCount,
                'metadata' => [
                    'cashier' => $payment->cashier_name,
                    'payment_method' => $payment->payment_method,
                    'original_date' => $payment->created_at->toDateTimeString(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return [
                'success' => true,
                'message' => 'Product payment refunded successfully',
                'refund_log' => $refundLog,
                'payment' => $payment->fresh(),
                'inventory_restored' => $inventoryValueRestored,
            ];
        });
    }

    /**
     * Process a membership payment refund
     *
     * @param int $paymentId
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function refundMembershipPayment($paymentId, array $options = [])
    {
        return DB::transaction(function () use ($paymentId, $options) {
            // Fetch membership payment
            $payment = MembershipPayment::findOrFail($paymentId);

            // Validate refund eligibility
            $this->validateMembershipRefund($payment, $options);

            // Get membership record
            $member = Membership::find($payment->membership_id);
            if (!$member) {
                throw new Exception('Membership record not found');
            }

            // Store previous state
            $previousDueDate = $member->due_date;
            $previousStatus = $member->status;

            // Calculate refund details
            $refundAmount = $options['refund_amount'] ?? $payment->amount;
            $isFullRefund = $refundAmount >= $payment->amount;
            $refundType = $isFullRefund ? 'full' : 'partial';

            // Reverse membership changes
            if ($isFullRefund) {
                // Full refund - reverse the entire membership extension
                $this->reverseMembershipExtension($member, $payment);
            } else {
                // Partial refund - proportional adjustment
                $this->adjustMembershipPartial($member, $payment, $refundAmount);
            }

            // Update payment record
            $payment->update([
                'is_refunded' => $isFullRefund,
                'refund_status' => $refundType,
                'refunded_amount' => $payment->refunded_amount + $refundAmount,
                'refunded_at' => now(),
                'refund_reason' => $options['reason'] ?? null,
                'refunded_by' => Auth::user()->name ?? 'Admin',
                'previous_due_date' => $previousDueDate,
                'previous_status' => $previousStatus,
            ]);

            // Create refund log
            $refundLog = RefundLog::create([
                'refundable_type' => MembershipPayment::class,
                'refundable_id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'transaction_type' => 'membership',
                'original_amount' => $payment->amount,
                'refund_amount' => $refundAmount,
                'refund_type' => $refundType,
                'customer_name' => $payment->member_name,
                'member_id' => $payment->membership_id,
                'refund_reason' => $options['reason'] ?? null,
                'processed_by' => Auth::user()->name ?? 'Admin',
                'status' => 'completed',
                'inventory_value_restored' => 0,
                'items_count' => 0,
                'metadata' => [
                    'plan_type' => $payment->plan_type,
                    'duration' => $payment->duration_days ?? $payment->duration,
                    'payment_type' => $payment->payment_type,
                    'previous_due_date' => $previousDueDate?->toDateString(),
                    'new_due_date' => $member->fresh()->due_date?->toDateString(),
                    'previous_status' => $previousStatus,
                    'new_status' => $member->fresh()->status,
                ],
                'ip_address' => request()->ip(),
            ]);

            return [
                'success' => true,
                'message' => 'Membership payment refunded successfully',
                'refund_log' => $refundLog,
                'payment' => $payment->fresh(),
                'member' => $member->fresh(),
            ];
        });
    }

    /**
     * Validate product refund eligibility
     *
     * @param Payment $payment
     * @param array $options
     * @throws Exception
     */
    protected function validateProductRefund(Payment $payment, array $options)
    {
        // Check if already fully refunded
        if ($payment->is_refunded && $payment->refund_status === 'full') {
            throw new Exception('This payment has already been fully refunded');
        }

        // Check refund amount
        $refundAmount = $options['refund_amount'] ?? $payment->total_amount;
        $maxRefundable = $payment->total_amount - $payment->refunded_amount;

        if ($refundAmount > $maxRefundable) {
            throw new Exception("Refund amount (₱{$refundAmount}) exceeds maximum refundable amount (₱{$maxRefundable})");
        }

        if ($refundAmount <= 0) {
            throw new Exception('Refund amount must be greater than zero');
        }

        // Validate items if partial refund
        if (isset($options['items'])) {
            foreach ($payment->items as $item) {
                if (isset($options['items'][$item->id])) {
                    $requestedQty = $options['items'][$item->id]['quantity'];
                    $maxRefundableQty = $item->quantity - $item->refunded_quantity;
                    
                    if ($requestedQty > $maxRefundableQty) {
                        throw new Exception("Cannot refund {$requestedQty} of {$item->product_name}. Maximum: {$maxRefundableQty}");
                    }
                }
            }
        }
    }

    /**
     * Validate membership refund eligibility
     *
     * @param MembershipPayment $payment
     * @param array $options
     * @throws Exception
     */
    protected function validateMembershipRefund(MembershipPayment $payment, array $options)
    {
        // Check if already fully refunded
        if ($payment->is_refunded && $payment->refund_status === 'full') {
            throw new Exception('This payment has already been fully refunded');
        }

        // Check refund amount
        $refundAmount = $options['refund_amount'] ?? $payment->amount;
        $maxRefundable = $payment->amount - $payment->refunded_amount;

        if ($refundAmount > $maxRefundable) {
            throw new Exception("Refund amount (₱{$refundAmount}) exceeds maximum refundable amount (₱{$maxRefundable})");
        }

        if ($refundAmount <= 0) {
            throw new Exception('Refund amount must be greater than zero');
        }

        // Business rule: Can't refund if membership was used extensively (optional)
        // You can add additional validation here based on your business rules
    }

    /**
     * Reverse membership extension from a payment
     * FIXED: Now properly recalculates status after date adjustment
     *
     * @param Membership $member
     * @param MembershipPayment $payment
     */
    protected function reverseMembershipExtension(Membership $member, MembershipPayment $payment)
    {
        // Get the duration that was added
        $duration = $payment->duration_days ?? $payment->duration;

        if ($member->due_date) {
            // Subtract the duration
            $newDueDate = $member->due_date->copy()->subDays($duration);
            
            // Update the due date
            $member->due_date = $newDueDate;
            
            // FIXED: Recalculate status based on the new due date
            $member->status = $this->calculateMembershipStatus($newDueDate);
            
            $member->save();
        } else {
            // If no due date, set to expired
            $member->status = 'Expired';
            $member->save();
        }
    }

    /**
     * Adjust membership proportionally for partial refund
     * FIXED: Now properly recalculates status after date adjustment
     *
     * @param Membership $member
     * @param MembershipPayment $payment
     * @param float $refundAmount
     */
    protected function adjustMembershipPartial(Membership $member, MembershipPayment $payment, $refundAmount)
    {
        // Calculate proportional days to remove
        $refundPercentage = $refundAmount / $payment->amount;
        $duration = $payment->duration_days ?? $payment->duration;
        $daysToRemove = (int) round($duration * $refundPercentage);

        if ($member->due_date && $daysToRemove > 0) {
            $newDueDate = $member->due_date->copy()->subDays($daysToRemove);
            
            // Update the due date
            $member->due_date = $newDueDate;
            
            // FIXED: Recalculate status based on the new due date
            $member->status = $this->calculateMembershipStatus($newDueDate);
            
            $member->save();
        }
    }

    /**
     * Calculate membership status based on due date
     * This matches the logic in MembershipController::calculateStatus()
     *
     * @param Carbon $dueDate
     * @return string
     */
    protected function calculateMembershipStatus($dueDate)
    {
        try {
            $today = Carbon::now()->startOfDay();
            $dueDate = Carbon::parse($dueDate)->startOfDay();
            $daysUntilDue = $today->diffInDays($dueDate, false);

            // If due date has passed (negative days), status is Expired
            if ($daysUntilDue < 0) {
                return 'Expired';
            }
            
            // If due date is within 7 days, status is Due soon
            if ($daysUntilDue <= 7) {
                return 'Due soon';
            }
            
            // Otherwise, status is Active
            return 'Active';
        } catch (\Exception $e) {
            // Default to Active if there's any error in calculation
            \Log::warning("Error calculating status in RefundService: " . $e->getMessage());
            return 'Active';
        }
    }

    /**
     * Get refund statistics
     *
     * @param array $filters
     * @return array
     */
    public function getRefundStatistics(array $filters = [])
    {
        $query = RefundLog::query();

        // Apply filters
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        $stats = [
            'total_refunds' => $query->count(),
            'total_amount_refunded' => $query->sum('refund_amount'),
            'product_refunds' => $query->clone()->where('transaction_type', 'product')->count(),
            'membership_refunds' => $query->clone()->where('transaction_type', 'membership')->count(),
            'full_refunds' => $query->clone()->where('refund_type', 'full')->count(),
            'partial_refunds' => $query->clone()->where('refund_type', 'partial')->count(),
            'inventory_value_restored' => $query->sum('inventory_value_restored'),
        ];

        return $stats;
    }

    /**
     * Cancel/void a refund (admin only, exceptional cases)
     *
     * @param int $refundLogId
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function cancelRefund($refundLogId, $reason)
    {
        return DB::transaction(function () use ($refundLogId, $reason) {
            $refundLog = RefundLog::findOrFail($refundLogId);

            if ($refundLog->status !== 'completed') {
                throw new Exception('Only completed refunds can be cancelled');
            }

            // This is an exceptional case and should be logged
            $refundLog->update([
                'status' => 'rejected',
                'metadata' => array_merge($refundLog->metadata ?? [], [
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => Auth::user()->name ?? 'Admin',
                    'cancellation_reason' => $reason,
                ]),
            ]);

            return [
                'success' => true,
                'message' => 'Refund cancelled',
                'refund_log' => $refundLog->fresh(),
            ];
        });
    }
}