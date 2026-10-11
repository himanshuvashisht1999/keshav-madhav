@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Available Fabric Stock
        </div>
        <div class="erp-header-actions">
            <button id="generateExcelBtn" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-excel text-success"></i> Excel Report
            </button>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <div class="row align-items-end">
            <div class="col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Fabric SKU</label>
                <select name="sku" id="sku" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL FABRIC SKU --</option>
                    @foreach($fabrics as $fabric)
                        <option value="{{ $fabric->sku }}">{{ $fabric->sku }}</option>
                    @endforeach
                </select>
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
                        <th>SKU</th>
                        <th>Total Fabric (Meters)</th>
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
                url: '{!! route('admin.stock.fabricIndexList') !!}',
                data: function (d) {
                    d.sku = $('#sku').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', className: 'text-center'},
                {data: 'sku', name: 'sku'},
                {data: 'total_meter', name: 'total_meter'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ],
            dom: '<"top"lf>rt<"bottom"ip><"clear">'
        });

        $('#sku').on('change', function (e) {
            oTable.draw();
        });

        $('#btnApplyFilter').on('click', function (e) {
            oTable.draw();
        });

        $('#btnResetFilter').on('click', function (e) {
            $('#sku').val('').trigger('change');
            oTable.draw();
        });

        $('#generateExcelBtn').on('click', function (e) {
            e.preventDefault();
            var data = {
                sku: $('#sku').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.reports.fabricStockSkuExcel") }}',
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
