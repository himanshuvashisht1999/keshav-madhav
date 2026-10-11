@extends('admin.layouts.app')
@section('title', 'Edit Payment Adjustment Batch')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-balance-scale text-primary"></i> Edit Payment Adjustment Batch <span class="badge-erp-status badge-erp-info ml-2">{{ $batchId }}</span>
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.payment.adjustment.show', $batchId) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-eye"></i> View Batch
                </a>
                <a href="{{ route('admin.payment.adjustment.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('admin.payment.adjustment.update', $batchId) }}" method="POST">
            @csrf

            <!-- Batch Header Parameters -->
            <div class="erp-card p-3 mb-2">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control form-control-sm erp-input" value="{{ $first->date }}" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="bulk_type" class="form-control form-control-sm erp-input" required>
                            <option value="credit" {{ $first->type == 'credit' ? 'selected' : '' }}>Credit (+)</option>
                            <option value="debit" {{ $first->type == 'debit' ? 'selected' : '' }}>Debit (-)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-label">To mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" id="bulk_payment_mode" class="form-control form-control-sm erp-input" required>
                            <option value="">-- Mode --</option>
                            <option value="bank" {{ $first->payment_mode == 'bank' ? 'selected' : '' }}>Bank</option>
                            <option value="cash" {{ $first->payment_mode == 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-label">To account <span class="text-danger">*</span></label>
                        <select name="payment_account_id" id="bulk_account_id" class="form-control form-control-sm erp-input select2" required style="width: 100%;">
                            <option value="{{ $first->payment_account_id }}" selected>
                                @if($first->payment_mode == 'bank')
                                    {{ $first->account->bank_name ?? 'Account' }} ({{ $first->account->account_number ?? '' }})
                                @else
                                    {{ $first->account->name ?? 'Cash' }}
                                @endif
                            </option>
                        </select>
                        <small id="bulk_account_balance" class="text-muted d-block mt-1 font-weight-bold" style="font-size: 0.75rem;"></small>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">Expected total <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="total_expected_amount" id="total_expected_amount" class="form-control form-control-sm erp-input font-weight-bold" style="background-color: #fefce8; border-color: #fef08a; color: #854d0e;" value="{{ $groupedAdjustments->sum('amount') }}" required>
                    </div>
                    <div class="col-md-12">
                        <div id="amount_alert" class="alert alert-warning py-1 mt-2 mb-0" style="display:none; font-size: 0.85rem; border-radius: 6px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Batch Total: <strong id="running_total_display">0</strong> | Remaining: <strong id="remaining_total_display">0</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table Card -->
            <div class="erp-card p-0 mb-2">
                <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
                    <span class="font-weight-bold" style="font-size: 0.85rem; color: #0f172a;">
                        Adjustment entries
                    </span>
                    <button type="button" class="btn-erp btn-erp-secondary btn-sm" id="add_more">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table erp-table table-bordered mb-0 w-100" id="adjustment_table">
                        <thead>
                            <tr>
                                <th style="min-width: 320px;">Master type & item (FROM)</th>
                                <th style="width: 200px;">Amount</th>
                                <th>Remarks</th>
                                <th class="text-center" style="width: 50px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="adjustment_rows">
                            @foreach($groupedAdjustments as $index => $group)
                            @php 
                                $isShipment = in_array($group->adjustment_master_id, [14, 16, 18]);
                            @endphp
                            <tr class="adjustment-row" data-row="{{ $index }}">
                                <td>
                                    <select name="adjustment_master_id[]" class="form-control form-control-sm erp-input select2 master-type" required style="width: 100%;">
                                        <option value="">-- Master Type --</option>
                                        @foreach($masters as $master)
                                            <option value="{{ $master->id }}" {{ $group->adjustment_master_id == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                        @endforeach
                                    </select>
                                    
                                    <div class="mt-2">
                                        <select name="ref_id[]" class="form-control form-control-sm erp-input select2 master-item" required style="width: 100%;">
                                            <option value="{{ $group->ref_id }}" selected>{{ $group->parent_name }}</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="amount[]" class="form-control form-control-sm erp-input row-amount font-weight-bold" value="{{ $group->amount }}" required style="height: 38px; font-size: 1.05rem;">
                                </td>
                                <td>
                                    <textarea name="remarks[]" class="form-control form-control-sm erp-input" rows="2">{{ str_replace('[Dist] ', '', $group->remarks) }}</textarea>
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc;">
                                <th class="text-right font-weight-bold">Running total:</th>
                                <th id="footer_running_total" class="font-weight-bold text-success" style="font-size: 1rem;">0.00</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn-erp btn-erp-primary px-4 py-2" style="font-size: 0.95rem;">
                    <i class="fas fa-save mr-1"></i> Update Adjustment Batch
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template Row (Hidden) -->
<table style="display:none;">
    <tr id="row_template">
        <td>
            <select name="adjustment_master_id[]" class="form-control form-control-sm erp-input master-type" required style="width: 100%;">
                <option value="">-- Master Type --</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                @endforeach
            </select>
            <div class="mt-2">
                <select name="ref_id[]" class="form-control form-control-sm erp-input master-item" required disabled style="width: 100%;">
                    <option value="">-- Select Item --</option>
                </select>
            </div>
        </td>
        <td>
            <input type="number" step="0.01" name="amount[]" class="form-control form-control-sm erp-input row-amount font-weight-bold" placeholder="Amount" required style="height: 38px; font-size: 1.05rem;">
        </td>
        <td>
            <textarea name="remarks[]" class="form-control form-control-sm erp-input" rows="2" placeholder="Remarks"></textarea>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove row">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</table>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
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

        // Run calculation initially
        calculateTotals();

        // Add More Rows
        $('#add_more').on('click', function() {
            var newRow = $('#row_template').clone().removeAttr('id');
            $('#adjustment_rows').append(newRow);
            newRow.find('select').addClass('select2').select2({ width: '100%' });
            populateItemsForRow(newRow);
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

        // Bulk Payment Mode Change
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

        var allMasterItems = [];

        function fetchAllItems() {
            return $.ajax({
                url: "{{ route('admin.payment.adjustment.getSubMastersAll') }}",
                type: "GET",
                success: function(data) {
                    allMasterItems = data;
                }
            });
        }

        function populateItemsForRow($row) {
            var $itemSelect = $row.find('.master-item');
            var masterId = $row.find('.master-type').val();
            var currentVal = $itemSelect.val();

            if (masterId) {
                var filtered = allMasterItems.filter(function(item) {
                    return item.master_id == masterId;
                });
                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(filtered, function(key, value) {
                    var selected = (value.id == currentVal) ? 'selected' : '';
                    $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '" '+selected+'>' + value.name + '</option>');
                });
                $itemSelect.prop('disabled', false).trigger('change.select2');
            } else {
                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(allMasterItems, function(key, value) {
                    var selected = (value.id == currentVal) ? 'selected' : '';
                    $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '" '+selected+'>' + value.name + '</option>');
                });
                $itemSelect.prop('disabled', false).trigger('change.select2');
            }
        }

        fetchAllItems();

        $(document).on('change', '.master-type', function() {
            populateItemsForRow($(this).closest('tr'));
        });

        $(document).on('change', '.master-item', function() {
            var $row = $(this).closest('tr');
            var $masterTypeSelect = $row.find('.master-type');
            var selectedOption = $(this).find(':selected');
            var masterId = selectedOption.data('master-id');
            var refId = $(this).val();

            if (masterId && (!$masterTypeSelect.val() || $masterTypeSelect.val() == "")) {
                $masterTypeSelect.val(masterId).trigger('change');
                return;
            }

            var balance = selectedOption.data('balance');
            var $balanceDisplay = $row.find('.item-balance-display');
            if ($balanceDisplay.length == 0) {
                $balanceDisplay = $('<small class="text-muted item-balance-display d-block font-weight-bold" style="font-size: 0.72rem;"></small>');
                $(this).parent().append($balanceDisplay);
            }

            if (balance !== undefined && refId) {
                var bal = parseFloat(balance);
                var typeStr = (bal < 0) ? ' (Dr)' : ' (Cr)';
                $balanceDisplay.text('Current Balance: ' + Math.abs(bal).toFixed(2) + typeStr);
            } else {
                $balanceDisplay.text('');
            }
        });

        if ($('#bulk_account_id').val()) {
            $('#bulk_account_id').trigger('change');
        }
    });
</script>
@endpush
