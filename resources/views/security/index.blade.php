@extends('layouts.admin')

@section('title', 'CyberShield AI - Security Assessment & Vulnerability Subsystem')

@push('styles')
@vite(['resources/css/security-assessment.css'])
@endpush

@section('content')

  <!-- 1. Page Header (Matches Native AbsTrack Header Style) -->
  <div class="card page-header-card security-page-header">
      <div class="card-body page-header-body">
          <div>
              <h2 class="page-header-title">CyberShield Security Center</h2>
              <p class="page-header-subtitle">System Hardening, Real-time Threat Intelligence & Vulnerability Audits.</p>
          </div>
          <div class="security-header-actions">
              <span class="security-role-pill">
                  <i class="mdi mdi-shield-account"></i> Owner, Manager & Admin Clearance
              </span>
              <a href="{{ route('security.export-report') }}" class="btn btn-outline-warning" target="_blank">
                  <i class="mdi mdi-file-pdf-box"></i> Export Audit PDF
              </a>
              <button class="btn btn-page-action" id="btnRunSystemScan">
                  <i class="mdi mdi-radar"></i> Run Security Scan
              </button>
          </div>
      </div>
  </div>

  <!-- 2. Standard AbsTrack KPI Statistics Cards -->
  @include('security.partials._kpi-cards')

  <!-- 3. Page Segmented Tabs (Matches Native AbsTrack Design) -->
  <div class="security-tabs-container">
      <button class="security-tab-btn active" data-tab="tab-scanner">
          <i class="mdi mdi-tune-vertical"></i>
          <span>Hardening Scanner</span>
          <span class="tab-count-pill">{{ $latestScan ? $latestScan->total_checks : 9 }}</span>
      </button>

      <button class="security-tab-btn" data-tab="tab-threats">
          <i class="mdi mdi-alert-decagram-outline"></i>
          <span>Threat Intelligence</span>
          <span class="tab-count-pill">{{ $activeAlertsCount }}</span>
      </button>

      <button class="security-tab-btn" data-tab="tab-owasp">
          <i class="mdi mdi-clipboard-check-outline"></i>
          <span>OWASP Matrix</span>
          <span class="tab-count-pill">{{ $owaspScore }}%</span>
      </button>

      <button class="security-tab-btn" data-tab="tab-advisor">
          <i class="mdi mdi-robot-outline"></i>
          <span>AI Security Advisor</span>
          <span class="tab-count-pill">Copilot</span>
      </button>
  </div>

  <!-- 4. Sub-Module Content Panes -->
  <div class="tab-content">
      <!-- Tab 1: Hardening & Configuration Scanner -->
      <div class="security-tab-pane active" id="tab-scanner">
          @include('security.partials._tab-scanner')
      </div>

      <!-- Tab 2: AI Threat Intelligence -->
      <div class="security-tab-pane" id="tab-threats">
          @include('security.partials._tab-threats')
      </div>

      <!-- Tab 3: OWASP Vulnerability Matrix -->
      <div class="security-tab-pane" id="tab-owasp">
          @include('security.partials._tab-owasp')
      </div>

      <!-- Tab 4: AI Security Advisor -->
      <div class="security-tab-pane" id="tab-advisor">
          @include('security.partials._tab-advisor')
      </div>
  </div>

@endsection

@push('scripts')
@vite(['resources/js/pages/security-assessment.js'])
@endpush
