@extends('admin.layouts.app')

@section('content')
<style>
    /* CSS Variables for Premium Theme */
    :root {
        --bg-color: #f3f4f6;
        --card-bg: #ffffff;
        --border-color: #e5e7eb;
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --secondary: #ec4899;
        --success: #10b981;
        --warning: #f59e0b;
        --info: #3b82f6;
        
        --panel-gap: 20px;
    }

    .content-wrapper {
        background-color: var(--bg-color);
        min-height: calc(100vh - 57px);
        padding: 20px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    /* Layout */
    .spa-container {
        display: flex;
        gap: var(--panel-gap);
        height: calc(100vh - 120px);
        align-items: stretch;
    }

    /* Common Panel Styles */
    .spa-panel {
        background: var(--card-bg);
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
    }

    .spa-panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        z-index: 10;
    }

    .spa-panel-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .spa-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
        background: #f9fafb;
    }

    /* Scrollbar styling */
    .spa-panel-body::-webkit-scrollbar {
        width: 6px;
    }
    .spa-panel-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .spa-panel-body::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 10px;
    }

    /* Panels Sizing */
    .panel-designs { flex: 0 0 300px; }
    .panel-lots { flex: 0 0 250px; }
    .panel-details { flex: 1; }

    /* Filter Inputs */
    .spa-search-box {
        margin-top: 15px;
        position: relative;
    }
    .spa-search-box input, .spa-search-box select {
        width: 100%;
        padding: 10px 15px 10px 35px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: #f9fafb;
        font-size: 0.9rem;
        transition: all 0.2s;
        margin-bottom: 10px;
    }
    .spa-search-box input:focus, .spa-search-box select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: #fff;
    }
    .spa-search-box i {
        position: absolute;
        top: 12px;
        left: 12px;
        color: #9ca3af;
    }

    /* List Items */
    .list-item {
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        cursor: pointer;
        border: 1px solid transparent;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .list-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-color: #e5e7eb;
    }
    .list-item.active {
        background: var(--primary);
        color: white;
        box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
    }
    
    .list-item-title {
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 4px;
        color: var(--text-main);
    }
    .list-item.active .list-item-title {
        color: white;
    }
    .list-item-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .list-item.active .list-item-meta {
        color: rgba(255,255,255,0.8);
    }

    .badge-soft {
        background: #f3f4f6;
        color: #4b5563;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .list-item.active .badge-soft {
        background: rgba(255,255,255,0.2);
        color: #fff;
    }

    .qty-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #eef2ff;
        color: var(--primary);
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.8rem;
    }
    .list-item.active .qty-badge {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    /* Lots List */
    .lot-item {
        padding: 15px;
        background: #fff;
        border-radius: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
    .lot-item:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    .lot-item.active {
        border-color: var(--secondary);
        background: #fdf2f8;
        color: var(--secondary);
    }
    .lot-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
    }
    .lot-item.active .lot-icon {
        background: var(--secondary);
        color: white;
    }

    /* Details Panel */
    .details-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #9ca3af;
        text-align: center;
    }
    .details-placeholder i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        padding: 10px;
    }

    .stage-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e5e7eb;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .stage-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.04);
    }

    .stage-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    .stage-card.stage-primary::before { background: var(--primary); }
    .stage-card.stage-warning::before { background: var(--warning); }
    .stage-card.stage-info::before { background: var(--info); }
    .stage-card.stage-secondary::before { background: var(--secondary); }
    .stage-card.stage-dark::before { background: #1e293b; }
    .stage-card.stage-success::before { background: var(--success); }

    .stage-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        color: #64748b;
        margin-bottom: 5px;
    }
    .stage-location {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 15px;
    }
    .stage-qty {
        font-size: 2rem;
        font-weight: 900;
    }
    
    .stage-card.stage-primary .stage-qty { color: var(--primary); }
    .stage-card.stage-warning .stage-qty { color: var(--warning); }
    .stage-card.stage-info .stage-qty { color: var(--info); }
    .stage-card.stage-secondary .stage-qty { color: var(--secondary); }
    .stage-card.stage-dark .stage-qty { color: #1e293b; }
    .stage-card.stage-success .stage-qty { color: var(--success); }

    /* Loader */
    .loader {
        border: 3px solid #f3f3f3;
        border-radius: 50%;
        border-top: 3px solid var(--primary);
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    /* Right panel detailed adjustments */
    .panel-details .content-wrapper {
        min-height: auto !important;
        padding: 0 !important;
        background: transparent !important;
    }
    .panel-details .content {
        padding: 0 !important;
    }
    .panel-details .container-fluid {
        padding-left: 0;
        padding-right: 0;
    }

</style>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 style="font-weight: 800; color: #1e293b; margin: 0;">
            <i class="fas fa-chart-pie mr-2" style="color: var(--primary);"></i> Design WIP Dashboard
        </h2>
        <div>
            <button class="btn btn-primary" onclick="loadDesigns()" style="border-radius: 8px; font-weight: 600;">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </div>
    </div>

    <div class="spa-container">
        <!-- 1. Designs Panel -->
        <div class="spa-panel panel-designs">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title">
                    <i class="fas fa-tshirt"></i> Designs
                </h3>
                <div class="spa-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="filter_design" placeholder="Search Design No...">

                    <button class="btn btn-block btn-sm mt-2" style="background: #eef2ff; color: var(--primary); font-weight: 700; border-radius: 8px;" onclick="loadDesigns()">
                        Apply Filters
                    </button>
                </div>
            </div>
            <div class="spa-panel-body" id="designs_list">
                <div class="loader"></div>
            </div>
        </div>

        <!-- 2. Lots Panel -->
        <div class="spa-panel panel-lots">
            <div class="spa-panel-header">
                <h3 class="spa-panel-title">
                    <i class="fas fa-layer-group"></i> Lots
                </h3>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">
                    Select a design to view its lots
                </div>
            </div>
            <div class="spa-panel-body" id="lots_list">
                <div class="details-placeholder">
                    <i class="fas fa-hand-pointer"></i>
                    <p>Select a design<br>from the left panel.</p>
                </div>
            </div>
        </div>

        <!-- 3. Details Panel -->
        <div class="spa-panel panel-details">
            <div class="spa-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 class="spa-panel-title">
                        <i class="fas fa-chart-bar"></i> WIP Details
                    </h3>
                    <div id="details_subtitle" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px; font-weight: 600;">
                        Select a lot to view its current stage breakdown
                    </div>
                </div>
                <div>
                    <!-- Stage filter could go here if needed -->
                </div>
            </div>
            <div class="spa-panel-body">
                <div id="details_list">
                    <div class="details-placeholder">
                        <i class="fas fa-project-diagram"></i>
                        <p>Select a lot<br>to view WIP details.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
    let activeDesign = null;
    let activeLot = null;

    $(document).ready(function() {
        loadDesigns();
        
        // Instant search on typing for design number
        let typingTimer;
        $('#filter_design').on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(loadDesigns, 500);
        });
    });

    function loadDesigns() {
        $('#designs_list').html('<div class="loader"></div>');
        $('#lots_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-hand-pointer"></i>
                <p>Select a design<br>from the left panel.</p>
            </div>
        `);
        $('#details_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-project-diagram"></i>
                <p>Select a lot<br>to view WIP details.</p>
            </div>
        `);
        $('#details_subtitle').text('Select a lot to view its current stage breakdown');

        let data = {
            design_no: $('#filter_design').val()
        };

        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.designs') }}",
            type: "GET",
            data: data,
            success: function(res) {
                if(res.status) {
                    let html = '';
                    if(res.data.length === 0) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No designs found.</div>';
                    } else {
                        res.data.forEach(function(item) {
                            html += `
                                <div class="list-item" onclick="selectDesign('${item.design_no}', this)">
                                    <div class="list-item-title">${item.product_name} ${item.design_no}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500; margin-top: 4px;">Lots: <strong style="color: var(--primary);">${item.lot_count}</strong></div>
                                    <div class="qty-badge">${item.total_qty}</div>
                                </div>
                            `;
                        });
                    }
                    $('#designs_list').html(html);
                }
            },
            error: function() {
                $('#designs_list').html('<div class="text-danger text-center mt-3">Error loading designs.</div>');
            }
        });
    }

    function selectDesign(designNo, element) {
        $('.list-item').removeClass('active');
        $(element).addClass('active');
        activeDesign = designNo;
        
        // Reset details
        $('#details_list').html(`
            <div class="details-placeholder">
                <i class="fas fa-project-diagram"></i>
                <p>Select a lot<br>to view WIP details.</p>
            </div>
        `);
        $('#details_subtitle').text('Select a lot to view its current stage breakdown');

        loadLots(designNo);
    }

    function loadLots(designNo) {
        $('#lots_list').html('<div class="loader"></div>');
        
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.lots') }}",
            type: "GET",
            data: { design_no: designNo },
            success: function(res) {
                if(res.status) {
                    let html = '';
                    if(res.data.length === 0) {
                        html = '<div class="text-center text-muted mt-4" style="font-weight: 600;">No lots found.</div>';
                    } else {
                        res.data.forEach(function(item) {
                            let lotStr = item.lot_no;
                            let isBase = (lotStr === designNo);
                            html += `
                                <div class="lot-item" onclick="selectLot('${item.lot_no}', this)">
                                    <div class="lot-icon">
                                        <i class="fas ${isBase ? 'fa-box' : 'fa-layer-group'}"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-size: 0.95rem; font-weight: 700;">${item.lot_no}</div>
                                        ${isBase ? '<div style="font-size: 0.7rem; color: #9ca3af;">Base Design Lot</div>' : ''}
                                    </div>
                                    <div class="qty-badge" style="background: #eef2ff; color: var(--primary); padding: 4px 10px;">${item.qty}</div>
                                </div>
                            `;
                        });
                    }
                    $('#lots_list').html(html);
                    
                    // If only 1 lot, auto-click it
                    if(res.data.length === 1) {
                        $('.lot-item').first().trigger('click');
                    }
                }
            },
            error: function() {
                $('#lots_list').html('<div class="text-danger text-center mt-3">Error loading lots.</div>');
            }
        });
    }

    function selectLot(lotNo, element) {
        $('.lot-item').removeClass('active');
        $(element).addClass('active');
        activeLot = lotNo;
        
        $('#details_subtitle').html(`Viewing WIP details for Lot: <span class="badge badge-secondary" style="font-size: 0.9rem;">${lotNo}</span>`);
        loadLotDetails(lotNo, activeDesign);
    }

    function loadLotDetails(lotNo, designNo) {
        $('#details_list').html('<div class="loader"></div>');
        
        $.ajax({
            url: "{{ route('admin.reports.design-wip.api.lot-details') }}",
            type: "GET",
            data: { lot_no: lotNo, design_no: designNo },
            success: function(res) {
                if(res.status) {
                    $('#details_list').html(res.html);
                } else {
                    $('#details_list').html(`
                        <div class="details-placeholder">
                            <i class="fas fa-exclamation-circle" style="color: var(--warning); opacity: 1;"></i>
                            <p style="font-weight: 600; color: var(--text-main); font-size: 1.1rem;">${res.message || 'No data found.'}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#details_list').html('<div class="text-danger text-center mt-3">Error loading details.</div>');
            }
        });
    }

</script>
@endsection
