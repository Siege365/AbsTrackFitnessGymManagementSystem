<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AbsTrack Fitness Gym - Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #FFA726;
        }
        
        .header h1 {
            color: #FFA726;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 11px;
        }
        
        .meta-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .meta-info p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background: #FFA726;
            color: white;
            padding: 8px 10px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .kpi-row {
            display: table-row;
        }
        
        .kpi-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .kpi-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .kpi-change {
            font-size: 10px;
            font-weight: bold;
        }
        
        .kpi-change.positive {
            color: #4CAF50;
        }
        
        .kpi-change.negative {
            color: #F44336;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        table th {
            background: #f5f5f5;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 11px;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        
        table tr:nth-child(even) {
            background: #fafafa;
        }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AbsTrack Fitness Gym</h1>
        <p>Performance Report & Analytics</p>
    </div>

    <div class="meta-info">
        <p><strong>Generated:</strong> {{ $data['generated_at'] }}</p>
        <p><strong>Period:</strong> {{ $data['date_range'] }}</p>
        <p><strong>Report Type:</strong> {{ ucfirst($scope) }}</p>
    </div>

    @if(isset($data['kpis']))
    <div class="section">
        <div class="section-title">Key Performance Indicators</div>
        <div class="kpi-grid">
            <div class="kpi-row">
                <div class="kpi-cell">
                    <div class="kpi-label">Monthly Revenue</div>
                    <div class="kpi-value">₱{{ number_format($data['kpis']['monthly_revenue'], 2) }}</div>
                    <div class="kpi-change {{ $data['kpis']['revenue_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $data['kpis']['revenue_change'] >= 0 ? '↑' : '↓' }} {{ abs($data['kpis']['revenue_change']) }}%
                    </div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Retail Sales</div>
                    <div class="kpi-value">₱{{ number_format($data['kpis']['retail_sales'], 2) }}</div>
                    <div class="kpi-change {{ $data['kpis']['retail_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $data['kpis']['retail_change'] >= 0 ? '↑' : '↓' }} {{ abs($data['kpis']['retail_change']) }}%
                    </div>
                </div>
            </div>
            <div class="kpi-row">
                <div class="kpi-cell">
                    <div class="kpi-label">Membership Revenue</div>
                    <div class="kpi-value">₱{{ number_format($data['kpis']['membership_revenue'], 2) }}</div>
                    <div class="kpi-change {{ $data['kpis']['membership_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $data['kpis']['membership_change'] >= 0 ? '↑' : '↓' }} {{ abs($data['kpis']['membership_change']) }}%
                    </div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">PT Revenue</div>
                    <div class="kpi-value">₱{{ number_format($data['kpis']['pt_revenue'], 2) }}</div>
                    <div class="kpi-change {{ $data['kpis']['pt_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $data['kpis']['pt_change'] >= 0 ? '↑' : '↓' }} {{ abs($data['kpis']['pt_change']) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['revenue']) && isset($data['revenue']['labels']) && count($data['revenue']['labels']) > 0)
    <div class="section">
        <div class="section-title">Revenue Over Time</div>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Retail</th>
                    <th class="text-right">Membership</th>
                    <th class="text-right">PT Revenue</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $labels = $data['revenue']['labels'];
                    $retail = $data['revenue']['datasets'][0]['data'] ?? [];
                    $membership = $data['revenue']['datasets'][1]['data'] ?? [];
                    $pt = $data['revenue']['datasets'][2]['data'] ?? [];
                @endphp
                @foreach($labels as $index => $label)
                @php
                    $r = $retail[$index] ?? 0;
                    $m = $membership[$index] ?? 0;
                    $p = $pt[$index] ?? 0;
                    $total = $r + $m + $p;
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-right">₱{{ number_format($r, 2) }}</td>
                    <td class="text-right">₱{{ number_format($m, 2) }}</td>
                    <td class="text-right">₱{{ number_format($p, 2) }}</td>
                    <td class="text-right">₱{{ number_format($total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['products']) && count($data['products']) > 0)
    <div class="section">
        <div class="section-title">Top Selling Products</div>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th class="text-center">Quantity Sold</th>
                    <th class="text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['products'] as $product)
                <tr>
                    <td>{{ $product['name'] }}</td>
                    <td class="text-center">{{ $product['quantity'] }}</td>
                    <td class="text-right">₱{{ number_format($product['revenue'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['breakdown']) && count($data['breakdown']) > 0)
    <div class="section">
        <div class="section-title">Revenue Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Revenue Source</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = array_sum(array_column($data['breakdown'], 'amount'));
                @endphp
                @foreach($data['breakdown'] as $item)
                <tr>
                    <td>{{ $item['source'] }}</td>
                    <td class="text-right">₱{{ number_format($item['amount'], 2) }}</td>
                    <td class="text-right">{{ $total > 0 ? number_format(($item['amount'] / $total) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background: #f0f0f0;">
                    <td>TOTAL</td>
                    <td class="text-right">₱{{ number_format($total, 2) }}</td>
                    <td class="text-right">100%</td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['transactions']) && count($data['transactions']) > 0)
    <div class="section">
        <div class="section-title">Transaction History by Payment Method</div>
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-center">Number of Transactions</th>
                    <th class="text-right">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalCount = array_sum(array_column($data['transactions'], 'count'));
                @endphp
                @foreach($data['transactions'] as $transaction)
                <tr>
                    <td>{{ $transaction['method'] }}</td>
                    <td class="text-center">{{ $transaction['count'] }}</td>
                    <td class="text-right">{{ $totalCount > 0 ? number_format(($transaction['count'] / $totalCount) * 100, 1) : 0 }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>AbsTrack Fitness Gym Management System | Generated on {{ date('F d, Y h:i A') }}</p>
    </div>
</body>
</html>
