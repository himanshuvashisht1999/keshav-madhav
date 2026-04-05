<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fabric Shipment Report - {{ $data->shipment_id }}</title>
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
            color: #007bff;
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
            background-color: #343a40;
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

        .text-warning {
            color: #ffc107;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-info {
            color: #17a2b8;
        }

        .qr-img {
            width: 60px;
            height: 60px;
        }

        .barcode-container {
            text-align: center;
        }

        .barcode-img {
            width: 120px;
            height: 40px;
        }

        .barcode-number {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Fabric Shipment Report</h1>
        <p>Shipment ID: {{ $data->shipment_id }}</p>
    </div>

    <div class="section">
        <div class="section-title">Summary Information</div>
        <div class="summary-row">
            <div class="summary-item">
                <small>Amount</small>
                <h5>&#8377; {{ number_format($data->amount ?? 0, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>GST ({{ $data->gst_percentage ?? 0 }}%)</small>
                <h5 class="text-warning">&#8377; {{ number_format($data->gst_amount ?? 0, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Other Charges</small>
                <h5 class="text-info">&#8377; {{ number_format($data->other_charges ?? 0, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Total Amount</small>
                <h5 class="text-success">&#8377; {{ number_format($data->total_amount ?? 0, 2) }}</h5>
            </div>
            <div class="summary-item">
                <small>Total Rolls</small>
                <h5>{{ $data->details->count() }}</h5>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">General Details</div>
        <table class="info-table">
            <tr>
                <td class="label">Vendor</td>
                <td>{{ $data->vendor->name ?? '-' }}</td>
                <td class="label">Warehouse</td>
                <td>{{ $data->cutting_master->cutting_master_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td>{{ \Carbon\Carbon::parse($data->time)->format('j M Y') }}</td>
                <td class="label">Received By</td>
                <td>{{ $data->received_by ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Payment Status</td>
                <td>
                    @php
                        $paid = $data->paid_amount;
                        $total = $data->total_amount;
                    @endphp
                    @if($paid >= $total && $total > 0)
                        <span class="text-success">Paid</span>
                    @else
                        <span class="text-danger">Unpaid</span> (Paid: &#8377;{{ number_format($paid, 2) }})
                    @endif
                </td>
                <td class="label">Bill No</td>
                <td>{{ $data->bill_no ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Roll Details</div>
        <table class="details-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fabric</th>
                    <th>Price/Meter</th>
                    <th>Roll No</th>
                    <th>Meter</th>
                    <th>Barcode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data->details as $key => $detail)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $detail->fabric->name ?? '-' }}</td>
                        <td>&#8377; {{ number_format($detail->price_per_meter ?? 0, 2) }}</td>
                        <td>{{ $detail->roll_number }}</td>
                        <td>{{ $detail->meter }}</td>
                        <td>
                            <div class="barcode-container">
                                @php
                                    $barcodeFilename = $detail->getRawOriginal('barcode');
                                    $barcodePath = public_path('assets/barcodes/' . $barcodeFilename);
                                @endphp
                                @if($barcodeFilename && file_exists($barcodePath))
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($barcodePath)) }}"
                                        class="barcode-img">
                                @else
                                    -
                                @endif
                                <div class="barcode-number">{{ $detail->qrcode_number }}</div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No details found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        Generated on {{ date('j M Y') }} | Keshav Madhav
    </div>

</body>

</html>