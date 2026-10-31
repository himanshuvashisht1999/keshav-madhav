@extends('owner.layouts.app')

@section('title', 'Order Summary')

@section('content')
    @php
        $filterKeys = ['order_no', 'po_number', 'design_number', 'lot_no', 'customer_id', 'status'];
        $nFilters = collect($filterKeys)->filter(fn($k) => request()->filled($k))->count();
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Order Summary',
        'sub' => 'Ordered, packed and dispatched pieces of every order',
        'back' => route('owner.dashboard'),
        'actions' => null,
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Orders', 'value' => number_format($totals['orders'])],
        ['label' => 'Total pcs', 'value' => number_format($totals['total'])],
        ['label' => 'Packed', 'value' => number_format($totals['packed']), 'tone' => 'light'],
        ['label' => 'Dispatched', 'value' => number_format($totals['dispatched']), 'tone' => 'word'],
        ['label' => 'To stock', 'value' => number_format($totals['to_stock']), 'tone' => 'light'],
        ['label' => 'Remaining', 'value' => number_format($totals['remaining']), 'tone' => $totals['remaining'] > 0 ? 'yellow' : null],
        ['label' => 'Completed orders', 'value' => number_format($totals['completed']), 'tone' => 'word'],
    ]])

    <nav class="ow-tabs">
        <a class="active" href="{{ route('owner.order-summary.index') }}"><i class="fas fa-file-invoice"></i> Order Summary</a>
        <a href="{{ route('owner.orders') }}"><i class="fas fa-layer-group"></i> Sales Orders (lots)</a>
    </nav>

    <details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.order-summary.index') }}">
            <div>
                <label>Order no</label>
                <input type="text" class="form-control" name="order_no" value="{{ request('order_no') }}" placeholder="Any part">
            </div>
            <div>
                <label>PO no</label>
                <input type="text" class="form-control" name="po_number" value="{{ request('po_number') }}" placeholder="Any part">
            </div>
            <div>
                <label>Design no</label>
                <input type="text" class="form-control" name="design_number" value="{{ request('design_number') }}" placeholder="Any part">
            </div>
            <div>
                <label>Lot no</label>
                <input type="text" class="form-control" name="lot_no" value="{{ request('lot_no') }}" placeholder="Any part">
            </div>
            <div>
                <label>Customer</label>
                <select class="form-control select2" name="customer_id" data-allow-clear="1" data-placeholder="All">
                    <option value=""></option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ (string) request('customer_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Status</label>
                <select class="form-control" name="status">
                    <option value="">All</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.order-summary.index') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Swipe the table sideways to see all columns</div>
    @include('owner.reports.partials.order_summary_table', ['orders' => $orders])
    {{ $orders->links() }}
@endsection
