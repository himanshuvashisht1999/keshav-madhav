@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Order Dispatches</h1>
                    <small class="text-muted">Manage and track all dispatched orders</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.order-dispatch.create') }}" class="btn btn-primary px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Create Dispatch
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- FILTER CARD -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body bg-light rounded">
                    <div class="row">
                        
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted">Dispatcher No</label>
                            <input type="text" id="order_dispatch_no" class="form-control" placeholder="Enter Dispatch No...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted">Search Order No</label>
                            <input type="text" id="main_order_id" class="form-control" placeholder="Enter Order No...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted">Customer</label>
                            <select id="customer_id" class="form-control select2">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted">Status</label>
                            <select id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="1">Dispatched</option>
                                <option value="2">Complete</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="dispatchTable" class="table table-hover table-striped mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th>Dispatch No</th>
                                    <th>Order No</th>
                                    <th>Customer</th>
                                    <th class="text-center">Cartons</th>
                                    <th>Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<style>
    .card { border-radius: 8px; }
    .form-control { border-radius: 6px; }
    .table thead th { border: none; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
    .table tbody td { vertical-align: middle; font-size: 0.95rem; }
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 6px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    tr{
        background-color: #f9f9f9;
    }
</style>

<script>
    $(function () {
        
        let table = $('#dispatchTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false, // Disable default ordering if not handled
            searching: false, // Disable default search box
            lengthChange: false,
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.order-dispatch.indexList') !!}',
                data: function (d) {
                    d.order_dispatch_no = $('#order_dispatch_no').val();
                    d.main_order_id = $('#main_order_id').val();
                    d.customer_id = $('#customer_id').val();  
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted'},
                {data: 'order_dispatch_no', name: 'order_dispatch_no', className: 'font-weight-bold'},
                {data: 'main_order_id', name: 'main_order_id'},                
                {data: 'customer_id', name: 'customer_id'}, 
                {data: 'total_quantity', name: 'total_quantity', className: 'text-center'}, // Actually Total Cartons
                {data: 'dispatch_date', name: 'dispatch_date'},              
                {data: 'status', name: 'status', className: 'text-center'},                
                {data: 'action', name: 'action', className: 'text-right'}
            ],
            language: {
                emptyTable: "No dispatches found",
                processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
            }
        });

        // Event Listeners for Filters
        $('#order_dispatch_no, #main_order_id, #customer_id, #status').on('keyup change', function() {
            table.draw();
        });

    });
</script>
@endsection
