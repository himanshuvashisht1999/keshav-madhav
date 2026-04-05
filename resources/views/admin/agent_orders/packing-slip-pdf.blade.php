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
        <div class="header-section text-center">
            <div class="estimate-title">ESTIMATE</div>
            <div class="company-name">{{ $settings->website_name ?? 'SURGICAL JEANS' }}</div>
            <div style="font-size: 10px;">{{ $settings->address ?? '' }}</div>
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
                    1. Goods once sold will not be taken back.<br>
                    2.FOR ANY ACCOUNT RELATED INFORMATION-8094409864(DEVA NAYAK)
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
