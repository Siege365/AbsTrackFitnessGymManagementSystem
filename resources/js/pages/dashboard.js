/**
 * Dashboard Page Module
 * Page-specific JavaScript for pages/dashboard.blade.php
 * Handles Chart.js configurations for real-time gym analytics.
 *
 * Architecture:
 *  - Static doughnut charts rendered from server-side data
 *  - Dynamic line/bar charts fetched via AJAX with filter buttons
 *  - CSRF token included in all fetch requests
 */

const DashboardPage = (function () {
  'use strict';

  // ───────────────────────────── State ─────────────────────────────

  /** @type {Object.<string, Chart>} Active Chart.js instances */
  let charts = {};

  // ───────────────────────────── Config ────────────────────────────

  const COLORS = {
    text:     '#8b92a7',
    gridLine: 'rgba(255, 255, 255, 0.05)',
    border:   'rgba(255, 255, 255, 0.1)',
    tooltip: {
      background: 'rgba(25, 28, 36, 0.95)',
      title:      '#fff',
      body:       '#8b92a7',
      border:     '#3a4048'
    },
    brand: {
      blue:   '#42A5F5',
      green:  '#66BB6A',
      orange: '#FFA726',
      purple: '#AB47BC',
      cyan:   '#26C6DA',
      red:    '#EF5350',
      pink:   '#EC407A'
    }
  };

  // ──────────────────── Shared Chart Helpers ───────────────────────

  function getTooltipConfig() {
    return {
      backgroundColor: COLORS.tooltip.background,
      padding:         12,
      titleColor:      COLORS.tooltip.title,
      bodyColor:       COLORS.tooltip.body,
      borderColor:     COLORS.tooltip.border,
      borderWidth:     1,
      cornerRadius:    8,
      displayColors:   true
    };
  }

  function getLegendConfig(position = 'bottom') {
    return {
      display:  true,
      position: position,
      labels: {
        color:         '#fff',
        usePointStyle: true,
        padding:       15,
        font:          { size: 11 }
      }
    };
  }

  function getYAxisConfig(currency = true) {
    return {
      beginAtZero: true,
      grid:  { color: COLORS.gridLine, drawBorder: false },
      ticks: {
        color:    COLORS.text,
        font:     { size: 11 },
        padding:  8,
        callback: currency
          ? (v) => '₱' + v.toLocaleString()
          : (v) => v.toLocaleString()
      }
    };
  }

  function getXAxisConfig() {
    return {
      grid:  { display: false },
      ticks: { color: COLORS.text, font: { size: 11 }, maxRotation: 45, minRotation: 0 }
    };
  }

  /** Fetch helper with CSRF */
  async function fetchJSON(url) {
    const res = await fetch(url, {
      headers: {
        'Accept':       'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
      }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  // ──────────────────── Chart Builders ─────────────────────────────

  /** Attendance Chart (Area/Line) */
  function buildAttendanceChart(labels, values) {
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;

    if (charts.attendance) charts.attendance.destroy();

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(66, 165, 245, 0.25)');
    gradient.addColorStop(1, 'rgba(66, 165, 245, 0.02)');

    charts.attendance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label:             'Check-ins',
          data:              values,
          borderColor:       COLORS.brand.blue,
          backgroundColor:   gradient,
          borderWidth:       2.5,
          fill:              true,
          tension:           0.4,
          pointRadius:       3,
          pointHoverRadius:  6,
          pointBackgroundColor: COLORS.brand.blue,
          pointBorderColor:  '#191C24',
          pointBorderWidth:  2
        }]
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        plugins: {
          legend:  { display: false },
          tooltip: {
            ...getTooltipConfig(),
            callbacks: {
              label: (ctx) => ` ${ctx.parsed.y} check-in${ctx.parsed.y !== 1 ? 's' : ''}`
            }
          }
        },
        scales: {
          y: getYAxisConfig(false),
          x: getXAxisConfig()
        },
        interaction: { intersect: false, mode: 'index' }
      }
    });
  }

  /** Revenue Trend Chart (Stacked Area) */
  function buildRevenueChart(data) {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    if (charts.revenue) charts.revenue.destroy();

    const colorMap = [
      { border: COLORS.brand.blue,   bg: 'rgba(66, 165, 245, 0.15)' },
      { border: COLORS.brand.green,  bg: 'rgba(102, 187, 106, 0.15)' },
      { border: COLORS.brand.orange, bg: 'rgba(255, 167, 38, 0.15)' }
    ];

    const datasets = (data.datasets || []).map((ds, i) => ({
      label:           ds.label,
      data:            ds.data,
      borderColor:     colorMap[i]?.border || COLORS.brand.cyan,
      backgroundColor: colorMap[i]?.bg || 'rgba(38, 198, 218, 0.15)',
      borderWidth:     2,
      fill:            true,
      tension:         0.4,
      pointRadius:     2,
      pointHoverRadius: 5
    }));

    charts.revenue = new Chart(ctx, {
      type: 'line',
      data: { labels: data.labels || [], datasets },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        plugins: {
          legend:  getLegendConfig(),
          tooltip: {
            ...getTooltipConfig(),
            mode: 'index',
            callbacks: {
              label: (ctx) => ` ${ctx.dataset.label}: ₱${ctx.parsed.y.toLocaleString(undefined, { minimumFractionDigits: 2 })}`
            }
          }
        },
        scales: {
          y: getYAxisConfig(true),
          x: getXAxisConfig()
        },
        interaction: { intersect: false, mode: 'index' }
      }
    });
  }

  /** Membership Status Doughnut */
  function buildMembershipStatusChart() {
    const ctx = document.getElementById('membershipStatusChart');
    if (!ctx) return;

    // Read values from DOM (rendered by Blade)
    const legendItems = document.querySelectorAll('.doughnut-legend .text-center');
    if (legendItems.length < 3) return;

    const active  = parseInt(legendItems[0]?.querySelector('h5')?.textContent) || 0;
    const dueSoon = parseInt(legendItems[1]?.querySelector('h5')?.textContent) || 0;
    const expired = parseInt(legendItems[2]?.querySelector('h5')?.textContent) || 0;

    if (active + dueSoon + expired === 0) {
      // Show empty state
      charts.membershipStatus = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['No Data'],
          datasets: [{ data: [1], backgroundColor: ['rgba(255,255,255,0.08)'], borderWidth: 0 }]
        },
        options: {
          responsive: true, maintainAspectRatio: false,
          cutout: '70%',
          plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
      });
      return;
    }

    charts.membershipStatus = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Due Soon', 'Expired'],
        datasets: [{
          data:            [active, dueSoon, expired],
          backgroundColor: [COLORS.brand.green, COLORS.brand.orange, COLORS.brand.red],
          borderWidth:     0,
          hoverOffset:     8
        }]
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        cutout:              '70%',
        plugins: {
          legend:  { display: false },
          tooltip: {
            ...getTooltipConfig(),
            callbacks: {
              label: (ctx) => ` ${ctx.label}: ${ctx.parsed} member${ctx.parsed !== 1 ? 's' : ''}`
            }
          }
        }
      }
    });
  }

  /** Plan Distribution Doughnut */
  function buildPlanDistributionChart() {
    const ctx = document.getElementById('planDistributionChart');
    if (!ctx) return;

    let planData = [];
    try {
      const el = document.getElementById('planDistributionData');
      if (el) planData = JSON.parse(el.textContent);
    } catch (e) {
      console.warn('Could not parse plan distribution data:', e);
    }

    const colors = [
      COLORS.brand.blue, COLORS.brand.green, COLORS.brand.orange,
      COLORS.brand.purple, COLORS.brand.cyan, COLORS.brand.red, COLORS.brand.pink
    ];

    if (!planData.length) {
      charts.planDistribution = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['No Data'],
          datasets: [{ data: [1], backgroundColor: ['rgba(255,255,255,0.08)'], borderWidth: 0 }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, cutout: '70%',
          plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
      });
      return;
    }

    const labels = planData.map(p => {
      const type = p.plan_type || 'N/A';
      return type.replace(/([A-Z])/g, ' $1').trim();
    });
    const values = planData.map(p => p.count);

    charts.planDistribution = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data:            values,
          backgroundColor: colors.slice(0, values.length),
          borderWidth:     0,
          hoverOffset:     8
        }]
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        cutout:              '70%',
        plugins: {
          legend:  { display: false },
          tooltip: {
            ...getTooltipConfig(),
            callbacks: {
              label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`
            }
          }
        }
      }
    });
  }

  /** Revenue Breakdown Doughnut */
  function buildRevenueBreakdownChart() {
    const ctx = document.getElementById('revenueBreakdownChart');
    if (!ctx) return;

    // Read from the doughnut-legend in the parent card
    const card = ctx.closest('.card');
    const cells = card?.querySelectorAll('.doughnut-legend h6');
    if (!cells || cells.length < 3) return;

    const parseVal = (el) => parseFloat((el?.textContent || '0').replace(/[₱,]/g, '')) || 0;

    const retail     = parseVal(cells[0]);
    const membership = parseVal(cells[1]);
    const pt         = parseVal(cells[2]);

    if (retail + membership + pt === 0) {
      charts.revenueBreakdown = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['No Data'],
          datasets: [{ data: [1], backgroundColor: ['rgba(255,255,255,0.08)'], borderWidth: 0 }]
        },
        options: {
          responsive: true, maintainAspectRatio: false, cutout: '70%',
          plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
      });
      return;
    }

    charts.revenueBreakdown = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Retail Sales', 'Membership', 'Personal Training'],
        datasets: [{
          data:            [retail, membership, pt],
          backgroundColor: [COLORS.brand.blue, COLORS.brand.green, COLORS.brand.orange],
          borderWidth:     0,
          hoverOffset:     8
        }]
      },
      options: {
        responsive:          true,
        maintainAspectRatio: false,
        cutout:              '70%',
        plugins: {
          legend:  { display: false },
          tooltip: {
            ...getTooltipConfig(),
            callbacks: {
              label: (ctx) => ` ${ctx.label}: ₱${ctx.parsed.toLocaleString(undefined, { minimumFractionDigits: 2 })}`
            }
          }
        }
      }
    });
  }

  // ──────────────────── Data Fetch Functions ───────────────────────

  async function loadAttendanceChart(period = 'today') {
    try {
      const resp = await fetchJSON(`/dashboard/attendance-chart?period=${period}`);
      if (resp.success && resp.data) {
        buildAttendanceChart(resp.data.labels, resp.data.values);
      }
    } catch (e) {
      console.error('Failed to load attendance chart:', e);
    }
  }

  async function loadRevenueChart(period = 'this_month') {
    try {
      const resp = await fetchJSON(`/dashboard/revenue-chart?period=${period}`);
      if (resp.success && resp.data) {
        buildRevenueChart(resp.data);
      }
    } catch (e) {
      console.error('Failed to load revenue chart:', e);
    }
  }

  // ──────────────────── Filter Button Handlers ────────────────────

  function initFilterButtons() {
    document.querySelectorAll('.btn-filter').forEach(btn => {
      btn.addEventListener('click', function () {
        const chart  = this.dataset.chart;
        const period = this.dataset.period;

        // Update active state within group
        this.closest('.chart-filter-group')
          .querySelectorAll('.btn-filter')
          .forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Fetch new data
        if (chart === 'attendance') {
          loadAttendanceChart(period);
        } else if (chart === 'revenue') {
          loadRevenueChart(period);
        }
      });
    });
  }

  // ──────────────────── Pagination Helper ────────────────────

  function renderPagination(containerId, currentPage, totalPages, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    container.innerHTML = `
      <div class="d-flex align-items-center justify-content-end">
        <button class="dash-page-btn" data-action="prev" ${currentPage === 1 ? 'disabled' : ''}>
          <i class="mdi mdi-chevron-left"></i>
        </button>
        <span class="dash-page-info">Page ${currentPage} of ${totalPages}</span>
        <button class="dash-page-btn" data-action="next" ${currentPage === totalPages ? 'disabled' : ''}>
          <i class="mdi mdi-chevron-right"></i>
        </button>
      </div>
    `;
    container.querySelector('[data-action="prev"]').addEventListener('click', () => onPageChange(currentPage - 1));
    container.querySelector('[data-action="next"]').addEventListener('click', () => onPageChange(currentPage + 1));
  }

  // ──────────────── Payment Filter + Pagination ────────────────

  const paymentState = { filter: 'all', page: 1, perPage: 10 };

  function applyPaymentView() {
    const table     = document.getElementById('recentPaymentsTable');
    const noResults = document.getElementById('noFilteredPayments');
    const rows      = Array.from(document.querySelectorAll(
      '#recentPaymentsTable tbody tr[data-payment-category]'
    ));

    const filtered = paymentState.filter === 'all'
      ? rows
      : rows.filter(r => r.dataset.paymentCategory === paymentState.filter);

    const totalPages = Math.max(1, Math.ceil(filtered.length / paymentState.perPage));
    if (paymentState.page > totalPages) paymentState.page = totalPages;

    const start = (paymentState.page - 1) * paymentState.perPage;
    const end   = start + paymentState.perPage;

    rows.forEach(r => (r.style.display = 'none'));
    filtered.slice(start, end).forEach(r => (r.style.display = ''));

    if (noResults) noResults.style.display = filtered.length === 0 ? '' : 'none';
    if (table)     table.style.display     = filtered.length === 0 ? 'none' : '';

    renderPagination('paymentsPagination', paymentState.page, totalPages, (p) => {
      paymentState.page = p;
      applyPaymentView();
    });
  }

  function initPaymentFilters() {
    const dropdown = document.getElementById('paymentFilterDropdown');
    if (!dropdown) return;

    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
      item.addEventListener('click', function (e) {
        e.preventDefault();
        paymentState.filter = this.dataset.filter;
        paymentState.page   = 1;

        dropdown.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');

        const labelEl = document.getElementById('paymentFilterLabel');
        if (labelEl) labelEl.textContent = paymentState.filter === 'all' ? 'All' : paymentState.filter;

        applyPaymentView();
      });
    });

    applyPaymentView();
  }

  // ──────────────── Generic Table Pagination (5 per page) ────────────────

  function initTablePagination(tableId, paginationId, perPage) {
    const table = document.getElementById(tableId);
    if (!table) return;

    const state = { page: 1, perPage: perPage };
    const rows  = Array.from(table.querySelectorAll('tbody tr'));

    function apply() {
      const totalPages = Math.max(1, Math.ceil(rows.length / state.perPage));
      if (state.page > totalPages) state.page = totalPages;

      const start = (state.page - 1) * state.perPage;
      const end   = start + state.perPage;

      rows.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
      });

      renderPagination(paginationId, state.page, totalPages, (p) => {
        state.page = p;
        apply();
      });
    }

    apply();
  }

  // ──────────────── Low Stock List Pagination ────────────────

  function initLowStockPagination(perPage) {
    const list = document.getElementById('lowStockList');
    if (!list) return;

    const state = { page: 1, perPage: perPage };
    const items = Array.from(list.querySelectorAll('.stock-alert-item'));

    function apply() {
      const totalPages = Math.max(1, Math.ceil(items.length / state.perPage));
      if (state.page > totalPages) state.page = totalPages;

      const start = (state.page - 1) * state.perPage;
      const end   = start + state.perPage;

      items.forEach((item, i) => {
        const show = i >= start && i < end;
        if (show) {
          item.classList.add('d-flex');
          item.style.removeProperty('display');
        } else {
          item.classList.remove('d-flex');
          item.style.setProperty('display', 'none', 'important');
        }
      });

      renderPagination('lowStockPagination', state.page, totalPages, (p) => {
        state.page = p;
        apply();
      });
    }

    apply();
  }

  // ──────────────────── Initialize ────────────────────────────────

  function init() {
    // Load dynamic charts from API
    loadAttendanceChart('today');
    loadRevenueChart('this_month');

    // Build static charts from server-rendered data
    buildMembershipStatusChart();
    buildPlanDistributionChart();
    buildRevenueBreakdownChart();

    // Set up filter buttons
    initFilterButtons();

    // Set up payment table filters
    initPaymentFilters();

    // Set up row-5 pagination (5 rows per page)
    initTablePagination('activityTimeline', 'activityPagination', 5);
    initTablePagination('ptScheduleTable', 'ptSchedulePagination', 5);
    initTablePagination('expiringSoonTable', 'expiringSoonPagination', 5);
    initLowStockPagination(5);
  }

  // ──────────────────── Bootstrap ─────────────────────────────────

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  return { init };
})();
