<!-- View Stock History Modal -->
<div class="modal fade" id="stockHistoryModal" tabindex="-1" role="dialog" aria-labelledby="stockHistoryModalLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stockHistoryModalLabel">
          Stock History — <span id="stockHistoryProductName"></span>
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Summary Stats -->
        <div class="stock-summary-card" id="stockHistorySummary">
          <div class="stock-summary-item">
            <div class="summary-value text-success" id="historyTotalIn">0</div>
            <div class="summary-label">Total Stock In</div>
          </div>
          <div class="stock-summary-item">
            <div class="summary-value text-warning" id="historyTotalOut">0</div>
            <div class="summary-label">Total Stock Out</div>
          </div>
          <div class="stock-summary-item">
            <div class="summary-value" id="historyNetChange">0</div>
            <div class="summary-label">Net Change</div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="stock-history-timeline" id="stockHistoryTimeline">
          <div class="stock-history-loading">
            <div class="spinner-border" role="status"></div>
            <p class="mt-2">Loading stock history...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-cancel" data-dismiss="modal">Close</button>
        <a href="#" id="stockHistoryFullLink" class="btn btn-update">
          <i class="mdi mdi-history"></i> Full Stock History
        </a>
      </div>
    </div>
  </div>
</div>
