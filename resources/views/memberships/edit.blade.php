@extends('layouts.admin')

@section('title', 'Edit Member')

@section('content')
<div class="content-wrapper" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%); min-height: 100vh;">
    <div class="container-fluid py-4">
        <!-- Breadcrumb Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h3 class="text-white mb-2" style="font-weight: 500;">Edit Member</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb" style="background: transparent;">
                        <li class="breadcrumb-item"><a href="{{ route('memberships.index') }}" class="text-white-50">Memberships</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Edit Member</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if(session('error'))
        <div class="row mb-3">
            <div class="col-lg-8 mx-auto">
                <div class="alert alert-danger alert-dismissible fade show" style="background: rgba(220, 53, 69, 0.9); border: none; border-radius: 10px; color: white;" role="alert">
                    <i class="mdi mdi-alert-circle mr-2"></i>
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="row mb-3">
            <div class="col-lg-8 mx-auto">
                <div class="alert alert-danger alert-dismissible fade show" style="background: rgba(220, 53, 69, 0.9); border: none; border-radius: 10px; color: white;" role="alert">
                    <i class="mdi mdi-alert-circle mr-2"></i>
                    <strong>Validation Errors!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close text-white" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Card -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card" style="background: rgba(30, 58, 95, 0.8); border: none; border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('memberships.update', $membership) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Name -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Member Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $membership->name) }}" 
                                       placeholder="Enter member name"
                                       required
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Age -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Age</label>
                                <input type="number" name="age" class="form-control @error('age') is-invalid @enderror" 
                                       value="{{ old('age', $membership->age) }}" 
                                       placeholder="Enter age"
                                       min="1"
                                       max="120"
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                @error('age')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Avatar -->
                            <div class="form-group mb-4">
                                <label class="text-white mb-2" style="font-weight: 500;">Profile Picture</label>
                                @if($membership->avatar)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $membership->avatar) }}" alt="{{ $membership->name }}" id="avatarPreview" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid rgba(255, 255, 255, 0.2);">
                                    </div>
                                @else
                                    <div class="mb-2" id="avatarPreview">
                                        <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 48px; color: white; font-weight: 600;">
                                            {{ strtoupper(substr($membership->name, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" 
                                       accept="image/*"
                                       onchange="previewImage(event)"
                                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                <small class="text-white-50">Upload a new profile picture to replace the current one (Max: 2MB)</small>
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
                                                required
                                                style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                            <option value="">Select Plan Type</option>
                                            <option value="Monthly" {{ old('plan_type', $membership->plan_type) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="Session" {{ old('plan_type', $membership->plan_type) == 'Session' ? 'selected' : '' }}>Session</option>
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
                                               value="{{ old('contact', $membership->contact) }}" 
                                               placeholder="e.g., 0912-345-6789"
                                               required
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
                                               value="{{ old('start_date', $membership->start_date->format('Y-m-d')) }}"
                                               required
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
                                               value="{{ old('due_date', $membership->due_date->format('Y-m-d')) }}"
                                               required
                                               style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 12px;">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status Info (Read-only display) -->
                            <div class="alert" style="background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.3); border-radius: 8px;">
                                <i class="mdi mdi-information text-warning"></i>
                                <span class="text-white-50">Status will be automatically calculated based on the dates provided.</span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="{{ route('memberships.index') }}" class="btn btn-secondary" style="padding: 10px 24px; border-radius: 8px;">
                                    <i class="mdi mdi-close"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-warning" style="background: #FFA500; border: none; padding: 10px 24px; border-radius: 8px; color: white;">
                                    <i class="mdi mdi-pencil"></i> Update Member
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
        border-color: #FFA500;
        color: white;
        box-shadow: 0 0 0 0.2rem rgba(255, 165, 0, 0.25);
    }
    select.form-control option {
        background: #2c3e50;
        color: white;
    }
</style>

<script>
    function previewImage(event) {
        const preview = document.getElementById('avatarPreview');
        const file = event.target.files[0];
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 3px solid rgba(255, 255, 255, 0.2);">';
            }
            
            reader.readAsDataURL(file);
        }
    }
</script>
@endsection
