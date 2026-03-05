<?php

namespace App\Http\Controllers;

use App\Models\GymPlan;
use App\Models\InventorySupply;
use App\Helpers\CategoryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GymConfigurationController extends Controller
{
    /**
     * Display the configuration page.
     */
    public function index()
    {
        $membershipPlans = GymPlan::membership()->ordered()->paginate(10, ['*'], 'membership_page');
        $ptPlans         = GymPlan::personalTraining()->ordered()->paginate(10, ['*'], 'pt_page');

        // Get categories with product counts for category management
        $categoriesCollection = InventorySupply::select('category', 'category_color')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category', 'category_color')
            ->selectRaw('COUNT(*) as product_count')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'name'          => $item->category,
                    'color'         => $item->category_color,
                    'icon'          => CategoryHelper::getIcon($item->category),
                    'product_count' => $item->product_count,
                ];
            })
            ->sortBy('name')
            ->values();

        // Manually paginate the categories collection
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('category_page');
        $currentItems = $categoriesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $categories = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $categoriesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'category_page']
        );

        return view('configuration.index', compact('membershipPlans', 'ptPlans', 'categories'));
    }

    /**
     * Store a new plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'         => 'required|in:membership,personal_training',
            'plan_name'        => 'required|string|max:100',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1',
            'duration_label'   => 'nullable|string|max:50',
            'badge_text'       => 'nullable|string|max:30',
            'badge_color'      => 'nullable|string|max:20',
            'requires_student' => 'boolean',
            'requires_buddy'   => 'boolean',
            'buddy_count'      => 'integer|min:1|max:10',
            'description'      => 'nullable|string|max:255',
        ]);

        // Auto-generate plan_key from plan_name
        $validated['plan_key'] = $this->generatePlanKey($validated['plan_name']);
        $validated['requires_student'] = $request->boolean('requires_student');
        $validated['requires_buddy']   = $request->boolean('requires_buddy');
        $validated['buddy_count']      = $request->input('buddy_count', 1);

        // Set sort_order to the next value for that category
        $maxSort = GymPlan::where('category', $validated['category'])->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        $plan = GymPlan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully.',
            'plan'    => $plan,
        ]);
    }

    /**
     * Update an existing plan.
     */
    public function update(Request $request, $id)
    {
        $plan = GymPlan::findOrFail($id);

        $validated = $request->validate([
            'plan_name'        => 'required|string|max:100',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1',
            'duration_label'   => 'nullable|string|max:50',
            'badge_text'       => 'nullable|string|max:30',
            'badge_color'      => 'nullable|string|max:20',
            'requires_student' => 'boolean',
            'requires_buddy'   => 'boolean',
            'buddy_count'      => 'integer|min:1|max:10',
            'description'      => 'nullable|string|max:255',
            'is_active'        => 'boolean',
        ]);

        $validated['requires_student'] = $request->boolean('requires_student');
        $validated['requires_buddy']   = $request->boolean('requires_buddy');
        $validated['buddy_count']      = $request->input('buddy_count', 1);
        $validated['is_active']        = $request->boolean('is_active', true);

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plan updated successfully.',
            'plan'    => $plan->fresh(),
        ]);
    }

    /**
     * Delete a plan.
     */
    public function destroy($id)
    {
        $plan = GymPlan::findOrFail($id);
        $planName = $plan->plan_name;
        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => "Plan \"{$planName}\" deleted successfully.",
        ]);
    }

    /**
     * Toggle plan status (enable/disable).
     */
    public function toggleStatus(Request $request, $id)
    {
        $plan = GymPlan::findOrFail($id);
        
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $plan->is_active = $validated['is_active'];
        $plan->save();

        $status = $plan->is_active ? 'enabled' : 'disabled';

        return response()->json([
            'success' => true,
            'message' => "Plan \"{$plan->plan_name}\" has been {$status}.",
        ]);
    }

    /**
     * Reorder plans (drag & drop / sort).
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order'        => 'required|array',
            'order.*.id'   => 'required|exists:gym_plans,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            GymPlan::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan order updated.',
        ]);
    }

    /**
     * Get all active plans as JSON (for Payment page).
     */
    public function activePlans()
    {
        $plans = GymPlan::active()->ordered()->get();

        return response()->json([
            'membership'        => $plans->where('category', 'membership')->values(),
            'personal_training' => $plans->where('category', 'personal_training')->values(),
        ]);
    }

    /**
     * Update a category (rename and/or change color).
     */
    public function updateCategory(Request $request, $name)
    {
        $name = urldecode($name);

        $validated = $request->validate([
            'new_name'  => 'required|string|max:100',
            'new_color' => 'nullable|string|max:20',
        ]);

        $newName  = trim($validated['new_name']);
        $newColor = $validated['new_color'] ?? null;

        // Check if category exists
        $count = InventorySupply::where('category', $name)->count();
        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        // If renaming, check for similarity with existing categories
        if (strtolower($newName) !== strtolower($name)) {
            $existing = InventorySupply::whereRaw('LOWER(category) = ?', [strtolower($newName)])
                ->where('category', '!=', $name)
                ->exists();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => "A category named \"{$newName}\" already exists.",
                ], 422);
            }

            // Check similarity
            $similar = CategoryHelper::checkSimilarCategories($newName, 85.0);
            $similar = array_filter($similar, fn($s) => strtolower($s['name']) !== strtolower($name));
            if (!empty($similar)) {
                $top = reset($similar);
                return response()->json([
                    'success' => false,
                    'message' => "The name \"{$newName}\" is too similar to \"{$top['name']}\" ({$top['score']}% match).",
                ], 422);
            }
        }

        // Update all products with this category
        InventorySupply::where('category', $name)->update([
            'category'       => $newName,
            'category_color' => $newColor,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Category updated successfully. {$count} product(s) affected.",
        ]);
    }

    /**
     * Delete a category (optionally reassign products to another category).
     */
    public function destroyCategory(Request $request, $name)
    {
        $name = urldecode($name);

        $count = InventorySupply::where('category', $name)->count();
        if ($count === 0) {
            return response()->json(['success' => false, 'message' => 'Category not found.'], 404);
        }

        $reassignTo = $request->input('reassign_to');

        if ($count > 0 && $reassignTo) {
            // Verify destination category exists
            $destExists = InventorySupply::where('category', $reassignTo)->exists();
            if (!$destExists) {
                return response()->json([
                    'success' => false,
                    'message' => "Destination category \"{$reassignTo}\" does not exist.",
                ], 422);
            }

            // Get destination color
            $destColor = InventorySupply::where('category', $reassignTo)->value('category_color');

            // Reassign products
            InventorySupply::where('category', $name)->update([
                'category'       => $reassignTo,
                'category_color' => $destColor,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Category \"{$name}\" deleted. {$count} product(s) reassigned to \"{$reassignTo}\".",
            ]);
        } elseif ($count > 0 && !$reassignTo) {
            // Clear category from products (set to null)
            InventorySupply::where('category', $name)->update([
                'category'       => null,
                'category_color' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Category \"{$name}\" deleted. {$count} product(s) are now uncategorized.",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Category \"{$name}\" deleted successfully.",
        ]);
    }

    /**
     * Generate a unique PascalCase key from the plan name.
     */
    private function generatePlanKey(string $name): string
    {
        $base = Str::studly($name);
        $key  = $base;
        $i    = 1;

        while (GymPlan::where('plan_key', $key)->exists()) {
            $key = $base . $i;
            $i++;
        }

        return $key;
    }
}
