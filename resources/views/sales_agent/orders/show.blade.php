@extends('sales_agent.layouts.app', ['title' => 'Order Details'])

@section('content')
    <div class="container pt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('agent.orders.index') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i> Back to History
            </a>
            <span class="badge badge-light border text-muted px-2 py-1">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>

        <!-- ORDER INFO CARD -->
        <div class="app-card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="small mb-1 opacity-75">Status</p>
                    <h4 class="font-weight-bold mb-0 text-uppercase">{{ $order->status }}</h4>
                </div>
                <div class="text-right">
                    <p class="small mb-1 opacity-75">Total Items</p>
                    <h3 class="font-weight-bold mb-0">{{ $order->total_qty }} pcs</h3>
                    <div class="d-flex flex-wrap justify-content-end mt-2">
                        <div class="dropdown mr-2 mb-1">
                            <button class="btn btn-sm btn-success dropdown-toggle rounded-pill px-3 font-weight-bold shadow-sm text-nowrap" 
                                type="button" id="whatsappOrderDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background-color: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp"></i> WA PDF
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="whatsappOrderDropdown">
                                @if(Auth::guard('sales_agent')->user()->see_price)
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.send-whatsapp-order', $order->id) }}?see_price=1" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-invoice-dollar text-success mr-2"></i> With Price
                                </a>
                                @endif
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.send-whatsapp-order', $order->id) }}?see_price=0" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-contract text-secondary mr-2"></i> Without Price
                                </a>
                                @if(Auth::guard('sales_agent')->user()->see_price)
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.send-whatsapp-order', $order->id) }}?see_price=2" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $order->shop_phone ?? '' }}'); if(phone) { window.location.href = this.href + '&phone=' + encodeURIComponent(phone); }">
                                    <i class="fas fa-file-invoice text-info mr-2"></i> Unit Price Only
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="dropdown mb-1">
                            <button class="btn btn-sm btn-light dropdown-toggle rounded-pill px-3 font-weight-bold shadow-sm text-nowrap" 
                                type="button" id="downloadOrderDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm border-0" aria-labelledby="downloadOrderDropdown">
                                @if(Auth::guard('sales_agent')->user()->see_price)
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.download-order', $order->id) }}?see_price=1">
                                    <i class="fas fa-file-invoice-dollar text-success mr-2"></i> With Price
                                </a>
                                @endif
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.download-order', $order->id) }}?see_price=0">
                                    <i class="fas fa-file-contract text-secondary mr-2"></i> Without Price
                                </a>
                                @if(Auth::guard('sales_agent')->user()->see_price)
                                <a class="dropdown-item py-2" href="{{ route('agent.orders.download-order', $order->id) }}?see_price=2">
                                    <i class="fas fa-file-invoice text-info mr-2"></i> Unit Price Only
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-card shadow-sm border-0 mb-4 bg-white">
            <div class="row">
                <div class="col-12">
                    <h6 class="font-weight-bold text-muted small uppercase mb-1 text-secondary">Shipping to:</h6>
                    <h5 class="font-weight-bold mb-1">{{ $order->shop_name }}</h5>
                    <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, h:i A') }}</p>
                </div>
            </div>
        </div>

        @if($order->status === 'pending')
            <div class="mb-4">
                <a href="{{ route('agent.orders.edit', $order->id) }}" class="btn btn-primary btn-block py-3 rounded-lg">
                    <i class="fas fa-edit mr-2"></i> Edit Order
                </a>
            </div>
        @endif

        <!-- ITEM LIST -->
        <h6 class="font-weight-bold mb-3">Order Items ({{ count($groupedItems) }} Variants)</h6>
        @foreach($groupedItems as $key => $group)
            <div class="app-card mb-2 p-3 shadow-none border variation-card" style="cursor: pointer;" data-toggle="modal"
                data-target="#modal_{{ $key }}">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="font-weight-bold mb-0 text-primary">{{ $group->product_name }}</h6>
                    <div>
                        @if($group->status == 'Dispatched')
                            <span class="badge badge-success mr-2"><i class="fas fa-check mr-1"></i>DISPATCHED</span>
                        @else
                            <span class="badge badge-secondary mr-2">PENDING</span>
                        @endif
                        <span class="badge badge-primary">{{ $group->box_count }} Boxes</span>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">

                    <span class="text-muted small bg-light px-2 rounded mr-2">Col: {{ $group->color_name }}</span>
                    <span class="text-muted small bg-light px-2 rounded mr-2">Set: {{ $group->size_set_name }}</span>
                    @if($group->fitting_name)
                        <span class="text-muted small bg-light px-2 rounded mr-2">Fit: {{ $group->fitting_name }}</span>
                    @endif
                    @if($group->pattern_name)
                        <span class="text-muted small bg-light px-2 rounded">Pat: {{ $group->pattern_name }}</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-end border-top pt-2 mt-1">
                    <div>
                        <span class="text-dark font-weight-bold d-block">{{ $group->total_qty }} pcs total</span>
                        <small class="text-muted">Avg. {{ number_format($group->total_qty / $group->box_count, 1) }} /
                            box</small>
                    </div>
                    <div class="text-right">
                        @if(Auth::guard('sales_agent')->user()->see_price)
                            <span class="text-primary font-weight-bold d-block">₹{{ number_format($group->selling_price, 2) }} /
                                pc</span>
                        @else
                            <span class="text-primary font-weight-bold d-block">Packed</span>
                            <small class="text-muted">Ready</small>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Modal for this variant -->
            <div class="modal fade" id="modal_{{ $key }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title font-weight-bold">Box Details</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="p-3 bg-light border-bottom">
                                <h6 class="font-weight-bold mb-1">{{ $group->product_name }}</h6>
                                <p class="small text-muted mb-0">
                                    {{ $group->color_name }} | {{ $group->size_set_name }}
                                    @if($group->fitting_name) | {{ $group->fitting_name }} @endif
                                    @if($group->pattern_name) | {{ $group->pattern_name }} @endif
                                </p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-white small text-muted text-uppercase">
                                        <tr>
                                            <th class="pl-3">Box #</th>
                                            <th>Carton</th>
                                            <th class="text-right pr-3">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group->boxes as $box)
                                            <tr>
                                                <td class="pl-3 font-weight-bold">{{ $box->box_no ?: 'Pending' }}</td>
                                                <td class="text-muted">{{ $box->carton_no ?: '-' }}</td>
                                                <td class="text-right pr-3 font-weight-bold text-primary">{{ $box->quantity }} pcs
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td colspan="2" class="pl-3">Total ({{ $group->box_count }} Boxes)</td>
                                            <td class="text-right pr-3">{{ $group->total_qty }} pcs</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary btn-block rounded-pill"
                                data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
@endsection

@push('styles')
    <style>
        .opacity-75 {
            opacity: 0.75;
        }

        .bg-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        }
    </style>
@endpush