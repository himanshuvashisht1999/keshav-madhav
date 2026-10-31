@extends('owner.layouts.app')

@section('title', 'Party Ledger')

@section('content')
    @php
        // Ledger sign rule: negative = Dr (the party owes us / amount spent), positive = Cr (we owe the party)
        $drcr = fn($v) => '₹ ' . number_format(abs((float) $v), 2) . ' ' . ((float) $v >= 0 ? 'Cr' : 'Dr');
        $isCustomerSel = $selectedMaster && $selectedMaster->model_name === 'App\Models\MasterCustomer';
        $isVendorSel = $selectedMaster && $selectedMaster->model_name === 'App\Models\Vendor';
        $filterCount = collect([
            request('search'), $typeId, $startDate, $endDate, request('balance_type'),
            request('min_closing'), request('max_closing'),
            $isCustomerSel ? $customerType : null, $isVendorSel ? $vendorType : null,
        ])->filter(fn($v) => (string) $v !== '')->count();
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
        'title' => 'Party Ledger',
        'sub' => $periodText
            ? 'Period: ' . $periodText . ' · closing on the To date · Dr = owes us, Cr = we owe'
            : 'Balance of every party today · Dr = owes us (or spent), Cr = we owe',
        'back' => route('owner.dashboard'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . e(route('owner.bank-cash-ledger.index')) . '"><i class="fas fa-university"></i> Bank &amp; Cash</a>',
    ])

    @include('owner.common.totals', ['totals' => array_values(array_filter([
        ['label' => 'Parties', 'value' => number_format($summary['count'])],
        ['label' => 'Total Dr (owed to us)', 'value' => '₹ ' . number_format($summary['debit'], 2), 'tone' => 'red'],
        ['label' => 'Total Cr (we owe)', 'value' => '₹ ' . number_format($summary['credit'], 2), 'tone' => 'word'],
        ['label' => 'Net', 'value' => $drcr($summary['net']), 'tone' => $summary['net'] < 0 ? 'red' : 'word'],
        $summary['dated'] ? ['label' => 'Opening', 'value' => $drcr($summary['opening']), 'tone' => 'light'] : null,
        $summary['dated'] ? ['label' => 'Period debit', 'value' => '₹ ' . number_format($summary['period_debit'], 2), 'tone' => 'yellow'] : null,
        $summary['dated'] ? ['label' => 'Period credit', 'value' => '₹ ' . number_format($summary['period_credit'], 2), 'tone' => 'yellow'] : null,
    ]))])

    @if($summary['agent_note'])
        <div class="small text-muted mb-2">{{ \App\Services\Ledger\PartyLedgerService::AGENT_NOTE }}</div>
    @endif

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.party-ledger.index') }}">
            <div>
                <label>Party name</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search">
            </div>
            <div>
                <label>Type</label>
                <select name="type_id" id="pl-type" class="form-control select2" data-allow-clear="1" data-placeholder="All types">
                    <option value=""></option>
                    <option value="sales_agent" {{ $typeId === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                    @foreach($masters as $m)
                        <option value="{{ $m->id }}" data-model="{{ class_basename($m->model_name) }}" {{ (string) $typeId === (string) $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="pl-customer-type" style="{{ $isCustomerSel ? '' : 'display:none' }}">
                <label>Customer type</label>
                <select name="customer_type" class="form-control">
                    <option value="">All</option>
                    @foreach(['corporate' => 'Corporate', 'domestic' => 'Domestic', 'direct' => 'Domestic (Direct)', 'agent' => 'Domestic (Agent)'] as $k => $label)
                        <option value="{{ $k }}" {{ $isCustomerSel && $customerType === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div id="pl-vendor-type" style="{{ $isVendorSel ? '' : 'display:none' }}">
                <label>Vendor type</label>
                <select name="vendor_type" class="form-control">
                    <option value="">All</option>
                    <option value="direct" {{ $isVendorSel && $vendorType === 'direct' ? 'selected' : '' }}>Direct</option>
                    <option value="agent" {{ $isVendorSel && $vendorType === 'agent' ? 'selected' : '' }}>Fabric Purchase Agent</option>
                    @foreach($purchaseAgents as $pa)
                        <option value="pa_{{ $pa->id }}" {{ $isVendorSel && ($vendorType === 'pa_' . $pa->id || (string) $vendorType === (string) $pa->id) ? 'selected' : '' }}>{{ $pa->name }}</option>
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
                    <option value="debit" {{ request('balance_type') === 'debit' ? 'selected' : '' }}>Dr only (owes us)</option>
                    <option value="credit" {{ request('balance_type') === 'credit' ? 'selected' : '' }}>Cr only (we owe)</option>
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
                <a class="ow-btn is-outline" href="{{ route('owner.party-ledger.index') }}">Reset</a>
            </div>
        </form>
    </details>
    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Party</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th class="num">Opening</th>
                    <th class="num">Debit</th>
                    <th class="num">Credit</th>
                    <th class="num">Closing</th>
                </tr>
            </thead>
            <tbody id="party-rows">
                @if($parties->isEmpty())
                    <tr><td colspan="7" class="ow-empty">No parties found</td></tr>
                @else
                    @include('owner.party-ledger.partials.ledger_list', ['parties' => $parties, 'startDate' => $startDate, 'endDate' => $endDate])
                @endif
            </tbody>
            @if($summary['count'] > 0)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="2">{{ number_format($summary['count']) }} {{ $summary['count'] === 1 ? 'party' : 'parties' }}</td>
                        @if($summary['dated'])
                            <td class="num">{{ $drcr($summary['opening']) }}</td>
                            <td class="num">{{ number_format($summary['period_debit'], 2) }}</td>
                            <td class="num">{{ number_format($summary['period_credit'], 2) }}</td>
                        @else
                            <td colspan="3" class="num">closing of all parties</td>
                        @endif
                        <td class="num {{ $summary['net'] < 0 ? 'ow-dr' : 'ow-cr' }}">{{ $drcr($summary['net']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($parties->hasMorePages())
        <div class="text-center mb-3">
            <div class="small text-muted mb-1" id="party-shown">Showing {{ number_format($parties->lastItem()) }} of {{ number_format($parties->total()) }} parties</div>
            <button type="button" class="ow-btn is-outline" id="party-more" data-next="{{ $parties->currentPage() + 1 }}">
                <i class="fas fa-chevron-down"></i> Load more
            </button>
        </div>
    @endif
    <p class="small text-muted mb-3">
        Every active party is listed, and an inactive or deleted party too while its balance is not 0 (marked Inactive / Deleted, counted in the totals), as on the admin Party Ledger.
        @if($summary['not_active'] > 0)
            This list has {{ number_format($summary['not_active']) }} such {{ $summary['not_active'] === 1 ? 'party' : 'parties' }}.
        @endif
    </p>
@endsection

@push('scripts')
<script>
    $(function () {
        // Customer / vendor type filters only apply to that party type (a hidden one is cleared, so it is not sent)
        $('#pl-type').on('change', function () {
            var model = String($(this).find('option:selected').data('model') || '');
            $('#pl-customer-type').toggle(model === 'MasterCustomer');
            $('#pl-vendor-type').toggle(model === 'Vendor');
            if (model !== 'MasterCustomer') { $('#pl-customer-type select').val(''); }
            if (model !== 'Vendor') { $('#pl-vendor-type select').val(''); }
        });

        // Load more: appends the next parties (the totals above already cover every party of the filter)
        var total = {{ (int) $parties->total() }};
        $('#party-more').on('click', function () {
            var $btn = $(this), next = $btn.data('next');
            if (!next) { return; }
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading');
            var url = new URL(window.location.href);
            url.searchParams.set('page', next);
            $.ajax({ url: url.toString(), headers: { 'X-Requested-With': 'XMLHttpRequest' }, dataType: 'json' })
                .done(function (res) {
                    $('#party-rows').append(res.html);
                    $('#party-shown').text('Showing ' + $('#party-rows tr').length.toLocaleString('en-IN') + ' of ' + total.toLocaleString('en-IN') + ' parties');
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
