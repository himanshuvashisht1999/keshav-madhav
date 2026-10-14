@extends('admin.layouts.app')
@section('title', 'Committee Master')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-users text-primary"></i> Committee Master
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Opening</span>
                    <span class="hud-stat-value text-info font-weight-bold">₹{{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Current Balance</span>
                    <span class="hud-stat-value text-success font-weight-bold">₹{{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                </div>
                <a href="{{ route('admin.payment.master.committee.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> Add Committee
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="committees_table" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Opening Balance</th>
                            <th>Balance</th>
                            <th>Period</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            var oTable = $('#committees_table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.payment.master.committee.indexList') !!}',
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'amount', name: 'amount' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'balance', name: 'balance' },
                    { data: 'period', name: 'period' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ]
            });
        });
    </script>
@endsection