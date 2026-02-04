@extends('owner.layouts.app')

@section('content')
    <style>
        /* ===== REPORT COMMON STYLE ===== */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-bottom: 1px solid #eee;
        }

        .report-header h3 {
            font-weight: 700;
            margin: 0;
            color: #1e3a8a;
        }

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            border: none;
            margin: 0 20px;
        }

        .table-report thead th {
            background: #1e3a8a;
            color: #fff !important;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            padding: 12px;
        }

        .table-report tbody td {
            vertical-align: middle;
            font-size: 14px;
            padding: 12px;
        }

        .badge-status {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .report-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                color: white;
                border: none;
            }

            .report-header h3 {
                color: white;
            }

            .report-card {
                margin: -30px 15px 20px;
            }

            .table-report thead {
                display: none;
            }

            .table-report,
            .table-report tbody,
            .table-report tr,
            .table-report td {
                display: block;
                width: 100%;
            }

            .table-report tr {
                margin-bottom: 15px;
                border: 1px solid #eee;
                border-radius: 12px;
                background: white;
                padding: 10px;
            }

            .table-report td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border: none;
            }

            .table-report td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                text-align: left;
                font-weight: 700;
                color: #64748b;
            }

            .table-report td:last-child {
                text-align: center;
                padding-left: 12px;
            }
        }
    </style>

    <div class="report-header">
        <div>
            <h3>Order Summary Report</h3>
        </div>
        <div class="desktop-only text-muted">Date: {{ now()->format('d M Y') }}</div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <!-- Filters (Desktop Optimized) -->
            <div class="row mb-4">
                <div class="col-md-4 mb-2">
                    <input type="text" id="order_no" class="form-control" placeholder="Search Order No...">
                </div>
                <div class="col-md-4 mb-2">
                    <select id="customer_id" class="form-control select2">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="reportTable" class="table table-hover table-report">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(function () {
            let table = $('#reportTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                ajax: {
                    url: '{{ route('owner.order-summary.indexList') }}',
                    data: function (d) {
                        d.order_no = $('#order_no').val();
                        d.customer_id = $('#customer_id').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center' },
                    { data: 'sku', className: 'font-weight-bold' },
                    { data: 'customer_name' },
                    { data: 'created_at' },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function () { return '<span class="badge bg-success badge-status">Active</span>'; }
                    },
                    { data: 'action', className: 'text-center' }
                ],
                createdRow: function (row, data, dataIndex) {
                    $(row).find('td:eq(0)').attr('data-label', '#');
                    $(row).find('td:eq(1)').attr('data-label', 'Order No');
                    $(row).find('td:eq(2)').attr('data-label', 'Customer');
                    $(row).find('td:eq(3)').attr('data-label', 'Date');
                    $(row).find('td:eq(4)').attr('data-label', 'Status');
                }
            });

            $('#order_no, #customer_id').on('keyup change', function () { table.draw(); });
        });
    </script>
@endsection