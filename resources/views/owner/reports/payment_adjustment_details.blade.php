@extends('owner.layouts.app')

@section('title', 'Payment Batch')

@section('content')
    @php
        $isGrouped = !str_starts_with($batchId, 'unique_');
    @endphp

    @include('owner.common.page_head', [
        'title' => $isGrouped ? 'Batch ' . $batchId : 'Payment #' . $first->id,
        'sub' => date('d M Y', strtotime($first->date)) . ' - ' . ($first->type === 'credit' ? 'Credit (received)' : 'Debit (paid out)') . ' - ' . $first->account,
        'back' => route('owner.payment.history.index'),
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Lines', 'value' => number_format($totals['lines'])],
        ['label' => 'Batch total', 'value' => '₹ ' . number_format($totals['amount'], 2), 'tone' => $first->type === 'credit' ? 'word' : 'red'],
        ['label' => 'Paid out (Debit)', 'value' => '₹ ' . number_format($totals['debit'], 2), 'tone' => 'red'],
        ['label' => 'Received (Credit)', 'value' => '₹ ' . number_format($totals['credit'], 2), 'tone' => 'word'],
    ]])

    <div class="ow-section">
        <dl class="ow-kv mb-0">
            <dt>Date</dt><dd>{{ date('d M Y', strtotime($first->date)) }}</dd>
            <dt>Account</dt><dd>{{ $first->account }} <small class="text-muted text-uppercase">{{ $first->payment_mode }}</small></dd>
            <dt>Type</dt>
            <dd>
                @if($first->type === 'credit')
                    <span class="ow-badge green">Credit - received</span>
                @else
                    <span class="ow-badge red">Debit - paid out</span>
                @endif
            </dd>
            <dt>Batch</dt><dd>{{ $isGrouped ? $batchId : '-' }}</dd>
        </dl>
    </div>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Party</th>
                    <th>Master</th>
                    <th>Type</th>
                    <th class="num">Amount</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $l)
                    <tr>
                        <td>
                            @if($l->ledger_url)
                                <a href="{{ $l->ledger_url }}">{{ $l->party }}</a>
                            @else
                                {{ $l->party }}
                            @endif
                        </td>
                        <td>{{ $l->master_name }}</td>
                        <td>
                            @if($l->type === 'credit')
                                <span class="ow-badge green">Credit</span>
                            @else
                                <span class="ow-badge red">Debit</span>
                            @endif
                        </td>
                        <td class="num {{ $l->type === 'credit' ? 'ow-cr' : 'ow-dr' }}"><b>{{ number_format($l->amount, 2) }}</b></td>
                        <td class="wrap">{{ $l->remarks_text !== '' ? $l->remarks_text : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total ({{ number_format($totals['lines']) }} lines)</td>
                    <td></td>
                    <td></td>
                    <td class="num">{{ number_format($totals['amount'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p class="small text-muted">Tap a party to open its ledger statement.</p>
@endsection
