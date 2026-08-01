<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $dispatch->id }}</title>
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
        .items-table td { border-right: 1px solid #000; padding: 8px 10px; vertical-align: top; }
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
        $surgicalLogo = ""; $snapkidLogo = "";
        $surgical = \App\Models\Brand::where('name', 'SURGICAL')->first();
        if($surgical && $surgical->logo) {
            $sPath = public_path('assets/brands/' . $surgical->logo);
            if(file_exists($sPath)) { $surgicalLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($sPath)); }
        }
        $snapkid = \App\Models\Brand::where('name', 'SNAPKID')->first();
        if($snapkid && $snapkid->logo) {
            $skPath = public_path('assets/brands/' . $snapkid->logo);
            if(file_exists($skPath)) { $snapkidLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($skPath)); }
        }

        $showSurgical = false; $showSnapkid = false;
        if(isset($type) && $type == 'actual') {
            if(isset($brandCount) && $brandCount > 1) { $showSurgical = true; $showSnapkid = true; }
            else {
                foreach($groupedItems as $item) {
                     if(isset($item->brand_id)) {
                         if($item->brand_id == ($surgical->id ?? 0)) $showSurgical = true;
                         if($item->brand_id == ($snapkid->id ?? 0)) $showSnapkid = true;
                     }
                }
            }
        } else if(isset($selectedBrand) && $selectedBrand) {
            if($selectedBrand->name == 'SURGICAL') $showSurgical = true;
            if($selectedBrand->name == 'SNAPKID') $showSnapkid = true;
        }
        
        $leftLogo = $showSurgical ? $surgicalLogo : ($showSnapkid ? $snapkidLogo : $snapkidLogo);
        $rightLogo = (isset($type) && $type == 'actual') ? $snapkidLogo : (($showSurgical && $showSnapkid) ? $snapkidLogo : "");
        if ($leftLogo == $rightLogo) {
            $rightLogo = "";
        }
        
        $brandTitle = "SNAPKID";
        if($showSurgical && $showSnapkid) $brandTitle = "SNAPKID";
        elseif($showSurgical) $brandTitle = "SURGICAL";
        elseif($showSnapkid) $brandTitle = "SNAPKID";

        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucwords($f->format($filteredGrandTotal));

        $party = $dispatch->party_type === 'vendor' ? $dispatch->vendor : $dispatch->shop;
    @endphp

    <div class="main-container">
        <div class="header-section text-center">
            @if($leftLogo) <img src="{{ $leftLogo }}" class="logo-img" style="left: 15px;"> @endif
            <div class="estimate-title">INVOICE</div>
            <div class="company-name">{{ $brandTitle }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? 'TRONIKA CITY GHAZIABAD-201102' }}</div>
            @if($rightLogo) <img src="{{ $rightLogo }}" class="logo-img" style="right: 15px;"> @endif
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <div class="party-label">Party Details :</div>
                    <div class="party-name">{{ $party->name ?? 'N/A' }}</div>
                    <div>{{ $party->address ?? '' }}</div>
                    <div>Phone: {{ $party->phone ?? 'N/A' }}</div>
                </td>
                <td width="40%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Invoice No</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ strtoupper($brandTitle) }}/{{ $dispatch->id }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Dated</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ date('d-m-Y', strtotime($dispatch->dispatch_date)) }}</strong></td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">AG/DISTRIBUTOR</td>
                            <td style="padding: 2px 0; border:none;">: {{ $dispatch->agent->name ?? 'N/A' }}</td>
                        </tr>
                        @if($dispatch->company)
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Company</td>
                            <td style="padding: 2px 0; border:none;">: {{ $dispatch->company->name }}</td>
                        </tr>
                        @endif
                        @if($dispatch->bill_no)
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Bill No</td>
                            <td style="padding: 2px 0; border:none;">: {{ $dispatch->bill_no }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">S.N.</th>
                    <th width="30%">Description of Goods</th>
                    <th width="8%" class="text-center">PCs Qty.</th>
                    <th width="8%" class="text-center">BoX Qty.</th>
                    <th width="8%" class="text-center">Unit</th>
                    <th width="10%" class="text-right">MRP</th>
                    <th width="8%" class="text-right">Disc. %</th>
                    <th width="10%" class="text-right">Price</th>
                    <th width="13%" class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $tP = 0; $tB = 0; @endphp
                @foreach($groupedItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td style="text-transform: uppercase;"><strong>{{ $item->product_name }} {{ $item->size_set_name }} {{-- @if($item->color_name) ({{ $item->color_name }}) @endif --}}</strong></td>
                        <td class="text-right">{{ number_format($item->total_qty, 2) }}</td>
                        <td class="text-right">{{ number_format($item->box_count, 2) }}</td>
                        <td class="text-center">BOX</td>
                        <td class="text-right">{{ number_format($item->mrp, 2) }}</td>
                        <td class="text-right">
                            @if($item->mrp > 0 && $item->mrp > $item->selling_price)
                                {{ number_format((($item->mrp - $item->selling_price) / $item->mrp) * 100, 2) }}%
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total_qty * $item->selling_price, 2) }}</td>
                    </tr>
                    @php 
                        $tP += $item->total_qty; 
                        $tB += $item->box_count;
                    @endphp
                @endforeach

                @for ($i = count($groupedItems); $i < 12; $i++)
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                @endfor

                <tr class="total-row">
                    <td></td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ number_format($tP, 2) }}Pcs.</td>
                    <td class="text-right">{{ number_format($tB, 2) }}</td>
                    <td class="text-center">Box.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ number_format($filteredSubtotal, 2) }}</td>
                </tr>

                @if(isset($discountAmt) && $discountAmt > 0)
                <tr class="summary-row">
                    <td colspan="8" class="text-right">Extra Discount</td>
                    <td class="text-right">- {{ number_format($discountAmt, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row">
                    <td colspan="7" class="text-right">GST</td>
                    <td class="text-right"></td>
                    <td class="text-right">{{ number_format($filteredGst, 2) }}</td>
                </tr>

                @if($dispatch->other_charges > 0 && !$brandId)
                <tr class="summary-row">
                    <td colspan="8" class="text-right">Other Charges</td>
                    <td class="text-right">{{ number_format($dispatch->other_charges, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row" style="background-color: #f8f9fa; border-top: 2px solid #000;">
                    <td colspan="8" class="text-right" style="font-size: 13px;">Grand Total :</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($filteredGrandTotal, 2) }}</td>
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
                        For <strong>{{ $brandTitle }}</strong><br>
                        <strong>Authorised Signatory</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
