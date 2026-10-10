@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-building text-primary"></i> Manage Companies
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.company.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Company
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="companies-table" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Name</th>
                        <th>GST Number</th>
                        <th style="width: 100px;" class="text-center">Status</th>
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
        var oTable = $('#companies-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.company.indexList') !!}',
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'gst_number', name: 'gst_number'},
                {data: 'status', name: 'status', className: 'text-center'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ]
        });
    });

    function deleteItem(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "This company will be marked as inactive!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.master.company.delete') }}",
                    type: "POST",
                    data: {
                        id: id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire("Deleted!", response.message, "success");
                            $('#companies-table').DataTable().draw();
                        }
                    }
                });
            }
        });
    }
</script>
@endsection
