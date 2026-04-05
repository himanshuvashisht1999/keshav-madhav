<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $data->sku }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .invoice-box {
            padding: 20px;
        }

        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-details h2 {
            margin: 0;
            font-weight: bold;
            color: #007bff;
            font-size: 20px;
        }

        .company-details p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        .vendor-box,
        .po-box {
            background: #f9f9f9;
            padding: 12px;
            border-radius: 6px;
            width: 46%;
        }

        .text-primary {
            color: #007bff !important;
            font-weight: bold;
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.invoice-table th {
            background: #007bff;
            color: #fff;
            text-align: center;
            padding: 8px;
            font-size: 12px;
            border: 1px solid #007bff;
        }

        table.invoice-table td {
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            border: 1px solid #e0e0e0;
            font-size: 11px;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            background: #f1f1f1;
        }

        .mt-4 { margin-top: 1.5rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-0 { margin-bottom: 0; }
        .text-right { text-align: right; }

        .footer {
            margin-top: 40px;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 200px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <div class="invoice-box">
        {{-- Header --}}
        <table width="100%" class="invoice-header">
            <tr>
                <td class="company-details">
                    @if($data->company)
                        <h2>{{ $data->company->name }}</h2>
                        <p>{{ $data->company->address }}</p>
                        <p>{{ $data->company->email }}</p>
                        <p><b>Phone:</b> {{ $data->company->phone }}</p>
                        <p><b>GST:</b> {{ $data->company->gst_number }}</p>
                    @else
                        <h2>{{ $general_setting->website_name }}</h2>
                        <p>{{ $general_setting->address }}</p>
                        <p>{{ $general_setting->email }}</p>
                        <p>Phone: {{ $general_setting->phone }}</p>
                    @endif
                    <p style="margin-top: 5px;"><b>Delivery Warehouse Address:</b> {{ $data->fabric_warehouse->address }}</p>
                </td>
                <td align="right" valign="top">
                    @php
                        $logoFilename = $general_setting->getRawOriginal('logo');
                        $logoPath = public_path('assets/general-settings-image/' . $logoFilename);
                    @endphp
                    @if($logoFilename && file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="70">
                    @endif
                </td>
            </tr>
        </table>

        {{-- Vendor & PO Info --}}
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="48%" class="vendor-box" valign="top" style="border: 1px solid #dee2e6; padding: 10px;">
                    <h4 class="text-primary" style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:5px;"><b>VENDOR DETAILS</b></h4>
                    <table width="100%">
                        <tr>
                            <td width="30%"><b>Name:</b></td>
                            <td><b>{{ $data->vendor->name ?? 'N/A' }}</b></td>
                        </tr>
                        <tr>
                            <td><b>Email:</b></td>
                            <td><b>{{ $data->vendor->email ?? 'N/A' }}</b></td>
                        </tr>
                        <tr>
                            <td><b>Phone:</b></td>
                            <td><b>{{ $data->vendor->phone ?? 'N/A' }}</b></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Address:</b></td>
                            <td><b>{{ $data->vendor->address ?? 'N/A' }}</b></td>
                        </tr>
                    </table>
                </td>
                <td width="4%"></td>
                <td width="48%" class="po-box" valign="top" style="border: 1px solid #dee2e6; padding: 10px;">
                    <h4 class="text-primary" style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:5px;"><b>PURCHASE ORDER INFO</b></h4>
                    <table width="100%">
                        <tr>
                            <td width="55%" align="right"><b>PO Number:</b></td>
                            <td width="45%"><b>{{ $data->sku }}</b></td>
                        </tr>
                        <tr>
                            <td align="right"><b>PO Date:</b></td>
                            <td><b>{{ getformatDate($data->date) }}</b></td>
                        </tr>
                        <tr>
                            <td align="right"><b>Delivery Date:</b></td>
                            <td><b>{{ getformatDate($data->delivery_date) }}</b></td>
                        </tr>
                        <tr>
                            <td align="right"><b>Transport:</b></td>
                            <td><b>{{ $data->transport ?? 'N/A' }}</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Items --}}
        <h4 class="mt-4 text-primary">Order Items</h4>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Fabric</th>
                    <th width="15%">Meters</th>
                    <th width="15%">Price</th>
                    <th width="20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp

                @foreach($data->items as $index => $item)

                    @php
                        $total = null;

                        if ($item->price > 0) {
                            $total = $item->meter * $item->price;
                            $grandTotal += $total;
                        }
                    @endphp

                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td align="left">
                            <b>{{ $item->fabric->name }}</b>
                            <br>
                            <span style="font-size: 9px; color: #666;">Composition: {{ $item->fabric->fabric_composition->name ?? 'N/A' }}</span>
                        </td>
                        <td>{{ $item->meter }}</td>

                        <td>
                            {{ $item->price > 0 ? getIndianCurrency($item->price) : 'N/A' }}
                        </td>

                        <td>
                            {{ $total !== null ? getIndianCurrency($total) : 'N/A' }}
                        </td>
                    </tr>

                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="4" class="text-right">Grand Total</td>
                    <td>{{ $grandTotal > 0 ? getIndianCurrency($grandTotal) : 'N/A' }}</td>
                </tr>
            </tfoot>
        </table>

        <table width="100%" class="mt-4">
            <tr>
                <td width="48%" valign="top">
                    <h4 class="text-primary mb-1">Delivery Warehouse Address</h4>
                    <div style="background:#f1f1f1; padding:10px; border-radius:6px; font-size: 11px;">
                        {{ $data->fabric_warehouse->address }}
                    </div>
                </td>
                <td width="4%"></td>
                <td width="48%" valign="top">
                    <h4 class="text-primary mb-1">Remark</h4>
                    <div style="background:#f1f1f1; padding:10px; border-radius:6px; font-size: 11px;">
                        {{ $data->remark ?? 'N/A' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <table width="100%" class="footer">
            <tr>
                <td width="50%">
                    <p><b>Authorized Signature</b></p>
                    <div class="signature-line"></div>
                </td>
                <td width="50%" class="text-right">
                    <p class="text-primary" style="font-size: 14px;"><b>Thank you for your business!</b></p>
                </td>
            </tr>
        </table>

        <div style="text-align: center; font-size: 9px; color: #999; margin-top: 30px;">
            Generated on {{ date('j M Y, h:i A') }}
        </div>
    </div>

</body>

</html>
