@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">List of Sales Orders</h1>
                </div>
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Production Order</li>
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
                            <select name="master_customer_id" id="master_customer_id" class="form-control select2" style="width: 100%;">
                                <option value="">All</option>
                                @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select id="order_type" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="corporate">Corporate</option>
                                <option value="domestic">Domestic</option>
                            </select>
                        </td>
                       
                        <td>
                            <input type="date" class="form-control" name="created_at" id="created_at" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <select id="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="1">In Progress</option>
                                <option value="2">Partial</option>
                                <option value="3">Completed</option>
                            </select>
                        </td>

                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Order Type</th>
                        <th>Order Date</th>
                        <th>Expected Delivery Date</th>
                        <th>Total Pcs</th>
                        <th>Dispatch Pcs</th>
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
                url: '{!! route('admin.product_order.indexListOrder') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.master_customer_id = $('#master_customer_id').val();
                    d.order_type = $('#order_type').val();
                    d.created_at = $('#created_at').val();
                    d.expected_delivery_date = $('#expected_delivery_date').val();
                    d.status = $('#status').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'master_customer_id', name: 'master_customer_id'},                
                {data: 'order_type', name: 'order_type'},                
                {data: 'created_at', name: 'created_at'},                
                {data: 'expected_delivery_date', name: 'expected_delivery_date'},     
                {data: 'total_pcs', name: 'total_pcs'}, 
                {data: 'dispatch_pcs', name: 'dispatch_pcs'},          
                {data: 'status', name: 'status'},                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Sales Order',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.sales_order.create') }}";
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

        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#master_customer_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#order_type').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#expected_delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#created_at').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        

    });



</script>

@endsection
