<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Membership::query();
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('contact', 'LIKE', "%{$search}%")
                  ->orWhere('plan_type', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }
        
        $memberships = $query->latest()->paginate(10)->appends(['search' => $request->search]);
        
        // Calculate statistics using date-based queries (not status column)
        // This ensures real-time accuracy even if status column has stale data
        $today = Carbon::today();
        $sevenDaysFromNow = $today->copy()->addDays(7);
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $totalMembers = Membership::count();
        
        // Active: due_date is more than 7 days in the future
        $activeMembers = Membership::whereDate('due_date', '>', $sevenDaysFromNow)->count();
        
        // Expiring This Week: due_date is between today and 7 days from now (inclusive)
        $expiringThisWeek = Membership::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $sevenDaysFromNow)
            ->count();
        
        // New Signups This Month (based on start_date, not created_at)
        $newSignupsThisMonth = Membership::whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->count();
        
        return view('memberships.index', compact(
            'memberships',
            'totalMembers',
            'activeMembers',
            'expiringThisWeek',
            'newSignupsThisMonth'
        ));
    }

    /**
     * Search memberships for autocomplete (AJAX)
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');

        if (trim($q) === '') {
            return response()->json([]);
        }

        $results = Membership::where('name', 'LIKE', "%{$q}%")
            ->orWhere('contact', 'LIKE', "%{$q}%")
            ->orderBy('name')
            // fetch a slightly larger set then dedupe in-memory to avoid returning duplicates
            ->limit(30)
            ->get(['id', 'name', 'contact']);

        // Remove duplicate entries by normalizing name + contact, keep first occurrence
        $unique = $results->unique(function ($item) {
            return strtolower(trim($item->name)) . '|' . (isset($item->contact) ? trim($item->contact) : '');
        })->values()->take(10);

        return response()->json($unique);
    }

    /**
     * Check for similar names (same first name, different last name)
     * 
     * @param string $name - The name to check
     * @param int|null $excludeId - Exclude this ID from check (for updates)
     * @return array|null - Returns similar member info if found, null otherwise
     */
    private function checkSimilarNames($name, $excludeId = null)
    {
        $nameParts = explode(' ', trim($name));
        $firstName = strtolower($nameParts[0]);
        
        // Build query to find members with same first name
        $query = Membership::whereRaw('LOWER(SUBSTRING_INDEX(name, " ", 1)) = ?', [$firstName]);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $similarMembers = $query->get(['id', 'name']);
        
        // Check if any have different last names
        foreach ($similarMembers as $member) {
            $existingName = strtolower(trim($member->name));
            $newName = strtolower(trim($name));
            
            // If exact match, it's a duplicate
            if ($existingName === $newName) {
                return [
                    'type' => 'exact',
                    'message' => "A member with the exact name '{$member->name}' already exists.",
                    'existing' => $member->name
                ];
            }
            
            // Check if same first name but different full name
            if ($existingName !== $newName) {
                return [
                    'type' => 'similar',
                    'message' => "A member with a similar name '{$member->name}' already exists. Please confirm this is a different person.",
                    'existing' => $member->name
                ];
            }
        }
        
        return null;
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
                'avatar_url' => 'nullable|url',
                'plan_type' => 'required|in:Monthly,Session',
                'start_date' => 'required|date',
                'due_date' => 'required|date|after:start_date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
                'confirm_similar' => 'nullable|boolean',
            ]);

            // Check for similar names (unless user confirmed)
            if (!$request->input('confirm_similar')) {
                $similarCheck = $this->checkSimilarNames($validated['name']);
                if ($similarCheck) {
                    if ($similarCheck['type'] === 'exact') {
                        return response()->json([
                            'success' => false,
                            'message' => $similarCheck['message'],
                            'type' => 'duplicate'
                        ], 400);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => $similarCheck['message'],
                            'type' => 'similar',
                            'existing_name' => $similarCheck['existing'],
                            'requires_confirmation' => true
                        ], 409);
                    }
                }
            }

            // Remove confirm_similar from validated data
            unset($validated['confirm_similar']);

            // Calculate status automatically based on dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            // Handle avatar upload (file or URL)
            if ($request->hasFile('avatar')) {
                try {
                    $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
                } catch (\Exception $e) {
                    return response()->json(['success' => false, 'message' => 'Failed to upload avatar. Please try again.'], 400);
                }
            } elseif ($request->filled('avatar_url')) {
                try {
                    // Download image from URL using helper method
                    $imageContent = $this->downloadImageFromUrl($validated['avatar_url']);
                    if ($imageContent !== false) {
                        $extension = pathinfo(parse_url($validated['avatar_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = 'jpg';
                        }
                        $filename = 'avatars/' . uniqid() . '.' . $extension;
                        Storage::disk('public')->put($filename, $imageContent);
                        $validated['avatar'] = $filename;
                    } else {
                        Log::warning('Failed to download avatar from URL: ' . $validated['avatar_url']);
                    }
                } catch (\Exception $e) {
                    // If URL download fails, continue without avatar
                    Log::warning('Failed to download avatar from URL: ' . $validated['avatar_url'] . ' - ' . $e->getMessage());
                }
            }

            // Remove avatar_url from validated data as it's not in the database
            unset($validated['avatar_url']);

            Membership::create($validated);

            // Return JSON response for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Membership created successfully!']);
            }

            return redirect()->route('memberships.index')
                ->with('success', 'Membership created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'An error occurred while creating the membership.'], 500);
            }
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
            return DB::transaction(function () use ($request, $id) {
                $membership = Membership::lockForUpdate()->findOrFail($id);

                $validated = $request->validate([
                    'name' => 'required|string|max:255',
                    'age' => 'nullable|integer|min:1|max:120',
                    'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                    'avatar_url' => 'nullable|url',
                    'plan_type' => 'required|in:Monthly,Session',
                    'start_date' => 'required|date',
                    'due_date' => 'required|date|after:start_date',
                    'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9() ]+$/'],
                ]);

            // Recalculate status automatically based on updated dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            // Handle avatar upload (file or URL)
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
            } elseif ($request->filled('avatar_url')) {
                try {
                    // Download image from URL using helper method
                    $imageContent = $this->downloadImageFromUrl($validated['avatar_url']);
                    if ($imageContent !== false) {
                        // Delete old avatar
                        if ($membership->avatar && Storage::disk('public')->exists($membership->avatar)) {
                            Storage::disk('public')->delete($membership->avatar);
                        }
                        $extension = pathinfo(parse_url($validated['avatar_url'], PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = 'jpg';
                        }
                        $filename = 'avatars/' . uniqid() . '.' . $extension;
                        Storage::disk('public')->put($filename, $imageContent);
                        $validated['avatar'] = $filename;
                    } else {
                        Log::warning('Failed to download avatar from URL: ' . $validated['avatar_url']);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to download avatar from URL: ' . $validated['avatar_url'] . ' - ' . $e->getMessage());
                }
            }

            // Remove avatar_url from validated data as it's not in the database
            unset($validated['avatar_url']);

            $membership->update($validated);

            return redirect()->route('memberships.index')
                ->with('success', 'Membership updated successfully!');
            });
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
    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $membership = Membership::lockForUpdate()->findOrFail($id);
                
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

                // Return JSON response for AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Membership deleted successfully!'
                    ]);
                }

                return redirect()->route('memberships.index')
                    ->with('success', 'Membership deleted successfully!');
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Membership not found.'
                ], 404);
            }
            return redirect()->route('memberships.index')
                ->with('error', 'Membership not found.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the membership. Please try again.'
                ], 500);
            }
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
    public function renew(Request $request, Membership $membership)
    {
        try {
            return DB::transaction(function () use ($request, $membership) {
                // Lock the record to prevent race conditions
                $membership = Membership::lockForUpdate()->findOrFail($membership->id);
                
                // Validate input
                $validated = $request->validate([
                    'start_date' => 'required|date',
                    'due_date' => 'required|date|after_or_equal:start_date'
                ]);
                
                // Parse dates
                $newStartDate = Carbon::parse($validated['start_date']);
                $newDueDate = Carbon::parse($validated['due_date']);
                
                // Additional validation: Ensure dates make sense
                if ($newDueDate->lte($newStartDate)) {
                    throw new \Exception('Due date must be after start date');
                }
                
                // Prevent backdating more than 30 days
                if ($newStartDate->lt(Carbon::now()->subDays(30))) {
                    throw new \Exception('Start date cannot be more than 30 days in the past');
                }
                
                // Update the membership (status will be calculated automatically by accessor)
                $membership->update([
                    'start_date' => $newStartDate,
                    'due_date' => $newDueDate
                ]);
                
                // Return JSON response for AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Membership renewed successfully! New due date: ' . $newDueDate->format('M d, Y'),
                        'data' => [
                            'start_date' => $newStartDate->format('Y-m-d'),
                            'due_date' => $newDueDate->format('Y-m-d'),
                            'status' => $membership->status
                        ]
                    ]);
                }
                
                // Fallback for regular form submission
                return redirect()->route('memberships.index')
                    ->with('success', 'Membership renewed successfully! New due date: ' . $newDueDate->format('M d, Y'));
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error renewing membership: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while renewing the membership'
                ], 500);
            }
            
            return back()->with('error', 'An error occurred while renewing the membership');
        }
    }

    /**
     * Get real-time KPI statistics (AJAX endpoint)
     * Calculates fresh stats using date-based queries
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKpis()
    {
        try {
            $today = Carbon::today();
            $sevenDaysFromNow = $today->copy()->addDays(7);
            $currentMonth = now()->month;
            $currentYear = now()->year;
            
            // Use date-based queries to ensure real-time accuracy
            $totalMembers = Membership::count();
            
            // Active: due_date is more than 7 days in the future
            $activeMembers = Membership::whereDate('due_date', '>', $sevenDaysFromNow)->count();
            
            // Expiring This Week: due_date is between today and 7 days from now
            $expiringThisWeek = Membership::whereDate('due_date', '>=', $today)
                ->whereDate('due_date', '<=', $sevenDaysFromNow)
                ->count();
            
            // New Signups This Month (based on start_date, not created_at)
            $newSignupsThisMonth = Membership::whereMonth('start_date', $currentMonth)
                ->whereYear('start_date', $currentYear)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalMembers,
                    'active' => $activeMembers,
                    'expiring' => $expiringThisWeek,
                    'new_signups' => $newSignupsThisMonth
                ],
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching KPIs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data'
            ], 500);
        }
    }

    /**
     * Download image from URL using cURL for better HTTPS support
     * 
     * @param string $url
     * @return string|false
     */
    private function downloadImageFromUrl($url)
    {
        // Try cURL first (better SSL support)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $imageContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300 && $imageContent !== false) {
                return $imageContent;
            }
        }
        
        // Fallback to file_get_contents with context
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        
        return @file_get_contents($url, false, $context);
    }
}
