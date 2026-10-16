@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        @if(!isset($boxes))
            <!-- PAGE HEADER (Step 1) -->
            <div class="erp-header-bar mb-2">
                <div>
                    <div class="erp-header-title"><i class="fas fa-plus-circle text-primary"></i> Create New {{ request('order_type') == 'direct' ? 'Direct' : 'Sales' }} Order</div>
                    <div class="erp-page-subtitle">Initiate a new order by selecting {{ request('order_type') == 'direct' ? 'a customer' : 'an agent and a customer' }}.</div>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.agent-orders.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-list mr-1"></i> All Orders</a>
                </div>
            </div>

            <!-- STEP 1: Selection Form -->
            <div class="ao-card">
                <form action="{{ route('admin.agent-orders.store') }}" method="POST" class="ao-form">
                    @csrf
                    <input type="hidden" name="order_type" value="{{ request('order_type', 'normal') }}">

                    <div class="ao-body">
                        <div class="ao-grid">
                            <!-- Row 1 -->
                            <div class="ao-field">
                                <label class="ao-label">Party Type <span class="req">*</span></label>
                                <div class="ao-seg">
                                    <input type="radio" id="typeCustomer" name="party_type" value="customer" {{ old('party_type', request('party_type', 'customer')) == 'customer' ? 'checked' : '' }}>
                                    <label for="typeCustomer"><i class="fas fa-store"></i> Customer</label>
                                    <input type="radio" id="typeVendor" name="party_type" value="vendor" {{ old('party_type', request('party_type')) == 'vendor' ? 'checked' : '' }}>
                                    <label for="typeVendor"><i class="fas fa-truck-loading"></i> Vendor</label>
                                </div>
                            </div>

                            <div class="ao-field" id="customerWrapper" style="{{ old('party_type', request('party_type', 'customer')) == 'customer' ? '' : 'display:none;' }}">
                                <label class="ao-label">
                                    <span>Select Customer <span class="req">*</span></span>
                                    <span class="ao-links">
                                        <a href="{{ route('admin.master.customer.create') }}" target="_blank" title="Create New"><i class="fas fa-plus"></i> New</a>
                                        <a href="javascript:void(0)" id="refreshCustomerBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="master_customer_id" id="customerSelect" class="form-control select2 @error('master_customer_id') is-invalid @enderror">
                                    <option value="">-- Choose Customer --</option>
                                    @foreach($shops as $shop_item)
                                        <option value="{{ $shop_item->id }}" {{ old('master_customer_id', request('master_customer_id')) == $shop_item->id ? 'selected' : '' }}>
                                            {{ $shop_item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ao-field" id="vendorWrapper" style="{{ old('party_type', request('party_type')) == 'vendor' ? '' : 'display:none;' }}">
                                <label class="ao-label">
                                    <span>Select Vendor <span class="req">*</span></span>
                                    <span class="ao-links">
                                        <a href="{{ route('admin.master.vendor.create') }}" target="_blank" title="Create New"><i class="fas fa-plus"></i> New</a>
                                        <a href="javascript:void(0)" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="master_vendor_id" id="vendorSelect" class="form-control select2 @error('master_vendor_id') is-invalid @enderror">
                                    <option value="">-- Choose Vendor --</option>
                                    @foreach($vendors as $vendor_item)
                                        <option value="{{ $vendor_item->id }}" {{ old('master_vendor_id', request('master_vendor_id')) == $vendor_item->id ? 'selected' : '' }}>
                                            {{ $vendor_item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Row 2 -->
                            <div class="ao-field">
                                <label class="ao-label">
                                    <span>Sales Man <small class="text-muted font-weight-normal">(optional)</small></span>
                                    <span class="ao-links">
                                        <a href="{{ route('admin.master.sales-man.create') }}" target="_blank" title="Create New"><i class="fas fa-plus"></i> New</a>
                                        <a href="javascript:void(0)" id="refreshSalesManBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="sales_man_id" id="salesManSelect" class="form-control select2">
                                    <option value="">-- Choose Sales Man --</option>
                                    @foreach($salesMen as $man)
                                        <option value="{{ $man->id }}" {{ old('sales_man_id', request('sales_man_id')) == $man->id ? 'selected' : '' }}>
                                            {{ $man->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ao-field">
                                <label class="ao-label">Order Date <span class="req">*</span></label>
                                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            </div>

                            <!-- Row 3 -->
                            <div class="ao-field">
                                <label class="ao-label">Order Type <span class="req">*</span></label>
                                <select name="order_type" id="orderTypeSelect" class="form-control @error('order_type') is-invalid @enderror" required>
                                    <option value="normal" {{ request('order_type') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="direct" {{ request('order_type') == 'direct' ? 'selected' : '' }}>Direct (Instant Bill/Dispatch)</option>
                                </select>
                            </div>

                            <div class="ao-field">
                                <label class="ao-label">Sale Type <span class="req">*</span></label>
                                <select name="sale_type" id="saleTypeSelect" class="form-control @error('sale_type') is-invalid @enderror" required>
                                    <option value="item" {{ old('sale_type', 'item') == 'item' ? 'selected' : '' }}>Item (Ready Goods)</option>
                                    <option value="fabric" {{ old('sale_type') == 'fabric' ? 'selected' : '' }}>Fabric</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-none" id="agentSelectionWrapper" style="display:none;">
                            <select name="sales_agent_id" id="agentSelect" class="form-control">
                                 <option value="direct">Direct</option>
                            </select>
                        </div>
                    </div>

                    <!-- Footer row: pricing switch + actions -->
                    <div class="ao-actions">
                        <div class="custom-control custom-switch ao-switch">
                            <input type="checkbox" class="custom-control-input" id="sampleSetToggleStep1" name="is_sample_set" value="1">
                            <label class="custom-control-label" for="sampleSetToggleStep1">Use Sample Set Pricing for this Order</label>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <a href="{{ route('admin.agent-orders.index') }}" class="btn-erp btn-erp-outline">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn-erp btn-erp-primary px-4">
                                Proceed <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <!-- PAGE HEADER (Step 2) -->
            <div class="erp-header-bar mb-2">
                <div style="min-width: 0;">
                    <div class="erp-header-title text-truncate" title="New {{ request('order_type') == 'direct' ? 'Direct' : 'Agent' }} Order: {{ $shop->name }}">
                        <i class="fas fa-shopping-basket text-primary"></i> New {{ request('order_type') == 'direct' ? 'Direct' : 'Agent' }} Order: <span class="erp-party-name">{{ $shop->name }}</span>
                    </div>
                    <div class="erp-page-subtitle text-truncate"><i class="fas fa-user-tie mr-1"></i> {{ $agent->id === 'direct' ? 'Direct Sale (No Agent)' : 'Agent: ' . $agent->name }}</div>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.agent-orders.create') }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-exchange-alt mr-1"></i> Change Agent/Shop
                    </a>
                    <button type="button" class="btn-erp btn-erp-outline open-checkout-modal-btn" id="topCheckoutBtn" title="Review selected items and place order">
                        <i class="fas fa-arrow-circle-right mr-1"></i> Next <span class="badge badge-primary ml-1" id="topBoxesBadge">0 Boxes</span>
                    </button>
                </div>
            </div>

            <!-- FILTERS BAR -->
            <div class="erp-filter-bar mb-2">
                <form method="GET" action="{{ route('admin.agent-orders.create') }}" id="filterForm">
                    <input type="hidden" name="sales_agent_id" value="{{ $agent->id }}">
                    <input type="hidden" name="sales_man_id" value="{{ request('sales_man_id') }}">
                    <input type="hidden" name="party_type" value="{{ request('party_type', 'customer') }}">
                    <input type="hidden" name="master_customer_id" value="{{ request('master_customer_id') }}">
                    <input type="hidden" name="master_vendor_id" value="{{ request('master_vendor_id') }}">
                    <input type="hidden" name="order_date" value="{{ request('order_date') }}">
                    <input type="hidden" name="order_type" value="{{ request('order_type') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label"><i class="fas fa-warehouse mr-1 text-primary"></i> Warehouse</label>
                            <select name="warehouse_ids[]" id="warehouse_ids" class="form-control select2" multiple data-placeholder="Select Warehouse(s)">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ in_array($wh->id, $selectedWarehouseIds ?? []) ? 'selected' : '' }}>
                                        {{ $wh->name }}{{ $wh->order_priority ? ' (P' . $wh->order_priority . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Design No</label>
                            <select name="design_number" class="form-control select2">
                                <option value="">All Designs</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design }}" {{ request('design_number') == $design ? 'selected' : '' }}>{{ $design }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Product Name</label>
                            <select name="product_name" class="form-control select2">
                                <option value="">All Products</option>
                                @foreach($product_names as $name)
                                    <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Color</label>
                            <select name="color_name" class="form-control select2">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Size Set</label>
                            <select name="size_set_name" class="form-control select2">
                                <option value="">All Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>{{ $set }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Pattern</label>
                            <select name="pattern_id" class="form-control select2">
                                <option value="">All Patterns</option>
                                @foreach($patterns as $id => $name)
                                    <option value="{{ $id }}" {{ request('pattern_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Fitting</label>
                            <select name="fitting_id" class="form-control select2">
                                <option value="">All Fittings</option>
                                @foreach($fittings as $id => $name)
                                    <option value="{{ $id }}" {{ request('fitting_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Product Nature</label>
                            <select name="product_nature_id" class="form-control select2">
                                <option value="">All Natures</option>
                                @foreach($product_natures as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_nature_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Fabric Type</label>
                            <select name="fabric_type_id" class="form-control select2">
                                <option value="">All Fabrics</option>
                                @foreach($fabric_types as $id => $name)
                                    <option value="{{ $id }}" {{ request('fabric_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Brand</label>
                            <select name="brand_id" id="brand_id" class="form-control select2">
                                <option value="">All Brands</option>
                                @foreach($brands as $id => $name)
                                    <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">MRP From</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text py-0">₹</span></div>
                                <input type="number" name="mrp_from" id="mrp_from" class="form-control" placeholder="Min" min="0" step="any" value="{{ request('mrp_from') }}">
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">MRP To</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text py-0">₹</span></div>
                                <input type="number" name="mrp_to" id="mrp_to" class="form-control" placeholder="Max" min="0" step="any" value="{{ request('mrp_to') }}">
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Min Boxes</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text py-0"><i class="fas fa-box small"></i></span></div>
                                <input type="number" name="min_boxes" id="min_boxes" class="form-control" placeholder="Min Boxes" min="0" value="{{ request('min_boxes') }}">
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-2">
                            <label class="erp-label">Max Boxes</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend"><span class="input-group-text py-0"><i class="fas fa-boxes small"></i></span></div>
                                <input type="number" name="max_boxes" id="max_boxes" class="form-control" placeholder="Max Boxes" min="0" value="{{ request('max_boxes') }}">
                            </div>
                        </div>
                        <div class="col-auto mb-2 ml-auto">
                            <div class="erp-filter-actions">
                                <button type="submit" class="btn-erp btn-erp-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.agent-orders.create', ['order_type' => request('order_type'), 'sale_type' => request('sale_type'), 'party_type' => request('party_type', 'customer'), 'sales_agent_id' => $agent->id, 'sales_man_id' => request('sales_man_id'), 'master_customer_id' => request('master_customer_id'), 'master_vendor_id' => request('master_vendor_id'), 'order_date' => request('order_date')]) }}"
                                   class="btn-erp btn-erp-outline" title="Reset Filters">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- INVENTORY TABLE CARD -->
            <div class="erp-card" style="margin-bottom: 75px !important;">
                <div class="erp-card-header flex-wrap">
                    <div class="d-flex align-items-center">
                        <h6 class="erp-card-title mr-3"><i class="fas fa-boxes"></i> Available Inventory</h6>
                        <span class="erp-stat-pill" id="variationsCount">{{ $boxes->total() }} Variations Available</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn-erp btn-erp-primary open-checkout-modal-btn" id="cardCheckoutBtn" style="display: none;">
                            <i class="fas fa-shopping-cart mr-1"></i> Next: Review Order (<span id="cardBoxesCount">0</span> Boxes • ₹<span id="cardGrandTotal">0</span>) <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
                <div class="erp-card-body p-0">
                    <div class="table-responsive">
                        <table class="erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="80" class="text-center">Image</th>
                                    <th>Product Details</th>
                                    <th>Size Set</th>
                                    <th class="text-center">Pcs/Box</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-right">Price ({{ request('order_type') == 'direct' ? 'Customer' : 'Agent' }})</th>
                                    <th width="150" class="text-center px-4">Order Qty (Boxes)</th>
                                </tr>
                            </thead>
                            <tbody id="variation-container">
                                @forelse($boxes as $variation)
                                    @php
                                        $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                                        $image = $boxImages[$vKey] ?? null;
                                    @endphp
                                    @include('admin.agent_orders.partials.variation_row', ['variation' => $variation, 'vKey' => $vKey, 'image' => $image])
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No inventory found for selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="load-more-sentinel" style="height: 20px;"></div>
                <div id="loading-spinner" class="text-center py-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Floating Bottom Summary Bar -->
            <div class="fixed-bottom bg-white border-top py-2 px-3 animate__animated animate__fadeInUp"
                 id="summaryBar" style="z-index: 1040; left: 250px; display: none;">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="mr-3">
                            <span class="erp-sum-label mr-1">Selected:</span>
                            <span class="badge badge-primary px-2 py-1" style="font-size: 13px;">
                                <span id="selectedCount">0</span> Boxes
                            </span>
                        </div>
                        <div class="mr-3 border-left pl-3 d-none d-sm-inline-block">
                            <span class="erp-sum-label mr-1">Subtotal:</span>
                            <span class="font-weight-bold">₹<span id="bottomSubTotal">0.00</span></span>
                        </div>
                        <div class="border-left pl-3">
                            <span class="erp-sum-label mr-1">Est. Grand Total:</span>
                            <span class="erp-sum-total">₹<span id="bottomGrandTotal">0.00</span></span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn-erp btn-erp-primary open-checkout-modal-btn">
                            Next: Review &amp; Place Order <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Review & Checkout Modal -->
            <div class="modal fade erp-modal" id="orderCheckoutModal" tabindex="-1" role="dialog" aria-labelledby="orderCheckoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 920px;">
                    <div class="modal-content border-0">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title mb-0" id="orderCheckoutModalLabel">
                                    <i class="fas fa-clipboard-check mr-2"></i> Review &amp; Finalize {{ request('order_type') == 'direct' ? 'Direct' : 'Sales' }} Order
                                </h5>
                                <small class="erp-modal-sub">
                                    Party: <strong>{{ $shop->name }}</strong> ({{ ucfirst(request('party_type', 'customer')) }}) &nbsp;•&nbsp;
                                    {{ $agent->id === 'direct' ? 'Direct Sale (No Agent)' : 'Agent: ' . $agent->name }}
                                </small>
                            </div>
                            <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body p-3">
                            <div class="row">
                                <!-- Left: Selected Items Breakdown -->
                                <div class="col-md-5 mb-3 mb-md-0 border-right">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="erp-section-title"><i class="fas fa-boxes mr-1"></i> Selected Variations</span>
                                        <span class="erp-stat-pill" id="modalItemsCount">0 Items</span>
                                    </div>
                                    <div id="modalSelectedItemsList" class="erp-soft-box p-2" style="max-height: 250px; overflow-y: auto;">
                                        <!-- Dynamically populated -->
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                        <span class="erp-sum-label">Total Selected Boxes:</span>
                                        <span class="badge badge-primary px-2 py-1" style="font-size: 13px;"><span id="modalTotalBoxes">0</span> Boxes</span>
                                    </div>
                                </div>

                                <!-- Right: Pricing & Tax Breakdown -->
                                <div class="col-md-7">
                                    <span class="erp-section-title d-block mb-2"><i class="fas fa-calculator mr-1"></i> Financial Summary</span>
                                    <div class="erp-soft-box p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="erp-sum-label">Subtotal:</span>
                                            <span class="font-weight-bold h6 mb-0">₹<span id="subTotalAmount">0.00</span></span>
                                        </div>

                                        <div class="row align-items-center mb-2">
                                            <div class="col-6">
                                                <div class="input-group input-group-sm" title="Discount Percentage">
                                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2 font-weight-bold" style="font-size: 11px;">Disc %</span></div>
                                                    <input type="number" id="discountPercentage" class="form-control text-right" style="font-weight: bold;" value="0" min="0" max="100" step="any">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm" title="Discount Amount">
                                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2 font-weight-bold" style="font-size: 11px;">Disc ₹</span></div>
                                                    <input type="number" id="discountAmountInput" class="form-control text-right" style="font-weight: bold;" value="0" min="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-2">
                                            <span class="erp-sum-label">Taxable Amount:</span>
                                            <span class="font-weight-bold h6 mb-0">₹<span id="taxableAmount">0.00</span></span>
                                        </div>

                                        <div class="row align-items-center mb-2">
                                            <div class="col-6">
                                                <div class="input-group input-group-sm" title="GST Percentage">
                                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2 font-weight-bold" style="font-size: 11px;">GST %</span></div>
                                                    <input type="number" id="gstPercentage" class="form-control text-right" style="font-weight: bold;" value="{{ $gst_percentage }}" min="0" max="100" step="any">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm" title="GST Amount">
                                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2 font-weight-bold" style="font-size: 11px;">GST ₹</span></div>
                                                    <input type="number" id="gstAmountInput" class="form-control text-right" style="font-weight: bold;" value="0" min="0">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-3">
                                            <div class="col-6"><span class="erp-sum-label">Other Charges:</span></div>
                                            <div class="col-6">
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend"><span class="input-group-text py-0 px-2">₹</span></div>
                                                    <input type="number" id="other_charges" class="form-control text-right font-weight-bold" value="0" min="0" step="1">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Grand Total Box -->
                                        <div class="erp-grand-box text-center">
                                            <small class="text-uppercase font-weight-bold d-block">Grand Total</small>
                                            <span class="erp-grand-val">₹<span id="grandTotalAmount">0.00</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logistics & Dispatch Details -->
                            <div class="border-top pt-3 mt-3">
                                <span class="erp-section-title d-block mb-2"><i class="fas fa-truck mr-1"></i> Order &amp; Logistics Details</span>
                                <div class="row">
                                    @if(request('order_type') != 'direct')
                                        <div class="col-md-3 col-6 mb-2">
                                            <label class="erp-label">Expected Dispatch</label>
                                            <input type="date" id="expectedDispatchDate" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                                        </div>
                                        <div class="col-md-3 col-6 mb-2">
                                            <label class="erp-label">Status</label>
                                            <select id="orderStatus" class="form-control form-control-sm">
                                                <option value="pending" selected>PENDING</option>
                                                <option value="delayed">DELAYED</option>
                                                <option value="dispatched">DISPATCHED</option>
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-md-3 col-6 mb-2">
                                        <label class="erp-label">Booking Station</label>
                                        <input type="text" id="booking_station" class="form-control form-control-sm" placeholder="e.g. Surat">
                                    </div>
                                    <div class="col-md-3 col-6 mb-2">
                                        <label class="erp-label">Transport</label>
                                        <input type="text" id="transport" class="form-control form-control-sm" placeholder="Transport Name">
                                    </div>
                                    <div class="col-12 mb-1">
                                        <label class="erp-label">Order Remark / Notes</label>
                                        @php
                                            $previousRemarks = \DB::table('agent_orders')->whereNotNull('remark')->where('remark', '!=', '')->distinct()->pluck('remark');
                                        @endphp
                                        <input type="text" id="remark" class="form-control form-control-sm" list="previous_remarks_list" placeholder="Enter notes or remarks..." autocomplete="off">
                                        <datalist id="previous_remarks_list">
                                            @foreach($previousRemarks as $rem)
                                                <option value="{{ $rem }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Inventory
                            </button>
                            <button type="button" class="btn-erp btn-erp-primary px-4 place-order-btn">
                                {{ request('order_type') == 'direct' ? 'Deduct Stock & Bill' : 'Place Order' }} <i class="fas fa-check-circle ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    /* ---- Agent Order Create: brand-themed (uses master brand variables from admin_assets/style.css) ---- */
    .erp-page .erp-party-name { color: var(--erp-green-primary); }
    .erp-page .erp-link { color: var(--erp-green-primary); font-weight: 600; font-size: 12px; }
    .erp-page .erp-link:hover { color: var(--brand-wordmark-green, #36b54a); text-decoration: none; }
    .erp-page .erp-switch-box {
        border: 1px solid var(--erp-border); border-left: 4px solid var(--brand-yellow, #fcee21);
        border-radius: 6px; padding: 10px 12px 10px 2.75rem; background: var(--erp-yellow-bg);
    }
    .erp-page .erp-switch-box label { color: var(--erp-green-primary); }
    .erp-page .erp-section-title {
        font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--erp-green-primary);
    }
    .erp-page .erp-sum-label { font-size: 12px; font-weight: 700; color: var(--erp-text-subtle); }
    .erp-page .erp-sum-total { font-size: 18px; font-weight: 800; color: var(--erp-green-primary); }
    .erp-page .erp-soft-box { background: var(--erp-green-light); border: 1px solid var(--erp-green-border); border-radius: 6px; }
    .erp-page .erp-grand-box {
        background: var(--erp-green-primary); color: #fff; border-radius: 6px; padding: 8px;
        border-bottom: 4px solid var(--brand-yellow, #fcee21);
    }
    .erp-page .erp-grand-box small { color: rgba(255,255,255,.75); letter-spacing: .5px; }
    .erp-page .erp-grand-val { font-size: 24px; font-weight: 800; color: var(--brand-yellow, #fcee21); }
    .erp-page .input-group-text {
        background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid #ced4da; font-size: 12px; font-weight: 700;
    }
    .erp-page .input-group-sm .form-control { height: 33px !important; }

    /* Modal header / footer */
    #orderCheckoutModal .modal-content { border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(5,66,28,.25); }
    #orderCheckoutModal .modal-header {
        background: var(--erp-green-primary); color: #fff; padding: 10px 16px;
        border-bottom: 4px solid var(--brand-yellow, #fcee21); align-items: center;
    }
    #orderCheckoutModal .modal-title { font-size: 15px; font-weight: 700; color: #fff; }
    #orderCheckoutModal .erp-modal-sub, #orderCheckoutModal .erp-modal-sub strong { color: rgba(255,255,255,.85); }
    #orderCheckoutModal .modal-header .close { color: #fff; opacity: .9; text-shadow: none; }
    #orderCheckoutModal .modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 8px 16px; }
    #orderCheckoutModal .form-control { height: 33px; font-size: 13px; border-radius: 4px; border: 1px solid #ced4da; }
    #orderCheckoutModal .form-control:focus { border-color: var(--erp-green-primary); box-shadow: 0 0 0 2px rgba(5,66,28,.15); }

    /* Inventory rows */
    .variation-row { transition: background .2s; }
    .variation-row:hover { background-color: var(--erp-green-light); }
    .variation-row.has-qty, .variation-row.has-qty:hover { background-color: #dff1c8 !important; background-color: color-mix(in srgb, var(--brand-light-green) 28%, #ffffff) !important; box-shadow: inset 5px 0 0 var(--brand-wordmark-green, #36b54a); }
    .variation-row.has-qty td { border-color: color-mix(in srgb, var(--brand-light-green) 55%, #ffffff) !important; }
    .erp-table td { vertical-align: middle !important; }
    .quantity-control { max-width: 140px; margin: 0 auto; border-radius: 4px; overflow: hidden; }
    .quantity-control input { border-left: 0; border-right: 0; font-weight: bold; text-align: center; }
    .quantity-control .btn { border: 1px solid #ced4da; background: #fff; color: var(--erp-green-primary); font-weight: 700; }
    .quantity-control .btn:hover { background: var(--erp-green-light); border-color: var(--erp-green-primary); color: var(--erp-green-primary); }
    .quantity-control .btn-plus { background: var(--erp-green-primary); color: #fff; border-color: var(--erp-green-primary); }
    .quantity-control .btn-plus:hover { background: var(--erp-green-hover); color: #fff; }

    /* Summary bar */
    .fixed-bottom { transition: left .3s; }
    #summaryBar { border-top: 3px solid var(--brand-yellow, #fcee21) !important; box-shadow: 0 -4px 15px rgba(5,66,28,.12) !important; }
    #modalSelectedItemsList::-webkit-scrollbar { width: 5px; }
    #modalSelectedItemsList::-webkit-scrollbar-thumb { background: var(--erp-green-border); border-radius: 4px; }
    #topCheckoutBtn.btn-erp-primary { background-color: var(--erp-green-primary) !important; color: #fff !important; }
    @media (max-width: 991px) { .fixed-bottom { left: 0 !important; } }

    /* ---- Step 1 form (scoped; overrides global body.km-admin form styles) ---- */
    .erp-page .ao-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 4px 18px rgba(5,66,28,.07); overflow:hidden; }
    .erp-page .ao-card-head { padding:18px 24px 14px; background:linear-gradient(135deg,var(--erp-green-light),#fff); border-bottom:1px solid #e2e8f0; border-top:4px solid var(--brand-yellow,#fcee21); }
    .erp-page .ao-card-head h6 { margin:6px 0 2px; font-size:17px; font-weight:800; color:var(--erp-green-primary); }
    .erp-page .ao-card-head h6 i { margin-right:8px; color:var(--brand-wordmark-green,#36b54a); }
    .erp-page .ao-card-head p { margin:0; font-size:13px; color:#64748b !important; }
    .erp-page .ao-step { display:inline-block; font-size:10.5px; font-weight:800; letter-spacing:.8px; text-transform:uppercase; background:var(--brand-yellow,#fcee21); color:var(--erp-green-primary); padding:2px 9px; border-radius:20px; }

    .erp-page .ao-section { padding:18px 24px; border-bottom:1px solid #eef2f6; }
    .erp-page .ao-section-title { font-size:11.5px; font-weight:800; letter-spacing:.7px; text-transform:uppercase; color:var(--erp-green-primary); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
    .erp-page .ao-section-title i { width:24px; height:24px; border-radius:6px; background:var(--erp-green-light); color:var(--erp-green-primary); display:inline-flex; align-items:center; justify-content:center; font-size:12px; }
    .erp-page .ao-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 16px; }
    @media (max-width:575px){ .erp-page .ao-grid{ grid-template-columns:1fr; } }
    .erp-page .ao-field { margin-bottom:14px; }
    .erp-page .ao-grid .ao-field { margin-bottom:0; }
    .erp-page .ao-label { display:flex; justify-content:space-between; align-items:center; width:100%; font-size:12.5px; font-weight:700; color:#334155; margin-bottom:5px; }
    .erp-page .ao-label .req { color:#dc2626; margin-left:2px; }
    .erp-page .ao-links { display:inline-flex; gap:10px; align-items:center; }
    .erp-page .ao-links a { font-size:12px; font-weight:700; color:var(--erp-green-primary); }
    .erp-page .ao-links a:hover { color:var(--brand-wordmark-green,#36b54a); text-decoration:none; }

    /* Controls: one consistent look for native inputs/selects and select2 */
    .erp-page .ao-form .form-control,
    .erp-page .ao-form .select2-container--default .select2-selection--single,
    .erp-page .ao-form .select2-container--bootstrap4 .select2-selection--single {
        height:40px !important; min-height:40px !important; border:1px solid #cbd5e1 !important; border-radius:6px !important;
        font-size:14px !important; color:#1e293b !important; background-color:#fff; padding:0 12px !important; box-shadow:none;
    }
    .erp-page .ao-form select.form-control { padding-right:30px !important; }
    .erp-page .ao-form .select2-container { width:100% !important; }
    .erp-page .ao-form .select2-selection--single .select2-selection__rendered { line-height:38px !important; padding:0 !important; color:#1e293b !important; font-weight:500; }
    .erp-page .ao-form .select2-selection--single .select2-selection__arrow { height:38px !important; top:0 !important; right:8px !important; }
    .erp-page .ao-form .form-control:focus,
    .erp-page .ao-form .select2-container--open .select2-selection--single,
    .erp-page .ao-form .select2-container--focus .select2-selection--single {
        border-color:var(--erp-green-primary) !important; box-shadow:0 0 0 3px rgba(5,66,28,.12) !important; outline:none;
    }

    /* Segmented Customer / Vendor toggle */
    .erp-page .ao-seg { display:inline-flex; border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; background:#f8fafc; }
    .erp-page .ao-seg input { position:absolute; opacity:0; pointer-events:none; }
    .erp-page .ao-seg label { margin:0; padding:9px 22px; font-size:13.5px; font-weight:700; color:#475569; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .15s; user-select:none; }
    .erp-page .ao-seg label + input + label { border-left:1px solid #cbd5e1; }
    .erp-page .ao-seg input:checked + label { background:var(--erp-green-primary); color:#fff; }
    .erp-page .ao-seg input:checked + label i { color:var(--brand-yellow,#fcee21); }
    .erp-page .ao-seg input:focus-visible + label { outline:2px solid var(--brand-yellow,#fcee21); outline-offset:-2px; }

    /* Sample switch aligned with other fields */
    .erp-page .ao-switch { height:40px; display:flex; align-items:center; padding-left:3rem; border:1px dashed #cbd5e1; border-radius:6px; background:var(--erp-yellow-bg); }
    .erp-page .ao-switch label { font-size:13px; font-weight:700; color:var(--erp-green-primary); cursor:pointer; margin:0; user-select:none; line-height:1.9rem; }

    .erp-page .ao-actions { display:flex; justify-content:space-between; align-items:center; padding:14px 24px; background:#f8fafc; }
    .erp-page .ao-actions .btn-erp { height:38px; padding:0 18px !important; font-size:13.5px !important; }

    /* ---- simplified full-width layout: two fields per row ---- */
    .erp-page .ao-card-head { display:none; }
    .erp-page .ao-card { border-top:4px solid var(--brand-yellow,#fcee21); }
    .erp-page .ao-body { padding:20px 24px 8px; }
    .erp-page .ao-body .ao-grid { gap:16px 24px; align-items:start; }
    .erp-page .ao-body .ao-field { margin-bottom:0; }
    .erp-page .ao-body .ao-field > .ao-seg { display:flex; width:100%; height:40px; }
    .erp-page .ao-body .ao-seg input + label { flex:1; justify-content:center; padding:0 12px; }
    .erp-page .ao-actions { border-top:1px solid #eef2f6; margin-top:12px; }
    .erp-page .ao-switch { background:var(--erp-yellow-bg); padding-right:14px; }

    /* ---- compact inventory rows (more products per screen) ---- */
    #variation-container td { padding: 3px 8px !important; line-height: 1.25; }
    #variation-container td img, #variation-container td > div.bg-light { width: 34px !important; height: 34px !important; }
    #variation-container td:nth-child(2) > div { display: inline-flex !important; align-items: center; vertical-align: middle; margin-right: 8px; font-size: 13px; }
    #variation-container td:nth-child(2) > div.small { font-size: 12px; }
    #variation-container td:nth-child(6) > div { display: inline; font-size: 13.5px; }
    #variation-container td:nth-child(6) small { display: inline; margin-left: 6px; font-size: 11px; }
    #variation-container .quantity-control { max-width: 120px; }
    #variation-container .quantity-control .btn,
    #variation-container .quantity-control input { height: 28px !important; padding-top: 0; padding-bottom: 0; line-height: 26px; font-size: 13px !important; }
    #variation-container .badge-erp-status { padding: 1px 8px; font-size: 11px; }
    .erp-table thead th { padding: 6px 10px !important; }

    /* Customer / Vendor toggle: label colours must beat the generic form-label rule */
    body.km-admin .erp-page .ao-seg label { color: #475569 !important; font-size: 13.5px; margin: 0; }
    body.km-admin .erp-page .ao-seg input:checked + label { color: #ffffff !important; background: var(--erp-green-primary); }
    body.km-admin .erp-page .ao-seg input:checked + label i { color: var(--brand-yellow, #fcee21) !important; }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }

        // Refresh Customer
        $('#refreshCustomerBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.sales_order.all_customers') }}", function(data) {
                var select = $('#customerSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">-- Choose Customer --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        // Refresh Vendor
        $('#refreshVendorBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.purchase_order.all_vendors') }}", function(data) {
                var select = $('#vendorSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">-- Choose Vendor --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        // Refresh Sales Man
        $('#refreshSalesManBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.master.sales-man.all_sales_men') }}", function(data) {
                var select = $('#salesManSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">-- Optional: Choose Sales Man --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        // Agent selection removed - automatically determined based on customer and order type
        $('input[name="party_type"]').change(function() {
            if (this.value === 'customer') {
                $('#customerWrapper').show();
                $('#vendorWrapper').hide();
                $('#customerSelect').attr('required', true);
                $('#vendorSelect').attr('required', false);
            } else {
                $('#customerWrapper').hide();
                $('#vendorWrapper').show();
                $('#customerSelect').attr('required', false);
                $('#vendorSelect').attr('required', true);
            }
        });

        @if(isset($boxes))
            let discount_mode = 'percentage'; // 'percentage' or 'amount'
            let gst_mode = 'percentage';
            let cart = new Map();
            const storageKey = 'admin_order_cart_{{ $agent->id }}_{{ $shop->id }}';
            
            // Sync with local storage to handle pagination and app restarts
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(key => cart.set(key, data[key]));
            }

            // --- INFINITE SCROLL & REAL-TIME FILTER START ---
            let nextPage = {{ $boxes->nextPageUrl() ? ($boxes->currentPage() + 1) : 'null' }};
            let loading = false;
            const container = $('#variation-container');
            const scrollContainer = $('.content-wrapper');

            container.css('min-height', '400px'); // Prevent page collapse

            scrollContainer.on('scroll', function() {
                if (loading || !nextPage) return;
                
                // Trigger when 80% through the scrollable height
                let scrollTop = scrollContainer.scrollTop();
                let innerHeight = scrollContainer.innerHeight();
                let scrollHeight = scrollContainer[0].scrollHeight;
                
                if (scrollTop + innerHeight >= scrollHeight - 300) {
                    loadMore();
                }
            });

            function loadMore(reset = false) {
                if (loading) return;
                loading = true;
                $('#loading-spinner').show();
                
                if (reset) {
                    nextPage = 1;
                    container.css('opacity', '0.5'); // Visual feedback without collapse
                }

                let formData = $('#filterForm').serialize();
                let requestData = formData + '&load_more=1&page=' + nextPage;
                
                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: requestData,
                    success: function(response) {
                        if (reset) {
                            container.empty().css('opacity', '1');
                        }
                        
                        // Append and filter out potential duplicates if needed, but append is standard
                        container.append(response.html);
                        nextPage = response.next_page;
                        
                        if (response.total_count !== undefined) {
                            $('#variationsCount').text(response.total_count + ' Variations Available');
                        }

                        loading = false;
                        $('#loading-spinner').hide();
                        updateUI();
                        
                        if (container.is(':empty') && !response.html) {
                             container.append('<tr><td colspan="7" class="text-center py-5 text-muted">No inventory found for selected filters.</td></tr>');
                        }
                    },
                    error: function() {
                        loading = false;
                        container.css('opacity', '1');
                        $('#loading-spinner').hide();
                    }
                });
            }

            // Real-time filter triggers
            $('#filterForm select').on('change', function() {
                // If this is triggered by Select2 initialization, ignore it if possible
                // But generally fine to reset on change
                loadMore(true);
            });

            let filterInputTimer;
            $('#mrp_from, #mrp_to, #min_boxes, #max_boxes').on('input change', function() {
                clearTimeout(filterInputTimer);
                filterInputTimer = setTimeout(function() {
                    loadMore(true);
                }, 400);
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadMore(true);
            });
            // --- INFINITE SCROLL & REAL-TIME FILTER END ---


            function updateUI() {
                let totalBoxes = 0;
                let subTotal = 0;

                cart.forEach((item) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                subTotal = Math.ceil(subTotal);

                const otherCharges = Math.ceil(parseFloat($('#other_charges').val()) || 0);
                let discountAmount = 0;
                let discountPercent = parseFloat($('#discountPercentage').val()) || 0;

                if (discount_mode === 'amount') {
                    discountAmount = Math.ceil(parseFloat($('#discountAmountInput').val()) || 0);
                    // update percentage silently if not focused
                    if (!$('#discountPercentage').is(':focus') && subTotal > 0) {
                        $('#discountPercentage').val((discountAmount / subTotal * 100).toFixed(6));
                    }
                } else {
                    discountAmount = Math.ceil(subTotal * (discountPercent / 100));
                    if (!$('#discountAmountInput').is(':focus')) {
                        $('#discountAmountInput').val(discountAmount);
                    }
                }

                const taxableAmount = Math.ceil(subTotal - discountAmount);
                let gstAmount = 0;
                let gstPercent = parseFloat($('#gstPercentage').val()) || 0;

                if (gst_mode === 'amount') {
                    gstAmount = Math.ceil(parseFloat($('#gstAmountInput').val()) || 0);
                    if (!$('#gstPercentage').is(':focus') && taxableAmount > 0) {
                        $('#gstPercentage').val((gstAmount / taxableAmount * 100).toFixed(6));
                    }
                } else {
                    gstAmount = Math.ceil(taxableAmount * (gstPercent / 100));
                    if (!$('#gstAmountInput').is(':focus')) {
                        $('#gstAmountInput').val(gstAmount);
                    }
                }

                const grandTotal = Math.ceil(taxableAmount + gstAmount + otherCharges);

                const formattedSubTotal = subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const formattedTaxable = taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                const formattedGrandTotal = grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });

                // Update counts & amounts across page and modal
                $('#selectedCount, #modalTotalBoxes').text(totalBoxes);
                $('#subTotalAmount, #bottomSubTotal').text(formattedSubTotal);
                $('#taxableAmount').text(formattedTaxable);
                $('#grandTotalAmount, #bottomGrandTotal, #cardGrandTotal').text(formattedGrandTotal);

                $('#topBoxesBadge').text(totalBoxes + (totalBoxes === 1 ? ' Box' : ' Boxes'));
                $('#cardBoxesCount').text(totalBoxes);

                if (totalBoxes > 0) {
                    $('#topCheckoutBtn').removeClass('btn-erp-outline').addClass('btn-erp-primary');
                    $('#cardCheckoutBtn').fadeIn(200);
                    $('#summaryBar').fadeIn(200);
                } else {
                    $('#topCheckoutBtn').removeClass('btn-erp-primary').addClass('btn-erp-outline');
                    $('#cardCheckoutBtn').fadeOut(200);
                    $('#summaryBar').fadeOut(200);
                }

                $('.variation-row').each(function() {
                    const key = $(this).data('key');
                    const item = cart.get(key);
                    const input = $(this).find('.box-qty-input');
                    if (item && item.qty > 0) {
                        input.val(item.qty);
                        $(this).addClass('has-qty');
                    } else {
                        input.val(0);
                        $(this).removeClass('has-qty');
                    }
                });

                // Persistence
                const storageObj = {};
                cart.forEach((val, key) => { if (val.qty > 0) storageObj[key] = val; });
                localStorage.setItem(storageKey, JSON.stringify(storageObj));
            }

            $(document).on('change', '.box-qty-input', function() {
                const row = $(this).closest('.variation-row');
                const key = row.data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0;
                if (qty > max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' available.', 'warning');
                    qty = max;
                    $(this).val(qty);
                }

                if (qty > 0) {
                    const designNumber = row.data('design') || row.find('td:nth-child(2) .font-weight-bold').first().text().trim();
                    const garmentName = row.data('garment') || row.find('td:nth-child(2) .text-primary').first().text().trim();
                    const colorName = row.data('color') || row.find('td:nth-child(2) .text-muted.small').first().text().replace(/[\r\n\t]+/g, ' ').replace('Color:', '').trim();
                    const sizeName = row.data('size') || row.find('td:nth-child(3) .badge').first().text().trim();

                    cart.set(key, {
                        product_id: row.data('product-id'),
                        color_id: row.data('color-id'),
                        size_set_id: row.data('size-set-id'),
                        qty: qty,
                        pcs_per_box: parseFloat(row.data('pcs')),
                        unit_price: parseFloat(row.data('price')),
                        design: designNumber,
                        garment: garmentName,
                        color: colorName,
                        size: sizeName,
                        max: isNaN(max) ? null : max
                    });
                } else {
                    cart.delete(key);
                }
                updateUI();
            });

            function renderModalItems() {
                let itemsHtml = '';
                let count = 0;
                cart.forEach((item, key) => {
                    if (item.qty > 0) {
                        count++;
                        // If metadata is missing from saved cart (e.g. from previous localstorage), attempt recovery from DOM
                        if (!item.design || !item.color) {
                            const row = $(`.variation-row[data-key="${key}"]`);
                            if (row.length) {
                                item.design = row.data('design') || row.find('td:nth-child(2) .font-weight-bold').first().text().trim();
                                item.garment = row.data('garment') || row.find('td:nth-child(2) .text-primary').first().text().trim();
                                item.color = row.data('color') || row.find('td:nth-child(2) .text-muted.small').first().text().replace(/[\r\n\t]+/g, ' ').replace('Color:', '').trim();
                                item.size = row.data('size') || row.find('td:nth-child(3) .badge').first().text().trim();
                                const rInput = row.find('.box-qty-input');
                                if (rInput.length && rInput.attr('max')) {
                                    item.max = parseInt(rInput.attr('max'));
                                }
                                cart.set(key, item);
                            }
                        }

                        const designText = item.design || ('Design #' + item.product_id);
                        const garmentText = item.garment || '';
                        const colorText = item.color || '';
                        const sizeText = item.size || '';
                        const maxAttr = (item.max !== null && item.max !== undefined && !isNaN(item.max)) ? `max="${item.max}"` : '';
                        const lineTotal = item.qty * item.pcs_per_box * item.unit_price;

                        itemsHtml += `
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom modal-item-row" id="modal-item-${key}" data-key="${key}">
                                <div style="line-height: 1.25; max-width: 58%;">
                                    <div class="font-weight-bold text-dark mb-0">
                                        ${designText} 
                                        ${sizeText ? `<span class="badge badge-light border text-muted ml-1">${sizeText}</span>` : ''}
                                    </div>
                                    <small class="text-muted d-block text-truncate">${garmentText} ${colorText ? `• ${colorText}` : ''}</small>
                                    <small class="text-secondary">${item.pcs_per_box} pcs/box @ ₹${parseFloat(item.unit_price).toFixed(2)}</small>
                                </div>
                                <div class="text-right ml-2" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-end mb-1">
                                        <div class="input-group input-group-sm" style="width: 105px;">
                                            <div class="input-group-prepend">
                                                <button type="button" class="btn btn-outline-danger btn-modal-minus py-0 px-2 font-weight-bold" data-key="${key}" title="Decrease quantity" style="font-size: 13px; line-height: 1.4;">-</button>
                                            </div>
                                            <input type="number" class="form-control text-center py-0 px-1 font-weight-bold modal-qty-input" 
                                                value="${item.qty}" min="0" ${maxAttr} data-key="${key}" style="height: 28px; font-size: 13px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-success btn-modal-plus py-0 px-2 font-weight-bold" data-key="${key}" title="Increase quantity" style="font-size: 13px; line-height: 1.4;">+</button>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-link text-danger p-0 ml-2 btn-modal-remove" data-key="${key}" title="Remove variation">
                                            <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                        </button>
                                    </div>
                                    <div class="font-weight-bold text-dark small">
                                        <span class="text-muted font-weight-normal mr-1" style="font-size: 11px;">Total:</span>₹<span id="modal-item-total-${key}">${Math.ceil(lineTotal).toLocaleString('en-IN')}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                if (count === 0) {
                    itemsHtml = '<div class="text-center text-muted py-4"><i class="fas fa-boxes fa-2x mb-2 d-block opacity-50"></i>No variations selected yet.</div>';
                }

                $('#modalSelectedItemsList').html(itemsHtml);
                $('#modalItemsCount').text(count + (count === 1 ? ' Item' : ' Items'));
            }

            // Modal Plus Button
            $(document).on('click', '.btn-modal-plus', function() {
                const key = $(this).data('key');
                const item = cart.get(key);
                if (!item) return;

                let max = item.max;
                if (max === undefined || max === null) {
                    const rowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (rowInput.length && rowInput.attr('max')) {
                        max = parseInt(rowInput.attr('max'));
                        item.max = max;
                    }
                }

                if (max !== null && max !== undefined && !isNaN(max) && item.qty >= max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' available.', 'warning');
                    return;
                }

                item.qty += 1;
                cart.set(key, item);

                $(`#modal-item-${key} .modal-qty-input`).val(item.qty);
                const lineTotal = item.qty * item.pcs_per_box * item.unit_price;
                $(`#modal-item-total-${key}`).text(Math.ceil(lineTotal).toLocaleString('en-IN'));

                const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                if (bgRowInput.length) {
                    bgRowInput.val(item.qty);
                    bgRowInput.closest('.variation-row').addClass('has-qty');
                }

                updateUI();
            });

            // Modal Minus Button
            $(document).on('click', '.btn-modal-minus', function() {
                const key = $(this).data('key');
                const item = cart.get(key);
                if (!item) return;

                const newQty = item.qty - 1;
                if (newQty <= 0) {
                    cart.delete(key);
                    $(`#modal-item-${key}`).fadeOut(200, function() {
                        $(this).remove();
                        let remainingItems = 0;
                        cart.forEach(it => { if (it.qty > 0) remainingItems++; });
                        $('#modalItemsCount').text(remainingItems + (remainingItems === 1 ? ' Item' : ' Items'));
                        if (remainingItems === 0) {
                            $('#modalSelectedItemsList').html('<div class="text-center text-muted py-4"><i class="fas fa-boxes fa-2x mb-2 d-block opacity-50"></i>No variations selected yet.</div>');
                        }
                    });

                    const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (bgRowInput.length) {
                        bgRowInput.val(0);
                        bgRowInput.closest('.variation-row').removeClass('has-qty');
                    }
                } else {
                    item.qty = newQty;
                    cart.set(key, item);

                    $(`#modal-item-${key} .modal-qty-input`).val(item.qty);
                    const lineTotal = item.qty * item.pcs_per_box * item.unit_price;
                    $(`#modal-item-total-${key}`).text(Math.ceil(lineTotal).toLocaleString('en-IN'));

                    const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (bgRowInput.length) {
                        bgRowInput.val(item.qty);
                    }
                }

                updateUI();
            });

            // Modal Trash Remove Button
            $(document).on('click', '.btn-modal-remove', function() {
                const key = $(this).data('key');
                cart.delete(key);
                $(`#modal-item-${key}`).fadeOut(200, function() {
                    $(this).remove();
                    let remainingItems = 0;
                    cart.forEach(it => { if (it.qty > 0) remainingItems++; });
                    $('#modalItemsCount').text(remainingItems + (remainingItems === 1 ? ' Item' : ' Items'));
                    if (remainingItems === 0) {
                        $('#modalSelectedItemsList').html('<div class="text-center text-muted py-4"><i class="fas fa-boxes fa-2x mb-2 d-block opacity-50"></i>No variations selected yet.</div>');
                    }
                });

                const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                if (bgRowInput.length) {
                    bgRowInput.val(0);
                    bgRowInput.closest('.variation-row').removeClass('has-qty');
                }

                updateUI();
            });

            // Modal Direct Quantity Input
            $(document).on('change', '.modal-qty-input', function() {
                const key = $(this).data('key');
                const item = cart.get(key);
                if (!item) return;

                let qty = parseInt($(this).val()) || 0;
                let max = item.max;
                if (max === undefined || max === null) {
                    const rowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (rowInput.length && rowInput.attr('max')) {
                        max = parseInt(rowInput.attr('max'));
                        item.max = max;
                    }
                }

                if (max !== null && max !== undefined && !isNaN(max) && qty > max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' available.', 'warning');
                    qty = max;
                    $(this).val(qty);
                }

                if (qty <= 0) {
                    cart.delete(key);
                    $(`#modal-item-${key}`).fadeOut(200, function() {
                        $(this).remove();
                        let remainingItems = 0;
                        cart.forEach(it => { if (it.qty > 0) remainingItems++; });
                        $('#modalItemsCount').text(remainingItems + (remainingItems === 1 ? ' Item' : ' Items'));
                        if (remainingItems === 0) {
                            $('#modalSelectedItemsList').html('<div class="text-center text-muted py-4"><i class="fas fa-boxes fa-2x mb-2 d-block opacity-50"></i>No variations selected yet.</div>');
                        }
                    });

                    const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (bgRowInput.length) {
                        bgRowInput.val(0);
                        bgRowInput.closest('.variation-row').removeClass('has-qty');
                    }
                } else {
                    item.qty = qty;
                    cart.set(key, item);
                    const lineTotal = item.qty * item.pcs_per_box * item.unit_price;
                    $(`#modal-item-total-${key}`).text(Math.ceil(lineTotal).toLocaleString('en-IN'));

                    const bgRowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (bgRowInput.length) {
                        bgRowInput.val(item.qty);
                        bgRowInput.closest('.variation-row').addClass('has-qty');
                    }
                }

                updateUI();
            });

            $(document).on('click', '.open-checkout-modal-btn', function() {
                let totalBoxes = 0;
                cart.forEach(item => { if (item.qty > 0) totalBoxes += item.qty; });
                if (totalBoxes === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Boxes Selected',
                        text: 'Please select quantity for at least one variation before proceeding.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                renderModalItems();
                $('#orderCheckoutModal').modal('show');
            });

            $(document).on('input', '#discountPercentage', function() {
                discount_mode = 'percentage';
                updateUI();
            });

            $(document).on('input', '#discountAmountInput', function() {
                discount_mode = 'amount';
                updateUI();
            });

            $(document).on('input', '#gstPercentage', function() {
                gst_mode = 'percentage';
                updateUI();
            });

            $(document).on('input', '#gstAmountInput', function() {
                gst_mode = 'amount';
                updateUI();
            });

            $(document).on('input', '#other_charges', function() {
                updateUI();
            });

            $(document).on('click', '.btn-plus', function() {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const max = parseInt(input.attr('max'));
                const current = parseInt(input.val()) || 0;
                if (isNaN(max) || current < max) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function() {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) input.val(current - 1).trigger('change');
            });

            $('.place-order-btn').click(function() {
                const btn = $(this);
                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) return;

                Swal.fire({
                    title: 'Place Order?',
                    text: "Create a new order for {{ $shop->name }}?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Place'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Placing...');
                        $.ajax({
                            url: "{{ route('admin.agent-orders.store') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                sales_agent_id: "{{ $agent->id }}",
                                sales_man_id: "{{ request('sales_man_id') }}",
                                party_type: "{{ request('party_type', 'customer') }}",
                                master_customer_id: "{{ request('master_customer_id') }}",
                                master_vendor_id: "{{ request('master_vendor_id') }}",
                                order_date: "{{ request('order_date', date('Y-m-d')) }}",
                                order_type: "{{ request('order_type', 'normal') }}",
                                sale_type: "{{ request('sale_type', 'item') }}",
                                is_sample_set: $('#sampleSetToggle').is(':checked') ? 1 : 0,
                                variations: variations,
                                expected_dispatch_date: $('#expectedDispatchDate').val(),
                                status: $('#orderStatus').val() || null,
                                discount_percentage: $('#discountPercentage').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_percentage: $('#gstPercentage').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                other_charges: $('#other_charges').val(),
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val()
                            },
                            success: function(response) {
                                if (response.success) {
                                    localStorage.removeItem(storageKey);
                                    Swal.fire('Success!', response.message, 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    btn.prop('disabled', false).html('Place Order <i class="fas fa-check-circle ml-2"></i>');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                                btn.prop('disabled', false).html('Place Order <i class="fas fa-check-circle ml-2"></i>');
                            }
                        });
                    }
                });
            });

            updateUI();
        @endif
    });
</script>
@endpush
