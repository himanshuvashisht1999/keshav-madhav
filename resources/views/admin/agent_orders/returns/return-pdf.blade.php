<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Return - {{ $return->id }}</title>
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
        $snapkidLogo = "";
        $snapkid = \App\Models\Brand::where('name', 'SNAPKID')->first();
        if($snapkid && $snapkid->logo) {
            $skPath = public_path('assets/brands/' . $snapkid->logo);
            if(file_exists($skPath)) { $snapkidLogo = 'data:image/png;base64,' . base64_encode(file_get_contents($skPath)); }
        }
        
        $brandTitle = "SNAPKID";

        $f = new \NumberFormatter("en", \NumberFormatter::SPELLOUT);
        $amountInWords = ucwords($f->format($return->grand_total));

        $party = $return->dispatch->party;
    @endphp

    <div class="main-container">
        <div class="header-section text-center">
            @if($snapkidLogo) <img src="{{ $snapkidLogo }}" class="logo-img" style="left: 15px;"> @endif
            <div class="estimate-title">SALES RETURN</div>
            <div class="company-name">{{ $brandTitle }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? 'TRONIKA CITY GHAZIABAD-201102' }}</div>
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
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Return No</td>
                            <td style="padding: 2px 0; border:none;">: <strong>SR-{{ $return->id }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Return Date</td>
                            <td style="padding: 2px 0; border:none;">: <strong>{{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Against Dispatch</td>
                            <td style="padding: 2px 0; border:none;">: #{{ $return->agent_order_dispatch_id }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 2px 0; border:none; font-weight:bold;">Agent</td>
                            <td style="padding: 2px 0; border:none;">: {{ $return->dispatch->agent->name ?? 'Direct' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%" class="text-center">S.N.</th>
                    <th width="45%">Item Description</th>
                    <th width="15%" class="text-center">Returned Qty</th>
                    <th width="15%" class="text-right">Price</th>
                    <th width="20%" class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; @endphp
                @foreach($return->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td style="text-transform: uppercase;">
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->item_type === 'standard')
                                <br><small>{{ $item->design_number }} | {{ $item->color_name }} | {{ $item->size_set_name }}</small>
                            @else
                                <br><small>Fabric Roll Return</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, ($item->item_type === 'fabric' ? 2 : 0)) }} {{ $item->unit }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @php $totalQty += $item->quantity; @endphp
                @endforeach

                @for ($i = count($return->items); $i < 12; $i++)
                    <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                @endfor

                <tr class="total-row">
                    <td></td>
                    <td class="text-right">Subtotal:</td>
                    <td class="text-center">{{ number_format($totalQty, 2) }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($return->total_amount, 2) }}</td>
                </tr>

                @if($return->discount_amount > 0)
                <tr class="summary-row">
                    <td colspan="4" class="text-right">Extra Discount ({{ number_format($return->discount_percentage, 2) }}%)</td>
                    <td class="text-right">- {{ number_format($return->discount_amount, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row">
                    <td colspan="4" class="text-right">GST ({{ number_format($return->gst_percentage, 2) }}%)</td>
                    <td class="text-right">+ {{ number_format($return->gst_amount, 2) }}</td>
                </tr>

                @if($return->other_charges > 0)
                <tr class="summary-row">
                    <td colspan="4" class="text-right">Other Charges</td>
                    <td class="text-right">+ {{ number_format($return->other_charges, 2) }}</td>
                </tr>
                @endif

                <tr class="summary-row" style="background-color: #f8f9fa; border-top: 2px solid #000;">
                    <td colspan="4" class="text-right" style="font-size: 13px;">Grand Total :</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($return->grand_total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="amount-in-words">
            Rupees {{ $amountInWords }} Only
        </div>

        <table class="footer-table">
            <tr>
                <td width="60%">
                    <div style="margin-bottom: 10px;"><strong>Remarks :</strong> {{ $return->remark ?? 'No remarks provided.' }}</div>
                </td>
                <td width="40%" class="text-right">
                    <strong>Authorised Signatory</strong><br><br><br><br>
                    <div style="margin-top: 20px;">
                        For <strong>{{ $brandTitle }}</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
