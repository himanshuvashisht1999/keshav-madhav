@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Return Fabric ({{ $data->shipment_id }})</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" class="btn btn-primary mr-1" id="submitBtn" onclick="$('#returnForm').submit()">
                            <i class="fas fa-save"></i> Save Return
                        </button>
                        <a href="{{ route('admin.fabric_receipt.view', ['id' => $data->id]) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('admin.fabric_receipt.store_return') }}" method="POST" id="returnForm">
                    @csrf
                    <input type="hidden" name="fabric_receipt_id" value="{{ $data->id }}">

                    <div class="row">
                        <!-- Receipt Info Summary -->
                        <div class="col-md-4">
                            <div class="card card-outline card-info shadow-sm">
                                <div class="card-header font-weight-bold">
                                    <i class="fas fa-info-circle mr-1"></i> Shipment Info
                                </div>
                                <div class="card-body">
                                    <p><strong>Vendor:</strong> {{ $data->vendor->name ?? '-' }}</p>
                                    <p><strong>Bill No:</strong> {{ $data->bill_no ?? '-' }}</p>
                                    <p><strong>Shipment GST:</strong> {{ $data->gst_percentage ?? 0 }}%</p>
                                    <hr>
                                    <div class="form-group">
                                        <label>Return Date</label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Reason for return..."></textarea>
                                    </div>
                                    <hr>
                                    <div class="bg-light p-3 rounded">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span class="font-weight-bold">₹ <span id="display_subtotal">0.00</span></span>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">GST (%)</label>
                                                    <input type="number" name="gst_percentage" id="gst_percentage" class="form-control form-control-sm calc-input" step="0.01" value="{{ $data->gst_percentage ?? 0 }}">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-2">
                                                    <label class="small mb-1">GST Amt (₹)</label>
                                                    <input type="number" name="gst_amount" id="input_gst_amount" class="form-control form-control-sm calc-input" step="0.01" value="0">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Other Charges (+)</label>
                                            <input type="number" name="other_charges" id="other_charges" class="form-control form-control-sm calc-input" step="0.01" value="0">
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Discount (-)</label>
                                            <input type="number" name="discount" id="discount" class="form-control form-control-sm calc-input" step="0.01" value="0">
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between text-danger">
                                            <span class="font-weight-bold">Total Return:</span>
                                            <span class="h5 mb-0 font-weight-bold">₹ <span id="display_total">0.00</span></span>
                                        </div>
                                        <input type="hidden" name="sub_total" id="input_subtotal" value="0">
                                        <input type="hidden" name="total_amount" id="input_total" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Return Items Selection -->
                        <div class="col-md-8">
                            <div class="card card-outline card-primary shadow-sm">
                                <div class="card-header font-weight-bold d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-list mr-1"></i> Select Fabrics to Return</span>
                                    <div class="card-tools w-50">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="fabricSearch" class="form-control" placeholder="Search fabric or roll number...">
                                            <div class="input-group-append">
                                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover table-striped mb-0 table-head-fixed">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Roll Details</th>
                                                <th class="text-right">Price/m</th>
                                                <th class="text-center">Rem. (m)</th>
                                                <th width="150">Return Meter</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @foreach($data->details as $detail)
                                                @if($detail->remaining_quantity > 0)
                                                    <tr class="item-row">
                                                        <td>{{ $i++ }}</td>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $detail->fabric->name ?? '-' }}</div>
                                                            <small class="text-muted">Roll: {{ $detail->roll_number }}</small>
                                                        </td>
                                                        <td class="text-right">
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">₹</span>
                                                                </div>
                                                                <input type="number" 
                                                                    name="returns[{{ $detail->id }}][price_per_meter]" 
                                                                    class="form-control item-price calc-input" 
                                                                    step="0.01" 
                                                                    value="{{ $detail->price_per_meter }}">
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge badge-info">{{ $detail->remaining_quantity }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" 
                                                                    name="returns[{{ $detail->id }}][return_meter]" 
                                                                    class="form-control return-input" 
                                                                    step="0.01" 
                                                                    min="0" 
                                                                    max="{{ $detail->remaining_quantity }}"
                                                                    data-max="{{ $detail->remaining_quantity }}"
                                                                    placeholder="0.00">
                                                            </div>
                                                        </td>
                                                        <td class="text-right font-weight-bold">
                                                            ₹ <span class="item-total">0.00</span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            @if($i == 1)
                                                <tr>
                                                    <td colspan="6" class="text-center p-4">
                                                        <div class="text-muted">No returnable fabrics found in this shipment.</div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        function calculateTotals(source = '') {
            let subtotal = 0;
            
            $('.item-row').each(function() {
                let price = parseFloat($(this).find('.item-price').val()) || 0;
                let qty = parseFloat($(this).find('.return-input').val()) || 0;
                let itemTotal = price * qty;
                $(this).find('.item-total').text(itemTotal.toFixed(2));
                subtotal += itemTotal;
            });

            $('#display_subtotal').text(subtotal.toFixed(2));
            $('#input_subtotal').val(subtotal.toFixed(2));

            let gstPercent = parseFloat($('#gst_percentage').val()) || 0;
            let gstAmount = parseFloat($('#input_gst_amount').val()) || 0;

            if (source === 'percent') {
                gstAmount = (subtotal * gstPercent) / 100;
                $('#input_gst_amount').val(gstAmount.toFixed(2));
            } else if (source === 'amount') {
                if (subtotal > 0) {
                    gstPercent = (gstAmount / subtotal) * 100;
                    $('#gst_percentage').val(gstPercent.toFixed(2));
                }
            } else {
                // Default calc on item change
                gstAmount = (subtotal * gstPercent) / 100;
                $('#input_gst_amount').val(gstAmount.toFixed(2));
            }

            let otherCharges = parseFloat($('#other_charges').val()) || 0;
            let discount = parseFloat($('#discount').val()) || 0;

            let total = subtotal + gstAmount + otherCharges - discount;
            $('#display_total').text(total.toFixed(2));
            $('#input_total').val(total.toFixed(2));
        }

        $(document).on('input', '.return-input', function() {
            calculateTotals();
        });

        $(document).on('input', '#gst_percentage', function() {
            calculateTotals('percent');
        });

        $(document).on('input', '#input_gst_amount', function() {
            calculateTotals('amount');
        });

        $(document).on('input', '#other_charges, #discount', function() {
            calculateTotals();
        });

        $(document).on('input', '.item-price', function() {
            calculateTotals();
        });

        // Live Search Handler
        $('#fabricSearch').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('.item-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        $('#returnForm').on('submit', function(e) {
            let hasReturn = false;
            let error = false;

            $('.return-input').each(function() {
                let val = parseFloat($(this).val());
                let max = parseFloat($(this).data('max'));

                if (val > 0) {
                    hasReturn = true;
                    if (val > max) {
                        alert('Return quantity cannot exceed remaining quantity!');
                        $(this).focus();
                        error = true;
                        return false;
                    }
                }
            });

            if (error) {
                e.preventDefault();
                return false;
            }

            if (!hasReturn) {
                alert('Please enter return quantity for at least one item.');
                e.preventDefault();
                return false;
            }

            if (!confirm('Are you sure you want to process this return? Final Total: ₹ ' + $('#display_total').text())) {
                e.preventDefault();
                return false;
            }

            $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        });

        // Initialize calculations
        calculateTotals();
    });
</script>
@endsection
