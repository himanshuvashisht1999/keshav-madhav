@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-warehouse text-primary"></i> Storeroom Master
        </div>
        <div class="erp-header-actions">
            <button type="button" class="btn-erp btn-erp-primary" data-toggle="modal" data-target="#createModal">
                <i class="fas fa-plus"></i> Add New Storeroom
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover" id="storeroomTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 140px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.master.storeroom.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Create Storeroom</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Name</label>
                        <input type="text" name="name" class="form-control erp-filter-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Order Taken</label>
                        <select name="order_taken" id="order_taken" class="form-control erp-filter-select">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div class="mb-3" id="order_priority_div" style="display: none;">
                        <label class="form-label font-weight-bold">Order Priority</label>
                        <input type="number" name="order_priority" class="form-control erp-filter-input" placeholder="Enter Priority Number (e.g., 1, 2, 3)" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Order Dispatch</label>
                        <select name="order_dispatch" id="order_dispatch" class="form-control erp-filter-select">
                            <option value="Yes" selected>Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Description</label>
                        <textarea name="description" class="form-control erp-filter-input" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn-erp btn-erp-primary">Save Storeroom</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function () {
        var table = $('#storeroomTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.master.storeroom.indexList') }}",
            order: [[1, 'asc']],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'description', name: 'description'},
                {data: 'status', name: 'status', className: 'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ]
        });

        $('#order_taken').change(function() {
            if ($(this).val() == 'Yes') {
                $('#order_priority_div').show();
            } else {
                $('#order_priority_div').hide();
            }
        });
    });

    function deleteData(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.storeroom.delete') }}?id=" + id;
            }
        });
    }
</script>
@endsection
