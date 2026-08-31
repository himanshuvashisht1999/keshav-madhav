@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-trash-restore-alt text-danger mr-2"></i> Deletion Audit Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Reports</li>
                        <li class="breadcrumb-item active">Deletion Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Filter Deletion Records</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.reports.deletionLogs') }}" class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6 form-group">
                            <label class="font-weight-semibold">Module:</label>
                            <select name="module" class="form-control select2">
                                <option value="">-- All Modules --</option>
                                @foreach($modules as $mod)
                                    <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label class="font-weight-semibold">Record ID:</label>
                            <input type="number" name="record_id" class="form-control" placeholder="e.g. 127" value="{{ request('record_id') }}">
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label class="font-weight-semibold">Deleted By:</label>
                            <select name="deleted_by" class="form-control select2">
                                <option value="">-- All Users --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ request('deleted_by') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label class="font-weight-semibold">From Date:</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2 col-sm-6 form-group">
                            <label class="font-weight-semibold">To Date:</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 col-sm-6 form-group">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-history text-secondary mr-1"></i> Deleted History 
                        <span class="badge badge-info ml-2">{{ $logs->total() }} Records</span>
                    </h3>
                    @if(request()->hasAny(['module', 'record_id', 'deleted_by', 'date_from', 'date_to', 'search']))
                        <a href="{{ route('admin.reports.deletionLogs') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times mr-1"></i> Clear Filters
                        </a>
                    @endif
                </div>

                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 80px;">Log ID</th>
                                <th>Module</th>
                                <th>Original Record ID</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                                <th style="width: 140px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $badgeClass = 'badge-secondary';
                                    if(str_contains(strtolower($log->module), 'dispatch')) $badgeClass = 'badge-warning';
                                    elseif(str_contains(strtolower($log->module), 'order')) $badgeClass = 'badge-primary';
                                    elseif(str_contains(strtolower($log->module), 'po')) $badgeClass = 'badge-info';
                                    elseif(str_contains(strtolower($log->module), 'pack')) $badgeClass = 'badge-success';
                                    elseif(str_contains(strtolower($log->module), 'payment')) $badgeClass = 'badge-danger';
                                @endphp
                                <tr>
                                    <td class="font-weight-bold text-muted">#{{ $log->id }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }} px-2 py-1 font-size-12">
                                            {{ $log->module }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">#{{ $log->record_id ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        {{ $log->user->name ?? 'System / Unknown' }}
                                    </td>
                                    <td>
                                        <i class="far fa-clock text-muted mr-1"></i>
                                        {{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info view-payload-btn" 
                                                data-id="{{ $log->id }}"
                                                title="View Snapshot / Payload">
                                            <i class="fas fa-eye mr-1"></i> View Data
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                        <p class="mb-0 font-weight-semibold">No deletion logs found.</p>
                                        <small>Deleted records across modules will automatically appear here.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                        </span>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<!-- Modal for Viewing Deleted Payload Snapshot -->
<div class="modal fade" id="payloadModal" tabindex="-1" role="dialog" aria-labelledby="payloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title font-weight-bold" id="payloadModalLabel">
                    <i class="fas fa-database text-warning mr-2"></i> Deleted Record Snapshot
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Meta Details Header -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body bg-white">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="text-muted small text-uppercase font-weight-bold">Module</label>
                                <h6 class="font-weight-bold text-primary" id="modalModule">-</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small text-uppercase font-weight-bold">Record ID</label>
                                <h6 class="font-weight-bold" id="modalRecordId">-</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small text-uppercase font-weight-bold">Deleted By</label>
                                <h6 class="font-weight-bold" id="modalDeletedBy">-</h6>
                            </div>
                            <div class="col-md-3">
                                <label class="text-muted small text-uppercase font-weight-bold">Deleted At</label>
                                <h6 class="font-weight-bold text-danger" id="modalDeletedAt">-</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nav Tabs -->
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="pills-summary-tab" data-toggle="pill" href="#pills-summary" role="tab">
                            <i class="fas fa-table mr-1"></i> Structured Summary
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="pills-json-tab" data-toggle="pill" href="#pills-json" role="tab">
                            <i class="fas fa-code mr-1"></i> Raw JSON Payload
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Tab 1: Structured Summary -->
                    <div class="tab-pane fade show active" id="pills-summary" role="tabpanel">
                        <div id="structuredContent" class="card shadow-sm p-3 bg-white">
                            <!-- Injected dynamically via JavaScript -->
                        </div>
                    </div>

                    <!-- Tab 2: Raw JSON -->
                    <div class="tab-pane fade" id="pills-json" role="tabpanel">
                        <div class="card shadow-sm p-3 bg-white">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Full data captured at time of deletion:</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="copyJsonBtn">
                                    <i class="fas fa-copy mr-1"></i> Copy JSON
                                </button>
                            </div>
                            <pre id="rawJsonCode" class="p-3 bg-dark text-success rounded" style="max-height: 450px; overflow-y: auto; font-family: monospace; font-size: 13px;"></pre>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.view-payload-btn').on('click', function() {
        var logId = $(this).data('id');
        var url = "{{ url('admin/reports/deletion-logs') }}/" + logId;

        // Reset modal content
        $('#modalModule').text('Loading...');
        $('#modalRecordId').text('...');
        $('#modalDeletedBy').text('...');
        $('#modalDeletedAt').text('...');
        $('#structuredContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
        $('#rawJsonCode').text('Loading payload...');

        $('#payloadModal').modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.status && res.data) {
                    var data = res.data;
                    $('#modalModule').text(data.module);
                    $('#modalRecordId').text('#' + (data.record_id || 'N/A'));
                    $('#modalDeletedBy').text(data.deleted_by);
                    $('#modalDeletedAt').text(data.created_at);

                    var payload = data.payload;
                    var jsonString = JSON.stringify(payload, null, 4);
                    $('#rawJsonCode').text(jsonString);

                    // Build Structured Summary View
                    var html = '';
                    if (typeof payload === 'object' && payload !== null) {
                        html += '<div class="table-responsive"><table class="table table-bordered table-striped">';
                        html += '<thead class="thead-dark"><tr><th style="width: 250px;">Field / Section</th><th>Data Content</th></tr></thead><tbody>';

                        $.each(payload, function(key, val) {
                            html += '<tr>';
                            html += '<td class="font-weight-bold text-dark text-capitalize">' + key.replace(/_/g, ' ') + '</td>';
                            
                            if (typeof val === 'object' && val !== null) {
                                html += '<td><pre class="mb-0 bg-light p-2 rounded" style="max-height: 200px; overflow: auto;">' + JSON.stringify(val, null, 2) + '</pre></td>';
                            } else {
                                html += '<td>' + (val !== null && val !== '' ? val : '<span class="text-muted">null</span>') + '</td>';
                            }
                            html += '</tr>';
                        });

                        html += '</tbody></table></div>';
                    } else {
                        html = '<p class="text-muted">' + (payload || 'No payload captured') + '</p>';
                    }

                    $('#structuredContent').html(html);
                } else {
                    $('#structuredContent').html('<div class="alert alert-danger">Failed to load log snapshot.</div>');
                }
            },
            error: function() {
                $('#structuredContent').html('<div class="alert alert-danger">Error retrieving data from server.</div>');
            }
        });
    });

    $('#copyJsonBtn').on('click', function() {
        var text = $('#rawJsonCode').text();
        navigator.clipboard.writeText(text).then(function() {
            alert('JSON payload copied to clipboard!');
        });
    });
});
</script>
@endsection
