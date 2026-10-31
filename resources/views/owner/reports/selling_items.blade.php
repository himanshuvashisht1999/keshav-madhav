@extends('owner.layouts.app')

@section('title', 'Selling Items')

@section('content')
    @php
        $filterCount = ($start ? 1 : 0) + ($end ? 1 : 0) + ($design !== '' ? 1 : 0) + ($orderBy === 'asc' ? 1 : 0) + ($orderType !== '' ? 1 : 0);
        $salesWhat = ['corporate' => 'corporate sales orders', 'domestic' => 'domestic sales orders'][$orderType] ?? 'sales orders';
        $period = $start && $end ? date('d M Y', strtotime($start)) . ' to ' . date('d M Y', strtotime($end))
            : ($start ? 'From ' . date('d M Y', strtotime($start)) : ($end ? 'Up to ' . date('d M Y', strtotime($end)) : 'All dates'));
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Selling Items',
        'sub' => 'Pieces ordered per design (' . $salesWhat . ' + agent orders) - ' . $period,
        'back' => route('owner.dashboard'),
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Designs', 'value' => number_format($totals['designs'])],
        ['label' => 'Sales-order pcs', 'value' => number_format($totals['sales']), 'tone' => 'light'],
        ['label' => 'Agent-order pcs', 'value' => number_format($totals['agent']), 'tone' => 'yellow'],
        ['label' => 'Total pcs', 'value' => number_format($totals['total']), 'tone' => 'word'],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.reports.selling-items') }}">
            <div>
                <label>Order date from</label>
                <input type="date" class="form-control" name="start_date" value="{{ $start }}">
            </div>
            <div>
                <label>Order date to</label>
                <input type="date" class="form-control" name="end_date" value="{{ $end }}">
            </div>
            <div>
                <label>Design no.</label>
                <input type="text" class="form-control" name="design" value="{{ $design }}" placeholder="Any design">
            </div>
            <div>
                <label>Sales-order type</label>
                <select class="form-control" name="order_type">
                    <option value="">All (corporate + domestic)</option>
                    <option value="corporate" {{ $orderType === 'corporate' ? 'selected' : '' }}>Corporate</option>
                    <option value="domestic" {{ $orderType === 'domestic' ? 'selected' : '' }}>Domestic</option>
                </select>
            </div>
            <div>
                <label>Sort</label>
                <select class="form-control" name="order_by">
                    <option value="desc" {{ $orderBy === 'desc' ? 'selected' : '' }}>Most pieces first</option>
                    <option value="asc" {{ $orderBy === 'asc' ? 'selected' : '' }}>Fewest pieces first</option>
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.reports.selling-items') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Design</th>
                    <th class="num">Rank</th>
                    <th class="num">Sales-order pcs</th>
                    <th class="num">Agent-order pcs</th>
                    <th class="num">Total pcs</th>
                </tr>
            </thead>
            <tbody>
                @include('owner.reports.partials.selling_items_list', ['items' => $rows])
            </tbody>
            @if($rows->total() || $noDesign)
                <tfoot>
                    @if($noDesign)
                        {{-- domestic orders saved without a design: counted in the totals, not ranked --}}
                        <tr>
                            <td>No design chosen (domestic)</td>
                            <td class="num">-</td>
                            <td class="num">{{ number_format($noDesign->sales_qty) }}</td>
                            <td class="num">{{ number_format($noDesign->agent_qty) }}</td>
                            <td class="num">{{ number_format($noDesign->total_qty) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Total ({{ number_format($totals['designs']) }} designs{{ $noDesign ? ' + no design' : '' }})</td>
                        <td></td>
                        <td class="num">{{ number_format($totals['sales']) }}</td>
                        <td class="num">{{ number_format($totals['agent']) }}</td>
                        <td class="num">{{ number_format($totals['total']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    {{ $rows->links() }}

    <p class="small text-muted">
        Sales-order pcs = corporate and domestic sales orders (order date = the day the order was entered); the
        sales-order type filter keeps one of them. Domestic orders make our own stock, which the agents then sell.
        Agent-order pcs = items of agent orders by order date (not changed by the sales-order type filter).
        @if($noDesign)
            No design chosen (domestic) = domestic orders saved without a design; their pieces are in the totals but not ranked.
        @endif
        @if($totals['max_double'] > 0)
            {{-- pieces without a design cannot be matched with agent items, so the figure leaves them out --}}
            With All or Domestic, Total pcs can count a piece twice, once when it is ordered for our stock and once when
            an agent sells it: up to {{ number_format($totals['max_double']) }} pcs for these filters{{ ($noDesign->sales_qty ?? 0) > 0 ? ' (the ' . number_format($noDesign->sales_qty) . ' pcs made without a design are not in this figure)' : '' }}.
        @endif
    </p>
@endsection
