<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Production Slip #{{ $slip->id }}</title>

    <style>
        @page {
            margin: 0.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }

        /* Header Styling */
        .header {
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 60px;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 60px;
            height: auto;
        }

        .header-company {
            padding-left: 15px;
            vertical-align: middle;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            margin: 0;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            margin-top: 2px;
        }

        .header-title {
            text-align: right;
            vertical-align: bottom;
        }

        .header-title h1 {
            font-size: 18px;
            margin: 0;
            color: #444;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Section Styling */
        .section-title {
            background-color: #f4f4f4;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            border-left: 4px solid #444;
            margin: 15px 0 10px 0;
        }

        /* Info Grid */
        .info-grid {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-grid td {
            padding: 4px 0;
            vertical-align: top;
        }

        .label {
            color: #777;
            font-size: 9px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 1px;
        }

        .value {
            font-weight: bold;
            font-size: 11px;
            color: #000;
        }

        /* Table Styling */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table.data-table th {
            background-color: #444;
            color: #fff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
        }

        table.data-table td {
            border-bottom: 1px solid #ddd;
            padding: 6px 8px;
        }

        /* Box / Card Styling */
        .card {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .card-header {
            background-color: #f9f9f9;
            padding: 5px 10px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 11px;
        }

        .card-body {
            padding: 10px;
        }

        /* Badge Styling */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background-color: #eee;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #555;
            border: 1px solid #ccc;
        }

        /* Size/Qty Tables Short Styling */
        .size-box {
            display: inline-block;
            border: 1px solid #333;
            margin-right: 5px;
            margin-bottom: 5px;
            min-width: 60px;
        }

        .size-box .s-label {
            background: #333;
            color: #fff;
            font-size: 8px;
            padding: 2px;
            text-align: center;
        }

        .size-box .s-value {
            font-weight: bold;
            font-size: 11px;
            padding: 3px;
            text-align: center;
        }

        /* Signature Section */
        .signatures {
            margin-top: 40px;
            width: 100%;
        }

        .sig-box {
            width: 30%;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>

<body>

    @foreach(['From Stage Unit Person', 'To Stage Unit Person'] as $copyTitle)
    <div class="container" style="{{ !$loop->first ? 'page-break-before: always;' : '' }}">
        <div style="text-align: center; font-weight: bold; border: 1px solid #ddd; padding: 5px; margin-bottom: 10px; background: #f4f4f4;">
            {{ $copyTitle }} COPY
        </div>
        {{-- ================= HEADER ================= --}}
        <header class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if($general_setting && $general_setting->logo)
                            @php
                                $logoPath = public_path('assets/general-settings-image/' . basename($general_setting->logo));
                                if (file_exists($logoPath)) {
                                    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                                    $data = file_get_contents($logoPath);
                                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                } else {
                                    $logoBase64 = null;
                                }
                            @endphp
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Logo">
                            @endif
                        @endif
                    </td>
                    <td class="header-company">
                        <div class="company-name">{{ $general_setting->website_name ?? 'SNAPKID' }}</div>
                        <div class="company-info">
                            {{ $general_setting->address ?? '' }}<br>
                            Ph: {{ $general_setting->phone ?? '' }} | Email: {{ $general_setting->email ?? '' }}
                        </div>
                    </td>
                    <td class="header-title">
                        <h1>Production Slip</h1>
                        <div class="value" style="color: #666;">#{{ $slip->id }}</div>
                    </td>
                </tr>
            </table>
        </header>

        @php
            $all_sizes = [];
            foreach($rolls as $r) { foreach($r->fabricRollAssigningsDetail as $sd) { $all_sizes[] = $sd->size; } }
            foreach($printings as $p) { foreach($p->details as $rs) { $all_sizes[] = $rs->size; } }
            foreach($stage_transactions as $st) { foreach($st->details as $rs) { $all_sizes[] = $rs->size; } }
            $all_sizes = array_unique(array_filter($all_sizes));
            if (count($all_sizes) > 0) {
                natsort($all_sizes);
                $all_sizes = array_values($all_sizes);
                $actual_range = $all_sizes[0] . '-' . $all_sizes[count($all_sizes)-1];
            } else {
                $actual_range = '-';
            }
        @endphp

        {{-- ================= SLIP SUMMARY ================= --}}
        <table class="info-grid" style="background:#f9f9f9; border: 1px solid #ddd; margin-bottom: 20px;">
            <tr>
                <td width="20%" style="padding:10px; border-right: 1px solid #eee;">
                    <span class="label">Slip ID</span>
                    <span class="value" style="font-size: 14px; color: #6366f1;">#{{ $slip->id }}</span>
                </td>
                <td width="20%" style="padding:10px; border-right: 1px solid #eee;">
                    <span class="label">From Stage</span>
                    <span class="value">{{ $slip->fromStage?->name ?? '-' }}</span>
                </td>
                <td width="20%" style="padding:10px; border-right: 1px solid #eee;">
                    <span class="label">Unit</span>
                    <span class="value">{{ $slip->getUnitMaster?->name ?? '-' }}</span>
                </td>
                <td width="20%" style="padding:10px; border-right: 1px solid #eee; text-align: center;">
                    <span class="label">Slip Range</span>
                    <span class="value" style="color: #3b82f6; font-size: 14px;">{{ $actual_range }}</span>
                </td>
                <td width="20%" style="padding:10px; text-align: center;">
                    <span class="label">Total Pieces</span>
                    <span class="value" style="font-size: 14px;">{{ $pcs_in_set }}</span>
                </td>
            </tr>
        </table>

        @php
            $all_sizes = [];
            foreach($rolls as $r) { foreach($r->fabricRollAssigningsDetail as $sd) { $all_sizes[] = $sd->size; } }
            foreach($printings as $p) { foreach($p->details as $rs) { $all_sizes[] = $rs->size; } }
            foreach($stage_transactions as $st) { foreach($st->details as $rs) { $all_sizes[] = $rs->size; } }
            $all_sizes = array_unique(array_filter($all_sizes));
            if (count($all_sizes) > 0) {
                natsort($all_sizes);
                $all_sizes = array_values($all_sizes);
                $actual_range = $all_sizes[0] . '-' . $all_sizes[count($all_sizes)-1];
            } else {
                $actual_range = '-';
            }
        @endphp

        {{-- =====================================================
        🟢 TYPE 1 → LOT / ROLLS
        ===================================================== --}}
        @if($slip->save_type == 1 && count($lots) > 0)
            @foreach($lots as $index => $lot)
                <div style="page-break-inside: avoid; border: 1.5px solid #10b981; margin-bottom: 25px;">
                    <div style="background: #ecfdf5; padding: 10px; border-bottom: 1px solid #10b981;">
                        <table width="100%">
                            <tr>
                                <td style="font-weight: bold; font-size: 14px;">Session #{{ $index + 1 }} - Cutting</td>
                                <td style="text-align: right; font-weight: bold; color: #059669;">LOT #{{ $lot->lot_no }}</td>
                            </tr>
                        </table>
                    </div>

                    @php 
                        $ops = $lot->orderProductSet ?? $orderProductSet; 
                    @endphp

                    @if($ops)
                    <div style="padding: 10px; background: #f9fafb; border-bottom: 1px solid #eee;">
                        <table width="100%" class="info-grid">
                            <tr>
                                <td width="25%"><span class="label">Order No</span><span class="value">{{ $ops->orderMain?->sku ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Design No</span><span class="value">{{ $ops->design_number ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Fabric</span><span class="value">{{ $ops->fabric?->name ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Color</span><span class="value">{{ $ops->colors?->name ?? '-' }}</span></td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    
                    @php
                        $currentRolls = $rolls->where('order_lot_id', $lot->id);
                    @endphp

                    <table class="info-grid">
                        <tr>
                            <td width="25%">
                                <span class="label">Lot Number</span>
                                <span class="value">#{{ $lot->lot_no }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">Design Number</span>
                                <span class="value">{{ $lot->orderProductSet?->design_number ?? '-' }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">Order reference</span>
                                <span class="value">{{ $lot->orderMain?->sku ?? '-' }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">Production Date</span>
                                <span class="value">{{ getformatDateTime($lot->production_datetime) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="label">Fabric</span>
                                <span class="value">{{ $lot->orderProductSet?->fabric?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="label">Color</span>
                                <span class="value">{{ $lot->orderProductSet?->colors?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="label">Pattern</span>
                                <span class="value">{{ $lot->orderProductSet?->master_design_pattern?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="label">Fitting</span>
                                <span class="value">{{ $lot->orderProductSet?->master_product_fitting?->name ?? '-' }}</span>
                            </td>
                        </tr>
                    </table>

                    <div style="font-weight:bold; margin-top:10px; border-bottom:1px solid #ddd;">Rolls</div>
                    <table width="100%" style="border-collapse: collapse; margin-top: 5px; margin-bottom: 10px;">
                        <thead>
                            <tr style="background: #f2f2f2;">
                                <th style="border: 1px solid #ccc; padding: 4px; text-align: left; font-size: 9px;">Roll ID</th>
                                <th style="border: 1px solid #ccc; padding: 4px; text-align: left; font-size: 9px;">Usage (Meters)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currentRolls as $roll)
                                <tr>
                                    <td style="border: 1px solid #ccc; padding: 4px; font-weight: bold;">{{ $roll->roll_no }}</td>
                                    <td style="border: 1px solid #ccc; padding: 4px;">{{ $roll->meter }} m</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @php
                        $consolidated = [];
                        foreach($currentRolls as $r) {
                            foreach($r->fabricRollAssigningsDetail as $sd) {
                                $consolidated[$sd->size] = ($consolidated[$sd->size] ?? 0) + $sd->quantity;
                            }
                        }
                    @endphp

                    @if(count($consolidated) > 0)
                        <div style="margin-top: 5px; border: 1.5px solid #000; padding: 5px;">
                            <table width="100%" style="border-collapse: collapse;">
                                <tr>
                                    @foreach($consolidated as $sz => $qty)
                                        <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">
                                            <div style="font-size: 8px; color: #666; text-transform: uppercase;">SIZE {{ $sz }}</div>
                                            <div style="font-size: 11px; font-weight: bold;">{{ $qty }}</div>
                                        </td>
                                    @endforeach
                                    <td style="border: 1.5px solid #000; padding: 5px; text-align: center; background: #eee;">
                                        <div style="font-size: 8px; font-weight: bold;">TOTAL</div>
                                        <div style="font-size: 12px; font-weight: 900;">{{ array_sum($consolidated) }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
                <div style="margin-bottom: 20px;"></div>
            @endforeach
        @endif

        {{-- =====================================================
        🔵 TYPE 2 → PRINTING
        ===================================================== --}}
        @if($slip->save_type == 2 && $printings->isNotEmpty())
            @foreach($printings as $index => $printing)
                <div style="page-break-inside: avoid; border: 1.5px solid #3b82f6; margin-bottom: 25px;">
                    <div style="background: #eff6ff; padding: 10px; border-bottom: 1px solid #3b82f6;">
                        <table width="100%">
                            <tr>
                                <td style="font-weight: bold; font-size: 14px;">Session #{{ $index + 1 }} - Printing</td>
                                <td style="text-align: right; font-weight: bold; color: #2563eb;">LOT #{{ $printing->lot_no }}</td>
                            </tr>
                        </table>
                    </div>

                    @php 
                        $ops = $printing->orderProduct?->orderProductSet ?? $orderProductSet; 
                    @endphp

                    @if($ops)
                    <div style="padding: 10px; background: #f9fafb; border-bottom: 1px solid #eee;">
                        <table width="100%" class="info-grid">
                            <tr>
                                <td width="25%"><span class="label">Order No</span><span class="value">{{ $ops->orderMain?->sku ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Design No</span><span class="value">{{ $ops->design_number ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Fabric</span><span class="value">{{ $ops->fabric?->name ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Color</span><span class="value">{{ $ops->colors?->name ?? '-' }}</span></td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    
                    {{-- Simplified Size Set Calculation --}}


                    <table class="info-grid">
                        <tr>
                            <td width="25%">
                                <span class="label">Lot No</span>
                                <span class="value">#{{ $printing->lot_no }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">Total Quantity</span>
                                <span class="value">{{ $printing->quantity }} Pcs</span>
                            </td>
                            <td width="25%">
                                <span class="label">From Stage</span>
                                <span class="value">{{ $printing->from_stage?->name ?? '-' }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">To Stage</span>
                                <span class="value">{{ $printing->to_stage?->name ?? '-' }}</span>
                            </td>
                        </tr>
                    </table>

                    @php
                        $consolidated = [];
                        foreach($printing->details as $rs) {
                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                        }
                    @endphp

                    @if(count($consolidated) > 0)
                        <div style="margin-top: 10px; border: 1.5px solid #000; padding: 5px;">
                            <table width="100%" style="border-collapse: collapse;">
                                <tr>
                                    @foreach($consolidated as $sz => $qty)
                                        <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">
                                            <div style="font-size: 8px; color: #666; text-transform: uppercase;">SIZE {{ $sz }}</div>
                                            <div style="font-size: 11px; font-weight: bold;">{{ $qty }}</div>
                                        </td>
                                    @endforeach
                                    <td style="border: 1.5px solid #000; padding: 5px; text-align: center; background: #eee;">
                                        <div style="font-size: 8px; font-weight: bold;">TOTAL</div>
                                        <div style="font-size: 12px; font-weight: 900;">{{ array_sum($consolidated) }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
                <div style="margin-bottom: 20px;"></div>
            @endforeach
        @endif

        {{-- =====================================================
        🟠 TYPE 3 → OTHER
        ===================================================== --}}
        @if($slip->save_type == 3 && $stage_transactions->isNotEmpty())
            @foreach($stage_transactions as $index => $transaction)
                <div style="page-break-inside: avoid; border: 1.5px solid #f59e0b; margin-bottom: 25px;">
                    <div style="background: #fffbeb; padding: 10px; border-bottom: 1px solid #f59e0b;">
                        <table width="100%">
                            <tr>
                                <td style="font-weight: bold; font-size: 14px;">Session #{{ $index + 1 }} - Transfer</td>
                                <td style="text-align: right; font-weight: bold; color: #d97706;">LOT #{{ $transaction->lot_no }}</td>
                            </tr>
                        </table>
                    </div>

                    @php 
                        $ops = $transaction->orderProduct?->orderProductSet ?? $orderProductSet; 
                    @endphp

                    @if($ops)
                    <div style="padding: 10px; background: #f9fafb; border-bottom: 1px solid #eee;">
                        <table width="100%" class="info-grid">
                            <tr>
                                <td width="25%"><span class="label">Order No</span><span class="value">{{ $ops->orderMain?->sku ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Design No</span><span class="value">{{ $ops->design_number ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Fabric</span><span class="value">{{ $ops->fabric?->name ?? '-' }}</span></td>
                                <td width="25%"><span class="label">Color</span><span class="value">{{ $ops->colors?->name ?? '-' }}</span></td>
                            </tr>
                        </table>
                    </div>
                    @endif
                    


                    <table class="info-grid">
                        <tr>
                            <td width="25%">
                                <span class="label">Lot No</span>
                                <span class="value">#{{ $transaction->lot_no }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">Quantity</span>
                                <span class="value">{{ $transaction->quantity }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">From Stage</span>
                                <span class="value">{{ $transaction->from_stage?->name ?? '-' }}</span>
                            </td>
                            <td width="25%">
                                <span class="label">To Stage</span>
                                <span class="value">{{ $transaction->to_stage?->name ?? '-' }}</span>
                            </td>
                        </tr>
                    </table>

                    @php
                        $consolidated = [];
                        foreach($transaction->details as $rs) {
                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                        }
                    @endphp

                    @if(count($consolidated) > 0)
                        <div style="margin-top: 10px; border: 1.5px solid #000; padding: 5px;">
                            <table width="100%" style="border-collapse: collapse;">
                                <tr>
                                    @foreach($consolidated as $sz => $qty)
                                        <td style="border: 1px solid #ccc; padding: 5px; text-align: center;">
                                            <div style="font-size: 8px; color: #666; text-transform: uppercase;">SIZE {{ $sz }}</div>
                                            <div style="font-size: 11px; font-weight: bold;">{{ $qty }}</div>
                                        </td>
                                    @endforeach
                                    <td style="border: 1.5px solid #000; padding: 5px; text-align: center; background: #eee;">
                                        <div style="font-size: 8px; font-weight: bold;">TOTAL</div>
                                        <div style="font-size: 12px; font-weight: 900;">{{ array_sum($consolidated) }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif
                </div>
                <div style="margin-bottom: 20px;"></div>
            @endforeach
        @endif

        {{-- ================= MOVEMENT & LOSSES ================= --}}
        @if($outflows->isNotEmpty() || $reworks->isNotEmpty())
            <div style="page-break-inside: avoid; margin-bottom: 20px;">
                <div class="section-title">Unit Movement & Losses Summary</div>
                <table class="data-table">
                    <thead>
                        <tr style="background: #f4f4f4;">
                            <th width="15%">Type</th>
                            <th width="20%">Item / Size</th>
                            <th width="10%" style="text-align: center;">Qty</th>
                            <th width="25%">Destination / Account</th>
                            <th width="20%">Remarks</th>
                            <th width="10%">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outflows as $o)
                            <tr>
                                <td><span class="badge">{{ strtoupper($o->type) }}</span></td>
                                <td>
                                    <strong>{{ $o->product->design_number ?? 'N/A' }}</strong><br>
                                    Color: {{ $o->color->name ?? 'N/A' }} | Size: {{ $o->size->size ?? 'N/A' }}
                                </td>
                                <td align="center"><strong>{{ $o->quantity }}</strong></td>
                                <td>
                                    @if($o->type == 'debit')
                                        {{ $o->responsibleStage->name ?? '' }} → {{ $o->responsibleUnit->name ?? 'N/A' }}<br>
                                        <small>Amount: Rs. {{ number_format($o->total_amount, 2) }}</small>
                                    @else
                                        Storage: {{ $o->rack->storeroom->name ?? 'N/A' }}<br>
                                        <small>Location: {{ $o->responsibleUnit->name ?? 'Main' }}</small>
                                    @endif
                                </td>
                                <td>{{ $o->remarks ?: '-' }}</td>
                                <td>{{ $o->created_at->format('d/m/y') }}</td>
                            </tr>
                        @endforeach

                        @foreach($reworks as $r)
                            @foreach($r->details as $rd)
                                <tr style="background-color: #fcfcfc;">
                                    <td><span class="badge" style="border-color: #3b82f6; color: #3b82f6;">REWORK</span></td>
                                    <td>
                                        <strong>Defect Rework</strong><br>
                                        Size: {{ $rd->size }}
                                    </td>
                                    <td align="center"><strong>{{ $rd->quantity }}</strong></td>
                                    <td>{{ $r->toStage->name ?? 'N/A' }} → {{ $r->toUnit->name ?? 'N/A' }}</td>
                                    <td>{{ $r->remarks ?: 'Repairing' }}</td>
                                    <td>{{ $r->created_at->format('d/m/y') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ================= PACKING DETAILS ================= --}}
        @if($packing_details->isNotEmpty())
            @foreach($packing_details as $index => $packing)
                <div style="page-break-inside: avoid;">
                    <div class="section-title">Packing Session #{{ $index + 1 }}</div>
                    @foreach($packing->cartons as $carton)
                        <div class="card" style="margin-bottom: 10px;">
                            <div class="card-header" style="background: #fcfcfc;">
                                <table width="100%">
                                    <tr>
                                        <td><strong>Carton #{{ $carton->carton_no }}</strong></td>
                                        <td align="right"><strong>{{ $carton->boxes->count() }} Boxes</strong></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-body">
                                @php
                                    $boxed_summary = []; $loose_summary = [];
                                    $total_boxes = $carton->boxes->count();
                                    foreach ($carton->items as $item) {
                                        $name = $item->detail ? $item->detail->size : ($item->size ? $item->size->name : 'ID:' . $item->size_id);
                                        if($item->packing_box_id) {
                                            $boxed_summary[$name] = ($boxed_summary[$name] ?? 0) + $item->quantity;
                                        } else {
                                            $loose_summary[$name] = ($loose_summary[$name] ?? 0) + $item->quantity;
                                        }
                                    }
                                @endphp

                                <!-- 1. Boxed Items Summary -->
                                @if(count($boxed_summary) > 0 && $total_boxes > 0)
                                    <div style="font-size: 9px; margin-bottom: 5px; color: #666; font-weight: bold; text-transform: uppercase;">
                                            Packing Summary (Per Box)
                                    </div>
                                    @foreach($boxed_summary as $name => $total_qty)
                                        @php $per_box = $total_qty / $total_boxes; @endphp
                                        <div class="size-box">
                                            <div class="s-label">SIZE {{ $name }}</div>
                                            <div class="s-value">{{ number_format($per_box, 0) }}</div>
                                        </div>
                                    @endforeach
                                @endif

                                <!-- 2. Loose Items Summary -->
                                @if(count($loose_summary) > 0)
                                    <div style="font-size: 9px; margin-top: 10px; margin-bottom: 5px; color: #d97706; font-weight: bold; text-transform: uppercase;">
                                        Loose Packing Detail
                                    </div>
                                    <table width="100%" style="border-collapse: collapse;">
                                        <tr>
                                            @foreach($loose_summary as $name => $qty)
                                                <td style="border: 1px solid #ddd; padding: 3px; font-size: 9px;">
                                                    <span style="color: #666;">Size {{ $name }}:</span> <strong>{{ $qty }}</strong>
                                                </td>
                                            @endforeach
                                        </tr>
                                    </table>
                                @endif

                                <div style="margin-top: 8px; border-top: 1px dashed #ddd; padding-top: 5px; text-align: right; font-size: 10px;">
                                    Total pieces in carton: <strong>{{ array_sum($boxed_summary) + array_sum($loose_summary) }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

        {{-- ================= SIGNATURES ================= --}}
        <table class="signatures">
            <tr>
                <td class="sig-box">Issued By</td>
                <td width="5%"></td>
                <td class="sig-box">Checked By</td>
                <td width="5%"></td>
                <td class="sig-box">Received By</td>
            </tr>
        </table>

        {{-- ================= ATTACHED IMAGE ================= --}}
        @if($slip->slip_file)
            <div style="page-break-before: always;"></div>
            <div class="section-title">Attached Work Slip Document</div>
            <div style="text-align: center; border: 1px dashed #ccc; padding: 10px;">
                @php
                    $imagePath = public_path('assets/production_slips/' . $slip->slip_file);
                    if (file_exists($imagePath)) {
                        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                        $data = file_get_contents($imagePath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    } else {
                        $base64 = null;
                    }
                @endphp
                @if($base64)
                    <img src="{{ $base64 }}" style="max-width: 100%; height: auto; border: 1px solid #ddd;">
                @else
                    <div style="padding: 50px; color: #999;">Original attachment scan was not found or could not be loaded.</div>
                @endif
            </div>
        @endif

        <footer class="footer">
            Printed on {{ now()->format('d M Y, h:i A') }} | Production Management System
        </footer>
    </div>
    @endforeach

</body>

</html>