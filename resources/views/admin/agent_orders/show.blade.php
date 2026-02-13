@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between">
                    <h1 class="m-0 font-weight-bold text-dark">Order Details #ORD-{{ $order->id }}</h1>
                    <div class="btn-group">
                        <a href="{{ route('admin.agent-orders.invoice', $order->id) }}"
                            class="btn btn-primary rounded-pill px-4 mr-2">
                            <i class="fas fa-file-invoice mr-1"></i> Download Invoice
                        </a>
                        @if($order->status == 'pending')
                            <a href="{{ route('admin.agent-orders.edit', $order->id) }}"
                                class="btn btn-warning rounded-pill px-4 mr-2">
                                <i class="fas fa-edit mr-1"></i> Edit Order
                            </a>
                        @endif
                        <a href="{{ route('admin.agent-orders.index') }}"
                            class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

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
                                    <li class="list-group-item d-flex justify-content-between p-2">
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

                                @if($order->status == 'pending')
                                    <hr>
                                    <div class="alert alert-info small mb-3">
                                        <i class="fas fa-barcode mr-1"></i> Scan-based dispatch is required for this order.
                                    </div>
                                    <a href="{{ route('admin.agent-orders.dispatch-scan', $order->id) }}"
                                        class="btn btn-primary btn-block btn-lg shadow-sm mb-2">
                                        <i class="fas fa-barcode mr-2"></i> START DISPATCH SCAN
                                    </a>
                                    <form action="{{ route('admin.agent-orders.dispatch', $order->id) }}" method="POST"
                                        onsubmit="return confirm('Note: Dispatching will PERMANENTLY remove scanned boxes from current inventory. All items must be scanned first. Proceed?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-block btn-lg shadow-sm">
                                            <i class="fas fa-truck mr-2"></i> COMPLETE DISPATCH
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-success mt-3 text-center">
                                        <i class="fas fa-check-circle mr-1"></i> This order was dispatched.
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
                                            <th>Box #</th>
                                            <th>Product Details</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td><span class="badge badge-secondary">Box {{ $item->box_no }}</span></td>
                                                <td>
                                                    <strong>{{ $item->product_name }}</strong><br>
                                                    <small class="text-muted">D: {{ $item->design_number }} | C:
                                                        {{ $item->color_name }} | S: {{ $item->size_set_name }}</small>
                                                </td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-primary">
                                                    ₹{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
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