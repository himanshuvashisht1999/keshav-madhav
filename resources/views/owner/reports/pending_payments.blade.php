@extends('owner.layouts.app')

@section('title', 'Pending Payments')

@section('content')
    @php
        $isReceivable = $tab === 'receivable';
        $filterCount = ($search !== '' ? 1 : 0) + ($type !== '' ? 1 : 0);
        $chips = [
            ['label' => 'Parties', 'value' => number_format($totals['parties'])],
            ['label' => $isReceivable ? 'They owe us (Dr)' : 'We owe (Cr)', 'value' => '₹ ' . number_format($totals['amount'], 2), 'tone' => $isReceivable ? 'red' : 'yellow'],
        ];
        foreach ($totals['by_type'] as $t) {
            $chips[] = ['label' => $t->label . ' (' . number_format($t->count) . ')', 'value' => '₹ ' . number_format($t->amount, 2), 'tone' => 'light'];
        }
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Pending Payments',
        'sub' => $isReceivable ? 'Customers and vendors who owe us money (ledger balance Dr)' : 'Parties we owe money to (ledger balance Cr)',
        'back' => route('owner.dashboard'),
    ])

    @include('owner.common.totals', ['totals' => $chips])

    <nav class="ow-tabs">
        <a class="{{ $isReceivable ? 'active' : '' }}" href="{{ route('owner.payment.pending.index', ['tab' => 'receivable']) }}"><i class="fas fa-arrow-down"></i> They owe us</a>
        <a class="{{ $isReceivable ? '' : 'active' }}" href="{{ route('owner.payment.pending.index', ['tab' => 'payable']) }}"><i class="fas fa-arrow-up"></i> We owe</a>
    </nav>

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.payment.pending.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label>Party name</label>
                <input type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Search name">
            </div>
            <div>
                <label>Party type</label>
                <select class="form-control select2" name="type" data-allow-clear="1" data-placeholder="All types">
                    <option value=""></option>
                    @foreach($typeOptions as $key => $label)
                        <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.payment.pending.index', ['tab' => $tab]) }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Party</th>
                    <th>Type</th>
                    <th class="num">{{ $isReceivable ? 'They owe us' : 'We owe' }}</th>
                    <th>{{ $isReceivable ? 'Last dispatch' : 'Last bill' }}</th>
                    <th>Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parties as $p)
                    <tr>
                        <td class="wrap">
                            <a href="{{ route('owner.party-ledger.show', ['type' => $p->type, 'id' => $p->id]) }}">{{ $p->name }}</a>
                            @if($p->status !== 1)
                                <span class="ow-badge {{ $p->status === 3 ? 'red' : 'grey' }}">{{ $p->status === 3 ? 'Deleted' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td><span class="ow-badge type">{{ $p->type_label }}</span></td>
                        <td class="num {{ $isReceivable ? 'ow-dr' : 'ow-cr' }}"><b>{{ number_format($p->amount, 2) }} {{ $isReceivable ? 'Dr' : 'Cr' }}</b></td>
                        <td>{{ $p->last_date ? date('d M Y', strtotime($p->last_date)) : '-' }}</td>
                        <td>{{ $p->phone ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ow-empty">{{ $isReceivable ? 'Nobody owes us money' : 'We owe nobody money' }} for these filters</td></tr>
                @endforelse
            </tbody>
            @if($parties->total())
                <tfoot>
                    <tr>
                        <td>Total ({{ number_format($totals['parties']) }} parties)</td>
                        <td></td>
                        <td class="num {{ $isReceivable ? 'ow-dr' : 'ow-cr' }}">{{ number_format($totals['amount'], 2) }} {{ $isReceivable ? 'Dr' : 'Cr' }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    {{ $parties->links() }}

    <p class="small text-muted">
        Amounts are the party ledger balances (the same figures as the Party Ledger statements).
        @if($isReceivable)
            Dr = the party owes us. For a vendor this is usually an advance we paid.
        @else
            Cr = we owe the party. For a customer this is money received in advance. The partners' capital is not included.
        @endif
        Inactive and deleted parties are included while their balance is not 0.
        Tap a party to open its ledger statement.
    </p>
@endsection
