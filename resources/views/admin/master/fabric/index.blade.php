@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Manage Fabric
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Fabric
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
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name" id="name" placeholder="Search name..." autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control select2 erp-filter-select" name="vendor_id" id="vendor_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach ($vendor_data as $single_data)
                                    <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control select2 erp-filter-select" name="composition_id" id="composition_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach ($fab_composition_data as $single_data)
                                    <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td></td>
                        <td>
                            <select class="form-control select2 erp-filter-select" name="status" id="status" autocomplete="off">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 70px;">Image</th>
                        <th>Name</th>
                        <th>Supplier Name</th>
                        <th>Composition</th>
                        <th style="width: 80px;">Unit</th>
                        <th style="width: 90px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0 text-center">
                <img id="previewImg" src="" style="width:100%; height:auto; border-radius:6px;">
            </div>
            <button type="button" class="btn btn-danger" style="position:absolute; top:10px; right:10px;" data-dismiss="modal">X</button>
        </div>
    </div>
</div>

<script>
    $(function() {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [
                [25, 100, -1],
                [25, 100, "All"]
            ],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.fabric.indexList') !!}',
                data: function(d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.status = $('#status').val();
                    d.composition_id = $('#composition_id').val();
                },
                orderable: false
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'image', name: 'image' },
                { data: 'name', name: 'name' },
                { data: 'vendor_id', name: 'vendor_id' },
                { data: 'composition_id', name: 'composition_id' },
                { data: 'fabric_unit_id', name: 'fabric_unit_id' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'lfrtip'
        });

        $('#name').on('keyup', function(e) {
            oTable.draw();
            e.preventDefault();
        });
       
        $('#composition_id').on('change', function(e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#vendor_id').on('change', function(e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#status').on('change', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    });

    function deleteData(id) {
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
                window.location.href = "{{ route('admin.master.fabric.delete') }}?id=" + id;
            }
        });
    }

    $(document).on("click", ".fabric-img", function () {
        let src = $(this).attr("src");
        $("#previewImg").attr("src", src);
        $("#imgModal").modal("show");
    });
</script>
@endsection
