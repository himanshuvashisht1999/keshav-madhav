@extends('admin.layouts.app')

@section('content')
    <style>
        /* ===== REPORT COMMON STYLE (SAME AS OTHER REPORTS) ===== */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .report-header h3 {
            font-weight: 600;
            margin: 0;
        }

        /* .report-meta {
        font-size: 13px;
        color: #6c757d;
    } */

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .table-report thead th {
            background: #f3f4f6;
            /* light grey header */
            color: #374151 !important;
            /* dark slate text */
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .table-report tbody td {
            vertical-align: middle;
            font-size: 14px;
        }

        .badge-status {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
        }

        .expand-btn {
            font-size: 13px;
        }
    </style>

    <div class="content-wrapper">

        {{-- ================= HEADER ================= --}}
        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                        <div class="report-meta">Report No : RJ 3</div>
                    </div>
                    <div>
                        <h3>Order Summary Report</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        {{-- ================= CONTENT ================= --}}
        <section class="content">
            <div class="container-fluid">

                {{-- ================= FILTERS ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">

                            <div class="col-md-2">
                                <label>Order No</label>
                                <input type="text" id="order_no" class="form-control" placeholder="Order No">
                            </div>

                            <div class="col-md-2">
                                <label>PO Number</label>
                                <input type="text" id="po_number" class="form-control" placeholder="PO Number">
                            </div>

                            <div class="col-md-2">
                                <label>Design No</label>
                                <input type="text" id="design_number" class="form-control" placeholder="Design No">
                            </div>

                            <div class="col-md-4">
                                <label>Customer</label>
                                <select id="customer_id" class="form-control select2">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="searchBtn">
                                    <i class="fas fa-filter"></i> Apply
                                </button>
                            </div> -->

                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-secondary w-100"
                                    onclick="$('#order_no,#po_number,#design_number,#customer_id').val('').trigger('change')">
                                    <i class="fas fa-sync"></i> Reset
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="card report-card">
                    <div class="card-body">
                        <div class="table-responsive">

                            <table id="reportTable" class="table table-bordered table-report">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th>Order No</th>
                                        <th>PO Number</th>
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

            </div>
        </section>
    </div>

    {{-- ================= SCRIPT ================= --}}
    <script>
        $(function () {

            let table = $('#reportTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: false,
                lengthChange: false,
                pageLength: 25,
                ajax: {
                    url: '{!! route('admin.report.order-summary.indexList') !!}',
                    data: function (d) {
                        d.order_no = $('#order_no').val();
                        d.po_number = $('#po_number').val();
                        d.design_number = $('#design_number').val();
                        d.customer_id = $('#customer_id').val();
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'id',
                        className: 'text-center text-muted'
                    },
                    {
                        data: 'sku',
                        name: 'sku',
                        className: 'fw-bold'
                    },
                    {
                        data: 'po_number',
                        name: 'po_number'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer.name'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center'
                    }
                ]
            });

            $('#searchBtn').on('click', function () {
                table.draw();
            });

            $('#order_no, #po_number, #design_number, #customer_id').on('keyup change', function () {
                table.draw();
            });

        });
    </script>

@endsection