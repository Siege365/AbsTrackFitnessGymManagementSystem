<!-- Sub-Module 2: AI Threat Intelligence & Real-time Logs -->
<div class="row">
    <!-- Left Column: Threat Intelligence & Anomaly Alerts -->
    <div class="col-12 col-lg-7 grid-margin stretch-card">
        <div class="card clean-security-card w-100">
            <div class="card-header">
                <h4>
                    <i class="mdi mdi-alert-decagram-outline text-warning mr-1"></i> AI Threat & Anomaly Feed
                </h4>
                <button class="btn btn-sm btn-outline-warning" id="btnAnalyzeThreats">
                    <i class="mdi mdi-radar mr-1"></i> Analyze Logs with AI
                </button>
            </div>
            <div class="card-body">
                @forelse($threatAlerts as $alert)
                    <div class="clean-threat-item" id="threatCard_{{ $alert->id }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="clean-threat-title">{{ $alert->title }}</div>
                            @if($alert->severity === 'critical' || $alert->severity === 'high')
                                <span class="badge badge-clean-danger">{{ strtoupper($alert->severity) }}</span>
                            @elseif($alert->severity === 'medium')
                                <span class="badge badge-clean-warn">{{ strtoupper($alert->severity) }}</span>
                            @else
                                <span class="badge badge-clean-info">{{ strtoupper($alert->severity) }}</span>
                            @endif
                        </div>
                        <div class="clean-threat-desc">{{ $alert->description }}</div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-dark">
                            <div class="clean-threat-meta">
                                <span><i class="mdi mdi-ip-network-outline"></i> {{ $alert->source_ip ?? '127.0.0.1' }}</span>
                                @if($alert->affected_user)
                                    <span><i class="mdi mdi-account-outline"></i> {{ $alert->affected_user }}</span>
                                @endif
                                <span><i class="mdi mdi-clock-outline"></i> {{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <div>
                                @if($alert->status === 'resolved')
                                    <span class="badge badge-clean-pass"><i class="mdi mdi-check"></i> Resolved</span>
                                @else
                                    <button class="btn btn-sm btn-outline-success btn-resolve-alert" data-id="{{ $alert->id }}">
                                        <i class="mdi mdi-check"></i> Mark Resolved
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="mdi mdi-shield-check text-success" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">No active threat alerts detected in current log window.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Activity Logs -->
    <div class="col-12 col-lg-5 grid-margin stretch-card">
        <div class="card clean-security-card w-100">
            <div class="card-header">
                <h4>
                    <i class="mdi mdi-history text-info mr-1"></i> Security Audit Trail
                </h4>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-light">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table security-table">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSecurityLogs as $log)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-white">{{ $log->user_name ?? 'System' }}</div>
                                        <small class="text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-clean-info">{{ ucfirst($log->action ?? 'Event') }}</span>
                                    </td>
                                    <td>{{ ucfirst($log->module ?? 'General') }}</td>
                                    <td>
                                        <small class="text-muted">{{ $log->created_at ? $log->created_at->format('h:i A') : '--:--' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent activity logs recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
