@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="erp-header-title">
            <i class="fas fa-tags text-primary"></i> Manage item attributes
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.item-attributes.create', ['id' => request('id')]) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add item attribute
            </a>
            <a href="{{ route('admin.master.item.index') }}" class="btn-erp btn-erp-outline ml-1">
                <i class="fas fa-arrow-left mr-1"></i> Back to items
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
                            <input type="text" class="form-control erp-filter-input" name="sku" id="sku" placeholder="Search SKU..." autocomplete="off">
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>SKU</th>
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
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.item-attributes.indexList', ['id' => request('id')]) !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: []
        });

        $('#sku').on('keyup change', function () {
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
                window.location.href = "{{ route('admin.master.item-attributes.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection
