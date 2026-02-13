@extends('sales_agent.layouts.app', ['title' => 'Create Order'])

@section('content')
    <div class="container-fluid pb-5 mb-5 mt-3">
        <!-- Shop Header -->
        <div class="bg-white p-3 rounded shadow-sm mb-4 border-left border-primary"
            style="border-left-width: 4px !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted small mb-1">Ordering for:</h6>
                    <h5 class="font-weight-bold mb-0 text-dark">{{ $shop->name }}</h5>
                    <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> {{ $shop->address }}</small>
                </div>
                <div class="text-right">
                    <a href="{{ route('agent.shops.index') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="fas fa-exchange-alt mr-1"></i> Change Shop
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('agent.orders.create') }}" id="filterForm">
                    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ request('name') }}" placeholder="Search by Name">
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Design No</label>
                            <select name="design_number" class="form-control form-control-sm select2">
                                <option value="">All Designs</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design }}" {{ request('design_number') == $design ? 'selected' : '' }}>
                                        {{ $design }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Color</label>
                            <select name="color_name" class="form-control form-control-sm select2">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>
                                        {{ $color }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-12 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                            <select name="size_set_name" class="form-control form-control-sm select2">
                                <option value="">All Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>
                                        {{ $set }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 col-12 mb-2 d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('agent.orders.create', ['shop_id' => $shop->id]) }}"
                                class="btn btn-secondary btn-sm px-3">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 12px;">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="fas fa-boxes mr-2 text-primary"></i> Available Inventory
                    </h6>
                    <span class="badge badge-light border text-muted px-3 py-2">
                        {{ $boxes->total() }} Boxes Found
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <form id="orderForm">
                    @csrf
                    <input type="hidden" name="shop_id" value="{{ $shop->id }}">

                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Name & Color</th>
                                    <th>Size Set</th>
                                    <th class="text-center">Pcs/Box</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-right">Price</th>
                                    <th width="150" class="text-center px-4">Order Qty (Boxes)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($boxes as $variation)
                                    @php
                                        $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                                        $image = $boxImages[$vKey] ?? null;
                                    @endphp
                                    <tr class="variation-row" data-key="{{ $vKey }}"
                                        data-product-id="{{ $variation->product_id }}"
                                        data-color-id="{{ $variation->color_id }}"
                                        data-size-set-id="{{ $variation->size_set_id }}"
                                        data-pcs="{{ $variation->pcs_per_box }}" data-price="{{ $variation->unit_price }}"
                                        data-available="{{ $variation->available_boxes }}">
                                        <td>
                                            @if($image)
                                                <img src="{{ asset('uploads/inventory_prices/' . $image) }}" alt="Product"
                                                    class="rounded border shadow-xs"
                                                    style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                    onclick="window.open(this.src)">
                                            @else
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center"
                                                    style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted opacity-50"></i>
                                                </div>
                                            @endif
                                        </td>
                                       
                                        <td>
                                            <div class="text-dark font-weight-500">{{ $variation->name }} ( {{ $variation->design_number }} )</div>
                                            <div class="text-muted small"><i class="fas fa-palette mr-1"></i>
                                                {{ $variation->color_name }}</div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-outline-secondary px-2 py-1">{{ $variation->size_set_name }}</span>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ number_format($variation->pcs_per_box, 0) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info px-2 py-1">{{ $variation->available_boxes }}
                                                Boxes</span>
                                        </td>
                                        <td class="text-right">
                                            <div class="text-dark font-weight-bold">
                                                ₹{{ number_format($variation->unit_price, 2) }}</div>
                                            <small class="text-muted">per pc</small>
                                        </td>
                                        <td class="text-center px-4">
                                            <div class="input-group input-group-sm quantity-control">
                                                <div class="input-group-prepend">
                                                    <button class="btn btn-outline-secondary btn-minus" type="button">-</button>
                                                </div>
                                                <input type="number" class="form-control text-center box-qty-input" value="0"
                                                    min="0" max="{{ $variation->available_boxes }}" data-key="{{ $vKey }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary btn-plus" type="button">+</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-4">
                                                <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                                                <h6 class="text-muted">No variations found matching those filters.</h6>
                                                <a href="{{ route('agent.orders.create', ['shop_id' => $shop->id]) }}"
                                                    class="btn btn-link btn-sm">Clear all filters</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="d-md-none space-y-3">
                        @forelse($boxes as $variation)
                            @php
                                $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                                $image = $boxImages[$vKey] ?? null;
                                $initialQty = 0; // Create page always starts with 0
                            @endphp
                            <div class="card border-0 shadow-sm rounded-lg mb-2 variation-row overflow-hidden {{ $initialQty > 0 ? 'has-qty' : '' }}" 
                                data-key="{{ $vKey }}"
                                data-product-id="{{ $variation->product_id }}" 
                                data-color-id="{{ $variation->color_id }}"
                                data-size-set-id="{{ $variation->size_set_id }}" 
                                data-pcs="{{ $variation->pcs_per_box }}"
                                data-price="{{ $variation->unit_price }}" 
                                data-available="{{ $variation->available_boxes }}">
                                <div class="card-body p-2">
                                    <div class="d-flex">
                                        <!-- Image -->
                                        <div class="mr-3">
                                            @if($image)
                                                <img src="{{ asset('uploads/inventory_prices/' . $image) }}" alt="Product"
                                                    class="rounded-lg border"
                                                    style="width: 70px; height: 70px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded-lg border d-flex align-items-center justify-content-center"
                                                    style="width: 70px; height: 70px;">
                                                    <i class="fas fa-image text-muted opacity-50"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Details -->
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <div>
                                                    <h6 class="font-weight-bold text-dark mb-0 text-truncate" style="max-width: 150px;">
                                                        {{ $variation->design_number }}
                                                    </h6>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                                                        {{ $variation->color_name }} • {{ $variation->size_set_name }}
                                                    </small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-weight-bold text-dark">₹{{ number_format($variation->unit_price, 0) }}</div>
                                                    <small class="text-muted" style="font-size: 10px;">/pc</small>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-end mt-2">
                                                <div class="small text-muted" style="font-size: 11px; line-height: 1.2;">
                                                    <div>{{ $variation->pcs_per_box }} pcs/box</div>
                                                    <div class="{{ $variation->available_boxes < 10 ? 'text-danger' : 'text-success' }}">
                                                        {{ $variation->available_boxes }} boxes left
                                                    </div>
                                                </div>
                                                
                                                <div class="quantity-control input-group input-group-sm border rounded-pill overflow-hidden" style="width: 100px;">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-light btn-minus border-0 text-muted px-2" type="button">
                                                            <i class="fas fa-minus fa-xs"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" class="form-control text-center box-qty-input border-0 bg-transparent p-0 h-auto" 
                                                        value="0" min="0" max="{{ $variation->available_boxes }}" data-key="{{ $vKey }}"
                                                        style="font-size: 14px; font-weight: 600;">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-light btn-plus border-0 text-primary px-2" type="button">
                                                            <i class="fas fa-plus fa-xs"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No variations found.</h6>
                            </div>
                        @endforelse
                    </div>
                </form>
            </div>
            @if($boxes->hasPages())
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $boxes->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Sticky Bottom Summary Bar -->
    <!-- Desktop View -->
    <div class="fixed-bottom bg-white shadow-lg border-top p-3 d-none d-md-block animate__animated animate__fadeInUp" id="summaryBarDesktop"
        style="z-index: 1050; display: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-2 border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Shop</small>
                    <span class="text-dark font-weight-bold truncate d-block">{{ $shop->name }}</span>
                </div>
                <div class="col-md-2 text-center border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Summary</small>
                    <span class="h5 font-weight-bold text-dark mb-0" id="selectedCountDesktop">0</span>
                    <small class="text-muted ml-1">Boxes</small>
                </div>
                <div class="col-md-3 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Subtotal:</small>
                        <span class="font-weight-bold">₹<span id="subTotalAmountDesktop">0</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Discount (%):</small>
                        <input type="number" id="discountInputDesktop" class="form-control form-control-sm text-right p-1 py-0 discount-input"
                            style="width: 60px; height: 24px;" value="0" min="0" max="100">
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted font-weight-bold">GST ({{ $gst_percentage }}%):</small>
                        <span class="font-weight-bold text-danger">+₹<span id="gstAmountDesktop">0</span></span>
                    </div>
                </div>
                <div class="col-md-2 text-center pl-4">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Grand Total</small>
                    <span class="h4 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmountDesktop">0</span></span>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success btn-block btn-lg py-2 font-weight-bold shadow-sm place-order-btn">
                        Confirm Order <i class="fas fa-check-circle ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="fixed-bottom bg-white shadow-lg border-top d-md-none" id="summaryBarMobile"
        style="z-index: 1050; display: none;">
        <div class="p-2">
            <!-- Summary Info -->
            <div class="row mb-1">
                <div class="col-6">
                    <small class="text-muted d-block" style="font-size: 11px;">Subtotal</small>
                    <span class="font-weight-bold text-dark">₹<span id="subTotalAmountMobile">0</span></span>
                </div>
                <div class="col-6 text-right">
                    <small class="text-muted d-block" style="font-size: 11px;">Boxes</small>
                    <span class="font-weight-bold text-dark"><span id="selectedCountMobile">0</span></span>
                </div>
            </div>

            <!-- Discount & GST Row -->
            <div class="row mb-1">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <small class="text-muted mr-2" style="font-size: 11px;">Discount %</small>
                        <input type="number" id="discountInputMobile" class="form-control form-control-sm text-center discount-input"
                            style="width: 50px; height: 28px; font-size: 13px;" value="0" min="0" max="100">
                    </div>
                </div>
                <div class="col-6 text-right">
                    <small class="text-muted d-block" style="font-size: 11px;">GST ({{ $gst_percentage }}%)</small>
                    <span class="text-success font-weight-bold">+₹<span id="gstAmountMobile">0</span></span>
                </div>
            </div>

            <!-- Grand Total & Button -->
            <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                <div>
                    <small class="text-muted d-block" style="font-size: 11px;">Grand Total</small>
                    <span class="h5 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmountMobile">0</span></span>
                </div>
                <div>
                    <button type="button" class="btn btn-success px-4 py-2 font-weight-bold shadow-sm place-order-btn">
                        Confirm <i class="fas fa-check ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .font-weight-500 {
            font-weight: 500;
        }

        .shadow-xs {
            shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .variation-row {
            transition: background 0.2s;
        }

        .variation-row:hover {
            background-color: #f8f9fa;
        }

        .variation-row.has-qty {
            background-color: #e3f2fd;
        }

        .badge-outline-secondary {
            border: 1px solid #6c757d;
            color: #6c757d;
            background: transparent;
        }

        /* Sticky Summary Bar Animation */
        #summaryBar {
            transition: transform 0.3s ease-in-out;
        }

        .quantity-control {
            max-width: 140px;
            margin: 0 auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-radius: 6px;
            overflow: hidden;
        }

        .quantity-control input {
            border-left: 0;
            border-right: 0;
            font-weight: bold;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.5em + .5rem + 2px) !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // State: Map of VariationKey -> {product_id, color_id, size_set_id, qty, pcs_per_box, unit_price}
            let cart = new Map();

            // Try to load from sessionStorage
            const storageKey = 'agent_order_cart_{{ $shop->id }}';
            const saved = sessionStorage.getItem(storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(key => cart.set(key, data[key]));
            }

            // Initialize Select2
            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            function updateUI() {
                let totalBoxes = 0;
                let totalPieces = 0;
                let subTotal = 0;

                cart.forEach((item, key) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        totalPieces += (item.qty * item.pcs_per_box);
                        subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                // Get discount from whichever input is visible or has value
                let discountPercent = 0;
                if ($('#discountInputDesktop').is(':visible')) {
                    discountPercent = parseFloat($('#discountInputDesktop').val()) || 0;
                } else {
                    discountPercent = parseFloat($('#discountInputMobile').val()) || 0;
                }

                // Sync discount inputs
                $('.discount-input').val(discountPercent);

                const discountAmount = subTotal * (discountPercent / 100);
                const taxableAmount = subTotal - discountAmount;
                const gstPercent = {{ $gst_percentage }};
                const gstAmount = taxableAmount * (gstPercent / 100);
                const grandTotal = taxableAmount + gstAmount;

                // Update Desktop Summary
                $('#selectedCountDesktop').text(totalBoxes);
                $('#subTotalAmountDesktop').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#gstAmountDesktop').text(gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#grandTotalAmountDesktop').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                // Update Mobile Summary
                $('#selectedCountMobile').text(totalBoxes);
                $('#miniBoxCount').text(totalBoxes);
                $('#subTotalAmountMobile').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#gstAmountMobile').text(gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#grandTotalAmountMobile').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                // Show/Hide Bars
                if (totalBoxes > 0) {
                    if (window.innerWidth >= 768) {
                        $('#summaryBarDesktop').fadeIn();
                        $('#summaryBarMobile').hide();
                    } else {
                        $('#summaryBarMobile').fadeIn();
                        $('#summaryBarDesktop').hide();
                    }
                } else {
                    $('#summaryBarDesktop').fadeOut();
                    $('#summaryBarMobile').fadeOut();
                }

                // Sync inputs and row classes
                $('.variation-row').each(function () {
                    const key = $(this).data('key');
                    const item = cart.get(key);
                    const input = $(this).find('.box-qty-input');

                    if (item && item.qty > 0) {
                        input.val(item.qty);
                        $(this).addClass('has-qty');
                        $(this).find('.card').addClass('border-primary bg-light'); // Highlight mobile card
                    } else {
                        input.val(0);
                        $(this).removeClass('has-qty');
                        $(this).find('.card').removeClass('border-primary bg-light');
                    }
                });

                // Save to session
                const storageObj = {};
                cart.forEach((val, key) => { if (val.qty > 0) storageObj[key] = val; });
                sessionStorage.setItem(storageKey, JSON.stringify(storageObj));
            }

            // Numeric Input Change
            $(document).on('change', '.box-qty-input', function () {
                const row = $(this).closest('.variation-row');
                const key = row.data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0; // Ensure non-negative
                if (qty > max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' boxes available.', 'warning');
                    qty = max;
                    $(this).val(qty); // Update input value to max
                }

                // Find all inputs for this key (both desktop and mobile) and update them
                $(`.variation-row[data-key="${key}"] .box-qty-input`).val(qty);

                if (qty > 0) {
                    // Update cart
                    // data attributes might be on the row or the card, depending on view
                    // We can always get it from the triggered element's closest variation-row
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

            // Discount Input Change
            $('.discount-input').on('input change', function () {
                let val = parseFloat($(this).val());
                if (val < 0) $(this).val(0);
                if (val > 100) $(this).val(100);
                
                // Sync other discount inputs
                $('.discount-input').not(this).val($(this).val());
                
                updateUI();
            });



            // Plus/Minus Buttons
            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                const max = parseInt(input.attr('max'));
                if (current < max) {
                    input.val(current + 1).trigger('change');
                }
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) {
                    input.val(current - 1).trigger('change');
                }
            });

            // Handle Resize
            $(window).resize(function() {
                updateUI();
            });

            // Initial UI Update
            updateUI();

            // Place Order AJAX
            $('.place-order-btn').click(function () {
                const btn = $(this);
                const originalText = btn.html();

                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) {
                    Swal.fire('Empty Selection', 'Please select at least one item.', 'warning');
                    return;
                }

                // Get discount from visible input
                let discountPercent = 0;
                if ($('#discountInputDesktop').is(':visible')) {
                    discountPercent = parseFloat($('#discountInputDesktop').val()) || 0;
                } else {
                    discountPercent = parseFloat($('#discountInputMobile').val()) || 0;
                }

                Swal.fire({
                    title: 'Place Order?',
                    text: "Order Summary: " + variations.reduce((a, b) => a + b.qty, 0) + " boxes total.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Place Order'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable all place order buttons
                        $('.place-order-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                        $.ajax({
                            url: "{{ route('agent.orders.store') }}",
                            method: "POST",
                            data: {
                                shop_id: "{{ $shop->id }}",
                                variations: variations,
                                discount_percentage: discountPercent,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function (response) {
                                if (response.success) {
                                    sessionStorage.removeItem(storageKey);
                                    Swal.fire('Success!', 'Order placed successfully.', 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    $('.place-order-btn').prop('disabled', false).html(originalText);
                                }
                            },
                            error: function (xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                                $('.place-order-btn').prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush