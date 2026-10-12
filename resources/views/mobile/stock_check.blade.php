<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#05421c">
    <title>Warehouse Stock Check - Mobile</title>

    <!-- Google Font & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin_assets/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('admin_assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --brand-green: #05421c;
            --brand-green-light: #0d6830;
            --brand-green-subtle: #edf5ee;
            --brand-yellow: #fcee21;
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --border-color: #cbd5e1;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --accent-blue: #0284c7;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 10px rgba(0,0,0,0.08);
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            line-height: 1.4;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Mobile Shell: Centered max-width on desktop, full width on phones */
        .mobile-shell {
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            min-height: 100vh;
            box-shadow: 0 0 25px rgba(0,0,0,0.06);
        }

        /* Header Bar */
        .app-top-bar {
            background: var(--brand-green);
            color: #ffffff;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(5, 66, 28, 0.25);
        }

        .bar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bar-logo-icon {
            width: 34px;
            height: 34px;
            background: #ffffff;
            color: var(--brand-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
        }

        .bar-title h1 {
            font-size: 16px;
            font-weight: 800;
            line-height: 1.1;
            color: #ffffff;
        }

        .bar-title span {
            font-size: 11px;
            color: #d1fae5;
            font-weight: 500;
        }

        .bar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-top-action {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #ffffff;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-top-action:active {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0.92);
        }

        /* UPPER TOTALS STRIP (TOTAL ACTUAL BOXES & TOTAL ACTUAL PCS) */
        .upper-totals-section {
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px 16px;
            position: sticky;
            top: 58px;
            z-index: 90;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }

        .upper-totals-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .stat-card {
            border-radius: var(--radius-md);
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 1.5px solid transparent;
            transition: all 0.2s ease;
        }

        .stat-card.boxes-card {
            background: #f0fdf4;
            border-color: #bbf7d0;
            border-left: 4px solid var(--brand-green);
        }

        .stat-card.pcs-card {
            background: #f0f9ff;
            border-color: #bae6fd;
            border-left: 4px solid var(--accent-blue);
        }

        .stat-card-label {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stat-card.boxes-card .stat-card-label { color: #166534; }
        .stat-card.pcs-card .stat-card-label { color: #075985; }

        .stat-card-val-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .stat-card-number {
            font-size: 26px;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card.boxes-card .stat-card-number { color: var(--brand-green); }
        .stat-card.pcs-card .stat-card-number { color: var(--accent-blue); }

        .stat-card-unit {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
        }

        /* Filter Status Note */
        .filter-status-row {
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .status-badge {
            background: #e2e8f0;
            color: #334155;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 11px;
        }

        .status-badge.active {
            background: #edf5ee;
            color: var(--brand-green);
            border: 1px solid #cce3d0;
        }

        /* FILTERS FORM SECTION */
        .filters-container {
            padding: 14px 16px;
            background: #ffffff;
        }

        .filters-card {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 14px;
        }

        .filters-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .filters-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .btn-reset-link {
            font-size: 12px;
            font-weight: 700;
            color: #dc2626;
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 6px;
            border-radius: 6px;
        }

        .btn-reset-link:active {
            background: #fee2e2;
        }

        /* Form Controls */
        .form-group-filter {
            margin-bottom: 12px;
        }

        .form-group-filter:last-child {
            margin-bottom: 0;
        }

        .filter-label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .filter-label i {
            color: var(--brand-green);
            font-size: 11px;
            width: 14px;
            text-align: center;
        }

        /* Custom Select2 Styling for Mobile Touch */
        .select2-container--bootstrap4 .select2-selection--single {
            height: 42px !important;
            line-height: 40px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: var(--radius-md) !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            background-color: #ffffff !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            color: var(--text-dark) !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }

        .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
            border-color: var(--brand-green) !important;
            box-shadow: 0 0 0 3px rgba(5, 66, 28, 0.15) !important;
        }

        .select2-dropdown {
            border: 1.5px solid var(--brand-green) !important;
            border-radius: var(--radius-md) !important;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
            font-size: 13.5px !important;
        }

        .select2-results__option {
            padding: 9px 12px !important;
            font-size: 13px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .select2-results__option--highlighted {
            background-color: var(--brand-green) !important;
            color: #ffffff !important;
        }

        /* RESULTS AREA */
        .results-section {
            padding: 10px 16px 30px 16px;
            background: var(--bg-page);
        }

        /* Initial / Empty Message Box */
        .prompt-box {
            text-align: center;
            padding: 36px 16px;
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            border-radius: var(--radius-lg);
            color: var(--text-muted);
        }

        .prompt-box-icon {
            font-size: 36px;
            color: var(--brand-green);
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .prompt-box-title {
            font-size: 14.5px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .prompt-box-text {
            font-size: 12.5px;
            color: var(--text-muted);
            max-width: 280px;
            margin: 0 auto;
        }

        /* Compact Stock Item Card */
        .item-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md);
            margin-bottom: 10px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .item-card-header {
            padding: 10px 12px;
            background: #fafdfa;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .item-design-badge {
            background: #edf5ee;
            color: var(--brand-green);
            font-size: 13px;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 5px;
            border: 1px solid #cce3d0;
        }

        .item-location {
            font-size: 11.5px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .item-card-body {
            padding: 10px 12px;
        }

        .item-product-name {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .item-meta-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .meta-pill {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        /* Card Stock Summary Row */
        .item-totals-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .item-stat-col {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .item-stat-num {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
        }

        .item-stat-num.green { color: var(--brand-green); }
        .item-stat-num.blue { color: var(--accent-blue); }

        .item-stat-txt {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* Color details list */
        .item-colors-container {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .color-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            padding: 4px 6px;
            border-radius: 4px;
            background: #fafafa;
        }

        .color-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .color-chip {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 1px solid rgba(0,0,0,0.2);
        }

        .color-counts {
            font-weight: 700;
            font-size: 11.5px;
        }

        .color-counts .bxs { color: var(--brand-green); margin-right: 6px; }
        .color-counts .pcs { color: var(--accent-blue); }

        /* Spinner */
        .loading-wrap {
            text-align: center;
            padding: 24px 0;
            display: none;
        }

        /* Toast */
        .mobile-toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1e293b;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 600;
            padding: 9px 16px;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 999;
            opacity: 0;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .mobile-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

<div class="mobile-shell">
    <!-- APP TOP BAR -->
    <header class="app-top-bar">
        <div class="bar-brand">
            <div class="bar-logo-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="bar-title">
                <h1>Snapkid Stock</h1>
                <span>Physical Warehouse Stock</span>
            </div>
        </div>
        <div class="bar-actions">
            <button type="button" class="btn-top-action" id="btnShare" title="Share URL">
                <i class="fas fa-share-alt"></i>
            </button>
            <button type="button" class="btn-top-action" id="btnRefresh" title="Refresh">
                <i class="fas fa-sync-alt" id="refreshSpin"></i>
            </button>
        </div>
    </header>

    <!-- UPPER TOTALS STRIP (TOTAL ACTUAL BOXES & TOTAL ACTUAL PCS) -->
    <div class="upper-totals-section">
        <div class="upper-totals-grid">
            <!-- Actual Boxes -->
            <div class="stat-card boxes-card">
                <span class="stat-card-label">
                    <i class="fas fa-box"></i> Actual Boxes
                </span>
                <div class="stat-card-val-row">
                    <span class="stat-card-number" id="header_total_boxes">0</span>
                    <span class="stat-card-unit">Boxes</span>
                </div>
            </div>

            <!-- Actual Pcs -->
            <div class="stat-card pcs-card">
                <span class="stat-card-label">
                    <i class="fas fa-tshirt"></i> Actual Pcs
                </span>
                <div class="stat-card-val-row">
                    <span class="stat-card-number" id="header_total_pcs">0</span>
                    <span class="stat-card-unit">Pcs</span>
                </div>
            </div>
        </div>

        <div class="filter-status-row">
            <span id="filterStatusText">Select any filter to check stock</span>
            <span id="activeFilterBadge" class="status-badge">0 Filter Selected</span>
        </div>
    </div>

    <!-- 5 FILTERS SECTION -->
    <div class="filters-container">
        <div class="filters-card">
            <div class="filters-title-row">
                <span class="filters-title">
                    <i class="fas fa-filter text-success"></i> Stock Filters
                </span>
                <button type="button" id="btnResetFilters" class="btn-reset-link">
                    <i class="fas fa-undo"></i> Reset Filters
                </button>
            </div>

            <!-- 1. Warehouse -->
            <div class="form-group-filter">
                <label class="filter-label" for="storeroom_filter">
                    <i class="fas fa-warehouse"></i> 1. Warehouse
                </label>
                <select id="storeroom_filter" class="form-control select2">
                    <option value="">All Warehouses</option>
                    @foreach($storerooms as $room)
                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 2. Rack -->
            <div class="form-group-filter">
                <label class="filter-label" for="rack_filter">
                    <i class="fas fa-th-large"></i> 2. Rack
                </label>
                <select id="rack_filter" class="form-control select2">
                    <option value="">All Racks</option>
                    @foreach($racks as $rack)
                        <option value="{{ $rack->id }}" data-storeroom="{{ $rack->storeroom_id }}">
                            {{ $rack->name }} ({{ $rack->storeroom->name ?? 'Warehouse' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Design No. -->
            <div class="form-group-filter">
                <label class="filter-label" for="design_filter">
                    <i class="fas fa-hashtag"></i> 3. Design No.
                </label>
                <select id="design_filter" class="form-control select2">
                    <option value="">All Design Nos.</option>
                    @foreach($designs as $d)
                        <option value="{{ $d->design_number }}">{{ $d->design_number }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 4. Product -->
            <div class="form-group-filter">
                <label class="filter-label" for="product_filter">
                    <i class="fas fa-tshirt"></i> 4. Product
                </label>
                <select id="product_filter" class="form-control select2">
                    <option value="">All Products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->design_number }} - {{ ($p->series->name ?? '') . ' ' . $p->name_of_garment }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 5. Size Set -->
            <div class="form-group-filter">
                <label class="filter-label" for="size_set_filter">
                    <i class="fas fa-ruler-combined"></i> 5. Size Set
                </label>
                <select id="size_set_filter" class="form-control select2">
                    <option value="">All Size Sets</option>
                    @foreach($size_sets as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- RESULTS FEED -->
    <div class="results-section">
        <!-- Spinner -->
        <div id="loadingIndicator" class="loading-wrap">
            <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
            <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Calculating stock...</div>
        </div>

        <!-- Initial Prompt: Shown when no filter is selected -->
        <div id="initialPromptBox" class="prompt-box">
            <div class="prompt-box-icon"><i class="fas fa-hand-point-up"></i></div>
            <div class="prompt-box-title">Select Any Filter</div>
            <div class="prompt-box-text">Choose a Warehouse, Rack, Design No., Product, or Size Set above to see actual stock totals.</div>
        </div>

        <!-- Empty Results Message -->
        <div id="emptyResultBox" class="prompt-box" style="display: none;">
            <div class="prompt-box-icon text-muted"><i class="fas fa-box-open"></i></div>
            <div class="prompt-box-title">No Stock Found</div>
            <div class="prompt-box-text">No physical stock records match your selected filter.</div>
        </div>

        <!-- Items Cards Container -->
        <div id="stockItemsContainer"></div>
    </div>
</div>

<!-- Toast -->
<div id="toastBox" class="mobile-toast">
    <i class="fas fa-check-circle text-success"></i>
    <span id="toastText">Done</span>
</div>

<!-- jQuery & Select2 -->
<script src="{{ asset('admin_assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
$(function() {
    // Initialize Select2 with Bootstrap 4 theme
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        dropdownAutoWidth: true
    });

    // Cache original rack options
    const originalRackOptions = $('#rack_filter option').clone();

    // DOM Elements
    const $totalBoxes = $('#header_total_boxes');
    const $totalPcs = $('#header_total_pcs');
    const $statusText = $('#filterStatusText');
    const $activeBadge = $('#activeFilterBadge');
    const $initialPrompt = $('#initialPromptBox');
    const $emptyResult = $('#emptyResultBox');
    const $itemsContainer = $('#stockItemsContainer');
    const $loader = $('#loadingIndicator');
    const $toast = $('#toastBox');
    const $toastText = $('#toastText');

    let isFetching = false;

    function showToast(msg) {
        $toastText.text(msg);
        $toast.addClass('show');
        setTimeout(() => $toast.removeClass('show'), 2000);
    }

    // Dynamic Rack Filtering on Warehouse Change
    $('#storeroom_filter').on('change', function() {
        const storeroomId = $(this).val();
        const currentRackVal = $('#rack_filter').val();

        $('#rack_filter').empty().append('<option value="">All Racks</option>');

        if (storeroomId) {
            originalRackOptions.each(function() {
                const stId = $(this).data('storeroom');
                if (stId && String(stId) === String(storeroomId)) {
                    $('#rack_filter').append($(this).clone());
                }
            });
        } else {
            originalRackOptions.each(function() {
                if ($(this).val()) {
                    $('#rack_filter').append($(this).clone());
                }
            });
        }

        if (currentRackVal && $(`#rack_filter option[value="${currentRackVal}"]`).length) {
            $('#rack_filter').val(currentRackVal);
        }
        $('#rack_filter').trigger('change.select2');
    });

    // Count Active Filters
    function getFilterCount() {
        let count = 0;
        if ($('#storeroom_filter').val()) count++;
        if ($('#rack_filter').val()) count++;
        if ($('#design_filter').val()) count++;
        if ($('#product_filter').val()) count++;
        if ($('#size_set_filter').val()) count++;
        return count;
    }

    // Main Function: Check and Calculate Stock based on selected filters
    function executeFilterStock() {
        const activeCount = getFilterCount();

        if (activeCount === 0) {
            // No filter selected: Reset to zero and show prompt
            $totalBoxes.text('0');
            $totalPcs.text('0');
            $activeBadge.text('0 Filter Selected').removeClass('active');
            $statusText.text('Select any filter to check stock');
            $initialPrompt.show();
            $emptyResult.hide();
            $itemsContainer.empty();
            $loader.hide();
            return;
        }

        // Active filters exist: Update badge and query stock
        $activeBadge.text(activeCount + (activeCount === 1 ? ' Filter Active' : ' Filters Active')).addClass('active');
        $statusText.text('Calculating filtered stock...');
        $initialPrompt.hide();
        $emptyResult.hide();
        $itemsContainer.empty();
        $loader.show();

        if (isFetching) return;
        isFetching = true;

        const requestData = {
            storeroom_id: $('#storeroom_filter').val(),
            rack_id: $('#rack_filter').val(),
            design_filter: $('#design_filter').val(),
            product_id: $('#product_filter').val(),
            size_set_id: $('#size_set_filter').val()
        };

        $.ajax({
            url: "{{ route('mobile.stock.data') }}",
            type: "GET",
            data: requestData,
            dataType: "json",
            success: function(res) {
                isFetching = false;
                $loader.hide();

                const boxes = Number(res.total_boxes || 0);
                const pcs = Number(res.total_pcs || 0);

                // Update the upper totals immediately
                $totalBoxes.text(boxes.toLocaleString());
                $totalPcs.text(pcs.toLocaleString());
                $statusText.text(`Showing stock for selected filters (${boxes} boxes, ${pcs} pcs)`);

                if (!res.items || res.items.length === 0) {
                    $emptyResult.show();
                    return;
                }

                $emptyResult.hide();
                renderCards(res.items);
            },
            error: function() {
                isFetching = false;
                $loader.hide();
                $statusText.text('Failed to load stock data');
                showToast('Error checking stock');
            }
        });
    }

    // Render Clean Compact Stock Cards
    function renderCards(items) {
        $itemsContainer.empty();

        items.forEach(item => {
            let colorRowsHtml = '';
            if (item.colors && item.colors.length > 0) {
                item.colors.forEach(c => {
                    colorRowsHtml += `
                        <div class="color-row">
                            <span class="color-label">
                                <span class="color-chip" style="background-color: ${c.color_code || '#64748b'};"></span>
                                <span>${c.color_name}</span>
                            </span>
                            <span class="color-counts">
                                <span class="bxs">${c.boxes} Bx</span>
                                <span class="pcs">${c.pcs} Pcs</span>
                            </span>
                        </div>
                    `;
                });
            } else {
                colorRowsHtml = '<div style="font-size:11.5px; color:#94a3b8; text-align:center;">No color breakdown</div>';
            }

            const cardHtml = `
                <div class="item-card">
                    <div class="item-card-header">
                        <span class="item-design-badge">#${item.design_number}</span>
                        <span class="item-location">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            ${item.warehouse_name} • ${item.rack_name}
                        </span>
                    </div>
                    <div class="item-card-body">
                        <div class="item-product-name">${item.product_name}</div>
                        <div class="item-meta-row">
                            <span class="meta-pill"><i class="fas fa-ruler-combined mr-1"></i> ${item.size_set_name}</span>
                            <span class="meta-pill"><i class="fas fa-layer-group mr-1"></i> ${item.pcs_per_box} pcs/bx</span>
                        </div>

                        <div class="item-totals-row">
                            <div class="item-stat-col">
                                <span class="item-stat-num green">${item.total_boxes}</span>
                                <span class="item-stat-txt">Boxes</span>
                            </div>
                            <div class="item-stat-col">
                                <span class="item-stat-num blue">${item.total_pcs.toLocaleString()}</span>
                                <span class="item-stat-txt">Pieces</span>
                            </div>
                        </div>

                        <div class="item-colors-container">
                            ${colorRowsHtml}
                        </div>
                    </div>
                </div>
            `;
            $itemsContainer.append(cardHtml);
        });
    }

    // ON SELECT ANY FILTER: Trigger calculation immediately
    $('#storeroom_filter, #rack_filter, #design_filter, #product_filter, #size_set_filter').on('change', function() {
        executeFilterStock();
    });

    // Reset Filters
    $('#btnResetFilters').on('click', function() {
        $('#storeroom_filter').val('').trigger('change.select2');
        $('#rack_filter').empty().append('<option value="">All Racks</option>');
        originalRackOptions.each(function() {
            if ($(this).val()) $('#rack_filter').append($(this).clone());
        });
        $('#rack_filter').val('').trigger('change.select2');
        $('#design_filter').val('').trigger('change.select2');
        $('#product_filter').val('').trigger('change.select2');
        $('#size_set_filter').val('').trigger('change.select2');

        executeFilterStock();
        showToast('Filters reset');
    });

    // Refresh Button
    $('#btnRefresh').on('click', function() {
        $('#refreshSpin').addClass('fa-spin');
        executeFilterStock();
        setTimeout(() => {
            $('#refreshSpin').removeClass('fa-spin');
            showToast('Refreshed');
        }, 500);
    });

    // Share Button
    $('#btnShare').on('click', function() {
        const url = window.location.href.split('?')[0];
        if (navigator.share) {
            navigator.share({
                title: 'Snapkid Warehouse Stock Check',
                url: url
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(url).then(() => {
                showToast('Link copied to clipboard');
            }).catch(() => {
                prompt('Copy this URL:', url);
            });
        }
    });
});
</script>

</body>
</html>
