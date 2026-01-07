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

                                {{-- <label>GST (in %)</label>
                                <input type="number" id="gst_percentage" name="gst_percentage" class="form-control"
                                    min="0" step="0.01" >
                                @if ($errors->has('gst_percentage'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('gst_percentage') }}
                                    </span>
                                @endif --}}
                            </div>

                            <!-- Add Product -->
                            <div class="card p-3 border">
                                <h5 class="mb-3">Add Product</h5>

                                <div class="product-row">
                                    <label for="bar_code">Provided Bar Code</label>

                                    <input
                                        type="text"
                                        id="bar_code"
                                        name="bar_code"
                                        class="form-control bar_code-input mb-3 @error('bar_code') is-invalid @enderror"
                                        placeholder="Enter bar code"
                                    >
                                    @error('bar_code')
                                        <span class="invalid-feedback d-block">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                    <div class="col-md-12">
                                        <label>Design Number (Royal Jeans)</label>
                                        <a href="{{route('admin.master.production-goods.create')}}" target="_blank" style="float:right;">Create New +</a>
                                    </div>
                                    
                                    <select class="form-control select2 mb-2 design-input" name="design_id" id="design_id">
                                        <option value="">Select</option>
                                        
                                    </select>
                                    @if ($errors->has('designList'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('designList') }}
                                        </span>
                                    @endif
                                    <div class="col-md-12">
                                        <label>Name of the Set Size (Royal Jeans)</label>
                                        <a href="{{route('admin.master.size-measurement.create')}}" target="_blank" style="float:right;">Create New +</a>
                                    </div>
                                    <select class="form-control select2 mb-2 size-input" name="set_size" id="set_size">
                                        {{-- @foreach($product_size as $size)
                                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                                        @endforeach --}}
                                    </select>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label id="custom_size_set_show" ></label>

                                        <div id="custom_size_set" class="d-none">
                                            <label class="open-label" onclick="openModal()">Create Custom Size Set</label>
                                        </div>
                                    </div>
                                    {{-- <div class="modal" id="sizeModal">
                                        <div class="modal-content login-style">

                                            <!-- Header -->
                                            <div class="modal-header login-header">
                                                <h4>Create Custom Size Set</h4>
                                                <span class="close" onclick="closeModal()">×</span>
                                            </div>

                                            <!-- Body -->
                                            <div class="modal-body">
                                                <div id="sizeList" class="size-list">

                                                </div>

                                                <div class="output">
                                                    <span>Size Group</span>
                                                    <strong id="groupText">—</strong>
                                                </div>

                                            </div>

                                            <!-- Footer -->
                                            <div class="modal-footer login-footer">
                                                <button class="save-btn" onclick="saveGroup()">Save</button>
                                            </div>

                                        </div>
                                    </div> --}}
                                    <!-- Custom Size Modal -->
                                    <div class="modal" id="sizeModal">
                                        <div class="modal-content login-style">

                                            <!-- Header -->
                                            <div class="modal-header login-header">
                                                <h4>Create Custom Size Set</h4>
                                                <span class="close" onclick="closeModal()">×</span>
                                            </div>

                                            <!-- Body -->
                                            <div class="modal-body">
                                                <div class="output">
                                                    <span>Size Name :</span>
                                                    <span id="size_name"></span>
                                                </div>
                                                <div id="sizeList"></div>

                                                <div class="output">
                                                    <span>Size Group:</span>
                                                    <strong id="groupText">—</strong>
                                                </div>
                                            </div>

                                            <!-- Footer -->
                                            <div class="modal-footer login-footer">
                                                <button type="button" class="save-btn" onclick="saveGroup()">Save</button>
                                            </div>

                                        </div>
                                    </div>


                                    @if ($errors->has('sizeList'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sizeList') }}
                                        </span>
                                    @endif
                                    <div class="col-md-12">
                                        <label>Colour (Royal Jeans)</label>
                                        <a href="{{route('admin.master.colors.create')}}" target="_blank" style="float:right;">Create New +</a>
                                    </div>
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

                                    {{-- <label>Basic Amount (Royal Jeans)</label>
                                    <input type="number" class="form-control qty-input mb-3" min="1" id="rate" name="rate">
                                    @if ($errors->has('product_rate'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('product_rate') }}
                                        </span>
                                    @endif --}}
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
                                            <th>Provided Bar Code</th>
                                            <th>Design</th>
                                            <th>Set Size</th>
                                            <th>Colour</th>
                                            <th>Set Quantity</th>
                                            <th>Pcs per Set</th>
                                            <th>Total Quantity</th>
                                            {{-- <th>Basic Amount</th> --}}
                                            {{-- <th>GST (%)</th>
                                            <th>Total Amount</th> --}}
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    {{-- <tfoot>
                                        <tr>
                                            <!-- Total Amount ke pehle ke columns -->
                                            <th colspan="9" class="text-right">Grand Total</th>

                                            <!-- Total Amount column -->
                                            <th class="text-right">
                                                <strong id="grand_total_text">0.00</strong>
                                                <input type="hidden" name="grand_total" id="grand_total">
                                            </th>

                                            <!-- Action column -->
                                            <th></th>
                                        </tr>
                                    </tfoot> --}}
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


<style>
   
    /* Button */
    .open-btn {
        padding: 10px 18px;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 15px;
    }

    /* Modal Overlay */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
    }

    /* Modal Box */
    .modal-content {
        background: #fff;
        width: 420px;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0,0,0,.2);
        animation: scaleIn .2s ease;
    }

    @keyframes scaleIn {
        from { transform: scale(.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .close {
        font-size: 20px;
        cursor: pointer;
        font-weight: bold;
    }

    select {
        width: 100%;
        padding: 8px;
        margin-top: 10px;
    }

    .size-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        padding: 6px 10px;
        background: #eef2f7;
        border-radius: 5px;
    }

    .counter button {
        width: 28px;
        height: 28px;
        border: none;
        background: #28a745;
        color: #fff;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .counter span {
        margin: 0 8px;
        font-weight: bold;
    }

    .output {
        margin-top: 15px;
        font-weight: bold;
    }

    .modal-footer {
        text-align: right;
        margin-top: 15px;
    }

    .save-btn {
        background: #007bff;
        color: #fff;
        padding: 8px 14px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .open-label{
        cursor: pointer;
        color: #007bff;
        font-weight: 600;
        text-decoration: underline;
        display: inline-block;
    }
    .open-label:hover{
        color: #0056b3;
    }


    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-content {
        background: #fff;
        width: 420px;
        padding: 20px;
        border-radius: 8px;
    }

    .size-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        padding: 6px 10px;
        background: #eef2f7;
        border-radius: 5px;
    }

    .counter button {
        width: 28px;
        height: 28px;
        border: none;
        background: #28a745;
        color: #fff;
        font-size: 16px;
        border-radius: 4px;
        cursor: pointer;
    }

    .counter span {
        margin: 0 8px;
        font-weight: bold;
    }

    .open-label {
        cursor: pointer;
        color: #007bff;
        font-weight: 600;
        text-decoration: underline;
    }

</style>

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
                    let pcsPerSet = parts[1] ?? '';       // "12"
                    let setGroup = parts[2] ?? '';
                    $("#set_size").append(`
                        <option value="${index}" data-pcs="${pcsPerSet}" data-set-group="${setGroup}">
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
        let bar_code = row.find(".bar_code-input").val();
        let design = row.find(".design-input option:selected");
        let size = row.find(".size-input option:selected");
        let colour = row.find(".colour-input option:selected");
        let qty = row.find(".qty-input").val();
        let no_of_pcs_hidden = row.find('#no_of_pcs_hidden').val();
        let pcsPerSet = no_of_pcs_hidden ? no_of_pcs_hidden : (size.data('pcs') || 1);
        let total_qty = qty * pcsPerSet; // Calculate total quantity
        
        let hidden_set_size_id = row.find('#size_set_hidden').val();

        let size_set_id = hidden_set_size_id ? hidden_set_size_id : size.val();
        // let gst_percentage = $("#gst_percentage").val() ? $("#gst_percentage").val() : 0;
        // let rate = parseFloat(row.find("input[name='rate']").val());
        // let total_amount = rate + (rate * gst_percentage / 100);
        
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
                <td>${bar_code}
                    <input type="hidden" name="bar_codeList[]" value="${bar_code}">
                </td>
                <td>${design.text()}
                    <input type="hidden" name="designList[]" value="${design.val()}">
                </td>
                <td>${size.text()}
                    <input type="hidden" name="sizeList[]" value="${size_set_id}">
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
        // row.find("#rate").val("");
        $("#bar_code").val("");
        row.find(".img-section").html("");
        $("#custom_size_set_show").html("");
        $("#no_of_pcs_hidden").val("");
        $("#size_set_hidden").val("");
        
        calculateGrandTotal();
        
    });

    // Remove row
    $(document).on("click", ".remove-row", function () {
        $(this).closest("tr").remove();
        calculateGrandTotal();
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



/////// custom size set
    $(document).ready(function () {
    
        /* --------------------
        Open modal on set_size change
        -------------------- */
        $('#set_size').on('change', function () {

            let option = $(this).find(':selected');
            let setGroup = option.data('set-group');
            let setSizeName = $('#set_size option:selected').text();
            if (!setGroup) {
                return;
            }
            
            $('#size_name').text(setSizeName); 
            $('#custom_size_set').removeClass('d-none'); 
            currentSetSizeOption = option; // save reference
            loadSizeGroup(setGroup);
        });


    });
        let sizeCounts = {};
    let currentSetSizeOption = null;
    /* --------------------
   Modal open / close
-------------------- */
function openModal() {
    document.getElementById('sizeModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('sizeModal').style.display = 'none';
}

/* --------------------
   Load size group from data-set-group
   Example: "18,19,20"
-------------------- */
function loadSizeGroup(group) {

    sizeCounts = {};

    if (group) {
        group.toString().split(',').forEach(size => {
            sizeCounts[size] = 1; // initial count = 1
        });
    }

    renderSizes();
}

/* --------------------
   + / - buttons
-------------------- */
function changeCount(size, change) {

    sizeCounts[size] += change;

    // minimum 1 allowed
    if (sizeCounts[size] < 0) {
        sizeCounts[size] = 0;
        return;
    }

    renderSizes();
}

/* --------------------
   Render UI + final string
-------------------- */
function renderSizes() {

    let list = document.getElementById('sizeList');
    list.innerHTML = '';

    let group = [];

    Object.keys(sizeCounts)
        .sort((a, b) => a - b)
        .forEach(size => {

            let count = sizeCounts[size];

            for (let i = 0; i < count; i++) {
                group.push(size);
            }

            list.innerHTML += `
                <div class="size-row">
                    <strong>${size}</strong>
                    <div class="counter">
                        <button type="button" onclick="changeCount('${size}', -1)">−</button>
                        <span>${count}</span>
                        <button type="button" onclick="changeCount('${size}', 1)">+</button>
                    </div>
                </div>
            `;
        });

    document.getElementById('groupText').innerText = group.join(',');
}

/* --------------------
   Save edited group back to option
-------------------- */
function saveGroup() {

    let finalGroup = document.getElementById('groupText').innerText;
    
    let option = $('#set_size').find(':selected');
    let setGroup = option.data('set-group');
    
    if (setGroup == finalGroup) {
        closeModal();
        return;
    }
        
    if (currentSetSizeOption) {
        currentSetSizeOption.attr('data-set-group', finalGroup);
    }
    
    closeModal();
    const CSRF_TOKEN = "{{ csrf_token() }}";
    let customer_id = $('#master_customer_id').val();
    let set_size = $('#set_size option:selected').text();
    let set_size_id = $('#set_size').val();
    let design_id = $('#design_id').val();
    if(customer_id === "" || finalGroup === ''){
        return;
    }
    let apiUrl = "{{ route('admin.sales_order.saveCustomSetSize') }}";
    $.ajax({
        url: apiUrl,   // Route
        type: 'POST',
        data: { 
            _token          :   CSRF_TOKEN,
            customer_id     :   customer_id,
            set_size_id     :   set_size_id, 
            set_size_name   :   set_size,
            finalGroup      :   finalGroup,  
            design_id       :   design_id, 
        },
        success: function (response) {
            if(response.new_size_group){
                $('#custom_size_set_show').text("New Size Set ("+ response.new_size_group + ")");
                // check hidden input exist or not
                if ($('#size_set_hidden').length === 0) {

                    // create hidden input if not exists
                    $('#custom_size_set_show').after(`
                        <input type="hidden" 
                            id="size_set_hidden" 
                            name="size_set_hidden" 
                            value="${response.new_size_set_id}">
                    `);

                    $('#custom_size_set_show').after(`
                        <input type="hidden" 
                            id="no_of_pcs_hidden" 
                            name="no_of_pcs_hidden" 
                            value="${response.no_of_pcs}">
                    `);
                    

                } else {
                    // update value if exists
                    $('#size_set_hidden').val(response.new_size_set_id);
                    $('#no_of_pcs_hidden').val(response.no_of_pcs);
                }
            }
        },
        error: function(xhr){
            console.log(xhr.responseText);
        }
    });
}
    
function calculateGrandTotal() {

    let grandTotal = 0;

    $("#productList tbody tr").each(function () {

        let rowTotal = parseFloat(
            $(this).find("input[name='total_amount[]']").val() || 0
        );

        grandTotal += rowTotal;
    });

    $("#grand_total_text").text(grandTotal.toFixed(2));
    $("#grand_total").val(grandTotal.toFixed(2));
}


</script>

@endsection
