@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!--  Header (Simplified & Compact) -->
    <section class="content-header py-2 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-tasks text-secondary"></i> Warehouse
            </h5>
            <ol class="breadcrumb float-sm-right mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-primary">Home</a></li>
                <li class="breadcrumb-item active text-muted">Manage Warehouse</li>
            </ol>
        </div>
    </section>

    <!--  Table Section -->
    <section class="content mt-3">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 rounded-3">
                <!-- <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-secondary">
                        <i class="fas fa-table"></i> Stage Overview
                    </h6>
                </div> -->

                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table id="order_stage" class="table table-sm table-bordered text-center align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order No</th>
                                    <th>Product SKU</th>
                                    <th>Lot No.</th>
                                    <th>From Stage</th>
                                    <th>Rack</th>
                                    <th>Qty</th>
                                    <!-- <th>Status</th> -->
                                    <th>Received</th>
                                    <!-- <th>Action</th> -->
                                </tr>
                                <tr class="bg-white">
                                    <td></td>
                                    <td><input type="text" class="form-control form-control-sm" id="sku" placeholder="Order No"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="order_product_id" placeholder="Product SKU"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="lot_no" placeholder="Lot No."></td>
                                    
                                    <td>
                                        <select id="from_stage_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach($product_stage as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select id="master_warehouse_block_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach($master_blocks as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" id="original_qty" placeholder="Qty"></td>

                                    <td><input type="date" class="form-control form-control-sm" id="created_at"></td>
                                    <!-- <td></td> -->
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

<!--  JS Section -->
<script>
$(function () {
    var oTable = $('#order_stage').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 10,
        ajax: {
            url: '{!! route('admin.warehouse.indexListListing') !!}',
            data: function (d) {
                d.sku = $('#sku').val();
                d.order_product_id = $('#order_product_id').val();
                d.from_stage_id = $('#from_stage_id').val();
                d.master_warehouse_block_id = $('#master_warehouse_block_id').val();
                d.lot_no = $('#lot_no').val();
                d.original_qty = $('#original_qty').val();
                d.created_at = $('#created_at').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', width: '5%' },
            { data: 'sku', name: 'sku', width: '10%' },
            { data: 'order_product_id', name: 'order_product_id', width: '12%' },
            { data: 'lot_no', name: 'lot_no' },
            { data: 'from_stage_id', name: 'from_stage_id', width: '10%' },
            { data: 'master_warehouse_block_id', name: 'master_warehouse_block_id' },
            { data: 'original_qty', name: 'original_qty', width: '7%' },
            { data: 'created_at', name: 'created_at', width: '12%' },
            // { data: 'action', name: 'action', width: '10%', orderable: false, searchable: false }
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

        $('#master_warehouse_block_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#original_qty').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#lot_no').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        })
        
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

});
</script>

@endsection
