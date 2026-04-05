@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Stock Transfer History</h1>
                        <small class="text-muted">Recorded history of all warehouse inventory movements</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Warehouse Stock
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="historyTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="5%" class="py-3">#</th>
                                        <th class="py-3">Transfer No.</th>
                                        <th class="py-3">Date & Time</th>
                                        <th class="py-3">Destination (WH / Rack)</th>
                                        <th class="py-3 text-center">Total Boxes</th>
                                        <th class="py-3">Transferred By</th>
                                        <th class="py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }
    </style>

    <script>
        $(function () {
            $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: "{{ route('admin.inventory.warehouse_stock.history.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center' },
                    { data: 'transfer_no', name: 'transfer_no' },
                    { 
                        data: 'created_at', 
                        name: 'created_at',
                        render: function(data) {
                            if(!data) return '';
                            let date = new Date(data);
                            return date.toLocaleString();
                        }
                    },
                    { data: 'to_location', name: 'to_location' },
                    { data: 'items_count', name: 'items_count', className: 'text-center' },
                    { data: 'user.name', name: 'user.name', defaultContent: 'System' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });
        });
    </script>
@endsection
