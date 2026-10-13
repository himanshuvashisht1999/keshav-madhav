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
        background-color: #fefce8;
        box-shadow: 2px 0 6px rgba(0, 0, 0, 0.08);
    }
    .matrix-table thead th:first-child {
        z-index: 15;
        background: linear-gradient(135deg, #ffd600 0%, #f59e0b 100%) !important;
        background-color: #ffd600 !important;
        color: #78350f !important;
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
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3E%3Cpath fill='%2316a34a' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3E%3C/svg%3E") !important;
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
        <a href="{{ route('agent.offline.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
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
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 mr-1 shadow-sm" id="btnSyncCatalogInline" title="Sync / Download Latest Data">
                <i class="fas fa-sync-alt mr-1" id="inlineSyncIcon"></i><span class="small font-weight-bold">Sync</span>
            </button>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" id="btnOpenScanner">
                <i class="fas fa-qrcode mr-1"></i> Scan Tag
            </button>
        </div>
    </div>

    <!-- INLINE SYNC PROGRESS BAR (HIDDEN BY DEFAULT) -->
    <div id="inlineSyncProgressContainer" class="card border-0 shadow-sm p-2 mb-2 bg-light" style="display: none; border-radius: 12px;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small font-weight-bold text-dark" id="inlineSyncStatusText">Downloading catalog data...</span>
            <span class="small font-weight-bold text-success" id="inlineSyncPercentText">0%</span>
        </div>
        <div class="progress" style="height: 6px; border-radius: 4px;">
            <div id="inlineSyncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
        </div>
    </div>

    <!-- SHOP SELECTION CARD -->
    <div class="card border-0 shadow-sm p-3 mb-2 bg-white" style="border-radius: 14px;">
        <label class="font-weight-bold text-dark small mb-1">Select Shop <span class="text-danger">*</span></label>
        <select id="offlineShopSelect" class="form-control select2" style="width: 100%;">
            <option value="">-- Choose Shop --</option>
        </select>
        <div id="shopDetailsBox" class="mt-2 p-2 bg-light rounded small" style="display: none;">
            <div class="d-flex justify-content-between">
                <span class="text-muted"><i class="fas fa-phone mr-1"></i> <span id="shopPhoneText">-</span></span>
                <span class="font-weight-bold text-success"><i class="fas fa-wallet mr-1"></i> ₹<span id="shopBalanceText">0</span></span>
            </div>
            <div class="text-muted mt-1 text-truncate"><i class="fas fa-map-marker-alt mr-1"></i> <span id="shopAddressText">-</span></div>
        </div>
    </div>

    <!-- COLLAPSIBLE FILTERS CARD -->
    <div id="filterContainer" class="card border-0 shadow-sm p-3 mb-2 bg-white animate__animated animate__fadeInDown" style="display: none; border-radius: 14px;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="font-weight-bold text-dark mb-0 small uppercase tracking-wider"><i class="fas fa-sliders-h mr-1 text-primary"></i> Catalog Filters</h6>
            <button type="button" class="btn btn-xs btn-link text-danger p-0 font-weight-bold" id="btnResetFilters" style="font-size: 11px;">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
        </div>
        <div class="row">
            <div class="col-6 mb-2">
                <label class="small text-muted font-weight-bold uppercase mb-1">Color</label>
                <select id="filterColorSelect" class="form-control form-control-sm">
                    <option value="">All Colors</option>
                </select>
            </div>
            <div class="col-6 mb-2">
                <label class="small text-muted font-weight-bold uppercase mb-1">Size Set</label>
                <select id="filterSizeSelect" class="form-control form-control-sm">
                    <option value="">All Size Sets</option>
                </select>
            </div>
            <div class="col-6 mb-1">
                <label class="small text-muted font-weight-bold uppercase mb-1">Brand</label>
                <select id="filterBrandSelect" class="form-control form-control-sm">
                    <option value="">All Brands</option>
                </select>
            </div>
            <div class="col-6 mb-1">
                <label class="small text-muted font-weight-bold uppercase mb-1">Fitting</label>
                <select id="filterFittingSelect" class="form-control form-control-sm">
                    <option value="">All Fittings</option>
                </select>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="mb-3 px-1">
        <div class="input-group bg-white rounded-pill shadow-sm p-1 border">
            <div class="input-group-prepend pl-2">
                <span class="input-group-text bg-transparent border-0 text-muted"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="offlineSearchInput" class="form-control border-0 bg-transparent px-2" placeholder="Search design, series, color, size...">
            <div class="input-group-append">
                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 mr-1" id="btnClearSearch" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- PRODUCTS GRID -->
    <div id="productsGrid" class="row">
        <div class="col-12 text-center py-5 text-muted" id="catalogLoadingMsg">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
            <p class="mb-0">Loading offline products...</p>
        </div>
    </div>

    <!-- PAGINATION / LOAD MORE -->
    <div class="text-center my-3" id="loadMoreContainer" style="display: none;">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" id="btnLoadMore">
            Load More Products
        </button>
    </div>

</div>

<!-- FLOATING BOTTOM SUMMARY BAR -->
<div id="offlineSummaryBar" class="fixed-bottom bg-white border-top shadow-lg px-3 py-2" style="display: none; z-index: 1030; border-top-left-radius: 18px; border-top-right-radius: 18px;">
    <div class="container" style="max-width: 600px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge badge-primary px-2 py-1 font-weight-bold" id="totalBoxesCount">0 Boxes</span>
                <h5 class="mb-0 font-weight-bold text-success mt-1" id="grandTotalContainer" style="{{ (isset($agent) && !$agent->see_price) ? 'display: none;' : '' }}">₹<span id="grandTotalText">0</span></h5>
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3" id="modalProdBody">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer border-top bg-light p-2 d-flex justify-content-between align-items-center" style="border-radius: 0 0 20px 20px;">
                <div class="d-flex align-items-center">
                    <span class="small font-weight-bold text-muted mr-2">Quantity:</span>
                    <div class="quantity-control-app d-inline-flex align-items-center p-0 bg-white border shadow-xs" style="border-radius: 8px; height: 32px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-modal-minus" style="border:0; height: 30px;"><i class="fas fa-minus"></i></button>
                        <input type="number" id="modalQtyInput" class="form-control form-control-sm text-center font-weight-bold p-0 border-0" style="width: 45px; height: 30px; background: transparent;" value="0" min="0">
                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-modal-plus" style="border:0; height: 30px; background: #26a744;"><i class="fas fa-plus"></i></button>
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
                <img src="" id="zoomedImage" style="max-height: 92vh; max-width: 95vw; width: auto; height: auto; object-fit: contain; box-shadow: 0 0 25px rgba(0,0,0,0.6); border-radius: 8px;">
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
                <div class="mt-2 text-white small">Point camera at sample tag or box barcode</div>
            </div>
            <div class="modal-footer p-2 bg-light border-0">
                <div class="input-group input-group-sm">
                    <input type="text" id="manualBarcodeInput" class="form-control" placeholder="Type barcode (e.g. D116S4C2)">
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
            <div class="modal-body p-2" id="cartItemsList">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer p-2 bg-light border-0 flex-column align-items-stretch">
                <div class="row no-gutters mb-2" id="offlinePricingInputs" style="{{ (isset($agent) && !$agent->see_price) ? 'display: none;' : '' }}">
                    <div class="col-6 pr-1">
                        <input type="number" id="offlineDiscount" class="form-control form-control-sm" placeholder="Discount ₹" min="0">
                    </div>
                    <div class="col-6 pl-1">
                        <input type="number" id="offlineOtherCharges" class="form-control form-control-sm" placeholder="Other Charges ₹" min="0">
                    </div>
                </div>
                <input type="text" id="offlineRemark" class="form-control form-control-sm mb-2" placeholder="Order Remark (Optional)">
                <button type="button" class="btn btn-success btn-block rounded-pill font-weight-bold" id="btnModalSaveOrder">
                    <i class="fas fa-save mr-1"></i> Confirm & Save Offline
                </button>
            </div>
        </div>
    </div>
</div>

<!-- OFFLINE SCAN SELECTION / COLOR MATRIX MODAL -->
<div class="modal fade bottom-drawer" id="offlineScanSelectionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document" style="max-width: 980px; width: 96%;">
        <div class="modal-content border-0 position-relative shadow" style="border-radius: 20px 20px 0 0;">
            <button type="button" class="close position-absolute" data-dismiss="modal" aria-label="Close" style="top: 10px; right: 16px; z-index: 1050; font-size: 26px; opacity: 0.7;">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body bg-white pt-2 px-3" style="overflow-y: auto; -webkit-overflow-scrolling: touch; overscroll-behavior-y: contain; max-height: calc(100vh - 120px);">
                <div id="offlineColorSelectionList" class="pb-2">
                    <!-- Excel Matrix Content dynamically injected here -->
                </div>
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
<script>
// Global helper to resolve offline image errors via IndexedDB blob cache or Cache API
window.handleOfflineImageError = function (imgEl, filename) {
    if (!imgEl) return;
    const key = filename || (imgEl.getAttribute('data-src') || imgEl.src || '').split('/').pop().split('?')[0];
    if (!key) {
        imgEl.style.display = 'none';
        if (imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('placeholder-box')) {
            imgEl.nextElementSibling.style.display = 'flex';
        }
        if (imgEl.id === 'scanHeroImage') {
            const ph = document.getElementById('scanHeroPlaceholder');
            if (ph) ph.style.display = 'flex';
        }
        return;
    }
    if (window.SnapKidOfflineDB && SnapKidOfflineDB.getImageBlobUrl) {
        SnapKidOfflineDB.getImageBlobUrl(key).then(blobUrl => {
            if (blobUrl) {
                imgEl.onerror = null;
                imgEl.src = blobUrl;
                imgEl.dataset.src = blobUrl;
                imgEl.style.display = '';
                if (imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('placeholder-box')) {
                    imgEl.nextElementSibling.style.display = 'none';
                }
                if (imgEl.id === 'scanHeroImage') {
                    const ph = document.getElementById('scanHeroPlaceholder');
                    if (ph) ph.style.display = 'none';
                }
            } else {
                imgEl.style.display = 'none';
                if (imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('placeholder-box')) {
                    imgEl.nextElementSibling.style.display = 'flex';
                }
                if (imgEl.id === 'scanHeroImage') {
                    const ph = document.getElementById('scanHeroPlaceholder');
                    if (ph) ph.style.display = 'flex';
                }
            }
        }).catch(() => {
            imgEl.style.display = 'none';
            if (imgEl.nextElementSibling && imgEl.nextElementSibling.classList.contains('placeholder-box')) {
                imgEl.nextElementSibling.style.display = 'flex';
            }
            if (imgEl.id === 'scanHeroImage') {
                const ph = document.getElementById('scanHeroPlaceholder');
                if (ph) ph.style.display = 'flex';
            }
        });
    }
};

$(document).ready(function () {
    let allProducts = [];
    let displayedProducts = [];
    let cart = new Map();
    let currentPage = 1;
    const pageSize = 24;
    let scanner = null;
    let metaInfo = null;
    let activeModalKey = null;

    // Check sales agent rate viewing permission
    const agentSeePrice = {{ (isset($agent) && $agent->see_price) ? 'true' : 'false' }};
    function canSeePrice() {
        if (!agentSeePrice) return false;
        if (metaInfo && typeof metaInfo.see_price !== 'undefined') {
            return (metaInfo.see_price === true || metaInfo.see_price === 1 || metaInfo.see_price === '1');
        }
        return agentSeePrice;
    }

    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }

    // 1. Initialize Offline Catalog & Shops
    async function initOfflineCatalog() {
        try {
            metaInfo = await SnapKidOfflineDB.getMeta();
            const shops = await SnapKidOfflineDB.getShops();
            const shopSelect = $('#offlineShopSelect');

            shopSelect.empty().append('<option value="">-- Choose Shop --</option>');
            shops.forEach(s => {
                const opt = $(`<option value="${s.id}">${s.name} (${s.city || 'Local'})</option>`);
                opt.data('shop', s);
                shopSelect.append(opt);
            });

            const savedShopId = localStorage.getItem('last_offline_shop_id');
            if (savedShopId && shops.some(s => s.id == savedShopId)) {
                shopSelect.val(savedShopId);
                shopSelect.trigger('change');
            }

            allProducts = await SnapKidOfflineDB.getCatalog();
            $('#catalogLoadingMsg').hide();

            if (allProducts.length === 0) {
                $('#productsGrid').html(`
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-2"></i>
                        <h6 class="font-weight-bold">No Offline Products Found</h6>
                        <p class="text-muted small">Please go back to Offline Hub and click <strong>"Sync Catalog"</strong> when you have internet.</p>
                        <a href="{{ route('agent.offline.index') }}" class="btn btn-primary btn-sm rounded-pill px-4">Go to Offline Hub</a>
                    </div>
                `);
                return;
            }

            populateFilterDropdowns();
            filterAndRenderProducts();
        } catch (e) {
            console.error('Failed to load offline data:', e);
            $('#catalogLoadingMsg').html('<p class="text-danger">Failed to load offline data.</p>');
        }
    }

    // Populate Filters
    function populateFilterDropdowns() {
        const colors = [...new Set(allProducts.map(p => p.color_name).filter(Boolean))].sort();
        const sizes = [...new Set(allProducts.map(p => p.size_set_name).filter(Boolean))].sort();
        const brands = [...new Set(allProducts.map(p => p.brand_name).filter(Boolean))].sort();
        const fittings = [...new Set(allProducts.map(p => p.fitting_name).filter(Boolean))].sort();

        const colorSel = $('#filterColorSelect').empty().append('<option value="">All Colors</option>');
        colors.forEach(c => colorSel.append(`<option value="${c}">${c}</option>`));

        const sizeSel = $('#filterSizeSelect').empty().append('<option value="">All Size Sets</option>');
        sizes.forEach(s => sizeSel.append(`<option value="${s}">${s}</option>`));

        const brandSel = $('#filterBrandSelect').empty().append('<option value="">All Brands</option>');
        brands.forEach(b => brandSel.append(`<option value="${b}">${b}</option>`));

        const fittingSel = $('#filterFittingSelect').empty().append('<option value="">All Fittings</option>');
        fittings.forEach(f => fittingSel.append(`<option value="${f}">${f}</option>`));
    }

    initOfflineCatalog();

    // Toggle Filter Drawer
    $('#btnToggleFilters').click(function () {
        $('#filterContainer').slideToggle(200);
    });

    $('#filterColorSelect, #filterSizeSelect, #filterBrandSelect, #filterFittingSelect').on('change', function () {
        filterAndRenderProducts(true);
    });

    $('#btnResetFilters').click(function () {
        $('#filterColorSelect').val('');
        $('#filterSizeSelect').val('');
        $('#filterBrandSelect').val('');
        $('#filterFittingSelect').val('');
        $('#offlineSearchInput').val('');
        $('#btnClearSearch').hide();
        filterAndRenderProducts(true);
    });

    // Shop change handler
    $('#offlineShopSelect').on('change', function () {
        const selected = $(this).find('option:selected').data('shop');
        if (selected) {
            localStorage.setItem('last_offline_shop_id', selected.id);
            $('#shopPhoneText').text(selected.phone || 'N/A');
            $('#shopBalanceText').text(selected.balance || '0');
            $('#shopAddressText').text(selected.address || 'N/A');
            $('#shopDetailsBox').slideDown();
        } else {
            $('#shopDetailsBox').slideUp();
        }
    });

    // 2. Filter & Render Product Cards
    function filterAndRenderProducts(resetPage = true) {
        if (resetPage) {
            currentPage = 1;
        }

        const query = $('#offlineSearchInput').val().trim().toLowerCase();
        const filterColor = $('#filterColorSelect').val();
        const filterSize = $('#filterSizeSelect').val();
        const filterBrand = $('#filterBrandSelect').val();
        const filterFitting = $('#filterFittingSelect').val();

        displayedProducts = allProducts.filter(p => {
            if (filterColor && p.color_name !== filterColor) return false;
            if (filterSize && p.size_set_name !== filterSize) return false;
            if (filterBrand && p.brand_name !== filterBrand) return false;
            if (filterFitting && p.fitting_name !== filterFitting) return false;

            if (query) {
                const matchDesign = p.design_number && p.design_number.toLowerCase().includes(query);
                const matchSeries = p.series_name && p.series_name.toLowerCase().includes(query);
                const matchBrand = p.brand_name && p.brand_name.toLowerCase().includes(query);
                const matchColor = p.color_name && p.color_name.toLowerCase().includes(query);
                const matchSize = p.size_set_name && p.size_set_name.toLowerCase().includes(query);
                const matchGarment = p.name_of_garment && String(p.name_of_garment).toLowerCase().includes(query);
                const matchFitting = p.fitting_name && p.fitting_name.toLowerCase().includes(query);
                const matchPattern = p.pattern_name && p.pattern_name.toLowerCase().includes(query);
                const matchBarcode = p.barcode && p.barcode.toLowerCase().includes(query);

                return matchDesign || matchSeries || matchBrand || matchColor || matchSize || matchGarment || matchFitting || matchPattern || matchBarcode;
            }

            return true;
        });

        const limit = currentPage * pageSize;
        const toRender = displayedProducts.slice(0, limit);

        if (toRender.length === 0) {
            $('#productsGrid').html(`
                <div class="col-12 text-center py-4 text-muted">
                    <i class="fas fa-search fa-2x mb-2 text-muted opacity-50"></i>
                    <p class="mb-0 font-weight-bold">No matching products found.</p>
                    <small>Try clearing your search query or reset filters.</small>
                </div>
            `);
            $('#loadMoreContainer').hide();
            return;
        }

        const showStock = !metaInfo || !metaInfo.settings || metaInfo.settings.agent_app_show_stock !== 0;
        const seePrice = canSeePrice();

        let html = '';
        toRender.forEach(prod => {
            const inCartQty = cart.has(prod.key) ? cart.get(prod.key).qty : 0;
            const imgSrc = prod.image_url || '';
            const imgFile = prod.image || (prod.image_url ? prod.image_url.split('/').pop().split('?')[0] : '');

            // Clean title logic
            const seriesName = prod.series_name || '';
            const garmentName = prod.name_of_garment ? String(prod.name_of_garment).trim() : '';
            let title = '';
            if (seriesName && garmentName && seriesName !== garmentName) {
                title = `${seriesName} ${garmentName}`;
            } else if (seriesName) {
                title = seriesName;
            } else if (garmentName) {
                title = `Garment ${garmentName}`;
            } else {
                title = `Design #${prod.design_number}`;
            }

            html += `
                <div class="col-6 col-md-4 col-lg-3 mb-2 px-1">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden variation-card ${inCartQty > 0 ? 'border-success' : ''}" data-key="${prod.key}" style="border-radius: 12px; font-size: 13px;">
                        
                        <!-- Image Container with Badges -->
                        <div style="height: 140px; background: #f8fafc;" class="d-flex align-items-center justify-content-center position-relative">
                            ${imgSrc ? `
                                <img src="${imgSrc}" class="w-100 h-100 zoom-image-trigger" data-src="${imgSrc}" style="object-fit: contain; cursor: pointer;" onerror="handleOfflineImageError(this, '${imgFile}')">
                                <div class="w-100 h-100 align-items-center justify-content-center text-muted placeholder-box" style="display:none;">
                                    <i class="fas fa-tshirt fa-2x opacity-25"></i>
                                </div>
                            ` : `
                                <i class="fas fa-tshirt fa-2x text-muted opacity-25"></i>
                            `}
                            
                            <!-- Design Number Badge -->
                            <span class="badge badge-dark position-absolute" style="top: 6px; left: 6px; font-size: 10.5px; font-weight: 700; border-radius: 6px; opacity: 0.9; letter-spacing: 0.5px;">
                                #${prod.design_number}
                            </span>

                            <!-- Zoom Button on image -->
                            ${imgSrc ? `
                                <span class="badge badge-light position-absolute zoom-image-trigger" data-src="${imgSrc}" style="top: 6px; right: 6px; cursor: pointer; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15);" title="Zoom Photo">
                                    <i class="fas fa-search-plus text-secondary" style="font-size: 10px;"></i>
                                </span>
                            ` : ''}
                        </div>

                        <!-- Card Body -->
                        <div class="card-body p-2 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Product / Series Title -->
                                <h6 class="font-weight-bold text-dark mb-1 text-truncate" style="font-size: 12.5px;" title="${title}">
                                    ${title}
                                </h6>
                                
                                <!-- Color & Size Set -->
                                <div class="d-flex justify-content-between align-items-center mb-1 text-muted" style="font-size: 11px;">
                                    <span class="text-truncate mr-1" title="${prod.color_name}"><i class="fas fa-palette mr-1 text-primary"></i>${prod.color_name}</span>
                                    <span class="font-weight-bold text-dark text-nowrap"><i class="fas fa-ruler-combined mr-1 text-muted"></i>${prod.size_set_name}</span>
                                </div>

                                <!-- Fitting & Pattern tags -->
                                <div class="d-flex align-items-center text-truncate mb-2" style="font-size: 10px;">
                                    ${prod.fitting_name ? `<span class="badge badge-light border text-muted mr-1 px-1 py-0 text-truncate" style="max-width: 65px;" title="${prod.fitting_name}">${prod.fitting_name}</span>` : ''}
                                    ${prod.pattern_name ? `<span class="badge badge-light border text-muted mr-1 px-1 py-0 text-truncate" style="max-width: 65px;" title="${prod.pattern_name}">${prod.pattern_name}</span>` : ''}
                                    <span class="text-muted ml-auto">${prod.pcs_per_box} pcs</span>
                                </div>

                                <!-- Available Stock & Price Box -->
                                <div class="bg-light rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                                    ${showStock ? `
                                        <div>
                                            <small class="text-muted d-block uppercase font-weight-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;">AVAILABLE</small>
                                            <span class="font-weight-bold text-dark" style="font-size: 11.5px;">${prod.available_boxes} Box</span>
                                        </div>
                                    ` : `<div></div>`}

                                    ${seePrice ? `
                                        <div class="text-right">
                                            <small class="text-muted d-block uppercase font-weight-bold" style="font-size: 0.6rem; letter-spacing: 0.5px;">PRICE</small>
                                            <span class="text-primary font-weight-bold" style="font-size: 12px;">₹${Number(prod.unit_price).toLocaleString()}</span>
                                            ${prod.mrp > 0 ? `<small class="text-muted d-block" style="font-size: 9.5px; line-height: 1;">MRP: ₹${Number(prod.mrp).toLocaleString()}</small>` : ''}
                                        </div>
                                    ` : `<div></div>`}
                                </div>
                            </div>

                            <!-- Bottom Row: Specs & Stepper -->
                            <div class="d-flex align-items-center justify-content-between pt-1 border-top">
                                <button type="button" class="btn btn-link btn-sm p-0 text-muted btn-open-details" data-key="${prod.key}" style="font-size: 11px; text-decoration: none;" title="View Complete Product Details">
                                    <i class="fas fa-info-circle mr-1 text-primary"></i>Specs
                                </button>
                                <div class="quantity-control-app d-inline-flex align-items-center p-0 bg-white border shadow-xs" style="border-radius: 8px; height: 28px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-minus" data-key="${prod.key}" style="border:0; height: 26px;">
                                        <i class="fas fa-minus" style="font-size: 9px;"></i>
                                    </button>
                                    <input type="number" class="form-control form-control-sm text-center font-weight-bold p-0 border-0 qty-input" 
                                        style="height: 26px; width: 36px; background: transparent;" 
                                        data-key="${prod.key}" 
                                        value="${inCartQty}" min="0" max="${prod.available_boxes}">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-plus" data-key="${prod.key}" style="border:0; height: 26px; background: #26a744;">
                                        <i class="fas fa-plus" style="font-size: 9px;"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            `;
        });

        $('#productsGrid').html(html);

        if (displayedProducts.length > limit) {
            $('#loadMoreContainer').show();
        } else {
            $('#loadMoreContainer').hide();
        }
    }

    // Search events
    $('#offlineSearchInput').on('input', function () {
        const val = $(this).val();
        if (val) $('#btnClearSearch').show();
        else $('#btnClearSearch').hide();
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

    // 3. Cart & Quantity Handlers
    function updateCartItem(key, qty) {
        qty = Math.max(0, parseInt(qty) || 0);
        const prod = allProducts.find(p => p.key === key);
        if (!prod) return;

        if (qty > 0) {
            cart.set(key, { item: prod, qty: qty });
        } else {
            cart.delete(key);
        }

        $(`.qty-input[data-key="${key}"]`).val(qty);
        if (activeModalKey === key) {
            $('#modalQtyInput').val(qty);
        }

        // Highlight card if in cart
        const card = $(`.variation-card[data-key="${key}"]`);
        if (qty > 0) card.addClass('border-success');
        else card.removeClass('border-success');

        updateSummaryUI();
    }

    $(document).on('click', '.btn-plus', function () {
        const key = $(this).data('key');
        const current = cart.has(key) ? cart.get(key).qty : 0;
        updateCartItem(key, current + 1);
    });

    $(document).on('click', '.btn-minus', function () {
        const key = $(this).data('key');
        const current = cart.has(key) ? cart.get(key).qty : 0;
        if (current > 0) {
            updateCartItem(key, current - 1);
        }
    });

    $(document).on('change', '.qty-input', function () {
        const key = $(this).data('key');
        updateCartItem(key, $(this).val());
    });

    // Modal quantity controls
    $('.btn-modal-plus').click(function () {
        if (!activeModalKey) return;
        const current = cart.has(activeModalKey) ? cart.get(activeModalKey).qty : 0;
        updateCartItem(activeModalKey, current + 1);
    });

    $('.btn-modal-minus').click(function () {
        if (!activeModalKey) return;
        const current = cart.has(activeModalKey) ? cart.get(activeModalKey).qty : 0;
        if (current > 0) {
            updateCartItem(activeModalKey, current - 1);
        }
    });

    $('#modalQtyInput').on('change', function () {
        if (!activeModalKey) return;
        updateCartItem(activeModalKey, $(this).val());
    });

    // 4. Product Details Modal
    $(document).on('click', '.btn-open-details', function () {
        const key = $(this).data('key');
        const prod = allProducts.find(p => p.key === key);
        if (!prod) return;

        activeModalKey = key;
        const currentQty = cart.has(key) ? cart.get(key).qty : 0;
        $('#modalQtyInput').val(currentQty);

        $('#modalProdDesignBadge').text('#' + prod.design_number);
        const title = (prod.series_name ? prod.series_name + ' ' : '') + (prod.name_of_garment ? prod.name_of_garment : '');
        $('#modalProdTitle').text(title || prod.design_number);

        const imgSrc = prod.image_url || '';
        const imgFile = prod.image || (prod.image_url ? prod.image_url.split('/').pop().split('?')[0] : '');
        let bodyHtml = `
            <div class="row align-items-center mb-3">
                <div class="col-4 text-center">
                    <div style="height: 120px; background: #f8fafc; border-radius: 12px; overflow: hidden;" class="d-flex align-items-center justify-content-center border">
                        ${imgSrc ? `
                            <img src="${imgSrc}" class="w-100 h-100 zoom-image-trigger" data-src="${imgSrc}" style="object-fit: contain; cursor: pointer;" onerror="handleOfflineImageError(this, '${imgFile}')">
                        ` : `
                            <i class="fas fa-tshirt fa-3x text-muted opacity-25"></i>
                        `}
                    </div>
                    ${imgSrc ? `<small class="text-primary font-weight-bold d-block mt-1 zoom-image-trigger" data-src="${imgSrc}" style="cursor: pointer;"><i class="fas fa-search-plus mr-1"></i>Zoom</small>` : ''}
                </div>
                <div class="col-8">
                    <h6 class="font-weight-bold text-dark mb-1">${title || ('Design #' + prod.design_number)}</h6>
                    <div class="small text-muted mb-1"><i class="fas fa-tag mr-1 text-secondary"></i> Brand: <strong class="text-dark">${prod.brand_name || 'SNAPKID'}</strong></div>
                    <div class="small text-muted mb-1"><i class="fas fa-layer-group mr-1 text-secondary"></i> Series: <strong class="text-dark">${prod.series_name || '-'}</strong></div>
                    <div class="small text-muted"><i class="fas fa-barcode mr-1 text-secondary"></i> Barcode: <code class="text-dark font-weight-bold">${prod.barcode}</code></div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0" style="font-size: 12.5px;">
                    <tbody>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted" style="width: 35%;">Color</td>
                            <td class="font-weight-bold text-dark"><i class="fas fa-palette mr-1 text-primary"></i> ${prod.color_name}</td>
                        </tr>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Size Set</td>
                            <td class="font-weight-bold text-dark"><i class="fas fa-ruler-combined mr-1 text-primary"></i> ${prod.size_set_name} ${prod.size_group ? '(' + prod.size_group + ')' : ''}</td>
                        </tr>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Pieces / Box</td>
                            <td class="font-weight-bold text-dark">${prod.pcs_per_box} Pieces</td>
                        </tr>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Fitting</td>
                            <td>${prod.fitting_name || '-'}</td>
                        </tr>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Pattern</td>
                            <td>${prod.pattern_name || '-'}</td>
                        </tr>
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Available Stock</td>
                            <td class="font-weight-bold text-success">${prod.available_boxes} Boxes</td>
                        </tr>
                        ${canSeePrice() ? `
                        <tr>
                            <td class="bg-light font-weight-bold text-muted">Unit Price</td>
                            <td class="font-weight-bold text-primary">₹${Number(prod.unit_price).toLocaleString()}</td>
                        </tr>
                        ${prod.mrp > 0 ? `
                            <tr>
                                <td class="bg-light font-weight-bold text-muted">MRP</td>
                                <td class="text-muted">₹${Number(prod.mrp).toLocaleString()}</td>
                            </tr>
                        ` : ''}
                        ` : ''}
                    </tbody>
                </table>
            </div>
        `;

        $('#modalProdBody').html(bodyHtml);
        $('#productDetailsModal').modal('show');
    });

    // 5. Image Zoom Handler (with IndexedDB blob support)
    $(document).on('click', '.zoom-image-trigger', async function (e) {
        e.stopPropagation();
        let src = $(this).data('src') || $(this).attr('src');
        if (!src) return;
        if (!src.startsWith('blob:') && !src.startsWith('data:') && window.SnapKidOfflineDB) {
            try {
                const blobUrl = await SnapKidOfflineDB.getImageBlobUrl(src);
                if (blobUrl) src = blobUrl;
            } catch (_) {}
        }
        $('#zoomedImage').attr('src', src);
        $('#imageZoomModal').modal('show');
    });

    // 6. Update Bottom Summary Bar
    function updateSummaryUI() {
        const seePrice = canSeePrice();
        let totalBoxes = 0;
        let subtotal = 0;
        let itemsCount = cart.size;

        cart.forEach((val) => {
            totalBoxes += val.qty;
            const pcs = val.qty * (val.item.pcs_per_box || 1);
            subtotal += (pcs * val.item.unit_price);
        });

        const discount = parseFloat($('#offlineDiscount').val()) || 0;
        const otherCharges = parseFloat($('#offlineOtherCharges').val()) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const gst = Math.round(taxable * 0.05);
        const grandTotal = Math.round(taxable + gst + otherCharges);

        $('#totalBoxesCount').text(totalBoxes + (totalBoxes === 1 ? ' Box' : ' Boxes'));
        $('#cartItemsCount').text(itemsCount);

        if (seePrice) {
            $('#grandTotalContainer').show();
            $('#grandTotalText').text(grandTotal.toLocaleString());
            $('#offlinePricingInputs').show();
        } else {
            $('#grandTotalContainer').hide();
            $('#offlinePricingInputs').hide();
        }

        if (totalBoxes > 0) {
            $('#offlineSummaryBar').slideDown();
        } else {
            $('#offlineSummaryBar').slideUp();
        }
    }

    $('#offlineDiscount, #offlineOtherCharges').on('input', updateSummaryUI);

    // 7. Cart Details Modal Rendering
    $('#btnOpenCartModal').click(function () {
        const seePrice = canSeePrice();
        const list = $('#cartItemsList');
        if (cart.size === 0) {
            list.html('<p class="text-center text-muted py-3">Cart is empty</p>');
            $('#cartDetailsModal').modal('show');
            return;
        }

        let html = '';
        cart.forEach((val, key) => {
            const p = val.item;
            const itemTotal = val.qty * (p.pcs_per_box || 1) * p.unit_price;
            const title = (p.series_name ? p.series_name + ' ' : '') + (p.name_of_garment ? p.name_of_garment : '');
            html += `
                <div class="d-flex justify-content-between align-items-center p-2 mb-1 bg-light rounded">
                    <div style="max-width: 65%;">
                        <div class="font-weight-bold text-dark text-truncate">#${p.design_number} ${title ? ' - ' + title : ''}</div>
                        <small class="text-muted"><i class="fas fa-palette mr-1 text-primary"></i>${p.color_name} | <i class="fas fa-ruler-combined mr-1 text-muted"></i>${p.size_set_name} (${val.qty} Boxes)</small>
                    </div>
                    <div class="text-right">
                        ${seePrice ? `<div class="font-weight-bold text-primary">₹${Math.round(itemTotal).toLocaleString()}</div>` : ''}
                        <button type="button" class="btn btn-xs text-danger btn-remove-cart" data-key="${key}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        list.html(html);
        if (seePrice) {
            $('#offlinePricingInputs').show();
        } else {
            $('#offlinePricingInputs').hide();
        }
        $('#cartDetailsModal').modal('show');
    });

    $(document).on('click', '.btn-remove-cart', function () {
        const key = $(this).data('key');
        updateCartItem(key, 0);
        $(this).closest('div.d-flex').remove();
    });

    // 8. Camera Scanner (Html5Qrcode)
    $('#btnOpenScanner').click(function () {
        $('#offlineScannerModal').modal('show');
    });

    $('#offlineScannerModal').on('shown.bs.modal', function () {
        startOfflineScanner();
    });

    $('#offlineScannerModal').on('hidden.bs.modal', function () {
        stopOfflineScanner();
    });

    function startOfflineScanner() {
        if (scanner) return;
        try {
            scanner = new Html5Qrcode("offlineReader");
            const config = {
                fps: 15,
                qrbox: function (viewfinderWidth, viewfinderHeight) {
                    const width = Math.min(Math.floor(viewfinderWidth * 0.88), 320);
                    const height = Math.min(Math.floor(viewfinderHeight * 0.65), 220);
                    return { width, height };
                },
                aspectRatio: 1.0
            };
            scanner.start({ facingMode: "environment" }, config, onBarcodeScanned)
                .catch(err => {
                    console.error('Camera error:', err);
                    Swal.fire('Camera Error', 'Could not access camera. Please allow camera permissions in browser settings.', 'error');
                });
        } catch (e) {
            console.error(e);
            Swal.fire('Camera Error', 'Could not initialize camera scanner: ' + e.message, 'error');
        }
    }

    function stopOfflineScanner() {
        if (scanner) {
            scanner.stop().then(() => { scanner = null; }).catch(() => { scanner = null; });
        }
    }

    async function onBarcodeScanned(decodedText) {
        stopOfflineScanner();
        $('#offlineScannerModal').modal('hide');
        processBarcodeMatch(decodedText);
    }

    $('#btnManualBarcodeSubmit').click(function () {
        const code = $('#manualBarcodeInput').val().trim();
        if (code) {
            $('#offlineScannerModal').modal('hide');
            processBarcodeMatch(code);
        }
    });

    $('#manualBarcodeInput').on('keypress', function (e) {
        if (e.which === 13) {
            $('#btnManualBarcodeSubmit').click();
        }
    });



    // Inline Catalog Sync
    $('#btnSyncCatalogInline').click(async function () {
        if (!navigator.onLine) {
            Swal.fire('No Internet', 'Please connect to internet to sync catalog data.', 'warning');
            return;
        }

        const btn = $(this);
        const icon = $('#inlineSyncIcon');
        btn.prop('disabled', true);
        icon.addClass('fa-spin');
        $('#inlineSyncProgressContainer').slideDown();
        $('#inlineSyncStatusText').text('Downloading latest catalog...');
        $('#inlineSyncProgressBar').css('width', '25%');
        $('#inlineSyncPercentText').text('25%');

        try {
            const res = await $.ajax({
                url: "{{ route('agent.offline.catalog-data') }}",
                method: "GET"
            });

            if (res.success) {
                $('#inlineSyncStatusText').text('Updating offline database...');
                $('#inlineSyncProgressBar').css('width', '60%');
                $('#inlineSyncPercentText').text('60%');

                await SnapKidOfflineDB.saveCatalogData(res, function (cached, total) {
                    const pct = Math.round(60 + ((cached / total) * 40));
                    $('#inlineSyncProgressBar').css('width', pct + '%');
                    $('#inlineSyncPercentText').text(pct + '%');
                    $('#inlineSyncStatusText').text('Caching photos (' + cached + '/' + total + ')...');
                });

                $('#inlineSyncProgressBar').css('width', '100%');
                $('#inlineSyncPercentText').text('100%');
                $('#inlineSyncStatusText').text('Synced!');

                setTimeout(() => {
                    $('#inlineSyncProgressContainer').slideUp();
                    btn.prop('disabled', false);
                    icon.removeClass('fa-spin');
                    initOfflineCatalog();
                    Swal.fire({
                        icon: 'success',
                        title: 'Catalog Synced!',
                        text: res.shops.length + ' shops and ' + res.products.length + ' products ready offline.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 600);
            } else {
                throw new Error(res.message || 'Sync failed');
            }
        } catch (err) {
            $('#inlineSyncProgressContainer').slideUp();
            btn.prop('disabled', false);
            icon.removeClass('fa-spin');
            Swal.fire('Sync Error', 'Could not sync catalog: ' + (err.message || 'Network error'), 'error');
        }
    });

    async function processBarcodeMatch(rawCode) {
        if (!rawCode) return;
        let code = String(rawCode).trim();

        // If scanned text is a URL, extract barcode segment
        if (code.includes('/fc/')) {
            const parts = code.split('/fc/')[1];
            code = parts.split('/')[0].split('?')[0].split('#')[0].trim();
        } else if (code.includes('/sc/')) {
            const parts = code.split('/sc/')[1];
            code = parts.split('/')[0].split('?')[0].split('#')[0].trim();
        } else if (code.startsWith('http://') || code.startsWith('https://')) {
            const cleanUrl = code.split('?')[0].split('#')[0];
            const parts = cleanUrl.split('/');
            code = parts[parts.length - 1].trim();
        }

        const res = await SnapKidOfflineDB.findVariationByBarcode(code);
        if (res && res.success && res.size_sets && res.size_sets.length > 0) {
            showOfflineColorSelection(res);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Barcode Not Found',
                text: `No matching product found for code "${code}" in offline catalog. If newly added, please tap "Sync" to update.`,
                confirmButtonText: 'OK'
            });
        }
    }

    function showOfflineColorSelection(data) {
        const list = $('#offlineColorSelectionList');
        list.empty();

        if (!data.size_sets || data.size_sets.length === 0) {
            list.append('<div class="alert alert-warning">No variations found for this product.</div>');
            $('#offlineScanSelectionModal').modal('show');
            return;
        }

        // Hero Image resolution
        let initialHeroImage = data.product.image || '';
        if (!initialHeroImage) {
            for (let ss of data.size_sets) {
                if (ss.image) { initialHeroImage = ss.image; break; }
                if (ss.colors) {
                    for (let c of ss.colors) {
                        if (c.image) { initialHeroImage = c.image; break; }
                    }
                }
                if (initialHeroImage) break;
            }
        }

        const heroFilename = (initialHeroImage || (data.product && (data.product.image || data.product.image_url)) || '').split('/').pop().split('?')[0];

        let maxColors = 0;
        data.size_sets.forEach(ss => {
            const count = (ss.colors || []).length;
            if (count > maxColors) maxColors = count;
        });
        if (maxColors === 0) maxColors = 1;

        let html = '';

        // 1. Header with Design Number & Series / Garment
        html += `
            <div class="text-center mb-1">
                <span class="badge badge-primary px-3 py-1 font-weight-bold shadow-sm d-inline-block" style="font-size: 15px; border-radius: 8px; letter-spacing: 0.5px;">
                    DESIGN NO: ${data.product.design_number || 'N/A'}
                </span>
                ${(data.product.name && data.product.name !== data.product.design_number) ? `
                    <div class="font-weight-bold text-dark mt-1" style="font-size: 14px;">${data.product.name}</div>
                ` : ''}
                ${data.product.series_name ? `
                    <div class="small text-muted font-weight-bold"><i class="fas fa-layer-group mr-1"></i> ${data.product.series_name}</div>
                ` : ''}
            </div>
        `;

        // 2. Centered Large Preview Hero Image
        html += `
            <div class="text-center mb-2">
                <div class="d-inline-block position-relative rounded-xl shadow-sm border overflow-hidden bg-white" style="max-width: 100%;">
                    <img id="scanHeroImage" 
                         src="${initialHeroImage || ''}" 
                         class="img-fluid zoom-image-trigger" 
                         style="max-height: 380px; min-height: 200px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer; ${!initialHeroImage ? 'display:none;' : ''}" 
                         data-src="${initialHeroImage || ''}"
                         onerror="handleOfflineImageError(this, '${heroFilename}')"
                         alt="Product Preview">
                    <div id="scanHeroPlaceholder" style="width: 260px; height: 180px; display: ${initialHeroImage ? 'none' : 'flex'}; align-items: center; justify-content: center; background: #f8f9fa;">
                        <div class="text-muted text-center"><i class="fas fa-image fa-3x opacity-50 mb-1"></i><div class="small font-weight-bold">No Image</div></div>
                    </div>
                    <span class="badge badge-dark position-absolute" style="bottom: 8px; right: 8px; opacity: 0.85; pointer-events: none; font-size: 11px; padding: 4px 8px; border-radius: 6px;">
                        <i class="fas fa-search-plus mr-1"></i>Tap to Zoom
                    </span>
                </div>
            </div>
        `;

        // 3. Master Global Apply Bar
        html += `
            <div class="card border-0 mb-3 shadow-sm" style="background: linear-gradient(90deg, #fefce8 0%, #f0fdf4 100%); border: 1.5px solid #facc15 !important; border-radius: 12px;">
                <div class="card-body p-2 d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                    <div>
                        <span class="font-weight-bold" style="color: #854d0e;"><i class="fas fa-bolt mr-1" style="color: #eab308;"></i> Apply Quantity to All</span>
                        <div class="small text-muted">Set quantity for all size sets and colors</div>
                    </div>
                    <div class="quantity-control-app d-flex align-items-center p-1 bg-white shadow-xs" style="border-radius: 8px; border: 1.5px solid #facc15;">
                        <button type="button" class="btn-q btn-minus-master font-weight-bold" style="color: #854d0e;">-</button>
                        <input type="number" id="masterQtyInput" class="font-weight-bold" min="0" value="0" style="color: #854d0e; width: 45px; text-align: center; border: 0; background: transparent;">
                        <button type="button" class="btn-q btn-plus-master font-weight-bold" style="color: #854d0e;">+</button>
                    </div>
                </div>
            </div>
        `;

        // 4. Matrix Structure Table
        html += `
            <div class="table-responsive border rounded-lg mb-3 shadow-sm bg-white" style="max-height: 440px; overflow-y: auto;">
                <table class="table table-bordered table-sm mb-0 matrix-table text-center align-middle" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th class="align-middle text-center font-weight-bold px-2 py-2" style="min-width: 140px; width: 140px; background: linear-gradient(135deg, #ffd600 0%, #f59e0b 100%); background-color: #ffd600; color: #78350f !important; text-shadow: 0 1px 0 rgba(255,255,255,0.3);">
                                SIZE SET
                            </th>
                            ${Array.from({ length: maxColors }).map((_, idx) => {
                                let colColorObj = null;
                                for (let ss of data.size_sets) {
                                    if (ss.colors && ss.colors[idx]) {
                                        colColorObj = ss.colors[idx];
                                        break;
                                    }
                                }
                                const colName = colColorObj ? (colColorObj.name ? `${colColorObj.name}` : `COLOR ${colColorObj.id}`) : `COL ${idx + 1}`;

                                return `
                                    <th class="align-middle text-center font-weight-bold text-white p-2 matrix-col-header matrix-col-${idx}" data-col-index="${idx}" style="min-width: 145px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); background-color: #16a34a; color: #fff;">
                                        <div class="custom-control custom-checkbox d-inline-block text-left" style="max-width: 100%;">
                                            <input type="checkbox" class="custom-control-input toggle-col-checkbox" id="toggle_col_${idx}" data-col-index="${idx}" checked>
                                            <label class="custom-control-label font-weight-bold text-white" for="toggle_col_${idx}" style="font-size: 13px; cursor: pointer; user-select: none; line-height: 1.5;" title="${colName}">
                                                <span class="d-inline-block text-truncate" style="max-width: 105px; vertical-align: middle;">${colName}</span>
                                            </label>
                                        </div>
                                        <div class="mt-1">
                                            <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px;">
                                                <button type="button" class="btn-q btn-minus-col px-2 py-0 text-success font-weight-bold" data-col-index="${idx}" style="line-height: 1;">-</button>
                                                <input type="number" class="col-batch-qty-input font-weight-bold text-center text-success" data-col-index="${idx}" min="0" value="0" style="width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                                <button type="button" class="btn-q btn-plus-col px-2 py-0 text-success font-weight-bold" data-col-index="${idx}" style="line-height: 1;">+</button>
                                            </div>
                                        </div>
                                    </th>
                                `;
                            }).join('')}
                        </tr>
                    </thead>
                    <tbody>
        `;

        data.size_sets.forEach(ss => {
            html += `
                <tr class="matrix-row-${ss.size_set_id}">
                    <td class="align-middle p-2 text-center matrix-ss-header-cell" style="min-width: 140px; width: 140px; background-color: #fefce8; border-right: 2px solid #fef08a;">
                        <div class="custom-control custom-checkbox d-inline-block mb-1">
                            <input type="checkbox" class="custom-control-input toggle-ss-checkbox" id="toggle_ss_${ss.size_set_id}" data-size-set="${ss.size_set_id}" checked>
                            <label class="custom-control-label font-weight-bold" for="toggle_ss_${ss.size_set_id}" style="color: #854d0e; font-size: 14px; cursor: pointer; user-select: none;">
                                ${ss.size_set_name}
                            </label>
                        </div>
                        <div>
                            <div class="quantity-control-app d-inline-flex align-items-center p-0 border bg-white shadow-xs" style="border-radius: 6px; height: 26px; border-color: #fde047 !important;">
                                <button type="button" class="btn-q btn-minus-ss px-2 py-0 font-weight-bold" data-size-set="${ss.size_set_id}" style="color: #854d0e; line-height: 1;">-</button>
                                <input type="number" class="ss-batch-qty-input font-weight-bold text-center" data-size-set="${ss.size_set_id}" min="0" value="0" style="color: #854d0e; width: 32px; border: 0; background: transparent; font-size: 12px; padding: 0;">
                                <button type="button" class="btn-q btn-plus-ss px-2 py-0 font-weight-bold" data-size-set="${ss.size_set_id}" style="color: #854d0e; line-height: 1;">+</button>
                            </div>
                        </div>
                    </td>
            `;

            for (let c = 0; c < maxColors; c++) {
                const colorObj = (ss.colors && ss.colors[c]) ? ss.colors[c] : null;
                if (!colorObj) {
                    html += `<td class="matrix-cell align-middle text-muted bg-light p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}">-</td>`;
                } else {
                    const vKey = colorObj.key || (data.product.id + '_' + colorObj.id + '_' + ss.size_set_id);
                    const currentQty = cart.has(vKey) ? cart.get(vKey).qty : 0;
                    const cImg = colorObj.image || ss.image || '';

                    html += `
                        <td class="matrix-cell matrix-cell-ss-${ss.size_set_id} matrix-cell-col-${c} align-middle p-2" data-size-set="${ss.size_set_id}" data-col-index="${c}" style="background: #fff; min-width: 145px;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="font-weight-bold text-dark text-truncate text-left flex-grow-1" style="font-size: 13px;" title="${colorObj.name}">
                                    ${colorObj.name}
                                </span>
                                ${cImg ? `
                                    <button type="button" class="btn btn-outline-info btn-xs rounded-circle p-0 ml-1 d-inline-flex align-items-center justify-content-center zoom-image-trigger" data-src="${cImg}" title="View Photo" style="width: 22px; height: 22px; flex-shrink: 0;">
                                        <i class="fas fa-camera" style="font-size: 10px;"></i>
                                    </button>
                                ` : ''}
                            </div>
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="quantity-control-app d-inline-flex align-items-center p-0 border shadow-xs" style="border-radius: 8px;">
                                    <button type="button" class="btn-q btn-minus-scan px-2 py-1" data-key="${vKey}">-</button>
                                    <input type="number" 
                                        class="box-qty-scan-input font-weight-bold" 
                                        data-key="${vKey}"
                                        data-size-set="${ss.size_set_id}"
                                        data-col-index="${c}"
                                        min="0"
                                        max="${colorObj.available_boxes}"
                                        value="${currentQty}"
                                        style="width: 38px; border: 0; background: transparent; text-align: center; font-size: 13px;">
                                    <button type="button" class="btn-q btn-plus-scan px-2 py-1" data-key="${vKey}">+</button>
                                </div>
                            </div>
                            <div class="text-muted text-center mt-1" style="font-size: 10px; line-height: 1;">${colorObj.available_boxes} avl</div>
                        </td>
                    `;
                }
            }

            html += `</tr>`;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        list.append(html);
        if (heroFilename && window.SnapKidOfflineDB) {
            SnapKidOfflineDB.getImageBlobUrl(heroFilename).then(blob => {
                if (blob) {
                    const heroImg = document.getElementById('scanHeroImage');
                    const heroPh = document.getElementById('scanHeroPlaceholder');
                    if (heroImg) {
                        heroImg.src = blob;
                        heroImg.dataset.src = blob;
                        heroImg.style.display = '';
                        if (heroPh) heroPh.style.display = 'none';
                    }
                }
            }).catch(() => {});
        }
        $('#offlineScanSelectionModal').modal('show');
    }

    // Modal Stepper Listeners
    $(document).on('click', '.btn-plus-master', function () {
        let input = $('#masterQtyInput');
        let val = parseInt(input.val()) || 0;
        input.val(val + 1).trigger('change');
    });

    $(document).on('click', '.btn-minus-master', function () {
        let input = $('#masterQtyInput');
        let val = parseInt(input.val()) || 0;
        if (val > 0) input.val(val - 1).trigger('change');
    });

    $(document).on('change', '#masterQtyInput', function () {
        let masterQty = parseInt($(this).val()) || 0;
        if (masterQty < 0) { masterQty = 0; $(this).val(0); }

        $('.matrix-table .ss-batch-qty-input:not(:disabled)').val(masterQty);
        $('.matrix-table .col-batch-qty-input:not(:disabled)').val(masterQty);

        $('.matrix-table .box-qty-scan-input:not(:disabled)').each(function () {
            const max = parseInt($(this).attr('max'));
            let targetQty = masterQty;
            if (!isNaN(max) && targetQty > max) targetQty = max;
            const key = $(this).data('key');
            $(this).val(targetQty);
            updateCartItem(key, targetQty);
        });
    });

    $(document).on('click', '.btn-plus-ss', function () {
        let input = $(this).siblings('.ss-batch-qty-input');
        if (input.prop('disabled')) return;
        let val = parseInt(input.val()) || 0;
        input.val(val + 1).trigger('change');
    });

    $(document).on('click', '.btn-minus-ss', function () {
        let input = $(this).siblings('.ss-batch-qty-input');
        if (input.prop('disabled')) return;
        let val = parseInt(input.val()) || 0;
        if (val > 0) input.val(val - 1).trigger('change');
    });

    $(document).on('change', '.ss-batch-qty-input', function () {
        const ssId = $(this).data('size-set');
        let rowQty = parseInt($(this).val()) || 0;
        if (rowQty < 0) { rowQty = 0; $(this).val(0); }

        $(`.matrix-row-${ssId} .box-qty-scan-input:not(:disabled)`).each(function () {
            const max = parseInt($(this).attr('max'));
            let targetQty = rowQty;
            if (!isNaN(max) && targetQty > max) targetQty = max;
            const key = $(this).data('key');
            $(this).val(targetQty);
            updateCartItem(key, targetQty);
        });
    });

    $(document).on('click', '.btn-plus-col', function () {
        let input = $(this).siblings('.col-batch-qty-input');
        if (input.prop('disabled')) return;
        let val = parseInt(input.val()) || 0;
        input.val(val + 1).trigger('change');
    });

    $(document).on('click', '.btn-minus-col', function () {
        let input = $(this).siblings('.col-batch-qty-input');
        if (input.prop('disabled')) return;
        let val = parseInt(input.val()) || 0;
        if (val > 0) input.val(val - 1).trigger('change');
    });

    $(document).on('change', '.col-batch-qty-input', function () {
        const colIndex = $(this).data('col-index');
        let colQty = parseInt($(this).val()) || 0;
        if (colQty < 0) { colQty = 0; $(this).val(0); }

        $(`.matrix-cell-col-${colIndex} .box-qty-scan-input:not(:disabled)`).each(function () {
            const max = parseInt($(this).attr('max'));
            let targetQty = colQty;
            if (!isNaN(max) && targetQty > max) targetQty = max;
            const key = $(this).data('key');
            $(this).val(targetQty);
            updateCartItem(key, targetQty);
        });
    });

    // Cell +/- Handlers
    $(document).on('click', '.btn-plus-scan', function () {
        const input = $(this).siblings('.box-qty-scan-input');
        if (input.prop('disabled')) return;
        const key = $(this).data('key');
        let val = parseInt(input.val()) || 0;
        const max = parseInt(input.attr('max'));
        if (!isNaN(max) && val >= max) return;
        input.val(val + 1);
        updateCartItem(key, val + 1);
    });

    $(document).on('click', '.btn-minus-scan', function () {
        const input = $(this).siblings('.box-qty-scan-input');
        if (input.prop('disabled')) return;
        const key = $(this).data('key');
        let val = parseInt(input.val()) || 0;
        if (val > 0) {
            input.val(val - 1);
            updateCartItem(key, val - 1);
        }
    });

    $(document).on('change', '.box-qty-scan-input', function () {
        const key = $(this).data('key');
        let val = parseInt($(this).val()) || 0;
        const max = parseInt($(this).attr('max'));
        if (val < 0) val = 0;
        if (!isNaN(max) && val > max) val = max;
        $(this).val(val);
        updateCartItem(key, val);
    });

    // Toggle Size Set Row Selection
    $(document).on('change', '.toggle-ss-checkbox', function () {
        const ssId = $(this).data('size-set');
        const isChecked = $(this).is(':checked');
        const row = $(this).closest('tr');

        if (!isChecked) {
            row.addClass('matrix-row-deselected');
            row.find('.ss-batch-qty-input').prop('disabled', true).val(0);
            row.find('.btn-minus-ss, .btn-plus-ss').prop('disabled', true);
            row.find('.box-qty-scan-input').each(function () {
                $(this).prop('disabled', true);
                const key = $(this).data('key');
                $(this).val(0);
                updateCartItem(key, 0);
            });
            row.find('.btn-minus-scan, .btn-plus-scan').prop('disabled', true);
        } else {
            row.removeClass('matrix-row-deselected');
            row.find('.ss-batch-qty-input').prop('disabled', false);
            row.find('.btn-minus-ss, .btn-plus-ss').prop('disabled', false);
            row.find('.matrix-cell').each(function () {
                const colIndex = $(this).data('col-index');
                const isColChecked = $(`.toggle-col-checkbox[data-col-index="${colIndex}"]`).is(':checked');
                if (isColChecked) {
                    $(this).find('.box-qty-scan-input').prop('disabled', false);
                    $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', false);
                }
            });
        }
    });

    // Toggle Color Column Selection
    $(document).on('change', '.toggle-col-checkbox', function () {
        const colIndex = $(this).data('col-index');
        const isChecked = $(this).is(':checked');
        const th = $(`.matrix-col-${colIndex}`);

        if (!isChecked) {
            th.addClass('matrix-col-deselected');
            th.find('.col-batch-qty-input').prop('disabled', true).val(0);
            th.find('.btn-minus-col, .btn-plus-col').prop('disabled', true);
            $(`.matrix-cell-col-${colIndex}`).each(function () {
                $(this).addClass('cell-disabled');
                const input = $(this).find('.box-qty-scan-input');
                input.prop('disabled', true);
                const key = input.data('key');
                input.val(0);
                updateCartItem(key, 0);
                $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', true);
            });
        } else {
            th.removeClass('matrix-col-deselected');
            th.find('.col-batch-qty-input').prop('disabled', false);
            th.find('.btn-minus-col, .btn-plus-col').prop('disabled', false);
            $(`.matrix-cell-col-${colIndex}`).each(function () {
                const ssId = $(this).data('size-set');
                const isRowChecked = $(`#toggle_ss_${ssId}`).is(':checked');
                if (isRowChecked) {
                    $(this).removeClass('cell-disabled');
                    $(this).find('.box-qty-scan-input').prop('disabled', false);
                    $(this).find('.btn-minus-scan, .btn-plus-scan').prop('disabled', false);
                }
            });
        }
    });

    // 9. Save Offline Order
    async function saveCurrentOrder() {
        const shopId = $('#offlineShopSelect').val();
        if (!shopId) {
            Swal.fire('Shop Required', 'Please select a shop before saving order.', 'warning');
            return;
        }

        if (cart.size === 0) {
            Swal.fire('Empty Cart', 'Please add at least one product box.', 'warning');
            return;
        }

        const shopObj = $('#offlineShopSelect').find('option:selected').data('shop');
        let variations = [];
        let subtotal = 0;
        let totalBoxes = 0;

        cart.forEach((val) => {
            const p = val.item;
            const pcs = val.qty * (p.pcs_per_box || 1);
            const lineTotal = pcs * p.unit_price;
            subtotal += lineTotal;
            totalBoxes += val.qty;

            variations.push({
                product_id: p.product_id,
                color_id: p.color_id,
                size_set_id: p.size_set_id,
                qty: val.qty,
                pcs_per_box: p.pcs_per_box,
                unit_price: p.unit_price,
                design_number: p.design_number,
                color_name: p.color_name,
                size_set_name: p.size_set_name
            });
        });

        const discount = parseFloat($('#offlineDiscount').val()) || 0;
        const otherCharges = parseFloat($('#offlineOtherCharges').val()) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const gst = Math.round(taxable * 0.05);
        const grandTotal = Math.round(taxable + gst + otherCharges);

        const orderData = {
            shop_id: shopId,
            shop_name: shopObj ? shopObj.name : 'Shop #' + shopId,
            variations: variations,
            total_boxes: totalBoxes,
            subtotal: subtotal,
            discount_amount: discount,
            other_charges: otherCharges,
            gst_amount: gst,
            gst_percentage: 5.00,
            grand_total: grandTotal,
            remark: $('#offlineRemark').val() || '',
            order_date: new Date().toISOString().split('T')[0]
        };

        try {
            await SnapKidOfflineDB.saveOfflineOrder(orderData);
            $('#cartDetailsModal').modal('hide');

            const seePrice = canSeePrice();
            const orderDesc = seePrice 
                ? `Order for ${orderData.shop_name} (${totalBoxes} boxes, ₹${grandTotal.toLocaleString()}) is recorded on your phone.`
                : `Order for ${orderData.shop_name} (${totalBoxes} boxes) is recorded on your phone.`;

            Swal.fire({
                icon: 'success',
                title: 'Order Saved Offline!',
                text: orderDesc,
                confirmButtonText: 'Go to Offline Hub',
                confirmButtonColor: '#16a34a'
            }).then(() => {
                window.location.href = "{{ route('agent.offline.index') }}";
            });
        } catch (e) {
            Swal.fire('Error', 'Failed to save order: ' + e.message, 'error');
        }
    }

    $('#btnSaveOrderOffline, #btnModalSaveOrder').click(saveCurrentOrder);
});
</script>
@endpush
