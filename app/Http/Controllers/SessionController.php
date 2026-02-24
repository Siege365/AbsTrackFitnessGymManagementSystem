<?php

namespace App\Http\Controllers;

use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Get KPI data for real-time updates
     */
    public function getKPIs()
    {
        try {
            // Auto-expire overdue PT schedules
            PTSchedule::expireOverdueSchedules();

            $today = Carbon::today();
            
            $ptSessionsToday = PTSchedule::whereDate('scheduled_date', $today)->count();
            $upcomingPTSessions = PTSchedule::where('status', 'upcoming')
                ->whereDate('scheduled_date', '>=', $today)
                ->count();
            $ptCancellations = PTSchedule::where('status', 'cancelled')->count();
            $customersEnteredToday = Attendance::whereDate('date', $today)->count();
            
            // Calculate percentage change for customers entered (compared to yesterday)
            $yesterday = Carbon::yesterday();
            $customersYesterday = Attendance::whereDate('date', $yesterday)->count();
            $customerPercentChange = $customersYesterday > 0 
                ? round((($customersEnteredToday - $customersYesterday) / $customersYesterday) * 100, 1)
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'ptSessionsToday' => $ptSessionsToday,
                    'upcomingPTSessions' => $upcomingPTSessions,
                    'ptCancellations' => $ptCancellations,
                    'customersEnteredToday' => $customersEnteredToday,
                    'customerPercentChange' => $customerPercentChange
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data'
            ], 500);
        }
    }

    /**
     * Display the sessions page with KPIs and tables
     */
    public function index(Request $request)
    {
        try {
            // Auto-expire overdue PT schedules
            PTSchedule::expireOverdueSchedules();

            // KPI Data
            $today = Carbon::today();
            
            $ptSessionsToday = PTSchedule::whereDate('scheduled_date', $today)->count();
            $upcomingPTSessions = PTSchedule::where('status', 'upcoming')
                ->whereDate('scheduled_date', '>=', $today)
                ->count();
            $ptCancellations = PTSchedule::where('status', 'cancelled')->count();
            $customersEnteredToday = Attendance::whereDate('date', $today)->count();
            
            // Calculate percentage change for customers entered (compared to yesterday)
            $yesterday = Carbon::yesterday();
            $customersYesterday = Attendance::whereDate('date', $yesterday)->count();
            $customerPercentChange = $customersYesterday > 0 
                ? round((($customersEnteredToday - $customersYesterday) / $customersYesterday) * 100, 1)
                : 0;

            // Today's Customers (Attendance) with search and filter
            $attendanceQuery = Attendance::with('client')
                ->whereDate('date', $today);
            
            if ($request->filled('attendance_search')) {
                $search = $request->attendance_search;
                $attendanceQuery->whereHas('client', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('attendance_status')) {
                $attendanceQuery->where('status', $request->attendance_status);
            }
            
            // Customer type filter
            if ($request->filled('customer_type') && $request->customer_type !== 'all') {
                $type = $request->customer_type;
                
                if ($type === 'member') {
                    // Has membership only or both
                    $attendanceQuery->whereNotNull('membership_id');
                } elseif ($type === 'client') {
                    // Has client only (no membership)
                    $attendanceQuery->whereNotNull('client_id')
                                   ->whereNull('membership_id');
                } elseif ($type === 'walkin') {
                    // Walk-in (no client, no membership)
                    $attendanceQuery->whereNull('client_id')
                                   ->whereNull('membership_id');
                }
            }
            
            // Sort order
            $attendanceSort = $request->get('attendance_sort', 'recent');
            if ($attendanceSort === 'oldest') {
                $attendances = $attendanceQuery->orderBy('time_in', 'asc')->paginate(10, ['*'], 'attendance_page');
            } else {
                $attendances = $attendanceQuery->orderBy('time_in', 'desc')->paginate(10, ['*'], 'attendance_page');
            }

            // PT Schedules with search and filter
            $ptQuery = PTSchedule::with(['client', 'membership']);
            
            if ($request->filled('pt_search')) {
                $search = $request->pt_search;
                $ptQuery->where(function($q) use ($search) {
                    $q->whereHas('client', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('membership', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('trainer_name', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('pt_status')) {
                $ptQuery->where('status', $request->pt_status);
            }
            
            // Date filter - filter by specific date
            if ($request->filled('pt_date')) {
                $ptQuery->whereDate('scheduled_date', $request->pt_date);
            }
            
            // Sort order handling
            $ptSort = $request->get('pt_sort', 'default');
            
            if ($ptSort === 'recent') {
                // Recent first: newest dates first, then newest times
                $ptSchedules = $ptQuery
                    ->orderBy('scheduled_date', 'desc')
                    ->orderBy('scheduled_time', 'desc')
                    ->orderByRaw('
                        COALESCE(
                            (SELECT name FROM clients WHERE clients.id = pt_schedules.client_id),
                            (SELECT name FROM memberships WHERE memberships.id = pt_schedules.membership_id),
                            pt_schedules.customer_name
                        ) ASC
                    ')
                    ->paginate(10, ['*'], 'pt_page');
            } elseif ($ptSort === 'oldest') {
                // Oldest first: oldest dates first, then earliest times
                $ptSchedules = $ptQuery
                    ->orderBy('scheduled_date', 'asc')
                    ->orderBy('scheduled_time', 'asc')
                    ->orderByRaw('
                        COALESCE(
                            (SELECT name FROM clients WHERE clients.id = pt_schedules.client_id),
                            (SELECT name FROM memberships WHERE memberships.id = pt_schedules.membership_id),
                            pt_schedules.customer_name
                        ) ASC
                    ')
                    ->paginate(10, ['*'], 'pt_page');
            } else {
                // Default complex sorting: 
                // 1. Today first (sorted by time earliest to last)
                // 2. Future dates next (sorted by date asc, then time asc)
                // 3. Past dates last (sorted by date desc, then time desc)
                // 4. If same date and time, sort by customer name alphabetically
                $todayString = $today->toDateString();
                
                $ptSchedules = $ptQuery
                    ->selectRaw('pt_schedules.*, 
                        CASE 
                            WHEN scheduled_date = ? THEN 1 
                            WHEN scheduled_date > ? THEN 2 
                            ELSE 3 
                        END as date_priority', [$todayString, $todayString])
                    ->orderByRaw('date_priority ASC')
                    ->orderByRaw('
                        CASE 
                            WHEN date_priority = 1 THEN scheduled_time
                            WHEN date_priority = 2 THEN NULL
                            ELSE NULL
                        END ASC
                    ')
                    ->orderByRaw('
                        CASE 
                            WHEN date_priority = 2 THEN scheduled_date
                            ELSE NULL
                        END ASC
                    ')
                    ->orderByRaw('
                        CASE 
                            WHEN date_priority = 2 THEN scheduled_time
                            ELSE NULL
                        END ASC
                    ')
                    ->orderByRaw('
                        CASE 
                            WHEN date_priority = 3 THEN scheduled_date
                            ELSE NULL
                        END DESC
                    ')
                    ->orderByRaw('
                        CASE 
                            WHEN date_priority = 3 THEN scheduled_time
                            ELSE NULL
                        END DESC
                    ')
                    ->orderByRaw('
                        COALESCE(
                            (SELECT name FROM clients WHERE clients.id = pt_schedules.client_id),
                            (SELECT name FROM memberships WHERE memberships.id = pt_schedules.membership_id),
                            pt_schedules.customer_name
                        ) ASC
                    ')
                    ->paginate(10, ['*'], 'pt_page');
            }

            // Get all clients for dropdowns
            $clients = Client::orderBy('name')->get();

            // Get all memberships for attendance search
            $memberships = Membership::orderBy('name')->get();
            
            // Trainers list (could be from a trainers table in the future)
            $trainers = [
                'David Laid',
                'Eulo Icon Sexcion',
                'Justin Troy Rosalada',
                'Nicolas Deloso Torre III',
                'Ronnie Coleman',
            ];

            return view('Sessions.index', compact(
                'ptSessionsToday',
                'upcomingPTSessions',
                'ptCancellations',
                'customersEnteredToday',
                'customerPercentChange',
                'attendances',
                'ptSchedules',
                'clients',
                'memberships',
                'trainers'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load sessions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new PT schedule (supports clients, memberships, and walk-ins)
     */
    public function storePTSchedule(Request $request)
    {
        try {
            $customerSource = $request->input('customer_source', 'walkin');
            $isWalkIn = $customerSource === 'walkin';

            $rules = [
                'trainer_name' => 'required|string|max:255',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required',
                'payment_type' => 'required|string|in:Cash,Gcash,Card,Bank Transfer',
                'notes' => 'nullable|string|max:500',
            ];

            $messages = [
                'trainer_name.required' => 'Please select or enter a trainer.',
                'scheduled_date.required' => 'Please select a date.',
                'scheduled_date.after_or_equal' => 'Date cannot be in the past.',
                'scheduled_time.required' => 'Please select a time.',
                'payment_type.required' => 'Please select a payment type.',
            ];

            if ($isWalkIn) {
                $rules['customer_name'] = 'required|string|max:255';
                $rules['customer_age'] = 'nullable|integer|min:1|max:120';
                $rules['customer_sex'] = 'nullable|string|in:Male,Female';
                $rules['customer_contact'] = 'nullable|string|max:255';
                $messages['customer_name.required'] = 'Please enter the customer name.';
            } elseif ($customerSource === 'membership') {
                $rules['membership_id'] = 'required|exists:memberships,id';
                $messages['membership_id.required'] = 'Please select a membership.';
                $messages['membership_id.exists'] = 'Selected membership does not exist.';
            } else {
                $rules['client_id'] = 'required|exists:clients,id';
                $messages['client_id.required'] = 'Please select a client.';
                $messages['client_id.exists'] = 'Selected client does not exist.';
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'trainer_name' => $request->trainer_name,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'payment_type' => $request->payment_type,
                'status' => 'upcoming',
                'notes' => $request->notes,
                'customer_source' => $customerSource,
            ];

            if ($isWalkIn) {
                $data['customer_name'] = $request->customer_name;
                $data['customer_age'] = $request->customer_age;
                $data['customer_sex'] = $request->customer_sex;
                $data['customer_contact'] = $request->customer_contact;
            } elseif ($customerSource === 'membership') {
                $data['membership_id'] = $request->membership_id;
            } else {
                $data['client_id'] = $request->client_id;
            }

            $ptSchedule = PTSchedule::create($data);
            $ptSchedule->load(['client', 'membership']);

            return response()->json([
                'success' => true,
                'message' => 'PT Schedule created successfully!',
                'data' => $ptSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create PT schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing PT schedule
     */
    public function updatePTSchedule(Request $request, $id)
    {
        try {
            $ptSchedule = PTSchedule::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'trainer_name' => 'required|string|max:255',
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
                'payment_type' => 'required|string|in:Cash,Gcash,Card,Bank Transfer',
                'status' => 'sometimes|in:upcoming,in_progress,done,cancelled,expired',
                'notes' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ptSchedule->update($request->only([
                'trainer_name',
                'scheduled_date',
                'scheduled_time',
                'payment_type',
                'status',
                'notes'
            ]));

            $ptSchedule->load('client');

            return response()->json([
                'success' => true,
                'message' => 'PT Schedule updated successfully!',
                'data' => $ptSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update PT schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a PT schedule
     */
    public function destroyPTSchedule($id)
    {
        try {
            $ptSchedule = PTSchedule::findOrFail($id);
            $ptSchedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'PT Schedule deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete PT schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark PT schedule as done/cancelled
     */
    public function updatePTStatus(Request $request, $id)
    {
        try {
            $ptSchedule = PTSchedule::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:upcoming,in_progress,done,cancelled,expired',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ptSchedule->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated to ' . ucfirst($request->status) . '!',
                'data' => $ptSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Book next session for a client
     */
    public function bookNextSession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required',
            ], [
                'scheduled_date.after_or_equal' => 'Date cannot be in the past.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the last session to copy trainer and payment info
            $lastSession = PTSchedule::where('client_id', $request->client_id)
                ->orderBy('scheduled_date', 'desc')
                ->first();

            $ptSchedule = PTSchedule::create([
                'client_id' => $request->client_id,
                'trainer_name' => $lastSession ? $lastSession->trainer_name : 'TBA',
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'payment_type' => $lastSession ? $lastSession->payment_type : 'Cash',
                'status' => 'upcoming',
            ]);

            $ptSchedule->load('client');

            return response()->json([
                'success' => true,
                'message' => 'Next session booked successfully!',
                'data' => $ptSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to book session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store attendance (customer check-in).
     * Supports existing clients, membership members, and walk-in customers.
     * Automatically detects customer type and updates PT schedule status.
     */
    public function storeAttendance(Request $request)
    {
        try {
            $isWalkIn = !$request->filled('client_id') && !$request->filled('membership_id');
            $hasClientId = $request->filled('client_id');
            $hasMembershipId = $request->filled('membership_id');

            $rules = [
                'date' => 'required|date',
                'time_in' => 'required',
            ];

            if ($isWalkIn) {
                $rules['customer_name'] = 'required|string|max:255';
                $rules['customer_contact'] = 'nullable|string|max:255';
            } elseif ($hasClientId) {
                $rules['client_id'] = 'required|exists:clients,id';
            } elseif ($hasMembershipId) {
                $rules['membership_id'] = 'required|exists:memberships,id';
            }

            $validator = Validator::make($request->all(), $rules, [
                'client_id.exists' => 'Selected customer does not exist.',
                'customer_name.required' => 'Please enter the customer name.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determine customer type and build attendance data
            $data = [
                'date' => $request->date,
                'time_in' => $request->time_in,
            ];

            if ($hasClientId) {
                $client = Client::findOrFail($request->client_id);

                // Check if already checked in today
                $existing = Attendance::where('client_id', $client->id)
                    ->whereDate('date', $request->date)
                    ->first();
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This customer has already checked in today.'
                    ], 422);
                }

                $data['client_id'] = $client->id;
                $status = strtolower($client->status ?? 'active');
                $data['status'] = $status === 'due soon' ? 'due_soon' : $status;

                // AUTO-DETECT: If customer also has membership, include both IDs
                if ($client->customer_id) {
                    $membership = Membership::where('customer_id', $client->customer_id)->first();
                    if ($membership) {
                        $data['membership_id'] = $membership->id;
                    }
                }

                // Detect customer type from membership record
                $data['customer_type'] = $this->detectCustomerType($client->name, $client->contact);

            } elseif ($hasMembershipId) {
                $membership = Membership::findOrFail($request->membership_id);

                // Check duplicate by membership_id + date
                $existing = Attendance::where('membership_id', $membership->id)
                    ->whereDate('date', $request->date)
                    ->first();
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This customer has already checked in today.'
                    ], 422);
                }

                $data['membership_id'] = $membership->id;
                $data['customer_name'] = $membership->name;
                $data['customer_contact'] = $membership->contact;

                // Status from membership
                $status = strtolower($membership->status ?? 'active');
                $data['status'] = $status === 'due soon' ? 'due_soon' : $status;

                // AUTO-DETECT: If customer also has client record, include both IDs
                if ($membership->customer_id) {
                    $client = Client::where('customer_id', $membership->customer_id)->first();
                    if ($client) {
                        $data['client_id'] = $client->id;
                    }
                }

                // Detect customer type from plan_type
                $data['customer_type'] = $this->mapPlanToCustomerType($membership->plan_type);

            } else {
                // Walk-in customer
                $customerName = $request->customer_name;

                // Check duplicate by name + date
                $existing = Attendance::where('customer_name', $customerName)
                    ->whereDate('date', $request->date)
                    ->first();
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This customer has already checked in today.'
                    ], 422);
                }

                $data['customer_name'] = $customerName;
                $data['customer_contact'] = $request->customer_contact;
                $data['status'] = 'active';

                // Try to detect type from existing records
                $data['customer_type'] = $this->detectCustomerType($customerName, $request->customer_contact);
            }

            $attendance = Attendance::create($data);
            $attendance->load(['client', 'membership']);

            // Auto-update PT schedule status to 'in_progress' if customer has session today
            $this->autoUpdatePTStatus($attendance);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully!',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect customer type by cross-referencing memberships table.
     */
    private function detectCustomerType(?string $name, ?string $contact): string
    {
        if (!$name) {
            return 'walk-in';
        }

        // Check memberships table for an active membership
        $membership = Membership::where('name', $name)
            ->when($contact, fn($q) => $q->orWhere('contact', $contact))
            ->orderByDesc('due_date')
            ->first();

        if ($membership && strtolower($membership->status) !== 'expired') {
            return $this->mapPlanToCustomerType($membership->plan_type);
        }

        return 'walk-in';
    }

    /**
     * Map a plan_type (plan_key) to a customer type string.
     */
    private function mapPlanToCustomerType(?string $planType): string
    {
        if (!$planType) {
            return 'walk-in';
        }

        $planLower = strtolower($planType);

        return match(true) {
            str_contains($planLower, 'annual') => 'annual',
            str_contains($planLower, 'half') => 'half-yearly',
            str_contains($planLower, 'threemonth'), str_contains($planLower, 'quarter') => 'quarterly',
            str_contains($planLower, 'regular'), str_contains($planLower, 'student'),
            str_contains($planLower, 'gymbuddy') => 'monthly',
            str_contains($planLower, 'session') => 'walk-in',
            default => 'walk-in',
        };
    }

    /**
     * Auto-update PT schedule to 'in_progress' when customer checks in.
     */
    private function autoUpdatePTStatus(Attendance $attendance): void
    {
        $today = Carbon::today();
        $customerName = $attendance->client?->name ?? $attendance->customer_name;

        if (!$customerName) {
            return;
        }

        // Find matching PT schedule for today with 'upcoming' status
        $ptSchedule = PTSchedule::where('status', 'upcoming')
            ->whereDate('scheduled_date', $today)
            ->where(function ($query) use ($attendance, $customerName) {
                if ($attendance->client_id) {
                    $query->where('client_id', $attendance->client_id);
                } else {
                    $query->where('customer_name', $customerName);
                }
            })
            ->first();

        if ($ptSchedule) {
            $ptSchedule->update(['status' => 'in_progress']);
        }
    }

    /**
     * Update attendance (check-out)
     */
    public function updateAttendance(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'time_out' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attendance->update($request->only(['time_out']));

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully!',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attendance record
     */
    public function destroyAttendance($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get PT schedule details for viewing/editing
     */
    public function getPTSchedule($id)
    {
        try {
            $ptSchedule = PTSchedule::with(['client', 'membership'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ptSchedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PT Schedule not found'
            ], 404);
        }
    }

    /**
     * Get attendance details
     */
    public function getAttendance($id)
    {
        try {
            $attendance = Attendance::with(['client', 'membership'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }
    }

    /**
     * Bulk delete attendances
     */
    public function bulkDeleteAttendance(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No records selected'
                ], 400);
            }

            $count = Attendance::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} attendance record(s) deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting attendance records: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete PT schedules
     */
    public function bulkDeletePT(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No records selected'
                ], 400);
            }

            $count = PTSchedule::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} PT schedule(s) deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting PT schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers across clients and memberships tables for auto-suggest.
     * Returns deduplicated results by name.
     */
    public function searchCustomers(Request $request)
    {
        try {
            // Accept both 'q' and 'query' parameters for compatibility
            $search = $request->input('q') ?? $request->input('query', '');
            
            // Allow empty search to return recent customers (for autocomplete on focus)
            $searchPattern = strlen($search) < 1 ? '%' : "%{$search}%";

            // Search memberships (prioritize for gym check-ins)
            $memberships = Membership::where('name', 'like', $searchPattern)
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(function($m) {
                    // Check if this customer also has a client (PT) subscription
                    $client = null;
                    if ($m->customer_id) {
                        $client = Client::where('customer_id', $m->customer_id)->first();
                    }
                    
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'source' => 'membership',
                        'age' => $m->age,
                        'sex' => $m->sex,
                        'contact' => $m->contact,
                        'plan_type' => $m->plan_type,
                        'formatted_plan_type' => $m->formatted_plan_type,
                        'avatar' => $m->avatar,
                        'status' => $m->status,
                        // Include client subscription info for PT modal
                        'client_plan_type' => $client?->plan_type,
                        'client_formatted_plan_type' => $client?->formatted_plan_type,
                    ];
                });

            // Search clients
            $clients = Client::where('name', 'like', $searchPattern)
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'source' => 'client',
                    'age' => $c->age,
                    'sex' => $c->sex,
                    'contact' => $c->contact,
                    'plan_type' => $c->plan_type,
                    'formatted_plan_type' => $c->formatted_plan_type,
                    'avatar' => $c->avatar,
                    'status' => $c->status,
                ]);

            // Merge and deduplicate by name (prefer membership over client for gym check-ins)
            $combined = $memberships->concat($clients)
                ->unique('name')
                ->sortBy('name')
                ->values()
                ->take(15);

            return response()->json($combined);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Search trainers for autocomplete in PT schedule
     */
    public function searchTrainers(Request $request)
    {
        try {
            // Accept both 'q' and 'query' parameters for compatibility
            $search = $request->input('q') ?? $request->input('query', '');

            // Static trainers list (can be moved to database in future)
            $trainers = [
                'Ronnie Coleman',
                'Justin Troy Rosalada',
                'Eulo Icon Sexcion',
                'David Laid',
                'Nicolas Deloso Torre III',
            ];

            // Filter trainers based on search query
            $filtered = collect($trainers)
                ->filter(function ($trainer) use ($search) {
                    return empty($search) || stripos($trainer, $search) !== false;
                })
                ->map(function ($trainer, $index) {
                    return [
                        'id' => $index + 1,
                        'name' => $trainer,
                    ];
                })
                ->values();

            return response()->json($filtered);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
}
