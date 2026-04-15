@extends('admin.layouts.app')
@section('content')
    <style>
        .flatpickr-calendar {
            z-index: 9999 !important;
        }

        #fileBox {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .refresh-master-btn {
            border: 1px solid #d0d7de;
            background: #f8f9fa;
            color: #0d6efd;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .refresh-master-btn:hover {
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        .refresh-master-btn i {
            transition: transform 0.4s ease;
        }

        .refresh-master-btn:hover i {
            transform: rotate(180deg);
        }
    </style>

    <div class="content-wrapper">

        <!-- HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12 text-center">
                        <h1>Corporate Order</h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <form action="{{ route('admin.sales_order.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- CUSTOMER / DELIVERY / FILE -->
                    <div class="card p-3 mb-3 shadow-sm border-0">
                        <h5 class="border-bottom pb-2 mb-3">Customer & Delivery</h5>

                        <div class="row">

                            <div class="col-md-12 mb-3 d-none">
                                <label class="d-block">Order Type</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="order_type" id="order_type_domestic"
                                        value="domestic">
                                    <label class="form-check-label" for="order_type_domestic">Domestic</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="order_type" id="order_type_corporate"
                                        value="corporate" checked>
                                    <label class="form-check-label" for="order_type_corporate">Corporate</label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label>Select Customer</label>
                                <select name="master_customer_id" id="master_customer_id" class="form-control select2"
                                    required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- <div class="col-md-4">
                                            <label>Expected Delivery Date</label>

                                            <input type="text" id="ex_delivery_date" class="form-control" placeholder="Select Expected Delivery Date">

                                            <input type="hidden"
                                                name="expected_delivery_date"
                                                id="ex_d_date_hidden"
                                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                        </div> -->

                            <div class="col-md-4">
                                <label>Expected Delivery Date</label>
                                <input type="date" name="expected_delivery_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label>PO Number</label>
                                <input type="text" name="po_number" class="form-control" placeholder="PO Number (Optional)">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>PO Date</label>
                                <input type="date" name="po_date" class="form-control">
                            </div>

                            <div class="col-md-4 mt-2">
                                <label>Upload Order File</label>
                                <input type="file" name="corporate_order_file" id="corporate_order_file"
                                    class="form-control">
                            </div>

                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div id="fileBox" class="d-none p-2 border rounded bg-light">
                                    <strong>Uploaded File:</strong>
                                    <span id="fileName"></span>
                                    <a href="#" id="openFileBtn" target="_blank" class="btn btn-sm btn-primary ml-2">
                                        Open File
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ADD PRODUCT -->
                    <div class="card p-3 mb-3 shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="mb-0">Add Product</h5>

                            <button type="button" class="btn btn-light btn-sm refresh-master-btn"
                                onclick="loadSalesOrderMasterData()">
                                <i class="fa fa-sync-alt mr-1"></i>
                                Refresh Design / Size / Colour
                            </button>
                        </div>

                        <div class="row product-row">

                            <div class="col-md-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Provided Bar Code</label>
                                </div>
                                <input type="text" id="bar_code" name="bar_code" class="form-control bar_code-input">
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Design Number</label>
                                    <a href="{{ route('admin.master.production-goods.create') }}" target="_blank"
                                        class="text-primary" style="font-size:13px;">
                                        Create New +
                                    </a>
                                </div>

                                <select class="form-control select2 design-input" name="design_id" id="design_id">

                                    <option value="">Select Design</option>

                                </select>
                            </div>

                            <div class="col-md-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Set Size</label>
                                    <a href="{{ route('admin.master.size-measurement.create') }}" target="_blank"
                                        class="text-primary" style="font-size:13px;">
                                        Create New +
                                    </a>
                                </div>

                                <select class="form-control select2 size-input" name="set_size" id="set_size">
                                    <option value="">Select Set Size</option>

                                </select>
                                <input type="hidden" id="size_radio" name="size_radio">
                                <label id="custom_size_set_show"></label>
                                <div class="open-label" id="openCustomSizeBtn" style="display:none;">
                                    Update Ratio
                                </div>

                            </div>

                            <div class="col-md-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Colour</label>
                                    <a href="{{ route('admin.master.colors.create') }}" target="_blank" class="text-primary"
                                        style="font-size:13px;">
                                        Create New +
                                    </a>
                                </div>

                                <select class="form-control select2 colour-input" name="colour_id">
                                    <option value="">Select Colour</option>

                                </select>
                            </div>

                            <div class="col-md-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <label class="mb-0">Qty</label>
                                </div>

                                <input type="number" min="1" class="form-control qty-input">
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-primary w-100 add-product">
                                    +
                                </button>
                            </div>

                        </div>

                        <div class="img-section text-center mt-3"></div>
                    </div>

                    <!-- PRODUCT LIST -->
                    <div class="card p-3 shadow-sm border-0">
                        <h5 class="border-bottom pb-2 mb-3">Added Products</h5>

                        <table class="table table-bordered" id="productList">
                            <thead class="bg-light">
                                <tr>
                                    <th>Bar Code</th>
                                    <th>Design</th>
                                    <th>Set Size</th>
                                    <th>Colour</th>
                                    <th>Set Qty</th>
                                    <th>Pcs/Set</th>
                                    <th>Total Qty</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">TOTAL</td>
                                    <td id="total_set_qty">0</td>
                                    <td></td>
                                    <td id="total_pcs_qty">0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-right mt-3">
                        <button class="btn btn-success px-4">
                            Submit Order
                        </button>
                    </div>

                </form>
            </div>
        </section>
    </div>

    <!-- IMAGE MODAL -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <button type="button" class="close position-absolute" style="top:10px; right:10px; font-size:30px;"
                    data-dismiss="modal">
                    &times;
                </button>
                <div class="modal-body text-center p-0">
                    <img id="modal-photo" style="width:100%;">
                </div>
            </div>
        </div>
    </div>

    <!-- CUSTOM SIZE MODAL -->
    <div class="modal" id="sizeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Update Ratio</h4>
                <span class="close" onclick="closeModal()">×</span>
            </div>
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
            <div class="modal-footer">
                <button type="button" class="save-btn" onclick="saveGroup()">Save</button>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 10px
        }

        .open-label {
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
            text-decoration: underline
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            justify-content: center;
            align-items: center;
            z-index: 9999
        }

        .modal-content {
            background: #fff;
            width: 420px;
            padding: 20px;
            border-radius: 8px
        }

        .size-row {
            display: flex;
            justify-content: space-between;
            padding: 6px;
            background: #eef2f7;
            margin-top: 6px
        }

        .counter button {
            width: 28px;
            height: 28px;
            border: none;
            background: #28a745;
            color: #fff
        }
    </style>

    <script>
        $(document).ready(function () {

            $('.select2').select2();
            loadSalesOrderMasterData();


            // File preview
            // File preview (Image or PDF)
            $("#corporate_order_file").on("change", function (e) {

                let file = e.target.files[0];
                if (!file) return;

                let fileURL = URL.createObjectURL(file);

                $("#fileName").text(file.name);
                $("#openFileBtn").attr("href", fileURL);
                $("#fileBox").removeClass("d-none");
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

                if (!design.val() || !size.val() || !colour.val() || qty === "") {
                    // alert("Please select all fields");
                    return;
                }

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
            $('#openCustomSizeBtn').hide();
            $('#set_size').on('change', function () {

                if ($(this).val()) {
                    $('#openCustomSizeBtn')
                        .show()
                        .attr('onclick', 'openModal()');
                } else {
                    $('#openCustomSizeBtn')
                        .hide()
                        .removeAttr('onclick');
                }

                let option = $(this).find(':selected');
                let setGroup = option.data('set-group') || "";   // allow blank
                let setSizeName = option.text();

                $('#size_name').text(setSizeName);
                currentSetSizeOption = option;
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
            let sizeRatio = getSizeRatio(group.join(','));
            const groupTextElement = document.getElementById('size_radio');
            groupTextElement.value = sizeRatio;
        }

        function getSizeRatio(sizeString) {

            let sizes = sizeString.split(',');

            let countMap = {};

            sizes.forEach(size => {
                countMap[size] = (countMap[size] || 0) + 1;
            });

            // size ke order me ratio chahiye
            let ratio = Object.keys(countMap)
                .sort((a, b) => a - b)
                .map(size => countMap[size]);

            return ratio.join(',');
        }


        /* --------------------
           Save edited group back to option
        -------------------- */
        function saveGroup() {
            console.log('ad=---------');
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
            if (finalGroup === '') {
                return;
            }
            let apiUrl = "{{ route('admin.sales_order.saveCustomSetSize') }}";
            $.ajax({
                url: apiUrl,   // Route
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    customer_id: customer_id,
                    set_size_id: set_size_id,
                    set_size_name: set_size,
                    finalGroup: finalGroup,
                    design_id: design_id,
                },
                success: function (response) {
                    if (response.new_size_group) {
                        $('#custom_size_set_show').text("New Size Set (" + response.new_size_group + ")");
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
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        function calculateGrandTotal() {

            let totalSetQty = 0;
            let totalPcsQty = 0;

            $("#productList tbody tr").each(function () {
                let setQty = parseFloat($(this).find("input[name='product_quantity[]']").val() || 0);
                let pcsQty = parseFloat($(this).find("input[name='total_quantity[]']").val() || 0);

                totalSetQty += setQty;
                totalPcsQty += pcsQty;
            });

            $("#total_set_qty").text(totalSetQty.toLocaleString());
            $("#total_pcs_qty").text(totalPcsQty.toLocaleString());
        }


    </script>
    <script>
        flatpickr("#ex_delivery_date", {
            dateFormat: "d M Y",
            appendTo: document.body,
            position: "auto left",
            static: false,
            defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d') }}",
            onChange: function (selectedDates) {
                document.getElementById("ex_d_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });

        flatpickr("#delivery_date", {
            dateFormat: "d M Y",
            minDate: "today",
            onChange: function (selectedDates) {
                document.getElementById("delivery_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });
    </script>

    <script>
        function loadSalesOrderMasterData() {

            $.ajax({
                url: "{{ route('admin.sales_order.master_data') }}",
                type: "GET",
                success: function (res) {

                    /* ------------------------
                       DESIGN
                    ------------------------ */
                    let designSelect = $('#design_id');
                    designSelect.empty().append('<option value="">Select Design</option>');

                    res.products.forEach(item => {
                        let seriesName = item.series ? item.series.name : '';
                        let garmentName = item.name_of_garment ? item.name_of_garment : '';
                        designSelect.append(
                            `<option value="${item.id}">${item.design_number} (${seriesName} ${garmentName})</option>`
                        );
                    });

                    /* ------------------------
                       SIZE
                    ------------------------ */
                    let sizeSelect = $('#set_size');
                    sizeSelect.empty().append('<option value="">Select Set Size</option>');

                    res.sizes.forEach(item => {
                        sizeSelect.append(
                            `<option value="${item.id}" data-set-group="${item.size_group}" data-pcs="${item.no_of_pcs}">
                                        ${item.name}
                                    </option>`
                        );
                    });

                    /* ------------------------
                       COLOUR
                    ------------------------ */
                    let colourSelect = $('.colour-input');
                    colourSelect.empty().append('<option value="">Select Colour</option>');

                    res.colours.forEach(item => {
                        colourSelect.append(
                            `<option value="${item.id}">${item.name}</option>`
                        );
                    });

                    $('.select2').trigger('change');
                }
            });
        }
    </script>

@endsection