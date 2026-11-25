<!-- Sub-Module 3: OWASP Top 10 Vulnerability Matrix (Standardized Assessment Table) -->
<div class="card clean-security-card">
    <div class="card-header">
        <h4>
            <i class="mdi mdi-clipboard-check-outline text-success mr-1"></i> OWASP Top 10 Vulnerability Matrix
        </h4>
        <span class="badge badge-clean-pass">
            <i class="mdi mdi-check-all"></i> Overall Compliance: 90%
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table security-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Standard</th>
                        <th style="width: 26%;">Vulnerability Category</th>
                        <th style="width: 16%;">Compliance</th>
                        <th style="width: 48%;">Defensive Implementation in AbsTrack</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($owaspMatrix as $item)
                        <tr>
                            <td>
                                <span class="badge badge-clean-info font-weight-bold">{{ $item['id'] }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-white mb-1">{{ $item['title'] }}</div>
                                <small class="text-muted">{{ $item['description'] }}</small>
                            </td>
                            <td>
                                @if($item['status'] === 'pass')
                                    <span class="badge badge-clean-pass">
                                        <i class="mdi mdi-check"></i> {{ $item['compliance'] }}
                                    </span>
                                @else
                                    <span class="badge badge-clean-warn">
                                        <i class="mdi mdi-alert"></i> {{ $item['compliance'] }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-white-50" style="font-size: 0.88rem; line-height: 1.45;">
                                    {{ $item['details'] }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
