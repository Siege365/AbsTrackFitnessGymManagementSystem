/**
 * Reports Page Module
 * Page-specific JavaScript for ReportAndBilling/ReportAndBilling.blade.php
 * Handles Chart.js configurations for analytics/reporting with API integration
 */

const ReportsPage = (function() {
  'use strict';

  // Chart instances (for later reference/updates)
  let charts = {};

  // Current filter states
  let currentFilters = {
    revenueOverTime: 'this_year',
    topSelling: 'this_week',
    revenueBreakdown: 'this_month',
    transactionHistory: 'this_month',
    attendance: 'today'
  };

  // Common chart configuration
  const COMMON_CONFIG = {
    colors: {
      text: '#8b92a7',
      gridLine: 'rgba(255, 255, 255, 0.05)',
      border: 'rgba(255, 255, 255, 0.1)',
      tooltip: {
        background: 'rgba(42, 48, 56, 0.95)',
        title: '#fff',
        body: '#8b92a7',
        border: '#3a4048'
      }
    },
    brandColors: {
      orange: '#FFA726',
      green: '#66BB6A',
      blue: '#42A5F5',
      purple: '#AB47BC',
      cyan: '#26C6DA'
    }
  };

  /**
   * Get common tooltip configuration
   * @returns {Object} Tooltip config
   */
  function getTooltipConfig() {
    return {
      backgroundColor: COMMON_CONFIG.colors.tooltip.background,
      padding: 12,
      titleColor: COMMON_CONFIG.colors.tooltip.title,
      bodyColor: COMMON_CONFIG.colors.tooltip.body,
      borderColor: COMMON_CONFIG.colors.tooltip.border,
      borderWidth: 1
    };
  }

  /**
   * Get common legend configuration
   * @param {string} position - Legend position
   * @returns {Object} Legend config
   */
  function getLegendConfig(position = 'bottom') {
    return {
      display: true,
      position: position,
      labels: {
        color: '#fff',
        usePointStyle: true,
        padding: 15,
        font: {
          size: 12
        }
      }
    };
  }

  /**
   * Get common Y-axis configuration
   * @returns {Object} Y-axis config
   */
  function getYAxisConfig() {
    return {
      beginAtZero: true,
      grid: {
        color: COMMON_CONFIG.colors.gridLine,
        drawBorder: false
      },
      ticks: {
        color: COMMON_CONFIG.colors.text,
        font: {
          size: 11
        }
      }
    };
  }

  /**
   * Get common X-axis configuration
   * @param {boolean} showGrid - Whether to show grid lines
   * @returns {Object} X-axis config
   */
  function getXAxisConfig(showGrid = true) {
    return {
      grid: showGrid ? {
        color: COMMON_CONFIG.colors.gridLine,
        drawBorder: false
      } : {
        display: false
      },
      ticks: {
        color: COMMON_CONFIG.colors.text,
        font: {
          size: 11
        }
      }
    };
  }

  /**
   * Apply global Chart.js defaults
   */
  function applyChartDefaults() {
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded');
      return;
    }
    
    Chart.defaults.color = COMMON_CONFIG.colors.text;
    Chart.defaults.borderColor = COMMON_CONFIG.colors.border;
  }

  /**
   * Create Revenue Over Time chart
   * @param {string} canvasId - Canvas element ID
   * @param {Object} data - Chart data (optional, uses sample if not provided)
   */
  function createRevenueOverTimeChart(canvasId, data = null) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['January', 'February', 'March', 'April', 'May', 'June'],
      datasets: [
        {
          label: 'Retail',
          data: [65, 75, 90, 85, 95, 110],
          borderColor: COMMON_CONFIG.brandColors.orange,
          backgroundColor: 'rgba(255, 167, 38, 0.1)',
          tension: 0.4,
          borderWidth: 2
        },
        {
          label: 'Membership',
          data: [85, 95, 105, 100, 90, 115],
          borderColor: COMMON_CONFIG.brandColors.green,
          backgroundColor: 'rgba(102, 187, 106, 0.1)',
          tension: 0.4,
          borderWidth: 2
        },
        {
          label: 'Revenue Pending',
          data: [55, 65, 75, 70, 60, 80],
          borderColor: COMMON_CONFIG.brandColors.blue,
          backgroundColor: 'rgba(66, 165, 245, 0.1)',
          tension: 0.4,
          borderWidth: 2
        }
      ]
    };

    charts.revenueOverTime = new Chart(ctx, {
      type: 'line',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: getLegendConfig('bottom'),
          tooltip: getTooltipConfig()
        },
        scales: {
          y: getYAxisConfig(),
          x: getXAxisConfig()
        }
      }
    });

    return charts.revenueOverTime;
  }

  /**
   * Create Top Selling Products chart
   * @param {string} canvasId - Canvas element ID
   * @param {Object} data - Chart data (optional)
   */
  function createTopSellingChart(canvasId, data = null) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Product A', 'Product B', 'Product C', 'Product D', 'Product E', 'Product F'],
      datasets: [
        {
          label: 'Membership',
          data: [45, 65, 75, 55, 85, 95],
          backgroundColor: COMMON_CONFIG.brandColors.orange,
          borderRadius: 4
        },
        {
          label: 'Retail (Day/Week)',
          data: [55, 75, 85, 65, 95, 70],
          backgroundColor: COMMON_CONFIG.brandColors.blue,
          borderRadius: 4
        },
        {
          label: 'Retail (6mo/1year)',
          data: [35, 55, 65, 45, 75, 85],
          backgroundColor: COMMON_CONFIG.brandColors.green,
          borderRadius: 4
        },
        {
          label: 'Membership (6MTH)',
          data: [65, 85, 95, 75, 65, 80],
          backgroundColor: COMMON_CONFIG.brandColors.purple,
          borderRadius: 4
        }
      ]
    };

    charts.topSelling = new Chart(ctx, {
      type: 'bar',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: getLegendConfig('bottom'),
          tooltip: getTooltipConfig()
        },
        scales: {
          y: getYAxisConfig(),
          x: getXAxisConfig(false)
        }
      }
    });

    return charts.topSelling;
  }

  /**
   * Create Revenue Breakdown chart (doughnut)
   * @param {string} canvasId - Canvas element ID
   * @param {Object} data - Chart data (optional)
   */
  function createRevenueBreakdownChart(canvasId, data = null) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Retail Sales', 'Membership', 'Personal Training', 'Walk-ins'],
      datasets: [{
        data: [35, 30, 20, 15],
        backgroundColor: [
          COMMON_CONFIG.brandColors.blue,
          COMMON_CONFIG.brandColors.green,
          COMMON_CONFIG.brandColors.orange,
          COMMON_CONFIG.brandColors.cyan
        ],
        borderWidth: 0,
        hoverOffset: 4
      }]
    };

    charts.revenueBreakdown = new Chart(ctx, {
      type: 'doughnut',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: getLegendConfig('right'),
          tooltip: getTooltipConfig()
        }
      }
    });

    return charts.revenueBreakdown;
  }

  /**
   * Create Transaction History chart (doughnut)
   * @param {string} canvasId - Canvas element ID
   * @param {Object} data - Chart data (optional)
   */
  function createTransactionHistoryChart(canvasId, data = null) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['Mpesa', 'Cash', 'PesaPal'],
      datasets: [{
        data: [50, 30, 20],
        backgroundColor: [
          COMMON_CONFIG.brandColors.blue,
          COMMON_CONFIG.brandColors.green,
          COMMON_CONFIG.brandColors.orange
        ],
        borderWidth: 0,
        hoverOffset: 4
      }]
    };

    charts.transactionHistory = new Chart(ctx, {
      type: 'doughnut',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: getLegendConfig('right'),
          tooltip: getTooltipConfig()
        }
      }
    });

    return charts.transactionHistory;
  }

  /**
   * Create Customer Attendance chart
   * @param {string} canvasId - Canvas element ID
   * @param {Object} data - Chart data (optional)
   */
  function createCustomerAttendanceChart(canvasId, data = null) {
    const ctx = document.getElementById(canvasId)?.getContext('2d');
    if (!ctx) return null;

    const chartData = data || {
      labels: ['9:00 AM', '12:00 PM', '3:00 PM', '6:00 PM', '9:00 PM'],
      datasets: [{
        label: 'Check-ins',
        data: [45, 52, 60, 65, 48],
        borderColor: COMMON_CONFIG.brandColors.blue,
        backgroundColor: 'rgba(66, 165, 245, 0.1)',
        tension: 0.4,
        fill: true,
        borderWidth: 2,
        pointRadius: 4,
        pointBackgroundColor: COMMON_CONFIG.brandColors.blue,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6
      }]
    };

    charts.customerAttendance = new Chart(ctx, {
      type: 'line',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: getLegendConfig('bottom'),
          tooltip: getTooltipConfig()
        },
        scales: {
          y: getYAxisConfig(),
          x: getXAxisConfig()
        }
      }
    });

    return charts.customerAttendance;
  }

  /**
   * Initialize all charts on the page
   * @param {Object} options - Configuration options with optional data
   */
  function init(options = {}) {
    applyChartDefaults();
    
    // Load KPIs
    loadKPIs();

    // Create charts with sample data first, then fetch real data
    createRevenueOverTimeChart('revenueOverTimeChart', options.revenueData);
    createTopSellingChart('topSellingProductsChart', options.topSellingData);
    createRevenueBreakdownChart('revenueBreakdownChart', options.breakdownData);
    createTransactionHistoryChart('transactionHistoryChart', options.transactionData);
    createCustomerAttendanceChart('customerAttendanceChart', options.attendanceData);

    // Fetch real data from API
    fetchAllChartData();

    // Setup filter event listeners
    setupFilterListeners();
  }

  /**
   * Load KPI data from API
   */
  async function loadKPIs() {
    try {
      const response = await fetch('/reports/kpis');
      const result = await response.json();
      
      if (result.success) {
        updateKPIDisplay(result.data);
      }
    } catch (error) {
      console.error('Error loading KPIs:', error);
    }
  }

  /**
   * Update KPI display with data
   * @param {Object} data - KPI data from API
   */
  function updateKPIDisplay(data) {
    // Format currency
    const formatCurrency = (value) => '₱' + parseFloat(value).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    
    // Update values
    document.getElementById('kpi_monthly_revenue').textContent = formatCurrency(data.monthly_revenue);
    document.getElementById('kpi_retail_sales').textContent = formatCurrency(data.retail_sales);
    document.getElementById('kpi_membership_revenue').textContent = formatCurrency(data.membership_revenue);
    document.getElementById('kpi_pt_revenue').textContent = formatCurrency(data.pt_revenue);
    
    // Update badges
    updateKPIBadge('kpi_revenue_badge', 'kpi_revenue_icon', data.revenue_change);
    updateKPIBadge('kpi_retail_badge', 'kpi_retail_icon', data.retail_change);
    updateKPIBadge('kpi_membership_badge', 'kpi_membership_icon', data.membership_change);
    updateKPIBadge('kpi_pt_badge', 'kpi_pt_icon', data.pt_change);
  }

  /**
   * Update KPI badge styling based on value
   * @param {string} badgeId - Badge element ID
   * @param {string} iconId - Icon element ID
   * @param {number} value - Percentage change value
   */
  function updateKPIBadge(badgeId, iconId, value) {
    const badge = document.getElementById(badgeId);
    const icon = document.getElementById(iconId);
    
    if (!badge || !icon) return;
    
    const isPositive = value >= 0;
    const sign = isPositive ? '+' : '';
    
    badge.textContent = sign + value.toFixed(1) + '%';
    badge.className = 'badge badge-' + (isPositive ? 'success' : 'danger');
    
    const iconSpan = icon.querySelector('span');
    if (iconSpan) {
      iconSpan.className = 'mdi mdi-arrow-' + (isPositive ? 'top-right' : 'bottom-right');
    }
    icon.className = 'icon-box ' + (isPositive ? 'text-success' : 'text-danger');
  }

  /**
   * Fetch all chart data from API
   */
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
   * Fetch revenue over time data
   */
  async function fetchRevenueOverTime() {
    try {
      const response = await fetch('/reports/revenue-over-time?period=' + currentFilters.revenueOverTime);
      const result = await response.json();
      
      if (result.success && charts.revenueOverTime) {
        const data = result.data;
        charts.revenueOverTime.data.labels = data.labels;
        charts.revenueOverTime.data.datasets[0].data = data.datasets[0].data;
        charts.revenueOverTime.data.datasets[0].label = data.datasets[0].label;
        charts.revenueOverTime.data.datasets[1].data = data.datasets[1].data;
        charts.revenueOverTime.data.datasets[1].label = data.datasets[1].label;
        charts.revenueOverTime.data.datasets[2].data = data.datasets[2].data;
        charts.revenueOverTime.data.datasets[2].label = data.datasets[2].label;
        charts.revenueOverTime.update();
      }
    } catch (error) {
      console.error('Error fetching revenue over time:', error);
    }
  }

  /**
   * Fetch top selling products data
   */
  async function fetchTopSelling() {
    try {
      const response = await fetch('/reports/top-selling?period=' + currentFilters.topSelling);
      const result = await response.json();
      
      if (result.success && charts.topSelling) {
        const data = result.data;
        charts.topSelling.data.labels = data.labels;
        charts.topSelling.data.datasets = data.datasets;
        charts.topSelling.update();
      }
    } catch (error) {
      console.error('Error fetching top selling:', error);
    }
  }

  /**
   * Fetch revenue breakdown data
   */
  async function fetchRevenueBreakdown() {
    try {
      const response = await fetch('/reports/revenue-breakdown?period=' + currentFilters.revenueBreakdown);
      const result = await response.json();
      
      if (result.success && charts.revenueBreakdown) {
        const data = result.data;
        charts.revenueBreakdown.data.labels = data.labels;
        charts.revenueBreakdown.data.datasets[0].data = data.values;
        charts.revenueBreakdown.data.datasets[0].backgroundColor = data.colors;
        charts.revenueBreakdown.update();
        
        // Update custom legend
        updateRevenueBreakdownLegend(data);
      }
    } catch (error) {
      console.error('Error fetching revenue breakdown:', error);
    }
  }

  /**
   * Update revenue breakdown legend
   * @param {Object} data - Revenue breakdown data
   */
  function updateRevenueBreakdownLegend(data) {
    const legendEl = document.getElementById('revenueBreakdownLegend');
    if (!legendEl) return;
    
    let html = '';
    data.labels.forEach((label, index) => {
      html += '<div class="legend-item mb-2">';
      html += '<span class="legend-color" style="background: ' + data.colors[index] + '"></span>';
      html += '<span class="legend-label">' + label + '</span>';
      html += '<span class="legend-value">₱' + parseFloat(data.values[index]).toLocaleString('en-PH', {minimumFractionDigits: 2}) + '</span>';
      html += '</div>';
    });
    
    legendEl.innerHTML = html;
  }

  /**
   * Fetch transaction history data
   */
  async function fetchTransactionHistory() {
    try {
      const response = await fetch('/reports/transaction-history?period=' + currentFilters.transactionHistory);
      const result = await response.json();
      
      if (result.success && charts.transactionHistory) {
        const data = result.data;
        charts.transactionHistory.data.labels = data.labels;
        charts.transactionHistory.data.datasets[0].data = data.values;
        charts.transactionHistory.data.datasets[0].backgroundColor = data.colors;
        charts.transactionHistory.update();
        
        // Update custom legend
        updateTransactionLegend(data);
      }
    } catch (error) {
      console.error('Error fetching transaction history:', error);
    }
  }

  /**
   * Update transaction history legend
   * @param {Object} data - Transaction data
   */
  function updateTransactionLegend(data) {
    const legendEl = document.getElementById('transactionHistoryLegend');
    if (!legendEl) return;
    
    let html = '';
    data.labels.forEach((label, index) => {
      html += '<div class="legend-item mb-2">';
      html += '<span class="legend-color" style="background: ' + data.colors[index] + '"></span>';
      html += '<span class="legend-label">' + label + '</span>';
      html += '<span class="legend-value">₱' + parseFloat(data.values[index]).toLocaleString('en-PH', {minimumFractionDigits: 2}) + '</span>';
      html += '</div>';
    });
    
    legendEl.innerHTML = html;
  }

  /**
   * Fetch attendance data
   */
  async function fetchAttendance() {
    try {
      const response = await fetch('/reports/attendance-trend?period=' + currentFilters.attendance);
      const result = await response.json();
      
      if (result.success && charts.customerAttendance) {
        const data = result.data;
        charts.customerAttendance.data.labels = data.labels;
        charts.customerAttendance.data.datasets[0].data = data.values;
        charts.customerAttendance.update();
      }
    } catch (error) {
      console.error('Error fetching attendance:', error);
    }
  }

  /**
   * Setup filter dropdown event listeners
   */
  function setupFilterListeners() {
    document.querySelectorAll('.filter-option').forEach(option => {
      option.addEventListener('click', function(e) {
        e.preventDefault();
        
        const chart = this.dataset.chart;
        const period = this.dataset.period;
        
        // Update active state
        this.closest('.filter-dropdown').querySelectorAll('.filter-option').forEach(opt => {
          opt.classList.remove('active');
        });
        this.classList.add('active');
        
        // Update filter state and fetch data
        currentFilters[chart] = period;
        
        switch(chart) {
          case 'revenueOverTime':
            fetchRevenueOverTime();
            break;
          case 'topSelling':
            fetchTopSelling();
            break;
          case 'revenueBreakdown':
            fetchRevenueBreakdown();
            break;
          case 'transactionHistory':
            fetchTransactionHistory();
            break;
          case 'attendance':
            fetchAttendance();
            break;
        }
      });
    });
  }

  /**
   * Export report
   */
  async function exportReport() {
    const format = document.querySelector('input[name="export_format"]:checked')?.value || 'pdf';
    const dateRange = document.querySelector('select[name="export_date_range"]')?.value || 'this_month';
    const scope = document.querySelector('select[name="export_scope"]')?.value || 'all';
    
    // Find the export button
    const btn = document.querySelector('.modal-footer .btn-warning') || document.querySelector('[onclick*="exportReport"]');
    const originalText = btn ? btn.innerHTML : 'Export';
    
    try {
      // Show loading state
      if (btn) {
        btn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Exporting...';
        btn.disabled = true;
      }
      
      // Create form and submit for file download
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/reports/export';
      form.style.display = 'none';
      
      // Add CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
      const csrfInput = document.createElement('input');
      csrfInput.type = 'hidden';
      csrfInput.name = '_token';
      csrfInput.value = csrfToken;
      form.appendChild(csrfInput);
      
      // Add format
      const formatInput = document.createElement('input');
      formatInput.type = 'hidden';
      formatInput.name = 'format';
      formatInput.value = format;
      form.appendChild(formatInput);
      
      // Add date range
      const dateRangeInput = document.createElement('input');
      dateRangeInput.type = 'hidden';
      dateRangeInput.name = 'date_range';
      dateRangeInput.value = dateRange;
      form.appendChild(dateRangeInput);
      
      // Add scope
      const scopeInput = document.createElement('input');
      scopeInput.type = 'hidden';
      scopeInput.name = 'scope';
      scopeInput.value = scope;
      form.appendChild(scopeInput);
      
      // Submit form
      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
      
      // Show success message
      setTimeout(() => {
        showToast('Report export started. Download will begin shortly.', 'success');
        $('#exportReportModal').modal('hide');
        
        if (btn) {
          btn.innerHTML = originalText;
          btn.disabled = false;
        }
      }, 500);
      
    } catch (error) {
      console.error('Export error:', error);
      showToast('Export failed. Please try again.', 'error');
      
      // Reset button
      if (btn) {
        btn.innerHTML = originalText;
        btn.disabled = false;
      }
    }
  }

  /**
   * Show toast notification
   * @param {string} message - Toast message
   * @param {string} type - Toast type (success, error, warning, info)
   */
  function showToast(message, type = 'info') {
    // Use existing toast system if available
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type === 'error' ? 'error' : type === 'success' ? 'success' : 'info',
        title: message,
        showConfirmButton: false,
        timer: 3000
      });
    } else {
      alert(message);
    }
  }

  /**
   * Get chart instance by name
   * @param {string} name - Chart name
   * @returns {Object} Chart instance
   */
  function getChart(name) {
    return charts[name];
  }

  /**
   * Update chart data
   * @param {string} name - Chart name
   * @param {Object} data - New data
   */
  function updateChartData(name, data) {
    if (charts[name]) {
      charts[name].data = data;
      charts[name].update();
    }
  }

  // Public API
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

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ReportsPage;
}

// Make globally accessible for inline scripts
window.ReportsPage = ReportsPage;
