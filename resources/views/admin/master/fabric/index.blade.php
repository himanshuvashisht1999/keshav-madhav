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
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
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
                 <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Fabric</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.master.fabric.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Fabric</a>
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
                            <input type="text" class="form-control" name="name" id="name" autocomplete="off">
                        </td>
                         <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        <td>
                            <select class="form-control" name="dye_id" id="dye_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($dye_data as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <!-- <td>
                            <select class="form-control" name="width_id" id="width_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($fab_width_data as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td> -->
                        <td>
                            <select class="form-control" name="gsm_id" id="gsm_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($fab_gsm_data as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-control" name="composition_id" id="composition_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($fab_composition_data as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>

                        
                        <td>
                       
                       </td>
                    </tr>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Dye</th>
                    <th>GSM</th>
                    <th>Composition</th>
                    
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
                url: '{!! route('admin.master.fabric.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name = $('#name').val();
                    d.sku = $('#sku').val();
                    d.dye_id = $('#dye_id').val();
                    d.gsm_id = $('#gsm_id').val();
                    d.composition_id = $('#composition_id').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'sku', name: 'sku'},
                {data: 'dye_id', name: 'dye_id'},
                {data: 'gsm_id', name: 'gsm_id'},
                {data: 'composition_id', name: 'composition_id'},
                
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

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#dye_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#gsm_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#composition_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#sku').on('change', function (e) {
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
                window.location.href = "{{ route('admin.master.fabric.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection
