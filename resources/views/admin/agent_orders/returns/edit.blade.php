@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        .content-wrapper {
            background-color: var(--bg-main);
            font-family: 'Inter', sans-serif;
            padding-bottom: 2rem;
            color: var(--text-main);
        }

        .premium-page-header {
            background: #fff;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.025em;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0;
        }

        .page-title i {
            color: #ef4444;
            font-size: 1.25rem;
        }
        
        .premium-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .premium-card-header {
            background: #fff;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            color: #1e293b;
            font-size: 1rem;
        }

        .form-control-premium {
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
            box-shadow: none;
        }
        
        .form-control-premium:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
        }

        .summary-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.25rem;
            display: block;
        }

        .return-row input.return-qty {
            min-width: 60px;
            max-width: 90px;
            text-align: center;
        }
        
        .return-row input.return-price {
            min-width: 70px;
            max-width: 100px;
        }
        
        .table td {
            white-space: nowrap;
        }
        
        .item-details-cell {
            white-space: normal !important;
            min-width: 180px;
        }
    </style>

    <div class="content-wrapper">
        <div class="premium-page-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-undo"></i>
                            Edit Sales Return
                        </h1>
                        <p class="text-muted mb-0 mt-1 small" style="margin-left: 2.75rem;">
                            Return: <strong class="text-dark">#SR-{{ $return->id }}</strong>
                            <span class="mx-2 text-light-gray">|</span>
                            Dispatch: <strong class="text-dark">#DSP-{{ str_pad($dispatch->id, 5, '0', STR_PAD_LEFT) }}</strong>
                            <span class="mx-2 text-light-gray">|</span>
                            Party: <strong class="text-dark">{{ $dispatch->party->name ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('admin.agent-orders.returns.show', $return->id) }}" class="btn btn-sm btn-light border shadow-sm" style="border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-arrow-left mr-1 text-muted"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-9">
                        <div class="premium-card">
                            <div class="premium-card-header">
                                <i class="fas fa-box-open text-primary mr-2"></i> Select Items to Return
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 180px;">Item Details</th>
                                                <th class="text-center" style="white-space: nowrap;">Disp.</th>
                                                <th class="text-center" style="white-space: nowrap;">Returned</th>
                                                <th class="text-center" width="100" style="white-space: nowrap;">Return Qty</th>
                                                <th class="text-right" width="100" style="white-space: nowrap;">Price (₹)</th>
                                                <th class="text-right" width="90" style="white-space: nowrap;">Total</th>
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
                                                    <td class="item-details-cell">
                                                        <div class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">{{ $item->product_name }}</div>
                                                        <div class="d-flex flex-wrap" style="gap: 4px;">
                                                            <span class="badge badge-light border text-muted px-1 py-1 font-weight-normal" style="font-size: 0.7rem;"><i class="fas fa-hashtag text-secondary mr-1"></i><span class="text-dark font-weight-600">{{ $item->design_number }}</span></span>
                                                            <span class="badge badge-light border text-muted px-1 py-1 font-weight-normal" style="font-size: 0.7rem;"><i class="fas fa-palette text-secondary mr-1"></i>{{ $item->color_name }}</span>
                                                            <span class="badge badge-light border text-muted px-1 py-1 font-weight-normal" style="font-size: 0.7rem;"><i class="fas fa-layer-group text-secondary mr-1"></i>{{ $item->size_set_name }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><span class="badge badge-light border">{{ number_format($item->scanned_box_qty, 0) }} Bx</span></td>
                                                    <td class="text-center"><span class="badge badge-light border">{{ number_format($returned, 0) }} Bx</span></td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm form-control-premium return-qty mx-auto" min="0" max="{{ $available + $currentQty }}" step="1" value="{{ (int)$currentQty }}">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" class="form-control form-control-sm form-control-premium return-price ml-auto text-right" value="{{ $currentPrice + 0 }}" step="0.01">
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success row-total">₹0.00</td>
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
                                                    <td class="item-details-cell">
                                                        <div class="font-weight-bold text-dark mb-1" style="font-size: 0.95rem;">{{ $item->fabric->name ?? 'Fabric' }}</div>
                                                        <div class="d-flex flex-wrap" style="gap: 4px;">
                                                            <span class="badge badge-light border text-muted px-1 py-1 font-weight-normal" style="font-size: 0.7rem;"><i class="fas fa-scroll text-secondary mr-1"></i>Roll: <span class="text-dark font-weight-600">{{ $item->roll->roll_number ?? 'N/A' }}</span></span>
                                                            <span class="badge badge-light border text-muted px-1 py-1 font-weight-normal" style="font-size: 0.7rem;"><i class="fas fa-barcode text-secondary mr-1"></i>Batch: {{ $item->roll->batch_no ?? 'N/A' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center font-weight-bold">{{ number_format($item->meter, 2) }} m</td>
                                                    <td class="text-center">{{ number_format($returned, 2) }} m</td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm form-control-premium return-qty mx-auto" min="0" max="{{ $available + $currentQty }}" step="0.01" value="{{ $currentQty + 0 }}">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" class="form-control form-control-sm form-control-premium return-price ml-auto text-right" value="{{ $currentPrice + 0 }}" step="0.01">
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success row-total">₹0.00</td>
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
                        <div class="premium-card sticky-top" style="top: 20px;">
                            <div class="premium-card-header bg-light">
                                <i class="fas fa-receipt text-secondary mr-2"></i> Return Summary
                            </div>
                            <div class="card-body p-3">
                                <div class="form-group mb-3">
                                    <label class="summary-label">Return Date</label>
                                    <input type="date" name="return_date" id="return_date" class="form-control form-control-sm form-control-premium text-muted" value="{{ $return->return_date }}">
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <span class="text-muted font-weight-600" style="font-size: 0.85rem;">Subtotal:</span>
                                    <span class="font-weight-bold text-dark">₹<span id="subTotal">0.00</span></span>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label class="summary-label">Discount</label>
                                    <div class="row no-gutters">
                                        <div class="col-5">
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="discountPercentage" name="discount_percentage" class="form-control form-control-premium border-right-0" value="{{ $return->discount_percentage }}" step="any">
                                                <div class="input-group-append"><span class="input-group-text bg-light text-muted border-left-0" style="border-radius: 0 6px 6px 0; padding: 0 8px;">%</span></div>
                                            </div>
                                        </div>
                                        <div class="col-7 pl-2">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 6px 0 0 6px; padding: 0 8px;">₹</span></div>
                                                <input type="number" id="discountAmountInput" class="form-control form-control-premium border-left-0 pl-0" value="{{ $return->discount_amount }}" step="any">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right small text-danger font-weight-bold mt-1" style="display:none; font-size: 0.75rem;">-₹<span id="discountAmount">0.00</span></div>
                                </div>
                                
                                <div class="form-group mb-3 border-top pt-3">
                                    <label class="summary-label">GST</label>
                                    <div class="row no-gutters">
                                        <div class="col-5">
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="gstPercentage" name="gst_percentage" class="form-control form-control-premium border-right-0" value="{{ $return->gst_percentage }}" step="any">
                                                <div class="input-group-append"><span class="input-group-text bg-light text-muted border-left-0" style="border-radius: 0 6px 6px 0; padding: 0 8px;">%</span></div>
                                            </div>
                                        </div>
                                        <div class="col-7 pl-2">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 6px 0 0 6px; padding: 0 8px;">₹</span></div>
                                                <input type="number" id="gstAmountInput" class="form-control form-control-premium border-left-0 pl-0" value="{{ $return->gst_amount }}" step="any">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right small text-success font-weight-bold mt-1" style="display:none; font-size: 0.75rem;">+₹<span id="gstAmount">0.00</span></div>
                                </div>
                                
                                <div class="form-group mb-3 border-top pt-3">
                                    <label class="summary-label">Other Charges (Misc)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text bg-light text-muted border-right-0" style="border-radius: 6px 0 0 6px;">₹</span></div>
                                        <input type="number" id="otherCharges" class="form-control form-control-premium border-left-0 pl-0" value="{{ $return->other_charges }}" step="0.01">
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded border">
                                    <span class="font-weight-bold text-dark" style="font-size: 0.9rem;">Total Refund:</span>
                                    <span class="font-weight-bold text-primary" style="font-size: 1.1rem;">₹<span id="grandTotal">0.00</span></span>
                                </div>

                                <div class="form-group mb-3 border-top pt-3">
                                    <label class="summary-label">Remarks</label>
                                    <textarea name="remark" id="remark" class="form-control form-control-sm form-control-premium" rows="2" placeholder="Reason for return...">{{ $return->remark }}</textarea>
                                </div>

                                <button type="button" class="btn btn-primary btn-block shadow-sm submit-return-btn" style="border-radius: 8px; font-weight: 700; padding: 0.6rem;">
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
                // Initial or subtotal change - sync amount from percentage
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
                // Initial or subtotal change - sync amount from percentage
                gstAmt = taxableAmt * (gstPercent / 100);
                $('#gstAmountInput').val(gstAmt.toFixed(2));
            }

            const otherCharges = parseFloat($('#otherCharges').val()) || 0;
            const grandTotal = taxableAmt + gstAmt + otherCharges;

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
