<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\PaymentItem;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PaymentHistoryController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    public function index(Request $request)
    {
        // Sort direction per table (default: newest)
        $productSort = $request->get('product_sort', 'newest');
        $membershipSort = $request->get('membership_sort', 'newest');

        // Build base queries — exclude refunded (they appear in the refunded table)
        $productQuery = Payment::with('items')
            ->where('is_refunded', false)
            ->orderBy('created_at', $productSort === 'oldest' ? 'asc' : 'desc');

        $membershipQuery = MembershipPayment::where('is_refunded', false)
            ->orderBy('created_at', $membershipSort === 'oldest' ? 'asc' : 'desc');

        // Per-table search inputs
        $productSearch = $request->get('product_search', null);
        $membershipSearch = $request->get('membership_search', null);
        $refundSearch = $request->get('refund_search', null);

        // Refund table type filter
        $refundFilter = $request->get('refund_filter', 'all');

        // Apply product search
        if (!empty($productSearch)) {
            $productQuery->where(function($q) use ($productSearch) {
                $q->where('receipt_number', 'LIKE', "%{$productSearch}%")
                  ->orWhere('customer_name', 'LIKE', "%{$productSearch}%");
            });
        }

        // Apply membership search
        if (!empty($membershipSearch)) {
            $membershipQuery->where(function($q) use ($membershipSearch) {
                $q->where('receipt_number', 'LIKE', "%{$membershipSearch}%")
                  ->orWhere('member_name', 'LIKE', "%{$membershipSearch}%");
            });
        }

        // Apply membership plan type filter
        $membershipPlanFilter = $request->get('membership_plan_filter', null);
        if (!empty($membershipPlanFilter)) {
            $membershipQuery->where('plan_type', $membershipPlanFilter);
        }

        // Apply membership payment type filter
        $membershipTypeFilter = $request->get('membership_type_filter', null);
        if (!empty($membershipTypeFilter)) {
            $membershipQuery->where('payment_type', $membershipTypeFilter);
        }

        // Paginate lists — 6 rows per page
        $productPayments = $productQuery->paginate(6, ['*'], 'product_page')
            ->appends($request->except('product_page'));
        $membershipPayments = $membershipQuery->paginate(6, ['*'], 'membership_page')
            ->appends($request->except('membership_page'));

        // Build combined refunded list
        $refProd = Payment::whereNotNull('refunded_at')->where('is_refunded', true);
        $refMem = MembershipPayment::whereNotNull('refunded_at')->where('is_refunded', true);

        if (!empty($refundSearch)) {
            $refProd->where(function($q) use ($refundSearch) {
                $q->where('receipt_number', 'LIKE', "%{$refundSearch}%")
                  ->orWhere('customer_name', 'LIKE', "%{$refundSearch}%");
            });
            $refMem->where(function($q) use ($refundSearch) {
                $q->where('receipt_number', 'LIKE', "%{$refundSearch}%")
                  ->orWhere('member_name', 'LIKE', "%{$refundSearch}%");
            });
        }

        if ($refundFilter === 'product') {
            $refMem = MembershipPayment::whereRaw('1=0');
        } elseif ($refundFilter === 'membership') {
            $refProd = Payment::whereRaw('1=0');
        }

        $refProdList = collect($refProd->get()->map(function($p) {
            return (object)[
                'id' => $p->id,
                'receipt_number' => $p->receipt_number,
                'name' => $p->customer_name,
                'refunded_at' => $p->refunded_at,
                'amount' => $p->total_amount,
                'refunded_amount' => $p->refunded_amount,
                'refund_status' => $p->refund_status,
                'refund_reason' => $p->refund_reason,
                'refunded_by' => $p->refunded_by,
                'type' => 'Product'
            ];
        })->all());

        $refMemList = collect($refMem->get()->map(function($m) {
            return (object)[
                'id' => $m->id,
                'receipt_number' => $m->receipt_number,
                'name' => $m->member_name,
                'refunded_at' => $m->refunded_at,
                'amount' => $m->amount,
                'refunded_amount' => $m->refunded_amount,
                'refund_status' => $m->refund_status,
                'refund_reason' => $m->refund_reason,
                'refunded_by' => $m->refunded_by,
                'type' => 'Membership'
            ];
        })->all());

        $combined = $refProdList->merge($refMemList)->sortByDesc('refunded_at')->values();

        // Paginate combined refunds — 6 per page
        $page = $request->get('refunded_page', 1);
        $perPage = 6;
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = $combined->slice($offset, $perPage)->all();

        $combinedRefunds = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $combined->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'pageName' => 'refunded_page']
        );
        $combinedRefunds->appends($request->except('refunded_page'));

        // Stats: income totals (non-refunded payments only)
        $membershipIncome = MembershipPayment::where('is_refunded', false)->sum('amount');
        $productIncome    = Payment::where('is_refunded', false)->sum('total_amount');
        $refundedTotal    = Payment::where('is_refunded', true)->sum('refunded_amount')
                          + MembershipPayment::where('is_refunded', true)->sum('refunded_amount');
        $ptIncome         = 0; // PT payments not yet implemented

        return view('PaymentAndBillings.PaymentHistory', compact(
            'productPayments',
            'membershipPayments',
            'combinedRefunds',
            'membershipIncome',
            'productIncome',
            'refundedTotal',
            'ptIncome'
        ));
    }

    /**
     * Get receipt data for product payment (AJAX)
     */
    public function getReceiptData($id)
    {
        $payment = Payment::with('items')->findOrFail($id);
        
        return response()->json([
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'customer_name' => $payment->customer_name,
            'total_amount' => $payment->total_amount,
            'paid_amount' => $payment->paid_amount,
            'return_amount' => $payment->return_amount,
            'payment_method' => $payment->payment_method,
            'cashier_name' => $payment->cashier_name,
            'formatted_date' => \Carbon\Carbon::parse($payment->created_at)->setTimezone('Asia/Manila')->format('F d, Y - h:i A'),
            'created_at' => $payment->created_at,
            'is_refunded' => $payment->is_refunded,
            'refund_status' => $payment->refund_status,
            'refunded_amount' => $payment->refunded_amount,
            'refunded_at' => $payment->refunded_at,
            'refund_reason' => $payment->refund_reason,
            'refunded_by' => $payment->refunded_by,
            'items' => $payment->items->map(function($item) {
                return [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ];
            })
        ]);
    }

    /**
     * Get receipt data for membership payment (AJAX)
     */
    public function getMembershipReceipt($id)
    {
        $payment = MembershipPayment::findOrFail($id);
        
        // Ensure membership relation is loaded to access contact
        $payment->load('membership');

        return response()->json([
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'member_name' => $payment->member_name,
            'member_contact' => $payment->membership->contact ?? 'N/A',
            'amount' => $payment->amount,
            'plan_type' => $payment->plan_type,
            // JS expects `duration` key — use `duration_days` from model
            'duration' => $payment->duration_days,
            'payment_type' => $payment->payment_type,
            'payment_method' => $payment->payment_method,
            'processed_by' => $payment->processed_by,
            // Provide formatted date for display
            'formatted_date' => \Carbon\Carbon::parse($payment->created_at)->setTimezone('Asia/Manila')->format('F d, Y - h:i A'),
            'is_refunded' => $payment->is_refunded,
            'refund_status' => $payment->refund_status,
            'refunded_amount' => $payment->refunded_amount,
            'refunded_at' => $payment->refunded_at,
            'refund_reason' => $payment->refund_reason,
            'refunded_by' => $payment->refunded_by,
            'previous_due_date' => $payment->previous_due_date ? \Carbon\Carbon::parse($payment->previous_due_date)->format('F d, Y') : null,
            'new_due_date' => $payment->new_due_date ? \Carbon\Carbon::parse($payment->new_due_date)->format('F d, Y') : null,
            'buddy_member_id' => $payment->buddy_member_id,
            'buddy_name' => $payment->buddy_name,
            'buddy_contact' => $payment->buddy_contact,
        ]);
    }

    /**
     * Process product refund using RefundService
     */
    public function refundProduct(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->refundService->refundProductPayment($id, [
                'reason' => $validated['reason'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Process membership refund using RefundService
     */
    public function refundMembership(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->refundService->refundMembershipPayment($id, [
                'reason' => $validated['reason'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete product payment (with inventory restoration)
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $paymentRecord = Payment::findOrFail($id);
                $paymentItems = PaymentItem::where('payment_id', $paymentRecord->id)->get();

                // Restore inventory if not already refunded
                if (!$paymentRecord->is_refunded) {
                    foreach ($paymentItems as $item) {
                        $inventory = \App\Models\InventorySupply::find($item->inventory_supply_id);
                        if ($inventory) {
                            $inventory->increment('stock_qty', $item->quantity);
                        }
                    }
                }

                PaymentItem::where('payment_id', $paymentRecord->id)->delete();
                $paymentRecord->delete();
            });

            return back()->with('success', 'Transaction deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete transaction: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete membership payments
     */
    public function bulkDeleteMembership(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return back()->withErrors(['error' => 'No payments selected.']);
        }

        try {
            DB::transaction(function () use ($ids) {
                foreach ($ids as $id) {
                    $payment = MembershipPayment::find($id);
                    if (!$payment) continue;

                    // If refunded, restore the member's previous state
                    if ($payment->is_refunded && $payment->previous_due_date) {
                        $member = \App\Models\Membership::find($payment->membership_id);
                        if ($member) {
                            $member->due_date = $payment->previous_due_date;
                            $member->status = $payment->previous_status ?? 'Active';
                            $member->save();
                        }
                    }

                    $payment->delete();
                }
            });

            return back()->with('success', count($ids) . ' membership payment(s) deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete payments: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete membership payment
     */
    public function destroyMembership($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $payment = MembershipPayment::findOrFail($id);
                
                // If refunded, we should restore the member's previous state
                if ($payment->is_refunded && $payment->previous_due_date) {
                    // Use Membership model (not Member) — Membership stores member records
                    $member = \App\Models\Membership::find($payment->membership_id ?? $payment->membership_id);
                    if ($member) {
                        $member->due_date = $payment->previous_due_date;
                        // previous_status may not exist; default to 'Active'
                        $member->status = $payment->previous_status ?? 'Active';
                        $member->save();
                    }
                }
                
                $payment->delete();
            });

            return back()->with('success', 'Membership payment deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete payment: ' . $e->getMessage()]);
        }
    }
}