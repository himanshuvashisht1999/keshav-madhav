@extends('admin.layouts.app')

@section('title', 'Edit Order #ORD-' . $order->id)

@section('content')
<div class="content-wrapper erp-page p-2 pb-5">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div style="min-width: 0;">
                <div class="erp-header-title text-truncate">
                    <i class="fas fa-edit text-primary"></i> Edit Order <span class="erp-party-name">#ORD-{{ $order->id }}</span>
                </div>
                <div class="erp-page-subtitle text-truncate">
                    <i class="fas fa-store mr-1"></i> {{ $shop->name ?? 'N/A' }}
                    &nbsp;|&nbsp; <i class="fas fa-user-tie mr-1"></i> Agent: {{ $order->agent->name ?? 'N/A' }}
                </div>
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Order
                </a>
                <button type="button" class="btn-erp btn-erp-outline open-checkout-modal-btn" id="topCheckoutBtn" title="Review selected items and update order">
                    <i class="fas fa-arrow-circle-right mr-1"></i> Review &amp; Update <span class="badge badge-primary ml-1" id="topBoxesBadge">0 Boxes</span>
                </button>
            </div>
        </div>

        <!-- Order Basic Information -->
        <div class="erp-card mb-2">
            <div class="erp-card-header">
                <h6 class="erp-card-title"><i class="fas fa-info-circle"></i> Order Basic Information</h6>
            </div>
            <div class="erp-card-body">
            <div class="form-row align-items-center">
                <div class="col-md-3 mb-2">
                    <label class="erp-label mb-1">Party type</label>
                    <div class="d-flex align-items-center mt-1">
                        <div class="custom-control custom-radio mr-4">
                            <input class="custom-control-input" type="radio" id="editTypeCustomer" name="party_type" value="customer" {{ $order->party_type == 'customer' ? 'checked' : '' }}>
                            <label for="editTypeCustomer" class="custom-control-label font-weight-normal">Customer</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="editTypeVendor" name="party_type" value="vendor" {{ $order->party_type == 'vendor' ? 'checked' : '' }}>
                            <label for="editTypeVendor" class="custom-control-label font-weight-normal">Vendor</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-2" id="editCustomerWrapper" style="{{ $order->party_type == 'customer' ? '' : 'display:none;' }}">
                    <label class="erp-label">Select customer</label>
                    <select id="editCustomerSelect" class="form-control form-control-sm erp-input select2">
                        <option value="">-- Choose Customer --</option>
                        @foreach($shops as $shop_item)
                            <option value="{{ $shop_item->id }}" {{ $order->master_customer_id == $shop_item->id ? 'selected' : '' }}>
                                {{ $shop_item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2" id="editVendorWrapper" style="{{ $order->party_type == 'vendor' ? '' : 'display:none;' }}">
                    <label class="erp-label">Select vendor</label>
                    <select id="editVendorSelect" class="form-control form-control-sm erp-input select2">
                        <option value="">-- Choose Vendor --</option>
                        @foreach($vendors as $vendor_item)
                            <option value="{{ $vendor_item->id }}" {{ $order->master_vendor_id == $vendor_item->id ? 'selected' : '' }}>
                                {{ $vendor_item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="erp-filter-bar mb-2">
            <form method="GET" action="{{ route('admin.agent-orders.edit', $order->id) }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-auto mb-2">
                        <div class="custom-control custom-switch erp-switch-box">
                            <input type="checkbox" class="custom-control-input" id="sampleSetToggle" name="sample_set" value="1" {{ (request()->has('product_name') ? request('sample_set') == '1' : $order->is_sample_set == 1) ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="sampleSetToggle" style="cursor:pointer; user-select: none;">Sample Set Price</label>
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Design no</label>
                        <select name="design_number" class="form-control form-control-sm erp-input select2">
                            <option value="">All Designs</option>
                            @foreach($designs as $design)
                                <option value="{{ $design }}" {{ request('design_number') == $design ? 'selected' : '' }}>
                                    {{ $design }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <label class="erp-label">Product name</label>
                        <select name="product_name" class="form-control form-control-sm erp-input select2">
                            <option value="">All Products</option>
                            @foreach($product_names as $name)
                                <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Color</label>
                        <select name="color_name" class="form-control form-control-sm erp-input select2">
                            <option value="">All Colors</option>
                            @foreach($colors as $color)
                                <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <label class="erp-label">Size set</label>
                        <select name="size_set_name" class="form-control form-control-sm erp-input select2">
                            <option value="">All Sets</option>
                            @foreach($size_sets as $set)
                                <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>
                                    {{ $set }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Pattern</label>
                        <select name="pattern_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All Patterns</option>
                            @foreach($patterns as $id => $name)
                                <option value="{{ $id }}" {{ request('pattern_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Fitting</label>
                        <select name="fitting_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All Fittings</option>
                            @foreach($fittings as $id => $name)
                                <option value="{{ $id }}" {{ request('fitting_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Product nature</label>
                        <select name="product_nature_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All Natures</option>
                            @foreach($product_natures as $id => $name)
                                <option value="{{ $id }}" {{ request('product_nature_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Fabric type</label>
                        <select name="fabric_type_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All Fabrics</option>
                            @foreach($fabric_types as $id => $name)
                                <option value="{{ $id }}" {{ request('fabric_type_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Brand</label>
                        <select name="brand_id" id="brand_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All Brands</option>
                            @foreach($brands as $id => $name)
                                <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">MRP from</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0">₹</span>
                            </div>
                            <input type="number" name="mrp_from" id="mrp_from" class="form-control form-control-sm erp-input" placeholder="Min" min="0" step="any" value="{{ request('mrp_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">MRP to</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0">₹</span>
                            </div>
                            <input type="number" name="mrp_to" id="mrp_to" class="form-control form-control-sm erp-input" placeholder="Max" min="0" step="any" value="{{ request('mrp_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Min boxes</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0"><i class="fas fa-box small"></i></span>
                            </div>
                            <input type="number" name="min_boxes" id="min_boxes" class="form-control form-control-sm erp-input" placeholder="Min Boxes" min="0" value="{{ request('min_boxes') }}">
                        </div>
                    </div>
                    <div class="col-md-2 col-6 mb-2">
                        <label class="erp-label">Max boxes</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0"><i class="fas fa-boxes small"></i></span>
                            </div>
                            <input type="number" name="max_boxes" id="max_boxes" class="form-control form-control-sm erp-input" placeholder="Max Boxes" min="0" value="{{ request('max_boxes') }}">
                        </div>
                    </div>
                    <div class="col-auto mb-2 ml-auto">
                        <div class="erp-filter-actions">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button type="button" id="btn-reset-filters" class="btn-erp btn-erp-outline" title="Reset Filters">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="erp-card" style="margin-bottom: 75px !important;">
            <div class="erp-card-header flex-wrap">
                <div class="d-flex align-items-center">
                    <h6 class="erp-card-title mr-3"><i class="fas fa-boxes"></i> Inventory Selection</h6>
                    <span class="erp-stat-pill" id="variationsCount">{{ $boxes->total() }} Items Available</span>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn-erp btn-erp-primary open-checkout-modal-btn" id="cardCheckoutBtn" style="display: none;">
                        <i class="fas fa-shopping-cart mr-1"></i> Review &amp; Update (<span id="cardBoxesCount">0</span> Boxes • ₹<span id="cardGrandTotal">0</span>) <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="erp-table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">Image</th>
                            <th>Design & color</th>
                            <th>Size set</th>
                            <th class="text-center" style="width: 90px;">Pcs/box</th>
                            <th class="text-center" style="width: 140px;">Available</th>
                            <th class="text-right" style="width: 110px;">Price</th>
                            <th class="text-center" style="width: 150px;">Order qty (Boxes)</th>
                        </tr>
                    </thead>
                    <tbody id="variation-container">
                        @foreach($boxes as $variation)
                            @php
                                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                                $image = $boxImages[$vKey] ?? null;
                                $itemData = $selected_quantities[$vKey] ?? null;
                                $initialQty = $itemData ? $itemData['qty'] : 0;
                            @endphp
                            @include('admin.agent_orders.partials.variation_row', ['variation' => $variation, 'image' => $image, 'initialQty' => $initialQty, 'dispatchedQty' => $dispatched_quantities[$vKey] ?? 0])
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div id="loading-spinner" class="text-center py-3" style="display: none;">
                <div class="spinner-border text-success" role="status" style="width: 2rem; height: 2rem; color: #05421c !important;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Bottom Summary Bar -->
<div class="fixed-bottom bg-white border-top py-2 px-3" id="summaryBar" style="z-index: 1040; left: 250px;">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-wrap">
            <div class="mr-3">
                <span class="erp-sum-label mr-1">Selected:</span>
                <span class="badge badge-primary px-2 py-1" style="font-size: 13px;"><span id="selectedCount">0</span> Boxes</span>
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
                Review &amp; Update <i class="fas fa-arrow-right ml-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Order Review & Update Modal -->
<div class="modal fade erp-modal" id="orderCheckoutModal" tabindex="-1" role="dialog" aria-labelledby="orderCheckoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 920px;">
        <div class="modal-content border-0">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="orderCheckoutModalLabel">
                        <i class="fas fa-clipboard-check mr-2"></i> Review &amp; Update Order #ORD-{{ $order->id }}
                    </h5>
                    <small class="erp-modal-sub">
                        Party: <strong>{{ $shop->name ?? 'N/A' }}</strong> ({{ ucfirst($order->party_type ?? 'customer') }}) &nbsp;•&nbsp;
                        Agent: {{ $order->agent->name ?? 'Direct' }}
                    </small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="modal-body p-3">
                <div class="row">
                    <!-- Left: Selected Variations Breakdown -->
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

                            <div class="form-row align-items-center mb-2">
                                <div class="col-6">
                                    <label class="erp-label mb-1">Discount %</label>
                                    <input type="number" id="discountPercentage" class="form-control form-control-sm erp-input text-right font-weight-bold" 
                                        value="{{ $order->discount_percentage }}" min="0" max="100" step="any">
                                </div>
                                <div class="col-6">
                                    <label class="erp-label mb-1">Discount amount (₹)</label>
                                    <input type="number" id="discountAmountInput" class="form-control form-control-sm erp-input text-right font-weight-bold" 
                                        value="{{ $order->discount_amount }}" min="0">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-2">
                                <span class="erp-sum-label">Taxable Amount:</span>
                                <span class="font-weight-bold h6 mb-0">₹<span id="taxableAmount">0.00</span></span>
                            </div>

                            <div class="form-row align-items-center mb-2">
                                <div class="col-6">
                                    <label class="erp-label mb-1">GST %</label>
                                    <input type="number" id="gstPercentage" class="form-control form-control-sm erp-input text-right font-weight-bold" 
                                        value="{{ $order->gst_percentage }}" min="0" max="100" step="any">
                                </div>
                                <div class="col-6">
                                    <label class="erp-label mb-1">GST amount (₹)</label>
                                    <input type="number" id="gstAmountInput" class="form-control form-control-sm erp-input text-right font-weight-bold" 
                                        value="{{ $order->gst_amount }}" min="0">
                                </div>
                            </div>

                            <div class="form-row align-items-center mb-3">
                                <div class="col-6">
                                    <span class="erp-sum-label">Other Charges (₹):</span>
                                </div>
                                <div class="col-6">
                                    <input type="number" id="other_charges" class="form-control form-control-sm erp-input text-right font-weight-bold" value="{{ $order->other_charges ?? 0 }}" min="0" step="1">
                                </div>
                            </div>

                            <!-- Grand Total Box -->
                            <div class="erp-grand-box text-center">
                                <small class="text-uppercase font-weight-bold d-block">Final Grand Total</small>
                                <span class="erp-grand-val">₹<span id="grandTotalAmount">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logistics & Dispatch Details -->
                <div class="border-top pt-2 mt-3">
                    <span class="erp-section-title d-block mb-2"><i class="fas fa-truck mr-1"></i> Order &amp; Logistics Details</span>
                    <div class="form-row">
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Expected dispatch</label>
                            <input type="date" id="expectedDispatchDate" class="form-control form-control-sm erp-input" 
                                value="{{ $order->expected_dispatch_date ?: (\Carbon\Carbon::parse($order->expected_dispatch_date)->format('Y-m-d') ?: date('Y-m-d', strtotime('+3 days'))) }}">
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Status</label>
                            <select id="orderStatus" class="form-control form-control-sm erp-input">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                <option value="delayed" {{ $order->status == 'delayed' ? 'selected' : '' }}>DELAYED</option>
                                <option value="partially_dispatched" {{ $order->status == 'partially_dispatched' ? 'selected' : '' }}>PARTIALLY DISPATCHED</option>
                                <option value="dispatched" {{ $order->status == 'dispatched' ? 'selected' : '' }}>DISPATCHED</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Booking station</label>
                            <input type="text" id="booking_station" class="form-control form-control-sm erp-input" placeholder="Station" value="{{ $order->booking_station }}">
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="erp-label">Transport</label>
                            <input type="text" id="transport" class="form-control form-control-sm erp-input" placeholder="Transport Name" value="{{ $order->transport }}">
                        </div>
                        <div class="col-12 mb-1">
                            <label class="erp-label">Order remark / Notes</label>
                            @php
                                $previousRemarks = \DB::table('agent_orders')->whereNotNull('remark')->where('remark', '!=', '')->distinct()->pluck('remark');
                            @endphp
                            <input type="text" id="remark" class="form-control form-control-sm erp-input" list="previous_remarks_list" placeholder="Enter notes..." value="{{ $order->remark }}" autocomplete="off">
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
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn-erp btn-erp-outline text-danger mr-2">
                        Cancel
                    </a>
                    <button type="button" class="btn-erp btn-erp-primary font-weight-bold update-order-btn">
                        Update Order <i class="fas fa-save ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
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
    .erp-page .erp-section-title , #orderCheckoutModal .erp-section-title {
        font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--erp-green-primary);
    }
    .erp-page .erp-sum-label , #orderCheckoutModal .erp-sum-label { font-size: 12px; font-weight: 700; color: var(--erp-text-subtle); }
    .erp-page .erp-sum-total { font-size: 18px; font-weight: 800; color: var(--erp-green-primary); }
    .erp-page .erp-soft-box , #orderCheckoutModal .erp-soft-box { background: var(--erp-green-light); border: 1px solid var(--erp-green-border); border-radius: 6px; }
    .erp-page .erp-grand-box , #orderCheckoutModal .erp-grand-box {
        background: var(--erp-green-primary); color: #fff; border-radius: 6px; padding: 8px;
        border-bottom: 4px solid var(--brand-yellow, #fcee21);
    }
    .erp-page .erp-grand-box small , #orderCheckoutModal .erp-grand-box small , #orderCheckoutModal .erp-grand-box small , #orderCheckoutModal .erp-grand-box small { color: rgba(255,255,255,.75); letter-spacing: .5px; }
    .erp-page .erp-grand-val , #orderCheckoutModal .erp-grand-val { font-size: 24px; font-weight: 800; color: var(--brand-yellow, #fcee21); }
    .erp-page .input-group-text , #orderCheckoutModal .input-group-text {
        background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid #ced4da; font-size: 12px; font-weight: 700;
    }
    .erp-page .input-group-sm .form-control , #orderCheckoutModal .input-group-sm .form-control { height: 33px !important; }

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

    .erp-page .erp-stat-pill { font-weight: 700; }
    #orderCheckoutModal .erp-stat-pill { display: inline-block; }
    #orderCheckoutModal .erp-label { color: var(--erp-text-heading) !important; }
</style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            let discount_mode = 'percentage'; // 'percentage' or 'amount'
            let gst_mode = 'percentage';
            let cart = new Map();
            const initialVariations = @json($selected_quantities);
            const dispatchedQuantities = @json($dispatched_quantities ?? []);

            // --- INFINITE SCROLL & REAL-TIME FILTER START ---
            let nextPage = {{ $boxes->nextPageUrl() ? ($boxes->currentPage() + 1) : 'null' }};
            let loading = false;
            const container = $('#variation-container');
            const scrollContainer = $('.content-wrapper');

            container.css('min-height', '400px');

            scrollContainer.on('scroll', function() {
                if (loading || !nextPage) return;
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
                    container.css('opacity', '0.5');
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
                        container.append(response.html);
                        nextPage = response.next_page;
                        
                        if (response.total_count !== undefined) {
                            $('#variationsCount').text(response.total_count + ' Items Available');
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

            $('#filterForm select').on('change', function() {
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

            $('#btn-reset-filters').on('click', function() {
                $('#filterForm select').val('').trigger('change');
                $('#filterForm input[type="number"], #filterForm input[type="text"]').not(':hidden').val('');
                loadMore(true);
            });
            // --- INFINITE SCROLL & REAL-TIME FILTER END ---

            Object.keys(initialVariations).forEach(key => {
                const itemData = initialVariations[key];
                const dispQty = parseInt(itemData.dispatched_qty || dispatchedQuantities[key] || 0) || 0;
                cart.set(key, {
                    product_id: itemData.product_id,
                    color_id: itemData.color_id,
                    size_set_id: itemData.size_set_id,
                    qty: parseInt(itemData.qty) || 0,
                    pcs_per_box: parseFloat(itemData.pcs_per_box),
                    unit_price: parseFloat(itemData.unit_price),
                    design: itemData.design_number || null,
                    garment: itemData.garment || null,
                    color: itemData.color_name || null,
                    size: itemData.size_set_name || null,
                    min: dispQty,
                    max: null
                });
            });

            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            function updateUI() {
                // Sync prices and limits from DOM to cart if available
                $('.variation-row').each(function () {
                    const key = $(this).data('key');
                    if (cart.has(key)) {
                        const item = cart.get(key);
                        item.unit_price = parseFloat($(this).data('price'));
                        const rInput = $(this).find('.box-qty-input');
                        if (rInput.length) {
                            if (rInput.attr('min')) item.min = parseInt(rInput.attr('min')) || 0;
                            if (rInput.attr('max')) item.max = parseInt(rInput.attr('max'));
                        }
                    }
                });

                let totalBoxes = 0;
                let subTotal = 0;

                cart.forEach((item) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        if (item.pcs_per_box) {
                            subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                        }
                    }
                });

                subTotal = Math.ceil(subTotal);

                let otherCharges = Math.ceil(parseFloat($('#other_charges').val()) || 0);
                let discountAmount = 0;
                let discountPercent = parseFloat($('#discountPercentage').val()) || 0;

                if (discount_mode === 'amount') {
                    discountAmount = Math.ceil(parseFloat($('#discountAmountInput').val()) || 0);
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

                const formattedSubTotal = subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const formattedTaxable = taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const formattedGrandTotal = grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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

                $('.variation-row').each(function () {
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
            }

            $(document).on('change', '.box-qty-input', function () {
                const row = $(this).closest('.variation-row');
                const key = row.data('key');
                const min = parseInt($(this).attr('min')) || 0;
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < min) {
                    Swal.fire('Locked Quantity', 'This item has ' + min + ' boxes already dispatched and cannot be reduced below that.', 'warning');
                    qty = min;
                    $(this).val(qty);
                }
                if (!isNaN(max) && qty > max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' boxes available.', 'warning');
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
                        min: min,
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
                        // If metadata is missing, attempt recovery from DOM
                        if (!item.design || !item.color) {
                            const row = $(`.variation-row[data-key="${key}"]`);
                            if (row.length) {
                                item.design = row.data('design') || row.find('td:nth-child(2) .font-weight-bold').first().text().trim();
                                item.garment = row.data('garment') || row.find('td:nth-child(2) .text-primary').first().text().trim();
                                item.color = row.data('color') || row.find('td:nth-child(2) .text-muted.small').first().text().replace(/[\r\n\t]+/g, ' ').replace('Color:', '').trim();
                                item.size = row.data('size') || row.find('td:nth-child(3) .badge').first().text().trim();
                                const rInput = row.find('.box-qty-input');
                                if (rInput.length) {
                                    if (rInput.attr('min')) item.min = parseInt(rInput.attr('min')) || 0;
                                    if (rInput.attr('max')) item.max = parseInt(rInput.attr('max'));
                                }
                                cart.set(key, item);
                            }
                        }

                        const designText = item.design || ('Design #' + item.product_id);
                        const garmentText = item.garment || '';
                        const colorText = item.color || '';
                        const sizeText = item.size || '';
                        const minQty = parseInt(item.min) || 0;
                        const maxAttr = (item.max !== null && item.max !== undefined && !isNaN(item.max)) ? `max="${item.max}"` : '';
                        const lineTotal = item.qty * item.pcs_per_box * item.unit_price;

                        itemsHtml += `
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom modal-item-row" id="modal-item-${key}" data-key="${key}">
                                <div style="line-height: 1.25; max-width: 56%;">
                                    <div class="font-weight-bold text-dark mb-0">
                                        ${designText} 
                                        ${sizeText ? `<span class="badge badge-light border text-muted ml-1">${sizeText}</span>` : ''}
                                        ${minQty > 0 ? `<span class="badge badge-warning text-dark font-weight-bold ml-1 px-1" style="font-size: 10px;" title="Dispatched: ${minQty} boxes"><i class="fas fa-truck mr-1"></i>Disp: ${minQty}</span>` : ''}
                                    </div>
                                    <small class="text-muted d-block text-truncate">${garmentText} ${colorText ? `• ${colorText}` : ''}</small>
                                    <small class="text-secondary">${item.pcs_per_box} pcs/box @ ₹${parseFloat(item.unit_price).toFixed(2)}</small>
                                </div>
                                <div class="text-right ml-2" style="min-width: 145px;">
                                    <div class="d-flex align-items-center justify-content-end mb-1">
                                        <div class="input-group input-group-sm" style="width: 105px;">
                                            <div class="input-group-prepend">
                                                <button type="button" class="btn btn-outline-danger btn-modal-minus py-0 px-2 font-weight-bold" data-key="${key}" title="Decrease quantity" style="font-size: 13px; line-height: 1.4;">-</button>
                                            </div>
                                            <input type="number" class="form-control text-center py-0 px-1 font-weight-bold modal-qty-input" 
                                                value="${item.qty}" min="${minQty}" ${maxAttr} data-key="${key}" style="height: 28px; font-size: 13px;">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-success btn-modal-plus py-0 px-2 font-weight-bold" data-key="${key}" title="Increase quantity" style="font-size: 13px; line-height: 1.4;">+</button>
                                            </div>
                                        </div>
                                        ${minQty > 0 
                                            ? `<span class="text-muted ml-2" title="Cannot remove: ${minQty} boxes already dispatched" style="cursor: not-allowed; opacity: 0.4;"><i class="fas fa-lock" style="font-size: 13px;"></i></span>`
                                            : `<button type="button" class="btn btn-link text-danger p-0 ml-2 btn-modal-remove" data-key="${key}" title="Remove variation">
                                                <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                               </button>`
                                        }
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

                const min = parseInt(item.min) || 0;
                if (item.qty <= min) {
                    if (min > 0) {
                        Swal.fire('Locked Quantity', 'Cannot reduce below already dispatched quantity (' + min + ' boxes).', 'info');
                    }
                    return;
                }

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
                const item = cart.get(key);
                if (!item) return;

                const min = parseInt(item.min) || 0;
                if (min > 0) {
                    Swal.fire('Locked Quantity', 'Cannot remove this item because ' + min + ' boxes are already dispatched.', 'warning');
                    return;
                }

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
                const min = parseInt(item.min) || 0;
                let max = item.max;

                if (max === undefined || max === null) {
                    const rowInput = $(`.variation-row[data-key="${key}"] .box-qty-input`);
                    if (rowInput.length && rowInput.attr('max')) {
                        max = parseInt(rowInput.attr('max'));
                        item.max = max;
                    }
                }

                if (qty < min) {
                    Swal.fire('Locked Quantity', 'Cannot reduce below already dispatched quantity (' + min + ' boxes).', 'warning');
                    qty = min;
                    $(this).val(qty);
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

            $('#other_charges').on('input change', function () {
                updateUI();
            });

            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                const max = parseInt(input.attr('max'));
                if (isNaN(max) || current < max) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                const min = parseInt(input.attr('min')) || 0;
                if (current > min) {
                    input.val(current - 1).trigger('change');
                } else if (min > 0 && current <= min) {
                    Swal.fire('Locked Quantity', 'Cannot reduce below already dispatched quantity (' + min + ' boxes).', 'info');
                }
            });

            updateUI();

            $('.update-order-btn').click(function () {
                const btn = $(this);
                const originalText = btn.html();

                let variations = [];
                cart.forEach((item, key) => {
                    if (item.qty > 0) {
                        variations.push(item);
                    }
                });

                if (variations.length === 0) {
                    Swal.fire('Empty Selection', 'Please select at least one item.', 'warning');
                    return;
                }

                let otherCharges = parseFloat($('#other_charges').val()) || 0;
                let expectedDate = $('#expectedDispatchDate').val();

                Swal.fire({
                    title: 'Update Order?',
                    text: "Total " + variations.reduce((a, b) => a + b.qty, 0) + " boxes will be updated.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

                        $.ajax({
                            url: "{{ route('admin.agent-orders.update', $order->id) }}",
                            method: "POST",
                            data: {
                                _method: 'PUT',
                                _token: "{{ csrf_token() }}",
                                variations: variations,
                                is_sample_set: $('#sampleSetToggle').is(':checked') ? 1 : 0,
                                discount_percentage: $('#discountPercentage').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_percentage: $('#gstPercentage').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                other_charges: otherCharges,
                                expected_dispatch_date: expectedDate,
                                status: $('#orderStatus').val(),
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val(),
                                party_type: $('input[name="party_type"]:checked').val(),
                                master_customer_id: $('#editCustomerSelect').val(),
                                master_vendor_id: $('#editVendorSelect').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Success!', response.message, 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    btn.prop('disabled', false).html(originalText);
                                }
                            },
                            error: function (xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                                btn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            $('input[name="party_type"]').on('change', function() {
                if ($(this).val() === 'customer') {
                    $('#editCustomerWrapper').show();
                    $('#editVendorWrapper').hide();
                    $('#editVendorSelect').val('').trigger('change');
                } else {
                    $('#editCustomerWrapper').hide();
                    $('#editVendorWrapper').show();
                    $('#editCustomerSelect').val('').trigger('change');
                }
            });
        });
    </script>
@endpush