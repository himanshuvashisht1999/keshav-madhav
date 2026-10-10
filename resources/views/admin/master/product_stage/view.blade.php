@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Manage Production Stage ({{ $stage_data->name }})
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.product_stage.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Stages
            </a>
            <a href="{{ route('admin.master.product_stage.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Production Stage
            </a>
        </div>
    </div>

    <div class="erp-card">
        <div class="erp-card-body p-0 table-responsive">
            <table id="customers" class="erp-table">
                <thead>
                    <tr role="row" class="filter">
                        <td>
                            <input type="hidden" class="form-control" name="stage_id" id="stage_id" value="{{ $stage_data->id }}" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name" id="name" placeholder="Filter Stage..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="sku" id="sku" placeholder="Filter SKU..." autocomplete="off">
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Stage</th>
                        <th>SKU</th>
                        <th style="width: 120px;" class="text-center">Action</th>
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
                url: '{!! route('admin.master.product_stage.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.sku = $('#sku').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'sku', name: 'sku'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ],
            dom: '<"d-flex justify-content-between align-items-center p-2"lf>rt<"d-flex justify-content-between align-items-center p-2"ip>'
        });

        $('#name, #sku').on('keyup', function () {
            oTable.draw();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "var(--brand-dark-green)",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.colors.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection
