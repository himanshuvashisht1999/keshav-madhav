@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Payment Adjustment Batch</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payment.adjustment.index') }}">Adjustments</a></li>
                        <li class="breadcrumb-item active">Edit Batch</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Editing Batch: <code>{{ $batchId }}</code></h3>
                </div>
                <form action="{{ route('admin.payment.adjustment.update', $batchId) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row mb-3 bg-light p-3 border rounded shadow-sm">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ $first->date }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" id="bulk_type" class="form-control" required>
                                        <option value="credit" {{ $first->type == 'credit' ? 'selected' : '' }}>Credit (+)</option>
                                        <option value="debit" {{ $first->type == 'debit' ? 'selected' : '' }}>Debit (-)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>To Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="bulk_payment_mode" class="form-control" required>
                                        <option value="">-- Mode --</option>
                                        <option value="bank" {{ $first->payment_mode == 'bank' ? 'selected' : '' }}>Bank</option>
                                        <option value="cash" {{ $first->payment_mode == 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>To Account <span class="text-danger">*</span></label>
                                    <select name="payment_account_id" id="bulk_account_id" class="form-control select2" required>
                                        <option value="{{ $first->payment_account_id }}" selected>
                                            @if($first->payment_mode == 'bank')
                                                {{ $first->account->bank_name ?? 'Account' }} ({{ $first->account->account_number ?? '' }})
                                            @else
                                                {{ $first->account->name ?? 'Cash' }}
                                            @endif
                                        </option>
                                    </select>
                                    <small id="bulk_account_balance" class="text-muted"></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Expected Total <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="total_expected_amount" id="total_expected_amount" class="form-control font-weight-bold" style="background-color: #fffde7;" value="{{ $groupedAdjustments->sum('amount') }}" required>
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
                                @foreach($groupedAdjustments as $index => $group)
                                @php 
                                    $isShipment = in_array($group->adjustment_master_id, [14, 16, 18]);
                                @endphp
                                <tr class="adjustment-row" data-row="{{ $index }}">
                                    <td>
                                        <select name="adjustment_master_id[]" class="form-control select2 master-type" required>
                                            <option value="">-- Master Type --</option>
                                            @foreach($masters as $master)
                                                <option value="{{ $master->id }}" {{ $group->adjustment_master_id == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                            @endforeach
                                        </select>
                                        
                                        <!-- Item Selection -->
                                        <select name="ref_id[]" class="form-control select2 master-item mt-2" required>
                                            <option value="{{ $group->ref_id }}" selected>{{ $group->parent_name }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="amount[]" class="form-control row-amount" value="{{ $group->amount }}" required style="height: 40px; font-size: 1.2rem;">
                                    </td>
                                    <td>
                                        <textarea name="remarks[]" class="form-control" rows="2">{{ str_replace('[Dist] ', '', $group->remarks) }}</textarea>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
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
                    <button type="submit" class="btn btn-warning">Update Adjustment Batch</button>
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

        calculateTotals();

        $(document).on('input', '.row-amount, #total_expected_amount', function() {
            calculateTotals();
        });

        $('#add_more').on('click', function() {
            var newRow = $('#row_template').clone().removeAttr('id');
            $('#adjustment_rows').append(newRow);
            newRow.find('select').addClass('select2').select2({ width: '100%' });
            // For new rows, we might want to populate items if master type is selected
            if (allMasterItems.length > 0) {
                populateItemsForRow(newRow);
            }
            calculateTotals();
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#adjustment_rows tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                alert('At least one row is required.');
            }
        });

        $('form').on('submit', function(e) {
            var expected = parseFloat($('#total_expected_amount').val()) || 0;
            var running = 0;
            $('.row-amount').each(function() {
                running += parseFloat($(this).val()) || 0;
            });

            if (Math.abs(expected - running) > 0.01) {
                e.preventDefault();
                alert('Total amounts do not match!');
                return false;
            }
        });

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

        function fetchAllItems() {
            return $.ajax({
                url: "{{ route('admin.payment.adjustment.getSubMastersAll') }}",
                type: "GET",
                success: function(data) {
                    allMasterItems = data;
                    // Populate all existing rows
                    $('.adjustment-row').each(function() {
                        populateItemsForRow($(this));
                    });
                }
            });
        }

        function populateItemsForRow($row) {
            var $masterTypeSelect = $row.find('.master-type');
            var $itemSelect = $row.find('.master-item');
            var masterId = $masterTypeSelect.val();
            var currentValue = $itemSelect.val();

            if (masterId) {
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
                    var selected = (currentValue && currentValue == value.id) ? 'selected' : '';
                    $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '" '+selected+'>' + value.name + balanceText + '</option>');
                });
                $itemSelect.prop('disabled', false).trigger('change.select2');
            } else {
                // If no master type, show all (legacy behavior)
                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(allMasterItems, function(key, value) {
                    var selected = (currentValue && currentValue == value.id) ? 'selected' : '';
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

            // Auto-select master type if item is selected first
            if (masterId && (!$masterTypeSelect.val() || $masterTypeSelect.val() == "")) {
                $masterTypeSelect.val(masterId).trigger('change');
                return;
            }

            var balance = selectedOption.data('balance');
            var $balanceDisplay = $row.find('.item-balance-display');
            if ($balanceDisplay.length == 0) {
                $balanceDisplay = $('<small class="text-muted item-balance-display d-block"></small>');
                $(this).after($balanceDisplay);
            }

            if (balance !== undefined && refId) {
                var bal = parseFloat(balance);
                var typeStr = (bal < 0) ? ' (Dr)' : ' (Cr)';
                $balanceDisplay.text('Current Balance: ' + Math.abs(bal).toFixed(2) + typeStr);
            } else {
                $balanceDisplay.text('');
            }
        });

        // Initialize account balance on load if already selected
        if ($('#bulk_account_id').val()) {
            $('#bulk_account_id').trigger('change');
        }
    });
</script>
@endpush
