@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Multiple Payment Adjustments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Multiple Adjustments</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Record Adjustments</h3>
                </div>
                <form action="{{ route('admin.payment.adjustment.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row mb-3 bg-light p-3 border rounded shadow-sm">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" id="bulk_type" class="form-control" required>
                                        <option value="credit">Credit (+)</option>
                                        <option value="debit">Debit (-)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>To Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="bulk_payment_mode" class="form-control" required>
                                        <option value="">-- Mode --</option>
                                        <option value="bank">Bank</option>
                                        <option value="cash">Cash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>To Account <span class="text-danger">*</span></label>
                                    <select name="payment_account_id" id="bulk_account_id" class="form-control select2" required disabled>
                                        <option value="">-- Select Account --</option>
                                    </select>
                                    <small id="bulk_account_balance" class="text-muted"></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Expected Total <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="total_expected_amount" id="total_expected_amount" class="form-control font-weight-bold" style="background-color: #e3f2fd;" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div id="amount_alert" class="alert alert-warning py-1 mt-2 mb-0" style="display:none; font-size: 0.9rem;">
                                    <i class="fas fa-exclamation-triangle"></i> Batch Total: <strong id="running_total_display">0</strong> | Remaining: <strong id="remaining_total_display">0</strong>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="adjustment_table">
                                <thead>
                                    <tr>
                                        <th style="min-width: 300px;">Master Type & Item (FROM)</th>
                                        <th style="width: 200px;">Amount</th>
                                        <th>Remarks</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="adjustment_rows">
                                    <tr class="adjustment-row" data-row="0">
                                        <td>
                                            <select name="adjustment_master_id[]" class="form-control select2 master-type" required>
                                                <option value="">-- Master Type --</option>
                                                @foreach($masters as $master)
                                                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                                                @endforeach
                                            </select>
                                            <select name="ref_id[]" class="form-control select2 master-item mt-2" required disabled>
                                                <option value="">-- Select Item --</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="amount[]" class="form-control row-amount" placeholder="Amount" required style="height: 40px; font-size: 1.2rem;">
                                        </td>
                                        <td>
                                            <textarea name="remarks[]" class="form-control" rows="2" placeholder="Remarks"></textarea>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th class="text-right">Running Total:</th>
                                        <th id="footer_running_total" class="font-weight-bold">0.00</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="add_more"><i class="fas fa-plus"></i> Add Another Row</button>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Save All Adjustments</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>


<!-- Template Row (Hidden) -->
<table style="display:none;">
    <tr id="row_template">
        <td>
            <select name="adjustment_master_id[]" class="form-control master-type" required>
                <option value="">-- Master Type --</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                @endforeach
            </select>
            <select name="ref_id[]" class="form-control master-item mt-2" required disabled>
                <option value="">-- Select Item --</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="amount[]" class="form-control row-amount" placeholder="Amount" required style="height: 40px; font-size: 1.2rem;">
        </td>
        <td>
            <textarea name="remarks[]" class="form-control" rows="2" placeholder="Remarks"></textarea>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</table>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 on existing rows
        $('.select2').select2({ width: '100%' });

        function calculateTotals() {
            var expected = parseFloat($('#total_expected_amount').val()) || 0;
            var running = 0;
            
            $('.row-amount').each(function() {
                running += parseFloat($(this).val()) || 0;
            });

            $('#footer_running_total').text(running.toFixed(2));
            $('#running_total_display').text(running.toFixed(2));
            
            var remaining = expected - running;
            $('#remaining_total_display').text(remaining.toFixed(2));

            if (expected > 0) {
                $('#amount_alert').show();
                if (Math.abs(remaining) < 0.01) {
                    $('#amount_alert').removeClass('alert-warning').addClass('alert-success');
                    $('#remaining_total_display').text('Balanced!');
                } else {
                    $('#amount_alert').removeClass('alert-success').addClass('alert-warning');
                }
            } else {
                $('#amount_alert').hide();
            }
        }

        $(document).on('input', '.row-amount, #total_expected_amount', function() {
            calculateTotals();
        });

        // Add More Rows
        $('#add_more').on('click', function() {
            var newRow = $('#row_template').clone().removeAttr('id');
            $('#adjustment_rows').append(newRow);
            newRow.find('select').addClass('select2').select2({ width: '100%' });
            populateAllItemSelects();
            calculateTotals();
        });

        // Remove Row
        $(document).on('click', '.remove-row', function() {
            if ($('#adjustment_rows tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                alert('At least one row is required.');
            }
        });

        // Form Submit Validation
        $('form').on('submit', function(e) {
            var expected = parseFloat($('#total_expected_amount').val()) || 0;
            var running = 0;
            $('.row-amount').each(function() {
                running += parseFloat($(this).val()) || 0;
            });

            if (Math.abs(expected - running) > 0.01) {
                e.preventDefault();
                alert('Total amounts do not match! \nExpected: ' + expected.toFixed(2) + '\nCurrent: ' + running.toFixed(2) + '\nDifference: ' + (expected - running).toFixed(2));
                return false;
            }
        });

        // Handle Bulk Payment Mode Change
        $('#bulk_payment_mode').on('change', function() {
            var mode = $(this).val();
            $('#bulk_account_balance').text('');
            if (mode) {
                $.ajax({
                    url: "{{ route('admin.payment.adjustment.getAccounts') }}",
                    type: "GET",
                    data: { mode: mode },
                    success: function(data) {
                        $('#bulk_account_id').empty().append('<option value="">-- Select Account --</option>');
                        $.each(data, function(key, value) {
                            $('#bulk_account_id').append('<option value="' + value.id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
                        });
                        $('#bulk_account_id').prop('disabled', false).trigger('change');
                    }
                });
            } else {
                $('#bulk_account_id').empty().append('<option value="">-- Select Account --</option>').prop('disabled', true).trigger('change');
            }
        });

        $('#bulk_account_id').on('change', function() {
            var balance = parseFloat($(this).find(':selected').data('balance'));
            if (!isNaN(balance)) {
                var typeStr = (balance < 0) ? ' (Dr)' : ' (Cr)';
                $('#bulk_account_balance').text('Available Balance: ' + Math.abs(balance).toFixed(2) + typeStr);
            } else {
                $('#bulk_account_balance').text('');
            }
        });

        // Global variable to store all items
        var allMasterItems = [];

        // Fetch all items on load
        function fetchAllItems() {
            return $.ajax({
                url: "{{ route('admin.payment.adjustment.getSubMastersAll') }}",
                type: "GET",
                success: function(data) {
                    allMasterItems = data;
                    populateAllItemSelects();
                }
            });
        }

        function populateAllItemSelects() {
            $('.master-item').each(function() {
                var $select = $(this);
                var $row = $select.closest('tr');
                var currentMasterId = $row.find('.master-type').val();
                
                if (!currentMasterId) {
                    $select.empty().append('<option value="">-- Select Item --</option>');
                    $.each(allMasterItems, function(key, value) {
                        $select.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
                    });
                    $select.prop('disabled', false).trigger('change.select2');
                }
            });
        }

        fetchAllItems();

        // Handle Master Type Change
        $(document).on('change', '.master-type', function(e, isAutoFill) {
            if (isAutoFill) return; // Prevent loop

            var $row = $(this).closest('tr');
            var masterId = $(this).val();
            var $itemSelect = $row.find('.master-item');
            
            if (masterId) {
                // Filter allMasterItems for this masterId
                var filteredItems = allMasterItems.filter(function(item) {
                    return item.master_id == masterId;
                });

                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(filteredItems, function(key, value) {
                    var bal = parseFloat(value.balance);
                    var balanceText = '';
                    if (!isNaN(bal)) {
                        var typeStr = (bal < 0) ? ' (Dr)' : ' (Cr)';
                        balanceText = ' (Bal: ' + Math.abs(bal).toFixed(2) + typeStr + ')';
                    }
                    $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + balanceText + '</option>');
                });
                $itemSelect.prop('disabled', false).trigger('change.select2');
            } else {
                // Restore all items if master type is cleared
                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(allMasterItems, function(key, value) {
                    $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
                });
                $itemSelect.prop('disabled', false).trigger('change.select2');
            }
        });

        // Handle Master Item Change (to auto-fill Master Type)
        $(document).on('change', '.master-item', function() {
            var $row = $(this).closest('tr');
            var $masterTypeSelect = $row.find('.master-type');
            var selectedOption = $(this).find(':selected');
            var masterId = selectedOption.data('master-id');
            
            if (masterId && !$masterTypeSelect.val()) {
                $masterTypeSelect.val(masterId).trigger('change', [true]);
                // Re-trigger change to let other handlers (like shipment picker) react to the newly set master-type
                $(this).trigger('change');
                return;
            }

            var balance = selectedOption.data('balance');
            var $balanceDisplay = $row.find('.item-balance-display');
            
            if ($balanceDisplay.length == 0) {
                $balanceDisplay = $('<small class="text-muted item-balance-display d-block"></small>');
                $(this).after($balanceDisplay);
            }

            if (balance !== undefined && !$(this).val().includes(',')) {
                var bal = parseFloat(balance);
                var typeStr = (bal < 0) ? ' (Dr)' : ' (Cr)';
                $balanceDisplay.text('Current Balance: ' + Math.abs(bal).toFixed(2) + typeStr);
            } else {
                $balanceDisplay.text('');
            }
        });

    });
</script>
@endpush
