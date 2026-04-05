@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Fabric Warehouse</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Fabric Warehouse</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                
                <div class="card-body table-responsive">
                <table id="fabric_warehouse" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="cutting_master_name" id="cutting_master_name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="address" id="address" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </td>
                        <td>
                       </td>
                    </tr>
                   <tr>
                    <th>ID</th>
                    <th>Cutting Master Name</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(function () {
        var i = 1;
        var oTable = $('#fabric_warehouse').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
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
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'cutting_master_name', name: 'cutting_master_name'},
                {data: 'address', name: 'address'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Fabric Warehouse',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.fabric_warehouse.create') }}";
                    }
                }
            ]
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
