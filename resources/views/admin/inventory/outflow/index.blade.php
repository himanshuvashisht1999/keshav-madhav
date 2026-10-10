@extends('admin.layouts.app')
@section('title', 'Unit Movement & Loss Report')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Unit Movement & Loss Report</h1>
                <p class="erp-page-subtitle">Track dead stock, sampling, and unit debits across production</p>
            </div>
            <div>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-boxes mr-1"></i> Inventory Overview
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="erp-card mb-2 p-2">
            <div class="form-row align-items-end">
                <div class="col-md-3 mb-2">
                    <label class="erp-label">Movement type</label>
                    <select id="type_filter" class="form-control form-control-sm erp-input select2">
                        <option value="">All types</option>
                        <option value="dead">Dead stock (Damage)</option>
                        <option value="sampling">Sampling</option>
                        <option value="debit">Unit debit</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="erp-label">Design / order no.</label>
                    <input type="text" id="search_filter" class="form-control form-control-sm erp-input" placeholder="Search design or order...">
                </div>
                <div class="col-md-2 mb-2">
                    <button id="reset_filters" class="btn-erp btn-erp-secondary w-100">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="outflowTable" class="table erp-table table-bordered table-hover mb-0">
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
