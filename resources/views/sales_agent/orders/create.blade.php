@extends('sales_agent.layouts.app', ['title' => 'Create Order'])

@section('content')
    <div class="content-wrapper bg-light" style="min-height: 100vh; padding-bottom: 180px;">
        <!-- Header App Bar -->
        <div class="bg-white shadow-sm sticky-top" style="z-index: 1040;">
            <div class="container-fluid py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1.1rem;">{{ $shop->name }}</h5>
                        <small class="text-muted"><i class="fas fa-user-tie mr-1"></i> {{ $agent->name }}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary btn-sm rounded-circle mr-2 shadow-sm" id="btnScanQR"
                            style="width: 40px; height: 40px;">
                            <i class="fas fa-qrcode"></i>
                        </button>
                        <button class="btn btn-light btn-sm rounded-circle mr-2" id="toggleFilters"
                            style="width: 36px; height: 36px;">
                            <i class="fas fa-filter text-primary"></i>
                        </button>
                        <a href="{{ route('agent.shops.index') }}" class="btn btn-light btn-sm rounded-circle"
                            style="width: 36px; height: 36px;">
                            <i class="fas fa-exchange-alt"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Collapsible Filters -->
            <div id="filterContainer" style="display: none;"
                class="bg-white border-top animate__animated animate__fadeInDown p-3 shadow-sm">
                <form method="GET" action="{{ route('agent.orders.create') }}" id="filterForm">
                    <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                    <div class="row">

                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Product</label>
                            <select name="product_name" class="form-control form-control-sm select2">
                                <option value="">All Products</option>
                                @foreach($product_names as $name)
                                    <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>
                                        {{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Color</label>
                            <select name="color_name" class="form-control form-control-sm select2">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color }}" {{ request('color_name') == $color ? 'selected' : '' }}>
                                        {{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Size Set</label>
                            <select name="size_set_name" class="form-control form-control-sm select2">
                                <option value="">All Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set }}" {{ request('size_set_name') == $set ? 'selected' : '' }}>{{ $set }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="d-flex mt-2">
                        <button type="submit"
                            class="btn btn-primary btn-sm flex-grow-1 mr-2 rounded-pill font-weight-bold">Apply
                            Filters</button>
                        <a href="{{ route('agent.orders.create', ['shop_id' => $shop->id]) }}"
                            class="btn btn-light btn-sm rounded-pill"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="container-fluid mt-3">
            <!-- Variation Cards (Mobile App Style) -->
            <div id="variation-container" class="row">
                @forelse($boxes as $variation)
                    @php
                        $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                        $image = $boxImages[$vKey] ?? null;
                    @endphp
                    <div class="col-md-4 col-lg-3 mb-3 variation-row-container" id="row-{{ $vKey }}">
                        @include('sales_agent.orders.partials.variation_card', ['variation' => $variation, 'vKey' => $vKey, 'image' => $image])
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="bg-white p-5 rounded-lg shadow-sm border" style="border-radius: 20px;">
                            <div class="mb-4">
                                <div class="bg-primary-soft rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="fas fa-search fa-2x text-primary"></i>
                                </div>
                            </div>
                            <h5 class="text-dark font-weight-bold">Ready to Start Ordering?</h5>
                            <p class="text-muted px-4">Use the <b>filters</b> above or <b>scan a barcode</b> to find products and add them to your cart.</p>
                            <button class="btn btn-primary rounded-pill px-4 mt-2 font-weight-bold" onclick="$('#toggleFilters').click()">
                                <i class="fas fa-filter mr-2"></i> Open Filters
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($boxes->hasPages())
                <div class="text-center mt-3">
                    <button type="button" id="loadMoreBtn"
                        class="btn btn-white btn-sm px-5 rounded-pill shadow-sm border font-weight-bold text-primary">
                        Load More Products
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Compact App Summary Bar -->
    <div class="fixed-bottom bg-white shadow-lg border-top animate__animated animate__slideInUp" id="summaryBar"
        style="z-index: 1050; display: none; bottom: 60px; border-radius: 20px 20px 0 0;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-soft rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px;">
                        <i class="fas fa-shopping-cart text-primary"></i>
                    </div>
                    <div>
                        <span class="h5 font-weight-bold mb-0 d-block text-dark">₹<span
                                id="grandTotalAmount">0</span></span>
                        <small class="text-muted"><span id="selectedCount">0</span> Boxes Selected</small>
                    </div>
                </div>
                <button class="btn btn-light btn-sm rounded-pill px-3 font-weight-bold text-primary" id="btnShowDetails">
                    Details <i class="fas fa-chevron-up ml-1"></i>
                </button>
            </div>
            <button type="button"
                class="btn btn-primary btn-block btn-lg py-3 rounded-xl font-weight-bold shadow-lg place-order-btn"
                style="border-radius: 12px; font-size: 1.1rem;">
                Confirm Order <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <!-- Order Details Modal/Drawer (Mobile App Style) -->
    <div class="modal fade bottom-drawer" id="detailsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 20px 20px 0 0;">
                <div class="modal-header border-0 bg-light pb-0" style="border-radius: 20px 20px 0 0;">
                    <h6 class="modal-title font-weight-bold mx-auto text-muted uppercase tracking-wider">Order Summary &
                        Adjustments</h6>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div class="card shadow-none border-0 mb-3" style="border-radius: 15px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="font-weight-bold">₹<span id="subTotalAmount">0</span></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Manual Discount (₹)</label>
                                <input type="number" id="discountAmountInput"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold" value="0">
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Taxable Amount:</span>
                                <span class="font-weight-bold">₹<span id="taxableAmount">0</span></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">GST Amount (₹)</label>
                                <input type="number" id="gstAmountInput"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold" value="0">
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Other Charges (₹)</label>
                                <input type="number" id="other_charges"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-none border-0 mb-3" style="border-radius: 15px;">
                        <div class="card-body">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted uppercase">Dispatch & Shipping</label>
                                <input type="date" id="expectedDispatchDate" class="form-control form-control-sm mb-2"
                                    value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                                <input type="text" id="booking_station" class="form-control form-control-sm mb-2"
                                    placeholder="Booking Station">
                                <input type="text" id="transport" class="form-control form-control-sm mb-2"
                                    placeholder="Transport Name">
                                <textarea id="remark" class="form-control form-control-sm" rows="2"
                                    placeholder="Any special instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-dark btn-block btn-lg rounded-pill"
                        data-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Modal -->
    <div class="modal fade" id="scannerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title font-weight-bold text-muted uppercase tracking-wider mx-auto">Scan Product Barcode</h6>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div id="reader" style="width: 100%; border-radius: 15px; overflow: hidden; background: #000;"></div>
                    <div class="mt-3 text-center">
                        <p class="small text-muted mb-2">Scan the 'Fair Product' barcode to select colors</p>
                        <div class="input-group input-group-sm rounded-pill bg-light px-2" style="border: 1px solid #eee;">
                            <input type="text" id="manual_barcode" class="form-control border-0 bg-transparent" placeholder="Enter barcode manually...">
                            <div class="input-group-append">
                                <button class="btn btn-link text-primary font-weight-bold" id="btnManualSubmit">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan Selection Modal -->
    <div class="modal fade bottom-drawer" id="scanSelectionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0" style="border-radius: 20px 20px 0 0;">
                <div class="modal-header border-0 bg-white pb-0" style="border-radius: 20px 20px 0 0;">
                    <h6 class="modal-title font-weight-bold text-dark mx-auto text-uppercase tracking-wider">Select Color & Quantity</h6>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-white pt-2">
                    <div id="scanProductHeader" class="mb-3 p-3 bg-light rounded-lg">
                        <h6 id="scanProductName" class="font-weight-bold text-dark mb-1">Product Name</h6>
                        <div class="d-flex justify-content-between align-items-center">

                            <span class="small text-muted font-weight-bold" id="scanSizeSet">Size Set</span>
                        </div>
                    </div>

                    <div id="colorSelectionList" class="pb-3">
                        <!-- Colors will be injected here -->
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white shadow-lg">
                    <button type="button" class="btn btn-primary btn-block btn-lg rounded-xl font-weight-bold" data-dismiss="modal">Apply Selections</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-primary-soft {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .rounded-xl {
            border-radius: 12px !important;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .tracking-wider {
            letter-spacing: 0.5px;
        }

        /* Animation for the card appear */
        .variation-row-container {
            animation: fadeInUp 0.5s ease backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Drawer Style Modal */
        @media (max-width: 768px) {
            .modal.bottom-drawer .modal-dialog {
                margin: 0;
                margin-top: auto;
                align-items: flex-end;
                display: flex;
            }

            .modal.bottom-drawer .modal-content {
                border-radius: 20px 20px 0 0;
                width: 100%;
            }

            .modal-body {
                max-height: 70vh;
                overflow-y: auto;
            }
        }

        .variation-card {
            border-radius: 15px;
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .variation-card.has-qty {
            border-color: #007bff;
            background-color: #f0f7ff;
        }

        .quantity-control-app {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .quantity-control-app input {
            background: transparent;
            border: 0;
            font-weight: 800;
            text-align: center;
            width: 40px;
        }

        .quantity-control-app .btn-q {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            color: #007bff;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        $(document).ready(function () {
            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            let cart = new Map();
            const storageKey = 'agent_order_cart_{{ $agent->id }}_{{ $shop->id }}';

            // --- SCANNER LOGIC ---
            let html5QrcodeScanner = null;

            $('#btnScanQR').click(function() {
                $('#scannerModal').modal('show');
            });

            $('#scannerModal').on('shown.bs.modal', function () {
                startScanner();
            });

            $('#scannerModal').on('hidden.bs.modal', function () {
                stopScanner();
            });

            function startScanner() {
                if (html5QrcodeScanner) return;
                
                html5QrcodeScanner = new Html5Qrcode("reader");
                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrcodeScanner.start(
                    { facingMode: "environment" }, 
                    config, 
                    onScanSuccess
                ).catch(err => {
                    console.error("Scanner error:", err);
                    Swal.fire('Camera Error', 'Could not start camera scanner. Please ensure you have given camera permissions.', 'error');
                });
            }

            function stopScanner() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().then(() => {
                        html5QrcodeScanner = null;
                    }).catch(err => console.error(err));
                }
            }

            function onScanSuccess(decodedText) {
                stopScanner();
                $('#scannerModal').modal('hide');
                handleBarcode(decodedText);
            }

            $('#btnManualSubmit').click(function() {
                const bc = $('#manual_barcode').val().trim();
                if (bc) {
                    $('#scannerModal').modal('hide');
                    handleBarcode(bc);
                }
            });

            function handleBarcode(barcode) {
                // If the barcode is a URL, extract the last segment (the actual barcode)
                if (barcode.includes('/fc/')) {
                    const parts = barcode.split('/');
                    barcode = parts[parts.length - 1];
                } else if (barcode.startsWith('http')) {
                    // Fallback for any other URL format
                    const parts = barcode.split('/');
                    barcode = parts[parts.length - 1];
                }

                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('agent.orders.get-variation-by-barcode') }}",
                    data: { barcode: barcode },
                    success: function(res) {
                        Swal.close();
                        if (res.success) {
                            showColorSelection(res);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Failed to fetch product details.', 'error');
                    }
                });
            }

            function showColorSelection(data) {
                $('#scanProductName').text(data.product.name);

                $('#scanSizeSet').text(data.product.size_set_name);

                const list = $('#colorSelectionList');
                list.empty();

                data.colors.forEach(color => {
                    const vKey = data.product.id + '_' + color.id + '_' + data.product.size_set_id;
                    const item = cart.get(vKey);
                    const currentQty = item ? item.qty : 0;

                    const html = `
                        <div class="card border-0 shadow-sm mb-2 rounded-lg" data-key="${vKey}">
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">${color.name}</h6>
                                    <small class="text-muted">${color.available_boxes} Boxes available</small>
                                </div>
                                <div class="quantity-control-app d-flex align-items-center p-1">
                                    <button class="btn-q btn-minus-scan" data-key="${vKey}">-</button>
                                    <input type="number" class="box-qty-scan-input" 
                                        data-product-id="${data.product.id}"
                                        data-color-id="${color.id}"
                                        data-size-set-id="${data.product.size_set_id}"
                                        data-pcs="${color.pcs_per_box}"
                                        data-price="${data.product.unit_price}"
                                        max="${color.available_boxes}"
                                        value="${currentQty}">
                                    <button class="btn-q btn-plus-scan" data-key="${vKey}">+</button>
                                </div>
                            </div>
                        </div>
                    `;
                    list.append(html);
                });

                $('#scanSelectionModal').modal('show');
            }

            $(document).on('click', '.btn-plus-scan', function() {
                const input = $(this).closest('.quantity-control-app').find('.box-qty-scan-input');
                const max = parseInt(input.attr('max'));
                let val = parseInt(input.val()) || 0;
                if (val < max) {
                    val++;
                    input.val(val).trigger('change');
                }
            });

            $(document).on('click', '.btn-minus-scan', function() {
                const input = $(this).closest('.quantity-control-app').find('.box-qty-scan-input');
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    val--;
                    input.val(val).trigger('change');
                }
            });

            $(document).on('change', '.box-qty-scan-input', function() {
                const key = $(this).closest('.card').data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));
                
                if (qty < 0) qty = 0;
                if (qty > max) qty = max;
                $(this).val(qty);

                if (qty > 0) {
                    cart.set(key, {
                        product_id: $(this).data('product-id'),
                        color_id: $(this).data('color-id'),
                        size_set_id: $(this).data('size-set-id'),
                        qty: qty,
                        pcs_per_box: parseFloat($(this).data('pcs')),
                        unit_price: parseFloat($(this).data('price'))
                    });
                } else {
                    cart.delete(key);
                }
                updateUI();
            });

            // Load from session storage
            const saved = sessionStorage.getItem(storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                Object.keys(data).forEach(key => cart.set(key, data[key]));
            }

            $('#toggleFilters').click(function () {
                $('#filterContainer').slideToggle();
            });

            $('#btnShowDetails').click(function () {
                $('#detailsModal').modal('show');
            });

            // --- INFINITE SCROLL / LOAD MORE ---
            let nextPage = {{ $boxes->nextPageUrl() ? ($boxes->currentPage() + 1) : 'null' }};
            let loading = false;
            const container = $('#variation-container');

            $('#loadMoreBtn').click(function () {
                loadMore();
            });

            function loadMore() {
                if (loading || !nextPage) return;
                loading = true;
                $('#loading-spinner').show();
                $('#loadMoreBtn').prop('disabled', true).text('Loading...');

                let formData = $('#filterForm').serialize();
                let requestData = formData + '&load_more=1&page=' + nextPage;

                $.ajax({
                    url: window.location.pathname,
                    method: 'GET',
                    data: requestData,
                    success: function (response) {
                        container.append(response.html);
                        nextPage = response.next_page;
                        loading = false;
                        $('#loading-spinner').hide();

                        if (!nextPage) {
                            $('#loadMoreBtn').hide();
                        } else {
                            $('#loadMoreBtn').prop('disabled', false).text('Load More Variations');
                        }
                        updateUI();
                    },
                    error: function () {
                        loading = false;
                        $('#loading-spinner').hide();
                        $('#loadMoreBtn').prop('disabled', false).text('Load More Variations');
                    }
                });
            }

            function updateUI() {
                let totalBoxes = 0;
                let subTotal = 0;
                const seePrice = {{ Auth::guard('sales_agent')->user()->see_price ? 'true' : 'false' }};

                cart.forEach((item) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                const otherCharges = parseFloat($('#other_charges').val()) || 0;
                const discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                const taxableAmount = subTotal - discountAmount;

                let gstAmount = parseFloat($('#gstAmountInput').val()) || 0;
                const defaultGstPercent = {{ $gst_percentage }};
                if (!$('#gstAmountInput').is(':focus') && (gstAmount === 0 || !$('#gstAmountInput').data('manual')) && taxableAmount > 0) {
                    gstAmount = taxableAmount * (defaultGstPercent / 100);
                    $('#gstAmountInput').val(gstAmount.toFixed(2));
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;

                $('#selectedCount').text(totalBoxes);
                $('#subTotalAmount').text(subTotal.toFixed(2));
                $('#taxableAmount').text(taxableAmount.toFixed(2));
                $('#grandTotalAmount').text(grandTotal.toFixed(2));

                if (totalBoxes > 0) {
                    $('#summaryBar').fadeIn();
                } else {
                    $('#summaryBar').fadeOut();
                }

                $('.variation-card').each(function () {
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

                const storageObj = {};
                cart.forEach((val, key) => { if (val.qty > 0) storageObj[key] = val; });
                sessionStorage.setItem(storageKey, JSON.stringify(storageObj));
            }

            $('#gstAmountInput').on('input', function() {
                $(this).data('manual', true);
                updateUI();
            });

            $(document).on('change', '.box-qty-input', function () {
                const card = $(this).closest('.variation-card');
                const key = card.data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0;
                if (qty > max) { qty = max; $(this).val(qty); }

                if (qty > 0) {
                    cart.set(key, {
                        product_id: card.data('product-id'),
                        color_id: card.data('color-id'),
                        size_set_id: card.data('size-set-id'),
                        qty: qty,
                        pcs_per_box: parseFloat(card.data('pcs')),
                        unit_price: parseFloat(card.data('price'))
                    });
                    
                    // Move the card to the top for better visibility
                    const row = card.closest('.variation-row-container');
                    if (!row.hasClass('has-qty-top')) {
                        row.addClass('has-qty-top').prependTo('#variation-container');
                        // Small scroll adjustment to keep user focused
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } else {
                    cart.delete(key);
                    card.closest('.variation-row-container').removeClass('has-qty-top');
                }
                updateUI();
            });

            $(document).on('input', '#discountAmountInput, #other_charges', function () {
                updateUI();
            });

            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current < parseInt(input.attr('max'))) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) input.val(current - 1).trigger('change');
            });

            $('.place-order-btn').click(function () {
                const btn = $(this);
                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) return;

                Swal.fire({
                    title: 'Confirm Order',
                    text: "Create order for " + variations.reduce((a, b) => a + b.qty, 0) + " boxes?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    confirmButtonColor: '#007bff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                        $.ajax({
                            url: "{{ route('agent.orders.store') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                shop_id: "{{ $shop->id }}",
                                order_date: "{{ date('Y-m-d') }}",
                                order_type: 'normal',
                                sale_type: 'item',
                                variations: variations,
                                expected_dispatch_date: $('#expectedDispatchDate').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                other_charges: $('#other_charges').val(),
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    sessionStorage.removeItem(storageKey);
                                    Swal.fire('Ordered!', 'Your order has been placed successfully.', 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    btn.prop('disabled', false).html('Confirm Order <i class="fas fa-arrow-right ml-2"></i>');
                                }
                            }
                        });
                    }
                });
            });

            updateUI();
        });
    </script>
@endpush