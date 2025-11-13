@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Purchase Order Items</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Purchase Order Items</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
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
                            <select class="form-control" name="vendor_id" id="vendor_id" autocomplete="off">
                                <option value="">ALL</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="date" class="form-control" name="delivery_date" id="delivery_date" autocomplete="off">
                        </td>
                        <td> </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>PO No.</th>
                        <th>Purchase Order Date</th>
                        <th>Vendor</th>
                        <th>Expected Delivery Date</th>
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
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.purchase_order_material.indexList') !!}',
                data: function (d) {
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.delivery_date = $('#delivery_date').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Purchase Order Items',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.purchase_order_material.create') }}";
                    }
                }
            ]
        });

        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#vendor_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });
</script>
@endsection