@extends('admin.layouts.app')
@section('title', (isset($voucher) ? 'Edit' : 'Create') . ' Journal Voucher')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-book text-primary"></i> {{ isset($voucher) ? 'Edit' : 'New' }} Journal Voucher
            </div>
            <p class="erp-page-subtitle mb-0">Record double-entry accounting journal vouchers with multi-entity debit and credit balance adjustments</p>
        </div>
        <div class="erp-header-actions">
            @if(isset($voucher))
                <a href="{{ route('admin.payment.journal-voucher.show', $voucher->id) }}" class="btn-erp btn-erp-outline mr-1">
                    <i class="fas fa-eye mr-1"></i> View
                </a>
            @endif
            <a href="{{ route('admin.payment.journal-voucher.index') }}" class="btn-erp btn-erp-secondary">
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

    <form action="{{ isset($voucher) ? route('admin.payment.journal-voucher.update', $voucher->id) : route('admin.payment.journal-voucher.store') }}" method="POST">
        @csrf

        <!-- Voucher Details Card -->
        <div class="erp-card p-3 mb-2">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="erp-label"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Voucher Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control form-control-sm erp-input" value="{{ isset($voucher) ? $voucher->date : date('Y-m-d') }}" required>
                </div>
                <div class="col-md-9 mb-2">
                    <label class="erp-label"><i class="fas fa-comment-alt mr-1 text-muted"></i> Global Narration</label>
                    <input type="text" name="narration" class="form-control form-control-sm erp-input" value="{{ isset($voucher) ? $voucher->narration : '' }}" placeholder="Enter global narration for this voucher...">
                </div>
            </div>
        </div>

        <!-- Journal Rows Table Card -->
        <div class="erp-card p-0 mb-2">
            <div class="erp-card-header d-flex justify-content-between align-items-center">
                <div class="erp-card-title">
                    <i class="fas fa-balance-scale"></i> Journal Entries (Debit &amp; Credit)
                </div>
                <button type="button" class="btn-erp btn-erp-primary" id="add_more" style="padding: 2px 10px; font-size: 11.5px;">
                    <i class="fas fa-plus mr-1"></i> Add Entry
                </button>
            </div>
            <div class="table-responsive">
                <table class="table erp-table table-bordered mb-0" id="journal_table">
                    <thead>
                        <tr>
                            <th style="min-width: 320px;">Party / Ledger Selection</th>
                            <th style="width: 140px;">Type</th>
                            <th class="text-right" style="width: 160px;">Amount (₹)</th>
                            <th>Item Narration</th>
                            <th class="text-center" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="journal_rows">
                        @if(isset($voucher))
                            @foreach($voucher->items as $index => $item)
                                <tr class="journal-row">
                                    <td>
                                        <select name="master_type[]" class="form-control form-control-sm erp-input select2 master-type" required style="width: 100%;">
                                            <option value="">-- Master Type --</option>
                                            @foreach($masters as $master)
                                                <option value="{{ $master->id }}" {{ $item->master_type == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="master_id[]" class="form-control form-control-sm erp-input select2 master-item mt-1" required style="width: 100%;">
                                            <option value="">-- Select Item --</option>
                                            <option value="{{ $item->master_id }}" selected>Loading...</option>
                                        </select>
                                        <small class="text-muted item-balance-display d-block mt-1 font-weight-bold" style="font-size: 11px;"></small>
                                    </td>
                                    <td>
                                        <select name="type[]" class="form-control form-control-sm erp-input row-type" required>
                                            <option value="debit" {{ $item->type == 'debit' ? 'selected' : '' }}>Debit (Dr)</option>
                                            <option value="credit" {{ $item->type == 'credit' ? 'selected' : '' }}>Credit (Cr)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="amount[]" class="form-control form-control-sm erp-input text-right row-amount font-weight-bold" value="{{ $item->amount }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="item_narration[]" class="form-control form-control-sm erp-input" value="{{ $item->narration }}" placeholder="Entry narration...">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="erp-action-btn erp-btn-delete remove-row border-0" title="Remove Entry"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="journal-row">
                                <td>
                                    <select name="master_type[]" class="form-control form-control-sm erp-input select2 master-type" required style="width: 100%;">
                                        <option value="">-- Master Type --</option>
                                        @foreach($masters as $master)
                                            <option value="{{ $master->id }}">{{ $master->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="master_id[]" class="form-control form-control-sm erp-input select2 master-item mt-1" required disabled style="width: 100%;">
                                        <option value="">-- Select Item --</option>
                                    </select>
                                    <small class="text-muted item-balance-display d-block mt-1 font-weight-bold" style="font-size: 11px;"></small>
                                </td>
                                <td>
                                    <select name="type[]" class="form-control form-control-sm erp-input row-type" required>
                                        <option value="debit">Debit (Dr)</option>
                                        <option value="credit">Credit (Cr)</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="amount[]" class="form-control form-control-sm erp-input text-right row-amount font-weight-bold" placeholder="0.00" required>
                                </td>
                                <td>
                                    <input type="text" name="item_narration[]" class="form-control form-control-sm erp-input" placeholder="Entry narration...">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="erp-action-btn erp-btn-delete remove-row border-0" title="Remove Entry"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="erp-table-grand-total">
                            <th class="text-right font-weight-bold">Summary &amp; Balance:</th>
                            <th class="text-center">
                                <span id="balance_diff" class="badge-erp-status badge-erp-warning">Diff: ₹0.00</span>
                            </th>
                            <th colspan="2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold" style="color: #05421c;">Total DR: ₹<span id="total_debit_display">0.00</span></span>
                                    <span class="text-danger font-weight-bold">Total CR: ₹<span id="total_credit_display">0.00</span></span>
                                </div>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="erp-card p-3 mb-2 d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="fas fa-info-circle mr-1"></i> Total Debit must exactly equal Total Credit before saving.
            </span>
            <div class="d-flex" style="gap: 8px;">
                <a href="{{ route('admin.payment.journal-voucher.index') }}" class="btn-erp btn-erp-secondary">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary" id="submit_btn" disabled>
                    <i class="fas fa-save mr-1"></i> {{ isset($voucher) ? 'Update Voucher' : 'Save Voucher' }}
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Template Row --}}
<table style="display:none;">
    <tr id="row_template">
        <td>
            <select name="master_type[]" class="form-control form-control-sm erp-input master-type" required style="width: 100%;">
                <option value="">-- Master Type --</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                @endforeach
            </select>
            <select name="master_id[]" class="form-control form-control-sm erp-input master-item mt-1" required disabled style="width: 100%;">
                <option value="">-- Select Item --</option>
            </select>
            <small class="text-muted item-balance-display d-block mt-1 font-weight-bold" style="font-size: 11px;"></small>
        </td>
        <td>
            <select name="type[]" class="form-control form-control-sm erp-input row-type" required>
                <option value="debit">Debit (Dr)</option>
                <option value="credit">Credit (Cr)</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="amount[]" class="form-control form-control-sm erp-input text-right row-amount font-weight-bold" placeholder="0.00" required>
        </td>
        <td>
            <input type="text" name="item_narration[]" class="form-control form-control-sm erp-input" placeholder="Entry narration...">
        </td>
        <td class="text-center">
            <button type="button" class="erp-action-btn erp-btn-delete remove-row border-0" title="Remove Entry"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
</table>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

    var allMasterItems = [];

    function fetchAllItems() {
        return $.ajax({
            url: "{{ route('admin.payment.adjustment.getSubMastersAll') }}",
            type: "GET",
            success: function(data) {
                allMasterItems = data;
                populateAllItemSelects();
                
                @if(isset($voucher))
                    @foreach($voucher->items as $index => $item)
                        var $row = $('#journal_rows tr').eq({{ $index }});
                        $row.find('.master-item').val("{{ $item->master_id }}").trigger('change.select2');
                    @endforeach
                @endif
            }
        });
    }

    function populateAllItemSelects() {
        $('#journal_rows .master-item').each(function() {
            var $select = $(this);
            var $row = $select.closest('tr');
            var currentMasterId = $row.find('.master-type').val();
            
            $select.empty().append('<option value="">-- Select Item --</option>');
            
            var itemsToPopulate = allMasterItems;
            if (currentMasterId) {
                itemsToPopulate = allMasterItems.filter(function(item) { 
                    return item.master_id == currentMasterId; 
                });
            }

            $.each(itemsToPopulate, function(key, value) {
                $select.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
            });
            $select.prop('disabled', false).trigger('change.select2');
        });
    }

    fetchAllItems();

    function calculateTotals() {
        var debit = 0;
        var credit = 0;

        $('.journal-row').each(function() {
            var type = $(this).find('.row-type').val();
            var amount = parseFloat($(this).find('.row-amount').val()) || 0;

            if (type == 'debit') debit += amount;
            else credit += amount;
        });

        $('#total_debit_display').text(debit.toFixed(2));
        $('#total_credit_display').text(credit.toFixed(2));

        var diff = Math.abs(debit - credit);
        $('#balance_diff').text('Diff: ₹' + diff.toFixed(2));

        if (diff < 0.01 && (debit + credit) > 0) {
            $('#balance_diff').removeClass('badge-erp-warning badge-danger').addClass('badge-erp-success').text('Balanced!');
            $('#submit_btn').prop('disabled', false);
        } else {
            $('#balance_diff').removeClass('badge-erp-success badge-erp-warning').addClass('badge-danger');
            $('#submit_btn').prop('disabled', true);
        }
    }

    $(document).on('input', '.row-amount', calculateTotals);
    $(document).on('change', '.row-type', calculateTotals);

    $('#add_more').on('click', function() {
        var newRow = $('#row_template').clone().removeAttr('id').addClass('journal-row');
        $('#journal_rows').append(newRow);
        newRow.find('select').addClass('select2').select2({ theme: 'bootstrap4', width: '100%' });
        
        var $select = newRow.find('.master-item');
        $select.empty().append('<option value="">-- Select Item --</option>');
        $.each(allMasterItems, function(key, value) {
            $select.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
        });
        $select.prop('disabled', false).trigger('change.select2');
        
        calculateTotals();
    });

    $(document).on('click', '.remove-row', function() {
        if ($('#journal_rows tr').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    $(document).on('change', '.master-type', function(e, isAutoFill) {
        if (isAutoFill) return;
        var $row = $(this).closest('tr');
        var masterId = $(this).val();
        var $itemSelect = $row.find('.master-item');
        
        if (masterId) {
            var filteredItems = allMasterItems.filter(function(item) { return item.master_id == masterId; });
            $itemSelect.empty().append('<option value="">-- Select Item --</option>');
            $.each(filteredItems, function(key, value) {
                $itemSelect.append('<option value="' + value.id + '" data-master-id="' + value.master_id + '" data-balance="' + value.balance + '">' + value.name + '</option>');
            });
            $itemSelect.prop('disabled', false).trigger('change.select2');
        } else {
            populateAllItemSelects();
        }
    });

    $(document).on('change', '.master-item', function() {
        var $row = $(this).closest('tr');
        var $masterTypeSelect = $row.find('.master-type');
        var selectedOption = $(this).find(':selected');
        var masterId = selectedOption.data('master-id');
        
        if (masterId && !$masterTypeSelect.val()) {
            $masterTypeSelect.val(masterId).trigger('change', [true]);
        }

        var balance = selectedOption.data('balance');
        var $balanceDisplay = $row.find('.item-balance-display');
        if (balance !== undefined) {
            $balanceDisplay.text('Current Balance: ₹' + balance);
        } else {
            $balanceDisplay.text('');
        }
    });

    $('form').on('submit', function(e) {
        var debit = parseFloat($('#total_debit_display').text());
        var credit = parseFloat($('#total_credit_display').text());
        
        if (Math.abs(debit - credit) > 0.01) {
            e.preventDefault();
            alert('Voucher must be balanced! Debit must equal Credit.');
            return false;
        }
    });

    calculateTotals();
});
</script>
@endpush
