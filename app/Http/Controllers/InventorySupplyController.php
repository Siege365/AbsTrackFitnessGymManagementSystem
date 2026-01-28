<?php

namespace App\Http\Controllers;

use App\Models\InventorySupply;
use Illuminate\Http\Request;

class InventorySupplyController extends Controller
{
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
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Get paginated results
        $inventoryItems = $query->paginate(10)->withQueryString();
        
        // Calculate statistics (based on filtered results or all)
        $totalProducts = InventorySupply::count();
        $lowStockItems = InventorySupply::whereColumn('stock_qty', '<', 'low_stock_threshold')
                                    ->where('stock_qty', '>', 0)
                                    ->count();
        $outOfStockItems = InventorySupply::where('stock_qty', 0)->count();
        $stockValue = InventorySupply::sum(\DB::raw('unit_price * stock_qty'));
        
        return view('inventorySupplies.inventory', compact(
            'inventoryItems',
            'totalProducts',
            'lowStockItems',
            'outOfStockItems',
            'stockValue'
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
                'last_restocked' => 'nullable|date',
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

            InventorySupply::create($validated);

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
        $item = InventorySupply::findOrFail($id);
        
        $validated = $request->validate([
            'product_number' => 'required|unique:inventory_supplies,product_number,' . $id,
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'last_restocked' => 'nullable|date',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.index')
                        ->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $item = InventorySupply::findOrFail($id);
        $item->delete();

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

        return redirect()->route('inventory.index')
                        ->with('success', "$deletedCount product(s) deleted successfully!");
    }
}