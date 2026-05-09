@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #eef2ff;
            --secondary-color: #94a3b8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        .content-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-main);
            padding-bottom: 300px;
        }

        .content-header {
            padding: 0 !important;
        }

        .content {
            padding-top: 0 !important;
        }

        .premium-page-header {
            padding: 0.75rem 0;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.025em;
            margin-bottom: 0.125rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* ITEM CARD STYLING */
        .inventory-item-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: visible;
        }

        .inventory-item-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header-premium {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fcfdfe;
            border-radius: 16px 16px 0 0;
        }

        .item-number {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body-premium {
            padding: 1.5rem;
        }

        .input-group-premium {
            margin-bottom: 0.75rem;
        }

        .label-premium {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.25rem;
        }

        .form-control-premium {
            height: 44px;
            width: 100%;
            font-size: 0.9375rem;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background-color: #fff;
            transition: all 0.2s;
            padding: 0 12px;
        }

        .form-control-premium:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        /* SELECT2 CUSTOMIZATION */
        .select2-container--bootstrap4 .select2-selection {
            height: 44px !important;
            border-radius: 10px !important;
            border: 1px solid var(--border-color) !important;
            display: flex;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 42px !important;
            font-size: 0.9375rem !important;
            padding-left: 12px !important;
        }

        .select2-dropdown {
            border-radius: 12px !important;
            box-shadow: var(--shadow-lg) !important;
            border-color: var(--border-color) !important;
            z-index: 10000 !important;
            /* Ensure it stays on top of everything including sticky footer */
            overflow: hidden;
        }

        .select2-results__options {
            max-height: 500px !important;
        }

        /* BUTTONS */
        .btn-add-item {
            background: #fff;
            color: var(--primary-color);
            border: 2px dashed var(--primary-color);
            border-radius: 12px;
            padding: 1rem;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-top: 1rem;
            cursor: pointer;
        }

        .btn-add-item:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            border-style: solid;
        }

        .btn-remove-item {
            background: #fee2e2;
            color: var(--danger-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-remove-item:hover {
            background: var(--danger-color);
            color: #fff;
        }

        .sticky-actions {
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            padding: 1.25rem 2.5rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2rem;
            z-index: 1000;
            box-shadow: 0 -8px 20px -5px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 991.98px) {
            .sticky-actions {
                left: 0;
            }
        }

        .btn-confirm {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: #fff !important;
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2.5rem;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(99, 102, 241, 0.4);
        }

        .btn-cancel {
            color: var(--text-muted);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .btn-cancel:hover {
            color: var(--text-main);
        }

        .animate-in {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <header class="premium-page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Purchase Stock Entry</h1>
                        <!-- <p class="page-subtitle">Add new stock from vendors or customers.</p> -->
                    </div>
                    <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn btn-outline-primary btn-sm shadow-sm px-3" style="border-radius: 0.5rem; font-weight: 600;">
                        <i class="fas fa-history mr-2"></i>View History
                    </a>
                </header>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('admin.inventory.store') }}" method="POST" id="addStockForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="label-premium">Source Type</label>
                            <select name="source_type" id="sourceType" class="form-control select2">
                                <option value="vendor">Vendor</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="label-premium text-primary">Load from Production PO</label>
                            <select name="production_po_id" id="loadProductionPO" class="form-control select2">
                                <option value="">Select PO (Optional)</option>
                                @foreach($productionPOs as $po)
                                    <option value="{{ $po->id }}">{{ $po->po_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" id="vendorContainer">
                            <label class="label-premium">Select Vendor</label>
                            <select name="vendor_id" id="vendorSelect" class="form-control select2">
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->company_name ?? $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3" id="customerContainer" style="display: none;">
                            <label class="label-premium">Select Customer</label>
                            <select name="customer_id" id="customerSelect" class="form-control select2">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="poReferenceContainer" class="card card-premium mb-3 animate-in" style="display: none; border-left: 4px solid #6366f1;">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-list-ul mr-2"></i>PO Items Reference</h6>
                                <span class="badge badge-primary px-3" id="poRefNumber">PO #000</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design No</th>
                                            <th>Pattern / Fitting</th>
                                            <th>Size / Color</th>
                                            <th>PO Quantity</th>
                                            <th>PO Rate</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="poRefBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Add Header Section -->
                    <div class="card shadow-sm border-0 mb-3 bg-soft-primary animate-in">
                        <div class="card-body p-3">
                            <h6 class="text-primary font-weight-bold mb-3">
                                <i class="fas fa-cart-plus mr-2"></i>Quick Add Product
                            </h6>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Warehouse *</label>
                                    <select id="headerWarehouse" class="form-control select2">
                                        <option value="">Warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Rack *</label>
                                    <select id="headerRack" class="form-control select2">
                                        <option value="">Rack</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="label-premium text-primary">Design No *</label>
                                    <select id="headerDesign" class="form-control select2">
                                        <option value="">Select Design</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">
                                                {{ $product->design_number }} ({{ $product->series->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Pattern</label>
                                    <input type="text" id="headerPatternDisplay"
                                        class="form-control form-control-premium bg-light" placeholder="Auto" readonly>
                                    <input type="hidden" id="headerPattern">
                                </div>
                                <div class="col-md-2">
                                    <label class="label-premium text-primary">Fitting</label>
                                    <input type="text" id="headerFittingDisplay"
                                        class="form-control form-control-premium bg-light" placeholder="Auto" readonly>
                                    <input type="hidden" id="headerFitting">
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label class="label-premium text-primary">Size Set *</label>
                                    <select id="headerSizeSet" class="form-control select2">
                                        <option value="">Size Set</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label class="label-premium text-primary">Color *</label>
                                    <select id="headerColor" class="form-control select2">
                                        <option value="">Color</option>
                                    </select>
                                </div>
                                <div class="col-md-1 mt-2">
                                    <label class="label-premium text-primary">Pcs/Box</label>
                                    <input type="number" id="headerPcsPerBox"
                                        class="form-control form-control-premium bg-light" readonly>
                                </div>
                                <div class="col-md-1 mt-2">
                                    <label class="label-premium text-primary">MRP</label>
                                    <input type="number" id="headerMRP" class="form-control form-control-premium bg-light"
                                        readonly>
                                </div>
                                <div class="col-md-1 mt-2">
                                    <label class="label-premium text-primary">Boxes *</label>
                                    <input type="number" id="headerTotalBoxes" class="form-control form-control-premium"
                                        placeholder="Qty" min="1">
                                </div>
                                <div class="col-md-1 mt-2">
                                    <label class="label-premium text-primary">Rate *</label>
                                    <input type="number" id="headerPurchaseRate" class="form-control form-control-premium"
                                        placeholder="Rate" step="0.01" min="0">
                                </div>
                                <div class="col-md-2 mt-3">
                                    <button type="button" id="btnAddToList"
                                        class="btn btn-primary btn-block font-weight-bold"
                                        style="height: 48px; border-radius: 12px;">
                                        <i class="fas fa-plus mr-2"></i>Add Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product List Table -->
                    <div class="card shadow-sm border-0 mb-4 animate-in">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="purchaseTable">
                                    <thead class="bg-light text-primary">
                                        <tr>
                                            <th class="pl-4">Design</th>
                                            <th>Pattern/Fitting</th>
                                            <th>Warehouse/Rack</th>
                                            <th>Size/Color</th>
                                            <th>Boxes</th>
                                            <th>Pcs/Box</th>
                                            <th>MRP</th>
                                            <th>Rate</th>
                                            <th>Total</th>
                                            <th class="text-right pr-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsContainer">
                                        <!-- Table Rows Will Be Appended Here -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="emptyState" class="p-5 text-center text-muted">
                                <i class="fas fa-shopping-basket fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No products added yet. Use the header above to add products.</p>
                            </div>
                        </div>
                    </div>

                    <div id="purchaseSummaryContainer" class="card mt-4 shadow-sm border-0" style="display: none;">
                        <div class="card-header bg-soft-primary py-3">
                            <h5 class="mb-0 text-primary font-weight-bold"><i
                                    class="fas fa-file-invoice-dollar mr-2"></i>Purchase Summary (Complete)</h5>
                        </div>
                        <div class="card-body bg-light-gray">
                            <div class="row">
                                <div class="col-md-3 input-group-premium">
                                    <label class="label-premium">Sub Total</label>
                                    <input type="number" name="sub_total" id="global_sub_total"
                                        class="form-control form-control-premium bg-light" placeholder="0.00" step="0.01"
                                        readonly>
                                </div>
                                <div class="col-md-3 input-group-premium">
                                    <label class="label-premium">GST</label>
                                    <div class="input-group">
                                        <input type="number" name="gst_value" id="global_gst_value"
                                            class="form-control form-control-premium" placeholder="Value" step="0.01"
                                            min="0">
                                        <div class="input-group-append">
                                            <select name="gst_type" id="global_gst_type"
                                                class="custom-select form-control-premium bg-light"
                                                style="width: auto; border-radius: 0 8px 8px 0;">
                                                <option value="percentage">%</option>
                                                <option value="amount">₹</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" name="gst" id="global_gst_amount">
                                </div>
                                <div class="col-md-2 input-group-premium">
                                    <label class="label-premium">Other Amount</label>
                                    <input type="number" name="other_amount" id="global_other_amount"
                                        class="form-control form-control-premium" placeholder="Other" step="0.01" min="0">
                                </div>
                                <div class="col-md-2 input-group-premium">
                                    <label class="label-premium">Discount</label>
                                    <input type="number" name="discount" id="global_discount"
                                        class="form-control form-control-premium" placeholder="Disc" step="0.01" min="0">
                                </div>
                                <div class="col-md-3 input-group-premium">
                                    <label class="label-premium text-primary font-weight-bold">Grand Total Amount</label>
                                    <input type="number" name="total_amount" id="global_total_amount"
                                        class="form-control form-control-premium bg-light" placeholder="0.00" step="0.01"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="sticky-actions">
                        <a href="{{ route('admin.inventory.index') }}" class="btn-cancel">Cancel and Exit</a>
                        <button type="submit" class="btn btn-confirm">
                            <i class="fas fa-check-double mr-2"></i> Confirm and Upload Stock
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @section('scripts')
        <script>
            $(function () {
                let itemCount = 0;
                let currentVariants = [];

                function initSelect2(container) {
                    container.find('.select2').each(function () {
                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownAutoWidth: true,
                            dropdownParent: $('body')
                        });
                    });
                }

                initSelect2($('.content-wrapper'));

                // Handle Source Type
                $('#sourceType').on('change', function () {
                    let type = $(this).val();
                    $('#purchaseSummaryContainer').show();
                    if (type === 'vendor') {
                        $('#vendorContainer').show();
                        $('#customerContainer').hide();
                    } else if (type === 'customer') {
                        $('#vendorContainer').hide();
                        $('#customerContainer').show();
                    }
                }).trigger('change');

                // Header Warehouse -> Load Racks
                $('#headerWarehouse').on('change', function () {
                    let warehouseId = $(this).val();
                    let rackSelect = $('#headerRack');
                    rackSelect.empty().append('<option value="">Rack</option>');
                    if (warehouseId) {
                        $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                            data.forEach(function (rack) {
                                rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                            });
                            rackSelect.trigger('change.select2');
                            if (data.length > 0) rackSelect.val(data[0].id).trigger('change.select2');
                        });
                    }
                });

                // Header Design -> Load Pattern/Fitting/Variants
                $('#headerDesign').on('change', function () {
                    let productId = $(this).val();
                    let sizeSelect = $('#headerSizeSet');
                    let colorSelect = $('#headerColor');

                    // Reset fields
                    $('#headerPatternDisplay').val('');
                    $('#headerPattern').val('');
                    $('#headerFittingDisplay').val('');
                    $('#headerFitting').val('');
                    sizeSelect.empty().append('<option value="">Size Set</option>').trigger('change.select2');
                    colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                    $('#headerPcsPerBox').val('');
                    $('#headerMRP').val('');
                    currentVariants = [];

                    if (productId) {
                        $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                            if (data.success) {
                                $('#headerPatternDisplay').val(data.pattern_name);
                                $('#headerPattern').val(data.pattern_id);
                                $('#headerFittingDisplay').val(data.fitting_name);
                                $('#headerFitting').val(data.fitting_id);
                                currentVariants = data.variants;

                                data.variants.forEach(function (v) {
                                    sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                });
                                sizeSelect.trigger('change.select2');
                            }
                        });
                    }
                });

                // Header Size Set -> Load Colors, Pcs/Box, MRP
                $('#headerSizeSet').on('change', function () {
                    let sizeSetId = $(this).val();
                    let colorSelect = $('#headerColor');
                    colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                    $('#headerPcsPerBox').val('');
                    $('#headerMRP').val('');

                    if (sizeSetId) {
                        $.get("{{ url('admin/inventory/get-size-set-info') }}/" + sizeSetId, function (data) {
                            $('#headerPcsPerBox').val(data.no_of_pcs);
                        });

                        let variant = currentVariants.find(v => v.size_set_id == sizeSetId);
                        if (variant) {
                            $('#headerMRP').val(variant.mrp);
                            variant.colors.forEach(function (c) {
                                colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            colorSelect.trigger('change.select2');

                            // Auto-fill Quantity and Rate from PO if unique match found
                            if (currentPOItems.length > 0) {
                                const productId = $('#headerDesign').val();
                                const poMatch = currentPOItems.find(item => 
                                    item.product_id == productId && 
                                    item.size_set_id == sizeSetId
                                );
                                if (poMatch) {
                                    $('#headerTotalBoxes').val(poMatch.total_boxes);
                                    $('#headerPurchaseRate').val(poMatch.purchase_rate);
                                    
                                    // If only one color in PO for this design+size, select it
                                    const colorsInPOForThisSize = currentPOItems.filter(item => 
                                        item.product_id == productId && item.size_set_id == sizeSetId
                                    );
                                    if (colorsInPOForThisSize.length === 1) {
                                        colorSelect.val(colorsInPOForThisSize[0].color_id).trigger('change.select2');
                                    }
                                }
                            }
                        }
                    }
                });

                // Add To List Button
                $('#btnAddToList').on('click', function () {
                    const data = {
                        warehouse_id: $('#headerWarehouse').val(),
                        warehouse_name: $('#headerWarehouse option:selected').text(),
                        rack_id: $('#headerRack').val(),
                        rack_name: $('#headerRack option:selected').text(),
                        product_id: $('#headerDesign').val(),
                        product_name: $('#headerDesign option:selected').text(),
                        pattern_id: $('#headerPattern').val(),
                        pattern_name: $('#headerPatternDisplay').val(),
                        fitting_id: $('#headerFitting').val(),
                        fitting_name: $('#headerFittingDisplay').val(),
                        size_set_id: $('#headerSizeSet').val(),
                        size_set_name: $('#headerSizeSet option:selected').text(),
                        color_id: $('#headerColor').val(),
                        color_name: $('#headerColor option:selected').text(),
                        pieces_per_box: $('#headerPcsPerBox').val(),
                        mrp: $('#headerMRP').val(),
                        total_boxes: $('#headerTotalBoxes').val(),
                        purchase_rate: $('#headerPurchaseRate').val()
                    };

                    // Validation
                    if (!data.warehouse_id || !data.product_id || !data.size_set_id || !data.color_id || !data.total_boxes || !data.purchase_rate) {
                        toastr.warning('Please fill all required fields marked with *');
                        return;
                    }

                    addToTable(data);
                    toastr.success('Product added to list');

                    // Reset only specific fields for faster entry
                    $('#headerTotalBoxes').val('');
                    $('#headerPurchaseRate').val('');
                    $('#headerSizeSet').val('').trigger('change.select2');
                    $('#headerColor').val('').trigger('change.select2');
                });

                const originalDesignHtml = $('#headerDesign').html();
                let currentPOItems = [];

                // Load from Production PO
                $('#loadProductionPO').on('change', function() {
                    const poId = $(this).val();
                    
                    if (!poId) {
                        $('#poReferenceContainer').hide();
                        currentPOItems = [];
                        return;
                    }

                    Swal.fire({
                        title: 'Loading PO Data...',
                        text: 'Filtering products based on Production PO.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `{{ url('admin/inventory/get-po-items') }}/${poId}`,
                        type: 'GET',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                currentPOItems = response.items;

                                if (response.vendor_id) {
                                    $('#sourceType').val('vendor').trigger('change');
                                    $('#vendorSelect').val(response.vendor_id).trigger('change');
                                } else if (response.customer_id) {
                                    $('#sourceType').val('customer').trigger('change');
                                    $('#customerSelect').val(response.customer_id).trigger('change');
                                }

                                // Filter Design Dropdown
                                let filteredHtml = '<option value="">Select Design (PO Filtered)</option>';
                                let refHtml = '';
                                const addedProductIds = new Set();
                                
                                response.items.forEach(item => {
                                    if (!addedProductIds.has(item.product_id)) {
                                        filteredHtml += `<option value="${item.product_id}" data-name="${item.product_name}">${item.design_number}</option>`;
                                        addedProductIds.add(item.product_id);
                                    }

                                    refHtml += `
                                        <tr>
                                            <td class="font-weight-bold">${item.design_number}</td>
                                            <td>${item.pattern_name} / ${item.fitting_name}</td>
                                            <td><span class="badge badge-light">${item.size_set_name}</span> / ${item.color_name}</td>
                                            <td>${item.total_boxes} Boxes</td>
                                            <td>₹${item.purchase_rate}</td>
                                            <td class="text-right">
                                                <button type="button" class="btn btn-primary btn-xs px-2 py-0 btn-apply-po-item" 
                                                    data-product="${item.product_id}" 
                                                    data-size="${item.size_set_id}" 
                                                    data-color="${item.color_id}"
                                                    data-boxes="${item.total_boxes}"
                                                    data-rate="${item.purchase_rate}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                                
                                $('#poRefNumber').text($('#loadProductionPO option:selected').text());
                                $('#poRefBody').html(refHtml);
                                $('#poReferenceContainer').fadeIn();

                                toastr.success(`PO loaded: ${response.items.length} items for reference`);
                            }
                        },
                        error: function() {
                            Swal.close();
                            toastr.error('Failed to load PO data');
                        }
                    });
                });

                // Apply PO item to form
                $(document).on('click', '.btn-apply-po-item', function() {
                    const productId = $(this).data('product');
                    const sizeSetId = $(this).data('size');
                    const colorId = $(this).data('color');
                    const boxes = $(this).data('boxes');
                    const rate = $(this).data('rate');

                    $('#headerDesign').val(productId).trigger('change');
                    
                    // Wait for Size Set dropdown to populate
                    setTimeout(() => {
                        $('#headerSizeSet').val(sizeSetId).trigger('change');
                        setTimeout(() => {
                            $('#headerColor').val(colorId).trigger('change.select2');
                            $('#headerTotalBoxes').val(boxes);
                            $('#headerPurchaseRate').val(rate);
                            toastr.info('PO item details applied to form');
                        }, 500);
                    }, 800);
                });

                function addToTable(data) {
                    // Check for duplicates in UI
                    let exists = false;
                    $('#itemsContainer tr').each(function() {
                        const rowProductId = $(this).find('input[name*="[product_id]"]').val();
                        const rowColorId = $(this).find('input[name*="[color_id]"]').val();
                        const rowSizeSetId = $(this).find('input[name*="[size_set_id]"]').val();
                        const rowPatternId = $(this).find('input[name*="[pattern_id]"]').val();
                        const rowFittingId = $(this).find('input[name*="[fitting_id]"]').val();
                        const rowRackId = $(this).find('input[name*="[rack_id]"]').val();

                        if (rowProductId == data.product_id && 
                            rowColorId == data.color_id && 
                            rowSizeSetId == data.size_set_id &&
                            rowPatternId == data.pattern_id &&
                            rowFittingId == data.fitting_id &&
                            rowRackId == (data.rack_id || '')) {
                            exists = true;
                        }
                    });

                    if (exists) {
                        toastr.warning('Product already in list');
                        return;
                    }

                    const idx = itemCount++;
                    const total = (parseFloat(data.total_boxes) * parseFloat(data.pieces_per_box) * parseFloat(data.purchase_rate)).toFixed(2);

                    const rowHtml = `
                                        <tr class="animate-in" data-index="${idx}">
                                            <td class="pl-4">
                                                <span class="font-weight-bold text-dark">${data.product_name}</span>
                                                <div class="small text-muted">#${data.design_number || ''}</div>
                                                <input type="hidden" name="products[${idx}][product_id]" value="${data.product_id}">
                                            </td>
                                            <td>
                                                <div class="small text-muted">${data.pattern_name} / ${data.fitting_name}</div>
                                                <input type="hidden" name="products[${idx}][pattern_id]" value="${data.pattern_id}">
                                                <input type="hidden" name="products[${idx}][fitting_id]" value="${data.fitting_id}">
                                            </td>
                                            <td>
                                                <div class="small text-muted">${data.warehouse_name || 'N/A'} / ${data.rack_name || 'N/A'}</div>
                                                <input type="hidden" name="products[${idx}][warehouse_id]" value="${data.warehouse_id || ''}">
                                                <input type="hidden" name="products[${idx}][rack_id]" value="${data.rack_id || ''}">
                                            </td>
                                            <td>
                                                <div class="badge badge-soft-info">${data.size_set_name}</div>
                                                <div class="small text-muted">${data.color_name}</div>
                                                <input type="hidden" name="products[${idx}][size_set_id]" value="${data.size_set_id}">
                                                <input type="hidden" name="products[${idx}][color_id]" value="${data.color_id}">
                                            </td>
                                            <td>
                                                <input type="number" name="products[${idx}][total_boxes]" value="${data.total_boxes}" class="form-control form-control-sm row-boxes" style="width: 70px;">
                                            </td>
                                            <td>
                                                <input type="number" name="products[${idx}][pieces_per_box]" value="${data.pieces_per_box}" class="form-control form-control-sm row-pcs" style="width: 70px;">
                                            </td>
                                            <td>
                                                <span>₹${data.mrp}</span>
                                                <input type="hidden" name="products[${idx}][mrp]" value="${data.mrp}">
                                            </td>
                                            <td>
                                                <input type="number" name="products[${idx}][purchase_rate]" value="${data.purchase_rate}" class="form-control form-control-sm row-rate" step="0.01" style="width: 90px;">
                                            </td>
                                            <td>
                                                <span class="text-primary font-weight-bold row-total">₹${total}</span>
                                            </td>
                                            <td class="text-right pr-4">
                                                <button type="button" class="btn btn-soft-danger btn-sm btn-remove-row">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;

                    $('#itemsContainer').append(rowHtml);
                    $('#emptyState').hide();
                    calculateGlobalTotal();
                }

                // Also allow editing quantity and rate in the list
                $(document).on('input', '.row-boxes, .row-pcs, .row-rate', function() {
                    const row = $(this).closest('tr');
                    const boxes = parseFloat(row.find('.row-boxes').val()) || 0;
                    const pcs = parseFloat(row.find('.row-pcs').val()) || 0;
                    const rate = parseFloat(row.find('.row-rate').val()) || 0;
                    const total = (boxes * pcs * rate).toFixed(2);
                    row.find('.row-total').text(`₹${total}`);
                    calculateGlobalTotal();
                });

                // Remove Row
                $(document).on('click', '.btn-remove-row', function () {
                    $(this).closest('tr').fadeOut(300, function () {
                        $(this).remove();
                        if ($('#itemsContainer tr').length === 0) {
                            $('#emptyState').show();
                        }
                        calculateGlobalTotal();
                    });
                });

                $(document).on('input', '#global_gst_value, #global_other_amount, #global_discount', function () {
                    calculateGlobalTotal();
                });

                $(document).on('change', '#global_gst_type', function () {
                    calculateGlobalTotal();
                });

                function calculateGlobalTotal() {
                    let subTotal = 0;

                    $('#itemsContainer tr').each(function () {
                        let boxes = parseFloat($(this).find('.row-boxes').val()) || 0;
                        let pcs = parseFloat($(this).find('.row-pcs').val()) || 0;
                        let rate = parseFloat($(this).find('.row-rate').val()) || 0;
                        subTotal += (boxes * pcs * rate);
                    });

                    $('#global_sub_total').val(subTotal.toFixed(2));

                    let gstValue = parseFloat($('#global_gst_value').val()) || 0;
                    let gstType = $('#global_gst_type').val();
                    let other = parseFloat($('#global_other_amount').val()) || 0;
                    let discount = parseFloat($('#global_discount').val()) || 0;

                    let gstAmount = 0;
                    if (gstType === 'percentage') {
                        gstAmount = (subTotal * gstValue) / 100;
                    } else {
                        gstAmount = gstValue;
                    }

                    $('#global_gst_amount').val(gstAmount.toFixed(2));
                    let grandTotal = subTotal + gstAmount + other - discount;

                    $('#global_total_amount').val(grandTotal.toFixed(2));
                }

                $('#addStockForm').on('submit', function (e) {
                    if ($('#itemsContainer tr').length === 0) {
                        e.preventDefault();
                        toastr.error('Please add at least one product to the list.');
                        return;
                    }

                    e.preventDefault();
                    let form = $(this);
                    let btn = form.find('button[type="submit"]');
                    let originalHtml = btn.html();

                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);

                                // Auto-trigger barcode generation
                                let pdfForm = $('<form>', {
                                    action: "{{ route('admin.inventory.barcode-generator.generate-bulk-tspl') }}",
                                    method: 'POST',
                                    target: '_blank'
                                }).append($('<input>', {
                                    type: 'hidden',
                                    name: '_token',
                                    value: "{{ csrf_token() }}"
                                }));

                                response.ids.forEach(id => {
                                    pdfForm.append($('<input>', {
                                        type: 'hidden',
                                        name: 'ids[]',
                                        value: id
                                    }));
                                });

                                $('body').append(pdfForm);
                                pdfForm.submit();
                                pdfForm.remove();

                                setTimeout(() => {
                                    window.location.href = "{{ route('admin.inventory.index') }}";
                                }, 1000);
                            }
                        },
                        error: function (xhr) {
                            btn.prop('disabled', false).html(originalHtml);
                            let error = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding stock';
                            toastr.error(error);
                        }
                    });
                });

                // Auto-select first warehouse
                (function () {
                    let w = $('#headerWarehouse');
                    if (w.find('option').length > 1) {
                        w.val(w.find('option:eq(1)').val()).trigger('change');
                    }
                })();
            });
        </script>
    @endsection
@endsection