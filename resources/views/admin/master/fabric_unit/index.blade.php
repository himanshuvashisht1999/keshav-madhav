@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Fabric Unit</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Fabric Unit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default ">
                <div class="card-body table-responsive">
                <table id="fabric_units" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control" name="name" id="name" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="symbol" id="symbol" autocomplete="off">
                        </td>
                        <td></td>
                    </tr>
                  <tr>
                    <th>ID</th>
                    <th>Unit Name</th>
                    <th>Symbol</th>
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
        var oTable = $('#fabric_units').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.master.fabric_unit.indexList') !!}',
                data: function (d) {
                    d.name = $('#name').val();
                    d.symbol = $('#symbol').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'symbol', name: 'symbol'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Fabric Unit',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.master.fabric_unit.create') }}";
                    }
                }
            ]
        });

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#symbol').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

    });
</script>

@endsection
