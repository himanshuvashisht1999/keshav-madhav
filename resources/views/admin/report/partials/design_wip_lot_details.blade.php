@php use Carbon\Carbon; @endphp
<style>
    /* Dense ERP Side Panel Layout */
    .wip-widget {
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif;
        color: #111827;
        font-size: 13px;
    }
    .wip-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 16px;
    }
    .wip-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        padding: 8px 10px;
    }
    .wip-box label {
        display: block;
        font-size: 10px;
        color: #6b7280;
        font-weight: 700;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wip-box div {
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }

    .wip-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #374151;
        margin-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 4px;
    }

    .wip-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
    }
    .wip-table th {
        text-align: left;
        background: #f3f4f6;
        padding: 6px 8px;
        font-size: 11px;
        color: #4b5563;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
    }
    .wip-table td {
        padding: 6px 8px;
        font-size: 12px;
        border-bottom: 1px solid #e5e7eb;
    }
    .wip-table tr:last-child td { border-bottom: none; }
    
    .wip-stage {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 3px solid #0f62fe;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
        position: relative;
    }
    .wip-stage-transfer { border-left-color: #f59e0b; }
    
    .wip-stage-badge {
        position: absolute;
        top: -8px;
        right: 10px;
        font-size: 10px;
        font-weight: 700;
        background: #0f62fe;
        color: #fff;
        padding: 2px 6px;
        border-radius: 2px;
        text-transform: uppercase;
    }
    .badge-transfer { background: #f59e0b; }
    
    .wip-metrics {
        display: flex;
        justify-content: space-between;
        background: #f9fafb;
        border: 1px solid #f3f4f6;
        border-radius: 4px;
        padding: 6px 8px;
        margin-top: 8px;
    }
    .wip-metric { font-size: 11px; color: #6b7280; }
    .wip-metric strong { display: block; font-size: 12px; color: #111827; font-weight: 700; }
</style>

@php 
    $lot = $data['lots_data'][0] ?? null; 
    if(!$lot) return;
@endphp

<div class="wip-widget">

    <div class="wip-grid">
        <div class="wip-box">
            <label>Order SKU</label>
            <div>{{ $lot->orderProductSet->orderMain->sku ?? '-' }}</div>
        </div>
        <div class="wip-box">
            <label>Customer</label>
            <div>{{ $lot->orderProductSet->orderMain->customer->name ?? '-' }}</div>
        </div>
        <div class="wip-box">
            <label>Fabric</label>
            <div>{{ $lot->orderProductSet->fabric->name ?? '-' }}</div>
        </div>
        <div class="wip-box">
            <label>Color</label>
            <div>{{ $lot->orderProductSet->colors->name ?? '-' }}</div>
        </div>
        <div class="wip-box">
            <label>Pattern</label>
            <div>{{ $lot->orderProductSet->master_design_pattern->name ?? '-' }}</div>
        </div>
        <div class="wip-box">
            <label>Fitting</label>
            <div>{{ $lot->orderProductSet->master_product_fitting->name ?? '-' }}</div>
        </div>
    </div>

    <div class="wip-section-title">Initial Cutting & Rolls</div>
    <table class="wip-table">
        <thead>
            <tr>
                <th>Size</th>
                <th style="text-align: right;">Pieces</th>
            </tr>
        </thead>
        <tbody>
            @php $qtyTotal = 0; @endphp
            @foreach($data['rolls_data'] as $roll)
                @foreach($roll->fabricRollAssigningsDetail ?? [] as $d)
                    <tr>
                        <td>{{ $d->size }}</td>
                        <td style="text-align: right;">{{ $d->quantity }}</td>
                    </tr>
                    @php $qtyTotal += $d->quantity; @endphp
                @endforeach
            @endforeach
            <tr style="background: #f9fafb; font-weight: 700;">
                <td>Total</td>
                <td style="text-align: right; color: #0f62fe;">{{ $qtyTotal }}</td>
            </tr>
        </tbody>
    </table>

        <div class="wip-section-title" style="margin-top: 16px;">Stage Progress</div>
    <div style="margin-bottom: 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 4px; padding: 0 10px;">
        @foreach($master_stages as $stage)
            @php
                $d = getLotDetails($data['lot_no'], $stage->id);
                if (!$d || !$d['time_allocation']) continue;
                
                $remaining = (int) $d['remaining_quantity'];
                $total = (int) $d['quantity'];

                if ($total === 0) continue;

                $eta = \Carbon\Carbon::parse($d['time_allocation']);
                $completed = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;
                
                if ($remaining === 0) $status = ($completed && $completed->gt($eta)) ? 'delayed' : 'completed';
                elseif (now()->gt($eta)) $status = 'delayed';
                else $status = 'progress';

                $statusColors = [
                    'not_started' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'label' => 'Not Started'],
                    'progress' => ['bg' => '#e0e8ff', 'text' => '#0f62fe', 'label' => 'In Progress'],
                    'completed' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Completed'],
                    'delayed' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Delayed'],
                ];
                $c = $statusColors[$status];
            @endphp
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
                <div>
                    <div style="font-size: 12px; font-weight: 700; color: #111827;">{{ $stage->name }}</div>
                    <div style="font-size: 10px; color: #6b7280; margin-top: 2px;">{{ $status == 'not_started' ? 'Not Assigned' : $d['unit_name'] }}</div>
                </div>
                <div style="text-align: right;">
                    <span style="background: {{ $c['bg'] }}; color: {{ $c['text'] }}; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase;">{{ $c['label'] }}</span>
                    @if($status != 'not_started')
                        <div style="font-size: 10px; color: #6b7280; margin-top: 4px;">{{ $remaining }} / {{ $total }} left</div>
                    @endif
                </div>
            </div>
        @endforeach
        <div style="height: 1px;"></div> <!-- for last border issue -->
    </div>
    <div class="wip-section-title">Production Sessions</div>
    
    @php $cutting_unit = $lot->productionSlipDigitization->getUnitMaster->name ?? 'N/A'; @endphp
    
    {{-- TYPE 1 : CUTTING --}}
    @if(count($data['lots']) > 0)
        @foreach($data['lots'] as $index => $lotItem)
            @php $currentRolls = $data['rolls']->where('production_slip_digitization_id', $lotItem->production_slip_digitization_id); @endphp
            <div class="wip-stage">
                <div class="wip-stage-badge">Cutting</div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                    Unit: <strong style="color: #111827;">{{ $cutting_unit }}</strong>
                </div>
                <div class="wip-metrics">
                    <div class="wip-metric">Date<strong>{{ \Carbon\Carbon::parse($lot->production_datetime)->format('d M y') }}</strong></div>
                    <div class="wip-metric" style="text-align: center;">Rolls<strong>{{ $currentRolls->count() }}</strong></div>
                    <div class="wip-metric" style="text-align: right;">Meters<strong>{{ $currentRolls->sum('meter') }}</strong></div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- TYPE 2 : PRINTING --}}
    @if(count($data['printings']) > 0)
        @foreach($data['printings'] as $index => $printing)
            <div class="wip-stage">
                <div class="wip-stage-badge">Printing</div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                    <strong style="color: #111827;">{{ $printing->from_stage?->name ?? 'Cutting' }}</strong> &rarr; <strong style="color: #111827;">{{ $printing->to_stage?->name ?? '-' }}</strong><br>
                    Unit: <strong style="color: #111827;">{{ $printing->getToUnitMaster?->name ?? '-' }}</strong>
                </div>
                <div class="wip-metrics">
                    <div class="wip-metric">Date<strong>{{ \Carbon\Carbon::parse($printing->production_datetime)->format('d M y') }}</strong></div>
                    <div class="wip-metric" style="text-align: right;">Pieces<strong>{{ $printing->quantity }}</strong></div>
                </div>
            </div>
        @endforeach
    @endif

    {{-- TYPE 3 : STAGE TRANSFER --}}
    @if(count($data['stage_transactions']) > 0)
        @foreach($data['stage_transactions'] as $index => $transaction)
            <div class="wip-stage wip-stage-transfer">
                <div class="wip-stage-badge badge-transfer">Transfer</div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">
                    <strong style="color: #111827;">{{ $transaction->from_stage?->name ?? '-' }}</strong> &rarr; <strong style="color: #111827;">{{ $transaction->to_stage?->name ?? '-' }}</strong><br>
                    From: <strong style="color: #111827;">{{ $transaction->getFromUnitMaster?->name ?? '-' }}</strong><br>
                    To: <strong style="color: #111827;">{{ $transaction->getToUnitMaster?->name ?? '-' }}</strong>
                </div>
                <div class="wip-metrics">
                    <div class="wip-metric">Date<strong>{{ \Carbon\Carbon::parse($transaction->production_datetime)->format('d M y') }}</strong></div>
                    <div class="wip-metric" style="text-align: right;">Pieces<strong>{{ $transaction->quantity }}</strong></div>
                </div>
            </div>
        @endforeach
    @endif
    
    @if(count($data['lots']) == 0 && count($data['printings']) == 0 && count($data['stage_transactions']) == 0)
        <div style="text-align: center; color: #6b7280; font-size: 12px; margin-top: 20px;">
            <i class="fas fa-info-circle" style="font-size: 24px; color: #d1d5db; margin-bottom: 8px;"></i><br>
            No production stage data recorded for this lot yet.
        </div>
    @endif

</div>

