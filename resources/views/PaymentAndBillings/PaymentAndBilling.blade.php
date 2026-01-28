@extends('layouts.admin')

@section('title', 'Payments & Billing')

@push('styles')
<style>

  .card-body:hover {
    transform: translateY(-2px);
  }

  .table-responsive::-webkit-scrollbar {
    height: 8px;
  }
  
  .table-responsive::-webkit-scrollbar-track {
    background: #191C24;
  }
  
  .table-responsive::-webkit-scrollbar-thumb {
    background-color: #555;
    border-radius: 4px;
  }

  .pagination .page-item.active .page-link {
    background-color: #191C24;
    border-color: #191C24;
  }
  
  .pagination .page-link {
    color: #555;
  }
  
  .pagination .page-link:hover {
    background-color: #191C24;
    border-color: #191C24;
    color: #000000;
  }

  .form-control[readonly] {
    background-color: #2A3038 !important;
    color: #495057 !important;
  }

  .table thead th,
  .table tbody td {
    color: #ffffff !important;
  }

  .table-hover tbody tr:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
  }

  #itemsTableBody tr {
    height: 53px;
  }

  #searchClearBtn {
    min-width: 80px;
  }

  #addItemBtn {
    min-width: 80px;
    background-color: #17a2b8;
    border-color: #17a2b8;
  }
  #addItemBtn:hover {
    background-color: #138496;
    border-color: #117a8b;
  }
  .search-results {
    position: absolute;
    background: #2A3038;
    border: 1px solid #555;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    z-index: 1000;
    border-radius: 4px;
    margin-top: 2px;
  }

  .search-result-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #555;
    color: #ffffff;
  }

  .search-result-item:hover {
    background-color: #191C24;
  }

  .search-result-item:last-child {
    border-bottom: none;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($totalRevenueMonth ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success ">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Total Revenue This Month</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($retailSalesRevenue ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+11%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Retail Sales Revenue</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($dailyIncome ?? 0, 2) }}</h3>
              <p class="text-danger ml-2 mb-0 font-weight-medium">-2.4%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-danger">
              <span class="mdi mdi-arrow-bottom-left icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Daily Income</h6>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-9">
            <div class="d-flex align-items-center align-self-start">
              <h3 class="mb-0">₱{{ number_format($weeklyIncome ?? 0, 2) }}</h3>
              <p class="text-success ml-2 mb-0 font-weight-medium">+3.5%</p>
            </div>
          </div>
          <div class="col-3">
            <div class="icon icon-box-success ">
              <span class="mdi mdi-arrow-top-right icon-item"></span>
            </div>
          </div>
        </div>
        <h6 class="text-muted font-weight-normal">Weekly Income</h6>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="card-title mb-0">Payment Details Form</h4>
          <div class="d-flex" style="position: relative; width: 420px;">
            <input type="text" id="searchItem" class="form-control form-control-sm mr-2" placeholder="Search items..." style="width: 300px;">
            <button type="button" class="btn btn-sm btn-primary mr-2" id="addItemBtn">
              Add Item
            </button>
            <button type="button" class="btn btn-sm btn-warning" id="searchClearBtn">
              Clear
            </button>
            <div id="searchResults" class="search-results" style="display: none;"></div>
          </div>
        </div>
        <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
          @csrf

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="customerName">Customer Name</label>
                <div style="position: relative;">
                  <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Name" autocomplete="off" required>
                  <input type="hidden" id="customerId" name="customer_id">
                  <div id="customerResults" class="search-results" style="display:none;"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="totalAmount">Total Amount</label>
                <input type="number" class="form-control" id="totalAmount" name="total_amount" placeholder="₱0.00" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="transactionType">Transaction Type</label>
                <select class="form-control" id="transactionType" name="transaction_type">
                  <option>Mixed</option>
                  <option>Cash</option>
                  <option>Credit Card</option>
                  <option>Online Payment</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="paidAmount">Paid Amount</label>
                <input type="number" class="form-control" id="paidAmount" name="paid_amount" placeholder="₱0.00" step="0.01">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="paymentMethod">Payment Method</label>
                <select class="form-control" id="paymentMethod" name="payment_method">
                  <option>Cash</option>
                  <option>Credit Card</option>
                  <option>Debit Card</option>
                  <option>GCash</option>
                  <option>Online Payment</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="returnAmount">Return Amount</label>
                <input type="number" class="form-control readonly-field" id="returnAmount" placeholder="₱0.00" readonly>
              </div>
            </div>
          </div>
          
          <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Items</h5>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                  <thead>
                    <tr>
                      <th style="min-width: 200px;">Item</th>
                      <th style="min-width: 80px;">Qty</th>
                      <th style="min-width: 120px;">Unit Price (₱)</th>
                      <th style="min-width: 120px;">Subtotal (₱)</th>
                      <th style="min-width: 100px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="itemsTableBody">
                    <tr><td colspan="5" class="text-center text-muted">No items added</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          
          <input type="hidden" id="itemsData" name="items_data">
          
          <div class="row mt-3">
            <div class="col-12">
              <button type="button" class="btn btn-secondary mr-2" id="clearBtn">Clear</button>
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="card-title mb-0">Transaction History</h4>
          <div class="d-flex">
            <button class="btn btn-sm btn-outline-secondary mr-2">
              <i class="mdi mdi-filter-variant"></i> Filter
            </button>
            <input type="text" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
          </div>
        </div>
        <div class="table-responsive" style="min-height: 600px;">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="min-width: 50px;">
                  <div class="form-check form-check-muted m-0">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input">
                    </label>
                  </div>
                </th>
                <th style="min-width: 80px;">Receipt#</th>
                <th style="min-width: 180px;">Customer Name</th>
                <th style="min-width: 150px;">Date & Time</th>
                <th style="min-width: 120px;">Payment Type</th>
                <th style="min-width: 80px;">Quantity</th>
                <th style="min-width: 120px;">Total Price (₱)</th>
                <th style="min-width: 150px;">Cashier</th>
                <th style="min-width: 80px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @php
                $transactionCount = isset($transactions) ? $transactions->count() : 0;
                $maxRows = 10;
              @endphp
              
              @if(isset($transactions) && $transactions->count() > 0)
                @foreach($transactions as $transaction)
                <tr>
                  <td>
                    <div class="form-check form-check-muted m-0">
                      <label class="form-check-label">
                        <input type="checkbox" class="form-check-input">
                      </label>
                    </div>
                  </td>
                  <td>{{ $transaction->receipt_number }}</td>
                  <td>{{ $transaction->customer_name }}</td>
                  <td>{{ $transaction->created_at->format('Y-m-d, H:i') }}</td>
                  <td>{{ $transaction->payment_method }}</td>
                  <td>{{ $transaction->total_quantity }}</td>
                  <td>₱{{ number_format($transaction->total_amount, 2) }}</td>
                  <td>{{ $transaction->cashier_name }}</td>
                  <td>
                    <button class="btn btn-sm btn-link">
                      <i class="mdi mdi-dots-vertical"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              @endif
              
              @for($i = $transactionCount; $i < $maxRows; $i++)
              <tr>
                <td style="height: 53px;">&nbsp;</td>
                <td>&nbsp;</td>
                <td>
                  @if($i == 0)
                    <span class="text-muted">No transactions found</span>
                  @endif
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
              </tr>
              @endfor
            </tbody>
          </table>
        </div>
        
        @if(isset($transactions) && $transactions->total() > 0)
        <div class="d-flex justify-content-between align-items-center mt-3">
          <button class="btn btn-danger btn-sm">
            <i class="mdi mdi-delete"></i>
          </button>
          <div>
            {{ $transactions->links() }}
          </div>
        </div>
        @else
        <div class="d-flex justify-content-between align-items-center mt-3">
          <button class="btn btn-danger btn-sm">
            <i class="mdi mdi-delete"></i>
          </button>
          <div class="text-muted">
            Showing 0 to 0 of 0 entries
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- Page Module -->
<script src="{{ asset('js/pages/payments.js') }}?v={{ time() }}"></script>
<script>
  // Initialize payments page with Laravel data
  document.addEventListener('DOMContentLoaded', function() {
    PaymentsPage.init({
      inventoryItems: @json($inventoryItems ?? []),
      memberSearchUrl: '{{ url("/members/search") }}'
    });
  });
</script>
@endpush