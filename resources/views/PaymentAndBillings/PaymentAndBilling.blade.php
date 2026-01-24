@extends('layouts.admin')

@section('title', 'Payments & Billing')

@push('styles')
<style>
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
                          <h3 class="mb-0">₱0.00</h3>
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
                          <h3 class="mb-0">₱0.00</h3>
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
                          <h3 class="mb-0">₱0.00</h3>
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
                          <h3 class="mb-0">₱0.00</h3>
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
                    <h4 class="card-title mb-4">Payment Details Form</h4>
                    <form>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="customerName">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" placeholder="Name">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="totalAmount">Total Amount</label>
                            <input type="number" class="form-control" id="totalAmount" placeholder="₱0.00" readonly>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="transactionType">Transaction Type</label>
                            <select class="form-control" id="transactionType">
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
                            <input type="number" class="form-control" id="paidAmount" placeholder="₱0.00">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="paymentMethod">Payment Method</label>
                            <select class="form-control" id="paymentMethod">
                              <option>Cash</option>
                              <option>Credit Card</option>
                              <option>Debit Card</option>
                              <option>Online Payment</option>
                              <option>Cash on Delivery</option>
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
                          <h5 class="mb-3">Items</h5>
                          <div class="table-responsive">
                            <table class="table table-bordered">
                              <thead>
                                <tr>
                                  <th style="min-width: 200px;">Item</th>
                                  <th style="min-width: 80px;">Qty</th>
                                  <th style="min-width: 120px;">Unit Price (₱)</th>
                                  <th style="min-width: 120px;">Subtotal (₱)</th>
                                  <th style="min-width: 100px;">Actions</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td>
                                    <span class="badge badge-primary mr-2"></span>
                                    Coca-Cola (190ml)
                                  </td>
                                  <td>1</td>
                                  <td>₱15</td>
                                  <td>₱15</td>
                                  <td>
                                    <button type="button" class="btn btn-sm btn-danger">
                                      <i class="mdi mdi-delete"></i>
                                    </button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                    <span class="badge badge-primary mr-2"></span>
                                    Cobra Energy Drink (350ml)
                                  </td>
                                  <td>2</td>
                                  <td>₱25</td>
                                  <td>₱50</td>
                                  <td>
                                    <button type="button" class="btn btn-sm btn-danger">
                                      <i class="mdi mdi-delete"></i>
                                    </button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                    <span class="badge badge-primary mr-2"></span>
                                    Piattos
                                  </td>
                                  <td>1</td>
                                  <td>₱16</td>
                                  <td>₱16</td>
                                  <td>
                                    <button type="button" class="btn btn-sm btn-danger">
                                      <i class="mdi mdi-delete"></i>
                                    </button>
                                  </td>
                                </tr>
                                <tr>
                                  <td>
                                    <span class="badge badge-warning mr-2"></span>
                                    Walk In Session
                                  </td>
                                  <td>1</td>
                                  <td>₱120</td>
                                  <td>₱70</td>
                                  <td>
                                    <button type="button" class="btn btn-sm btn-danger">
                                      <i class="mdi mdi-delete"></i>
                                    </button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-12">
                          <button type="button" class="btn btn-secondary mr-2">Clear</button>
                          <button type="submit" class="btn btn-secondary">Submit</button>
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
                    <div class="table-responsive">
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
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0001</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face1.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Henry Klein</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>Cash</td>
                            <td>1</td>
                            <td>₱15</td>
                            <td>Luwid Rosaladers</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0002</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face2.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Estella Bryan</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>GCash</td>
                            <td>2</td>
                            <td>₱30</td>
                            <td>Luwid Rosaladers</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0003</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face5.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Lucy Abbott</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>Cash</td>
                            <td>3</td>
                            <td>₱45</td>
                            <td>Luwid Rosaladers</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0004</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face3.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Peter Gill</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>Cash</td>
                            <td>3</td>
                            <td>₱75</td>
                            <td>Luwid Rosaladers</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0005</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face4.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Sallie Reyes</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>GCash</td>
                            <td>1</td>
                            <td>₱120</td>
                            <td>Luwid Rosaladers</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0006</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face1.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Henry Klein</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>GCash</td>
                            <td>1</td>
                            <td>₱70</td>
                            <td>Cashier 2</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0007</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face2.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Estella Bryan</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>Cash</td>
                            <td>1</td>
                            <td>₱25</td>
                            <td>Cashier 2</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0008</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face5.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Lucy Abbott</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>GCash</td>
                            <td>1</td>
                            <td>₱150</td>
                            <td>Cashier 2</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0009</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face3.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Peter Gill</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>Cash</td>
                            <td>1</td>
                            <td>₱1200</td>
                            <td>Cashier 2</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <div class="form-check form-check-muted m-0">
                                <label class="form-check-label">
                                  <input type="checkbox" class="form-check-input">
                                </label>
                              </div>
                            </td>
                            <td>0010</td>
                            <td>
                              <div class="d-flex align-items-center">
                                <img src="{{ asset('template/assets/images/faces/face4.jpg') }}" alt="image" class="mr-2" style="width: 30px; height: 30px; border-radius: 50%;" />
                                <span>Sallie Reyes</span>
                              </div>
                            </td>
                            <td>2025-09-02, 14:32</td>
                            <td>GCash</td>
                            <td>1</td>
                            <td>₱150</td>
                            <td>Cashier 2</td>
                            <td>
                              <button class="btn btn-sm btn-link">
                                <i class="mdi mdi-dots-vertical"></i>
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                      <button class="btn btn-danger btn-sm">
                        <i class="mdi mdi-delete"></i>
                      </button>
                      <nav>
                        <ul class="pagination mb-0">
                          <li class="page-item disabled">
                            <a class="page-link" href="#">«</a>
                          </li>
                          <li class="page-item active">
                            <a class="page-link" href="#">1</a>
                          </li>
                          <li class="page-item">
                            <a class="page-link" href="#">2</a>
                          </li>
                          <li class="page-item">
                            <a class="page-link" href="#">3</a>
                          </li>
                          <li class="page-item">
                            <a class="page-link" href="#">»</a>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  </div>
                </div>
              </div>
            </div>
@endsection