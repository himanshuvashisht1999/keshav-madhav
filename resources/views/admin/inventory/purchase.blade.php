@extends('admin.layouts.app')

@section('content')

    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <!-- ERP HEADER BAR -->
            <div class="erp-header-bar mb-2">
                <div>
                    <h1 class="erp-page-title m-0">Purchase & Inward Entry</h1>
                    <p class="erp-page-subtitle m-0">Add vendor purchases, inward physical boxes and update stock inventory</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-history mr-1"></i> Purchase history
                    </a>
                    <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to inventory
                    </a>
                </div>
            </div>
                <form action="{{ route('admin.inventory.store') }}" method="POST" id="addStockForm">
                    @csrf

<style>
/* =========================================================
   PURCHASE & INWARD ENTRY - MODERN ERP DESIGN ENHANCEMENTS
   ========================================================= */
.purchase-hud-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 14px;
    margin-bottom: 10px;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
    gap: 12px;
}

.purchase-kpi-container {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.purchase-kpi-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 14px;
    flex: 1;
    min-width: 160px;
    max-width: 260px;
    transition: all 0.15s ease;
}

.purchase-kpi-item:hover {
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.kpi-item-boxes {
    border-left: 3.5px solid #0284c7;
}

.kpi-item-pieces {
    border-left: 3.5px solid #16a34a;
}

.kpi-item-subtotal {
    border-left: 3.5px solid #f59e0b;
}

.kpi-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.bg-primary-soft { background: #e0f2fe; }
.bg-success-soft { background: #dcfce7; }
.bg-warning-soft { background: #fef3c7; }

.kpi-details {
    display: flex;
    flex-direction: column;
}

.kpi-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #64748b;
    line-height: 1.2;
    margin-bottom: 2px;
}

.kpi-value {
    display: flex;
    align-items: baseline;
    gap: 5px;
    line-height: 1.2;
}

.kpi-number {
    font-size: 16px;
    font-weight: 800;
    line-height: 1;
}

.kpi-suffix {
    font-size: 11px;
    color: #64748b;
    font-weight: 600;
}

.purchase-hud-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.btn-hud-upload {
    background: #05421c !important;
    color: #ffffff !important;
    border: 1px solid #043315 !important;
    font-size: 12.5px !important;
    font-weight: 700 !important;
    padding: 6px 16px !important;
    border-radius: 5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 2px 4px rgba(5, 66, 28, 0.2) !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
    height: 34px !important;
}

.btn-hud-upload:hover {
    background: #075a27 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(5, 66, 28, 0.25) !important;
    color: #fcee21 !important;
}

/* 2. SECTION 2: ADD PRODUCT ITEM GRID */
.product-entry-grid {
    display: grid;
    grid-template-columns: 1.4fr 0.9fr 2.4fr 1fr 1.3fr 0.95fr 1.05fr 0.95fr;
    gap: 8px;
    align-items: end;
}

.product-entry-grid .erp-label {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #334155 !important;
    margin-bottom: 3px !important;
    white-space: nowrap !important;
}

.product-entry-grid .select2-container {
    width: 100% !important;
}

.product-entry-grid .erp-input {
    width: 100% !important;
    height: 33px !important;
}

.btn-add-item {
    height: 33px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    background: #05421c !important;
    color: #ffffff !important;
    border: 1px solid #043315 !important;
    border-radius: 4px !important;
    transition: all 0.15s ease !important;
    white-space: nowrap !important;
    box-shadow: 0 1px 3px rgba(5, 66, 28, 0.2) !important;
    cursor: pointer !important;
}

.btn-add-item:hover {
    background: #075a27 !important;
    color: #fcee21 !important;
    border-color: #05421c !important;
}

@media (max-width: 1024px) {
    .product-entry-grid {
        grid-template-columns: repeat(6, 1fr);
    }
    .entry-col-warehouse { grid-column: span 2; }
    .entry-col-rack { grid-column: span 1; }
    .entry-col-design { grid-column: span 3; }
    .entry-col-sizeset { grid-column: span 2; }
    .entry-col-color { grid-column: span 2; }
    .entry-col-boxes { grid-column: span 1; }
    .entry-col-rate { grid-column: span 1; }
    .entry-col-btn { grid-column: span 2; }
}

@media (max-width: 576px) {
    .product-entry-grid {
        grid-template-columns: 1fr 1fr;
    }
    .entry-col-warehouse,
    .entry-col-design,
    .entry-col-btn { grid-column: span 2; }
    .entry-col-rack,
    .entry-col-sizeset,
    .entry-col-color,
    .entry-col-boxes,
    .entry-col-rate { grid-column: span 1; }
}
</style>

                    <!-- ========================================================= -->
                    <!-- 1. MODERN ERP TOP HUD BANNER -->
                    <!-- ========================================================= -->
                    <div class="purchase-hud-banner">
                        <div class="purchase-kpi-container">
                            <!-- Total Boxes Stat Box -->
                            <div class="purchase-kpi-item kpi-item-boxes">
                                <div class="kpi-icon-wrap bg-primary-soft">
                                    <i class="fas fa-boxes text-primary"></i>
                                </div>
                                <div class="kpi-details">
                                    <span class="kpi-title">Total Boxes</span>
                                    <div class="kpi-value">
                                        <span id="hudTotalBoxes" class="kpi-number text-primary">0</span>
                                        <span class="kpi-suffix">Boxes</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Pieces Stat Box -->
                            <div class="purchase-kpi-item kpi-item-pieces">
                                <div class="kpi-icon-wrap bg-success-soft">
                                    <i class="fas fa-tshirt text-success"></i>
                                </div>
                                <div class="kpi-details">
                                    <span class="kpi-title">Total Pieces</span>
                                    <div class="kpi-value">
                                        <span id="hudTotalPieces" class="kpi-number text-success">0</span>
                                        <span class="kpi-suffix">Pieces</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub Total Stat Box -->
                            <div class="purchase-kpi-item kpi-item-subtotal">
                                <div class="kpi-icon-wrap bg-warning-soft">
                                    <i class="fas fa-receipt text-warning"></i>
                                </div>
                                <div class="kpi-details">
                                    <span class="kpi-title">Sub Total</span>
                                    <div class="kpi-value">
                                        <span id="hudSubTotal" class="kpi-number text-dark">₹0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions in HUD -->
                        <div class="purchase-hud-actions">
                            <button type="button" class="btn-hud-upload btn-open-summary" id="btnHudOpenSummary">
                                <i class="fas fa-file-invoice-dollar mr-1"></i> Review & upload
                            </button>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 2. SECTION 1: SOURCE & INVOICE DETAILS (ERP CARD) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-primary">
                                <i class="fas fa-file-invoice"></i> 1. Source & invoice details
                            </div>
                        </div>
                        <div class="p-2">
                            <div class="form-row align-items-end">
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Source Type *</label>
                                    <select name="source_type" id="sourceType" class="erp-input select2">
                                        <option value="vendor">Vendor</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label text-primary">Production PO (Optional)</label>
                                    <select name="production_po_id" id="loadProductionPO" class="erp-input select2">
                                        <option value="">-- Optional: Select PO --</option>
                                        @foreach($productionPOs as $po)
                                            <option value="{{ $po->id }}">{{ $po->po_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1" id="vendorContainer">
                                    <label class="erp-label">Vendor *</label>
                                    <select name="vendor_id" id="vendorSelect" class="erp-input select2">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->company_name ?? $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1" id="customerContainer" style="display: none;">
                                    <label class="erp-label">Customer *</label>
                                    <select name="customer_id" id="customerSelect" class="erp-input select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Bill / Inv No.</label>
                                    <input type="text" name="bill_no" class="erp-input" placeholder="Enter Bill No." value="{{ old('bill_no') }}">
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Purchase Date *</label>
                                    <input type="date" name="purchase_date" class="erp-input" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PO Reference Panel (When PO selected) -->
                    <div id="poReferenceContainer" class="erp-card border border-primary mb-2" style="display: none;">
                        <div class="erp-card-header bg-light">
                            <div class="erp-card-title text-primary" style="font-size: 11.5px;">
                                <i class="fas fa-list-ul"></i> PO Items Reference (<span id="poRefNumber">PO #</span>)
                            </div>
                            <span class="badge badge-primary" style="font-size: 10px;">Click 'Select' to use an item</span>
                        </div>
                        <div class="table-responsive">
                            <table class="erp-table table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Design No</th>
                                        <th>Pattern / Fitting</th>
                                        <th>Size / Color</th>
                                        <th class="text-center">PO Quantity</th>
                                        <th class="text-right">PO Rate</th>
                                        <th class="text-center" style="width: 70px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="poRefBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 3. SECTION 2: ADD PRODUCT ITEM (CLEAN GRID NO OVERLAP) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-success">
                                <i class="fas fa-cart-plus"></i> 2. Add product item
                            </div>
                            <span class="badge badge-light border text-muted font-weight-normal" style="font-size: 11px;">
                                Fill item specifications & click <strong>+ Add</strong>
                            </span>
                        </div>
                        <div class="p-2">
                            <div class="product-entry-grid">
                                <div class="entry-col entry-col-warehouse">
                                    <label class="erp-label">Warehouse *</label>
                                    <select id="headerWarehouse" class="erp-input select2">
                                        <option value="">Warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="entry-col entry-col-rack">
                                    <label class="erp-label">Rack *</label>
                                    <select id="headerRack" class="erp-input select2">
                                        <option value="">Rack</option>
                                    </select>
                                </div>
                                <div class="entry-col entry-col-design">
                                    <label class="erp-label">Design *</label>
                                    <select id="headerDesign" class="erp-input select2">
                                        <option value="">Select design</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">
                                                {{ $product->design_number }} ({{ $product->series->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="entry-col entry-col-sizeset">
                                    <label class="erp-label">Size set *</label>
                                    <select id="headerSizeSet" class="erp-input select2">
                                        <option value="">Size</option>
                                    </select>
                                </div>
                                <div class="entry-col entry-col-color">
                                    <label class="erp-label">Color *</label>
                                    <select id="headerColor" class="erp-input select2">
                                        <option value="">Color</option>
                                    </select>
                                </div>
                                <div class="entry-col entry-col-boxes">
                                    <label class="erp-label">Boxes *</label>
                                    <input type="number" id="headerTotalBoxes" class="erp-input text-center font-weight-bold" min="1" placeholder="Qty">
                                </div>
                                <div class="entry-col entry-col-rate">
                                    <label class="erp-label">Rate (₹) *</label>
                                    <input type="number" id="headerPurchaseRate" class="erp-input text-right font-weight-bold" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="entry-col entry-col-btn">
                                    <label class="erp-label d-none d-lg-block">&nbsp;</label>
                                    <button type="button" id="btnAddToList" class="btn btn-block btn-add-item">
                                        <i class="fas fa-plus mr-1"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields for specs -->
                        <input type="hidden" id="headerPattern">
                        <input type="hidden" id="headerFitting">
                        <input type="hidden" id="headerPatternDisplay">
                        <input type="hidden" id="headerFittingDisplay">
                        <input type="hidden" id="headerPcsPerBox" value="1">
                        <input type="hidden" id="headerMRP" value="0">
                    </div>

                    <!-- ========================================================= -->
                    <!-- 4. SECTION 3: ADDED PRODUCTS LIST (ERP TABLE) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-dark">
                                <i class="fas fa-boxes"></i> 3. Added products list
                                <span class="badge badge-light border text-muted ml-2 font-weight-normal" style="font-size:11px;">
                                    <strong id="tableItemCount">0</strong> items added
                                </span>
                            </div>
                            <div id="tableCountBadges" style="display: none;">
                                <span class="badge badge-primary px-2 py-1 mr-1" style="font-size: 11px;">
                                    <strong id="tableHeaderTotalBoxes">0</strong> Boxes
                                </span>
                                <span class="badge badge-success px-2 py-1" style="font-size: 11px;">
                                    <strong id="tableHeaderTotalPieces">0</strong> Pieces
                                </span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="erp-table table table-sm table-hover mb-0" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th style="width: 3%; text-align: center;">#</th>
                                        <th style="width: 22%;">Design no</th>
                                        <th style="width: 14%;">Pattern / fit</th>
                                        <th style="width: 15%;">Warehouse / rack</th>
                                        <th style="width: 13%;">Size / color</th>
                                        <th style="width: 7%; text-align: center;">Boxes</th>
                                        <th style="width: 6%; text-align: center;">Pcs/bx</th>
                                        <th style="width: 7%; text-align: right;">MRP</th>
                                        <th style="width: 7%; text-align: right;">Rate (₹)</th>
                                        <th style="width: 8%; text-align: right;">Total (₹)</th>
                                        <th style="width: 4%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <tr id="emptyState">
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-basket mr-1 fa-lg text-secondary opacity-50"></i> No products added yet. Select options above and click <strong>+ Add</strong>.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 5. STICKY BOTTOM ACTIONS BAR (MATCHES CONSUME PAGE) -->
                    <!-- ========================================================= -->
                    <div class="sticky-bottom-bar">
                        <div>
                            <a href="{{ route('admin.inventory.index') }}" class="text-secondary font-weight-bold mr-3" style="font-size:12px;">
                                <i class="fas fa-arrow-left mr-1"></i> Cancel and Exit
                            </a>
                            <span class="text-muted small">All stock entries are validated in real time before submission.</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="mr-4 text-right">
                                <span class="text-muted small mr-2 font-weight-bold">
                                    Boxes: <strong class="text-primary" id="footerTotalBoxes">0</strong>
                                </span>
                                <span class="text-muted small mr-2 font-weight-bold">|</span>
                                <span class="text-muted small mr-2 font-weight-bold">
                                    Pieces: <strong class="text-success" id="footerTotalPieces">0</strong>
                                </span>
                                <span class="text-muted small mr-2 font-weight-bold">|</span>
                                <span class="text-muted small font-weight-bold">
                                    Sub Total: <strong class="text-dark" id="footerSubTotal">₹0.00</strong>
                                </span>
                            </div>
                            <button type="button" class="btn-hud-confirm btn-open-summary" id="btnBottomOpenSummary">
                                <i class="fas fa-check-double mr-1"></i> Review Summary & Upload
                            </button>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 6. INVOICE SUMMARY MODAL (MATCHES ERP DESIGN) -->
                    <!-- ========================================================= -->
                    <div class="modal fade" id="purchaseSummaryModal" tabindex="-1" role="dialog" aria-labelledby="summaryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
                            <div class="modal-content shadow-lg border-0" style="border-radius: 10px; overflow: hidden;">
                                <div class="modal-header bg-light py-2 px-3 border-bottom">
                                    <h6 class="modal-title font-weight-bold text-dark mb-0" id="summaryModalLabel">
                                        <i class="fas fa-receipt text-primary mr-1"></i> Purchase Order Invoice Summary
                                    </h6>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body p-3 bg-light">
                                    <!-- 3 Top Metric Cards -->
                                    <div class="row no-gutters bg-white border rounded p-2 mb-3 text-center shadow-sm">
                                        <div class="col-4 border-right">
                                            <span class="hud-label text-primary">Total Boxes</span>
                                            <div class="h5 mb-0 font-weight-bold text-primary" id="modalTotalBoxes">0</div>
                                            <input type="hidden" id="global_total_boxes" value="0">
                                        </div>
                                        <div class="col-4 border-right">
                                            <span class="hud-label text-success">Total Pieces</span>
                                            <div class="h5 mb-0 font-weight-bold text-success" id="modalTotalPieces">0</div>
                                            <input type="hidden" id="global_total_pieces" value="0">
                                        </div>
                                        <div class="col-4">
                                            <span class="hud-label text-warning">Sub Total</span>
                                            <div class="h5 mb-0 font-weight-bold text-dark" id="modalSubTotalText">₹0.00</div>
                                            <input type="hidden" name="sub_total" id="global_sub_total" value="0.00">
                                        </div>
                                    </div>

                                    <!-- Financial Calculation Box -->
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <span class="font-weight-bold text-muted small text-uppercase">Sub Total</span>
                                                <span class="font-weight-bold text-dark" id="modalSubTotalRow">₹0.00</span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block">GST Tax</span>
                                                    <small class="text-muted" style="font-size:11px;">Percentage (%) or Flat (₹)</small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="input-group input-group-sm" style="width: 165px;">
                                                        <input type="number" name="gst_value" id="global_gst_value" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0">
                                                        <div class="input-group-append">
                                                            <select name="gst_type" id="global_gst_type" class="custom-select custom-select-sm bg-light" style="width: 65px;">
                                                                <option value="percentage">%</option>
                                                                <option value="amount">₹</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted font-weight-bold d-block mt-1" id="modalGstCalcText">+ ₹0.00</small>
                                                    <input type="hidden" name="gst" id="global_gst_amount" value="0.00">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block">Other Charges (+)</span>
                                                    <small class="text-muted" style="font-size:11px;">Freight, loading, etc.</small>
                                                </div>
                                                <div class="input-group input-group-sm" style="width: 165px;">
                                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">₹</span></div>
                                                    <input type="number" name="other_amount" id="global_other_amount" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block">Discount (-)</span>
                                                    <small class="text-muted" style="font-size:11px;">Vendor concession</small>
                                                </div>
                                                <div class="input-group input-group-sm" style="width: 165px;">
                                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">₹</span></div>
                                                    <input type="number" name="discount" id="global_discount" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2">
                                                <div style="min-width: 130px;">
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block"><i class="fas fa-comment-alt text-secondary mr-1"></i> Remarks</span>
                                                    <small class="text-muted" style="font-size:11px;">Order note or remarks</small>
                                                </div>
                                                <div class="ml-3 flex-grow-1" style="max-width: 250px;">
                                                    <input type="text" name="remarks" id="global_remarks" class="form-control form-control-sm" placeholder="Enter remarks (optional)..." maxlength="500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grand Total Banner -->
                                    <div class="d-flex justify-content-between align-items-center p-3 rounded shadow-sm" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                                        <div>
                                            <span class="hud-label text-white-50">Net Payable Amount</span>
                                            <h6 class="mb-0 text-white font-weight-bold">Grand Total</h6>
                                        </div>
                                        <div class="text-right">
                                            <h3 class="mb-0 font-weight-bold text-warning" id="displayGrandTotal">₹0.00</h3>
                                            <input type="hidden" name="total_amount" id="global_total_amount" value="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-white py-2 px-3 border-top d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 font-weight-bold" data-dismiss="modal">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Edit
                                    </button>
                                    <button type="submit" class="btn-hud-confirm" id="btnConfirmSubmit">
                                        <i class="fas fa-check-circle mr-1"></i> Confirm & Upload Stock
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>

    @section('scripts')
        <script>
            $(function () {
                let itemCount = 0;
                let currentVariants = [];
                let currentPOItems = [];

                function initSelect2(container) {
                    container.find('.select2').each(function () {
                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownAutoWidth: true
                        });
                    });
                }

                initSelect2($('.content-wrapper'));

                // Handle Source Type
                $('#sourceType').on('change', function () {
                    let type = $(this).val();
                    if (type === 'vendor') {
                        $('#vendorContainer').show();
                        $('#customerContainer').hide();
                    } else if (type === 'customer') {
                        $('#vendorContainer').hide();
                        $('#customerContainer').show();
                    }
                }).trigger('change');

                // Header Warehouse -> Load Racks
                $('#headerWarehouse').on('change', function () {
                    let warehouseId = $(this).val();
                    let rackSelect = $('#headerRack');
                    rackSelect.empty().append('<option value="">Rack</option>');
                    if (warehouseId) {
                        $.get("{{ url('admin/inventory/warehouse-stock/racks') }}/" + warehouseId, function (data) {
                            data.forEach(function (rack) {
                                rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                            });
                            rackSelect.trigger('change.select2');
                            if (data.length > 0) {
                                rackSelect.val(data[0].id).trigger('change.select2');
                            }
                        });
                    }
                });

                // Header Design -> Load Pattern/Fitting/Variants
                $('#headerDesign').on('change', function () {
                    let productId = $(this).val();
                    let sizeSelect = $('#headerSizeSet');
                    let colorSelect = $('#headerColor');

                    // Reset fields
                    $('#headerPatternDisplay').val('');
                    $('#headerPattern').val('');
                    $('#headerFittingDisplay').val('');
                    $('#headerFitting').val('');

                    sizeSelect.empty().append('<option value="">Size</option>').trigger('change.select2');
                    colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                    $('#headerPcsPerBox').val('1');
                    $('#headerMRP').val('0');
                    currentVariants = [];

                    if (productId) {
                        $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                            if (data.success) {
                                $('#headerPatternDisplay').val(data.pattern_name);
                                $('#headerPattern').val(data.pattern_id);
                                $('#headerFittingDisplay').val(data.fitting_name);
                                $('#headerFitting').val(data.fitting_id);
                                currentVariants = data.variants;

                                data.variants.forEach(function (v) {
                                    sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                });
                                sizeSelect.trigger('change.select2');
                            }
                        });
                    }
                });

                // Header Size Set -> Load Colors, Pcs/Box, MRP
                $('#headerSizeSet').on('change', function () {
                    let sizeSetId = $(this).val();
                    let colorSelect = $('#headerColor');
                    colorSelect.empty().append('<option value="">Color</option>').trigger('change.select2');
                    $('#headerPcsPerBox').val('1');
                    $('#headerMRP').val('0');

                    if (sizeSetId) {
                        $.get("{{ url('admin/inventory/get-size-set-info') }}/" + sizeSetId, function (data) {
                            $('#headerPcsPerBox').val(data.no_of_pcs);
                        });

                        let variant = currentVariants.find(v => v.size_set_id == sizeSetId);
                        if (variant) {
                            $('#headerMRP').val(variant.mrp);
                            variant.colors.forEach(function (c) {
                                colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            colorSelect.trigger('change.select2');

                            // Auto-fill Quantity and Rate from PO if match found
                            if (currentPOItems.length > 0) {
                                const productId = $('#headerDesign').val();
                                const poMatch = currentPOItems.find(item => 
                                    item.product_id == productId && 
                                    item.size_set_id == sizeSetId
                                );
                                if (poMatch) {
                                    $('#headerTotalBoxes').val(poMatch.total_boxes);
                                    $('#headerPurchaseRate').val(poMatch.purchase_rate);
                                    
                                    const colorsInPOForThisSize = currentPOItems.filter(item => 
                                        item.product_id == productId && item.size_set_id == sizeSetId
                                    );
                                    if (colorsInPOForThisSize.length === 1) {
                                        colorSelect.val(colorsInPOForThisSize[0].color_id).trigger('change.select2');
                                    }
                                }
                            }
                        }
                    }
                });

                // Add To List Button
                $('#btnAddToList').on('click', function () {
                    const data = {
                        warehouse_id: $('#headerWarehouse').val(),
                        warehouse_name: $('#headerWarehouse option:selected').text(),
                        rack_id: $('#headerRack').val(),
                        rack_name: $('#headerRack option:selected').text(),
                        product_id: $('#headerDesign').val(),
                        product_name: $('#headerDesign option:selected').text(),
                        pattern_id: $('#headerPattern').val(),
                        pattern_name: $('#headerPatternDisplay').val() || '-',
                        fitting_id: $('#headerFitting').val(),
                        fitting_name: $('#headerFittingDisplay').val() || '-',
                        size_set_id: $('#headerSizeSet').val(),
                        size_set_name: $('#headerSizeSet option:selected').text(),
                        color_id: $('#headerColor').val(),
                        color_name: $('#headerColor option:selected').text(),
                        pieces_per_box: $('#headerPcsPerBox').val() || 1,
                        mrp: $('#headerMRP').val() || 0,
                        total_boxes: $('#headerTotalBoxes').val(),
                        purchase_rate: $('#headerPurchaseRate').val()
                    };

                    // Validation
                    if (!data.warehouse_id || !data.product_id || !data.size_set_id || !data.color_id || !data.total_boxes || !data.purchase_rate) {
                        toastr.warning('Please fill all required fields marked with *');
                        return;
                    }

                    addToTable(data);
                    toastr.success('Product added to list');

                    // Reset boxes quantity
                    $('#headerTotalBoxes').val('');
                });

                // Load from Production PO
                $('#loadProductionPO').on('change', function() {
                    const poId = $(this).val();
                    
                    if (!poId) {
                        $('#poReferenceContainer').hide();
                        currentPOItems = [];
                        return;
                    }

                    Swal.fire({
                        title: 'Loading PO Data...',
                        text: 'Filtering products based on Production PO.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: `{{ url('admin/inventory/get-po-items') }}/${poId}`,
                        type: 'GET',
                        success: function(response) {
                            Swal.close();
                            if (response.success) {
                                currentPOItems = response.items;

                                if (response.vendor_id) {
                                    $('#sourceType').val('vendor').trigger('change');
                                    $('#vendorSelect').val(response.vendor_id).trigger('change');
                                } else if (response.customer_id) {
                                    $('#sourceType').val('customer').trigger('change');
                                    $('#customerSelect').val(response.customer_id).trigger('change');
                                }

                                // Filter Reference Table
                                let refHtml = '';
                                
                                response.items.forEach(item => {
                                    refHtml += `
                                        <tr>
                                            <td class="font-weight-bold">${item.design_number}</td>
                                            <td>${item.pattern_name} / ${item.fitting_name}</td>
                                            <td><span class="erp-pill erp-pill-source">${item.size_set_name}</span> / ${item.color_name}</td>
                                            <td class="text-center font-weight-bold">${item.total_boxes} Bx</td>
                                            <td class="text-right font-weight-bold">₹${item.purchase_rate}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-xs px-2 py-0 btn-apply-po-item" 
                                                    data-product="${item.product_id}" 
                                                    data-size="${item.size_set_id}" 
                                                    data-color="${item.color_id}"
                                                    data-boxes="${item.total_boxes}"
                                                    data-rate="${item.purchase_rate}">
                                                    Select
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                });
                                
                                $('#poRefNumber').text($('#loadProductionPO option:selected').text());
                                $('#poRefBody').html(refHtml);
                                $('#poReferenceContainer').fadeIn();

                                toastr.success(`PO loaded: ${response.items.length} items available`);
                            }
                        },
                        error: function() {
                            Swal.close();
                            toastr.error('Failed to load PO data');
                        }
                    });
                });

                // Apply PO item to form
                $(document).on('click', '.btn-apply-po-item', function() {
                    const productId = $(this).data('product');
                    const sizeSetId = $(this).data('size');
                    const colorId = $(this).data('color');
                    const boxes = $(this).data('boxes');
                    const rate = $(this).data('rate');

                    $('#headerDesign').val(productId).trigger('change');
                    
                    // Wait for Size Set dropdown to populate
                    setTimeout(() => {
                        $('#headerSizeSet').val(sizeSetId).trigger('change');
                        setTimeout(() => {
                            $('#headerColor').val(colorId).trigger('change.select2');
                            $('#headerTotalBoxes').val(boxes);
                            $('#headerPurchaseRate').val(rate);
                            toastr.info('PO item applied to input line');
                        }, 500);
                    }, 800);
                });

                function addToTable(data) {
                    // Check for duplicates in UI
                    let exists = false;
                    $('#itemsContainer tr').not('#emptyState').each(function() {
                        const rowProductId = $(this).find('input[name*="[product_id]"]').val();
                        const rowColorId = $(this).find('input[name*="[color_id]"]').val();
                        const rowSizeSetId = $(this).find('input[name*="[size_set_id]"]').val();
                        const rowPatternId = $(this).find('input[name*="[pattern_id]"]').val();
                        const rowFittingId = $(this).find('input[name*="[fitting_id]"]').val();
                        const rowRackId = $(this).find('input[name*="[rack_id]"]').val();

                        if (rowProductId == data.product_id && 
                            rowColorId == data.color_id && 
                            rowSizeSetId == data.size_set_id &&
                            rowPatternId == data.pattern_id &&
                            rowFittingId == data.fitting_id &&
                            rowRackId == (data.rack_id || '')) {
                            exists = true;
                        }
                    });

                    if (exists) {
                        toastr.warning('Product item already in the list');
                        return;
                    }

                    const idx = itemCount++;
                    const rowNumber = $('#itemsContainer tr').not('#emptyState').length + 1;
                    const totalPcs = parseFloat(data.total_boxes) * parseFloat(data.pieces_per_box);
                    const total = (totalPcs * parseFloat(data.purchase_rate)).toFixed(2);

                    const rowHtml = `
                        <tr data-index="${idx}">
                            <td class="text-center font-weight-bold row-index text-muted" style="font-size:11px;">${rowNumber}</td>
                            <td>
                                <strong class="text-dark">${data.product_name}</strong>
                                <input type="hidden" name="products[${idx}][product_id]" value="${data.product_id}">
                            </td>
                            <td>
                                <span class="text-muted">${data.pattern_name} / ${data.fitting_name}</span>
                                <input type="hidden" name="products[${idx}][pattern_id]" value="${data.pattern_id}">
                                <input type="hidden" name="products[${idx}][fitting_id]" value="${data.fitting_id}">
                            </td>
                            <td>
                                <span class="text-muted">${data.warehouse_name || 'N/A'} / ${data.rack_name || 'N/A'}</span>
                                <input type="hidden" name="products[${idx}][warehouse_id]" value="${data.warehouse_id || ''}">
                                <input type="hidden" name="products[${idx}][rack_id]" value="${data.rack_id || ''}">
                            </td>
                            <td>
                                <span class="erp-pill erp-pill-source mr-1">${data.size_set_name}</span>
                                <span class="erp-pill erp-pill-gen">${data.color_name}</span>
                                <input type="hidden" name="products[${idx}][size_set_id]" value="${data.size_set_id}">
                                <input type="hidden" name="products[${idx}][color_id]" value="${data.color_id}">
                            </td>
                            <td class="text-center">
                                <input type="number" name="products[${idx}][total_boxes]" value="${data.total_boxes}" class="erp-input text-center row-boxes mx-auto font-weight-bold" min="1" style="max-width: 65px;">
                            </td>
                            <td class="text-center text-muted font-weight-bold" style="font-size:11px;">
                                ${data.pieces_per_box}
                                <input type="hidden" name="products[${idx}][pieces_per_box]" value="${data.pieces_per_box}" class="row-pcs">
                            </td>
                            <td class="text-right text-muted" style="font-size:11px;">
                                ₹${parseFloat(data.mrp || 0).toFixed(2)}
                                <input type="hidden" name="products[${idx}][mrp]" value="${data.mrp}">
                            </td>
                            <td class="text-right">
                                <input type="number" name="products[${idx}][purchase_rate]" value="${data.purchase_rate}" class="erp-input text-right row-rate mx-auto font-weight-bold" step="0.01" min="0" style="max-width: 75px;">
                            </td>
                            <td class="text-right font-weight-bold text-primary row-total">
                                ₹${total}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn-erp-icon btn-erp-del btn-remove-row" title="Remove Item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#emptyState').hide();
                    $('#itemsContainer').append(rowHtml);
                    calculateGlobalTotal();
                }

                function reindexRows() {
                    $('#itemsContainer tr').not('#emptyState').each(function(i) {
                        $(this).find('.row-index').text(i + 1);
                    });
                }

                // Allow live editing quantity and rate in the table list
                $(document).on('input', '.row-boxes, .row-rate', function() {
                    const row = $(this).closest('tr');
                    const boxes = parseFloat(row.find('.row-boxes').val()) || 0;
                    const pcs = parseFloat(row.find('.row-pcs').val()) || 0;
                    const rate = parseFloat(row.find('.row-rate').val()) || 0;
                    const total = (boxes * pcs * rate).toFixed(2);
                    row.find('.row-total').text(`₹${total}`);
                    calculateGlobalTotal();
                });

                // Remove Row
                $(document).on('click', '.btn-remove-row', function () {
                    $(this).closest('tr').remove();
                    reindexRows();
                    if ($('#itemsContainer tr').not('#emptyState').length === 0) {
                        $('#emptyState').show();
                    }
                    calculateGlobalTotal();
                });

                // Open Summary Modal (from either top HUD or bottom bar)
                $(document).on('click', '.btn-open-summary', function() {
                    const rowCount = $('#itemsContainer tr').not('#emptyState').length;
                    if (rowCount === 0) {
                        toastr.error('Please add at least one product to the list first.');
                        return;
                    }
                    calculateGlobalTotal();
                    $('#purchaseSummaryModal').modal('show');
                });

                $(document).on('input', '#global_gst_value, #global_other_amount, #global_discount', function () {
                    calculateGlobalTotal();
                });

                $(document).on('change', '#global_gst_type', function () {
                    calculateGlobalTotal();
                });

                function calculateGlobalTotal() {
                    let subTotal = 0;
                    let totalBoxes = 0;
                    let totalPieces = 0;

                    const rows = $('#itemsContainer tr').not('#emptyState');
                    const rowCount = rows.length;

                    rows.each(function () {
                        let boxes = parseFloat($(this).find('.row-boxes').val()) || 0;
                        let pcs = parseFloat($(this).find('.row-pcs').val()) || 0;
                        let rate = parseFloat($(this).find('.row-rate').val()) || 0;
                        totalBoxes += boxes;
                        totalPieces += (boxes * pcs);
                        subTotal += (boxes * pcs * rate);
                    });

                    // Update Top HUD values
                    $('#hudTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#hudTotalPieces').text(totalPieces.toLocaleString());
                    $('#hudSubTotal').text('₹' + subTotal.toFixed(2));

                    // Update Modal values
                    $('#global_total_boxes').val(totalBoxes);
                    $('#global_total_pieces').val(totalPieces);
                    $('#modalTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#modalTotalPieces').text(totalPieces.toLocaleString());
                    $('#modalSubTotalText').text('₹' + subTotal.toFixed(2));
                    $('#modalSubTotalRow').text('₹' + subTotal.toFixed(2));

                    // Update Bottom Bar values
                    $('#footerTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#footerTotalPieces').text(totalPieces.toLocaleString());
                    $('#footerSubTotal').text('₹' + subTotal.toFixed(2));

                    // Update Table Header values
                    $('#tableItemCount').text(rowCount);
                    $('#tableHeaderTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#tableHeaderTotalPieces').text(totalPieces.toLocaleString());

                    if (rowCount > 0) {
                        $('#tableCountBadges').show();
                        $('#emptyState').hide();
                    } else {
                        $('#tableCountBadges').hide();
                        $('#emptyState').show();
                    }

                    $('#global_sub_total').val(subTotal.toFixed(2));

                    let gstValue = parseFloat($('#global_gst_value').val()) || 0;
                    let gstType = $('#global_gst_type').val();
                    let other = parseFloat($('#global_other_amount').val()) || 0;
                    let discount = parseFloat($('#global_discount').val()) || 0;

                    let gstAmount = 0;
                    if (gstType === 'percentage') {
                        gstAmount = (subTotal * gstValue) / 100;
                    } else {
                        gstAmount = gstValue;
                    }

                    $('#global_gst_amount').val(gstAmount.toFixed(2));
                    $('#modalGstCalcText').text('+ ₹' + gstAmount.toFixed(2));

                    let grandTotal = subTotal + gstAmount + other - discount;

                    $('#global_total_amount').val(grandTotal.toFixed(2));
                    $('#displayGrandTotal').text('₹' + grandTotal.toFixed(2));
                }

                $('#addStockForm').on('submit', function (e) {
                    if ($('#itemsContainer tr').not('#emptyState').length === 0) {
                        e.preventDefault();
                        toastr.error('Please add at least one product to the list.');
                        return;
                    }

                    e.preventDefault();
                    let form = $(this);
                    let btn = $('#btnConfirmSubmit');
                    let originalHtml = btn.html();

                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function (response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#purchaseSummaryModal').modal('hide');

                                // Auto-trigger barcode generation
                                let pdfForm = $('<form>', {
                                    action: "{{ route('admin.inventory.barcode-generator.generate-bulk-tspl') }}",
                                    method: 'POST',
                                    target: '_blank'
                                }).append($('<input>', {
                                    type: 'hidden',
                                    name: '_token',
                                    value: "{{ csrf_token() }}"
                                }));

                                if (response.print_data) {
                                    response.print_data.forEach(data => {
                                        pdfForm.append($('<input>', {
                                            type: 'hidden',
                                            name: 'print_data[' + data.id + ']',
                                            value: data.qty
                                        }));
                                    });
                                } else if (response.ids) {
                                    response.ids.forEach(id => {
                                        pdfForm.append($('<input>', {
                                            type: 'hidden',
                                            name: 'ids[]',
                                            value: id
                                        }));
                                    });
                                }

                                $('body').append(pdfForm);
                                pdfForm.submit();
                                pdfForm.remove();

                                setTimeout(() => {
                                    window.location.href = "{{ route('admin.inventory.index') }}";
                                }, 1000);
                            }
                        },
                        error: function (xhr) {
                            btn.prop('disabled', false).html(originalHtml);
                            let error = xhr.responseJSON ? xhr.responseJSON.message : 'Error adding stock';
                            toastr.error(error);
                        }
                    });
                });

                // Auto-select first warehouse
                (function () {
                    let w = $('#headerWarehouse');
                    if (w.find('option').length > 1) {
                        w.val(w.find('option:eq(1)').val()).trigger('change');
                    }
                })();
            });
        </script>
    @endsection
@endsection