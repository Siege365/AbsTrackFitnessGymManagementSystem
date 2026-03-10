<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Membership;
use App\Models\Attendance;
use App\Models\PTSchedule;
use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\InventorySupply;
use App\Models\InventoryTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekFromNow = Carbon::today()->addDays(7);
        $monthStart = Carbon::now()->startOfMonth();

        // ─── Top-Level KPIs ───
        $totalActiveMembers = Membership::where('due_date', '>', $today)->count()
                            + Client::where('due_date', '>', $today)->count();

        $todayRevenue = Payment::whereDate('created_at', $today)
                            ->where('is_refunded', false)
                            ->sum('total_amount')
                      + MembershipPayment::whereDate('created_at', $today)
                            ->whereNull('refunded_at')
                            ->sum('amount');

        $todayAttendance = Attendance::whereDate('date', $today)->count();

        $expiringCount = Membership::whereBetween('due_date', [$today, $weekFromNow])->count()
                       + Client::whereBetween('due_date', [$today, $weekFromNow])->count();

        $lowStockCount = InventorySupply::whereColumn('stock_qty', '<=', 'low_stock_threshold')
                            ->where('stock_qty', '>', 0)->count();

        $attentionItems = $expiringCount + $lowStockCount;

        // ─── Clients Summary ───
        $totalClients = Client::count();
        $activeClients = Client::where('due_date', '>', $weekFromNow)->count();
        $expiringClients = Client::whereBetween('due_date', [$today, $weekFromNow])->count();
        $newClientsThisMonth = Client::whereYear('start_date', $today->year)
                                ->whereMonth('start_date', $today->month)->count();

        // ─── Memberships Summary ───
        $totalMemberships = Membership::count();
        $activeMemberships = Membership::where('due_date', '>', $weekFromNow)->count();
        $expiringMemberships = Membership::whereBetween('due_date', [$today, $weekFromNow])->count();
        $newMembershipsThisMonth = Membership::whereYear('start_date', $today->year)
                                    ->whereMonth('start_date', $today->month)->count();

        // ─── Sessions Summary ───
        $ptSessionsToday = PTSchedule::whereDate('scheduled_date', $today)->count();
        $upcomingPT = PTSchedule::where('status', 'upcoming')
                        ->where('scheduled_date', '>=', $today)->count();
        $completedPTToday = PTSchedule::where('status', 'done')
                            ->whereDate('scheduled_date', $today)->count();
        $walkInsToday = Attendance::whereDate('date', $today)
                        ->whereNull('client_id')
                        ->whereNull('membership_id')->count();

        // ─── Payments Summary ───
        $monthlyRevenue = Payment::where('created_at', '>=', $monthStart)
                            ->where('is_refunded', false)
                            ->sum('total_amount')
                        + MembershipPayment::where('created_at', '>=', $monthStart)
                            ->whereNull('refunded_at')
                            ->sum('amount');

        $todayTransactions = Payment::whereDate('created_at', $today)->count()
                           + MembershipPayment::whereDate('created_at', $today)->count();

        $pendingRefunds = Payment::where('refund_status', 'partial')->count();

        // ─── Inventory Summary ───
        $totalProducts = InventorySupply::count();
        $outOfStock = InventorySupply::where('stock_qty', 0)->count();
        $stockValue = InventorySupply::selectRaw('SUM(unit_price * stock_qty) as total')
                        ->value('total') ?? 0;

        // ─── Reports & Analytics Summary (using ReportController KPIs) ───
        $reportController = new \App\Http\Controllers\ReportController();
        $reportResponse = $reportController->getKPIs(new \Illuminate\Http\Request());
        $reportData = $reportResponse->getData();
        
        // Extract KPI data from ReportController
        $totalRevenueThisMonth = $reportData->success ? $reportData->data->monthly_revenue : 0;
        $retailSalesThisMonth = $reportData->success ? $reportData->data->retail_sales : 0;
        $membershipRevenueThisMonth = $reportData->success ? $reportData->data->membership_revenue : 0;
        $ptRevenueThisMonth = $reportData->success ? $reportData->data->pt_revenue : 0;
        
        // Additional metrics not in getKPIs (keep separate)
        $totalTransactionsThisMonth = Payment::where('created_at', '>=', $monthStart)->count()
                                    + MembershipPayment::where('created_at', '>=', $monthStart)->count();
        $totalRefundsThisMonth = Payment::where('created_at', '>=', $monthStart)
                                    ->where('is_refunded', true)->count()
                                + MembershipPayment::where('created_at', '>=', $monthStart)
                                    ->whereNotNull('refunded_at')->count();
        $inventoryMovements = InventoryTransaction::where('created_at', '>=', $monthStart)->count();

        // ─── Recent Activity ───
        $recentPayments = Payment::latest()
                            ->take(5)
                            ->get(['id', 'receipt_number', 'customer_name', 'total_amount', 'payment_method', 'created_at', 'is_refunded']);

        $upcomingPTSessions = PTSchedule::where('status', 'upcoming')
                                ->where('scheduled_date', '>=', $today)
                                ->orderBy('scheduled_date')
                                ->orderBy('scheduled_time')
                                ->take(5)
                                ->get(['id', 'customer_name', 'trainer_name', 'scheduled_date', 'scheduled_time', 'status']);

        $recentAttendance = Attendance::whereDate('date', $today)
                            ->latest()
                            ->take(5)
                            ->get(['id', 'customer_name', 'customer_type', 'time_in', 'time_out', 'status']);

        return view('dashboard.index', compact(
            // Top KPIs
            'totalActiveMembers', 'todayRevenue', 'todayAttendance', 'attentionItems',
            'expiringCount', 'lowStockCount',
            // Clients
            'totalClients', 'activeClients', 'expiringClients', 'newClientsThisMonth',
            // Memberships
            'totalMemberships', 'activeMemberships', 'expiringMemberships', 'newMembershipsThisMonth',
            // Sessions
            'ptSessionsToday', 'upcomingPT', 'completedPTToday', 'todayAttendance', 'walkInsToday',
            // Payments
            'monthlyRevenue', 'todayRevenue', 'todayTransactions', 'pendingRefunds',
            // Inventory
            'totalProducts', 'lowStockCount', 'outOfStock', 'stockValue',
            // Reports (now using ReportController KPIs)
            'totalRevenueThisMonth', 'retailSalesThisMonth', 'membershipRevenueThisMonth', 'ptRevenueThisMonth',
            'totalTransactionsThisMonth', 'totalRefundsThisMonth', 'inventoryMovements',
            // Recent Activity
            'recentPayments', 'upcomingPTSessions', 'recentAttendance'
        ));
    }
}
