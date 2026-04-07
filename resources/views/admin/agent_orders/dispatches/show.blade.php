@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shipping-fast mr-2"></i> Dispatch
                        #DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-muted mb-0">Shop: <strong>{{ $dispatch->shop->name ?? 'N/A' }}</strong> &nbsp;|&nbsp;
                        Agent: <strong>{{ $dispatch->agent->name ?? 'Direct' }}</strong></p>
                </div>
                <div class="d-flex align-items-center">
                    <div class="mr-3">
                        <select id="brandSelect" class="form-control form-control-sm shadow-sm"
                            style="border-radius: 8px; min-width: 150px; height: 38px;">
                            <option value="actual">Actual</option>
                            <option value="2">Surgical</option>
                            <option value="1">Snapkid</option>
                        </select>
                    </div>
                    <a href="{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}"
                        id="packingSlipBtn" class="btn btn-outline-info shadow-sm px-4 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-boxes mr-2"></i> PACKING SLIP
                    </a>
                    <a href="{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}" id="invoiceBtn"
                        class="btn btn-primary shadow-sm px-4" style="border-radius: 8px;">
                        <i class="fas fa-file-invoice mr-2"></i> DOWNLOAD INVOICE
                    </a>
                </div>
            </div>
        </div>

        <section class="content mt-3">
            <div class="container-fluid">

                <!-- KPI Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Dispatch Date</h6>
                                <h5 class="font-weight-bold mb-0 text-dark">
                                    {{ $dispatch->dispatch_date ? date('d M Y, h:i A', strtotime($dispatch->dispatch_date)) : 'N/A' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    @php
                        $overallQty = $dispatch->orders->flatMap(function ($o) use ($dispatch) {
                            return $o->items->where('agent_order_dispatch_id', $dispatch->id);
                        })->sum('quantity');
                    @endphp
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Total Units</h6>
                                <h4 class="font-weight-bold mb-0 text-primary">{{ number_format($overallQty) }} PCs</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Shipment Status</h6>
                                <span class="badge badge-success px-4 py-2"
                                    style="font-size: 14px;">{{ strtoupper($dispatch->status) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm bg-primary border-0 h-100 text-white">
                            <div class="card-body text-center py-4">
                                <h6 class="text-white-50 font-weight-bold text-uppercase mb-2">Grand Total</h6>
                                <h4 class="font-weight-bold mb-0">₹{{ number_format($dispatch->grand_total, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Grouped By Order -->
                @foreach($dispatch->orders as $order)
                    @php
                        $sessionItems = $order->items->where('agent_order_dispatch_id', $dispatch->id);
                        if ($sessionItems->isEmpty())
                            continue;

                        $orderDispatchedQty = $sessionItems->sum('quantity');
                        $orderSubtotal = $sessionItems->sum(function ($i) {
                            return $i->quantity * $i->selling_price;
                        });
                    @endphp

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold">
                                <i class="fas fa-shopping-cart text-primary mr-2"></i>
                                Order <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="text-dark"
                                    target="_blank">#ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</a>
                            </h5>
                            <div class="text-right">
                                <span class="badge badge-light border px-3 py-2 mr-2"
                                    style="font-size: 14px;">{{ number_format($orderDispatchedQty) }} PCs</span>
                                <span class="badge badge-success px-3 py-2"
                                    style="font-size: 14px;">₹{{ number_format($orderSubtotal, 2) }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 text-nowrap">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design #</th>
                                            <th>Product Name</th>
                                            <th>Color</th>
                                            <th>Size Set</th>
                                            <th class="text-center">Boxes</th>
                                            <th class="text-center">Total Pcs</th>
                                            <th class="text-right">Price/Pc</th>
                                            <th class="text-right">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessionItems as $item)
                                            <tr>
                                                <td class="font-weight-bold text-dark">{{ $item->design_number }}</td>
                                                <td>{{ $item->product_name }}</td>
                                                <td>{{ $item->color_name }}</td>
                                                <td>{{ $item->size_set_name ?? 'N/A' }}</td>
                                                <td class="text-center"><span class="badge badge-light border px-3 py-1"
                                                        style="font-size: 13px;">{{ number_format($item->box_qty, 0) }}</span></td>
                                                <td class="text-center font-weight-bold" style="font-size: 15px;">
                                                    {{ number_format($item->quantity, 0) }}
                                                </td>
                                                <td class="text-right text-muted">₹{{ number_format($item->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-success" style="font-size: 15px;">
                                                    ₹{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const packingBaseUrl = "{{ route('admin.agent-orders.dispatches.download-packing-slip', $dispatch->id) }}";
            const invoiceBaseUrl = "{{ route('admin.agent-orders.dispatches.download-invoice', $dispatch->id) }}";

            function updateUrls() {
                const val = $('#brandSelect').val();
                let packingUrl = packingBaseUrl;
                let invoiceUrl = invoiceBaseUrl;

                if (val === 'actual') {
                    packingUrl += '?type=actual';
                    invoiceUrl += '?type=actual';
                } else if (val) {
                    packingUrl += '?brand_id=' + val;
                    invoiceUrl += '?brand_id=' + val;
                }

                $('#packingSlipBtn').attr('href', packingUrl);
                $('#invoiceBtn').attr('href', invoiceUrl);
            }

            // On change
            $('#brandSelect').on('change', updateUrls);

            // 🔥 IMPORTANT: Run once on page load
            updateUrls();
        });
    </script>
@endpush