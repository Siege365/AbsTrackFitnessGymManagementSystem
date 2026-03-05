@extends('layouts.admin')

@section('title', 'Settings')

@push('styles')
@vite(['resources/css/configuration.css'])
@endpush

@section('content')

  <!-- Page Header with Section Toggle -->
  <div class="card page-header-card">
      <div class="card-body">
          <div>
              <h2 class="page-header-title">Settings</h2>
              <p class="page-header-subtitle">Manage membership plans, personal training rates & inventory categories</p>
          </div>
          <div class="config-toggle-container">
            <button class="config-toggle-btn active" data-section="manage">
              <i class="mdi mdi-clipboard-text-outline"></i>
              <span>Manage Plans</span>
            </button>
            <button class="config-toggle-btn" data-section="categories">
              <i class="mdi mdi-package-variant"></i>
              <span>Inventory Categories</span>
            </button>
          </div>
      </div>
  </div>

  {{-- Section Partials --}}
  @include('configuration.partials._plans')
  @include('configuration.partials._categories')

  {{-- Modals --}}
  @include('configuration.partials.modals._add-plan')
  @include('configuration.partials.modals._delete-plan')
  @include('configuration.partials.modals._toggle-status')
  @include('configuration.partials.modals._edit-category')
  @include('configuration.partials.modals._delete-category')

@endsection

@push('scripts')
@include('configuration.partials._scripts')
@endpush
