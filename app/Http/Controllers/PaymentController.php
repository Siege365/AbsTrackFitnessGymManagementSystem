<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\InventorySupply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
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

        return view('PaymentAndBillings.PaymentAndBilling', compact(
            'inventoryItems',
            'transactions',
            'totalRevenueMonth',
            'retailSalesRevenue',
            'dailyIncome',
            'weeklyIncome',
            'from',
            'to',
            'total'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'transaction_type' => 'required',
            'payment_method' => 'required',
            'paid_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'items_data' => 'required'
        ]);

        $items = json_decode($request->items_data, true);

        if ($request->paid_amount < $request->total_amount) {
            return back()->withErrors(['paid_amount' => 'Paid amount must be equal to or greater than total amount.'])->withInput();
        }

        DB::transaction(function () use ($request, $items) {
            $last = Payment::latest()->first();
            $receipt = $last ? str_pad($last->id + 1, 4, '0', STR_PAD_LEFT) : '0001';

            $payment = Payment::create([
                'receipt_number' => $receipt,
                'customer_name' => $request->customer_name,
                'transaction_type' => $request->transaction_type,
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
        });

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
            'transaction_type' => $payment->transaction_type,
            'total_amount' => $payment->total_amount,
            'paid_amount' => $payment->paid_amount,
            'return_amount' => $payment->return_amount,
            'formatted_date' => $payment->created_at->format('M d, Y - h:i A'),
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
        $ids = json_decode($request->ids, true);
        
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
}