@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Packing Dashboard</h1>
                        <small class="text-muted">Monitor and manage order packing sessions</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Start New Packing
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-2">
                        <div class="row align-items-end">
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Order No</label>
                                <input type="text" id="order_no" class="form-control form-control-sm" placeholder="Search Order...">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Customer Name</label>
                                <input type="text" id="customer_name" class="form-control form-control-sm" placeholder="Search Customer...">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Start Date</label>
                                <input type="date" id="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">End Date</label>
                                <input type="date" id="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-auto mb-2 text-right">
                                <button id="resetFilters" class="btn btn-sm btn-outline-secondary px-3 shadow-sm">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="packingTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Order No</th>
                                        <th class="py-3">Customer</th>
                                        <th class="py-3">Slip ID</th>
                                        <th class="py-3">Packing Date</th>
                                        <th class="text-center py-3">Status</th>
                                        <th class="text-right py-3 px-4">Action</th>
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

        .badge {
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }
        .form-control { border-radius: 8px; }
    </style>

    <script>
        $(function () {
            let table = $('#packingTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                ajax: {
                    url: '{!! route('admin.packing.indexList') !!}',
                    data: function (d) {
                        d.order_no = $('#order_no').val();
                        d.customer_name = $('#customer_name').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted' },
                    { data: 'order_no', name: 'order_no', className: 'font-weight-bold text-primary' },
                    { data: 'customer', name: 'customer' },
                    { data: 'slip_id', name: 'slip_id' },
                    { data: 'packing_date', name: 'packing_date' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'action', name: 'action', className: 'text-right px-4' }
                ],
                language: {
                    emptyTable: "No packing sessions found",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            // Trigger filter
            $('#order_no, #customer_name, #start_date, #end_date').on('keyup change', function() {
                table.draw();
            });

            // Reset filter
            $('#resetFilters').on('click', function() {
                $('#order_no, #customer_name, #start_date, #end_date').val('');
                table.draw();
            });
        });
    </script>
@endsection