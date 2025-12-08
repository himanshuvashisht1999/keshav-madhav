@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Production Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Production Report</li>
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
                            <select name="master_customer_id" id="master_customer_id" class="form-control">
                                <option value="">All</option>
                                @foreach($customers as $customer)
                                <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                            
                        </td>
                        
                       
                        <td>
                            <input type="date" class="form-control" name="created_at" id="created_at" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
                        </td>
                        <td>
                            <select id="status" class="form-control form-control-sm">
                                <option value="">All</option>
                                <option value="1">In Progress</option>
                                <option value="2">Completed</option>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Estimated Delivery Date</th>
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
                url: '{!! route('admin.reports.productionList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.master_customer_id = $('#master_customer_id').val();
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
                {data: 'created_at', name: 'created_at'},                
                {data: 'expected_delivery_date', name: 'expected_delivery_date'},                
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
               {
                    text: `<i class="nav-icon fas fa-download"></i> Excel Reports`,
                    attr: {
                        id: 'generateExcel',
                        name: 'generateExcel'
                    },
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        e.preventDefault();
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
        $('#expected_delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#created_at').on('change', function (e) {
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
        
        $(document).ready(function () {
            $('#generateExcel').on('click', function (e) {
                e.preventDefault(); // prevent default behavior

                // Collect filter values
                var data = {
                    id: $('#id').val(),
                    sku: $('#sku').val(),
                    master_customer_id: $('#master_customer_id').val(),
                    vendor_id: $('#vendor_id').val(),
                    create_at: $('#created_at').val(),
                    expected_delivery_date: $('#expected_delivery_date').val(),
                    status: $('#status').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Create a hidden form for POST submission (so file download works)
                var form = $('<form>', {
                    action: '{{ route("admin.reports.productionExcel") }}',
                    method: 'POST',
                    target: '_blank'
                }).append($.map(data, function(v, k) {
                    return $('<input>', { type: 'hidden', name: k, value: v });
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            });
        });

    });
    
</script>

@endsection
