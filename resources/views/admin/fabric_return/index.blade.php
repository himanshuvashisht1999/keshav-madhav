@extends('admin.layouts.app')
@section('title', 'Fabric Returns')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-undo-alt text-danger"></i> Fabric Returns
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.fabric_return.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> New Fabric Return
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <div class="row align-items-end">
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-receipt mr-1"></i> Return No.</label>
                <input type="text" class="form-control erp-input" name="return_number" id="filter_return_number" placeholder="Return No..." autocomplete="off">
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                <select class="form-control select2 erp-input" name="vendor_id" id="filter_vendor_id" style="width: 100%;">
                    <option value="">-- ALL VENDORS --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                <input type="date" class="form-control erp-input" name="start_date" id="filter_start_date" autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                <input type="date" class="form-control erp-input" name="end_date" id="filter_end_date" autocomplete="off">
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
        <div class="col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Return Vouchers</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;" id="summary_total_returns">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Returns
                </span>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between" style="border-left: 3.5px solid var(--brand-light-green, #8bc63e); background: #fff;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: #f0f8e7; color: var(--brand-dark-green, #05421c); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid #c8e6c9;">
                        <i class="fas fa-scroll"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Rolls Returned</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--brand-dark-green, #05421c); line-height: 1.2;" id="summary_total_rolls">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #f0f8e7; color: var(--brand-dark-green, #05421c); border: 1px solid #c8e6c9; font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;" id="summary_total_meters">
                    0.00 M
                </span>
            </div>
        </div>
        <div class="col-md-4 col-sm-12 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Returned Value</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            Rs. <span id="summary_total_amount"><i class="fas fa-spinner fa-spin fa-xs"></i></span>
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    INR (Rs.)
                </span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="returnsTable" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th style="width: 140px;">Return No.</th>
                        <th style="width: 105px;" class="text-center">Date</th>
                        <th>Vendor</th>
                        <th style="width: 180px;">Shipments</th>
                        <th style="width: 90px;" class="text-center">Rolls</th>
                        <th style="width: 100px;" class="text-right">Meters</th>
                        <th style="width: 120px;" class="text-right">Subtotal</th>
                        <th style="width: 120px;" class="text-right">GST</th>
                        <th style="width: 130px;" class="text-right">Total (Rs.)</th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="5" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">TOTAL (FILTERED):</td>
                        <td class="text-center font-weight-bold grand-total-val" id="footer_total_rolls">0</td>
                        <td class="text-right font-weight-bold grand-total-val" id="footer_total_meters">0.00 M</td>
                        <td colspan="2" class="text-right font-weight-bold">GRAND TOTAL:</td>
                        <td class="text-right font-weight-bold grand-total-val" id="footer_total_amount">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        var rTable = $('#returnsTable').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthChange: false,
            bLengthChange: false,
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.fabric_return.indexList') !!}',
                data: function (d) {
                    d.return_number = $('#filter_return_number').val();
                    d.vendor_id = $('#filter_vendor_id').val();
                    d.start_date = $('#filter_start_date').val();
                    d.end_date = $('#filter_end_date').val();
                },
                orderable: false
            },
            drawCallback: function (settings) {
                var json = settings.json;
                if (json) {
                    var totalReturns = json.total_returns !== undefined ? json.total_returns : 0;
                    var rolls = json.formatted_total_rolls !== undefined ? json.formatted_total_rolls : (json.total_rolls || 0);
                    var meters = json.formatted_total_meters !== undefined ? json.formatted_total_meters : (json.total_meters ? Number(json.total_meters).toFixed(2) + ' M' : '0.00 M');
                    var amount = json.formatted_total_amount !== undefined ? json.formatted_total_amount : (json.total_amount ? Number(json.total_amount).toFixed(2) : '0.00');

                    $('#summary_total_returns').text(totalReturns);
                    $('#summary_total_rolls').text(rolls);
                    $('#summary_total_meters').text(meters);
                    $('#summary_total_amount').text(amount);

                    $('#footer_total_rolls').text(rolls);
                    $('#footer_total_meters').text(meters);
                    $('#footer_total_amount').text(amount);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center' },
                { data: 'return_number', name: 'return_number' },
                { data: 'date', name: 'date', className: 'text-center' },
                { data: 'vendor_name', name: 'vendor_name' },
                { data: 'shipments', name: 'shipments' },
                { data: 'rolls_count', name: 'rolls_count', className: 'text-center' },
                { data: 'total_meters', name: 'total_meters', className: 'text-right font-weight-bold' },
                { data: 'sub_total', name: 'sub_total', className: 'text-right' },
                { data: 'gst_amount', name: 'gst_amount', className: 'text-right' },
                { data: 'total_amount', name: 'total_amount', className: 'text-right font-weight-bold' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'rtip'
        });

        // Trigger filter
        $('#btnApplyFilter').on('click', function (e) {
            e.preventDefault();
            rTable.draw();
        });

        // Filter on Enter key inside filter inputs
        $('.erp-filter-bar input').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                rTable.draw();
            }
        });

        // Reset Filter
        $('#btnResetFilter').on('click', function (e) {
            e.preventDefault();
            $('#filter_return_number').val('');
            $('#filter_vendor_id').val('').trigger('change.select2');
            $('#filter_start_date').val('');
            $('#filter_end_date').val('');
            rTable.draw();
        });
    });

    function confirmDeleteReturn(url, returnNo) {
        Swal.fire({
            title: "Delete Return " + returnNo + "?",
            text: "This will restore returned roll quantities to available stock and revert vendor balance!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete & restore rolls!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
@endsection
