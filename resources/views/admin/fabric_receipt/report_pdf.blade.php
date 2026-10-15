<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fabric Shipment - {{ $data->shipment_id }}</title>
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
            border: 1px solid #c8d8c4;
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
            border: 1px solid #c8d8c4;
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
            color: #05421c;
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
            border: 1px solid #c8d8c4;
            background: #fbfdfc;
            padding: 6px 8px;
            text-align: center;
        }

        .summary-label {
            font-size: 8.5px;
            font-weight: 700;
            color: #05421c;
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
            border-right: 1px solid #c8d8c4;
            border-left: 1px solid #c8d8c4;
            text-align: center;
        }

        .items-table td {
            padding: 5px 6px;
            font-size: 9.5px;
            color: #05421c;
            border: 1px solid #dfe9da;
            vertical-align: middle;
            font-weight: 600;
        }

        .items-table tbody tr.even {
            background: #fbfdfc;
        }

        .barcode-img {
            width: 100px;
            height: 32px;
        }

        .barcode-number {
            font-size: 8px;
            font-weight: bold;
            margin-top: 1px;
            color: #05421c;
        }

        /* Sign-off & Footer */
        .signoff-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        .signature-line {
            width: 180px;
            border-bottom: 1px solid #05421c;
            height: 30px;
            margin-bottom: 4px;
        }

        .signoff-caption {
            font-size: 8.5px;
            font-weight: 700;
            color: #05421c;
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
            color: #05421c;
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
                        $general_setting = \App\Models\GeneralSettings::where('status', 1)->first();
                    @endphp
                    <div class="company-name">@include('admin.pdf.partials.brand_name', ['name' => $general_setting->website_name ?? 'SNAPKID'])</div>
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
                        $logoPath = public_path('admin_assets/img/logo.png');
                    @endphp
                    @if(file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="48" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>

        <!-- Valuation Summary Strip -->
        <table class="summary-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Base Amount</div>
                    <div class="summary-val">Rs. {{ number_format($data->amount ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">GST ({{ $data->gst_percentage ?? 0 }}%)</div>
                    <div class="summary-val">Rs. {{ number_format($data->gst_amount ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Other Charges</div>
                    <div class="summary-val">Rs. {{ number_format($data->other_charges ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell summary-total" width="20%">
                    <div class="summary-label" style="color: #15803d;">Total Amount</div>
                    <div class="summary-val" style="color: #052a12;">Rs. {{ number_format($data->total_amount ?? 0, 2) }}</div>
                </td>
                <td class="summary-cell" width="20%">
                    <div class="summary-label">Total Rolls</div>
                    <div class="summary-val">{{ $data->details->count() }}</div>
                </td>
            </tr>
        </table>

        <!-- Two Column Voucher Info -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Vendor Details</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Vendor Name</td>
                                    <td class="info-val">: {{ $data->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Phone</td>
                                    <td class="info-val">: {{ $data->vendor->phone ?? 'N/A' }}</td>
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
                        <div class="info-card-header">Shipment Information</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Shipment No</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $data->shipment_id }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Bill No</td>
                                    <td class="info-val">: {{ $data->bill_no ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Warehouse</td>
                                    <td class="info-val">: {{ $data->cutting_master->cutting_master_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Date</td>
                                    <td class="info-val">: {{ \Carbon\Carbon::parse($data->time)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Received By</td>
                                    <td class="info-val">: {{ $data->received_by ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Fabric Rolls Section -->
        <div class="section-title">Received Fabric Rolls Breakdown</div>
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th class="text-left">Fabric Item</th>
                    <th width="15%" class="text-right">Price/Mtr (Rs.)</th>
                    <th width="18%">Roll No</th>
                    <th width="15%" class="text-right">Meters</th>
                    <th width="22%">Barcode</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalMetersSum = 0;
                @endphp
                @forelse($data->details as $key => $detail)
                    @php
                        $totalMetersSum += $detail->meter;
                    @endphp
                    <tr class="{{ $key % 2 == 1 ? 'even' : '' }}">
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td class="text-left"><b>{{ $detail->fabric->name ?? '-' }}</b></td>
                        <td class="text-right">{{ number_format($detail->price_per_meter ?? 0, 2) }}</td>
                        <td class="text-center"><b>{{ $detail->roll_number }}</b></td>
                        <td class="text-right"><b>{{ number_format($detail->meter, 2) }}</b></td>
                        <td class="text-center">
                            @php
                                $barcodeFilename = $detail->getRawOriginal('barcode');
                                $barcodePath = public_path('assets/barcodes/' . $barcodeFilename);
                            @endphp
                            @if($barcodeFilename && file_exists($barcodePath))
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($barcodePath)) }}" class="barcode-img">
                            @endif
                            <div class="barcode-number">{{ $detail->qrcode_number }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No fabric roll details found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right" style="font-size: 9.5px; text-transform: uppercase;"><b>Total Received Meters:</b></td>
                    <td class="text-right"><b>{{ number_format($totalMetersSum, 2) }}</b></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Sign-off & Footer -->
        <table class="signoff-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" valign="bottom">
                    <div class="signature-line"></div>
                    <div class="signoff-caption">Authorized Receiver Signature</div>
                </td>
                <td width="50%" align="right" valign="bottom">
                    <div class="thank-you">Thank you for your business!</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            Generated on {{ date('j M Y, h:i A') }} | Fabric Shipment {{ $data->shipment_id }}
        </div>
    </div>
</body>
</html>