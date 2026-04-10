@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Fabric</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Manage Fabric</li>
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
                    <!-- <div class="row">
                        <div class="col-9 card-header">
                            <h3 class="card-title">Manage Fabric</h3>
                        </div>
                        <div class="col-3 card-header">
                            <a href="{{ route('admin.master.fabric.create') }}" class="btn btn-primary"
                                style =" float: right;  width: max-content;">Add Fabric</a>
                        </div>
                    </div> -->
                    
                    <div class="card-body table-responsive">
                        <table id="customers" class="table table-bordered table-hover">
                            <thead>
                                <tr role="row" class="filter">
                                    <td>
                                        <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                                    </td>
                                    <td>

                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="name" id="name"
                                            autocomplete="off">
                                    </td>
                                    
                                    <td>
                                        <select class="form-control select2" name="vendor_id" id="vendor_id" autocomplete="off">
                                            <option value="">ALL</option>
                                            @foreach ($vendor_data as $single_data)
                                                <option value="{{ $single_data->id }}" >{{ $single_data->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    
                                    <td>
                                        <select class="form-control select2" name="composition_id" id="composition_id"
                                            autocomplete="off">
                                            <option value="">ALL</option>
                                            @foreach ($fab_composition_data as $single_data)
                                                <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td></td>
                                    <td>
                                        <select class="form-control select2" name="status" id="status" autocomplete="off">
                                            <option value="">Select Status</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </td>
                                    <td></td>

                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Supplier Name</th>
                                    <th>Composition</th>
                                    <th>Unit</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- <tr>
                            <td>1</td>
                            <td>wefds</td>
                            <td>Win 95+</td>
                            <td> 4</td>
                            <td>X</td>
                          </tr> -->

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imgModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0 text-center">
                    <img id="previewImg" src="" style="width:100%; height:auto; border-radius:6px;">
                </div>

                <button type="button" class="btn btn-danger"
                    style="position:absolute; top:10px; right:10px;" 
                    data-dismiss="modal">X</button>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            var i = 1;
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
                "pageLength": 25,
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
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'id'
                    },
                    {
                        data: 'image',
                        name: 'image'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'vendor_id',
                        name: 'vendor_id'
                    },
                    {
                        data: 'composition_id',
                        name: 'composition_id'
                    },
                    {
                        data: 'fabric_unit_id',
                        name: 'fabric_unit_id'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        searchable: false
                    }
                ],
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: 'Add Fabric',
                        className: 'btn-datatable',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.master.fabric.create') }}";
                        }
                    }
                ]
            });

            $('#email-queue-search-form').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });

            $('#id').on('keyup', function(e) {
                oTable.draw();
                e.preventDefault();
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

        $(document).ready(function() {

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
                    // If user confirms, trigger the delete route
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
