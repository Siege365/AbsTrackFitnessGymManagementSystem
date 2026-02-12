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
        // Build base queries - exclude refunded from main lists
        $productQuery = Payment::with('items')
            ->where('is_refunded', false)
            ->orderBy('created_at', 'desc');
            
        $membershipQuery = MembershipPayment::where('is_refunded', false)
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $productQuery->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });

            $membershipQuery->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('member_name', 'LIKE', "%{$search}%");
            });
        }

        // Type filter (product/membership/all)
        $filterType = $request->get('filter_type', 'all');

        // Paginate lists
        $productPayments = $productQuery->paginate(10, ['*'], 'product_page')->withQueryString();
        $membershipPayments = $membershipQuery->paginate(10, ['*'], 'membership_page')->withQueryString();

        // Build combined refunded list
        $refProd = Payment::whereNotNull('refunded_at')->where('is_refunded', true);
        $refMem = MembershipPayment::whereNotNull('refunded_at')->where('is_refunded', true);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $refProd->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
            $refMem->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('member_name', 'LIKE', "%{$search}%");
            });
        }

        if ($filterType === 'product') {
            $refMem = MembershipPayment::whereRaw('1=0');
        } elseif ($filterType === 'membership') {
            $refProd = Payment::whereRaw('1=0');
        }

        $refProdList = $refProd->get()->map(function($p) {
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
        });

        $refMemList = $refMem->get()->map(function($m) {
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
        });

        $combined = $refProdList->merge($refMemList)->sortByDesc('refunded_at')->values();

        // Simple paginator for combined refunds
        $page = $request->get('refunded_page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $itemsForCurrentPage = $combined->slice($offset, $perPage)->all();

        $combinedRefunds = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $combined->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );

        return view('PaymentAndBillings.PaymentHistory', compact('productPayments', 'membershipPayments', 'combinedRefunds'));
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
            'payment_method' => $payment->payment_method,
            'cashier_name' => $payment->cashier_name,
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
                    'total_price' => $item->total_price,
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