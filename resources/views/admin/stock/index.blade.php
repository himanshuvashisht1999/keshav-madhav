@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Fabric Stock SKU: <span class="badge erp-badge-yellow ml-2">{{ $fabrics->sku }}</span>
        </div>
        <div class="erp-header-actions">
            <button id="generatePDFBtn" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </button>
            <button id="generateExcelBtn" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-excel text-success"></i> Excel
            </button>
            <a href="{{ route('admin.stock.fabricQuantityExcel') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-download"></i> Fabric Quantity
            </a>
            <a href="{{ route('admin.stock.fabricIndex') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <input type="hidden" name="sku" id="sku" value="{{$fabrics->sku}}">
        <div class="row align-items-end">
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Date</label>
                <input type="date" class="form-control erp-input" name="date" id="date" autocomplete="off">
            </div>
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-ruler mr-1"></i> Meter</label>
                <input type="text" class="form-control erp-input" name="meter" id="meter" placeholder="Filter meter..." autocomplete="off">
            </div>
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Unique No</label>
                <input type="text" class="form-control erp-input" name="unique_number" id="unique_number" placeholder="Unique No..." autocomplete="off">
            </div>
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Batch No</label>
                <input type="text" class="form-control erp-input" name="batch_no" id="batch_no" placeholder="Batch No..." autocomplete="off">
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="btnApplyFilter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetFilter">
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
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Date</th>
                        <th>Meter</th>
                        <th>Unique No</th>
                        <th>Batch No</th>
                        <th style="width: 100px;" class="text-center">Action</th>
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
                url: '{!! route('admin.stock.indexList') !!}',
                data: function (d) {
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.meter = $('#meter').val();
                    d.unique_number = $('#unique_number').val();
                    d.batch_no = $('#batch_no').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', className: 'text-center'},
                {data: 'date', name: 'date'},
                {data: 'meter', name: 'meter'},
                {data: 'unique_number', name: 'unique_number'},
                {data: 'batch_no', name: 'batch_no'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ],
            dom: '<"top"lf>rt<"bottom"ip><"clear">'
        });

        $('#btnApplyFilter').on('click', function (e) {
            oTable.draw();
        });

        $('#date, #meter, #unique_number, #batch_no').on('change keyup', function (e) {
            oTable.draw();
        });

        $('#btnResetFilter').on('click', function (e) {
            $('#date').val('');
            $('#meter').val('');
            $('#unique_number').val('');
            $('#batch_no').val('');
            oTable.draw();
        });

        $('#generatePDFBtn').on('click', function (e) {
            e.preventDefault();
            var data = {
                sku: $('#sku').val(),
                date: $('#date').val(),
                meter: $('#meter').val(),
                unique_number: $('#unique_number').val(),
                batch_no: $('#batch_no').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.stock.generatePdf") }}',
                method: 'POST',
                target: '_blank'
            }).append($.map(data, function(v, k) {
                return $('<input>', { type: 'hidden', name: k, value: v });
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });

        $('#generateExcelBtn').on('click', function (e) {
            e.preventDefault();
            var data = {
                sku: $('#sku').val(),
                date: $('#date').val(),
                meter: $('#meter').val(),
                unique_number: $('#unique_number').val(),
                batch_no: $('#batch_no').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.stock.generateExcel") }}',
                method: 'POST',
                target: '_blank'
            }).append($.map(data, function(v, k) {
                return $('<input>', { type: 'hidden', name: k, value: v });
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });
    });
</script>

@endsection
