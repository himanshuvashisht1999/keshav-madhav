@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Receipt Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Fabric Receipt Detail</li>
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
                    <h3 class="card-title">Fabric Receipt Detail</h3>
                </div>

                <form action="{{ route('admin.fabric_receipt.storeDetail') }}" method="post" enctype="multipart/form-data">
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
                                        <th>Roll No</th>
                                        <th>Fabric SKU</th>
                                        <th>Meter</th>
                                        <th>Batch No.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 1; $i <= $data->roll; $i++)
                                        <tr>
                                            <!-- Roll Number -->
                                            <td>
                                                <input type="hidden" name="rolls[{{ $i }}][roll]" value="{{ $i }}">
                                                <span class="fw-bold">{{ $i }}</span>
                                            </td>

                                            <!-- Fabric SKU -->
                                            <td style="width:40%;">
                                                <select name="rolls[{{ $i }}][fabric_sku]" 
                                                        class="form-control select2 fabric-sku" 
                                                        data-row="{{ $i }}" required>
                                                    <option value="">Select SKU</option>
                                                    @foreach ($fabrics as $single_data)
                                                        <option value="{{ $single_data->id }}">{{ $single_data->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <!-- Meter -->
                                            <td>
                                                <input type="number" 
                                                    name="rolls[{{ $i }}][meter]" 
                                                    class="form-control meter" 
                                                    data-row="{{ $i }}" 
                                                    placeholder="Enter Meters" min="0" step="0.01" required>
                                            </td>

                                            <!-- Batch -->
                                            <td>
                                                <input type="text" 
                                                    name="rolls[{{ $i }}][batch]" 
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
                            <!-- <a href="{{ route('admin.fabric_receipt.index') }}" class="btn btn-danger">Exit Without Save</a> -->
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
    window._propagatingFabric = false;
    window._propagatingMeter  = false;
    window._propagatingBatch  = false;

    // ---------- Fabric SKU: change on a row -> copy to ALL rows BELOW ----------
    $(document).on('change', '.fabric-sku', function () {
        if (window._propagatingFabric) return; // avoid recursion
        const row = parseInt($(this).data('row'));
        const value = $(this).val();

        // only propagate non-empty selections
        if (value === null || value === '') return;

        window._propagatingFabric = true;

        // set value for every lower row (overwrite)
        $('.fabric-sku').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value);

                // update Select2 UI
                $(this).trigger('change.select2'); // ensures Select2 shows updated value
            }
        });

        window._propagatingFabric = false;
    });

    // ---------- Meter: input on a row -> copy to ALL rows BELOW ----------
    $(document).on('input', '.meter', function () {
        if (window._propagatingMeter) return;
        const row = parseInt($(this).data('row'));
        const value = $(this).val();

        if (value === '') return;

        window._propagatingMeter = true;

        $('.meter').each(function () {
            const currentRow = parseInt($(this).data('row'));
            if (currentRow > row) {
                $(this).val(value);
                // optionally trigger input event if some logic listens to it:
                $(this).trigger('input');
            }
        });

        window._propagatingMeter = false;
    });

    // ---------- Batch: input on a row -> copy to ALL rows BELOW ----------
    // $(document).on('input', '.batch', function () {
    //     if (window._propagatingBatch) return;
    //     const row = parseInt($(this).data('row'));
    //     const value = $(this).val();

    //     if (value === '') return;

    //     window._propagatingBatch = true;

    //     $('.batch').each(function () {
    //         const currentRow = parseInt($(this).data('row'));
    //         if (currentRow > row) {
    //             $(this).val(value);
    //             $(this).trigger('input');
    //         }
    //     });

    //     window._propagatingBatch = false;
    // });
});
</script>

@endsection



