@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-store text-primary"></i> Manage Shops
            <small class="text-muted ml-2 font-weight-normal" style="font-size: 13px;">({{ $agent->name }})</small>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-agent.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Agents
            </a>
            <a href="{{ route('admin.master.sales-agent-shops.create', $agent->id) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Shop
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="p-2 border-bottom bg-light">
            <div class="d-flex align-items-center" style="max-width: 320px;">
                <input type="text" id="global_search" class="form-control erp-filter-input" placeholder="Search by name, email, or phone...">
            </div>
        </div>
        <div class="erp-card-body p-2 table-responsive">
            <table id="agent-shops" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Shop Name</th>
                        <th>Email Address</th>
                        <th>Phone Number</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 90px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#agent-shops').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.sales-agent-shops.indexList', $agent->id) !!}',
                data: function (d) {
                    d.search = d.search || {};
                    d.search.value = $('#global_search').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center' },
                { data: 'name', name: 'name', className: 'font-weight-bold' },
                { data: 'email', name: 'email' },
                { data: 'phone', name: 'phone' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'lrtip',
            language: {
                emptyTable: "No shops registered for this agent.",
                processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
            }
        });

        $('#global_search').on('keyup change', function (e) {
            oTable.draw();
        });
    });
</script>
@endsection
