@extends('owner.layouts.app')

@section('title', 'Ready Stock')

@section('content')
@php
    $nFilters = (request()->filled('design_number') ? 1 : 0) + (request()->filled('product_id') ? 1 : 0)
        + (request()->filled('size_set_id') ? 1 : 0) + (request()->filled('color_id') ? 1 : 0)
        + (request()->filled('storeroom_id') ? 1 : 0) + (request()->boolean('in_stock') ? 1 : 0);
@endphp

@include('owner.common.page_head', [
    'title' => 'Ready Stock',
    'sub' => 'Finished goods in stock, by design, size set and colour',
    'back' => route('owner.stock'),
    'actions' => null,
])
@include('owner.common.totals', ['totals' => [
    ['label' => 'Rows', 'value' => number_format($totals->row_count)],
    ['label' => 'Products', 'value' => number_format($totals->products), 'tone' => 'light'],
    ['label' => 'Boxes', 'value' => number_format($totals->boxes), 'tone' => 'word'],
    ['label' => 'Pieces', 'value' => number_format($totals->pieces), 'tone' => 'word'],
    ['label' => 'Value at MRP (₹)', 'value' => number_format($totals->mrp_value, 2), 'tone' => 'yellow'],
    ['label' => 'On open orders (boxes)', 'value' => number_format($totals->order_boxes), 'tone' => 'light'],
]])

<details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
    <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
    <form class="ow-filter-body" method="GET" action="{{ route('owner.ready-stock.index') }}">
        <div>
            <label>Design no</label>
            <input type="text" class="form-control" name="design_number" value="{{ request('design_number') }}" placeholder="Part of design no">
        </div>
        <div>
            <label>Product</label>
            <select class="form-control select2" name="product_id" data-allow-clear="1" data-placeholder="All">
                <option value=""></option>
                @foreach($products as $p)
                    @php $pName = trim(($p->series_name ?? '') . ' ' . ($p->name_of_garment ?? '')); @endphp
                    <option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->design_number ?: '-' }}{{ $pName !== '' ? ' - ' . $pName : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Size set</label>
            <select class="form-control select2" name="size_set_id" data-allow-clear="1" data-placeholder="All">
                <option value=""></option>
                @foreach($sizeSets as $s)
                    <option value="{{ $s->id }}" {{ (string) request('size_set_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Colour</label>
            <select class="form-control select2" name="color_id" data-allow-clear="1" data-placeholder="All">
                <option value=""></option>
                @foreach($colors as $c)
                    <option value="{{ $c->id }}" {{ (string) request('color_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Storeroom</label>
            <select class="form-control select2" name="storeroom_id" data-allow-clear="1" data-placeholder="All">
                <option value=""></option>
                @foreach($storerooms as $sr)
                    <option value="{{ $sr->id }}" {{ (string) request('storeroom_id') === (string) $sr->id ? 'selected' : '' }}>{{ $sr->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Show</label>
            <select class="form-control" name="in_stock">
                <option value="">All rows</option>
                <option value="1" {{ request()->boolean('in_stock') ? 'selected' : '' }}>Only rows with boxes</option>
            </select>
        </div>
        <div class="ow-filter-actions">
            <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
            <a class="ow-btn is-outline" href="{{ route('owner.ready-stock.index') }}">Reset</a>
        </div>
    </form>
</details>

<div class="ow-table-wrap">
    <table class="erp-table sticky-first">
        <thead>
            <tr>
                <th>Design no</th>
                <th>Product</th>
                <th>Size set</th>
                <th>Colour</th>
                <th>Fitting</th>
                <th>Pattern</th>
                <th class="num">MRP (₹)</th>
                <th class="num">Boxes</th>
                <th class="num">Pieces</th>
                <th class="num">On order (boxes)</th>
            </tr>
        </thead>
        <tbody id="stock-rows">
            @include('owner.reports.inventory_list', ['rows' => $rows])
            @if($rows->isEmpty())
                <tr><td colspan="10" class="ow-empty">No stock found</td></tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td>Total ({{ number_format($totals->row_count) }})</td>
                <td></td><td></td><td></td><td></td><td></td><td></td>
                <td class="num">{{ number_format($totals->boxes) }}</td>
                <td class="num">{{ number_format($totals->pieces) }}</td>
                <td class="num">{{ number_format($totals->order_boxes) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@include('owner.reports.partials.stock_load_more', ['paginator' => $rows, 'target' => 'stock-rows'])
@endsection
