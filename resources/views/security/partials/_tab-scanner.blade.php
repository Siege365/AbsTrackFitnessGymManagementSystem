<!-- Sub-Module 1: System Hardening & Configuration Scanner (Executive Audit Sheet) -->
<div class="card clean-security-card">
    <div class="card-header">
        <h4>
            <i class="mdi mdi-tune-vertical text-warning mr-1"></i> System Hardening & Configuration Scanner
        </h4>
        <small class="text-muted">
            <i class="mdi mdi-clock-outline"></i> Last Scan: {{ $latestScan ? $latestScan->created_at->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}
        </small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table security-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Audit Parameter</th>
                        <th style="width: 18%;">Category</th>
                        <th style="width: 14%;">Status</th>
                        <th style="width: 43%;">Diagnostic Finding & Recommended Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($latestScan->results))
                        @foreach($latestScan->results as $key => $check)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-white mb-1">{{ $check['title'] ?? $key }}</div>
                                    <small class="text-muted">Parameter ID: <code>{{ $key }}</code></small>
                                </td>
                                <td>
                                    <span class="badge badge-clean-info">{{ $check['category'] ?? 'Core' }}</span>
                                </td>
                                <td>
                                    @if(($check['status'] ?? '') === 'pass')
                                        <span class="badge badge-clean-pass">
                                            <i class="mdi mdi-check"></i> PASS
                                        </span>
                                    @elseif(($check['status'] ?? '') === 'warning')
                                        <span class="badge badge-clean-warn">
                                            <i class="mdi mdi-alert"></i> WARNING
                                        </span>
                                    @else
                                        <span class="badge badge-clean-danger">
                                            <i class="mdi mdi-close"></i> CRITICAL
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-white-50 mb-1">{{ $check['detail'] ?? '' }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">
                                        <i class="mdi mdi-lightbulb-on-outline text-warning mr-1"></i> {{ $check['recommended'] ?? 'Maintain current configuration.' }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No scan results found. Click "Run Security Scan" to perform an audit.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
