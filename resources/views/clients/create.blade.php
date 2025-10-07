@extends('layouts.admin')

@section('title', 'Add New Client')

@section('content')
<div class="content-wrapper" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); min-height: 100vh;">
    <div class="container-fluid py-4">
        <!-- Breadcrumb Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-white mb-2" style="font-weight: 500;">Add New Client</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb" style="background: transparent;">
                        <li class="breadcrumb-item"><a href="{{ route('clients.index') }}" class="text-white-50">Clients</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Add New</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Form Card -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Name -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Client Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" 
                                       placeholder="Enter client name"
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Age -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Age</label>
                                <input type="number" name="age" class="form-control @error('age') is-invalid @enderror" 
                                       value="{{ old('age') }}" 
                                       placeholder="Enter age"
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                @error('age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Avatar -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Profile Picture</label>
                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                                       accept="image/*"
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                <small class="text-white-50">Upload a profile picture (Max: 2MB)</small>
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Plan Type and Contact -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="text-white mb-2" style="font-weight: 500;">Plan Type</label>
                                        <select name="plan_type" class="form-control @error('plan_type') is-invalid @enderror"
                                                style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
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
                                    <div class="form-group mb-4">
                                        <label class="text-white mb-2" style="font-weight: 500;">Contact Number</label>
                                        <input type="text" name="contact" class="form-control @error('contact') is-invalid @enderror" 
                                               value="{{ old('contact') }}" 
                                               placeholder="e.g., 0912-345-6789"
                                               pattern="^[+]?[0-9() ]+$"
                                               title="Please enter a valid contact number (only numbers, +, (), and spaces allowed. NO minus signs!)"
                                               style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                        @error('contact')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Start Date and Due Date -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="text-white mb-2" style="font-weight: 500;">Start Date</label>
                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                               value="{{ old('start_date') }}"
                                               style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-4">
                                        <label class="text-white mb-2" style="font-weight: 500;">Due Date</label>
                                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                                               value="{{ old('due_date') }}"
                                               style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="{{ route('clients.index') }}" class="btn btn-secondary" style="padding: 10px 24px; border-radius: 8px;">
                                    <i class="mdi mdi-close"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-success" style="background: #4CAF50; border: none; padding: 10px 24px; border-radius: 8px;">
                                    <i class="mdi mdi-check"></i> Add Client
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #4CAF50;
        color: white;
        box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
    }
    select.form-control option {
        background: #2c3e50;
        color: white;
    }
</style>
@endsection
