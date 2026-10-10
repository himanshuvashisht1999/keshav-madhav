@php use Carbon\Carbon; @endphp


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
                <td style="text-align: right; color: var(--brand-dark-green, #05421c);">{{ $qtyTotal }}</td>
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
                    'progress' => ['bg' => '#edf5ee', 'text' => '#05421c', 'label' => 'In Progress'],
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

