@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-warehouse text-primary mr-2"></i> Warehouse Stock Listing
        </div>
        <div class="erp-header-actions">
            <!-- Action buttons if needed -->
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No</label>
                <input type="text" class="form-control erp-input" id="sku" placeholder="Order No">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Product SKU</label>
                <input type="text" class="form-control erp-input" id="order_product_id" placeholder="Product SKU">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-boxes mr-1"></i> Lot No.</label>
                <input type="text" class="form-control erp-input" id="lot_no" placeholder="Lot No.">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-random mr-1"></i> From Stage</label>
                <select id="from_stage_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL STAGES --</option>
                    @foreach($product_stage as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-th mr-1"></i> Rack / Block</label>
                <select id="master_warehouse_block_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL RACKS --</option>
                    @foreach($master_blocks as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-2 col-sm-3 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calculator mr-1"></i> Qty</label>
                <input type="text" class="form-control erp-input" id="original_qty" placeholder="Qty">
            </div>
            <div class="col-lg-1 col-md-2 col-sm-3 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Received</label>
                <input type="date" class="form-control erp-input" id="created_at">
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="btnApplyFilter" title="Apply Filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetFilter" title="Reset Filters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="order_stage" class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Order No</th>
                        <th>Product SKU</th>
                        <th>Lot No.</th>
                        <th>From Stage</th>
                        <th>Rack</th>
                        <th>Qty</th>
                        <th>Received</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS Section -->
<script>
$(function () {
    var oTable = $('#order_stage').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
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
            { data: 'DT_RowIndex', name: 'id', width: '5%', orderable: false, searchable: false },
            { data: 'sku', name: 'sku', width: '12%' },
            { data: 'order_product_id', name: 'order_product_id', width: '15%' },
            { data: 'lot_no', name: 'lot_no', width: '12%' },
            { data: 'from_stage_id', name: 'from_stage_id', width: '14%' },
            { data: 'master_warehouse_block_id', name: 'master_warehouse_block_id', width: '14%' },
            { data: 'original_qty', name: 'original_qty', width: '10%' },
            { data: 'created_at', name: 'created_at', width: '14%' }
        ],
        dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>rt<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#btnApplyFilter').on('click', function (e) {
        e.preventDefault();
        oTable.draw();
    });

    $('#btnResetFilter').on('click', function (e) {
        e.preventDefault();
        $('#sku').val('');
        $('#order_product_id').val('');
        $('#lot_no').val('');
        $('#from_stage_id').val('').trigger('change');
        $('#master_warehouse_block_id').val('').trigger('change');
        $('#original_qty').val('');
        $('#created_at').val('');
        oTable.draw();
    });

    $('#sku, #order_product_id, #lot_no, #original_qty').on('keyup', function (e) {
        if (e.keyCode === 13) oTable.draw();
    });

    $('#from_stage_id, #master_warehouse_block_id, #created_at').on('change', function () {
        oTable.draw();
    });
});
</script>
@endsection
