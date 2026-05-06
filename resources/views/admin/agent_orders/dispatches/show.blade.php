@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shipping-fast mr-2"></i> Dispatch
                        #DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-muted mb-0">Party: <strong>
                            @if ($dispatch->party_type === 'vendor')
                                {{ $dispatch->vendor->name ?? 'N/A' }} <span class="badge badge-warning shadow-sm ml-1"
                                    style="font-size: 10px;">VENDOR</span>
                            @else
                                {{ $dispatch->shop->name ?? 'N/A' }}
                            @endif
                        </strong> &nbsp;|&nbsp;
                        Agent: <strong>{{ $dispatch->agent->name ?? 'Direct' }}</strong></p>
                </div>
                <div class="d-flex align-items-center">
                    @if(!$isFabric)
                    <div class="mr-3">
                        <select id="brandSelect" class="form-control form-control-sm shadow-sm"
                            style="border-radius: 8px; min-width: 150px; height: 38px;">
                            <option value="actual">Actual</option>
                            <option value="2">Surgical</option>
                            <option value="1">Snapkid</option>
                        </select>
                    </div>
                    @endif
                    <button type="button" class="btn btn-outline-warning shadow-sm px-4 mr-2"
                        style="border-radius: 8px;" data-toggle="modal" data-target="#editInvoiceModal">
                        <i class="fas fa-edit mr-2"></i> EDIT INVOICE
                    </button>
                    <a href="{{ route('admin.agent-orders.dispatches.return.create', $dispatch->id) }}"
                        class="btn btn-outline-danger shadow-sm px-4 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-undo mr-2"></i> SALES RETURN
                    </a>
                    <a href="{{ route('admin.agent-orders.dispatches.destroy', $dispatch->id) }}"
                        class="btn btn-danger shadow-sm px-4 mr-2" style="border-radius: 8px;"
                        onclick="return confirm('Are you sure you want to PERMANENTLY delete this dispatch? This will reverse stock and customer balance.')">
                        <i class="fas fa-trash-alt mr-2"></i> DELETE DISPATCH
                    </a>
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

        <!-- Edit Invoice Modal -->
        <div class="modal fade" id="editInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                    <div class="modal-header bg-warning text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title font-weight-bold" id="editInvoiceModalLabel"><i class="fas fa-edit mr-2"></i> Update Dispatch Invoice</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="editInvoiceForm">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Subtotal Amount (Total Pcs * Price)</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control border-left-0" id="total_amount" name="total_amount" value="{{ $dispatch->total_amount }}" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Extra Discount</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-minus-circle text-danger"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control border-left-0" id="discount_amount" name="discount_amount" value="{{ $dispatch->discount_amount ?? 0 }}">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Other Charges</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-plus-circle text-info"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control border-left-0" id="other_charges" name="other_charges" value="{{ $dispatch->other_charges ?? 0 }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase">GST %</label>
                                        <div class="input-group shadow-sm">
                                            <input type="number" step="any" class="form-control" id="gst_percentage" name="gst_percentage" value="{{ $dispatch->gst_percentage ?? 5 }}">
                                            <div class="input-group-append">
                                                <span class="input-group-text bg-white"><i class="fas fa-percentage text-secondary"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase">GST Amount</label>
                                        <div class="input-group shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-muted"></i></span>
                                            </div>
                                            <input type="number" step="any" class="form-control border-left-0" id="gst_amount_input" value="{{ round($dispatch->gst_amount, 2) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="bg-light p-3 rounded-lg text-center shadow-sm border">
                                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Final Grand Total</h6>
                                <h3 class="mb-0 text-primary font-weight-bold" id="grand_total_display">₹{{ number_format($dispatch->grand_total, 2) }}</h3>
                            </div>
                        </div>
                        <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                            <button type="button" class="btn btn-outline-secondary px-4 mr-2" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-warning px-5 font-weight-bold" style="border-radius: 8px;">UPDATE INVOICE</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <section class="content mt-3">
            <div class="container-fluid">

                <!-- KPI Summary Cards -->
                <div class="row">
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
                        $overallQty = $groupedItems->sum('total_qty');
                        $overallFabricMeters = $fabricItems->sum('meter');
                    @endphp
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Total Units</h6>
                                <h4 class="font-weight-bold mb-0 text-primary">
                                    @if($overallQty > 0) {{ number_format($overallQty) }} PCs @endif
                                    @if($overallQty > 0 && $overallFabricMeters > 0) + @endif
                                    @if($overallFabricMeters > 0) {{ number_format($overallFabricMeters, 2) }} m @endif
                                </h4>
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
                        <div class="card shadow-sm border-0 border-left-primary h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Subtotal</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($dispatch->total_amount, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calculator fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4 mb-4">
                        <div class="card shadow-sm border-0 border-left-danger h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Extra Discount</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($dispatch->discount_amount ?? 0, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tags fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4 mb-4">
                        <div class="card shadow-sm border-0 border-left-info h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">GST ({{ number_format($dispatch->gst_percentage ?? 5, 1) }}%)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($dispatch->gst_amount, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percent fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4 mb-4">
                        <div class="card shadow-sm border-0 border-left-warning h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Other Charges</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($dispatch->other_charges ?? 0, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-plus fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mt-4 mb-4">
                        <div class="card shadow-sm bg-success border-0 h-100 text-white">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">Final Amount</div>
                                        <div class="h4 mb-0 font-weight-bold">₹{{ number_format($dispatch->grand_total, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Grouped By Order -->
                @foreach($dispatch->orders as $order)
                    @php
                        $sessionItems = $order->items->where('agent_order_dispatch_id', $dispatch->id);
                        $sessionFabricItems = $fabricItems->where('agent_order_id', $order->id);
                        
                        if ($sessionItems->isEmpty() && $sessionFabricItems->isEmpty())
                            continue;

                        $orderDispatchedQty = $sessionItems->sum('quantity');
                        $orderDispatchedMeters = $sessionFabricItems->sum('meter');
                        $orderSubtotal = $sessionItems->sum(function ($i) {
                            return $i->quantity * $i->selling_price;
                        }) + $sessionFabricItems->sum(function ($i) {
                            return $i->meter * $i->selling_price;
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
                                <span class="badge badge-light border px-3 py-2 mr-2" style="font-size: 14px;">
                                    @if($orderDispatchedQty > 0) {{ number_format($orderDispatchedQty) }} PCs @endif
                                    @if($orderDispatchedQty > 0 && $orderDispatchedMeters > 0) + @endif
                                    @if($orderDispatchedMeters > 0) {{ number_format($orderDispatchedMeters, 2) }} m @endif
                                </span>
                                <span class="badge badge-success px-3 py-2" style="font-size: 14px;">₹{{ number_format($orderSubtotal, 2) }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 text-nowrap">
                                    <thead class="bg-light">
                                        @if($sessionItems->isNotEmpty())
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
                                        @else
                                            <tr>
                                                <th>Roll #</th>
                                                <th>Fabric Name</th>
                                                <th class="text-center">Batch No</th>
                                                <th class="text-center">Meters</th>
                                                <th class="text-right">Price/m</th>
                                                <th class="text-right">Total Amount</th>
                                            </tr>
                                        @endif
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

                                        @foreach($sessionFabricItems as $fItem)
                                            <tr>
                                                <td><span class="badge badge-primary">{{ $fItem->roll_number }}</span></td>
                                                <td><div class="font-weight-bold text-dark">{{ $fItem->fabric_name }}</div></td>
                                                <td class="text-center">{{ $fItem->batch_no }}</td>
                                                <td class="text-center font-weight-bold" style="font-size: 15px;">{{ number_format($fItem->meter, 2) }} m</td>
                                                <td class="text-right text-muted">₹{{ number_format($fItem->selling_price, 2) }}</td>
                                                <td class="text-right font-weight-bold text-success" style="font-size: 15px;">₹{{ number_format($fItem->meter * $fItem->selling_price, 2) }}</td>
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

            // Invoice Modal Calculations
            function calculateInvoice(source) {
                const totalAmount = parseFloat($('#total_amount').val()) || 0;
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                const otherCharges = parseFloat($('#other_charges').val()) || 0;
                const taxableAmount = totalAmount - discountAmount;

                let gstPercentage = parseFloat($('#gst_percentage').val()) || 0;
                let gstAmount = parseFloat($('#gst_amount_input').val()) || 0;

                if (source === 'percentage') {
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                } else if (source === 'amount') {
                    if (taxableAmount > 0) {
                        gstPercentage = (gstAmount / taxableAmount) * 100;
                        $('#gst_percentage').val(gstPercentage.toFixed(6));
                    } else {
                        $('#gst_percentage').val(0);
                    }
                } else {
                    // Default/Other fields changed - update amount from percentage
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#gst_amount_input').val(gstAmount.toFixed(2));
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;
                $('#grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#gst_percentage').on('input', function() {
                calculateInvoice('percentage');
            });

            $('#gst_amount_input').on('input', function() {
                calculateInvoice('amount');
            });

            $('#total_amount, #discount_amount, #other_charges').on('input', function() {
                calculateInvoice('default');
            });

            // Invoice Modal Submission
            $('#editInvoiceForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> UPDATING...');

                $.ajax({
                    url: "{{ route('admin.agent-orders.dispatches.update-invoice', $dispatch->id) }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            toastr.error(response.message);
                            btn.prop('disabled', false).text('UPDATE INVOICE');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again.');
                        btn.prop('disabled', false).text('UPDATE INVOICE');
                    }
                });
            });
        });
    </script>
@endpush