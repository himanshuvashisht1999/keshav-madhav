@extends('owner.layouts.app')

@section('title', 'Statement')

@section('content')
    @php
        $drcr = fn($v) => '₹ ' . number_format(abs((float) $v), 2) . ' ' . ((float) $v >= 0 ? 'Cr' : 'Dr');
        $isAgent = $type === 'sales_agent';
        $partyWise = $isAgent && ($viewMode ?? 'mix') === 'party_wise' && !empty($groupedLedgers);
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
        $pdfUrl = route('owner.party-ledger.download', array_merge($query, ['type' => $type, 'id' => $party->id]));
        $filterKeys = ['start_date', 'end_date', 'transaction_type', 'ref_no', 'customer_id', 'view_mode'];
        $filterCount = collect($filterKeys)->filter(fn($k) => (string) request($k) !== '' && !($k === 'view_mode' && request($k) === 'mix'))->count();
        $lastBalance = $transactions->isEmpty() ? $opening : (float) $transactions->last()->running_balance;
        $storedBalance = method_exists($party, 'getOriginal') ? $party->getOriginal('balance') : null;
        $sub = ucwords(str_replace('_', ' ', $type)) . (!empty($party->phone) ? ' · ' . $party->phone : '') . ' · ' . $periodText;
    @endphp

    @include('owner.common.page_head', [
        'title' => $party->name,
        'sub' => $sub,
        'back' => route('owner.party-ledger.index', $isAgent ? ['type_id' => 'sales_agent'] : []),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . e($pdfUrl) . '"'
            . ($pdfTooBig ? ' title="Too many entries for one PDF: choose From / To dates first"' : '')
            . '><i class="fas fa-file-pdf"></i> PDF</a>',
    ])

    {{-- Same status badge as the list (status 1 = active, 0 = inactive, 3 = deleted) --}}
    @if((int) ($party->status ?? 1) !== 1)
        @php $deleted = (int) ($party->status ?? 1) === 3; @endphp
        <div class="small text-muted mb-2">
            <span class="ow-badge {{ $deleted ? 'red' : 'grey' }}">{{ $deleted ? 'Deleted' : 'Inactive' }}</span>
            This party is {{ $deleted ? 'deleted' : 'inactive' }}; its ledger is shown as usual.
        </div>
    @endif

    @if($storedBalance !== null && empty($endDate) && abs((float) $storedBalance - $closing) >= 0.01)
        <div class="ow-alert err">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            The saved balance of this account is {{ $drcr($storedBalance) }}, but its ledger closes at {{ $drcr($closing) }}
            (difference ₹ {{ number_format((float) $storedBalance - $closing, 2) }}). The admin balance check lists it for correction.
        </div>
    @endif

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Opening', 'value' => $drcr($opening), 'tone' => 'light'],
        ['label' => 'Period debit', 'value' => '₹ ' . number_format($periodDebit, 2), 'tone' => 'red'],
        ['label' => 'Period credit', 'value' => '₹ ' . number_format($periodCredit, 2), 'tone' => 'word'],
        ['label' => 'Closing', 'value' => $drcr($closing), 'tone' => $closing < 0 ? 'red' : 'word'],
        ['label' => 'Entries', 'value' => number_format($transactions->count()), 'tone' => 'yellow'],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.party-ledger.show', ['type' => $type, 'id' => $party->id]) }}">
            <div>
                <label>From</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div>
                <label>Entry type</label>
                <select name="transaction_type" class="form-control">
                    <option value="">All</option>
                    @foreach($availableTypes ?? [] as $t)
                        <option value="{{ $t }}" {{ strcasecmp((string) request('transaction_type'), $t) === 0 ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Ref / bill no</label>
                <input type="text" class="form-control" name="ref_no" value="{{ request('ref_no') }}" placeholder="Search">
            </div>
            @if($isAgent)
                <div>
                    <label>Shop</label>
                    <select name="customer_id" class="form-control select2" data-allow-clear="1" data-placeholder="All shops">
                        <option value=""></option>
                        @foreach($shops ?? [] as $shop)
                            <option value="{{ $shop->id }}" {{ (string) request('customer_id') === (string) $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>View</label>
                    <select name="view_mode" class="form-control">
                        <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>All shops together</option>
                        <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Shop by shop</option>
                    </select>
                </div>
            @endif
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.party-ledger.show', ['type' => $type, 'id' => $party->id]) }}">Reset</a>
            </div>
        </form>
    </details>

    @if($partyWise)
        {{-- One table per shop (sales agent, "Shop by shop") --}}
        @php $hidden = 0; @endphp
        @foreach($groupedLedgers as $ledger)
            @php
                $shopTx = collect($ledger->transactions);
                if ($shopTx->isEmpty() && abs((float) $ledger->opening_balance) < 0.005 && abs((float) $ledger->closing_balance) < 0.005) { $hidden++; continue; }
            @endphp
            <div class="ow-table-title">
                <i class="fas fa-store"></i> {{ $ledger->shop->name }}
                <span class="small text-muted">· Opening {{ $drcr($ledger->opening_balance) }} · Closing {{ $drcr($ledger->closing_balance) }}</span>
            </div>
            <div class="ow-table-wrap">
                <table class="erp-table sticky-first">
                    <thead>
                        <tr>
                            <th>Date</th><th>Type</th><th>Ref</th><th>Particulars</th>
                            <th class="num">Debit</th><th class="num">Credit</th><th class="num">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $startDate ? $fmtDate($startDate) : '-' }}</td>
                            <td><span class="ow-badge yellow">Opening</span></td>
                            <td>-</td>
                            <td class="wrap"><b>Opening balance brought forward</b></td>
                            <td class="num">-</td><td class="num">-</td>
                            <td class="num {{ $ledger->opening_balance < 0 ? 'ow-dr' : 'ow-cr' }}"><b>{{ $drcr($ledger->opening_balance) }}</b></td>
                        </tr>
                        @forelse($shopTx as $tx)
                            @include('owner.party-ledger.partials.statement_row', ['tx' => $tx, 'billCol' => false])
                        @empty
                            <tr><td colspan="7" class="ow-empty">No entries in this period</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td><td colspan="3"></td>
                            <td class="num">{{ number_format($shopTx->sum('debit'), 2) }}</td>
                            <td class="num">{{ number_format($shopTx->sum('credit'), 2) }}</td>
                            <td class="num">{{ $drcr($ledger->closing_balance) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach
        @if($hidden)
            <div class="small text-muted mb-3">{{ number_format($hidden) }} shops with no entries and a zero balance are not shown.</div>
        @endif
    @else
        <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
        <div class="ow-table-wrap">
            <table class="erp-table sticky-first">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Ref</th>
                        <th>Bill no</th>
                        <th>Particulars</th>
                        <th class="num">Debit</th>
                        <th class="num">Credit</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if($entries->currentPage() === 1)
                            <td>{{ $startDate ? $fmtDate($startDate) : '-' }}</td>
                            <td><span class="ow-badge yellow">Opening</span></td>
                            <td>-</td><td>-</td>
                            <td class="wrap"><b>Opening balance brought forward</b></td>
                        @else
                            <td>-</td>
                            <td><span class="ow-badge grey">B/F</span></td>
                            <td>-</td><td>-</td>
                            <td class="wrap"><b>Balance brought forward from page {{ $entries->currentPage() - 1 }}</b></td>
                        @endif
                        <td class="num">-</td><td class="num">-</td>
                        <td class="num {{ $broughtForward < 0 ? 'ow-dr' : 'ow-cr' }}"><b>{{ $drcr($broughtForward) }}</b></td>
                    </tr>
                    @forelse($entries as $tx)
                        @include('owner.party-ledger.partials.statement_row', ['tx' => $tx, 'billCol' => true])
                    @empty
                        <tr><td colspan="8" class="ow-empty">No entries in this period</td></tr>
                    @endforelse
                </tbody>
                @if($transactions->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td colspan="4">{{ number_format($transactions->count()) }} entries{{ $entries->hasPages() ? ' (all pages)' : '' }}</td>
                            <td class="num">{{ number_format($periodDebit, 2) }}</td>
                            <td class="num">{{ number_format($periodCredit, 2) }}</td>
                            <td class="num {{ $lastBalance < 0 ? 'ow-dr' : 'ow-cr' }}">{{ $drcr($lastBalance) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if($entries->hasPages())
            <div class="small text-muted">Entries {{ number_format($entries->firstItem()) }}-{{ number_format($entries->lastItem()) }} of {{ number_format($entries->total()) }}</div>
            {{ $entries->onEachSide(1)->links() }}
        @endif
    @endif
@endsection
