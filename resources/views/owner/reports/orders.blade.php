@extends('owner.layouts.app')

@section('title', 'Sales Orders')

@section('content')
    @php
        $filterKeys = ['order_no', 'lot_no', 'customer_id', 'delay_status', 'date_from', 'date_to'];
        $nFilters = collect($filterKeys)->filter(fn($k) => request()->filled($k))->count();
        $stageTone = fn($stage) => in_array($stage, ['Dispatched', 'Packed (stock)'], true) ? 'green' : ($stage === 'Not Issued' ? 'grey' : 'yellow');
        $pageRows = $orders->getCollection()->flatten(1);
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Sales Orders',
        'sub' => 'Every lot of every order. Dispatched = sent to the customer or put in domestic stock',
        'back' => route('owner.dashboard'),
        'actions' => null,
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Orders', 'value' => number_format($totals['orders'])],
        ['label' => 'Ordered pcs', 'value' => number_format($totals['ordered'])],
        ['label' => 'Lots', 'value' => number_format($totals['lots']), 'tone' => 'light'],
        ['label' => 'Cut pcs', 'value' => number_format($totals['cut'])],
        ['label' => 'Dispatched pcs', 'value' => number_format($totals['dispatched']), 'tone' => 'word'],
        ['label' => 'Delayed lots', 'value' => number_format($totals['delayed']), 'tone' => $totals['delayed'] > 0 ? 'red' : null],
    ]])

    <nav class="ow-tabs">
        <a href="{{ route('owner.order-summary.index') }}"><i class="fas fa-file-invoice"></i> Order Summary</a>
        <a class="active" href="{{ route('owner.orders') }}"><i class="fas fa-layer-group"></i> Sales Orders (lots)</a>
    </nav>

    <details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.orders') }}">
            <div>
                <label>Order no</label>
                <input type="text" class="form-control" name="order_no" value="{{ request('order_no') }}" placeholder="Any part">
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
                <label>Delay</label>
                <select class="form-control" name="delay_status">
                    <option value="">All</option>
                    <option value="Yes" {{ request('delay_status') === 'Yes' ? 'selected' : '' }}>Delayed</option>
                    <option value="No" {{ request('delay_status') === 'No' ? 'selected' : '' }}>On time</option>
                </select>
            </div>
            <div>
                <label>Order date from</label>
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label>Order date to</label>
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.orders') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Swipe the table sideways to see all columns</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Order no</th>
                    <th>Order date</th>
                    <th>Customer</th>
                    <th class="num">Order pcs</th>
                    <th>Lot no</th>
                    <th class="num">Lot pcs</th>
                    <th class="num">Dispatched</th>
                    <th>Current stage</th>
                    <th>Delay</th>
                    <th>Stage due</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $orderNo => $lots)
                    @foreach($lots as $i => $lot)
                        <tr @if($i === 0 && !$loop->parent->first) class="ow-row-sub" @endif>
                            <td>
                                @if($i === 0)
                                    <a href="{{ route('owner.order-summary.view', ['id' => $lot['order_id']]) }}">{{ $orderNo }}</a>
                                @else
                                    <span class="ow-muted-cell">{{ $orderNo }}</span>
                                @endif
                            </td>
                            <td>{{ $i === 0 ? date('d M Y', strtotime($lot['order_date'])) : '' }}</td>
                            <td>{{ $i === 0 ? $lot['customer'] : '' }}</td>
                            <td class="num">{{ $i === 0 ? number_format($lot['total_pcs_in_order']) : '' }}</td>
                            <td>
                                @if($lot['lot_no'] !== '-')
                                    <a href="{{ route('owner.lot-details', ['lot_no' => $lot['lot_no']]) }}">{{ $lot['lot_no'] }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="num">{{ number_format($lot['pieces_in_lot']) }}</td>
                            <td class="num">{{ number_format($lot['dispatched']) }}</td>
                            <td><span class="ow-badge {{ $stageTone($lot['stage_name']) }}">{{ $lot['stage_name'] }}</span></td>
                            <td>
                                @if($lot['lot_no'] === '-')
                                    -
                                @elseif($lot['order_done'] && !in_array($lot['stage_name'], ['Dispatched', 'Packed (stock)'], true))
                                    {{-- order marked Completed and lot not fully sent: tagged like the Lots page, never delayed --}}
                                    <span class="ow-badge grey">Order completed</span>
                                @elseif($lot['isDelayed'] === 'Yes')
                                    <span class="ow-badge red">Delayed</span>
                                @else
                                    <span class="ow-badge green">On time</span>
                                @endif
                            </td>
                            <td>{{ $lot['allowed_till_datetime'] ? date('d M Y', strtotime($lot['allowed_till_datetime'])) : '-' }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="10" class="ow-empty">No orders found</td></tr>
                @endforelse
            </tbody>
            @if($pageRows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td>{{ $orders->hasPages() ? 'This page' : 'Total' }}</td>
                        <td>{{ number_format($orders->count()) }} orders</td>
                        <td></td>
                        <td class="num">{{ number_format($orders->getCollection()->sum(fn($lots) => $lots->first()['total_pcs_in_order'])) }}</td>
                        <td>{{ number_format($pageRows->where('lot_no', '!=', '-')->count()) }} lots</td>
                        <td class="num">{{ number_format($pageRows->sum('pieces_in_lot')) }}</td>
                        <td class="num">{{ number_format($pageRows->sum('dispatched')) }}</td>
                        <td></td>
                        <td>{{ number_format($pageRows->where('isDelayed', 'Yes')->count()) }} delayed</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    {{ $orders->links() }}

    <p class="small text-muted mb-3">
        Delayed lots = lots whose current stage is past its planned end. Lots of orders marked Completed are not
        counted as delayed (tagged Order completed).
    </p>
@endsection
