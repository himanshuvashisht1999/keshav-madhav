<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order & Receiving Slip</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #000;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .half-page {
            width: 100%;
            height: 45%;
            padding: 15px 25px;
            box-sizing: border-box;
            border: 1px solid #000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .divider {
            border-top: 2px dashed #000;
            margin: 22px 0;
        }
        .header {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .info-table {
            width: 80%;
            margin-bottom: 10px;
            margin-left: 30px;
        }
        .info-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        table.items {
            width: 80%;
            border-collapse: collapse;
        }
        table.items th, table.items td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        table.items th {
            background: #f5f5f5;
            width: 75%;
        }
        
        .footer {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
        }
        .sign-box {
            text-align: right;
            padding-top: 10px; /* increased space for signature */
            font-size: 12px;
        }
        .bottom-space {
            height: 20px;
        }
    </style>
</head>
<body>

    {{-- Top Half: ORDER SLIP --}}
    <div class="half-page">
        <div>
            <div class="header">ORDER SLIP</div>
            <table class="info-table">
                <tr>
                    <td ><strong>Order No:</strong> {{ $order_no }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong> {{ $order_date }}</td>
                    <td><strong>Time:</strong> {{ $order_time }}</td>
                </tr>
            </table>

            <table class="items" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="background: #f5f5f5; text-align: left; width: 75%; padding: 8px;">Product Name</th>
                        <th style="background: #f5f5f5; text-align: left; width: 25%; padding: 8px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td style="text-align: left;">{{ $item['sku'] }}</td>
                        <td style="text-align: left;">{{ $item['qty'] }} pcs</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            <br>
            <table class="items" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="background: #f5f5f5; text-align: left; width: 75%; padding: 8px;">Fabric Name</th>
                        <th style="background: #f5f5f5; text-align: left; width: 25%; padding: 8px;">Fabric Quantity (M)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td style="text-align: left;">{{ $fabric_sku }}</td>
                        <td style="text-align: left;">{{ $fabric_qty }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 100px;">
            <div class="footer" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <div class="sign-box">Prepared By (Sign & Date)</div>
                <div class="sign-box">Authorized Signature</div>
            </div>
        </div>
        
    </div>

    {{-- Divider Line --}}
    <div class="divider"></div>

    {{-- Bottom Half: RECEIVING SLIP --}}
    <div class="half-page">
        <div>
            <div class="header">RECEIVING SLIP</div>
            <table class="info-table">
                <tr>
                    <td><strong>Order No:</strong> {{ $order_no }}</td>
                    <td><strong>Customer:</strong> {{ $customer_name }}</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong> {{ $order_date }}</td>
                    <td><strong>Time:</strong> {{ $order_time }}</td>
                </tr>
            </table>
            <table class="items" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="background: #f5f5f5; text-align: left; width: 75%; padding: 8px;">Product Name</th>
                        <th style="background: #f5f5f5; text-align: left; width: 25%; padding: 8px;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td style="text-align: left;">{{ $item['sku'] }}</td>
                        <td style="text-align: left;">{{ $item['qty'] }} pcs</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            <br>
            <table class="items" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="background: #f5f5f5; text-align: left; width: 75%; padding: 8px;">Fabric Name</th>
                        <th style="background: #f5f5f5; text-align: left; width: 25%; padding: 8px;">Fabric Quantity (M)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td style="text-align: left;">{{ $fabric_sku }}</td>
                        <td style="text-align: left;">{{ $fabric_qty }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 100px;">
            <div class="footer" style="margin: 0 auto; width: 90%; border-collapse: collapse;">
                <div class="sign-box">Received By (Sign & Date)</div>
                <div class="sign-box">Authorized Signature</div>
            </div>
        </div>
    </div>

</body>
</html>
