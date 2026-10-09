@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-truck text-warning mr-1"></i> Corporate Order Dispatches
            </h5>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.order-dispatch.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> Create Dispatch
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Dispatch No</label>
                <input type="text" id="order_dispatch_no" class="form-control form-control-sm erp-input" placeholder="Search Dispatch No...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Order No</label>
                <input type="text" id="main_order_id" class="form-control form-control-sm erp-input" placeholder="Search Order SKU...">
            </div>
            <div class="col-md-3 mb-2">
                <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Customer</label>
                <select id="customer_id" class="form-control form-control-sm erp-input select2">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Status</label>
                <select id="status" class="form-control form-control-sm erp-input">
                    <option value="">All Status</option>
                    <option value="1">Dispatched</option>
                    <option value="2">Complete</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-0 table-responsive">
            <table id="dispatchTable" class="table table-hover table-bordered erp-table mb-0">
                <thead>
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th>DISPATCH NO</th>
                        <th>BILL NO</th>
                        <th>ORDER NO</th>
                        <th>CUSTOMER</th>
                        <th class="text-center" width="8%">CARTONS</th>
                        <th width="15%">DATE</th>
                        <th class="text-center" width="10%">STATUS</th>
                        <th class="text-center" width="85px">ACTION</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('.select2').select2({ width: '100%' });

        let table = $('#dispatchTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            searching: false,
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
                {data: 'DT_RowIndex', name: 'id', className: 'text-center font-weight-bold'},
                {data: 'order_dispatch_no', name: 'order_dispatch_no', className: 'font-weight-bold text-dark'},
                {data: 'bill_number', name: 'bill_number'},
                {data: 'main_order_id', name: 'main_order_id', className: 'font-weight-bold'},                
                {data: 'customer_id', name: 'customer_id'}, 
                {data: 'total_quantity', name: 'total_quantity', className: 'text-center font-weight-bold'},
                {data: 'dispatch_date', name: 'dispatch_date'},              
                {data: 'status', name: 'status', className: 'text-center'},                
                {data: 'action', name: 'action', className: 'text-center text-nowrap'}
            ],
            language: {
                emptyTable: "No dispatches found",
                processing: '<i class="fas fa-spinner fa-spin text-warning mr-1"></i> Loading dispatches...'
            }
        });

        // Event Listeners for Filters
        $('#order_dispatch_no, #main_order_id, #customer_id, #status').on('keyup change', function() {
            table.draw();
        });

    });

    function deleteDispatch(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Dispatch?',
                text: 'This will refund customer balance and revert cartons to ready for dispatch.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('admin.order-dispatch.delete') }}?id=" + id;
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this dispatch? This will refund customer balance and revert cartons to ready for dispatch.')) {
                window.location.href = "{{ route('admin.order-dispatch.delete') }}?id=" + id;
            }
        }
    }
</script>
@endsection
