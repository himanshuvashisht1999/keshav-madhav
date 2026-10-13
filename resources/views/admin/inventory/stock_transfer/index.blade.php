@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-dolly-flatbed text-primary"></i> Stock Transfer
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-history"></i> Transfer History
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>

                <!-- MODE TABS -->
                <ul class="nav nav-pills mb-4" id="transferModeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold px-4 py-2" id="manual-tab" data-toggle="tab" href="#manual-mode" role="tab">
                            <i class="fas fa-list mr-2"></i> Manual Mode
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold px-4 py-2" id="scan-tab" data-toggle="tab" href="#scan-mode" role="tab">
                            <i class="fas fa-barcode mr-2"></i> Barcode Scan Mode
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="transferModeContent">

                    {{-- ========================= MANUAL MODE ========================= --}}
                    <div class="tab-pane fade show active" id="manual-mode" role="tabpanel">

                        <!-- STEP 1 -->
                        <div class="erp-card mb-2" style="border-radius:12px;">
                            <div class="p-2 border-bottom bg-light">
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-search mr-2"></i> Step 1: Find Source Stock</h6>
                            </div>
                            <div class="p-3">
                                <div class="row align-items-end">
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Source warehouse</label>
                                        <select id="storeroom_filter" class="form-control form-control-sm select2">
                                            <option value="">All Warehouses</option>
                                            @foreach($storerooms as $storeroom)
                                                <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Source rack</label>
                                        <select id="rack_filter" class="form-control form-control-sm select2">
                                            <option value="">All Racks</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Design no.</label>
                                        <select id="design_filter" class="form-control form-control-sm select2">
                                            <option value="">All Design Nos.</option>
                                            @foreach($designs as $design)
                                                <option value="{{ $design->design_number }}">{{ $design->design_number }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Product</label>
                                        <select id="product_filter" class="form-control form-control-sm select2">
                                            <option value="">All Products</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->series ? $product->series->name . ' ' : '' }}{{ $product->name_of_garment }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Color</label>
                                        <select id="color_filter" class="form-control form-control-sm select2">
                                            <option value="">All Colors</option>
                                            @foreach($colors as $color)
                                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small font-weight-bold text-muted mb-1">Size set</label>
                                        <select id="size_set_filter" class="form-control form-control-sm select2">
                                            <option value="">All Size sets</option>
                                            @foreach($size_sets as $set)
                                                <option value="{{ $set->id }}">{{ $set->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt-3">
                                        <button type="button" id="apply_filters" class="btn-erp btn-erp-primary" title="Apply Filters">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <button id="reset_filters" class="btn-erp btn-erp-secondary btn-block">
                                            <i class="fas fa-undo mr-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2 + 3 -->
                        <form id="bulk-transfer-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="erp-card mb-2" style="border-radius:12px; overflow:hidden;">
                                        <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-list mr-2"></i> Step 2: Select Items to Transfer</h6>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select-all">
                                                <label class="custom-control-label small font-weight-bold" for="select-all">Select All</label>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table id="inventoryTable" class="erp-table table-bordered table-hover mb-0">
                                                    <thead class="bg-light contrast-text">
                                                        <tr>
                                                            <th class="py-3 text-center" style="width:50px;"></th>
                                                            <th class="py-3">Product / Design</th>
                                                            <th class="py-3">Size / Color</th>
                                                            <th class="py-3">Current Location</th>
                                                            <th class="py-3 text-center">Available</th>
                                                            <th class="py-3 text-center" style="width:120px;">Qty to Transfer</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                            <div id="loading-spinner" class="text-center py-3" style="display:none;">
                                                <div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>
                                            </div>
                                            <div id="no-more-data" class="text-center py-3 text-muted small" style="display:none;">No more records to load.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="erp-card mb-2" style="border-radius:12px; position:sticky; top:20px;">
                                        <div class="p-2 border-bottom bg-light">
                                            <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-map-marker-alt mr-2"></i> Step 3: Destination</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group mb-3">
                                                <label class="small font-weight-bold text-muted mb-1">Target warehouse</label>
                                                <select id="target_storeroom" class="form-control form-control-sm select2" required>
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($storerooms as $storeroom)
                                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group mb-4">
                                                <label class="small font-weight-bold text-muted mb-1">Target rack</label>
                                                <select name="target_rack_id" id="target_rack" class="form-control form-control-sm select2" required>
                                                    <option value="">Select Rack</option>
                                                </select>
                                            </div>
                                            <hr>
                                            <button type="submit" id="btn-submit-transfer" class="btn-erp btn-erp-primary btn-block py-2 font-weight-bold">
                                                <i class="fas fa-exchange-alt mr-2"></i> Perform transfer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- ========================= BARCODE SCAN MODE ========================= --}}
                    <div class="tab-pane fade" id="scan-mode" role="tabpanel">
                        <div class="row">
                            <div class="col-md-9">

                                {{-- Locations --}}
                                <div class="erp-card mb-2" style="border-radius:12px;">
                                    <div class="p-2 border-bottom bg-light">
                                        <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-map-marker-alt mr-2"></i> Step 1: Select Locations</h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="row align-items-end">
                                            <div class="col-md-3">
                                                <label class="small font-weight-bold text-muted mb-1">From warehouse</label>
                                                <select id="scan_from_storeroom" class="form-control">
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($storerooms as $storeroom)
                                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small font-weight-bold text-muted mb-1">From rack</label>
                                                <select id="scan_from_rack" class="form-control">
                                                    <option value="">Select Rack</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small font-weight-bold text-muted mb-1">To warehouse</label>
                                                <select id="scan_to_storeroom" class="form-control">
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($storerooms as $storeroom)
                                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small font-weight-bold text-muted mb-1">To rack</label>
                                                <select id="scan_to_rack" class="form-control">
                                                    <option value="">Select Rack</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Scanner Input --}}
                                <div class="erp-card mb-2" style="border-radius:12px;">
                                    <div class="p-2 border-bottom bg-light">
                                        <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-barcode mr-2"></i> Step 2: Scan Barcode</h6>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="input-group mr-3" style="max-width:460px;">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" style="background: #05421c; color: #fff; border-color: #05421c;" style="border-radius:8px 0 0 8px;">
                                                        <i class="fas fa-barcode"></i>
                                                    </span>
                                                </div>
                                                <input type="text" id="barcode_input" class="form-control font-weight-bold"
                                                    placeholder="Scan or type barcode here, then press Enter..."
                                                    autocomplete="off" style="font-size:1.05rem; border-radius:0 8px 8px 0;">
                                            </div>
                                            <button id="btn_scan_manual" class="btn-erp btn-erp-outline px-4 mr-3">
                                                <i class="fas fa-search mr-1"></i> Add
                                            </button>
                                            <span id="scan-spinner" class="text-muted" style="display:none;">
                                                <i class="fas fa-spinner fa-spin"></i> Looking up...
                                            </span>
                                        </div>
                                        <div id="scan-error-msg" class="alert alert-danger mt-3 py-2 mb-0" style="display:none; border-radius:8px;"></div>
                                    </div>
                                </div>

                                {{-- Scanned Table --}}
                                <div class="erp-card mb-2" style="border-radius:12px; overflow:hidden;">
                                    <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 font-weight-bold" style="color: #05421c;">
                                            <i class="fas fa-list mr-2"></i> Step 3: Scanned Items
                                            <span id="scanned-count" class="badge-erp-status badge-erp-primary ml-2">0</span>
                                        </h6>
                                        <button type="button" id="btn-clear-scan-list" class="btn-erp btn-erp-danger btn-sm" style="display:none;">
                                            <i class="fas fa-trash mr-1"></i> Clear All
                                        </button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="erp-table table-bordered table-hover mb-0" id="scanned-table">
                                                <thead class="bg-light contrast-text">
                                                    <tr>
                                                        <th class="py-3" style="width:40px;">#</th>
                                                        <th class="py-3">Product / Design</th>
                                                        <th class="py-3">Size / Color</th>
                                                        <th class="py-3">Barcode</th>
                                                        <th class="py-3 text-center">Available</th>
                                                        <th class="py-3 text-center" style="width:130px;">Boxes to Transfer</th>
                                                        <th class="py-3 text-center" style="width:60px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="scanned-tbody">
                                                    <tr id="empty-scan-row">
                                                        <td colspan="7" class="text-center py-5 text-muted">
                                                            <i class="fas fa-barcode fa-3x mb-3 d-block" style="color:#e5e7eb;"></i>
                                                            No barcodes scanned yet. Select locations above, then scan a barcode.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Summary Panel -->
                            <div class="col-md-3">
                                <div class="erp-card mb-2" style="border-radius:12px; position:sticky; top:20px;">
                                    <div class="p-2 border-bottom bg-light">
                                        <h6 class="mb-0 font-weight-bold text-success"><i class="fas fa-exchange-alt mr-2"></i> Transfer Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 p-3 rounded" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                                            <div class="small text-muted font-weight-bold mb-1 text-uppercase">From</div>
                                            <div id="summary-from" class="font-weight-bold text-dark" style="font-size:0.9rem;">— not selected —</div>
                                        </div>
                                        <div class="mb-3 p-3 rounded" style="background:#eff6ff; border:1px solid #bfdbfe;">
                                            <div class="small text-muted font-weight-bold mb-1 text-uppercase">To</div>
                                            <div id="summary-to" class="font-weight-bold text-dark" style="font-size:0.9rem;">— not selected —</div>
                                        </div>
                                        <div class="mb-3 p-3 rounded text-center" style="background:#fefce8; border:1px solid #fef08a;">
                                            <div class="small text-muted font-weight-bold mb-1">Total Scanned Items</div>
                                            <div id="summary-total" class="h2 mb-0 font-weight-bold" style="color:#b45309;">0</div>
                                        </div>
                                        <div class="mb-4 p-3 rounded text-center" style="background:#e0e7ff; border:1px solid #c7d2fe;">
                                            <div class="small text-muted font-weight-bold mb-1">Total Boxes to Transfer</div>
                                            <div id="summary-boxes" class="h2 mb-0 font-weight-bold" style="color:#4338ca;">0</div>
                                        </div>
                                        <hr>
                                        <button id="btn-perform-scan-transfer" class="btn-erp btn-erp-primary btn-block py-2 font-weight-bold">
                                            <i class="fas fa-exchange-alt mr-2"></i> Perform transfer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /tab-content -->
            </div>
        </div>

    <script>
    $(function () {
        // Init Select2 for all manual-mode dropdowns
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // ============================== MANUAL MODE ==============================
        const container = $('#inventoryTable tbody');
        let nextPage = 1, loading = false;

        function loadMore(reset = false) {
            if (loading) return;
            
            loading = true;
            $('#loading-spinner').show();
            
            if (reset) { nextPage = 1; container.css('opacity', '0.5'); }
            $.ajax({
                url: "{{ route('admin.inventory.stock_transfer.search') }}",
                type: "GET",
                data: {
                    page: nextPage,
                    storeroom_id: $('#storeroom_filter').val(),
                    rack_id: $('#rack_filter').val(),
                    size_set_id: $('#size_set_filter').val(),
                    design_filter: $('#design_filter').val(),
                    product_id: $('#product_filter').val(),
                    color_id: $('#color_filter').val()
                },
                success: function(res) {
                    container.empty().css('opacity', '1').append(res.html);
                    nextPage = (res.meta && res.meta.current) || 1;
                    if (window.ErpPager) ErpPager.render($('#inventoryTable'), res.meta, function (pg) { nextPage = pg; loadMore(false); });
                    loading = false;
                    $('#loading-spinner').hide();
                    
                    if (container.is(':empty')) {
                        container.append('<tr><td colspan="6" class="text-center py-5 text-muted">No inventory found for selected filters.</td></tr>');
                        
                    }
                },
                error: function() { loading = false; $('#loading-spinner').hide(); container.css('opacity', '1'); toastr.error('Failed to load inventory.'); }
            });
        }
        loadMore();



        $('#apply_filters').on('click', function() { loadMore(true); });
        $('#design_filter').on('keydown', function(e) { if (e.which === 13) { e.preventDefault(); loadMore(true); } });
        $('#reset_filters').on('click', function() { $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #color_filter').val('').trigger('change'); loadMore(true); });

        $('#storeroom_filter').on('change', function() {
            let wh = $(this).val(), rack_filter = $('#rack_filter');
            rack_filter.html('<option value="">All Racks</option>');
            if (wh) $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh, function(d) { $.each(d, function(i, r) { rack_filter.append('<option value="'+r.id+'">'+r.name+'</option>'); }); });
        });
        $('#target_storeroom').on('change', function() {
            let wh = $(this).val(), tr = $('#target_rack');
            tr.html('<option value="">Select Rack</option>');
            if (wh) $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh, function(d) { $.each(d, function(i, r) { tr.append('<option value="'+r.id+'">'+r.name+'</option>'); }); });
        });

        $('#select-all').on('click', function() { $('.inventory-checkbox').prop('checked', this.checked); });
        $(document).on('click', '.inventory-checkbox', function() {
            $('#select-all').prop('checked', $('.inventory-checkbox:checked').length === $('.inventory-checkbox').length && $('.inventory-checkbox').length > 0);
        });

        $('#bulk-transfer-form').on('submit', function(e) {
            e.preventDefault();
            if (!$('.inventory-checkbox:checked').length) { toastr.error('Please select at least one item to transfer.'); return; }
            if (!$('#target_rack').val()) { toastr.error('Please select a destination rack.'); return; }
            let btn = $('#btn-submit-transfer').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSING...');
            $.ajax({
                url: "{{ route('admin.inventory.stock_transfer.transfer') }}", type: "POST", data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> Perform transfer');
                    if (res.status == 'success') { toastr.success(res.message); loadMore(true); } else { toastr.error(res.message); }
                },
                error: function() { btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> Perform transfer'); toastr.error('An error occurred during transfer.'); }
            });
        });

        // ============================== BARCODE SCAN MODE ==============================
        let scannedItems = {}, scanRowCount = 0;

        function loadRacks(storeName, rackName, isFromRack = false) {
            let wh = $(storeName).val(), rack = $(rackName);
            rack.html('<option value="">Select Rack</option>');
            if (isFromRack && wh) rack.append('<option value="all">All Racks</option>');
            if (wh) $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh, function(d) { $.each(d, function(i, r) { rack.append('<option value="'+r.id+'">'+r.name+'</option>'); }); });
            updateSummary();
        }

        $('#scan_from_storeroom').on('change', function() { loadRacks('#scan_from_storeroom', '#scan_from_rack', true); });
        $('#scan_to_storeroom').on('change', function() { loadRacks('#scan_to_storeroom', '#scan_to_rack', false); });
        $('#scan_from_rack, #scan_to_rack').on('change', updateSummary);

        function updateSummary() {
            let fwh = $('#scan_from_storeroom option:selected').text();
            let frk = $('#scan_from_rack option:selected').text();
            let twh = $('#scan_to_storeroom option:selected').text();
            let trk = $('#scan_to_rack option:selected').text();
            $('#summary-from').text($('#scan_from_rack').val() ? fwh + ' / ' + frk : '— not selected —');
            $('#summary-to').text($('#scan_to_rack').val() ? twh + ' / ' + trk : '— not selected —');
            $('#summary-total').text(Object.keys(scannedItems).length);
            $('#scanned-count').text(Object.keys(scannedItems).length);

            let totalBoxes = 0;
            $.each(scannedItems, function(invId, item) {
                totalBoxes += parseInt(item.qty) || 0;
            });
            $('#summary-boxes').text(totalBoxes);
        }

        $('#barcode_input').on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); doScan(); } });
        $('#btn_scan_manual').on('click', doScan);

        function doScan() {
            let barcode = $.trim($('#barcode_input').val()).toUpperCase();
            if (!barcode) { $('#barcode_input').focus(); return; }

            if (!$('#scan_from_rack').val()) { toastr.warning('Please select a FROM rack before scanning.'); return; }
            if (!$('#scan_to_rack').val())   { toastr.warning('Please select a TO rack before scanning.'); return; }

            $('#scan-error-msg').hide();
            $('#scan-spinner').show();
            $('#btn_scan_manual').prop('disabled', true);

            $.ajax({
                url: "{{ route('admin.inventory.stock_transfer.scan_barcode') }}",
                type: "GET",
                data: { barcode: barcode, from_storeroom_id: $('#scan_from_storeroom').val(), from_rack_id: $('#scan_from_rack').val() },
                success: function(res) {
                    $('#scan-spinner').hide();
                    $('#btn_scan_manual').prop('disabled', false);
                    $('#barcode_input').val('').focus();

                    if (res.status !== 'ok') { showScanError(res.message); return; }

                    let invId = res.inventory_id;

                    // Already scanned: bump qty by 1
                    if (scannedItems[invId]) {
                        let row = $('#scanned-tbody tr[data-inv-id="'+invId+'"]');
                        let inp = row.find('.scan-qty-input');
                        let cur = parseInt(inp.val()) || 1;
                        let max = parseInt(inp.attr('max')) || 1;
                        if (cur < max) { inp.val(cur + 1); scannedItems[invId].qty = cur + 1; }
                        row.addClass('scan-row-new');
                        setTimeout(function() { row.removeClass('scan-row-new'); }, 1400);
                        toastr.info('Already in list – quantity updated to ' + (cur < max ? cur + 1 : cur) + '.');
                        updateSummary();
                        return;
                    }

                    scanRowCount++;
                    scannedItems[invId] = { qty: 1, data: res };
                    $('#empty-scan-row').hide();
                    $('#btn-clear-scan-list').show();

                    let row = $(`<tr class="scan-row-new" data-inv-id="${invId}">
                        <td class="text-muted font-weight-bold">${scanRowCount}</td>
                        <td>
                            <strong class="text-dark">${res.product_name}</strong>
                            <div class="small text-muted">${res.design_number}</div>
                        </td>
                        <td>
                            <div>${res.size_set_name}</div>
                            <div class="small text-muted">${res.color_name}</div>
                        </td>
                        <td><span class="badge badge-secondary px-2 py-1" style="font-size:0.8rem; letter-spacing:0.5px;">${res.barcode}</span></td>
                        <td class="text-center font-weight-bold">${res.total_boxes} <small class="text-muted">boxes</small></td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm qty-input scan-qty-input"
                                value="1" min="1" max="${res.total_boxes}"
                                data-inv-id="${invId}" style="width:80px; margin:0 auto;">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-erp btn-erp-danger btn-sm btn-remove-scan" data-inv-id="${invId}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>`);

                    $('#scanned-tbody').append(row);
                    setTimeout(function() { row.removeClass('scan-row-new'); }, 1400);
                    updateSummary();
                    toastr.success(`Added: ${res.product_name} (${res.color_name})`);
                },
                error: function(xhr) {
                    $('#scan-spinner').hide();
                    $('#btn_scan_manual').prop('disabled', false);
                    $('#barcode_input').val('').focus();
                    let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Barcode not found in selected location.';
                    showScanError(msg);
                }
            });
        }

        function showScanError(msg) {
            $('#scan-error-msg').text(msg).show();
            $('#barcode_input').addClass('border-danger').focus();
            setTimeout(function() { $('#scan-error-msg').fadeOut(400); $('#barcode_input').removeClass('border-danger'); }, 5000);
        }

        $(document).on('input change', '.scan-qty-input', function() {
            let invId = $(this).data('inv-id');
            let max   = parseInt($(this).attr('max')) || 1;
            let min   = parseInt($(this).attr('min')) || 1;
            let val   = parseInt($(this).val()) || 1;

            if (val > max) {
                $(this).val(max);
                val = max;
                $(this).addClass('border-danger text-danger').attr('title', 'Cannot exceed available boxes (' + max + ')');
                toastr.warning('Maximum allowed is ' + max + ' boxes for this item.');
            } else if (val < min) {
                $(this).val(min);
                val = min;
                $(this).removeClass('border-danger text-danger').removeAttr('title');
            } else {
                $(this).removeClass('border-danger text-danger').removeAttr('title');
            }

            if (scannedItems[invId]) scannedItems[invId].qty = val;
            updateSummary();
        });

        $(document).on('click', '.btn-remove-scan', function() {
            let invId = $(this).data('inv-id');
            delete scannedItems[invId];
            $(this).closest('tr').remove();
            updateSummary();
            if (Object.keys(scannedItems).length === 0) { $('#empty-scan-row').show(); $('#btn-clear-scan-list').hide(); }
        });

        $('#btn-clear-scan-list').on('click', function() {
            if (!confirm('Clear all scanned items?')) return;
            scannedItems = {}; scanRowCount = 0;
            $('#scanned-tbody tr:not(#empty-scan-row)').remove();
            $('#empty-scan-row').show();
            $(this).hide();
            updateSummary();
        });

        $('#btn-perform-scan-transfer').on('click', function() {
            if (!Object.keys(scannedItems).length) { toastr.error('No items scanned yet.'); return; }
            if (!$('#scan_to_rack').val()) { toastr.error('Please select a TO rack.'); return; }

            let formData = {
                _token: '{{ csrf_token() }}',
                target_rack_id: $('#scan_to_rack').val(),
                'inventory_ids[]': []
            };
            $.each(scannedItems, function(invId, item) {
                formData['inventory_ids[]'].push(invId);
                formData['transfer_qty[' + invId + ']'] = item.qty;
            });

            let btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSING...');
            $.ajax({
                url: "{{ route('admin.inventory.stock_transfer.transfer') }}", type: "POST", data: formData,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> Perform transfer');
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        scannedItems = {}; scanRowCount = 0;
                        $('#scanned-tbody tr:not(#empty-scan-row)').remove();
                        $('#empty-scan-row').show();
                        $('#btn-clear-scan-list').hide();
                        updateSummary();
                    } else { toastr.error(res.message); }
                },
                error: function() { btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> Perform transfer'); toastr.error('An error occurred during transfer.'); }
            });
        });

        // When scan tab becomes visible: init select2 on its dropdowns + focus barcode input
        $('#scan-tab').on('shown.bs.tab', function() {
            $('#scan_from_storeroom, #scan_from_rack, #scan_to_storeroom, #scan_to_rack').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({ theme: 'bootstrap4', width: '100%' });
                }
            });
            $('#barcode_input').focus();
        });

    });
    </script>
@endsection
