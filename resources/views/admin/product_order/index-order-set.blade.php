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

                    <table id="customers" class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <td><input type="hidden" id="id" value="{{ $order_main->id }}"></td>
                            <td><input type="text" class="form-control" id="bar_code"></td>
                            <td><input type="text" class="form-control" id="design_number"></td>
                            <td colspan="9"></td>
                        </tr>

                        <tr>
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
                            <th colspan="6" class="text-right">Total</th>
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
                        <select id="fabric_id" name="fabric_id" class="form-control select2" required>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}">{{ $fabric->name }} ({{ ($fabric->receipt_details_sum_remaining_quantity ?? 0) }} meter)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- FITTING -->
                    <div class="form-group">
                        <label>Fitting</label>
                        <select name="master_fitting_id" class="form-control select2" required>
                            <option value="">Select</option>
                            @foreach($fittings as $fitting)
                                <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Design Pattern</label>
                        <select name="master_pattern_id" class="form-control select2" required>
                            <option value="">Select</option>
                            @foreach($patterns as $pattern)
                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- REMARK -->
                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark" class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Assign</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- PHP DATA TO JS -->
<script>
    const warehouses = Object.values(@json($cutting_units));
</script>

<!-- SCRIPTS -->
<script>
$(document).ready(function () {

    $('.select2').select2({ width: '100%' });

    // Load default warehouse cutting masters
    warehouseChange($('#warehouse_id').val());

    // Open modal
    $(document).on('click', '.assign-btn', function () {
        $('#modal_order_set_id').val($(this).data('id'));
        $('#modal_design_number').text($(this).data('design'));
        $('#modal_set_size').text($(this).data('set-size'));
        $('#modal_set_size_group').text($(this).data('set-size-group'));
        $('#modal_color').text($(this).data('color'));
        $('#modal_total_qty').text($(this).data('total'));
        $('#assignModal').modal('show');
    });

    // DataTable
    $('#customers').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: "{{ route('admin.product_order.indexListOrderSet') }}",
            data: function (d) {
                d.id = $('#id').val();
                d.bar_code = $('#bar_code').val();
                d.design_number = $('#design_number').val();
            }
        },
        columns: [
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
            $('#set_qty_total').html(sum(6));
            $('#total_qty_total').html(sum(8));
        }
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
}

$('#assignForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('admin.product_order.assign_to') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function () {
            $('#assignModal').modal('hide');
            $('#customers').DataTable().ajax.reload(null, false);
        },
        error: function () {
            alert('Something went wrong');
        }
    });
});

</script>

@endsection
