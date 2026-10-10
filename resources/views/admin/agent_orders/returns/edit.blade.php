@extends('admin.layouts.app')

@section('title', 'Edit Sales Return')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Edit Sales Return #SR-{{ $return->id }}</h1>
                <p class="erp-page-subtitle">
                    Dispatch: <strong class="text-dark">#DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</strong>
                    <span class="mx-2 text-muted">|</span>
                    Party: <strong class="text-dark">{{ $dispatch->party->name ?? 'N/A' }}</strong>
                </p>
            </div>
            <div>
                <a href="{{ route('admin.agent-orders.returns.show', $return->id) }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancel
                </a>
            </div>
        </div>

        <form id="returnForm">
            @csrf
            <div class="row">
                <!-- Items Table (Left) -->
                <div class="col-md-9 mb-2">
                    <div class="erp-card p-0">
                        <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                                <i class="fas fa-box-open mr-1"></i> Select Items to Return
                            </h6>
                            <span class="badge-erp-status badge-erp-secondary">Editing Return Items</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table erp-table table-bordered table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="min-width: 200px;">Item details</th>
                                        <th class="text-center" style="width: 80px;">Disp.</th>
                                        <th class="text-center" style="width: 80px;">Returned</th>
                                        <th class="text-center" style="width: 110px;">Return qty</th>
                                        <th class="text-right" style="width: 120px;">Price (₹)</th>
                                        <th class="text-right" style="width: 120px;">Total</th>
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
                                                <div class="font-weight-bold text-dark mb-1">{{ $item->product_name }}</div>
                                                <div class="d-flex flex-wrap" style="gap: 4px;">
                                                    <span class="badge-erp-status badge-erp-secondary font-weight-bold">#{{ $item->design_number }}</span>
                                                    <span class="badge-erp-status badge-erp-info">{{ $item->color_name }}</span>
                                                    <span class="badge-erp-status badge-erp-primary">{{ $item->size_set_name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ number_format($item->scanned_box_qty, 0) }} Bx</td>
                                            <td class="text-center text-muted">{{ number_format($returned, 0) }} Bx</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm erp-input return-qty text-center mx-auto" min="0" max="{{ $available + $currentQty }}" step="1" value="{{ (int)$currentQty }}">
                                            </td>
                                            <td class="text-right">
                                                <input type="number" class="form-control form-control-sm erp-input return-price text-right ml-auto" value="{{ $currentPrice + 0 }}" step="0.01">
                                            </td>
                                            <td class="text-right font-weight-bold text-dark row-total">₹0.00</td>
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
                                                <div class="font-weight-bold text-dark mb-1">{{ $item->fabric->name ?? 'Fabric' }}</div>
                                                <div class="d-flex flex-wrap" style="gap: 4px;">
                                                    <span class="badge-erp-status badge-erp-info">Roll: {{ $item->roll->roll_number ?? 'N/A' }}</span>
                                                    <span class="badge-erp-status badge-erp-secondary">Batch: {{ $item->roll->batch_no ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                                            <td class="text-center text-muted">{{ number_format($returned, 2) }} m</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm erp-input return-qty text-center mx-auto" min="0" max="{{ $available + $currentQty }}" step="0.01" value="{{ $currentQty + 0 }}">
                                            </td>
                                            <td class="text-right">
                                                <input type="number" class="form-control form-control-sm erp-input return-price text-right ml-auto" value="{{ $currentPrice + 0 }}" step="0.01">
                                            </td>
                                            <td class="text-right font-weight-bold text-dark row-total">₹0.00</td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary (Right) -->
                <div class="col-md-3 mb-2">
                    <div class="erp-card p-3" style="position: sticky; top: 15px;">
                        <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                            <i class="fas fa-receipt mr-1"></i> Return Summary
                        </h6>
                        
                        <div class="form-group mb-2">
                            <label class="erp-label">Return date</label>
                            <input type="date" name="return_date" id="return_date" class="form-control form-control-sm erp-input" value="{{ $return->return_date }}">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="erp-label mb-0">Subtotal:</span>
                            <span class="font-weight-bold text-dark">₹<span id="subTotal">0.00</span></span>
                        </div>
                        
                        <div class="form-group mb-2">
                            <label class="erp-label">Discount</label>
                            <div class="form-row">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="discountPercentage" name="discount_percentage" class="form-control form-control-sm erp-input border-right-0" value="{{ $return->discount_percentage }}" step="any">
                                        <div class="input-group-append"><span class="input-group-text bg-light">%</span></div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light">₹</span></div>
                                        <input type="number" id="discountAmountInput" class="form-control form-control-sm erp-input border-left-0" value="{{ $return->discount_amount }}" step="any">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right small text-danger font-weight-bold mt-1" style="display:none;">-₹<span id="discountAmount">0.00</span></div>
                        </div>
                        
                        <div class="form-group mb-2 border-top pt-2">
                            <label class="erp-label">GST</label>
                            <div class="form-row">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="gstPercentage" name="gst_percentage" class="form-control form-control-sm erp-input border-right-0" value="{{ $return->gst_percentage }}" step="any">
                                        <div class="input-group-append"><span class="input-group-text bg-light">%</span></div>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light">₹</span></div>
                                        <input type="number" id="gstAmountInput" class="form-control form-control-sm erp-input border-left-0" value="{{ $return->gst_amount }}" step="any">
                                    </div>
                                </div>
                            </div>
                            <div class="text-right small text-success font-weight-bold mt-1" style="display:none;">+₹<span id="gstAmount">0.00</span></div>
                        </div>
                        
                        <div class="form-group mb-2 border-top pt-2">
                            <label class="erp-label">Other charges (Misc)</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text bg-light">₹</span></div>
                                <input type="number" id="otherCharges" class="form-control form-control-sm erp-input border-left-0" value="{{ $return->other_charges }}" step="0.01">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: #edf5ee; border: 1px solid #cce3d0;">
                            <span class="font-weight-bold" style="color: #05421c;">Total refund:</span>
                            <span class="font-weight-bold" style="color: #05421c; font-size: 1.15rem;">₹<span id="grandTotal">0.00</span></span>
                        </div>

                        <div class="form-group mb-3 border-top pt-2">
                            <label class="erp-label">Remarks</label>
                            <textarea name="remark" id="remark" class="form-control form-control-sm erp-input" rows="2" placeholder="Reason for return...">{{ $return->remark }}</textarea>
                        </div>

                        <button type="button" class="btn-erp btn-erp-primary w-100 py-2 submit-return-btn font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Update Return
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        function calculateTotals(source = 'default') {
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

            let discountPercent = parseFloat($('#discountPercentage').val()) || 0;
            let discountAmt = parseFloat($('#discountAmountInput').val()) || 0;

            if (source === 'discount_percentage') {
                discountAmt = subTotal * (discountPercent / 100);
                $('#discountAmountInput').val(discountAmt.toFixed(2));
            } else if (source === 'discount_amount') {
                if (subTotal > 0) {
                    discountPercent = (discountAmt / subTotal) * 100;
                    $('#discountPercentage').val(discountPercent.toFixed(6));
                } else {
                    $('#discountPercentage').val(0);
                }
            } else {
                discountAmt = subTotal * (discountPercent / 100);
                $('#discountAmountInput').val(discountAmt.toFixed(2));
            }

            const taxableAmt = subTotal - discountAmt;
            let gstPercent = parseFloat($('#gstPercentage').val()) || 0;
            let gstAmt = parseFloat($('#gstAmountInput').val()) || 0;

            if (source === 'gst_percentage') {
                gstAmt = taxableAmt * (gstPercent / 100);
                $('#gstAmountInput').val(gstAmt.toFixed(2));
            } else if (source === 'gst_amount') {
                if (taxableAmt > 0) {
                    gstPercent = (gstAmt / taxableAmt) * 100;
                    $('#gstPercentage').val(gstPercent.toFixed(6));
                } else {
                    $('#gstPercentage').val(0);
                }
            } else {
                gstAmt = taxableAmt * (gstPercent / 100);
                $('#gstAmountInput').val(gstAmt.toFixed(2));
            }

            const otherCharges = parseFloat($('#otherCharges').val()) || 0;
            const grandTotal = Math.ceil(taxableAmt + gstAmt + otherCharges);

            $('#subTotal').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#discountAmount').text(discountAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#gstAmount').text(gstAmt.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#grandTotal').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

            $('.submit-return-btn').prop('disabled', !hasSelection);
        }

        $(document).on('input', '.return-qty, .return-price, #otherCharges', function() {
            if ($(this).hasClass('return-qty')) {
                const max = parseFloat($(this).attr('max'));
                let val = parseFloat($(this).val());
                if (val > max) $(this).val(max);
                if (val < 0) $(this).val(0);
            }
            calculateTotals('default');
        });

        $('#discountPercentage').on('input', function() { calculateTotals('discount_percentage'); });
        $('#discountAmountInput').on('input', function() { calculateTotals('discount_amount'); });
        $('#gstPercentage').on('input', function() { calculateTotals('gst_percentage'); });
        $('#gstAmountInput').on('input', function() { calculateTotals('gst_amount'); });

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
                confirmButtonColor: '#05421c',
                confirmButtonText: 'Yes, Update Return'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');
                    
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
                                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Return');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong.');
                            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Return');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
