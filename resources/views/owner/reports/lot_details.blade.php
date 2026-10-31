@extends('owner.layouts.app')

@section('title', 'Lot ' . $data['lot_no'])

@section('content')
    @php
        $set = $lot->orderProductSet ?? null;
        $order = $set->orderMain ?? null;
        $rolls = $data['rolls_data'];
        $piecesCut = (int) $rolls->sum(fn($r) => $r->fabricRollAssigningsDetail->sum('quantity'));
        $metres = (float) $rolls->sum('meter');
        // Printing (1) and Godam (13) hold embroidery parts, the other stages hold garments: keep the two counts apart (as on the WIP page)
        $partsRemaining = collect($stages)->whereIn('id', [1, 13])->sum('remaining');
        $stageRemaining = collect($stages)->whereNotIn('id', [1, 13])->sum('remaining');
        $stageNow = $facts['stage_name'] ?? ($data['last_current_stage'] ?? 'Cutting');
        $isLate = ($facts['is_delayed'] ?? 'No') === 'Yes';
        $isDone = in_array($stageNow, ['Dispatched', 'Packed (stock)'], true);

        // pieces cut per size (sizes in the order they were cut)
        $bySize = [];
        foreach ($rolls as $r) {
            foreach ($r->fabricRollAssigningsDetail as $d) {
                $bySize[$d->size] = ($bySize[$d->size] ?? 0) + (int) $d->quantity;
            }
        }
        $statusText = ['done' => 'Done', 'late' => 'Done late', 'delayed' => 'Delayed', 'progress' => 'In progress'];
        $statusTone = ['done' => 'green', 'late' => 'red', 'delayed' => 'red', 'progress' => 'yellow'];
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Lot ' . $data['lot_no'],
        'sub' => trim(($order->sku ?? '-') . ' · ' . ($order->customer->name ?? '-')),
        'back' => route('owner.lots'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.lot-details.pdf', ['lot_no' => $data['lot_no']]) . '"><i class="fas fa-file-pdf"></i> PDF</a>',
    ])

    @include('owner.common.totals', ['totals' => array_values(array_filter([
        ['label' => 'Pieces cut', 'value' => number_format($piecesCut)],
        ['label' => 'Packed', 'value' => number_format($facts['packed'] ?? 0), 'tone' => 'light'],
        ['label' => 'Dispatched', 'value' => number_format($facts['dispatched'] ?? 0), 'tone' => 'word'],
        ($facts['in_stock'] ?? 0) > 0 ? ['label' => 'Packed to stock', 'value' => number_format($facts['in_stock']), 'tone' => 'light'] : null,
        ['label' => 'Pcs in stages', 'value' => number_format($stageRemaining), 'tone' => 'yellow'],
        $partsRemaining > 0 ? ['label' => 'Parts at Printing / Godam', 'value' => number_format($partsRemaining), 'tone' => 'yellow'] : null,
        ['label' => 'Stage now', 'value' => $stageNow . ($isLate ? ' (delayed)' : ''), 'tone' => $isLate ? 'red' : ($isDone ? 'word' : null)],
        ['label' => 'Rolls', 'value' => number_format($rolls->count())],
        ['label' => 'Metres', 'value' => number_format($metres, 2)],
    ]))])

    <div class="ow-section">
        <dl class="ow-kv">
            <dt>Order</dt><dd>{{ $order->sku ?? '-' }}</dd>
            <dt>Customer</dt><dd>{{ $order->customer->name ?? '-' }}</dd>
            <dt>Design</dt><dd>{{ $set->design_number ?? '-' }}</dd>
            <dt>Fabric</dt><dd>{{ $set->fabric->name ?? '-' }}</dd>
            <dt>Colour</dt><dd>{{ $set->colors->name ?? '-' }}</dd>
            <dt>Pattern</dt><dd>{{ $set->master_design_pattern->name ?? '-' }}</dd>
            <dt>Fitting</dt><dd>{{ $set->master_product_fitting->name ?? '-' }}</dd>
            <dt>Production unit</dt><dd>{{ $unitName }}</dd>
            <dt>Lot date</dt><dd>{{ $lotDate ? \Carbon\Carbon::parse($lotDate)->format('d M Y') : '-' }}</dd>
            <dt>Stage now</dt>
            <dd>
                <span class="ow-badge {{ $isDone ? 'green' : ($isLate ? 'red' : 'yellow') }}">{{ $stageNow }}</span>
                @if($isLate && !empty($facts['allowed_till']))
                    <span class="ow-badge red">Delayed: plan was {{ \Carbon\Carbon::parse($facts['allowed_till'])->format('d M Y') }}</span>
                @endif
            </dd>
            <dt>Plan end</dt><dd>{{ !empty($facts['expected_end']) ? \Carbon\Carbon::parse($facts['expected_end'])->format('d M Y') : '-' }}</dd>
        </dl>
    </div>

    <div class="ow-table-title"><i class="fas fa-stream"></i> Stage progress</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Unit</th>
                    <th class="num">Total</th>
                    <th class="num">Remaining</th>
                    <th>Start</th>
                    <th>Plan end</th>
                    <th>Completed</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stages as $s)
                    <tr>
                        <td>{{ $s['name'] }}</td>
                        <td>{{ $s['unit'] }}</td>
                        <td class="num">{{ number_format($s['total']) }}</td>
                        <td class="num {{ $s['remaining'] > 0 ? 'ow-dr' : '' }}">{{ number_format($s['remaining']) }}</td>
                        <td>{{ $s['start'] ? $s['start']->format('d M Y') : '-' }}</td>
                        <td>{{ $s['eta']->format('d M Y') }}</td>
                        <td>{{ $s['completed'] ? $s['completed']->format('d M Y') : '-' }}</td>
                        <td><span class="ow-badge {{ $statusTone[$s['status']] }}">{{ $statusText[$s['status']] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ow-empty">No stage has received pieces of this lot yet</td></tr>
                @endforelse
            </tbody>
            @if(count($stages))
                <tfoot>
                    <tr>
                        <td>Pcs in stages</td>
                        <td colspan="2">{{ $partsRemaining > 0 ? '+ ' . number_format($partsRemaining) . ' parts at Printing / Godam' : '' }}</td>
                        <td class="num">{{ number_format($stageRemaining) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="ow-table-title"><i class="fas fa-cut"></i> Pieces cut by size</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Size</th>
                    <th class="num">Pieces</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bySize as $size => $qty)
                    <tr>
                        <td>{{ $size }}</td>
                        <td class="num">{{ number_format($qty) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="ow-empty">No pieces cut yet</td></tr>
                @endforelse
            </tbody>
            @if(count($bySize))
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="num">{{ number_format(array_sum($bySize)) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="ow-table-title"><i class="fas fa-scroll"></i> Rolls used</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Roll</th>
                    <th>Fabric</th>
                    <th class="num">Metres</th>
                </tr>
            </thead>
            <tbody>
                {{-- pieces are recorded once per cutting session (on its first roll), so they are shown by size and per session, not per roll --}}
                @forelse($rolls as $r)
                    <tr>
                        <td>{{ $r->roll_no ?: '-' }}</td>
                        <td>{{ $r->fabricReceiptDetail->fabric->name ?? '-' }}</td>
                        <td class="num">{{ number_format((float) $r->meter, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ow-empty">No rolls used yet</td></tr>
                @endforelse
            </tbody>
            @if($rolls->count())
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td>{{ number_format($rolls->count()) }} rolls</td>
                        <td class="num">{{ number_format($metres, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="ow-table-title"><i class="fas fa-history"></i> Production sessions</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Stage</th>
                    <th>Unit</th>
                    <th class="num">Pieces</th>
                    <th class="num">Rolls</th>
                    <th class="num">Metres</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($s['date'])->format('d M Y') }}</td>
                        <td><span class="ow-badge {{ $s['rework'] ? 'red' : ($s['type'] === 'Cutting' ? 'green' : 'grey') }}">{{ $s['type'] }}</span></td>
                        <td>{{ $s['from_stage'] }} <i class="fas fa-arrow-right ow-arrow"></i> {{ $s['to_stage'] }}</td>
                        <td>{{ $s['from_unit'] }} <i class="fas fa-arrow-right ow-arrow"></i> {{ $s['to_unit'] }}</td>
                        <td class="num">{{ number_format($s['pieces']) }}</td>
                        <td class="num">{{ $s['rolls'] !== null ? number_format($s['rolls']) : '' }}</td>
                        <td class="num">{{ $s['metres'] !== null ? number_format($s['metres'], 2) : '' }}</td>
                        <td class="wrap {{ $s['rework'] ? 'ow-dr' : '' }}">{{ $s['remark'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ow-empty">No production sessions recorded</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="small text-muted mb-3">Cutting rows show the pieces, rolls and metres cut in that session; the other rows show pieces moved from one stage or unit to the next.</p>
@endsection
