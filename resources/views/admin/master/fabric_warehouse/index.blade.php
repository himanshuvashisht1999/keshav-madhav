@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">List of Available Fabric Warehouse</h1>
                </div>
                <!-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Warehouse</li>
                    </ol>
                </div> -->
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
                        <h3 class="card-title">Manage Product Color</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.warehouse.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Color</a>
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
                            <input type="text" class="form-control" name="cutting_master_name" id="cutting_master_name" autocomplete="off">
                        </td>
                        <!-- <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td> -->
                        <td>
                            <input type="text" class="form-control" name="address" id="address" autocomplete="off">
                        </td>
                        
                        <td>
                       
                       </td>
                    </tr>
                  <tr>
                    <th>ID</th>
                    <th>Warehouse</th>
                    <!-- <th>SKU</th> -->
                    <th>Address</th>
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
                url: '{!! route('admin.master.fabric_warehouse.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.cutting_master_name = $('#cutting_master_name').val();
                    d.address = $('#address').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'cutting_master_name', name: 'cutting_master_name'},
                {data: 'address', name: 'address'},
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

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#cutting_master_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#address').on('keyup', function (e) {
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
                window.location.href = "{{ route('admin.master.fabric_warehouse.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection
