@extends('admin.layouts.app')
@section('title', 'Edit Contractor Voucher')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-user-cog text-primary"></i> Edit Contractor Voucher #{{ $data->voucher_number ?: $data->id }}
            </div>
            <p class="erp-page-subtitle mb-0">Modify contractor billing, lot details and expense line items</p>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.voucher.contractor.index') }}" class="btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('admin.payment.voucher.contractor.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $data->id }}">

        <!-- Voucher Details Card -->
        <div class="erp-card p-3 mb-2">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="erp-label d-flex justify-content-between align-items-center mb-1">
                        <span><i class="fas fa-user mr-1 text-primary"></i> Select Contractor <span class="text-danger">*</span></span>
                        <span style="font-size: 0.75rem; font-weight: normal;">
                            <a href="{{ route('admin.payment.master.contractor.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                            <a href="javascript:void(0)" class="text-info" id="refreshContractorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                        </span>
                    </label>
                    <select name="contractor_id" id="contractorSelect" class="form-control form-control-sm erp-input select2" required style="width: 100%;">
                        <option value="">Select Contractor</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}" {{ $data->contractor_id == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="erp-label"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Voucher Date <span class="text-danger">*</span></label>
                    <input type="date" name="voucher_date" class="form-control form-control-sm erp-input" value="{{ $data->voucher_date }}" required>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="erp-label"><i class="fas fa-hashtag mr-1 text-muted"></i> Voucher Number</label>
                    <input type="text" name="voucher_number" class="form-control form-control-sm erp-input" value="{{ $data->voucher_number }}" placeholder="Enter Voucher Number">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="erp-label"><i class="fas fa-paperclip mr-1 text-success"></i> Upload Document / Slip (Leave empty to keep current)</label>
                    <div class="custom-file custom-file-sm">
                        <input type="file" name="document" class="custom-file-input form-control-sm" id="documentFile">
                        <label class="custom-file-label" for="documentFile" style="height: calc(1.5em + .5rem + 2px); padding: .25rem .5rem; font-size: .875rem;">Choose new file...</label>
                    </div>
                    @if($data->document)
                        <div class="mt-1">
                            <span class="text-muted small">Current file:</span>
                            <a href="{{ asset($data->document) }}" target="_blank" class="badge-erp-status badge-erp-info ml-1">
                                <i class="fas fa-file-pdf mr-1"></i> View Document
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="erp-card p-0 mb-2">
            <div class="erp-card-header">
                <div class="erp-card-title">
                    <i class="fas fa-list-ul"></i> Item &amp; Lot Details
                </div>
            </div>
            <div class="table-responsive">
                <table class="table erp-table table-bordered mb-0" id="itemTable">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Lot No</th>
                            <th style="width: 30%;">Item Name</th>
                            <th class="text-right" style="width: 15%;">Quantity</th>
                            <th class="text-right" style="width: 15%;">Rate (₹)</th>
                            <th class="text-right" style="width: 15%;">Amount (₹)</th>
                            <th class="text-center" style="width: 8%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->items as $index => $item)
                        <tr>
                            <td>
                                <select name="items[{{ $index }}][order_lot_id]" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                                    <option value="">Select Lot</option>
                                    @foreach($lots as $lot)
                                        <option value="{{ $lot->id }}" {{ $item->order_lot_id == $lot->id ? 'selected' : '' }}>{{ $lot->lot_no }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="items[{{ $index }}][item_name]" class="form-control form-control-sm erp-input" value="{{ $item->item_name }}" required></td>
                            <td><input type="number" step="0.01" name="items[{{ $index }}][quantity]" class="form-control form-control-sm erp-input text-right qty" value="{{ $item->quantity }}" required></td>
                            <td><input type="number" step="0.01" name="items[{{ $index }}][rate]" class="form-control form-control-sm erp-input text-right rate" value="{{ $item->rate }}" required></td>
                            <td><input type="number" step="0.01" name="items[{{ $index }}][amount]" class="form-control form-control-sm erp-input text-right amount" value="{{ $item->amount }}" readonly style="background: #f8fafc; font-weight: 600;"></td>
                            <td class="text-center">
                                @if($index == 0)
                                    <button type="button" class="btn-erp btn-erp-primary addRow" title="Add Row" style="padding: 2px 8px; height: 28px;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @else
                                    <button type="button" class="erp-action-btn erp-btn-delete removeRow" title="Remove Row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right font-weight-bold">Sub Total</th>
                            <td><input type="number" step="0.01" name="sub_total" id="sub_total" class="form-control form-control-sm erp-input text-right" value="{{ $data->sub_total }}" readonly style="background: #f8fafc; font-weight: 600;"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right font-weight-bold">GST (₹)</th>
                            <td><input type="number" step="0.01" name="gst" id="gst" class="form-control form-control-sm erp-input text-right total-calc" value="{{ $data->gst }}"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right font-weight-bold">Other Charges (₹)</th>
                            <td><input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control form-control-sm erp-input text-right total-calc" value="{{ $data->other_charges }}"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right font-weight-bold">Round off</th>
                            <td><input type="number" step="0.01" name="round_off" id="round_off" class="form-control form-control-sm erp-input text-right total-calc" value="{{ $data->round_off }}"></td>
                            <td></td>
                        </tr>
                        <tr class="erp-table-grand-total">
                            <th colspan="4" class="text-right" style="font-size: var(--erp-font-md); font-weight: 800;">Total Amount (₹)</th>
                            <td><input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control form-control-sm erp-input text-right grand-total-val" value="{{ $data->total_amount }}" readonly style="font-size: 15px; font-weight: 800; background: #eaf5ea; color: #05421c;"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Remarks & Submit Card -->
        <div class="erp-card p-3 mb-2">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="erp-label"><i class="fas fa-comment-alt mr-1 text-muted"></i> Remarks / Notes</label>
                    <textarea name="remarks" class="form-control form-control-sm erp-input" rows="2" placeholder="Enter remarks or notes...">{{ $data->remarks }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end align-items-center pt-2 border-top" style="gap: 8px;">
                <a href="{{ route('admin.payment.voucher.contractor.index') }}" class="btn-erp btn-erp-secondary">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save mr-1"></i> Update Voucher
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let rowCount = {{ count($data->items) }};

        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }

        $('#refreshContractorBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.payment.master.contractor.all_contractors') }}", function(data) {
                var select = $('#contractorSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">Select Contractor</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        $('#documentFile').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Choose file...');
        });

        $(document).on('click', '.addRow', function() {
            let newRow = `
                <tr>
                    <td>
                        <select name="items[${rowCount}][order_lot_id]" class="form-control form-control-sm erp-input select2" style="width: 100%;">
                            <option value="">Select Lot</option>
                            @foreach($lots as $lot)
                                <option value="{{ $lot->id }}">{{ $lot->lot_no }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="items[${rowCount}][item_name]" class="form-control form-control-sm erp-input" placeholder="Item description" required></td>
                    <td><input type="number" step="0.01" name="items[${rowCount}][quantity]" class="form-control form-control-sm erp-input text-right qty" placeholder="0.00" required></td>
                    <td><input type="number" step="0.01" name="items[${rowCount}][rate]" class="form-control form-control-sm erp-input text-right rate" placeholder="0.00" required></td>
                    <td><input type="number" step="0.01" name="items[${rowCount}][amount]" class="form-control form-control-sm erp-input text-right amount" placeholder="0.00" readonly style="background: #f8fafc; font-weight: 600;"></td>
                    <td class="text-center">
                        <button type="button" class="erp-action-btn erp-btn-delete removeRow" title="Remove Row"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#itemTable tbody').append(newRow);
            $('#itemTable tbody tr:last-child .select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
            rowCount++;
        });

        $(document).on('click', '.removeRow', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });

        $(document).on('input', '.qty, .rate', function() {
            let tr = $(this).closest('tr');
            let qty = parseFloat(tr.find('.qty').val()) || 0;
            let rate = parseFloat(tr.find('.rate').val()) || 0;
            let amount = qty * rate;
            tr.find('.amount').val(amount.toFixed(2));
            calculateTotals();
        });

        $(document).on('input', '.total-calc', function() {
            calculateTotals();
        });

        function calculateTotals() {
            let subTotal = 0;
            $('.amount').each(function() {
                subTotal += parseFloat($(this).val()) || 0;
            });
            $('#sub_total').val(subTotal.toFixed(2));

            let gst = parseFloat($('#gst').val()) || 0;
            let otherCharges = parseFloat($('#other_charges').val()) || 0;
            let roundOff = parseFloat($('#round_off').val()) || 0;
            let totalAmount = subTotal + gst + otherCharges + roundOff;
            $('#total_amount').val(totalAmount.toFixed(2));
        }
    });
</script>
@endsection
