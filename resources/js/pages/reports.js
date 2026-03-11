/**
 * Reports Page Module
 * Page-specific JavaScript for ReportAndBilling/ReportAndBilling.blade.php
 * Handles Chart.js configurations for analytics/reporting with API integration.
 *
 * Architecture:
 *  - Chart creation functions build initial placeholder charts
 *  - API fetch functions pull real data and update charts dynamically
 *  - Filter listeners trigger re-fetches per chart
 *  - Export function creates a hidden form POST for file download
 */

const ReportsPage = (function () {
  'use strict';

  // ───────────────────────────── State ─────────────────────────────

  /** @type {Object.<string, Chart>} Active Chart.js instances */
  let charts = {};

  /** Current filter period per chart */
  let currentFilters = {
    revenueOverTime:    'this_year',
    topSelling:         'this_week',
    revenueBreakdown:   'this_month',
    transactionHistory: 'this_month',
    attendance:         'today'
  };

  // ───────────────────────────── Config ────────────────────────────

  const COMMON_CONFIG = {
    colors: {
      text:     '#8b92a7',
      gridLine: 'rgba(255, 255, 255, 0.05)',
      border:   'rgba(255, 255, 255, 0.1)',
      tooltip: {
        background: 'rgba(42, 48, 56, 0.95)',
        title:      '#fff',
        body:       '#8b92a7',
        border:     '#3a4048'
      }
    },
    brandColors: {
      orange: '#FFA726',
      green:  '#66BB6A',
      blue:   '#42A5F5',
      purple: '#AB47BC',
      cyan:   '#26C6DA'
    }
  };

  // ──────────────────── Shared Chart Helpers ───────────────────────

  /** Common tooltip appearance */
  function getTooltipConfig() {
    return {
      backgroundColor: COMMON_CONFIG.colors.tooltip.background,
      padding:     12,
      titleColor:  COMMON_CONFIG.colors.tooltip.title,
      bodyColor:   COMMON_CONFIG.colors.tooltip.body,
      borderColor: COMMON_CONFIG.colors.tooltip.border,
      borderWidth: 1
    };
  }

  /** Common legend appearance */
  function getLegendConfig(position = 'bottom') {
    return {
      display: true,
      position: position,
      labels: { color: '#fff', usePointStyle: true, padding: 15, font: { size: 12 } }
    };
  }

  /** Common Y-axis */
  function getYAxisConfig() {
    return {
      beginAtZero: true,
      grid:  { color: COMMON_CONFIG.colors.gridLine, drawBorder: false },
      ticks: { color: COMMON_CONFIG.colors.text, font: { size: 11 } }
    };
  }

  /** Common X-axis */
  function getXAxisConfig(showGrid = true) {
    return {
      grid: showGrid
        ? { color: COMMON_CONFIG.colors.gridLine, drawBorder: false }
        : { display: false },
      ticks: { color: COMMON_CONFIG.colors.text, font: { size: 11 } }
    };
  }

  /** Apply global Chart.js defaults */
  function applyChartDefaults() {
    if (typeof Chart === 'undefined') { console.error('Chart.js is not loaded'); return; }
    Chart.defaults.color       = COMMON_CONFIG.colors.text;
    Chart.defaults.borderColor = COMMON_CONFIG.colors.border;
  }

  // ────────────────────── Chart Creators ───────────────────────────

  /**
   * Revenue Over Time – multi-line chart (Retail / Membership / PT).
   * Supports both monthly and daily labels dynamically.
   */
  function createRevenueOverTimeChart(canvasId, data) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const lineColors = [
      { border: COMMON_CONFIG.brandColors.orange, bg: 'rgba(255, 167, 38, 0.1)' },
      { border: COMMON_CONFIG.brandColors.green,  bg: 'rgba(102, 187, 106, 0.1)' },
      { border: COMMON_CONFIG.brandColors.blue,   bg: 'rgba(66, 165, 245, 0.1)' }
    ];

    const chartData = data || {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [
        { label: 'Retail',            data: [0,0,0,0,0,0] },
        { label: 'Membership',        data: [0,0,0,0,0,0] },
        { label: 'Personal Training', data: [0,0,0,0,0,0] }
      ]
    };

    // Apply styling to each dataset
    chartData.datasets.forEach((ds, i) => {
      const c = lineColors[i] || lineColors[0];
      ds.borderColor     = ds.borderColor || c.border;
      ds.backgroundColor = ds.backgroundColor || c.bg;
      ds.tension     = ds.tension ?? 0.4;
      ds.borderWidth = ds.borderWidth ?? 2;
    });

    charts.revenueOverTime = new Chart(ctx, {
      type: 'line',
      data: chartData,
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: getLegendConfig('bottom'), tooltip: getTooltipConfig() },
        scales:  { y: getYAxisConfig(), x: getXAxisConfig() }
      }
    });
    return charts.revenueOverTime;
  }

  /**
   * Top Selling Products – grouped bar chart.
   * Datasets are rebuilt entirely from API data (variable product count).
   */
  function createTopSellingChart(canvasId, data) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: []
    };

    // Ensure borderRadius on each dataset
    chartData.datasets.forEach(ds => { ds.borderRadius = ds.borderRadius ?? 4; });

    charts.topSelling = new Chart(ctx, {
      type: 'bar',
      data: chartData,
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: getLegendConfig('bottom'), tooltip: getTooltipConfig() },
        scales:  { y: getYAxisConfig(), x: getXAxisConfig(false) }
      }
    });
    return charts.topSelling;
  }

  /**
   * Revenue Breakdown – doughnut chart.
   */
  function createRevenueBreakdownChart(canvasId, data) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Retail Sales', 'Membership', 'Personal Training'],
      datasets: [{
        data: [0, 0, 0],
        backgroundColor: [
          COMMON_CONFIG.brandColors.blue, COMMON_CONFIG.brandColors.green,
          COMMON_CONFIG.brandColors.orange
        ],
        borderWidth: 0, hoverOffset: 4
      }]
    };

    charts.revenueBreakdown = new Chart(ctx, {
      type: 'doughnut',
      data: chartData,
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: getLegendConfig('right'), tooltip: getTooltipConfig() }
      }
    });
    return charts.revenueBreakdown;
  }

  /**
   * Transaction History – doughnut (pie-style) chart.
   */
  function createTransactionHistoryChart(canvasId, data) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Cash', 'GCash'],
      datasets: [{
        data: [0, 0],
        backgroundColor: [COMMON_CONFIG.brandColors.blue, COMMON_CONFIG.brandColors.green],
        borderWidth: 0, hoverOffset: 4
      }]
    };

    charts.transactionHistory = new Chart(ctx, {
      type: 'doughnut',
      data: chartData,
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: getLegendConfig('right'), tooltip: getTooltipConfig() }
      }
    });
    return charts.transactionHistory;
  }

  /**
   * Customer Attendance – area line chart.
   */
  function createCustomerAttendanceChart(canvasId, data) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['6:00 AM', '9:00 AM', '12:00 PM', '3:00 PM', '6:00 PM', '9:00 PM'],
      datasets: [{
        label: 'Check-ins', data: [0, 0, 0, 0, 0, 0],
        borderColor: COMMON_CONFIG.brandColors.blue,
        backgroundColor: 'rgba(66, 165, 245, 0.1)',
        tension: 0.4, fill: true, borderWidth: 2,
        pointRadius: 4, pointBackgroundColor: COMMON_CONFIG.brandColors.blue,
        pointBorderColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 6
      }]
    };

    charts.customerAttendance = new Chart(ctx, {
      type: 'line',
      data: chartData,
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: getLegendConfig('bottom'), tooltip: getTooltipConfig() },
        scales:  { y: getYAxisConfig(), x: getXAxisConfig() }
      }
    });
    return charts.customerAttendance;
  }

  // ──────────────────────── Init ──────────────────────────────────

  /**
   * Initialize all charts and kick off API data fetches.
   * @param {Object} options - Optional pre-loaded data per chart
   */
  function init(options = {}) {
    applyChartDefaults();

    // Load KPIs
    loadKPIs();

    // Create charts with placeholder data first
    createRevenueOverTimeChart('revenueOverTimeChart', options.revenueData || null);
    createTopSellingChart('topSellingProductsChart', options.topSellingData || null);
    createRevenueBreakdownChart('revenueBreakdownChart', options.breakdownData || null);
    createTransactionHistoryChart('transactionHistoryChart', options.transactionData || null);
    createCustomerAttendanceChart('customerAttendanceChart', options.attendanceData || null);

    // Fetch real data from API (all in parallel)
    fetchAllChartData();

    // Bind filter dropdowns
    setupFilterListeners();
  }

  // ────────────────────── KPI Loading ─────────────────────────────

  /** Fetch KPI data and update the 4 stat cards. */
  async function loadKPIs() {
    try {
      const response = await fetch('/reports-analytics/kpis');
      const result = await response.json();
      
      if (result.success) {
        updateKPIDisplay(result.data);
      }
    } catch (error) {
      console.error('Error loading KPIs:', error);
    }
  }

  /** Update KPI DOM elements with values. */
  function updateKPIDisplay(data) {
    // Update values with abbreviated formatting
    document.getElementById('kpi_monthly_revenue').textContent = formatKPICurrency(data.monthly_revenue);
    document.getElementById('kpi_retail_sales').textContent = formatKPICurrency(data.retail_sales);
    document.getElementById('kpi_membership_revenue').textContent = formatKPICurrency(data.membership_revenue);
    document.getElementById('kpi_pt_revenue').textContent = formatKPICurrency(data.pt_revenue);
    
    // Update badges
    updateKPIBadge('kpi_revenue_badge', 'kpi_revenue_icon', data.revenue_change);
    updateKPIBadge('kpi_retail_badge', 'kpi_retail_icon', data.retail_change);
    updateKPIBadge('kpi_membership_badge', 'kpi_membership_icon', data.membership_change);
    updateKPIBadge('kpi_pt_badge',         'kpi_pt_icon',         data.pt_change);
  }

  /** Style a KPI badge as positive/negative. */
  function updateKPIBadge(badgeId, iconId, value) {
    const badge = document.getElementById(badgeId);
    const icon  = document.getElementById(iconId);
    if (!badge || !icon) return;

    const positive = value >= 0;
    badge.textContent = (positive ? '+' : '') + value.toFixed(1) + '%';
    badge.className   = 'badge badge-' + (positive ? 'success' : 'danger');

    const span = icon.querySelector('span');
    if (span) span.className = 'mdi mdi-arrow-' + (positive ? 'top-right' : 'bottom-right');
    icon.className = 'icon-box ' + (positive ? 'text-success' : 'text-danger');
  }

  // ────────────────────── Data Fetchers ───────────────────────────

  /** Fetch all chart data in parallel. */
  async function fetchAllChartData() {
    await Promise.all([
      fetchRevenueOverTime(),
      fetchTopSelling(),
      fetchRevenueBreakdown(),
      fetchTransactionHistory(),
      fetchAttendance()
    ]);
  }

  /**
   * Revenue Over Time – rebuild datasets dynamically so the
   * chart works whether labels are months or days.
   */
  async function fetchRevenueOverTime() {
    try {
      const response = await fetch('/reports-analytics/revenue-over-time?period=' + currentFilters.revenueOverTime);
      const result = await response.json();
      
      if (result.success && charts.revenueOverTime) {
        const d = result.data;
        const lineColors = [
          { border: COMMON_CONFIG.brandColors.orange, bg: 'rgba(255, 167, 38, 0.1)' },
          { border: COMMON_CONFIG.brandColors.green,  bg: 'rgba(102, 187, 106, 0.1)' },
          { border: COMMON_CONFIG.brandColors.blue,   bg: 'rgba(66, 165, 245, 0.1)' }
        ];

        charts.revenueOverTime.data.labels = d.labels;

        // Rebuild datasets array to match API response length
        charts.revenueOverTime.data.datasets = d.datasets.map((ds, i) => {
          const c = lineColors[i] || lineColors[0];
          return {
            label:           ds.label,
            data:            ds.data,
            borderColor:     c.border,
            backgroundColor: c.bg,
            tension:     0.4,
            borderWidth: 2
          };
        });

        charts.revenueOverTime.update();
      }
    } catch (err) {
      console.error('Error fetching revenue over time:', err);
    }
  }

  /**
   * Top Selling Products – fully replace datasets since the
   * number of products may change between filter periods.
   */
  async function fetchTopSelling() {
    try {
      const response = await fetch('/reports-analytics/top-selling?period=' + currentFilters.topSelling);
      const result = await response.json();
      
      if (result.success && charts.topSelling) {
        const d = result.data;
        charts.topSelling.data.labels = d.labels;

        // Apply borderRadius to each dataset coming from the API
        charts.topSelling.data.datasets = (d.datasets || []).map(ds => ({
          ...ds,
          borderRadius: ds.borderRadius ?? 4
        }));

        charts.topSelling.update();
      }
    } catch (err) {
      console.error('Error fetching top selling:', err);
    }
  }

  /** Revenue Breakdown – update donut slices + custom legend. */
  async function fetchRevenueBreakdown() {
    try {
      const response = await fetch('/reports-analytics/revenue-breakdown?period=' + currentFilters.revenueBreakdown);
      const result = await response.json();
      
      if (result.success && charts.revenueBreakdown) {
        const d = result.data;
        charts.revenueBreakdown.data.labels                     = d.labels;
        charts.revenueBreakdown.data.datasets[0].data            = d.values;
        charts.revenueBreakdown.data.datasets[0].backgroundColor = d.colors;
        charts.revenueBreakdown.update();

        updateRevenueBreakdownLegend(d);
      }
    } catch (err) {
      console.error('Error fetching revenue breakdown:', err);
    }
  }

  /** Build HTML for the breakdown side-legend. */
  function updateRevenueBreakdownLegend(data) {
    const el = document.getElementById('revenueBreakdownLegend');
    if (!el) return;

    const fmt = (v) => '\u20b1' + parseFloat(v).toLocaleString('en-PH', { minimumFractionDigits: 2 });

    el.innerHTML = data.labels.map((label, i) =>
      '<div class="legend-item mb-2">' +
        '<span class="legend-color" style="background:' + data.colors[i] + '"></span>' +
        '<span class="legend-label">' + label + '</span>' +
        '<span class="legend-value">' + fmt(data.values[i]) + '</span>' +
      '</div>'
    ).join('');
  }

  /** Transaction History – update donut slices + custom legend. */
  async function fetchTransactionHistory() {
    try {
      const response = await fetch('/reports-analytics/transaction-history?period=' + currentFilters.transactionHistory);
      const result = await response.json();
      
      if (result.success && charts.transactionHistory) {
        const d = result.data;
        charts.transactionHistory.data.labels                     = d.labels;
        charts.transactionHistory.data.datasets[0].data            = d.values;
        charts.transactionHistory.data.datasets[0].backgroundColor = d.colors;
        charts.transactionHistory.update();

        updateTransactionLegend(d);
      }
    } catch (err) {
      console.error('Error fetching transaction history:', err);
    }
  }

  /** Build HTML for the transaction side-legend. */
  function updateTransactionLegend(data) {
    const el = document.getElementById('transactionHistoryLegend');
    if (!el) return;

    const fmt = (v) => '\u20b1' + parseFloat(v).toLocaleString('en-PH', { minimumFractionDigits: 2 });

    el.innerHTML = data.labels.map((label, i) =>
      '<div class="legend-item mb-2">' +
        '<span class="legend-color" style="background:' + data.colors[i] + '"></span>' +
        '<span class="legend-label">' + label + '</span>' +
        '<span class="legend-value">' + fmt(data.values[i]) + '</span>' +
      '</div>'
    ).join('');
  }

  /** Customer Attendance – update line chart data. */
  async function fetchAttendance() {
    try {
      const response = await fetch('/reports-analytics/attendance-trend?period=' + currentFilters.attendance);
      const result = await response.json();
      
      if (result.success && charts.customerAttendance) {
        charts.customerAttendance.data.labels           = result.data.labels;
        charts.customerAttendance.data.datasets[0].data = result.data.values;
        charts.customerAttendance.update();
      }
    } catch (err) {
      console.error('Error fetching attendance:', err);
    }
  }

  // ────────────────────── Filter Listeners ─────────────────────────

  /** Bind click handlers to .filter-option dropdown items. */
  function setupFilterListeners() {
    document.querySelectorAll('.filter-option').forEach(option => {
      option.addEventListener('click', function (e) {
        e.preventDefault();

        const chart  = this.dataset.chart;
        const period = this.dataset.period;

        // Toggle active class within the same dropdown
        this.closest('.filter-dropdown')
          .querySelectorAll('.filter-option')
          .forEach(o => o.classList.remove('active'));
        this.classList.add('active');

        // Update state and re-fetch
        currentFilters[chart] = period;

        const fetchMap = {
          revenueOverTime:    fetchRevenueOverTime,
          topSelling:         fetchTopSelling,
          revenueBreakdown:   fetchRevenueBreakdown,
          transactionHistory: fetchTransactionHistory,
          attendance:         fetchAttendance
        };

        if (fetchMap[chart]) fetchMap[chart]();
      });
    });
  }

  // ────────────────────── Export ───────────────────────────────────

  /**
   * Trigger a report export via hidden form POST.
   * Supported formats: PDF, CSV, Excel.  PNG is rejected server-side.
   */
  async function exportReport() {
    const format    = document.querySelector('input[name="export_format"]:checked')?.value || 'pdf';
    const dateRange = document.querySelector('select[name="export_date_range"]')?.value || 'this_month';
    const scope     = document.querySelector('select[name="export_scope"]')?.value || 'all';

    const btn = document.querySelector('.modal-footer .btn-warning');
    const originalText = btn ? btn.innerHTML : 'Export';

    try {
      // Loading state
      if (btn) { btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Exporting...'; btn.disabled = true; }

      // Build hidden form
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/reports-analytics/export';
      form.style.display = 'none';

      const addField = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = name; input.value = value;
        form.appendChild(input);
      };

      addField('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
      addField('format', format);
      addField('date_range', dateRange);
      addField('scope', scope);

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);

      // Reset UI after short delay (file download is async)
      setTimeout(() => {
        showToast('Report export started. Download will begin shortly.', 'success');
        if (typeof $ !== 'undefined') $('#exportReportModal').modal('hide');
        if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
      }, 500);
    } catch (err) {
      console.error('Export error:', err);
      showToast('Export failed. Please try again.', 'error');
      if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
    }
  }

  // ────────────────────── Utilities ───────────────────────────────

  /**
   * Show a toast notification (SweetAlert2 if available, else alert).
   */
  function showToast(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        toast: true, position: 'top-end',
        icon: type === 'error' ? 'error' : type === 'success' ? 'success' : 'info',
        title: message, showConfirmButton: false, timer: 3000
      });
    } else {
      ToastUtils.showInfo(message);
    }
  }

  /** Get a chart instance by key name. */
  function getChart(name) { return charts[name]; }

  /** Replace a chart's data and re-render. */
  function updateChartData(name, data) {
    if (charts[name]) { charts[name].data = data; charts[name].update(); }
  }

  // ────────────────────── Public API ──────────────────────────────

  return {
    init,
    COMMON_CONFIG,
    getChart,
    updateChartData,
    createRevenueOverTimeChart,
    createTopSellingChart,
    createRevenueBreakdownChart,
    createTransactionHistoryChart,
    createCustomerAttendanceChart,
    loadKPIs,
    fetchAllChartData,
    exportReport,
    showToast
  };
})();

// CommonJS export (if bundled)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ReportsPage;
}

// Global access for inline <script> calls
window.ReportsPage = ReportsPage;
