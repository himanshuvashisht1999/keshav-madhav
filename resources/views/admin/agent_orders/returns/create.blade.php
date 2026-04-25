@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Process Sales Return</h1>
                    <p class="text-muted">Against Dispatch #{{ $dispatch->id }} | Party: {{ $dispatch->party->name ?? 'N/A' }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dispatch
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form id="returnForm">
                @csrf
                <div class="row">
                    <div class="col-md-9">
                        <div class="card shadow-sm border-0" style="border-radius: 15px;">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-undo mr-2 text-danger"></i>Select Items to Return</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Item Details</th>
                                                <th class="text-center">Dispatched</th>
                                                <th class="text-center">Already Returned</th>
                                                <th class="text-center" width="150">Return Qty</th>
                                                <th class="text-right">Price</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $item)
                                                @php
                                                    $returned = isset($returnedQuantities['standard']) ? ($returnedQuantities['standard']->where('item_id', $item->id)->first()->total_returned ?? 0) : 0;
                                                    $available = $item->scanned_box_qty - $returned;
                                                @endphp
                                                @if($available > 0)
                                                <tr class="return-row" data-type="standard" data-id="{{ $item->id }}" data-price="{{ $item->selling_price }}" data-pcs-per-box="{{ $item->scanned_quantity / ($item->scanned_box_qty ?: 1) }}">
                                                    <td>
                                                        <strong>{{ $item->product_name }}</strong><br>
                                                        <small class="text-muted">Design: {{ $item->design_number }} | Color: {{ $item->color_name }} | Set: {{ $item->size_set_name }}</small>
                                                    </td>
                                                    <td class="text-center">{{ $item->scanned_box_qty }} Boxes</td>
                                                    <td class="text-center">{{ $returned }} Boxes</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control return-qty" min="0" max="{{ $available }}" step="1" value="0">
                                                            <div class="input-group-append"><span class="input-group-text">Boxes</span></div>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="input-group input-group-sm ml-auto shadow-sm" style="width: 120px;">
                                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                            <input type="number" class="form-control return-price text-right" 
                                                                   value="{{ $item->selling_price }}" step="0.01">
                                                        </div>
                                                    </td>
                                                    <td class="text-right font-weight-bold row-total">₹0.00</td>
                                                </tr>
                                                @endif
                                            @endforeach

                                            @foreach($fabricItems as $item)
                                                @php
                                                    $returned = isset($returnedQuantities['fabric']) ? ($returnedQuantities['fabric']->where('item_id', $item->id)->first()->total_returned ?? 0) : 0;
                                                    $available = $item->meter - $returned;
                                                @endphp
                                                @if($available > 0)
                                                <tr class="return-row" data-type="fabric" data-id="{{ $item->id }}" data-price="{{ $item->selling_price }}">
                                                    <td>
                                                        <strong>{{ $item->fabric->name ?? 'Fabric' }}</strong><br>
                                                        <small class="text-muted">Roll: {{ $item->roll->roll_number ?? 'N/A' }} | Batch: {{ $item->roll->batch_no ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="text-center">{{ number_format($item->meter, 2) }} m</td>
                                                    <td class="text-center">{{ number_format($returned, 2) }} m</td>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" class="form-control return-qty" min="0" max="{{ $available }}" step="0.01" value="0">
                                                            <div class="input-group-append"><span class="input-group-text">m</span></div>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="input-group input-group-sm ml-auto shadow-sm" style="width: 120px;">
                                                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                            <input type="number" class="form-control return-price text-right" 
                                                                   value="{{ $item->selling_price }}" step="0.01">
                                                        </div>
                                                    </td>
                                                    <td class="text-right font-weight-bold row-total">₹0.00</td>
                                                </tr>
                                                @endif
                                            @endforeach

                                            @if($items->isEmpty() && $fabricItems->isEmpty())
                                                <tr>
                                                    <td colspan="6" class="text-center py-5 text-muted">No items available for return.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 sticky-top" style="top: 20px; border-radius: 15px;">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0 font-weight-bold">Return Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">Return Date</label>
                                    <input type="date" name="return_date" id="return_date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="font-weight-bold">₹<span id="subTotal">0.00</span></span>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Discount (%)</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <input type="number" id="discountPercentage" class="form-control" value="0" step="0.01">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                    <div class="text-right small text-danger font-weight-bold mt-1">-₹<span id="discountAmount">0.00</span></div>
                                </div>
                                <div class="form-group mb-2 border-top pt-2">
                                    <label class="small font-weight-bold text-muted mb-1">GST (%)</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <input type="number" id="gstPercentage" class="form-control" value="{{ $dispatch->gst_percentage ?? 5 }}" step="0.01">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                    <div class="text-right small text-success font-weight-bold mt-1">+₹<span id="gstAmount">0.00</span></div>
                                </div>
                                <div class="form-group mb-3 border-top pt-2">
                                    <label class="small font-weight-bold text-muted mb-1">Other Charges (Transport/Misc)</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                        <input type="number" id="otherCharges" class="form-control" value="0" step="0.01">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-3 border-top pt-2">
                                    <span class="h5 mb-0 font-weight-bold">Total Refund:</span>
                                    <span class="h5 mb-0 font-weight-bold text-primary">₹<span id="grandTotal">0.00</span></span>
                                </div>

                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">Remarks</label>
                                    <textarea name="remark" id="remark" class="form-control" rows="3" placeholder="Reason for return..."></textarea>
                                </div>

                                <button type="button" class="btn btn-danger btn-block btn-lg shadow-sm rounded-pill submit-return-btn" disabled>
                                    <i class="fas fa-check-circle mr-2"></i> CONFIRM RETURN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        function calculateTotals() {
            let subTotal = 0;
            let hasSelection = false;

            $('.return-row').each(function() {
                const qty = parseFloat($(this).find('.return-qty').val()) || 0;
                const price = parseFloat($(this).find('.return-price').val()) || 0;
                const type = $(this).data('type');
                
                let rowPcs = qty;
                if (type === 'standard') {
                    const pcsPerBox = parseFloat($(this).data('pcs-per-box')) || 1;
                    rowPcs = qty * pcsPerBox;
                }

                const total = rowPcs * price;
                $(this).find('.row-total').text('₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                
                if (qty > 0) {
                    subTotal += total;
                    hasSelection = true;
                }
            });

            const discountPercent = parseFloat($('#discountPercentage').val()) || 0;
            const gstPercent = parseFloat($('#gstPercentage').val()) || 0;
            const otherCharges = parseFloat($('#otherCharges').val()) || 0;

            const discountAmt = subTotal * (discountPercent / 100);
            const taxableAmt = subTotal - discountAmt;
            const gstAmt = taxableAmt * (gstPercent / 100);
            const grandTotal = taxableAmt + gstAmt + otherCharges;

            $('#subTotal').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#discountAmount').text(discountAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#gstAmount').text(gstAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#grandTotal').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

            $('.submit-return-btn').prop('disabled', !hasSelection);
        }

        $(document).on('input', '.return-qty, .return-price, #discountPercentage, #gstPercentage, #otherCharges', function() {
            if ($(this).hasClass('return-qty')) {
                const max = parseFloat($(this).attr('max'));
                let val = parseFloat($(this).val());
                if (val > max) $(this).val(max);
                if (val < 0) $(this).val(0);
            }
            calculateTotals();
        });

        $('.submit-return-btn').click(function() {
            const btn = $(this);
            const returns = [];

            $('.return-row').each(function() {
                const qty = parseFloat($(this).find('.return-qty').val()) || 0;
                if (qty > 0) {
                    returns.push({
                        item_type: $(this).data('type'),
                        item_id: $(this).data('id'),
                        quantity: qty,
                        price: parseFloat($(this).find('.return-price').val()) || 0
                    });
                }
            });

            Swal.fire({
                title: 'Confirm Sales Return?',
                text: "Inventory will be restored and party balance will be adjusted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Process Return'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                    
                    $.ajax({
                        url: "{{ route('admin.agent-orders.dispatches.return.store', $dispatch->id) }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            return_date: $('#return_date').val(),
                            remark: $('#remark').val(),
                            gst_percentage: $('#gstPercentage').val(),
                            discount_percentage: $('#discountPercentage').val(),
                            other_charges: $('#otherCharges').val(),
                            returns: returns
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success').then(() => {
                                    window.location.href = response.redirect_url;
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> CONFIRM RETURN');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> CONFIRM RETURN');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
