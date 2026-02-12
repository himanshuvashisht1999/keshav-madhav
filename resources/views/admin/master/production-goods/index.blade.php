@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Available Products</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Available Products</li>
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
                 <!-- <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Products</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.production-goods.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Product</a>
                    </div>
                </div> -->
                 
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <!-- <td></td> -->
                        <td>
                            <input type="text" class="form-control" name="design_number" id="design_number" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="name_of_garment" id="name_of_garment" autocomplete="off">
                        </td>
                        {{-- <td>
                            <select class="form-control" name="status" id="status" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="1">Published</option>
                                <option value="0">Pending</option>
                            </select>
                        </td> --}}
                        <td>
                       
                       </td>
                    </tr>
                  <tr>
                    <th>ID</th>
                    <!-- <th>Image</th> -->
                    <th>Design Number</th>
                    <th>Name</th>
                    {{-- <th>Status</th> --}}
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
<script>
    $(function () {
        var i = 1;
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.master.production-goods.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name_of_garment = $('#name_of_garment').val();
                    d.design_number = $('#design_number').val();
                    d.status = $('#status').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                // {data: 'main_image', name: 'main_image', orderable: false, searchable: false},
                {data: 'design_number', name: 'design_number'},
                {data: 'name_of_garment', name: 'name_of_garment'},
               // {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add New Product',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.production-goods.create') }}";
                    }
                }
            ]
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#name_of_garment').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#design_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

       

    });

    $(document).ready(function () {
        
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
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.master.production-goods.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection
