@extends('admin.layouts.app')
@section('title', 'New Fabric Return')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-undo-alt text-danger mr-1"></i> Create Fabric Return <small class="text-muted font-weight-normal">(Multi-Shipment)</small>
        </div>
        <div class="erp-header-actions d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 py-1 mr-2 shadow-sm" id="btnHeaderProceed" onclick="openProceedModal()" style="display: none; background: var(--brand-dark-green, #05421c); border-color: var(--brand-dark-green, #05421c);">
                <i class="fas fa-check-circle mr-1"></i> Proceed to Return (<span id="headerProceedRolls">0</span> Rolls - ₹ <span id="headerProceedAmount">0.00</span>)
            </button>
            <a href="{{ route('admin.fabric_return.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to Returns
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2 p-2" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close p-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <form action="{{ route('admin.fabric_return.store') }}" method="POST" id="returnForm">
        @csrf

        <!-- 1. ULTRA-COMPACT TOP VENDOR BAR (One Single Slim Row) -->
        <div class="erp-card mb-2 py-2 px-3">
            <div class="row align-items-center">
                <!-- Vendor Select -->
                <div class="col-lg-5 col-md-5 col-sm-12 mb-1 mb-md-0" style="min-width: 0;">
                    <div class="d-flex align-items-center">
                        <label class="erp-filter-label font-weight-bold text-nowrap mr-2 mb-0" style="color: var(--brand-dark-green, #05421c); font-size: 12px; min-width: 65px;">
                            <i class="fas fa-user-tie mr-1"></i> VENDOR: <span class="text-danger">*</span>
                        </label>
                        <div style="flex: 1; min-width: 0; width: 100%;">
                            <select name="vendor_id" id="vendor_select" class="form-control select2 erp-input" required style="width: 100%;">
                                <option value="">-- Choose Vendor to Return --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ (isset($selectedVendorId) && $selectedVendorId == $vendor->id) ? 'selected' : '' }}>
                                        {{ $vendor->name }} {{ $vendor->phone ? '(' . $vendor->phone . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Return Date -->
                <div class="col-lg-3 col-md-3 col-sm-6 mb-1 mb-md-0 pl-md-3">
                    <div class="d-flex align-items-center">
                        <label class="erp-filter-label font-weight-bold text-nowrap mr-2 mb-0" style="font-size: 12px; min-width: 45px;">
                            <i class="fas fa-calendar mr-1"></i> DATE: <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="date" class="form-control erp-input" value="{{ date('Y-m-d') }}" style="height: 32px; width: 140px; min-width: 130px;" required>
                    </div>
                </div>

                <!-- Inline Vendor Stats -->
                <div class="col-lg-4 col-md-4 col-sm-6 text-md-right text-left">
                    <div id="vendorStatsStrip" style="display: none;">
                        <span class="badge badge-light border text-dark mr-2 py-1 px-2" style="font-size: 11px;">
                            Balance: <strong class="text-danger">₹ <span id="vInfoBalance">0.00</span></strong>
                        </span>
                        <span class="badge badge-light border text-dark py-1 px-2" style="font-size: 11px;">
                            Available: <strong class="text-success"><span id="vInfoAvailableRolls">0 Rolls</span></strong>
                        </span>
                    </div>
                    <div id="vendorPlaceholderText" class="text-muted small">
                        <i class="fas fa-info-circle mr-1" style="color: var(--brand-light-green, #8bc63e);"></i> Select vendor to load rolls
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. NAVIGATION TABS (Available Rolls vs Selected Rolls) -->
        <ul class="nav nav-tabs erp-tabs mb-2" id="returnTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold px-3 py-2" id="tabAvailableLink" href="javascript:void(0)" onclick="switchTab('available')">
                    <i class="fas fa-boxes mr-1" style="color: var(--brand-dark-green, #05421c);"></i> 1. Available Rolls Across Shipments (<span id="tabAvailableCount">0</span>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold px-3 py-2" id="tabSelectedLink" href="javascript:void(0)" onclick="switchTab('selected')">
                    <i class="fas fa-check-circle mr-1" style="color: var(--brand-wordmark-green, #36b54a);"></i> 2. Selected Rolls for Return (<span id="tabSelectedCount" class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">0</span>)
                </a>
            </li>
        </ul>

        <!-- 3. COMPREHENSIVE FILTERS WITH SELECT2 MULTI-SELECT -->
        <div id="availableFiltersBar" class="erp-card mb-2 py-2 px-3" style="background: #fbfdfa; border: 1px solid #e2e8f0; border-top: 2.5px solid var(--brand-dark-green, #05421c);">
            <div class="row align-items-end">
                <!-- Shipment Multi-Select -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-2 mb-lg-0">
                    <label class="d-block mb-1 font-weight-bold" style="font-size: 11px; color: var(--brand-dark-green, #05421c); text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-truck mr-1" style="color: var(--brand-light-green, #8bc63e);"></i> Shipments
                    </label>
                    <select id="filterShipment" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="All Shipments">
                    </select>
                </div>

                <!-- Fabric Multi-Select -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-2 mb-lg-0">
                    <label class="d-block mb-1 font-weight-bold" style="font-size: 11px; color: var(--brand-dark-green, #05421c); text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-layer-group mr-1" style="color: var(--brand-light-green, #8bc63e);"></i> Fabrics
                    </label>
                    <select id="filterFabric" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="All Fabrics">
                    </select>
                </div>

                <!-- Warehouse Multi-Select -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-2 mb-lg-0">
                    <label class="d-block mb-1 font-weight-bold" style="font-size: 11px; color: var(--brand-dark-green, #05421c); text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-warehouse mr-1" style="color: var(--brand-light-green, #8bc63e);"></i> Warehouses
                    </label>
                    <select id="filterWarehouse" class="form-control select2" multiple="multiple" style="width: 100%;" data-placeholder="All Warehouses">
                    </select>
                </div>

                <!-- Roll / Bill Number Search & Reset -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-2 mb-lg-0">
                    <label class="d-block mb-1 font-weight-bold" style="font-size: 11px; color: var(--brand-dark-green, #05421c); text-transform: uppercase; letter-spacing: 0.3px;">
                        <i class="fas fa-search mr-1" style="color: var(--brand-light-green, #8bc63e);"></i> Roll / Bill Search
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="filterRollNo" class="form-control" style="height: 33px; font-size: 12px; border-color: #ced4da;" placeholder="Type roll / bill no...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary px-2" id="btnClearRollSearch" title="Clear search text" style="border-color: #ced4da; height: 33px;">
                                <i class="fas fa-times"></i>
                            </button>
                            <button type="button" class="btn font-weight-bold px-2" id="btnResetFilters" title="Clear all filters" style="background: var(--brand-yellow, #fcee21); border: 1px solid #e5d718; color: var(--brand-dark-green, #05421c); height: 33px;">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Notice on Selected Tab -->
        <div id="selectedNoticeBar" class="alert py-2 px-3 mb-2 justify-content-between align-items-center d-none" style="background: #eaf7ec; border: 1px solid #b7e3bd; color: var(--brand-dark-green, #05421c);">
            <div>
                <i class="fas fa-check-double mr-1" style="color: var(--brand-wordmark-green, #36b54a);"></i> Reviewing <strong id="selectedNoticeCount">0</strong> selected roll(s). You can adjust <strong>Return Meters</strong> and <strong>Rates</strong> directly in the table below.
            </div>
            <button type="button" class="btn btn-xs font-weight-bold px-2 py-1" onclick="switchTab('available')" style="border: 1px solid var(--brand-dark-green, #05421c); color: var(--brand-dark-green, #05421c); background: #ffffff;">
                <i class="fas fa-plus mr-1"></i> Add More Rolls
            </button>
        </div>

        <!-- 4. FULL-WIDTH ROLLS TABLE -->
        <div class="erp-card mb-3">
            <div class="erp-card-body p-0 table-responsive" style="max-height: 560px; overflow-y: auto;">
                <table class="erp-table table table-bordered table-hover mb-0" id="rollsTable">
                    <thead class="sticky-top bg-light" style="top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" id="headerCheckbox" title="Select All Filtered">
                            </th>
                            <th style="width: 120px;">Roll No.</th>
                            <th style="width: 180px;">Shipment & Bill</th>
                            <th>Fabric Name & SKU</th>
                            <th style="width: 130px;">Warehouse</th>
                            <th style="width: 100px;" class="text-right">Available</th>
                            <th style="width: 130px;" class="text-right">Return (M)</th>
                            <th style="width: 120px;" class="text-right">Rate / M (₹)</th>
                            <th style="width: 130px;" class="text-right">Amount (₹)</th>
                            <th style="width: 50px;" class="text-center" id="colActionHeader">Action</th>
                        </tr>
                    </thead>
                    <tbody id="rollsTableBody">
                        <tr id="noVendorRow">
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-user-check fa-3x mb-3 text-secondary opacity-50"></i>
                                <div class="h6 font-weight-bold">No Vendor Selected</div>
                                <p class="small text-muted mb-0">Please select a vendor from the dropdown above to load all available rolls across shipments.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Status Strip -->
            <div class="p-2 border-top bg-light d-flex justify-content-between align-items-center flex-wrap" style="font-size: var(--erp-font-sm);">
                <div>
                    <span>Showing: <strong id="visibleRollsCount">0</strong> Rolls</span>
                    <span class="mx-2 text-muted">|</span>
                    <span>Selected: <strong id="selectedCountText" class="text-success font-weight-bold">0</strong></span>
                    <span class="mx-2 text-muted">|</span>
                    <span>Selected Meters: <strong id="selectedMetersText" class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">0.00 M</strong></span>
                </div>
                <div class="d-flex align-items-center" style="gap: 15px;">
                    <div>
                        <span>Subtotal: <strong id="tableFooterSubtotal" class="text-dark">₹ 0.00</strong></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 py-1 shadow-sm" id="btnFooterProceed" onclick="openProceedModal()" style="display: none; background: var(--brand-dark-green, #05421c); border-color: var(--brand-dark-green, #05421c);">
                        <i class="fas fa-arrow-right mr-1"></i> Proceed to Return (<span id="footerProceedRolls">0</span> Rolls)
                    </button>
                </div>
            </div>
        </div>

        <!-- 5. PROCEED MODAL (Charges, GST & Grand Total) -->
        <div class="modal fade" id="proceedModal" tabindex="-1" role="dialog" aria-labelledby="proceedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header py-2 px-3 text-white" style="background: var(--brand-dark-green, #05421c);">
                        <h6 class="modal-title font-weight-bold m-0" id="proceedModalLabel">
                            <i class="fas fa-receipt mr-1"></i> Review & Finalize Return Voucher
                        </h6>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3 bg-light">
                        <!-- Summary Cards Row -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="bg-white p-2 rounded border text-center">
                                    <div class="small text-muted font-weight-bold">SELECTED ROLLS</div>
                                    <div class="h5 m-0 font-weight-bold text-success" id="modalRollsCount">0</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white p-2 rounded border text-center">
                                    <div class="small text-muted font-weight-bold">TOTAL METERS</div>
                                    <div class="h5 m-0 font-weight-bold text-primary" id="modalMetersCount">0.00 M</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-white p-2 rounded border text-center">
                                    <div class="small text-muted font-weight-bold">SUBTOTAL</div>
                                    <div class="h5 m-0 font-weight-bold text-dark">₹ <span id="modalSubtotal">0.00</span></div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Charges & Tax Controls -->
                        <div class="card border mb-3">
                            <div class="card-body p-3 bg-white">
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="erp-filter-label font-weight-bold mb-1">GST Percentage (%)</label>
                                        <input type="number" name="gst_percentage" id="gst_percentage" class="form-control erp-input calc-trigger" step="0.01" min="0" max="100" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="erp-filter-label font-weight-bold mb-1">GST Amount (₹)</label>
                                        <input type="text" id="display_gst_amount" class="form-control erp-input font-weight-bold text-right bg-light" readonly value="0.00">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="erp-filter-label font-weight-bold mb-1">Other Charges (+ ₹)</label>
                                        <input type="number" name="other_charges" id="other_charges" class="form-control erp-input calc-trigger" step="0.01" min="0" value="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="erp-filter-label font-weight-bold mb-1">Discount (- ₹)</label>
                                        <input type="number" name="discount" id="discount" class="form-control erp-input calc-trigger" step="0.01" min="0" value="0">
                                    </div>
                                </div>

                                <hr class="my-2">

                                <!-- Grand Total Box -->
                                <div class="d-flex justify-content-between align-items-center py-2 px-3 rounded" style="background: #fef2f2; border: 2px solid #ef4444;">
                                    <span class="font-weight-bold text-danger text-uppercase" style="font-size: 14px;">Grand Return Total:</span>
                                    <span class="font-weight-bold text-danger" style="font-size: 20px;">
                                        ₹ <span id="modalGrandTotal">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Input -->
                        <div class="form-group mb-0">
                            <label class="erp-filter-label font-weight-bold mb-1"><i class="fas fa-comment-alt mr-1"></i> Remarks / Reason for Return</label>
                            <textarea name="remarks" id="remarks" class="form-control erp-input" rows="2" placeholder="e.g. Defective rolls, shade variation, rejected by QC..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-3 bg-white justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Rolls
                        </button>
                        <button type="button" class="btn btn-danger font-weight-bold px-3 py-2 shadow-sm" onclick="confirmAndSubmitReturn()">
                            <i class="fas fa-check-circle mr-1"></i> Confirm & Generate Return Voucher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let rollsData = [];
    let currentTab = 'available'; // 'available' or 'selected'

    function initMultiSelects() {
        $('#filterShipment').select2({
            theme: 'bootstrap4',
            placeholder: "All Shipments",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
        $('#filterFabric').select2({
            theme: 'bootstrap4',
            placeholder: "All Fabrics",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
        $('#filterWarehouse').select2({
            theme: 'bootstrap4',
            placeholder: "All Warehouses",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
    }

    $(document).ready(function () {
        // Vendor Single Select2
        $('#vendor_select').select2({
            theme: 'bootstrap4',
            placeholder: "-- Choose Vendor to Return --",
            allowClear: true,
            width: '100%'
        });

        // Initialize Filter Multi-Selects
        initMultiSelects();

        // On Vendor Change, Fetch Rolls
        $('#vendor_select').on('change', function () {
            const vendorId = $(this).val();
            if (vendorId) {
                fetchVendorRolls(vendorId);
            } else {
                resetRollsTable();
            }
        });

        if ($('#vendor_select').val()) {
            $('#vendor_select').trigger('change');
        }

        // Filter change events
        $('#filterShipment, #filterFabric, #filterWarehouse').on('change', function () {
            applyFilters();
        });

        // Filter by Roll No / Bill No
        $('#filterRollNo').on('keyup input', function () {
            applyFilters();
        });

        $('#btnClearRollSearch').on('click', function () {
            $('#filterRollNo').val('');
            applyFilters();
        });

        // Reset All Filters
        $('#btnResetFilters').on('click', function () {
            $('#filterShipment').val(null).trigger('change');
            $('#filterFabric').val(null).trigger('change');
            $('#filterWarehouse').val(null).trigger('change');
            $('#filterRollNo').val('');
            applyFilters();
        });

        // Header Checkbox (Select all currently visible in table)
        $('#headerCheckbox').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.roll-row:visible .roll-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Modal calculation triggers
        $('.calc-trigger').on('input change', function () {
            recalculateGrandTotal();
        });
    });

    function switchTab(tab) {
        currentTab = tab;
        if (tab === 'available') {
            $('#tabAvailableLink').addClass('active');
            $('#tabSelectedLink').removeClass('active');
            $('#availableFiltersBar').show();
            $('#selectedNoticeBar').removeClass('d-flex').addClass('d-none');
        } else {
            $('#tabSelectedLink').addClass('active');
            $('#tabAvailableLink').removeClass('active');
            $('#availableFiltersBar').hide();
            $('#selectedNoticeBar').removeClass('d-none').addClass('d-flex');
            $('#selectedNoticeCount').text($('.roll-checkbox:checked').length);
        }
        applyFilters();
    }

    function resetRollsTable() {
        rollsData = [];
        $('#rollsTableBody').html(`
            <tr id="noVendorRow">
                <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-user-check fa-3x mb-3 text-secondary opacity-50"></i>
                    <div class="h6 font-weight-bold">No Vendor Selected</div>
                    <p class="small text-muted mb-0">Please select a vendor from the dropdown above to load all available rolls across shipments.</p>
                </td>
            </tr>
        `);
        $('#vendorStatsStrip').hide();
        $('#vendorPlaceholderText').show();
        
        if ($('#filterShipment').hasClass('select2-hidden-accessible')) { $('#filterShipment').select2('destroy'); }
        if ($('#filterFabric').hasClass('select2-hidden-accessible')) { $('#filterFabric').select2('destroy'); }
        if ($('#filterWarehouse').hasClass('select2-hidden-accessible')) { $('#filterWarehouse').select2('destroy'); }
        
        $('#filterShipment').empty().val(null);
        $('#filterFabric').empty().val(null);
        $('#filterWarehouse').empty().val(null);
        
        initMultiSelects();
        
        $('#filterRollNo').val('');
        $('#tabAvailableCount').text('0');
        $('#tabSelectedCount').text('0');
        $('#btnHeaderProceed, #btnFooterProceed').hide();
        recalculateGrandTotal();
    }

    function fetchVendorRolls(vendorId) {
        $('#rollsTableBody').html(`
            <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                    <i class="fas fa-spinner fa-spin fa-2x mb-2" style="color: var(--brand-dark-green, #05421c);"></i>
                    <div class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">Searching & Loading rolls from all shipments for this vendor...</div>
                </td>
            </tr>
        `);

        const url = '{{ route("admin.fabric_return.vendor_available_rolls", ":id") }}'.replace(':id', vendorId);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    rollsData = res.rolls;

                    if (res.vendor) {
                        $('#vInfoBalance').text(Number(res.vendor.balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $('#vInfoAvailableRolls').text(res.total_rolls + ' Rolls (' + Number(res.total_meters || 0).toFixed(2) + ' M)');
                        $('#vendorPlaceholderText').hide();
                        $('#vendorStatsStrip').show();
                    }

                    $('#tabAvailableCount').text(res.total_rolls);

                    // Populate Multi-Select Filter Dropdowns
                    populateShipmentDropdown(rollsData);
                    populateFabricDropdown(rollsData);
                    populateWarehouseDropdown(rollsData);

                    if (rollsData.length === 0) {
                        $('#rollsTableBody').html(`
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-2 text-warning opacity-50"></i>
                                    <div class="h6 font-weight-bold text-dark">No Available Rolls Found</div>
                                    <p class="small text-muted mb-0">This vendor currently has 0 unreturned rolls with remaining balance across shipments.</p>
                                </td>
                            </tr>
                        `);
                        $('#btnHeaderProceed, #btnFooterProceed').hide();
                        recalculateGrandTotal();
                        return;
                    }

                    renderRollsTable(rollsData);
                } else {
                    Swal.fire('Error', res.message || 'Failed to fetch rolls.', 'error');
                    resetRollsTable();
                }
            },
            error: function () {
                Swal.fire('Error', 'Network or server error while fetching rolls for vendor.', 'error');
                resetRollsTable();
            }
        });
    }

    function populateShipmentDropdown(rolls) {
        const shipmentMap = {};
        rolls.forEach(r => {
            const key = r.shipment_sku || 'N/A';
            if (!shipmentMap[key]) {
                shipmentMap[key] = { count: 0, bill: r.bill_no, date: r.receipt_date };
            }
            shipmentMap[key].count++;
        });

        let opts = '';
        Object.keys(shipmentMap).sort().forEach(shp => {
            const billText = shipmentMap[shp].bill ? ' (Bill: ' + shipmentMap[shp].bill + ')' : '';
            opts += `<option value="${shp}">${shp}${billText} - ${shipmentMap[shp].count} Roll(s)</option>`;
        });

        const $el = $('#filterShipment');
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.html(opts).val(null);
        $el.select2({
            theme: 'bootstrap4',
            placeholder: "All Shipments",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
    }

    function populateFabricDropdown(rolls) {
        const fabricMap = {};
        rolls.forEach(r => {
            const key = r.fabric_name || 'N/A';
            if (!fabricMap[key]) {
                fabricMap[key] = { count: 0, sku: r.fabric_sku || '' };
            }
            fabricMap[key].count++;
        });

        let opts = '';
        Object.keys(fabricMap).sort().forEach(fab => {
            const skuText = fabricMap[fab].sku ? ' [' + fabricMap[fab].sku + ']' : '';
            opts += `<option value="${fab}">${fab}${skuText} - ${fabricMap[fab].count} Roll(s)</option>`;
        });

        const $el = $('#filterFabric');
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.html(opts).val(null);
        $el.select2({
            theme: 'bootstrap4',
            placeholder: "All Fabrics",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
    }

    function populateWarehouseDropdown(rolls) {
        const whMap = {};
        rolls.forEach(r => {
            const key = r.warehouse || 'N/A';
            if (!whMap[key]) {
                whMap[key] = 0;
            }
            whMap[key]++;
        });

        let opts = '';
        Object.keys(whMap).sort().forEach(wh => {
            opts += `<option value="${wh}">${wh} - ${whMap[wh]} Roll(s)</option>`;
        });

        const $el = $('#filterWarehouse');
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.html(opts).val(null);
        $el.select2({
            theme: 'bootstrap4',
            placeholder: "All Warehouses",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        });
    }

    function renderRollsTable(rolls) {
        let rowsHtml = '';

        rolls.forEach((roll) => {
            const rowId = 'roll_' + roll.id;
            rowsHtml += `
                <tr class="roll-row" id="${rowId}" 
                    data-roll-id="${roll.id}" 
                    data-shipment="${roll.shipment_sku}" 
                    data-fabric="${roll.fabric_name}" 
                    data-warehouse="${roll.warehouse}" 
                    data-roll-no="${roll.roll_number}" 
                    data-bill-no="${roll.bill_no}">
                    <td class="text-center align-middle">
                        <input type="checkbox" class="roll-checkbox" name="returns[${roll.id}][selected]" value="1" data-id="${roll.id}">
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border font-weight-bold text-dark px-2 py-1" style="font-size: 11px;">
                            ${roll.roll_number}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">${roll.shipment_sku}</div>
                        <div class="small text-muted">Bill: ${roll.bill_no} | ${roll.receipt_date}</div>
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-bold">${roll.fabric_name}</div>
                        <div class="small text-muted">${roll.fabric_sku}</div>
                    </td>
                    <td class="align-middle text-muted small">${roll.warehouse}</td>
                    <td class="text-right align-middle">
                        <span class="badge font-weight-bold px-2 py-1" style="font-size: 11px; background: #eaf7ec; color: var(--brand-dark-green, #05421c); border: 1px solid #b7e3bd;">
                            ${roll.remaining_quantity.toFixed(2)} M
                        </span>
                    </td>
                    <td class="text-right align-middle">
                        <input type="number" 
                               name="returns[${roll.id}][return_meter]" 
                               class="form-control erp-input text-right font-weight-bold return-meter-input" 
                               style="height: 30px; font-size: 12px;" 
                               step="0.01" 
                               min="0.01" 
                               max="${roll.remaining_quantity}" 
                               value="${roll.remaining_quantity}" 
                               data-id="${roll.id}">
                    </td>
                    <td class="text-right align-middle">
                        <input type="number" 
                               name="returns[${roll.id}][price_per_meter]" 
                               class="form-control erp-input text-right font-weight-bold rate-input" 
                               style="height: 30px; font-size: 12px;" 
                               step="0.01" 
                               min="0" 
                               value="${roll.price_per_meter.toFixed(2)}" 
                               data-id="${roll.id}">
                    </td>
                    <td class="text-right align-middle font-weight-bold text-danger row-amount" id="amount_${roll.id}">
                        ₹ ${(roll.remaining_quantity * roll.price_per_meter).toFixed(2)}
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-row" data-id="${roll.id}" title="Uncheck / Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#rollsTableBody').html(rowsHtml);

        // Bind Row Checkbox Change
        $('.roll-checkbox').on('change', function () {
            const rollId = $(this).data('id');
            const row = $('#roll_' + rollId);
            if ($(this).is(':checked')) {
                row.addClass('table-warning font-weight-bold');
            } else {
                row.removeClass('table-warning font-weight-bold');
            }
            recalculateGrandTotal();
            if (currentTab === 'selected') {
                applyFilters();
            }
        });

        // Bind Meter Input Change
        $('.return-meter-input').on('input change', function () {
            const rollId = $(this).data('id');
            const max = parseFloat($(this).attr('max'));
            let val = parseFloat($(this).val()) || 0;

            if (val > max) {
                $(this).val(max);
                val = max;
            }

            updateRowAmount(rollId);

            // Auto-check if meter > 0
            const chk = $('#roll_' + rollId + ' .roll-checkbox');
            if (val > 0 && !chk.is(':checked')) {
                chk.prop('checked', true).trigger('change');
            } else if (val <= 0 && chk.is(':checked')) {
                chk.prop('checked', false).trigger('change');
            } else {
                recalculateGrandTotal();
            }
        });

        // Bind Rate Input Change
        $('.rate-input').on('input change', function () {
            const rollId = $(this).data('id');
            updateRowAmount(rollId);
            recalculateGrandTotal();
        });

        // Remove button in row
        $('.btn-remove-row').on('click', function () {
            const rollId = $(this).data('id');
            $('#roll_' + rollId + ' .roll-checkbox').prop('checked', false).trigger('change');
        });

        applyFilters();
        recalculateGrandTotal();
    }

    function updateRowAmount(rollId) {
        const meterInput = $('#roll_' + rollId + ' .return-meter-input');
        const rateInput = $('#roll_' + rollId + ' .rate-input');

        const meter = parseFloat(meterInput.val()) || 0;
        const rate = parseFloat(rateInput.val()) || 0;
        const lineAmt = meter * rate;

        $('#amount_' + rollId).text('₹ ' + lineAmt.toFixed(2));
    }

    function applyFilters() {
        const selectedShipments = ($('#filterShipment').val() || []).map(v => v.toLowerCase().trim());
        const selectedFabrics = ($('#filterFabric').val() || []).map(v => v.toLowerCase().trim());
        const selectedWarehouses = ($('#filterWarehouse').val() || []).map(v => v.toLowerCase().trim());
        const rollFilter = ($('#filterRollNo').val() || '').toLowerCase().trim();
        let visibleCount = 0;

        $('.roll-row').each(function () {
            const row = $(this);
            const isChecked = row.find('.roll-checkbox').is(':checked');
            const rowShipment = (row.data('shipment') || '').toString().toLowerCase().trim();
            const rowFabric = (row.data('fabric') || '').toString().toLowerCase().trim();
            const rowWarehouse = (row.data('warehouse') || '').toString().toLowerCase().trim();
            const rowRollNo = (row.data('roll-no') || '').toString().toLowerCase();
            const rowBillNo = (row.data('bill-no') || '').toString().toLowerCase();

            let matchesTab = (currentTab === 'available') || (currentTab === 'selected' && isChecked);
            let matchesShipment = selectedShipments.length === 0 || selectedShipments.includes(rowShipment);
            let matchesFabric = selectedFabrics.length === 0 || selectedFabrics.includes(rowFabric);
            let matchesWarehouse = selectedWarehouses.length === 0 || selectedWarehouses.includes(rowWarehouse);
            let matchesRoll = !rollFilter || (rowRollNo.indexOf(rollFilter) !== -1) || (rowBillNo.indexOf(rollFilter) !== -1);

            if (matchesTab && matchesShipment && matchesFabric && matchesWarehouse && matchesRoll) {
                row.show();
                visibleCount++;
            } else {
                row.hide();
            }
        });

        $('#visibleRollsCount').text(visibleCount);
    }

    function recalculateGrandTotal() {
        let selectedCount = 0;
        let totalReturnMeters = 0;
        let subtotal = 0;

        $('.roll-checkbox:checked').each(function () {
            const rollId = $(this).data('id');
            const returnMeter = parseFloat($('#roll_' + rollId + ' .return-meter-input').val()) || 0;
            const rate = parseFloat($('#roll_' + rollId + ' .rate-input').val()) || 0;

            if (returnMeter > 0) {
                selectedCount++;
                totalReturnMeters += returnMeter;
                subtotal += (returnMeter * rate);
            }
        });

        // Tab & Footer Badges
        $('#tabSelectedCount').text(selectedCount);
        $('#selectedNoticeCount').text(selectedCount);
        $('#selectedCountText').text(selectedCount);
        $('#selectedMetersText').text(totalReturnMeters.toFixed(2) + ' M');
        $('#tableFooterSubtotal').text('₹ ' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Modal Summary
        $('#modalRollsCount').text(selectedCount);
        $('#modalMetersCount').text(totalReturnMeters.toFixed(2) + ' M');
        $('#modalSubtotal').text(subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        // Taxes & Other charges
        const gstPct = parseFloat($('#gst_percentage').val()) || 0;
        const gstAmount = (subtotal * gstPct) / 100;
        $('#display_gst_amount').val(gstAmount.toFixed(2));

        const otherCharges = parseFloat($('#other_charges').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;

        const grandTotal = Math.max(0, subtotal + gstAmount + otherCharges - discount);
        const formattedGrandTotal = grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        $('#modalGrandTotal').text(formattedGrandTotal);

        // Header & Footer Proceed Button
        if (selectedCount > 0) {
            $('#headerProceedRolls').text(selectedCount);
            $('#headerProceedAmount').text(formattedGrandTotal);
            $('#footerProceedRolls').text(selectedCount);

            $('#btnHeaderProceed').fadeIn();
            $('#btnFooterProceed').fadeIn();
        } else {
            $('#btnHeaderProceed').fadeOut();
            $('#btnFooterProceed').fadeOut();
        }
    }

    function openProceedModal() {
        const vendorId = $('#vendor_select').val();
        if (!vendorId) {
            Swal.fire('Vendor Required', 'Please select a vendor first.', 'warning');
            return;
        }

        const checkedCount = $('.roll-checkbox:checked').length;
        if (checkedCount === 0) {
            Swal.fire('No Rolls Selected', 'Please select at least one roll with return meter > 0.', 'warning');
            return;
        }

        recalculateGrandTotal();
        $('#proceedModal').modal('show');
    }

    function confirmAndSubmitReturn() {
        const checkedCount = $('.roll-checkbox:checked').length;
        const grandTotal = $('#modalGrandTotal').text();

        Swal.fire({
            title: "Confirm Return Voucher?",
            text: `Generate voucher for ${checkedCount} roll(s) totaling ₹ ${grandTotal}? This will deduct returned roll stock and debit the vendor ledger.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#05421c",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Generate Voucher!"
        }).then((result) => {
            if (result.isConfirmed) {
                $('#proceedModal').modal('hide');
                $('#returnForm').submit();
            }
        });
    }
</script>
@endsection
