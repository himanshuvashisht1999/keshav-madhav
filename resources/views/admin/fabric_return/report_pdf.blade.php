<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Return - {{ $return->return_number }}</title>
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
            font-weight: 600;
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
            font-size: 10px;
        }

        /* Valuation Summary Strip */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-cell {
            border: 1px solid #cbd5e1;
            background: #fbfdfc;
            padding: 6px 8px;
            text-align: center;
        }

        .summary-label {
            font-size: 8.5px;
            font-weight: 700;
            color: #074e22;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .summary-val {
            font-size: 11px;
            font-weight: 800;
            color: #05421c;
        }

        .summary-total {
            background: #edf7e4;
            border-top: 2px solid #05421c;
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
                    @php
                        $general_setting = \App\Models\GeneralSettings::where('status', 1)->first() ?? \App\Models\GeneralSettings::first();
                    @endphp
                    <div class="company-name">{{ $general_setting->website_name ?? 'SNAPKID' }}</div>
                    <div class="company-meta">
                        <div>{{ $general_setting->address ?? 'K-43 SECTOR D-1(P), TRONIKA CITY GHAZIABAD-201102' }}</div>
                        <div>
                            @if(isset($general_setting->phone))<span><b>Phone:</b> {{ $general_setting->phone }}</span>@endif
                            @if(isset($general_setting->email))<span style="margin-left: 10px;"><b>Email:</b> {{ $general_setting->email }}</span>@endif
                        </div>
                    </div>
                </td>
                <td width="30%" align="right" valign="top">
                    @php
                        $logoFilename = $general_setting ? ($general_setting->getRawOriginal('logo') ?? $general_setting->logo) : null;
                        $logoPath = $logoFilename ? public_path('assets/general-settings-image/' . $logoFilename) : null;
                        if (!$logoPath || !file_exists($logoPath)) {
                            $logoPath = public_path('images/snapkid_logo.png');
                        }
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="48" style="object-fit: contain;" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>

        <!-- Valuation Summary Strip -->
        <table class="summary-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Base Amount</div>
                    <div class="summary-val">Rs. {{ number_format($return->sub_total ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">GST ({{ (float)($return->gst_percentage ?? 0) }}%)</div>
                    <div class="summary-val">Rs. {{ number_format($return->gst_amount ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Charges / Disc.</div>
                    <div class="summary-val">Rs. {{ number_format(($return->other_charges ?? 0) - ($return->discount ?? 0), 2) }}</div>
                </td>
                <td class="summary-cell summary-total" width="20%">
                    <div class="summary-label" style="color: #15803d;">Total Return Value</div>
                    <div class="summary-val" style="color: #052a12;">Rs. {{ number_format($return->total_amount ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Total Rolls Returned</div>
                    <div class="summary-val">{{ $return->details->count() }}</div>
                </td>
            </tr>
        </table>

        <!-- Two Column Voucher Info -->
        @php
            $vendor = $return->vendor ?? ($return->receipt->vendor ?? null);
        @endphp
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Vendor / Supplier Details</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Vendor Name</td>
                                    <td class="info-val">: {{ $vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Phone</td>
                                    <td class="info-val">: {{ $vendor->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Address</td>
                                    <td class="info-val">: {{ $vendor->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Email</td>
                                    <td class="info-val">: {{ $vendor->email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Return Voucher Information</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Voucher No</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $return->return_number }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Return Date</td>
                                    <td class="info-val">: {{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Total Meters</td>
                                    <td class="info-val">: {{ number_format($return->details->sum('return_meter'), 2) }} M</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Total Rolls</td>
                                    <td class="info-val">: {{ $return->details->count() }} Rolls</td>
                                </tr>
                                @if($return->remarks)
                                <tr>
                                    <td class="info-label">Remarks</td>
                                    <td class="info-val">: {{ $return->remarks }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Returned Fabric Rolls Section -->
        <div class="section-title">Returned Fabric Rolls (Multi-Shipment Breakdown)</div>
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="4%">#</th>
                    <th width="14%">Roll No</th>
                    <th width="15%">Shipment No</th>
                    <th width="11%">Bill No</th>
                    <th class="text-left">Fabric Item & SKU</th>
                    <th width="14%">Warehouse</th>
                    <th width="12%" class="text-right">Return (M)</th>
                    <th width="12%" class="text-right">Rate/Mtr (Rs.)</th>
                    <th width="14%" class="text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalMetersSum = 0;
                    $totalAmountSum = 0;
                @endphp
                @forelse($return->details as $key => $detail)
                    @php
                        $rd = $detail->receipt_detail;
                        $rc = $rd->fabric_receipt ?? null;
                        $wh = $rd->master_fabric_warehouse->cutting_master_name ?? ($rc->master_fabric_warehouse->cutting_master_name ?? 'N/A');
                        $lineTotal = $detail->return_meter * $detail->price_per_meter;
                        $totalMetersSum += $detail->return_meter;
                        $totalAmountSum += $lineTotal;
                    @endphp
                    <tr class="{{ $key % 2 == 1 ? 'even' : '' }}">
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td class="text-center"><b>{{ $rd->roll_number ?? ('#' . $rd->id) }}</b></td>
                        <td class="text-center"><b>{{ $rc ? ($rc->shipment_id ?: $rc->sku) : '-' }}</b></td>
                        <td class="text-center">{{ $rc->bill_no ?? '-' }}</td>
                        <td class="text-left">
                            <b>{{ $detail->fabric->name ?? 'N/A' }}</b>
                            @if(!empty($detail->fabric->sku))
                                <span style="font-size: 8.5px; color: #457855;">({{ $detail->fabric->sku }})</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $wh }}</td>
                        <td class="text-right"><b>{{ number_format($detail->return_meter, 2) }}</b></td>
                        <td class="text-right">{{ number_format($detail->price_per_meter, 2) }}</td>
                        <td class="text-right"><b>{{ number_format($lineTotal, 2) }}</b></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No returned fabric roll details found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="summary-total" style="font-weight: 800;">
                    <td colspan="6" class="text-right" style="font-size: 9.5px; text-transform: uppercase;"><b>TOTAL:</b></td>
                    <td class="text-right"><b>{{ number_format($totalMetersSum, 2) }} M</b></td>
                    <td></td>
                    <td class="text-right"><b>Rs. {{ number_format($totalAmountSum, 2) }}</b></td>
                </tr>
            </tfoot>
        </table>

        <!-- Sign-off & Footer -->
        <table class="signoff-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="35%" valign="bottom">
                    <div class="signature-line"></div>
                    <div class="signoff-caption">Prepared By / Store Incharge</div>
                </td>
                <td width="35%" valign="bottom">
                    <div class="signature-line"></div>
                    <div class="signoff-caption">Authorized Signatory / Vendor Ack</div>
                </td>
                <td width="30%" align="right" valign="bottom">
                    <div class="thank-you">Thank you for your business!</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            Generated on {{ date('j M Y, h:i A') }} | Fabric Return {{ $return->return_number }}
        </div>
    </div>
</body>
</html>
