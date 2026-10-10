@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-warehouse text-primary"></i> Manage Fabric Warehouse
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric_warehouse.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Fabric Warehouse
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="fabric_warehouse" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="cutting_master_name" id="cutting_master_name" placeholder="Search cutting master..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="address" id="address" placeholder="Search address..." autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control erp-filter-select" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Cutting Master Name</th>
                        <th>Address</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#fabric_warehouse').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.fabric_warehouse.indexList') !!}',
                data: function (d) {
                    d.cutting_master_name = $('#cutting_master_name').val();
                    d.address = $('#address').val();
                    d.status = $('#status').val();
                },
                orderable: false
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'cutting_master_name', name: 'cutting_master_name' },
                { data: 'address', name: 'address' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'lfrtip'
        });

        $('#cutting_master_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#address').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.fabric_warehouse.delete') }}?id=" + id;
            }
        });
    }
</script>
@endsection
