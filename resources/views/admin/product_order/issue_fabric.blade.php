@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-layer-group text-warning mr-1"></i> Issue Fabric to <span class="text-primary">{{ $data->first_stage->stage->name ?? 'Cutting' }}</span>
            </h5>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.index') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="erp-card p-3">
        <form id="fabricIssueForm" action="{{ route('admin.product_order.issueFabricPost') }}" method="POST">
            @csrf
            <input type="hidden" name="order_product_id" value="{{ $data->id }}">
            
            <div class="bg-light p-2 rounded border mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Select Cutting Unit <span class="text-danger">*</span></label>
                            <select name="sub_stage_id" class="form-control form-control-sm erp-input select2" style="width: 100%;" required>
                                @foreach($sub_stages_cutting as $single_data)
                                    <option value="{{$single_data->id}}">{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($data->product_details as $index => $detail)
            <input type="hidden" name="order_product_detail_ids[]" value="{{ $detail->id }}">

            <div class="bg-white p-3 rounded border mb-3 shadow-none" style="border: 1px solid #e2e8f0 !important;">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">
                        Fabric: <strong class="text-primary">{{ $detail->fabric_sku }}</strong>
                    </span>
                    <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 11px;">
                        Required: {{ $detail->total_meter }} m
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm erp-table mb-0" id="fabric-table-{{ $index }}">
                        <thead>
                            <tr>
                                <th>FABRIC ROLL</th>
                                <th style="width: 200px;">METER TO ISSUE</th>
                                <th style="width: 70px;" class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Hidden stock options for cloning -->
                <div id="fabric-options-{{ $index }}" class="d-none">
                    <option value="">-- Select Roll --</option>
                    @foreach($detail->fabric_stocks->where('meter','>',0) as $stock)
                        <option value="{{ $stock->id }}" data-meter="{{ $stock->meter }}">
                            {{ $stock->unique_number }} (Available: {{ $stock->meter }} m)
                        </option>
                    @endforeach
                </div>

                <input type="hidden" class="total-meter" value="{{ $detail->total_meter }}">
            </div>
            @endforeach

            <div class="d-flex justify-content-end align-items-center pt-2">
                <button type="submit" class="btn btn-sm btn-erp-primary px-3">
                    <i class="fas fa-save mr-1"></i> Submit Fabric Issue
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ➕ Add new row (only delete icon for added ones)
    $(document).on('click', '.add-row', function() {
        let index = $(this).data('index');
        let tableBody = $('#fabric-table-' + index + ' tbody');
        let stockOptions = $('#fabric-options-' + index).html();

        let newRow = `
            <tr>
                <td>
                    <select name="fabric_roll[${index}][]" class="form-control form-control-sm erp-input">
                        ${stockOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="meter[${index}][]" 
                        class="form-control form-control-sm erp-input meter-input" 
                        min="0" step="0.01" placeholder="Enter meter" 
                        data-index="${index}">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        tableBody.append(newRow);
    });

    // 🗑️ Remove row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });

    // 🧮 Validate total dynamically
    $(document).on('input', '.meter-input', function() {
        let index = $(this).data('index');
        let totalRequired = parseFloat($('#fabric-table-' + index).closest('.bg-white').find('.total-meter').val());
        let totalUsed = 0;

        $('#fabric-table-' + index + ' input[name^="meter"]').each(function() {
            totalUsed += parseFloat($(this).val()) || 0;
        });

        if (totalUsed > totalRequired) {
            alert(`You cannot issue more than ${totalRequired} meters.`);
            $(this).val('');
        }
    });

    // ✅ Final submit validation
    $('#fabricIssueForm').on('submit', function(e) {
        let allValid = true;

        $('.bg-white').each(function() {
            const totalRequired = parseFloat($(this).find('.total-meter').val()) || 0;
            let totalUsed = 0;

            $(this).find('input[name^="meter"]').each(function() {
                totalUsed += parseFloat($(this).val()) || 0;
            });

            if (Math.abs(totalUsed - totalRequired) > 0.01) {
                alert(`Total issued meter (${totalUsed}) must exactly match required (${totalRequired}).`);
                allValid = false;
                return false;
            }
        });

        if (!allValid) e.preventDefault();
    });

    // 🎯 Auto-select rolls + distribute meters for ALL fabrics
    @foreach($data->product_details as $index => $detail)
        (function(index) {
            const totalRequired = parseFloat({{ $detail->total_meter }});
            const tableBody = $('#fabric-table-' + index + ' tbody');
            const stockOptions = $('#fabric-options-' + index).html();
            let remaining = totalRequired;

            tableBody.empty();

            let isFirstRow = true;

            @foreach($detail->fabric_stocks->where('meter','>',0) as $stock)
                if (remaining > 0) {
                    const stockId = "{{ $stock->id }}";
                    const available = parseFloat("{{ $stock->meter }}");
                    const used = Math.min(available, remaining);
                    remaining -= used;

                    // First row: Add button | others: Delete button
                    const actionButton = isFirstRow
                        ? `<button type="button" class="btn btn-xs btn-erp-primary add-row" data-index="${index}" title="Add Roll">
                               <i class="fas fa-plus"></i>
                           </button>`
                        : `<button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Delete">
                               <i class="fas fa-trash"></i>
                           </button>`;
                    isFirstRow = false;

                    const newRow = `
                        <tr>
                            <td>
                                <select name="fabric_roll[${index}][]" class="form-control form-control-sm erp-input roll-select">
                                    ${stockOptions}
                                </select>
                            </td>
                            <td>
                                <input type="number" name="meter[${index}][]" class="form-control form-control-sm erp-input meter-input"
                                    min="0" step="0.01" value="${used.toFixed(2)}" data-index="${index}">
                            </td>
                            <td class="text-center align-middle">${actionButton}</td>
                        </tr>`;

                    tableBody.append(newRow);
                    const lastRow = tableBody.find('tr:last');
                    lastRow.find('select.roll-select').val(stockId);
                }
            @endforeach
        })({{ $index }});
    @endforeach

});
</script>
@endsection
