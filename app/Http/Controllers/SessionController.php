<?php

namespace App\Http\Controllers;

use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\Client;
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
            
            $attendances = $attendanceQuery->orderBy('time_in', 'desc')->paginate(10, ['*'], 'attendance_page');

            // PT Schedules with search and filter
            $ptQuery = PTSchedule::with('client');
            
            if ($request->filled('pt_search')) {
                $search = $request->pt_search;
                $ptQuery->where(function($q) use ($search) {
                    $q->whereHas('client', function($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })->orWhere('trainer_name', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('pt_status')) {
                $ptQuery->where('status', $request->pt_status);
            }
            
            $ptSchedules = $ptQuery->orderBy('scheduled_date', 'desc')
                ->orderBy('scheduled_time', 'desc')
                ->paginate(10, ['*'], 'pt_page');

            // Get all clients for dropdowns
            $clients = Client::orderBy('name')->get();
            
            // Trainers list (could be from a trainers table in the future)
            $trainers = [
                'Ronnie Coleman',
                'Justin Troy Rosalada',
                'Eulo Icon Sexcion',
                'David Laid',
                'Nicolas Deloso Torre III',
            ];

            return view('Sessions.Session', compact(
                'ptSessionsToday',
                'upcomingPTSessions',
                'ptCancellations',
                'customersEnteredToday',
                'customerPercentChange',
                'attendances',
                'ptSchedules',
                'clients',
                'trainers'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load sessions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new PT schedule
     */
    public function storePTSchedule(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'trainer_name' => 'required|string|max:255',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'scheduled_time' => 'required',
                'payment_type' => 'required|string|in:Cash,Gcash,Card,Bank Transfer',
                'notes' => 'nullable|string|max:500',
            ], [
                'client_id.required' => 'Please select a client.',
                'client_id.exists' => 'Selected client does not exist.',
                'trainer_name.required' => 'Please select a trainer.',
                'scheduled_date.required' => 'Please select a date.',
                'scheduled_date.after_or_equal' => 'Date cannot be in the past.',
                'scheduled_time.required' => 'Please select a time.',
                'payment_type.required' => 'Please select a payment type.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ptSchedule = PTSchedule::create([
                'client_id' => $request->client_id,
                'trainer_name' => $request->trainer_name,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'payment_type' => $request->payment_type,
                'status' => 'upcoming',
                'notes' => $request->notes,
            ]);

            $ptSchedule->load('client');

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
                'status' => 'sometimes|in:upcoming,done,cancelled',
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
                'status' => 'required|in:upcoming,done,cancelled',
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
     * Store attendance (customer check-in)
     */
    public function storeAttendance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'date' => 'required|date',
                'time_in' => 'required',
            ], [
                'client_id.required' => 'Please select a client.',
                'client_id.exists' => 'Selected client does not exist.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if client already checked in today
            $existingAttendance = Attendance::where('client_id', $request->client_id)
                ->whereDate('date', $request->date)
                ->first();

            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'This client has already checked in today.'
                ], 422);
            }

            // Get client to determine status based on membership
            $client = Client::findOrFail($request->client_id);
            $status = $client->status ?? 'active';
            
            // Convert status to lowercase for consistency
            $status = strtolower($status);
            if ($status === 'due soon') {
                $status = 'due_soon';
            }

            $attendance = Attendance::create([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'time_in' => $request->time_in,
                'status' => $status,
            ]);

            $attendance->load('client');

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
            $ptSchedule = PTSchedule::with('client')->findOrFail($id);

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
            $attendance = Attendance::with('client')->findOrFail($id);

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
}
