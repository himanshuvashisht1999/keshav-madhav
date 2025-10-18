@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0">Fabric Issue</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.product_order.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Section -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- ✅ Wrap entire content inside form -->
            <form id="fabricIssueForm" action="{{ route('admin.product_order.issueFabricPost') }}" method="POST">
                @csrf

                @foreach($data->product_details as $index => $single_detail)
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <strong>
                            <i class="fas fa-info-circle mr-1"></i> Production Order Information
                        </strong>
                    </div>

                    <div class="card-body">
                        <table class="table table-bordered table-striped mb-0">
                            <tr>
                                <th width="20%">Product SKU</th>
                                <td>{{ $single_detail->product_sku }}</td>
                                <th>Fabric SKU</th>
                                <td>{{ $single_detail->fabric_sku }}</td>
                            </tr>
                            <tr>
                                <th>Total Quantity</th>
                                <td>{{ $single_detail->order_quantity }}</td>
                                <th>Meter per Product</th>
                                <td>{{ $single_detail->meter }}</td>
                                <th>Total Required Meter</th>
                                <td>{{ $single_detail->total_meter }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Multi Roll Selection -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white">
                        <strong><i class="fas fa-scroll mr-1"></i> Select Fabric Rolls</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0" id="fabric-table-{{ $index }}">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50%">Roll (Unique Number)</th>
                                    <th width="30%">Meter to Issue</th>
                                    <th width="20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="fabric_roll[{{ $index }}][]" class="form-control">
                                            <option value="">-- Select Roll --</option>
                                            @foreach($single_detail->fabric_stocks as $single_stock)
                                                <option value="{{ $single_stock->id }}">
                                                    {{ $single_stock->unique_number }} (Available: {{ $single_stock->meter }} m)
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" 
                                            name="meter[{{ $index }}][]" 
                                            class="form-control meter-input" 
                                            min="0" step="0.01" 
                                            placeholder="Enter meter"
                                            data-index="{{ $index }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-success btn-sm add-row" data-index="{{ $index }}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="p-3">
                            <strong>Total Required:</strong> {{ $single_detail->total_meter }} m
                            <input type="hidden" class="total-meter" value="{{ $single_detail->total_meter }}">
                            <br>
                            <small class="text-muted">Add multiple rolls until the total meter is fulfilled.</small>
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- ✅ Submit Button -->
                <div class="text-right mb-4">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-save mr-1"></i> Submit Fabric Issue
                    </button>
                </div>
            </form>

        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ➕ Add new row
    $(document).on('click', '.add-row', function() {
        let index = $(this).data('index');
        let tableBody = $('#fabric-table-' + index + ' tbody');
        let totalRequired = parseFloat($('#fabric-table-' + index).closest('.card').find('.total-meter').val());

        // Calculate current total
        let currentTotal = 0;
        $('#fabric-table-' + index + ' input[name^="meter"]').each(function() {
            currentTotal += parseFloat($(this).val()) || 0;
        });

        // Check if total already reached
        if (currentTotal >= totalRequired) {
            alert(`You have already reached the required total of ${totalRequired} meters.`);
            return;
        }

        // Add new row
        let newRow = `
            <tr>
                <td>
                    <select name="fabric_roll[${index}][]" class="form-control">
                        <option value="">-- Select Roll --</option>
                        @foreach($data->product_details->first()->fabric_stocks as $fabric_stock)
                            <option value="{{ $fabric_stock->id }}">
                                {{ $fabric_stock->unique_number }} (Available: {{ $fabric_stock->meter }} m)
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" 
                        name="meter[${index}][]" 
                        class="form-control meter-input"
                        min="0" step="0.01" 
                        placeholder="Enter meter"
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
        let totalRequired = parseFloat($('#fabric-table-' + index).closest('.card').find('.total-meter').val());
        let totalUsed = 0;

        $('#fabric-table-' + index + ' input[name^="meter"]').each(function() {
            totalUsed += parseFloat($(this).val()) || 0;
        });

        if (totalUsed > totalRequired) {
            alert(`You cannot issue more than ${totalRequired} meters.`);
            $(this).val('');
        }
    });

    // ✅ Final form submit validation (fixed)
    $('#fabricIssueForm').on('submit', function(e) {
        let allValid = true;

        // Iterate only over cards that contain .total-meter
        $('.card').has('.total-meter').each(function() {
            // find the exact total required for this product
            const totalRequiredRaw = $(this).find('.total-meter').val();
            const totalRequired = parseFloat(totalRequiredRaw) || 0;

            // sum meters only inside this card
            let totalUsed = 0;
            $(this).find('input[name^="meter"]').each(function() {
                totalUsed += parseFloat($(this).val()) || 0;
            });

            console.log('totalUsed', totalUsed);
            console.log('totalRequired', totalRequired);

            // Use a small epsilon for float comparison (handles decimals like 0.01)
            const EPS = 0.0001;
            if (Math.abs(totalUsed - totalRequired) > EPS) {
                alert(`Total issued meter (${totalUsed}) must exactly match required (${totalRequired}).`);
                allValid = false;
                return false; // break out of .each
            }
        });

        if (!allValid) {
            e.preventDefault();
        }
    });


});
</script>

@endsection
