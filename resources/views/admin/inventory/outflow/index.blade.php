@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Unit Movement & Loss Report</h1>
                        <small class="text-muted">Track Dead Stock, Sampling, and Unit Debits across production</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Movement Type</label>
                                <select id="type_filter" class="form-control select2">
                                    <option value="">All Types</option>
                                    <option value="dead">Dead Stock (Damage)</option>
                                    <option value="sampling">Sampling</option>
                                    <option value="debit">Unit Debit</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Design / Order No.</label>
                                <input type="text" id="search_filter" class="form-control" placeholder="Search Design or Order...">
                            </div>
                            <div class="col-md-2">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="outflowTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Type</th>
                                        <th class="py-3">Order No.</th>
                                        <th class="py-3">Product / Color</th>
                                        <th class="py-3">Size</th>
                                        <th class="py-3 text-center">Qty</th>
                                        <th class="py-3">Storage (Wh/Rack)</th>
                                        <th class="py-3">Amount/Responsible</th>
                                        <th class="py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }
        .badge {
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }
    </style>

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
                    { data: 'DT_RowIndex', className: 'text-center text-muted' },
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
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
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
