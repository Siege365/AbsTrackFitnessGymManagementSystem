<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrainerController extends Controller
{
    /**
     * Display trainer management page.
     */
    public function index(Request $request)
    {
        $query = Trainer::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('specialization', 'LIKE', "%{$search}%")
                  ->orWhere('contact_number', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        $trainers = $query->latest()->paginate(10)->appends([
            'search' => $request->search,
        ]);

        // Statistics
        $totalTrainers = Trainer::count();
        $newThisMonth = Trainer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $specializations = Trainer::whereNotNull('specialization')
            ->distinct('specialization')
            ->count('specialization');

        return view('UserAndAdmin.TrainerManagement', compact(
            'trainers',
            'totalTrainers',
            'newThisMonth',
            'specializations'
        ));
    }

    /**
     * Store a newly created trainer.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'contact_number' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'emergency_contact' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'address' => 'nullable|string|max:500',
            ]);

            $trainer = Trainer::create($validated);

            ActivityLog::log(
                'created',
                'trainer',
                "Added new trainer: {$validated['full_name']}",
                'TRN-' . $trainer->id,
                $validated['full_name'],
                $trainer,
                ['specialization' => $validated['specialization'] ?? null]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Trainer added successfully!']);
            }

            return redirect()->route('UserAndAdmin.TrainerManagement')
                ->with('success', 'Trainer added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create trainer: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create trainer.'], 500);
            }
            return back()->with('error', 'Failed to create trainer.');
        }
    }

    /**
     * Update the specified trainer.
     */
    public function update(Request $request, $id)
    {
        try {
            $trainer = Trainer::findOrFail($id);

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'specialization' => 'nullable|string|max:255',
                'contact_number' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'emergency_contact' => ['nullable', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'address' => 'nullable|string|max:500',
            ]);

            $oldName = $trainer->full_name;
            $trainer->update($validated);

            ActivityLog::log(
                'updated',
                'trainer',
                "Updated trainer: {$validated['full_name']}" . ($oldName !== $validated['full_name'] ? " (was: {$oldName})" : ''),
                'TRN-' . $trainer->id,
                $validated['full_name'],
                $trainer,
                ['specialization' => $validated['specialization'] ?? null]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Trainer updated successfully!']);
            }

            return redirect()->route('UserAndAdmin.TrainerManagement')
                ->with('success', 'Trainer updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to update trainer: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update trainer.'], 500);
            }
            return back()->with('error', 'Failed to update trainer.');
        }
    }

    /**
     * Remove the specified trainer.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $trainer = Trainer::findOrFail($id);
            $trainerName = $trainer->full_name;

            ActivityLog::log(
                'deleted',
                'trainer',
                "Deleted trainer: {$trainerName}",
                'TRN-' . $trainer->id,
                $trainerName,
                null,
                ['deleted_trainer' => $trainerName]
            );

            $trainer->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Trainer deleted successfully!']);
            }

            return redirect()->route('UserAndAdmin.TrainerManagement')
                ->with('success', 'Trainer deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete trainer: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete trainer.'], 500);
            }
            return back()->with('error', 'Failed to delete trainer.');
        }
    }

    /**
     * Bulk delete trainers.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validated = $request->validate([
                'trainer_ids' => 'required|array|min:1',
                'trainer_ids.*' => 'integer|exists:trainers,id',
            ]);

            $trainers = Trainer::whereIn('id', $validated['trainer_ids'])->get();
            $names = $trainers->pluck('full_name')->implode(', ');
            $count = $trainers->count();

            Trainer::whereIn('id', $validated['trainer_ids'])->delete();

            ActivityLog::log(
                'bulk_deleted',
                'trainer',
                "Bulk deleted {$count} trainer(s): {$names}",
                null,
                null,
                null,
                ['deleted_count' => $count, 'names' => $names]
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => "{$count} trainer(s) deleted successfully!"]);
            }

            return redirect()->route('UserAndAdmin.TrainerManagement')
                ->with('success', "{$count} trainer(s) deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to bulk delete trainers: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete trainers.'], 500);
            }
            return back()->with('error', 'Failed to delete trainers.');
        }
    }
}
