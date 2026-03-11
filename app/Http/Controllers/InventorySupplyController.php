<?php

namespace App\Http\Controllers;

use App\Models\InventorySupply;
use App\Models\InventoryTransaction;
use App\Helpers\CategoryHelper;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\NotificationService;

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

    /**
     * Check a category name for similarity against existing categories.
     * Returns JSON with exact match, similar matches, and icon info.
     */
    public function checkCategory(Request $request)
    {
        $name = trim($request->get('name', ''));
        
        if (empty($name)) {
            return response()->json(['similar' => [], 'icon' => 'mdi-tag-outline']);
        }

        $similar = CategoryHelper::checkSimilarCategories($name);
        $icon = CategoryHelper::getIcon($name);

        return response()->json([
            'similar' => $similar,
            'icon' => $icon,
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
                    $query->whereColumn('stock_qty', '>', 'low_stock_threshold')
                          ->where('stock_qty', '>', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_qty', '<=', 'low_stock_threshold')
                          ->where('stock_qty', '>', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                case 'out_of_stock':
                    $query->where('stock_qty', 0)
                          ->orderBy('created_at', 'desc');
                    break;
                default:
                    // Dynamic category filter: check if the filter matches an existing category
                    $categoryExists = InventorySupply::where('category', $request->filter)->exists();
                    if ($categoryExists) {
                        $query->where('category', $request->filter)
                              ->orderBy('created_at', 'desc');
                    } else {
                        $query->orderBy('created_at', 'desc');
                    }
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        // Get paginated results
        $inventoryItems = $query->paginate(10)->withQueryString();
        
        // Calculate statistics (based on all items)
        $totalProducts = InventorySupply::count();
        $lowStockItems = InventorySupply::whereColumn('stock_qty', '<=', 'low_stock_threshold')
                                    ->where('stock_qty', '>', 0)
                                    ->count();
        $outOfStockItems = InventorySupply::where('stock_qty', 0)->count();
        $stockValue = InventorySupply::sum(DB::raw('unit_price * stock_qty'));

        // Get all categories for dynamic filters and dropdowns
        $categories = CategoryHelper::getAllCategories();
        
        return view('InventorySupplies.Inventory', compact(
            'inventoryItems',
            'totalProducts',
            'lowStockItems',
            'outOfStockItems',
            'stockValue',
            'categories'
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
            default:
                // Dynamic category filter: check if the filter matches an existing category
                $categoryExists = InventorySupply::where('category', $activityFilter)->exists();
                if ($categoryExists) {
                    $activityQuery->whereHas('inventorySupply', function($q) use ($activityFilter) {
                        $q->where('category', $activityFilter);
                    })->orderBy('created_at', 'desc');
                } else {
                    // Default: newest
                    $activityQuery->orderBy('created_at', 'desc');
                }
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

        // Get all categories for dynamic filters
        $categories = CategoryHelper::getAllCategories();
        
        return view('InventorySupplies.inventory-logs', compact(
            'recentActivity',
            'activityFilter',
            'totalTransactions',
            'totalStockIn',
            'totalStockOut',
            'transactionsThisMonth',
            'categories'
        ));
    }

    /**
     * Generate a distinct color for a new category
     * Ensures the color is visually different from existing category colors
     */
    private function generateDistinctColor()
    {
        // Get all existing category colors
        $existingColors = InventorySupply::whereNotNull('category_color')
            ->distinct()
            ->pluck('category_color')
            ->toArray();

        // Predefined distinct color palette (HSL-based for better distribution)
        $colorPalette = [
            '#E53935', '#D32F2F', '#C62828', // Reds
            '#8E24AA', '#7B1FA2', '#6A1B9A', // Purples
            '#5E35B1', '#512DA8', '#4527A0', // Deep Purples
            '#3949AB', '#303F9F', '#283593', // Indigos
            '#1E88E5', '#1976D2', '#1565C0', // Blues
            '#00897B', '#00796B', '#00695C', // Teals
            '#43A047', '#388E3C', '#2E7D32', // Greens
            '#7CB342', '#689F38', '#558B2F', // Light Greens
            '#FDD835', '#FBC02D', '#F9A825', // Yellows
            '#FFB300', '#FFA000', '#FF8F00', // Ambers
            '#FB8C00', '#F57C00', '#EF6C00', // Oranges
            '#F4511E', '#E64A19', '#D84315', // Deep Oranges
            '#6D4C41', '#5D4037', '#4E342E', // Browns
            '#546E7A', '#455A64', '#37474F', // Blue Greys
        ];

        // Filter out existing colors and similar colors
        $availableColors = array_filter($colorPalette, function($color) use ($existingColors) {
            foreach ($existingColors as $existingColor) {
                if ($this->areColorsSimilar($color, $existingColor)) {
                    return false;
                }
            }
            return true;
        });

        // If we have available distinct colors, use one randomly
        if (!empty($availableColors)) {
            return $availableColors[array_rand($availableColors)];
        }

        // If all colors are used, generate a random one
        // Ensure good saturation and brightness for visibility
        $hue = rand(0, 360);
        $saturation = rand(60, 90);
        $lightness = rand(35, 55);
        
        return $this->hslToHex($hue, $saturation, $lightness);
    }

    /**
     * Check if two colors are visually similar
     */
    private function areColorsSimilar($color1, $color2)
    {
        $rgb1 = $this->hexToRgb($color1);
        $rgb2 = $this->hexToRgb($color2);

        // Calculate Euclidean distance in RGB space
        $distance = sqrt(
            pow($rgb1['r'] - $rgb2['r'], 2) +
            pow($rgb1['g'] - $rgb2['g'], 2) +
            pow($rgb1['b'] - $rgb2['b'], 2)
        );

        // Colors are similar if distance is less than 50 (out of ~441 max)
        return $distance < 50;
    }

    /**
     * Convert hex color to RGB
     */
    private function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Convert HSL to Hex color
     */
    private function hslToHex($h, $s, $l)
    {
        $h /= 360;
        $s /= 100;
        $l /= 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = $this->hueToRgb($p, $q, $h + 1/3);
            $g = $this->hueToRgb($p, $q, $h);
            $b = $this->hueToRgb($p, $q, $h - 1/3);
        }

        return sprintf('#%02X%02X%02X', round($r * 255), round($g * 255), round($b * 255));
    }

    /**
     * Helper for HSL to RGB conversion
     */
    private function hueToRgb($p, $q, $t)
    {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;
        if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1/2) return $q;
        if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
        return $p;
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_number' => 'required|unique:inventory_supplies,product_number',
                'product_name' => 'required|string|max:255|unique:inventory_supplies,product_name',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'avatar_url' => 'nullable|url',
                'category' => 'required|string|max:255',
                'unit_price' => 'required|numeric|min:0',
                'stock_qty' => 'required|integer|min:0',
                'low_stock_threshold' => 'required|integer|min:0',
            ], [
                'product_number.required' => 'Product number is required.',
                'product_number.unique' => 'This product number already exists.',
                'product_name.required' => 'Product name is required.',
                'product_name.unique' => 'A product with this name already exists.',
                'avatar.image' => 'Avatar must be an image file.',
                'avatar.mimes' => 'Avatar must be a JPEG, PNG, GIF, or WebP image.',
                'avatar.max' => 'Avatar size must not exceed 2MB.',
                'avatar_url.url' => 'Avatar URL must be a valid URL.',
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

            // Handle avatar upload (file or URL)
            if ($request->hasFile('avatar')) {
                try {
                    $validated['avatar'] = $request->file('avatar')->store('product-avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                                    ->withErrors(['avatar' => 'Failed to upload avatar. Please try again.'])
                                    ->withInput();
                }
            } elseif ($request->filled('avatar_url')) {
                try {
                    $imageContent = file_get_contents($validated['avatar_url']);
                    if ($imageContent !== false) {
                        $extension = pathinfo(parse_url($validated['avatar_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = 'jpg';
                        }
                        $filename = 'product-avatars/' . uniqid() . '.' . $extension;
                        \Storage::disk('public')->put($filename, $imageContent);
                        $validated['avatar'] = $filename;
                    }
                } catch (\Exception $e) {
                    // Continue without avatar if URL download fails
                }
            }

            // Remove avatar_url from validated data as it's not a database field
            unset($validated['avatar_url']);

            // Strict category similarity check — reject categories too similar to existing ones
            $similarCategories = CategoryHelper::checkSimilarCategories($validated['category']);
            $highMatch = collect($similarCategories)->first(function ($s) {
                return $s['score'] >= 85 && $s['type'] !== 'exact';
            });
            $exactMatch = collect($similarCategories)->first(function ($s) {
                return $s['type'] === 'exact';
            });

            if ($exactMatch) {
                // If exact match (case-insensitive), normalize to the existing category name
                $validated['category'] = $exactMatch['name'];
            } elseif ($highMatch) {
                // Block creation for highly similar categories (plural forms, typos, etc.)
                return redirect()->back()
                    ->withErrors(['category' => 'This category is too similar to "' . $highMatch['name'] . '". Please use the existing category instead.'])
                    ->withInput();
            }

            // Auto-generate category color if not already set for this category
            $existingCategoryColor = InventorySupply::where('category', $validated['category'])
                ->whereNotNull('category_color')
                ->value('category_color');

            if ($existingCategoryColor) {
                // Use existing color for this category
                $validated['category_color'] = $existingCategoryColor;
            } else {
                // Generate a new distinct color for this new category
                $validated['category_color'] = $this->generateDistinctColor();
            }

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

            ActivityLog::log('created', 'inventory', "Added product: {$item->product_name}", $item->product_number, null, $item, ['category' => $item->category, 'unit_price' => $item->unit_price, 'initial_stock' => $validated['stock_qty']]);

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
                'product_name' => 'required|string|max:255|unique:inventory_supplies,product_name,' . $id,
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'avatar_url' => 'nullable|url',
                'category' => 'required|string|max:255',
                'unit_price' => 'required|numeric|min:0',
            ], [
                'product_name.required' => 'Product name is required.',
                'product_name.unique' => 'A product with this name already exists.',
                'avatar.image' => 'Avatar must be an image file.',
                'avatar.mimes' => 'Avatar must be a JPEG, PNG, GIF, or WebP image.',
                'avatar.max' => 'Avatar size must not exceed 2MB.',
                'avatar_url.url' => 'Avatar URL must be a valid URL.',
                'category.required' => 'Category is required.',
                'unit_price.required' => 'Unit price is required.',
                'unit_price.numeric' => 'Unit price must be a number.',
                'unit_price.min' => 'Unit price must be at least 0.',
            ]);

            // Handle avatar upload (file or URL)
            if ($request->hasFile('avatar')) {
                try {
                    // Delete old avatar if exists
                    if ($item->avatar) {
                        \Storage::disk('public')->delete($item->avatar);
                    }
                    $validated['avatar'] = $request->file('avatar')->store('product-avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                                    ->withErrors(['avatar' => 'Failed to upload avatar. Please try again.'])
                                    ->withInput();
                }
            } elseif ($request->filled('avatar_url')) {
                try {
                    // Delete old avatar if exists
                    if ($item->avatar) {
                        \Storage::disk('public')->delete($item->avatar);
                    }
                    $imageContent = file_get_contents($validated['avatar_url']);
                    if ($imageContent !== false) {
                        $extension = pathinfo(parse_url($validated['avatar_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = 'jpg';
                        }
                        $filename = 'product-avatars/' . uniqid() . '.' . $extension;
                        \Storage::disk('public')->put($filename, $imageContent);
                        $validated['avatar'] = $filename;
                    }
                } catch (\Exception $e) {
                    // Continue without updating avatar if URL download fails
                }
            }

            // Remove avatar_url from validated data as it's not a database field
            unset($validated['avatar_url']);

            // Strict category similarity check — reject categories too similar to existing ones
            if (isset($validated['category']) && $validated['category'] !== $item->category) {
                $similarCategories = CategoryHelper::checkSimilarCategories($validated['category']);
                $highMatch = collect($similarCategories)->first(function ($s) {
                    return $s['score'] >= 85 && $s['type'] !== 'exact';
                });
                $exactMatch = collect($similarCategories)->first(function ($s) {
                    return $s['type'] === 'exact';
                });

                if ($exactMatch) {
                    // If exact match (case-insensitive), normalize to the existing category name
                    $validated['category'] = $exactMatch['name'];
                } elseif ($highMatch) {
                    // Block update for highly similar categories
                    return redirect()->back()
                        ->withErrors(['category' => 'This category is too similar to "' . $highMatch['name'] . '". Please use the existing category instead.'])
                        ->withInput();
                }

                $existingCategoryColor = InventorySupply::where('category', $validated['category'])
                    ->whereNotNull('category_color')
                    ->where('id', '!=', $item->id)
                    ->value('category_color');

                if ($existingCategoryColor) {
                    // Use existing color for this category
                    $validated['category_color'] = $existingCategoryColor;
                } else {
                    // Generate a new distinct color for this new category
                    $validated['category_color'] = $this->generateDistinctColor();
                }
            }

            $item->update($validated);

            ActivityLog::log('updated', 'inventory', "Updated product: {$item->product_name}", $item->product_number, null, $item, ['category' => $item->category, 'unit_price' => $item->unit_price]);

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
        $item = InventorySupply::find($id);

        if (!$item) {
            return redirect()->route('inventory.index')
                            ->with('error', 'Product not found.');
        }

        $productName = $item->product_name;
        $productNumber = $item->product_number;
        $item->delete();

        ActivityLog::log('deleted', 'inventory', "Deleted product: {$productName}", $productNumber);

        return redirect()->route('inventory.index')
                        ->with('success', 'Product deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $existingIds = InventorySupply::whereIn('id', $request->ids)->pluck('id')->toArray();

        if (empty($existingIds)) {
            return redirect()->route('inventory.index')
                            ->with('error', 'Product not found.');
        }

        $deletedCount = InventorySupply::whereIn('id', $existingIds)->delete();

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

            // Check for low stock after transaction
            if ($newStock <= $item->low_stock_threshold) {
                if ($newStock == 0) {
                    NotificationService::outOfStock($item->product_name);
                } else {
                    NotificationService::lowStock($item->product_name, $newStock, $item->low_stock_threshold);
                }
            }

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

        $categories = CategoryHelper::getAllCategories();

        return view('InventorySupplies.transaction-history', compact('item', 'categories'));
    }

    /**
     * Return stock history as JSON for the modal AJAX request.
     */
    public function stockHistoryJson($id)
    {
        $item = InventorySupply::with(['transactions' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
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