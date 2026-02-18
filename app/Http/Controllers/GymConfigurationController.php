<?php

namespace App\Http\Controllers;

use App\Models\GymPlan;
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
        $membershipPlans = GymPlan::membership()->ordered()->get();
        $ptPlans         = GymPlan::personalTraining()->ordered()->get();

        return view('configuration.index', compact('membershipPlans', 'ptPlans'));
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
