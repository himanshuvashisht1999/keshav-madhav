<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            border: none;
            padding: 4px 6px;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            width: 160px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 15px 0 8px;
            text-transform: uppercase;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .data-table th {
            font-weight: bold;
            background: #f2f2f2;
        }

        .signature-table td {
            border: none;
            padding-top: 40px;
            text-align: center;
        }

        .footer-note {
            margin-top: 10px;
            text-align: center;
            font-size: 11px;
            color: #555;
        }
    </style>
</head>

<body>

<!-- ===================== TITLE ===================== -->
<div class="title">
    Cutting Master Production Order
</div>

<!-- ===================== HEADER DETAILS ===================== -->
<table class="meta-table">
    <tr>
        <td class="meta-label">CMPO No:</td>
        <td>CMPO-{{ $header['cmpo_id'] }}</td>

        <td class="meta-label">Date:</td>
        <td>{{ $header['date'] }}</td>
    </tr>

    <tr>
        <td class="meta-label">Sales Order No:</td>
        <td>{{ $header['order_no'] }}</td>

        <td class="meta-label">Customer:</td>
        <td>{{ $header['customer'] }}</td>
    </tr>

    <tr>
        <td class="meta-label">Fabric:</td>
        <td>{{ $header['fabric'] }}</td>

        <td class="meta-label">Fitting:</td>
        <td>{{ $header['fitting'] }}</td>
    </tr>

    <tr>
        <td class="meta-label">Cutting Master:</td>
        <td>{{ $header['cuttingMaster'] }}</td>

        <td class="meta-label">Address:</td>
        <td>{{ $header['cuttingMasterAddress'] }}</td>
    </tr>

    <tr>
        <td class="meta-label">Remark:</td>
        <td colspan="3">{{ $header['remark'] }}</td>
    </tr>
</table>

<!-- ===================== PRODUCT TABLE HEADING ===================== -->
<div class="section-title">
    Product & Quantity Details
</div>

<!-- ===================== PRODUCT TABLE ===================== -->
<table class="data-table">
    <thead>
        <tr>
            <th style="width:6%;">#</th>
            <th style="width:30%;">Design No</th>
            <th style="width:20%;">Color</th>
            <th style="width:20%;">Size</th>
            <th style="width:14%;">PCS</th>
        </tr>
    </thead>

    <tbody>
        @foreach($sizeData as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['design_no'] }} ({{ $header['fitting'] }})</td>
                <td>{{ $row['color'] }}</td>
                <td>{{ $row['size'] }}</td>
                <td>{{ $row['pcs'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- ===================== SIGNATURE SECTION ===================== -->
<table class="signature-table" width="100%">
    <tr>
        <td>
            _______________________<br>
            <strong>Prepared By</strong>
        </td>

        <td>
            _______________________<br>
            <strong>Cutting Master</strong>
        </td>

        <td>
            _______________________<br>
            <strong>Authorized Sign</strong>
        </td>
    </tr>
</table>

<!-- ===================== FOOTER NOTE ===================== -->
<div class="footer-note">
    This is a system generated sales order slip.
</div>

</body>
</html>
