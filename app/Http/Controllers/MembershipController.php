<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $memberships = Membership::latest()->paginate(10);
        
        // Calculate statistics
        $totalMembers = Membership::count();
        $activeMembers = Membership::where('status', 'Active')->count();
        $expiringThisWeek = Membership::where('status', 'Due soon')->count();
        $newSignupsThisMonth = Membership::whereMonth('created_at', now()->month)->count();
        
        return view('memberships.index', compact(
            'memberships',
            'totalMembers',
            'activeMembers',
            'expiringThisWeek',
            'newSignupsThisMonth'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('memberships.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'nullable|integer|min:1|max:120',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'plan_type' => 'required|in:Monthly,Session',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after:start_date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
            ]);

            // Calculate status automatically based on dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            if ($request->hasFile('avatar')) {
                try {
                    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload avatar. Please try again.');
                }
            }

            Membership::create($validated);

            return redirect()->route('memberships.index')
                ->with('success', 'Membership created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the membership. Please try again.');
        }
    }

    /**
     * Calculate membership status based on dates.
     */
    private function calculateStatus($startDate, $dueDate)
    {
        try {
            $today = now()->startOfDay();
            $dueDate = \Carbon\Carbon::parse($dueDate)->startOfDay();
            $daysUntilDue = $today->diffInDays($dueDate, false);

            // If due date has passed (negative days), status is Expired
            if ($daysUntilDue < 0) {
                return 'Expired';
            }
            
            // If due date is within 7 days, status is Due soon
            if ($daysUntilDue <= 7) {
                return 'Due soon';
            }
            
            // Otherwise, status is Active
            return 'Active';
        } catch (\Exception $e) {
            // Default to Active if there's any error in calculation
            Log::warning("Error calculating status: " . $e->getMessage());
            return 'Active';
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $membership = Membership::findOrFail($id);
            return view('memberships.show', compact('membership'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'Membership not found.');
        } catch (\Exception $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'An error occurred while retrieving the membership.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $membership = Membership::findOrFail($id);
            return view('memberships.edit', compact('membership'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'Membership not found.');
        } catch (\Exception $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $membership = Membership::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'nullable|integer|min:1|max:120',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'plan_type' => 'required|in:Monthly,Session',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after:start_date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
            ]);

            // Recalculate status automatically based on updated dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            if ($request->hasFile('avatar')) {
                try {
                    // Delete old avatar
                    if ($membership->avatar && Storage::disk('public')->exists($membership->avatar)) {
                        Storage::disk('public')->delete($membership->avatar);
                    }
                    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Failed to upload new avatar. Please try again.');
                }
            }

            $membership->update($validated);

            return redirect()->route('memberships.index')
                ->with('success', 'Membership updated successfully!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'Membership not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the membership. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $membership = Membership::findOrFail($id);
            
            // Delete avatar if exists
            if ($membership->avatar) {
                try {
                    if (Storage::disk('public')->exists($membership->avatar)) {
                        Storage::disk('public')->delete($membership->avatar);
                    }
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    Log::warning("Failed to delete avatar for membership {$id}: " . $e->getMessage());
                }
            }
            
            $membership->delete();

            return redirect()->route('memberships.index')
                ->with('success', 'Membership deleted successfully!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'Membership not found.');
        } catch (\Exception $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'An error occurred while deleting the membership. Please try again.');
        }
    }

    /**
     * Bulk delete memberships.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $membershipIds = $request->input('membership_ids', []);
            
            if (empty($membershipIds)) {
                return redirect()->route('memberships.index')
                    ->with('error', 'No memberships selected for deletion.');
            }

            $deletedCount = 0;
            $errors = [];

            foreach ($membershipIds as $id) {
                try {
                    $membership = Membership::findOrFail($id);
                    
                    // Delete avatar if exists
                    if ($membership->avatar && Storage::disk('public')->exists($membership->avatar)) {
                        Storage::disk('public')->delete($membership->avatar);
                    }
                    
                    $membership->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete membership ID {$id}";
                    Log::warning("Bulk delete error for membership {$id}: " . $e->getMessage());
                }
            }

            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} membership(s).";
                if (!empty($errors)) {
                    $message .= " However, " . count($errors) . " deletion(s) failed.";
                }
                return redirect()->route('memberships.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('memberships.index')
                    ->with('error', 'Failed to delete any memberships. Please try again.');
            }
            
        } catch (\Exception $e) {
            return redirect()->route('memberships.index')
                ->with('error', 'An error occurred during bulk deletion. Please try again.');
        }
    }

    /**
     * Renew a membership subscription
     */
    public function renew(Membership $membership)
    {
        try {
            // Set the new start date to today
            $newStartDate = Carbon::today();
            
            // Calculate the new due date (1 month from new start date)
            $newDueDate = Carbon::today()->addMonth();
            
            // Update the membership
            $membership->update([
                'start_date' => $newStartDate,
                'due_date' => $newDueDate,
                'status' => $this->calculateStatus($newStartDate, $newDueDate)
            ]);
            
            return redirect()->route('memberships.index')
                ->with('success', 'Membership renewed successfully! New due date: ' . $newDueDate->format('M d, Y'));
                
        } catch (\Exception $e) {
            Log::error('Membership renewal error: ' . $e->getMessage());
            return redirect()->route('memberships.index')
                ->with('error', 'An error occurred while renewing the membership. Please try again.');
        }
    }
}
