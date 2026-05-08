@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ isset($voucher) ? 'Edit' : 'New' }} Journal Voucher</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payment.journal-voucher.index') }}">Journal Vouchers</a></li>
                        <li class="breadcrumb-item active">{{ isset($voucher) ? 'Edit' : 'New' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ isset($voucher) ? route('admin.payment.journal-voucher.update', $voucher->id) : route('admin.payment.journal-voucher.store') }}" method="POST">
                @csrf
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Voucher Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ isset($voucher) ? $voucher->date : date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Narration (Global)</label>
                                    <input type="text" name="narration" class="form-control" value="{{ isset($voucher) ? $voucher->narration : '' }}" placeholder="Enter global narration for this voucher">
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="journal_table">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="min-width: 350px;">Party / Ledger Selection</th>
                                        <th style="width: 150px;">Type</th>
                                        <th style="width: 200px;">Amount</th>
                                        <th>Item Narration</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="journal_rows">
                                    @if(isset($voucher))
                                        @foreach($voucher->items as $index => $item)
                                            <tr class="journal-row">
                                                <td>
                                                    <select name="master_type[]" class="form-control select2 master-type" required>
                                                        <option value="">-- Master Type --</option>
                                                        @foreach($masters as $master)
                                                            <option value="{{ $master->id }}" {{ $item->master_type == $master->id ? 'selected' : '' }}>{{ $master->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="master_id[]" class="form-control select2 master-item mt-2" required>
                                                        <option value="">-- Select Item --</option>
                                                        @if(isset($voucher))
                                                            <option value="{{ $item->master_id }}" selected>Loading...</option>
                                                        @endif
                                                    </select>
                                                    <small class="text-muted item-balance-display d-block mt-1"></small>
                                                </td>
                                                <td>
                                                    <select name="type[]" class="form-control row-type" required>
                                                        <option value="debit" {{ $item->type == 'debit' ? 'selected' : '' }}>Debit (Dr)</option>
                                                        <option value="credit" {{ $item->type == 'credit' ? 'selected' : '' }}>Credit (Cr)</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" name="amount[]" class="form-control row-amount" value="{{ $item->amount }}" required>
                                                </td>
                                                <td>
                                                    <input type="text" name="item_narration[]" class="form-control" value="{{ $item->narration }}" placeholder="Narration">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="journal-row">
                                            <td>
                                                <select name="master_type[]" class="form-control select2 master-type" required>
                                                    <option value="">-- Master Type --</option>
                                                    @foreach($masters as $master)
                                                        <option value="{{ $master->id }}">{{ $master->name }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="master_id[]" class="form-control select2 master-item mt-2" required disabled>
                                                    <option value="">-- Select Item --</option>
                                                </select>
                                                <small class="text-muted item-balance-display d-block mt-1"></small>
                                            </td>
                                            <td>
                                                <select name="type[]" class="form-control row-type" required>
                                                    <option value="debit">Debit (Dr)</option>
                                                    <option value="credit">Credit (Cr)</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="amount[]" class="form-control row-amount" placeholder="0.00" required>
                                            </td>
                                            <td>
                                                <input type="text" name="item_narration[]" class="form-control" placeholder="Narration">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <th class="text-right">Totals:</th>
                                        <th class="text-center">Balanced?</th>
                                        <th colspan="2">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-success font-weight-bold">Debit: ₹<span id="total_debit_display">0.00</span></span>
                                                <span class="text-danger font-weight-bold">Credit: ₹<span id="total_credit_display">0.00</span></span>
                                                <span id="balance_diff" class="badge badge-warning">Diff: ₹0.00</span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mt-2" id="add_more">
                            <i class="fas fa-plus"></i> Add Entry
                        </button>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow" id="submit_btn">
                            <i class="fas fa-save mr-1"></i> Save Voucher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

{{-- Template Row --}}
<table style="display:none;">
    <tr id="row_template">
        <td>
            <select name="master_type[]" class="form-control master-type" required>
                <option value="">-- Master Type --</option>
                @foreach($masters as $master)
                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                @endforeach
            </select>
            <select name="master_id[]" class="form-control master-item mt-2" required disabled>
                <option value="">-- Select Item --</option>
            </select>
            <small class="text-muted item-balance-display d-block mt-1"></small>
        </td>
        <td>
            <select name="type[]" class="form-control row-type" required>
                <option value="debit">Debit (Dr)</option>
                <option value="credit">Credit (Cr)</option>
            </select>
        </td>
        <td>
            <input type="number" step="0.01" name="amount[]" class="form-control row-amount" placeholder="0.00" required>
        </td>
        <td>
            <input type="text" name="item_narration[]" class="form-control" placeholder="Narration">
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
            $('#balance_diff').removeClass('badge-warning badge-danger').addClass('badge-success').text('Balanced!');
            $('#submit_btn').prop('disabled', false);
        } else {
            $('#balance_diff').removeClass('badge-success badge-warning').addClass('badge-danger');
            $('#submit_btn').prop('disabled', true);
        }
    }

    $(document).on('input', '.row-amount', calculateTotals);
    $(document).on('change', '.row-type', calculateTotals);

    $('#add_more').on('click', function() {
        var newRow = $('#row_template').clone().removeAttr('id').addClass('journal-row');
        $('#journal_rows').append(newRow);
        newRow.find('select').addClass('select2').select2({ width: '100%' });
        
        // Populate items based on currently selected allMasterItems
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
            $balanceDisplay.text('Balance: ₹' + balance);
        } else {
            $balanceDisplay.text('');
        }
    });

    // Final Validation
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
