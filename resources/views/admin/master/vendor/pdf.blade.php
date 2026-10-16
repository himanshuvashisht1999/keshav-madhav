<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Vendor Master Records</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
        .report-title {
            font-size: 16px;
            text-align: right;
            text-transform: uppercase;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .info-table td {
            vertical-align: top;
            padding: 5px 0;
        }
        .vendor-table {
            width: 100%;
            border-collapse: collapse;
        }
        .vendor-table th {
            background: #333;
            color: #fff;
            padding: 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        .vendor-table td {
            border-bottom: 1px solid #eee;
            padding: 6px;
            font-size: 9px;
        }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="company-name">@include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID'])</div>
                <div style="font-size: 9px; color: #666; margin-top: 5px;">Management System Vendor Master</div>
            </td>
            <td class="report-title">
                Vendor Directory
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Total Records:</strong> {{ count($vendors) }}<br/>
                @if ($hasDateFilter)
                    <strong>Period:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-M-Y') : 'Beginning' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-M-Y') : 'Today' }}
                @endif
            </td>
            <td width="50%" class="text-right">
                <strong>Generated On:</strong> {{ date('d-M-Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="vendor-table">
        <thead>
            <tr>
                <th width="6%">ID</th>
                <th width="24%">Name</th>
                <th width="16%">Phone</th>
                <th width="20%">Email</th>
                <th width="14%" class="text-right">Opening Balance</th>
                <th width="12%" class="text-right">Balance</th>
                <th width="8%" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $key => $vendor)
                @php
                    $opBal = ($hasDateFilter && isset($calculatedBalances[$vendor->id])) ? $calculatedBalances[$vendor->id]['opening_balance'] : null;
                    $clBal = ($hasDateFilter && isset($calculatedBalances[$vendor->id])) ? $calculatedBalances[$vendor->id]['closing_balance'] : $vendor->balance;
                @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $vendor->name }}</td>
                    <td>{{ $vendor->phone ?? '-' }}</td>
                    <td>{{ $vendor->email ?? '-' }}</td>
                    <td class="text-right">
                        @if ($opBal !== null)
                            {{ number_format(abs($opBal), 2) }} ({{ $opBal >= 0 ? 'Cr' : 'Dr' }})
                        @elseif ($vendor->currentOpeningBalance)
                            @php
                                $type = $vendor->currentOpeningBalance->balance_type;
                            @endphp
                            {{ number_format($vendor->currentOpeningBalance->amount, 2) }} ({{ $type == 'Credit' ? 'Cr' : 'Dr' }})
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right text-bold" style="color: {{ $clBal >= 0 ? '#36b54a' : '#dc3545' }}">
                        {{ number_format(abs($clBal), 2) }} ({{ $clBal >= 0 ? 'Cr' : 'Dr' }})
                    </td>
                    <td class="text-right">
                        @if($vendor->status == 1)
                            Active
                        @else
                            Inactive
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No vendors found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer generated document. | @include('admin.pdf.partials.brand_name', ['name' => 'SNAPKID', 'height' => 10])</p>
    </div>
</body>
</html>
