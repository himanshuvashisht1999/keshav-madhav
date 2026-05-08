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
                                        
                                        <!-- Item Selection (Always use this as a display/selector) -->
                                        <select class="form-control select2 master-item mt-2" required>
                                            <option value="{{ $group->parent_id }}" selected>{{ $group->parent_name }}</option>
                                        </select>
                                        <!-- Real input passed to controller -->
                                        <input type="hidden" name="ref_id[]" class="real-ref-id" value="{{ $group->ref_id }}">
                                        
                                        <div class="shipment-selection-area mt-2" style="{{ $isShipment ? 'display:block;' : 'display:none;' }} background: #f8f9fa; border: 1px solid #dee2e6; padding: 12px; border-radius: 8px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="text-sm font-weight-bold mb-0">Select Items for Adjustment:</label>
                                                <span class="badge badge-info selection-stats">0 selected</span>
                                            </div>
                                            <div class="shipment-list mb-2">
                                                @if($isShipment)
                                                    @foreach($group->items as $item)
                                                    <button type="button" class="btn btn-xs btn-success selected m-1 quick-add-ship" 
                                                        data-id="{{ $item->id }}" data-no="{{ $item->name }}" data-balance="{{ $item->balance }}">
                                                        <i class="fas fa-check"></i> {{ $item->name }} (₹{{ number_format($item->balance, 0) }})
                                                    </button>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <div class="total-selected-balance border-top pt-1 mt-1" style="display:none;">
                                                <small class="text-muted font-weight-bold">Total Balance: ₹<span class="selected-balance-val">0.00</span></small>
                                            </div>
                                        </div>
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
        <select class="form-control master-item mt-2" required disabled>
            <option value="">-- Select Item --</option>
        </select>
        <input type="hidden" name="ref_id[]" class="real-ref-id">
        <div class="shipment-selection-area mt-2" style="display:none; background: #f8f9fa; border: 1px solid #dee2e6; padding: 12px; border-radius: 8px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="text-sm font-weight-bold mb-0">Select Items for Adjustment:</label>
                <span class="badge badge-info selection-stats">0 selected</span>
            </div>
            <div class="shipment-list mb-2"></div>
            <div class="total-selected-balance border-top pt-1 mt-1" style="display:none;">
                <small class="text-muted font-weight-bold">Total Balance: ₹<span class="selected-balance-val">0.00</span></small>
            </div>
        </div>
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

        var vendorMasterId = "{{ $vendorMaster->id ?? '' }}";
        var domesticMasterId = "{{ $domesticMaster->id ?? '' }}";
        var shipmentMasterId = "{{ $shipmentMaster->id ?? '' }}";

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

        function previewDistribution($row) {
            var amount = parseFloat($row.find('.row-amount').val()) || 0;
            var $badges = $row.find('.quick-add-ship.selected');
            
            $badges.each(function() {
                var $b = $(this);
                var bal = parseFloat($b.data('balance'));
                $b.find('.rem-text').remove();

                if (amount <= 0) {
                    $b.removeClass('btn-success btn-info').addClass('btn-primary').css('opacity', '0.7');
                    return;
                }

                $b.css('opacity', '1');
                if (amount >= bal) {
                    $b.removeClass('btn-primary btn-info').addClass('btn-success');
                    amount -= bal;
                } else {
                    $b.removeClass('btn-primary btn-success').addClass('btn-info');
                    $b.append(`<span class="ml-1 small rem-text" style="font-size: 0.7rem; opacity: 0.9;">(Rem: ₹${(bal - amount).toFixed(0)})</span>`);
                    amount = 0;
                }
            });
        }

        function updateRowSelection($row, isInitial = false) {
            var selectedIds = [];
            var totalBalance = 0;
            var textParts = [];
            
            var $selected = $row.find('.quick-add-ship.selected');
            $selected.each(function() {
                var btn = $(this);
                if (btn.find('i').length == 0) btn.prepend('<i class="fas fa-check"></i> ');
                
                var id = btn.data('id');
                selectedIds.push(id.toString());
                totalBalance += parseFloat(btn.data('balance')) || 0;
                textParts.push(btn.data('no'));
            });
            
            $row.find('.quick-add-ship:not(.selected) i').remove();
            
            // Update stats
            $row.find('.selection-stats').text(selectedIds.length + ' selected');
            $row.find('.selected-balance-val').text(totalBalance.toLocaleString());
            
            if (selectedIds.length > 0) {
                $row.find('.total-selected-balance').show();
                var collectiveText = selectedIds.length + " Shipments: " + textParts.join(', ');
                var collectiveVal = selectedIds.join(',');
                
                // Update Select2 display
                var $itemSelect = $row.find('.master-item');
                var $opt = $itemSelect.find('option[property="collective"]');
                if ($opt.length == 0) {
                    $opt = $('<option property="collective"></option>');
                    $itemSelect.append($opt);
                }
                $opt.text(collectiveText).val(collectiveVal).prop('selected', true);
                if (!isInitial) $itemSelect.trigger('change.select2');
                
                $row.find('.real-ref-id').val(collectiveVal);
            } else {
                $row.find('.total-selected-balance').hide();
            }

            previewDistribution($row);
        }

        // Initialize with data
        calculateTotals();
        
        // Auto-load shipments and initialize existing selections
        $('.adjustment-row').each(function() {
            var $row = $(this);
            var masterId = $row.find('.master-type').val();
            var $itemSelect = $row.find('.master-item');
            var refId = $itemSelect.val();
            var $realRefInput = $row.find('.real-ref-id');
            var selectedIds = $realRefInput.val() ? $realRefInput.val().split(',') : [];
            var isShipmentRow = [14, 16, 18, vendorMasterId, domesticMasterId].includes(Number(masterId));

            // Initialize PHP elements
            updateRowSelection($row, true);

            if (isShipmentRow && refId) {
                $.ajax({
                    url: "{{ route('admin.payment.adjustment.getVendorShipments') }}",
                    type: "GET",
                    data: { vendor_id: refId, master_id: masterId, batch_id: "{{ $batchId }}" },
                    success: function(data) {
                        var $list = $row.find('.shipment-list');
                        $list.empty();
                        if (data.length > 0) {
                            $.each(data, function(i, ship) {
                                var isSelected = selectedIds.includes(ship.id.toString());
                                var btnClass = isSelected ? 'btn-success selected' : 'btn-outline-primary';
                                
                                $list.append(`
                                    <button type="button" class="btn btn-xs ${btnClass} m-1 quick-add-ship" 
                                        data-id="${ship.id}" data-no="${ship.shipment_no}" data-balance="${ship.balance}">
                                        ${ship.shipment_no} (₹${parseFloat(ship.balance).toFixed(0)})
                                    </button>
                                `);
                            });
                            $row.find('.shipment-selection-area').show();
                            updateRowSelection($row, true);
                        }
                    }
                });
            }
        });
        
        // Trigger account load to populate info but don't clear selected
        var currentAccountId = "{{ $first->payment_account_id }}";
        $.ajax({
            url: "{{ route('admin.payment.adjustment.getAccounts') }}",
            type: "GET",
            data: { mode: $('#bulk_payment_mode').val() },
            success: function(data) {
                $('#bulk_account_id').empty().append('<option value="">-- Select Account --</option>');
                $.each(data, function(key, value) {
                    var selected = (value.id == currentAccountId) ? 'selected' : '';
                    $('#bulk_account_id').append('<option value="' + value.id + '" data-balance="' + value.balance + '" '+selected+'>' + value.name + '</option>');
                });
                $('#bulk_account_id').trigger('change');
            }
        });

        $(document).on('input', '.row-amount, #total_expected_amount', function() {
            calculateTotals();
        });

        $('#add_more').on('click', function() {
            var newRow = $('#row_template').clone().removeAttr('id');
            $('#adjustment_rows').append(newRow);
            newRow.find('select').addClass('select2').select2({ width: '100%' });
            populateAllItemSelects();
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
                var currentValue = $select.val();
                
                if (!currentMasterId || currentMasterId == "") {
                    // Populate with all items if no master type is selected
                    $select.empty().append('<option value="">-- Select Item --</option>');
                    $.each(allMasterItems, function(key, value) {
                        var selected = (value.id == currentValue) ? 'selected' : '';
                        $select.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '" '+selected+'>' + value.name + '</option>');
                    });
                    $select.prop('disabled', false).trigger('change.select2');
                } else {
                    // Filter items if master type is already selected (e.g. on page load for existing rows)
                    var filteredItems = allMasterItems.filter(function(item) {
                        return item.master_id == currentMasterId;
                    });
                    
                    var existingOptions = $select.find('option').map(function() { return $(this).val(); }).get();
                    
                    $.each(filteredItems, function(key, value) {
                        if (!existingOptions.includes(value.id.toString())) {
                            $select.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
                        }
                    });
                    $select.prop('disabled', false).trigger('change.select2');
                }
            });
        }

        fetchAllItems();

        $(document).on('change', '.master-type', function(e, isAutoFill) {
            if (isAutoFill) return; // Prevent loop

            var $row = $(this).closest('tr');
            var masterId = $(this).val();
            var $itemSelect = $row.find('.master-item');
            
            // Clear current items and related areas
            $row.find('.shipment-selection-area').hide().find('.shipment-list').empty();
            $row.find('.real-ref-id').val('');

            if (masterId) {
                // Filter allMasterItems for this masterId
                var filteredItems = allMasterItems.filter(function(item) {
                    return item.master_id == masterId;
                });

                $itemSelect.empty().append('<option value="">-- Select Item --</option>');
                $.each(filteredItems, function(key, value) {
                    var balanceText = (value.balance !== undefined) ? ' (Bal: ' + value.balance + ')' : '';
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
            var refId = $(this).val();

            if (masterId && (!$masterTypeSelect.val() || $masterTypeSelect.val() == "")) {
                $masterTypeSelect.val(masterId).trigger('change', [true]);
                $(this).trigger('change');
                return;
            }

            // Balance display
            var balance = selectedOption.data('balance');
            var $balanceDisplay = $row.find('.item-balance-display');
            if ($balanceDisplay.length == 0) {
                $balanceDisplay = $('<small class="text-muted item-balance-display d-block"></small>');
                $(this).after($balanceDisplay);
            }
            if (balance !== undefined && refId && !refId.includes(',')) {
                $balanceDisplay.text('Current Balance: ' + balance);
            } else {
                $balanceDisplay.text('');
            }

            var isShipmentRow = [14, 16, 18, vendorMasterId, domesticMasterId].includes(Number($masterTypeSelect.val()));

            if (!isShipmentRow) {
                $row.find('.real-ref-id').val(refId);
            }

            if (isShipmentRow && refId && !refId.includes(',')) {
                $.ajax({
                    url: "{{ route('admin.payment.adjustment.getVendorShipments') }}",
                    type: "GET",
                    data: { vendor_id: refId, master_id: $masterTypeSelect.val(), batch_id: "{{ $batchId }}" },
                    success: function(data) {
                        var $list = $row.find('.shipment-list');
                        $list.empty();
                        if (data.length > 0) {
                            $.each(data, function(i, ship) {
                                $list.append(`
                                    <button type="button" class="btn btn-xs btn-outline-primary m-1 quick-add-ship" 
                                        data-id="${ship.id}" data-no="${ship.shipment_no}" data-balance="${ship.balance}">
                                        ${ship.shipment_no} (₹${parseFloat(ship.balance).toFixed(0)})
                                    </button>
                                `);
                            });
                            $row.find('.shipment-selection-area').slideDown();
                        } else {
                            $row.find('.shipment-selection-area').hide();
                        }
                    }
                });
            } else if (!refId || !refId.includes(',')) {
                $row.find('.shipment-selection-area').hide();
            }
        });

        $(document).on('click', '.quick-add-ship', function() {
            var $btn = $(this);
            var $row = $btn.closest('tr');
            $btn.toggleClass('btn-outline-primary btn-success selected');
            updateRowSelection($row);
        });

        $(document).on('input', '.row-amount', function() {
            previewDistribution($(this).closest('tr'));
        });

        $('#bulk_account_id').on('change', function() {
            var balance = $(this).find(':selected').data('balance');
            if (balance !== undefined) {
                $('#bulk_account_balance').text('Available Balance: ' + balance);
            }
        });
    });
</script>
@endpush
