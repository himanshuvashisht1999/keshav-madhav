<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Customer Master Records</title>
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
        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .customer-table th {
            background: #333;
            color: #fff;
            padding: 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        .customer-table td {
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
                <div class="company-name">SNAPKID</div>
                <div style="font-size: 9px; color: #666; margin-top: 5px;">Management System Customer Master</div>
            </td>
            <td class="report-title">
                Customer Directory
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Total Records:</strong> {{ count($customers) }}<br/>
                @if ($hasDateFilter)
                    <strong>Period:</strong> {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d-M-Y') : 'Beginning' }} to {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d-M-Y') : 'Today' }}
                @endif
            </td>
            <td width="50%" class="text-right">
                <strong>Generated On:</strong> {{ date('d-M-Y H:i') }}
            </td>
        </tr>
    </table>

    <table class="customer-table">
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="20%">Name</th>
                <th width="15%">Phone</th>
                <th width="15%">Agent Name</th>
                <th width="10%">Type</th>
                <th width="15%" class="text-right">Opening Balance</th>
                <th width="12%" class="text-right">Balance</th>
                <th width="8%" class="text-right">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $key => $customer)
                @php
                    $opBal = ($hasDateFilter && isset($calculatedBalances[$customer->id])) ? $calculatedBalances[$customer->id]['opening_balance'] : null;
                    $clBal = ($hasDateFilter && isset($calculatedBalances[$customer->id])) ? $calculatedBalances[$customer->id]['closing_balance'] : $customer->balance;
                @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->phone ?? '-' }}</td>
                    <td>{{ $customer->agent ? $customer->agent->name : '-' }}</td>
                    <td>{{ ucfirst($customer->type) }}</td>
                    <td class="text-right">
                        @if ($opBal !== null)
                            {{ number_format(abs($opBal), 2) }} ({{ $opBal >= 0 ? 'Cr' : 'Dr' }})
                        @elseif ($customer->currentOpeningBalance)
                            @php
                                $type = $customer->currentOpeningBalance->balance_type;
                            @endphp
                            {{ number_format($customer->currentOpeningBalance->amount, 2) }} ({{ $type == 'Credit' ? 'Cr' : 'Dr' }})
                        @else
                            0.00
                        @endif
                    </td>
                    <td class="text-right text-bold" style="color: {{ $clBal >= 0 ? '#28a745' : '#dc3545' }}">
                        {{ number_format(abs($clBal), 2) }} ({{ $clBal >= 0 ? 'Cr' : 'Dr' }})
                    </td>
                    <td class="text-right">
                        @if($customer->status == 1)
                            Active
                        @else
                            Inactive
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">No customers found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer generated document. | SNAPKID</p>
    </div>
</body>
</html>
