<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Purchase Order</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 15px;
            color: #222;
            margin: 0;
            padding: 18px;
        }

        /* HEADER */
        .header {
            position: relative;
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1.2px;
        }

        .slip-no {
            position: absolute;
            top: -20px;
            left: 0;
            font-size: 14px;
            font-weight: bold;
        }

        /* SECTION TITLE */
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 8px;
            border-bottom: 2px solid #aaa;
            padding-bottom: 4px;
        }

        /* INFO TABLE */
        table.info {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        table.info td {
            padding: 7px;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 30%;
        }

        /* PRODUCT TABLE */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 16px;
        }

        table.products th,
        table.products td {
            border: 1.5px solid #000;
            padding: 8px;
            text-align: center;
        }

        table.products th {
            background: #f2f2f2;
        }

        .total-row td {
            font-weight: bold;
            background: #fafafa;
            font-size: 17px;
        }

        /* SIGNATURE */
        table.sign {
            width: 100%;
            margin-top: 45px;
            font-size: 14px;
        }

        table.sign td {
            text-align: center;
            padding-top: 30px;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            width: 100%;
            font-size: 13px;
            text-align: center;
            color: #555;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="slip-no">
        PO SKU: {{ $po->sku }}
    </div>

    <h1>PRODUCTION PURCHASE ORDER</h1>

    <table style="width:100%; margin-top:12px; font-size:18px;">
        <tr>
            <td style="text-align:left;">
                <strong>Company:</strong> Keshav Madhav
            </td>
            <td style="text-align:right; font-size:15px;">
                <strong>Date:</strong> {{ date('d-m-Y', strtotime($po->created_at)) }}
            </td>
        </tr>
    </table>
</div>

<!-- PO TO -->
<div class="section-title">PO TO</div>

<table class="info">
    <tr>
        <td class="label">Entity Name</td>
        <td>{{ $po->vendor_id ? ($po->vendor->name ?? '-') : ($po->customer->name ?? '-') }}</td>
    </tr>
    <tr>
        <td class="label">Type</td>
        <td>{{ $po->vendor_id ? 'Vendor' : 'Customer' }}</td>
    </tr>
    <tr>
        <td class="label">Delivery Date</td>
        <td>{{ $po->till_allowed_time ? date('d-m-Y', strtotime($po->till_allowed_time)) : '-' }}</td>
    </tr>
</table>

<!-- PRODUCT DETAILS -->
<div class="section-title">Product & Quantity Details</div>

<table class="products">
    <thead>
        <tr>
            <th>Design No</th>
            <th>Colour</th>
            <th>Size</th>
            <th>Pcs in Set</th>
            <th>Quantity</th>
            <th>Rate</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $po->productSet->design_number ?? '-' }}</td>
            <td>{{ $po->productSet->colors->name ?? '-' }}</td>
            <td>{{ $po->productSet->set_size ?? '-' }}</td>
            <td>{{ $po->productSet->no_of_pcs ?? 0 }}</td>
            <td>{{ $po->quantity }}</td>
            <td>{{ number_format($po->rate, 2) }}</td>
            <td>{{ number_format($po->quantity * $po->rate, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="4" style="text-align:right;">Total</td>
            <td>{{ $po->quantity }}</td>
            <td></td>
            <td>{{ number_format($po->quantity * $po->rate, 2) }}</td>
        </tr>
    </tbody>
</table>

<!-- REMARKS -->
@if(!empty($po->remarks))
<div class="section-title">Remarks</div>
<div style="padding: 10px; background: #f9f9f9; border-left: 4px solid #000;">
    {{ $po->remarks }}
</div>
@endif

<!-- SIGNATURE -->
<table class="sign">
    <tr>
        <td>_______________________<br>Prepared By</td>
        <td>_______________________<br>Receiver Sign</td>
        <td>_______________________<br>Authorized Sign</td>
    </tr>
</table>

<div class="footer">
    This is a system generated production purchase order.
</div>

</body>
</html>
