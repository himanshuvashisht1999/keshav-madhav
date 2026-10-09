<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Purchase Order - {{ $po->po_number }}</title>
    <style>
        @page {
            margin: 8mm 8mm 8mm 8mm;
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
            padding: 12px 14px;
            background: #ffffff;
        }

        /* Header */
        .company-header {
            width: 100%;
            border-bottom: 2px solid #05421c;
            padding-bottom: 8px;
            margin-bottom: 10px;
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
            font-size: 9.5px;
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
            padding: 2.5px 0;
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
            font-size: 9.5px;
        }

        /* Items Table */
        .section-title {
            font-size: 10.5px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 10px 0 5px 0;
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
            letter-spacing: 0.3px;
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

        .detail-line {
            margin: 1.5px 0;
            font-size: 9px;
            color: #1e293b;
        }

        .detail-line b {
            color: #05421c;
        }

        /* Remarks Box */
        .bottom-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            margin-top: 10px;
        }

        .bottom-card-header {
            background: #edf7e4;
            border-bottom: 1px solid #8bc63e;
            padding: 3px 8px;
            font-size: 8.5px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
        }

        .bottom-card-body {
            padding: 5px 8px;
            font-size: 9px;
            color: #05421c;
            font-weight: 600;
        }

        /* Sign-off & Footer */
        .signoff-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .signoff-box {
            border-top: 1px solid #05421c;
            display: inline-block;
            width: 180px;
            text-align: center;
            padding-top: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #05421c;
            text-transform: uppercase;
        }

        .print-date {
            text-align: center;
            font-size: 8px;
            color: #457855;
            margin-top: 12px;
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
                <td width="72%" valign="top">
                    <div class="company-name">{{ $general_setting->website_name ?? 'SNAPKID' }}</div>
                    <div class="company-meta">
                        <div>{{ $general_setting->address ?? '' }}</div>
                        <div>
                            @if(!empty($general_setting->phone))<span><b>Phone:</b> {{ $general_setting->phone }}</span>@endif
                            @if(!empty($general_setting->email))<span style="margin-left: 10px;"><b>Email:</b> {{ $general_setting->email }}</span>@endif
                        </div>
                    </div>
                </td>
                <td width="28%" align="right" valign="top">
                    @php
                        $logoFilename = $general_setting ? $general_setting->getRawOriginal('logo') : null;
                        $logoPath = $logoFilename ? public_path('assets/general-settings-image/' . $logoFilename) : null;
                    @endphp
                    @if($logoFilename && file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="48" style="object-fit: contain;">
                    @elseif($general_setting && $general_setting->logo && file_exists(public_path(str_replace(url('/'), '', $general_setting->logo))))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path(str_replace(url('/'), '', $general_setting->logo)))) }}" height="48" style="object-fit: contain;">
                    @endif
                </td>
            </tr>
        </table>

        <!-- PO Assigned To & PO Information Side by Side -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">PO Assigned To</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Party Type</td>
                                    <td class="info-val">: {{ $po->vendor_id ? 'Vendor' : 'Customer' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Name</td>
                                    <td class="info-val">: {{ $po->vendor->name ?? ($po->customer->name ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Mobile</td>
                                    <td class="info-val">: {{ $po->vendor->mobile ?? ($po->customer->mobile ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Address</td>
                                    <td class="info-val">: {{ Str::limit($po->vendor->address ?? ($po->customer->address ?? 'N/A'), 65) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">PO Information</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">PO Number</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $po->po_number }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Sales Order</td>
                                    <td class="info-val">: {{ $po->orderMain->sku ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">PO Date</td>
                                    <td class="info-val">: {{ $po->created_at->format('j M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Delivery Date</td>
                                    <td class="info-val">: {{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Section Title -->
        <div class="section-title">Order Items</div>

        <!-- Items Table -->
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 14%;">DESIGN</th>
                    <th>PRODUCT DETAILS</th>
                    <th style="width: 10%;">QUANTITY</th>
                    <th style="width: 11%;">RATE (RS.)</th>
                    <th style="width: 12%;">TOTAL (RS.)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; $totalAmount = 0; @endphp
                @foreach($po->items as $index => $item)
                    @php 
                        $subtotal = $item->quantity * $item->rate;
                        $totalQty += $item->quantity;
                        $totalAmount += $subtotal;

                        $ratioStr = '-';
                        if(!empty($item->productSet->size_measurement->size_group)) {
                            $sizes = array_map('trim', explode(',', $item->productSet->size_measurement->size_group));
                            $counts = array_count_values($sizes);
                            $parts = [];
                            foreach($counts as $s => $c) {
                                $parts[] = $s . ':' . $c;
                            }
                            $ratioStr = implode(', ', $parts);
                        }
                    @endphp
                    <tr class="{{ $index % 2 == 1 ? 'even' : '' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            <span style="font-weight: 800; font-size: 10.5px; color: #05421c;">{{ $item->productSet->design_number ?? 'N/A' }}</span>
                            @if(!empty($item->productSet->sku))
                                <br><small style="font-size: 8px; color: #64748b;">{{ $item->productSet->sku }}</small>
                            @endif
                        </td>
                        <td class="text-left">
                            <div class="detail-line">
                                <b>Color:</b> {{ $item->productSet->colors->name ?? 'N/A' }} | 
                                <b>Size:</b> {{ $item->productSet->set_size ?? ($item->productSet->size_set_name ?? 'N/A') }} | 
                                <b>Ratio:</b> {{ $ratioStr }}
                            </div>
                            <div class="detail-line">
                                <b>Fabric:</b> {{ $item->fabric_names ?: '-' }} | 
                                <b>Pattern:</b> {{ $item->pattern->name ?? '-' }} | 
                                <b>Fitting:</b> {{ $item->master_fitting->name ?? '-' }}
                            </div>
                            @if($item->belt)
                                <div class="detail-line"><b>Belt:</b> {{ $item->belt }}</div>
                            @endif
                            @if($item->remarks)
                                <div class="detail-line" style="font-style: italic;"><b>Remark:</b> {{ $item->remarks }}</div>
                            @endif
                        </td>
                        <td class="text-center" style="font-weight: bold; font-size: 10px;">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="text-transform: uppercase;">TOTAL:</td>
                    <td class="text-center">{{ $totalQty }} Pcs</td>
                    <td></td>
                    <td class="text-right">Rs. {{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- PO Remark if available -->
        @if(!empty($po->remark))
            <div class="bottom-card">
                <div class="bottom-card-header">Global Remark</div>
                <div class="bottom-card-body">{{ $po->remark }}</div>
            </div>
        @endif

        <!-- Sign-off Block -->
        <table class="signoff-table">
            <tr>
                <td width="50%" align="left" valign="bottom">
                    <span style="font-size: 8.5px; color: #64748b;">Generated on: {{ date('d-m-Y h:i A') }}</span>
                </td>
                <td width="50%" align="right" valign="bottom">
                    <div class="signoff-box">Authorized Signature</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            This is a computer generated purchase order and does not require physical stamp unless specified.
        </div>
    </div>
</body>
</html>
