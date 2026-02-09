<?php

namespace App\Services;

use App\Models\RefundAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RefundService
{
    /**
     * Process a refund for any payment-like model
     * Supports both Payment and MembershipPayment polymorphically
     *
     * @param Model $payment The payment to refund (Payment or MembershipPayment)
     * @param float $refundAmount Amount to refund
     * @param string $refundReason Reason for refund
     * @param string $refundMethod Method of refund (cash, card_reversal, store_credit)
     * @param string|null $productName Name of product being refunded
     * @param int|null $quantity Quantity being refunded
     * @param string|null $refundedBy User who processed (defaults to current user)
     * @return RefundAuditLog The created refund audit log
     * @throws \Exception If refund fails or amount exceeds remaining balance
     */
    public function processRefund(
        Model $payment,
        float $refundAmount,
        string $refundReason,
        string $refundMethod = 'cash',
        ?string $productName = null,
        ?int $quantity = null,
        ?string $refundedBy = null
    ): RefundAuditLog {
        return DB::transaction(function () use ($payment, $refundAmount, $refundReason, $refundMethod, $productName, $quantity, $refundedBy) {
            // Get the model class name
            $paymentClass = get_class($payment);
            
            // Lock payment to prevent concurrent refunds
            $payment = $paymentClass::lockForUpdate()->findOrFail($payment->id);

            // Validate refund is allowed
            $this->validateRefund($payment, $refundAmount, $refundReason, $refundMethod);

            // Get current totals for audit trail
            $previousRefundedAmount = $payment->getTotalRefundedAmount();
            
            // Get customer name based on payment type
            $customerName = $payment->customer_name ?? $payment->member_name ?? 'Unknown';
            
            // If product name not provided, try to get from first item
            if (!$productName && method_exists($payment, 'items') && $payment->items()->exists()) {
                $productName = $payment->items()->first()?->product_name ?? 'Multiple items';
            }
            
            if (!$productName) {
                $productName = 'Service/Membership';
            }

            // Create refund audit log entry
            $refundLog = RefundAuditLog::create([
                'refundable_type' => $paymentClass,
                'refundable_id' => $payment->id,
                'receipt_number' => $payment->receipt_number,
                'customer_name' => $customerName,
                'product_name' => $productName,
                'quantity' => $quantity ?? 1,
                'refund_amount' => $refundAmount,
                'refund_reason' => $refundReason,
                'refund_method' => $refundMethod,
                'refunded_by' => $refundedBy ?? (Auth::user()?->name ?? 'System'),
                'authorized_by' => null,
                'notes' => "Refund processed for " . ($payment->receipt_number ?? $payment->id),
                'status' => 'completed',
                'previous_refunded_amount' => $previousRefundedAmount,
            ]);

            // Update just the refunded_amount, don't touch payment_status
            $newRefundedAmount = $previousRefundedAmount + $refundAmount;
            $payment->refunded_amount = $newRefundedAmount;
            
            // Determine status based on refunded amount
            $totalAmount = (float)$payment->total_amount;
            if ($newRefundedAmount >= $totalAmount) {
                $payment->payment_status = 'refunded';
            } elseif ($newRefundedAmount > 0) {
                $payment->payment_status = 'partially_refunded';
            }
            
            $payment->save();

            return $refundLog;
        }, 3); // 3 attempts for transaction
    }

    /**
     * Validate that a refund can be processed
     *
     * @param Model $payment
     * @param float $refundAmount
     * @param string $refundReason
     * @param string $refundMethod
     * @throws \Exception
     */
    public function validateRefund(
        Model $payment,
        float $refundAmount,
        string $refundReason,
        string $refundMethod
    ): void {
        // Check amount is positive
        if ($refundAmount <= 0) {
            throw new \Exception('Refund amount must be greater than 0.');
        }

        // Check reason is provided
        if (empty(trim($refundReason))) {
            throw new \Exception('Refund reason is required.');
        }

        // Check valid refund method
        $validMethods = ['cash', 'card_reversal', 'store_credit'];
        if (!in_array($refundMethod, $validMethods)) {
            throw new \Exception("Invalid refund method. Allowed: " . implode(', ', $validMethods));
        }

        // Check payment is not already fully refunded
        if ($payment->isFullyRefunded()) {
            throw new \Exception('This payment is already fully refunded.');
        }

        // Check refund amount doesn't exceed remaining refundable balance
        $remainingBalance = $payment->getRemainingRefundableAmount();
        if ($refundAmount > $remainingBalance) {
            throw new \Exception("Refund amount exceeds remaining balance. Maximum refundable: ₱" . number_format($remainingBalance, 2));
        }

        // Prevent double-refund on same day for exact amount
        $existingRefund = $payment->completedRefunds()
            ->where('refund_amount', $refundAmount)
            ->where('refund_reason', $refundReason)
            ->whereDate('created_at', today())
            ->first();

        if ($existingRefund) {
            throw new \Exception('This exact refund was already processed today. Please contact administrator if this is a mistake.');
        }
    }

    /**
     * Cancel a refund (set status to cancelled)
     *
     * @param RefundAuditLog $refund
     * @param string|null $reason
     * @return RefundAuditLog
     * @throws \Exception
     */
    public function cancelRefund(RefundAuditLog $refund, ?string $reason = null): RefundAuditLog
    {
        return DB::transaction(function () use ($refund, $reason) {
            // Can only cancel completed refunds
            if ($refund->status !== 'completed') {
                throw new \Exception('Can only cancel completed refunds.');
            }

            $refund->status = 'cancelled';
            $refund->notes = ($reason ? "Cancelled: {$reason}" : "Cancelled by " . Auth::user()?->name);
            $refund->save();

            // Revert the payment status
            $payment = $refund->refundable;
            $payment->updateRefundStatus();

            return $refund;
        });
    }

    /**
     * Get refund history for a payment
     *
     * @param Model $payment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRefundHistory(Model $payment)
    {
        return $payment->refunds()
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get refund summary for a payment
     *
     * @param Model $payment
     * @return array
     */
    public function getRefundSummary(Model $payment): array
    {
        return [
            'receipt_number' => $payment->receipt_number ?? 'N/A',
            'total_amount' => $payment->total_amount ?? $payment->amount,
            'refunded_amount' => $payment->getTotalRefundedAmount(),
            'remaining_refundable' => $payment->getRemainingRefundableAmount(),
            'payment_status' => $payment->payment_status,
            'is_fully_refunded' => $payment->isFullyRefunded(),
            'is_partially_refunded' => $payment->isPartiallyRefunded(),
            'refund_count' => $payment->completedRefunds()->count(),
        ];
    }
}
