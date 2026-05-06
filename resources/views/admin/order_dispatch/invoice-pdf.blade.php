<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order_dispatch_data['order_dispatch_no'] }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #222; margin: 0; padding: 30px; line-height: 1.4; }
        .main-container { border: 2px solid #000; min-height: 980px; position: relative; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .header-section { border-bottom: 2px solid #000; padding: 15px; background-color: #f8f9fa; position: relative; }
        .estimate-title { font-size: 13px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; text-decoration: underline; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .info-table { width: 100%; border-bottom: 2px solid #000; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 10px; border-right: 1px solid #000; }
        .info-table td:last-child { border-right: none; }
        .party-label { font-size: 10px; text-transform: uppercase; color: #555; margin-bottom: 5px; }
        .party-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { border-bottom: 2px solid #000; border-right: 1px solid #000; padding: 8px 5px; background-color: #f2f2f2; text-transform: uppercase; font-size: 10px; }
        .items-table td { border-right: 1px solid #000; padding: 8px 10px; vertical-align: top; }
        .items-table th:last-child, .items-table td:last-child { border-right: none; }
        .total-row td { border-top: 2px solid #000; border-bottom: 2px solid #000; font-weight: bold; padding: 10px 8px; background-color: #f8f9fa; }
        .summary-row td { font-weight: bold; padding: 5px 8px; }
        .amount-in-words { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px 15px; font-weight: bold; font-size: 11px; background-color: #fdfdfd; }
        .logo-img { height: 60px; max-width: 120px; position: absolute; top: 15px; }
    </style>
</head>
<body>
    @php
        $snapkidLogo = "";
        $snapkid = \App\Models\Brand::where('name', 'SNAPKID')->first();
        if($snapkid && $snapkid->logo) {
            $skPath = public_path('assets/brands/' . $snapkid->logo);
            if(file_exists($skPath)) { $snapkidLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($skPath)); }
        }

        $brandTitle = "SNAPKID";
        $leftLogo = $snapkidLogo;

        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucwords($f->format($filteredGrandTotal));
    @endphp

    <div class="main-container">
        <div class="header-section text-center">
            @if($leftLogo) <img src="{{ $leftLogo }}" class="logo-img" style="left: 15px;"> @endif
            <div class="estimate-title">INVOICE</div>
            <div class="company-name">{{ $brandTitle }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? 'TRONIKA CITY GHAZIABAD-201102' }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <div class="party-label">Party Details :</div>
                    <div class="party-name">{{ $order_dispatch_data['customer'] }}</div>
                    <div>{{ $order_dispatch_data['address'] }}</div>
                </td>
                <td width="40%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Invoice No</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ $order_dispatch_data['order_dispatch_no'] }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Dated</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ date('d-m-Y', strtotime($dispatch->dispatch_date)) }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Order No</td>
                            <td style="padding: 2px 0; border:none;">: {{ $order_dispatch_data['order_no'] }}</td>
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
                @php $tP = 0; $tB = 0; @endphp
                @foreach($groupedItems as $index => $item)
                    <tr>
                        <td class="text-center" style="border-bottom: 0.5px solid #eee;">{{ $index + 1 }}.</td>
                        <td style="border-bottom: 0.5px solid #eee; text-transform: uppercase;"><strong>{{ $item['product_name'] }} {{ $item['size_set_name'] }} {{ $item['color_name'] }}</strong></td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item['total_qty'], 0) }}</td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item['box_count'], 0) }}</td>
                        <td class="text-center" style="border-bottom: 0.5px solid #eee;">BOX</td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item['selling_price'], 2) }}</td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item['total_qty'] * $item['selling_price'], 2) }}</td>
                    </tr>
                    @php 
                        $tP += $item['total_qty']; 
                        $tB += $item['box_count'];
                    @endphp
                @endforeach

                @for ($i = count($groupedItems); $i < 10; $i++)
                    <tr><td style="height: 28px; border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td><td style="border-bottom: 0.5px solid #eee;"></td></tr>
                @endfor

                <tr class="total-row">
                    <td class="text-center" colspan="2" style="font-size: 12px; text-transform: uppercase;">Total Shipment Quantity</td>
                    <td class="text-right" style="font-size: 11px;">{{ number_format($tP, 0) }} Pcs.</td>
                    <td class="text-right" style="font-size: 11px;">{{ number_format($tB, 0) }}</td>
                    <td class="text-center" style="font-size: 11px;">Box.</td>
                    <td></td>
                    <td class="text-right" style="font-size: 12px;">{{ number_format($filteredSubtotal, 2) }}</td>
                </tr>

                @if(isset($discountAmt) && $discountAmt > 0)
                <tr class="summary-row">
                    <td colspan="6" class="text-right">Extra Discount (-)</td>
                    <td class="text-right">- Rs. {{ number_format($discountAmt, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row">
                    <td colspan="5" class="text-right">GST (+)</td>
                    <td class="text-right"></td>
                    <td class="text-right">Rs. {{ number_format($filteredGst, 2) }}</td>
                </tr>

                <tr class="summary-row" style="background-color: #f8f9fa;">
                    <td colspan="6" class="text-right" style="font-size: 13px; border-top: 2px solid #000;">Grand Total :</td>
                    <td class="text-right" style="font-size: 13px; border-top: 2px solid #000;">Rs. {{ number_format($filteredGrandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-in-words">
            Amount In Words: Rupees {{ $amountInWords }} Only
        </div>

        <table width="100%" style="border-collapse: collapse;">
            <tr>
                <td width="60%" style="padding: 10px 15px; vertical-align: top;">
                    <div style="margin-bottom: 15px;"><strong>Remarks :</strong> {{ $dispatch->remarks ?? '' }}</div>
                    <strong>Terms & Conditions</strong><br>
                    E.& O.E.<br>
                    1. Goods once sold will not be taken back.<br>
                    2. Interest @ 24% p.a. will be charged after 30 days.<br>
                </td>
                <td width="40%" style="padding: 10px 15px; vertical-align: top; text-align: right;">
                    <strong>Receiver's Signature :</strong><br><br><br><br>
                    <div style="margin-top: 10px;">
                        For <strong>{{ $brandTitle }}</strong><br>
                        <strong>Authorised Signatory</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
