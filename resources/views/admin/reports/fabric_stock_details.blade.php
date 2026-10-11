@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary mr-2"></i> Fabric Stock SKU: <span class="badge erp-badge-yellow ml-2">{{ $fabrics->sku }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.reports.fabricStock') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to SKU List
            </a>
            <button type="button" class="btn-erp btn-erp-outline" id="btnExportExcelTop" title="Export Excel">
                <i class="fas fa-file-excel text-success"></i> Excel
            </button>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <input type="hidden" name="sku" id="sku" value="{{$fabrics->sku}}">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Date</label>
                <input type="date" class="form-control erp-input" name="date" id="date" autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-ruler mr-1"></i> Meters</label>
                <input type="text" class="form-control erp-input" name="meter" id="meter" autocomplete="off" placeholder="Search Meters">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Unique No</label>
                <input type="text" class="form-control erp-input" name="unique_number" id="unique_number" autocomplete="off" placeholder="Search Unique No">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Batch No</label>
                <input type="text" class="form-control erp-input" name="batch_no" id="batch_no" autocomplete="off" placeholder="Search Batch No">
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
                        <th style="width: 60px;">#</th>
                        <th>Date</th>
                        <th>Meter</th>
                        <th>Unique No</th>
                        <th>Batch No</th>
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
                url: '{!! route('admin.reports.fabricStockList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.meter = $('#meter').val();
                    d.unique_number = $('#unique_number').val();
                    d.batch_no = $('#batch_no').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                {data: 'date', name: 'date'},
                {data: 'meter', name: 'meter'},
                {data: 'unique_number', name: 'unique_number'},
                {data: 'batch_no', name: 'batch_no'}
            ],
            dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6'f>>rt<'row mt-2'<'col-sm-5'i><'col-sm-7'p>>"
        });

        $('#btnApplyFilter').on('click', function (e) {
            e.preventDefault();
            oTable.draw();
        });

        $('#btnResetFilter').on('click', function (e) {
            e.preventDefault();
            $('#date').val('');
            $('#meter').val('');
            $('#unique_number').val('');
            $('#batch_no').val('');
            oTable.draw();
        });

        $('#date').on('change', function () {
            oTable.draw();
        });

        $('#meter, #unique_number, #batch_no').on('keyup', function (e) {
            if (e.keyCode === 13) oTable.draw();
        });

        function exportExcel() {
            var data = {
                id: $('#id').val(),
                sku: $('#sku').val(),
                date: $('#date').val(),
                meter: $('#meter').val(),
                unique_number: $('#unique_number').val(),
                batch_no: $('#batch_no').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.reports.fabricStockExcel") }}',
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
