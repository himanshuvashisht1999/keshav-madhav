@extends('owner.layouts.app')

@section('title', 'WIP Complete')

@section('content')
    @php
        $filterCount = ($customerId ? 1 : 0) + ($status !== '' ? 1 : 0) + (count($selected['order_ids']) ? 1 : 0)
            + (count($selected['design_nos']) ? 1 : 0) + (count($selected['lot_nos']) ? 1 : 0);
        $colCount = 4 + ($showCustomer ? 1 : 0) + count($stages) + 3;
    @endphp

    @include('owner.common.page_head', [
        'title' => 'WIP Complete',
        'sub' => 'Pieces at every stage of each lot: still there (WIP), received (In) and finished (Done)',
        'back' => route('owner.dashboard'),
        'actions' => null,
    ])

    @include('owner.common.totals', ['totals' => array_values(array_filter([
        ['label' => 'Lots', 'value' => number_format($totals['lots'])],
        ['label' => 'Pieces cut', 'value' => number_format($totals['pieces'])],
        ['label' => 'WIP pcs (Stitching to Packing)', 'value' => number_format($totals['wip']), 'tone' => 'yellow'],
        $totals['parts'] > 0 ? ['label' => 'Parts at Printing / Godam', 'value' => number_format($totals['parts']), 'tone' => 'light'] : null,
        ['label' => 'Packed', 'value' => number_format($totals['packed']), 'tone' => 'light'],
        ['label' => 'Dispatched', 'value' => number_format($totals['dispatched']), 'tone' => 'word'],
        $totals['in_stock'] > 0 ? ['label' => 'Packed to stock', 'value' => number_format($totals['in_stock']), 'tone' => 'light'] : null,
        ['label' => 'Late lots', 'value' => number_format($totals['late']), 'tone' => $totals['late'] > 0 ? 'red' : null],
    ]))])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.wip-complete') }}" id="wip-filters">
            <div>
                <label>Customer</label>
                <select name="customer_id" id="wip-customer" class="form-control select2" data-allow-clear="1" data-placeholder="All customers">
                    <option value=""></option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ $customerId === (int) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($customerId)
                <div>
                    <label>Orders</label>
                    <select name="order_ids[]" id="wip-orders" class="form-control select2" multiple data-placeholder="All orders">
                        @foreach($options['orders'] as $id => $sku)
                            <option value="{{ $id }}" {{ in_array($id, $selected['order_ids']) ? 'selected' : '' }}>{{ $sku }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Designs</label>
                    <select name="design_nos[]" id="wip-designs" class="form-control select2" multiple data-placeholder="All designs">
                        @foreach($options['designs'] as $designNo => $orderIds)
                            <option value="{{ $designNo }}" data-orders="{{ implode(',', $orderIds) }}" {{ in_array((string) $designNo, $selected['design_nos']) ? 'selected' : '' }}>{{ $designNo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Lots</label>
                    <select name="lot_nos[]" id="wip-lots" class="form-control select2" multiple data-placeholder="All lots">
                        @foreach($options['lots'] as $lotNo => $info)
                            <option value="{{ $lotNo }}" data-order="{{ $info['order_id'] }}" data-design="{{ $info['design'] }}" {{ in_array((string) $lotNo, $selected['lot_nos']) ? 'selected' : '' }}>{{ $lotNo }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="" {{ $status === '' ? 'selected' : '' }}>All lots</option>
                    <option value="running" {{ $status === 'running' ? 'selected' : '' }}>In production</option>
                    <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.wip-complete') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-title">
        <span>Stage figures:</span>
        <button type="button" class="ow-btn is-sm wip-mode" data-mode="wip" title="Pieces still at the stage">WIP</button>
        <button type="button" class="ow-btn is-sm is-outline wip-mode" data-mode="in" title="Pieces the stage received">In</button>
        <button type="button" class="ow-btn is-sm is-outline wip-mode" data-mode="done" title="Pieces the stage finished and sent on">Done</button>
    </div>
    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every stage</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Order</th>
                    @if($showCustomer)
                        <th>Customer</th>
                    @endif
                    <th>Design</th>
                    <th class="num">Cut</th>
                    @foreach($stages as $stage)
                        <th class="num" title="{{ $stage->name }}">{!! wordwrap(e($stage->name), 10, '<br>') !!}</th>
                    @endforeach
                    <th class="num">Packed</th>
                    <th class="num">Dispatched</th>
                    <th>Now at</th>
                </tr>
            </thead>
            <tbody id="wip-rows">
                @if($data->isEmpty())
                    <tr><td colspan="{{ $colCount }}" class="ow-empty">No lots found</td></tr>
                @else
                    @include('owner.reports.partials.wip_complete_rows', ['data' => $data, 'stages' => $stages, 'showCustomer' => $showCustomer])
                @endif
            </tbody>
            @if($data->total() > 0)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="{{ $showCustomer ? 3 : 2 }}">{{ number_format($totals['lots']) }} lots{{ $data->total() > $data->count() ? ' (all pages)' : '' }}</td>
                        <td class="num">{{ number_format($totals['pieces']) }}</td>
                        @foreach($stages as $stage)
                            @php $st = $stageTotals[(int) $stage->id]; @endphp
                            <td class="num wip-t" data-in="{{ $st['in'] }}" data-done="{{ $st['done'] }}" data-wip="{{ $st['wip'] }}">{{ number_format($st['wip']) }}</td>
                        @endforeach
                        <td class="num">{{ number_format($totals['packed']) }}</td>
                        <td class="num">{{ number_format($totals['dispatched']) }}</td>
                        <td>{{ number_format($totals['late']) }} late</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($data->hasMorePages())
        <div class="text-center mb-3" id="wip-more-box">
            <div class="small text-muted mb-1" id="wip-shown">Showing {{ number_format($data->lastItem()) }} of {{ number_format($data->total()) }} lots</div>
            <button type="button" class="ow-btn is-outline" id="wip-more" data-next="{{ $data->currentPage() + 1 }}">
                <i class="fas fa-chevron-down"></i> Load more
            </button>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    $(function () {
        // a new customer: forget the old order / design / lot choices (they belong to the previous customer) and reload
        $('#wip-customer').on('change', function () {
            var form = document.getElementById('wip-filters');
            $(form).find('select[multiple]').val([]);
            form.submit();
        });

        // designs follow the chosen orders, lots follow the chosen orders and designs
        var $orders = $('#wip-orders'), $designs = $('#wip-designs'), $lots = $('#wip-lots');
        if ($orders.length) {
            var allDesigns = $designs.find('option').map(function () {
                return { v: this.value, t: $(this).text(), orders: String($(this).attr('data-orders') || '').split(',') };
            }).get();
            var allLots = $lots.find('option').map(function () {
                return { v: this.value, t: $(this).text(), order: String($(this).attr('data-order') || ''), design: String($(this).attr('data-design') || '') };
            }).get();
            var rebuildLots = function () {
                var ords = $orders.val() || [], des = $designs.val() || [], keep = $lots.val() || [];
                $lots.empty();
                allLots.forEach(function (o) {
                    if (ords.length && ords.indexOf(o.order) === -1) { return; }
                    if (des.length && des.indexOf(o.design) === -1) { return; }
                    var opt = new Option(o.t, o.v, false, keep.indexOf(o.v) !== -1);
                    $(opt).attr({ 'data-order': o.order, 'data-design': o.design });
                    $lots.append(opt);
                });
                $lots.trigger('change.select2');
            };
            var rebuildDesigns = function () {
                var ords = $orders.val() || [], keep = $designs.val() || [];
                $designs.empty();
                allDesigns.forEach(function (o) {
                    if (ords.length && !o.orders.some(function (id) { return ords.indexOf(id) !== -1; })) { return; }
                    var opt = new Option(o.t, o.v, false, keep.indexOf(o.v) !== -1);
                    $(opt).attr('data-orders', o.orders.join(','));
                    $designs.append(opt);
                });
                $designs.trigger('change.select2');
                rebuildLots();
            };
            $orders.on('change', rebuildDesigns);
            $designs.on('change', rebuildLots);
            if (($orders.val() || []).length || ($designs.val() || []).length) { rebuildDesigns(); }
        }

        // WIP / In / Done: every stage cell keeps all three numbers, the buttons choose which one is shown
        var mode = 'wip';
        var fmt = function (n) { return Number(n).toLocaleString('en-US'); };
        var paint = function () {
            $('#wip-rows td.wip-c').each(function () {
                var v = parseInt(this.getAttribute('data-' + mode), 10) || 0;
                if (v === 0) { this.innerHTML = '<span class="text-muted">0</span>'; }
                else if (mode === 'wip') { this.innerHTML = '<span class="ow-badge yellow">' + fmt(v) + '</span>'; }
                else { this.textContent = fmt(v); }
            });
            $('td.wip-t').each(function () { this.textContent = fmt(parseInt(this.getAttribute('data-' + mode), 10) || 0); });
        };
        $('.wip-mode').on('click', function () {
            mode = $(this).data('mode');
            $('.wip-mode').addClass('is-outline');
            $(this).removeClass('is-outline');
            paint();
        });

        // Load more: appends the next page's rows (totals above already cover every lot of the filter)
        var total = {{ (int) $data->total() }};
        $('#wip-more').on('click', function () {
            var $btn = $(this), next = $btn.data('next');
            if (!next) { return; }
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading');
            var url = new URL(window.location.href);
            url.searchParams.set('page', next);
            $.ajax({ url: url.toString(), headers: { 'X-Requested-With': 'XMLHttpRequest' }, dataType: 'json' })
                .done(function (res) {
                    $('#wip-rows').append(res.html);
                    if (mode !== 'wip') { paint(); }
                    var shown = $('#wip-rows tr').length;
                    $('#wip-shown').text('Showing ' + fmt(shown) + ' of ' + fmt(total) + ' lots');
                    if (res.next_page) {
                        $btn.data('next', res.next_page).prop('disabled', false).html('<i class="fas fa-chevron-down"></i> Load more');
                    } else {
                        $('#wip-more-box').remove();
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false).html('<i class="fas fa-redo"></i> Try again');
                });
        });
    });
</script>
@endpush
