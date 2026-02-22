<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Unified autocomplete API for customer search
     * Used across the application for finding existing customers
     * Returns customers with their active subscriptions info
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('query', '');
        
        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $customers = Customer::where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('contact', 'LIKE', "%{$query}%");
            })
            ->with(['activeClient', 'activeMembership'])
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'contact' => $customer->contact,
                    'avatar' => $customer->avatar,
                    'age' => $customer->age,
                    'sex' => $customer->sex,
                    // Include subscription status
                    'has_active_client' => $customer->hasActiveClient(),
                    'has_active_membership' => $customer->hasActiveMembership(),
                    'active_client' => $customer->activeClient ? [
                        'id' => $customer->activeClient->id,
                        'plan_type' => $customer->activeClient->plan_type,
                        'due_date' => $customer->activeClient->due_date,
                    ] : null,
                    'active_membership' => $customer->activeMembership ? [
                        'id' => $customer->activeMembership->id,
                        'plan_type' => $customer->activeMembership->plan_type,
                        'due_date' => $customer->activeMembership->due_date,
                    ] : null,
                ];
            });

        return response()->json($customers);
    }

    /**
     * Get or create a customer by name and contact
     * Used when adding new clients/memberships
     */
    public function findOrCreate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'required|string|max:255',
            'avatar' => 'nullable|string',
            'age' => 'nullable|integer|min:1|max:150',
            'sex' => 'nullable|in:Male,Female,Other',
        ]);

        $customer = Customer::firstOrCreate(
            ['contact' => $validated['contact']],
            [
                'name' => $validated['name'],
                'avatar' => $validated['avatar'] ?? null,
                'age' => $validated['age'] ?? null,
                'sex' => $validated['sex'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Update customer information
     * Used when updating client/membership details
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|string|max:255|unique:customers,contact,' . $id,
            'avatar' => 'nullable|string',
            'age' => 'nullable|integer|min:1|max:150',
            'sex' => 'nullable|in:Male,Female,Other',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer->fresh(),
        ]);
    }
}
