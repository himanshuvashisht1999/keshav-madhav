@extends('owner.layouts.app')

@section('title', 'Stock by Roll')

@section('content')
@php
    $m = fn($v) => number_format((float) $v, 2);
    $whSel = array_map('strval', $warehouseIds);
    $nFilters = (request()->filled('fabric_id') ? 1 : 0) + (count($whSel) ? 1 : 0) + (request()->filled('roll_no') ? 1 : 0)
        + (request()->filled('qty_from') ? 1 : 0) + (request()->filled('qty_to') ? 1 : 0) + (request()->boolean('in_stock') ? 1 : 0);
@endphp

@include('owner.common.page_head', [
    'title' => 'Stock by Roll',
    'sub' => 'Every roll received; tap a roll number for its history (metres)',
    'back' => route('owner.stock'),
    'actions' => null,
])
@include('owner.common.totals', ['totals' => [
    ['label' => 'Rolls', 'value' => number_format($totals->rolls)],
    ['label' => 'Rolls with stock', 'value' => number_format($totals->rolls_in_stock), 'tone' => 'light'],
    ['label' => 'Received (m)', 'value' => $m($totals->received)],
    ['label' => 'Issued (m)', 'value' => $m($totals->received - $totals->remaining), 'tone' => 'yellow'],
    ['label' => 'Remaining (m)', 'value' => $m($totals->remaining), 'tone' => 'word'],
]])

<details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
    <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
    <form class="ow-filter-body" method="GET" action="{{ route('owner.report.stock.rolls') }}">
        <div>
            <label>Fabric</label>
            <select class="form-control select2" name="fabric_id" data-allow-clear="1" data-placeholder="All">
                <option value=""></option>
                @foreach($fabrics as $f)
                    <option value="{{ $f->id }}" {{ (string) request('fabric_id') === (string) $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Warehouse (now)</label>
            <select class="form-control select2" name="warehouse_id[]" multiple data-allow-clear="1" data-placeholder="All">
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ in_array((string) $wh->id, $whSel, true) ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Roll no</label>
            <input type="text" class="form-control" name="roll_no" value="{{ request('roll_no') }}" placeholder="Part of roll no">
        </div>
        <div>
            <label>Remaining from (m)</label>
            <input type="number" step="0.01" class="form-control" name="qty_from" value="{{ request('qty_from') }}" placeholder="0.00">
        </div>
        <div>
            <label>Remaining to (m)</label>
            <input type="number" step="0.01" class="form-control" name="qty_to" value="{{ request('qty_to') }}" placeholder="Any">
        </div>
        <div>
            <label>Show</label>
            <select class="form-control" name="in_stock">
                <option value="">All rolls</option>
                <option value="1" {{ request()->boolean('in_stock') ? 'selected' : '' }}>Only rolls with stock</option>
            </select>
        </div>
        <div class="ow-filter-actions">
            <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
            <a class="ow-btn is-outline" href="{{ route('owner.report.stock.rolls') }}">Reset</a>
        </div>
    </form>
</details>

<div class="ow-table-wrap">
    <table class="erp-table sticky-first">
        <thead>
            <tr>
                <th>Roll no</th>
                <th>Fabric</th>
                <th>Warehouse</th>
                <th>Supplier</th>
                <th>PO</th>
                <th>Shipment no</th>
                <th class="num">Received (m)</th>
                <th class="num">Issued (m)</th>
                <th class="num">Remaining (m)</th>
            </tr>
        </thead>
        <tbody id="roll-rows">
            @include('owner.reports.partials.stock_rolls_list', ['rolls' => $rolls])
            @if($rolls->isEmpty())
                <tr><td colspan="9" class="ow-empty">No rolls found</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total ({{ number_format($totals->rolls) }})</td>
                <td></td><td></td><td></td><td></td><td></td>
                <td class="num">{{ $m($totals->received) }}</td>
                <td class="num">{{ $m($totals->received - $totals->remaining) }}</td>
                <td class="num">{{ $m($totals->remaining) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@include('owner.reports.partials.stock_load_more', ['paginator' => $rolls, 'target' => 'roll-rows'])
@endsection
