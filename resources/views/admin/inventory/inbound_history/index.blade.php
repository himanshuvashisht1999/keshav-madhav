@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Inbound Stock Session History</h1>
                <p class="erp-page-subtitle m-0">Track and view all manual stock entry sessions</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> New stock generate
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to inventory
                </a>
            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="erp-card mb-2 p-2">
            <div class="row align-items-end">
                <div class="col-md-3 mb-1">
                    <label class="erp-label mb-1">Start date</label>
                    <input type="date" id="start_date" class="form-control form-control-sm erp-input">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-label mb-1">End date</label>
                    <input type="date" id="end_date" class="form-control form-control-sm erp-input">
                </div>
                <div class="col-md-2 mb-1">
                    <button id="reset_filters" class="btn-erp btn-erp-secondary w-100" style="height: 29px;">
                        <i class="fas fa-undo mr-1"></i> Reset filters
                    </button>
                </div>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="entryHistoryTable" class="erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Date</th>
                            <th>Session ID</th>
                            <th style="text-align: center;">Total boxes</th>
                            <th style="width: 100px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        const table = $('#entryHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.inventory.inbound_history.list') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center font-weight-bold', orderable: false, searchable: false },
                { 
                    data: 'date', 
                    name: 'date', 
                    render: function(data) {
                        return `<div class="font-weight-bold text-dark">${data}</div>`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'id',
                    render: function(data) {
                        return `<span class="badge badge-light border text-dark font-weight-bold">SESSION-${data}</span>`;
                    }
                },
                { 
                    data: 'total_boxes', 
                    name: 'total_boxes',
                    searchable: false,
                    orderable: false,
                    className: 'text-center',
                    render: (d) => `<span class="badge badge-light border font-weight-bold px-2 py-1">${d || 0} Boxes</span>`
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        // Filter triggers
        $('#start_date, #end_date').on('change keyup', function () {
            table.draw();
        });

        // Reset filter
        $('#reset_filters').on('click', function () {
            $('#start_date, #end_date').val('');
            table.draw();
        });
    });
</script>
@endpush
@endsection
