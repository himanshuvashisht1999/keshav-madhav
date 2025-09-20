@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Fabric Receipt</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Fabric Receipt</li>
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
                 <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Fabric Receipt</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.fabric_receipt.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Fabric Receipt</a>
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
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        
                        <td>
                            <select class="form-control" name="vendor_id" id="vendor_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="truck_number" id="truck_number" autocomplete="off">
                        </td>
                        
                        <td>
                            <input type="date" class="form-control" name="time" id="time" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="roll" id="roll" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="received_by" id="received_by" autocomplete="off">
                        </td>
                        
                        
                        <td>
                       
                       </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Vendor</th>
                        <th>Truck Number</th>
                        <th>Time</th>
                        <th>Roll</th>
                        <th>Received By</th>
                        
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
                url: '{!! route('admin.fabric_receipt.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.truck_number = $('#truck_number').val();
                    d.time = $('#time').val();
                    d.roll = $('#roll').val();
                    d.received_by = $('#received_by').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'truck_number', name: 'truck_number'},
                {data: 'time', name: 'time'},
                {data: 'roll', name: 'roll'},
                {data: 'received_by', name: 'received_by'},
                
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
        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#vendor_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#truck_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#roll').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#received_by').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#time').on('change', function (e) {
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
            confirmButtonColor: "#53718dff",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.fabric_receipt.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection
