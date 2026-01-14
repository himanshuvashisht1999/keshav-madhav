@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Order Summary Report</h1>
                    <small class="text-muted">360-degree view of all orders</small>
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
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-muted">Search Order No</label>
                            <input type="text" id="order_no" class="form-control" placeholder="Enter Order No...">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-muted">Customer</label>
                            <select id="customer_id" class="form-control select2">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{$customer->id}}">{{$customer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="searchBtn">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="reportTable" class="table table-hover table-striped mb-0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th width="5%" class="text-center">#</th>
                                    <th>Order No</th>
                                    <th>Customer</th>
                                    <th>Order Date</th>
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
</style>

<script>
    $(function () {
        let table = $('#reportTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
            lengthChange: false,
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.report.order-summary.indexList') !!}',
                data: function (d) {
                    d.order_no = $('#order_no').val();
                    d.customer_id = $('#customer_id').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted'},
                {data: 'sku', name: 'sku', className: 'font-weight-bold'},
                {data: 'customer_name', name: 'customer.name'},
                {data: 'created_at', name: 'created_at'},
                {data: 'status', name: 'status', className: 'text-center', render: function(data) {
                    return '<span class="badge badge-info">Active</span>'; // Placeholder
                }},
                {data: 'action', name: 'action', className: 'text-right'}
            ]
        });

        $('#searchBtn').on('click', function() {
            table.draw();
        });
        
        $('#order_no, #customer_id').on('keyup change', function() {
            table.draw();
        });
    });
</script>
@endsection
