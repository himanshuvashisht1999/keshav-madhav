<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fabric Return Report - {{ $return->return_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #dc3545;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-weight: bold;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            padding: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        .info-table .label {
            font-weight: bold;
            background-color: #f1f1f1;
            width: 30%;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .summary-item small {
            display: block;
            color: #666;
            font-size: 11px;
            margin-bottom: 5px;
        }

        .summary-item h5 {
            margin: 0;
            font-size: 18px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .details-table th {
            background-color: #dc3545;
            color: #fff;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-info {
            color: #17a2b8;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Fabric Return Report</h1>
        <p>Return Number: {{ $return->return_number }}</p>
    </div>

    <div class="section">
        <div class="section-title">Return Summary</div>
        <div class="summary-row">
            <div class="summary-item">
                <small>Sub-total</small>
                <h5>&#8377; {{ number_format($return->sub_total, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>GST ({{ $return->gst_percentage }}%)</small>
                <h5>&#8377; {{ number_format($return->gst_amount, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Other Charges</small>
                <h5>&#8377; {{ number_format($return->other_charges, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Discount</small>
                <h5>-&#8377; {{ number_format($return->discount, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Total Return Amount</small>
                <h5 class="text-danger">&#8377; {{ number_format($return->total_amount, 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Shipment Information</div>
        <table class="info-table">
            <tr>
                <td class="label">Vendor</td>
                <td>{{ $return->receipt->vendor->name ?? '-' }}</td>
                <td class="label">Shipment ID</td>
                <td>{{ $return->receipt->shipment_id }}</td>
            </tr>
            <tr>
                <td class="label">Return Date</td>
                <td>{{ \Carbon\Carbon::parse($return->date)->format('j M Y') }}</td>
                <td class="label">Remarks</td>
                <td>{{ $return->remarks ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Returned Roll Details</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fabric</th>
                    <th>Roll No</th>
                    <th>Returned Meter</th>
                    <th>Price/Meter</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->details as $key => $detail)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $detail->fabric->name ?? '-' }}</td>
                        <td>{{ $detail->receipt_detail->roll_number ?? '-' }}</td>
                        <td>{{ $detail->return_meter }} mtr</td>
                        <td>&#8377; {{ number_format($detail->price_per_meter, 2) }}</td>
                        <td>&#8377; {{ number_format($detail->return_meter * $detail->price_per_meter, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Total Meters:</td>
                    <td>{{ number_format($return->details->sum('return_meter'), 2) }} mtr</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Generated on {{ date('j M Y') }} | Keshav Madhav
    </div>

</body>

</html>
