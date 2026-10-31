@extends('owner.layouts.app')

@section('title', 'Delayed Payments')

@section('content')
    @php
        $filterCount = ($days !== 120 ? 1 : 0) + ($type !== '' ? 1 : 0) + ($search !== '' ? 1 : 0) + ($show === 'all' ? 1 : 0);
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Delayed Payments',
        'sub' => 'What parties owe us beyond their dispatch bills of the last ' . $days . ' days (only those bills count as not yet due)',
        'back' => route('owner.dashboard'),
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Parties', 'value' => number_format($totals['parties'])],
        ['label' => 'Overdue > ' . $days . ' days', 'value' => '₹ ' . number_format($totals['overdue'], 2), 'tone' => 'red'],
        ['label' => 'Total owed (Dr)', 'value' => '₹ ' . number_format($totals['owed'], 2), 'tone' => 'yellow'],
        ['label' => 'Not yet overdue', 'value' => '₹ ' . number_format($totals['recent'], 2), 'tone' => 'light'],
        ['label' => 'Customers / Vendors', 'value' => number_format($totals['customers']) . ' / ' . number_format($totals['vendors'])],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.reports.delayed-payments') }}">
            <div>
                <label>Older than (days)</label>
                <input type="number" min="1" max="3650" class="form-control" name="days" value="{{ $days }}">
            </div>
            <div>
                <label>Party type</label>
                <select class="form-control" name="type">
                    <option value="">Customers and vendors</option>
                    <option value="customer" {{ $type === 'customer' ? 'selected' : '' }}>Customers</option>
                    <option value="vendor" {{ $type === 'vendor' ? 'selected' : '' }}>Vendors</option>
                </select>
            </div>
            <div>
                <label>Party name</label>
                <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search name">
            </div>
            <div>
                <label>Show</label>
                <select class="form-control" name="show">
                    <option value="overdue" {{ $show === 'overdue' ? 'selected' : '' }}>Only overdue parties</option>
                    <option value="all" {{ $show === 'all' ? 'selected' : '' }}>Everyone who owes us</option>
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.reports.delayed-payments') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Party</th>
                    <th>Type</th>
                    <th class="num">Overdue &gt; {{ $days }} days</th>
                    <th class="num">Total owed (Dr)</th>
                    <th class="num">Not yet overdue</th>
                    <th>Last dispatch</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $p)
                    <tr>
                        <td class="wrap">
                            <a href="{{ route('owner.party-ledger.show', ['type' => $p->type, 'id' => $p->id]) }}">{{ $p->name }}</a>
                            @if($p->status === 3)
                                <span class="ow-badge red">Deleted</span>
                            @elseif($p->status !== 1)
                                <span class="ow-badge grey">Inactive</span>
                            @endif
                        </td>
                        <td><span class="ow-badge type">{{ $p->type_label }}</span></td>
                        <td class="num {{ $p->overdue > 0 ? 'ow-dr' : '' }}"><b>{{ number_format($p->overdue, 2) }}</b></td>
                        <td class="num ow-dr">{{ number_format($p->owed, 2) }} Dr</td>
                        <td class="num">{{ number_format($p->recent, 2) }}</td>
                        <td>{{ $p->last_dispatch ? date('d M Y', strtotime($p->last_dispatch)) : '-' }}</td>
                        <td>{{ $p->phone ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ow-empty">No party owes us for goods dispatched more than {{ $days }} days ago</td></tr>
                @endforelse
            </tbody>
            @if($parties->total())
                <tfoot>
                    <tr>
                        <td>Total ({{ number_format($totals['parties']) }} parties)</td>
                        <td></td>
                        <td class="num ow-dr">{{ number_format($totals['overdue'], 2) }}</td>
                        <td class="num ow-dr">{{ number_format($totals['owed'], 2) }} Dr</td>
                        <td class="num">{{ number_format($totals['recent'], 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    {{ $parties->links() }}

    <p class="small text-muted">
        Owed = the party's ledger balance (Dr); inactive and deleted parties that still owe us are listed with their status.
        Money received clears the oldest bills first, so the part of the balance covered by dispatch bills of the last
        {{ $days }} days (sales-order and agent-order dispatches, less sales returns of those bills) is not yet late; the rest is overdue.
        Only dispatch bills count as not yet late: other debits of the last {{ $days }} days, such as a payment we made
        to the party or a journal-voucher debit, count as overdue straight away.
        Vendors are listed only when we sell to them on agent orders; any other vendor in Dr holds an advance we paid.
        Tap a party to open its ledger statement.
    </p>
@endsection
