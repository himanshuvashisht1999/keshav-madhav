@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Edit Sales Return #SR-{{ $return->id }}</h1>
                    <p class="text-muted">Against Dispatch #{{ $dispatch->id }} | Party: {{ $dispatch->party->name ?? 'N/A' }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.agent-orders.returns.show', $return->id) }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0 font-weight-bold">Select Items to Return</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light text-uppercase small font-weight-bold">
                                        <tr>
                                            <th>Item Details</th>
                                            <th class="text-center">Dispatched</th>
                                            <th class="text-center">Already Returned</th>
                                            <th class="text-center" style="width: 180px;">Return Qty</th>
                                            <th class="text-right" style="width: 150px;">Price</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php
                                                $returned = isset($returnedQuantities['standard']) ? ($returnedQuantities['standard']->where('item_id', $item->id)->first()->total_returned ?? 0) : 0;
                                                $available = $item->scanned_box_qty - $returned;
                                                $currentReturnItem = $return->items->where('item_type', 'standard')->where('item_id', $item->id)->first();
                                                $currentQty = $currentReturnItem->quantity ?? 0;
                                                $currentPrice = $currentReturnItem->price ?? $item->selling_price;
                                            @endphp
                                            @if($available > 0 || $currentQty > 0)
                                            <tr class="return-row" data-type="standard" data-id="{{ $item->id }}" data-pcs-per-box="{{ $item->scanned_quantity / ($item->scanned_box_qty ?: 1) }}">
                                                <td>
                                                    <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                                                    <small class="text-muted">{{ $item->design_number }} | {{ $item->color_name }} | {{ $item->size_set_name }}</small>
                                                </td>
                                                <td class="text-center">{{ number_format($item->scanned_box_qty, 0) }} Boxes</td>
                                                <td class="text-center">{{ number_format($returned, 0) }} Boxes</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control return-qty" min="0" max="{{ $available + $currentQty }}" step="1" value="{{ $currentQty }}">
                                                        <div class="input-group-append"><span class="input-group-text">Boxes</span></div>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm ml-auto shadow-sm">
                                                        <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                        <input type="number" class="form-control return-price text-right" value="{{ $currentPrice }}" step="0.01">
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
                                                $currentReturnItem = $return->items->where('item_type', 'fabric')->where('item_id', $item->id)->first();
                                                $currentQty = $currentReturnItem->quantity ?? 0;
                                                $currentPrice = $currentReturnItem->price ?? $item->selling_price;
                                            @endphp
                                            @if($available > 0 || $currentQty > 0)
                                            <tr class="return-row" data-type="fabric" data-id="{{ $item->id }}">
                                                <td>
                                                    <strong>{{ $item->fabric->name ?? 'Fabric' }}</strong><br>
                                                    <small class="text-muted">Roll: {{ $item->roll->roll_number ?? 'N/A' }} | Batch: {{ $item->roll->batch_no ?? 'N/A' }}</small>
                                                </td>
                                                <td class="text-center">{{ number_format($item->meter, 2) }} m</td>
                                                <td class="text-center">{{ number_format($returned, 2) }} m</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" class="form-control return-qty" min="0" max="{{ $available + $currentQty }}" step="0.01" value="{{ $currentQty }}">
                                                        <div class="input-group-append"><span class="input-group-text">m</span></div>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm ml-auto shadow-sm">
                                                        <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                        <input type="number" class="form-control return-price text-right" value="{{ $currentPrice }}" step="0.01">
                                                    </div>
                                                </td>
                                                <td class="text-right font-weight-bold row-total">₹0.00</td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 sticky-top" style="border-radius: 15px; top: 20px;">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 font-weight-bold">Return Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Return Date</label>
                                <input type="date" id="return_date" class="form-control shadow-sm" value="{{ $return->return_date }}">
                            </div>

                            <div class="bg-light p-3 rounded mb-3 border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="font-weight-bold">₹<span id="subTotal">0.00</span></span>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Discount (%)</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <input type="number" id="discountPercentage" class="form-control" value="{{ $return->discount_percentage }}" step="0.01">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                    <div class="text-right small text-danger font-weight-bold mt-1">-₹<span id="discountAmount">0.00</span></div>
                                </div>
                                <div class="form-group mb-2 border-top pt-2">
                                    <label class="small font-weight-bold text-muted mb-1">GST (%)</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <input type="number" id="gstPercentage" class="form-control" value="{{ $return->gst_percentage }}" step="0.01">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                    <div class="text-right small text-success font-weight-bold mt-1">+₹<span id="gstAmount">0.00</span></div>
                                </div>
                                <div class="form-group mb-3 border-top pt-2">
                                    <label class="small font-weight-bold text-muted mb-1">Other Charges</label>
                                    <div class="input-group input-group-sm shadow-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                        <input type="number" id="otherCharges" class="form-control" value="{{ $return->other_charges }}" step="0.01">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-3 border-top pt-2">
                                    <span class="h5 mb-0 font-weight-bold">Total Refund:</span>
                                    <span class="h5 mb-0 font-weight-bold text-primary">₹<span id="grandTotal">0.00</span></span>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-muted uppercase">Remark</label>
                                <textarea id="remark" class="form-control shadow-sm" rows="3" placeholder="Reason for return...">{{ $return->remark }}</textarea>
                            </div>

                            <button class="btn btn-primary btn-block btn-lg rounded-pill shadow-sm submit-return-btn">
                                <i class="fas fa-check-circle mr-2"></i> UPDATE RETURN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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

        // Initial calculation
        calculateTotals();

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
                title: 'Update Sales Return?',
                text: "Inventory and party balance will be re-adjusted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Update Return'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
                    
                    $.ajax({
                        url: "{{ route('admin.agent-orders.returns.update', $return->id) }}",
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
                                toastr.success(response.message);
                                window.location.href = "{{ route('admin.agent-orders.returns.show', $return->id) }}";
                            } else {
                                toastr.error(response.message);
                                btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> UPDATE RETURN');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong.');
                            btn.prop('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> UPDATE RETURN');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
