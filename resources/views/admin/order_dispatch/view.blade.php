@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shipping-fast mr-2"></i> Dispatch
                        #{{ $order_dispatch_data['order_dispatch_no'] }}</h1>
                    <p class="text-muted mb-0">Customer: <strong>{{ $order_dispatch_data['customer'] }}</strong> &nbsp;|&nbsp;
                        Order: <strong>{{ $order_dispatch_data['order_no'] }}</strong></p>
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.order-dispatch.index') }}" class="btn btn-outline-secondary shadow-sm px-3 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> BACK
                    </a>
                    <button type="button" class="btn btn-outline-warning shadow-sm px-4 mr-2"
                        style="border-radius: 8px;" data-toggle="modal" data-target="#editInvoiceModal">
                        <i class="fas fa-edit mr-2"></i> EDIT INVOICE
                    </button>
                    <a href="{{ route('admin.order-dispatch.download-packing-slip', ['id' => $order_dispatch_data['id']]) }}"
                        id="packingSlipBtn" class="btn btn-outline-info shadow-sm px-4 mr-2" style="border-radius: 8px;">
                        <i class="fas fa-boxes mr-2"></i> PACKING SLIP
                    </a>
                    <a href="{{ route('admin.order-dispatch.download-invoice', ['id' => $order_dispatch_data['id']]) }}" id="invoiceBtn"
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
                        <input type="hidden" name="dispatch_id" value="{{ $order_dispatch_data['id'] }}">
                        <div class="modal-body p-4">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Subtotal Amount (Items * Price)</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control border-left-0" id="subtotal_amount" value="{{ $order_dispatch_data['total_dispatch_amount'] }}" readonly>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-muted small text-uppercase">Extra Discount Amount</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-minus-circle text-danger"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control border-left-0" id="discount_amount" name="discount_amount" value="{{ $dispatch->discount_amount ?? 0 }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="font-weight-bold text-muted small text-uppercase">GST %</label>
                                        <div class="input-group shadow-sm">
                                            <input type="number" step="0.01" class="form-control" id="gst_percentage" name="gst_percentage" value="{{ $dispatch->gst_percentage ?? 5 }}">
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
                                            @php 
                                               $taxable = $order_dispatch_data['total_dispatch_amount'] - ($dispatch->discount_amount ?? 0);
                                               $gstAmt = ($taxable * ($dispatch->gst_percentage ?? 5)) / 100;
                                            @endphp
                                            <input type="text" class="form-control bg-light" id="gst_amount_display" readonly value="{{ number_format($gstAmt, 2) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="bg-light p-3 rounded-lg text-center shadow-sm border">
                                <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Final Grand Total</h6>
                                <h3 class="mb-0 text-primary font-weight-bold" id="grand_total_display">₹{{ number_format($dispatch->total_amount, 2) }}</h3>
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
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Dispatch Date</h6>
                                <h5 class="font-weight-bold mb-0 text-dark">
                                    {{ $order_dispatch_data['dispatch_date'] }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Total Units</h6>
                                <h4 class="font-weight-bold mb-0 text-primary">{{ number_format($order_dispatch_data['total_items_dispatch']) }} PCs</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Total Cartons</h6>
                                <h4 class="font-weight-bold mb-0 text-info">{{ number_format($order_dispatch_data['total_cartons']) }} Box</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center py-4">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-2">Grand Total</h6>
                                <h4 class="font-weight-bold mb-0 text-success">₹{{ number_format($dispatch->total_amount, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 border-left-primary h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Subtotal (Net Value)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($order_dispatch_data['total_dispatch_amount'], 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calculator fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 border-left-info h-100">
                            <div class="card-body py-3">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">GST ({{ number_format($dispatch->gst_percentage ?? 5, 1) }}%)</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($gstAmt, 2) }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-percent fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carton Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i> Packed Cartons & Items</h5>
                    </div>
                    <div class="card-body p-0">
                        @forelse($cartonsDetails as $carton)
                            <div class="p-4 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="font-weight-bold mb-0">Carton #{{ $carton['carton_no'] }}</h6>
                                        <small class="text-muted">{{ $carton['storeroom'] }} | {{ $carton['rack'] }}</small>
                                    </div>
                                    <span class="badge badge-light border px-3 py-2">{{ $carton['total_items'] }} Pcs Packed</span>
                                </div>
                                <div class="table-responsive rounded border shadow-xs">
                                    <table class="table table-striped table-hover mb-0 text-nowrap">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Design | Color</th>
                                                <th>Size Set</th>
                                                <th class="text-center">Total Pcs</th>
                                                <th class="text-right">Price/Pc</th>
                                                <th class="text-right">Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($carton['sets'] as $set)
                                                <tr>
                                                    <td><strong>{{ $set['design'] }}</strong> | {{ $set['color'] }}</td>
                                                    <td><span class="badge badge-light">{{ $set['size_set'] }}</span></td>
                                                    <td class="text-center font-weight-bold">{{ $set['total_qty'] }}</td>
                                                    <td class="text-right text-muted">₹{{ number_format($set['price'], 2) }}</td>
                                                    <td class="text-right font-weight-bold text-success">₹{{ number_format($set['total_qty'] * $set['price'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <p>No carton details available.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </section>
    </div>

    <style>
        .border-left-primary { border-left: 4px solid #007bff !important; }
        .border-left-success { border-left: 4px solid #28a745 !important; }
        .border-left-info { border-left: 4px solid #17a2b8 !important; }
        .border-left-danger { border-left: 4px solid #dc3545 !important; }
        .bg-primary-light { background-color: rgba(0, 123, 255, 0.05); }
        .card { border-radius: 12px; }
        .table thead th { font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; border-top: none; }
    </style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Calculation Logic
        function calculateInvoice() {
            const subtotal = parseFloat($('#subtotal_amount').val()) || 0;
            const discountAmount = parseFloat($('#discount_amount').val()) || 0;
            const gstPercentage = parseFloat($('#gst_percentage').val()) || 0;

            const taxable = subtotal - discountAmount;
            const gstAmount = (taxable * gstPercentage) / 100;
            const grandTotal = taxable + gstAmount;

            $('#gst_amount_display').val(gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        $('#discount_amount, #gst_percentage').on('input', calculateInvoice);

        // Submission
        $('#editInvoiceForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> UPDATING...');

            $.ajax({
                url: "{{ route('admin.order-dispatch.update-invoice') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
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
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong';
                    toastr.error(msg);
                    btn.prop('disabled', false).text('UPDATE INVOICE');
                }
            });
        });
    });
</script>
@endpush