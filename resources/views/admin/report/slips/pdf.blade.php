<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Report #{{ $slip->id }}</title>
    <style>
        @page { margin: 1cm 1.2cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 8px; margin-bottom: 15px; }
        .header table { width: 100%; border-collapse: collapse; }
        .company-name { font-size: 16px; font-weight: bold; color: #4f46e5; text-transform: uppercase; }
        .doc-title { text-align: right; font-size: 14px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; border: 1px solid #cbd5e1; }
        .meta-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        .lbl { font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase; display: block; }
        .val { font-size: 10px; font-weight: bold; color: #0f172a; }
        .section-title { font-size: 12px; font-weight: bold; color: #1e293b; margin-top: 12px; margin-bottom: 6px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9px; }
        .data-table th { background: #f1f5f9; color: #334155; padding: 5px 6px; text-align: left; border: 1px solid #cbd5e1; font-weight: bold; }
        .data-table td { padding: 4px 6px; border: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 5px; font-size: 8px; font-weight: bold; border-radius: 3px; background: #e2e8f0; }
        .badge-lot { background: #e0e7ff; color: #3730a3; }
        .summary-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 6px 10px; margin-bottom: 12px; border-radius: 4px; font-size: 10px; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="company-name">{{ $general_setting->site_title ?? 'KESHAV MADHAV' }}</div>
                    <div style="font-size: 9px; color: #64748b;">Comprehensive Production Slip Analysis</div>
                </td>
                <td style="text-align: right;">
                    <div class="doc-title">SLIP REPORT #{{ $slip->id }}</div>
                    <div style="font-size: 9px; color: #64748b;">Date: {{ $slip->created_at->format('d M, Y h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Metadata Grid -->
    <table class="meta-table">
        <tr>
            <td style="width: 25%;">
                <span class="lbl">Slip ID / Bill No</span>
                <span class="val">#{{ $slip->id }} @if($slip->bill_number) (Bill: {{ $slip->bill_number }}) @endif</span>
            </td>
            <td style="width: 25%;">
                <span class="lbl">From Stage & Unit</span>
                <span class="val">{{ $slip->fromStage->name ?? '-' }} ({{ $slip->getUnitMaster->name ?? 'Admin' }})</span>
            </td>
            <td style="width: 25%;">
                <span class="lbl">Digitization Status</span>
                <span class="val">{{ $slip->status == 1 ? 'Digitized' : ($slip->status == 0 ? 'Pending' : 'Skipped') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="lbl">Total Processed</span>
                <span class="val" style="color: #059669;">{{ number_format($summary['total_pieces']) }} pcs</span>
            </td>
        </tr>
    </table>

    <!-- Summary Box -->
    <div class="summary-box">
        <table style="width: 100%;">
            <tr>
                <td style="width: 25%;"><strong>Total Lots:</strong> {{ $summary['total_lots'] }}</td>
                <td style="width: 25%;"><strong>Total Entries:</strong> {{ $summary['total_sessions'] }}</td>
                <td style="width: 25%;"><strong>Total Pieces:</strong> {{ number_format($summary['total_pieces']) }}</td>
                <td style="width: 25%;"><strong>Remaining Balance:</strong> {{ number_format($summary['total_remaining_balance']) }} pcs</td>
            </tr>
        </table>
    </div>

    <!-- 1. Cutting Entries -->
    @if($lots->isNotEmpty())
        <div class="section-title">1. Cutting & Fabric Roll Entries</div>
        @foreach($lots as $lot)
            @php
                $set = $lot->orderProductSet;
                $lotRolls = $rolls->where('lot_no', $lot->lot_no);
            @endphp
            <div style="margin-bottom: 6px;">
                <span class="badge badge-lot">Lot #{{ $lot->lot_no }}</span>
                <strong>Design: {{ $set->design_number ?? '-' }}</strong> &bull;
                <span>SKU: {{ $set->orderMain->sku ?? '-' }}</span> &bull;
                <span>Customer: {{ $set->orderMain->customer->name ?? '-' }}</span>
            </div>
            @if($lotRolls->isNotEmpty())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Warehouse</th>
                            <th>Color</th>
                            <th>Size Breakdown</th>
                            <th class="text-right">Total Pcs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lotRolls as $roll)
                            <tr>
                                <td>Roll #{{ $roll->fabric_roll_id ?? $roll->id }}</td>
                                <td>{{ $roll->stageMasterUnit->masterFabricWarehouse->cutting_master_name ?? '-' }}</td>
                                <td>{{ $set->colors->name ?? '-' }}</td>
                                <td>
                                    @if($roll->fabricRollAssigningsDetail)
                                        @foreach($roll->fabricRollAssigningsDetail as $det)
                                            Size {{ $det->size }}: <strong>{{ $det->quantity }}</strong>, 
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">
                                    {{ number_format($roll->fabricRollAssigningsDetail ? $roll->fabricRollAssigningsDetail->sum('quantity') : 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif

    <!-- 2. Intermediate Stage Entries -->
    @if($stage_transactions->isNotEmpty() || $printings->isNotEmpty())
        <div class="section-title">2. Stage & Production Entries</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Lot No</th>
                    <th>Stage & Unit Destination</th>
                    <th>Design / SKU</th>
                    <th>Size Breakdown</th>
                    <th class="text-right">Assigned Qty</th>
                    <th class="text-right">Remaining</th>
                </tr>
            </thead>
            <tbody>
                @foreach($printings as $pt)
                    @php 
                        $set = $pt->resolved_set ?? ($pt->orderProduct ? $pt->orderProduct->orderProductSet : null); 
                        $orderMain = $pt->resolved_order_main ?? ($set ? $set->orderMain : null);
                    @endphp
                    <tr>
                        <td><strong>#{{ $pt->lot_no }}</strong></td>
                        <td>{{ $pt->from_stage->name ?? '-' }} &rarr; {{ $pt->to_stage->name ?? 'Printing' }} ({{ $pt->getToUnitMaster->name ?? '-' }})</td>
                        <td>{{ $set->design_number ?? '-' }} <small>({{ $orderMain->sku ?? '-' }})</small></td>
                        <td>
                            @if($pt->details)
                                @foreach($pt->details as $d)
                                    S{{ $d->size }}:{{ $d->quantity }} 
                                @endforeach
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ $pt->quantity }}</td>
                        <td class="text-right">{{ $pt->remaining_quantity }}</td>
                    </tr>
                @endforeach

                @foreach($stage_transactions as $st)
                    @php 
                        $set = $st->resolved_set ?? ($st->orderProduct ? $st->orderProduct->orderProductSet : null); 
                        $orderMain = $st->resolved_order_main ?? ($set ? $set->orderMain : null);
                    @endphp
                    <tr>
                        <td><strong>#{{ $st->lot_no }}</strong></td>
                        <td>{{ $st->from_stage->name ?? '-' }} &rarr; {{ $st->to_stage->name ?? '-' }} ({{ $st->getToUnitMaster->name ?? '-' }})</td>
                        <td>{{ $set->design_number ?? '-' }} <small>({{ $orderMain->sku ?? '-' }})</small></td>
                        <td>
                            @if($st->details)
                                @foreach($st->details as $d)
                                    S{{ $d->size }}:{{ $d->quantity }} 
                                @endforeach
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ $st->quantity }}</td>
                        <td class="text-right">{{ $st->remaining_quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
