<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Packing Slip - {{ $dispatch->id }}</title>
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
        .info-table { width: 100%; border-bottom: 1px solid #000; border-collapse: collapse; }
        .info-table td { vertical-align: top; padding: 10px; border-right: 1px solid #000; }
        .info-table td:last-child { border-right: none; }
        .party-label { font-size: 10px; text-transform: uppercase; color: #555; margin-bottom: 5px; }
        .party-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { border-bottom: 2px solid #000; border-right: 1px solid #000; padding: 8px 5px; background-color: #f2f2f2; text-transform: uppercase; font-size: 10px; }
        .items-table td { border-right: 1px solid #000; padding: 8px 10px; vertical-align: top; border-bottom: 1px solid #ddd; }
        .items-table th:last-child, .items-table td:last-child { border-right: none; }
        .total-row td { border-top: 2px solid #000; border-bottom: 2px solid #000; font-weight: bold; padding: 10px 8px; background-color: #f8f9fa; }
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
    @endphp

    <div class="main-container">
        <div class="header-section text-center">
            @if($logoData) <img src="{{ $logoData }}" class="logo-img" style="left: 15px;"> @endif
            <div class="estimate-title">FABRIC PACKING SLIP</div>
            <div class="company-name">{{ $settings->website_name ?? 'SURGICAL JEANS' }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
        </div>

        <table class="info-table">
            <tr>
                <td width="60%">
                    <div class="party-label">Party Details :</div>
                    <div class="party-name">{{ $dispatch->shop->name ?? 'N/A' }}</div>
                    <div>{{ $dispatch->shop->address ?? '' }}</div>
                    <div>Phone: {{ $dispatch->shop->phone ?? 'N/A' }}</div>
                </td>
                <td width="40%">
                    <table width="100%" style="border-collapse: collapse;">
                        <tr>
                            <td width="45%" style="padding: 2px 0; border:none; font-weight:bold;">Slip No</td>
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
                    <th>Fabric Description</th>
                    <th class="text-center" width="15%">Batch No</th>
                    <th class="text-center" width="15%">Meters</th>
                </tr>
            </thead>
            <tbody>
                @php $tMeters = 0; @endphp
                @foreach($fabricItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td><strong>{{ $item->roll_number }}</strong></td>
                        <td style="text-transform: uppercase;">{{ $item->fabric_name }}</td>
                        <td class="text-center">{{ $item->batch_no }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                    </tr>
                    @php $tMeters += $item->meter; @endphp
                @endforeach

                @for ($i = count($fabricItems); $i < 12; $i++)
                    <tr><td style="height: 25px;">&nbsp;</td><td></td><td></td><td></td><td></td></tr>
                @endfor

                <tr class="total-row">
                    <td class="text-center" colspan="4" style="font-size: 12px;">TOTAL SHIPMENT METERS</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($tMeters, 2) }} m</td>
                </tr>
            </tbody>
        </table>

        <table width="100%" style="border-top: 1px solid #000; border-collapse: collapse; margin-top: 5px;">
            <tr>
                <td colspan="2" style="padding: 10px 15px 5px 15px;">
                    <strong>Remarks :</strong> {{ $dispatch->remarks ?? '' }}
                </td>
            </tr>
            <tr>
                <td width="60%" style="padding: 5px 15px; vertical-align: top;">
                    <strong>Terms & Conditions</strong><br>
                    E.& O.E.<br>
                    1. Goods once sold will not be taken back.<br>
                </td>
                <td width="40%" style="padding: 5px 15px; vertical-align: top; text-align: right;">
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
