@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Stock</li>
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
                        <h3 class="card-title">Manage Stock</h3>
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
                            <input type="date" class="form-control" name="date" id="date" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="goods_entry_number" id="goods_entry_number" autocomplete="off">
                        </td>
                        
                        <td>
                            <input type="text" class="form-control" name="meter" id="meter" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="roll" id="roll" autocomplete="off">
                        </td>  
                        <td>
                       
                       </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Date</th>
                        <th>Goods Entry Number</th>
                        <th>Meter</th>
                        <th>Roll</th>
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
                url: '{!! route('admin.stock.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.goods_entry_number = $('#goods_entry_number').val();
                    d.meter = $('#meter').val();
                    d.roll = $('#roll').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'goods_entry_number', name: 'goods_entry_number'},
                {data: 'meter', name: 'meter'},
                {data: 'roll', name: 'roll'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy']
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#goods_entry_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#meter').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#roll').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

    });

    $(document).ready(function () {
        
    });

</script>

@endsection
