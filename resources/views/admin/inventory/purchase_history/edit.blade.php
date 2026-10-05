@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --erp-primary: #4f46e5;
            --erp-primary-hover: #4338ca;
            --erp-primary-light: #eef2ff;
            --erp-success: #059669;
            --erp-success-light: #ecfdf5;
            --erp-danger: #dc2626;
            --erp-danger-light: #fef2f2;
            --erp-warning: #d97706;
            --erp-border: #cbd5e1;
            --erp-bg-header: #f1f5f9;
            --erp-text: #0f172a;
            --erp-muted: #64748b;
        }

        .content-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            padding-bottom: 75px;
        }

        /* STICKY ERP TOP HUD BAR */
        .sticky-erp-hud {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            padding: 8px 16px;
            margin: -0.5rem -0.5rem 10px -0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: nowrap;
        }

        .hud-stat-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 14px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            min-width: 200px;
        }

        .hud-source-box {
            border-left: 4px solid var(--erp-primary);
            background: #fafbff;
        }

        .hud-target-box {
            border-left: 4px solid var(--erp-success);
            background: #fbfdfb;
        }

        .hud-subtotal-box {
            border-left: 4px solid #f59e0b;
            background: #fffdf7;
        }

        .hud-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 2px;
        }

        .hud-numbers {
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.2;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        /* ERP CARDS & TABLES */
        .erp-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 10px;
            overflow: hidden;
        }

        .erp-card-header {
            padding: 7px 12px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .erp-card-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .erp-table {
            width: 100%;
            margin-bottom: 0;
            font-size: 11.5px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .erp-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border-bottom: 2px solid #cbd5e1;
            border-top: none;
            vertical-align: middle;
            white-space: nowrap;
        }

        .erp-table tbody td {
            padding: 5px 8px;
            vertical-align: middle;
            border-top: 1px solid #edf2f7;
            border-bottom: none;
        }

        .erp-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* COMPACT CONTROLS IN ERP */
        .erp-input {
            height: 31px !important;
            padding: 2px 8px !important;
            font-size: 12px !important;
            font-weight: 600;
            border-radius: 5px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff;
            line-height: 1.2;
            width: 100%;
        }

        .erp-input:focus {
            border-color: var(--erp-primary) !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
            outline: none;
        }

        .erp-input[readonly] {
            background-color: #f1f5f9 !important;
            color: #475569;
        }

        .erp-label {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #475569;
            margin-bottom: 3px;
            display: block;
        }

        /* SELECT2 IN ERP */
        .select2-container--bootstrap4 .select2-selection {
            height: 31px !important;
            min-height: 31px !important;
            border-radius: 5px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 29px !important;
            font-size: 12px !important;
            padding-left: 8px !important;
            padding-right: 18px !important;
            color: var(--erp-text);
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 29px !important;
            right: 4px !important;
            width: 14px !important;
        }

        .select2-dropdown {
            border-radius: 8px !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12) !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 12px !important;
            z-index: 10050 !important;
        }

        /* PILLS & BADGES */
        .erp-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1.3;
            white-space: nowrap;
        }

        .erp-pill-source {
            background: #e0e7ff;
            color: #3730a3;
            border: 1px solid #c7d2fe;
        }

        .erp-pill-gen {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        /* BUTTONS */
        .btn-erp-add {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 5px;
            line-height: 1.4;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        .btn-erp-icon {
            width: 26px;
            height: 26px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            font-size: 11px;
            border: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .btn-erp-del {
            background: #fee2e2;
            color: #ef4444;
        }

        .btn-erp-del:hover {
            background: #ef4444;
            color: #fff;
        }

        .btn-hud-confirm {
            background: linear-gradient(135deg, var(--erp-primary) 0%, var(--erp-primary-hover) 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 12.5px;
            border-radius: 6px;
            padding: 6px 16px;
            border: none;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);
            white-space: nowrap;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-hud-confirm:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
        }

        /* STICKY BOTTOM ACTIONS BAR */
        .sticky-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 250px;
            right: 0;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            padding: 8px 24px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.04);
        }

        @media (max-width: 991.98px) {
            .sticky-bottom-bar {
                left: 0;
            }
            .sticky-erp-hud {
                flex-wrap: wrap;
            }
        }
    </style>

    <div class="content-wrapper">
        <section class="content pt-1">
            <div class="container-fluid px-2">
                <form action="{{ route('admin.inventory.purchase_history.update', $purchase->id) }}" method="POST" id="editStockForm">
                    @csrf

                    <!-- ========================================================= -->
                    <!-- 1. STICKY ERP TOP HUD BAR (MATCHES CREATE PAGE) -->
                    <!-- ========================================================= -->
                    <div class="sticky-erp-hud">
                        <!-- Total Boxes Stat Box -->
                        <div class="hud-stat-box hud-source-box">
                            <div>
                                <span class="hud-label text-primary"><i class="fas fa-boxes mr-1"></i> TOTAL BOXES</span>
                                <div class="hud-numbers">
                                    <span id="hudTotalBoxes" class="text-primary">0</span> <small class="text-muted" style="font-size:10px;">Boxes</small>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pieces Stat Box -->
                        <div class="hud-stat-box hud-target-box">
                            <div>
                                <span class="hud-label text-success"><i class="fas fa-tshirt mr-1"></i> TOTAL PIECES</span>
                                <div class="hud-numbers">
                                    <span id="hudTotalPieces" class="text-success">0</span> <small class="text-muted" style="font-size:10px;">Pieces</small>
                                </div>
                            </div>
                        </div>

                        <!-- Sub Total Stat Box -->
                        <div class="hud-stat-box hud-subtotal-box">
                            <div>
                                <span class="hud-label text-warning"><i class="fas fa-receipt mr-1"></i> SUB TOTAL</span>
                                <div class="hud-numbers">
                                    <span id="hudSubTotal" class="text-dark">₹0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions in HUD -->
                        <div class="d-flex align-items-center">
                            <a href="{{ route('admin.inventory.purchase_history.index') }}" class="btn btn-outline-secondary btn-erp-add mr-2" style="height: 31px;">
                                <i class="fas fa-history mr-1"></i> Purchase History
                            </a>
                            <button type="button" class="btn-hud-confirm btn-open-summary" id="btnHudOpenSummary">
                                <i class="fas fa-save mr-1"></i> Review & Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 2. SECTION 1: SOURCE & INVOICE DETAILS (ERP CARD) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-primary">
                                <i class="fas fa-file-invoice"></i> 1. Source & Invoice Details
                                <span class="badge badge-warning ml-2 font-weight-bold" style="font-size: 10.5px;">Editing PO #{{ $purchase->id }}</span>
                            </div>
                        </div>
                        <div class="p-2">
                            <div class="form-row align-items-end">
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Source Type *</label>
                                    <select name="source_type" id="sourceType" class="erp-input select2">
                                        <option value="vendor" {{ $purchase->vendor_id ? 'selected' : '' }}>Vendor</option>
                                        <option value="customer" {{ $purchase->customer_id ? 'selected' : '' }}>Customer</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label text-primary">Production PO (Optional)</label>
                                    <select name="production_po_id" id="loadProductionPO" class="erp-input select2">
                                        <option value="">-- Optional: Select PO --</option>
                                        @foreach($productionPOs as $po)
                                            <option value="{{ $po->id }}" {{ $purchase->production_po_id == $po->id ? 'selected' : '' }}>{{ $po->po_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1" id="vendorContainer" style="{{ $purchase->vendor_id || !$purchase->customer_id ? '' : 'display: none;' }}">
                                    <label class="erp-label">Vendor *</label>
                                    <select name="vendor_id" id="vendorSelect" class="erp-input select2">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ $purchase->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->company_name ?? $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6 mb-1" id="customerContainer" style="{{ $purchase->customer_id ? '' : 'display: none;' }}">
                                    <label class="erp-label">Customer *</label>
                                    <select name="customer_id" id="customerSelect" class="erp-input select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ $purchase->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->company_name ?? $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Bill / Inv No.</label>
                                    <input type="text" name="bill_no" class="erp-input" placeholder="Enter Bill No." value="{{ old('bill_no', $purchase->bill_no) }}">
                                </div>
                                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                                    <label class="erp-label">Purchase Date *</label>
                                    <input type="date" name="purchase_date" class="erp-input" value="{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : date('Y-m-d') }}">
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
                    <!-- 3. SECTION 2: ADD PRODUCT ITEM (1 SINGLE COMPACT LINE) -->
                    <!-- ========================================================= -->
                    <div class="erp-card">
                        <div class="erp-card-header">
                            <div class="erp-card-title text-success">
                                <i class="fas fa-cart-plus"></i> 2. Add Product Item
                            </div>
                        </div>
                        <div class="p-2">
                            <div class="form-row align-items-end">
                                <div class="col-lg-2 col-md-3 col-6 mb-1">
                                    <label class="erp-label">Warehouse *</label>
                                    <select id="headerWarehouse" class="erp-input select2">
                                        <option value="">Warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2 col-6 mb-1">
                                    <label class="erp-label">Rack *</label>
                                    <select id="headerRack" class="erp-input select2">
                                        <option value="">Rack</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-12 mb-1">
                                    <label class="erp-label">Design *</label>
                                    <select id="headerDesign" class="erp-input select2">
                                        <option value="">Select Design</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-name="{{ $product->name_of_garment }}">
                                                {{ $product->design_number }} ({{ $product->series->name ?? '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2 col-6 mb-1">
                                    <label class="erp-label">Size Set *</label>
                                    <select id="headerSizeSet" class="erp-input select2">
                                        <option value="">Size</option>
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-6 mb-1">
                                    <label class="erp-label">Color *</label>
                                    <select id="headerColor" class="erp-input select2">
                                        <option value="">Color</option>
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2 col-4 mb-1">
                                    <label class="erp-label">Boxes *</label>
                                    <input type="number" id="headerTotalBoxes" class="erp-input text-center font-weight-bold" min="1" placeholder="Qty">
                                </div>
                                <div class="col-lg-1 col-md-2 col-4 mb-1">
                                    <label class="erp-label">Rate (₹) *</label>
                                    <input type="number" id="headerPurchaseRate" class="erp-input text-right font-weight-bold" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-lg-1 col-md-2 col-4 mb-1">
                                    <button type="button" id="btnAddToList" class="btn btn-primary btn-block btn-erp-add justify-content-center" style="height: 31px;">
                                        <i class="fas fa-plus"></i> Add
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
                                <i class="fas fa-boxes"></i> 3. Added Products List
                                <span class="badge badge-light border text-muted ml-2 font-weight-normal" style="font-size:11px;">
                                    <strong id="tableItemCount">{{ count($purchase->items) }}</strong> items in purchase
                                </span>
                            </div>
                            <div id="tableCountBadges" style="{{ count($purchase->items) > 0 ? '' : 'display: none;' }}">
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
                                        <th style="width: 22%;">Design No</th>
                                        <th style="width: 14%;">Pattern / Fit</th>
                                        <th style="width: 15%;">Warehouse / Rack</th>
                                        <th style="width: 13%;">Size / Color</th>
                                        <th style="width: 7%; text-align: center;">Boxes</th>
                                        <th style="width: 6%; text-align: center;">Pcs/Bx</th>
                                        <th style="width: 7%; text-align: right;">MRP</th>
                                        <th style="width: 7%; text-align: right;">Rate (₹)</th>
                                        <th style="width: 8%; text-align: right;">Total (₹)</th>
                                        <th style="width: 4%; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    @forelse($purchase->items as $idx => $item)
                                        @php
                                            $lineTotal = $item->box_quantity * $item->pieces_per_box * $item->purchase_rate;
                                        @endphp
                                        <tr class="item-row" data-index="{{ $idx }}">
                                            <td style="text-align: center;" class="text-muted font-weight-bold row-index">{{ $idx + 1 }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $item->newProduct->design_number ?? 'N/A' }}</strong>
                                                <small class="text-muted d-block">{{ $item->newProduct->name_of_garment ?? '' }}</small>
                                                <input type="hidden" name="products[{{ $idx }}][product_id]" value="{{ $item->new_product_id }}">
                                            </td>
                                            <td>
                                                <span class="erp-pill erp-pill-source">{{ $item->newPattern->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ $item->newFitting->name ?? 'N/A' }}</small>
                                                <input type="hidden" name="products[{{ $idx }}][pattern_id]" value="{{ $item->new_pattern_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][fitting_id]" value="{{ $item->new_fitting_id }}">
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-dark">{{ $item->newWarehouse->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ $item->newRack->name ?? 'N/A' }}</small>
                                                <input type="hidden" name="products[{{ $idx }}][warehouse_id]" value="{{ $item->new_warehouse_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][rack_id]" value="{{ $item->new_rack_id }}">
                                            </td>
                                            <td>
                                                <span class="badge badge-primary font-weight-bold">{{ $item->newSizeSet->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ $item->newColor->name ?? 'N/A' }}</small>
                                                <input type="hidden" name="products[{{ $idx }}][size_set_id]" value="{{ $item->new_size_set_id }}">
                                                <input type="hidden" name="products[{{ $idx }}][color_id]" value="{{ $item->new_color_id }}">
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="number" name="products[{{ $idx }}][total_boxes]" value="{{ $item->box_quantity }}" class="erp-input text-center font-weight-bold row-boxes" style="width: 55px; display: inline-block;" min="1">
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge badge-light border row-pcs-badge">{{ $item->pieces_per_box }}</span>
                                                <input type="hidden" name="products[{{ $idx }}][pieces_per_box]" value="{{ $item->pieces_per_box }}" class="row-pcs">
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="text-muted font-weight-bold">₹{{ number_format($item->mrp, 2) }}</span>
                                                <input type="hidden" name="products[{{ $idx }}][mrp]" value="{{ $item->mrp }}">
                                            </td>
                                            <td style="text-align: right;">
                                                <input type="number" name="products[{{ $idx }}][purchase_rate]" value="{{ number_format($item->purchase_rate, 2, '.', '') }}" class="erp-input text-right font-weight-bold row-rate" style="width: 70px; display: inline-block;" step="0.01" min="0">
                                            </td>
                                            <td style="text-align: right;" class="font-weight-bold text-primary row-total">
                                                ₹{{ number_format($lineTotal, 2) }}
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" class="btn-erp-icon btn-erp-del btn-remove-row" title="Remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="emptyState">
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="fas fa-shopping-basket mr-1 fa-lg text-secondary opacity-50"></i> No products added yet. Select options above and click <strong>+ Add</strong>.
                                            </td>
                                        </tr>
                                    @endforelse
                                    <tr id="emptyState" style="{{ count($purchase->items) > 0 ? 'display: none;' : '' }}">
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-basket mr-1 fa-lg text-secondary opacity-50"></i> No products added yet. Select options above and click <strong>+ Add</strong>.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================================= -->
                    <!-- 5. STICKY BOTTOM ACTIONS BAR (MATCHES CREATE PAGE) -->
                    <!-- ========================================================= -->
                    <div class="sticky-bottom-bar">
                        <div>
                            <a href="{{ route('admin.inventory.purchase_history.index') }}" class="text-secondary font-weight-bold mr-3" style="font-size:12px;">
                                <i class="fas fa-arrow-left mr-1"></i> Cancel and Exit
                            </a>
                            <span class="text-muted small">Editing PO #<strong>{{ $purchase->id }}</strong> &bull; Changes will update inventory stock and balances in real time.</span>
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
                                <i class="fas fa-save mr-1"></i> Review Summary & Save
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
                                        <i class="fas fa-receipt text-primary mr-1"></i> Purchase Order #{{ $purchase->id }} Invoice Summary
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
                                            <input type="hidden" name="sub_total" id="global_sub_total" value="{{ $purchase->sub_total ?? '0.00' }}">
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
                                                        <input type="number" name="gst_value" id="global_gst_value" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0" value="{{ $purchase->gst_value ?? '0' }}">
                                                        <div class="input-group-append">
                                                            <select name="gst_type" id="global_gst_type" class="custom-select custom-select-sm bg-light" style="width: 65px;">
                                                                <option value="percentage" {{ ($purchase->gst_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>%</option>
                                                                <option value="amount" {{ ($purchase->gst_type ?? '') === 'amount' ? 'selected' : '' }}>₹</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted font-weight-bold d-block mt-1" id="modalGstCalcText">+ ₹{{ number_format($purchase->gst ?? 0, 2) }}</small>
                                                    <input type="hidden" name="gst" id="global_gst_amount" value="{{ $purchase->gst ?? '0.00' }}">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block">Other Charges (+)</span>
                                                    <small class="text-muted" style="font-size:11px;">Freight, loading, etc.</small>
                                                </div>
                                                <div class="input-group input-group-sm" style="width: 165px;">
                                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">₹</span></div>
                                                    <input type="number" name="other_amount" id="global_other_amount" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0" value="{{ $purchase->other_amount ?? '0' }}">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block">Discount (-)</span>
                                                    <small class="text-muted" style="font-size:11px;">Vendor concession</small>
                                                </div>
                                                <div class="input-group input-group-sm" style="width: 165px;">
                                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">₹</span></div>
                                                    <input type="number" name="discount" id="global_discount" class="form-control text-right font-weight-bold" placeholder="0.00" step="0.01" min="0" value="{{ $purchase->discount ?? '0' }}">
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center py-2">
                                                <div style="min-width: 130px;">
                                                    <span class="font-weight-bold text-muted small text-uppercase d-block"><i class="fas fa-comment-alt text-secondary mr-1"></i> Remarks</span>
                                                    <small class="text-muted" style="font-size:11px;">Order note or remarks</small>
                                                </div>
                                                <div class="ml-3 flex-grow-1" style="max-width: 250px;">
                                                    <input type="text" name="remarks" id="global_remarks" class="form-control form-control-sm" placeholder="Enter remarks (optional)..." maxlength="500" value="{{ $purchase->remarks ?? '' }}">
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
                                            <input type="hidden" name="total_amount" id="global_total_amount" value="{{ $purchase->total_amount ?? '0.00' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-white py-2 px-3 border-top d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 font-weight-bold" data-dismiss="modal">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Edit
                                    </button>
                                    <button type="submit" class="btn-hud-confirm" id="btnConfirmSubmit">
                                        <i class="fas fa-save mr-1"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    @section('scripts')
        <script>
            $(function () {
                let itemCount = {{ count($purchase->items) }};
                let currentVariants = [];

                function initSelect2(container) {
                    container.find('.select2').each(function () {
                        $(this).select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            dropdownAutoWidth: true,
                            dropdownParent: $('body')
                        });
                    });
                }

                initSelect2($('.content-wrapper'));

                // Open Summary Modal
                $(document).on('click', '.btn-open-summary', function () {
                    if ($('#itemsContainer tr.item-row').length === 0) {
                        toastr.warning('Please add at least one product before reviewing summary.');
                        return;
                    }
                    calculateGlobalTotal();
                    $('#purchaseSummaryModal').modal('show');
                });

                // Handle Source Type Toggle
                $('#sourceType').on('change', function () {
                    let type = $(this).val();
                    if (type === 'vendor') {
                        $('#vendorContainer').show();
                        $('#customerContainer').hide();
                    } else if (type === 'customer') {
                        $('#vendorContainer').hide();
                        $('#customerContainer').show();
                    }
                });

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
                        });
                    }
                });

                // Header Design -> Load Pattern, Fitting, and Variants
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
                            if (data && data.no_of_pcs) {
                                $('#headerPcsPerBox').val(data.no_of_pcs);
                            }
                        });

                        let variant = currentVariants.find(v => v.size_set_id == sizeSetId);
                        if (variant) {
                            $('#headerMRP').val(variant.mrp || 0);
                            variant.colors.forEach(function (c) {
                                colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            colorSelect.trigger('change.select2');
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
                        product_name: $('#headerDesign option:selected').text().split('(')[0].trim(),
                        design_number: $('#headerDesign option:selected').text().split('(')[0].trim(),
                        pattern_id: $('#headerPattern').val(),
                        pattern_name: $('#headerPatternDisplay').val() || 'Regular',
                        fitting_id: $('#headerFitting').val(),
                        fitting_name: $('#headerFittingDisplay').val() || 'Standard',
                        size_set_id: $('#headerSizeSet').val(),
                        size_set_name: $('#headerSizeSet option:selected').text(),
                        color_id: $('#headerColor').val(),
                        color_name: $('#headerColor option:selected').text(),
                        pieces_per_box: $('#headerPcsPerBox').val() || 1,
                        mrp: $('#headerMRP').val() || 0,
                        total_boxes: $('#headerTotalBoxes').val(),
                        purchase_rate: $('#headerPurchaseRate').val() || 0
                    };

                    // Validation
                    if (!data.warehouse_id) { toastr.warning('Please select a Warehouse'); return; }
                    if (!data.rack_id) { toastr.warning('Please select a Rack'); return; }
                    if (!data.product_id) { toastr.warning('Please select a Design'); return; }
                    if (!data.size_set_id) { toastr.warning('Please select a Size Set'); return; }
                    if (!data.color_id) { toastr.warning('Please select a Color'); return; }
                    if (!data.total_boxes || parseInt(data.total_boxes) <= 0) { toastr.warning('Please enter valid number of boxes'); return; }

                    addToTable(data);
                    toastr.success('Product added to list');

                    // Reset quantity and rate for fast entry
                    $('#headerTotalBoxes').val('');
                    $('#headerPurchaseRate').val('');
                });

                function addToTable(data) {
                    const idx = itemCount++;
                    const totalBoxes = parseFloat(data.total_boxes) || 0;
                    const pcsPerBox = parseFloat(data.pieces_per_box) || 1;
                    const purchaseRate = parseFloat(data.purchase_rate) || 0;
                    const lineTotal = totalBoxes * pcsPerBox * purchaseRate;

                    const rowHtml = `
                        <tr class="item-row" data-index="${idx}">
                            <td style="text-align: center;" class="text-muted font-weight-bold row-index">1</td>
                            <td>
                                <strong class="text-dark">${data.product_name}</strong>
                                <input type="hidden" name="products[${idx}][product_id]" value="${data.product_id}">
                            </td>
                            <td>
                                <span class="erp-pill erp-pill-source">${data.pattern_name || 'N/A'}</span>
                                <small class="text-muted d-block">${data.fitting_name || 'N/A'}</small>
                                <input type="hidden" name="products[${idx}][pattern_id]" value="${data.pattern_id}">
                                <input type="hidden" name="products[${idx}][fitting_id]" value="${data.fitting_id}">
                            </td>
                            <td>
                                <span class="font-weight-bold text-dark">${data.warehouse_name}</span>
                                <small class="text-muted d-block">${data.rack_name}</small>
                                <input type="hidden" name="products[${idx}][warehouse_id]" value="${data.warehouse_id}">
                                <input type="hidden" name="products[${idx}][rack_id]" value="${data.rack_id}">
                            </td>
                            <td>
                                <span class="badge badge-primary font-weight-bold">${data.size_set_name}</span>
                                <small class="text-muted d-block">${data.color_name}</small>
                                <input type="hidden" name="products[${idx}][size_set_id]" value="${data.size_set_id}">
                                <input type="hidden" name="products[${idx}][color_id]" value="${data.color_id}">
                            </td>
                            <td style="text-align: center;">
                                <input type="number" name="products[${idx}][total_boxes]" value="${data.total_boxes}" class="erp-input text-center font-weight-bold row-boxes" style="width: 55px; display: inline-block;" min="1">
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-light border row-pcs-badge">${data.pieces_per_box}</span>
                                <input type="hidden" name="products[${idx}][pieces_per_box]" value="${data.pieces_per_box}" class="row-pcs">
                            </td>
                            <td style="text-align: right;">
                                <span class="text-muted font-weight-bold">₹${parseFloat(data.mrp).toFixed(2)}</span>
                                <input type="hidden" name="products[${idx}][mrp]" value="${data.mrp}">
                            </td>
                            <td style="text-align: right;">
                                <input type="number" name="products[${idx}][purchase_rate]" value="${parseFloat(data.purchase_rate).toFixed(2)}" class="erp-input text-right font-weight-bold row-rate" style="width: 70px; display: inline-block;" step="0.01" min="0">
                            </td>
                            <td style="text-align: right;" class="font-weight-bold text-primary row-total">
                                ₹${lineTotal.toFixed(2)}
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-erp-icon btn-erp-del btn-remove-row" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    `;

                    $('#emptyState').hide();
                    $('#itemsContainer').append(rowHtml);
                    reindexRows();
                    calculateGlobalTotal();
                }

                function reindexRows() {
                    $('#itemsContainer tr.item-row').each(function(i) {
                        $(this).find('.row-index').text(i + 1);
                        $(this).find('input, select').each(function() {
                            let name = $(this).attr('name');
                            if (name) {
                                $(this).attr('name', name.replace(/products\[\d+\]/, 'products[' + i + ']'));
                            }
                        });
                    });
                }

                // Row Boxes, Rate change
                $(document).on('input', '.row-boxes, .row-rate', function() {
                    const row = $(this).closest('tr');
                    const boxes = parseFloat(row.find('.row-boxes').val()) || 0;
                    const pcs = parseFloat(row.find('.row-pcs').val()) || 0;
                    const rate = parseFloat(row.find('.row-rate').val()) || 0;
                    const total = (boxes * pcs * rate).toFixed(2);
                    row.find('.row-total').text(`₹${total}`);
                    calculateGlobalTotal();
                });

                // Remove row
                $(document).on('click', '.btn-remove-row', function () {
                    $(this).closest('tr').remove();
                    if ($('#itemsContainer tr.item-row').length === 0) {
                        $('#emptyState').show();
                    }
                    reindexRows();
                    calculateGlobalTotal();
                });

                // Modal input listeners
                $(document).on('input', '#global_gst_value, #global_other_amount, #global_discount', function () {
                    calculateGlobalTotal();
                });

                $(document).on('change', '#global_gst_type', function () {
                    calculateGlobalTotal();
                });

                function calculateGlobalTotal() {
                    let rowCount = $('#itemsContainer tr.item-row').length;
                    let totalBoxes = 0;
                    let totalPieces = 0;
                    let subTotal = 0;

                    $('#itemsContainer tr.item-row').each(function () {
                        let boxes = parseFloat($(this).find('.row-boxes').val()) || 0;
                        let pcs = parseFloat($(this).find('.row-pcs').val()) || 0;
                        let rate = parseFloat($(this).find('.row-rate').val()) || 0;
                        let lineTotal = boxes * pcs * rate;

                        totalBoxes += boxes;
                        totalPieces += (boxes * pcs);
                        subTotal += lineTotal;

                        $(this).find('.row-total').text('₹' + lineTotal.toFixed(2));
                    });

                    // Update Sticky HUD values
                    $('#hudTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#hudTotalPieces').text(totalPieces.toLocaleString());
                    $('#hudSubTotal').text('₹' + subTotal.toFixed(2));

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
                    $('#modalSubTotalText').text('₹' + subTotal.toFixed(2));
                    $('#modalSubTotalRow').text('₹' + subTotal.toFixed(2));
                    $('#modalTotalBoxes').text(totalBoxes.toLocaleString());
                    $('#modalTotalPieces').text(totalPieces.toLocaleString());

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

                // Load from Production PO
                $('#loadProductionPO').on('change', function() {
                    const poId = $(this).val();
                    if (!poId) { $('#poReferenceContainer').hide(); return; }
                    
                    $.get("{{ url('admin/inventory/get-po-items') }}/" + poId, function(response) {
                        if (response.success && response.items.length > 0) {
                            let refHtml = '';
                            response.items.forEach(item => {
                                refHtml += `
                                    <tr>
                                        <td class="font-weight-bold">${item.design_number}</td>
                                        <td><span class="erp-pill erp-pill-source">${item.pattern_name}</span> <small class="text-muted d-block">${item.fitting_name}</small></td>
                                        <td><span class="badge badge-primary">${item.size_set_name}</span> <small class="text-muted d-block">${item.color_name}</small></td>
                                        <td class="text-center"><span class="badge badge-light border font-weight-bold">${item.total_boxes} Bx</span></td>
                                        <td class="text-right font-weight-bold text-dark">₹${parseFloat(item.purchase_rate).toFixed(2)}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-erp-add py-0 px-2 btn-apply-po-item" 
                                                data-product="${item.product_id}" 
                                                data-size="${item.size_set_id}" 
                                                data-color="${item.color_id}"
                                                data-boxes="${item.total_boxes}"
                                                data-rate="${item.purchase_rate}">
                                                <i class="fas fa-check"></i> Select
                                            </button>
                                        </td>
                                    </tr>
                                `;
                            });
                            $('#poRefNumber').text($('#loadProductionPO option:selected').text());
                            $('#poRefBody').html(refHtml);
                            $('#poReferenceContainer').show();
                        } else {
                            $('#poReferenceContainer').hide();
                        }
                    });
                });

                $(document).on('click', '.btn-apply-po-item', function() {
                    const d = $(this).data();
                    $('#headerDesign').val(d.product).trigger('change');
                    setTimeout(() => {
                        $('#headerSizeSet').val(d.size).trigger('change');
                        setTimeout(() => {
                            $('#headerColor').val(d.color).trigger('change.select2');
                            $('#headerTotalBoxes').val(d.boxes);
                            $('#headerPurchaseRate').val(d.rate);
                        }, 500);
                    }, 800);
                });

                // Form submit handler
                $('#editStockForm').on('submit', function (e) {
                    if ($('#itemsContainer tr.item-row').length === 0) {
                        e.preventDefault();
                        toastr.error('Please add at least one product to the list.');
                        $('#purchaseSummaryModal').modal('hide');
                        return false;
                    }

                    let btn = $('#btnConfirmSubmit');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving Changes...');
                    return true;
                });

                // Initialize calculation on load
                calculateGlobalTotal();
            });
        </script>
    @endsection
@endsection
