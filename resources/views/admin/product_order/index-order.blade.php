@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-industry text-primary"></i> Corporate Orders
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.sales_order.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Create Corporate Order
            </a>
            <a href="{{ route('admin.sales_order.create_domestic') }}" class="btn-erp btn-erp-yellow">
                <i class="fas fa-plus"></i> Create Domestic Order
            </a>
            <button type="button" class="btn-erp btn-erp-outline" id="btnExportExcel" title="Export Excel">
                <i class="fas fa-file-excel text-success"></i> Excel
            </button>
            <button type="button" class="btn-erp btn-erp-outline" id="btnExportPdf" title="Export PDF">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </button>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <div class="row align-items-end">
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> PO Number</label>
                <input type="text" class="form-control erp-input" name="po_number" id="po_num_search" autocomplete="off" placeholder="Search PO #">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No (SKU)</label>
                <input type="text" class="form-control erp-input" name="sku" id="sku" autocomplete="off" placeholder="Search SKU...">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Customer</label>
                <select name="master_customer_id" id="master_customer_id" class="form-control select2 erp-input" style="width: 100%;">
                    <option value="">-- ALL CUSTOMERS --</option>
                    @foreach($customers as $customer)
                        <option value="{{$customer->id}}">{{$customer->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tags mr-1"></i> Order Type</label>
                <select id="order_type" class="form-control erp-input">
                    <option value="">-- ALL TYPES --</option>
                    <option value="corporate">Corporate</option>
                    <option value="domestic">Domestic</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-tasks mr-1"></i> Assignment</label>
                <select id="assignment_status" class="form-control erp-input">
                    <option value="">-- ALL ASSIGNMENTS --</option>
                    <option value="assigned">Assigned</option>
                    <option value="not_assigned">Not Assigned</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-info-circle mr-1"></i> Order Status</label>
                <select id="status" class="form-control erp-input">
                    <option value="">-- ALL STATUSES --</option>
                    <option value="1">In Progress</option>
                    <option value="2">Partial</option>
                    <option value="3">Completed</option>
                </select>
            </div>

            <!-- Date row -->
            <div class="col-lg-4 col-md-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Order Date / Period</label>
                <div class="row no-gutters">
                    <div class="col-6 pr-1">
                        <input type="date" class="form-control erp-input" name="created_at" id="created_at" autocomplete="off">
                    </div>
                    <div class="col-3 pr-1">
                        <select id="created_month" class="form-control erp-input">
                            <option value="">Month</option>
                            @for($m=1; $m<=12; $m++)
                                <option value="{{$m}}">{{date('M', mktime(0,0,0,$m,1))}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-3">
                        <select id="created_year" class="form-control erp-input">
                            <option value="">Year</option>
                            @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                <option value="{{$y}}">{{$y}}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> Expected Delivery Date</label>
                <div class="row no-gutters">
                    <div class="col-6 pr-1">
                        <input type="date" class="form-control erp-input" name="expected_delivery_date" id="expected_delivery_date" autocomplete="off">
                    </div>
                    <div class="col-3 pr-1">
                        <select id="expected_delivery_month" class="form-control erp-input">
                            <option value="">Month</option>
                            @for($m=1; $m<=12; $m++)
                                <option value="{{$m}}">{{date('M', mktime(0,0,0,$m,1))}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-3">
                        <select id="expected_delivery_year" class="form-control erp-input">
                            <option value="">Year</option>
                            @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                <option value="{{$y}}">{{$y}}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="btnApplyFilter" title="Apply Filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetFilter" title="Reset Filters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Summary Metrics Strip -->
    <div class="row mb-2">
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Pieces (Filtered)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;" id="summary_total_pcs">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Pieces
                </span>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Orders Shown</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;" id="summary_orders_count">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Orders
                </span>
            </div>
        </div>
    </div>
    <span id="total_pcs_count" style="display: none;"></span>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 110px;">PO No</th>
                        <th style="width: 140px;">Order No</th>
                        <th>Customer</th>
                        <th style="width: 140px;">Design Numbers</th>
                        <th style="width: 95px;" class="text-center">Type</th>
                        <th style="width: 105px;" class="text-center">Order Date</th>
                        <th style="width: 105px;" class="text-center">Exp. Delivery</th>
                        <th style="width: 85px;" class="text-right">Total Pcs</th>
                        <th style="width: 85px;" class="text-right">Dispatch Pcs</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 115px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Sets Modal -->
<div class="modal fade" id="orderSetsModal" tabindex="-1" role="dialog" aria-labelledby="orderSetsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 6px; overflow: hidden; border: 1px solid var(--erp-border);">
            <div class="modal-header py-2 px-3" style="background: var(--erp-green-primary); color: #fff;">
                <h5 class="modal-title font-weight-bold" style="font-size: var(--erp-font-md);" id="orderSetsModalLabel">
                    <i class="fas fa-layer-group mr-1"></i> Order Designs / Sets
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="erp-table table table-bordered table-hover mb-0" id="orderSetsTable">
                        <thead>
                            <tr>
                                <th>Design Number</th>
                                <th>Size Set</th>
                                <th>Color</th>
                                <th class="text-center">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data injected via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthChange: false,
            bLengthChange: false,
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.product_order.indexListOrder') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.po_number = $('#po_num_search').val();
                    d.master_customer_id = $('#master_customer_id').val();
                    d.order_type = $('#order_type').val();
                    d.created_at = $('#created_at').val();
                    d.created_month = $('#created_month').val();
                    d.created_year = $('#created_year').val();
                    d.expected_delivery_date = $('#expected_delivery_date').val();
                    d.expected_delivery_month = $('#expected_delivery_month').val();
                    d.expected_delivery_year = $('#expected_delivery_year').val();
                    d.status = $('#status').val();
                    d.assignment_status = $('#assignment_status').val();
                },
                orderable: false
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center' },
                { data: 'po_number', name: 'po_number', className: 'font-weight-bold' },
                { data: 'sku', name: 'sku' },
                { data: 'master_customer_id', name: 'master_customer_id' },
                { data: 'design_number', name: 'design_number', orderable: false },
                { data: 'order_type', name: 'order_type', className: 'text-center' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'expected_delivery_date', name: 'expected_delivery_date', className: 'text-center' },
                { data: 'total_pcs', name: 'total_pcs', className: 'text-right font-weight-bold' },
                { data: 'dispatch_pcs', name: 'dispatch_pcs', className: 'text-right' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            drawCallback: function (settings) {
                var api = this.api();
                var json = api.ajax.json();
                if (json && json.total_pieces_sum !== undefined) {
                    $('#summary_total_pcs').text(json.total_pieces_sum + ' Pcs');
                    $('#total_pcs_count').text(' (Total: ' + json.total_pieces_sum + ' Pcs)');
                }
                if (json && json.recordsFiltered !== undefined) {
                    $('#summary_orders_count').text(json.recordsFiltered);
                }
            }
        });

        // Filter triggers
        $('#btnApplyFilter').on('click', function () {
            oTable.draw();
        });

        $('#btnResetFilter').on('click', function () {
            $('#sku').val('');
            $('#po_num_search').val('');
            $('#master_customer_id').val('').trigger('change.select2');
            $('#order_type').val('');
            $('#created_at').val('');
            $('#created_month').val('');
            $('#created_year').val('');
            $('#expected_delivery_date').val('');
            $('#expected_delivery_month').val('');
            $('#expected_delivery_year').val('');
            $('#assignment_status').val('');
            $('#status').val('');
            oTable.draw();
        });

        $('#sku, #po_num_search').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                oTable.draw();
            }
        });

        // Export handlers
        $('#btnExportExcel').on('click', function () {
            var params = oTable.ajax.params();
            var qs = $.param(params);
            window.location.href = "{{ route('admin.product_order.exportOrders') }}?export_type=excel&" + qs;
        });

        $('#btnExportPdf').on('click', function () {
            var params = oTable.ajax.params();
            var qs = $.param(params);
            window.location.href = "{{ route('admin.product_order.exportOrders') }}?export_type=pdf&" + qs;
        });

        // View Designs Modal logic
        $(document).on('click', '.view-designs', function(e) {
            e.preventDefault();
            let orderId = $(this).data('id');
            let tbody = $('#orderSetsTable tbody');
            tbody.html('<tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin text-primary"></i> Loading...</td></tr>');
            $('#orderSetsModal').modal('show');

            $.ajax({
                url: "{{ url('admin/production-order/get-order-sets') }}/" + orderId,
                type: 'GET',
                success: function(res) {
                    if(res.success) {
                        let html = '';
                        let totalQty = 0;
                        if(res.data.length > 0) {
                            res.data.forEach(function(row) {
                                totalQty += parseInt(row.quantity) || 0;
                                html += `
                                    <tr>
                                        <td class="font-weight-bold">${row.design_number}</td>
                                        <td>${row.size_set}</td>
                                        <td>${row.color}</td>
                                        <td class="text-center font-weight-bold">${row.quantity}</td>
                                    </tr>
                                `;
                            });
                            html += `
                                <tr class="bg-light font-weight-bold">
                                    <td colspan="3" class="text-right">Total:</td>
                                    <td class="text-center text-primary font-weight-bold">${totalQty}</td>
                                </tr>
                            `;
                        } else {
                            html = '<tr><td colspan="4" class="text-center text-muted py-3">No sets found for this order.</td></tr>';
                        }
                        tbody.html(html);
                    } else {
                        tbody.html('<tr><td colspan="4" class="text-center text-danger py-3">Error loading data.</td></tr>');
                    }
                },
                error: function() {
                    tbody.html('<tr><td colspan="4" class="text-center text-danger py-3">Failed to load order sets.</td></tr>');
                }
            });
        });
    });

    function deleteOrder(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.product_order.deleteOrderMain', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection