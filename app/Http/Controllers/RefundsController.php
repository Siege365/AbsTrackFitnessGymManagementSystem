<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\RefundAuditLog;
use App\Http\Requests\StoreRefundRequest;
use App\Services\RefundService;
use Illuminate\Http\Request;

class RefundsController extends Controller
{
    protected RefundService $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * Display refund history/audit logs
     * Shows all refunds for both payment types
     */
    public function index(Request $request)
    {
        $query = RefundAuditLog::orderBy('created_at', 'desc');

        // Filter by payment type (pos or membership)
        if ($request->has('type') && $request->type) {
            $type = $request->type === 'pos' ? Payment::class : MembershipPayment::class;
            $query->where('refundable_type', $type);
        }

        // Search by receipt number or customer
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'LIKE', "%{$search}%");
            });
        }

        // Filter by refund method
        if ($request->has('method') && $request->method) {
            $query->where('refund_method', $request->method);
        }

        $refunds = $query->paginate(15)->withQueryString();

        // Statistics
        $pendingRefunds = RefundAuditLog::where('status', '!=', 'completed')->count();
        $totalRefundedThisMonth = RefundAuditLog::whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->sum('refund_amount');
        $totalRefunds = RefundAuditLog::where('status', 'completed')->sum('refund_amount');

        return view('PaymentAndBillings.Refunds', compact('refunds', 'pendingRefunds', 'totalRefundedThisMonth', 'totalRefunds'));
    }

    /**
     * Show a single refund's details and history
     */
    public function show($id)
    {
        $refund = RefundAuditLog::findOrFail($id);
        $payment = $refund->refundable;
        $summary = $this->refundService->getRefundSummary($payment);

        return view('PaymentAndBillings.RefundDetails', compact('refund', 'payment', 'summary'));
    }

    /**
     * Search for customers by name or ID
     * AJAX endpoint for autocomplete
     */
    public function searchCustomer(Request $request)
    {
        $search = $request->query('q', '');

        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search POS payments
        $posPayments = Payment::where('customer_name', 'LIKE', "%{$search}%")
            ->orWhere('receipt_number', 'LIKE', "%{$search}%")
            ->select('id', 'receipt_number', 'customer_name', 'total_amount', 'refunded_amount', 'payment_status')
            ->limit(5)
            ->get();

        foreach ($posPayments as $p) {
            $totalAmount = floatval($p->total_amount ?? 0);
            $refundedAmount = floatval($p->refunded_amount ?? 0);
            
            $results[] = [
                'id' => $p->id,
                'type' => 'pos',
                'customer_name' => $p->customer_name,
                'receipt_number' => $p->receipt_number,
                'total_amount' => $totalAmount,
                'refunded_amount' => $refundedAmount,
                'remaining_refundable' => $totalAmount - $refundedAmount,
                'payment_status' => $p->payment_status ?? 'completed',
            ];
        }

        // Search Membership payments
        $membershipPayments = MembershipPayment::where('member_name', 'LIKE', "%{$search}%")
            ->orWhere('receipt_number', 'LIKE', "%{$search}%")
            ->select('id', 'receipt_number', 'member_name', 'amount', 'refunded_amount', 'payment_status')
            ->limit(5)
            ->get();

        foreach ($membershipPayments as $p) {
            $totalAmount = floatval($p->amount ?? 0);
            $refundedAmount = floatval($p->refunded_amount ?? 0);
            
            $results[] = [
                'id' => $p->id,
                'type' => 'membership',
                'customer_name' => $p->member_name,
                'receipt_number' => $p->receipt_number,
                'total_amount' => $totalAmount,
                'refunded_amount' => $refundedAmount,
                'remaining_refundable' => $totalAmount - $refundedAmount,
                'payment_status' => $p->payment_status ?? 'completed',
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Search for payments (both POS and Membership)
     * AJAX endpoint for autocomplete
     */
    public function searchPayments(Request $request)
    {
        $search = $request->query('q', '');

        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search POS payments
        $posPayments = Payment::where('customer_name', 'LIKE', "%{$search}%")
            ->orWhere('receipt_number', 'LIKE', "%{$search}%")
            ->select('id', 'receipt_number', 'customer_name', 'total_amount', 'refunded_amount', 'payment_status', 'created_at')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'type' => 'pos',
                    'text' => "POS: {$p->customer_name} - Rcpt #{$p->receipt_number}",
                    'receipt_number' => $p->receipt_number,
                    'name' => $p->customer_name,
                    'total_amount' => $p->total_amount,
                    'refunded_amount' => $p->refunded_amount,
                    'remaining_refundable' => $p->getRemainingRefundableAmount(),
                    'payment_status' => $p->payment_status,
                ];
            })
            ->toArray();

        // Search Membership payments
        $membershipPayments = MembershipPayment::where('member_name', 'LIKE', "%{$search}%")
            ->orWhere('receipt_number', 'LIKE', "%{$search}%")
            ->select('id', 'receipt_number', 'member_name', 'amount', 'refunded_amount', 'payment_status', 'created_at')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'type' => 'membership',
                    'text' => "Membership: {$p->member_name} - Rcpt #{$p->receipt_number}",
                    'receipt_number' => $p->receipt_number,
                    'name' => $p->member_name,
                    'total_amount' => $p->amount,
                    'refunded_amount' => $p->refunded_amount,
                    'remaining_refundable' => $p->getRemainingRefundableAmount(),
                    'payment_status' => $p->payment_status,
                ];
            })
            ->toArray();

        $results = array_merge($posPayments, $membershipPayments);

        return response()->json(['results' => $results]);
    }

    /**
     * Get payment details including items and refund info
     */
    public function getPaymentDetails(Request $request)
    {
        $type = $request->query('type'); // 'pos' or 'membership'
        $id = $request->query('id');

        try {
            $payment = $type === 'pos' 
                ? Payment::findOrFail($id)
                : MembershipPayment::findOrFail($id);

            $items = [];
            $paymentMethod = 'N/A';

            if ($type === 'pos') {
                $paymentMethod = $payment->payment_method ?? 'Cash';
                
                // Get all items for this payment - use query to ensure fresh data
                $paymentItems = \DB::table('payment_items')
                    ->where('payment_id', $payment->id)
                    ->get();
                
                foreach ($paymentItems as $item) {
                    $items[] = [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => floatval($item->unit_price),
                        'refunded_quantity' => 0,
                        'available_quantity' => $item->quantity,
                    ];
                }
            } elseif ($type === 'membership') {
                $paymentMethod = $payment->payment_method ?? 'N/A';
            }

            return response()->json([
                'success' => true,
                'payment_method' => $paymentMethod,
                'items' => $items,
            ]);

        } catch (\Exception $e) {
            \Log::error('Get payment details error', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load payment details'
            ], 400);
        }
    }

    /**
     * Get refund summary for a payment
     * Returns: total, refunded, remaining, and current status
     */
    public function getSummary(Request $request)
    {
        $type = $request->query('type'); // 'pos' or 'membership'
        $id = $request->query('id');

        $payment = $type === 'pos' 
            ? Payment::findOrFail($id)
            : MembershipPayment::findOrFail($id);

        $summary = $this->refundService->getRefundSummary($payment);
        $refundHistory = $this->refundService->getRefundHistory($payment);

        return response()->json([
            'summary' => $summary,
            'refunds' => $refundHistory->map(function ($log) {
                return [
                    'id' => $log->id,
                    'refund_amount' => $log->refund_amount,
                    'refund_reason' => $log->refund_reason,
                    'refund_method' => $log->refund_method,
                    'refunded_by' => $log->refunded_by,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                ];
            }),
        ]);
    }

    /**
     * Process a refund for either POS or Membership payment
     * Uses polymorphic relationships
     */
    public function store(StoreRefundRequest $request)
    {
        $type = $request->input('type'); // 'pos' or 'membership'
        $paymentId = $request->input('payment_id');

        try {
            // Get the payment based on type
            $payment = $type === 'pos'
                ? Payment::findOrFail($paymentId)
                : MembershipPayment::findOrFail($paymentId);

            // Process the refund using service
            $refund = $this->refundService->processRefund(
                $payment,
                $request->input('refund_amount'),
                $request->input('refund_reason'),
                $request->input('refund_method', 'cash'),
                $request->input('product_name'),
                $request->input('refund_quantity'),
                $request->input('processed_by') ?? (auth()->user()?->name ?? 'System')
            );

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully!',
                'refund_id' => $refund->id
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Refund processing error', [
                'type' => $type,
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel a refund
     */
    public function cancel($refundId)
    {
        try {
            $refund = RefundAuditLog::findOrFail($refundId);

            $this->refundService->cancelRefund(
                $refund,
                'Cancelled by ' . auth()->user()?->name
            );

            return back()->with('success', 'Refund cancelled successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * View audit history for a payment
     * Shows all refund operations for tracking
     */
    public function auditHistory($type, $paymentId)
    {
        $payment = $type === 'pos'
            ? Payment::findOrFail($paymentId)
            : MembershipPayment::findOrFail($paymentId);

        $audits = $payment->refunds()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'payment' => [
                'receipt_number' => $payment->receipt_number ?? 'N/A',
                'name' => $payment->customer_name ?? $payment->member_name ?? 'N/A',
                'type' => $type,
            ],
            'audits' => $audits->map(function ($log) {
                return [
                    'id' => $log->id,
                    'refund_amount' => $log->refund_amount,
                    'refund_reason' => $log->refund_reason,
                    'refund_method' => $log->refund_method,
                    'refunded_by' => $log->refunded_by,
                    'authorized_by' => $log->authorized_by,
                    'status' => $log->status,
                    'notes' => $log->notes,
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                ];
            }),
        ]);
    }
}
