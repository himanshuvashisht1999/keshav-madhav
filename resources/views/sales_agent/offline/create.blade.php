@extends('sales_agent.layouts.app', ['title' => 'Offline Order'])

@push('styles')
<style>
    .quantity-control-app {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ced4da;
    }
    .quantity-control-app .btn-q {
        background: #f8f9fa;
        border: none;
        padding: 2px 8px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
    }
    .quantity-control-app .btn-q:hover {
        background: #e9ecef;
    }
    .rounded-xl {
        border-radius: 14px !important;
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    /* Mobile Drawer Style Modal */
    #offlineScanSelectionModal .modal-dialog {
        max-width: 980px;
        width: 96%;
    }
    @media (max-width: 768px) {
        .modal.bottom-drawer .modal-dialog {
            margin: 0;
            margin-top: auto;
            align-items: flex-end;
            display: flex;
            height: 100%;
            max-width: 100% !important;
        }
        .modal.bottom-drawer .modal-content {
            border-radius: 20px 20px 0 0;
            width: 100%;
            max-height: 92vh;
            display: flex;
            flex-direction: column;
        }
        .modal.bottom-drawer .modal-body {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            flex: 1;
        }
    }
    /* Matrix Table Styles */
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .matrix-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
        border-top: none;
    }
    .matrix-table th:first-child,
    .matrix-table td:first-child {
        position: sticky;
        left: 0;
        z-index: 5;
        background-color: #fffde0;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
    }
    .matrix-table thead th:first-child {
        z-index: 15;
        background: linear-gradient(135deg, #fcee21 0%, #e2d514 100%) !important;
        background-color: #fcee21 !important;
        color: #05421c !important;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
    }
    .matrix-row-deselected {
        opacity: 0.38 !important;
        background-color: #f1f3f5 !important;
    }
    .matrix-row-deselected td,
    .matrix-row-deselected td:first-child {
        background-color: #f1f3f5 !important;
    }
    .matrix-col-deselected {
        opacity: 0.5 !important;
        background-color: #495057 !important;
    }
    .matrix-cell.cell-disabled {
        opacity: 0.35 !important;
        background-color: #f1f3f5 !important;
    }
    .matrix-col-header .custom-control-label::before {
        background-color: rgba(255, 255, 255, 0.3);
        border: 2px solid #ffffff;
        border-radius: 4px;
    }
    .matrix-col-header .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #ffffff !important;
        border-color: #ffffff !important;
    }
    .matrix-col-header .custom-control-input:checked ~ .custom-control-label::after {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3E%3Cpath fill='%2305421c' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3E%3C/svg%3E") !important;
    }
    .btn-xs {
        padding: 1px 5px;
        font-size: 11px;
        line-height: 1.5;
        border-radius: 3px;
    }
</style>
@endpush


@section('content')
<div class="container-fluid px-2 py-2" style="max-width: 800px; padding-bottom: 140px;">

    <!-- TOP BAR -->
    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
        <a href="{{ route('agent.offline.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 text-nowrap">
            <i class="fas fa-arrow-left mr-1"></i> Hub
        </a>
        <div class="text-center">
            <span class="badge badge-pill badge-secondary font-weight-bold px-3 py-1" id="offlineModeIndicator">
                <i class="fas fa-plane mr-1"></i> Offline Mode
            </span>
        </div>
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-light border rounded-circle mr-1 shadow-sm" id="btnToggleFilters" style="width: 32px; height: 32px;" title="Filters">
                <i class="fas fa-filter text-primary"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 mr-1 shadow-sm text-nowrap" id="btnSyncCatalogInline" title="Send orders and download the latest data">
                <i class="fas fa-sync-alt mr-1"></i><span class="small font-weight-bold">Sync</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm text-nowrap" id="btnOpenScanner">
                <i class="fas fa-qrcode mr-1"></i> Scan Tag
            </button>
        </div>
    </div>

    <!-- EDITING A SAVED ORDER -->
    <div id="editBanner" class="alert alert-warning small mb-2 px-3 py-2" style="display: none; border-radius: 12px;"></div>

    <!-- SHOP SELECTION CARD -->
    <div class="card border-0 shadow-sm p-3 mb-2 bg-white" style="border-radius: 14px;">
        <label class="font-weight-bold text-dark small mb-1" id="shopSelectLabel">Select Shop <span class="text-danger">*</span></label>
        <select id="offlineShopSelect" class="form-control select2" style="width: 100%;">
            <option value="">-- Choose Shop --</option>
        </select>
        <div id="shopDetailsBox" class="mt-2 p-2 bg-light rounded small" style="display: none;">
            <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="fas fa-phone mr-1"></i> <span id="shopPhoneText">-</span></span>
                <span class="badge badge-light border" id="shopTypeText"></span>
            </div>
            <div class="text-muted mt-1 text-truncate"><i class="fas fa-map-marker-alt mr-1"></i> <span id="shopAddressText">-</span></div>
        </div>
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="offlineSampleSet">
            <label class="custom-control-label small font-weight-bold" for="offlineSampleSet">Sample set order <span class="text-muted font-weight-normal">(sample-set prices, as the online sample set order)</span></label>
        </div>
    </div>

    <!-- COLLAPSIBLE FILTERS CARD -->
    <div id="filterContainer" class="card border-0 shadow-sm p-3 mb-2 bg-white" style="display: none; border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="font-weight-bold text-dark mb-0 small"><i class="fas fa-sliders-h mr-1 text-primary"></i> Catalog Filters</h6>
            <button type="button" class="btn btn-xs btn-link text-danger p-0 font-weight-bold" id="btnResetFilters" style="font-size: 11px;">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
        <div class="row">
            <div class="col-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Color</label>
                <select id="filterColorSelect" class="form-control form-control-sm"><option value="">All Colors</option></select>
            </div>
            <div class="col-6 mb-2">
                <label class="small text-muted font-weight-bold mb-1">Size Set</label>
                <select id="filterSizeSelect" class="form-control form-control-sm"><option value="">All Size Sets</option></select>
            </div>
            <div class="col-6 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Brand</label>
                <select id="filterBrandSelect" class="form-control form-control-sm"><option value="">All Brands</option></select>
            </div>
            <div class="col-6 mb-1">
                <label class="small text-muted font-weight-bold mb-1">Fitting</label>
                <select id="filterFittingSelect" class="form-control form-control-sm"><option value="">All Fittings</option></select>
            </div>
        </div>
    </div>

    <!-- SEARCH BAR -->
    <div class="mb-3 px-1">
        <div class="input-group bg-white rounded-pill shadow-sm p-1 border">
            <div class="input-group-prepend pl-2">
                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="offlineSearchInput" class="form-control border-0 bg-transparent px-2" placeholder="Search design, series, color, size...">
            <div class="input-group-append">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 mr-1" id="btnClearSearch" style="display: none;"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>

    <!-- PRODUCTS GRID -->
    <div id="productsGrid" class="row">
        <div class="col-12 text-center py-5 text-muted" id="catalogLoadingMsg">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
            <p class="mb-0">Loading products on this phone...</p>
        </div>
    </div>

    <div class="text-center my-3" id="loadMoreContainer" style="display: none;">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" id="btnLoadMore">Load More Products</button>
    </div>
</div>

<!-- FLOATING BOTTOM SUMMARY BAR -->
<div id="offlineSummaryBar" class="fixed-bottom bg-white border-top shadow-lg px-3 py-2" style="display: none; z-index: 1030; border-top-left-radius: 18px; border-top-right-radius: 18px;">
    <div class="container" style="max-width: 600px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge badge-primary px-2 py-1 font-weight-bold" id="totalBoxesCount">0 Boxes</span>
                <h5 class="mb-0 font-weight-bold text-success mt-1" id="grandTotalContainer" style="display: none;">₹<span id="grandTotalText">0</span> <small class="text-muted" style="font-size: 10px;">incl. GST</small></h5>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm mr-2 rounded-pill px-3" id="btnOpenCartModal">
                    <i class="fas fa-list mr-1"></i> Cart (<span id="cartItemsCount">0</span>)
                </button>
                <button type="button" class="btn btn-success btn-md rounded-pill px-4 font-weight-bold shadow-sm" id="btnSaveOrderOffline">
                    <i class="fas fa-check-circle mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PRODUCT DETAILS / SPECS MODAL -->
<div class="modal fade" id="productDetailsModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 bg-light py-2 px-3" style="border-radius: 20px 20px 0 0;">
                <div class="d-flex align-items-center">
                    <span class="badge badge-dark px-2 py-1 mr-2" id="modalProdDesignBadge" style="font-size: 13px;"></span>
                    <h6 class="modal-title font-weight-bold text-dark mb-0 text-truncate" id="modalProdTitle" style="max-width: 300px;"></h6>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-3" id="modalProdBody"></div>
            <div class="modal-footer border-top bg-light p-2 d-flex justify-content-between align-items-center" style="border-radius: 0 0 20px 20px;">
                <div class="d-flex align-items-center">
                    <span class="small font-weight-bold text-muted mr-2">Boxes:</span>
                    <div class="quantity-control-app d-inline-flex align-items-center p-0 bg-white border shadow-xs" style="border-radius: 8px; height: 32px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-modal-minus" style="border:0; height: 30px;"><i class="fas fa-minus"></i></button>
                        <input type="number" id="modalQtyInput" class="form-control form-control-sm text-center font-weight-bold p-0 border-0" style="width: 45px; height: 30px; background: transparent;" value="0" min="0">
                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-modal-plus" style="border:0; height: 30px; background: #05421c;"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 font-weight-bold" data-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- IMAGE ZOOM MODAL -->
<div class="modal fade" id="imageZoomModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 100%; margin: 0; height: 100vh;">
        <div class="modal-content border-0" style="min-height: 100vh; border-radius: 0; background: rgba(0, 0, 0, 0.92);">
            <button type="button" class="close text-white rounded-circle p-2" data-dismiss="modal" aria-label="Close" style="position: absolute; top: 15px; right: 20px; z-index: 1100; background: rgba(255,255,255,0.2); opacity: 1;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body p-0" style="overflow: hidden; position: relative; height: 100vh; display: flex; align-items: center; justify-content: center;">
                <img src="" id="zoomedImage" alt="" style="max-height: 92vh; max-width: 95vw; width: auto; height: auto; object-fit: contain; box-shadow: 0 0 25px rgba(0,0,0,0.6); border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

<!-- QR / BARCODE SCANNER MODAL -->
<div class="modal fade" id="offlineScannerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white border-0 py-2">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-qrcode mr-2"></i> Scan Tag / Barcode</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-2 text-center bg-black">
                <div id="offlineReader" style="width: 100%; min-height: 250px;"></div>
                <div class="mt-2 text-white small">Point the camera at a sample-set tag, sample tag or box barcode</div>
            </div>
            <div class="modal-footer p-2 bg-light border-0">
                <div class="input-group input-group-sm">
                    <input type="text" id="manualBarcodeInput" class="form-control" placeholder="Or type the code (e.g. F3LP, D116S4C2, design no.)">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" id="btnManualBarcodeSubmit">Enter</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CART DETAILS MODAL -->
<div class="modal fade" id="cartDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-shopping-cart text-primary mr-1"></i> Order Items</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-2" id="cartItemsList"></div>
            <div class="modal-footer p-2 bg-light border-0 flex-column align-items-stretch">
                <div id="offlinePricingInputs" style="display: none;">
                    <div class="row no-gutters mb-1">
                        <div class="col-6 pr-1">
                            <input type="number" id="offlineDiscount" class="form-control form-control-sm" placeholder="Discount ₹" min="0" step="1">
                        </div>
                        <div class="col-6 pl-1">
                            <input type="number" id="offlineOtherCharges" class="form-control form-control-sm" placeholder="Other Charges ₹" min="0" step="1">
                        </div>
                    </div>
                    <div class="small text-muted mb-1" id="totalsBreakdown"></div>
                    <div class="small text-danger font-weight-bold mb-1" id="totalsError" style="display: none;"></div>
                </div>
                <select id="offlineSalesMan" class="form-control form-control-sm mb-2">
                    <option value="">Select Sales Man (Optional)</option>
                </select>
                <label class="small text-muted mb-0" for="offlineDispatchDate">Expected dispatch date</label>
                <input type="date" id="offlineDispatchDate" class="form-control form-control-sm mb-2">
                <input type="text" id="offlineBookingStation" class="form-control form-control-sm mb-2" placeholder="Booking Station" maxlength="255">
                <input type="text" id="offlineTransport" class="form-control form-control-sm mb-2" placeholder="Transport Name" maxlength="255">
                <input type="text" id="offlineRemark" class="form-control form-control-sm mb-2" placeholder="Order Remark (Optional)" maxlength="1000">
                <button type="button" class="btn btn-success btn-block rounded-pill font-weight-bold" id="btnModalSaveOrder">
                    <i class="fas fa-save mr-1"></i> Confirm & Save on Phone
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SCAN RESULT: SIZE SET x COLOUR MATRIX -->
<div class="modal fade bottom-drawer" id="offlineScanSelectionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document" style="max-width: 980px; width: 96%;">
        <div class="modal-content border-0 position-relative shadow" style="border-radius: 20px 20px 0 0;">
            <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 10px; right: 16px; z-index: 1050; font-size: 26px; opacity: 0.7;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body bg-white pt-2 px-3" style="overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; max-height: calc(100vh - 120px);">
                <div id="offlineColorSelectionList" class="pb-2"></div>
            </div>
            <div class="modal-footer border-0 bg-white shadow-lg pt-1 pb-3">
                <button type="button" class="btn btn-primary btn-block btn-lg rounded-xl font-weight-bold" data-dismiss="modal">Apply Selections</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script src="{{ asset('agent_assets/agent-scanner.js') }}?v={{ @filemtime(public_path('agent_assets/agent-scanner.js')) }}"></script>
<script>
// A photo that is not reachable (no internet): show the copy saved on the phone, else a placeholder
window.handleOfflineImageError = function (imgEl, filename) {
    if (!imgEl) return;
    const placeholder = () => {
        imgEl.style.display = 'none';
        if (imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('placeholder-box')) imgEl.nextElementSibling.style.display = 'flex';
        if (imgEl.id === 'scanHeroImage') { const ph = document.getElementById('scanHeroPlaceholder'); if (ph) ph.style.display = 'flex'; }
    };
    imgEl.onerror = null;
    const key = filename || (imgEl.getAttribute('data-src') || imgEl.src || '');
    if (!key || !window.SnapKidOfflineDB) return placeholder();
    SnapKidOfflineDB.getImageBlobUrl(key).then(url => {
        if (!url) return placeholder();
        imgEl.src = url;
        imgEl.dataset.src = url;
        imgEl.style.display = '';
    }).catch(placeholder);
};

$(document).ready(function () {
    if (SK_AGENT.otherAgentCopy) return; // a saved copy of another agent login's page (the layout says so)
    const DB = SnapKidOfflineDB;
    const tz = SK_AGENT.timezone;
    const editId = new URLSearchParams(location.search).get('edit');
    const presetShopId = new URLSearchParams(location.search).get('shop_id');
    const lastShopKey = 'sk_offline_shop_' + SK_AGENT.id;
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const idKey = it => it.product_id + '_' + it.color_id + '_' + it.size_set_id;
    const itemLabel = it => [it.design_number, it.color_name, it.size_set_name].filter(Boolean).join(' ');

    let allProducts = [];
    const productsByKey = new Map();
    let displayedProducts = [];
    const cart = new Map();       // "product_colour_size" -> {item, qty}: one line per item, as the office saves it
    const scanItems = new Map();  // item key -> item from sample-set / sample tags (fixed or own price)
    let currentPage = 1;
    const pageSize = 24;
    let metaInfo = null;
    let activeModalKey = null;
    let shops = [];
    let shop = null;
    let sampleSet = false;
    let fairDiscounts = {};
    let reserved = {};            // boxes this agent's other orders on the phone already take
    let editing = null;

    // "Show pricing info" from admin: the page's value, or the last catalogue download when that is newer
    const agentSeePrice = {{ $agent->see_price ? 'true' : 'false' }};
    const pageRenderedAt = {{ (int) round(microtime(true) * 1000) }};
    let seePriceAllowed = agentSeePrice;
    const canSeePrice = () => seePriceAllowed;

    if ($.fn.select2) $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    $('#offlineDispatchDate').val(DB.addDays(DB.dayIn(new Date(), tz), 3));

    const settings = () => (metaInfo && metaInfo.settings) || {};
    function gstRate() {
        const g = parseFloat(settings().gst_order);
        return isNaN(g) ? 5 : g;
    }
    // same switches as the online screens: all orders, or sample-set orders only
    function allowOverStock() {
        const s = settings();
        return parseInt(s.agent_app_allow_over_stock) === 1 || (sampleSet && parseInt(s.agent_app_allow_over_stock_sample) === 1);
    }
    function stockVisible() {
        return parseInt(settings().agent_app_show_stock) !== 0;
    }
    function toast(msg, icon) {
        Swal.fire({ toast: true, position: 'top', timer: 2600, showConfirmButton: false, icon: icon || 'info', title: msg });
    }

    // Price of one piece: sample-set tags keep their own price; else MRP less the shop's brand discount
    // (vendors: no discount); in a sample set order the sample-set discount replaces it where there is one.
    function priceFor(item) {
        if (item.fixed_price) return Number(item.unit_price) || 0;
        let pct = 0;
        if (shop && shop.party_type !== 'vendor') pct = parseFloat((shop.brand_discounts || {})[item.brand_id]) || 0;
        if (sampleSet) {
            const f = fairDiscounts[item.product_id + '_' + item.size_set_id];
            if (f !== undefined && f !== null) pct = parseFloat(f) || 0;
        }
        return DB.priceAfter(item.mrp, pct);
    }

    // Boxes still free for an item: downloaded free stock less this agent's other orders on the phone
    function freeFor(item) {
        if (item.is_advance_sample) return Infinity;
        return Math.max(0, (parseInt(item.available_boxes) || 0) - (reserved[idKey(item)] || 0));
    }
    const capFor = item => (allowOverStock() || item.is_advance_sample) ? Infinity : freeFor(item);
    const cartQty = item => (cart.get(idKey(item)) || {}).qty || 0;
    const findItem = key => productsByKey.get(key) || scanItems.get(key) || ((cart.get(key) || {}).item);

    function repriceAll() {
        allProducts.forEach(p => { p.unit_price = priceFor(p); });
        scanItems.forEach(it => { if (!it.fixed_price) it.unit_price = priceFor(it); });
        cart.forEach(v => { if (!v.item.fixed_price) v.item.unit_price = priceFor(v.item); });
        filterAndRenderProducts(false);
        updateSummaryUI();
    }

    // ---------- loading ----------

    async function initOfflineCatalog() {
        try {
            seePriceAllowed = await DB.resolveSeePrice(agentSeePrice, pageRenderedAt);
            metaInfo = await DB.getMeta();
            if (!metaInfo) {
                $('#productsGrid').html('<div class="col-12 text-center py-5"><i class="fas fa-exclamation-triangle fa-3x text-warning mb-2"></i>' +
                    '<h6 class="font-weight-bold">No products on this phone for your login</h6><p class="text-muted small">Go to the Offline Hub and tap <strong>Sync</strong> when you have internet.</p>' +
                    '<a href="' + SK_AGENT.urls.hub + '" class="btn btn-primary btn-sm rounded-pill px-4">Go to Offline Hub</a></div>');
                return;
            }
            ((metaInfo.sales_men) || []).forEach(sm => $('#offlineSalesMan').append($('<option>').val(sm.id).text(sm.name)));
            shops = (await DB.getShops()).sort((a, b) => (a.party_type === b.party_type ? 0 : a.party_type === 'vendor' ? 1 : -1) || String(a.name).localeCompare(String(b.name)));
            if (shops.some(s => s.party_type === 'vendor')) $('#shopSelectLabel').html('Select Shop / Vendor <span class="text-danger">*</span>');
            const sel = $('#offlineShopSelect').empty().append($('<option value="">').text('-- Choose Shop --'));
            shops.forEach(s => sel.append($('<option>').val(s.uid).text(s.name + (s.party_type === 'vendor' ? ' (Vendor)' : '') + (s.phone ? ' · ' + s.phone : ''))));

            fairDiscounts = await DB.getFairDiscounts();
            allProducts = await DB.getCatalog();
            allProducts.forEach(p => productsByKey.set(p.key, p));
            reserved = await DB.reservedBoxes(editId || null);
            $('#catalogLoadingMsg').hide();

            if (editId) {
                if (!(await loadEditOrder())) return;
            } else {
                let uid = presetShopId ? 'customer_' + presetShopId : null;
                try { uid = uid || localStorage.getItem(lastShopKey); } catch (_) {}
                if (uid && shops.some(s => s.uid === uid)) selectShop(uid);
            }
            populateFilterDropdowns();
            repriceAll();
        } catch (e) {
            console.error('Failed to load offline data:', e);
            $('#catalogLoadingMsg').html('<p class="text-danger">Could not read the data on this phone: ' + esc(e.message) + '</p>');
        }
    }

    async function loadEditOrder() {
        const back = msg => Swal.fire('Cannot edit', msg, 'warning').then(() => { location.href = SK_AGENT.urls.hub; });
        const o = await DB.getOrder(editId);
        if (!o) { back('This order is not on this phone (it may have been uploaded already).'); return false; }
        if (!DB.isEditable(o)) { back('This order was sent and the office has not answered yet. Tap Sync first; it is never saved twice.'); return false; }
        editing = o;

        // current versions of the lines (prices / stock from the latest download); lines no longer in it keep the saved copy
        const payloadItems = new Map();
        Object.values(await DB.getScanPayloads()).forEach(p => (p.size_sets || []).forEach(ss => (ss.colors || []).forEach(c => { if (c.item) payloadItems.set(c.key, c.item); })));
        const gone = [];
        (o.variations || []).forEach(v => {
            let item = (v.key && (productsByKey.get(v.key) || payloadItems.get(v.key))) || productsByKey.get(idKey(v));
            if (!item) {
                item = Object.assign({}, v, { key: v.key || idKey(v), available_boxes: 0 });
                delete item.qty;
                gone.push(itemLabel(v));
            }
            if (!productsByKey.has(item.key)) scanItems.set(item.key, item);
            cart.set(idKey(item), { item, qty: parseInt(v.qty) || 0 });
        });

        sampleSet = !!o.is_sample_set;
        $('#offlineSampleSet').prop('checked', sampleSet);
        const uid = o.shop_uid || ((o.party_type || 'customer') + '_' + o.shop_id);
        if (shops.some(s => s.uid === uid)) selectShop(uid);
        $('#offlineDiscount').val(o.discount_amount || '');
        $('#offlineOtherCharges').val(o.other_charges || '');
        $('#offlineSalesMan').val(o.sales_man_id || '');
        $('#offlineDispatchDate').val(o.expected_dispatch_date || '');
        $('#offlineBookingStation').val(o.booking_station || '');
        $('#offlineTransport').val(o.transport || '');
        $('#offlineRemark').val(o.remark || '');

        let html = '<i class="fas fa-edit mr-1"></i><strong>Editing the order for ' + esc(o.shop_name) + '</strong> (booked ' + esc(new Date(o.booked_at || o.created_at).toLocaleString('en-IN')) + ').';
        if (o.last_error) html += '<br><span class="text-danger">Office said: ' + esc(o.last_error) + '</span>';
        if (!shop) html += '<br><span class="text-danger">This shop is no longer in your list (inactive or given to another agent). This order cannot be sent: discard it in the Hub.</span>';
        if (gone.length) html += '<br>No longer in stock on the latest download: ' + esc(gone.join(', ')) + '. Remove or reduce them.';
        html += '<br>Save to keep the changes; it uploads at the next Sync.';
        $('#editBanner').html(html).show();
        $('#btnSaveOrderOffline').html('<i class="fas fa-check-circle mr-1"></i> Save changes');
        $('#btnModalSaveOrder').html('<i class="fas fa-save mr-1"></i> Save changes on Phone');
        return true;
    }

    function selectShop(uid) {
        $('#offlineShopSelect').val(uid).trigger('change.select2');
        applyShop(uid);
    }

    function applyShop(uid) {
        shop = shops.find(s => s.uid === uid) || null;
        if (shop) {
            if (!editing) { try { localStorage.setItem(lastShopKey, shop.uid); } catch (_) {} }
            $('#shopPhoneText').text(shop.phone || 'N/A');
            $('#shopAddressText').text(shop.address || 'N/A');
            $('#shopTypeText').text(shop.party_type === 'vendor' ? 'Vendor (MRP, no discount)' : 'Shop');
            $('#shopDetailsBox').slideDown();
        } else {
            $('#shopDetailsBox').slideUp();
        }
    }

    function populateFilterDropdowns() {
        const fill = (id, label, values) => {
            const sel = $(id).empty().append($('<option value="">').text(label));
            [...new Set(values.filter(Boolean))].sort().forEach(v => sel.append($('<option>').val(v).text(v)));
        };
        fill('#filterColorSelect', 'All Colors', allProducts.map(p => p.color_name));
        fill('#filterSizeSelect', 'All Size Sets', allProducts.map(p => p.size_set_name));
        fill('#filterBrandSelect', 'All Brands', allProducts.map(p => p.brand_name));
        fill('#filterFittingSelect', 'All Fittings', allProducts.map(p => p.fitting_name));
    }

    initOfflineCatalog();

    $('#btnToggleFilters').click(() => $('#filterContainer').slideToggle(200));
    $('#filterColorSelect, #filterSizeSelect, #filterBrandSelect, #filterFittingSelect').on('change', () => filterAndRenderProducts(true));
    $('#btnResetFilters').click(function () {
        $('#filterColorSelect, #filterSizeSelect, #filterBrandSelect, #filterFittingSelect, #offlineSearchInput').val('');
        $('#btnClearSearch').hide();
        filterAndRenderProducts(true);
    });

    $('#offlineShopSelect').on('change', function () {
        applyShop($(this).val());
        repriceAll();
    });

    $('#offlineSampleSet').on('change', function () {
        sampleSet = $(this).is(':checked');
        repriceAll();
        toast(sampleSet ? 'Sample set order: sample-set prices where the item is in your sample sets.' : 'Normal order prices.');
    });

    // ---------- product cards ----------

    function productTitle(p) {
        const series = p.series_name || '';
        const garment = p.name_of_garment ? String(p.name_of_garment).trim() : '';
        if (series && garment && series !== garment) return series + ' ' + garment;
        return series || (garment ? 'Garment ' + garment : 'Design #' + p.design_number);
    }

    function filterAndRenderProducts(resetPage = true) {
        if (resetPage) currentPage = 1;
        if (!metaInfo) return;
        const query = $('#offlineSearchInput').val().trim().toLowerCase();
        const fc = $('#filterColorSelect').val(), fs = $('#filterSizeSelect').val(), fb = $('#filterBrandSelect').val(), ff = $('#filterFittingSelect').val();

        displayedProducts = allProducts.filter(p => {
            if (fc && p.color_name !== fc) return false;
            if (fs && p.size_set_name !== fs) return false;
            if (fb && p.brand_name !== fb) return false;
            if (ff && p.fitting_name !== ff) return false;
            if (!query) return true;
            return [p.design_number, p.series_name, p.brand_name, p.color_name, p.size_set_name, p.name_of_garment, p.fitting_name, p.pattern_name, p.barcode]
                .some(v => v && String(v).toLowerCase().includes(query));
        });

        const toRender = displayedProducts.slice(0, currentPage * pageSize);
        if (!toRender.length) {
            $('#productsGrid').html('<div class="col-12 text-center py-4 text-muted"><i class="fas fa-search fa-2x mb-2 opacity-50"></i><p class="mb-0 font-weight-bold">No matching products found.</p><small>Clear the search or reset the filters.</small></div>');
            $('#loadMoreContainer').hide();
            return;
        }
        const showStock = stockVisible();
        const seePrice = canSeePrice();
        let html = '';
        toRender.forEach(prod => {
            const qty = cartQty(prod);
            const img = prod.image_url || '';
            const imgFile = prod.image || (img ? img.split('/').pop().split('?')[0] : '');
            const title = productTitle(prod);
            const free = freeFor(prod);
            const cap = capFor(prod);
            html += `
                <div class="col-6 col-md-4 col-lg-3 mb-2 px-1">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden variation-card ${qty > 0 ? 'border-success' : ''}" data-key="${esc(prod.key)}" style="border-radius: 12px; font-size: 13px;">
                        <div style="height: 140px; background: #f8fafc;" class="d-flex align-items-center justify-content-center position-relative">
                            ${img ? `
                                <img src="${esc(img)}" alt="" class="w-100 h-100 zoom-image-trigger" data-src="${esc(img)}" style="object-fit: contain; cursor: pointer;" onerror="handleOfflineImageError(this, '${esc(imgFile)}')">
                                <div class="w-100 h-100 align-items-center justify-content-center text-muted placeholder-box" style="display:none;"><i class="fas fa-tshirt fa-2x opacity-25"></i></div>
                            ` : `<i class="fas fa-tshirt fa-2x text-muted opacity-25"></i>`}
                            <span class="badge badge-dark position-absolute" style="top: 6px; left: 6px; font-size: 10.5px; font-weight: 700; border-radius: 6px; opacity: 0.9;">#${esc(prod.design_number)}</span>
                        </div>
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <h6 class="font-weight-bold text-dark mb-1 text-truncate" style="font-size: 12.5px;" title="${esc(title)}">${esc(title)}</h6>
                                <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 11px;">
                                    <span class="text-truncate mr-1" title="${esc(prod.color_name)}"><i class="fas fa-palette mr-1 text-primary"></i>${esc(prod.color_name)}</span>
                                    <span class="font-weight-bold text-dark text-nowrap"><i class="fas fa-ruler-combined mr-1 text-muted"></i>${esc(prod.size_set_name)}</span>
                                </div>
                                <div class="d-flex align-items-center text-truncate mb-2" style="font-size: 10px;">
                                    ${prod.fitting_name ? `<span class="badge badge-light border text-muted mr-1 px-1 py-0 text-truncate" style="max-width: 65px;">${esc(prod.fitting_name)}</span>` : ''}
                                    ${prod.pattern_name ? `<span class="badge badge-light border text-muted mr-1 px-1 py-0 text-truncate" style="max-width: 65px;">${esc(prod.pattern_name)}</span>` : ''}
                                    <span class="text-muted ml-auto">${esc(prod.pcs_per_box)} pcs</span>
                                </div>
                                ${(seePrice || showStock) ? `
                                <div class="sk-pricebar sk-pricebar-sm">
                                    ${seePrice ? `<div class="sk-price sk-price-sm">${skPriceHtml(prod.unit_price, prod.mrp)}</div>` : ''}
                                    ${showStock ? `<span class="sk-stock"><i class="fas fa-box"></i> ${prod.is_advance_sample ? 'No limit' : free + (free === 1 ? ' Box' : ' Boxes')}</span>` : ''}
                                </div>` : ''}
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted btn-open-details" data-key="${esc(prod.key)}" style="font-size: 11px; text-decoration: none;">
                                    <i class="fas fa-info-circle mr-1 text-primary"></i>Specs
                                </button>
                                <div class="quantity-control-app d-inline-flex align-items-center p-0 bg-white border shadow-xs" style="border-radius: 8px; height: 28px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-minus" data-key="${esc(prod.key)}" style="border:0; height: 26px;"><i class="fas fa-minus" style="font-size: 9px;"></i></button>
                                    <input type="number" class="form-control form-control-sm text-center font-weight-bold p-0 border-0 qty-input" style="height: 26px; width: 36px; background: transparent;"
                                        data-key="${esc(prod.key)}" value="${qty}" min="0" ${cap === Infinity ? '' : `max="${cap}"`}>
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-plus" data-key="${esc(prod.key)}" style="border:0; height: 26px; background: #05421c;"><i class="fas fa-plus" style="font-size: 9px;"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
        });
        $('#productsGrid').html(html);
        $('#loadMoreContainer').toggle(displayedProducts.length > toRender.length);
    }

    $('#offlineSearchInput').on('input', function () {
        $('#btnClearSearch').toggle(!!$(this).val());
        filterAndRenderProducts(true);
    });
    $('#btnClearSearch').click(function () {
        $('#offlineSearchInput').val('');
        $(this).hide();
        filterAndRenderProducts(true);
    });
    $('#btnLoadMore').click(function () {
        currentPage++;
        filterAndRenderProducts(false);
    });

    // ---------- cart ----------

    /** Set the boxes of an item (by any of its keys); returns the boxes actually set. */
    function updateCartItem(itemKey, qty, quiet) {
        qty = Math.max(0, parseInt(qty) || 0);
        const item = findItem(itemKey);
        if (!item) return 0;
        if (!shop && qty > 0 && !quiet) toast('Choose the shop first, so the prices are right.', 'warning');
        const k = idKey(item);
        const cap = capFor(item);
        if (qty > cap) {
            qty = cap;
            if (!quiet) {
                toast(stockVisible()
                    ? 'Only ' + cap + ' box(es) free for ' + itemLabel(item) + (reserved[k] ? ' (your other orders on this phone take ' + reserved[k] + ')' : '') + '.'
                    : 'No more stock free for ' + itemLabel(item) + '.', 'warning');
            }
        }
        const cur = cart.get(k);
        if (qty > 0) {
            // a sample-set tag price stays when the same item is changed from the catalogue
            const keep = (cur && cur.item.fixed_price && !item.fixed_price) ? cur.item : item;
            cart.set(k, { item: keep, qty });
        } else {
            cart.delete(k);
        }
        showQty(k, qty);
        updateSummaryUI();
        return qty;
    }

    function showQty(k, qty) {
        $('.qty-input, .box-qty-scan-input').each(function () {
            const it = findItem(String($(this).data('key')));
            if (it && idKey(it) === k) $(this).val(qty);
        });
        $('.variation-card').each(function () {
            const it = findItem(String($(this).data('key')));
            if (it && idKey(it) === k) $(this).toggleClass('border-success', qty > 0);
        });
        if (activeModalKey) {
            const it = findItem(activeModalKey);
            if (it && idKey(it) === k) $('#modalQtyInput').val(qty);
        }
    }

    const keyOf = el => String($(el).data('key'));
    $(document).on('click', '.btn-plus', function () { const it = findItem(keyOf(this)); if (it) updateCartItem(keyOf(this), cartQty(it) + 1); });
    $(document).on('click', '.btn-minus', function () { const it = findItem(keyOf(this)); if (it && cartQty(it) > 0) updateCartItem(keyOf(this), cartQty(it) - 1); });
    $(document).on('change', '.qty-input', function () { $(this).val(updateCartItem(keyOf(this), $(this).val())); });

    $('.btn-modal-plus').click(function () { const it = activeModalKey && findItem(activeModalKey); if (it) updateCartItem(activeModalKey, cartQty(it) + 1); });
    $('.btn-modal-minus').click(function () { const it = activeModalKey && findItem(activeModalKey); if (it && cartQty(it) > 0) updateCartItem(activeModalKey, cartQty(it) - 1); });
    $('#modalQtyInput').on('change', function () { if (activeModalKey) $(this).val(updateCartItem(activeModalKey, $(this).val())); });
    $('#productDetailsModal').on('hidden.bs.modal', () => { activeModalKey = null; });

    $(document).on('click', '.btn-open-details', function () {
        const prod = findItem(keyOf(this));
        if (!prod) return;
        activeModalKey = prod.key;
        $('#modalQtyInput').val(cartQty(prod));
        $('#modalProdDesignBadge').text('#' + prod.design_number);
        const title = ((prod.series_name ? prod.series_name + ' ' : '') + (prod.name_of_garment || '')).trim();
        $('#modalProdTitle').text(title || prod.design_number);
        const img = prod.image_url || '';
        const imgFile = prod.image || (img ? img.split('/').pop().split('?')[0] : '');
        const row = (label, value) => `<tr><td class="bg-light font-weight-bold text-muted" style="width: 35%;">${label}</td><td class="font-weight-bold text-dark">${value}</td></tr>`;
        $('#modalProdBody').html(`
            <div class="row align-items-center mb-3">
                <div class="col-4 text-center">
                    <div style="height: 120px; background: #f8fafc; border-radius: 12px; overflow: hidden;" class="d-flex align-items-center justify-content-center border">
                        ${img ? `<img src="${esc(img)}" alt="" class="w-100 h-100 zoom-image-trigger" data-src="${esc(img)}" style="object-fit: contain; cursor: pointer;" onerror="handleOfflineImageError(this, '${esc(imgFile)}')">` : `<i class="fas fa-tshirt fa-3x text-muted opacity-25"></i>`}
                    </div>
                </div>
                <div class="col-8">
                    <h6 class="font-weight-bold text-dark mb-1">${esc(title || ('Design #' + prod.design_number))}</h6>
                    <div class="small text-muted mb-1">Brand: <strong class="text-dark">${esc(prod.brand_name || '-')}</strong></div>
                    <div class="small text-muted mb-1">Series: <strong class="text-dark">${esc(prod.series_name || '-')}</strong></div>
                    <div class="small text-muted">Barcode: <code class="text-dark font-weight-bold">${esc(prod.barcode || '-')}</code></div>
                </div>
            </div>
            <div class="table-responsive"><table class="table table-sm table-bordered mb-0" style="font-size: 12.5px;"><tbody>
                ${row('Color', esc(prod.color_name))}
                ${row('Size Set', esc(prod.size_set_name) + (prod.size_group ? ' (' + esc(prod.size_group) + ')' : ''))}
                ${row('Pieces / Box', esc(prod.pcs_per_box))}
                ${row('Fitting', esc(prod.fitting_name || '-'))}
                ${row('Pattern', esc(prod.pattern_name || '-'))}
                ${stockVisible() ? row('Free Stock', prod.is_advance_sample ? 'No limit' : freeFor(prod) + ' Boxes' + (reserved[idKey(prod)] ? ' <small class="text-muted">(after ' + reserved[idKey(prod)] + ' in your other orders on this phone)</small>' : '')) : ''}
                ${canSeePrice() ? row('Price / piece', skMoney(prod.unit_price)) + (prod.mrp > 0 ? row('MRP', skMoney(prod.mrp)) : '') : ''}
            </tbody></table></div>`);
        $('#productDetailsModal').modal('show');
    });

    $(document).on('click', '.zoom-image-trigger', async function (e) {
        e.stopPropagation();
        let src = $(this).data('src') || $(this).attr('src');
        if (!src) return;
        if (!/^(blob|data):/.test(src)) {
            try { const blobUrl = await DB.getImageBlobUrl(src); if (blobUrl) src = blobUrl; } catch (_) {}
        }
        $('#zoomedImage').attr('src', src);
        $('#imageZoomModal').modal('show');
    });

    // ---------- totals (same rounding and checks as the office) ----------

    function currentTotals() {
        let value = 0, boxes = 0;
        cart.forEach(v => {
            value += v.qty * (parseFloat(v.item.pcs_per_box) || 1) * (Number(v.item.unit_price) || 0);
            boxes += v.qty;
        });
        const t = DB.orderTotals(value, canSeePrice() ? $('#offlineDiscount').val() : 0, canSeePrice() ? $('#offlineOtherCharges').val() : 0, gstRate());
        t.boxes = boxes;
        return t;
    }

    function updateSummaryUI() {
        const t = currentTotals();
        $('#totalBoxesCount').text(t.boxes + (t.boxes === 1 ? ' Box' : ' Boxes'));
        $('#cartItemsCount').text(cart.size);
        if (canSeePrice()) {
            $('#grandTotalContainer, #offlinePricingInputs').show();
            $('#grandTotalText').text(t.grand.toLocaleString('en-IN'));
            $('#totalsBreakdown').text('Items ₹' + t.total.toLocaleString('en-IN') + (t.discount ? ' − discount ₹' + t.discount.toLocaleString('en-IN') : '') +
                ' + GST ' + t.gst_percentage + '% ₹' + t.gst.toLocaleString('en-IN') + (t.other ? ' + other ₹' + t.other.toLocaleString('en-IN') : '') + ' = ₹' + t.grand.toLocaleString('en-IN'));
            $('#totalsError').toggle(!!t.error).text(t.error || '');
        } else {
            $('#grandTotalContainer, #offlinePricingInputs').hide();
        }
        if (t.boxes > 0) $('#offlineSummaryBar').slideDown(); else $('#offlineSummaryBar').slideUp();
    }

    $('#offlineDiscount, #offlineOtherCharges').on('input', updateSummaryUI);

    function renderCartList() {
        const list = $('#cartItemsList');
        if (!cart.size) { list.html('<p class="text-center text-muted py-3">Cart is empty</p>'); return; }
        let html = '';
        cart.forEach((v, k) => {
            const p = v.item;
            const lineTotal = v.qty * (parseFloat(p.pcs_per_box) || 1) * (Number(p.unit_price) || 0);
            const over = !allowOverStock() && v.qty > freeFor(p);
            html += `
                <div class="d-flex justify-content-between align-items-center p-2 mb-1 rounded ${over ? 'border border-danger' : 'bg-light'}" style="${over ? 'background:#fdecec;' : ''}">
                    <div style="max-width: 70%;">
                        <div class="font-weight-bold text-dark text-truncate">#${esc(p.design_number)} ${esc(productTitle(p))}</div>
                        <small class="text-muted">${esc(p.color_name)} | ${esc(p.size_set_name)} | ${v.qty} box(es) × ${esc(p.pcs_per_box)} pcs${p.fixed_price ? ' | <span class="text-success">sample-set price</span>' : ''}</small>
                        ${over ? `<div class="small text-danger font-weight-bold">Only ${freeFor(p)} box(es) free</div>` : ''}
                    </div>
                    <div class="text-right">
                        ${canSeePrice() ? `<div class="font-weight-bold text-primary">${skMoney(lineTotal)}</div>` : ''}
                        <button type="button" class="btn btn-xs text-danger btn-remove-cart" data-cart-key="${esc(k)}" title="Remove"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>`;
        });
        list.html(html);
    }

    $('#btnOpenCartModal').click(function () {
        renderCartList();
        updateSummaryUI();
        $('#cartDetailsModal').modal('show');
    });

    $(document).on('click', '.btn-remove-cart', function () {
        const k = String($(this).data('cart-key'));
        cart.delete(k);
        showQty(k, 0);
        updateSummaryUI();
        renderCartList();
    });

    // ---------- scanning ----------

    $('#btnOpenScanner').click(() => $('#offlineScannerModal').modal('show'));
    $('#offlineScannerModal').on('shown.bs.modal', startOfflineScanner);
    $('#offlineScannerModal').on('hidden.bs.modal', stopOfflineScanner);

    // camera settings: public/agent_assets/agent-scanner.js (saved on the phone by the service worker)
    const offlineCam = window.AgentScanner ? AgentScanner.create('offlineReader', {
        onScan: onBarcodeScanned,
        onError: err => {
            console.error('Camera error:', err);
            toast('Camera not available. Type the code below instead.', 'warning');
        }
    }) : null;

    function startOfflineScanner() {
        if (offlineCam) offlineCam.start();
        else toast('Camera not available. Type the code below instead.', 'warning');
    }

    function stopOfflineScanner() {
        if (offlineCam) offlineCam.stop();
    }

    function onBarcodeScanned(decodedText) {
        $('#offlineScannerModal').modal('hide');
        processBarcodeMatch(decodedText);
    }

    $('#btnManualBarcodeSubmit').click(function () {
        const code = $('#manualBarcodeInput').val().trim();
        if (!code) return;
        $('#manualBarcodeInput').val('');
        $('#offlineScannerModal').modal('hide');
        processBarcodeMatch(code);
    });
    $('#manualBarcodeInput').on('keypress', function (e) {
        if (e.which === 13) $('#btnManualBarcodeSubmit').click();
    });

    async function processBarcodeMatch(rawCode) {
        if (!rawCode) return;
        const res = await DB.findVariationByBarcode(rawCode);
        if (res && res.success && res.size_sets && res.size_sets.length) {
            showOfflineColorSelection(res);
        } else {
            const msg = (res && res.message) || 'No matching product on this phone.';
            Swal.fire({ icon: 'warning', title: /not assigned|inactive/.test(msg) ? 'Cannot use this tag' : 'Code not found', text: msg });
        }
    }

    function showOfflineColorSelection(data) {
        const list = $('#offlineColorSelectionList').empty();
        // the catalogue's own copy of an item when there is one (same price / cart line); tag items otherwise
        data.size_sets.forEach(ss => (ss.colors || []).forEach(c => {
            let item = productsByKey.get(c.key);
            if (!item) {
                item = scanItems.get(c.key) || c.item || Object.assign({}, c, { key: c.key, product_id: data.product.id, color_id: c.id, size_set_id: ss.size_set_id, mrp: ss.mrp, brand_id: ss.brand_id });
                if (!item.fixed_price) item.unit_price = priceFor(item);
                scanItems.set(c.key, item);
            }
            c._item = item;
        }));

        // one column per colour (by colour, so a column is the same colour in every size set)
        const colours = [];
        data.size_sets.forEach(ss => (ss.colors || []).forEach(c => { if (!colours.some(x => x.id == c.id)) colours.push({ id: c.id, name: c.name }); }));
        let heroImage = data.product.image || '';
        if (!heroImage) {
            for (const ss of data.size_sets) {
                heroImage = ss.image || ((ss.colors || []).find(c => c.image) || {}).image || '';
                if (heroImage) break;
            }
        }
        const heroFile = (heroImage || '').split('/').pop().split('?')[0];
        const showStock = stockVisible();

        let html = `
            <div class="text-center mb-1">
                <span class="badge badge-primary px-3 py-1 font-weight-bold shadow-sm d-inline-block" style="font-size: 15px; border-radius: 8px;">DESIGN NO: ${esc(data.product.design_number || 'N/A')}</span>
                ${(data.product.name && data.product.name !== data.product.design_number) ? `<div class="font-weight-bold text-dark mt-1" style="font-size: 14px;">${esc(data.product.name)}</div>` : ''}
                ${data.size_sets.some(ss => ss.fixed_price) ? '<div class="small text-success font-weight-bold"><i class="fas fa-tag mr-1"></i>Sample-set tag: sample-set price</div>' : ''}
            </div>
            <div class="text-center mb-2">
                <div class="d-inline-block position-relative shadow-sm border overflow-hidden bg-white" style="max-width: 100%; border-radius: 14px;">
                    <img id="scanHeroImage" alt="" src="${esc(heroImage)}" class="img-fluid zoom-image-trigger" data-src="${esc(heroImage)}"
                         style="max-height: 380px; min-height: 200px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer; ${heroImage ? '' : 'display:none;'}"
                         onerror="handleOfflineImageError(this, '${esc(heroFile)}')">
                    <div id="scanHeroPlaceholder" style="width: 260px; height: 180px; display: ${heroImage ? 'none' : 'flex'}; align-items: center; justify-content: center; background: #f8f9fa;">
                        <div class="text-muted text-center"><i class="fas fa-image fa-3x opacity-50 mb-1"></i><div class="small font-weight-bold">No Image</div></div>
                    </div>
                </div>
            </div>
            <div class="card border-0 mb-3 shadow-sm" style="background: linear-gradient(90deg, #fffde0 0%, #f0f8e7 100%); border: 1.5px solid #e2d514 !important; border-radius: 12px;">
                <div class="card-body p-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <div><span class="font-weight-bold" style="color: #05421c;"><i class="fas fa-bolt mr-1"></i> Apply Quantity to All</span><div class="small text-muted">Boxes for every size set and colour</div></div>
                    <div class="quantity-control-app d-flex align-items-center p-1 bg-white shadow-xs" style="border-radius: 8px; border: 1.5px solid #e2d514;">
                        <button type="button" class="btn-q btn-minus-master font-weight-bold" style="color: #05421c;">-</button>
                        <input type="number" id="masterQtyInput" class="font-weight-bold" min="0" value="0" style="color: #05421c; width: 45px; text-align: center; border: 0; background: transparent;">
                        <button type="button" class="btn-q btn-plus-master font-weight-bold" style="color: #05421c;">+</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive border rounded-lg mb-3 shadow-sm bg-white" style="max-height: 440px; overflow-y: auto;">
                <table class="table table-bordered table-sm mb-0 matrix-table text-center align-middle" style="font-size: 13px;">
                    <thead><tr>
                        <th class="align-middle text-center font-weight-bold px-2 py-2" style="min-width: 140px; width: 140px;">SIZE SET</th>
                        ${colours.map((col, idx) => `
                            <th class="align-middle text-center font-weight-bold text-white p-2 matrix-col-header matrix-col-${idx}" data-col-index="${idx}" style="min-width: 145px; background: linear-gradient(135deg, #36b54a 0%, #05421c 100%); background-color: #05421c; color: #fff;">
                                <div class="custom-control custom-checkbox d-inline-block text-left" style="max-width: 100%;">
                                    <input type="checkbox" class="custom-control-input toggle-col-checkbox" id="toggle_col_${idx}" data-col-index="${idx}" checked>
                                    <label class="custom-control-label font-weight-bold text-white" for="toggle_col_${idx}" style="font-size: 13px; cursor: pointer; line-height: 1.5;" title="${esc(col.name)}">
                                        <span class="d-inline-block text-truncate" style="max-width: 105px; vertical-align: middle;">${esc(col.name)}</span>
                                    </label>
                                </div>
                                <div class="mt-1">
                                    <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px;">
                                        <button type="button" class="btn-q btn-minus-col px-2 py-0 text-success font-weight-bold" data-col-index="${idx}">-</button>
                                        <input type="number" class="col-batch-qty-input font-weight-bold text-center text-success" data-col-index="${idx}" min="0" value="0" style="width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                        <button type="button" class="btn-q btn-plus-col px-2 py-0 text-success font-weight-bold" data-col-index="${idx}">+</button>
                                    </div>
                                </div>
                            </th>`).join('')}
                    </tr></thead><tbody>`;

        data.size_sets.forEach(ss => {
            const firstItem = ((ss.colors || [])[0] || {})._item;
            const ssPrice = firstItem ? firstItem.unit_price : (ss.fixed_price ? ss.unit_price : priceFor(Object.assign({ product_id: data.product.id }, ss)));
            html += `
                <tr class="matrix-row-${ss.size_set_id}">
                    <td class="align-middle p-2 text-center matrix-ss-header-cell" style="min-width: 140px; width: 140px; background-color: #fffde0;">
                        <div class="custom-control custom-checkbox d-inline-block mb-1">
                            <input type="checkbox" class="custom-control-input toggle-ss-checkbox" id="toggle_ss_${ss.size_set_id}" data-size-set="${ss.size_set_id}" checked>
                            <label class="custom-control-label font-weight-bold" for="toggle_ss_${ss.size_set_id}" style="color: #05421c; font-size: 14px; cursor: pointer;">${esc(ss.size_set_name)}</label>
                        </div>
                        ${canSeePrice() ? `<div class="sk-price sk-price-xs mb-1">${skPriceHtml(ssPrice, ss.mrp)}</div>` : ''}
                        <div>
                            <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px;">
                                <button type="button" class="btn-q btn-minus-ss px-2 py-0 font-weight-bold" data-size-set="${ss.size_set_id}" style="color: #05421c;">-</button>
                                <input type="number" class="ss-batch-qty-input font-weight-bold text-center" data-size-set="${ss.size_set_id}" min="0" value="0" style="color: #05421c; width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                <button type="button" class="btn-q btn-plus-ss px-2 py-0 font-weight-bold" data-size-set="${ss.size_set_id}" style="color: #05421c;">+</button>
                            </div>
                        </div>
                    </td>`;
            colours.forEach((col, c) => {
                const colorObj = (ss.colors || []).find(x => x.id == col.id);
                if (!colorObj) {
                    html += `<td class="matrix-cell align-middle text-muted bg-light p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}">-</td>`;
                    return;
                }
                const item = colorObj._item;
                const cap = capFor(item);
                const free = freeFor(item);
                const cImg = colorObj.image || ss.image || '';
                html += `
                    <td class="matrix-cell matrix-cell-ss-${ss.size_set_id} matrix-cell-col-${c} align-middle p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}" style="background: #fff; min-width: 145px;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="font-weight-bold text-dark text-truncate text-left flex-grow-1" style="font-size: 13px;">${esc(colorObj.name)}</span>
                            ${cImg ? `<button type="button" class="btn btn-outline-info btn-xs rounded-circle p-0 ml-1 d-inline-flex align-items-center justify-content-center zoom-image-trigger" data-src="${esc(cImg)}" title="View Photo" style="width: 22px; height: 22px;"><i class="fas fa-camera" style="font-size: 10px;"></i></button>` : ''}
                        </div>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="quantity-control-app d-inline-flex align-items-center p-0 border shadow-xs" style="border-radius: 8px;">
                                <button type="button" class="btn-q btn-minus-scan px-2 py-1" data-key="${esc(item.key)}">-</button>
                                <input type="number" class="box-qty-scan-input font-weight-bold" data-key="${esc(item.key)}" data-size-set="${ss.size_set_id}" data-col-index="${c}" min="0"
                                    ${cap === Infinity ? '' : `max="${cap}"`} value="${cartQty(item)}" style="width: 38px; border: 0; background: transparent; text-align: center; font-size: 13px;">
                                <button type="button" class="btn-q btn-plus-scan px-2 py-1" data-key="${esc(item.key)}">+</button>
                            </div>
                        </div>
                        ${showStock ? `<div class="text-muted text-center mt-1" style="font-size: 10px; line-height: 1;">${item.is_advance_sample ? 'no limit' : free + ' free'}</div>` : (free === 0 && !item.is_advance_sample && !allowOverStock() ? '<div class="text-danger text-center mt-1" style="font-size: 10px;">no stock</div>' : '')}
                    </td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        list.append(html);

        if (heroFile) {
            DB.getImageBlobUrl(heroImage).then(url => {
                const img = document.getElementById('scanHeroImage');
                if (url && img) { img.src = url; img.dataset.src = url; img.style.display = ''; $('#scanHeroPlaceholder').hide(); }
            }).catch(() => {});
        }
        $('#offlineScanSelectionModal').modal('show');
    }

    // Matrix: every change goes through updateCartItem (stock limit, one line per item)
    const setCell = (input, qty) => $(input).val(updateCartItem(keyOf(input), qty, true));
    const stepInput = (input, d) => { const v = Math.max(0, (parseInt($(input).val()) || 0) + d); $(input).val(v).trigger('change'); };

    $(document).on('click', '.btn-plus-master', () => stepInput('#masterQtyInput', 1));
    $(document).on('click', '.btn-minus-master', () => stepInput('#masterQtyInput', -1));
    $(document).on('change', '#masterQtyInput', function () {
        const q = Math.max(0, parseInt($(this).val()) || 0);
        $(this).val(q);
        $('.matrix-table .ss-batch-qty-input:not(:disabled), .matrix-table .col-batch-qty-input:not(:disabled)').val(q);
        $('.matrix-table .box-qty-scan-input:not(:disabled)').each(function () { setCell(this, q); });
    });

    $(document).on('click', '.btn-plus-ss', function () { const i = $(this).siblings('.ss-batch-qty-input'); if (!i.prop('disabled')) stepInput(i, 1); });
    $(document).on('click', '.btn-minus-ss', function () { const i = $(this).siblings('.ss-batch-qty-input'); if (!i.prop('disabled')) stepInput(i, -1); });
    $(document).on('change', '.ss-batch-qty-input', function () {
        const q = Math.max(0, parseInt($(this).val()) || 0);
        $(this).val(q);
        $(`.matrix-row-${$(this).data('size-set')} .box-qty-scan-input:not(:disabled)`).each(function () { setCell(this, q); });
    });

    $(document).on('click', '.btn-plus-col', function () { const i = $(this).siblings('.col-batch-qty-input'); if (!i.prop('disabled')) stepInput(i, 1); });
    $(document).on('click', '.btn-minus-col', function () { const i = $(this).siblings('.col-batch-qty-input'); if (!i.prop('disabled')) stepInput(i, -1); });
    $(document).on('change', '.col-batch-qty-input', function () {
        const q = Math.max(0, parseInt($(this).val()) || 0);
        $(this).val(q);
        $(`.matrix-cell-col-${$(this).data('col-index')} .box-qty-scan-input:not(:disabled)`).each(function () { setCell(this, q); });
    });

    $(document).on('click', '.btn-plus-scan', function () {
        const input = $(this).siblings('.box-qty-scan-input');
        if (!input.prop('disabled')) input.val(updateCartItem(keyOf(input), (parseInt(input.val()) || 0) + 1));
    });
    $(document).on('click', '.btn-minus-scan', function () {
        const input = $(this).siblings('.box-qty-scan-input');
        const v = parseInt(input.val()) || 0;
        if (!input.prop('disabled') && v > 0) input.val(updateCartItem(keyOf(input), v - 1));
    });
    $(document).on('change', '.box-qty-scan-input', function () { $(this).val(updateCartItem(keyOf(this), $(this).val())); });

    $(document).on('change', '.toggle-ss-checkbox', function () {
        const row = $(this).closest('tr');
        const on = $(this).is(':checked');
        row.toggleClass('matrix-row-deselected', !on);
        row.find('.ss-batch-qty-input').prop('disabled', !on).val(on ? row.find('.ss-batch-qty-input').val() : 0);
        row.find('.btn-minus-ss, .btn-plus-ss').prop('disabled', !on);
        row.find('.matrix-cell').each(function () {
            const colOn = $(`.toggle-col-checkbox[data-col-index="${$(this).data('col-index')}"]`).is(':checked');
            const enable = on && colOn;
            $(this).find('.box-qty-scan-input').each(function () { if (!enable) setCell(this, 0); $(this).prop('disabled', !enable); });
            $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', !enable);
        });
    });

    $(document).on('change', '.toggle-col-checkbox', function () {
        const idx = $(this).data('col-index');
        const on = $(this).is(':checked');
        const th = $(`.matrix-col-${idx}`);
        th.toggleClass('matrix-col-deselected', !on);
        th.find('.col-batch-qty-input').prop('disabled', !on).val(on ? th.find('.col-batch-qty-input').val() : 0);
        th.find('.btn-minus-col, .btn-plus-col').prop('disabled', !on);
        $(`.matrix-cell-col-${idx}`).each(function () {
            const rowOn = $(`#toggle_ss_${$(this).data('size-set')}`).is(':checked');
            const enable = on && rowOn;
            $(this).toggleClass('cell-disabled', !enable);
            $(this).find('.box-qty-scan-input').each(function () { if (!enable) setCell(this, 0); $(this).prop('disabled', !enable); });
            $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', !enable);
        });
    });

    // ---------- sync from this screen ----------

    $('#btnSyncCatalogInline').click(function () {
        if (cart.size) {
            Swal.fire('Save first', 'Save this order (or empty the cart) before you Sync, so nothing is lost.', 'info');
            return;
        }
        location.href = SK_AGENT.urls.hub + '?autosync=1';
    });

    // ---------- save ----------

    // one save at a time: a double tap must not save the same order twice
    let saving = false;
    async function saveCurrentOrder() {
        if (saving) return;
        saving = true;
        const buttons = $('#btnSaveOrderOffline, #btnModalSaveOrder').prop('disabled', true);
        let saved = false;
        try {
            saved = await saveOrderNow();
        } finally {
            if (!saved) {
                saving = false;
                buttons.prop('disabled', false);
            }
        }
    }

    /** True when the order is on the phone (the page then goes to the hub; the buttons stay locked). */
    async function saveOrderNow() {
        if (!shop) {
            Swal.fire('Shop required', 'Please choose the shop before saving the order.', 'warning');
            return false;
        }
        const lines = [...cart.values()].filter(v => v.qty > 0);
        if (!lines.length) {
            Swal.fire('Empty order', 'Please add at least one box.', 'warning');
            return false;
        }
        if (!allowOverStock()) {
            const short = lines.filter(v => v.qty > freeFor(v.item)).map(v => itemLabel(v.item) + ': only ' + freeFor(v.item) + ' box(es) free, ' + v.qty + ' in the order');
            if (short.length) {
                Swal.fire({ icon: 'warning', title: 'Not enough stock', html: esc(short.join('\n')).replace(/\n/g, '<br>') + '<br><small>Reduce these lines (open the Cart).</small>' });
                return false;
            }
        }
        const t = currentTotals();
        if (t.error) {
            Swal.fire('Check the amounts', t.error, 'warning');
            return false;
        }
        const now = new Date();
        const order = {
            local_id: editing ? editing.local_id : undefined,
            created_at: editing ? editing.created_at : undefined,
            booked_at: editing ? (editing.booked_at || editing.created_at) : now.toISOString(),
            order_date: editing ? (editing.order_date || DB.dayIn(new Date(editing.created_at), tz)) : DB.dayIn(now, tz),
            attempts: editing ? (editing.attempts || 0) : 0,
            party_type: shop.party_type || 'customer',
            shop_id: shop.id,
            shop_uid: shop.uid,
            shop_name: shop.name,
            is_sample_set: sampleSet ? 1 : 0,
            variations: lines.map(v => {
                const it = v.item;
                return {
                    key: it.key, product_id: it.product_id, color_id: it.color_id, size_set_id: it.size_set_id, qty: v.qty,
                    pcs_per_box: it.pcs_per_box, unit_price: it.unit_price, mrp: it.mrp, brand_id: it.brand_id, fixed_price: !!it.fixed_price,
                    design_number: it.design_number, name_of_garment: it.name_of_garment || '', series_name: it.series_name || '', brand_name: it.brand_name || '',
                    color_name: it.color_name, size_set_name: it.size_set_name, image_url: it.image_url || null, is_advance_sample: !!it.is_advance_sample
                };
            }),
            total_boxes: t.boxes,
            items_value: t.total,
            discount_amount: t.discount,
            other_charges: t.other,
            gst_percentage: t.gst_percentage,
            gst_amount: t.gst,
            grand_total: t.grand,
            sales_man_id: $('#offlineSalesMan').val() || null,
            expected_dispatch_date: $('#offlineDispatchDate').val() || null,
            booking_station: $('#offlineBookingStation').val().trim(),
            transport: $('#offlineTransport').val().trim(),
            remark: $('#offlineRemark').val().trim()
        };
        try {
            await DB.saveOrder(order);
        } catch (e) {
            Swal.fire('Not saved', 'The order could not be saved on this phone: ' + e.message, 'error');
            return false;
        }
        $('#cartDetailsModal').modal('hide');
        const priced = canSeePrice() ? ', ' + skMoney(t.grand) + ' incl. GST' : '';
        Swal.fire({
            icon: 'success',
            title: editing ? 'Changes saved' : 'Order saved on phone',
            text: 'Order for ' + shop.name + ' (' + t.boxes + ' boxes' + priced + ') is on this phone. It goes to the office at the next Sync.',
            confirmButtonText: 'Go to Offline Hub',
            confirmButtonColor: '#05421c'
        }).then(() => { location.href = SK_AGENT.urls.hub; });
        return true;
    }

    $('#btnSaveOrderOffline, #btnModalSaveOrder').click(saveCurrentOrder);
});
</script>
@endpush
