@extends('layouts.admin')

@section('title', 'Add New Member')

@push('styles')
<style>
  .form-control:focus {
    border-color: #191C24;
    box-shadow: 0 0 0 0.2rem rgba(25, 28, 36, 0.25);
  }

  .card {
    background: #2A3038;
    border: none;
  }

  .card-body {
    color: #ffffff;
  }

  .form-control {
    background-color: #2A3038;
    border: 1px solid #555;
    color: #ffffff;
  }

  .form-control::placeholder {
    color: rgba(255, 255, 255, 0.5);
  }

  .form-control:focus {
    background-color: #343a46;
    color: #ffffff;
  }

  label {
    color: #ffffff;
    font-weight: 500;
  }

  .breadcrumb {
    background: transparent;
  }

  .breadcrumb-item a {
    color: rgba(255, 255, 255, 0.7);
  }

  .breadcrumb-item.active {
    color: #ffffff;
  }

  .btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
  }

  .btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
  }

  .btn-primary {
    background-color: #191C24;
    border-color: #191C24;
  }

  .btn-primary:hover {
    background-color: #0d0f14;
    border-color: #0d0f14;
  }

  .invalid-feedback {
    display: block;
  }

  .page-header {
    margin-bottom: 2rem;
  }

  .page-title {
    color: #ffffff;
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  select.form-control option {
    background: #2A3038;
    color: white;
  }
</style>
@endpush

@section('content')
<div class="row">
  <div class="col-12">
    <div class="page-header">
      <h3 class="page-title">Add New Member</h3>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('memberships.index') }}">Memberships</a></li>
          <li class="breadcrumb-item active" aria-current="page">Add New</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8 mx-auto grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-4">Member Information</h4>
        <form action="{{ route('memberships.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <!-- Name -->
          <div class="form-group">
            <label for="name">Member Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                   value="{{ old('name') }}" 
                   placeholder="Enter member name">
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Age -->
          <div class="form-group">
            <label for="age">Age</label>
            <input type="number" name="age" id="age" class="form-control @error('age') is-invalid @enderror" 
                   value="{{ old('age') }}" 
                   placeholder="Enter age">
            @error('age')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Avatar -->
          <div class="form-group">
            <label for="avatar">Profile Picture</label>
            <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                   accept="image/*">
            <small class="form-text text-muted">Upload a profile picture (Max: 2MB)</small>
            @error('avatar')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Plan Type and Contact -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="plan_type">Plan Type</label>
                <select name="plan_type" id="plan_type" class="form-control @error('plan_type') is-invalid @enderror">
                  <option value="">Select Plan Type</option>
                  <option value="Monthly" {{ old('plan_type') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                  <option value="Session" {{ old('plan_type') == 'Session' ? 'selected' : '' }}>Session</option>
                </select>
                @error('plan_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" name="contact" id="contact" class="form-control @error('contact') is-invalid @enderror" 
                       value="{{ old('contact') }}" 
                       placeholder="e.g., 0912-345-6789"
                       pattern="^[+]?[0-9() ]+$"
                       title="Please enter a valid contact number (only numbers, +, (), and spaces allowed. NO minus signs!)">
                @error('contact')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Start Date and Due Date -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                       value="{{ old('start_date') }}">
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                       value="{{ old('due_date') }}">
                @error('due_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-4">
            <button type="submit" class="btn btn-primary mr-2">
              <i class="mdi mdi-check"></i> Add Member
            </button>
            <a href="{{ route('memberships.index') }}" class="btn btn-secondary">
              <i class="mdi mdi-close"></i> Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection