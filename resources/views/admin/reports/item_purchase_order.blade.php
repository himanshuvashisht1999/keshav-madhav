@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-boxes text-primary mr-2"></i> Item Purchase Order Report
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
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> PO No. (SKU)</label>
                <input type="text" class="form-control erp-input" name="sku" id="sku" autocomplete="off" placeholder="Search PO #">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> PO Date</label>
                <input type="date" class="form-control erp-input" name="date" id="date" autocomplete="off">
            </div>
            <div class="col-lg-3 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-truck mr-1"></i> Vendor</label>
                <select class="form-control select2 erp-input" name="vendor_id" id="vendor_id" autocomplete="off" style="width: 100%;">
                    <option value="">-- ALL VENDORS --</option>
                    @foreach($vendors as $single_data)
                        <option value="{{$single_data->id}}">{{$single_data->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> Exp. Delivery Date</label>
                <input type="date" class="form-control erp-input" name="delivery_date" id="delivery_date" autocomplete="off">
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-day mr-1"></i> Date Filter Type & Range</label>
                <div class="d-flex">
                    <select id="selected_field" name="selected_field" class="form-control erp-input mr-1" style="width: 48%;">
                        <option value="date">PO Date</option>
                        <option value="delivery_date">Delivery Date</option>
                    </select>
                    <input type="text" id="report-range" name="date_range" class="form-control erp-input" placeholder="Select Range" autocomplete="off" style="width: 52%;">
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
            <table id="customers" class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>PO No.</th>
                        <th>Purchase Order Date</th>
                        <th>Vendor</th>
                        <th>Expected Delivery Date</th>
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
                url: '{!! route('admin.reports.itemPurchaseOrderList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.delivery_date = $('#delivery_date').val();
                    d.selected_field = $('#selected_field').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
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
            $('#date').val('');
            $('#vendor_id').val('').trigger('change');
            $('#delivery_date').val('');
            $('#selected_field').val('date');
            $('#report-range').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            oTable.draw();
        });

        $('#sku').on('keyup', function (e) {
            if (e.keyCode === 13) oTable.draw();
        });

        $('#date, #vendor_id, #delivery_date, #selected_field').on('change', function () {
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
                vendor_id: $('#vendor_id').val(),
                time: $('#date').val(),
                delivery_date: $('#delivery_date').val(),
                selected_field: $('#selected_field').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.reports.itemPurchaseOrderExcel") }}',
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
