{{-- Rows of the owner bank & cash list (also returned by "Load more"). Figures = BankLedgerService, as the admin list. --}}
@foreach($parties as $acc)
    @php
        $open = (float) ($acc->opening_balance ?? 0);
        $bal = (float) ($acc->balance ?? 0);
        $url = route('owner.bank-cash-ledger.show', array_filter([
            'type' => $acc->party_type,
            'id' => $acc->id,
            'start_date' => $startDate ?? null,
            'end_date' => $endDate ?? null,
        ]));
    @endphp
    <tr>
        <td class="wrap"><a href="{{ $url }}">{{ $acc->name }}</a></td>
        <td><span class="ow-badge grey">{{ ucwords($acc->party_type) }}</span></td>
        <td>{{ $acc->account_number ?? '-' }}</td>
        <td class="num">{{ number_format(abs($open), 2) }} {{ $open >= 0 ? 'Cr' : 'Dr' }}</td>
        <td class="num">{{ number_format((float) ($acc->period_debit ?? 0), 2) }}</td>
        <td class="num">{{ number_format((float) ($acc->period_credit ?? 0), 2) }}</td>
        <td class="num"><b>{{ number_format(abs($bal), 2) }} {{ $bal >= 0 ? 'Cr' : 'Dr' }}</b></td>
    </tr>
@endforeach
