@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">Sales Orders</h1>
                </div>
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Stock Order</li>
                    </ol>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- MAIN SECTION -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">

                <form action="{{ route('admin.sales_order.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                   <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <!-- Customer & Delivery -->
                            <div class="card mb-3 p-3 border">
                                <h5 class="mb-3">Customer & Delivery</h5>

                                <label>Select Customer</label>
                                <select name="master_customer_id" id="master_customer_id" class="form-control select2 mb-2" required>
                                    <option value="">Select Customer </option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('master_customer_id'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_customer_id') }}
                                    </span>
                                @endif
                                <label>Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                                @if ($errors->has('expected_delivery_date'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('expected_delivery_date') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Add Product -->
                            <div class="card p-3 border">
                                <h5 class="mb-3">Add Product</h5>

                                <div class="product-row">

                                    <label>Design Number (Royal Jeans)</label>
                                    <select class="form-control select2 mb-2 design-input" name="design_id" id="design_id">
                                        <option value="">Select</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-img="{{ $product->main_img }}">
                                                {{ $product->design_number }} - {{ $product->name_of_garment }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('designList'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('designList') }}
                                        </span>
                                    @endif
                                    <label>Set Size (Royal Jeans)</label>
                                    <select class="form-control select2 mb-2 size-input" name="set_size" id="set_size">
                                        {{-- @foreach($product_size as $size)
                                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                                        @endforeach --}}
                                    </select>
                                    @if ($errors->has('sizeList'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sizeList') }}
                                        </span>
                                    @endif
                                    <label>Colour (Royal Jeans)</label>
                                    <select class="form-control select2 mb-2 colour-input" name="colour_id">
                                        <option value="">Select</option>
                                        @foreach($colours as $colour)
                                            <option value="{{ $colour->id }}">{{ $colour->sku }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('colourList'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('colourList') }}
                                        </span>
                                    @endif
                                    <label>Set Quantity (Royal Jeans)</label>
                                    <input type="number" class="form-control qty-input mb-3" min="1" name="qty">
                                    @if ($errors->has('product_quantity'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('product_quantity') }}
                                        </span>
                                    @endif
                                    <button type="button" class="btn btn-primary btn-block add-product">
                                        + Add Product
                                    </button>

                                    <div class="img-section text-center mt-3"></div>

                                </div>

                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">
                            <div class="card p-3 border shadow-sm">
                                <h6>Upload Order File</h6>
                                <input type="file" name="corporate_order_file" id="corporate_order_file" class="form-control mb-2">
                                <img id="previewImg" class="w-100 mt-2" style="display:none; border-radius:6px;">
                            </div>
                        </div>

                        <!-- FULL WIDTH SECTION -->
                        <div class="col-md-12">
                            <div class="card mt-3 p-3 border">
                                <h5 class="mb-3">Added Products</h5>
                                <table class="table table-bordered" id="productList">
                                    <thead>
                                        <tr>
                                            {{-- <th>Image</th> --}}
                                            <th>Design</th>
                                            <th>Set Size</th>
                                            <th>Colour</th>
                                            <th>Set Quantity</th>
                                            <th>Pcs per Set</th>
                                            <th>Total Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <div class="text-right mt-3">
                        <button class="btn btn-success px-4">Submit Order</button>
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
$(document).ready(function () {

    $('#design_id').on('change', function () {
       
        let customer_id = $('#master_customer_id').val();
        let design_id = $(this).val();
        if(customer_id === "" ){
            alert('Please select customer first');
            let design_id = $(this).val("");
            return;
        }
        let apiUrl = "{{ route('admin.sales_order.getCustomerSizes') }}";
        $.ajax({
            url: apiUrl,   // Route
            type: 'GET',
            data: { customer_id: customer_id , design_id: design_id },
            success: function (response) {
                $("#set_size").empty(); // clear select

                $("#set_size").append('<option value="">Select Size</option>');
               
                $.each(response, function(index, size){
                    let parts = size.split("&&");   // FIXED

                    let sizeName = parts[0];        // "Large"
                    let pcsPerSet = parts[1];       // "12"

                    $("#set_size").append(`
                        <option value="${sizeName}" data-pcs="${pcsPerSet}">
                            ${sizeName}
                        </option>
                    `);
                });

                // Refresh select2
                $("#set_size").trigger('change');
            },
            error: function(xhr){
                console.log(xhr.responseText);
            }
        });

    });

    $('#master_customer_id').on('change', function () {
        
        let customer_id = $(this).val();

        if(customer_id === ""){
            return;
        }
        let apiUrl = "{{ route('admin.sales_order.getCustomerDesign') }}";
        $.ajax({
            url: apiUrl,   // Route
            type: 'GET',
            data: { customer_id: customer_id },
            success: function (response) {
                 $("#design_id").empty(); // clear select

                $("#design_id").append('<option value="">Select Size</option>');
               
                $.each(response, function(index, size){
                    $("#design_id").append(
                        `<option value="${index}">${size}</option>`
                    );
                });

                // Refresh select2
                $("#design_id").trigger('change');
            },
            error: function(xhr){
                console.log(xhr.responseText);
            }
        });

    });

    $('.select2').select2();

    // File preview
    // File preview (Image or PDF)
    $("#corporate_order_file").on("change", function (e) {
        let file = e.target.files[0];

        if (!file) return;

        let fileType = file.type;

        // Reset preview box
        $("#previewImg").hide().attr("src", "");
        $("#previewPDF").remove();

        // CASE 1 : If file is image
        if (fileType.startsWith("image/")) {

            let reader = new FileReader();
            reader.onload = () => {
                $("#previewImg")
                    .attr("src", reader.result)
                    .css({ "display": "block", "border-radius": "6px" })
                    .show();
            };
            reader.readAsDataURL(file);
        }

        // CASE 2 : If file is PDF
        else if (fileType === "application/pdf") {

            let src = URL.createObjectURL(file);

            // Create PDF viewer
            let pdfViewer = `
                <embed id="previewPDF" 
                    src="${src}" 
                    type="application/pdf" 
                    width="100%" 
                    height="550px" 
                    style="border:1px solid #ccc; border-radius:6px;" />
            `;

            $("#corporate_order_file").after(pdfViewer);
        }

        else {
            alert("Only Images or PDF files are allowed.");
            $(this).val(""); // reset file input
        }
    });


    // Show product image
    $(document).on("change", ".design-input", function () {
        let img = $(this).find(":selected").data("img");
        $(this).closest(".product-row").find(".img-section").html(
            img ? `<img src="${img}" style="max-width:150px;border:1px solid #ccc;">` : ''
        );
    });
    // click image to enlarge
    $(document).on("click", "img", function () {

        // ignore logo or system icons if needed
        if ($(this).hasClass("no-preview")) return;

        let fullImage = $(this).attr("src");

        if (!fullImage) return;

        $("#modal-photo").attr("src", fullImage);
        $("#photoModal").modal("show");
    });


    // Add product
    $(document).on("click", ".add-product", function () {

        let row = $(this).closest(".product-row");

        let design = row.find(".design-input option:selected");
        let size = row.find(".size-input option:selected");
        let colour = row.find(".colour-input option:selected");
        let qty = row.find(".qty-input").val();
        let pcsPerSet = size.data('pcs') || 1;
        let total_qty = qty * pcsPerSet; // Calculate total quantity

        if (!design.val() || !size.val() || !colour.val() || qty === "") {
            // alert("Please select all fields");
            return;
        }

        // $("#productList tbody").append(`
        //     <tr>
        //         <td>
        //             <img src="${design.data('img')}" style="width:50px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
        //             <input type="hidden" name="imageList[]" value="${design.data('img')}">
        //         </td>
        //         <td>${design.text()}
        //             <input type="hidden" name="designList[]" value="${design.val()}">
        //         </td>
        //         <td>${size.text()}
        //             <input type="hidden" name="sizeList[]" value="${size.val()}">
        //         </td>
        //         <td>${colour.text()}
        //             <input type="hidden" name="colourList[]" value="${colour.val()}">
        //         </td>
        //         <td>${qty}
        //             <input type="hidden" name="product_quantity[]" value="${qty}">
        //         </td>
        //         <td>${total_qty}
        //             <input type="hidden" name="total_quantity[]" value="${total_qty}">
        //         </td>
        //         <td>
        //             <button class="btn btn-danger btn-sm remove-row">X</button>
        //         </td>
        //     </tr>
        // `);

        $("#productList tbody").append(`
            <tr>
                <td>${design.text()}
                    <input type="hidden" name="designList[]" value="${design.val()}">
                </td>
                <td>${size.text()}
                    <input type="hidden" name="sizeList[]" value="${size.val()}">
                </td>
                <td>${colour.text()}
                    <input type="hidden" name="colourList[]" value="${colour.val()}">
                </td>
                <td>${qty}
                    <input type="hidden" name="product_quantity[]" value="${qty}">
                </td>
                <td>${pcsPerSet}
                    <input type="hidden" name="pcs[]" value="${pcsPerSet}">
                </td>
                <td>${total_qty}
                    <input type="hidden" name="total_quantity[]" value="${total_qty}">
                </td>
                <td>
                    <button class="btn btn-danger btn-sm remove-row">X</button>
                </td>
            </tr>
        `);
        row.find("select").val("").trigger("change");
        row.find(".qty-input").val("");
        row.find(".img-section").html("");
    });

    // Remove row
    $(document).on("click", ".remove-row", function () {
        $(this).closest("tr").remove();
    });

    // Validate before submit
    $("form").on("submit", function (e) {

        let rowCount = $("#productList tbody tr").length;

        if (rowCount === 0) {
            // alert("Please add at least one product before submitting.");
            e.preventDefault();
            return false;
        }

    });

});
</script>

@endsection
