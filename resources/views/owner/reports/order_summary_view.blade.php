@extends('owner.layouts.app')

@section('title', 'Order ' . $data['order']->sku)

@section('content')
    @php
        $order = $data['order'];
        $sets = $order->orderProductSets; // eager-loaded by OrderSummaryReportService::view (same key, no extra queries)
        $lots = collect($data['lotsData']);
        $facts = $data['lotFacts'];
        $cartons = $data['cartons'];
        $dispatches = $data['dispatches'];
        $fig = $data['figures'];
        $statusTone = ['Completed' => 'green', 'Partial' => 'yellow', 'In Progress' => 'grey'];
        $stageTone = fn($stage) => in_array($stage, ['Dispatched', 'Packed (stock)'], true) ? 'green' : 'yellow';
        $cartonStatus = [1 => ['Packed', 'yellow'], 2 => ['Dispatched', 'green'], 3 => ['In stock (domestic)', 'grey']];
        $factOf = fn($lotNo) => $facts[\App\Services\Admin\ReportService::lotKeyOf($lotNo)] ?? null;
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Order ' . $order->sku,
        'sub' => ($order->customer->name ?? 'N/A') . ' · ' . date('d M Y', strtotime($order->created_at)),
        'back' => route('owner.order-summary.index'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . e(route('owner.order-summary.pdf', ['id' => $order->id])) . '"><i class="fas fa-file-pdf"></i> PDF</a>',
    ])

    @include('owner.common.totals', ['totals' => [
        ['label' => 'Ordered', 'value' => number_format($fig['ordered'])],
        ['label' => 'Cut', 'value' => number_format($fig['cut']), 'tone' => 'light'],
        ['label' => 'Packed', 'value' => number_format($fig['packed']), 'tone' => 'light'],
        ['label' => 'Dispatched', 'value' => number_format($fig['dispatched']), 'tone' => 'word'],
        ['label' => 'To stock', 'value' => number_format($fig['to_stock']), 'tone' => 'light'],
        ['label' => 'Remaining', 'value' => number_format($fig['remaining']), 'tone' => $fig['remaining'] > 0 ? 'yellow' : null],
        ['label' => 'Dispatch bills', 'value' => '₹ ' . number_format($fig['bills'], 2)],
    ]])

    <div class="ow-section">
        <dl class="ow-kv">
            <dt>Status</dt>
            <dd><span class="ow-badge {{ $statusTone[$fig['status']] }}">{{ $fig['status'] }}</span></dd>
            <dt>Customer</dt>
            <dd>{{ $order->customer->name ?? 'N/A' }}</dd>
            <dt>PO no</dt>
            <dd>{{ $order->po_number ?: '-' }}</dd>
            <dt>Order date</dt>
            <dd>{{ date('d M Y', strtotime($order->created_at)) }}</dd>
            <dt>Exp. delivery</dt>
            <dd>{{ $order->expected_delivery_date ? date('d M Y', strtotime($order->expected_delivery_date)) : '-' }}</dd>
            <dt>Order type</dt>
            <dd>{{ $order->order_type ? ucfirst($order->order_type) : '-' }}</dd>
            <dt>Order file</dt>
            <dd>
                @if($order->corporate_order_file)
                    <a href="{{ asset('assets/products/' . $order->corporate_order_file) }}" target="_blank" rel="noopener" class="ow-btn is-outline is-sm"><i class="fas fa-file"></i> View file</a>
                @else
                    -
                @endif
            </dd>
        </dl>
    </div>

    <nav class="ow-tabs" id="ow-order-tabs">
        <button type="button" class="active" data-tab="lines">Lines ({{ $sets->count() }})</button>
        <button type="button" data-tab="lots">Lots ({{ $lots->count() }})</button>
        <button type="button" data-tab="cartons">Cartons ({{ $cartons->count() }})</button>
        <button type="button" data-tab="dispatches">Dispatches ({{ $dispatches->count() }})</button>
    </nav>
    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Swipe the table sideways to see all columns</div>

    {{-- Order lines (product sets) --}}
    <section data-pane="lines">
        <div class="ow-table-wrap">
            <table class="erp-table sticky-first">
                <thead>
                    <tr>
                        <th>Design no</th>
                        <th>Barcode</th>
                        <th>Set size (group)</th>
                        <th>Lot nos</th>
                        <th>Colour</th>
                        <th>Fabric</th>
                        <th>Fitting</th>
                        <th>Pattern</th>
                        <th>Assignment / PO</th>
                        <th>Cutting start</th>
                        <th>Exp. end</th>
                        <th>Completed</th>
                        <th class="num">Set qty</th>
                        <th class="num">Total qty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sets as $set)
                        @php
                            $cutting = $set->order_cutting_stage;
                            if ($set->remain_total_quantity <= 0) {
                                $assign = ($cutting && $cutting->is_po)
                                    ? ['PO: ' . ($cutting->vendor_id ? ($cutting->vendor->name ?? 'Vendor') : ($cutting->customer->name ?? 'Customer')), 'green']
                                    : ['Fully assigned', 'green'];
                            } elseif ($set->remain_total_quantity < $set->total_quantity) {
                                $assign = ['Partial', 'yellow'];
                            } else {
                                $assign = ['Not assigned', 'grey'];
                            }
                        @endphp
                        <tr>
                            <td>{{ $set->design_number ?? '-' }}</td>
                            <td>{{ $set->bar_code ?? '-' }}</td>
                            <td>{{ $set->size_measurement->name ?? '-' }} ({{ $set->size_measurement->size_group ?? '-' }})</td>
                            <td class="wrap">{{ $set->lots->pluck('lot_no')->unique()->implode(', ') ?: '-' }}</td>
                            <td>{{ $set->colors->name ?? '-' }}</td>
                            <td>{{ $set->fabric->name ?? '-' }}</td>
                            <td>{{ $set->master_product_fitting->name ?? '-' }}</td>
                            <td>{{ $set->master_design_pattern->name ?? '-' }}</td>
                            <td><span class="ow-badge {{ $assign[1] }}">{{ $assign[0] }}</span></td>
                            <td>{{ ($cutting && $cutting->start_date) ? date('d M Y', strtotime($cutting->start_date)) : '-' }}</td>
                            <td>{{ ($cutting && $cutting->end_date) ? date('d M Y', strtotime($cutting->end_date)) : '-' }}</td>
                            <td>{{ ($cutting && $cutting->complete_date) ? date('d M Y', strtotime($cutting->complete_date)) : '-' }}</td>
                            <td class="num">{{ number_format((int) $set->set_quantity) }}</td>
                            <td class="num">{{ number_format((int) $set->total_quantity) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="14" class="ow-empty">No order lines found</td></tr>
                    @endforelse
                </tbody>
                @if($sets->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td colspan="11"></td>
                            <td class="num">{{ number_format((int) $sets->sum('set_quantity')) }}</td>
                            <td class="num">{{ number_format((int) $sets->sum('total_quantity')) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    {{-- Lots (stage and progress from the lot records, as on the Sales Orders page) --}}
    <section data-pane="lots" hidden>
        <div class="ow-table-wrap">
            <table class="erp-table sticky-first">
                <thead>
                    <tr>
                        <th>Lot no</th>
                        <th>Design no</th>
                        <th>Cutting master</th>
                        <th>Current stage</th>
                        <th class="num">Cut pcs</th>
                        <th class="num">Packed</th>
                        <th class="num">Dispatched</th>
                        <th class="num">To stock</th>
                        <th>Delay</th>
                        <th>Stage due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lots as $lot)
                        @php $lf = $factOf($lot['lot_no']); @endphp
                        <tr>
                            <td><a href="{{ route('owner.lot-details', ['lot_no' => $lot['lot_no']]) }}">{{ $lot['lot_no'] }}</a></td>
                            <td>{{ $lot['design_number'] ?? '-' }}</td>
                            <td>{{ $lot['cutting_master'] ?? '-' }}</td>
                            <td><span class="ow-badge {{ $stageTone($lot['last_current_stage']) }}">{{ $lot['last_current_stage'] ?? 'N/A' }}</span></td>
                            <td class="num">{{ number_format((int) $lot['lot_quantity']) }}</td>
                            <td class="num">{{ number_format($lf['packed'] ?? 0) }}</td>
                            <td class="num">{{ number_format($lf['dispatched'] ?? 0) }}</td>
                            <td class="num">{{ number_format($lf['in_stock'] ?? 0) }}</td>
                            <td>
                                @if(($lf['is_delayed'] ?? 'No') === 'Yes')
                                    <span class="ow-badge red">Delayed</span>
                                @else
                                    <span class="ow-badge green">On time</span>
                                @endif
                            </td>
                            <td>{{ !empty($lf['allowed_till']) ? date('d M Y', strtotime($lf['allowed_till'])) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="ow-empty">No lots yet</td></tr>
                    @endforelse
                </tbody>
                @if($lots->isNotEmpty())
                    @php $lotFactRows = $lots->map(fn($l) => $factOf($l['lot_no']) ?? []); @endphp
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td colspan="3">{{ number_format($lots->count()) }} lots</td>
                            <td class="num">{{ number_format($lots->sum('lot_quantity')) }}</td>
                            <td class="num">{{ number_format($lotFactRows->sum('packed')) }}</td>
                            <td class="num">{{ number_format($lotFactRows->sum('dispatched')) }}</td>
                            <td class="num">{{ number_format($lotFactRows->sum('in_stock')) }}</td>
                            <td>{{ number_format($lotFactRows->where('is_delayed', 'Yes')->count()) }} delayed</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    {{-- Packing cartons --}}
    <section data-pane="cartons" hidden>
        <div class="ow-table-wrap">
            <table class="erp-table sticky-first">
                <thead>
                    <tr>
                        <th>Carton no</th>
                        <th>Contents (size: pcs)</th>
                        <th class="num">Pcs</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cartons as $carton)
                        @php
                            $contents = [];
                            foreach ($carton->items as $item) {
                                $size = $item->detail->size ?? $item->size_id;
                                $contents[$size] = ($contents[$size] ?? 0) + $item->quantity;
                            }
                            $cs = $cartonStatus[(int) $carton->status] ?? ['Status ' . $carton->status, 'grey'];
                        @endphp
                        <tr>
                            <td>{{ $carton->carton_no }}</td>
                            <td class="wrap">{{ collect($contents)->map(fn($q, $s) => $s . ': ' . number_format($q))->implode(', ') ?: '-' }}</td>
                            <td class="num">{{ number_format((int) $carton->items->sum('quantity')) }}</td>
                            <td><span class="ow-badge {{ $cs[1] }}">{{ $cs[0] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ow-empty">No cartons packed yet</td></tr>
                    @endforelse
                </tbody>
                @if($cartons->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format($cartons->count()) }} cartons</td>
                            <td class="num">{{ number_format($fig['packed']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>

    {{-- Dispatch bills --}}
    <section data-pane="dispatches" hidden>
        <div class="ow-table-wrap">
            <table class="erp-table sticky-first">
                <thead>
                    <tr>
                        <th>Dispatch no</th>
                        <th>Bill no</th>
                        <th>Date</th>
                        <th class="num">Boxes</th>
                        <th class="num">Bill amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                        <tr>
                            <td>{{ $dispatch->sku }}</td>
                            <td>{{ $dispatch->bill_number ?: '-' }}</td>
                            <td>{{ $dispatch->dispatch_date ? date('d M Y', strtotime($dispatch->dispatch_date)) : '-' }}</td>
                            <td class="num">{{ number_format((int) $dispatch->total_quantity) }}</td>
                            <td class="num">{{ number_format((float) $dispatch->total_amount, 2) }}</td>
                            <td><span class="ow-badge green">Complete</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ow-empty">No dispatches yet</td></tr>
                    @endforelse
                </tbody>
                @if($dispatches->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td>{{ number_format($dispatches->count()) }} bills</td>
                            <td></td>
                            <td class="num">{{ number_format((int) $dispatches->sum('total_quantity')) }}</td>
                            <td class="num">{{ number_format($fig['bills'], 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        $(function () {
            var $tabs = $('#ow-order-tabs [data-tab]');
            function showTab(tab) {
                if (!$('[data-pane="' + tab + '"]').length) return;
                $('[data-pane]').attr('hidden', true).filter('[data-pane="' + tab + '"]').attr('hidden', false);
                $tabs.removeClass('active').filter('[data-tab="' + tab + '"]').addClass('active');
            }
            $tabs.on('click', function () {
                var tab = $(this).data('tab');
                showTab(tab);
                if (window.history && history.replaceState) history.replaceState(null, '', '#' + tab);
            });
            showTab((location.hash || '').replace('#', ''));
        });
    </script>
@endpush
