@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Corporate Order</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Corporate Order</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN SECTION -->
    <section class="content">
        <div class="container-fluid">

            <div class="card card-default">

                <form action="{{route('admin.sales_order.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <!-- LEFT SIDE FORM -->
                            <div class="col-md-6">

                                <div class="row">

                                    <!-- Customer -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Customer</label>
                                            <select name="master_customer_id" class="form-control select2" required>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Delivery Date -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estimated Delivery Date</label>
                                            <input type="date" name="expected_delivery_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>

                                    <!-- PRODUCT INPUT SECTION -->
                                    <div class="col-md-12">

                                        <div class="product-row row mb-3 border p-3 rounded shadow-sm bg-white">

                                            <div class="col-md-6">
                                                <label>Select Design Number</label>
                                                <select class="form-control select2 design-input" required>
                                                    <option value="">Select Design Number</option>
                                                    @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-img="{{ $product->main_img }}">
                                                        {{ $product->design_number }} - {{ $product->name_of_garment }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Select Size</label>
                                                <select class="form-control select2 size-input" required>
                                                    <option value="">Select Size</option>
                                                    @foreach($product_size as $size)
                                                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-6 mt-2">
                                                <label>Select Colour</label>
                                                <select class="form-control select2 colour-input" required>
                                                    <option value="">Select Colour</option>
                                                    @foreach($colours as $colour)
                                                    <option value="{{ $colour->id }}">{{ $colour->sku }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-5 mt-2">
                                                <label>Quantity</label>
                                                <input type="number" class="form-control qty-input" min="1" required>
                                            </div>

                                            <div class="col-md-1 mt-4">
                                                <button type="button" class="btn btn-success w-100 add-product">
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>

                                            <div class="col-md-4 img-section mt-3"></div>

                                        </div>

                                    </div>

                                    <!-- ADDED PRODUCT LIST -->
                                    <div class="col-md-12 mt-4">
                                        <h5><strong>Added Products</strong></h5>
                                        <table class="table table-bordered" id="productList">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Design</th>
                                                    <th>Size</th>
                                                    <th>Colour</th>
                                                    <th>Qty</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>

                                </div>

                            </div>

                            <!-- RIGHT SIDE PREVIEW -->
                            <div class="col-md-6">
                                <div class="card shadow-sm border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <strong>Uploaded File Preview</strong>
                                    </div>
                                    <div class="card-body">
                                        <input type="file" name="corporate_order_file" id="corporate_order_file" class="form-control mb-2">

                                        <img id="previewImg" src=""
                                             style="width:100%; border:1px solid #ddd; border-radius:5px; padding:5px; display:none;">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-12 text-right mt-3">
                            <button type="submit" class="btn btn-primary px-4">Submit</button>
                        </div>

                    </div>

                </form>

            </div>

        </div>
    </section>

</div>


<!-- MODAL FOR PRODUCT IMAGE -->
<div class="modal fade" id="photoModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative">
            <button type="button" class="close position-absolute" style="top:8px; right:12px;" data-dismiss="modal">&times;</button>
            <div class="modal-body p-0">
                <img id="modal-photo" style="width:100%;">
            </div>
        </div>
    </div>
</div>


<!-- SCRIPTS -->
<script>

// ---- File Preview ----
$("#corporate_order_file").on("change", function (e) {
    let file = e.target.files[0];
    if (!file) return;

    let reader = new FileReader();
    reader.onload = function (event) {
        $("#previewImg").attr("src", event.target.result).show();
    };
    reader.readAsDataURL(file);
});

// ---- Select2 ----
$('.select2').select2();

// ---- Show Design Image ----
$(document).on("change", ".design-input", function () {
    let img = $(this).find(":selected").data("img");
    let section = $(this).closest(".product-row").find(".img-section");

    if (img) {
        section.html(`
            <img id="product-photo" src="${img}" style="max-width:150px; border:1px solid #ccc; padding:5px;">
        `);
    } else {
        section.html("");
    }
});

// ---- Add Product to List ----
$(document).on("click", ".add-product", function () {

    let row = $(this).closest(".product-row");

    let design = row.find(".design-input option:selected");
    let size = row.find(".size-input option:selected");
    let colour = row.find(".colour-input option:selected");
    let qty = row.find(".qty-input").val();

    if (!design.val() || !size.val() || !colour.val() || qty <= 0) {
        alert("Please fill all fields before adding.");
        return;
    }

    $("#productList tbody").append(`
        <tr>
            <td>${design.text()} <input type="hidden" name="designList[]" value="${design.val()}"></td>
            <td>${size.text()} <input type="hidden" name="sizeList[]" value="${size.val()}"></td>
            <td>${colour.text()} <input type="hidden" name="colourList[]" value="${colour.val()}"></td>
            <td>${qty} <input type="hidden" name="qtyList[]" value="${qty}"></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    `);

    // Clear inputs
    row.find("select").val("").trigger("change");
    row.find(".qty-input").val("");
    row.find(".img-section").empty();
});

// ---- Remove Product from list ----
$(document).on("click", ".remove-row", function () {
    $(this).closest("tr").remove();
});

</script>

@endsection
