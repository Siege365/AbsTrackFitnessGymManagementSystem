<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CyberShield Security Assessment & Vulnerability Audit Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #FFA726;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a2e;
            margin: 0 0 4px 0;
        }
        .subtitle {
            font-size: 11px;
            color: #7f8c8d;
            margin: 0;
        }
        .confidential-tag {
            display: inline-block;
            background: #fc424a;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .meta-box {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 10px;
        }
        .score-card {
            background: #191C24;
            color: #fff;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
        }
        .score-number {
            font-size: 28px;
            font-weight: bold;
            color: #00d25b;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a2e;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin: 18px 0 10px 0;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background: #282A36;
            color: #fff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
        }
        table.data-table td {
            border-bottom: 1px solid #e9ecef;
            padding: 6px 8px;
            font-size: 10px;
            vertical-align: top;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .badge-pass { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-critical { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 9px;
            color: #7f8c8d;
            text-align: center;
        }
        .signatures {
            width: 100%;
            margin-top: 35px;
        }
        .sig-line {
            border-top: 1px solid #333;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <div class="title">AbsTrack Fitness Gym Management System</div>
                <div class="subtitle">CyberShield AI Security Assessment & Vulnerability Audit Report</div>
            </td>
            <td style="text-align: right;">
                <span class="confidential-tag">CONFIDENTIAL &bull; OWNER / MANAGER / ADMIN ONLY</span><br>
                <span style="font-size: 9px; color: #7f8c8d;">Document Ref: SEC-{{ now()->format('Ymd') }}-{{ $scan->id ?? '01' }}</span>
            </td>
        </tr>
    </table>

    <!-- Executive Metadata -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 25%;"><strong>Audit Date:</strong> {{ $generatedAt }}</td>
                <td style="width: 25%;"><strong>Authorized Auditor:</strong> {{ $generatedBy }}</td>
                <td style="width: 25%;"><strong>Scan Type:</strong> Comprehensive Full Audit</td>
                <td rowspan="2" style="width: 25%;">
                    <div class="score-card">
                        <div class="score-number">{{ $scan->health_score ?? 92 }}%</div>
                        <div style="font-size: 9px; color: #aaa;">SYSTEM HEALTH SCORE</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong>Passing Checks:</strong> {{ $passedCount }} / {{ $totalCount }}</td>
                <td><strong>Active Threat Alerts:</strong> {{ $activeAlertsCount }}</td>
                <td><strong>Framework:</strong> Laravel 12 &bull; OWASP Top 10</td>
            </tr>
        </table>
    </div>

    <!-- Section 1: Hardening Checks -->
    <div class="section-title">1. System Configuration & Hardening Checks</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Audit Item</th>
                <th style="width: 18%;">Category</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 45%;">Diagnostic Finding & Remediation</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($scan->results))
                @foreach($scan->results as $key => $check)
                    <tr>
                        <td><strong>{{ $check['title'] ?? $key }}</strong></td>
                        <td>{{ $check['category'] ?? 'Core' }}</td>
                        <td>
                            @if(($check['status'] ?? '') === 'pass')
                                <span class="badge badge-pass">PASS</span>
                            @elseif(($check['status'] ?? '') === 'warning')
                                <span class="badge badge-warning">ATTENTION</span>
                            @else
                                <span class="badge badge-critical">CRITICAL</span>
                            @endif
                        </td>
                        <td>
                            {{ $check['detail'] ?? '' }}<br>
                            <em style="color: #666; font-size: 9px;">Recommendation: {{ $check['recommended'] ?? 'Maintain current configuration.' }}</em>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="4">No scan results recorded.</td></tr>
            @endif
        </tbody>
    </table>

    <!-- Section 2: AI Threat Intelligence -->
    <div class="section-title">2. AI Threat Intelligence & Anomaly Records</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Severity</th>
                <th style="width: 25%;">Incident / Anomaly</th>
                <th style="width: 15%;">Source IP / User</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 36%;">Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($threatAlerts as $alert)
                <tr>
                    <td>
                        @if($alert->severity === 'critical')
                            <span class="badge badge-critical">CRITICAL</span>
                        @elseif($alert->severity === 'high')
                            <span class="badge badge-critical">HIGH</span>
                        @elseif($alert->severity === 'medium')
                            <span class="badge badge-warning">MEDIUM</span>
                        @else
                            <span class="badge badge-info">{{ strtoupper($alert->severity) }}</span>
                        @endif
                    </td>
                    <td><strong>{{ $alert->title }}</strong></td>
                    <td>{{ $alert->source_ip }}<br><small>{{ $alert->affected_user }}</small></td>
                    <td><span class="badge {{ $alert->status === 'resolved' ? 'badge-pass' : 'badge-warning' }}">{{ strtoupper($alert->status) }}</span></td>
                    <td>{{ $alert->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No threat alerts detected in current audit window.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section 3: OWASP Top 10 Matrix -->
    <div class="section-title">3. OWASP Top 10 Web Application Compliance</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 25%;">Vulnerability Category</th>
                <th style="width: 15%;">Compliance</th>
                <th style="width: 50%;">Applied Defensive Implementation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($owaspMatrix as $item)
                <tr>
                    <td><strong>{{ $item['id'] }}</strong></td>
                    <td>{{ $item['title'] }}</td>
                    <td>
                        @if($item['status'] === 'pass')
                            <span class="badge badge-pass">{{ $item['compliance'] }}</span>
                        @else
                            <span class="badge badge-warning">{{ $item['compliance'] }}</span>
                        @endif
                    </td>
                    <td>{{ $item['details'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Sign-off Block -->
    <table class="signatures">
        <tr>
            <td style="width: 45%;">
                <div class="sig-line">
                    {{ $generatedBy }}<br>
                    <small style="font-weight: normal; color: #7f8c8d;">Security Officer / System Manager</small>
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%;">
                <div class="sig-line">
                    Executive Gym Owner Sign-off<br>
                    <small style="font-weight: normal; color: #7f8c8d;">AbsTrack Fitness Gym Executive Management</small>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        AbsTrack Fitness Gym Management System &bull; CyberShield AI Subsystem &bull; Generated dynamically via Laravel 12 &bull; Page 1 of 1
    </div>

</body>
</html>

