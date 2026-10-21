@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-history text-primary"></i> Inbound Stock Session History
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total sessions</span>
                    <span class="hud-stat-value text-success" id="header_total_sessions">-</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total boxes</span>
                    <span class="hud-stat-value text-primary" id="header_total_boxes">-</span>
                </div>
                <a href="{{ route('admin.inventory.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> New Stock Generate
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" id="start_date" class="form-control erp-input">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" id="end_date" class="form-control erp-input">
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
                <table id="entryHistoryTable" class="erp-table table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            <th>Date</th>
                            <th>Session ID</th>
                            <th class="text-center" style="width: 140px;">Total boxes</th>
                            <th style="width: 100px;" class="text-center">Action</th>
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

        table.on('xhr.dt', function(e, settings, json, xhr) {
            if (json) {
                if (json.total_sessions !== undefined) $('#header_total_sessions').text(Number(json.total_sessions).toLocaleString());
                if (json.total_boxes !== undefined) $('#header_total_boxes').text(Number(json.total_boxes).toLocaleString());
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
