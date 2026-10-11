@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-tasks text-primary mr-2"></i> {{ $stage_data->name }} Stage Report
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
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No</label>
                <input type="text" class="form-control erp-input" id="sku" placeholder="Order No">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Product SKU</label>
                <input type="text" class="form-control erp-input" id="order_product_id" placeholder="Product SKU">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-random mr-1"></i> From Stage</label>
                <select id="from_stage_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL STAGES --</option>
                    @foreach($product_stage as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-2 col-sm-4 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calculator mr-1"></i> Qty</label>
                <input type="text" class="form-control erp-input" id="quantity" placeholder="Qty">
            </div>
            <div class="col-lg-1 col-md-2 col-sm-4 mb-1">
                <label class="erp-filter-label"><i class="fas fa-balance-scale mr-1"></i> Remain</label>
                <input type="text" class="form-control erp-input" id="remaining_quantity" placeholder="Remain">
            </div>
            <div class="col-lg-1 col-md-2 col-sm-4 mb-1">
                <label class="erp-filter-label"><i class="fas fa-info-circle mr-1"></i> Status</label>
                <select id="status" class="form-control erp-input">
                    <option value="">-- ALL --</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-day mr-1"></i> Date Filter Type & Range</label>
                <div class="d-flex">
                    <select id="selected_field" name="selected_field" class="form-control erp-input mr-1" style="width: 48%;">
                        <option value="created_at">Received Date</option>
                        <option value="updated_at">Delivered Date</option>
                    </select>
                    <input type="text" id="report-range" name="date_range" class="form-control erp-input" placeholder="Date Range" autocomplete="off" style="width: 52%;">
                    <input type="hidden" name="start_date" id="start_date">
                    <input type="hidden" name="end_date" id="end_date">
                </div>
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
            <table id="order_stage" class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Order No</th>
                        <th>Product SKU</th>
                        <th>From Stage</th>
                        <th>Qty</th>
                        <th>Remain</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th>Delivered</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS Section -->
<script>
$(function () {
    var oTable = $('#order_stage').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: '{!! route('admin.reports.stagesList',['stage_id' => $stage_data->id]) !!}',
            data: function (d) {
                d.sku = $('#sku').val();
                d.order_product_id = $('#order_product_id').val();
                d.from_stage_id = $('#from_stage_id').val();
                d.quantity = $('#quantity').val();
                d.remaining_quantity = $('#remaining_quantity').val();
                d.status = $('#status').val();
                d.selected_field = $('#selected_field').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', width: '5%', orderable: false, searchable: false },
            { data: 'sku', name: 'sku', width: '10%' },
            { data: 'order_product_id', name: 'order_product_id', width: '15%' },
            { data: 'from_stage_id', name: 'from_stage_id', width: '12%' },
            { data: 'quantity', name: 'quantity', width: '8%' },
            { data: 'remaining_quantity', name: 'remaining_quantity', width: '8%' },
            { data: 'status', name: 'status', width: '10%' },
            { data: 'created_at', name: 'created_at', width: '16%' },
            { data: 'updated_at', name: 'updated_at', width: '16%' }
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
        $('#order_product_id').val('');
        $('#from_stage_id').val('').trigger('change');
        $('#quantity').val('');
        $('#remaining_quantity').val('');
        $('#status').val('');
        $('#selected_field').val('created_at');
        $('#report-range').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        oTable.draw();
    });

    $('#sku, #order_product_id, #quantity, #remaining_quantity').on('keyup', function (e) {
        if (e.keyCode === 13) oTable.draw();
    });

    $('#from_stage_id, #status, #selected_field').on('change', function () {
        oTable.draw();
    });

    if ($.fn.daterangepicker) {
        $('#report-range').daterangepicker({
            autoUpdateInput: false,
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
                'This Week': [moment().startOf('week'), moment().endOf('week')],
                'Last Week': [
                    moment().subtract(1, 'week').startOf('week'),
                    moment().subtract(1, 'week').endOf('week')
                ],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
            startDate: moment().subtract(1, 'month'),
            endDate: moment(),
        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });

        $('#report-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + " to " + picker.endDate.format('YYYY-MM-DD'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            oTable.draw(); 
        });

        $('#report-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#start_date').val('');
            $('#end_date').val('');
            oTable.draw(); 
        });
    }

    function exportExcel() {
        var data = {
            id: $('#id').val(),
            sku: $('#sku').val(),
            order_product_id: $('#order_product_id').val(),
            from_stage_id: $('#from_stage_id').val(),
            quantity: $('#quantity').val(),   
            remaining_quantity: $('#remaining_quantity').val(),
            status: $('#status').val(),
            selected_field: $('#selected_field').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            _token: '{{ csrf_token() }}'
        };

        var form = $('<form>', {
            action: '{{ route("admin.reports.stagesExcel" ,['stage_id' => $stage_data->id]) }}',
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
