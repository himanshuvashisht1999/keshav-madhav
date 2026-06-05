@extends('sales_agent.layouts.app', ['title' => 'Edit Order'])

@section('content')
    <div class="content-wrapper bg-light" style="min-height: 100vh; padding-bottom: 180px;">
        <!-- Header App Bar -->
        <div class="bg-white shadow-sm sticky-top" style="z-index: 1040;">
            <div class="container-fluid py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 1.1rem;">Edit Order #{{ $order->id }}
                        </h5>
                        <small class="text-muted"><i class="fas fa-store mr-1"></i> {{ $shop->name }}</small>
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
                        <a href="{{ route('agent.orders.show', $order->id) }}" class="btn btn-light btn-sm rounded-circle"
                            style="width: 36px; height: 36px;">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order-Level Settings and Filters -->
            <form method="GET" action="{{ route('agent.orders.edit', $order->id) }}" id="filterForm">
                <div class="container-fluid pt-3 px-3">
                    <div class="custom-control custom-switch border p-2 rounded bg-white shadow-sm" style="border-radius: 10px !important;">
                        <input type="checkbox" class="custom-control-input" id="sampleSetToggle" name="sample_set" value="1" {{ (request()->has('product_name') ? request('sample_set') == '1' : $order->is_sample_set == 1) ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="custom-control-label font-weight-bold ml-2 pt-1 text-primary" for="sampleSetToggle" style="cursor:pointer; user-select: none;">Use Sample Set Pricing for this Order</label>
                    </div>
                </div>

                <!-- Collapsible Filters -->
                <div id="filterContainer" style="display: none;"
                    class="bg-white border-top animate__animated animate__fadeInDown p-3 shadow-sm mt-2">
                    <div class="row" id="filters-container">
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
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Brand</label>
                            <select name="brand_id" class="form-control form-control-sm select2">
                                <option value="">All Brands</option>
                                @foreach($brands as $id => $name)
                                    <option value="{{ $id }}" {{ request('brand_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Fitting</label>
                            <select name="fitting_id" class="form-control form-control-sm select2">
                                <option value="">All Fittings</option>
                                @foreach($fittings as $id => $name)
                                    <option value="{{ $id }}" {{ request('fitting_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Pattern</label>
                            <select name="pattern_id" class="form-control form-control-sm select2">
                                <option value="">All Patterns</option>
                                @foreach($patterns as $id => $name)
                                    <option value="{{ $id }}" {{ request('pattern_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Product Nature</label>
                            <select name="product_nature_id" class="form-control form-control-sm select2">
                                <option value="">All Natures</option>
                                @foreach($product_natures as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_nature_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <label class="small text-muted font-weight-bold uppercase mb-1">Fabric Type</label>
                            <select name="fabric_type_id" class="form-control form-control-sm select2">
                                <option value="">All Fabric Types</option>
                                @foreach($fabric_types as $id => $name)
                                    <option value="{{ $id }}" {{ request('fabric_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex">
                        <button type="submit"
                            class="btn btn-primary btn-sm flex-grow-1 mr-2 rounded-pill font-weight-bold">Apply
                            Filters</button>
                        <a href="{{ route('agent.orders.edit', $order->id) }}" class="btn btn-light btn-sm rounded-pill"><i
                                class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Main Content Area -->
        <div class="container-fluid mt-3">
            <!-- Variation Cards -->
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
                            <h5 class="text-dark font-weight-bold">Looking for Products?</h5>
                            <p class="text-muted px-4">Use the <b>filters</b> above or <b>scan a barcode</b> to find variations and update your order.</p>
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
                        @if(Auth::guard('sales_agent')->user()->see_price)
                        <span class="h5 font-weight-bold mb-0 d-block text-dark">₹<span
                                id="grandTotalAmount">0</span></span>
                        @endif
                        <small class="text-muted"><span id="selectedCount">0</span> Boxes Selected</small>
                    </div>
                </div>
            </div>
            <button type="button"
                class="btn btn-primary btn-block btn-lg py-3 rounded-xl font-weight-bold shadow-lg" id="btnNextSummary"
                style="border-radius: 12px; font-size: 1.1rem;">
                Next <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    <!-- Image Zoom Modal -->
    <div class="modal fade" id="imageZoomModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0 justify-content-end" style="z-index: 1050;">
                    <button type="button" class="close text-white bg-dark rounded-circle p-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.8;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center pt-0 pb-4" style="overflow: hidden; touch-action: none; position: relative;">
                    <div id="zoom-container">
                        <img src="" id="zoomedImage" class="img-fluid rounded" style="max-height: 80vh; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    </div>
                    <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1060; background: rgba(0,0,0,0.6); padding: 10px; border-radius: 30px; display: flex; gap: 15px;">
                        <button type="button" class="btn btn-light btn-sm rounded-circle" id="btnZoomOut" style="width: 40px; height: 40px;"><i class="fas fa-search-minus"></i></button>
                        <button type="button" class="btn btn-light btn-sm rounded-circle" id="btnZoomIn" style="width: 40px; height: 40px;"><i class="fas fa-search-plus"></i></button>
                    </div>
                </div>
            </div>
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
                <div class="modal-body p-0" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Adjustments Container -->
                    @if(Auth::guard('sales_agent')->user()->see_price)
                    <div class="card shadow-none border-0 mb-2 border-bottom">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="font-weight-bold">₹<span id="subTotalAmount">0</span></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Manual Discount (₹)</label>
                                <input type="number" id="discountAmountInput"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold"
                                    value="{{ $order->discount_amount }}" data-manual="true">
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Taxable Amount:</span>
                                <span class="font-weight-bold">₹<span id="taxableAmount">0</span></span>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">GST Amount (₹)</label>
                                <input type="number" id="gstAmountInput"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold"
                                    value="{{ $order->gst_amount }}" data-manual="true">
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Other Charges (₹)</label>
                                <input type="number" id="other_charges"
                                    class="form-control form-control-lg border-0 bg-light font-weight-bold"
                                    value="{{ $order->other_charges }}">
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="card shadow-none border-0 mb-3" style="border-radius: 15px;">
                        <div class="card-body">
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted uppercase">Dispatch & Shipping</label>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold">Sales Man</label>
                                    <select id="sales_man_id" class="form-control form-control-sm">
                                        <option value="">Select Sales Man (Optional)</option>
                                        @foreach($sales_men as $sm)
                                            <option value="{{ $sm->id }}" {{ $order->sales_man_id == $sm->id ? 'selected' : '' }}>{{ $sm->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold">Expected Dispatch Date</label>
                                    <input type="date" id="expectedDispatchDate" class="form-control form-control-sm"
                                        value="{{ $order->expected_dispatch_date ?: date('Y-m-d', strtotime('+3 days')) }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold">Booking Station</label>
                                    <input type="text" id="booking_station" class="form-control form-control-sm"
                                        placeholder="Booking Station" value="{{ $order->booking_station }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold">Transport Name</label>
                                    <input type="text" id="transport" class="form-control form-control-sm"
                                        placeholder="Transport Name" value="{{ $order->transport }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small text-muted font-weight-bold">Remark</label>
                                    @php
                                        $previousRemarks = \DB::table('master_order_remarks')->where('status', 1)->orderBy('name')->pluck('name');
                                    @endphp
                                    <input type="text" id="remark" class="form-control form-control-sm" list="previous_remarks_list" placeholder="Any special instructions..." value="{{ $order->remark }}" autocomplete="off">
                                    <datalist id="previous_remarks_list">
                                        @foreach($previousRemarks as $rem)
                                            <option value="{{ $rem }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 d-flex">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary flex-grow-1 rounded-pill font-weight-bold update-order-btn">
                        Update Order <i class="fas fa-check ml-1"></i>
                    </button>
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
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
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
    <script src="https://unpkg.com/@panzoom/panzoom@4.5.1/dist/panzoom.min.js"></script>
    <script>
        let allowOverStock = {{ isset($settings) && $settings->agent_app_allow_over_stock ? 'true' : 'false' }};
        let showStock = {{ !isset($settings) || $settings->agent_app_show_stock ? 'true' : 'false' }};
        $(document).ready(function () {

            // Image Zoom functionality
            let pz = null;
            const elem = document.getElementById('zoomedImage');
            
            $(document).on('click', '.zoom-image', function() {
                var src = $(this).attr('src');
                $('#zoomedImage').attr('src', src);
                $('#imageZoomModal').modal('show');
                
                if(pz) {
                    pz.destroy();
                }
                pz = Panzoom(elem, {
                    maxScale: 5,
                    minScale: 1,
                    contain: 'outside'
                });
                elem.parentElement.addEventListener('wheel', pz.zoomWithWheel);
            });
            
            $(document).on('click', '#btnZoomIn', function() {
                if(pz) pz.zoomIn();
            });
            
            $(document).on('click', '#btnZoomOut', function() {
                if(pz) pz.zoomOut();
            });
            
            $('#imageZoomModal').on('hidden.bs.modal', function () {
                if(pz) {
                    pz.reset();
                }
            });

            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }

            let cart = new Map();
            const initialVariations = @json($selected_quantities);

            // Populate cart with initial variations from the order
            Object.keys(initialVariations).forEach(key => {
                const item = initialVariations[key];
                cart.set(key, {
                    product_id: item.product_id,
                    color_id: item.color_id,
                    size_set_id: item.size_set_id,
                    qty: parseInt(item.qty) || 0,
                    pcs_per_box: parseFloat(item.pcs_per_box),
                    unit_price: parseFloat(item.unit_price)
                });
            });

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

                const globalHtml = `
                    <div class="card border-primary shadow-sm mb-3 rounded-lg overflow-hidden" style="background-color: #f8faff;">
                        ${data.product.image ? `<div style="background-color: #f8f9fa; text-align: center; border-bottom: 1px solid #dee2e6;"><img src="${data.product.image}" class="zoom-image" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer;"></div>` : `<div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px; border-bottom: 1px solid #dee2e6;"><i class="fas fa-image fa-3x text-muted opacity-25"></i></div>`}
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="font-weight-bold text-primary mb-0"><i class="fas fa-layer-group mr-2"></i>Apply to All Colors</h6>
                                <small class="text-muted">Set quantity for every color</small>
                            </div>
                            <div class="quantity-control-app d-flex align-items-center p-1 border-primary">
                                <button class="btn-q btn-minus-global text-primary">-</button>
                                <input type="number" class="box-qty-global-input text-primary font-weight-bold" 
                                    min="0"
                                    value="0">
                                <button class="btn-q btn-plus-global text-primary">+</button>
                            </div>
                        </div>
                    </div>
                `;
                list.append(globalHtml);

                data.colors.forEach(color => {
                    const vKey = data.product.id + '_' + color.id + '_' + data.product.size_set_id;
                    const item = cart.get(vKey);
                    const currentQty = item ? item.qty : 0;

                    const html = `
                        <div class="card border-0 shadow-sm mb-3 rounded-lg overflow-hidden" data-key="${vKey}">
                            ${color.image ? `<div style="background-color: #f8f9fa; text-align: center; border-bottom: 1px solid #dee2e6;"><img src="${color.image}" class="zoom-image" style="max-height: 250px; width: auto; max-width: 100%; object-fit: contain; cursor: pointer;"></div>` : `<div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px; border-bottom: 1px solid #dee2e6;"><i class="fas fa-image fa-3x text-muted opacity-25"></i></div>`}
                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold text-dark mb-0">${color.name}</h6>
                                    ${showStock ? `<small class="text-muted">${color.available_boxes} Boxes available</small>` : ''}
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
                if (allowOverStock || val < max) {
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
                if (!allowOverStock && qty > max) qty = max;
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

                    // Append to DOM if not exists
                    if ($('.variation-card[data-key="' + key + '"]').length === 0) {
                        const productName = $('#scanProductName').text();
                        const sizeSetName = $('#scanSizeSet').text();
                        const colorName = $(this).closest('.card').find('h6.text-dark').text();
                        
                        const cardHtml = `
                            <div class="col-md-4 col-lg-3 mb-3 variation-row-container has-qty-top">
                                <div class="card variation-card shadow-sm h-100 has-qty"
                                    data-key="${key}"
                                    data-product-id="${$(this).data('product-id')}"
                                    data-color-id="${$(this).data('color-id')}"
                                    data-size-set-id="${$(this).data('size-set-id')}"
                                    data-price="${$(this).data('price')}"
                                    data-pcs="${$(this).data('pcs')}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-0">${productName}</h6>
                                                <small class="text-muted"><i class="fas fa-palette mr-1"></i> ${colorName}</small>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-light border mb-1"><i class="fas fa-ruler-horizontal mr-1"></i> ${sizeSetName}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <div class="font-weight-bold text-primary">
                                                ₹${$(this).data('price')}
                                            </div>
                                            <div class="quantity-control-app d-flex align-items-center p-1">
                                                <button type="button" class="btn-q btn-minus">-</button>
                                                <input type="number" class="box-qty-input" value="${qty}" max="${$(this).attr('max')}">
                                                <button type="button" class="btn-q btn-plus">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#variation-container').prepend(cardHtml);
                    }
                } else {
                    cart.delete(key);
                }
                updateUI();
            });

            $(document).on('click', '.btn-plus-global', function() {
                const input = $(this).siblings('.box-qty-global-input');
                let val = parseInt(input.val()) || 0;
                val++;
                input.val(val).trigger('change');
            });

            $(document).on('click', '.btn-minus-global', function() {
                const input = $(this).siblings('.box-qty-global-input');
                let val = parseInt(input.val()) || 0;
                if (val > 0) {
                    val--;
                    input.val(val).trigger('change');
                }
            });

            $(document).on('change', '.box-qty-global-input', function() {
                let globalQty = parseInt($(this).val()) || 0;
                if (globalQty < 0) {
                    globalQty = 0;
                    $(this).val(0);
                }

                $('#colorSelectionList .box-qty-scan-input').each(function() {
                    const max = parseInt($(this).attr('max'));
                    let targetQty = globalQty;
                    if (!allowOverStock && targetQty > max) targetQty = max; // Cap at max available
                    
                    if (parseInt($(this).val()) !== targetQty) {
                        $(this).val(targetQty).trigger('change');
                    }
                });
            });

            $('#toggleFilters').click(function () {
                $('#filterContainer').slideToggle();
            });

            $('#btnShowDetails, #btnNextSummary').click(function () {
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
                    }
                });
            }

            function updateUI() {
                // Sync prices from DOM to cart (handles sample set toggle changes)
                $('.variation-card').each(function () {
                    const key = $(this).data('key');
                    if (cart.has(key)) {
                        const item = cart.get(key);
                        item.unit_price = parseFloat($(this).data('price'));
                        
                        const availableText = $(this).find('.available-text');
                        if (availableText.length) {
                            const base = parseInt(availableText.data('base-available')) || 0;
                            availableText.text((base - item.qty) + ' Boxes');
                        }
                    } else {
                        const availableText = $(this).find('.available-text');
                        if (availableText.length) {
                            const base = parseInt(availableText.data('base-available')) || 0;
                            availableText.text(base + ' Boxes');
                        }
                    }
                });

                let totalBoxes = 0;
                let subTotal = 0;

                cart.forEach((item) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                const discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                const taxableAmount = subTotal - discountAmount;

                const otherCharges = parseFloat($('#other_charges').val()) || 0;

                let gstAmount = parseFloat($('#gstAmountInput').val()) || 0;

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
            }

            $(document).on('change', '.box-qty-input', function () {
                const card = $(this).closest('.variation-card');
                const key = card.data('key');
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0;
                if (!allowOverStock && qty > max) { qty = max; $(this).val(qty); }

                if (qty > 0) {
                    cart.set(key, {
                        product_id: card.data('product-id'),
                        color_id: card.data('color-id'),
                        size_set_id: card.data('size-set-id'),
                        qty: qty,
                        pcs_per_box: parseFloat(card.data('pcs')),
                        unit_price: parseFloat(card.data('price'))
                    });

                    // Move to top
                    const row = card.closest('.variation-row-container');
                    if (!row.hasClass('has-qty-top')) {
                        row.addClass('has-qty-top').prependTo('#variation-container');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                } else {
                    cart.delete(key);
                    card.closest('.variation-row-container').removeClass('has-qty-top');
                }
                updateUI();
            });

            $(document).on('input', '#discountAmountInput, #gstAmountInput, #other_charges', function () {
                if ($(this).attr('id') === 'gstAmountInput' || $(this).attr('id') === 'discountAmountInput') {
                    $(this).data('manual', true);
                }
                updateUI();
            });

            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (allowOverStock || current < parseInt(input.attr('max'))) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.variation-card').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) input.val(current - 1).trigger('change');
            });

            $('.update-order-btn').click(function () {
                const btn = $(this);
                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) return;

                Swal.fire({
                    title: 'Update Order?',
                    text: "Update order with " + variations.reduce((a, b) => a + b.qty, 0) + " boxes?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update',
                    confirmButtonColor: '#007bff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
                        $.ajax({
                            url: "{{ route('agent.orders.update', $order->id) }}",
                            method: "POST",
                            data: {
                                _method: 'PUT',
                                _token: "{{ csrf_token() }}",
                                variations: variations,
                                sales_man_id: $('#sales_man_id').val(),
                                expected_dispatch_date: $('#expectedDispatchDate').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                is_sample_set: $('#sampleSetToggle').is(':checked') ? 1 : 0,
                                other_charges: $('#other_charges').val(),
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val()
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire('Updated!', 'Order has been updated successfully.', 'success').then(() => {
                                        window.location.href = response.redirect_url;
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                    btn.prop('disabled', false).html('Update Order <i class="fas fa-save ml-2"></i>');
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
