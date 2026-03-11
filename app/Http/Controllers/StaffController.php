<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LogsActivity;
use App\Rules\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('contact', 'LIKE', "%{$search}%")
                  ->orWhere('role', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $staff = $query->latest()->paginate(10)->appends($request->only(['search', 'role', 'status']));

        $totalStaff = User::count();
        $activeStaff = User::where('status', 'active')->count();
        $adminCount = User::where('role', 'admin')->count();
        $cashierCount = User::where('role', 'cashier')->count();

        return view('UserAndAdmin.staff-management.index', compact(
            'staff', 'totalStaff', 'activeStaff', 'adminCount', 'cashierCount'
        ));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:users,email,' . $id,
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'role'    => 'required|in:admin,cashier',
            'status'  => 'required|in:active,inactive',
            'avatar'  => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars/staff', 'public');
        }

        $user->update($validated);

        $this->logActivity('updated', "Updated staff account: {$user->name}", $user, [], 'staff');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Staff account updated successfully!']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account updated successfully!');
    }

    public function destroy(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 403);
            }
            return redirect()->route('staff.index')->with('error', 'You cannot delete your own account.');
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $userName = $user->name;
        $user->delete();

        $this->logActivity('deleted', "Deleted staff account: {$userName}", null, [], 'staff');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Staff account deleted successfully!']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff account deleted successfully!');
    }

    public function toggleStatus(Request $request, string $id)
    {
        $request->validate([
            'cf-turnstile-response' => ['required', new Turnstile],
        ]);

        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot deactivate your own account.'], 403);
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $this->logActivity('updated', "Changed status of {$user->name} to {$user->status}", $user, [], 'staff');

        return response()->json(['success' => true, 'message' => "Staff status changed to {$user->status}."]);
    }
}
