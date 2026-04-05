<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice #ORD-{{ $order->id }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 30px;
            line-height: 1.4;
        }

        .main-container {
            border: 2px solid #000;
            min-height: 1000px;
            position: relative;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .header-section {
            border-bottom: 2px solid #000;
            padding: 8px;
            background-color: #f8f9fa;
        }

        .estimate-title {
            font-size: 13px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 2px;
            text-decoration: underline;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-bottom: 1px solid #000;
            border-collapse: collapse;
        }

        .info-table td {
            vertical-align: top;
            padding: 10px;
            border-right: 1px solid #000;
        }

        .info-table td:last-child {
            border-right: none;
        }

        .party-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 5px;
        }

        .party-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            border-bottom: 2px solid #000;
            border-right: 1px solid #000;
            padding: 8px 5px;
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-size: 10px;
        }

        .items-table td {
            border-right: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            border-right: none;
        }

        .total-row td {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
            padding: 8px;
        }

        .summary-row td {
            font-weight: bold;
            padding: 5px 8px;
        }

        .amount-in-words {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 12px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 10px 15px;
            vertical-align: top;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="header-section">
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="20%" class="text-left" style="vertical-align: middle;">
                        @if($settings && isset($settings->logo) && $settings->logo)
                            <img src="{{ public_path('assets/general-settings-image/' . $settings->logo) }}" style="height: 50px; max-width: 150px;">
                        @endif
                    </td>
                    <td width="60%" class="text-center" style="vertical-align: middle;">
                        <div class="estimate-title">INVOICE</div>
                        <div class="company-name">{{ $settings->website_name ?? 'SURGICAL JEANS' }}</div>
                        <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
                    </td>
                    <td width="20%" class="text-right" style="vertical-align: middle;">
                        @if($settings && isset($settings->logo) && $settings->logo)
                            <img src="{{ public_path('assets/general-settings-image/' . $settings->logo) }}" style="height: 50px; max-width: 150px;">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <div class="party-label">Party Details :</div>
                    <div class="party-name">{{ $order->shop_name }}</div>
                    <div>{{ $order->shop_address }}</div>
                    <div>Phone: {{ $order->shop_phone }}</div>
                </td>
                <td width="40%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Invoice No</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ $settings->website_name ?? 'SURGICAL' }}/{{ $order->id }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Dated</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ date('d-m-Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Bill</td>
                            <td style="padding: 2px 0; border:none;">: </td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">AG</td>
                            <td style="padding: 2px 0; border:none;">: {{ $order->agent_name }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">S.N.</th>
                    <th width="35%">Description of Goods</th>
                    <th width="10%" class="text-center">PCs Qty.</th>
                    <th width="10%" class="text-center">BoX Qty.</th>
                    <th width="10%" class="text-center">Unit</th>
                    <th width="12%" class="text-right">Price</th>
                    <th width="18%" class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $total_pcs = 0; 
                    $total_boxes = 0;
                    $subtotal = 0;
                @endphp
                @foreach($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td><strong>{{ $item->product_name }} {{ $item->size_set_name }} {{ $item->color_name }}</strong></td>
                        <td class="text-right">{{ number_format($item->total_qty, 2) }}</td>
                        <td class="text-right">{{ number_format($item->box_count, 2) }}</td>
                        <td class="text-center">BOX</td>
                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_qty * $item->selling_price, 2) }}</td>
                    </tr>
                    @php 
                        $total_pcs += $item->total_qty; 
                        $total_boxes += $item->box_count;
                        $subtotal += ($item->total_qty * $item->selling_price);
                    @endphp
                @endforeach

                {{-- Filling empty space --}}
                @for ($i = count($items); $i < 12; $i++)
                    <tr>
                        <td style="height: 25px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor

                <tr class="total-row">
                    <td></td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ number_format($total_pcs, 2) }}Pcs.</td>
                    <td class="text-right">{{ number_format($total_boxes, 2) }}</td>
                    <td class="text-center">Box.</td>
                    <td></td>
                    <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                </tr>

                @if($order->discount_percentage > 0)
                <tr class="summary-row">
                    <td colspan="5" class="text-right">Less : Discount</td>
                    <td class="text-right">@ {{ number_format($order->discount_percentage, 2) }} %</td>
                    <td class="text-right">{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif

                @if($order->gst_amount > 0)
                <tr class="summary-row">
                    <td colspan="5" class="text-right">GST</td>
                    <td class="text-right">@ {{ number_format($order->gst_percentage, 2) }} %</td>
                    <td class="text-right">{{ number_format($order->gst_amount, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row" style="background-color: #f8f9fa; border-top: 2px solid #000;">
                    <td colspan="6" class="text-right" style="font-size: 13px;">Grand Total :</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($order->grand_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
            $amountInWords = ucwords($f->format($order->grand_total));
        @endphp
        <div class="amount-in-words">
            Rupees {{ $amountInWords }} Only
        </div>

        <table class="footer-table">
            <tr>
                <td width="60%">
                    <div style="margin-bottom: 10px;"><strong>Remarks :</strong> {{ $order->remarks ?? '' }}</div>
                    <strong>Terms & Conditions</strong><br>
                    E.& O.E.<br>
                    1. Goods once sold will not be taken back.
                </td>
                <td width="40%" class="text-right">
                    <strong>Receiver's Signature :</strong><br><br><br><br>
                    <div style="margin-top: 20px;">
                        For <strong>{{ $settings->website_name ?? 'SURGICAL JEANS' }}</strong><br>
                        <strong>Authorised Signatory</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>