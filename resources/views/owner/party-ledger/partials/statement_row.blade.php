{{-- One statement entry (party ledger and bank & cash ledger). $billCol: show the Bill no column. --}}
@php $rb = (float) ($tx->running_balance ?? 0); @endphp
<tr>
    <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
    <td>{{ $tx->type ?? '-' }}</td>
    <td>
        {{ $tx->ref ?? '-' }}
        @if(!empty($tx->slip_url))
            <a href="{{ $tx->slip_url }}" target="_blank" rel="noopener" title="Open slip"><i class="fas fa-paperclip"></i></a>
        @endif
    </td>
    @if($billCol)
        <td>{{ $tx->bill_no ?? '-' }}</td>
    @endif
    <td class="wrap">{{ $tx->description ?? '-' }}</td>
    <td class="num">{{ (float) $tx->debit > 0 ? number_format((float) $tx->debit, 2) : '-' }}</td>
    <td class="num">{{ (float) $tx->credit > 0 ? number_format((float) $tx->credit, 2) : '-' }}</td>
    <td class="num {{ $rb < 0 ? 'ow-dr' : 'ow-cr' }}">{{ number_format(abs($rb), 2) }} {{ $rb >= 0 ? 'Cr' : 'Dr' }}</td>
</tr>
