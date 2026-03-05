<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\MembershipPayment;
use App\Models\PTSchedule;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * ReportService
 *
 * Centralizes all report data aggregation logic.
 * Keeps calculations reusable and separated from controller/UI concerns.
 */
class ReportService
{
    /**
     * Fixed PT session rate used for PT revenue calculations.
     */
    const PT_SESSION_RATE = 500;

    /**
     * Supported export formats.
     */
    const SUPPORTED_EXPORT_FORMATS = ['pdf', 'csv', 'excel'];

    // ──────────────────────────────────────────────────
    // KPI Calculations
    // ──────────────────────────────────────────────────

    /**
     * Get all KPI data for the current (or specified) month,
     * including month-over-month percentage changes.
     *
     * Monthly Revenue = Retail + Membership + PT for the month.
     *
     * @param  int|null  $month
     * @param  int|null  $year
     * @return array
     */
    public function getKPIs(?int $month = null, ?int $year = null): array
    {
        $month = $month ?? Carbon::now()->month;
        $year  = $year  ?? Carbon::now()->year;

        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd   = Carbon::create($year, $month, 1)->endOfMonth();
        $prevStart    = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd      = $currentStart->copy()->subMonth()->endOfMonth();

        // Retail revenue
        $currentRetail = $this->sumRetailRevenue($currentStart, $currentEnd);
        $prevRetail    = $this->sumRetailRevenue($prevStart, $prevEnd);

        // Membership revenue
        $currentMembership = $this->sumMembershipRevenue($currentStart, $currentEnd);
        $prevMembership    = $this->sumMembershipRevenue($prevStart, $prevEnd);

        // PT revenue (done sessions x rate)
        $currentPT = $this->sumPTRevenue($currentStart, $currentEnd);
        $prevPT    = $this->sumPTRevenue($prevStart, $prevEnd);

        // Totals
        $currentTotal = $currentRetail + $currentMembership + $currentPT;
        $prevTotal    = $prevRetail + $prevMembership + $prevPT;

        return [
            'monthly_revenue'   => (float) $currentTotal,
            'retail_sales'      => (float) $currentRetail,
            'membership_revenue'=> (float) $currentMembership,
            'pt_revenue'        => (float) $currentPT,
            'revenue_change'    => (float) $this->percentChange($prevTotal, $currentTotal),
            'retail_change'     => (float) $this->percentChange($prevRetail, $currentRetail),
            'membership_change' => (float) $this->percentChange($prevMembership, $currentMembership),
            'pt_change'         => (float) $this->percentChange($prevPT, $currentPT),
        ];
    }

    // ──────────────────────────────────────────────────
    // Revenue Over Time (line chart)
    // ──────────────────────────────────────────────────

    /**
     * Build revenue-over-time datasets.
     *
     * - "this_year"       -> monthly totals for the year
     * - "last_3_months"   -> monthly totals for the last 3 months
     * - "this_month"      -> daily totals for the current month
     *
     * @param  string  $period
     * @return array   { labels, datasets }
     */
    public function getRevenueOverTime(string $period = 'this_year'): array
    {
        $range = $this->getDateRange($period);

        // Short periods render daily; longer periods render monthly
        if (in_array($period, ['today', 'this_week', 'this_month'])) {
            return $this->buildDailyRevenue($range);
        }

        return $this->buildMonthlyRevenue($range);
    }

    // ──────────────────────────────────────────────────
    // Top Selling Products (bar chart)
    // ──────────────────────────────────────────────────

    /**
     * Get top 4 products by quantity sold, broken down by the appropriate
     * time grouping for the selected period.
     *
     * - "today"         -> single "Today" label
     * - "this_week"     -> Mon-Sun (day of week)
     * - "this_month"    -> Week 1, Week 2, ...
     * - "last_3_months" -> month names
     * - "this_year"     -> month names
     *
     * @param  string  $period
     * @return array   { labels, datasets }
     */
    public function getTopSellingProducts(string $period = 'this_week'): array
    {
        $range = $this->getDateRange($period);

        // Identify top 4 products
        $topProducts = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
            ->selectRaw('payment_items.product_name, SUM(payment_items.quantity) as total_qty')
            ->whereBetween('payments.created_at', [$range['start'], $range['end']])
            ->groupBy('payment_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(4)
            ->pluck('product_name')
            ->toArray();

        return match ($period) {
            'today'                      => $this->buildTopSellingForToday($range, $topProducts),
            'this_week'                  => $this->buildTopSellingByDayOfWeek($range, $topProducts),
            'this_month'                 => $this->buildTopSellingByWeek($range, $topProducts),
            'last_3_months', 'this_year' => $this->buildTopSellingByMonth($range, $topProducts),
            default                      => $this->buildTopSellingByDayOfWeek($range, $topProducts),
        };
    }

    /**
     * Top selling: single "Today" bar per product.
     */
    private function buildTopSellingForToday(array $range, array $topProducts): array
    {
        $labels = ['Today'];
        if (empty($topProducts)) {
            return ['labels' => $labels, 'datasets' => []];
        }

        $salesData = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
            ->selectRaw('payment_items.product_name, SUM(payment_items.quantity) as qty')
            ->whereBetween('payments.created_at', [$range['start'], $range['end']])
            ->whereIn('payment_items.product_name', $topProducts)
            ->groupBy('payment_items.product_name')
            ->get()
            ->keyBy('product_name');

        $colors   = ['#66BB6A', '#FFA726', '#AB47BC', '#26C6DA'];
        $datasets = [];

        foreach ($topProducts as $index => $product) {
            $datasets[] = [
                'label'           => $product,
                'data'            => [$salesData->has($product) ? (int) $salesData[$product]->qty : 0],
                'backgroundColor' => $colors[$index] ?? '#42A5F5',
                'borderRadius'    => 4,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Top selling: breakdown by day of week (Mon-Sun).
     */
    private function buildTopSellingByDayOfWeek(array $range, array $topProducts): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        if (empty($topProducts)) {
            return ['labels' => $days, 'datasets' => []];
        }

        $salesData = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
            ->selectRaw('payment_items.product_name, DAYOFWEEK(payments.created_at) as day_of_week, SUM(payment_items.quantity) as qty')
            ->whereBetween('payments.created_at', [$range['start'], $range['end']])
            ->whereIn('payment_items.product_name', $topProducts)
            ->groupBy('payment_items.product_name', 'day_of_week')
            ->get();

        $colors   = ['#66BB6A', '#FFA726', '#AB47BC', '#26C6DA'];
        $datasets = [];

        foreach ($topProducts as $index => $product) {
            $data = array_fill(0, 7, 0);

            foreach ($salesData as $sale) {
                if ($sale->product_name === $product) {
                    // MySQL DAYOFWEEK: 1=Sun, 7=Sat -> convert to 0=Mon index
                    $dayIndex = ($sale->day_of_week + 5) % 7;
                    $data[$dayIndex] = (int) $sale->qty;
                }
            }

            $datasets[] = [
                'label'           => $product,
                'data'            => $data,
                'backgroundColor' => $colors[$index] ?? '#42A5F5',
                'borderRadius'    => 4,
            ];
        }

        return ['labels' => $days, 'datasets' => $datasets];
    }

    /**
     * Top selling: breakdown by week of the month (Week 1, Week 2, ...).
     */
    private function buildTopSellingByWeek(array $range, array $topProducts): array
    {
        // Build week buckets for the month
        $cursor = $range['start']->copy();
        $weeks  = [];
        $weekIndex = 1;
        while ($cursor->lte($range['end'])) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek();
            if ($weekEnd->gt($range['end'])) {
                $weekEnd = $range['end']->copy();
            }
            $weeks[] = ['label' => 'Week ' . $weekIndex, 'start' => $weekStart, 'end' => $weekEnd];
            $cursor = $weekEnd->copy()->addDay()->startOfDay();
            $weekIndex++;
        }

        $labels = array_column($weeks, 'label');

        if (empty($topProducts)) {
            return ['labels' => $labels, 'datasets' => []];
        }

        $salesData = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
            ->selectRaw('payment_items.product_name, DATE(payments.created_at) as sale_date, SUM(payment_items.quantity) as qty')
            ->whereBetween('payments.created_at', [$range['start'], $range['end']])
            ->whereIn('payment_items.product_name', $topProducts)
            ->groupBy('payment_items.product_name', 'sale_date')
            ->get();

        $colors   = ['#66BB6A', '#FFA726', '#AB47BC', '#26C6DA'];
        $datasets = [];

        foreach ($topProducts as $index => $product) {
            $data = array_fill(0, count($weeks), 0);

            foreach ($salesData as $sale) {
                if ($sale->product_name === $product) {
                    $saleDate = Carbon::parse($sale->sale_date);
                    foreach ($weeks as $wi => $week) {
                        if ($saleDate->between($week['start'], $week['end'])) {
                            $data[$wi] += (int) $sale->qty;
                            break;
                        }
                    }
                }
            }

            $datasets[] = [
                'label'           => $product,
                'data'            => $data,
                'backgroundColor' => $colors[$index] ?? '#42A5F5',
                'borderRadius'    => 4,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * Top selling: breakdown by month (for last_3_months / this_year).
     */
    private function buildTopSellingByMonth(array $range, array $topProducts): array
    {
        // Build month labels
        $monthKeys = [];
        $labels    = [];
        $cursor    = $range['start']->copy()->startOfMonth();
        while ($cursor->lte($range['end'])) {
            $monthKeys[] = $cursor->format('Y-m');
            $labels[]    = $cursor->format('M');
            $cursor->addMonth();
        }

        if (empty($topProducts)) {
            return ['labels' => $labels, 'datasets' => []];
        }

        $salesData = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
            ->selectRaw('payment_items.product_name, MONTH(payments.created_at) as month, YEAR(payments.created_at) as year, SUM(payment_items.quantity) as qty')
            ->whereBetween('payments.created_at', [$range['start'], $range['end']])
            ->whereIn('payment_items.product_name', $topProducts)
            ->groupBy('payment_items.product_name', 'year', 'month')
            ->get();

        $colors   = ['#66BB6A', '#FFA726', '#AB47BC', '#26C6DA'];
        $datasets = [];

        foreach ($topProducts as $index => $product) {
            $data = array_fill(0, count($monthKeys), 0);

            foreach ($salesData as $sale) {
                if ($sale->product_name === $product) {
                    $saleKey  = $sale->year . '-' . str_pad($sale->month, 2, '0', STR_PAD_LEFT);
                    $keyIndex = array_search($saleKey, $monthKeys);
                    if ($keyIndex !== false) {
                        $data[$keyIndex] = (int) $sale->qty;
                    }
                }
            }

            $datasets[] = [
                'label'           => $product,
                'data'            => $data,
                'backgroundColor' => $colors[$index] ?? '#42A5F5',
                'borderRadius'    => 4,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    // ──────────────────────────────────────────────────
    // Revenue Breakdown (donut chart)
    // ──────────────────────────────────────────────────

    /**
     * Split revenue into Retail, Membership, and PT.
     *
     * Gym attendance is included with an active membership,
     * so there is no separate walk-in revenue.
     *
     * @param  string  $period
     * @return array   { labels, values, colors }
     */
    public function getRevenueBreakdown(string $period = 'this_month'): array
    {
        $range = $this->getDateRange($period);

        $retail     = $this->sumRetailRevenue($range['start'], $range['end']);
        $membership = $this->sumMembershipRevenue($range['start'], $range['end']);
        $pt         = $this->sumPTRevenue($range['start'], $range['end']);

        return [
            'labels' => ['Retail Sales', 'Membership', 'Personal Training'],
            'values' => [
                (float) $retail,
                (float) $membership,
                (float) $pt,
            ],
            'colors' => ['#42A5F5', '#66BB6A', '#FFA726'],
        ];
    }

    // ──────────────────────────────────────────────────
    // Transaction History (pie chart)
    // ──────────────────────────────────────────────────

    /**
     * Group all transactions by payment method (Cash, GCash, etc.).
     * Combines retail payments + membership payments.
     *
     * @param  string  $period
     * @return array   { labels, values, colors }
     */
    public function getTransactionHistory(string $period = 'this_month'): array
    {
        $range = $this->getDateRange($period);

        // Retail payments by method
        $retailTx = Payment::selectRaw('payment_method, SUM(total_amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('payment_method')
            ->get();

        // Membership payments by method
        $membershipTx = MembershipPayment::selectRaw('payment_method, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('payment_method')
            ->get();

        // Merge into single map
        $combined = [];
        foreach ($retailTx as $t) {
            $method = ucfirst(strtolower($t->payment_method));
            $combined[$method] = ($combined[$method] ?? 0) + (float) $t->total;
        }
        foreach ($membershipTx as $t) {
            $method = ucfirst(strtolower($t->payment_method));
            $combined[$method] = ($combined[$method] ?? 0) + (float) $t->total;
        }

        $colorMap = [
            'Cash'          => '#42A5F5',
            'Gcash'         => '#66BB6A',
            'Paymaya'       => '#FFA726',
            'Card'          => '#AB47BC',
            'Bank transfer'  => '#26C6DA',
        ];

        $labels = array_keys($combined);
        $values = array_values($combined);
        $colors = array_map(fn($l) => $colorMap[$l] ?? '#8b92a7', $labels);

        return compact('labels', 'values', 'colors');
    }

    // ──────────────────────────────────────────────────
    // Customer Attendance (line chart)
    // ──────────────────────────────────────────────────

    /**
     * - "today"         -> hourly check-ins from 6 AM to 10 PM
     * - "this_week"     -> daily attendance totals
     * - "this_month"    -> daily attendance totals
     * - "last_3_months" -> weekly attendance totals
     * - "this_year"     -> monthly attendance totals
     *
     * @param  string  $period
     * @return array   { labels, values }
     */
    public function getCustomerAttendance(string $period = 'today'): array
    {
        return match ($period) {
            'today'         => $this->buildHourlyAttendance(),
            'last_3_months' => $this->buildWeeklyAttendance($period),
            'this_year'     => $this->buildMonthlyAttendance($period),
            default         => $this->buildDailyAttendance($period),
        };
    }

    // ──────────────────────────────────────────────────
    // Export helpers
    // ──────────────────────────────────────────────────

    /**
     * Check whether a given export format is supported.
     *
     * @param  string  $format
     * @return bool
     */
    public function isSupportedExportFormat(string $format): bool
    {
        return in_array($format, self::SUPPORTED_EXPORT_FORMATS, true);
    }

    /**
     * Gather all data needed for an export based on scope.
     *
     * Scope mapping:
     *   "all"          -> KPIs + revenue + products + breakdown + transactions + attendance
     *   "revenue"      -> KPIs + revenue-related data
     *   "kpis"         -> KPIs only
     *   "products"     -> KPIs + top selling products
     *   "breakdown"    -> KPIs + revenue breakdown
     *   "transactions" -> KPIs + transaction history
     *   "attendance"   -> KPIs + attendance
     *
     * @param  string  $scope
     * @param  string  $dateRange
     * @return array
     */
    public function gatherExportData(string $scope, string $dateRange): array
    {
        $data = [
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'date_range'   => $this->getDateRangeLabel($dateRange),
        ];

        // Always include KPIs
        $data['kpis'] = $this->getKPIs();

        if (in_array($scope, ['all', 'revenue'])) {
            $data['revenue'] = $this->getRevenueOverTime($dateRange);
        }

        if (in_array($scope, ['all', 'products'])) {
            $raw = $this->getTopSellingProducts($dateRange);
            $products = [];
            foreach ($raw['datasets'] as $ds) {
                $totalQty = array_sum($ds['data']);
                $products[] = [
                    'name'     => $ds['label'],
                    'quantity' => $totalQty,
                    'revenue'  => $totalQty * 100, // estimated revenue
                ];
            }
            $data['products'] = $products;
        }

        if (in_array($scope, ['all', 'breakdown', 'revenue'])) {
            $raw = $this->getRevenueBreakdown($dateRange);
            $breakdown = [];
            foreach ($raw['labels'] as $i => $label) {
                $breakdown[] = [
                    'source' => $label,
                    'amount' => $raw['values'][$i] ?? 0,
                ];
            }
            $data['breakdown'] = $breakdown;
        }

        if (in_array($scope, ['all', 'transactions'])) {
            $raw = $this->getTransactionHistory($dateRange);
            $transactions = [];
            foreach ($raw['labels'] as $i => $label) {
                $transactions[] = [
                    'method' => $label,
                    'amount' => $raw['values'][$i] ?? 0,
                ];
            }
            $data['transactions'] = $transactions;
        }

        if (in_array($scope, ['all', 'attendance'])) {
            $data['attendance'] = $this->getCustomerAttendance(
                $dateRange === 'today' ? 'today' : 'this_week'
            );
        }

        return $data;
    }

    // ──────────────────────────────────────────────────
    // Reusable revenue aggregation helpers
    // ──────────────────────────────────────────────────

    /**
     * Sum retail (product) payments in a date range.
     */
    public function sumRetailRevenue(Carbon $start, Carbon $end): float
    {
        return (float) Payment::whereBetween('created_at', [$start, $end])
            ->sum('total_amount');
    }

    /**
     * Sum membership payments in a date range.
     */
    public function sumMembershipRevenue(Carbon $start, Carbon $end): float
    {
        return (float) MembershipPayment::whereBetween('created_at', [$start, $end])
            ->sum('amount');
    }

    /**
     * Sum PT revenue (done sessions x P500) in a date range.
     */
    public function sumPTRevenue(Carbon $start, Carbon $end): float
    {
        $sessions = PTSchedule::whereBetween('scheduled_date', [$start, $end])
            ->where('status', 'done')
            ->count();

        return (float) ($sessions * self::PT_SESSION_RATE);
    }

    // ──────────────────────────────────────────────────
    // Percentage change
    // ──────────────────────────────────────────────────

    /**
     * Calculate percentage change between two values.
     *
     * - If previous = 0 and current > 0 -> 100%
     * - If both = 0 -> 0%
     * - Otherwise normal formula.
     *
     * @param  float  $old
     * @param  float  $new
     * @return float
     */
    public function percentChange(float $old, float $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100.0 : 0.0;
        }

        return round((($new - $old) / $old) * 100, 1);
    }

    // ──────────────────────────────────────────────────
    // Date range helpers
    // ──────────────────────────────────────────────────

    /**
     * Convert a period string into start/end Carbon instances.
     * Defaults to "this_month" for unrecognised periods.
     *
     * Supported: today, this_week, this_month, last_month, last_3_months, this_year
     *
     * @param  string  $period
     * @return array{start: Carbon, end: Carbon}
     */
    public function getDateRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today'         => ['start' => $now->copy()->startOfDay(),   'end' => $now->copy()->endOfDay()],
            'this_week'     => ['start' => $now->copy()->startOfWeek(),  'end' => $now->copy()->endOfWeek()],
            'this_month'    => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'last_month'    => [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end'   => $now->copy()->subMonth()->endOfMonth(),
            ],
            'last_3_months' => [
                'start' => $now->copy()->subMonths(2)->startOfMonth(),
                'end'   => $now->copy()->endOfMonth(),
            ],
            'this_year'     => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            default         => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
        };
    }

    /**
     * Human-readable label for a period string.
     */
    public function getDateRangeLabel(string $period): string
    {
        return match ($period) {
            'today'         => 'Today',
            'this_week'     => 'This Week',
            'this_month'    => 'This Month',
            'last_month'    => 'Last Month',
            'last_3_months' => 'Last 3 Months',
            'this_year'     => 'This Year',
            default         => 'Custom Period',
        };
    }

    // ──────────────────────────────────────────────────
    // Private builders
    // ──────────────────────────────────────────────────

    /**
     * Build monthly revenue datasets for line chart.
     */
    private function buildMonthlyRevenue(array $range): array
    {
        $retailData = Payment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $membershipData = MembershipPayment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $ptData = PTSchedule::selectRaw('MONTH(scheduled_date) as month, YEAR(scheduled_date) as year, COUNT(*) as sessions')
            ->whereBetween('scheduled_date', [$range['start'], $range['end']])
            ->where('status', 'done')
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

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
            $pt[]         = $ptData->has($key) ? ($ptData[$key]->sessions * self::PT_SESSION_RATE) : 0;
            $cursor->addMonth();
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Retail',            'data' => $retail],
                ['label' => 'Membership',        'data' => $membership],
                ['label' => 'Personal Training', 'data' => $pt],
            ],
        ];
    }

    /**
     * Build daily revenue datasets for line chart ("This Month" view).
     */
    private function buildDailyRevenue(array $range): array
    {
        $retailData = Payment::selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $membershipData = MembershipPayment::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $ptData = PTSchedule::selectRaw('DATE(scheduled_date) as day, COUNT(*) as sessions')
            ->whereBetween('scheduled_date', [$range['start'], $range['end']])
            ->where('status', 'done')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $retail = [];
        $membership = [];
        $pt = [];

        $cursor = $range['start']->copy();
        while ($cursor <= $range['end']) {
            $key = $cursor->format('Y-m-d');
            $labels[]     = $cursor->format('M d');
            $retail[]     = $retailData->has($key) ? (float) $retailData[$key]->total : 0;
            $membership[] = $membershipData->has($key) ? (float) $membershipData[$key]->total : 0;
            $pt[]         = $ptData->has($key) ? ($ptData[$key]->sessions * self::PT_SESSION_RATE) : 0;
            $cursor->addDay();
        }

        return [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Retail',            'data' => $retail],
                ['label' => 'Membership',        'data' => $membership],
                ['label' => 'Personal Training', 'data' => $pt],
            ],
        ];
    }

    /**
     * Build hourly attendance for today (6 AM - 10 PM).
     */
    private function buildHourlyAttendance(): array
    {
        $data = Attendance::selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
            ->whereDate('date', Carbon::today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $labels = [];
        $values = [];

        for ($h = 6; $h <= 22; $h++) {
            $labels[] = Carbon::createFromTime($h, 0)->format('g:i A');
            $values[] = $data->has($h) ? (int) $data[$h]->count : 0;
        }

        return compact('labels', 'values');
    }

    /**
     * Build daily attendance for a given period (this_week, this_month).
     */
    private function buildDailyAttendance(string $period): array
    {
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

        return compact('labels', 'values');
    }

    /**
     * Build weekly attendance for last_3_months.
     * Aggregates check-ins by week instead of every single day.
     */
    private function buildWeeklyAttendance(string $period): array
    {
        $range = $this->getDateRange($period);

        $data = Attendance::selectRaw('DATE(date) as day, COUNT(*) as count')
            ->whereBetween('date', [$range['start'], $range['end']])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Build week buckets
        $cursor = $range['start']->copy();
        $weeks  = [];
        while ($cursor->lte($range['end'])) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek();
            if ($weekEnd->gt($range['end'])) {
                $weekEnd = $range['end']->copy();
            }
            $weeks[] = [
                'label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'start' => $weekStart,
                'end'   => $weekEnd,
            ];
            $cursor = $weekEnd->copy()->addDay()->startOfDay();
        }

        $labels = [];
        $values = [];

        foreach ($weeks as $week) {
            $labels[] = $week['label'];
            $total = 0;
            $dayCursor = $week['start']->copy();
            while ($dayCursor->lte($week['end'])) {
                $key = $dayCursor->format('Y-m-d');
                $total += $data->has($key) ? (int) $data[$key]->count : 0;
                $dayCursor->addDay();
            }
            $values[] = $total;
        }

        return compact('labels', 'values');
    }

    /**
     * Build monthly attendance for this_year.
     * Aggregates check-ins by month instead of every single day.
     */
    private function buildMonthlyAttendance(string $period): array
    {
        $range = $this->getDateRange($period);

        $data = Attendance::selectRaw('MONTH(date) as month, YEAR(date) as year, COUNT(*) as count')
            ->whereBetween('date', [$range['start'], $range['end']])
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get()
            ->keyBy(fn($i) => $i->year . '-' . str_pad($i->month, 2, '0', STR_PAD_LEFT));

        $labels = [];
        $values = [];

        $cursor = $range['start']->copy()->startOfMonth();
        while ($cursor->lte($range['end'])) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->format('M');
            $values[] = $data->has($key) ? (int) $data[$key]->count : 0;
            $cursor->addMonth();
        }

        return compact('labels', 'values');
    }
}
