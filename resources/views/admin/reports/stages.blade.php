@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!-- ✅ Header (Simplified & Compact) -->
    <section class="content-header py-2 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-tasks text-secondary"></i> {{ $stage_data->name }} Stage
            </h5>
            <ol class="breadcrumb float-sm-right mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-primary">Home</a></li>
                <li class="breadcrumb-item active text-muted">Stage Report</li>
            </ol>
        </div>
    </section>

    <!-- ✅ Table Section -->
    <section class="content mt-3">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-secondary">
                        <i class="fas fa-table"></i> Stage Report
                    </h6>
                </div>

                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table id="order_stage" class="table table-sm table-bordered text-center align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order No</th>
                                    <th>Product SKU</th>
                                    <th>From Stage</th>
                                    <th>Qty</th>
                                    <th>Remain</th>
                                    <th>Status</th>
                                    <th>Received</th>
                                    <th>Delivered</th>
                                    
                                </tr>
                                <tr class="bg-white">
                                    <td></td>
                                    <td><input type="text" class="form-control form-control-sm" id="sku" placeholder="Order No"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="order_product_id" placeholder="Product SKU"></td>
                                    <td>
                                        <select id="from_stage_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach($product_stage as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" id="quantity" placeholder="Qty"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="remaining_quantity" placeholder="Remain"></td>
                                    <td>
                                        <select id="status" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </td>
                                    <td><input type="date" class="form-control form-control-sm" id="created_at"></td>
                                    <td><input type="date" class="form-control form-control-sm" id="updated_at"></td>                                    
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- ✅ JS Section -->
<script>
$(function () {
    var oTable = $('#order_stage').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 10,
        ajax: {
            url: '{!! route('admin.order-stages.indexList',['stage_id' => $stage_data->id]) !!}',
            data: function (d) {
                d.sku = $('#sku').val();
                d.order_product_id = $('#order_product_id').val();
                d.from_stage_id = $('#from_stage_id').val();
                d.quantity = $('#quantity').val();
                d.remaining_quantity = $('#remaining_quantity').val();
                d.status = $('#status').val();
                d.created_at = $('#created_at').val();
                d.updated_at = $('#updated_at').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', width: '5%' },
            { data: 'sku', name: 'sku', width: '10%' },
            { data: 'order_product_id', name: 'order_product_id', width: '12%' },
            { data: 'from_stage_id', name: 'from_stage_id', width: '10%' },
            { data: 'quantity', name: 'quantity', width: '7%' },
            { data: 'remaining_quantity', name: 'remaining_quantity', width: '7%' },
            { data: 'status', name: 'status', width: '9%' },
            { data: 'created_at', name: 'created_at', width: '12%' },
            { data: 'updated_at', name: 'updated_at', width: '12%' }
        ],
        dom: 'lBfrtip',
        buttons: [
            {
                text: `<i class="nav-icon fas fa-download"></i> Excel Reports`,
                attr: {
                    id: 'generateExcel',
                    name: 'generateExcel'
                },
                className: 'btn-datatable',
                action: function (e, dt, node, config) {
                    e.preventDefault();
                }
            }
        ]
    });

    $('#email-queue-search-form').on('submit', function (e) {
        oTable.draw();
        e.preventDefault();
    });

    $('#id').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });

    $('#sku').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    $('#order_product_id').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    $('#from_stage_id').on('change', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    $('#quantity').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });

    $('#remaining_quantity').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    
    $('#order_product_id').on('keyup', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    
    $('#created_at').on('change', function (e) {
        oTable.draw();
        e.preventDefault();
    });
    $('#status').on('change', function (e) {
        oTable.draw();
        e.preventDefault();
    });        

    $(document).ready(function () {
        $('#generateExcel').on('click', function (e) {
            e.preventDefault(); // prevent default behavior

            // Collect filter values
            var data = {
                id: $('#id').val(),
                sku: $('#sku').val(),
                order_product_id: $('#order_product_id').val(),
                from_stage_id: $('#from_stage_id').val(),
                quantity: $('#quantity').val(),   
                remaining_quantity: $('#remaining_quantity').val(),
                status: $('#status').val(),
                created_at: $('#created_at').val(),
                updated_at: $('#updated_at').val(),
                _token: '{{ csrf_token() }}'
            };

            // Create a hidden form for POST submission (so file download works)
            var form = $('<form>', {
                action: '{{ route("admin.reports.stagesExcel" ,['stage_id' => $stage_data->id]) }}',
                method: 'POST',
                target: '_blank'
            }).append($.map(data, function(v, k) {
                return $('<input>', { type: 'hidden', name: k, value: v });
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });
    });
});


</script>
@endsection
