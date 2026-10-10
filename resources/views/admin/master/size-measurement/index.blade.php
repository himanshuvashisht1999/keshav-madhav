@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-ruler-combined text-primary"></i> Manage Size Group
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.size-measurement.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Size Group
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
                            <input type="text" class="form-control erp-filter-input" name="set_size" id="set_size" placeholder="Search name..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="no_of_pcs" id="no_of_pcs" placeholder="Search pcs..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="size_group" id="size_group" placeholder="Search size group..." autocomplete="off">
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
                        <th>Name</th>
                        <th>No of Pcs (per Set)</th>
                        <th>Size Group</th>
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
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.size-measurement.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.set_size = $('#set_size').val();
                    d.no_of_pcs = $('#no_of_pcs').val();
                    d.size_group = $('#size_group').val();
                    d.status = $('#status').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'set_size', name: 'set_size'},
                {data: 'no_of_pcs', name: 'no_of_pcs'},
                {data: 'size_group', name: 'size_group'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ]
        });

        $('#set_size').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#no_of_pcs').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#size_group').on('keyup', function (e) {
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
                window.location.href = "{{ route('admin.master.size-measurement.delete') }}?id=" + id;
            }
        });
    }
</script>
@endsection
