@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="erp-page p-2">

        <!-- HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-clock mr-1 text-success"></i> Time Allocation
                <span class="badge badge-light border ml-2 text-xs font-weight-normal text-muted">Stage Lead Times & Schedules</span>
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.time_allocation.backfill') }}" class="btn-erp btn-erp-outline" title="Sync Missing Production Lots">
                    <i class="fas fa-sync-alt mr-1"></i> Sync Missing Lots
                </a>
            </div>
        </div>

        <!-- MAIN TABLE CARD -->
        <div class="erp-card bg-white p-3">
            <div class="table-responsive">
                <table id="dataTable" class="table erp-table table-hover w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;" class="text-center">S.No</th>
                            <th>Lot No</th>
                            <th>Start Date & Time</th>
                            <th style="width: 100px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: "{{ route('admin.time_allocation.indexList') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center align-middle font-weight-bold text-muted' },
            { data: 'lot_no', name: 'lot_no', className: 'align-middle font-weight-bold' },
            { data: 'start_date_time', name: 'start_date_time', className: 'align-middle' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search lot number..."
        }
    });
});
</script>
@endsection
