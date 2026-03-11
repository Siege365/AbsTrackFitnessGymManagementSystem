<?php

namespace App\Http\Controllers;

use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\GymPlan;
use App\Models\Membership;
use App\Models\Trainer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\NotificationService;

class SessionController extends Controller
{
    /**
     * Get KPI data for real-time updates
     */
    public function getKPIs(Request $request)
    {
        try {
            $today = Carbon::today();
            $type = $request->get('type', 'pt');

            if ($type === 'attendance') {
                $customersEnteredToday = Attendance::whereDate('date', $today)->count();
                $membersToday = Attendance::whereDate('date', $today)->whereNotNull('membership_id')->count();
                $walkInsToday = Attendance::whereDate('date', $today)->whereNull('client_id')->whereNull('membership_id')->count();
                $totalThisMonth = Attendance::whereYear('date', $today->year)->whereMonth('date', $today->month)->count();

                $yesterday = Carbon::yesterday();
                $customersYesterday = Attendance::whereDate('date', $yesterday)->count();
                $customerPercentChange = $customersYesterday > 0 
                    ? round((($customersEnteredToday - $customersYesterday) / $customersYesterday) * 100, 1)
                    : 0;

                return response()->json([
                    'success' => true,
                    'data' => [
                        'customersEnteredToday' => $customersEnteredToday,
                        'membersToday' => $membersToday,
                        'walkInsToday' => $walkInsToday,
                        'totalThisMonth' => $totalThisMonth,
                        'customerPercentChange' => $customerPercentChange
                    ]
                ]);
            }

            // PT type (default)
            PTSchedule::expireOverdueSchedules();
            PTSchedule::completeOverdueInProgressSessions();
            
            $ptSessionsToday = PTSchedule::whereDate('scheduled_date', $today)->count();
            $upcomingPTSessions = PTSchedule::where('status', 'upcoming')
                ->whereDate('scheduled_date', '>=', $today)
                ->count();
            $ptCancellations = PTSchedule::where('status', 'cancelled')->count();
            $completedSessions = PTSchedule::where('status', 'done')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'ptSessionsToday' => $ptSessionsToday,
                    'upcomingPTSessions' => $upcomingPTSessions,
                    'ptCancellations' => $ptCancellations,
                    'completedSessions' => $completedSessions
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data'
            ], 500);
        }
    }

    /**
     * Display the PT Sessions page
     */
    public function ptIndex(Request $request)
    {
        try {
            // Auto-expire overdue PT schedules
            PTSchedule::expireOverdueSchedules();
            // Auto-complete overdue in-progress sessions
            PTSchedule::completeOverdueInProgressSessions();

            $today = Carbon::today();

            // PT-specific KPIs
            $ptSessionsToday = PTSchedule::whereDate('scheduled_date', $today)->count();
            $upcomingPTSessions = PTSchedule::where('status', 'upcoming')
                ->whereDate('scheduled_date', '>=', $today)
                ->count();
            $ptCancellations = PTSchedule::where('status', 'cancelled')->count();
            $completedSessions = PTSchedule::where('status', 'done')->count();

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
                $ptSchedules = $ptQuery
                    ->orderBy('created_at', 'desc')
                    ->paginate(10, ['*'], 'pt_page');
            }

            // Get all clients for dropdowns
            $clients = Client::orderBy('name')->get();
            
            // Trainers list - pulled from database
            $trainers = Trainer::orderBy('full_name')->pluck('full_name')->toArray();

            return view('Sessions.pt-sessions', compact(
                'ptSessionsToday',
                'upcomingPTSessions',
                'ptCancellations',
                'completedSessions',
                'ptSchedules',
                'clients',
                'trainers'
            ));
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to load training sessions: ' . $e->getMessage());
        }
    }

    /**
     * Display the Customer Attendance page
     */
    public function attendanceIndex(Request $request)
    {
        try {
            $today = Carbon::today();

            // Attendance-specific KPIs
            $customersEnteredToday = Attendance::whereDate('date', $today)->count();
            
            $membersToday = Attendance::whereDate('date', $today)
                ->whereNotNull('membership_id')
                ->count();
            
            $walkInsToday = Attendance::whereDate('date', $today)
                ->whereNull('client_id')
                ->whereNull('membership_id')
                ->count();

            $totalThisMonth = Attendance::whereYear('date', $today->year)
                ->whereMonth('date', $today->month)
                ->count();

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
                    $attendanceQuery->whereNotNull('membership_id');
                } elseif ($type === 'client') {
                    $attendanceQuery->whereNotNull('client_id')
                                   ->whereNull('membership_id');
                } elseif ($type === 'walkin') {
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

            // Get all clients and memberships for search
            $clients = Client::orderBy('name')->get();
            $memberships = Membership::orderBy('name')->get();

            return view('Sessions.attendance', compact(
                'customersEnteredToday',
                'membersToday',
                'walkInsToday',
                'totalThisMonth',
                'customerPercentChange',
                'attendances',
                'clients',
                'memberships'
            ));
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to load customer attendance: ' . $e->getMessage());
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
                'plan_key' => 'required|string|exists:gym_plans,plan_key',
                'notes' => 'nullable|string|max:500',
            ];

            $messages = [
                'trainer_name.required' => 'Please select or enter a trainer.',
                'scheduled_date.required' => 'Please select a date.',
                'scheduled_date.after_or_equal' => 'Date cannot be in the past.',
                'scheduled_time.required' => 'Please select a time.',
                'payment_type.required' => 'Please select a payment type.',
                'plan_key.required' => 'Please select a PT plan.',
                'plan_key.exists' => 'Selected PT plan is not available.',
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

            $plan = GymPlan::active()
                ->personalTraining()
                ->where('plan_key', $request->plan_key)
                ->first();

            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected PT plan is not available.'
                ], 422);
            }

            $data = [
                'trainer_name' => $request->trainer_name,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'payment_type' => $request->payment_type,
                'receipt_number' => $this->generatePTReceiptNumber(),
                'plan_key' => $plan->plan_key,
                'plan_name' => $plan->plan_name,
                'plan_duration_days' => $plan->duration_days,
                'amount' => $plan->price,
                'paid_amount' => $plan->price,
                'return_amount' => 0,
                'processed_by' => Auth::user()->name ?? 'Admin',
                'is_refunded' => false,
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

            // Send notification for new PT session
            $customerName = $data['customer_name'] ?? $ptSchedule->client->name ?? $ptSchedule->membership->name ?? 'Walk-in';
            NotificationService::newPTSession(
                $customerName,
                Carbon::parse($data['scheduled_date'])->format('M d, Y'),
                $data['scheduled_time']
            );
            NotificationService::paymentReceived($customerName, $plan->price, 'pt');

            return response()->json([
                'success' => true,
                'message' => 'PT payment processed and session booked successfully!',
                'data' => $ptSchedule
            ]);
        } catch (\Throwable $e) {
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
            return DB::transaction(function () use ($request, $id) {
                $ptSchedule = PTSchedule::lockForUpdate()->findOrFail($id);

                // Concurrency check
                if ($request->filled('last_updated_at')) {
                    $clientTimestamp = (int) $request->input('last_updated_at');
                    $serverTimestamp = $ptSchedule->updated_at->timestamp;
                    if ($clientTimestamp !== $serverTimestamp) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This record was modified by someone else while you were editing. Please close and reopen to get the latest data.'
                        ], 409);
                    }
                }

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

                $ptSchedule->load(['client', 'membership']);

                $displayName = $ptSchedule->display_name;
                ActivityLog::log('updated', 'pt_session', "Updated PT session for {$displayName}", 'PT-' . $ptSchedule->id, $displayName, $ptSchedule, ['trainer' => $ptSchedule->trainer_name, 'date' => $ptSchedule->scheduled_date]);

                return response()->json([
                    'success' => true,
                    'message' => 'PT Schedule updated successfully!',
                    'data' => $ptSchedule
                ]);
            });
        } catch (\Throwable $e) {
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
            $ptSchedule = PTSchedule::with(['client', 'membership'])->findOrFail($id);
            $displayName = $ptSchedule->display_name;
            $ptId = $ptSchedule->id;
            $ptSchedule->delete();

            ActivityLog::log('deleted', 'pt_session', "Deleted PT session for {$displayName}", 'PT-' . $ptId, $displayName);

            return response()->json([
                'success' => true,
                'message' => 'PT Schedule deleted successfully!'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'PT Schedule not found. It may have already been deleted.'
            ], 404);
        } catch (\Throwable $e) {
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
            return DB::transaction(function () use ($request, $id) {
                $ptSchedule = PTSchedule::lockForUpdate()->findOrFail($id);

                // Concurrency check
                if ($request->filled('last_updated_at')) {
                    $clientTimestamp = (int) $request->input('last_updated_at');
                    $serverTimestamp = $ptSchedule->updated_at->timestamp;
                    if ($clientTimestamp !== $serverTimestamp) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This session was already updated by someone else. Please refresh the page to see the latest status.'
                        ], 409);
                    }
                }

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
                $ptSchedule->load(['client', 'membership']);

                $displayName = $ptSchedule->display_name;
                ActivityLog::log('updated', 'pt_session', "Updated PT session status to '{$request->status}' for {$displayName}", 'PT-' . $ptSchedule->id, $displayName, $ptSchedule, ['status' => $request->status]);

                return response()->json([
                    'success' => true,
                    'message' => 'Status updated to ' . ucfirst($request->status) . '!',
                    'data' => $ptSchedule
                ]);
            });
        } catch (\Throwable $e) {
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
                'source_session_id' => 'required|exists:pt_schedules,id',
                'trainer_name'      => 'required|string|max:255',
                'scheduled_date'    => 'required|date|after_or_equal:today',
                'scheduled_time'    => 'required',
                'payment_type'      => 'required|in:Cash,Gcash',
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

            // Copy all customer info from the source session
            $sourceSession = PTSchedule::findOrFail($request->source_session_id);

            $ptSchedule = PTSchedule::create([
                'client_id'       => $sourceSession->client_id,
                'membership_id'   => $sourceSession->membership_id,
                'customer_name'   => $sourceSession->customer_name,
                'customer_age'    => $sourceSession->customer_age,
                'customer_sex'    => $sourceSession->customer_sex,
                'customer_contact'=> $sourceSession->customer_contact,
                'customer_source' => $sourceSession->customer_source,
                'trainer_name'    => $request->trainer_name,
                'scheduled_date'  => $request->scheduled_date,
                'scheduled_time'  => $request->scheduled_time,
                'payment_type'    => $request->payment_type,
                'status'          => 'upcoming',
            ]);

            $ptSchedule->load(['client', 'membership']);

            $displayName = $ptSchedule->display_name;
            ActivityLog::log('created', 'pt_session', "Booked next PT session for {$displayName}", 'PT-' . $ptSchedule->id, $displayName, $ptSchedule, ['date' => $ptSchedule->scheduled_date, 'time' => $ptSchedule->scheduled_time]);

            return response()->json([
                'success' => true,
                'message' => 'Next session booked successfully!',
                'data' => $ptSchedule
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to book session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate the next PT receipt number.
     */
    private function generatePTReceiptNumber(): string
    {
        $lastSchedule = PTSchedule::latest('id')->first();
        $nextId = $lastSchedule ? $lastSchedule->id + 1 : 1;

        return 'PT-' . date('Ymd') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
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

            $displayName = $attendance->display_name;
            $referenceId = $attendance->membership_id ? 'MEM-' . $attendance->membership_id : ($attendance->client_id ? 'PT-' . $attendance->client_id : null);
            ActivityLog::log('created', 'attendance', "Recorded attendance for {$displayName}", $referenceId, $displayName, $attendance, ['date' => $attendance->date, 'time_in' => $attendance->time_in]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully!',
                'data' => $attendance
            ]);
        } catch (\Throwable $e) {
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

            $displayName = $attendance->display_name;
            $referenceId = $attendance->membership_id ? 'MEM-' . $attendance->membership_id : ($attendance->client_id ? 'PT-' . $attendance->client_id : null);
            ActivityLog::log('updated', 'attendance', "Updated attendance (check-out) for {$displayName}", $referenceId, $displayName, $attendance);

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully!',
                'data' => $attendance
            ]);
        } catch (\Throwable $e) {
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
            $attendance = Attendance::with(['client', 'membership'])->findOrFail($id);
            $customerName = $attendance->display_name;
            $referenceId = $attendance->membership_id ? 'MEM-' . $attendance->membership_id : ($attendance->client_id ? 'PT-' . $attendance->client_id : null);
            $attendance->delete();

            ActivityLog::log('deleted', 'attendance', "Deleted attendance record for {$customerName}", $referenceId, $customerName);

            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully!'
            ]);
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
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

            ActivityLog::log('bulk_deleted', 'attendance', "Bulk deleted {$count} attendance record(s)", null, null, null, ['count' => $count]);

            return response()->json([
                'success' => true,
                'message' => "{$count} attendance record(s) deleted successfully"
            ]);
        } catch (\Throwable $e) {
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

            ActivityLog::log('bulk_deleted', 'pt_session', "Bulk deleted {$count} PT schedule(s)", null, null, null, ['count' => $count]);

            return response()->json([
                'success' => true,
                'message' => "{$count} PT schedule(s) deleted successfully"
            ]);
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            return response()->json([], 500);
        }
    }

    /**
     * Search trainers for autocomplete in PT schedule
     */
    public function searchTrainers(Request $request)
    {
        try {
            $search = $request->input('q') ?? $request->input('query', '');

            $query = Trainer::query();

            if (!empty($search)) {
                $query->where('full_name', 'LIKE', "%{$search}%");
            }

            $trainers = $query->orderBy('full_name')
                ->limit(15)
                ->get()
                ->map(function ($trainer) {
                    return [
                        'id' => $trainer->id,
                        'name' => $trainer->full_name,
                    ];
                });

            return response()->json($trainers);
        } catch (\Throwable $e) {
            return response()->json([], 500);
        }
    }
}
