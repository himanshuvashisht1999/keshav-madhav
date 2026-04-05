<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dispatch Slip - {{ $order_dispatch_data['order_dispatch_no'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #007bff;
            font-size: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f8fafc;
            padding: 8px 12px;
            font-weight: bold;
            border-bottom: 1px solid #edf2f7;
            font-size: 13px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-grid td {
            width: 33.33%;
            padding: 10px;
            border: 1px solid #edf2f7;
            vertical-align: top;
        }
        .info-grid label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .info-grid div {
            font-size: 11px;
            font-weight: bold;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.items-table th, table.items-table td {
            border: 1px solid #edf2f7;
            padding: 8px;
            text-align: left;
        }
        table.items-table th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
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
        .text-primary {
            color: #007bff;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        .summary-row td {
            border: none !important;
            padding: 4px 8px !important;
        }
        .grand-total {
            font-size: 14px;
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DISPATCH SLIP</h1>
        <p>Dispatch #{{ $order_dispatch_data['order_dispatch_no'] }} | Date: {{ $order_dispatch_data['dispatch_date'] }}</p>
    </div>

    <table class="info-grid">
        <tr>
            <td>
                <label>Order Information</label>
                <div>Order No: {{ $order_dispatch_data['order_no'] }}</div>
                <div>Dispatch Date: {{ $order_dispatch_data['dispatch_date'] }}</div>
                <div>Dispatch No: {{ $order_dispatch_data['order_dispatch_no'] }}</div>
            </td>
            <td>
                <label>Customer Details</label>
                <div style="font-size: 13px;">{{ $order_dispatch_data['customer'] }}</div>
                <div style="font-weight: normal; color: #475569; margin-top: 5px;">
                    {{ $order_dispatch_data['address'] ?? 'N/A' }}
                </div>
            </td>
            <td>
                <label>Summary</label>
                <table class="info-grid" style="border: none; margin-bottom: 0;">
                    <tr class="summary-row">
                        <td style="width: 60%; padding: 2px 0;">Item Subtotal:</td>
                        <td style="width: 40%; padding: 2px 0;" class="text-right">₹{{ number_format($order_dispatch_data['total_dispatch_amount'], 2) }}</td>
                    </tr>
                    @php 
                        $discount_val = ($order_dispatch_data['total_dispatch_amount'] * $order_dispatch_data['discount_percentage']) / 100;
                        $gst_val = (($order_dispatch_data['total_dispatch_amount'] - $discount_val) * $order_dispatch_data['gst_percentage']) / 100;
                    @endphp
                    <tr class="summary-row">
                        <td style="padding: 2px 0;">Discount ({{ number_format($order_dispatch_data['discount_percentage'], 2) }}%):</td>
                        <td style="padding: 2px 0;" class="text-right text-danger">- ₹{{ number_format($discount_val, 2) }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td style="padding: 2px 0;">GST ({{ number_format($order_dispatch_data['gst_percentage'], 2) }}%):</td>
                        <td style="padding: 2px 0;" class="text-right text-primary">+ ₹{{ number_format($gst_val, 2) }}</td>
                    </tr>
                    <tr class="summary-row grand-total">
                        <td style="padding: 8px 0; border-top: 1px solid #007bff !important;"><strong>Grand Total:</strong></td>
                        <td style="padding: 8px 0; border-top: 1px solid #007bff !important;" class="text-right text-success"><strong>₹{{ number_format($order_dispatch_data['total_amount'], 2) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Packed Cartons Details</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="20%">Carton No</th>
                    <th>Contents Summary</th>
                    <th width="15%" class="text-center">Total Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cartonsDetails as $index => $carton)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-weight-bold">Carton - {{ $carton['carton_no'] }}</div>
                            <div style="font-size: 9px; color: #64748b;">
                                Store Room: {{ $carton['storeroom'] ?? '' }}<br>
                                Rack: {{ $carton['rack'] ?? '' }}
                            </div>
                        </td>
                        <td>
                            @php $sets = $carton['sets'] ?? []; @endphp
                            @if(count($sets))
                                <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                    <thead>
                                        <tr style="background: #f8fafc;">
                                            <th style="border: 1px solid #e2e8f0; padding: 4px;">Design | Color</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 4px;">Sizes (Qty)</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 4px;" class="text-center">Total</th>
                                            <th style="border: 1px solid #e2e8f0; padding: 4px;" class="text-right">Value (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sets as $set)
                                            <tr>
                                                <td style="border: 1px solid #e2e8f0; padding: 4px;">
                                                    <strong>{{ $set['design'] }}</strong> | {{ $set['color'] }}
                                                </td>
                                                <td style="border: 1px solid #e2e8f0; padding: 4px;">
                                                    <span class="badge">{{ $set['size_set'] }}</span>
                                                </td>
                                                <td style="border: 1px solid #e2e8f0; padding: 4px;" class="text-center">{{ $set['total_qty'] }}</td>
                                                <td style="border: 1px solid #e2e8f0; padding: 4px;" class="text-right">₹{{ number_format($set['total_qty'] * $set['price'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="font-weight-bold">{{ $carton['total_items'] }} Pcs</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 30px; color: #94a3b8;">
                            No cartons found in this dispatch.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #94a3b8; font-size: 9px; border-top: 1px solid #f1f5f9; padding-top: 10px;">
        This is a computer generated document.
    </div>
</body>
</html>
