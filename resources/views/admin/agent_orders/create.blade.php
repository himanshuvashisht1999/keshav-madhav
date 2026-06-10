@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header pt-2 pb-1">
        <div class="container-fluid">
            @if(!isset($boxes))
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="m-0 font-weight-bold text-dark"><i class="fas fa-plus-circle mr-2"></i>Create New {{ request('order_type') == 'direct' ? 'Direct' : 'Sales' }} Order</h4>
                        <p class="text-muted">Initiate a new order by selecting {{ request('order_type') == 'direct' ? 'a customer' : 'an agent and a customer' }}.</p>
                    </div>
                </div>
            @else
                <div class="row align-items-center">
                    <div class="col-md-8 col-sm-7">
                        <h4 class="m-0 font-weight-bold text-dark text-truncate" title="New {{ request('order_type') == 'direct' ? 'Direct' : 'Agent' }} Order: {{ $shop->name }}"><i class="fas fa-shopping-basket mr-2"></i>New {{ request('order_type') == 'direct' ? 'Direct' : 'Agent' }} Order: <span class="text-primary">{{ $shop->name }}</span></h4>
                        <p class="text-muted small mb-0 text-truncate"><i class="fas fa-user-tie mr-1"></i> {{ $agent->id === 'direct' ? 'Direct Sale (No Agent)' : 'Agent: ' . $agent->name }}</p>
                    </div>
                    <div class="col-md-4 col-sm-5 text-right">
                        <a href="{{ route('admin.agent-orders.create') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
                            <i class="fas fa-exchange-alt mr-1"></i> Change Agent/Shop
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(!isset($boxes))
                <!-- STEP 1: Selection Form -->
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 text-dark font-weight-bold">Order Basic Information</h5>
                            </div>
                            <div class="card-body p-4">
                                <form action="{{ route('admin.agent-orders.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="order_type" value="{{ request('order_type', 'normal') }}">
                                    <div class="form-group mb-4">
                                        <label class="text-muted small font-weight-bold text-uppercase">Party Type <span class="text-danger">*</span></label>
                                        <div class="d-flex align-items-center">
                                            <div class="custom-control custom-radio mr-4">
                                                <input class="custom-control-input" type="radio" id="typeCustomer" name="party_type" value="customer" {{ old('party_type', request('party_type', 'customer')) == 'customer' ? 'checked' : '' }}>
                                                <label for="typeCustomer" class="custom-control-label font-weight-normal">Customer</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input class="custom-control-input" type="radio" id="typeVendor" name="party_type" value="vendor" {{ old('party_type', request('party_type')) == 'vendor' ? 'checked' : '' }}>
                                                <label for="typeVendor" class="custom-control-label font-weight-normal">Vendor</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4" id="customerWrapper" style="{{ old('party_type', request('party_type', 'customer')) == 'customer' ? '' : 'display:none;' }}">
                                        <label class="d-flex justify-content-between align-items-center mb-1 text-muted small font-weight-bold text-uppercase">
                                            <span>Select Customer <span class="text-danger">*</span></span>
                                            <span class="action-links text-capitalize" style="font-size: 0.85rem; font-weight: normal;">
                                                <a href="{{ route('admin.master.customer.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                                <a href="javascript:void(0)" class="text-info" id="refreshCustomerBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
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

                                    <div class="form-group mb-4" id="vendorWrapper" style="{{ old('party_type', request('party_type')) == 'vendor' ? '' : 'display:none;' }}">
                                        <label class="d-flex justify-content-between align-items-center mb-1 text-muted small font-weight-bold text-uppercase">
                                            <span>Select Vendor <span class="text-danger">*</span></span>
                                            <span class="action-links text-capitalize" style="font-size: 0.85rem; font-weight: normal;">
                                                <a href="{{ route('admin.master.vendor.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                                <a href="javascript:void(0)" class="text-info" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
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

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="text-muted small font-weight-bold text-uppercase">Order Type <span class="text-danger">*</span></label>
                                                <select name="order_type" id="orderTypeSelect" class="form-control @error('order_type') is-invalid @enderror" required>
                                                    <option value="normal" {{ request('order_type') == 'normal' ? 'selected' : '' }}>Normal</option>
                                                    <option value="direct" {{ request('order_type') == 'direct' ? 'selected' : '' }}>Direct (Instant Bill/Dispatch)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="text-muted small font-weight-bold text-uppercase">Sale Type <span class="text-danger">*</span></label>
                                                <select name="sale_type" id="saleTypeSelect" class="form-control @error('sale_type') is-invalid @enderror" required>
                                                    <option value="item" {{ old('sale_type', 'item') == 'item' ? 'selected' : '' }}>Item (Ready Goods)</option>
                                                    <option value="fabric" {{ old('sale_type') == 'fabric' ? 'selected' : '' }}>Fabric (Under Development)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="d-flex justify-content-between align-items-center mb-1 text-muted small font-weight-bold text-uppercase">
                                            <span>Select Sales Man</span>
                                            <span class="action-links text-capitalize" style="font-size: 0.85rem; font-weight: normal;">
                                                <a href="{{ route('admin.master.sales-man.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                                <a href="javascript:void(0)" class="text-info" id="refreshSalesManBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                            </span>
                                        </label>
                                        <select name="sales_man_id" id="salesManSelect" class="form-control select2">
                                            <option value="">-- Optional: Choose Sales Man --</option>
                                            @foreach($salesMen as $man)
                                                <option value="{{ $man->id }}" {{ old('sales_man_id', request('sales_man_id')) == $man->id ? 'selected' : '' }}>
                                                    {{ $man->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-4" id="agentSelectionWrapper" style="display:none;">
                                        <select name="sales_agent_id" id="agentSelect" class="form-control">
                                             <option value="direct">Direct</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <div class="custom-control custom-switch border p-3 rounded bg-light" style="border-radius: 10px !important;">
                                            <input type="checkbox" class="custom-control-input" id="sampleSetToggleStep1" name="is_sample_set" value="1">
                                            <label class="custom-control-label font-weight-bold ml-2 pt-1 text-primary" for="sampleSetToggleStep1" style="cursor:pointer; user-select: none;">Use Sample Set Pricing for this Order</label>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="text-muted small font-weight-bold text-uppercase">Order Date <span class="text-danger">*</span></label>
                                        <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
                                    </div>

                                    <div class="border-top pt-4 d-flex justify-content-between">
                                        <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary px-4">
                                            <i class="fas fa-times mr-2"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary px-5 shadow-sm" style="border-radius: 8px;">
                                            Proceed <i class="fas fa-arrow-right ml-2"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- STEP 2: Inventory Selection (Agent Type) -->
                <div class="row">
                    <div class="col-12">
                        <!-- Filters -->
                        <div class="card shadow-sm border-0 mb-3 bg-light">
                            <div class="card-body p-2">
                                <form method="GET" action="{{ route('admin.agent-orders.create') }}" id="filterForm">
                                    <input type="hidden" name="sales_agent_id" value="{{ $agent->id }}">
                                    <input type="hidden" name="sales_man_id" value="{{ request('sales_man_id') }}">
                                    <input type="hidden" name="party_type" value="{{ request('party_type', 'customer') }}">
                                    <input type="hidden" name="master_customer_id" value="{{ request('master_customer_id') }}">
                                    <input type="hidden" name="master_vendor_id" value="{{ request('master_vendor_id') }}">
                                    <input type="hidden" name="order_date" value="{{ request('order_date') }}">
                                    <input type="hidden" name="order_type" value="{{ request('order_type') }}">
                                    <div class="row align-items-end">
                                        <div class="col-md-3 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Design No</label>
                                            <select name="design_number" class="form-control form-control-sm select2">
                                                <option value="">All Designs</option>
                                                @foreach($designs as $design)
                                                    <option value="{{ $design }}" {{ request('design_number') == $design ? 'selected' : '' }}>
                                                        {{ $design }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Product Name</label>
                                            <select name="product_name" class="form-control form-control-sm select2">
                                                <option value="">All Products</option>
                                                @foreach($product_names as $name)
                                                    <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Color</label>
                                            <select name="color_name" class="form-control form-control-sm select2">
                                                <option value="">All Colors</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>
                                                        {{ $color }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Size Set</label>
                                            <select name="size_set_name" class="form-control form-control-sm select2">
                                                <option value="">All Sets</option>
                                                @foreach($size_sets as $set)
                                                    <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>
                                                        {{ $set }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Pattern</label>
                                            <select name="pattern_id" class="form-control form-control-sm select2">
                                                <option value="">All Patterns</option>
                                                @foreach($patterns as $id => $name)
                                                    <option value="{{ $id }}" {{ request('pattern_id') == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Fitting</label>
                                            <select name="fitting_id" class="form-control form-control-sm select2">
                                                <option value="">All Fittings</option>
                                                @foreach($fittings as $id => $name)
                                                    <option value="{{ $id }}" {{ request('fitting_id') == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Product Nature</label>
                                            <select name="product_nature_id" class="form-control form-control-sm select2">
                                                <option value="">All Natures</option>
                                                @foreach($product_natures as $id => $name)
                                                    <option value="{{ $id }}" {{ request('product_nature_id') == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-6 mb-1">
                                            <label class="small font-weight-bold text-muted mb-0">Fabric Type</label>
                                            <select name="fabric_type_id" class="form-control form-control-sm select2">
                                                <option value="">All Fabrics</option>
                                                @foreach($fabric_types as $id => $name)
                                                    <option value="{{ $id }}" {{ request('fabric_type_id') == $id ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 col-12 mb-1 text-right">
                                            <button type="submit" class="btn btn-primary btn-sm px-4 mr-2 shadow-sm">
                                                <i class="fas fa-search mr-1"></i> Filter
                                            </button>
                                            <a href="{{ route('admin.agent-orders.create', ['order_type' => request('order_type'), 'sales_agent_id' => $agent->id, 'sales_man_id' => request('sales_man_id'), 'master_customer_id' => $shop->id, 'order_date' => request('order_date')]) }}" 
                                               class="btn btn-secondary btn-sm px-3 shadow-sm">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Inventory Table -->
                        <div class="card shadow-sm border-0 overflow-hidden mb-5" style="border-radius: 12px; margin-bottom: 120px !important;">
                            <div class="card-header bg-white py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="font-weight-bold mb-0 text-dark">
                                        <i class="fas fa-boxes mr-2 text-primary"></i> Available Inventory
                                    </h6>
                                    <span class="badge badge-light border text-muted px-3 py-2" id="variationsCount">
                                        {{ $boxes->total() }} Variations Available
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="80">Image</th>
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
                    </div>
                </div>

                <!-- Sticky Summary Bar -->
                <div class="fixed-bottom bg-white shadow-lg border-top p-3 animate__animated animate__fadeInUp" 
                     id="summaryBar" style="z-index: 1050; left: 250px; display: none;">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Selected</small>
                                <span class="h5 font-weight-bold text-dark mb-0" id="selectedCount">0</span>
                                <small class="text-muted ml-1">Boxes</small>
                            </div>
                            
                            <div class="col-md-2 border-right">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted font-weight-bold">Subtotal:</small>
                                    <span class="font-weight-bold">₹<span id="subTotalAmount">0</span></span>
                                </div>
                                <div class="input-group input-group-sm mb-1" title="Discount Percentage">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0 px-1" style="font-size: 10px;">Disc %</span>
                                    </div>
                                    <input type="number" id="discountPercentage" class="form-control text-right h-auto py-0 px-1" 
                                        style="font-weight: bold;" value="0" min="0" max="100" step="any">
                                </div>
                                <div class="input-group input-group-sm" title="Discount Amount">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0 px-1" style="font-size: 10px;">Disc ₹</span>
                                    </div>
                                    <input type="number" id="discountAmountInput" class="form-control text-right h-auto py-0 px-1" 
                                        style="font-weight: bold;" value="0" min="0">
                                </div>
                            </div>

                            <div class="col-md-2 border-right">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted font-weight-bold">Taxable:</small>
                                    <span class="font-weight-bold">₹<span id="taxableAmount">0</span></span>
                                </div>
                                <div class="input-group input-group-sm mb-1" title="GST Percentage">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0 px-1" style="font-size: 10px;">GST %</span>
                                    </div>
                                    <input type="number" id="gstPercentage" class="form-control text-right h-auto py-0 px-1" 
                                        style="font-weight: bold;" value="{{ $gst_percentage }}" min="0" max="100" step="any">
                                </div>
                                <div class="input-group input-group-sm" title="GST Amount">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-0 px-1" style="font-size: 10px;">GST ₹</span>
                                    </div>
                                    <input type="number" id="gstAmountInput" class="form-control text-right h-auto py-0 px-1" 
                                        style="font-weight: bold;" value="0" min="0">
                                </div>
                            </div>

                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Other Charges</small>
                                <div class="input-group input-group-sm mt-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">₹</span>
                                    </div>
                                    <input type="number" id="other_charges" class="form-control" value="0" min="0" step="1">
                                </div>
                            </div>

                            @if(request('order_type') != 'direct')
                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Expected Dispatch</small>
                                <input type="date" id="expectedDispatchDate" class="form-control form-control-sm mt-1" 
                                    value="{{ date('Y-m-d', strtotime('+3 days')) }}" min="{{ date('Y-m-d') }}">
                            </div>
                            @endif

                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Booking & Transport</small>
                                <input type="text" id="booking_station" class="form-control form-control-sm mt-1" placeholder="Station">
                                <input type="text" id="transport" class="form-control form-control-sm mt-1" placeholder="Transport">
                            </div>

                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Order Remark</small>
                                @php
                                    $previousRemarks = \DB::table('agent_orders')->whereNotNull('remark')->where('remark', '!=', '')->distinct()->pluck('remark');
                                @endphp
                                <input type="text" id="remark" class="form-control form-control-sm mt-1" list="previous_remarks_list" placeholder="Notes..." autocomplete="off">
                                <datalist id="previous_remarks_list">
                                    @foreach($previousRemarks as $rem)
                                        <option value="{{ $rem }}">
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-md-2 text-center border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Grand Total</small>
                                <span class="h4 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmount">0</span></span>
                            </div>

                            <div class="col-md-2">
                                <button type="button" class="btn btn-success btn-lg btn-block py-2 font-weight-bold shadow-sm place-order-btn">
                                    {{ request('order_type') == 'direct' ? 'Deduct Stock & Bill' : 'Place Order' }} <i class="fas fa-check-circle ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<style>
    .font-weight-500 { font-weight: 500; }
    .variation-row { transition: background 0.2s; }
    .variation-row:hover { background-color: #f8f9fa; }
    .variation-row.has-qty { background-color: #e3f2fd; }
    .quantity-control { max-width: 140px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden; }
    .quantity-control input { border-left: 0; border-right: 0; font-weight: bold; }
    .badge-outline-secondary { border: 1px solid #6c757d; color: #6c757d; background: transparent; }
    .fixed-bottom { transition: left 0.3s; }
    @media (max-width: 991px) { .fixed-bottom { left: 0 !important; } }
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
            
            // Sync with session storage to handle pagination
            const saved = sessionStorage.getItem(storageKey);
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

                const otherCharges = parseFloat($('#other_charges').val()) || 0;
                let discountAmount = 0;
                let discountPercent = parseFloat($('#discountPercentage').val()) || 0;

                if (discount_mode === 'amount') {
                    discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                    // update percentage silently if not focused
                    if (!$('#discountPercentage').is(':focus') && subTotal > 0) {
                        $('#discountPercentage').val((discountAmount / subTotal * 100).toFixed(6));
                    }
                } else {
                    discountAmount = subTotal * (discountPercent / 100);
                    if (!$('#discountAmountInput').is(':focus')) {
                        $('#discountAmountInput').val(discountAmount.toFixed(2));
                    }
                }

                const taxableAmount = subTotal - discountAmount;
                let gstAmount = 0;
                let gstPercent = parseFloat($('#gstPercentage').val()) || 0;

                if (gst_mode === 'amount') {
                    gstAmount = parseFloat($('#gstAmountInput').val()) || 0;
                    if (!$('#gstPercentage').is(':focus') && taxableAmount > 0) {
                        $('#gstPercentage').val((gstAmount / taxableAmount * 100).toFixed(6));
                    }
                } else {
                    gstAmount = taxableAmount * (gstPercent / 100);
                    if (!$('#gstAmountInput').is(':focus')) {
                        $('#gstAmountInput').val(gstAmount.toFixed(2));
                    }
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;

                $('#selectedCount').text(totalBoxes);
                $('#subTotalAmount').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                $('#taxableAmount').text(taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                
                $('#grandTotalAmount').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

                if (totalBoxes > 0) {
                    $('#summaryBar').fadeIn();
                } else {
                    $('#summaryBar').fadeOut();
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
                sessionStorage.setItem(storageKey, JSON.stringify(storageObj));
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
                    cart.set(key, {
                        product_id: row.data('product-id'),
                        color_id: row.data('color-id'),
                        size_set_id: row.data('size-set-id'),
                        qty: qty,
                        pcs_per_box: parseFloat(row.data('pcs')),
                        unit_price: parseFloat(row.data('price'))
                    });
                } else {
                    cart.delete(key);
                }
                updateUI();
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
                const current = parseInt(input.val()) || 0;
                if (current < parseInt(input.attr('max'))) input.val(current + 1).trigger('change');
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
                                    sessionStorage.removeItem(storageKey);
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
