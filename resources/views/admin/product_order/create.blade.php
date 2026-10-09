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
        font-size: var(--erp-font-sm);
    }
    .open-ratio-pill {
        cursor: pointer;
        color: var(--erp-green-primary) !important;
        background: var(--erp-green-light);
        border: 1px solid var(--erp-green-border);
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        display: inline-flex;
        align-items: center;
        margin-top: 3px;
    }
    .open-ratio-pill:hover {
        background: #d8edd2;
    }
    #sizeModal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .size-modal-card {
        background: #fff;
        width: 440px;
        max-width: 95vw;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--erp-border);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .size-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 10px;
        background: #f8fafc;
        border: 1px solid var(--erp-border);
        border-radius: 4px;
        margin-top: 5px;
    }
    .size-row .counter {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .size-row .counter button {
        width: 26px;
        height: 26px;
        border: none;
        background: var(--erp-green-primary);
        color: #fff;
        border-radius: 3px;
        font-weight: bold;
        line-height: 1;
        cursor: pointer;
    }
    .size-row .counter button:hover {
        background: var(--erp-green-hover);
    }
    .size-row .counter span {
        font-weight: 700;
        min-width: 20px;
        text-align: center;
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-industry text-primary"></i> Create Corporate Order
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <form action="{{ route('admin.sales_order.store') }}" method="POST" enctype="multipart/form-data" id="salesOrderForm">
        @csrf
        <input type="hidden" name="order_type" value="corporate">

        <!-- Card 1: Customer & Delivery Details -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-user-tie"></i> Customer & Delivery Information
                </span>
            </div>
            <div class="erp-card-body p-2">
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Select Customer <span class="required text-danger">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.customer.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New Customer"><i class="fas fa-plus"></i> New</a>
                                <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshCustomerBtn" title="Refresh Customer List"><i class="fas fa-sync-alt"></i></a>
                            </span>
                        </label>
                        <select name="master_customer_id" id="master_customer_id" class="form-control select2 erp-input" required style="width: 100%;">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="erp-label">Expected Delivery Date <span class="required text-danger">*</span></label>
                        <input type="date" name="expected_delivery_date" class="form-control erp-input" min="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="erp-label">PO Number (Optional)</label>
                        <input type="text" name="po_number" class="form-control erp-input" placeholder="e.g. PO-84920">
                    </div>

                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="erp-label">PO Date</label>
                        <input type="date" name="po_date" class="form-control erp-input">
                    </div>

                    <div class="col-md-4 col-sm-6 mb-1">
                        <label class="erp-label">Upload Order File (Image/PDF)</label>
                        <input type="file" name="corporate_order_file" id="corporate_order_file" class="form-control erp-input">
                    </div>

                    <div class="col-md-8 col-sm-6 mb-1 d-flex align-items-end">
                        <div id="fileBox" class="d-none p-1 px-2 border rounded bg-light w-100">
                            <strong>File:</strong>
                            <span id="fileName" class="text-truncate"></span>
                            <a href="#" id="openFileBtn" target="_blank" class="btn-erp btn-erp-primary ml-auto">
                                <i class="fas fa-external-link-alt"></i> Preview
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Add Product Line Item -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1 d-flex justify-content-between align-items-center">
                <span class="erp-card-title">
                    <i class="fas fa-tshirt"></i> Add Product Line Item
                </span>
                <button type="button" class="btn-erp btn-erp-outline" onclick="loadSalesOrderMasterData()" title="Reload Master Designs, Sizes, Colours">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh Masters
                </button>
            </div>
            <div class="erp-card-body p-2">
                <div class="row align-items-end product-row">
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">Bar Code (Optional)</label>
                        <input type="text" id="bar_code" name="bar_code" class="form-control erp-input bar_code-input" placeholder="Scan or enter...">
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Design Number <span class="text-danger">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.production-goods.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New Design"><i class="fas fa-plus"></i> New</a>
                            </span>
                        </label>
                        <select class="form-control select2 erp-input design-input" name="design_id" id="design_id" style="width: 100%;">
                            <option value="">-- Select Design --</option>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Set Size <span class="text-danger">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.size-measurement.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New Size"><i class="fas fa-plus"></i> New</a>
                            </span>
                        </label>
                        <select class="form-control select2 erp-input size-input" name="set_size" id="set_size" style="width: 100%;">
                            <option value="">-- Select Set Size --</option>
                        </select>
                        <input type="hidden" id="size_radio" name="size_radio">
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span id="custom_size_set_show" class="small text-success font-weight-bold"></span>
                            <span class="open-ratio-pill" id="openCustomSizeBtn" style="display:none;">
                                <i class="fas fa-sliders-h mr-1"></i> Update Ratio
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Colour <span class="text-danger">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.colors.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New Colour"><i class="fas fa-plus"></i> New</a>
                            </span>
                        </label>
                        <select class="form-control select2 erp-input colour-input" name="colour_id" style="width: 100%;">
                            <option value="">-- Select Colour --</option>
                        </select>
                    </div>

                    <div class="col-lg-1 col-md-2 col-sm-3 mb-2">
                        <label class="erp-label">Sets Qty <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control erp-input qty-input" placeholder="Qty">
                    </div>

                    <div class="col-lg-1 col-md-2 col-sm-3 mb-2">
                        <button type="button" class="btn-erp btn-erp-primary add-product w-100 justify-content-center" style="height: 31px;">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

                <div class="img-section text-center mt-2"></div>
            </div>
        </div>

        <!-- Card 3: Added Products List -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-list-check"></i> Added Products in Order
                </span>
            </div>
            <div class="erp-card-body p-2 table-responsive">
                <table class="erp-table table table-bordered table-hover mb-0" id="productList">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Bar Code</th>
                            <th>Design</th>
                            <th>Set Size</th>
                            <th style="width: 120px;">Colour</th>
                            <th style="width: 100px;" class="text-right">Set Qty</th>
                            <th style="width: 90px;" class="text-right">Pcs / Set</th>
                            <th style="width: 110px;" class="text-right font-weight-bold">Total Qty</th>
                            <th style="width: 65px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr class="bg-light font-weight-bold">
                            <td colspan="4" class="text-right">TOTAL:</td>
                            <td id="total_set_qty" class="text-right font-weight-bold">0</td>
                            <td></td>
                            <td id="total_pcs_qty" class="text-right text-primary font-weight-bold">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Submit Footer -->
        <div class="erp-card p-2 d-flex justify-content-between align-items-center">
            <div class="text-muted font-weight-bold small">
                <i class="fas fa-info-circle mr-1"></i> Review all added product lines before saving.
            </div>
            <div>
                <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline mr-2">
                    Cancel
                </a>
                <button type="submit" class="btn-erp btn-erp-success">
                    <i class="fas fa-check-circle"></i> Submit Order
                </button>
            </div>
        </div>
    </form>
</div>

<!-- IMAGE PREVIEW MODAL -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <div class="modal-header py-1 px-2 bg-light">
                <span class="font-weight-bold small">Design Preview</span>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="modal-photo" style="max-width:100%; max-height: 480px; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- CUSTOM SIZE MODAL -->
<div id="sizeModal">
    <div class="size-modal-card">
        <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
            <h6 class="modal-title font-weight-bold mb-0">
                <i class="fas fa-sliders-h mr-1"></i> Update Ratio
            </h6>
            <button type="button" class="close text-white" onclick="closeModal()" style="opacity: 0.9;">&times;</button>
        </div>
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                <span class="text-muted font-weight-bold small">Size Set:</span>
                <span id="size_name" class="font-weight-bold text-dark"></span>
            </div>
            <div id="sizeList" class="my-2" style="max-height: 240px; overflow-y: auto;"></div>
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                <span class="text-muted font-weight-bold small">Resulting Size Group:</span>
                <strong id="groupText" class="text-primary">—</strong>
            </div>
        </div>
        <div class="modal-footer py-2 px-3 bg-light">
            <button type="button" class="btn-erp btn-erp-outline" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn-erp btn-erp-primary" onclick="saveGroup()">Save Ratio</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });
        loadSalesOrderMasterData();

        // Refresh Customer
        $('#refreshCustomerBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.sales_order.all_customers') }}", function(data) {
                var select = $('#master_customer_id');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">-- Select Customer --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        // File preview
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
                img ? `<img src="${img}" style="max-height:120px; border:1px solid #ced4da; border-radius: 4px; padding: 2px; cursor: pointer;">` : ''
            );
        });

        // Enlarge image
        $(document).on("click", "img", function () {
            if ($(this).hasClass("no-preview") || $(this).hasClass("brand-image")) return;
            let fullImage = $(this).attr("src");
            if (!fullImage) return;

            $("#modal-photo").attr("src", fullImage);
            $("#photoModal").modal("show");
        });

        // Add product line item
        $(document).on("click", ".add-product", function () {
            let row = $(this).closest(".product-row");
            let bar_code = row.find(".bar_code-input").val();
            let design = row.find(".design-input option:selected");
            let size = row.find(".size-input option:selected");
            let colour = row.find(".colour-input option:selected");
            let qty = row.find(".qty-input").val();
            let no_of_pcs_hidden = row.find('#no_of_pcs_hidden').val();
            let pcsPerSet = no_of_pcs_hidden ? no_of_pcs_hidden : (size.data('pcs') || 1);
            let total_qty = qty * pcsPerSet;

            let hidden_set_size_id = row.find('#size_set_hidden').val();
            let size_set_id = hidden_set_size_id ? hidden_set_size_id : size.val();

            if (!design.val() || !size.val() || !colour.val() || qty === "" || parseInt(qty) <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Details',
                    text: 'Please select Design, Size Set, Colour and enter a valid Quantity.'
                });
                return;
            }

            $("#productList tbody").append(`
                <tr>
                    <td>${bar_code || '-'}
                        <input type="hidden" name="bar_codeList[]" value="${bar_code}">
                    </td>
                    <td class="font-weight-bold">${design.text()}
                        <input type="hidden" name="designList[]" value="${design.val()}">
                    </td>
                    <td>${size.text()}
                        <input type="hidden" name="sizeList[]" value="${size_set_id}">
                    </td>
                    <td>${colour.text()}
                        <input type="hidden" name="colourList[]" value="${colour.val()}">
                    </td>
                    <td class="text-right font-weight-bold">${qty}
                        <input type="hidden" name="product_quantity[]" value="${qty}">
                    </td>
                    <td class="text-right">${pcsPerSet} 
                        <input type="hidden" name="pcs[]" value="${pcsPerSet}">
                    </td>
                    <td class="text-right text-primary font-weight-bold">${total_qty}
                        <input type="hidden" name="total_quantity[]" value="${total_qty}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);

            row.find("select").val("").trigger("change");
            row.find(".qty-input").val("");
            $("#bar_code").val("");
            row.find(".img-section").html("");
            $("#custom_size_set_show").html("");
            $("#no_of_pcs_hidden").val("");
            $("#size_set_hidden").val("");

            calculateGrandTotal();
        });

        // Remove line item
        $(document).on("click", ".remove-row", function () {
            $(this).closest("tr").remove();
            calculateGrandTotal();
        });

        // Validate form submit
        $("#salesOrderForm").on("submit", function (e) {
            let rowCount = $("#productList tbody tr").length;
            if (rowCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Products',
                    text: 'Please add at least one product before submitting the order.'
                });
                return false;
            }
        });
    });

    // Custom size set ratio logic
    $(document).ready(function () {
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
            let setGroup = option.data('set-group') || "";
            let setSizeName = option.text();

            $('#size_name').text(setSizeName);
            currentSetSizeOption = option;
            loadSizeGroup(setGroup);
        });
    });

    let sizeCounts = {};
    let currentSetSizeOption = null;

    function openModal() {
        document.getElementById('sizeModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('sizeModal').style.display = 'none';
    }

    function loadSizeGroup(group) {
        sizeCounts = {};
        if (group) {
            group.toString().split(',').forEach(size => {
                sizeCounts[size] = 1;
            });
        }
        renderSizes();
    }

    function changeCount(size, change) {
        sizeCounts[size] += change;
        if (sizeCounts[size] < 0) {
            sizeCounts[size] = 0;
            return;
        }
        renderSizes();
    }

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
                        <strong class="text-dark">${size}</strong>
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
        let ratio = Object.keys(countMap)
            .sort((a, b) => a - b)
            .map(size => countMap[size]);
        return ratio.join(',');
    }

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
        if (finalGroup === '') {
            return;
        }
        let apiUrl = "{{ route('admin.sales_order.saveCustomSetSize') }}";
        $.ajax({
            url: apiUrl,
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
                    $('#custom_size_set_show').text("Ratio: (" + response.new_size_group + ")");
                    if ($('#size_set_hidden').length === 0) {
                        $('#custom_size_set_show').after(`
                            <input type="hidden" id="size_set_hidden" name="size_set_hidden" value="${response.new_size_set_id}">
                        `);
                        $('#custom_size_set_show').after(`
                            <input type="hidden" id="no_of_pcs_hidden" name="no_of_pcs_hidden" value="${response.no_of_pcs}">
                        `);
                    } else {
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

    function loadSalesOrderMasterData() {
        $.ajax({
            url: "{{ route('admin.sales_order.master_data') }}",
            type: "GET",
            success: function (res) {
                let designSelect = $('#design_id');
                designSelect.empty().append('<option value="">-- Select Design --</option>');
                res.products.forEach(item => {
                    let seriesName = item.series ? item.series.name : '';
                    let garmentName = item.name_of_garment ? item.name_of_garment : '';
                    designSelect.append(
                        `<option value="${item.id}" data-img="${item.photo ? '{{ asset('/') }}' + item.photo : ''}">${item.design_number} (${seriesName} ${garmentName})</option>`
                    );
                });

                let sizeSelect = $('#set_size');
                sizeSelect.empty().append('<option value="">-- Select Set Size --</option>');
                res.sizes.forEach(item => {
                    if (item.status == 1) {
                        sizeSelect.append(
                            `<option value="${item.id}" data-set-group="${item.size_group}" data-pcs="${item.no_of_pcs}">
                                ${item.name}
                            </option>`
                        );
                    }
                });

                let colourSelect = $('.colour-input');
                colourSelect.empty().append('<option value="">-- Select Colour --</option>');
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