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
                                            <!-- Hidden field for multi-shipment IDs -->
                                            <input type="hidden" name="ref_id_multi[]" class="ref-id-multi">
                                            
                                            <!-- In-line Shipment Picker -->
                                            <div class="shipment-selection-area mt-2" style="display:none; max-height: 200px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; padding: 10px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <p class="mb-0 small font-weight-bold text-muted shipment-context-label">Select Items for Adjustment:</p>
                                                    <span class="badge badge-info selection-stats">0 selected</span>
                                                </div>
                                                <div class="shipment-list">
                                                    <!-- Clickable shipment badges populate here -->
                                                </div>
                                                <div class="mt-2 pt-2 border-top total-selected-balance" style="display:none;">
                                                    <small class="text-primary font-weight-bold">Total Balance: ₹<span class="selected-balance-val">0.00</span></small>
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

<!-- Shipment Selection Modal -->
<div class="modal fade" id="shipmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Select Items</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select_all_shipments"></th>
                                <th>Date</th>
                                <th>Ref No</th>
                                <th>Total Amount</th>
                                <th>Balance Amount</th>
                            </tr>
                        </thead>
                        <tbody id="shipments_modal_body">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add_selected_shipments">Add Selected Adjustments</button>
            </div>
        </div>
    </div>
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
            <!-- Hidden field for multi-shipment IDs -->
            <input type="hidden" name="ref_id_multi[]" class="ref-id-multi">
            
            <!-- In-line Shipment Picker -->
            <div class="shipment-selection-area mt-2" style="display:none; max-height: 200px; overflow-y: auto; background: #fff; border: 1px solid #ced4da; padding: 10px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="mb-0 small font-weight-bold text-muted shipment-context-label">Select Items for Adjustment:</p>
                    <span class="badge badge-info selection-stats">0 selected</span>
                </div>
                <div class="shipment-list">
                    <!-- Clickable shipment badges populate here -->
                </div>
                <div class="mt-2 pt-2 border-top total-selected-balance" style="display:none;">
                    <small class="text-primary font-weight-bold">Total Balance: ₹<span class="selected-balance-val">0.00</span></small>
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

        // Show balance for bulk account
        $('#bulk_account_id').on('change', function() {
            var balance = $(this).find(':selected').data('balance');
            if (balance !== undefined) {
                $('#bulk_account_balance').text('Available Balance: ' + balance);
            } else {
                $('#bulk_account_balance').text('');
            }
        });

        // Handle Master Type Change
        $(document).on('change', '.master-type', function() {
            var $row = $(this).closest('tr');
            var masterId = $(this).val();
            var $itemSelect = $row.find('.master-item');
            
            // Clear current items
            $itemSelect.empty().append('<option value="">-- Select Item --</option>').prop('disabled', true).trigger('change');

            if (masterId) {
                $.ajax({
                    url: "{{ route('admin.payment.adjustment.getSubMasters') }}",
                    type: "GET",
                    data: { master_id: masterId },
                    success: function(data) {
                        var bulkAccountId = $('#bulk_account_id').val();
                        var bulkMode = $('#bulk_payment_mode').val();
                        var masterText = $row.find('.master-type :selected').text().toLowerCase();
                        var isFinancialSource = masterText.includes('bank') || masterText.includes('cash');

                        $.each(data, function(key, value) {
                            if (isFinancialSource && bulkMode && bulkMode.startsWith(masterText.split(' ')[0].toLowerCase()) && value.id == bulkAccountId) {
                                return;
                            }
                            var balanceText = (value.balance !== undefined) ? ' (Bal: ' + value.balance + ')' : '';
                            $itemSelect.append('<option value="' + value.id + '" data-balance="' + value.balance + '">' + value.name + balanceText + '</option>');
                        });
                        $itemSelect.prop('disabled', false).trigger('change');
                    }
                });
            }
        });

        // Show balance for master item
        $(document).on('change', '.master-item', function() {
            var $row = $(this).closest('tr');
            var balance = $(this).find(':selected').data('balance');
            var $balanceDisplay = $row.find('.item-balance-display');
            
            if ($balanceDisplay.length == 0) {
                $balanceDisplay = $('<small class="text-muted item-balance-display"></small>');
                $(this).after($balanceDisplay);
            }

            if (balance !== undefined && !$(this).val().includes(',')) {
                $balanceDisplay.text('Current Balance: ' + balance);
            } else {
                $balanceDisplay.text('');
            }
        });

        // Handle Master Item Change (Vendor Specific - In-line Picker)
        var vendorMasterId = "{{ $vendorMaster->id ?? '' }}";
        var domesticMasterId = "{{ $domesticMaster->id ?? '' }}";
        var shipmentMasterId = "{{ $shipmentMaster->id ?? '' }}";

        $(document).on('change', '.master-item', function() {
            var $row = $(this).closest('tr');
            var masterId = $row.find('.master-type').val();
            var refId = $(this).val();

            // Show picker if we selected a vendor (ID 16) or Domestic (ID 18)
            if ((masterId == vendorMasterId || masterId == domesticMasterId) && refId && !refId.includes(',')) {
                $.ajax({
                    url: "{{ route('admin.payment.adjustment.getVendorShipments') }}",
                    type: "GET",
                    data: { 
                        vendor_id: refId,
                        master_id: masterId
                    },
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

        // Toggle Shipment Selection
        $(document).on('click', '.quick-add-ship', function() {
            var $btn = $(this);
            var $row = $btn.closest('tr');
            
            $btn.toggleClass('btn-outline-primary btn-primary selected');
            updateRowSelection($row);
        });

        function updateRowSelection($row) {
            var selectedIds = [];
            var totalBalance = 0;
            var textParts = [];
            
            $row.find('.quick-add-ship.selected').each(function() {
                var id = $(this).data('id');
                selectedIds.push(id);
                totalBalance += parseFloat($(this).data('balance')) || 0;
                textParts.push($(this).data('no'));
            });
            
            // Update hidden values and stats
            $row.find('.selection-stats').text(selectedIds.length + ' selected');
            $row.find('.selected-balance-val').text(totalBalance.toFixed(2));
            
            if (selectedIds.length > 0) {
                $row.find('.total-selected-balance').show();
                // Inject collective text into the Select2 display if possible
                var collectiveText = selectedIds.length + " Shipments: " + textParts.join(', ');
                var collectiveVal = selectedIds.join(',');
                
                // Update the real ref_id input with the comma-separated list
                var $itemSelect = $row.find('.master-item');
                if ($itemSelect.find(`option[value="${collectiveVal}"]`).length == 0) {
                    $itemSelect.append(new Option(collectiveText, collectiveVal, true, true));
                }
                $itemSelect.val(collectiveVal).trigger('change.select2');
            } else {
                $row.find('.total-selected-balance').hide();
            }

            previewDistribution($row);
        }

        function previewDistribution($row) {
            var amount = parseFloat($row.find('.row-amount').val()) || 0;
            var $badges = $row.find('.quick-add-ship.selected');
            
            $badges.each(function() {
                var $b = $(this);
                var bal = parseFloat($b.data('balance'));
                $b.find('.rem-text').remove();

                if (amount <= 0) {
                    $b.removeClass('btn-success btn-info').addClass('btn-primary');
                    return;
                }

                if (amount >= bal) {
                    $b.removeClass('btn-primary btn-info').addClass('btn-success');
                    amount -= bal;
                } else {
                    $b.removeClass('btn-primary btn-success').addClass('btn-info');
                    $b.append(`<span class="ml-1 small rem-text">(Rem: ₹${(bal - amount).toFixed(0)})</span>`);
                    amount = 0;
                }
            });
        }

        $(document).on('input', '.row-amount', function() {
            previewDistribution($(this).closest('tr'));
        });
    });
</script>
@endpush
