@extends('owner.layouts.app')

@section('title', 'Payment History')

@section('content')
    @php
        $filterCount = collect([$f['from_date'], $f['to_date'], $f['type'], $f['account_id'], $f['amount_from'], $f['amount_to'], $f['search']])
            ->filter(fn ($v) => $v !== null && $v !== '')->count();
        $accountValue = $f['account'] !== '' ? $f['account'] : ($f['account_id'] !== null ? 'bank:' . $f['account_id'] : '');
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Payment History',
        'sub' => 'Every payment entered on Payment Adjust, newest entry first',
        'back' => route('owner.dashboard'),
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Batches', 'value' => number_format($totals['batches'])],
        ['label' => 'Lines', 'value' => number_format($totals['lines'])],
        ['label' => 'Paid out (Debit)', 'value' => '₹ ' . number_format($totals['debit'], 2), 'tone' => 'red'],
        ['label' => 'Received (Credit)', 'value' => '₹ ' . number_format($totals['credit'], 2), 'tone' => 'word'],
        ['label' => 'Net (Credit - Debit)', 'value' => '₹ ' . number_format($totals['credit'] - $totals['debit'], 2), 'tone' => ($totals['credit'] - $totals['debit']) < 0 ? 'red' : 'yellow'],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.payment.history.index') }}">
            <div>
                <label>From</label>
                <input type="date" class="form-control" name="from_date" value="{{ $f['from_date'] }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control" name="to_date" value="{{ $f['to_date'] }}">
            </div>
            <div>
                <label>Type</label>
                <select class="form-control" name="type">
                    <option value="">All types</option>
                    <option value="debit" {{ $f['type'] === 'debit' ? 'selected' : '' }}>Debit (paid out)</option>
                    <option value="credit" {{ $f['type'] === 'credit' ? 'selected' : '' }}>Credit (received)</option>
                </select>
            </div>
            <div>
                <label>Account</label>
                <select class="form-control select2" name="account" data-allow-clear="1" data-placeholder="All accounts">
                    <option value=""></option>
                    @if($bankAccounts->count())
                        <optgroup label="Bank accounts">
                            @foreach($bankAccounts as $acc)
                                <option value="bank:{{ $acc->id }}" {{ $accountValue === 'bank:' . $acc->id ? 'selected' : '' }}>{{ $acc->bank_name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if($cashAccounts->count())
                        <optgroup label="Cash accounts">
                            @foreach($cashAccounts as $acc)
                                <option value="cash:{{ $acc->id }}" {{ $accountValue === 'cash:' . $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
            <div>
                <label>Amount from</label>
                <input type="number" step="0.01" class="form-control" name="amount_from" value="{{ $f['amount_from'] }}" placeholder="Min">
            </div>
            <div>
                <label>Amount to</label>
                <input type="number" step="0.01" class="form-control" name="amount_to" value="{{ $f['amount_to'] }}" placeholder="Max">
            </div>
            <div>
                <label>Search</label>
                <input type="text" class="form-control" name="search" value="{{ $f['search'] }}" placeholder="Party, batch, note, amount">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.payment.history.index') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Date / batch</th>
                    <th>Party</th>
                    <th>Master</th>
                    <th>Type</th>
                    <th>Account</th>
                    <th class="num">Lines</th>
                    <th class="num">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                    <tr>
                        <td>
                            <a href="{{ route('owner.payment-adjustment.show', $b->batch_key) }}">{{ date('d M Y', strtotime($b->date)) }}</a>
                            <small class="d-block text-muted">{{ $b->is_grouped ? $b->batch_key : '#' . $b->last_id }}</small>
                        </td>
                        <td class="wrap">
                            {{ $b->parties->first() }}
                            @if($b->parties->count() > 1)
                                <span class="ow-badge grey" title="{{ $b->parties->implode(', ') }}">+{{ $b->parties->count() - 1 }} more</span>
                            @endif
                        </td>
                        <td>{{ $b->masters->implode(', ') }}</td>
                        <td>
                            @if($b->type === 'credit')
                                <span class="ow-badge green">Credit - received</span>
                            @else
                                <span class="ow-badge red">Debit - paid out</span>
                            @endif
                        </td>
                        <td>{{ $b->account }} <small class="text-muted text-uppercase">{{ $b->payment_mode }}</small></td>
                        <td class="num">{{ number_format($b->line_count) }}</td>
                        <td class="num {{ $b->type === 'credit' ? 'ow-cr' : 'ow-dr' }}"><b>{{ number_format($b->total, 2) }}</b></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ow-empty">No payments found for these filters</td></tr>
                @endforelse
            </tbody>
            @if($batches->total())
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="4">{{ number_format($totals['batches']) }} batches - Debit (paid out) / Credit (received)</td>
                        <td class="num">{{ number_format($totals['lines']) }}</td>
                        <td class="num"><span class="ow-dr">{{ number_format($totals['debit'], 2) }} Dr</span><br><span class="ow-cr">{{ number_format($totals['credit'], 2) }} Cr</span></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    {{ $batches->links() }}

    <p class="small text-muted">
        One row per batch saved on Payment Adjust. Debit = money paid out of the account, Credit = money received into it.
        Tap the date to see every line of the batch.
    </p>
@endsection
