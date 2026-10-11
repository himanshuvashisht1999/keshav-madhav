@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-chart-pie text-primary mr-2"></i> Order Summary Report
        </div>
        <div class="erp-header-actions">
            <!-- Space for actions/export if needed -->
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No (SKU)</label>
                <input type="text" id="order_no" class="form-control erp-input" placeholder="Enter Order No...">
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Customer</label>
                <select id="customer_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL CUSTOMERS --</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="searchBtn" title="Apply Filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetFilter" title="Reset Filters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="reportTable" class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

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
                    return '<span class="badge erp-badge-yellow">Active</span>';
                }},
                {data: 'action', name: 'action', className: 'text-center'}
            ]
        });

        $('#searchBtn').on('click', function() {
            table.draw();
        });

        $('#btnResetFilter').on('click', function() {
            $('#order_no').val('');
            $('#customer_id').val('').trigger('change');
            table.draw();
        });
        
        $('#order_no').on('keyup', function(e) {
            if (e.keyCode === 13) table.draw();
        });

        $('#customer_id').on('change', function() {
            table.draw();
        });
    });
</script>
@endsection
