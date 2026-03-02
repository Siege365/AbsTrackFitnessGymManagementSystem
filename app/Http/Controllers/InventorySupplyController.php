<?php

namespace App\Http\Controllers;

use App\Models\InventorySupply;
use App\Models\InventoryTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventorySupplyController extends Controller
{
    /**
     * Generate the next product number in format PRD-0001
     */
    public function getNextProductNumber()
    {
        $lastProduct = InventorySupply::orderBy('id', 'desc')->first();
        
        if ($lastProduct && preg_match('/PRD-(\d+)/', $lastProduct->product_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            // Check for any existing products with PRD- prefix
            $maxNumber = InventorySupply::where('product_number', 'LIKE', 'PRD-%')
                ->get()
                ->map(function($item) {
                    if (preg_match('/PRD-(\d+)/', $item->product_number, $matches)) {
                        return intval($matches[1]);
                    }
                    return 0;
                })
                ->max();
            
            $nextNumber = $maxNumber ? $maxNumber + 1 : 1;
        }
        
        return response()->json([
            'product_number' => 'PRD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT)
        ]);
    }

    public function index(Request $request)
    {
        // Start query
        $query = InventorySupply::query();
        
        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_number', 'LIKE', "%{$search}%")
                ->orWhere('product_name', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply sorting filter
        if ($request->has('filter') && $request->filter != '') {
            switch($request->filter) {
                case 'name_asc':
                    $query->orderBy('product_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('product_name', 'desc');
                    break;
                case 'date_newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'date_oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'stock_asc':
                    $query->orderBy('stock_qty', 'asc');
                    break;
                case 'stock_desc':
                    $query->orderBy('stock_qty', 'desc');
                    break;
                case 'in_stock':
                    $query->whereColumn('stock_qty', '>=', 'low_stock_threshold')
                          ->where('stock_qty', '>', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_qty', '<', 'low_stock_threshold')
                          ->where('stock_qty', '>', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                case 'out_of_stock':
                    $query->where('stock_qty', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                case 'Supplement':
                case 'Equipment':
                case 'Apparel':
                case 'Beverages':
                case 'Snacks':
                case 'Accessories':
                case 'Food':
                case 'Drink':
                    $query->where('category', $request->filter)
                          ->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Get paginated results
        $inventoryItems = $query->paginate(10)->withQueryString();
        
        // Calculate statistics (based on all items)
        $totalProducts = InventorySupply::count();
        $lowStockItems = InventorySupply::whereColumn('stock_qty', '<', 'low_stock_threshold')
                                    ->where('stock_qty', '>', 0)
                                    ->count();
        $outOfStockItems = InventorySupply::where('stock_qty', 0)->count();
        $stockValue = InventorySupply::sum(DB::raw('unit_price * stock_qty'));
        
        return view('inventorySupplies.inventory', compact(
            'inventoryItems',
            'totalProducts',
            'lowStockItems',
            'outOfStockItems',
            'stockValue'
        ));
    }

    /**
     * Display the inventory logs (stock transactions) page.
     */
    public function logsIndex(Request $request)
    {
        // Build activity query
        $activityQuery = InventoryTransaction::with('inventorySupply')
            ->whereHas('inventorySupply');

        // Apply search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $activityQuery->whereHas('inventorySupply', function($q) use ($search) {
                $q->where('product_number', 'LIKE', "%{$search}%")
                  ->orWhere('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply activity filter
        $activityFilter = $request->get('activity_filter', 'newest');
        switch($activityFilter) {
            case 'oldest':
                $activityQuery->orderBy('created_at', 'asc');
                break;
            case 'stock_in':
                $activityQuery->where('transaction_type', 'stock_in')->orderBy('created_at', 'desc');
                break;
            case 'stock_out':
                $activityQuery->where('transaction_type', 'stock_out')->orderBy('created_at', 'desc');
                break;
            case 'Supplement':
            case 'Equipment':
            case 'Apparel':
            case 'Beverages':
            case 'Snacks':
            case 'Accessories':
            case 'Food':
            case 'Drink':
                $activityQuery->whereHas('inventorySupply', function($q) use ($activityFilter) {
                    $q->where('category', $activityFilter);
                })->orderBy('created_at', 'desc');
                break;
            default: // newest
                $activityQuery->orderBy('created_at', 'desc');
        }
        
        $recentActivity = $activityQuery->paginate(10)->withQueryString();

        // Calculate KPI stats for logs
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $monthStart = Carbon::now('Asia/Manila')->startOfMonth();

        $totalTransactions = InventoryTransaction::whereHas('inventorySupply')->count();
        $totalStockIn = InventoryTransaction::whereHas('inventorySupply')
            ->where('transaction_type', 'stock_in')
            ->where('created_at', '>=', $today)
            ->count();
        $totalStockOut = InventoryTransaction::whereHas('inventorySupply')
            ->where('transaction_type', 'stock_out')
            ->where('created_at', '>=', $today)
            ->count();
        $transactionsThisMonth = InventoryTransaction::whereHas('inventorySupply')
            ->where('created_at', '>=', $monthStart)
            ->count();
        
        return view('inventorySupplies.inventory-logs', compact(
            'recentActivity',
            'activityFilter',
            'totalTransactions',
            'totalStockIn',
            'totalStockOut',
            'transactionsThisMonth'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_number' => 'required|unique:inventory_supplies,product_number',
                'product_name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'unit_price' => 'required|numeric|min:0',
                'stock_qty' => 'required|integer|min:0',
                'low_stock_threshold' => 'required|integer|min:0',
            ], [
                'product_number.required' => 'Product number is required.',
                'product_number.unique' => 'This product number already exists.',
                'product_name.required' => 'Product name is required.',
                'category.required' => 'Category is required.',
                'unit_price.required' => 'Unit price is required.',
                'unit_price.numeric' => 'Unit price must be a number.',
                'unit_price.min' => 'Unit price must be at least 0.',
                'stock_qty.required' => 'Stock quantity is required.',
                'stock_qty.integer' => 'Stock quantity must be a whole number.',
                'stock_qty.min' => 'Stock quantity must be at least 0.',
                'low_stock_threshold.required' => 'Low stock threshold is required.',
                'low_stock_threshold.integer' => 'Low stock threshold must be a whole number.',
                'low_stock_threshold.min' => 'Low stock threshold must be at least 0.',
            ]);

            // Set last_restocked to current Philippines time if stock_qty > 0
            if ($validated['stock_qty'] > 0) {
                $validated['last_restocked'] = Carbon::now('Asia/Manila');
            }

            $item = InventorySupply::create($validated);

            // Create initial transaction record if stock qty > 0
            if ($validated['stock_qty'] > 0) {
                InventoryTransaction::create([
                    'inventory_supply_id' => $item->id,
                    'transaction_type' => 'stock_in',
                    'quantity' => $validated['stock_qty'],
                    'previous_stock' => 0,
                    'new_stock' => $validated['stock_qty'],
                    'notes' => 'Initial stock',
                    'performed_by' => auth()->user()->name ?? 'System',
                ]);
            }

            ActivityLog::log('created', 'inventory', "Added product: {$item->product_name} ({$item->product_number})", $item->product_number, null, $item, ['category' => $item->category, 'unit_price' => $item->unit_price, 'initial_stock' => $validated['stock_qty']]);

            return redirect()->route('inventory.index')
                            ->with('success', 'Product added successfully!');
                            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $item = InventorySupply::findOrFail($id);
            
            $validated = $request->validate([
                'product_name' => 'required|string|max:255',
                'category' => 'required|string|in:Supplement,Equipment,Apparel,Beverages,Snacks,Accessories,Food,Drink',
                'unit_price' => 'required|numeric|min:0',
            ], [
                'product_name.required' => 'Product name is required.',
                'category.required' => 'Category is required.',
                'category.in' => 'Please select a valid category.',
                'unit_price.required' => 'Unit price is required.',
                'unit_price.numeric' => 'Unit price must be a number.',
                'unit_price.min' => 'Unit price must be at least 0.',
            ]);

            $item->update($validated);

            ActivityLog::log('updated', 'inventory', "Updated product: {$item->product_name} ({$item->product_number})", $item->product_number, null, $item, ['category' => $item->category, 'unit_price' => $item->unit_price]);

            return redirect()->back()
                            ->with('success', 'Product updated successfully!');
                            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        }
    }

    public function destroy($id)
    {
        $item = InventorySupply::findOrFail($id);
        $productName = $item->product_name;
        $productNumber = $item->product_number;
        $item->delete();

        ActivityLog::log('deleted', 'inventory', "Deleted product: {$productName} ({$productNumber})", $productNumber);

        return redirect()->route('inventory.index')
                        ->with('success', 'Product deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inventory_supplies,id'
        ]);

        $deletedCount = InventorySupply::whereIn('id', $request->ids)->delete();

        ActivityLog::log('bulk_deleted', 'inventory', "Bulk deleted {$deletedCount} product(s)", null, null, null, ['count' => $deletedCount]);

        return redirect()->route('inventory.index')
                        ->with('success', "$deletedCount product(s) deleted successfully!");
    }

    public function stockTransaction(Request $request, $id)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:stock_in,stock_out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = InventorySupply::findOrFail($id);
        $previousStock = $item->stock_qty;

        // Calculate new stock based on transaction type
        if ($validated['transaction_type'] === 'stock_in') {
            $newStock = $previousStock + $validated['quantity'];
        } else {
            // Stock out
            if ($previousStock < $validated['quantity']) {
                return redirect()->back()
                    ->with('error', 'Insufficient stock! Current stock: ' . $previousStock);
            }
            $newStock = $previousStock - $validated['quantity'];
        }

        // Start database transaction
        DB::beginTransaction();
        try {
            // Update inventory stock with Philippines timezone
            $updateData = [
                'stock_qty' => $newStock,
            ];
            
            // Update last_restocked to Philippines time only for stock_in
            if ($validated['transaction_type'] === 'stock_in') {
                $updateData['last_restocked'] = Carbon::now('Asia/Manila');
            }
            
            $item->update($updateData);

            // Create transaction record
            InventoryTransaction::create([
                'inventory_supply_id' => $item->id,
                'transaction_type' => $validated['transaction_type'],
                'quantity' => $validated['quantity'],
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'notes' => $validated['notes'],
                'performed_by' => auth()->user()->name ?? 'System',
            ]);

            DB::commit();

            $actionLabel = $validated['transaction_type'] === 'stock_in' ? 'Stock In' : 'Stock Out';
            ActivityLog::log($validated['transaction_type'], 'inventory', "{$actionLabel}: {$validated['quantity']} unit(s) of {$item->product_name} ({$item->product_number})", $item->product_number, null, $item, ['quantity' => $validated['quantity'], 'previous_stock' => $previousStock, 'new_stock' => $newStock]);

            $message = $validated['transaction_type'] === 'stock_in' 
                ? 'Stock added successfully!' 
                : 'Stock removed successfully!';

            return redirect()->route('inventory.index')
                            ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

    public function transactionHistory($id)
    {
        $item = InventorySupply::with(['transactions' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('inventorySupplies.transaction-history', compact('item'));
    }

    /**
     * Return stock history as JSON for the modal AJAX request.
     */
    public function stockHistoryJson($id)
    {
        $item = InventorySupply::with(['transactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(20);
        }])->findOrFail($id);

        $totalIn = $item->transactions->where('transaction_type', 'stock_in')->sum('quantity');
        $totalOut = $item->transactions->where('transaction_type', 'stock_out')->sum('quantity');

        $transactions = $item->transactions->map(function ($t) {
            return [
                'transaction_type' => $t->transaction_type,
                'quantity' => $t->quantity,
                'previous_stock' => $t->previous_stock,
                'new_stock' => $t->new_stock,
                'notes' => $t->notes,
                'performed_by' => $t->performed_by,
                'date' => $t->created_at->timezone('Asia/Manila')->format('M d, Y h:i A'),
            ];
        });

        return response()->json([
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'transactions' => $transactions,
        ]);
    }
}