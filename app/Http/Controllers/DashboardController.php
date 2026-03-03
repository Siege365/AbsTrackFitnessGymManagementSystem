<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\MembershipPayment;
use App\Models\PTPayment;
use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\InventorySupply;
use App\Models\Trainer;
use App\Models\ActivityLog;
use App\Models\GymPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with server-side computed metrics.
     */
    public function index()
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        // ── Membership Metrics ──
        $totalMemberships  = Membership::count();
        $activeMemberships = Membership::whereDate('due_date', '>=', $today)->count();
        $expiredMemberships = Membership::whereDate('due_date', '<', $today)->count();
        $dueSoonMemberships = Membership::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->count();

        // ── Client Metrics ──
        $totalClients  = Client::count();
        $activeClients = Client::whereDate('due_date', '>=', $today)->count();
        $expiredClients = Client::whereDate('due_date', '<', $today)->count();

        // ── Total unique customers ──
        $totalCustomers = Customer::count();

        // ── Today's Attendance ──
        $todayAttendance = Attendance::whereDate('date', $today)->count();
        $activeInGym = Attendance::whereDate('date', $today)->whereNull('time_out')->count();

        // ── Today's PT Sessions ──
        $todayPTSessions = PTSchedule::whereDate('scheduled_date', $today)->count();
        $upcomingPT = PTSchedule::whereDate('scheduled_date', $today)->where('status', 'upcoming')->count();
        $inProgressPT = PTSchedule::whereDate('scheduled_date', $today)->where('status', 'in_progress')->count();
        $completedPTToday = PTSchedule::whereDate('scheduled_date', $today)->where('status', 'done')->count();

        // ── Revenue Metrics (This Month) ──
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $monthlyRetailRevenue = (float) Payment::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');
        $monthlyMembershipRevenue = (float) MembershipPayment::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
        $monthlyPTRevenue = (float) PTPayment::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
        $monthlyTotalRevenue = $monthlyRetailRevenue + $monthlyMembershipRevenue + $monthlyPTRevenue;

        // ── Today's Revenue ──
        $todayStart = $now->copy()->startOfDay();
        $todayEnd   = $now->copy()->endOfDay();

        $todayRetailRevenue = (float) Payment::whereBetween('created_at', [$todayStart, $todayEnd])->sum('total_amount');
        $todayMembershipRevenue = (float) MembershipPayment::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount');
        $todayPTRevenue = (float) PTPayment::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount');
        $todayTotalRevenue = $todayRetailRevenue + $todayMembershipRevenue + $todayPTRevenue;

        // ── Previous Month Revenue (for % change) ──
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd   = $now->copy()->subMonth()->endOfMonth();

        $prevRetail = (float) Payment::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->sum('total_amount');
        $prevMembership = (float) MembershipPayment::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->sum('amount');
        $prevPT = (float) PTPayment::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->sum('amount');
        $prevTotal = $prevRetail + $prevMembership + $prevPT;

        $revenueChange = $this->percentChange($prevTotal, $monthlyTotalRevenue);

        // ── Inventory Alerts ──
        $totalProducts = InventorySupply::count();
        $lowStockProducts = InventorySupply::whereColumn('stock_qty', '<', 'low_stock_threshold')
            ->where('stock_qty', '>', 0)
            ->count();
        $outOfStockProducts = InventorySupply::where('stock_qty', 0)->count();

        // ── Staff / Trainers ──
        $totalTrainers = Trainer::count();

        // ── Recent Activity Logs (last 50) ──
        $recentActivities = ActivityLog::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // ── Recent Payments (all types, last 50) ──
        $productPayments = Payment::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($p) {
                return (object) [
                    'receipt_number' => $p->receipt_number,
                    'name'           => $p->customer_name,
                    'category'       => 'Product',
                    'plan_type'      => $p->transaction_type ?? 'Retail',
                    'payment_method' => $p->payment_method,
                    'amount'         => $p->total_amount,
                    'processed_by'   => $p->cashier_name,
                    'created_at'     => $p->created_at,
                ];
            });

        $membershipPayments = MembershipPayment::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($p) {
                return (object) [
                    'receipt_number' => $p->receipt_number,
                    'name'           => $p->member_name,
                    'category'       => 'Membership',
                    'plan_type'      => $p->plan_type ?? 'Membership',
                    'payment_method' => $p->payment_method,
                    'amount'         => $p->amount,
                    'processed_by'   => $p->processed_by,
                    'created_at'     => $p->created_at,
                ];
            });

        $ptPayments = PTPayment::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($p) {
                return (object) [
                    'receipt_number' => $p->receipt_number,
                    'name'           => $p->member_name,
                    'category'       => 'PT',
                    'plan_type'      => $p->plan_type ?? 'Personal Training',
                    'payment_method' => $p->payment_method,
                    'amount'         => $p->amount,
                    'processed_by'   => $p->processed_by,
                    'created_at'     => $p->created_at,
                ];
            });

        $recentPayments = $productPayments
            ->concat($membershipPayments)
            ->concat($ptPayments)
            ->sortByDesc('created_at')
            ->take(50)
            ->values();

        // ── Membership Plan Distribution ──
        $planDistribution = Membership::whereDate('due_date', '>=', $today)
            ->selectRaw('plan_type, COUNT(*) as count')
            ->groupBy('plan_type')
            ->orderByDesc('count')
            ->get();

        // ── Upcoming Expiring Memberships (next 7 days) ──
        $expiringMemberships = Membership::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        // ── Upcoming Expiring Clients (next 7 days) ──
        $expiringClients = Client::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        // ── Low Stock Items ──
        $lowStockItems = InventorySupply::whereColumn('stock_qty', '<', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->limit(8)
            ->get();

        // ── Today's PT Schedule List ──
        $todaySchedules = PTSchedule::whereDate('scheduled_date', $today)
            ->orderBy('scheduled_time')
            ->limit(10)
            ->get();

        return view('pages.dashboard', compact(
            'totalMemberships', 'activeMemberships', 'expiredMemberships', 'dueSoonMemberships',
            'totalClients', 'activeClients', 'expiredClients',
            'totalCustomers',
            'todayAttendance', 'activeInGym',
            'todayPTSessions', 'upcomingPT', 'inProgressPT', 'completedPTToday',
            'monthlyTotalRevenue', 'monthlyRetailRevenue', 'monthlyMembershipRevenue', 'monthlyPTRevenue',
            'todayTotalRevenue', 'todayRetailRevenue', 'todayMembershipRevenue', 'todayPTRevenue',
            'revenueChange',
            'totalProducts', 'lowStockProducts', 'outOfStockProducts',
            'totalTrainers',
            'recentActivities', 'recentPayments',
            'planDistribution', 'expiringMemberships', 'expiringClients',
            'lowStockItems', 'todaySchedules'
        ));
    }

    /**
     * API: Get attendance chart data (hourly for today, daily for week/month).
     */
    public function getAttendanceChart(Request $request)
    {
        $period = $request->get('period', 'today');

        if ($period === 'today') {
            $data = Attendance::selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
                ->whereDate('date', Carbon::today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->get()
                ->keyBy('hour');

            $labels = [];
            $values = [];
            for ($h = 6; $h <= 22; $h++) {
                $labels[] = Carbon::createFromTime($h, 0)->format('g A');
                $values[] = $data->has($h) ? (int) $data[$h]->count : 0;
            }
        } else {
            $range = $this->getDateRange($period);
            $data = Attendance::selectRaw('DATE(date) as day, COUNT(*) as count')
                ->whereBetween('date', [$range['start'], $range['end']])
                ->groupBy('day')
                ->orderBy('day')
                ->get()
                ->keyBy('day');

            $labels = [];
            $values = [];
            $cursor = $range['start']->copy();
            while ($cursor <= $range['end']) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('D, M d');
                $values[] = $data->has($key) ? (int) $data[$key]->count : 0;
                $cursor->addDay();
            }
        }

        return response()->json(['success' => true, 'data' => compact('labels', 'values')]);
    }

    /**
     * API: Get revenue trend data (daily for this month).
     */
    public function getRevenueChart(Request $request)
    {
        $period = $request->get('period', 'this_month');
        $range  = $this->getDateRange($period);

        $retailData = Payment::selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $membershipData = MembershipPayment::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $ptData = PTPayment::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('day')->orderBy('day')->get()->keyBy('day');

        $labels = [];
        $retail = [];
        $membership = [];
        $pt = [];

        $cursor = $range['start']->copy();
        while ($cursor <= $range['end']) {
            $key = $cursor->format('Y-m-d');

            if ($period === 'this_year' || $period === 'last_3_months') {
                // Group by month for longer ranges
                $monthKey = $cursor->format('Y-m');
                if (empty($labels) || end($labels) !== $cursor->format('M')) {
                    $labels[] = $cursor->format('M');
                }
            } else {
                $labels[] = $cursor->format('M d');
            }

            $retail[]     = $retailData->has($key) ? (float) $retailData[$key]->total : 0;
            $membership[] = $membershipData->has($key) ? (float) $membershipData[$key]->total : 0;
            $pt[]         = $ptData->has($key) ? (float) $ptData[$key]->total : 0;
            $cursor->addDay();
        }

        // For year/quarterly, aggregate by month
        if (in_array($period, ['this_year', 'last_3_months'])) {
            return $this->getMonthlyRevenue($range);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Retail', 'data' => $retail],
                    ['label' => 'Membership', 'data' => $membership],
                    ['label' => 'Personal Training', 'data' => $pt],
                ],
            ],
        ]);
    }

    /**
     * API: Get membership status breakdown for doughnut chart.
     */
    public function getMembershipChart()
    {
        $today = Carbon::today();

        $active  = Membership::whereDate('due_date', '>=', $today->copy()->addDays(8))->count();
        $dueSoon = Membership::whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays(7))->count();
        $expired = Membership::whereDate('due_date', '<', $today)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => ['Active', 'Due Soon', 'Expired'],
                'values' => [$active, $dueSoon, $expired],
                'colors' => ['#66BB6A', '#FFA726', '#EF5350'],
            ],
        ]);
    }

    // ── Helpers ──

    private function percentChange(float $old, float $new): float
    {
        if ($old == 0) return $new > 0 ? 100.0 : 0.0;
        return round((($new - $old) / $old) * 100, 1);
    }

    private function getDateRange(string $period): array
    {
        $now = Carbon::now();
        return match ($period) {
            'today'         => ['start' => $now->copy()->startOfDay(),   'end' => $now->copy()->endOfDay()],
            'this_week'     => ['start' => $now->copy()->startOfWeek(),  'end' => $now->copy()->endOfWeek()],
            'this_month'    => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'last_month'    => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            'last_3_months' => ['start' => $now->copy()->subMonths(2)->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'this_year'     => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            default         => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
        };
    }

    private function getMonthlyRevenue(array $range)
    {
        $retailData = Payment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')
            ->get()->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $membershipData = MembershipPayment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')
            ->get()->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $ptData = PTPayment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('year', 'month')->orderBy('year')->orderBy('month')
            ->get()->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $labels = [];
        $retail = [];
        $membership = [];
        $pt = [];

        $cursor = $range['start']->copy()->startOfMonth();
        while ($cursor <= $range['end']) {
            $key = $cursor->format('Y-m');
            $labels[]     = $cursor->format('M');
            $retail[]     = $retailData->has($key) ? (float) $retailData[$key]->total : 0;
            $membership[] = $membershipData->has($key) ? (float) $membershipData[$key]->total : 0;
            $pt[]         = $ptData->has($key) ? (float) $ptData[$key]->total : 0;
            $cursor->addMonth();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Retail', 'data' => $retail],
                    ['label' => 'Membership', 'data' => $membership],
                    ['label' => 'Personal Training', 'data' => $pt],
                ],
            ],
        ]);
    }
}
