@extends('layouts.admin')

@section('title', 'Trainer Management')

@section('content')
            <div class="row">
              <div class="col-12 grid-margin">
                <div class="card">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="card-title mb-0">List of Trainers</h4>
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
                            <th> Trainer ID# </th>
                            <th> Full Name </th>
                            <th> Specialization </th>
                            <th> Contact # </th>
                            <th> Emergency Contact # </th>
                            <th> Birthdate </th>
                            <th> Address </th>
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