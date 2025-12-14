@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">Fabric Rolls Assigning</h1>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN SECTION -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">

                <form action="{{ route('admin.order_digitalization.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                   <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <!-- Customer & Delivery -->
                            <div class="card mb-3 p-3 border">
                                <label>Date - 14/12/2025</label>
                                <label>Lot No.</label>
                                <input type="text" class="form-control qty-input mb-3" placeholder="Enter Lot No." name="lot_no" id="lot_no">
                                {{-- <select name="order_no" id="order_no" class="form-control select2 mb-2" required>
                                    <option value="">Select Order No</option>
                                    @foreach($order_no_data as $order_id => $order_no)
                                        <option value="{{ $order_id }}">{{ $order_no }}</option>
                                    @endforeach
                                </select> --}}
                                @if ($errors->has('lot_no'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('lot_no') }}
                                    </span>
                                @endif

                                <label class="mt-2">From - (Cutting Master)</label>
                                <select name="cutting_unit" id="cutting_unit" class="form-control select2 mb-2" required>
                                    <option value="">Select Cutting Master</option>
                                    @foreach($cutting_units as $cutting_unit)
                                        <option value="{{ $cutting_unit->id }}">{{ $cutting_unit->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('cutting_unit'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('cutting_unit') }}
                                    </span>
                                @endif
                                {{-- <label>To -</label>
                                <select name="master_customer_id" id="master_customer_id" class="form-control select2 mb-2" required>
                                    <option value="">Select </option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('expected_delivery_date'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('expected_delivery_date') }}
                                    </span>
                                @endif --}}
                            </div>

                            <!-- Add Product -->
                            <div class="card p-3 border">
                                <h5 class="mb-3">Add Rolls</h5>

                                <div class="product-row">

                                    <label class="mt-2">Rolls No.</label>
                                    <input type="text" name="roll_no" id="roll_no" class="form-control" placeholder="Enter Roll No." >
                                    <label class="mt-2">Meters</label>
                                    <input type="number" name="meter" id="meter" class="form-control" placeholder="Enter Meter" min="0" step='0.01'>
                            
                                    <button type="button" class="btn btn-primary mt-3 btn-block add-product">
                                        + Add Rolls
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
                                            <th>Lot No.</th>
                                            <th>From - (Cutting Master)</th>
                                            <th>Rolls No.</th>
                                            <th>Meters</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-6 text-left">
                            <button class="btn btn-success px-4">Skip</button>
                        </div>
                        <div class="col-6 text-right">
                            <button class="btn btn-success px-4">Submit Order</button>
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
$(document).ready(function () {

    $('#fabric_id').on('change', function () {
       
        // let customer_id = $('#master_customer_id').val(1);
        let fabric_id = $(this).val();
        
        let apiUrl = "{{ route('admin.order_digitalization.getRollsData') }}";
        $.ajax({
            url: apiUrl,   // Route
            type: 'GET',
            data: { fabric_id: fabric_id },
            success: function (response) {
                $("#roll_no").empty(); // clear select

                $("#roll_no").append('<option value="">Select Rolls No</option>');
               
                $.each(response, function(index, roll_name){
                    $("#roll_no").append(`
                        <option value="${index}" >
                            ${roll_name}
                        </option>
                    `);
                });

                // Refresh select2
                $("#roll_no").trigger('change');
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


    $(document).on("click", ".add-product", function () {

        let lot_no     = $("#lot_no").val();
        let cuttingUnit = $("#cutting_unit option:selected");
        let roll        = $("#roll_no option:selected");
        let meter       = $("#meter").val();

        if (
            !lot_no."" ||
            !cuttingUnit.val() ||
            !fabric.val() ||
            !roll.val() ||
            meter === ""
        ) {
            alert("Please fill all required fields");
            return;
        }

        $("#productList tbody").append(`
            <tr>
                <td>${lot_no.text()}
                    <input type="hidden" name="lot_no_list[]" value="${lot_no.val()}">
                </td>

                <td>${cuttingUnit.text()}
                    <input type="hidden" name="cutting_unit_list[]" value="${cuttingUnit.val()}">
                </td>

                <td>${fabric.text()}
                    <input type="hidden" name="fabric_id_list[]" value="${fabric.val()}">
                </td>

                <td>${roll.text()}
                    <input type="hidden" name="roll_no_list[]" value="${roll.val()}">
                </td>

                <td>${meter}
                    <input type="hidden" name="meter_list[]" value="${meter}">
                </td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                </td>
            </tr>
        `);

        // Reset fields
        $("#fabric_id").val("").trigger("change");
        $("#roll_no").empty().append('<option value="">Select Rolls No</option>').trigger("change");
        $("#meter").val("");
    });

    // Remove row
    $(document).on("click", ".remove-row", function () {
        $(this).closest("tr").remove();
    });

    // Validate before submit
    $("form").on("submit", function (e) {
        if ($("#productList tbody tr").length === 0) {
            alert("Please add at least one roll");
            e.preventDefault();
        }
    });

});
</script>

@endsection
