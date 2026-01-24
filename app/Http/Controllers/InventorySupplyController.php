<?php

namespace App\Http\Controllers;

use App\Models\InventorySupply;
use Illuminate\Http\Request;

class InventorySupplyController extends Controller
{
    public function index()
    {
        // Get inventory items with pagination (10 per page)
        $inventoryItems = InventorySupply::paginate(10);
        
        // Calculate statistics
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

    public function create()
    {
        return view('inventorySupplies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_number' => 'required|unique:inventory_supplies',
            'product_name' => 'required',
            'category' => 'required',
            'unit_price' => 'required|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'last_restocked' => 'nullable|date',
        ]);

        InventorySupply::create($validated);

        return redirect()->route('inventory.index')
                        ->with('success', 'Product added successfully!');
    }

    public function edit($id)
    {
        $item = InventorySupply::findOrFail($id);
        return view('inventorySupplies.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = InventorySupply::findOrFail($id);
        
        $validated = $request->validate([
            'product_number' => 'required|unique:inventory_supplies,product_number,' . $id,
            'product_name' => 'required',
            'category' => 'required',
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
}