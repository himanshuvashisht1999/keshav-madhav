@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">List of Product Size</h1>
                </div>
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Size Group</li>
                    </ol>
                </div> --}}
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
                        <h3 class="card-title">Manage Product Size</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.size-measurement.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Product Size</a>
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
                            <select name="customer_id" id="customer_id" class="form-control select2" style="width: 100%;">
                                <option value="">All</option>
                                @foreach ($customers as $customer)
                                <option value="{{$customer->id}}" {{old('customer_id') == $customer->id ? 'selected' : ''}}>{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="design_number" id="design_number" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="name" id="name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="set_size" id="set_size" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="set_size" id="set_size" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="size_group" id="size_group" autocomplete="off">
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
                    <th>Company Name</th>
                    <th>Design Number</th>
                    <th>Name</th>
                    <th>Set Size</th>
                    <th>No of Pcs (per Set)</th>
                    <th>Size Group</th>
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
                url: '{!! route('admin.master.size-measurement.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.customer_id = $('#customer_id').val();
                    d.design_id = $('#design_id').val();
                    d.set_size = $('#set_size').val();
                    d.no_of_pcs = $('#no_of_pcs').val();
                    d.size_group = $('#size_group').val();
					d.status = $('#status').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'customer_id', name: 'customer_id'},
                {data: 'design_number', name: 'design_number'},
                {data: 'name', name: 'name'},
                {data: 'set_size', name: 'set_size'},
                {data: 'no_of_pcs', name: 'no_of_pcs'},
                {data: 'size_group', name: 'size_group'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Product Size',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.size-measurement.create') }}";
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

        $('#size_selection').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#measurement').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
       

        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#set_size').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#design_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#no_of_pcs').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#size_type').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#size_group').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#customer_id').on('change', function (e) {
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
                window.location.href = "{{ route('admin.master.size-measurement.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection


