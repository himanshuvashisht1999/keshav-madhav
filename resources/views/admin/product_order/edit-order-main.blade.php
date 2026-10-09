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
            <i class="fas fa-edit text-primary"></i> Edit Sales Order (#{{ $data->sku }})
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <form action="{{ route('admin.product_order.updateOrderMain', $data->id) }}" method="POST" enctype="multipart/form-data" id="editOrderMainForm">
        @csrf

        <!-- Card 1: Customer & Delivery Details -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-user-tie"></i> Customer & Delivery Details
                </span>
            </div>
            <div class="erp-card-body p-2">
                <div class="row">
                    <div class="col-md-12 mb-2">
                        <label class="erp-label mb-1">Order Type</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="order_type" id="order_type_domestic"
                                value="domestic" {{ $data->order_type == 'domestic' ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold" for="order_type_domestic">Domestic</label>
                        </div>
                        <div class="form-check form-check-inline ml-3">
                            <input class="form-check-input" type="radio" name="order_type" id="order_type_corporate"
                                value="corporate" {{ $data->order_type == 'corporate' ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold" for="order_type_corporate">Corporate</label>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Select Customer <span class="required text-danger">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.customer.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New</a>
                                <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshCustomerBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                            </span>
                        </label>
                        <select name="master_customer_id" id="master_customer_id" class="form-control select2 erp-input" required style="width: 100%;">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $data->master_customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="erp-label">Expected Delivery Date <span class="required text-danger">*</span></label>
                        <input type="date" id="expected_delivery_date" class="form-control erp-input" value="{{ $data->expected_delivery_date }}" required>
                        <input type="hidden" name="expected_delivery_date" id="ex_d_date_hidden" value="{{ $data->expected_delivery_date }}">
                    </div>

                    <div class="col-md-3 col-sm-6 mb-2">
                        <label class="erp-label">PO Number (Optional)</label>
                        <input type="text" name="po_number" class="form-control erp-input" placeholder="PO Number" value="{{ $data->po_number }}">
                    </div>

                    <div class="col-md-2 col-sm-6 mb-2">
                        <label class="erp-label">PO Date</label>
                        <input type="date" name="po_date" class="form-control erp-input" value="{{ $data->po_date }}">
                    </div>

                    <div class="col-md-4 col-sm-6 mb-1">
                        <label class="erp-label">Upload New Order File</label>
                        <input type="file" name="corporate_order_file" id="corporate_order_file" class="form-control erp-input">
                    </div>

                    <div class="col-md-4 col-sm-6 mb-1">
                        @if($data->corporate_order_file)
                            <div id="existingFileBox" class="p-1 px-2 border rounded bg-light mt-4 d-flex align-items-center justify-content-between">
                                <span class="text-truncate mr-2 small"><strong>Current:</strong> {{ $data->corporate_order_file }}</span>
                                <a href="{{ asset('assets/products/' . $data->corporate_order_file) }}" target="_blank" class="btn-erp btn-erp-outline">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-4 col-sm-12 mb-1 d-flex align-items-end">
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
                    <i class="fas fa-list-check"></i> Products in Order
                </span>
            </div>
            <div class="erp-card-body p-2 table-responsive">
                <table class="erp-table table table-bordered table-hover mb-0" id="productList">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Bar Code</th>
                            <th>Design</th>
                            <th>Set Size</th>
                            <th style="width: 130px;">Colour</th>
                            <th style="width: 100px;" class="text-right">Set Qty</th>
                            <th style="width: 90px;" class="text-right">Pcs / Set</th>
                            <th style="width: 110px;" class="text-right font-weight-bold">Total Qty</th>
                            <th style="width: 65px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->OrderProductSets as $set)
                            @php
                                $isAssigned = \App\Models\OrderCuttingStage::where('set_product_id', $set->id)->exists();
                            @endphp
                            <tr class="product-row-item" data-assigned="{{ $isAssigned ? 'true' : 'false' }}">
                                <td>
                                    <span>{{ $set->bar_code ?: '-' }}</span>
                                    <input type="hidden" name="bar_codeList[]" value="{{ $set->bar_code }}">
                                </td>
                                <td>
                                    @if($isAssigned)
                                        <input type="hidden" name="designList[]" value="{{ $set->production_goods_id }}">
                                        <select class="form-control erp-input row-design-select select2" disabled style="width: 100%;">
                                            <option value="{{ $set->production_goods_id }}" selected>
                                                {{ $set->design_number }}
                                            </option>
                                        </select>
                                    @else
                                        <select name="designList[]" class="form-control erp-input row-design-select select2" required style="width: 100%;">
                                            <option value="{{ $set->production_goods_id }}" selected>
                                                {{ $set->design_number }}
                                            </option>
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    @if($isAssigned)
                                        <input type="hidden" name="sizeList[]" value="{{ $set->set_size }}">
                                        <select class="form-control erp-input row-size-select select2" disabled style="width: 100%;">
                                            <option value="{{ $set->set_size }}" data-pcs="{{ $set->no_of_pcs }}" selected>
                                                {{ $set->size_measurement->name ?? '' }}
                                            </option>
                                        </select>
                                    @else
                                        <select name="sizeList[]" class="form-control erp-input row-size-select select2" required style="width: 100%;">
                                            <option value="{{ $set->set_size }}" data-pcs="{{ $set->no_of_pcs }}" selected>
                                                {{ $set->size_measurement->name ?? '' }}
                                            </option>
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    @if($isAssigned)
                                        <input type="hidden" name="colourList[]" value="{{ $set->color_id }}">
                                        <select class="form-control erp-input row-colour-select select2" disabled style="width: 100%;">
                                            <option value="{{ $set->color_id }}" selected>{{ $set->colors->name ?? '' }}</option>
                                        </select>
                                    @else
                                        <select name="colourList[]" class="form-control erp-input row-colour-select select2" required style="width: 100%;">
                                            <option value="{{ $set->color_id }}" selected>{{ $set->colors->name ?? '' }}</option>
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    @if($isAssigned)
                                        <input type="hidden" name="product_quantity[]" value="{{ $set->set_quantity }}">
                                        <input type="number" class="form-control erp-input row-qty-input text-right font-weight-bold" value="{{ $set->set_quantity }}" disabled>
                                    @else
                                        <input type="number" name="product_quantity[]" class="form-control erp-input row-qty-input text-right font-weight-bold" value="{{ $set->set_quantity }}" min="1">
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="row-pcs-per-set">{{ $set->no_of_pcs }}</span>
                                    <input type="hidden" name="pcs[]" value="{{ $set->no_of_pcs }}">
                                </td>
                                <td class="text-right font-weight-bold text-primary">
                                    <span class="row-total-qty">{{ $set->total_quantity }}</span>
                                    <input type="hidden" name="total_quantity[]" class="row-total-qty-input" value="{{ $set->total_quantity }}">
                                </td>
                                <td class="text-center">
                                    <input type="hidden" name="order_product_set_id[]" value="{{ $set->id }}">
                                    @if($isAssigned)
                                        <button type="button" class="erp-action-btn" style="background: #e2e8f0; color: #94a3b8; cursor: not-allowed;" disabled title="Assigned, cannot remove">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @else
                                        <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove Line">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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
                <i class="fas fa-info-circle mr-1"></i> Changes will update order sets and stock reservation.
            </div>
            <div>
                <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline mr-2">
                    Cancel
                </a>
                <button type="submit" class="btn-erp btn-erp-success">
                    <i class="fas fa-save mr-1"></i> Update Order
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

        $("#corporate_order_file").on("change", function (e) {
            let file = e.target.files[0];
            if (!file) return;

            let fileURL = URL.createObjectURL(file);
            $("#fileName").text(file.name);
            $("#openFileBtn").attr("href", fileURL);
            $("#fileBox").removeClass("d-none");
        });

        $(document).on("change", ".design-input", function () {
            let img = $(this).find(":selected").data("img");
            $(this).closest(".product-row").find(".img-section").html(
                img ? `<img src="${img}" style="max-height:120px; border:1px solid #ced4da; border-radius: 4px; padding: 2px; cursor: pointer;">` : ''
            );
        });

        $(document).on("click", "img", function () {
            if ($(this).hasClass("no-preview") || $(this).hasClass("brand-image")) return;
            let fullImage = $(this).attr("src");
            if (!fullImage) return;

            $("#modal-photo").attr("src", fullImage);
            $("#photoModal").modal("show");
        });

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
                <tr class="product-row-item">
                    <td>${bar_code || '-'}
                        <input type="hidden" name="bar_codeList[]" value="${bar_code}">
                    </td>
                    <td>
                        <select name="designList[]" class="form-control erp-input row-design-select select2" required style="width: 100%;">
                            <option value="${design.val()}" selected>${design.text()}</option>
                        </select>
                    </td>
                    <td>
                        <select name="sizeList[]" class="form-control erp-input row-size-select select2" required style="width: 100%;">
                            <option value="${size_set_id}" data-pcs="${pcsPerSet}" selected>${size.text()}</option>
                        </select>
                    </td>
                    <td>
                        <select name="colourList[]" class="form-control erp-input row-colour-select select2" required style="width: 100%;">
                            <option value="${colour.val()}" selected>${colour.text()}</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="product_quantity[]" class="form-control erp-input row-qty-input text-right font-weight-bold" value="${qty}" min="1">
                    </td>
                    <td class="text-right">
                        <span class="row-pcs-per-set">${pcsPerSet}</span>
                        <input type="hidden" name="pcs[]" value="${pcsPerSet}">
                    </td>
                    <td class="text-right font-weight-bold text-primary">
                        <span class="row-total-qty">${total_qty}</span>
                        <input type="hidden" name="total_quantity[]" class="row-total-qty-input" value="${total_qty}">
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="order_product_set_id[]" value="0">
                        <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);

            let lastRow = $("#productList tbody tr:last");
            populateRowSelects(lastRow);
            lastRow.find('.select2').select2({ width: '100%' });

            row.find("select").val("").trigger("change");
            row.find(".qty-input").val("");
            $("#bar_code").val("");
            row.find(".img-section").html("");
            $("#custom_size_set_show").html("");
            $("#no_of_pcs_hidden").val("");
            $("#size_set_hidden").val("");

            calculateGrandTotal();
        });

        $(document).on("input", ".row-qty-input", function () {
            updateRowTotals($(this).closest("tr"));
        });

        $(document).on("change", ".row-size-select", function () {
            let row = $(this).closest("tr");
            let pcs = $(this).find(":selected").data("pcs") || 0;
            row.find(".row-pcs-per-set").text(pcs);
            row.find("input[name='pcs[]']").val(pcs);
            updateRowTotals(row);
        });

        function updateRowTotals(row) {
            let qty = parseFloat(row.find(".row-qty-input").val()) || 0;
            let pcsPerSet = parseFloat(row.find(".row-pcs-per-set").text()) || 0;
            let totalQty = qty * pcsPerSet;

            row.find(".row-total-qty").text(totalQty);
            row.find(".row-total-qty-input").val(totalQty);

            calculateGrandTotal();
        }

        $(document).on("click", ".remove-row", function () {
            $(this).closest("tr").remove();
            calculateGrandTotal();
        });

        $("#editOrderMainForm").on("submit", function (e) {
            let rowCount = $("#productList tbody tr").length;
            if (rowCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Products',
                    text: 'Please add at least one product before updating the order.'
                });
                return false;
            }
        });

        calculateGrandTotal();
    });

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

                    if (globalMasterData && globalMasterData.sizes) {
                        let exists = globalMasterData.sizes.find(s => s.id == response.new_size_set_id);
                        if (!exists) {
                            globalMasterData.sizes.push({
                                id: response.new_size_set_id,
                                name: response.new_size_name,
                                size_group: response.new_size_group,
                                no_of_pcs: response.new_size_pcs || response.no_of_pcs,
                                status: 2
                            });
                        } else {
                            exists.no_of_pcs = response.new_size_pcs || response.no_of_pcs;
                            exists.size_group = response.new_size_group;
                        }
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

    let globalMasterData = null;

    function loadSalesOrderMasterData() {
        $.ajax({
            url: "{{ route('admin.sales_order.master_data') }}",
            type: "GET",
            success: function (res) {
                globalMasterData = res;

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
                    sizeSelect.append(
                        `<option value="${item.id}" data-set-group="${item.size_group}" data-pcs="${item.no_of_pcs}">
                            ${item.name}
                        </option>`
                    );
                });

                let colourSelect = $('.colour-input');
                colourSelect.empty().append('<option value="">-- Select Colour --</option>');
                res.colours.forEach(item => {
                    colourSelect.append(
                        `<option value="${item.id}">${item.name}</option>`
                    );
                });

                $('.select2').trigger('change');
                populateAllRowSelects();
                $('#productList .select2').select2({ width: '100%' });
            }
        });
    }

    function populateAllRowSelects() {
        $("#productList tbody tr").each(function () {
            populateRowSelects($(this));
        });
    }

    function populateRowSelects(row) {
        if (!globalMasterData) return;
        if (row.data('assigned') === true || row.data('assigned') === 'true') return;

        let designSelect = row.find(".row-design-select");
        let currentDesign = designSelect.val();
        designSelect.empty().append('<option value="">Select Design</option>');
        globalMasterData.products.forEach(item => {
            designSelect.append(`<option value="${item.id}" ${item.id == currentDesign ? 'selected' : ''}>${item.design_number}</option>`);
        });

        let sizeSelect = row.find(".row-size-select");
        let currentSize = sizeSelect.val();
        sizeSelect.empty().append('<option value="">Select Size</option>');
        globalMasterData.sizes.forEach(item => {
            sizeSelect.append(`<option value="${item.id}" data-pcs="${item.no_of_pcs}" ${item.id == currentSize ? 'selected' : ''}>${item.name}</option>`);
        });

        let colourSelect = row.find(".row-colour-select");
        let currentColour = colourSelect.val();
        colourSelect.empty().append('<option value="">Select Colour</option>');
        globalMasterData.colours.forEach(item => {
            colourSelect.append(`<option value="${item.id}" ${item.id == currentColour ? 'selected' : ''}>${item.name}</option>`);
        });

        row.find(".select2").trigger("change");
    }
</script>
@endsection