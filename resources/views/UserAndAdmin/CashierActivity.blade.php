@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Activity Logs</h2>
            <p class="page-header-subtitle">Track and review staff activity logs and transactions.</p>
        </div>
    </div>
</div>

            <div class="row">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="card-title mb-0">Activity Logs</h4>
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
                            <th> Log# </th>
                            <th> Cashier Name </th>
                            <th> Activity Description  </th>
                            <th> Customer </th>
                            <th> References </th>
                            <th> Date </th>
                            <th> TImestamp </th>
                          </tr>
                        </thead>
                       </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
@endsection