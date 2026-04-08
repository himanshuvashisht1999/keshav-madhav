<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Packing Slip #ORD-{{ $order->id }}</title>
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
            padding: 0;
            margin: 0;
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

        .footer-section {
            border-top: 1px solid #000;
            width: 100%;
            display: table;
            table-layout: fixed;
            background-color: #fff;
        }

        .footer-row {
            display: table-row;
        }

        .footer-col {
            display: table-cell;
            padding: 15px;
            border-right: 1px solid #000;
            vertical-align: top;
        }

        .footer-col:last-child {
            border-right: none;
        }

        .total-row td {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            padding: 10px 8px;
            background-color: #f8f9fa;
        }

        .box {
            border: 1px solid #000;
            padding: 5px;
        }

        .signature-space {
            height: 60px;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="header-section">
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td width="30%" class="text-left" style="vertical-align: middle;">
                        @php
                            $logoData = "";
                            if(isset($settings->logo) && $settings->logo) {
                                $logoPath = public_path('assets/general-settings-image/' . $settings->logo);
                                if(file_exists($logoPath)) {
                                    $logoData = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(@file_get_contents($logoPath));
                                }
                            }

                            $surgicalLogo = "";
                            $snapkidLogo = "";
                            $surgical = \App\Models\Brand::where('name', 'SURGICAL')->first();
                            if($surgical && $surgical->logo) {
                                $sPath = public_path('assets/brands/' . $surgical->logo);
                                if(file_exists($sPath)) {
                                    $surgicalLogo = 'data:image/' . pathinfo($sPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(@file_get_contents($sPath));
                                }
                            }
                            $snapkid = \App\Models\Brand::where('name', 'SNAPKID')->first();
                            if($snapkid && $snapkid->logo) {
                                $skPath = public_path('assets/brands/' . $snapkid->logo);
                                if(file_exists($skPath)) {
                                    $snapkidLogo = 'data:image/' . pathinfo($skPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(@file_get_contents($skPath));
                                }
                            }
                        @endphp

                        @if(isset($type) && $type == 'actual')
                            <div style="display: inline-block;">
                                <!-- @if($surgicalLogo)
                                    <img src="{{ $surgicalLogo }}" style="height: 45px; max-width: 100px; margin-right: 5px;">
                                @endif -->
                                @if($snapkidLogo)
                                    <img src="{{ $snapkidLogo }}" style="height: 45px; max-width: 100px;">
                                @endif
                            </div>
                        @elseif(isset($selectedBrand) && $selectedBrand)
                            @if($selectedBrand->name == 'SURGICAL' && $surgicalLogo)
                                <img src="{{ $surgicalLogo }}" style="height: 60px; max-width: 180px;">
                            @elseif($selectedBrand->name == 'SNAPKID' && $snapkidLogo)
                                <img src="{{ $snapkidLogo }}" style="height: 60px; max-width: 180px;">
                            @elseif($logoData)
                                <img src="{{ $logoData }}" style="height: 60px; max-width: 180px;">
                            @endif
                        @elseif($logoData)
                            <img src="{{ $logoData }}" style="height: 60px; max-width: 180px;">
                        @endif
                    </td>
                    <td width="40%" class="text-center" style="vertical-align: middle;">
                        <div class="estimate-title">PACKING SLIP</div>
                        <div class="company-name">
                            @if(isset($type) && $type == 'actual')
                                <!-- SURGICAL & SNAPKID -->
                                SNAPKID

                            @elseif(isset($selectedBrand) && $selectedBrand)
                                {{ $selectedBrand->name }}
                            @else
                                {{ $settings->website_name ?? 'SURGICAL JEANS' }}
                            @endif
                        </div>
                        <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
                    </td>
                    <td width="30%" class="text-right" style="vertical-align: middle;">
                         @if(isset($type) && $type == 'actual')
                            <div style="display: inline-block;">
                                <!-- @if($surgicalLogo)
                                    <img src="{{ $surgicalLogo }}" style="height: 45px; max-width: 100px; margin-right: 5px;">
                                @endif -->
                                @if($snapkidLogo)
                                    <img src="{{ $snapkidLogo }}" style="height: 45px; max-width: 100px;">
                                @endif
                            </div>
                        @elseif(isset($selectedBrand) && $selectedBrand)
                            @if($selectedBrand->name == 'SURGICAL' && $surgicalLogo)
                                <img src="{{ $surgicalLogo }}" style="height: 60px; max-width: 180px;">
                            @elseif($selectedBrand->name == 'SNAPKID' && $snapkidLogo)
                                <img src="{{ $snapkidLogo }}" style="height: 60px; max-width: 180px;">
                            @elseif($logoData)
                                <img src="{{ $logoData }}" style="height: 60px; max-width: 180px;">
                            @endif
                        @elseif($logoData)
                            <img src="{{ $logoData }}" style="height: 60px; max-width: 180px;">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <table class="info-table" cellpadding="0" cellspacing="0">
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
                            <td width="40%" style="padding: 2px 0; border:none; font-weight:bold;">Invoice No</td>
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

        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="5%" class="text-center">S.N.</th>
                    <th width="45%">Description of Goods</th>
                    <th width="15%" class="text-center">PCs Qty.</th>
                    <th width="15%" class="text-center">BoX Qty.</th>
                    <th width="20%" class="text-center">Unit</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $total_pcs = 0; 
                    $total_boxes = 0;
                @endphp
                @foreach($items as $index => $item)
                    <tr>
                        <td class="text-center" style="border-bottom: 0.5px solid #eee;">{{ $index + 1 }}.</td>
                        <td style="border-bottom: 0.5px solid #eee;"><strong>{{ $item->description }}</strong></td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item->pcs_qty, 2) }}</td>
                        <td class="text-right" style="border-bottom: 0.5px solid #eee;">{{ number_format($item->box_qty, 2) }}</td>
                        <td class="text-center" style="border-bottom: 0.5px solid #eee;">{{ $item->unit }}</td>
                    </tr>
                    @php 
                        $total_pcs += $item->pcs_qty; 
                        $total_boxes += $item->box_qty;
                    @endphp
                @endforeach
                
                {{-- Reduced filler rows to push total to a visible spot --}}
                @for ($i = count($items); $i < 10; $i++)
                    <tr>
                        <td style="height: 20px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor

                <tr class="total-row">
                    <td class="text-center" colspan="2" style="font-size: 12px;">TOTAL SHIPMENT QUANTITY</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($total_pcs, 2) }} Pcs.</td>
                    <td class="text-right" style="font-size: 13px;">{{ number_format($total_boxes, 2) }}</td>
                    <td class="text-center" style="font-size: 13px;">Box.</td>
                </tr>
            </tbody>
        </table>

        <table width="100%" style="border-top: 1px solid #000; border-collapse: collapse; margin-top: 5px;">
            <tr>
                <td colspan="2" style="padding: 10px 15px 5px 15px;">
                    <strong>Remarks :</strong> {{ $order->remarks ?? '' }}
                </td>
            </tr>
            <tr>
                <td width="60%" style="padding: 5px 15px; vertical-align: top;">
                    <strong>Terms & Conditions</strong><br>
                    E.& O.E.<br>
                    1. Goods once sold will not be taken back.
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
