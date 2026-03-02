<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display activity logs page with filtering & search.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()->latest();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                ->orWhere('customer_name', 'LIKE', "%{$search}%")
                ->orWhere('reference_number', 'LIKE', "%{$search}%")
                ->orWhere('user_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by module
        if ($module = $request->input('module')) {
            if ($module !== 'all') {
                $query->where('module', $module);
            }
        }

        // Filter by action
        if ($action = $request->input('action')) {
            if ($action !== 'all') {
                $query->where('action', $action);
            }
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            if ($userId !== 'all') {
                $query->where('user_id', $userId);
            }
        }

        // Filter by date range
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs = $query->paginate(20)->appends($request->query());

        // Stats
        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::whereDate('created_at', now()->toDateString())->count();
        $uniqueUsers = ActivityLog::distinct('user_id')->count('user_id');

        // Available users for filter dropdown
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('UserAndAdmin.CashierActivity', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'uniqueUsers',
            'users'
        ));
    }

    /**
     * Bulk delete activity logs.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        // Handle JSON string from form hidden input
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }

        if (empty($ids)) {
            return back()->with('error', 'No logs selected.');
        }

        ActivityLog::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' log(s) deleted successfully.');
    }

    /**
     * Clear all activity logs.
     */
    public function clearAll()
    {
        ActivityLog::truncate();

        return back()->with('success', 'All activity logs cleared.');
    }
}
