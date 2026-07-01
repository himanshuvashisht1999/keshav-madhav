<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip #{{ $slip->id }}</title>
    <style>
        @page { margin: 1.2cm 1.5cm; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── Header ── */
        .page-header {
            border-bottom: 2px solid #3da856;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .page-header table { width: 100%; border-collapse: collapse; }
        .company-name { font-size: 17px; font-weight: bold; text-transform: uppercase; color: #3da856; }
        .company-sub  { font-size: 9px; color: #555; margin-top: 2px; }
        .doc-title    { text-align: right; font-size: 14px; font-weight: bold; text-transform: uppercase; color: #3da856; }
        .doc-sub      { text-align: right; font-size: 9px; color: #555; margin-top: 3px; }

        /* ── Top meta strip ── */
        .meta-strip {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #3da856;
            margin-bottom: 18px;
        }
        .meta-strip td {
            padding: 6px 10px;
            border-right: 1px solid #e8f5e9;
        }
        .meta-strip td:last-child { border-right: none; }
        .lbl  { font-size: 8px; text-transform: uppercase; color: #3da856; display: block; margin-bottom: 2px; font-weight: bold; }
        .val  { font-size: 12px; font-weight: bold; color: #2e7d32; }

        /* ── Session block ── */
        .session {
            border: 1.5px solid #3da856;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .session-hdr {
            background: #3da856;
            color: #fff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
        }

        /* ── Size table ── */
        .size-tbl {
            width: 100%;
            border-collapse: collapse;
        }
        .size-tbl th {
            background: #4caf50;
            color: #fff;
            padding: 5px 4px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #3da856;
        }
        .size-tbl td {
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #e8f5e9;
            font-size: 11px;
        }
        .size-tbl td.total {
            font-weight: bold;
            font-size: 13px;
            background: #fff9c4;
            color: #854d0e;
            border: 1px solid #eab308;
        }

        /* ── Signatures ── */
        .sig-tbl { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .sig-tbl td {
            width: 33%;
            text-align: center;
            border-top: 1.5px solid #3da856;
            padding-top: 6px;
            font-size: 10px;
            color: #2e7d32;
            font-weight: bold;
        }

        /* ── Footer ── */
        .footer {
            position: fixed;
            bottom: 8px;
            left: 0; right: 0;
            text-align: center;
            font-size: 8px;
            color: #aaa;
        }

        .copy-label {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #2e7d32;
            background: #e8f5e9;
            border: 1.5px dashed #3da856;
            padding: 4px 0;
            margin-bottom: 10px;
        }

        .no-data { padding: 8px 10px; font-size: 10px; color: #777; }
    </style>
</head>
<body>

@foreach(['FROM STAGE COPY', 'TO STAGE COPY'] as $copyTitle)
<div style="{{ !$loop->first ? 'page-break-before: always;' : '' }}">

    <div class="copy-label">{{ $copyTitle }}</div>

    {{-- HEADER --}}
    <div class="page-header">
        <table>
            <tr>
                <td>
                    @if($general_setting && $general_setting->logo)
                        @php
                            $lp = public_path('assets/general-settings-image/' . basename($general_setting->logo));
                            $lb64 = file_exists($lp) ? 'data:image/'.pathinfo($lp,PATHINFO_EXTENSION).';base64,'.base64_encode(file_get_contents($lp)) : null;
                        @endphp
                        @if($lb64)<img src="{{ $lb64 }}" style="max-height:36px;vertical-align:middle;margin-right:8px;">@endif
                    @endif
                    <span class="company-name">{{ $general_setting->website_name ?? 'SNAPKID' }}</span>
                    <div class="company-sub">{{ $general_setting->address ?? '' }}</div>
                </td>
                <td style="vertical-align:bottom;">
                    <div class="doc-title">Production Slip</div>
                    <div class="doc-sub">{{ now()->format('d M Y, h:i A') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TOP META --}}
    @php
        $fromDisplay = $slip->fromStage ? $slip->fromStage->name : '-';
        if ($slip->getUnitMaster) {
            $fromDisplay .= ' (' . $slip->getUnitMaster->name . ')';
        }

        $toDestinations = collect();
        foreach($printings as $p) {
            $stageName = $p->to_stage ? $p->to_stage->name : null;
            $unitName = $p->getToUnitMaster ? $p->getToUnitMaster->name : null;
            if ($stageName) {
                $toDestinations->push($stageName . ($unitName ? ' (' . $unitName . ')' : ''));
            }
        }
        foreach($stage_transactions as $st) {
            $stageName = $st->to_stage ? $st->to_stage->name : null;
            $unitName = $st->getToUnitMaster ? $st->getToUnitMaster->name : null;
            if ($stageName) {
                $toDestinations->push($stageName . ($unitName ? ' (' . $unitName . ')' : ''));
            }
        }
        $toDisplay = $toDestinations->unique()->filter()->implode(' / ') ?: '-';
    @endphp
    <table class="meta-strip">
        <tr>
            <td width="18%"><span class="lbl">SLIP NO.</span><span class="val">#{{ $slip->id }}</span></td>
            <td width="22%"><span class="lbl">DATE</span><span class="val">{{ $slip->created_at->format('d M Y') }}</span></td>
            <td width="30%"><span class="lbl">FROM</span><span class="val">{{ $fromDisplay }}</span></td>
            <td width="30%"><span class="lbl">TO</span><span class="val">{{ $toDisplay }}</span></td>
        </tr>
    </table>

    {{-- ══ TYPE 1 → CUTTING / LOTS ══ --}}
    @if(count($lots) > 0)
        @foreach($lots as $idx => $lot)
            @php
                $sizeQtys = [];
                foreach($rolls->where('order_lot_id', $lot->id) as $roll)
                    foreach($roll->fabricRollAssigningsDetail as $d)
                        $sizeQtys[$d->size] = ($sizeQtys[$d->size] ?? 0) + $d->quantity;
                ksort($sizeQtys);
                $total = array_sum($sizeQtys);

                $ops = $lot->orderProductSet;
                $sizeMeasurementName = $ops && $ops->size_measurement ? $ops->size_measurement->name : '-';
                $colorName = $ops && $ops->colors ? $ops->colors->name : '-';
                $designNumber = $ops ? $ops->design_number : '-';
                $fabricName = $ops && $ops->fabric ? $ops->fabric->name : '-';
            @endphp
            <div class="session">
                <table style="width: 100%; border-collapse: collapse; background: #3da856; color: #fff;">
                    <tr>
                        <td style="padding: 5px 10px; font-size: 11px; font-weight: bold; text-align: left;">
                            LOT NO: {{ $lot->lot_no }}
                        </td>
                        <td style="padding: 5px 10px; font-size: 10px; text-align: right; color: #e8f5e9;">
                            DESIGN: <strong style="color: #fffb73;">{{ $designNumber }}</strong> &nbsp;|&nbsp;
                            SIZE SET: <strong style="color: #fffb73;">{{ $sizeMeasurementName }}</strong> &nbsp;|&nbsp;
                            COLOR: <strong style="color: #fffb73;">{{ $colorName }}</strong> &nbsp;|&nbsp;
                            FABRIC: <strong style="color: #fffb73;">{{ $fabricName }}</strong>
                        </td>
                    </tr>
                </table>
                @if(count($sizeQtys) > 0)
                    <table class="size-tbl">
                        <thead><tr>
                            @foreach($sizeQtys as $sz => $q)<th>SIZE {{ $sz }}</th>@endforeach
                            <th style="background: #eab308; color: #000; border: 1px solid #3da856;">TOTAL QTY</th>
                        </tr></thead>
                        <tbody><tr>
                            @foreach($sizeQtys as $sz => $q)<td>{{ $q }}</td>@endforeach
                            <td class="total">{{ $total }}</td>
                        </tr></tbody>
                    </table>
                @else
                    <div class="no-data">No size data.</div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ TYPE 2 → PRINTING ══ --}}
    @if($printings->isNotEmpty())
        @foreach($printings as $idx => $printing)
            @php
                $sizeQtys = [];
                foreach($printing->details as $d)
                    $sizeQtys[$d->size] = ($sizeQtys[$d->size] ?? 0) + $d->quantity;
                ksort($sizeQtys);
                $total = array_sum($sizeQtys) ?: $printing->quantity;

                $ops = $printing->orderProduct && $printing->orderProduct->orderProductSet ? $printing->orderProduct->orderProductSet : null;
                if (!$ops && $printing->lot_no) {
                    $lotData = \App\Models\OrderLot::where('lot_no', $printing->lot_no)->with([
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.size_measurement'
                    ])->first();
                    if ($lotData) {
                        $ops = $lotData->orderProductSet;
                    }
                }
                $sizeMeasurementName = $ops && $ops->size_measurement ? $ops->size_measurement->name : '-';
                $colorName = $ops && $ops->colors ? $ops->colors->name : '-';
                $designNumber = $ops ? $ops->design_number : '-';
                $fabricName = $ops && $ops->fabric ? $ops->fabric->name : '-';
            @endphp
            <div class="session">
                <table style="width: 100%; border-collapse: collapse; background: #3da856; color: #fff;">
                    <tr>
                        <td style="padding: 5px 10px; font-size: 11px; font-weight: bold; text-align: left;">
                            LOT NO: {{ $printing->lot_no }}
                        </td>
                        <td style="padding: 5px 10px; font-size: 10px; text-align: right; color: #e8f5e9;">
                            DESIGN: <strong style="color: #fffb73;">{{ $designNumber }}</strong> &nbsp;|&nbsp;
                            SIZE SET: <strong style="color: #fffb73;">{{ $sizeMeasurementName }}</strong> &nbsp;|&nbsp;
                            COLOR: <strong style="color: #fffb73;">{{ $colorName }}</strong> &nbsp;|&nbsp;
                            FABRIC: <strong style="color: #fffb73;">{{ $fabricName }}</strong>
                        </td>
                    </tr>
                </table>
                @if(count($sizeQtys) > 0)
                    <table class="size-tbl">
                        <thead><tr>
                            @foreach($sizeQtys as $sz => $q)<th>SIZE {{ $sz }}</th>@endforeach
                            <th style="background: #eab308; color: #000; border: 1px solid #3da856;">TOTAL QTY</th>
                        </tr></thead>
                        <tbody><tr>
                            @foreach($sizeQtys as $sz => $q)<td>{{ $q }}</td>@endforeach
                            <td class="total">{{ $total }}</td>
                        </tr></tbody>
                    </table>
                @else
                    <div class="no-data">Total Qty: <strong>{{ $printing->quantity }}</strong></div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- ══ TYPE 3 → STITCHING / TRANSFERS ══ --}}
    @if($stage_transactions->isNotEmpty())
        @foreach($stage_transactions as $idx => $transaction)
            @php
                $sizeQtys = [];
                foreach($transaction->details as $d)
                    $sizeQtys[$d->size] = ($sizeQtys[$d->size] ?? 0) + $d->quantity;
                ksort($sizeQtys);
                $total = array_sum($sizeQtys) ?: $transaction->quantity;

                $ops = $transaction->orderProduct && $transaction->orderProduct->orderProductSet ? $transaction->orderProduct->orderProductSet : null;
                if (!$ops && $transaction->lot_no) {
                    $lotData = \App\Models\OrderLot::where('lot_no', $transaction->lot_no)->with([
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.size_measurement'
                    ])->first();
                    if ($lotData) {
                        $ops = $lotData->orderProductSet;
                    }
                }
                $sizeMeasurementName = $ops && $ops->size_measurement ? $ops->size_measurement->name : '-';
                $colorName = $ops && $ops->colors ? $ops->colors->name : '-';
                $designNumber = $ops ? $ops->design_number : '-';
                $fabricName = $ops && $ops->fabric ? $ops->fabric->name : '-';
            @endphp
            <div class="session">
                <table style="width: 100%; border-collapse: collapse; background: #3da856; color: #fff;">
                    <tr>
                        <td style="padding: 5px 10px; font-size: 11px; font-weight: bold; text-align: left;">
                            LOT NO: {{ $transaction->lot_no }}
                        </td>
                        <td style="padding: 5px 10px; font-size: 10px; text-align: right; color: #e8f5e9;">
                            DESIGN: <strong style="color: #fffb73;">{{ $designNumber }}</strong> &nbsp;|&nbsp;
                            SIZE SET: <strong style="color: #fffb73;">{{ $sizeMeasurementName }}</strong> &nbsp;|&nbsp;
                            COLOR: <strong style="color: #fffb73;">{{ $colorName }}</strong> &nbsp;|&nbsp;
                            FABRIC: <strong style="color: #fffb73;">{{ $fabricName }}</strong>
                        </td>
                    </tr>
                </table>
                @if(count($sizeQtys) > 0)
                    <table class="size-tbl">
                        <thead><tr>
                            @foreach($sizeQtys as $sz => $q)<th>SIZE {{ $sz }}</th>@endforeach
                            <th style="background: #eab308; color: #000; border: 1px solid #3da856;">TOTAL QTY</th>
                        </tr></thead>
                        <tbody><tr>
                            @foreach($sizeQtys as $sz => $q)<td>{{ $q }}</td>@endforeach
                            <td class="total">{{ $total }}</td>
                        </tr></tbody>
                    </table>
                @else
                    <div class="no-data">Total Qty: <strong>{{ $transaction->quantity }}</strong></div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- SIGNATURES --}}
    <table class="sig-tbl">
        <tr>
            <td>Issued By</td>
            <td>Checked By</td>
            <td>Received By</td>
        </tr>
    </table>

</div>
@endforeach

<div class="footer">Slip #{{ $slip->id }} &nbsp;·&nbsp; {{ now()->format('d M Y, h:i A') }}</div>

</body>
</html>