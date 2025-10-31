<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Issue & Receive Slip</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 20px; }
        .header { text-align: center; font-weight: bold; font-size: 18px; margin-bottom: 10px; text-transform: uppercase; }
        .sub-header { text-align: center; font-size: 13px; color: #555; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #444; padding: 6px; font-size: 13px; }
        th { background: #f0f0f0; text-align: left; }
        .signature { margin-top: 30px; display: flex; justify-content: space-between; }
        .signature div { width: 45%; text-align: center; font-size: 13px; }
        .separator {
            border-top: 2px dotted #000;
            margin: 25px 0;
            position: relative;
        }
        .separator::before {
            content: "✂️";
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border: none;
            font-size: 13px;
        }
        .info-table td {
            border: none;
            padding: 4px 0;
        }
        .info-table strong {
            color: #222;
        }
    </style>
</head>
<body>

    {{-- ================= FABRIC ISSUE SLIP ================= --}}
    <div class="header">FABRIC ISSUE SLIP</div>

    <table class="info-table">
        <tr>
            <td><strong>Order SKU:</strong> {{ $order->sku ?? 'N/A' }}</td>
            <td><strong>Product SKU:</strong> {{ $orderProduct->product_sku ?? 'N/A' }}</td>
            <td><strong>Date:</strong> {{ now()->format('d M, Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Fabric</th>
                <th>Roll No</th>
                <th>Issued (Meter)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issuedData as $row)
            <tr>
                <td>{{ $row['fabric_name'] }}</td>
                <td>{{ $row['roll_no'] }}</td>
                <td>{{ number_format($row['meter'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>_________________________<br>Issued By ({{ $issuer }})</div>
        <div>_________________________<br>Signature</div>
    </div>

    <div class="separator"></div>

    {{-- ================= FABRIC RECEIVE SLIP ================= --}}
    <div class="header">FABRIC RECEIVE SLIP</div>

    <table class="info-table">
        <tr>
            <td><strong>Order SKU:</strong> {{ $order->sku ?? 'N/A' }}</td>
            <td><strong>Product SKU:</strong> {{ $orderProduct->product_sku ?? 'N/A' }}</td>
            <td><strong>Date:</strong> {{ now()->format('d M, Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Fabric</th>
                <th>Roll No</th>
                <th>Received (Meter)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issuedData as $row)
            <tr>
                <td>{{ $row['fabric_name'] }}</td>
                <td>{{ $row['roll_no'] }}</td>
                <td>{{ number_format($row['meter'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div>_________________________<br>Received By ({{ $receiver }})</div>
        <div>_________________________<br>Signature</div>
    </div>

</body>
</html>
