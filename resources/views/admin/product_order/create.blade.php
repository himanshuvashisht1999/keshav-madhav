@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sales Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Sales Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Sales Order</h3>
                </div>
                <form action="{{route('admin.sales_order.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select Customer</label>
                                    <select name="master_customer_id" class="form-control select2" style="width: 100%;">
                                        @foreach($customers as $customer)
                                        <option value="{{$customer->id}}" {{old('master_customer_id') == $customer->id ? 'selected' : ''}}>{{$customer->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('master_customer_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_customer_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Estimated Delivery Date</label>
                                    <input type="date" name="expected_delivery_date" class="form-control" value="{{old('expected_delivery_date')}}" min="{{ date('Y-m-d') }}" required>
                                    @if ($errors->has('expected_delivery_date'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('expected_delivery_date') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Select Product Sku</label>
                                <div id="products-container">
                                    <div class="product-row row mb-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <select name="product_sku[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Product SKU</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->sku }}">{{ $product->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="number" name="product_quantity[]" id="" class="form-control" placeholder="Quantity" min="1" step="1" required>
                                                
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success add-product"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            
                           
                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function () {
    // Initialize Select2 for existing selects
    $('.select2').select2();
    let products = @json($products);
    // Add new product row
    $(document).on('click', '.add-product', function () {
         // Prevent add more rows than products
        let totalProducts = products.length;
        let totalRows = $("select[name='product_sku[]']").length;

        if (totalRows >= totalProducts) {
            alert("You cannot add more rows. No more SKUs available.");
            return;
        }

        // Collect selected SKUs
        let selectedValues = [];
        $("select[name='product_sku[]']").each(function () {
            if ($(this).val()) selectedValues.push($(this).val());
        });

        // Create filtered options
        let options = `<option value="">Select Product SKU</option>`;
        products.forEach(p => {
            if (!selectedValues.includes(p.sku)) {
                options += `<option value="${p.sku}">${p.sku}</option>`;
            }
        });



        let newRow = `
            <div class="product-row row mb-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <select name="product_sku[]" class="form-control select2 stage-select" style="width: 100%;" required>
                            ${options}
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <input type="number" name="product_quantity[]" id="" class="form-control" placeholder="Quantity" min="1" step="1" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger remove-product"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        `;
        $('#products-container').append(newRow);
        $('.select2').select2(); // re-init Select2 for new rows
        refreshSKUOptions();
    });

     // Delete Row
    $(document).on('click', '.remove-product', function () {
        $(this).closest('.product-row').remove();
        refreshSKUOptions();
    });

    // Select change event
    $(document).on("change", "select[name='product_sku[]']", function () {
        refreshSKUOptions();
    });

    // Hide duplicate SKUs from dropdowns
    function refreshSKUOptions() {
        let selected = [];

        $("select[name='product_sku[]']").each(function () {
            if ($(this).val()) selected.push($(this).val());
        });

        $("select[name='product_sku[]']").each(function () {
            let current = $(this).val();
            let select = $(this);

            // REBUILD OPTIONS
            let options = `<option value="">Select Product SKU</option>`;

            products.forEach(p => {
                if (!selected.includes(p.sku) || current === p.sku) {
                    options += `<option value="${p.sku}">${p.sku}</option>`;
                }
            });

            // SET new options
            select.html(options);

            // RESTORE selected value
            if (current) select.val(current);

            // REINIT SELECT2
            select.trigger('change.select2');
        });
    }


});
</script>
@endsection
