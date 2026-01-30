<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\MembershipPayment;
use App\Models\PTSchedule;
use App\Models\Attendance;
use App\Models\InventorySupply;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the reports page
     */
    public function index()
    {
        return view('ReportAndBilling.ReportAndBilling');
    }

    /**
     * Get KPI data for the dashboard
     */
    public function getKPIs(Request $request)
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);
            
            $currentMonthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $currentMonthEnd = Carbon::create($year, $month, 1)->endOfMonth();
            
            // Previous month for comparison
            $prevMonthStart = $currentMonthStart->copy()->subMonth()->startOfMonth();
            $prevMonthEnd = $currentMonthStart->copy()->subMonth()->endOfMonth();

            // Monthly Retail Sales (from payments table - retail transactions)
            $currentRetailSales = Payment::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('total_amount');
            $prevRetailSales = Payment::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('total_amount');
            $retailSalesChange = $this->calculatePercentChange($prevRetailSales, $currentRetailSales);

            // Monthly Membership Revenue (from membership_payments table)
            $currentMembershipRevenue = MembershipPayment::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->sum('amount');
            $prevMembershipRevenue = MembershipPayment::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
                ->sum('amount');
            $membershipRevenueChange = $this->calculatePercentChange($prevMembershipRevenue, $currentMembershipRevenue);

            // Monthly PT Revenue (from pt_schedules with status 'done')
            // Assuming a fixed rate of ₱500 per PT session, or we can add a price column
            $ptRate = 500; // Default PT session rate
            $currentPTSessions = PTSchedule::whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
                ->where('status', 'done')
                ->count();
            $prevPTSessions = PTSchedule::whereBetween('scheduled_date', [$prevMonthStart, $prevMonthEnd])
                ->where('status', 'done')
                ->count();
            $currentPTRevenue = $currentPTSessions * $ptRate;
            $prevPTRevenue = $prevPTSessions * $ptRate;
            $ptRevenueChange = $this->calculatePercentChange($prevPTRevenue, $currentPTRevenue);

            // Total Monthly Revenue (sum of all revenue streams)
            $currentTotalRevenue = $currentRetailSales + $currentMembershipRevenue + $currentPTRevenue;
            $prevTotalRevenue = $prevRetailSales + $prevMembershipRevenue + $prevPTRevenue;
            $totalRevenueChange = $this->calculatePercentChange($prevTotalRevenue, $currentTotalRevenue);

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_revenue' => (float) $currentTotalRevenue,
                    'retail_sales' => (float) $currentRetailSales,
                    'membership_revenue' => (float) $currentMembershipRevenue,
                    'pt_revenue' => (float) $currentPTRevenue,
                    'revenue_change' => (float) $totalRevenueChange,
                    'retail_change' => (float) $retailSalesChange,
                    'membership_change' => (float) $membershipRevenueChange,
                    'pt_change' => (float) $ptRevenueChange
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue over time data for line chart
     */
    public function getRevenueOverTime(Request $request)
    {
        try {
            $period = $request->get('period', 'this_year'); // this_month, last_3_months, this_year
            $dateRange = $this->getDateRange($period);
            
            // Get retail sales by month
            $retailData = Payment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Get membership revenue by month
            $membershipData = MembershipPayment::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount) as total')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Get PT revenue by month (count sessions × rate)
            $ptRate = 500;
            $ptData = PTSchedule::selectRaw('MONTH(scheduled_date) as month, YEAR(scheduled_date) as year, COUNT(*) as sessions')
                ->whereBetween('scheduled_date', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'done')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get()
                ->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Build months array
            $labels = [];
            $retail = [];
            $membership = [];
            $pt = [];

            $current = $dateRange['start']->copy()->startOfMonth();
            while ($current <= $dateRange['end']) {
                $key = $current->format('Y-m');
                $labels[] = $current->format('M');
                
                $retail[] = $retailData->has($key) ? (float) $retailData[$key]->total : 0;
                $membership[] = $membershipData->has($key) ? (float) $membershipData[$key]->total : 0;
                $pt[] = $ptData->has($key) ? ($ptData[$key]->sessions * $ptRate) : 0;
                
                $current->addMonth();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        ['label' => 'Retail', 'data' => $retail],
                        ['label' => 'Membership', 'data' => $membership],
                        ['label' => 'Personal Training', 'data' => $pt]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products data for bar chart
     */
    public function getTopSellingProducts(Request $request)
    {
        try {
            $period = $request->get('period', 'this_week'); // this_week, this_month
            $dateRange = $this->getDateRange($period);

            // Get top products by quantity sold, grouped by day of week
            $salesData = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
                ->selectRaw('payment_items.product_name, DAYOFWEEK(payments.created_at) as day_of_week, SUM(payment_items.quantity) as qty')
                ->whereBetween('payments.created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('payment_items.product_name', 'day_of_week')
                ->orderBy('qty', 'desc')
                ->get();

            // Get top 4 products
            $topProducts = PaymentItem::join('payments', 'payment_items.payment_id', '=', 'payments.id')
                ->selectRaw('payment_items.product_name, SUM(payment_items.quantity) as total_qty')
                ->whereBetween('payments.created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('payment_items.product_name')
                ->orderBy('total_qty', 'desc')
                ->limit(4)
                ->pluck('product_name')
                ->toArray();

            // Build data structure for each day
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $datasets = [];
            $colors = ['#66BB6A', '#FFA726', '#AB47BC', '#26C6DA'];

            foreach ($topProducts as $index => $product) {
                $data = array_fill(0, 7, 0);
                
                foreach ($salesData as $sale) {
                    if ($sale->product_name === $product) {
                        // DAYOFWEEK returns 1=Sunday, 2=Monday, etc. Convert to 0=Monday
                        $dayIndex = ($sale->day_of_week + 5) % 7;
                        $data[$dayIndex] = (int) $sale->qty;
                    }
                }
                
                $datasets[] = [
                    'label' => $product,
                    'data' => $data,
                    'backgroundColor' => $colors[$index] ?? '#42A5F5'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $days,
                    'datasets' => $datasets
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue breakdown data for donut chart
     */
    public function getRevenueBreakdown(Request $request)
    {
        try {
            $period = $request->get('period', 'this_month');
            $dateRange = $this->getDateRange($period);
            $ptRate = 500;

            // Retail Sales
            $retailSales = Payment::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('total_amount');

            // Membership Revenue
            $membershipRevenue = MembershipPayment::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->sum('amount');

            // PT Revenue
            $ptSessions = PTSchedule::whereBetween('scheduled_date', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'done')
                ->count();
            $ptRevenue = $ptSessions * $ptRate;

            // Walk-ins (attendance without PT session - could be day pass or similar)
            // For now, we'll estimate based on attendance count
            $walkIns = Attendance::whereBetween('date', [$dateRange['start'], $dateRange['end']])
                ->count();
            $walkInRevenue = $walkIns * 50; // Assuming ₱50 per walk-in/day pass

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => ['Retail Sales', 'Membership', 'Personal Training', 'Walk-ins'],
                    'values' => [
                        (float) $retailSales,
                        (float) $membershipRevenue,
                        (float) $ptRevenue,
                        (float) $walkInRevenue
                    ],
                    'colors' => ['#42A5F5', '#66BB6A', '#FFA726', '#AB47BC']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch breakdown data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction history data for pie chart
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $period = $request->get('period', 'this_month');
            $dateRange = $this->getDateRange($period);

            // Get transactions grouped by payment method
            $transactions = Payment::selectRaw('payment_method, SUM(total_amount) as total')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('payment_method')
                ->get();

            // Also include membership payments
            $membershipTransactions = MembershipPayment::selectRaw('payment_method, SUM(amount) as total')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->groupBy('payment_method')
                ->get();

            // Combine and group by payment method
            $combined = [];
            foreach ($transactions as $t) {
                $method = ucfirst(strtolower($t->payment_method));
                $combined[$method] = ($combined[$method] ?? 0) + (float) $t->total;
            }
            foreach ($membershipTransactions as $t) {
                $method = ucfirst(strtolower($t->payment_method));
                $combined[$method] = ($combined[$method] ?? 0) + (float) $t->total;
            }

            $colorMap = [
                'Cash' => '#42A5F5',
                'Gcash' => '#66BB6A',
                'Paymaya' => '#FFA726',
                'Card' => '#AB47BC',
                'Bank Transfer' => '#26C6DA'
            ];

            $labels = array_keys($combined);
            $values = array_values($combined);
            $colors = array_map(function($label) use ($colorMap) {
                return $colorMap[$label] ?? '#8b92a7';
            }, $labels);

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values,
                    'colors' => $colors
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer attendance trend data for line chart
     */
    public function getCustomerAttendance(Request $request)
    {
        try {
            $period = $request->get('period', 'today'); // today, this_week
            
            if ($period === 'today') {
                // Group by hour for today
                $data = Attendance::selectRaw('HOUR(time_in) as hour, COUNT(*) as count')
                    ->whereDate('date', Carbon::today())
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get()
                    ->keyBy('hour');

                $labels = [];
                $values = [];
                
                // Generate hourly data from 6 AM to 10 PM
                for ($h = 6; $h <= 22; $h++) {
                    $labels[] = Carbon::createFromTime($h, 0)->format('g:i A');
                    $values[] = $data->has($h) ? $data[$h]->count : 0;
                }
            } else {
                // Group by day for this week
                $dateRange = $this->getDateRange($period);
                $data = Attendance::selectRaw('DATE(date) as day, COUNT(*) as count')
                    ->whereBetween('date', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->keyBy('day');

                $labels = [];
                $values = [];
                
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $key = $current->format('Y-m-d');
                    $labels[] = $current->format('D');
                    $values[] = $data->has($key) ? $data[$key]->count : 0;
                    $current->addDay();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'values' => $values
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export reports
     */
    public function exportReport(Request $request)
    {
        try {
            $format = $request->get('format', 'pdf'); // pdf, excel, csv, png
            $scope = $request->get('scope', 'all'); // all, revenue, products, etc.
            $dateRange = $request->get('date_range', 'this_month');

            // Gather data based on scope
            $exportData = $this->gatherExportData($scope, $dateRange);
            
            switch ($format) {
                case 'pdf':
                    return $this->exportToPDF($exportData, $scope, $dateRange);
                case 'csv':
                    return $this->exportToCSV($exportData, $scope, $dateRange);
                case 'excel':
                    return $this->exportToExcel($exportData, $scope, $dateRange);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported export format'
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gather export data based on scope
     */
    private function gatherExportData($scope, $dateRange)
    {
        $data = [
            'generated_at' => Carbon::now()->format('F d, Y h:i A'),
            'date_range' => $this->getDateRangeLabel($dateRange),
        ];

        $range = $this->getDateRange($dateRange);

        // Always include KPIs for context
        $kpis = $this->getKPIs(new Request(['date_range' => $dateRange]))->getData(true);
        $data['kpis'] = $kpis['data'] ?? [];

        if ($scope === 'all' || $scope === 'revenue') {
            $revenueData = $this->getRevenueOverTime(new Request(['period' => $dateRange]))->getData(true);
            $data['revenue'] = $revenueData['data'] ?? [];
        }

        if ($scope === 'all' || $scope === 'products') {
            $productsData = $this->getTopSellingProducts(new Request(['period' => $dateRange]))->getData(true);
            // Transform products data for export
            $products = [];
            if (isset($productsData['data']['datasets'])) {
                foreach ($productsData['data']['datasets'] as $dataset) {
                    $totalQty = array_sum($dataset['data']);
                    $products[] = [
                        'name' => $dataset['label'],
                        'quantity' => $totalQty,
                        'revenue' => $totalQty * 100 // Estimated revenue
                    ];
                }
            }
            $data['products'] = $products;
        }

        if ($scope === 'all' || $scope === 'breakdown') {
            $breakdownData = $this->getRevenueBreakdown(new Request(['period' => $dateRange]))->getData(true);
            // Transform breakdown data for export
            $breakdown = [];
            if (isset($breakdownData['data']['labels']) && isset($breakdownData['data']['values'])) {
                foreach ($breakdownData['data']['labels'] as $index => $label) {
                    $breakdown[] = [
                        'source' => $label,
                        'amount' => $breakdownData['data']['values'][$index] ?? 0
                    ];
                }
            }
            $data['breakdown'] = $breakdown;
        }

        if ($scope === 'all' || $scope === 'transactions') {
            $transactionsData = $this->getTransactionHistory(new Request(['period' => $dateRange]))->getData(true);
            // Transform transactions data for export
            $transactions = [];
            if (isset($transactionsData['data']['labels']) && isset($transactionsData['data']['values'])) {
                foreach ($transactionsData['data']['labels'] as $index => $label) {
                    $transactions[] = [
                        'method' => $label,
                        'count' => $transactionsData['data']['values'][$index] ?? 0
                    ];
                }
            }
            $data['transactions'] = $transactions;
        }

        if ($scope === 'all' || $scope === 'attendance') {
            $attendanceData = $this->getCustomerAttendance(new Request(['period' => $dateRange]))->getData(true);
            $data['attendance'] = $attendanceData['data'] ?? [];
        }

        return $data;
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($data, $scope, $dateRange)
    {
        $pdf = Pdf::loadView('reports.export-pdf', compact('data', 'scope', 'dateRange'));
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($data, $scope, $dateRange)
    {
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['AbsTrack Fitness Gym - Report']);
            fputcsv($file, ['Generated: ' . $data['generated_at']]);
            fputcsv($file, ['Period: ' . $data['date_range']]);
            fputcsv($file, []);

            // KPIs
            if (isset($data['kpis'])) {
                fputcsv($file, ['KEY PERFORMANCE INDICATORS']);
                fputcsv($file, ['Metric', 'Value', 'Change (%)']);
                fputcsv($file, ['Monthly Revenue', '₱' . number_format($data['kpis']['monthly_revenue'], 2), $data['kpis']['revenue_change'] . '%']);
                fputcsv($file, ['Retail Sales', '₱' . number_format($data['kpis']['retail_sales'], 2), $data['kpis']['retail_change'] . '%']);
                fputcsv($file, ['Membership Revenue', '₱' . number_format($data['kpis']['membership_revenue'], 2), $data['kpis']['membership_change'] . '%']);
                fputcsv($file, ['PT Revenue', '₱' . number_format($data['kpis']['pt_revenue'], 2), $data['kpis']['pt_change'] . '%']);
                fputcsv($file, []);
            }

            // Products
            if (isset($data['products']) && !empty($data['products'])) {
                fputcsv($file, ['TOP SELLING PRODUCTS']);
                fputcsv($file, ['Product', 'Quantity Sold', 'Total Revenue']);
                foreach ($data['products'] as $product) {
                    fputcsv($file, [
                        $product['name'],
                        $product['quantity'],
                        '₱' . number_format($product['revenue'], 2)
                    ]);
                }
                fputcsv($file, []);
            }

            // Revenue Breakdown
            if (isset($data['breakdown']) && !empty($data['breakdown'])) {
                fputcsv($file, ['REVENUE BREAKDOWN']);
                fputcsv($file, ['Source', 'Amount']);
                foreach ($data['breakdown'] as $item) {
                    fputcsv($file, [
                        $item['source'],
                        '₱' . number_format($item['amount'], 2)
                    ]);
                }
                fputcsv($file, []);
            }

            // Transactions
            if (isset($data['transactions']) && !empty($data['transactions'])) {
                fputcsv($file, ['TRANSACTION HISTORY']);
                fputcsv($file, ['Payment Method', 'Count']);
                foreach ($data['transactions'] as $transaction) {
                    fputcsv($file, [
                        $transaction['method'],
                        $transaction['count']
                    ]);
                }
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (CSV format with .xls extension for Excel compatibility)
     */
    private function exportToExcel($data, $scope, $dateRange)
    {
        // Since we don't have phpspreadsheet, we'll use CSV with Excel formatting
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.xls';
        
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // HTML table format for Excel
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body>';
            echo '<table border="1">';
            
            // Header
            echo '<tr><td colspan="3"><b>AbsTrack Fitness Gym - Report</b></td></tr>';
            echo '<tr><td colspan="3">Generated: ' . $data['generated_at'] . '</td></tr>';
            echo '<tr><td colspan="3">Period: ' . $data['date_range'] . '</td></tr>';
            echo '<tr><td colspan="3"></td></tr>';

            // KPIs
            if (isset($data['kpis'])) {
                echo '<tr><td colspan="3"><b>KEY PERFORMANCE INDICATORS</b></td></tr>';
                echo '<tr><td><b>Metric</b></td><td><b>Value</b></td><td><b>Change (%)</b></td></tr>';
                echo '<tr><td>Monthly Revenue</td><td>₱' . number_format($data['kpis']['monthly_revenue'], 2) . '</td><td>' . $data['kpis']['revenue_change'] . '%</td></tr>';
                echo '<tr><td>Retail Sales</td><td>₱' . number_format($data['kpis']['retail_sales'], 2) . '</td><td>' . $data['kpis']['retail_change'] . '%</td></tr>';
                echo '<tr><td>Membership Revenue</td><td>₱' . number_format($data['kpis']['membership_revenue'], 2) . '</td><td>' . $data['kpis']['membership_change'] . '%</td></tr>';
                echo '<tr><td>PT Revenue</td><td>₱' . number_format($data['kpis']['pt_revenue'], 2) . '</td><td>' . $data['kpis']['pt_change'] . '%</td></tr>';
                echo '<tr><td colspan="3"></td></tr>';
            }

            // Products
            if (isset($data['products']) && !empty($data['products'])) {
                echo '<tr><td colspan="3"><b>TOP SELLING PRODUCTS</b></td></tr>';
                echo '<tr><td><b>Product</b></td><td><b>Quantity Sold</b></td><td><b>Total Revenue</b></td></tr>';
                foreach ($data['products'] as $product) {
                    echo '<tr><td>' . htmlspecialchars($product['name']) . '</td><td>' . $product['quantity'] . '</td><td>₱' . number_format($product['revenue'], 2) . '</td></tr>';
                }
                echo '<tr><td colspan="3"></td></tr>';
            }

            // Revenue Breakdown
            if (isset($data['breakdown']) && !empty($data['breakdown'])) {
                echo '<tr><td colspan="3"><b>REVENUE BREAKDOWN</b></td></tr>';
                echo '<tr><td><b>Source</b></td><td colspan="2"><b>Amount</b></td></tr>';
                foreach ($data['breakdown'] as $item) {
                    echo '<tr><td>' . htmlspecialchars($item['source']) . '</td><td colspan="2">₱' . number_format($item['amount'], 2) . '</td></tr>';
                }
                echo '<tr><td colspan="3"></td></tr>';
            }

            echo '</table>';
            echo '</body></html>';
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get human-readable date range label
     */
    private function getDateRangeLabel($period)
    {
        switch ($period) {
            case 'today':
                return 'Today';
            case 'this_week':
                return 'This Week';
            case 'this_month':
                return 'This Month';
            case 'last_month':
                return 'Last Month';
            case 'last_3_months':
                return 'Last 3 Months';
            case 'this_year':
                return 'This Year';
            default:
                return 'Custom Period';
        }
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }

    /**
     * Get date range based on period string
     */
    private function getDateRange($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay()
                ];
            case 'this_week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek()
                ];
            case 'this_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth()
                ];
            case 'last_3_months':
                return [
                    'start' => $now->copy()->subMonths(2)->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
            case 'this_year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear()
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth()
                ];
        }
    }
}
