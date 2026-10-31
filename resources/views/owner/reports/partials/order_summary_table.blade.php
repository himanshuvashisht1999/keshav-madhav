{{--
    Order Summary list (one table for phone and laptop). $orders = paginator of OrderMain with
    ->figures (total, packed, dispatched, to_stock, remaining, status), ->design_list and ->lots_count (set in OwnerAuthController::orderSummary).
--}}
@php
    $statusTone = ['Completed' => 'green', 'Partial' => 'yellow', 'In Progress' => 'grey'];
    $pageFigures = $orders->getCollection()->pluck('figures');
@endphp
<div class="ow-table-wrap">
    <table class="erp-table sticky-first">
        <thead>
            <tr>
                <th>Order no</th>
                <th>Order date</th>
                <th>Customer</th>
                <th>PO no</th>
                <th>Design no</th>
                <th class="num">Lots</th>
                <th>Exp. delivery</th>
                <th class="num">Total pcs</th>
                <th class="num">Packed</th>
                <th class="num">Dispatched</th>
                <th class="num">To stock</th>
                <th class="num">Remaining</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php $f = $order->figures; $designs = $order->design_list; @endphp
                <tr>
                    <td><a href="{{ route('owner.order-summary.view', ['id' => $order->id]) }}">{{ $order->sku }}</a></td>
                    <td>{{ $order->created_at ? $order->created_at->format('d M Y') : '-' }}</td>
                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                    <td>{{ $order->po_number ?: '-' }}</td>
                    <td class="wrap">
                        @if($designs->isEmpty())
                            -
                        @else
                            {{ $designs->take(3)->implode(', ') }}@if($designs->count() > 3) <span class="text-muted">+{{ $designs->count() - 3 }} more</span>@endif
                        @endif
                    </td>
                    <td class="num">{{ number_format($order->lots_count) }}</td>
                    <td>{{ $order->expected_delivery_date ? date('d M Y', strtotime($order->expected_delivery_date)) : '-' }}</td>
                    <td class="num">{{ number_format($f['total']) }}</td>
                    <td class="num">{{ number_format($f['packed']) }}</td>
                    <td class="num">{{ number_format($f['dispatched']) }}</td>
                    <td class="num">{{ number_format($f['to_stock']) }}</td>
                    <td class="num">{{ number_format($f['remaining']) }}</td>
                    <td><span class="ow-badge {{ $statusTone[$f['status']] }}">{{ $f['status'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="13" class="ow-empty">No orders found</td></tr>
            @endforelse
        </tbody>
        @if($pageFigures->isNotEmpty())
            <tfoot>
                <tr>
                    <td>{{ $orders->hasPages() ? 'This page' : 'Total' }}</td>
                    <td>{{ number_format($pageFigures->count()) }} orders</td>
                    <td colspan="5"></td>
                    <td class="num">{{ number_format($pageFigures->sum('total')) }}</td>
                    <td class="num">{{ number_format($pageFigures->sum('packed')) }}</td>
                    <td class="num">{{ number_format($pageFigures->sum('dispatched')) }}</td>
                    <td class="num">{{ number_format($pageFigures->sum('to_stock')) }}</td>
                    <td class="num">{{ number_format($pageFigures->sum('remaining')) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
