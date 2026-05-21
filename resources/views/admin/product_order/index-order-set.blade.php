@extends('admin.layouts.app')

@section('content')
<style>
.assign-to {
    color: #007bff !important;
}
</style>

<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-center">Details of Sales Order</h1>
            <h4 class="text-center">Order No. - ({{ $order_main->sku }})</h4>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card card-default">
                <div class="card-body table-responsive">

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" id="bulkAssignBtn" class="btn btn-primary btn-sm">
                                Assign Selected to Cutting Master
                            </button>
                            <a href="{{ route('admin.product_order.bulkPO', ['order_id' => $order_main_id]) }}" class="btn btn-outline-info btn-sm">
                                Create PO
                            </a>
                        </div>
                    </div>

                    <table id="customers" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <td></td>
                            <td><input type="hidden" id="id" value="{{ $order_main->id }}"></td>
                            <td><input type="text" class="form-control" id="bar_code"></td>
                            <td><input type="text" class="form-control" id="design_number"></td>
                            <td colspan="6"></td>
                            <td>
                                <select class="form-control" id="assigned_filter">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="assigned">Assigned</option>
                                </select>
                            </td>
                            <td colspan="2"></td>
                        </tr>

                        <tr>
                            <th><input type="checkbox" id="select_all"></th>
                            <th>ID</th>
                            <th>Bar Code</th>
                            <th>Design No</th>
                            <th>Set Size</th>
                            <th>Size Group</th>
                            <th>Color</th>
                            <th>Set Qty</th>
                            <th>Pcs / Set</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th>Assign To</th>
                            <th>Action</th>
                        </tr>
                        </thead>

                        <tbody></tbody>

                        <tfoot>
                        <tr>
                            <th colspan="7" class="text-right">Total</th>
                            <th id="set_qty_total"></th>
                            <th>Total Qty</th>
                            <th id="total_qty_total"></th>
                            <th colspan="3"></th>
                        </tr>
                        </tfoot>
                    </table>

                </div>
            </div>

        </div>
    </section>
</div>

<!-- ASSIGN MODAL -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form id="assignForm">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Assign to Cutting Master</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <!-- ORDER INFO -->
                    <div class="border rounded p-2 mb-3 bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Design No:</strong>
                                <span id="modal_design_number"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Set Size:</strong>
                                <span id="modal_set_size"></span>
                            </div>
                            <div class="col-md-12">
                                <strong>Set Size Group:</strong>
                                <span id="modal_set_size_group"></span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Color:</strong>
                                <span id="modal_color"></span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Total Qty:</strong>
                                <span id="modal_total_qty"></span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="modal_order_set_id" name="order_product_set_id">
                    <input type="hidden" id="modal_order_set_ids" name="order_product_set_ids">

                    <!-- WAREHOUSE -->
                    <div class="form-group">
                        <label>Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id"
                                class="form-control select2"
                                onchange="warehouseChange(this.value)" required>
                            @foreach($cutting_units as $w)
                                <option value="{{ $w['id'] }}">{{ $w['warehouse_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- CUTTING MASTER -->
                    <div class="form-group">
                        <label>Cutting Master</label>
                        <select id="master_cutting_id" name="master_cutting_id"
                                class="form-control select2" required>
                        </select>
                    </div>

                    <!-- CUTTING MASTER -->
                    <div class="form-group">
                        <label>Fabric</label>
                        <select id="fabric_id" name="fabric_id[]" class="form-control select2" multiple required>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}">{{ $fabric->name }} ({{ ($fabric->receipt_details_sum_remaining_quantity ?? 0) }} meter)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- FITTING -->
                    <div class="form-group">
                        <label>Fitting</label>
                        <select name="master_fitting_id" class="form-control select2" required>
                            <!-- <option value="">Select</option> -->
                            @foreach($fittings as $fitting)
                                <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Design Pattern</label>
                        <select name="master_pattern_id" class="form-control select2" required>
                            <!-- <option value="">Select</option> -->
                            @foreach($patterns as $pattern)
                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- ASSIGN QTY -->
                    <div class="form-group" id="assign_qty_group">
                        <label>Total Pieces to Assign</label>
                        <input type="number" id="assign_quantity" name="assign_quantity" class="form-control" placeholder="Enter quantity">
                        <small class="text-muted">Current remaining pieces: <span id="current_remain_qty"></span></small>
                    </div>

                    <!-- BELT -->
                    <div class="form-group">
                        <label>Belt</label>
                        <input type="text" name="belt" class="form-control" placeholder="Enter belt details">
                    </div>

                    <!-- REMARK -->
                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control"></textarea>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold">Printing Preferences</h6>
                    <div class="form-group">
                        <label>Printing Required?</label>
                        <select name="is_printing" id="is_printing" class="form-control" onchange="togglePrinting(this.value)">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="form-group" id="printing_unit_group" style="display:none;">
                        <label>Printing & Embroidery Unit</label>
                        <select name="printing_unit_id" id="printing_unit_id" class="form-control select2">
                            <option value="">Select Printing Unit</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Assign</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- PO MODAL -->
<div class="modal fade" id="poModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form id="poForm">
                @csrf
                <input type="hidden" id="po_order_set_id" name="order_product_set_id">
                <input type="hidden" id="po_order_set_ids" name="order_product_set_ids">

                <div class="modal-header">
                    <h5 class="modal-title">Create Production PO</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="border rounded p-2 mb-3 bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Design No:</strong>
                                <span id="po_modal_design_number"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Color:</strong>
                                <span id="po_modal_color"></span>
                            </div>
                            <div class="col-md-12 mt-1">
                                <strong>Total Qty:</strong>
                                <span id="po_modal_total_qty"></span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>PO To</label>
                        <select name="po_type" id="po_type" class="form-control" onchange="togglePoTo(this.value)">
                            <option value="vendor">Vendor</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>

                    <div class="form-group" id="vendor_group">
                        <label>Vendor</label>
                        <select name="vendor_id" class="form-control select2">
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="customer_group" style="display:none;">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control select2">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Delivery Date</label>
                        <input type="date" name="delivery_date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Rate per Piece</label>
                        <input type="number" name="rate" class="form-control" placeholder="0.00" step="0.01">
                    </div>

                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-info">Create PO</button>
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
        // Reset customer selection
        $('#customer_group select').val('').trigger('change');
    } else {
        $('#vendor_group').hide();
        $('#customer_group').show();
        // Reset vendor selection
        $('#vendor_group select').val('').trigger('change');
        
        // Re-initialize or adjust Select2 if width issue exists
        $('#customer_group select').select2({
            width: '100%'
        });
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
        
        // Reset PO To to Vendor
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
            alert('Please select at least one set.');
            return;
        }

        const idsStr = selectedIds.join(',');
        window.location.href = "{{ route('admin.product_order.bulkPO') }}?set_ids=" + idsStr;
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
        
        $('#modal_total_qty').text(total);
        $('#current_remain_qty').text(remain);
        $('#assign_quantity').val(remain); // Default to full remaining
        $('#assign_qty_group').show();

        $('#assignModal').modal('show');
    });

    // DataTable
    $('#customers').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        paging: false,          // show complete order set (no pagination)
        info: true,
        lengthChange: false,
        ajax: {
            url: "{{ route('admin.product_order.indexListOrderSet') }}",
            data: function (d) {
                d.id = $('#id').val();
                d.bar_code = $('#bar_code').val();
                d.design_number = $('#design_number').val();
                d.assigned_filter = $('#assigned_filter').val();

                // force "all rows" behavior for server-side datatables
                d.start = 0;
                d.length = -1;
            }
        },
        columns: [
            {data: 'select', orderable: false, searchable: false},
            {data: 'DT_RowIndex'},
            {data: 'bar_code'},
            {data: 'design_number'},
            {data: 'set_size'},
            {data: 'size_group'},
            {data: 'color_id'},
            {data: 'set_quantity'},
            {data: 'no_of_pcs'},
            {data: 'total_qty'},
            {data: 'status'},
            {data: 'assign_to'},
            {data: 'action', searchable: false}
        ],
        footerCallback: function (row, data) {
            let api = this.api();
            let sum = col => api.column(col).data().reduce((a,b)=>+a + +b,0);
            $('#set_qty_total').html(sum(7));
            $('#total_qty_total').html(sum(9));
        }
    });

    // Reload on filters (debounced)
    let reloadTimer = null;
    function reloadTable() {
        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(function () {
            $('#customers').DataTable().ajax.reload(null, false);
        }, 250);
    }

    $('#bar_code, #design_number').on('keyup', reloadTable);
    $('#assigned_filter').on('change', reloadTable);

    // Select all checkbox
    $('#select_all').on('change', function () {
        const checked = $(this).is(':checked');
        $('.row-select').prop('checked', checked);
    });

    // Bulk assign button
    $('#bulkAssignBtn').on('click', function () {
        const selectedIds = $('.row-select:checked').map(function () {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one set.');
            return;
        }

        // Clear single-id and set multi-id
        $('#modal_order_set_id').val('');
        $('#modal_order_set_ids').val(selectedIds.join(','));

        // Indicate multiple selection
        $('#modal_design_number').text('Multiple sets selected');
        $('#modal_set_size').text('-');
        $('#modal_set_size_group').text('-');
        $('#modal_color').text('-');
        $('#modal_total_qty').text('-');

        $('#assign_qty_group').hide(); // Hide for bulk assignment for now

        $('#assignModal').modal('show');
    });

});

// WAREHOUSE CHANGE (NO AJAX)
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

    // Make sure all printing units are loaded (not warehouse specific)
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

    // Basic validation for quantity
    if ($('#modal_order_set_id').val()) {
        const qty = parseInt($('#assign_quantity').val()) || 0;
        const remain = parseInt($('#current_remain_qty').text()) || 0;
        if (qty <= 0) {
            alert('Please enter a valid quantity.');
            return;
        }
        if (qty > remain) {
            alert('Quantity exceeds remaining pieces.');
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
            } else {
                alert(res.message);
            }
        },
        error: function () {
            alert('Something went wrong');
        }
    });
});

// Delete Assignment Handler
$(document).on('click', '.delete-assign-btn', function () {
    const id = $(this).data('id');
    if (confirm('Are you sure you want to delete all assignment details for this set? This will revert stock and marks it as Not Assigned.')) {
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
                } else {
                    alert(res.message);
                }
            },
            error: function () {
                alert('Something went wrong');
            }
        });
    }
});

</script>

@endsection
