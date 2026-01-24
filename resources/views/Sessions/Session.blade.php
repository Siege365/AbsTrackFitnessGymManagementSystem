@extends('layouts.admin')

@section('title', 'Sessions')
@section('content')
            <div class="row">
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">0</h3>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">PT Sessions Today</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">0</h3>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Upcoming PT Sessions</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">0</h3>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">PT Cancellations</h6>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-9">
                        <div class="d-flex align-items-center align-self-start">
                          <h3 class="mb-0">0</h3>
                        </div>
                      </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Customer Attended Today</h6>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="card-title mb-0">Today's Customer</h4>
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
                            <th> ID# </th>
                            <th> Name </th>
                            <th> Plan Type </th>
                            <th> Date </th>
                            <th> Time In</th>
                            <th> Status </th>
                            <th> Contact # </th>
                            <th> Actions </th>
                          </tr>
                        </thead>
                       </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
                <div class="col-12 grid-margin">
                    <div class="card">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">Personal Training Schedule</h4>
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
                                <th> Client's Name </th>
                                <th> Trainer </th>
                                <th> Date </th>
                                <th> Time </th>
                                <th> Payment Type </th>
                                <th> Status </th>
                                <th> Actions </th>
                                </tr>
                            </thead>
                            </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
@endsection