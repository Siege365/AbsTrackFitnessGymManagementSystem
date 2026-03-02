<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff accounts.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'staff');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $staff = $query->latest()->paginate(10)->appends([
            'search' => $request->search,
        ]);

        // Statistics
        $totalStaff = User::where('role', 'staff')->count();
        $totalAdmin = User::where('role', 'admin')->count();
        $newThisMonth = User::where('role', 'staff')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('UserAndAdmin.UserManagement', compact(
            'staff',
            'totalStaff',
            'totalAdmin',
            'newThisMonth'
        ));
    }

    /**
     * Store a newly created staff account.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'contact_number' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'emergency_contact' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'address' => 'nullable|string|max:500',
            ], [
                'email.unique' => 'This email address is already registered.',
                'password.confirmed' => 'The password confirmation does not match.',
                'password.min' => 'Password must be at least 8 characters.',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                'contact_number' => $validated['contact_number'],
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            ActivityLog::log(
                'created',
                'staff',
                "Added new staff account: {$validated['name']}",
                'STAFF-' . $user->id,
                $validated['name'],
                $user,
                ['email' => $validated['email']]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Staff account created successfully!']);
            }

            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('success', 'Staff account created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            Log::error('Error creating staff account: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the staff account.'
                ], 500);
            }
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the staff account.');
        }
    }

    /**
     * Update the specified staff account.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::where('role', 'staff')->findOrFail($id);

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'contact_number' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'emergency_contact' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'address' => 'nullable|string|max:500',
            ];

            // Only validate password if provided
            if ($request->filled('password')) {
                $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
            }

            $validated = $request->validate($rules, [
                'email.unique' => 'This email address is already in use by another account.',
                'password.confirmed' => 'The password confirmation does not match.',
                'password.min' => 'Password must be at least 8 characters.',
            ]);

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'contact_number' => $validated['contact_number'],
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'address' => $validated['address'] ?? null,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            ActivityLog::log(
                'updated',
                'staff',
                "Updated staff account: {$user->name}",
                'STAFF-' . $user->id,
                $user->name,
                $user,
                ['email' => $user->email]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Staff account updated successfully!']);
            }

            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('success', 'Staff account updated successfully!');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Staff account not found.'], 404);
            }
            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('error', 'Staff account not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            Log::error('Error updating staff account: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the staff account.'
                ], 500);
            }
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while updating the staff account.');
        }
    }

    /**
     * Remove the specified staff account.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $user = User::where('role', 'staff')->lockForUpdate()->findOrFail($id);
                $userName = $user->name;
                $userId = $user->id;

                // Prevent deleting yourself
                if ($user->id === auth()->id()) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You cannot delete your own account.'
                        ], 403);
                    }
                    return redirect()->route('UserAndAdmin.UserManagement')
                        ->with('error', 'You cannot delete your own account.');
                }

                $user->delete();

                ActivityLog::log(
                    'deleted',
                    'staff',
                    "Deleted staff account: {$userName}",
                    'STAFF-' . $userId,
                    $userName
                );

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => true, 'message' => 'Staff account deleted successfully!']);
                }

                return redirect()->route('UserAndAdmin.UserManagement')
                    ->with('success', 'Staff account deleted successfully!');
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Staff account not found.'], 404);
            }
            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('error', 'Staff account not found.');
        } catch (\Exception $e) {
            Log::error('Error deleting staff account: ' . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the staff account.'
                ], 500);
            }
            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('error', 'An error occurred while deleting the staff account.');
        }
    }

    /**
     * Bulk delete staff accounts.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'staff_ids' => 'required|array',
                'staff_ids.*' => 'exists:users,id'
            ]);

            $staffIds = $request->staff_ids;
            $deletedCount = 0;
            $errors = [];

            foreach ($staffIds as $id) {
                try {
                    // Skip own account
                    if ((int)$id === auth()->id()) {
                        $errors[] = "Cannot delete your own account";
                        continue;
                    }

                    $user = User::where('role', 'staff')->findOrFail($id);
                    $user->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete staff ID: {$id}";
                    Log::error("Bulk delete error for staff {$id}: " . $e->getMessage());
                }
            }

            if ($deletedCount > 0) {
                ActivityLog::log(
                    'bulk_deleted',
                    'staff',
                    "Bulk deleted {$deletedCount} staff account(s)",
                    null,
                    null,
                    null,
                    ['count' => $deletedCount]
                );

                $message = "Successfully deleted {$deletedCount} staff account" . ($deletedCount > 1 ? 's' : '') . ".";
                if (count($errors) > 0) {
                    $message .= " However, " . count($errors) . " account" . (count($errors) > 1 ? 's' : '') . " could not be deleted.";
                }
                return redirect()->route('UserAndAdmin.UserManagement')->with('success', $message);
            } else {
                return redirect()->route('UserAndAdmin.UserManagement')
                    ->with('error', 'No staff accounts were deleted. ' . implode(', ', $errors));
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('error', 'Invalid request. Please select valid staff to delete.');
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->route('UserAndAdmin.UserManagement')
                ->with('error', 'An error occurred while deleting staff accounts.');
        }
    }
}
