<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\RefundLog;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class RefundController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
        
        // Admin-only middleware (uncomment when ready)
        // $this->middleware(['auth', 'admin']);
    }

    /**
     * Display refund management dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        // Build query
        $query = RefundLog::with('refundable')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($type !== 'all') {
            $query->where('transaction_type', $type);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
        }

        $refunds = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = $this->refundService->getRefundStatistics([
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        return view('refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Show refund processing form for product payment
     */
    public function showProductRefundForm($paymentId)
    {
        $payment = Payment::with('items')->findOrFail($paymentId);

        if ($payment->is_refunded && $payment->refund_status === 'full') {
            return back()->withErrors(['error' => 'This payment has already been fully refunded']);
        }

        return view('refunds.product-form', compact('payment'));
    }

    /**
     * Show refund processing form for membership payment
     */
    public function showMembershipRefundForm($paymentId)
    {
        $payment = MembershipPayment::findOrFail($paymentId);

        if ($payment->is_refunded && $payment->refund_status === 'full') {
            return back()->withErrors(['error' => 'This payment has already been fully refunded']);
        }

        return view('refunds.membership-form', compact('payment'));
    }

    /**
     * Process product payment refund
     */
    public function processProductRefund(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->refundService->refundProductPayment($paymentId, [
                'refund_amount' => $validated['refund_amount'],
                'reason' => $validated['reason'] ?? null,
                'items' => $validated['items'] ?? [],
            ]);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return redirect()
                ->route('refunds.index')
                ->with('success', $result['message']);

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Process membership payment refund
     */
    public function processMembershipRefund(Request $request, $paymentId)
    {
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->refundService->refundMembershipPayment($paymentId, [
                'refund_amount' => $validated['refund_amount'],
                'reason' => $validated['reason'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return redirect()
                ->route('refunds.index')
                ->with('success', $result['message']);

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Get refund details (for AJAX)
     */
    public function getRefundDetails($refundId)
    {
        $refund = RefundLog::with('refundable', 'inventoryAdjustments')
            ->findOrFail($refundId);

        return response()->json([
            'success' => true,
            'refund' => $refund,
        ]);
    }

    /**
     * Export refund logs
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = RefundLog::query();

        if ($type !== 'all') {
            $query->where('transaction_type', $type);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $refunds = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'refund_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($refunds) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'ID',
                'Receipt Number',
                'Transaction Type',
                'Customer/Member',
                'Original Amount',
                'Refund Amount',
                'Refund Type',
                'Reason',
                'Processed By',
                'Status',
                'Date',
            ]);

            // CSV Data
            foreach ($refunds as $refund) {
                fputcsv($file, [
                    $refund->id,
                    $refund->receipt_number,
                    ucfirst($refund->transaction_type),
                    $refund->customer_name,
                    number_format($refund->original_amount, 2),
                    number_format($refund->refund_amount, 2),
                    ucfirst($refund->refund_type),
                    $refund->refund_reason ?? 'N/A',
                    $refund->processed_by,
                    ucfirst($refund->status),
                    $refund->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get refund statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $period = $request->get('period', 'month');

        $filters = match($period) {
            'today' => [
                'start_date' => now()->startOfDay(),
                'end_date' => now()->endOfDay(),
            ],
            'week' => [
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
            ],
            'year' => [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ],
            default => [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
            ],
        };

        $stats = $this->refundService->getRefundStatistics($filters);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'period' => $period,
        ]);
    }

    /**
     * Cancel/void a refund (exceptional cases only)
     */
    public function cancelRefund(Request $request, $refundId)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $result = $this->refundService->cancelRefund($refundId, $validated['reason']);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return back()->with('success', $result['message']);

        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}