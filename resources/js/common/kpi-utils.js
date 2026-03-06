/**
 * KPI Number Formatting Utilities
 * 
 * Provides automatic number abbreviation for KPI stat cards:
 *   0 - 999        → full number (e.g., 500)
 *   1,000 - 999K   → K (e.g., 1.5K, 150K)
 *   1M - 999M      → M (e.g., 1.5M, 150M)
 *   1B+             → B (e.g., 1.5B, 150B)
 * 
 * Decimals are hidden when they're .0 (e.g., 1.0K → 1K)
 */

/**
 * Format large numbers with K, M, B abbreviations
 * @param {number} num - The number to format
 * @returns {string} - Formatted string (e.g., "1.5K", "2M")
 */
function formatKPINumber(num) {
    if (num === null || num === undefined || isNaN(num)) {
        return '0';
    }

    num = Number(num);
    const absNum = Math.abs(num);
    const sign = num < 0 ? '-' : '';

    if (absNum >= 1000000000) {
        return sign + (absNum / 1000000000).toFixed(2) + 'B';
    } else if (absNum >= 1000000) {
        return sign + (absNum / 1000000).toFixed(2) + 'M';
    } else if (absNum >= 1000) {
        return sign + (absNum / 1000).toFixed(2) + 'K';
    } else {
        return num.toString();
    }
}

/**
 * Format large currency numbers with K, M, B abbreviations
 * @param {number} num - The number to format
 * @param {string} currencySymbol - Currency symbol (default: '₱')
 * @returns {string} - Formatted string (e.g., "₱1.5K", "₱2M")
 */
function formatKPICurrency(num, currencySymbol = '₱') {
    return currencySymbol + formatKPINumber(num);
}

/**
 * Auto-format all KPI stat card values on the page.
 * Call this on DOMContentLoaded for server-rendered KPI values.
 * 
 * Looks for elements with [data-kpi-value] attribute and formats them.
 * - data-kpi-value: the raw numeric value
 * - data-kpi-type: "number" (default), "currency"
 * - data-kpi-currency: currency symbol (default: "₱")
 * 
 * Also sets title attribute with the full formatted number for hover tooltip.
 */
function initKPIFormatting() {
    document.querySelectorAll('[data-kpi-value]').forEach(function(el) {
        const rawValue = parseFloat(el.getAttribute('data-kpi-value'));
        const type = el.getAttribute('data-kpi-type') || 'number';
        const currency = el.getAttribute('data-kpi-currency') || '₱';

        if (isNaN(rawValue)) return;

        // Set full number as tooltip
        if (type === 'currency') {
            el.setAttribute('title', currency + rawValue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            el.textContent = formatKPICurrency(rawValue, currency);
        } else {
            el.setAttribute('title', rawValue.toLocaleString());
            el.textContent = formatKPINumber(rawValue);
        }
    });
}

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', initKPIFormatting);

// Expose globally for use in page-specific JS
window.formatKPINumber = formatKPINumber;
window.formatKPICurrency = formatKPICurrency;
window.initKPIFormatting = initKPIFormatting;
