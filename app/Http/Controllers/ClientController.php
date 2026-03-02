<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Customer;
use App\Models\GymPlan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::with('gymPlan');
        
        // Define date ranges for status filtering
        $today = Carbon::today();
        $sevenDaysFromNow = $today->copy()->addDays(7);
        
        // Status filter (based on due_date)
        if ($request->has('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'active') {
                $query->whereDate('due_date', '>', $sevenDaysFromNow);
            } elseif ($status === 'due_soon') {
                $query->whereDate('due_date', '>=', $today)
                      ->whereDate('due_date', '<=', $sevenDaysFromNow);
            } elseif ($status === 'expired') {
                $query->whereDate('due_date', '<', $today);
            }
        }
        
        // Plan type filter
        if ($request->has('plan') && $request->plan !== 'all') {
            $query->where('plan_type', $request->plan);
        }
        
        // Gender filter
        if ($request->has('gender') && $request->gender !== 'all') {
            $query->where('sex', $request->gender);
        }
        
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
        
        $clients = $query->latest()->paginate(10)->appends([
            'search' => $request->search,
            'status' => $request->status,
            'plan' => $request->plan,
            'gender' => $request->gender,
        ]);
        
        // Calculate statistics using date-based queries (not status column)
        // This ensures real-time accuracy even if status column has stale data
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $totalClients = Client::count();
        
        // Active: due_date is more than 7 days in the future
        $activeClients = Client::whereDate('due_date', '>', $sevenDaysFromNow)->count();
        
        // Expiring This Week: due_date is between today and 7 days from now (inclusive)
        $expiringThisWeek = Client::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $sevenDaysFromNow)
            ->count();
        
        // New Signups This Month (based on start_date, not created_at)
        $newSignupsThisMonth = Client::whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->count();
        
        // Fetch active personal training plans for dropdowns
        $ptPlans = GymPlan::active()->personalTraining()->ordered()->get();
        
        return view('clients.index', compact(
            'clients',
            'totalClients',
            'activeClients',
            'expiringThisWeek',
            'newSignupsThisMonth',
            'ptPlans'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
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
                'sex' => 'nullable|in:Male,Female',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'avatar_url' => 'nullable|url',
                'plan_type' => 'required|exists:gym_plans,plan_key',
                'start_date' => 'required|date|after_or_equal:today',
                'due_date' => 'nullable|date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
                'confirm_similar' => 'nullable|boolean',
            ], [
                'start_date.after_or_equal' => 'Start date cannot be in the past. Please select today or a future date.',
            ]);

            // Server-side due_date calculation (never trust client)
            $plan = GymPlan::where('plan_key', $validated['plan_type'])->firstOrFail();
            $validated['due_date'] = Carbon::parse($validated['start_date'])
                ->addDays($plan->duration_days)
                ->format('Y-m-d');

            // Check for similar names unless user has confirmed
            if (!$request->input('confirm_similar')) {
                $similarCheck = $this->checkSimilarNames($validated['name']);
                if ($similarCheck) {
                    if ($similarCheck['type'] === 'exact') {
                        return response()->json([
                            'success' => false,
                            'message' => $similarCheck['message'],
                            'type' => 'exact',
                            'existing' => $similarCheck['existing']
                        ], 400);
                    } else {
                        // Similar name found - return 409 for confirmation
                        return response()->json([
                            'success' => false,
                            'requires_confirmation' => true,
                            'message' => $similarCheck['message'],
                            'type' => 'similar',
                            'existing' => $similarCheck['existing']
                        ], 409);
                    }
                }
            }

            // Remove confirm_similar from validated data
            unset($validated['confirm_similar']);

            // Find or create customer record
            $customer = Customer::firstOrCreate(
                ['contact' => $validated['contact']],
                [
                    'name' => $validated['name'],
                    'age' => $validated['age'] ?? null,
                    'sex' => $validated['sex'] ?? null,
                ]
            );
            
            // Set customer_id
            $validated['customer_id'] = $customer->id;

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
                    Log::warning('Failed to download avatar from URL: ' . $validated['avatar_url'] . ' - ' . $e->getMessage());
                }
            }

            // Remove avatar_url from validated data
            unset($validated['avatar_url']);

            $client = Client::create($validated);

            ActivityLog::log('created', 'client', "Added new PT client: {$validated['name']}", 'PT-' . $client->id, $validated['name'], $client, ['plan_type' => $validated['plan_type'], 'start_date' => $validated['start_date'], 'due_date' => $validated['due_date']]);

            // Return JSON response for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Client created successfully!']);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Client created successfully!');
                
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
                return response()->json(['success' => false, 'message' => 'An error occurred while creating the client.'], 500);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the client. Please try again.');
        }
    }

    /**
     * Calculate client status based on dates.
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
            $client = Client::findOrFail($id);
            return view('clients.show', compact('client'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while retrieving the client.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            return view('clients.edit', compact('client'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $client = Client::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'nullable|integer|min:1|max:120',
                'sex' => 'nullable|in:Male,Female',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
                'avatar_url' => 'nullable|url',
                'plan_type' => 'required|exists:gym_plans,plan_key',
                'start_date' => 'required|date',
                'due_date' => 'nullable|date',
                'contact' => ['required', 'string', 'max:255', 'regex:/^[+]?[0-9()\- ]+$/'],
            ]);

            // Server-side due_date calculation (never trust client)
            $plan = GymPlan::where('plan_key', $validated['plan_type'])->firstOrFail();
            $validated['due_date'] = Carbon::parse($validated['start_date'])
                ->addDays($plan->duration_days)
                ->format('Y-m-d');

            // Update or create customer record
            if ($client->customer_id) {
                // Update existing customer
                $customer = $client->customer;
                $customer->update([
                    'name' => $validated['name'],
                    'contact' => $validated['contact'],
                    'age' => $validated['age'] ?? $customer->age,
                    'sex' => $validated['sex'] ?? $customer->sex,
                ]);
            } else {
                // Create new customer if not linked
                $customer = Customer::firstOrCreate(
                    ['contact' => $validated['contact']],
                    [
                        'name' => $validated['name'],
                        'age' => $validated['age'] ?? null,
                        'sex' => $validated['sex'] ?? null,
                    ]
                );
                $validated['customer_id'] = $customer->id;
            }

            // Recalculate status automatically based on updated dates
            $validated['status'] = $this->calculateStatus($validated['start_date'], $validated['due_date']);

            // Handle avatar upload (file or URL)
            if ($request->hasFile('avatar')) {
                try {
                    // Delete old avatar
                    if ($client->avatar && Storage::disk('public')->exists($client->avatar)) {
                        Storage::disk('public')->delete($client->avatar);
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
                        if ($client->avatar && Storage::disk('public')->exists($client->avatar)) {
                            Storage::disk('public')->delete($client->avatar);
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

            // Remove avatar_url from validated data
            unset($validated['avatar_url']);

            $client->update($validated);

            ActivityLog::log('updated', 'client', "Updated PT client: {$client->name}", 'PT-' . $client->id, $client->name, $client, ['plan_type' => $validated['plan_type']]);

            return redirect()->route('clients.index')
                ->with('success', 'Client updated successfully!');
                
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the client. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $client = Client::lockForUpdate()->findOrFail($id);
                $clientName = $client->name;
                $clientId = $client->id;
            
            // Delete avatar if exists
            if ($client->avatar) {
                try {
                    if (Storage::disk('public')->exists($client->avatar)) {
                        Storage::disk('public')->delete($client->avatar);
                    }
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    Log::warning("Failed to delete avatar for client {$id}: " . $e->getMessage());
                }
            }
            
            $client->delete();

            ActivityLog::log('deleted', 'client', "Deleted PT client: {$clientName}", 'PT-' . $clientId, $clientName);

            // Return JSON response for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Client deleted successfully!'
                ]);
            }

            return redirect()->route('clients.index')
                ->with('success', 'Client deleted successfully!');
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found.'
                ], 404);
            }
            return redirect()->route('clients.index')
                ->with('error', 'Client not found.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the client. Please try again.'
                ], 500);
            }
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while deleting the client. Please try again.');
        }
    }

    /**
     * Delete multiple clients at once
     */
    public function bulkDelete(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'client_ids' => 'required|array',
                'client_ids.*' => 'exists:clients,id'
            ]);

            $clientIds = $request->client_ids;
            $deletedCount = 0;
            $errors = [];

            foreach ($clientIds as $id) {
                try {
                    $client = Client::findOrFail($id);
                    
                    // Delete avatar if exists
                    if ($client->avatar && Storage::exists('public/' . $client->avatar)) {
                        Storage::delete('public/' . $client->avatar);
                    }
                    
                    // Delete the client
                    $client->delete();
                    $deletedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete client ID: {$id}";
                    Log::error("Bulk delete error for client {$id}: " . $e->getMessage());
                }
            }

            // Prepare response message
            if ($deletedCount > 0) {
                ActivityLog::log('bulk_deleted', 'client', "Bulk deleted {$deletedCount} PT client(s)", null, null, null, ['count' => $deletedCount]);

                $message = "Successfully deleted {$deletedCount} client" . ($deletedCount > 1 ? 's' : '') . ".";
                
                if (count($errors) > 0) {
                    $message .= " However, " . count($errors) . " client" . (count($errors) > 1 ? 's' : '') . " could not be deleted.";
                }
                
                return redirect()->route('clients.index')
                    ->with('success', $message);
            } else {
                return redirect()->route('clients.index')
                    ->with('error', 'No clients were deleted. ' . implode(', ', $errors));
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Invalid request. Please select valid clients to delete.');
        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->route('clients.index')
                ->with('error', 'An error occurred while deleting clients. Please try again.');
        }
    }

    /**
     * Renew a client subscription
     */
    public function renew(Request $request, Client $client)
    {
        try {
            return DB::transaction(function () use ($request, $client) {
                // Lock the record to prevent race conditions
                $client = Client::lockForUpdate()->findOrFail($client->id);
                
                // Validate input
                $validated = $request->validate([
                    'start_date' => 'required|date|after_or_equal:' . now()->subDays(30)->format('Y-m-d'),
                    'due_date' => 'required|date|after:start_date'
                ]);
                
                // Parse dates
                $newStartDate = Carbon::parse($validated['start_date']);
                $newDueDate = Carbon::parse($validated['due_date']);
                
                // Validate that start_date is not more than 30 days in the past
                if ($newStartDate->lt(now()->subDays(30))) {
                    throw new \Exception('Start date cannot be more than 30 days in the past');
                }
                
                // Validate that due_date is after start_date
                if ($newDueDate->lte($newStartDate)) {
                    throw new \Exception('Due date must be after start date');
                }
                
                // Update the client (status is calculated automatically via accessor)
                $client->update([
                    'start_date' => $newStartDate,
                    'due_date' => $newDueDate
                ]);

                ActivityLog::log('renewed', 'client', "Renewed PT client subscription for {$client->name} — new due date: " . $newDueDate->format('M d, Y'), null, $client->name, $client, ['new_due_date' => $newDueDate->toDateString()]);
                
                // Return JSON response for AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Client subscription renewed successfully! New due date: ' . $newDueDate->format('M d, Y'),
                        'data' => [
                            'start_date' => $newStartDate->format('Y-m-d'),
                            'due_date' => $newDueDate->format('Y-m-d'),
                            'status' => $client->status
                        ]
                    ]);
                }
                
                // Fallback for regular form submission
                return redirect()->route('clients.index')
                    ->with('success', 'Client subscription renewed successfully! New due date: ' . $newDueDate->format('M d, Y'));
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
            Log::error('Error renewing client subscription: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while renewing the subscription'
                ], 500);
            }
            
            return back()->with('error', 'An error occurred while renewing the subscription');
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
            $totalClients = Client::count();
            
            // Active: due_date is more than 7 days in the future
            $activeClients = Client::whereDate('due_date', '>', $sevenDaysFromNow)->count();
            
            // Expiring This Week: due_date is between today and 7 days from now
            $expiringThisWeek = Client::whereDate('due_date', '>=', $today)
                ->whereDate('due_date', '<=', $sevenDaysFromNow)
                ->count();
            
            // New Signups This Month (based on start_date, not created_at)
            $newSignupsThisMonth = Client::whereMonth('start_date', $currentMonth)
                ->whereYear('start_date', $currentYear)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalClients,
                    'active' => $activeClients,
                    'expiring' => $expiringThisWeek,
                    'new_signups' => $newSignupsThisMonth
                ],
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching client KPIs: ' . $e->getMessage());
            
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

    /**
     * Check for similar or exact name matches
     * @param string $name The name to check
     * @param int|null $excludeId ID to exclude from check (for updates)
     * @return array|null Returns null if no match, or array with 'type' (exact/similar), 'message', and 'existing' name
     */
    private function checkSimilarNames($name, $excludeId = null)
    {
        $name = trim($name);
        
        // Check for exact match
        $exactMatch = Client::where('name', $name)
            ->when($excludeId, function($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->first();

        if ($exactMatch) {
            return [
                'type' => 'exact',
                'message' => 'A client with this exact name already exists.',
                'existing' => $exactMatch->name
            ];
        }

        // Check for similar first name
        $firstName = explode(' ', $name)[0];
        $similarMatch = Client::whereRaw("SUBSTRING_INDEX(name, ' ', 1) = ?", [$firstName])
            ->when($excludeId, function($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->first();

        if ($similarMatch) {
            return [
                'type' => 'similar',
                'message' => 'A client with a similar name already exists.',
                'existing' => $similarMatch->name
            ];
        }

        return null;
    }

    /**
     * Autocomplete API for customer suggestions
     * Used in client add modal to pre-fill data from existing customers
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
            ->get([
                'id',
                'name',
                'age',
                'sex',
                'contact',
                'avatar'
            ])
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'age' => $customer->age,
                    'sex' => $customer->sex,
                    'contact' => $customer->contact,
                    'avatar' => $customer->avatar,
                    'has_active_client' => $customer->hasActiveClient(),
                    'has_active_membership' => $customer->hasActiveMembership(),
                ];
            });

        return response()->json($customers);
    }
}
