@extends('admin.layouts.app')
@section('content')
<style>
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
        background: rgba(0, 0, 0, 0.55);
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
    .size-editor-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 10px;
        background: #f8fafc;
        border: 1px solid var(--erp-border);
        border-radius: 4px;
        margin-top: 5px;
    }
    .counter-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .counter-controls button {
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
    .counter-controls button:hover {
        background: var(--erp-green-hover);
    }
    .counter-controls span {
        font-weight: 700;
        min-width: 20px;
        text-align: center;
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-home text-primary"></i> Create Domestic Order
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <form action="{{ route('admin.sales_order.store_domestic') }}" method="POST" id="domesticOrderForm">
        @csrf

        <!-- Card 1: Select Design & Size Set -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-tshirt"></i> Add Domestic Line Item
                </span>
            </div>
            <div class="erp-card-body p-2">
                <div class="row align-items-end">
                    <div class="col-lg-4 col-md-5 col-sm-12 mb-2">
                        <label class="erp-label">Select Design (Optional)</label>
                        <select id="production_goods_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Use Default Domestic Design --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->design_number }} ({{ $prod->name_of_garment }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">Select Size Set <span class="required text-danger">*</span></label>
                        <select id="set_size_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Size Set --</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size->id }}" data-set-group="{{ $size->size_group }}" data-pcs="{{ $size->no_of_pcs }}">
                                    {{ $size->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span id="custom_ratio_info" class="small text-success font-weight-bold"></span>
                            <span class="open-ratio-pill" id="openCustomSizeBtn" style="display:none;">
                                <i class="fas fa-sliders-h mr-1"></i> Update Ratio
                            </span>
                        </div>
                        <input type="hidden" id="size_set_hidden">
                        <input type="hidden" id="no_of_pcs_hidden">
                    </div>

                    <div class="col-lg-2 col-md-3 col-sm-3 mb-2">
                        <label class="erp-label">Quantity (Sets) <span class="required text-danger">*</span></label>
                        <input type="number" id="product_quantity" class="form-control erp-input" min="1" placeholder="Enter sets">
                    </div>

                    <div class="col-lg-2 col-md-12 col-sm-3 mb-2">
                        <button type="button" id="btnAddItem" class="btn-erp btn-erp-primary w-100 justify-content-center" style="height: 31px;">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Added Items Table -->
        <div class="erp-card mb-2" id="itemsTableWrapper">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-list-check"></i> Added Domestic Items
                </span>
            </div>
            <div class="erp-card-body p-2 table-responsive">
                <table class="erp-table table table-bordered table-hover mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Design</th>
                            <th>Size Set</th>
                            <th style="width: 120px;" class="text-right">Qty (Sets)</th>
                            <th style="width: 120px;" class="text-right">Total Pcs</th>
                            <th style="width: 65px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle mr-1"></i> No items added yet. Select Design, Size Set, Quantity and click <strong>+ Add Item</strong> above.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="itemsTableFoot" style="display: none;">
                        <tr class="font-weight-bold bg-light">
                            <td colspan="3" class="text-right">Total:</td>
                            <td class="text-right text-primary font-weight-bold" id="totalSetsCount">0</td>
                            <td class="text-right text-success font-weight-bold" id="totalPcsCount">0 pcs</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <input type="hidden" name="items_json" id="items_json" value="[]">
            </div>
        </div>

        <!-- Card 3: Assign to Cutting Master (Optional) -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-cut"></i> Assign to Cutting Master (Optional)
                </span>
            </div>
            <div class="erp-card-body p-2">
                <div class="row">
                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id" class="form-control select2 erp-input" onchange="warehouseChange(this.value)" style="width: 100%;">
                            <option value="">-- Select Warehouse --</option>
                            @foreach($cutting_units as $w)
                                <option value="{{ $w['id'] }}">{{ $w['warehouse_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Cutting Master</label>
                        <select name="master_cutting_id" id="master_cutting_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Cutting Master --</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Fabric</label>
                        <select name="fabric_id[]" id="fabric_id" class="form-control select2 erp-input" multiple data-placeholder="Select Fabric(s)" style="width: 100%;">
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Season</label>
                        <select name="product_season_id" id="product_season_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Season --</option>
                            @if(isset($seasons))
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Fitting</label>
                        <select name="master_fitting_id" id="master_fitting_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Fitting --</option>
                            @foreach($fittings as $fitting)
                                <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Product Style / Pattern</label>
                        <select name="master_pattern_id" id="master_pattern_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Pattern --</option>
                            @foreach($patterns as $pattern)
                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Belt Details</label>
                        <input type="text" name="belt" class="form-control erp-input" placeholder="Enter belt details">
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">Remark</label>
                        <input type="text" name="remark" class="form-control erp-input" placeholder="Any special instructions...">
                    </div>

                    <div class="col-md-6 col-sm-6 mb-1">
                        <label class="erp-label">Printing Required?</label>
                        <select name="is_printing" id="is_printing" class="form-control erp-input" onchange="togglePrinting(this.value)">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-1" id="printing_unit_group" style="display:none;">
                        <label class="erp-label">Printing & Embroidery Unit <span class="required text-danger">*</span></label>
                        <select name="printing_unit_id" id="printing_unit_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- Select Printing Unit --</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Footer -->
        <div class="erp-card p-2 d-flex justify-content-between align-items-center">
            <div class="text-muted font-weight-bold small">
                <i class="fas fa-info-circle mr-1"></i> Check all sizes and quantities before creating order.
            </div>
            <div>
                <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline mr-2">
                    Cancel
                </a>
                <button type="submit" class="btn-erp btn-erp-success">
                    <i class="fas fa-check-circle"></i> Create & Assign Order
                </button>
            </div>
        </div>
    </form>
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
                <span id="size_name_display" class="font-weight-bold text-dark"></span>
            </div>
            <div id="sizeListContainer" class="my-2" style="max-height: 240px; overflow-y: auto;"></div>
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                <span class="text-muted font-weight-bold small">Proposed Ratio:</span>
                <strong id="proposedRatioText" class="text-primary">—</strong>
            </div>
        </div>
        <div class="modal-footer py-2 px-3 bg-light">
            <button type="button" class="btn-erp btn-erp-outline" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn-erp btn-erp-primary" onclick="saveCustomRatio()">Save Changes</button>
        </div>
    </div>
</div>

<!-- PHP DATA TO JS -->
<script>
    const warehouses = Object.values(@json($cutting_units));
    const printing_warehouses = Object.values(@json($printing_units ?? []));
</script>

<script>
    let addedItems = [];

    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });
        
        warehouseChange($('#warehouse_id').val());
        printingWarehouseChange();

        // Show/Hide Update Ratio button
        $('#set_size_id').on('change', function () {
            if ($(this).val()) {
                $('#openCustomSizeBtn').show();
            } else {
                $('#openCustomSizeBtn').hide();
            }
            $('#custom_ratio_info').text('');
            $('#size_set_hidden').val('');
            $('#no_of_pcs_hidden').val('');
        });

        // Open Modal
        $('#openCustomSizeBtn').on('click', function () {
            openModal();
        });

        // Add Item to Table
        $('#btnAddItem').on('click', function () {
            let prodSelect = $('#production_goods_id');
            let prodId = prodSelect.val();
            let prodText = prodId ? prodSelect.find(':selected').text().trim() : 'Domestic Design (Domestic Garment)';

            let sizeSelect = $('#set_size_id');
            let originalSizeId = sizeSelect.val();
            if (!originalSizeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Size Set',
                    text: 'Please select a Size Set.'
                });
                sizeSelect.select2('open');
                return;
            }

            let sizeOption = sizeSelect.find(':selected');
            let sizeName = sizeOption.text().trim();
            let pcsPerSet = parseInt($('#no_of_pcs_hidden').val()) || parseInt(sizeOption.data('pcs')) || 1;
            let actualSizeId = $('#size_set_hidden').val() || originalSizeId;
            let customRatioText = $('#custom_ratio_info').text();

            let qtyInput = $('#product_quantity');
            let qty = parseInt(qtyInput.val());
            if (!qty || qty < 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Quantity',
                    text: 'Please enter a valid Quantity (Sets).'
                });
                qtyInput.focus();
                return;
            }

            let totalPcs = qty * pcsPerSet;

            addedItems.push({
                production_goods_id: prodId,
                production_goods_name: prodText,
                set_size_id: actualSizeId,
                set_size_name: sizeName,
                custom_ratio: customRatioText,
                pcs_per_set: pcsPerSet,
                product_quantity: qty,
                total_pcs: totalPcs
            });

            renderItemsTable();
            qtyInput.val('');
        });

        // Form Submit Validation
        $('#domesticOrderForm').on('submit', function (e) {
            if (addedItems.length === 0) {
                let sizeVal = $('#set_size_id').val();
                let qtyVal = $('#product_quantity').val();
                if (sizeVal && qtyVal && parseInt(qtyVal) > 0) {
                    $('#btnAddItem').trigger('click');
                } else {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Items Added',
                        text: 'Please add at least one item using the "+ Add Item" button before creating the order.'
                    });
                    return false;
                }
            }
            return true;
        });
    });

    function renderItemsTable() {
        let tbody = $('#itemsTableBody');
        tbody.empty();

        if (addedItems.length === 0) {
            tbody.append(`
                <tr id="emptyRow">
                    <td colspan="6" class="text-center text-muted py-3">
                        <i class="fas fa-info-circle mr-1"></i> No items added yet. Select Design, Size Set, Quantity and click <strong>+ Add Item</strong> above.
                    </td>
                </tr>
            `);
            $('#itemsTableFoot').hide();
            $('#items_json').val('[]');
            return;
        }

        let totalSets = 0;
        let totalPcs = 0;

        addedItems.forEach((item, index) => {
            totalSets += item.product_quantity;
            totalPcs += item.total_pcs;

            let ratioBadge = item.custom_ratio ? `<div class="small text-success font-weight-bold">${item.custom_ratio}</div>` : '';

            tbody.append(`
                <tr>
                    <td class="text-center font-weight-bold">${index + 1}</td>
                    <td class="font-weight-bold">${item.production_goods_name}</td>
                    <td>
                        <span>${item.set_size_name}</span>
                        ${ratioBadge}
                    </td>
                    <td class="text-right font-weight-bold">${item.product_quantity}</td>
                    <td class="text-right text-primary font-weight-bold">${item.total_pcs} pcs</td>
                    <td class="text-center">
                        <button type="button" class="erp-action-btn erp-btn-delete" onclick="removeItem(${index})" title="Remove Item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $('#totalSetsCount').text(totalSets);
        $('#totalPcsCount').text(totalPcs + ' pcs');
        $('#itemsTableFoot').show();
        $('#items_json').val(JSON.stringify(addedItems));
    }

    function removeItem(index) {
        addedItems.splice(index, 1);
        renderItemsTable();
    }

    let sizeCounts = {};
    let currentSetSizeOption = null;

    function openModal() {
        let select = $('#set_size_id');
        let option = select.find(':selected');
        if (!option.val()) return;

        let setGroup = option.data('set-group') || "";
        let sizeName = option.text();

        $('#size_name_display').text(sizeName);

        sizeCounts = {};
        if (setGroup) {
            setGroup.toString().split(',').forEach(size => {
                size = size.trim();
                if (size) {
                    sizeCounts[size] = (sizeCounts[size] || 0) + 1;
                }
            });
        }

        renderSizes();
        $('#sizeModal').css('display', 'flex');
    }

    function closeModal() {
        $('#sizeModal').hide();
    }

    function changeCount(size, change) {
        sizeCounts[size] = (sizeCounts[size] || 0) + change;
        if (sizeCounts[size] < 0) sizeCounts[size] = 0;
        renderSizes();
    }

    function renderSizes() {
        let container = $('#sizeListContainer');
        container.empty();

        let allSizes = [];
        Object.keys(sizeCounts).sort((a, b) => a - b).forEach(size => {
            let count = sizeCounts[size];
            for (let i = 0; i < count; i++) allSizes.push(size);

            container.append(`
                <div class="size-editor-row">
                    <strong class="text-dark">Size: ${size}</strong>
                    <div class="counter-controls">
                        <button type="button" onclick="changeCount('${size}', -1)">−</button>
                        <span>${count}</span>
                        <button type="button" onclick="changeCount('${size}', 1)">+</button>
                    </div>
                </div>
            `);
        });

        $('#proposedRatioText').text(allSizes.join(','));
    }

    function saveCustomRatio() {
        let finalGroup = $('#proposedRatioText').text();
        if (!finalGroup || finalGroup === '—') {
            Swal.fire({ icon: 'warning', title: 'Invalid Ratio', text: 'Please specify at least one size.' });
            return;
        }

        let select = $('#set_size_id');
        let option = select.find(':selected');
        let originalGroup = option.data('set-group');

        if (finalGroup === originalGroup) {
            closeModal();
            return;
        }

        $.ajax({
            url: "{{ route('admin.sales_order.saveCustomSetSize') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                customer_id: 0,
                set_size_id: select.val(),
                set_size_name: option.text().trim(),
                finalGroup: finalGroup,
                design_id: 0
            },
            success: function (res) {
                if (res.new_size_group) {
                    $('#custom_ratio_info').text("Custom Ratio: (" + res.new_size_group + ")");
                    $('#size_set_hidden').val(res.new_size_set_id);
                    $('#no_of_pcs_hidden').val(res.no_of_pcs);
                    closeModal();
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error saving custom ratio.' });
                console.error(xhr.responseText);
            }
        });
    }

    function warehouseChange(warehouse_id) {
        let cuttingSelect = $('#master_cutting_id');
        cuttingSelect.empty().append('<option value="">-- Select Cutting Master --</option>');

        let warehouse = warehouses.find(w => w.id == warehouse_id);

        if (warehouse && warehouse.cutting_units) {
            warehouse.cutting_units.forEach(unit => {
                cuttingSelect.append(
                    `<option value="${unit.id}">${unit.name}</option>`
                );
            });
        }

        cuttingSelect.trigger('change.select2');

        let fabricSelect = $('#fabric_id');
        let prevSelected = fabricSelect.val() || [];
        
        $.ajax({
            url: "{{ route('admin.product_order.getFabricsByWarehouse') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                warehouse_id: warehouse_id
            },
            success: function (res) {
                fabricSelect.empty();
                res.forEach(fabric => {
                    let remaining = fabric.receipt_details_sum_remaining_quantity ? parseFloat(fabric.receipt_details_sum_remaining_quantity).toFixed(2) : '0.00';
                    fabricSelect.append(
                        `<option value="${fabric.id}">${fabric.name} (${remaining} meter)</option>`
                    );
                });
                if (prevSelected.length > 0) {
                    fabricSelect.val(prevSelected);
                }
                fabricSelect.trigger('change.select2');
            },
            error: function () {
                fabricSelect.empty();
                fabricSelect.trigger('change.select2');
            }
        });
    }

    function printingWarehouseChange() {
        let printingSelect = $('#printing_unit_id');
        printingSelect.empty();
        printingSelect.append('<option value="">-- Select Printing Unit --</option>');

        printing_warehouses.forEach(warehouse => {
            if (warehouse.printing_units) {
                warehouse.printing_units.forEach(unit => {
                    printingSelect.append(
                        `<option value="${unit.id}">${unit.name} (${warehouse.warehouse_name})</option>`
                    );
                });
            }
        });

        printingSelect.trigger('change.select2');
    }

    function togglePrinting(val) {
        if (val === 'yes') {
            $('#printing_unit_group').show();
            $('#printing_unit_id').prop('required', true);
        } else {
            $('#printing_unit_group').hide();
            $('#printing_unit_id').prop('required', false).val('').trigger('change');
        }
    }
</script>
@endsection