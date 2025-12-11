@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Corporate Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Corporate Order</li>
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
                    <h3 class="card-title">Create Corporate Order</h3>
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
                                <div id="products-container">
                                    <div class="product-row row mb-2">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Select Design Number</label>
                                                <select name="product_design_number[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Design Number</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-img="{{ $product->main_img }}">{{ $product->design_number }} - {{$product->name_of_garment}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                         <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Select Size</label>
                                                <select name="product_size[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Size</option>
                                                    @foreach($product_size as $size)
                                                        <option value="{{ $size->id }}">{{ $size->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                         <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Select Colour</label>
                                                <select name="product_color[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Colour</option>
                                                    @foreach($colours as $colour)
                                                        <option value="{{ $colour->id }}">{{ $colour->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>Quantity</label>
                                                <input type="number" name="product_quantity[]" id="" class="form-control" placeholder="Quantity" min="1" step="1" required>                                               
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group" style="margin-top:31px">
                                                <button type="button" class="btn btn-success add-product"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-3 img-section"></div>
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

<!-- Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content position-relative">

        <!-- Custom Close Button -->
        <button type="button" 
                class="close position-absolute" 
                style="top:10px; right:10px; font-size:30px; z-index:999;" 
                data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>

        <div class="modal-body text-center p-0">
            <img id="modal-photo" src="" style="width:100%; height:auto;">
        </div>

    </div>
  </div>
</div>




<script>
$(document).on('click', '#product-photo', function () {
    let src = $(this).attr('src');
    $('#modal-photo').attr('src', src);
    $('#photoModal').modal('show');
});

$(document).ready(function () {
    // Initialize Select2 for existing selects
    $('.select2').select2();
    let products = @json($products);
    // Add new product row
    $(document).on('click', '.add-product', function () {
         // Prevent add more rows than products
        let totalProducts = products.length;
        let totalRows = $("select[name='product_design_number[]']").length;

        if (totalRows >= totalProducts) {
            alert("You cannot add more rows. No more SKUs available.");
            return;
        }

        // Collect selected SKUs
        let selectedValues = [];
        console.log(selectedValues);
        $("select[name='product_design_number[]']").each(function () {
            if ($(this).val()) selectedValues.push($(this).val());
        });

        // Create filtered options
        let options = `<option value="">Select Design Number</option>`;
        
        products.forEach(p => {
            
            if (!selectedValues.includes(String(p.id))) {
               let imgUrl = p.main_image ? p.main_image.image : '';

                options += `
                    <option value="${p.id}" data-img="${imgUrl}">
                        ${p.design_number} - ${p.name_of_garment}
                    </option>
                `;
            }
        });

        let newRow = `<div class="product-row row mb-2 mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Select Design Number</label>
                                <select name="product_design_number[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                    <option value="">Select Design Number</option>
                                    @foreach($products as $product)
                                        ${options}
                                    @endforeach
                                </select>
                            </div>
                        </div>
                            <div class="col-md-3">
                            <div class="form-group">
                                <label>Select Size</label>
                                <select name="product_size[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                    <option value="">Select Size</option>
                                    @foreach($product_size as $size)
                                        <option value="{{ $size->id }}">{{ $size->size_selection }} - {{ $size->measurement }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                            <div class="col-md-3">
                            <div class="form-group">
                                <label>Select Colour</label>
                                <select name="product_color[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                    <option value="">Select Colour</option>
                                    @foreach($colours as $colour)
                                        <option value="{{ $colour->id }}">{{ $colour->sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Quantity</label>
                                <input type="number" name="product_quantity[]" id="" class="form-control" placeholder="Quantity" min="1" step="1" required>                                               
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group" style="margin-top:31px">
                                <button type="button" class="btn btn-danger remove-product"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3 img-section" > </div>
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
    $(document).on("change", "select[name='product_design_number[]']", function () {
        
        refreshSKUOptions();
    });

    $(document).on("change", "select[name='product_design_number[]']", function() {
       
        let imgUrl = $(this).find(':selected').data('img');


        // Append new image only if valid
        if (imgUrl) {
            $(this).closest('.product-row').find('.img-section').html(`
                <img id="product-photo" 
                    src="${imgUrl}" 
                    style="max-width:150px; max-height:150px;  border:1px solid #ccc; padding:5px;">
            `);
        }
    });
    // Hide duplicate SKUs from dropdowns
    function refreshSKUOptions() {
        let selected = [];

        $("select[name='product_design_number[]']").each(function () {
            if ($(this).val()) selected.push($(this).val());
        });

        $("select[name='product_design_number[]']").each(function () {
            let current = $(this).val();
            let select = $(this);

            // REBUILD OPTIONS
            let options = `<option value="">Select Design Number</option>`;
            
            products.forEach(p => {
                
                if (!selected.includes(String(p.id)) || String(p.id) === String(current)) {
                    let imgUrl = p.main_image ? p.main_image.image : '';

                    options += `
                        <option value="${p.id}" data-img="${imgUrl}">
                            ${p.design_number} - ${p.name_of_garment}
                        </option>
                    `;
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

    function getProductPhoto() {
        
    }


});
</script>
@endsection
