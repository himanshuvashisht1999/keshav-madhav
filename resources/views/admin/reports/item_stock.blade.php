@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-boxes text-primary mr-2"></i> Item Stock Report
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
            <div class="col-lg-4 col-md-6 col-sm-12 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Item SKU</label>
                <select name="sku" id="sku" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL ITEM SKU --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->sku }}">{{ $item->sku }}</option>
                    @endforeach
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
                        <th style="width: 60px;">#</th>
                        <th>Item SKU</th>
                        <th>Total Item</th>
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
                url: '{!! route('admin.reports.itemStockSkuList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                {data: 'sku', name: 'sku'},
                {data: 'total_quantity', name: 'total_quantity'},
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
            $('#sku').val('').trigger('change');
            oTable.draw();
        });

        $('#sku').on('change', function (e) {
            oTable.draw();
        });

        function exportExcel() {
            var data = {
                id: $('#id').val(),
                sku: $('#sku').val(),
                _token: '{{ csrf_token() }}'
            };

            var form = $('<form>', {
                action: '{{ route("admin.reports.itemStockSkuExcel") }}',
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
