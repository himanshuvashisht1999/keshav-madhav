<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stage Transfer Slip</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 13px;
        }
        .section {
            width: 100%;
            border: 1px solid #000;
            padding: 10px 15px;
            margin-bottom: 10px;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .border-top-dotted {
            border-top: 2px dotted black;
            margin: 25px 0;
        }
        .sign-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }
        .sign {
            text-align: center;
            width: 45%;
        }
        .sign-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>

    <!-- ISSUE SECTION -->
    <div class="section">
        <div class="header">ISSUE SLIP (Copy for Giver)</div>
        <table>
            <tr><td><strong>Order No:</strong></td><td>{{ $transaction->sku ?? 'N/A' }}</td></tr>
            <tr><td><strong>Product:</strong></td><td>{{ $orderProduct->product_sku ?? 'N/A' }}</td></tr>
            <tr><td><strong>From Stage:</strong></td><td>{{ $transaction->from_stage->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>To Stage:</strong></td><td>{{ $transaction->to_stage->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>Quantity:</strong></td><td>{{ $transaction->quantity }}</td></tr>
            <tr><td><strong>Date:</strong></td><td>{{ getformatDateTime($transaction->created_at) }}</td></tr>
        </table>

        <div class="sign-section">
            <div class="sign">
                <div class="sign-line"></div>
                <div>Given By</div>
            </div>
            <div class="sign">
                <div class="sign-line"></div>
                <div>Manager</div>
            </div>
        </div>
    </div>

    <div class="border-top-dotted"></div>

    <!-- RECEIVE SECTION -->
    <div class="section">
        <div class="header">RECEIVE SLIP (Copy for Receiver)</div>
        <table>
            <tr><td><strong>Order No:</strong></td><td>{{ $transaction->sku ?? 'N/A' }}</td></tr>
            <tr><td><strong>Product:</strong></td><td>{{ $orderProduct->product_sku ?? 'N/A' }}</td></tr>
            <tr><td><strong>From Stage:</strong></td><td>{{ $transaction->from_stage->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>To Stage:</strong></td><td>{{ $transaction->to_stage->name ?? 'N/A' }}</td></tr>
            <tr><td><strong>Quantity:</strong></td><td>{{ $transaction->quantity }}</td></tr>
            <tr><td><strong>Date:</strong></td><td>{{ getformatDateTime($transaction->created_at) }}</td></tr>
        </table>

        <div class="sign-section">
            <div class="sign">
                <div class="sign-line"></div>
                <div>Received By</div>
            </div>
            <div class="sign">
                <div class="sign-line"></div>
                <div>Manager</div>
            </div>
        </div>
    </div>

</body>
</html>
