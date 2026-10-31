@extends('owner.layouts.app')

@section('title', 'Bank & Cash')

@section('content')
    @php
        // Same sign rule as the ledger: negative = Dr (money in the account / in hand), positive = Cr (owed to the bank: OD, CC, loan)
        $drcr = fn($v) => '₹ ' . number_format(abs((float) $v), 2) . ' ' . ((float) $v >= 0 ? 'Cr' : 'Dr');
        $filterCount = collect([request('search'), $typeId, $startDate, $endDate, request('balance_type'), request('min_closing'), request('max_closing')])
            ->filter(fn($v) => (string) $v !== '')->count();
        $fmtDate = fn($d) => \Carbon\Carbon::parse($d)->format('d M Y');
        if ($startDate && $endDate) {
            $periodText = $fmtDate($startDate) . ' to ' . $fmtDate($endDate);
        } elseif ($startDate) {
            $periodText = 'From ' . $fmtDate($startDate);
        } elseif ($endDate) {
            $periodText = 'Up to ' . $fmtDate($endDate);
        } else {
            $periodText = null;
        }
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Bank & Cash',
        'sub' => ($periodText ? 'Period: ' . $periodText . ' · ' : 'Balance today · ') . 'Dr = money held, Cr = owed to the bank (OD / CC / loan)',
        'back' => route('owner.dashboard'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . e(route('owner.party-ledger.index')) . '"><i class="fas fa-users"></i> Parties</a>',
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Accounts', 'value' => number_format($summary['count'])],
        ['label' => 'Total Dr (money held)', 'value' => '₹ ' . number_format($summary['debit'], 2), 'tone' => 'word'],
        ['label' => 'Total Cr (owed to bank)', 'value' => '₹ ' . number_format($summary['credit'], 2), 'tone' => 'red'],
        ['label' => 'Net', 'value' => $drcr($summary['net'])],
        ['label' => 'Opening', 'value' => $drcr($summary['opening']), 'tone' => 'light'],
        ['label' => 'Debit (money in)', 'value' => '₹ ' . number_format($summary['period_debit'], 2), 'tone' => 'yellow'],
        ['label' => 'Credit (money out)', 'value' => '₹ ' . number_format($summary['period_credit'], 2), 'tone' => 'yellow'],
    ]])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.bank-cash-ledger.index') }}">
            <div>
                <label>Account</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Bank, name or number">
            </div>
            <div>
                <label>Type</label>
                <select name="type_id" class="form-control">
                    <option value="">Bank and cash</option>
                    @foreach($masters as $m)
                        <option value="{{ $m->id }}" {{ (string) $typeId === (string) $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>From</label>
                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
            </div>
            <div>
                <label>Balance</label>
                <select name="balance_type" class="form-control">
                    <option value="">Dr and Cr</option>
                    <option value="debit" {{ request('balance_type') === 'debit' ? 'selected' : '' }}>Dr only</option>
                    <option value="credit" {{ request('balance_type') === 'credit' ? 'selected' : '' }}>Cr only</option>
                </select>
            </div>
            <div>
                <label>Closing from ₹</label>
                <input type="number" step="0.01" min="0" class="form-control" name="min_closing" value="{{ request('min_closing') }}">
            </div>
            <div>
                <label>Closing up to ₹</label>
                <input type="number" step="0.01" min="0" class="form-control" name="max_closing" value="{{ request('max_closing') }}">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.bank-cash-ledger.index') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Type</th>
                    <th>A/c no</th>
                    <th class="num">Opening</th>
                    <th class="num">Debit (in)</th>
                    <th class="num">Credit (out)</th>
                    <th class="num">Closing</th>
                </tr>
            </thead>
            <tbody id="bank-rows">
                @if($parties->isEmpty())
                    <tr><td colspan="7" class="ow-empty">No bank or cash accounts found</td></tr>
                @else
                    @include('owner.bank-cash-ledger.partials.ledger_list', ['parties' => $parties, 'startDate' => $startDate, 'endDate' => $endDate])
                @endif
            </tbody>
            @if($summary['count'] > 0)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="2">{{ number_format($summary['count']) }} accounts</td>
                        <td class="num">{{ $drcr($summary['opening']) }}</td>
                        <td class="num">{{ number_format($summary['period_debit'], 2) }}</td>
                        <td class="num">{{ number_format($summary['period_credit'], 2) }}</td>
                        <td class="num">{{ $drcr($summary['net']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($parties->hasMorePages())
        <div class="text-center mb-3">
            <div class="small text-muted mb-1" id="bank-shown">Showing {{ number_format($parties->lastItem()) }} of {{ number_format($parties->total()) }} accounts</div>
            <button type="button" class="ow-btn is-outline" id="bank-more" data-next="{{ $parties->currentPage() + 1 }}">
                <i class="fas fa-chevron-down"></i> Load more
            </button>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    $(function () {
        var total = {{ (int) $parties->total() }};
        $('#bank-more').on('click', function () {
            var $btn = $(this), next = $btn.data('next');
            if (!next) { return; }
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading');
            var url = new URL(window.location.href);
            url.searchParams.set('page', next);
            $.ajax({ url: url.toString(), headers: { 'X-Requested-With': 'XMLHttpRequest' }, dataType: 'json' })
                .done(function (res) {
                    $('#bank-rows').append(res.html);
                    $('#bank-shown').text('Showing ' + $('#bank-rows tr').length.toLocaleString('en-IN') + ' of ' + total.toLocaleString('en-IN') + ' accounts');
                    if (res.next_page) {
                        $btn.data('next', res.next_page).prop('disabled', false).html('<i class="fas fa-chevron-down"></i> Load more');
                    } else {
                        $btn.parent().remove();
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false).html('<i class="fas fa-redo"></i> Try again');
                });
        });
    });
</script>
@endpush
