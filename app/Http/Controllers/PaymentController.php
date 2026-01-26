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
    public function index()
{
    $inventoryItems = InventorySupply::all();
    
    $transactions = Payment::with('items')->orderBy('created_at', 'desc')->paginate(10);
    
    $totalRevenueMonth = Payment::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->sum('total_amount');
    
    $retailSalesRevenue = Payment::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->sum('total_amount');
    
    $dailyIncome = Payment::whereDate('created_at', today())->sum('total_amount');
    
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
        'weeklyIncome'
    ));
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required',
            'transaction_type' => 'required',
            'payment_method' => 'required',
            'paid_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'items_data' => 'required'
        ]);
        
        $items = json_decode($request->items_data, true);
        
        if (empty($items)) {
            return back()->with('error', 'No items in cart!');
        }
        
        DB::beginTransaction();
        
        try {
            // Generate receipt number
            $lastPayment = Payment::latest()->first();
            $receiptNumber = $lastPayment ? str_pad($lastPayment->id + 1, 4, '0', STR_PAD_LEFT) : '0001';
            
            // Calculate total quantity
            $totalQty = array_sum(array_column($items, 'qty'));
            
            // Create payment record
            $payment = Payment::create([
                'receipt_number' => $receiptNumber,
                'customer_name' => $request->customer_name,
                'transaction_type' => $request->transaction_type,
                'payment_method' => $request->payment_method,
                'paid_amount' => $request->paid_amount,
                'total_amount' => $request->total_amount,
                'return_amount' => $request->paid_amount - $request->total_amount,
                'total_quantity' => $totalQty,
                'cashier_name' => Auth::user()->name ?? 'Admin User',
            ]);
            
            // Process each item
            foreach ($items as $item) {
                $inventoryItem = InventorySupply::find($item['id']);
                
                if (!$inventoryItem) {
                    throw new \Exception("Item not found: " . $item['name']);
                }
                
                if ($inventoryItem->stock_qty < $item['qty']) {
                    throw new \Exception("Insufficient stock for: " . $item['name']);
                }
                
                // Deduct stock
                $inventoryItem->decrement('stock_qty', $item['qty']);
                
                // Create payment item record
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'inventory_supply_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty']
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('payments.index')
                           ->with('success', 'Payment processed successfully! Receipt #' . $receiptNumber);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}