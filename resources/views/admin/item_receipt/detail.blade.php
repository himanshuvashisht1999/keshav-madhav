@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Receipt Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Item Receipt Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Item Receipt Detail</h3>
                </div>

                <form action="{{ route('admin.item_receipt.storeDetail') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">

                    <div class="card-body">
                        
                        <!-- Purchase Order -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label>Purchase Order</label>
                                <select name="purchase_order_id" id="purchase_order_id" class="form-control select2" style="width: 100%;" >
                                    <option value="">NILL</option>
                                    @foreach ($purchase_orders as $single_data)
                                        <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Excel style table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped text-center align-middle">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Box No</th>
                                        <th>Item SKU</th>
                                        <th>Quantity</th>
                                        <th>Batch No.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= $data->box; $i++)
                                        <tr>
                                            <!-- Box Number -->
                                            <td>
                                                <input type="hidden" name="boxes[{{ $i }}][box]" value="{{ $i }}">
                                                <span class="fw-bold">{{ $i }}</span>
                                            </td>

                                            <!-- Item SKU -->
                                            <td style="width:40%;">
                                                <select name="boxes[{{ $i }}][item_sku]" 
                                                        class="form-control select2 item-sku" 
                                                        data-row="{{ $i }}" required>
                                                    <option value="">Select SKU</option>
                                                    @foreach ($items as $single_data)
                                                        <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Quantity -->
                                            <td>
                                                <input type="number" 
                                                    name="boxes[{{ $i }}][quantity]" 
                                                    class="form-control quantity" 
                                                    data-row="{{ $i }}" 
                                                    placeholder="Enter Quantity" min="1" required>
                                            </td>

                                            <!-- Batch -->
                                            <td>
                                                <input type="text" 
                                                    name="boxes[{{ $i }}][batch]" 
                                                    class="form-control batch" 
                                                    data-row="{{ $i }}" 
                                                    placeholder="Batch Number" value="{{$new_batch_no + $i - 1 }}" required readonly>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4" style="float:right">
                            <button type="submit" class="btn btn-success">Save</button>
                            <!-- <a href="{{ route('admin.item_receipt.index') }}" class="btn btn-danger">Exit Without Save</a> -->
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    // init select2 once
    $('.select2').select2({ width: '100%' });

    // guard flags to avoid re-entrant propagation
    window._propagatingItem = false;
    window._propagatingQuantity  = false;
    window._propagatingBatch  = false;

    // ---------- Item SKU: change on a row -> copy to ALL rows BELOW ----------
    $(document).on('change', '.item-sku', function () {
        if (window._propagatingItem) return; // avoid recursion
        const row = parseInt($(this).data('row'));
        const value = $(this).val();

        // only propagate non-empty selections
        if (value === null || value === '') return;

        window._propagatingItem = true;

        // set value for every lower row (overwrite)
        $('.item-sku').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value);

                // update Select2 UI
                $(this).trigger('change.select2'); // ensures Select2 shows updated value
            }
        });

        window._propagatingItem = false;
    });

    // ---------- Quantity: change on a row -> copy to ALL rows BELOW ----------
    $(document).on('input', '.quantity', function () {
        if (window._propagatingQuantity) return;
        const row = parseInt($(this).data('row'));
        const value = $(this).val();

        if (value === null || value === '') return;

        window._propagatingQuantity = true;

        $('.quantity').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value);
            }
        });

        window._propagatingQuantity = false;
    });

    // ---------- Batch: change on a row -> copy to ALL rows BELOW ----------
    $(document).on('input', '.batch', function () {
        if (window._propagatingBatch) return;
        const row = parseInt($(this).data('row'));
        const value = $(this).val();

        if (value === null || value === '') return;

        window._propagatingBatch = true;

        $('.batch').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value);
            }
        });

        window._propagatingBatch = false;
    });
});
</script>
@endsection