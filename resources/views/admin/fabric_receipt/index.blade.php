@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-truck-loading text-primary"></i> Fabric Shipments
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.fabric_receipt.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Fabric Shipment
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <div class="row align-items-end">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-truck mr-1"></i> Shipment No</label>
                <input type="text" class="form-control erp-input" name="shipment_id" id="shipment_id" placeholder="Shipment No..." autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-file-invoice mr-1"></i> Bill No</label>
                <input type="text" class="form-control erp-input" name="bill_no" id="bill_no" placeholder="Bill No..." autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                <select class="form-control select2 erp-input" name="vendor_id" id="vendor_id" style="width: 100%;">
                    <option value="">-- ALL VENDORS --</option>
                    @foreach($vendors as $single_data)
                        <option value="{{$single_data->id}}">{{$single_data->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-warehouse mr-1"></i> Warehouse</label>
                <select class="form-control select2 erp-input" name="master_fabric_warehouse_id" id="master_fabric_warehouse_id" style="width: 100%;">
                    <option value="">-- ALL WAREHOUSES --</option>
                    @foreach($cutting_units as $fabric_warehouse)
                        <option value="{{$fabric_warehouse->id}}">{{$fabric_warehouse->cutting_master_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                <input type="date" class="form-control erp-input" name="from_date" id="from_date" autocomplete="off">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                <input type="date" class="form-control erp-input" name="to_date" id="to_date" autocomplete="off">
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
                        <i class="fas fa-scroll"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Rolls (Filtered)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;" id="summary_total_rolls">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Rolls
                </span>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Amount (Filtered)</div>
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
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 140px;">Shipment No.</th>
                        <th style="width: 130px;">Bill No.</th>
                        <th>Vendor</th>
                        <th style="width: 160px;">Warehouse</th>
                        <th style="width: 110px;" class="text-center">Date</th>
                        <th style="width: 70px;" class="text-right">Rolls</th>
                        <th style="width: 140px;" class="text-right">Total Amount (Rs.)</th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="6" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">TOTAL (FILTERED):</td>
                        <td class="text-right font-weight-bold grand-total-val" id="footer_total_rolls">0</td>
                        <td class="text-right font-weight-bold grand-total-val" id="footer_total_amount">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
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
                url: '{!! route('admin.fabric_receipt.indexList') !!}',
                data: function (d) {
                    d.shipment_id = $('#shipment_id').val();
                    d.bill_no = $('#bill_no').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.master_fabric_warehouse_id = $('#master_fabric_warehouse_id').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                },
                orderable: false
            },
            drawCallback: function (settings) {
                var json = settings.json;
                if (json) {
                    var rolls = json.formatted_total_rolls !== undefined ? json.formatted_total_rolls : (json.total_rolls || 0);
                    var amount = json.formatted_total_amount !== undefined ? json.formatted_total_amount : (json.total_amount ? Number(json.total_amount).toFixed(2) : '0.00');

                    $('#summary_total_rolls').text(rolls);
                    $('#summary_total_amount').text(amount);

                    $('#footer_total_rolls').text(rolls);
                    $('#footer_total_amount').text(amount);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center' },
                { data: 'shipment_id', name: 'shipment_id', className: 'font-weight-bold' },
                { data: 'bill_no', name: 'bill_no' },
                { data: 'vendor_id', name: 'vendor_id' },
                { data: 'master_fabric_warehouse_id', name: 'master_fabric_warehouse_id' },
                { data: 'time', name: 'time', className: 'text-center' },
                { data: 'roll', name: 'roll', className: 'text-right' },
                { data: 'total_amount', name: 'total_amount', className: 'text-right font-weight-bold' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'rtip'
        });

        // Trigger filter only on explicit button click or Enter key
        $('#btnApplyFilter').on('click', function (e) {
            e.preventDefault();
            oTable.draw();
        });

        // Filter on Enter key inside filter inputs
        $('.erp-filter-bar input').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                oTable.draw();
            }
        });

        // Reset Filter
        $('#btnResetFilter').on('click', function (e) {
            e.preventDefault();
            $('#shipment_id').val('');
            $('#bill_no').val('');
            $('#vendor_id').val('').trigger('change.select2');
            $('#master_fabric_warehouse_id').val('').trigger('change.select2');
            $('#from_date').val('');
            $('#to_date').val('');
            oTable.draw();
        });
    });

    function deleteData(id) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this fabric receipt!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#15803d",
            cancelButtonColor: "#dc2626",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.fabric_receipt.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection