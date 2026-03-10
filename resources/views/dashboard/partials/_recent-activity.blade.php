{{-- Recent Activity Section: Latest payments, upcoming sessions, today's attendance --}}
<div class="dashboard-section">
    <h4 class="section-title">
        <i class="mdi mdi-history"></i> Recent Activity
    </h4>
    <div class="row">
        {{-- Recent Payments --}}
        <div class="col-xl-4 col-md-6 grid-margin stretch-card">
            <div class="card activity-card">
                <div class="card-body">
                    <div class="activity-header">
                        <h5 class="card-title mb-0">Recent Payments</h5>
                        <a href="{{ route('payments.history') }}" class="activity-view-all">View all</a>
                    </div>
                    <div class="activity-list">
                        @forelse($recentPayments as $payment)
                            <div class="activity-item">
                                <div class="activity-icon {{ $payment->is_refunded ? 'bg-danger-soft' : 'bg-success-soft' }}">
                                    <i class="mdi {{ $payment->is_refunded ? 'mdi-cash-refund text-danger' : 'mdi-cash-check text-success' }}"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-name mb-0">{{ $payment->customer_name }}</p>
                                    <p class="activity-meta mb-0">
                                        {{ $payment->receipt_number }} · {{ $payment->payment_method }}
                                    </p>
                                </div>
                                <div class="activity-amount {{ $payment->is_refunded ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($payment->total_amount, 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="activity-empty">
                                <i class="mdi mdi-receipt"></i>
                                <p>No recent payments</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Upcoming PT Sessions --}}
        <div class="col-xl-4 col-md-6 grid-margin stretch-card">
            <div class="card activity-card">
                <div class="card-body">
                    <div class="activity-header">
                        <h5 class="card-title mb-0">Upcoming PT Sessions</h5>
                        <a href="{{ route('sessions.pt.index') }}" class="activity-view-all">View all</a>
                    </div>
                    <div class="activity-list">
                        @forelse($upcomingPTSessions as $session)
                            <div class="activity-item">
                                <div class="activity-icon bg-info-soft">
                                    <i class="mdi mdi-dumbbell text-info"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-name mb-0">{{ $session->customer_name }}</p>
                                    <p class="activity-meta mb-0">
                                        {{ $session->trainer_name }} · {{ $session->scheduled_date->format('M d') }} at {{ \Carbon\Carbon::parse($session->scheduled_time)->format('g:i A') }}
                                    </p>
                                </div>
                                <div class="activity-badge">
                                    <span class="badge badge-outline-info">{{ ucfirst($session->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="activity-empty">
                                <i class="mdi mdi-calendar-blank"></i>
                                <p>No upcoming sessions</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Attendance --}}
        <div class="col-xl-4 col-md-12 grid-margin stretch-card">
            <div class="card activity-card">
                <div class="card-body">
                    <div class="activity-header">
                        <h5 class="card-title mb-0">Today's Check-ins</h5>
                        <a href="{{ route('sessions.attendance.index') }}" class="activity-view-all">View all</a>
                    </div>
                    <div class="activity-list">
                        @forelse($recentAttendance as $record)
                            <div class="activity-item">
                                <div class="activity-icon bg-primary-soft">
                                    <i class="mdi mdi-login text-primary"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-name mb-0">{{ $record->customer_name }}</p>
                                    <p class="activity-meta mb-0">
                                        {{ $record->customer_type }}
                                        @if($record->time_in)
                                            · In: {{ \Carbon\Carbon::parse($record->time_in)->format('g:i A') }}
                                        @endif
                                        @if($record->time_out)
                                            · Out: {{ \Carbon\Carbon::parse($record->time_out)->format('g:i A') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="activity-badge">
                                    @php
                                        $badgeClass = match($record->status) {
                                            'checked_in' => 'badge-outline-success',
                                            'checked_out' => 'badge-outline-secondary',
                                            default => 'badge-outline-info'
                                        };
                                        $statusText = match($record->status) {
                                            'checked_in' => 'In',
                                            'checked_out' => 'Out',
                                            default => ucfirst($record->status ?? 'N/A')
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="activity-empty">
                                <i class="mdi mdi-account-clock"></i>
                                <p>No check-ins yet today</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
