<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\InventorySupply;
use App\Models\MembershipPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\RefundService;
use App\Services\NotificationService;

class PaymentController extends Controller
{
    protected $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    public function index(Request $request)
    {
        $inventoryItems = InventorySupply::all();

        // Start query
        $query = Payment::with('items');

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('cashier_name', 'LIKE', "%{$search}%");
            });
        }

        // Apply payment method filter
        if ($request->has('payment_method') && $request->payment_method != '') {
            $query->where('payment_method', $request->payment_method);
        }

        // Apply sorting filter
        if ($request->has('filter') && $request->filter != '') {
            switch($request->filter) {
                case 'date_newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'date_oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_asc':
                    $query->orderBy('customer_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('customer_name', 'desc');
                    break;
                case 'amount_asc':
                    $query->orderBy('total_amount', 'asc');
                    break;
                case 'amount_desc':
                    $query->orderBy('total_amount', 'desc');
                    break;
                case 'payment_method':
                    $query->orderBy('payment_method', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $transactions = $query->paginate(10)->withQueryString();

        // Calculate pagination info
        $from = $transactions->firstItem() ?? 0;
        $to = $transactions->lastItem() ?? 0;
        $total = $transactions->total() ?? 0;

        // Statistics
        $totalRevenueMonth = Payment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $retailSalesRevenue = $totalRevenueMonth;

        $dailyIncome = Payment::whereDate('created_at', today())
            ->sum('total_amount');

        $weeklyIncome = Payment::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->sum('total_amount');

        // Product payment is now an @include inside MembershipPayment blade
        return redirect()->route('membership.payment.index', ['tab' => 'product']);
    }

    public function productPaymentIndex()
    {
        $inventoryItems = InventorySupply::all();

        return view('PaymentAndBillings.product-payment', compact('inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'payment_method' => 'required',
            'paid_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'items_data' => 'required'
        ]);

        $items = json_decode($request->items_data, true);

        if ($request->paid_amount < $request->total_amount) {
            return back()->withErrors(['paid_amount' => 'Paid amount must be equal to or greater than total amount.'])->withInput();
        }

        $payment = DB::transaction(function () use ($request, $items) {
            $last = Payment::latest()->first();
            $receipt = $last ? str_pad($last->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

            $payment = Payment::create([
                'receipt_number' => $receipt,
                'customer_name' => $request->customer_name,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'total_amount' => $request->total_amount,
                'return_amount' => $request->paid_amount - $request->total_amount,
                'total_quantity' => collect($items)->sum('qty'),
                'cashier_name' => Auth::user()->name ?? 'Admin',
            ]);

            foreach ($items as $item) {
                $inventory = InventorySupply::findOrFail($item['id']);

                if ($inventory->stock_qty < $item['qty']) {
                    abort(400, 'Insufficient stock for ' . $inventory->product_name);
                }

                $inventory->decrement('stock_qty', $item['qty']);

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'inventory_supply_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);
            }

            return $payment;
        });

        // Send payment notification
        NotificationService::paymentReceived($request->customer_name, $request->total_amount, 'product');

        // Check for low stock after payment
        foreach (json_decode($request->items_data, true) as $item) {
            $inventory = InventorySupply::find($item['id']);
            if ($inventory && $inventory->stock_qty <= $inventory->low_stock_threshold) {
                if ($inventory->stock_qty == 0) {
                    NotificationService::outOfStock($inventory->product_name);
                } else {
                    NotificationService::lowStock($inventory->product_name, $inventory->stock_qty, $inventory->low_stock_threshold);
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment completed successfully.', 'payment' => $payment]);
        }

        return redirect()->route('payments.index')->with('success', 'Payment completed successfully.');
    }

    public function receipt($payment)
    {
        $payment = Payment::with('items')->findOrFail($payment);
        return view('PaymentAndBillings.Receipt', compact('payment'));
    }

    public function receiptData($payment)
    {
        $payment = Payment::with('items')->findOrFail($payment);
        
        return response()->json([
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'customer_name' => $payment->customer_name,
            'cashier_name' => $payment->cashier_name,
            'payment_method' => $payment->payment_method,
            'total_amount' => $payment->total_amount,
            'paid_amount' => $payment->paid_amount,
            'return_amount' => $payment->return_amount,
            'formatted_date' => $payment->created_at->format('M d, Y - h:i A'),
            'refunded_at' => $payment->refunded_at ? $payment->refunded_at->format('M d, Y - h:i A') : null,
            'refund_reason' => $payment->refund_reason,
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
     * Show consolidated payment history (product + membership)
     */
    public function history(Request $request)
    {
        // Build base queries
        $productQuery = Payment::with('items')->orderBy('created_at', 'desc');
        $membershipQuery = MembershipPayment::orderBy('created_at', 'desc');

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
        $refProd = Payment::whereNotNull('refunded_at');
        $refMem = MembershipPayment::whereNotNull('refunded_at');

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
     * Process a refund for a product payment
     */
    public function refund(Request $request, $id)
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

    public function destroy($payment)
    {
        try {
            DB::transaction(function () use ($payment) {
                $paymentRecord = Payment::findOrFail($payment);
                $paymentItems = PaymentItem::where('payment_id', $paymentRecord->id)->get();
                
                foreach ($paymentItems as $item) {
                    $inventory = InventorySupply::find($item->inventory_supply_id);
                    if ($inventory) {
                        $inventory->increment('stock_qty', $item->quantity);
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

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (!is_array($ids) || empty($ids)) {
            return back()->withErrors(['error' => 'No transactions selected.']);
        }

        try {
            DB::transaction(function () use ($ids) {
                $paymentItems = PaymentItem::whereIn('payment_id', $ids)->get();
                
                foreach ($paymentItems as $item) {
                    $inventory = InventorySupply::find($item->inventory_supply_id);
                    if ($inventory) {
                        $inventory->increment('stock_qty', $item->quantity);
                    }
                }
                
                PaymentItem::whereIn('payment_id', $ids)->delete();
                Payment::whereIn('id', $ids)->delete();
            });

            return back()->with('success', count($ids) . ' transaction(s) deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete transactions: ' . $e->getMessage()]);
        }
    }
    /**
     * Display unified Payment System page (SPA-style with all 3 payment forms)
     * Accepts paymentType via route defaults: 'membership', 'pt', or 'product'
     */
    public function membership(Request $request)
    {
        // Get payment type from route default (set in route definition)
        $paymentType = $request->route()->parameter('paymentType') ?? 'membership';

        // Fetch data for Membership Payment form
        $monthlyRevenue = \App\Models\MembershipPayment::whereNull('refunded_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $todayRevenue = \App\Models\MembershipPayment::whereNull('refunded_at')
            ->whereDate('created_at', today())
            ->sum('amount');

        $transactionCount = \App\Models\MembershipPayment::whereDate('created_at', today())->count();

        $membershipPlans = \App\Models\GymPlan::active()->membership()->ordered()->get();

        // Fetch data for PT Payment form  
        $ptPlans = \App\Models\GymPlan::active()->personalTraining()->ordered()->get();
        
        $trainers = [
            'David Laid',
            'Eulo Icon Sexcion',
            'Justin Troy Rosalada',
            'Nicolas Deloso Torre III',
            'Ronnie Coleman',
        ];

        // Fetch data for Product Payment form
        $inventoryItems = InventorySupply::all();

        return view('PaymentAndBillings.payment-system', compact(
            'monthlyRevenue',
            'todayRevenue',
            'transactionCount',
            'membershipPlans',
            'ptPlans',
            'trainers',
            'inventoryItems',
            'paymentType'
        ));
    }

}