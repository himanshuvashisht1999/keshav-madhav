<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Invoice - {{ $dispatch->id }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #222; margin: 0; padding: 30px; line-height: 1.4; }
        .main-container { border: 2px solid #000; min-height: 1000px; position: relative; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .header-section { border-bottom: 2px solid #000; padding: 15px; background-color: #f8f9fa; position: relative; }
        .estimate-title { font-size: 13px; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; text-decoration: underline; }
        .company-name { font-size: 20px; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .info-table { width: 100%; border-bottom: 1px solid #000; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 10px; border-right: 1px solid #000; }
        .info-table td:last-child { border-right: none; }
        .party-label { font-size: 10px; text-transform: uppercase; color: #555; margin-bottom: 5px; }
        .party-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { border-bottom: 2px solid #000; border-right: 1px solid #000; padding: 8px 5px; background-color: #f2f2f2; text-transform: uppercase; font-size: 10px; }
        .items-table td { border-right: 1px solid #000; padding: 8px 10px; vertical-align: top; border-bottom: 1px solid #ddd; }
        .items-table th:last-child, .items-table td:last-child { border-right: none; }
        .total-row td { border-top: 2px solid #000; border-bottom: 1px solid #000; font-weight: bold; padding: 8px; }
        .summary-row td { font-weight: bold; padding: 5px 8px; }
        .amount-in-words { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px 15px; font-weight: bold; font-size: 11px; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { padding: 10px 15px; vertical-align: top; }
        .logo-img { height: 60px; max-width: 120px; position: absolute; top: 15px; }
    </style>
</head>
<body>
    @php
        $logoData = "";
        if(isset($settings->logo) && $settings->logo) {
            $logoPath = public_path('assets/general-settings-image/' . $settings->logo);
            if(file_exists($logoPath)) {
                $logoData = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(@file_get_contents($logoPath));
            }
        }
        
        $grandTotal = $dispatch->grand_total;
        $subtotal = $dispatch->total_amount;
        $gstAmount = $dispatch->gst_amount;
        $discountAmt = $dispatch->discount_amount ?? 0;

        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucwords($f->format($grandTotal));
    @endphp

    <div class="main-container">
        <div class="header-section text-center">
            @if($logoData) <img src="{{ $logoData }}" class="logo-img" style="left: 15px;"> @endif
            <div class="estimate-title">FABRIC INVOICE</div>
            <div class="company-name">{{ $settings->website_name ?? 'SURGICAL JEANS' }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    @php
                        $party = $dispatch->party_type === 'vendor' ? $dispatch->vendor : $dispatch->shop;
                    @endphp
                    <div class="party-label">Party Details :</div>
                    <div class="party-name">{{ $party->name ?? 'N/A' }}</div>
                    <div>{{ $party->address ?? '' }}</div>
                    <div>Phone: {{ $party->phone ?? 'N/A' }}</div>
                </td>
                <td width="40%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Invoice No</td>
                            <td style="padding: 2px 0; border:none;">: <strong>DSP/{{ $dispatch->id }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Dated</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ date('d-m-Y', strtotime($dispatch->dispatch_date)) }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">AG/DISTRIBUTOR</td>
                            <td style="padding: 2px 0; border:none;">: {{ $dispatch->agent->name ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">S.N.</th>
                    <th width="20%">Roll Number</th>
                    <th width="35%">Fabric Name</th>
                    <th width="15%" class="text-center">Meters</th>
                    <th width="12%" class="text-right">Price/m</th>
                    <th width="13%" class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $tMeters = 0; @endphp
                @foreach($fabricItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td><strong>{{ $item->roll_number }}</strong></td>
                        <td style="text-transform: uppercase;">{{ $item->fabric_name }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                        <td class="text-right font-weight-bold">Rs. {{ number_format($item->meter * $item->selling_price, 2) }}</td>
                    </tr>
                    @php $tMeters += $item->meter; @endphp
                @endforeach

                @for ($i = count($fabricItems); $i < 12; $i++)
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                @endfor

                <tr class="total-row">
                    <td></td>
                    <td colspan="2" class="text-right">TOTAL</td>
                    <td class="text-right">{{ number_format($tMeters, 2) }} m</td>
                    <td></td>
                    <td class="text-right">Rs. {{ number_format($subtotal, 2) }}</td>
                </tr>

                @if($discountAmt > 0)
                <tr class="summary-row">
                    <td colspan="5" class="text-right">Extra Discount</td>
                    <td class="text-right">- Rs. {{ number_format($discountAmt, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row">
                    <td colspan="4" class="text-right">GST</td>
                    <td class="text-right"></td>
                    <td class="text-right">Rs. {{ number_format($gstAmount, 2) }}</td>
                </tr>

                @if($dispatch->other_charges > 0 && !$brandId)
                <tr class="summary-row">
                    <td colspan="5" class="text-right">Other Charges</td>
                    <td class="text-right">Rs. {{ number_format($dispatch->other_charges, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row" style="background-color: #f8f9fa; border-top: 2px solid #000;">
                    <td colspan="5" class="text-right" style="font-size: 13px;">Grand Total :</td>
                    <td class="text-right" style="font-size: 13px; color: #d32f2f;">Rs. {{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-in-words">
            Rupees {{ $amountInWords }} Only
        </div>

        <table class="footer-table">
            <tr>
                <td width="60%">
                    <div style="margin-bottom: 10px;"><strong>Remarks :</strong> {{ $dispatch->remark ?? '' }}</div>
                    <strong>Terms & Conditions</strong><br>
                    E.& O.E.<br>
                    1. Goods once sold will not be taken back.<br>
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
