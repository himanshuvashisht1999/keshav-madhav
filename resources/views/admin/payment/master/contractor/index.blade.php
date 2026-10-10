@extends('admin.layouts.app')
@section('title', 'Contractor Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Contractor Master</h1>
                <p class="erp-page-subtitle">Master register for manufacturing and job work contractors</p>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <div class="hud-stat-box" style="padding: 4px 10px; font-size: 0.8rem;">
                    <div class="hud-stat-label text-muted" style="font-size: 0.65rem;">Opening ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</div>
                    <div class="hud-stat-value text-info font-weight-bold">
                        ₹ {{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}
                    </div>
                </div>
                <div class="hud-stat-box" style="padding: 4px 10px; font-size: 0.8rem;">
                    <div class="hud-stat-label text-muted" style="font-size: 0.65rem;">Current balance</div>
                    <div class="hud-stat-value text-success font-weight-bold">
                        ₹ {{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}
                    </div>
                </div>
                <a href="{{ route('admin.payment.master.contractor.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> Add Contractor
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
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="contractorTable" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Contractor name</th>
                            <th style="width: 130px;">Phone</th>
                            <th>Address</th>
                            <th class="text-right" style="width: 140px;">Opening balance</th>
                            <th class="text-right" style="width: 140px;">Balance</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 110px;">Action</th>
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
        $('#contractorTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            ajax: {
                url: '{!! route('admin.payment.master.contractor.indexList') !!}'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'address', name: 'address' },
                { data: 'opening_balance', name: 'opening_balance', className: 'text-right font-weight-bold' },
                { data: 'balance', name: 'balance', className: 'text-right font-weight-bold' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ]
        });
    });
</script>
@endsection
