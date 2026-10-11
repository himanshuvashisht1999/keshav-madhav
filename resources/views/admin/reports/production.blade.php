@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-industry text-primary mr-2"></i> Production Report
        </div>
        <div class="erp-header-actions">
            <button type="button" class="btn-erp btn-erp-outline" id="btnExportExcelTop" title="Export Excel">
                <i class="fas fa-file-excel text-success"></i> Excel
            </button>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order ID (SKU)</label>
                <input type="text" class="form-control erp-input" name="sku" id="sku" autocomplete="off" placeholder="Search SKU">
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Customer</label>
                <select name="master_customer_id" id="master_customer_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL CUSTOMERS --</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Order Date</label>
                <input type="date" class="form-control erp-input" name="created_at" id="created_at" autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> Exp. Delivery Date</label>
                <input type="date" class="form-control erp-input" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-info-circle mr-1"></i> Status</label>
                <select id="status" class="form-control erp-input">
                    <option value="">-- ALL STATUSES --</option>
                    <option value="1">In Progress</option>
                    <option value="2">Completed</option>
                </select>
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="btnApplyFilter" title="Apply Filter">
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
            <table id="customers" class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Expected Delivery Date</th>
                        <th style="width: 110px;">Status</th>
                        <th style="width: 90px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
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
                {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                {data: 'sku', name: 'sku'},
                {data: 'master_customer_id', name: 'master_customer_id'},
                {data: 'created_at', name: 'created_at'},
                {data: 'expected_delivery_date', name: 'expected_delivery_date'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ],
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>rt<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>"
        });

        $('#btnApplyFilter').on('click', function (e) {
            e.preventDefault();
            oTable.draw();
        });

        $('#btnResetFilter').on('click', function (e) {
            e.preventDefault();
            $('#sku').val('');
            $('#master_customer_id').val('').trigger('change');
            $('#created_at').val('');
            $('#expected_delivery_date').val('');
            $('#status').val('');
            oTable.draw();
        });

        $('#sku').on('keyup', function (e) {
            if (e.keyCode === 13) oTable.draw();
        });

        $('#master_customer_id, #expected_delivery_date, #created_at, #status').on('change', function () {
            oTable.draw();
        });

        function exportExcel() {
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
        }

        $('#btnExportExcelTop, #generateExcel').on('click', function (e) {
            e.preventDefault();
            exportExcel();
        });
    });
</script>
@endsection
