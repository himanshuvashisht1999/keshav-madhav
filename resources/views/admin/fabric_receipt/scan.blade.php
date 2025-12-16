<!DOCTYPE html>
<html>
<head>
    <title>Fabric Roll Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: system-ui;
            background:#f4f6f9;
            margin:0;
            padding:15px;
        }
        .card {
            background:#fff;
            border-radius:10px;
            padding:20px;
            box-shadow:0 4px 10px rgba(0,0,0,.1);
            max-width:480px;
            margin:auto;
        }
        h2 {
            margin-bottom:15px;
            text-align:center;
        }
        .row {
            margin-bottom:8px;
            display:flex;
            justify-content:space-between;
        }
        .label {
            font-weight:600;
            color:#555;
        }
        .scan-box {
            display:flex;
            justify-content:space-around;
            margin:15px 0;
        }
        .scan-item {
            text-align:center;
        }
        .scan-item img {
            border:1px solid #ddd;
            padding:6px;
            background:#fff;
        }
        .barcode-number {
            font-family:monospace;
            font-size:13px;
            margin-top:4px;
            letter-spacing:1px;
        }
        hr {
            margin:15px 0;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Fabric Roll Details</h2>

    <!-- QR & Barcode -->
    <div class="scan-box">

        <div class="scan-item">
            <img src="{{ $detail->qrcode }}" width="120" height="120" alt="QR Code">
            <div class="barcode-number">{{ $detail->qrcode_number }}</div>
        </div>

        <div class="scan-item">
            <img src="{{ $detail->barcode }}" width="200" height="70" alt="Barcode">
            <div class="barcode-number">{{ $detail->qrcode_number }}</div>
        </div>

    </div>

    <hr>

    <div class="row">
        <span class="label">Fabric</span>
        <span>{{ $detail->fabric->name }}</span>
    </div>

    <div class="row">
        <span class="label">Roll Number</span>
        <span>{{ $detail->roll_number }}</span>
    </div>

    <div class="row">
        <span class="label">Meter</span>
        <span>{{ $detail->meter }}</span>
    </div>

    <div class="row">
        <span class="label">Remaining</span>
        <span>{{ $detail->remaining_quantity }}</span>
    </div>

    <hr>

    <div class="row">
        <span class="label">Shipment</span>
        <span>{{ $detail->shipment_number }}</span>
    </div>

    <div class="row">
        <span class="label">Vendor</span>
        <span>{{ $detail->fabric_receipt->vendor->name ?? '-' }}</span>
    </div>

    <div class="row">
        <span class="label">Warehouse</span>
        <span>{{ $detail->fabric_receipt->cutting_master->cutting_master_name ?? '-' }}</span>
    </div>
    <div class="row">
        <span class="label">Warehouse Address</span>
        <span>{{ $detail->fabric_receipt->cutting_master->address ?? '-' }}</span>
    </div>

</div>

</body>
</html>
