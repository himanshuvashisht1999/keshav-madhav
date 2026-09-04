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
                                <label>Lot No</label>
                                <input type="text" id="lot_no" class="form-control" placeholder="Lot No">
                            </div>

                            <div class="col-md-2">
                                <label>Design No</label>
                                <input type="text" id="design_number" class="form-control" placeholder="Design No">
                            </div>

                            <div class="col-md-3">
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

                            <div class="col-md-1 d-flex align-items-end">
                                <button class="btn btn-secondary w-100"
                                    onclick="$('#order_no,#po_number,#lot_no,#design_number,#customer_id').val('').trigger('change')">
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
                                        <th>Design No</th>
                                        <th class="text-center">Lots</th>
                                        <th>Order Date</th>
                                        <th>Expected Delivery</th>
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

    <!-- Designs Modal -->
    <div class="modal fade" id="designsModal" tabindex="-1" role="dialog" aria-labelledby="designsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 16px; max-height: 85vh;">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title font-weight-bold" id="designsModalLabel">
                        <i class="fas fa-tshirt mr-2 text-info"></i>Design Numbers
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="px-4">Design Number</th>
                                    <th class="px-4 text-right">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="designsModalBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" style="border-radius: 8px;" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lots Modal -->
    <div class="modal fade" id="lotsModal" tabindex="-1" role="dialog" aria-labelledby="lotsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 16px; max-height: 85vh;">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <h5 class="modal-title font-weight-bold" id="lotsModalLabel">
                        <i class="fas fa-layer-group text-info mr-2"></i>Order Lots - <span id="lotsModalOrderSku" class="text-warning"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="overflow-y: auto;">
                    <div id="lotsModalLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading lots...</span>
                        </div>
                        <div class="text-muted mt-2 small font-weight-bold">Fetching lot details...</div>
                    </div>
                    <div id="lotsModalContent" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th class="px-3 text-center" width="5%">#</th>
                                        <th class="px-3">Lot Number</th>
                                        <th class="px-3">Design Number</th>
                                        <th class="px-3 text-right">Quantity</th>
                                        <th class="px-3">Cutting Master</th>
                                        <th class="px-3 text-center">Current Stage</th>
                                    </tr>
                                </thead>
                                <tbody id="lotsModalBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="lotsModalEmpty" class="text-center py-5" style="display: none;">
                        <i class="fas fa-box-open fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                        <h6 class="font-weight-bold text-muted">No lots found for this order</h6>
                    </div>
                </div>
                <div class="modal-footer justify-content-between py-2 px-4 bg-light">
                    <div class="small font-weight-bold text-dark" id="lotsModalSummary">
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-3 font-weight-bold" style="border-radius: 8px;" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
                        d.lot_no = $('#lot_no').val();
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
                        data: 'designs',
                        name: 'designs',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (!data) return '-';
                            let keys = Object.keys(data);
                            if (keys.length === 1) {
                                return '<b>' + keys[0] + '</b>';
                            } else if (keys.length > 1) {
                                let jsonStr = JSON.stringify(data).replace(/"/g, '&quot;');
                                return `<a href="javascript:void(0)" class="badge badge-info text-white" style="font-size: 12px; cursor: pointer;" onclick="showDesignsModal(${jsonStr})">Multiple</a>`;
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'lots',
                        name: 'lots',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'expected_delivery_date',
                        name: 'expected_delivery_date'
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

            $('#order_no, #po_number, #lot_no, #design_number, #customer_id').on('keyup change', function () {
                table.draw();
            });

        });

        function showDesignsModal(designs) {
            const tbody = document.getElementById('designsModalBody');
            tbody.innerHTML = '';
            
            for (const [designNo, quantity] of Object.entries(designs)) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 font-weight-bold">${designNo}</td>
                    <td class="px-4 text-right font-weight-bold text-success">${quantity} Pcs</td>
                `;
                tbody.appendChild(tr);
            }
            
            $('#designsModal').modal('show');
        }

        function showLotsModal(orderId, orderSku) {
            $('#lotsModalOrderSku').text(orderSku || '#' + orderId);
            $('#lotsModalLoading').show();
            $('#lotsModalContent').hide();
            $('#lotsModalEmpty').hide();
            $('#lotsModalSummary').text('');
            $('#lotsModalBody').html('');
            $('#lotsModal').modal('show');

            $.ajax({
                url: "{{ url('admin/report/order-summary/lots') }}/" + orderId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#lotsModalLoading').hide();
                    if (response.status && response.lots && response.lots.length > 0) {
                        let tbodyHtml = '';
                        let totalQty = 0;

                        response.lots.forEach(function(lot, index) {
                            totalQty += parseInt(lot.lot_quantity || 0);
                            tbodyHtml += `
                                <tr>
                                    <td class="px-3 text-center text-muted font-weight-bold">${index + 1}</td>
                                    <td class="px-3 font-weight-bold">
                                        <span class="badge badge-info shadow-xs px-2 py-1" style="font-size: 12px;">#${lot.lot_no}</span>
                                    </td>
                                    <td class="px-3 font-weight-bold">
                                        <span class="badge badge-dark px-2 py-1" style="background-color: #334155; font-size: 12px;">${lot.design_number || 'N/A'}</span>
                                    </td>
                                    <td class="px-3 text-right font-weight-bold" style="color: #047857; font-size: 13px;">
                                        ${Number(lot.lot_quantity || 0).toLocaleString()} <small class="text-muted font-weight-normal">pcs</small>
                                    </td>
                                    <td class="px-3 small font-weight-semibold text-dark">
                                        <i class="fas fa-user-tie mr-1 text-secondary"></i>${lot.cutting_master || 'N/A'}
                                    </td>
                                    <td class="px-3 text-center">
                                        <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-layer-group text-primary mr-1"></i>${lot.last_current_stage || 'N/A'}
                                        </span>
                                    </td>
                                </tr>
                            `;
                        });

                        $('#lotsModalBody').html(tbodyHtml);
                        $('#lotsModalSummary').html(`<strong>Total Lots:</strong> ${response.lots.length} &nbsp;|&nbsp; <strong>Total Pieces:</strong> <span class="text-success font-weight-bold">${totalQty.toLocaleString()} pcs</span>`);
                        $('#lotsModalContent').show();
                    } else {
                        $('#lotsModalEmpty').show();
                    }
                },
                error: function(err) {
                    $('#lotsModalLoading').hide();
                    $('#lotsModalEmpty').show().find('h6').text('Error fetching lot details.');
                }
            });
        }
    </script>

@endsection