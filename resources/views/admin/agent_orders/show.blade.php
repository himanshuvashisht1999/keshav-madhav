@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h1 class="m-0 font-weight-bold text-dark">Order Details #ORD-{{ $order->id }}</h1>

                    <div class="d-flex align-items-center">
                        <!-- Brand Filter Dropdown -->
                        <div class="mr-2">
                            <a href="{{ route('admin.agent-orders.download-order', $order->id) }}" 
                                class="btn btn-sm btn-success rounded-pill px-4 font-weight-bold shadow-sm">
                                <i class="fas fa-file-pdf mr-1"></i> Download Order Sheet
                            </a>
                        </div>

                        <!-- Brand Specific downloads moved to internal dispatch system as requested -->
                        <div class="btn-group d-none">
                            <a id="btnPacking" href="{{ route('admin.agent-orders.download-packing-slip', $order->id) }}" class="btn btn-sm btn-outline-danger">Slip</a>
                            <a id="btnInvoice" href="{{ route('admin.agent-orders.download-invoice', $order->id) }}" class="btn btn-sm btn-danger">Invoice</a>
                        </div>

                        <div class="btn-group">
                            @php $dispatchRecord = $order->dispatches->last(); @endphp

                            @if($dispatchRecord)
                                <a href="{{ route('admin.agent-orders.dispatches.show', $dispatchRecord->id) }}"
                                    class="btn btn-sm btn-info rounded-pill px-3 mr-2 font-weight-bold shadow-sm">
                                    <i class="fas fa-shipping-fast mr-1"></i> LOG
                                </a>
                            @endif

                            @if($order->status == 'pending')
                                <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                    class="btn btn-sm btn-warning rounded-pill px-3 mr-2 font-weight-bold shadow-sm">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                            @endif
                            <a href="{{ route('admin.agent-orders.index') }}"
                                class="btn btn-sm btn-outline-secondary rounded-pill px-3 font-weight-bold shadow-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const brandFilter = document.getElementById('brandFilter');
                const btnPacking = document.getElementById('btnPacking');
                const btnInvoice = document.getElementById('btnInvoice');

                const basePackingUrl = "{{ route('admin.agent-orders.download-packing-slip', $order->id) }}";
                const baseInvoiceUrl = "{{ route('admin.agent-orders.download-invoice', $order->id) }}";

                brandFilter.addEventListener('change', function () {
                    let val = this.value;
                    let query = val ? `?brand_id=${val}` : '';
                    if (val === 'actual') {
                        query = '?type=actual';
                    }

                    btnPacking.href = basePackingUrl + query;
                    btnInvoice.href = baseInvoiceUrl + query;
                });
            });
        </script>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- ORDER INFO -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Summary</h3>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Agent:</span>
                                        <span class="font-weight-bold">{{ $order->agent_name }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Shop:</span>
                                        <span class="font-weight-bold">{{ $order->shop_name }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Status:</span>
                                        <span
                                            class="badge {{ $order->status == 'pending' ? 'badge-warning' : 'badge-success' }}">{{ strtoupper($order->status) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Payment:</span>
                                        @if($order->total_paid >= $order->grand_total && $order->grand_total > 0)
                                            <a
                                                href="{{ route('admin.payment.history.index', ['paymentable_type' => 'App\Models\AgentOrder', 'paymentable_id' => $order->id]) }}">
                                                <span class="badge badge-success">PAID</span>
                                            </a>
                                        @else
                                            <span class="badge badge-danger">UNPAID</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Total Quantity:</span>
                                        <span class="font-weight-bold">{{ $order->total_qty }} pcs</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Exp. Dispatch:</span>
                                        <span
                                            class="font-weight-bold {{ $order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d') ? 'text-danger' : '' }}">
                                            {{ $order->expected_dispatch_date ? \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d-m-Y') : 'N/A' }}
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">Subtotal:</span>
                                        <span class="font-weight-bold">₹{{ number_format($order->total_amount, 2) }}</span>
                                    </li>
                                    <li
                                        class="list-group-item d-flex justify-content-between p-2 {{ $order->discount_amount > 0 ? '' : 'd-none' }}">
                                        <span class="text-muted">Discount
                                            ({{ number_format($order->discount_percentage, 0) }}%):</span>
                                        <span
                                            class="font-weight-bold text-success">-₹{{ number_format($order->discount_amount, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2">
                                        <span class="text-muted">GST
                                            ({{ number_format($order->gst_percentage, 0) }}%):</span>
                                        <span
                                            class="font-weight-bold text-danger">+₹{{ number_format($order->gst_amount, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2 bg-light">
                                        <span class="text-dark h5 mb-0">Grand Total:</span>
                                        <span
                                            class="font-weight-bold text-primary h4 mb-0">₹{{ number_format($order->grand_total, 2) }}</span>
                                    </li>
                                </ul>

                                @if($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'))
                                    <div class="alert alert-danger animate__animated animate__shakeX mt-3 mb-0 shadow-sm border-left border-danger"
                                        style="border-left-width: 5px !important;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                            <div>
                                                <h6 class="font-weight-bold mb-1 uppercase">ORDER DELAYED</h6>
                                                <p class="small mb-0">This order was expected to be dispatched by
                                                    <strong>{{ \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d M Y') }}</strong>.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if(in_array($order->status, ['pending', 'partially_dispatched']))
                                    <hr>
                                    <div class="alert alert-info small mb-3">
                                        <i class="fas fa-barcode mr-1"></i>
                                        @if($order->status == 'partially_dispatched')
                                            Order is partially dispatched. Continue scanning remaining items.
                                        @else
                                            Scan-based dispatch is required for this order.
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.agent-orders.dispatch-scan', $order->id) }}"
                                        class="btn btn-primary btn-block btn-lg shadow-sm mb-2">
                                        <i class="fas fa-barcode mr-2"></i> CONTINUE DISPATCH SCAN
                                    </a>
                                @else
                                    <div class="alert alert-success mt-3 text-center">
                                        <i class="fas fa-check-circle mr-1"></i> This order has been fully dispatched.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- ITEM DETAILS -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-dark text-white">
                                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Ordered Items</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th width="10%">Boxes</th>
                                            <th>Product Details</th>
                                            <th class="text-center">Total Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td>
                                                    <span
                                                        class="badge {{ $item->status == 'Scanned' ? 'badge-success' : ($item->status == 'Partial' ? 'badge-info' : 'badge-primary') }}">
                                                        {{ $item->scanned_box_qty }} / {{ $item->box_count }} Boxes
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ $item->product_name }}</strong><br>
                                                    <small style="color:#666;">
                                                        Design: {{ $item->design_number }} | Color: {{ $item->color_name }} |
                                                        Set:
                                                        {{ $item->size_set_name }} | Barcode: {{ $item->barcode }}
                                                        @if(isset($item->fitting_name) && $item->fitting_name) | Fit:
                                                        {{ $item->fitting_name }} @endif
                                                        @if(isset($item->pattern_name) && $item->pattern_name) | Pat:
                                                        {{ $item->pattern_name }} @endif
                                                    </small>
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $item->total_qty }} pcs</td>
                                                <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-primary">
                                                    ₹{{ number_format($item->total_qty * $item->selling_price, 2) }}</td>
                                                <td class="text-right">
                                                    @if($item->status == 'Dispatched')
                                                        <span class="badge badge-success"><i
                                                                class="fas fa-check mr-1"></i>{{ $item->status }}</span>
                                                    @elseif($item->status == 'Scanned')
                                                        <span class="badge badge-info"><i
                                                                class="fas fa-barcode mr-1"></i>{{ $item->status }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection