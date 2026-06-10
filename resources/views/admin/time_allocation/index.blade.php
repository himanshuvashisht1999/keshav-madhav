@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Time Allocation</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.time_allocation.backfill') }}" class="btn btn-info shadow-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Sync Missing Lots
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- <div class="card-header">
                            <h3 class="card-title">Lot Time Allocation List</h3>
                        </div> -->
                        <div class="card-body">
                            <table id="dataTable" class="table table-bordered table-striped w-100">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Lot No</th>
                                        <th>Start Date & Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.time_allocation.indexList') }}",
            type: 'GET'
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'lot_no', name: 'lot_no' },
            { data: 'start_date_time', name: 'start_date_time' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
});
</script>
@endsection
