<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $data->sku }}</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #05421c;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Outer Frame / Voucher Sheet */
        .voucher-sheet {
            border: 1px solid #cbd5e1;
            border-top: 4px solid #fcee21;
            border-radius: 4px;
            padding: 14px 16px;
        }

        /* Header */
        .company-header {
            width: 100%;
            border-bottom: 2px solid #05421c;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
        }

        .company-meta {
            font-size: 9.5px;
            color: #05421c;
            line-height: 1.4;
            font-weight: 600;
        }

        .company-meta b {
            color: #05421c;
            font-weight: bold;
        }

        /* 2-Column Info Boxes */
        .info-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
        }

        .info-card-header {
            background: #edf7e4;
            border-bottom: 1px solid #8bc63e;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .info-card-body {
            padding: 6px 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            font-size: 9.5px;
            vertical-align: top;
        }

        .info-label {
            width: 32%;
            font-weight: 700;
            color: #074e22;
            text-transform: uppercase;
            font-size: 8.5px;
        }

        .info-val {
            color: #05421c;
            font-weight: bold;
        }

        .sku-badge {
            display: inline-block;
            background: #fcee21;
            color: #05421c;
            border: 1px solid #e2d514;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 800;
            font-size: 10px;
        }

        /* Items Table */
        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 12px 0 5px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .items-table th {
            background: #edf7e4;
            color: #05421c;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 5px 6px;
            border-top: 1px solid #05421c;
            border-bottom: 2px solid #05421c;
            border-right: 1px solid #cbd5e1;
            border-left: 1px solid #cbd5e1;
            text-align: center;
        }

        .items-table td {
            padding: 5px 6px;
            font-size: 9.5px;
            color: #05421c;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-weight: 600;
        }

        .items-table tbody tr.even {
            background: #fbfdfc;
        }

        .items-table tfoot td {
            background: #edf7e4;
            color: #05421c;
            font-weight: 800;
            font-size: 10px;
            border-top: 2px solid #05421c;
            border-bottom: 2px solid #05421c;
            padding: 6px;
        }

        /* Warehouse & Remarks */
        .bottom-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            margin-top: 10px;
        }

        .bottom-card-header {
            background: #f2f8f4;
            border-bottom: 1px solid #d1e7dd;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: 800;
            color: #052a12;
            text-transform: uppercase;
        }

        .bottom-card-body {
            padding: 6px 8px;
            font-size: 9px;
            color: #0a3d1c;
            min-height: 25px;
        }

        /* Sign-off & Footer */
        .signoff-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        .signature-line {
            width: 180px;
            border-bottom: 1px solid #245834;
            height: 30px;
            margin-bottom: 4px;
        }

        .signoff-caption {
            font-size: 8.5px;
            font-weight: 700;
            color: #245834;
            text-transform: uppercase;
        }

        .thank-you {
            font-size: 11px;
            font-weight: 800;
            color: #15803d;
            font-style: italic;
        }

        .print-date {
            text-align: center;
            font-size: 8px;
            color: #457855;
            margin-top: 15px;
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="voucher-sheet">
        <!-- Company Header with Logo -->
        <table class="company-header" cellpadding="0" cellspacing="0">
            <tr>
                <td width="70%" valign="top">
                    @if($data->company)
                        <div class="company-name">{{ $data->company->name }}</div>
                        <div class="company-meta">
                            <div>{{ $data->company->address }}</div>
                            <div>
                                @if($data->company->phone)<span><b>Phone:</b> {{ $data->company->phone }}</span>@endif
                                @if($data->company->email)<span style="margin-left: 10px;"><b>Email:</b> {{ $data->company->email }}</span>@endif
                                @if($data->company->gst_number)<span style="margin-left: 10px;"><b>GST:</b> {{ $data->company->gst_number }}</span>@endif
                            </div>
                        </div>
                    @else
                        <div class="company-name">{{ $general_setting->website_name }}</div>
                        <div class="company-meta">
                            <div>{{ $general_setting->address }}</div>
                            <div>
                                @if($general_setting->phone)<span><b>Phone:</b> {{ $general_setting->phone }}</span>@endif
                                @if($general_setting->email)<span style="margin-left: 10px;"><b>Email:</b> {{ $general_setting->email }}</span>@endif
                            </div>
                        </div>
                    @endif
                </td>
                <td width="30%" align="right" valign="top">
                    @php
                        $logoFilename = $general_setting->getRawOriginal('logo');
                        $logoPath = public_path('assets/general-settings-image/' . $logoFilename);
                    @endphp
                    @if($logoFilename && file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="50" style="object-fit: contain;">
                    @endif
                </td>
            </tr>
        </table>

        <!-- Vendor & Purchase Order Info Side by Side -->
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Vendor Details</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Name</td>
                                    <td class="info-val">: {{ $data->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Phone</td>
                                    <td class="info-val">: {{ $data->vendor->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Email</td>
                                    <td class="info-val">: {{ $data->vendor->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Address</td>
                                    <td class="info-val">: {{ $data->vendor->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Purchase Order Info</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">PO Number</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $data->sku }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">PO Date</td>
                                    <td class="info-val">: {{ getformatDate($data->date) }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Delivery Date</td>
                                    <td class="info-val">: {{ getformatDate($data->delivery_date) }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Transport</td>
                                    <td class="info-val">: {{ $data->transport ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Order Items Section -->
        <div class="section-title">Fabric Order Items</div>
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th class="text-left">Fabric Item</th>
                    <th width="20%">Composition</th>
                    <th width="15%" class="text-right">Meters / Qty</th>
                    <th width="15%" class="text-right">Rate / Price (Rs.)</th>
                    <th width="18%" class="text-right">Total Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
                    $totalMeters = 0;
                @endphp
                @foreach($data->items as $index => $item)
                    @php
                        $total = null;
                        if ($item->price > 0) {
                            $total = $item->meter * $item->price;
                            $grandTotal += $total;
                        }
                        $totalMeters += $item->meter;
                    @endphp
                    <tr class="{{ $index % 2 == 1 ? 'even' : '' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left"><b>{{ $item->fabric->name }}</b></td>
                        <td class="text-center">{{ $item->fabric->fabric_composition->name ?? 'N/A' }}</td>
                        <td class="text-right"><b>{{ number_format($item->meter, 2) }}</b></td>
                        <td class="text-right">{{ $item->price > 0 ? getIndianCurrency($item->price) : 'N/A' }}</td>
                        <td class="text-right"><b>{{ $total !== null ? getIndianCurrency($total) : 'N/A' }}</b></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="font-size: 9.5px; text-transform: uppercase;"><b>Grand Total:</b></td>
                    <td class="text-right"><b>{{ number_format($totalMeters, 2) }}</b></td>
                    <td></td>
                    <td class="text-right"><b>{{ $grandTotal > 0 ? getIndianCurrency($grandTotal) : 'N/A' }}</b></td>
                </tr>
            </tfoot>
        </table>

        <!-- Delivery Warehouse & Remark Side by Side -->
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="bottom-card">
                        <div class="bottom-card-header">Delivery Warehouse Address</div>
                        <div class="bottom-card-body">
                            {{ $data->fabric_warehouse->address ?? 'N/A' }}
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="bottom-card">
                        <div class="bottom-card-header">Remarks / Notes</div>
                        <div class="bottom-card-body">
                            {{ $data->remark ?? 'None' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Sign-off & Footer -->
        <table class="signoff-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" valign="bottom">
                    <div class="signature-line"></div>
                    <div class="signoff-caption">Authorized Signature</div>
                </td>
                <td width="50%" align="right" valign="bottom">
                    <div class="thank-you">Thank you for your business!</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            Generated on {{ date('j M Y, h:i A') }} | Fabric Purchase Order {{ $data->sku }}
        </div>
    </div>
</body>
</html>
