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
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label>Transfer No</label>
                                <input type="text" name="transfer_no" id="transfer_no" class="form-control" placeholder="Search...">
                            </div>
                            <div class="col-md-3">
                                <label>From Warehouse</label>
                                <select name="from_warehouse_id" id="from_warehouse_id" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>To Warehouse</label>
                                <select name="to_warehouse_id" id="to_warehouse_id" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                                <button type="button" id="resetFilters" class="btn btn-secondary">Clear</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

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
    $('.select2').select2({
        width: '100%',
        theme: 'bootstrap4',
        allowClear: true
    });

    var table = $('#historyTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.inventory.fabric_transfer.history-list') }}",
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.transfer_no = $('#transfer_no').val();
                d.from_warehouse_id = $('#from_warehouse_id').val();
                d.to_warehouse_id = $('#to_warehouse_id').val();
            }
        },
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

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        $('.select2').val(null).trigger('change');
        table.draw();
    });
});
</script>
@endsection
