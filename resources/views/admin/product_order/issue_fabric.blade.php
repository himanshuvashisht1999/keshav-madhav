@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header border-bottom pb-2">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                Issue Fabric to {{ $data->first_stage->stage->name }}
            </h4>
            <a href="{{ route('admin.product_order.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content mt-4">
        <div class="container-fluid">

            <form id="fabricIssueForm" action="{{ route('admin.product_order.issueFabricPost') }}" method="POST">
                @csrf
                <input type="hidden" name="order_product_id" value="{{ $data->id }}">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Select Sub Stage</label>
                        <select name="sub_stage_id" class="form-control select2" style="width: 100%;" required>
                            @foreach($sub_stages_cutting as $single_data)
                            <option value="{{$single_data->id}}">{{$single_data->name}}</option>
                            @endforeach
                            
                        </select>
                    </div>
                </div>

                @foreach($data->product_details as $index => $detail)
                <input type="hidden" name="order_product_detail_ids[]" value="{{ $detail->id }}">

                

                <div class="bg-white p-3 rounded border mb-4">
                    <h6 class="mb-3 text-primary">
                        Fabric: <strong>{{ $detail->fabric_sku }}</strong>
                        <small class="text-muted">(Required: {{ $detail->total_meter }} m)</small>
                    </h6>

                    <table class="table table-sm mb-0" id="fabric-table-{{ $index }}">
                        <thead class="thead-light">
                            <tr>
                                <th>Fabric Roll</th>
                                <th width="25%">Meter to Issue</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

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

                <div class="text-right mb-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Submit Fabric Issue
                    </button>
                </div>
            </form>

        </div>
    </section>
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
                    <select name="fabric_roll[${index}][]" class="form-control form-control-sm">
                        ${stockOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="meter[${index}][]" 
                        class="form-control form-control-sm meter-input" 
                        min="0" step="0.01" placeholder="Enter meter" 
                        data-index="${index}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row">
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
                        ? `<button type="button" class="btn btn-success btn-sm add-row" data-index="${index}">
                               <i class="fas fa-plus"></i>
                           </button>`
                        : `<button type="button" class="btn btn-danger btn-sm remove-row">
                               <i class="fas fa-trash"></i>
                           </button>`;
                    isFirstRow = false;

                    const newRow = `
                        <tr>
                            <td>
                                <select name="fabric_roll[${index}][]" class="form-control form-control-sm roll-select">
                                    ${stockOptions}
                                </select>
                            </td>
                            <td>
                                <input type="number" name="meter[${index}][]" class="form-control form-control-sm meter-input"
                                    min="0" step="0.01" value="${used.toFixed(2)}" data-index="${index}">
                            </td>
                            <td class="text-center">${actionButton}</td>
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

<style>
.table th, .table td { vertical-align: middle !important; }
.bg-white { background: #fff !important; }
.table-sm th { color: #555; font-weight: 600; }
h6 { font-size: 15px; }
.btn-sm i { font-size: 13px; }
</style>
@endsection
