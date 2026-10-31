@extends('owner.layouts.app')

@section('title', 'Lots')

@section('content')
    @php
        $orderSel = request('order_id');
        $seasonSel = (string) request('product_season_id', '');
        $filterCount = ($orderSel ? 1 : 0) + (count($lotSel) ? 1 : 0) + ($seasonSel !== '' ? 1 : 0);
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Lots',
        'sub' => 'Every production lot, newest first: pieces cut, packed, sent and where the lot is now',
        'back' => route('owner.dashboard'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.reports.unit-assignments') . '"><i class="fas fa-industry"></i> Unit Assignments</a>',
    ])

    @include('owner.common.totals', ['totals' => array_values(array_filter([
        ['label' => 'Lots', 'value' => number_format($totals['lots'])],
        ['label' => 'Pieces cut', 'value' => number_format($totals['pieces'])],
        ['label' => 'Packed', 'value' => number_format($totals['packed']), 'tone' => 'light'],
        ['label' => 'Dispatched', 'value' => number_format($totals['dispatched']), 'tone' => 'word'],
        $totals['in_stock'] > 0 ? ['label' => 'Packed to stock', 'value' => number_format($totals['in_stock']), 'tone' => 'light'] : null,
        ['label' => 'Lots in production', 'value' => number_format($totals['in_production']), 'tone' => 'yellow'],
        ['label' => 'Delayed lots', 'value' => number_format($totals['delayed']), 'tone' => $totals['delayed'] > 0 ? 'red' : null],
    ]))])

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.lots') }}">
            <div>
                <label>Order</label>
                <select name="order_id" id="order_no" class="form-control select2" data-allow-clear="1" data-placeholder="All orders">
                    <option value=""></option>
                    @foreach(collect($lotNos)->filter(fn($r) => !empty($r['order_id']))->unique('order_id')->sortBy('order_no') as $row)
                        <option value="{{ $row['order_id'] }}" {{ (string) $orderSel === (string) $row['order_id'] ? 'selected' : '' }}>{{ $row['order_no'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Lot</label>
                <select name="lot_no[]" id="lot_no" class="form-control select2" multiple data-placeholder="All lots">
                    @foreach($lotNos as $row)
                        <option value="{{ $row['lot_no'] }}" data-order="{{ $row['order_id'] }}" {{ in_array((string) $row['lot_no'], $lotSel, true) ? 'selected' : '' }}>{{ $row['lot_no'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Season</label>
                <select name="product_season_id" class="form-control">
                    <option value="">All seasons</option>
                    @foreach($seasons as $season)
                        <option value="{{ $season->id }}" {{ $seasonSel === (string) $season->id ? 'selected' : '' }}>{{ $season->name }}</option>
                    @endforeach
                    <option value="none" {{ $seasonSel === 'none' ? 'selected' : '' }}>No season</option>
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.lots') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Lot</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Season</th>
                    <th>Lot date</th>
                    <th>Stage now</th>
                    <th class="num">Pcs cut</th>
                    <th class="num">Packed</th>
                    <th class="num">Dispatched</th>
                </tr>
            </thead>
            <tbody id="lots-rows">
                @if($data->isEmpty())
                    <tr><td colspan="9" class="ow-empty">No lots found</td></tr>
                @else
                    @include('owner.reports.partials.lots_list', ['data' => $data])
                @endif
            </tbody>
            @if($data->total() > 0)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td colspan="5">{{ number_format($totals['lots']) }} lots{{ $data->total() > $data->count() ? ' (all pages)' : '' }}</td>
                        <td class="num">{{ number_format($totals['pieces']) }}</td>
                        <td class="num">{{ number_format($totals['packed']) }}</td>
                        <td class="num">{{ number_format($totals['dispatched']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($data->hasMorePages())
        <div class="text-center mb-3" id="lots-more-box">
            <div class="small text-muted mb-1" id="lots-shown">Showing {{ number_format($data->lastItem()) }} of {{ number_format($data->total()) }} lots</div>
            <button type="button" class="ow-btn is-outline" id="lots-more" data-next="{{ $data->currentPage() + 1 }}">
                <i class="fas fa-chevron-down"></i> Load more
            </button>
        </div>
    @endif

    <p class="small text-muted mb-3">
        Lots in production = lots not yet fully dispatched or put in stock; lots of orders marked Completed are left out
        (tagged Order completed). Delayed lots = lots in production whose current stage is past its planned end.
    </p>
@endsection

@push('scripts')
<script>
    $(function () {
        // Lot list follows the chosen order (options keep their order id in data-order)
        var $lot = $('#lot_no');
        var allLots = $lot.find('option').map(function () {
            return { value: this.value, order: String($(this).data('order') || ''), text: $(this).text() };
        }).get();

        $('#order_no').on('change', function () {
            var orderId = String($(this).val() || '');
            var keep = ($lot.val() || []);
            $lot.empty();
            allLots.forEach(function (o) {
                if (orderId && o.order !== orderId) { return; }
                var opt = new Option(o.text, o.value, false, keep.indexOf(o.value) !== -1);
                $(opt).attr('data-order', o.order);
                $lot.append(opt);
            });
            $lot.trigger('change');
        });
        if ($('#order_no').val()) { $('#order_no').trigger('change'); }

        // Load more: appends the next page's rows (totals above already cover every lot of the filter)
        var total = {{ (int) $data->total() }};
        $('#lots-more').on('click', function () {
            var $btn = $(this), next = $btn.data('next');
            if (!next) { return; }
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading');
            var url = new URL(window.location.href);
            url.searchParams.set('page', next);
            $.ajax({ url: url.toString(), headers: { 'X-Requested-With': 'XMLHttpRequest' }, dataType: 'json' })
                .done(function (res) {
                    $('#lots-rows').append(res.html);
                    var shown = $('#lots-rows tr').length;
                    $('#lots-shown').text('Showing ' + shown.toLocaleString('en-IN') + ' of ' + total.toLocaleString('en-IN') + ' lots');
                    if (res.next_page) {
                        $btn.data('next', res.next_page).prop('disabled', false).html('<i class="fas fa-chevron-down"></i> Load more');
                    } else {
                        $('#lots-more').remove();
                    }
                })
                .fail(function () {
                    $btn.prop('disabled', false).html('<i class="fas fa-redo"></i> Try again');
                });
        });
    });
</script>
@endpush
