<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #ORD-{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
        }

        .logo {
            max-width: 150px;
        }

        .title {
            text-align: right;
            font-size: 24px;
            color: #4f46e5;
            text-transform: uppercase;
        }

        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }

        .info-section td {
            vertical-align: top;
            width: 50%;
        }

        .info-box h3 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 14px;
            color: #4f46e5;
            text-transform: uppercase;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #4f46e5;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .summary-table {
            width: 100%;
            margin-top: 20px;
        }

        .summary-table td {
            padding: 5px 10px;
        }

        .summary-label {
            text-align: right;
            font-weight: bold;
            width: 80%;
        }

        .summary-value {
            text-align: right;
            font-weight: bold;
            color: #4f46e5;
            font-size: 14px;
        }

        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-weight-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    @if(isset($settings->logo) && $settings->logo)
                        <img src="{{ public_path('storage/' . $settings->logo) }}" class="logo">
                    @else
                        <h2 style="margin:0; color:#4f46e5;">{{ $settings->website_name ?? 'Snapkid' }}</h2>
                    @endif
                </td>
                <td class="title">Invoice</td>
            </tr>
        </table>
    </div>

    <table class="info-section">
        <tr>
            <td>
                <div class="info-box">
                    <h3>From:</h3>
                    <strong>{{ $settings->website_name ?? 'Snapkid' }}</strong><br>
                    {{ $settings->address ?? '' }}<br>
                    Phone: {{ $settings->phone ?? '' }}<br>
                    Email: {{ $settings->email ?? '' }}
                </div>
            </td>
            <td>
                <div class="info-box" style="text-align: right;">
                    <h3>Bill To:</h3>
                    <strong>{{ $order->shop_name }}</strong><br>
                    {{ $order->shop_address ?? '' }}<br>
                    Phone: {{ $order->shop_phone ?? '' }}<br>
                    Email: {{ $order->shop_email ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-section" style="margin-bottom: 15px;">
        <tr>
            <td>
                <strong>Order ID:</strong> #ORD-{{ $order->id }}<br>
                <strong>Order Date:</strong> {{ date('d M Y', strtotime($order->order_date)) }}
            </td>
            <td style="text-align: right;">
                <strong>Status:</strong> <span style="text-transform: uppercase;">{{ $order->status }}</span><br>
                <strong>Agent:</strong> {{ $order->agent_name }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="50%">Product Details</th>
                <th width="15%" class="text-center">Qty (Pcs)</th>
                <th width="15%" class="text-right">Price</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $item->product_name }}</div>
                        <small style="color:#666;">
                            Design: {{ $item->design_number }} | Color: {{ $item->color_name }} | Set:
                            {{ $item->size_set_name }} | Box: {{ $item->box_no }}
                        </small>
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">&#8377;{{ number_format($item->selling_price, 2) }}</td>
                    <td class="text-right font-weight-bold">
                        &#8377;{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Quantity:</td>
            <td class="summary-value" style="color: #333;">{{ $order->total_qty }} pcs</td>
        </tr>
        <tr>
            <td class="summary-label">Subtotal:</td>
            <td class="summary-value" style="color: #333;">&#8377;{{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="summary-label">Discount ({{ number_format($order->discount_percentage, 0) }}%):</td>
            <td class="summary-value" style="color: #28a745;">-&#8377;{{ number_format($order->discount_amount, 2) }}
            </td>
        </tr>
        <tr>
            <td class="summary-label">GST ({{ number_format($order->gst_percentage, 0) }}%):</td>
            <td class="summary-value" style="color: #dc3545;">+&#8377;{{ number_format($order->gst_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="summary-label" style="font-size: 16px;">Grand Total:</td>
            <td class="summary-value" style="font-size: 16px;">&#8377;{{ number_format($order->grand_total, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->copy_right ?? '© ' . date('Y') . ' Snapkid. All rights reserved.' }}
    </div>
</body>

</html>