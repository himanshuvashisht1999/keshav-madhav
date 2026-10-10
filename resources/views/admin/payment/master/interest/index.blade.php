@extends('admin.layouts.app')
@section('title', 'Interest Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Interest Master</h1>
                <p class="erp-page-subtitle">Master register for interest rates and charges</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.interest.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> Add Interest
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
                <table id="interestTable" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 55px;">#</th>
                            <th>Name</th>
                            <th class="text-right" style="width: 140px;">Percentage (%)</th>
                            <th class="text-center" style="width: 120px;">Status</th>
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
        $('#interestTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            ajax: {
                url: '{!! route('admin.payment.master.interest.indexList') !!}'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'name', name: 'name' },
                { data: 'percentage', name: 'percentage', className: 'text-right font-weight-bold' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ]
        });
    });
</script>
@endsection