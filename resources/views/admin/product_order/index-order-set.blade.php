@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Sales Order Sets
            <span class="badge erp-badge-yellow ml-2" style="font-size: 11px; vertical-align: middle;">{{ $order_main->sku }}</span>
        </div>
        <div class="erp-header-actions">
            <button type="button" class="btn-erp btn-erp-yellow" id="bulkPoBtn" title="Bulk PO for selected sets">
                <i class="fas fa-file-invoice"></i> Bulk PO
            </button>
            <button type="button" class="btn-erp btn-erp-danger" id="bulkCmpoPdfBtn" title="Download combined PDF for selected sets">
                <i class="fas fa-file-pdf"></i> Combined PDF
            </button>
            <button type="button" class="btn-erp btn-erp-primary" id="bulkAssignBtn" title="Assign selected sets to Cutting Master">
                <i class="fas fa-check-double"></i> Assign Selected
            </button>
            <a href="{{ route('admin.product_order.bulkPO', ['order_id' => $order_main_id]) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-plus"></i> Create PO
            </a>
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <div class="row align-items-end">
            <input type="hidden" id="id" value="{{ $order_main->id }}">
            <div class="col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Bar Code</label>
                <input type="text" class="form-control erp-input" id="bar_code" placeholder="Filter Bar Code..." autocomplete="off">
            </div>
            <div class="col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tshirt mr-1"></i> Design Number</label>
                <input type="text" class="form-control erp-input" id="design_number" placeholder="Filter Design No..." autocomplete="off">
            </div>
            <div class="col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tasks mr-1"></i> Assignment Filter</label>
                <select class="form-control erp-input" id="assigned_filter">
                    <option value="">-- ALL SETS --</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                </select>
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetOrderSetFilter" title="Reset Filters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 35px;" class="text-center">
                            <input type="checkbox" id="select_all">
                        </th>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 120px;">Bar Code</th>
                        <th style="width: 130px;">Design No</th>
                        <th>Set Size</th>
                        <th>Size Group</th>
                        <th style="width: 100px;">Color</th>
                        <th style="width: 80px;" class="text-right">Set Qty</th>
                        <th style="width: 80px;" class="text-right">Pcs / Set</th>
                        <th style="width: 90px;" class="text-right font-weight-bold">Total Qty</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 130px;" class="text-center">Assign To</th>
                        <th style="width: 80px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <th colspan="7" class="text-right">Total:</th>
                        <th id="set_qty_total" class="text-right"></th>
                        <th class="text-right">Total Qty:</th>
                        <th id="total_qty_total" class="text-right text-primary font-weight-bold"></th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ASSIGN MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <form id="assignForm">
                @csrf
                <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
                    <h5 class="modal-title font-weight-bold" style="font-size: var(--erp-font-md);">
                        <i class="fas fa-cut mr-1"></i> Assign to Cutting Master
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.9;">&times;</button>
                </div>

                <div class="modal-body p-3">
                    <!-- ORDER INFO -->
                    <div class="border rounded p-2 mb-3 bg-light" style="font-size: var(--erp-font-sm);">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Design No:</span>
                                <span id="modal_design_number" class="font-weight-bold text-dark"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Set Size:</span>
                                <span id="modal_set_size" class="font-weight-bold text-dark"></span>
                            </div>
                            <div class="col-md-12 mb-1">
                                <span class="text-muted font-weight-bold">Set Size Group:</span>
                                <span id="modal_set_size_group" class="text-dark"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Color:</span>
                                <span id="modal_color" class="font-weight-bold text-dark"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Total Qty:</span>
                                <span id="modal_total_qty" class="font-weight-bold text-primary"></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="modal_order_set_id" name="order_product_set_id">
                    <input type="hidden" id="modal_order_set_ids" name="order_product_set_ids">

                    <!-- WAREHOUSE -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Warehouse <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-control select2 erp-input" onchange="warehouseChange(this.value)" required style="width: 100%;">
                            @foreach($cutting_units as $w)
                                <option value="{{ $w['id'] }}">{{ $w['warehouse_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- CUTTING MASTER -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Cutting Master <span class="text-danger">*</span></label>
                        <select id="master_cutting_id" name="master_cutting_id" class="form-control select2 erp-input" required style="width: 100%;">
                        </select>
                    </div>

                    <!-- FABRIC -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Fabric <span class="text-danger">*</span></label>
                        <select id="fabric_id" name="fabric_id[]" class="form-control select2 erp-input" multiple required style="width: 100%;">
                            <!-- Fabrics populated via AJAX -->
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- FITTING -->
                            <div class="erp-form-group">
                                <label class="erp-filter-label">Fitting <span class="text-danger">*</span></label>
                                <select name="master_fitting_id" class="form-control select2 erp-input" required style="width: 100%;">
                                    @foreach($fittings as $fitting)
                                        <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- DESIGN PATTERN -->
                            <div class="erp-form-group">
                                <label class="erp-filter-label">Design Pattern <span class="text-danger">*</span></label>
                                <select name="master_pattern_id" class="form-control select2 erp-input" required style="width: 100%;">
                                    @foreach($patterns as $pattern)
                                        <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- SEASON -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Season</label>
                        <select name="product_season_id" id="modal_product_season_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">Select Season</option>
                            @if(isset($seasons))
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}">{{ $season->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- ASSIGN QTY -->
                    <div class="erp-form-group" id="assign_qty_group">
                        <label class="erp-filter-label">Total Pieces to Assign <span class="text-danger">*</span></label>
                        <input type="number" id="assign_quantity" name="assign_quantity" class="form-control erp-input" placeholder="Enter quantity">
                        <small class="text-muted">Current remaining pieces: <span id="current_remain_qty" class="font-weight-bold text-dark"></span></small>
                    </div>

                    <!-- BELT -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Belt</label>
                        <input type="text" name="belt" class="form-control erp-input" placeholder="Enter belt details">
                    </div>

                    <!-- REMARK -->
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Remark</label>
                        <textarea name="remark" class="form-control erp-input" rows="2" placeholder="Any remarks..."></textarea>
                    </div>

                    <hr class="my-2">
                    <h6 class="font-weight-bold mb-2" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">Printing Preferences</h6>
                    <div class="erp-form-group">
                        <label class="erp-filter-label">Printing Required?</label>
                        <select name="is_printing" id="is_printing" class="form-control erp-input" onchange="togglePrinting(this.value)">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="erp-form-group" id="printing_unit_group" style="display:none;">
                        <label class="erp-filter-label">Printing & Embroidery Unit <span class="text-danger">*</span></label>
                        <select name="printing_unit_id" id="printing_unit_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">Select Printing Unit</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-erp btn-erp-primary">Confirm Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PO MODAL -->
<div class="modal fade" id="poModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <form id="poForm">
                @csrf
                <input type="hidden" id="po_order_set_id" name="order_product_set_id">
                <input type="hidden" id="po_order_set_ids" name="order_product_set_ids">

                <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
                    <h5 class="modal-title font-weight-bold" style="font-size: var(--erp-font-md);">
                        <i class="fas fa-file-invoice mr-1"></i> Create Production PO
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 0.9;">&times;</button>
                </div>

                <div class="modal-body p-3">
                    <div class="border rounded p-2 mb-3 bg-light" style="font-size: var(--erp-font-sm);">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Design No:</span>
                                <span id="po_modal_design_number" class="font-weight-bold text-dark"></span>
                            </div>
                            <div class="col-md-6 mb-1">
                                <span class="text-muted font-weight-bold">Color:</span>
                                <span id="po_modal_color" class="font-weight-bold text-dark"></span>
                            </div>
                            <div class="col-md-12 mt-1">
                                <span class="text-muted font-weight-bold">Total Qty:</span>
                                <span id="po_modal_total_qty" class="font-weight-bold text-primary"></span>
                            </div>
                        </div>
                    </div>

                    <div class="erp-form-group">
                        <label class="erp-filter-label">PO To</label>
                        <select name="po_type" id="po_type" class="form-control erp-input" onchange="togglePoTo(this.value)">
                            <option value="vendor">Vendor</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>

                    <div class="erp-form-group" id="vendor_group">
                        <label class="erp-filter-label">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-control select2 erp-input" style="width: 100%;">
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="erp-form-group" id="customer_group" style="display:none;">
                        <label class="erp-filter-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2 erp-input" style="width: 100%;">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="erp-form-group">
                        <label class="erp-filter-label">Delivery Date <span class="text-danger">*</span></label>
                        <input type="date" name="delivery_date" class="form-control erp-input" required>
                    </div>

                    <div class="erp-form-group">
                        <label class="erp-filter-label">Rate per Piece (Rs.)</label>
                        <input type="number" name="rate" class="form-control erp-input" placeholder="0.00" step="0.01">
                    </div>

                    <div class="erp-form-group">
                        <label class="erp-filter-label">Remark</label>
                        <textarea name="remark" class="form-control erp-input" rows="2" placeholder="Remark details..."></textarea>
                    </div>
                </div>

                <div class="modal-footer py-2 px-3 bg-light">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-erp btn-erp-primary">Create PO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PHP DATA TO JS -->
<script>
    const warehouses = Object.values(@json($cutting_units));
    const printing_warehouses = Object.values(@json($printing_units));
</script>

<!-- SCRIPTS -->
<script>
function togglePoTo(val) {
    if (val === 'vendor') {
        $('#vendor_group').show();
        $('#customer_group').hide();
        $('#customer_group select').val('').trigger('change');
    } else {
        $('#vendor_group').hide();
        $('#customer_group').show();
        $('#vendor_group select').val('').trigger('change');
        $('#customer_group select').select2({ width: '100%' });
    }
}

$(document).ready(function () {
    // Load default warehouse cutting masters
    warehouseChange($('#warehouse_id').val());
    printingWarehouseChange();

    $(document).on('click', '.po-btn', function() {
        const id = $(this).data('id');
        const design = $(this).data('design');
        const color = $(this).data('color');
        const total = $(this).data('total');

        $('#po_order_set_id').val(id);
        $('#po_order_set_ids').val('');
        
        $('#po_type').val('vendor').trigger('change');
        togglePoTo('vendor');
        
        $('#po_modal_design_number').text(design);
        $('#po_modal_color').text(color);
        $('#po_modal_total_qty').text(total);

        $('#poModal').modal('show');
    });

    $('#poForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Creating PO...');

        $.ajax({
            url: "{{ route('admin.product_order.createPO') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    $('#poModal').modal('hide');
                    $('#poForm')[0].reset();
                    table.ajax.reload();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('Something went wrong!');
            },
            complete: function() {
                btn.prop('disabled', false).text('Create PO');
            }
        });
    });

    // Bulk PO button
    $('#bulkPoBtn').on('click', function () {
        const selectedIds = $('.row-select:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: 'Please select at least one set.'
            });
            return;
        }

        const idsStr = selectedIds.join(',');
        window.location.href = "{{ route('admin.product_order.bulkPO') }}?set_ids=" + idsStr;
    });

    // Bulk CMPO PDF button
    $('#bulkCmpoPdfBtn').on('click', function () {
        const selectedIds = $('.row-select:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: 'Please select at least one set.'
            });
            return;
        }

        const idsStr = selectedIds.join(',');
        window.open("{{ route('admin.product_order.bulkCmpoDownload') }}?set_ids=" + idsStr, '_blank');
    });

    $(document).on('click', '.assign-btn', function () {
        $('#modal_order_set_id').val($(this).data('id'));
        $('#modal_order_set_ids').val('');
        $('#modal_design_number').text($(this).data('design'));
        $('#modal_set_size').text($(this).data('set-size'));
        $('#modal_set_size_group').text($(this).data('set-size-group'));
        $('#modal_color').text($(this).data('color'));
        
        const total = $(this).data('total');
        const remain = $(this).data('remain');
        const seasonId = $(this).data('season-id');
        
        $('#modal_total_qty').text(total);
        $('#current_remain_qty').text(remain);
        $('#assign_quantity').val(remain);
        $('#assign_qty_group').show();

        if (seasonId) {
            $('#modal_product_season_id').val(seasonId).trigger('change');
        } else {
            $('#modal_product_season_id').val('').trigger('change');
        }

        $('#assignModal').modal('show');
    });

    // DataTable
    var table = $('#customers').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        paging: false,
        info: true,
        lengthChange: false,
        ajax: {
            url: "{{ route('admin.product_order.indexListOrderSet') }}",
            data: function (d) {
                d.id = $('#id').val();
                d.bar_code = $('#bar_code').val();
                d.design_number = $('#design_number').val();
                d.assigned_filter = $('#assigned_filter').val();
                d.start = 0;
                d.length = -1;
            }
        },
        columns: [
            {data: 'select', orderable: false, searchable: false, className: 'text-center'},
            {data: 'DT_RowIndex', className: 'text-center'},
            {data: 'bar_code'},
            {data: 'design_number', className: 'font-weight-bold'},
            {data: 'set_size'},
            {data: 'size_group'},
            {data: 'color_id'},
            {data: 'set_quantity', className: 'text-right'},
            {data: 'no_of_pcs', className: 'text-right'},
            {data: 'total_qty', className: 'text-right font-weight-bold'},
            {data: 'status', className: 'text-center'},
            {data: 'assign_to', className: 'text-center'},
            {data: 'action', searchable: false, className: 'text-center'}
        ],
        footerCallback: function (row, data) {
            let api = this.api();
            let sum = col => api.column(col).data().reduce((a,b)=>+a + +b,0);
            $('#set_qty_total').html(sum(7));
            $('#total_qty_total').html(sum(9));
        }
    });

    // Debounced reload
    let reloadTimer = null;
    function reloadTable() {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(function () {
            table.ajax.reload(null, false);
        }, 250);
    }

    $('#bar_code, #design_number').on('keyup', reloadTable);
    $('#assigned_filter').on('change', reloadTable);

    $('#btnResetOrderSetFilter').on('click', function () {
        $('#bar_code').val('');
        $('#design_number').val('');
        $('#assigned_filter').val('');
        reloadTable();
    });

    // Select all checkbox
    $('#select_all').on('change', function () {
        const checked = $(this).is(':checked');
        $('.row-select').prop('checked', checked);
    });

    // Bulk assign button
    $('#bulkAssignBtn').on('click', function () {
        let hasAssigned = false;
        
        const selectedIds = $('.row-select:checked').map(function () {
            if ($(this).data('assigned') === true || $(this).data('assigned') === 'true') {
                hasAssigned = true;
            }
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Notice',
                text: 'Please select at least one set.'
            });
            return;
        }

        if (hasAssigned) {
            Swal.fire({
                icon: 'error',
                title: 'Not Allowed',
                text: 'One or more selected sets are already assigned. You cannot re-assign them.'
            });
            return;
        }

        $('#modal_order_set_id').val('');
        $('#modal_order_set_ids').val(selectedIds.join(','));

        $('#modal_design_number').text('Multiple sets selected');
        $('#modal_set_size').text('-');
        $('#modal_set_size_group').text('-');
        $('#modal_color').text('-');
        $('#modal_total_qty').text('-');

        $('#assign_qty_group').hide();
        $('#modal_product_season_id').val('').trigger('change');

        $('#assignModal').modal('show');
    });
});

// WAREHOUSE CHANGE
function warehouseChange(warehouse_id) {
    let cuttingSelect = $('#master_cutting_id');
    cuttingSelect.empty();

    let warehouse = warehouses.find(w => w.id == warehouse_id);

    if (warehouse && warehouse.cutting_units) {
        warehouse.cutting_units.forEach(unit => {
            cuttingSelect.append(
                `<option value="${unit.id}">${unit.name}</option>`
            );
        });
    }

    cuttingSelect.trigger('change.select2');

    // Fetch fabrics based on warehouse
    let fabricSelect = $('#fabric_id');
    fabricSelect.empty().append('<option value="">Loading Fabrics...</option>');
    fabricSelect.trigger('change.select2');
    
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
            fabricSelect.trigger('change.select2');
        },
        error: function () {
            fabricSelect.empty();
            fabricSelect.trigger('change.select2');
        }
    });

    printingWarehouseChange();
}

function printingWarehouseChange() {
    let printingSelect = $('#printing_unit_id');
    printingSelect.empty();
    printingSelect.append('<option value="">Select Printing Unit</option>');

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

$('#assignForm').on('submit', function (e) {
    e.preventDefault();

    if ($('#modal_order_set_id').val()) {
        const qty = parseInt($('#assign_quantity').val()) || 0;
        const remain = parseInt($('#current_remain_qty').text()) || 0;
        if (qty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Invalid Quantity', text: 'Please enter a valid quantity.' });
            return;
        }
        if (qty > remain) {
            Swal.fire({ icon: 'warning', title: 'Invalid Quantity', text: 'Quantity exceeds remaining pieces.' });
            return;
        }
    }

    $.ajax({
        url: "{{ route('admin.product_order.assign_to') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function (res) {
            if (res.status) {
                $('#assignModal').modal('hide');
                $('#customers').DataTable().ajax.reload(null, false);
                toastr.success('Assigned successfully');
            } else {
                Swal.fire({ icon: 'error', title: 'Assignment Failed', text: res.message });
            }
        },
        error: function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
        }
    });
});

// Delete Assignment Handler
$(document).on('click', '.delete-assign-btn', function () {
    const id = $(this).data('id');
    Swal.fire({
        title: "Delete Assignment?",
        text: "Are you sure you want to delete all assignment details for this set? This will revert stock and mark it as Not Assigned.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete assignment"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('admin.product_order.deleteAssignment') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function (res) {
                    if (res.status) {
                        $('#customers').DataTable().ajax.reload(null, false);
                        toastr.success('Assignment deleted successfully');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong' });
                }
            });
        }
    });
});
</script>
@endsection
