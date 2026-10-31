@extends('owner.layouts.app')

@section('title', 'Bank & Cash statement')

@section('content')
    @php
        $drcr = fn($v) => '₹ ' . number_format(abs((float) $v), 2) . ' ' . ((float) $v >= 0 ? 'Cr' : 'Dr');
        $closing = (float) $party->balance;
        $opening = (float) $openingBalAmount;
        $fmtDate = fn($d) => \Carbon\Carbon::parse($d)->format('d M Y');
        if ($startDate && $endDate) {
            $periodText = $fmtDate($startDate) . ' to ' . $fmtDate($endDate);
        } elseif ($startDate) {
            $periodText = 'From ' . $fmtDate($startDate) . ' to today';
        } elseif ($endDate) {
            $periodText = 'Up to ' . $fmtDate($endDate);
        } else {
            $periodText = 'All dates';
        }
        $query = \Illuminate\Support\Arr::except(request()->query(), ['page']);
        $pdfUrl = route('owner.bank-cash-ledger.download', array_merge($query, ['type' => $type, 'id' => $party->id]));
        $filterCount = collect(['start_date', 'end_date'])->filter(fn($k) => (string) request($k) !== '')->count();
        $lastBalance = $transactions->isEmpty() ? $opening : (float) $transactions->last()->running_balance;
        $storedBalance = $party->getOriginal('balance');
        $sub = ucwords($type) . (!empty($party->account_number) ? ' · A/c ' . $party->account_number : '') . ' · ' . $periodText;
    @endphp

    @include('owner.common.page_head', [
        'title' => $party->name,
        'sub' => $sub,
        'back' => route('owner.bank-cash-ledger.index'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . e($pdfUrl) . '"'
            . ($pdfTooBig ? ' title="Too many entries for one PDF: choose From / To dates first"' : '')
            . '><i class="fas fa-file-pdf"></i> PDF</a>',
    ])

    @if($storedBalance !== null && empty($endDate) && abs((float) $storedBalance - $closing) >= 0.01)
        <div class="ow-alert err">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            The saved balance of this account is {{ $drcr($storedBalance) }}, but its ledger closes at {{ $drcr($closing) }}
            (difference ₹ {{ number_format((float) $storedBalance - $closing, 2) }}). The admin balance check lists it for correction.
        </div>
    @endif

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Opening', 'value' => $drcr($opening), 'tone' => 'light'],
        ['label' => 'Debit (money in)', 'value' => '₹ ' . number_format($periodDebit, 2), 'tone' => 'word'],
        ['label' => 'Credit (money out)', 'value' => '₹ ' . number_format($periodCredit, 2), 'tone' => 'red'],
        ['label' => 'Closing', 'value' => $drcr($closing)],
        ['label' => 'Entries', 'value' => number_format($transactions->count()), 'tone' => 'yellow'],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.bank-cash-ledger.show', ['type' => $type, 'id' => $party->id]) }}">
            <div>
                <label>From</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.bank-cash-ledger.show', ['type' => $type, 'id' => $party->id]) }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Ref</th>
                    <th>Particulars</th>
                    <th class="num">Debit (in)</th>
                    <th class="num">Credit (out)</th>
                    <th class="num">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @if($entries->currentPage() === 1)
                        <td>{{ $startDate ? $fmtDate($startDate) : '-' }}</td>
                        <td><span class="ow-badge yellow">Opening</span></td>
                        <td>-</td>
                        <td class="wrap"><b>Opening balance brought forward</b></td>
                    @else
                        <td>-</td>
                        <td><span class="ow-badge grey">B/F</span></td>
                        <td>-</td>
                        <td class="wrap"><b>Balance brought forward from page {{ $entries->currentPage() - 1 }}</b></td>
                    @endif
                    <td class="num">-</td><td class="num">-</td>
                    <td class="num"><b>{{ $drcr($broughtForward) }}</b></td>
                </tr>
                @forelse($entries as $tx)
                    @include('owner.party-ledger.partials.statement_row', ['tx' => $tx, 'billCol' => false])
                @empty
                    <tr><td colspan="7" class="ow-empty">No entries in this period</td></tr>
                @endforelse
            </tbody>
            @if($transactions->isNotEmpty())
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="3">{{ number_format($transactions->count()) }} entries{{ $entries->hasPages() ? ' (all pages)' : '' }}</td>
                        <td class="num">{{ number_format($periodDebit, 2) }}</td>
                        <td class="num">{{ number_format($periodCredit, 2) }}</td>
                        <td class="num">{{ $drcr($lastBalance) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if($entries->hasPages())
        <div class="small text-muted">Entries {{ number_format($entries->firstItem()) }}-{{ number_format($entries->lastItem()) }} of {{ number_format($entries->total()) }}</div>
        {{ $entries->onEachSide(1)->links() }}
    @endif
@endsection
