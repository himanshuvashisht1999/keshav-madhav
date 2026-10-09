<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Summary - {{ $order->sku }}</title>
    <style>
        @page {
            margin: 8mm 8mm 8mm 8mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
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
            font-size: 9px;
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
            font-size: 9px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .info-card-body {
            padding: 5px 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 0;
            font-size: 9px;
            vertical-align: top;
        }

        .info-label {
            width: 34%;
            font-weight: 700;
            color: #074e22;
            text-transform: uppercase;
            font-size: 8px;
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
            font-size: 9px;
        }

        .po-badge {
            display: inline-block;
            background: #edf7e4;
            color: #05421c;
            border: 1px solid #8bc63e;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 800;
            font-size: 9px;
        }

        /* Section Titles */
        .section-title {
            font-size: 10px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 10px 0 4px 0;
        }

        /* Tables */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
            margin-bottom: 8px;
        }

        .items-table th {
            background: #edf7e4;
            color: #05421c;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 4px 5px;
            border-top: 1px solid #05421c;
            border-bottom: 2px solid #05421c;
            border-right: 1px solid #cbd5e1;
            border-left: 1px solid #cbd5e1;
            text-align: center;
        }

        .items-table td {
            padding: 4px 5px;
            font-size: 8.5px;
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
            font-size: 9px;
            border-top: 2px solid #05421c;
            border-bottom: 2px solid #05421c;
            padding: 5px;
        }

        /* Sign-off & Footer */
        .signoff-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .signoff-box {
            border-top: 1px solid #05421c;
            display: inline-block;
            width: 160px;
            text-align: center;
            padding-top: 3px;
            font-size: 8.5px;
            font-weight: bold;
            color: #05421c;
            text-transform: uppercase;
        }

        .print-date {
            text-align: center;
            font-size: 7.5px;
            color: #457855;
            margin-top: 10px;
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    @php
        $general_setting = \App\Models\GeneralSettings::first();
        $logoFilename = $general_setting ? $general_setting->getRawOriginal('logo') : null;
        $logoPath = $logoFilename ? public_path('assets/general-settings-image/' . $logoFilename) : null;
    @endphp

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
                    @if($logoFilename && file_exists($logoPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" height="44" style="object-fit: contain;">
                    @elseif($general_setting && $general_setting->logo && file_exists(public_path(str_replace(url('/'), '', $general_setting->logo))))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path(str_replace(url('/'), '', $general_setting->logo)))) }}" height="44" style="object-fit: contain;">
                    @endif
                </td>
            </tr>
        </table>

        <!-- Order Info & Customer Info Side by Side -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 4px;">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Customer Information</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Customer</td>
                                    <td class="info-val">: {{ $order->customer->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Order Type</td>
                                    <td class="info-val">: {{ ucfirst($order->order_type) }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Mobile</td>
                                    <td class="info-val">: {{ $order->customer->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Address</td>
                                    <td class="info-val">: {{ Str::limit($order->customer->address ?? 'N/A', 55) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">Order Summary Details</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Order SKU</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $order->sku }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">PO Number</td>
                                    <td class="info-val">: <span class="po-badge">{{ $order->po_number ?? '-' }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Order Date</td>
                                    <td class="info-val">: {{ date('d M Y', strtotime($order->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Exp. Delivery</td>
                                    <td class="info-val">: {{ date('d M Y', strtotime($order->expected_delivery_date)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Product Details -->
        <div class="section-title">Product Sets & Design Details</div>
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width: 10%;">BARCODE</th>
                    <th style="width: 11%;">DESIGN NO</th>
                    <th>SET SIZE</th>
                    <th style="width: 9%;">LOT NO'S</th>
                    <th style="width: 9%;">COLOUR</th>
                    <th style="width: 11%;">FABRIC</th>
                    <th style="width: 12%;">CUTTING MASTER</th>
                    <th style="width: 8%;">SET QTY</th>
                    <th style="width: 9%;">TOTAL QTY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderProductSets as $index => $set)
                    <tr class="{{ $index % 2 == 1 ? 'even' : '' }}">
                        <td class="text-center">{{ $set->bar_code ?: '-' }}</td>
                        <td class="text-center" style="font-weight: 800;">{{ $set->design_number ?: '-' }}</td>
                        <td>{{ $set->size_measurement->name ?? '-' }}</td>
                        <td class="text-center">{{ $set->lots->pluck('lot_no')->unique()->implode(', ') ?: '-' }}</td>
                        <td class="text-center">{{ $set->colors->name ?? '-' }}</td>
                        <td>{{ $set->fabric->name ?? '-' }}</td>
                        <td class="text-center">
                            {{ $set->lots->map(function ($lot) {
                                return $lot->stageMasterUnit->name ?? null;
                            })->unique()->filter()->implode(', ') ?: '-' }}
                        </td>
                        <td class="text-right">{{ $set->set_quantity }}</td>
                        <td class="text-right" style="font-weight: 800;">{{ $set->total_quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right" style="text-transform: uppercase;">TOTAL:</td>
                    <td class="text-right">{{ $order->orderProductSets->sum('set_quantity') }}</td>
                    <td class="text-right">{{ $order->orderProductSets->sum('total_quantity') }} Pcs</td>
                </tr>
            </tfoot>
        </table>

        <!-- Lots Details -->
        @if(!empty($lotsData) && count($lotsData) > 0)
            <div class="section-title">Lot Details</div>
            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 6%;">#</th>
                        <th>LOT NO</th>
                        <th>DESIGN NO</th>
                        <th style="width: 15%;">QUANTITY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotsData as $i => $lot)
                        <tr class="{{ $i % 2 == 1 ? 'even' : '' }}">
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="text-center font-weight-bold">{{ $lot['lot_no'] }}</td>
                            <td class="text-center">{{ $lot['design_number'] ?? 'N/A' }}</td>
                            <td class="text-right font-weight-bold">{{ $lot['lot_quantity'] }} Pcs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Packing Details -->
        @if(!empty($cartons) && count($cartons) > 0)
            <div class="section-title">Packing Details</div>
            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 15%;">CARTON NO</th>
                        <th>CONTENTS</th>
                        <th style="width: 12%;">TOTAL ITEMS</th>
                        <th style="width: 12%;">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartons as $cIdx => $carton)
                        <tr class="{{ $cIdx % 2 == 1 ? 'even' : '' }}">
                            <td class="text-center font-weight-bold">{{ $carton->carton_no }}</td>
                            <td>
                                @php
                                    $summary = [];
                                    foreach ($carton->items as $item) {
                                        $name = $item->detail->size ?? $item->size_id;
                                        if (!isset($summary[$name]))
                                            $summary[$name] = 0;
                                        $summary[$name] += $item->quantity;
                                    }
                                    $text = [];
                                    foreach ($summary as $k => $v)
                                        $text[] = "Size: $k (Qty: $v)";
                                @endphp
                                {{ implode(', ', $text) }}
                            </td>
                            <td class="text-center font-weight-bold">{{ $carton->items->sum('quantity') }}</td>
                            <td class="text-center">
                                {{ $carton->status == 2 ? 'Dispatched' : 'Packed' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Dispatch History -->
        @if(!empty($dispatches) && count($dispatches) > 0)
            <div class="section-title">Dispatch History</div>
            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>DISPATCH NO</th>
                        <th>DISPATCH DATE</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dispatches as $dIdx => $dispatch)
                        <tr class="{{ $dIdx % 2 == 1 ? 'even' : '' }}">
                            <td class="text-center font-weight-bold">{{ $dispatch->sku }}</td>
                            <td class="text-center">{{ date('d M Y', strtotime($dispatch->dispatch_date)) }}</td>
                            <td class="text-center">Complete</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Sign-off Block -->
        <table class="signoff-table">
            <tr>
                <td width="50%" align="left" valign="bottom">
                    <span style="font-size: 8px; color: #457855;">Generated on: {{ date('d-m-Y h:i A') }}</span>
                </td>
                <td width="50%" align="right" valign="bottom">
                    <div class="signoff-box">Authorized Signature</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            This is a system generated order summary report from Keshav Madhav ERP.
        </div>
    </div>
</body>
</html>