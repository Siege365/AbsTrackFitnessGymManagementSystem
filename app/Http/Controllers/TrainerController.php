<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainerController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = Trainer::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('specialization', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $trainers = $query->latest()->paginate(10)->appends($request->only(['search', 'status']));

        $totalTrainers = Trainer::count();
        $activeTrainers = Trainer::where('status', 'active')->count();
        $inactiveTrainers = Trainer::where('status', 'inactive')->count();

        return view('UserAndAdmin.trainers.index', compact(
            'trainers', 'totalTrainers', 'activeTrainers', 'inactiveTrainers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'         => 'required|string|max:255',
            'specialization'    => 'nullable|string|max:255',
            'contact_number'    => 'required|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'birth_date'        => 'nullable|date',
            'address'           => 'nullable|string|max:500',
            'avatar'            => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars/trainers', 'public');
        }

        $trainer = Trainer::create($validated);

        $this->logActivity('created', "Added new trainer: {$trainer->full_name}", $trainer, [], 'trainer');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Trainer added successfully!']);
        }

        return redirect()->route('trainers.index')->with('success', 'Trainer added successfully!');
    }

    public function update(Request $request, string $id)
    {
        $trainer = Trainer::findOrFail($id);

        $validated = $request->validate([
            'full_name'         => 'required|string|max:255',
            'specialization'    => 'nullable|string|max:255',
            'contact_number'    => 'required|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'birth_date'        => 'nullable|date',
            'address'           => 'nullable|string|max:500',
            'status'            => 'nullable|in:active,inactive',
            'avatar'            => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($trainer->avatar && Storage::disk('public')->exists($trainer->avatar)) {
                Storage::disk('public')->delete($trainer->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars/trainers', 'public');
        }

        $trainer->update($validated);

        $this->logActivity('updated', "Updated trainer: {$trainer->full_name}", $trainer, [], 'trainer');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Trainer updated successfully!']);
        }

        return redirect()->route('trainers.index')->with('success', 'Trainer updated successfully!');
    }

    public function destroy(Request $request, string $id)
    {
        $trainer = Trainer::findOrFail($id);

        if ($trainer->avatar && Storage::disk('public')->exists($trainer->avatar)) {
            Storage::disk('public')->delete($trainer->avatar);
        }

        $trainerName = $trainer->full_name;
        $trainer->delete();

        $this->logActivity('deleted', "Deleted trainer: {$trainerName}", null, [], 'trainer');

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Trainer deleted successfully!']);
        }

        return redirect()->route('trainers.index')->with('success', 'Trainer deleted successfully!');
    }
}
