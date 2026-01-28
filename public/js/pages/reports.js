/**
 * Reports Page Module
 * Page-specific JavaScript for ReportAndBilling/ReportAndBilling.blade.php
 * Handles Chart.js configurations for analytics/reporting
 */

const ReportsPage = (function() {
  'use strict';

  // Chart instances (for later reference/updates)
  let charts = {};

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

    createRevenueOverTimeChart('revenueOverTimeChart', options.revenueData);
    createTopSellingChart('topSellingProductsChart', options.topSellingData);
    createRevenueBreakdownChart('revenueBreakdownChart', options.breakdownData);
    createTransactionHistoryChart('transactionHistoryChart', options.transactionData);
    createCustomerAttendanceChart('customerAttendanceChart', options.attendanceData);
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
    createCustomerAttendanceChart
  };
})();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ReportsPage;
}
