@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-clipboard-list"></i> Order summary report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-hashtag text-muted"></i> Report no: RJ 3</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Generated: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <!-- Any quick actions -->
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <div class="form-row align-items-end">
            <div class="col-md-2 col-sm-4 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Order no</label>
                <input type="text" id="order_no" class="form-control form-control-sm erp-filter-input" placeholder="Order no...">
            </div>

            <div class="col-md-2 col-sm-4 mb-1">
                <label class="small text-muted font-weight-bold mb-1">PO number</label>
                <input type="text" id="po_number" class="form-control form-control-sm erp-filter-input" placeholder="PO number...">
            </div>

            <div class="col-md-2 col-sm-4 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Lot no</label>
                <input type="text" id="lot_no" class="form-control form-control-sm erp-filter-input" placeholder="Lot no...">
            </div>

            <div class="col-md-2 col-sm-4 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Design no</label>
                <input type="text" id="design_number" class="form-control form-control-sm erp-filter-input" placeholder="Design no...">
            </div>

            <div class="col-md-3 col-sm-4 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Customer</label>
                <select id="customer_id" class="form-control form-control-sm select2 erp-filter-select">
                    <option value="">All customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-1 col-sm-4 mb-1">
                <button type="button" class="btn-erp btn-erp-outline btn-sm w-100"
                    onclick="$('#order_no,#po_number,#lot_no,#design_number,#customer_id').val('').trigger('change')" title="Reset filters">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table id="reportTable" class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Order no</th>
                        <th>PO number</th>
                        <th>Customer</th>
                        <th>Design no</th>
                        <th class="text-center">Lots</th>
                        <th>Order date</th>
                        <th>Expected delivery</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- Designs Modal -->
<div class="modal fade" id="designsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title font-weight-bold" style="font-size: 13px;">
                    <i class="fas fa-tshirt text-success mr-1"></i> Design numbers
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Design number</th>
                                <th class="text-right">Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="designsModalBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn-erp btn-erp-outline btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Lots Modal -->
<div class="modal fade" id="lotsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title font-weight-bold" style="font-size: 13px;">
                    <i class="fas fa-layer-group text-success mr-1"></i> Order lots: <span id="lotsModalOrderSku" class="text-dark"></span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="lotsModalLoading" class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading lots...</span>
                    </div>
                    <div class="text-muted mt-2 small">Fetching lot details...</div>
                </div>
                <div id="lotsModalContent" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover erp-table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;" class="text-center">#</th>
                                    <th>Lot number</th>
                                    <th>Design number</th>
                                    <th class="text-right">Quantity</th>
                                    <th>Cutting master</th>
                                    <th class="text-center">Current stage</th>
                                </tr>
                            </thead>
                            <tbody id="lotsModalBody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="lotsModalEmpty" class="text-center py-5" style="display: none;">
                    <i class="fas fa-box-open fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h6 class="font-weight-bold text-muted">No lots found for this order</h6>
                </div>
            </div>
            <div class="modal-footer justify-content-between py-2 bg-light">
                <div class="small font-weight-bold text-dark" id="lotsModalSummary"></div>
                <button type="button" class="btn-erp btn-erp-outline btn-sm" data-dismiss="modal">Close</button>
            </div>
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
                    className: 'font-weight-bold'
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
                            return `<a href="javascript:void(0)" class="erp-badge-status erp-badge-status-open" style="cursor: pointer;" onclick="showDesignsModal(${jsonStr})">Multiple</a>`;
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
                <td class="px-3 font-weight-bold">${designNo}</td>
                <td class="px-3 text-right font-weight-bold text-success">${quantity} pcs</td>
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
                                <td class="text-center text-muted">${index + 1}</td>
                                <td><strong>#${lot.lot_no}</strong></td>
                                <td><span class="badge badge-light border">${lot.design_number || 'N/A'}</span></td>
                                <td class="text-right font-weight-bold text-success">
                                    ${Number(lot.lot_quantity || 0).toLocaleString()} <small class="text-muted font-weight-normal">pcs</small>
                                </td>
                                <td><i class="fas fa-user-tie text-muted mr-1"></i>${lot.cutting_master || 'N/A'}</td>
                                <td class="text-center">
                                    <span class="badge badge-light border text-dark font-weight-bold px-2">
                                        <i class="fas fa-layer-group text-success mr-1"></i>${lot.last_current_stage || 'N/A'}
                                    </span>
                                </td>
                            </tr>
                        `;
                    });

                    $('#lotsModalBody').html(tbodyHtml);
                    $('#lotsModalSummary').html(`<strong>Total lots:</strong> ${response.lots.length} &nbsp;|&nbsp; <strong>Total pieces:</strong> <span class="text-success font-weight-bold">${totalQty.toLocaleString()} pcs</span>`);
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