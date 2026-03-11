<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * ReportController
 *
 * Thin controller that delegates all data aggregation to ReportService.
 * Responsible only for HTTP concerns: request parsing, JSON responses,
 * view rendering, and file download responses.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    // ----------------------------------------------------------------
    // Page
    // ----------------------------------------------------------------

    /**
     * Display the reports & analytics page.
     */
    public function index()
    {
        return view('ReportAndBilling.ReportAndBilling');
    }

    // ----------------------------------------------------------------
    // API Endpoints (JSON)
    // ----------------------------------------------------------------

    /**
     * GET /reports/kpis
     * Return KPI card data for the current month.
     */
    public function getKPIs(Request $request)
    {
        try {
            $month = $request->get('month', Carbon::now()->month);
            $year  = $request->get('year', Carbon::now()->year);

            $data = $this->reportService->getKPIs((int) $month, (int) $year);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch KPI data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /reports/revenue-over-time?period=this_year
     * Return revenue line-chart data (monthly or daily).
     */
    public function getRevenueOverTime(Request $request)
    {
        try {
            $period = $request->get('period', 'this_year');
            $data   = $this->reportService->getRevenueOverTime($period);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /reports/top-selling?period=this_week
     * Return top-selling products bar-chart data.
     */
    public function getTopSellingProducts(Request $request)
    {
        try {
            $period = $request->get('period', 'this_week');
            $data   = $this->reportService->getTopSellingProducts($period);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /reports/revenue-breakdown?period=this_month
     * Return revenue donut-chart data.
     */
    public function getRevenueBreakdown(Request $request)
    {
        try {
            $period = $request->get('period', 'this_month');
            $data   = $this->reportService->getRevenueBreakdown($period);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch breakdown data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /reports/transaction-history?period=this_month
     * Return transaction pie-chart data grouped by payment method.
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $period = $request->get('period', 'this_month');
            $data   = $this->reportService->getTransactionHistory($period);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /reports/attendance-trend?period=today
     * Return attendance line-chart data (hourly or daily).
     */
    public function getCustomerAttendance(Request $request)
    {
        try {
            $period = $request->get('period', 'today');
            $data   = $this->reportService->getCustomerAttendance($period);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendance data: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ----------------------------------------------------------------
    // Export
    // ----------------------------------------------------------------

    /**
     * POST /reports/export
     * Download a report file in PDF, CSV, or Excel format.
     * Rejects unsupported formats (e.g. PNG) with an error message.
     */
    public function exportReport(Request $request)
    {
        try {
            $format    = $request->get('format', 'pdf');
            $scope     = $request->get('scope', 'all');
            $dateRange = $request->get('date_range', 'this_month');

            // Reject unsupported formats
            if (! $this->reportService->isSupportedExportFormat($format)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported export format.',
                ], 400);
            }

            $exportData = $this->reportService->gatherExportData($scope, $dateRange);

            return match ($format) {
                'pdf'   => $this->exportToPDF($exportData, $scope, $dateRange),
                'csv'   => $this->exportToCSV($exportData, $scope),
                'excel' => $this->exportToExcel($exportData, $scope),
            };
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ----------------------------------------------------------------
    // Private export builders
    // ----------------------------------------------------------------

    /**
     * Generate and download a PDF report via dompdf.
     */
    private function exportToPDF(array $data, string $scope, string $dateRange)
    {
        $pdf      = Pdf::loadView('reports.export-pdf', compact('data', 'scope', 'dateRange'));
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream a CSV download.
     */
    private function exportToCSV(array $data, string $scope)
    {
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

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

            // Revenue Over Time
            if (isset($data['revenue']['labels'])) {
                fputcsv($file, ['REVENUE OVER TIME']);
                fputcsv($file, ['Period', 'Retail', 'Membership', 'Personal Training', 'Total']);
                $labels = $data['revenue']['labels'];
                $retail = $data['revenue']['datasets'][0]['data'] ?? [];
                $mem    = $data['revenue']['datasets'][1]['data'] ?? [];
                $pt     = $data['revenue']['datasets'][2]['data'] ?? [];
                foreach ($labels as $i => $label) {
                    $r = $retail[$i] ?? 0;
                    $m = $mem[$i] ?? 0;
                    $p = $pt[$i] ?? 0;
                    fputcsv($file, [$label, '₱' . number_format($r, 2), '₱' . number_format($m, 2), '₱' . number_format($p, 2), '₱' . number_format($r + $m + $p, 2)]);
                }
                fputcsv($file, []);
            }

            // Products
            if (isset($data['products']) && !empty($data['products'])) {
                fputcsv($file, ['TOP SELLING PRODUCTS']);
                fputcsv($file, ['Product', 'Quantity Sold', 'Total Revenue']);
                foreach ($data['products'] as $product) {
                    fputcsv($file, [$product['name'], $product['quantity'], '₱' . number_format($product['revenue'], 2)]);
                }
                fputcsv($file, []);
            }

            // Revenue Breakdown
            if (isset($data['breakdown']) && !empty($data['breakdown'])) {
                fputcsv($file, ['REVENUE BREAKDOWN']);
                fputcsv($file, ['Source', 'Amount']);
                foreach ($data['breakdown'] as $item) {
                    fputcsv($file, [$item['source'], '₱' . number_format($item['amount'], 2)]);
                }
                fputcsv($file, []);
            }

            // Transactions
            if (isset($data['transactions']) && !empty($data['transactions'])) {
                fputcsv($file, ['TRANSACTION HISTORY']);
                fputcsv($file, ['Payment Method', 'Total Amount']);
                foreach ($data['transactions'] as $tx) {
                    fputcsv($file, [$tx['method'], '₱' . number_format($tx['amount'], 2)]);
                }
                fputcsv($file, []);
            }

            // Attendance
            if (isset($data['attendance']['labels'])) {
                fputcsv($file, ['CUSTOMER ATTENDANCE']);
                fputcsv($file, ['Time / Day', 'Check-ins']);
                foreach ($data['attendance']['labels'] as $i => $label) {
                    fputcsv($file, [$label, $data['attendance']['values'][$i] ?? 0]);
                }
                fputcsv($file, []);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Stream an Excel (HTML-table .xls) download.
     */
    private function exportToExcel(array $data, string $scope)
    {
        $filename = 'report_' . $scope . '_' . date('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table border="1">';

            // Header
            echo '<tr><td colspan="5"><b>AbsTrack Fitness Gym - Report</b></td></tr>';
            echo '<tr><td colspan="5">Generated: ' . htmlspecialchars($data['generated_at']) . '</td></tr>';
            echo '<tr><td colspan="5">Period: ' . htmlspecialchars($data['date_range']) . '</td></tr>';
            echo '<tr><td colspan="5"></td></tr>';

            // KPIs
            if (isset($data['kpis'])) {
                echo '<tr><td colspan="5"><b>KEY PERFORMANCE INDICATORS</b></td></tr>';
                echo '<tr><td><b>Metric</b></td><td><b>Value</b></td><td><b>Change (%)</b></td><td colspan="2"></td></tr>';
                echo '<tr><td>Monthly Revenue</td><td>₱' . number_format($data['kpis']['monthly_revenue'], 2) . '</td><td>' . $data['kpis']['revenue_change'] . '%</td><td colspan="2"></td></tr>';
                echo '<tr><td>Retail Sales</td><td>₱' . number_format($data['kpis']['retail_sales'], 2) . '</td><td>' . $data['kpis']['retail_change'] . '%</td><td colspan="2"></td></tr>';
                echo '<tr><td>Membership Revenue</td><td>₱' . number_format($data['kpis']['membership_revenue'], 2) . '</td><td>' . $data['kpis']['membership_change'] . '%</td><td colspan="2"></td></tr>';
                echo '<tr><td>PT Revenue</td><td>₱' . number_format($data['kpis']['pt_revenue'], 2) . '</td><td>' . $data['kpis']['pt_change'] . '%</td><td colspan="2"></td></tr>';
                echo '<tr><td colspan="5"></td></tr>';
            }

            // Revenue Over Time
            if (isset($data['revenue']['labels'])) {
                echo '<tr><td colspan="5"><b>REVENUE OVER TIME</b></td></tr>';
                echo '<tr><td><b>Period</b></td><td><b>Retail</b></td><td><b>Membership</b></td><td><b>PT Revenue</b></td><td><b>Total</b></td></tr>';
                $labels = $data['revenue']['labels'];
                $retail = $data['revenue']['datasets'][0]['data'] ?? [];
                $mem    = $data['revenue']['datasets'][1]['data'] ?? [];
                $pt     = $data['revenue']['datasets'][2]['data'] ?? [];
                foreach ($labels as $i => $label) {
                    $r = $retail[$i] ?? 0;
                    $m = $mem[$i] ?? 0;
                    $p = $pt[$i] ?? 0;
                    echo '<tr><td>' . htmlspecialchars($label) . '</td><td>₱' . number_format($r, 2) . '</td><td>₱' . number_format($m, 2) . '</td><td>₱' . number_format($p, 2) . '</td><td>₱' . number_format($r + $m + $p, 2) . '</td></tr>';
                }
                echo '<tr><td colspan="5"></td></tr>';
            }

            // Products
            if (isset($data['products']) && !empty($data['products'])) {
                echo '<tr><td colspan="5"><b>TOP SELLING PRODUCTS</b></td></tr>';
                echo '<tr><td><b>Product</b></td><td><b>Quantity</b></td><td><b>Revenue</b></td><td colspan="2"></td></tr>';
                foreach ($data['products'] as $product) {
                    echo '<tr><td>' . htmlspecialchars($product['name']) . '</td><td>' . $product['quantity'] . '</td><td>₱' . number_format($product['revenue'], 2) . '</td><td colspan="2"></td></tr>';
                }
                echo '<tr><td colspan="5"></td></tr>';
            }

            // Revenue Breakdown
            if (isset($data['breakdown']) && !empty($data['breakdown'])) {
                echo '<tr><td colspan="5"><b>REVENUE BREAKDOWN</b></td></tr>';
                echo '<tr><td><b>Source</b></td><td colspan="4"><b>Amount</b></td></tr>';
                foreach ($data['breakdown'] as $item) {
                    echo '<tr><td>' . htmlspecialchars($item['source']) . '</td><td colspan="4">₱' . number_format($item['amount'], 2) . '</td></tr>';
                }
                echo '<tr><td colspan="5"></td></tr>';
            }

            // Transaction History
            if (isset($data['transactions']) && !empty($data['transactions'])) {
                echo '<tr><td colspan="5"><b>TRANSACTION HISTORY</b></td></tr>';
                echo '<tr><td><b>Payment Method</b></td><td colspan="4"><b>Total Amount</b></td></tr>';
                foreach ($data['transactions'] as $tx) {
                    echo '<tr><td>' . htmlspecialchars($tx['method']) . '</td><td colspan="4">₱' . number_format($tx['amount'], 2) . '</td></tr>';
                }
                echo '<tr><td colspan="5"></td></tr>';
            }

            // Attendance
            if (isset($data['attendance']['labels'])) {
                echo '<tr><td colspan="5"><b>CUSTOMER ATTENDANCE</b></td></tr>';
                echo '<tr><td><b>Time / Day</b></td><td colspan="4"><b>Check-ins</b></td></tr>';
                foreach ($data['attendance']['labels'] as $i => $label) {
                    echo '<tr><td>' . htmlspecialchars($label) . '</td><td colspan="4">' . ($data['attendance']['values'][$i] ?? 0) . '</td></tr>';
                }
            }

            echo '</table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
