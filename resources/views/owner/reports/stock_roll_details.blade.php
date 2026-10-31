@extends('owner.layouts.app')

@section('title', 'Roll History')

@section('content')
@php
    $m = fn($v) => number_format((float) $v, 2);
    $first = $rolls->first();
    $tiles = [
        ['label' => 'In (m)', 'value' => $m($totals->in)],
        ['label' => 'Out (m)', 'value' => $m($totals->out), 'tone' => 'yellow'],
        ['label' => 'Balance (m)', 'value' => $m($totals->balance), 'tone' => 'word'],
        ['label' => 'Left in roll now (m)', 'value' => $m($totals->remaining), 'tone' => 'light'],
    ];
    if (abs($totals->gap) >= 0.01) {
        $tiles[] = ['label' => 'Not explained (m)', 'value' => $m($totals->gap), 'tone' => 'red'];
    }
@endphp

@include('owner.common.page_head', [
    'title' => 'Roll ' . ($rollNo ?: '-'),
    'sub' => ($fabric->name ?? 'Unknown fabric') . ' | now at ' . ($first->master_fabric_warehouse?->cutting_master_name ?? '-'),
    'back' => route('owner.report.stock.rolls', array_filter(['fabric_id' => $first->fabric_id])),
    'actions' => null,
])
@include('owner.common.totals', ['totals' => $tiles])

@if($rolls->count() > 1)
    <div class="ow-section">
        This roll number came in {{ $rolls->count() }} times for this fabric. The history below joins them; open one roll:
        @foreach($rolls as $r)
            <a href="{{ route('owner.report.stock.rolls.tracking', ['frd_id' => $r->id]) }}">row {{ $r->id }} ({{ $r->shipment_number ?: 'no shipment no' }}, {{ $m($r->meter) }} m)</a>@if(!$loop->last), @endif
        @endforeach
    </div>
@endif

@if(abs($totals->gap) >= 0.01)
    <div class="ow-alert err">
        In minus out is {{ $m($totals->balance) }} m but the roll shows {{ $m($totals->remaining) }} m left: {{ $m(abs($totals->gap)) }} m moved without a record shown here.
    </div>
@endif

<div class="ow-table-wrap">
    <table class="erp-table sticky-first">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th class="num">In (m)</th>
                <th class="num">Out (m)</th>
                <th class="num">Balance (m)</th>
                <th>Ref / order no</th>
                <th>Lot / shipment</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y, h:i A') }}</td>
                    <td><span class="ow-badge {{ $row->tone }}">{{ $row->type }}</span></td>
                    <td class="num">{{ $row->in > 0 ? $m($row->in) : '-' }}</td>
                    <td class="num {{ $row->out > 0 ? 'ow-dr' : '' }}">{{ $row->out > 0 ? $m($row->out) : '-' }}</td>
                    <td class="num"><strong>{{ $m($row->balance) }}</strong></td>
                    <td>{{ $row->ref }}</td>
                    <td>{{ $row->lot }}</td>
                    <td class="wrap">{{ $row->details }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="ow-empty">No movements found for this roll</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td></td>
                <td class="num">{{ $m($totals->in) }}</td>
                <td class="num">{{ $m($totals->out) }}</td>
                <td class="num">{{ $m($totals->balance) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
