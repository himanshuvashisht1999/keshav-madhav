@extends('admin.layouts.app')
@section('title', 'Unit Movement & Loss Report')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-chart-line text-primary"></i> Unit Movement & Loss Report
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-boxes"></i> Inventory Overview
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-exchange-alt mr-1"></i> Movement Type</label>
                    <select id="type_filter" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL TYPES --</option>
                        <option value="dead">Dead stock (Damage)</option>
                        <option value="sampling">Sampling</option>
                        <option value="debit">Unit debit</option>
                    </select>
                </div>
                <div class="col-md-4 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Design / Order No.</label>
                    <input type="text" id="search_filter" class="form-control erp-input" placeholder="Search design or order..." autocomplete="off">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button id="reset_filters" class="btn-erp btn-erp-outline">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="outflowTable" class="erp-table table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>Type</th>
                            <th>Order no.</th>
                            <th>Product / color</th>
                            <th>Size</th>
                            <th class="text-center" style="width: 70px;">Qty</th>
                            <th>Storage (wh/rack)</th>
                            <th>Amount / responsible</th>
                            <th style="width: 140px;">Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        let table = $('#outflowTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('admin.inventory.outflow.list') }}",
                data: function (d) {
                    d.type = $('#type_filter').val();
                    d.search = $('#search_filter').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center text-muted font-weight-bold' },
                { data: 'type_label' },
                { data: 'order_no' },
                { data: 'product_name' },
                { data: 'size' },
                { data: 'quantity_display', className: 'text-center' },
                { data: 'storage' },
                { 
                    data: null, 
                    render: function(data) {
                        if (data.type === 'debit') {
                            return `<div>${data.amount_display}</div><div class="small text-danger mt-1">Responsible: ${data.responsible}</div>`;
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                { 
                    data: 'created_at', 
                    render: function(data) {
                        return moment(data).format('DD-MM-YYYY HH:mm');
                    }
                }
            ],
            language: {
                processing: '<i class="fas fa-spinner fa-spin fa-2x text-success"></i>'
            }
        });

        $('#type_filter, #search_filter').on('change keyup', function () {
            table.ajax.reload();
        });

        $('#reset_filters').on('click', function () {
            $('#type_filter').val('').trigger('change');
            $('#search_filter').val('');
            table.ajax.reload();
        });
    });
</script>
@endsection
