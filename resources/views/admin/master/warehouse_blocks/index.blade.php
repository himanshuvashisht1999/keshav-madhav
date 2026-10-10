@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="erp-header-title">
            <i class="fas fa-th-large text-primary"></i> Manage warehouse racks
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.warehouse-blocks.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add rack
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name" id="name" placeholder="Search name..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="sku" id="sku" placeholder="Search SKU..." autocomplete="off">
                        </td>
                        <td>
                            <select id="master_warehouse_id" name="master_warehouse_id" class="form-control erp-filter-select">
                                <option value="">All warehouses</option>
                                @foreach($master_warehouses as $masterWarehouse)
                                    <option value="{{ $masterWarehouse->id }}" {{ old('master_warehouse_id') == $masterWarehouse->id ? 'selected' : '' }}>{{ $masterWarehouse->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Warehouse</th>
                        <th style="width: 100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.warehouse-blocks.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.sku = $('#sku').val();
                    d.master_warehouse_id = $('#master_warehouse_id').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'sku', name: 'sku'},
                {data: 'master_warehouse_id', name: 'master_warehouse_id'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: []
        });

        $('#name, #sku, #master_warehouse_id').on('keyup change', function () {
            oTable.draw();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "var(--erp-green-primary, #05421c)",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.warehouse-blocks.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection
