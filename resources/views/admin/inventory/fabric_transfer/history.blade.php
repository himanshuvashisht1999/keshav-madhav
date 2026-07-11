@extends('admin.layouts.app')
@section('title', 'Transfer History')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Transfer History</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.fabric_transfer.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Transfer
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="historyTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <!-- <th>Transfer No</th> -->
                                    <th>Date</th>
                                    <th>From Warehouse</th>
                                    <th>To Warehouse</th>
                                    <th>Fabric Name</th>
                                    <th>Total Rolls</th>
                                    <th>Total Meters</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(function() {
    $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.inventory.fabric_transfer.history-list') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            // {data: 'transfer_no', name: 'transfer_no'},
            {data: 'transfer_date', name: 'transfer_date'},
            {data: 'from_warehouse', name: 'from_warehouse'},
            {data: 'to_warehouse', name: 'to_warehouse'},
            {data: 'fabric_details', name: 'fabric_details', orderable: false, searchable: false},
            {data: 'total_rolls', name: 'total_rolls', searchable: false},
            {data: 'total_qty', name: 'total_qty', searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [[2, 'desc']]
    });
});
</script>
@endsection
