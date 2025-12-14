<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Order Slip</title>

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
            top: -40px;
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

        /* REMARKS */
        .remarks {
            font-size: 16px;
            padding: 10px;
            border-left: 4px solid #000;
            background: #f9f9f9;
            margin-top: 6px;
            margin-left: 0;
            text-align: left;
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
            bottom: 10px;          /* page ke bottom se gap */
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
        Slip No: {{ $mainOrder['slip_no'] ?? $mainOrder['name'] }}
    </div>

    <h1>SALES ORDER SLIP</h1>

    <table style="width:100%; margin-top:12px; font-size:18px;">
        <tr>
            <td style="text-align:left;">
                <strong>Company:</strong> {{ $mainOrder['company_name'] }}
            </td>
            <td style="text-align:right; font-size:15px;">
                <strong>Date:</strong> {{ $mainOrder['created_at'] }}
            </td>
        </tr>
        <tr>
            <td style="text-align:left;">
                <strong>Order No:</strong> {{ $mainOrder['name'] }}
            </td>
            <td></td>
        </tr>
    </table>
</div>

<!-- ASSIGNED TO -->
<div class="section-title">
    <table style="width:100%;">
        <tr>
            <td style="text-align:left;">
                Assigned To : (Cutting Stage)
            </td>
            <td style="text-align:right; font-size:14px; font-weight:normal;">
                Delivery Time Allowed : {{ $cuttingData[0]['delivery_time_allowed'] }} Days
            </td>
        </tr>
    </table>
</div>

<table class="info">
    <tr>
        <td class="label">Cutting Master</td>
        <td>{{ $cuttingData[0]['cuttingMaster'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Address</td>
        <td>{{ $cuttingData[0]['cutting_master_address'] ?? '' }}</td>
    </tr>
</table>

<!-- PRODUCT DETAILS -->
<div class="section-title">Product & Quantity Details</div>

<table class="products">
    <thead>
        <tr>
            <th>#</th>
            <th>Design No</th>
            <th>Colour</th>
            <th>Size</th>
            <th>Pcs</th>
            <th>Set Qty</th>
            <th>Total Qty</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($cuttingData as $index => $row)
            @php $grandTotal += $row['total_quantity']; @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['design_number'] }}</td>
                <td>{{ $row['colour'] }}</td>
                <td>{{ $row['set_size'] }}</td>
                <td>{{ $row['no_of_pcs'] }}</td>
                <td>{{ $row['set_quantity'] }}</td>
                <td>{{ $row['total_quantity'] }}</td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="6" style="text-align:right;">Grand Total</td>
            <td>{{ $grandTotal }}</td>
        </tr>
    </tbody>
</table>

<!-- REMARKS -->
@if(!empty($cuttingData[0]['remarks']))
<div class="section-title">Remarks</div>
<div class="">
    {{ $cuttingData[0]['remarks'] }}
</div>
@endif

<!-- SIGNATURE -->
<table class="sign">
    <tr>
        <td>_______________________<br>Prepared By</td>
        <td>_______________________<br>Cutting Master</td>
        <td>_______________________<br>Authorized Sign</td>
    </tr>
</table>

<div class="footer">
    This is a system generated sales order slip.
</div>

</body>
</html>
