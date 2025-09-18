@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Doctors</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Doctors</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Doctors</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.doctor.create')}}" class="btn btn-primary " style=" float: right; width: max-content;">Add Doctor</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="first_name" id="first_name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="last_name" id="last_name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="title" id="title" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="phone" id="phone" autocomplete="off">
                        </td>
                        
                        <td>
                            
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
                    <th>Name</th>
                    <th>Last Name</th>
                    <th>Title</th>
                    <th>Phone</th>
                    <th>Image</th>
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
                url: '{!! route('admin.doctor.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.first_name = $('#first_name').val();
                    d.role_id = $('#role_id').val();
					d.status = $('#status').val();
					d.last_name = $('#last_name').val();
					d.title = $('#title').val();
					d.phone = $('#phone').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'last_name', name: 'last_name'},
                {data: 'title', name: 'title'},
                {data: 'phone', name: 'phone'},
                {data: 'image', name: 'image',
                render: function(data, type, full, meta){
                    return "<img src=\" " + data + " \" height=\"50\" width=\"50\" >";
                 }
                },
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy']
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#first_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#last_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#title').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#phone').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#role_id').on('change', function (e) {
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

    function deleteUser(id){
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
                window.location.href = "{{ route('admin.doctor.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection
