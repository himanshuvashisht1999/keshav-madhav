@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h1 class="m-0 font-weight-bold text-dark">Order Details #ORD-{{ $order->id }}</h1>

                    <div class="d-flex align-items-center">
                        <!-- Brand Filter Dropdown -->
                        <!-- Download PDF with/without price -->
                        <div class="dropdown mr-2">
                            <button class="btn btn-sm btn-success dropdown-toggle rounded-pill px-4 font-weight-bold shadow-sm" 
                                type="button" id="downloadOrderDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-file-pdf mr-1"></i> Order Sheet
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="downloadOrderDropdown">
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=1">
                                    <i class="fas fa-file-invoice-dollar text-success mr-2"></i> With Price
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=1&with_warehouse=1">
                                    <i class="fas fa-warehouse text-success mr-2"></i> With Price & Wh. Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=0">
                                    <i class="fas fa-file-contract text-secondary mr-2"></i> Without Price
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=0&with_warehouse=1">
                                    <i class="fas fa-warehouse text-secondary mr-2"></i> Without Price & Wh. Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=2">
                                    <i class="fas fa-file-invoice text-info mr-2"></i> Unit Price Only
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.download-order', $order->id) }}?see_price=2&with_warehouse=1">
                                    <i class="fas fa-warehouse text-info mr-2"></i> Unit Price & Wh. Details
                                </a>
                            </div>
                        </div>

                        <!-- WhatsApp PDF -->
                        <div class="dropdown mr-2">
                            <button class="btn btn-sm btn-success dropdown-toggle rounded-pill px-4 font-weight-bold shadow-sm" 
                                type="button" id="whatsappOrderDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp mr-1"></i> WhatsApp PDF
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="whatsappOrderDropdown">
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=1" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-invoice-dollar text-success mr-2"></i> With Price
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=1&with_warehouse=1" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-warehouse text-success mr-2"></i> With Price & Wh. Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=0" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-contract text-secondary mr-2"></i> Without Price
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=0&with_warehouse=1" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-warehouse text-secondary mr-2"></i> Without Price & Wh. Details
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=2" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-invoice text-info mr-2"></i> Unit Price Only
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('admin.agent-orders.send-whatsapp-order', $order->id) }}?see_price=2&with_warehouse=1" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-warehouse text-info mr-2"></i> Unit Price & Wh. Details
                                </a>
                            </div>
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

                                @if($order->dispatches->count() == 0)
                                    <a href="{{ route('admin.agent-orders.destroy', $order->id) }}"
                                        onclick="return confirm('Are you sure you want to delete this order? This will restore any deducted inventory.')"
                                        class="btn btn-sm btn-danger rounded-pill px-3 mr-2 font-weight-bold shadow-sm">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </a>
                                @endif
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
                                        @php
                                            $isDelayed = ($order->status == 'delayed') || ($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'));
                                        @endphp
                                        <span
                                            class="badge {{ $isDelayed ? 'badge-danger' : ($order->status == 'pending' ? 'badge-warning' : 'badge-success') }}">{{ $isDelayed ? 'DELAYED' : strtoupper($order->status) }}</span>
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
                                            class="font-weight-bold {{ in_array($order->status, ['pending', 'delayed']) && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d') ? 'text-danger' : '' }}">
                                            {{ $order->expected_dispatch_date ? \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d-m-Y') : 'N/A' }}
                                        </span>
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
                                            ({{ number_format($order->gst_percentage, 1) }}%):</span>
                                        <span
                                            class="font-weight-bold text-danger">+₹{{ number_format($order->gst_amount, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2 {{ $order->other_charges > 0 ? '' : 'd-none' }}">
                                        <span class="text-muted">Other Charges:</span>
                                        <span
                                            class="font-weight-bold text-dark">+₹{{ number_format($order->other_charges, 2) }}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between p-2 bg-light">
                                        <span class="text-dark h5 mb-0">Grand Total:</span>
                                        <span
                                            class="font-weight-bold text-primary h4 mb-0">₹{{ number_format($order->grand_total, 2) }}</span>
                                    </li>
                                </ul>

                                <div class="mt-3">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-2">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Booking Station</small>
                                                    <span class="font-weight-bold">{{ $order->booking_station ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Transport</small>
                                                    <span class="font-weight-bold">{{ $order->transport ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <small class="text-muted d-block">Remarks</small>
                                            <p class="mb-0 small">{{ $order->remark ?? 'No remarks provided.' }}</p>
                                        </div>
                                    </div>
                                </div>

                                 @if(in_array($order->status, ['pending', 'delayed']) && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'))
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

                                @if(in_array($order->status, ['pending', 'delayed', 'partially_dispatched']))
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
                            <div class="card-body p-0 table-responsive">
                                <table class="table table-striped table-hover mb-0 align-middle">
                                    @if($order->sale_type === 'fabric')
                                        <thead>
                                            <tr>
                                                <th>Roll Number</th>
                                                <th>Fabric Details</th>
                                                <th class="text-center">Batch No</th>
                                                <th class="text-center">Meter</th>
                                                <th class="text-right">Price/m</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td><span class="badge badge-primary">{{ $item->roll_number }}</span></td>
                                                    <td><strong>{{ $item->fabric_name }}</strong></td>
                                                    <td class="text-center">{{ $item->batch_no }}</td>
                                                    <td class="text-center font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                                                    <td class="text-right">₹{{ number_format($item->selling_price, 2) }}</td>
                                                    <td class="text-right font-weight-bold text-primary">₹{{ number_format($item->meter * $item->selling_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @else
                                        <thead>
                                            <tr>
                                                <th width="10%">Boxes</th>
                                                <th>Product Details</th>
                                                <th class="text-center">Location</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-right">Price</th>
                                                <th class="text-right">Total</th>
                                                <th class="text-right">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                <tr>
                                                    <td class="align-middle">
                                                        <span
                                                            class="badge {{ $item->status == 'Scanned' ? 'badge-success' : ($item->status == 'Partial' ? 'badge-info' : 'badge-primary') }}">
                                                            {{ $item->scanned_box_qty }} / {{ $item->box_count }} Boxes
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <strong class="text-dark">{{ $item->product_name }}</strong>
                                                        <div class="text-muted mt-1" style="font-size: 12px; line-height: 1.4;">
                                                            <span class="mr-2"><i class="fas fa-hashtag mr-1"></i>{{ $item->design_number }}</span>
                                                            <span class="mr-2"><i class="fas fa-palette mr-1"></i>{{ $item->color_name }} ({{ $item->color_id }})</span>
                                                            <span class="mr-2"><i class="fas fa-ruler-combined mr-1"></i>{{ $item->size_set_name }}</span>
                                                            @if($item->barcode) <span class="mr-2"><i class="fas fa-barcode mr-1"></i>{{ $item->barcode }}</span> @endif
                                                            @if(isset($item->fitting_name) && $item->fitting_name) <span class="mr-2"><strong>Fit:</strong> {{ $item->fitting_name }}</span> @endif
                                                            @if(isset($item->pattern_name) && $item->pattern_name) <span class="mr-2"><strong>Pat:</strong> {{ $item->pattern_name }}</span> @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-center align-middle text-nowrap">
                                                        @if(isset($item->available_locations) && count($item->available_locations) > 1)
                                                            <select class="form-control form-control-sm location-select" data-item-id="{{ $item->id }}" style="min-width: 180px; display: inline-block;">
                                                                @foreach($item->available_locations as $loc)
                                                                    <option value="{{ $loc->rack_id }}" {{ $item->rack_name == $loc->rack_name && $item->warehouse_name == $loc->warehouse_name ? 'selected' : '' }}>
                                                                        {{ $loc->warehouse_name }} - {{ $loc->rack_name }} ({{ $loc->rack_id }}) ({{ $loc->boxes }} Box)
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <div class="small text-left d-inline-block">
                                                                <div class="text-muted mb-1"><i class="fas fa-warehouse mr-1"></i>{{ $item->warehouse_name }}</div>
                                                                <div class="text-muted"><i class="fas fa-layer-group mr-1"></i>{{ $item->rack_name }} @if($item->rack_id) ({{ $item->rack_id }}) @endif</div>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center font-weight-bold align-middle">{{ $item->total_qty }} pcs</td>
                                                    <td class="text-right align-middle text-muted">₹{{ number_format($item->selling_price, 2) }}</td>
                                                    <td class="text-right font-weight-bold text-primary align-middle">
                                                        ₹{{ number_format($item->total_qty * $item->selling_price, 2) }}</td>
                                                    <td class="text-right align-middle">
                                                        @if($item->status == 'Dispatched')
                                                            <span class="badge badge-success px-2 py-1"><i
                                                                    class="fas fa-check mr-1"></i>{{ $item->status }}</span>
                                                        @elseif($item->status == 'Scanned')
                                                            <span class="badge badge-info px-2 py-1"><i
                                                                    class="fas fa-barcode mr-1"></i>{{ $item->status }}</span>
                                                        @else
                                                            <span class="badge badge-secondary px-2 py-1">{{ $item->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.location-select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                const itemId = this.getAttribute('data-item-id');
                const rackId = this.value;
                this.disabled = true;

                fetch("{{ route('admin.agent-orders.update-item-location') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        item_id: itemId,
                        rack_id: rackId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        this.disabled = false;
                    } else {
                        alert("Failed to update location.");
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error("Error updating location:", error);
                    alert("An error occurred while updating the location.");
                    this.disabled = false;
                });
            });
        });
    });
    </script>
@endsection