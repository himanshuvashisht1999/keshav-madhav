<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Form #ORD-{{ $order->id }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #222; margin: 0; padding: 30px; line-height: 1.4; }
        .main-container { border: 2px solid #000; min-height: 1000px; position: relative; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-weight-bold { font-weight: bold; }
        .header-section { border-bottom: 2px solid #000; padding: 10px; background-color: #f8f9fa; }
        .title { font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .company-name { font-size: 18px; font-weight: bold; color: #d32f2f; }
        .info-table { width: 100%; border-bottom: 1px solid #000; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 8px 10px; border-right: 1px solid #000; }
        .info-table td:last-child { border-right: none; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { border-bottom: 2px solid #000; border-right: 1px solid #000; padding: 8px 5px; background-color: #f2f2f2; text-transform: uppercase; font-size: 10px; }
        .items-table td { border-right: 1px solid #000; padding: 5px 10px; vertical-align: top; border-bottom: 1px solid #bbb; }
        .items-table th:last-child, .items-table td:last-child { border-right: none; }
        .total-row td { border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold; padding: 8px; background: #fdfdfd; }
        .summary-row td { font-weight: bold; padding: 3px 8px; }
    </style>
</head>
<body>
    <div class="main-container">
        @php
            $logoData = "";
            if(isset($settings->logo) && $settings->logo) {
                $logoPath = public_path('assets/general-settings-image/' . $settings->logo);
                if(file_exists($logoPath)) {
                    $logoData = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(@file_get_contents($logoPath));
                }
            }
            $showPrice = ($order->see_price == 1);
            $showUnitPriceOnly = ($order->see_price == 2);
            $showAnyPrice = ($showPrice || $showUnitPriceOnly);
        @endphp

        <div class="header-section">
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="20%" class="text-left">
                        @if($logoData)
                            <img src="{{ $logoData }}" style="height: 50px; max-width: 150px;">
                        @endif
                    </td>
                    <td width="60%" class="text-center">
                        <div class="title">ORDER FORM</div>
                        <div class="company-name">{{ $settings->website_name ?? 'SURGICAL JEANS' }}</div>
                        <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
                    </td>
                    <td width="20%"></td>
                </tr>
            </table>
        </div>

        <table class="info-table">
            <tr>
                <td width="65%">
                    <div style="text-transform: uppercase; color: #666; font-size: 9px; margin-bottom: 3px;">CUSTOMER DETAILS :</div>
                    <div style="font-size: 14px; font-weight: bold;">{{ $order->shop_name }}</div>
                    <div style="font-size: 10px; color: #333;">{{ $order->shop_address }}</div>
                    <div>Phone: {{ $order->shop_phone }}</div>
                </td>
                <td width="35%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="40%" style="border:none; padding: 2px 0;"><strong>Order ID</strong></td>
                            <td style="border:none; padding: 2px 0;"><strong>: #ORD-{{ $order->id }}</strong></td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Order Date</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ \Carbon\Carbon::parse($order->order_date)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Dispatch Date</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ $order->expected_dispatch_date ? \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d-m-Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Agent</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ $order->agent_name }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Sales Man</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ $order->sales_man_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Booking Station</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ $order->booking_station ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="border:none; padding: 2px 0;"><strong>Transport</strong></td>
                            <td style="border:none; padding: 2px 0;">: {{ $order->transport ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">S.N.</th>
                    {{-- <th width="{{ $showPrice ? '23%' : ($showUnitPriceOnly ? '33%' : '43%') }}">Product Particulars</th> --}}
                    <th width="{{ $showPrice ? '43%' : ($showUnitPriceOnly ? '56%' : '68%') }}">Product Particulars</th>
                    {{-- <th width="10%" class="text-center">Warehouse</th> --}}
                    {{-- <th width="10%" class="text-center">Rack</th> --}}
                    <th width="10%" class="text-center">Set/Size</th>
                    <th width="8%" class="text-center">Boxes</th>
                    <th width="9%" class="text-center">Pcs Qty</th>
                    @if($showAnyPrice)
                    <th width="12%" class="text-right">Unit Price</th>
                    @endif
                    @if($showPrice)
                    <th width="13%" class="text-right">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php $tPcs = 0; $tBoxes = 0; $tAmt = 0; @endphp
                @foreach($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->product_name }}</strong><br>
                            <small>Color: {{ $item->color_name }} ({{ $item->color_id }}) {{-- | Barcode: {{ $item->barcode }} --}}</small>
                        </td>
                        {{-- <td class="text-center">{{ $item->warehouse_name }}</td> --}}
                        {{-- <td class="text-center">{{ $item->rack_name }}</td> --}}
                        <td class="text-center">{{ $item->size_set_name }}</td>
                        <td class="text-center">{{ number_format($item->box_count, 0) }}</td>
                        <td class="text-center">{{ number_format($item->total_qty, 0) }}</td>
                        @if($showAnyPrice)
                        <td class="text-right">Rs. {{ number_format($item->selling_price, 2) }}</td>
                        @endif
                        @if($showPrice)
                        <td class="text-right">Rs. {{ number_format($item->total_qty * $item->selling_price, 2) }}</td>
                        @endif
                    </tr>
                    @php $tPcs += $item->total_qty; $tBoxes += $item->box_count; $tAmt += ($item->total_qty * $item->selling_price); @endphp
                @endforeach

                @for ($i = count($items); $i < 15; $i++)
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td>@if($showAnyPrice)<td></td>@endif @if($showPrice)<td></td>@endif</tr>
                @endfor

                <tr class="total-row">
                    {{-- <td colspan="5" class="text-right">TOTAL</td> --}}
                    <td colspan="3" class="text-right">TOTAL</td>
                    <td class="text-center">{{ $tBoxes }}</td>
                    <td class="text-center">{{ $tPcs }}</td>
                    @if($showAnyPrice)
                    <td></td>
                    @endif
                    @if($showPrice)
                    <td class="text-right">Rs. {{ number_format($tAmt, 2) }}</td>
                    @endif
                </tr>

                @if($showPrice)
                    @if($order->discount_amount > 0)
                    <tr class="summary-row">
                        <td colspan="6" class="text-right">Discount ({{ $order->discount_percentage }}%)</td>
                        <td class="text-right">-Rs. {{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="summary-row">
                        <td colspan="6" class="text-right small">Taxable Amount</td>
                        <td class="text-right small">Rs. {{ number_format($tAmt - $order->discount_amount, 2) }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td colspan="6" class="text-right small">GST ({{ $order->gst_percentage }}%)</td>
                        <td class="text-right small">+Rs. {{ number_format($order->gst_amount, 2) }}</td>
                    </tr>
                    @if($order->other_charges > 0)
                    <tr class="summary-row">
                        <td colspan="6" class="text-right small">Other Charges</td>
                        <td class="text-right small">+Rs. {{ number_format($order->other_charges, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="summary-row" style="background:#f0f0f0;">
                        <td colspan="6" class="text-right" style="font-size:14px;">GRAND TOTAL</td>
                        <td class="text-right" style="font-size:14px; color:#d32f2f;">Rs. {{ number_format($order->grand_total, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if($showPrice)
            <div style="padding: 15px; border-top: 1px solid #000;">
                <strong>Amount in Words:</strong><br>
                @php $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT); @endphp
                Rupees {{ ucwords($f->format($order->grand_total)) }} Only
            </div>
        @endif

        <div style="padding: 15px; border-top: 1px solid #000; font-size: 10px;">
            <strong>REMARKS:</strong> {{ $order->remark ?? 'N/A' }}<br><br>
            * This is an automated order acknowledgement sheet.
        </div>

        <table width="100%" style="margin-top: 50px; padding: 0 30px;">
            <tr>
                <td width="50%" class="text-left">
                    <br><br>
                    _______________________<br>
                    <strong>Customer Signature</strong>
                </td>
                <td width="50%" class="text-right">
                    For <strong>{{ $settings->website_name ?? 'SURGICAL JEANS' }}</strong><br><br><br>
                    _______________________<br>
                    <strong>Authorised Signatory</strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
