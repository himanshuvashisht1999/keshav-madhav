@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="text-center">List of Corporate Orders <span id="total_pcs_count" class="badge badge-info ml-2" style="font-size: 18px; vertical-align: middle;"></span></h1>
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
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control" name="po_number" id="po_num_search" autocomplete="off" placeholder="PO #">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                                    </td>
                                    <td>
                                        <select name="master_customer_id" id="master_customer_id"
                                            class="form-control select2" style="width: 100%;">
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
                                        <input type="date" class="form-control mb-1" name="created_at" id="created_at"
                                            autocomplete="off">
                                        <div class="row no-gutters">
                                            <div class="col-6 pr-1">
                                                <select id="created_month" class="form-control form-control-sm">
                                                    <option value="">Month</option>
                                                    @for($m=1; $m<=12; $m++)
                                                        <option value="{{$m}}">{{date('M', mktime(0,0,0,$m,1))}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select id="created_year" class="form-control form-control-sm">
                                                    <option value="">Year</option>
                                                    @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                                        <option value="{{$y}}">{{$y}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control mb-1" name="expected_delivery_date"
                                            id="expected_delivery_date" autocomplete="off">
                                        <div class="row no-gutters">
                                            <div class="col-6 pr-1">
                                                <select id="expected_delivery_month" class="form-control form-control-sm">
                                                    <option value="">Month</option>
                                                    @for($m=1; $m<=12; $m++)
                                                        <option value="{{$m}}">{{date('M', mktime(0,0,0,$m,1))}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <select id="expected_delivery_year" class="form-control form-control-sm">
                                                    <option value="">Year</option>
                                                    @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                                        <option value="{{$y}}">{{$y}}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select id="assignment_status" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="not_assigned">Not Assigned</option>
                                        </select>
                                    </td>
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
                                    <th>PO No</th>
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
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.product_order.indexListOrder') !!}',
                    data: function (d) {
                        d.id = $('#id').val();
                        d.sku = $('#sku').val();
                        d.po_number = $('#po_num_search').val();
                        d.master_customer_id = $('#master_customer_id').val();
                        d.order_type = $('#order_type').val();
                        d.created_at = $('#created_at').val();
                        d.created_month = $('#created_month').val();
                        d.created_year = $('#created_year').val();
                        d.expected_delivery_date = $('#expected_delivery_date').val();
                        d.expected_delivery_month = $('#expected_delivery_month').val();
                        d.expected_delivery_year = $('#expected_delivery_year').val();
                        d.status = $('#status').val();
                        d.assignment_status = $('#assignment_status').val();

                    },
                    orderable: false
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'po_number', name: 'po_number' },
                    { data: 'sku', name: 'sku' },
                    { data: 'master_customer_id', name: 'master_customer_id' },
                    { data: 'order_type', name: 'order_type' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'expected_delivery_date', name: 'expected_delivery_date' },
                    { data: 'total_pcs', name: 'total_pcs' },
                    { data: 'dispatch_pcs', name: 'dispatch_pcs' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lBfrtip',
                drawCallback: function (settings) {
                    var api = this.api();
                    var json = api.ajax.json();
                    if (json && json.total_pieces_sum !== undefined) {
                        $('#total_pcs_count').text(' (Total: ' + json.total_pieces_sum + ' Pcs)');
                    }
                },
                buttons: [
                    {
                        text: 'Excel',
                        className: 'btn-datatable btn-success ml-2',
                        action: function (e, dt, node, config) {
                            var params = dt.ajax.params();
                            var qs = $.param(params);
                            window.location.href = "{{ route('admin.product_order.exportOrders') }}?export_type=excel&" + qs;
                        }
                    },
                    {
                        text: 'PDF',
                        className: 'btn-datatable btn-danger ml-2',
                        action: function (e, dt, node, config) {
                            var params = dt.ajax.params();
                            var qs = $.param(params);
                            window.location.href = "{{ route('admin.product_order.exportOrders') }}?export_type=pdf&" + qs;
                        }
                    },
                    {
                        text: 'Create Corporate Order',
                        className: 'btn-datatable btn-primary ml-2',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.sales_order.create') }}";
                        }
                    },
                    {
                        text: 'Create Domestic Order',
                        className: 'btn-datatable btn-info ml-2',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.sales_order.create_domestic') }}";
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
            $('#po_num_search').on('keyup', function (e) {
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
            $('#expected_delivery_date, #expected_delivery_month, #expected_delivery_year').on('change', function (e) {
                oTable.draw();
                e.preventDefault();
            });
            $('#created_at, #created_month, #created_year').on('change', function (e) {
                oTable.draw();
                e.preventDefault();
            });
            $('#status').on('change', function (e) {
                oTable.draw();
                e.preventDefault();
            });
            $('#assignment_status').on('change', function (e) {
                oTable.draw();
                e.preventDefault();
            });

        });

        function deleteOrder(id) {
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
                    window.location.href = "{{ route('admin.product_order.deleteOrderMain', ['id' => '']) }}" + id;
                }
            });
        }




    </script>

@endsection