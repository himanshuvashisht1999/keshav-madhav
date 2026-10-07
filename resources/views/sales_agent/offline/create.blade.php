@extends('sales_agent.layouts.app', ['title' => 'Offline Order'])

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
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" id="btnOpenScanner">
                <i class="fas fa-qrcode mr-1"></i> Scan Tag
            </button>
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
                <h5 class="mb-0 font-weight-bold text-success mt-1">₹<span id="grandTotalText">0</span></h5>
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
                <div class="row no-gutters mb-2">
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
@endsection

@push('scripts')
<script src="{{ asset('assets/js/html5-qrcode.min.js') }}"></script>
<script>
$(document).ready(function () {
    let allProducts = [];
    let displayedProducts = [];
    let cart = new Map();
    let currentPage = 1;
    const pageSize = 24;
    let scanner = null;
    let metaInfo = null;
    let activeModalKey = null;

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
        const seePrice = !metaInfo || metaInfo.see_price !== false;

        let html = '';
        toRender.forEach(prod => {
            const inCartQty = cart.has(prod.key) ? cart.get(prod.key).qty : 0;
            const imgSrc = prod.image_url || '';

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
                                <img src="${imgSrc}" class="w-100 h-100 zoom-image-trigger" data-src="${imgSrc}" style="object-fit: contain; cursor: pointer;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="w-100 h-100 align-items-center justify-content-center text-muted" style="display:none;">
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
                                    ` : `
                                        <div class="text-right">
                                            <small class="text-muted font-weight-bold" style="font-size: 10px;">Price Hidden</small>
                                        </div>
                                    `}
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
        let bodyHtml = `
            <div class="row align-items-center mb-3">
                <div class="col-4 text-center">
                    <div style="height: 120px; background: #f8fafc; border-radius: 12px; overflow: hidden;" class="d-flex align-items-center justify-content-center border">
                        ${imgSrc ? `
                            <img src="${imgSrc}" class="w-100 h-100 zoom-image-trigger" data-src="${imgSrc}" style="object-fit: contain; cursor: pointer;">
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
                    </tbody>
                </table>
            </div>
        `;

        $('#modalProdBody').html(bodyHtml);
        $('#productDetailsModal').modal('show');
    });

    // 5. Image Zoom Handler
    $(document).on('click', '.zoom-image-trigger', function (e) {
        e.stopPropagation();
        const src = $(this).data('src') || $(this).attr('src');
        if (!src) return;
        $('#zoomedImage').attr('src', src);
        $('#imageZoomModal').modal('show');
    });

    // 6. Update Bottom Summary Bar
    function updateSummaryUI() {
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

        $('#totalBoxesCount').text(totalBoxes + ' Boxes');
        $('#cartItemsCount').text(itemsCount);
        $('#grandTotalText').text(grandTotal.toLocaleString());

        if (totalBoxes > 0) {
            $('#offlineSummaryBar').slideDown();
        } else {
            $('#offlineSummaryBar').slideUp();
        }
    }

    $('#offlineDiscount, #offlineOtherCharges').on('input', updateSummaryUI);

    // 7. Cart Details Modal Rendering
    $('#btnOpenCartModal').click(function () {
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
                        <div class="font-weight-bold text-primary">₹${Math.round(itemTotal).toLocaleString()}</div>
                        <button type="button" class="btn btn-xs text-danger btn-remove-cart" data-key="${key}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        list.html(html);
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
            const config = { fps: 10, qrbox: { width: 220, height: 220 } };
            scanner.start({ facingMode: "environment" }, config, onBarcodeScanned)
                .catch(err => {
                    console.error('Camera error:', err);
                });
        } catch (e) {
            console.error(e);
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

    async function processBarcodeMatch(code) {
        const match = await SnapKidOfflineDB.findVariationByBarcode(code);
        if (match) {
            const current = cart.has(match.key) ? cart.get(match.key).qty : 0;
            updateCartItem(match.key, current + 1);

            Swal.fire({
                icon: 'success',
                title: 'Item Added!',
                text: `#${match.design_number} (${match.color_name} - ${match.size_set_name}) added to cart. Total: ${current + 1} Boxes.`,
                timer: 1600,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Barcode Not Found',
                text: `No matching product found for code: "${code}" in offline catalog.`,
                confirmButtonText: 'OK'
            });
        }
    }

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

            Swal.fire({
                icon: 'success',
                title: 'Order Saved Offline!',
                text: `Order for ${orderData.shop_name} (${totalBoxes} boxes, ₹${grandTotal.toLocaleString()}) is recorded on your phone.`,
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
