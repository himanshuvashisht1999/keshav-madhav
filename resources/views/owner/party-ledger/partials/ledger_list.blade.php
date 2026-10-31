{{-- Rows of the owner party list (also returned by "Load more"). Figures = PartyLedgerService, as the admin list. --}}
@foreach($parties as $party)
    @php
        $open = (float) ($party->opening_balance ?? 0);
        $bal = (float) ($party->balance ?? 0);
        $url = route('owner.party-ledger.show', array_filter([
            'type' => $party->party_type,
            'id' => $party->id,
            'start_date' => $startDate ?? null,
            'end_date' => $endDate ?? null,
        ]));
    @endphp
    <tr>
        <td class="wrap">
            <a href="{{ $url }}">{{ $party->name }}</a>
            @if((int) ($party->status ?? 1) !== 1)
                <span class="ow-badge {{ (int) ($party->status ?? 1) === 3 ? 'red' : 'grey' }}">{{ (int) ($party->status ?? 1) === 3 ? 'Deleted' : 'Inactive' }}</span>
            @endif
        </td>
        <td><span class="ow-badge type">{{ ucwords(str_replace('_', ' ', $party->party_type)) }}</span></td>
        <td>{{ $party->phone ?? '-' }}</td>
        <td class="num">{{ number_format(abs($open), 2) }} {{ $open >= 0 ? 'Cr' : 'Dr' }}</td>
        <td class="num">{{ number_format((float) ($party->period_debit ?? 0), 2) }}</td>
        <td class="num">{{ number_format((float) ($party->period_credit ?? 0), 2) }}</td>
        <td class="num {{ $bal < 0 ? 'ow-dr' : 'ow-cr' }}"><b>{{ number_format(abs($bal), 2) }} {{ $bal >= 0 ? 'Cr' : 'Dr' }}</b></td>
    </tr>
@endforeach
