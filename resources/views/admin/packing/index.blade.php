@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper erp-page p-2">
        <!-- 1. HEADER SECTION -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-box-open text-primary"></i> Packing Dashboard
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.packing.reworkList') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-tools text-warning"></i> Defect / Rework List
                </a>
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-yellow">
                    <i class="fas fa-plus"></i> Start New Packing
                </a>
            </div>
        </div>

        <!-- 2. FILTER BAR -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No</label>
                    <input type="text" id="order_no" class="form-control erp-input" placeholder="Search Order...">
                </div>
                <div class="col-md col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Customer Name</label>
                    <input type="text" id="customer_name" class="form-control erp-input" placeholder="Search Customer...">
                </div>
                <div class="col-md col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" id="start_date" class="form-control erp-input">
                </div>
                <div class="col-md col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" id="end_date" class="form-control erp-input">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button id="resetFilters" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. TABLE CARD -->
        <div class="erp-card">
            <div class="table-responsive">
                <table id="packingTable" class="table erp-table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Slip ID</th>
                            <th>Packing Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="10%">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            let table = $('#packingTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                ajax: {
                    url: '{!! route('admin.packing.indexList') !!}',
                    data: function (d) {
                        d.order_no = $('#order_no').val();
                        d.customer_name = $('#customer_name').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted' },
                    { data: 'order_no', name: 'order_no', className: 'font-weight-bold text-dark' },
                    { data: 'customer', name: 'customer' },
                    { data: 'slip_id', name: 'slip_id', className: 'font-weight-bold' },
                    { data: 'packing_date', name: 'packing_date' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', className: 'text-center' }
                ],
                language: {
                    emptyTable: "No packing sessions found",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x" style="color: #05421c;"></i>'
                }
            });

            // Trigger filter
            $('#order_no, #customer_name, #start_date, #end_date').on('keyup change', function() {
                table.draw();
            });

            // Reset filter
            $('#resetFilters').on('click', function() {
                $('#order_no, #customer_name, #start_date, #end_date').val('');
                table.draw();
            });
        });
    </script>
@endsection