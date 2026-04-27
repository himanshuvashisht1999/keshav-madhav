@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">Edit Order #ORD-{{ $order->id }}</h1>
                        <p class="text-muted small mb-0"><i class="fas fa-store mr-1"></i> {{ $shop->name ?? 'N/A' }} | <i class="fas fa-user mr-1"></i> Agent: {{ $order->agent->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.agent-orders.index') }}">Agent Orders</a></li>
                            <li class="breadcrumb-item active">Edit Order</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content pb-5 mb-5" style="padding-bottom: 150px !important;">
            <div class="container-fluid">
                <!-- Filters Section -->
                <div class="card shadow-sm border-0 mb-4 bg-light">
                    <div class="card-body p-3">
                        <form method="GET" action="{{ route('admin.agent-orders.edit', $order->id) }}" id="filterForm">
                            <div class="row align-items-end">
                                <div class="col-md-2 col-6 mb-2">
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
                                    <label class="small font-weight-bold text-muted mb-1">Product Name</label>
                                    <select name="product_name" class="form-control form-control-sm select2">
                                        <option value="">All Products</option>
                                        @foreach($product_names as $name)
                                            <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-6 mb-2">
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
                                <div class="col-md-2 col-6 mb-2">
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
                                <div class="col-md-3 col-6 mb-2">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 mr-2">
                                        <i class="fas fa-search mr-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.agent-orders.edit', $order->id) }}" class="btn btn-secondary btn-sm px-3">
                                        <i class="fas fa-undo"></i>
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
                                <i class="fas fa-boxes mr-2 text-primary"></i> Inventory Selection
                            </h6>
                            <span class="badge badge-light border text-muted px-3 py-2">
                                {{ $boxes->total() }} Items Available
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="80">Image</th>
                                        <th>Design & Color</th>
                                        <th>Size Set</th>
                                        <th class="text-center">Pcs/Box</th>
                                        <th class="text-center">Available</th>
                                        <th class="text-right">Price</th>
                                        <th width="150" class="text-center px-4">Order Qty (Boxes)</th>
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
                                        @include('admin.agent_orders.partials.variation_row', ['variation' => $variation, 'image' => $image, 'initialQty' => $initialQty])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div id="loading-spinner" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sticky Bottom Summary Bar -->
        <div class="fixed-bottom bg-white shadow-lg border-top p-3 animate__animated animate__fadeInUp"
            id="summaryBar" style="z-index: 1050; left: 250px;"> <!-- left: 250px to account for sidebar -->
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-2 border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Selected</small>
                        <span class="h5 font-weight-bold text-dark mb-0" id="selectedCount">0</span>
                        <small class="text-muted ml-1">Boxes</small>
                    </div>

                    <div class="col-md-3 border-right">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted font-weight-bold">Subtotal:</small>
                            <span class="font-weight-bold">₹<span id="subTotalAmount">0</span></span>
                        </div>
                        <div class="input-group input-group-sm mb-1" title="Discount Percentage">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0 px-1" style="font-size: 10px;">Disc %</span>
                            </div>
                            <input type="number" id="discountPercentage" class="form-control text-right h-auto py-0 px-1" 
                                style="font-weight: bold;" value="{{ $order->discount_percentage }}" min="0" max="100" step="0.1">
                        </div>
                        <div class="input-group input-group-sm" title="Discount Amount">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0 px-1" style="font-size: 10px;">Disc ₹</span>
                            </div>
                            <input type="number" id="discountAmountInput" class="form-control text-right h-auto py-0 px-1" 
                                style="font-weight: bold;" value="{{ $order->discount_amount }}" min="0">
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
                                style="font-weight: bold;" value="{{ $order->gst_percentage }}" min="0" max="100" step="0.1">
                        </div>
                        <div class="input-group input-group-sm" title="GST Amount">
                            <div class="input-group-prepend">
                                <span class="input-group-text py-0 px-1" style="font-size: 10px;">GST ₹</span>
                            </div>
                            <input type="number" id="gstAmountInput" class="form-control text-right h-auto py-0 px-1" 
                                style="font-weight: bold;" value="{{ $order->gst_amount }}" min="0">
                        </div>
                    </div>

                    <div class="col-md-2 border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Other Charges</small>
                        <div class="input-group input-group-sm mt-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text">₹</span>
                            </div>
                            <input type="number" id="other_charges" class="form-control" value="{{ $order->other_charges ?? 0 }}" min="0" step="1">
                        </div>
                    </div>
                    <div class="col-md-2 border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Expected Dispatch</small>
                        <input type="date" id="expectedDispatchDate" class="form-control form-control-sm mt-1"
                            value="{{ $order->expected_dispatch_date ?: (\Carbon\Carbon::parse($order->expected_dispatch_date)->format('Y-m-d') ?: date('Y-m-d', strtotime('+3 days'))) }}"
                            min="{{ date('Y-m-d') }}">
                    </div>

                    <div class="col-md-2 border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Booking & Transport</small>
                        <input type="text" id="booking_station" class="form-control form-control-sm mt-1" placeholder="Station" value="{{ $order->booking_station }}">
                        <input type="text" id="transport" class="form-control form-control-sm mt-1" placeholder="Transport" value="{{ $order->transport }}">
                    </div>

                    <div class="col-md-2 border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Order Remark</small>
                        <textarea id="remark" class="form-control form-control-sm mt-1" rows="2" placeholder="Notes...">{{ $order->remark }}</textarea>
                    </div>

                    <div class="col-md-2 text-center border-right">
                        <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Grand Total</small>
                        <span class="h4 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmount">0</span></span>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex flex-column gap-1">
                            <button type="button" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm update-order-btn">
                                Update <i class="fas fa-save ml-1"></i>
                            </button>
                            <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn btn-outline-secondary btn-block py-1 font-weight-bold small">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .font-weight-500 { font-weight: 500; }
        .variation-row { transition: background 0.2s; }
        .variation-row:hover { background-color: #f8f9fa; }
        .variation-row.has-qty { background-color: #e3f2fd; }
        .badge-outline-secondary { border: 1px solid #6c757d; color: #6c757d; background: transparent; }
        .quantity-control { max-width: 140px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden; }
        .quantity-control input { border-left: 0; border-right: 0; font-weight: bold; }
        .fixed-bottom { transition: left 0.3s; }
        @media (max-width: 991px) { .fixed-bottom { left: 0 !important; } }
    </style>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            let discount_mode = 'percentage'; // 'percentage' or 'amount'
            let gst_mode = 'percentage';
            let cart = new Map();
            const initialVariations = @json($selected_quantities);

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

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadMore(true);
            });
            // --- INFINITE SCROLL & REAL-TIME FILTER END ---

            Object.keys(initialVariations).forEach(key => {
                const itemData = initialVariations[key];
                cart.set(key, {
                    product_id: itemData.product_id,
                    color_id: itemData.color_id,
                    size_set_id: itemData.size_set_id,
                    qty: parseInt(itemData.qty) || 0,
                    pcs_per_box: parseFloat(itemData.pcs_per_box),
                    unit_price: parseFloat(itemData.unit_price)
                });
            });

            if ($.fn.select2) {
                $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            }


            function updateUI() {
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

                let otherCharges = parseFloat($('#other_charges').val()) || 0;
                let discountAmount = 0;
                let discountPercent = parseFloat($('#discountPercentage').val()) || 0;

                if (discount_mode === 'amount') {
                    discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                    if (!$('#discountPercentage').is(':focus') && subTotal > 0) {
                        $('#discountPercentage').val((discountAmount / subTotal * 100).toFixed(4));
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
                        $('#gstPercentage').val((gstAmount / taxableAmount * 100).toFixed(4));
                    }
                } else {
                    gstAmount = taxableAmount * (gstPercent / 100);
                    if (!$('#gstAmountInput').is(':focus')) {
                        $('#gstAmountInput').val(gstAmount.toFixed(2));
                    }
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;

                $('#selectedCount').text(totalBoxes);
                $('#subTotalAmount').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#taxableAmount').text(taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#grandTotalAmount').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

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
                let qty = parseInt($(this).val()) || 0;
                const max = parseInt($(this).attr('max'));

                if (qty < 0) qty = 0;
                if (qty > max) {
                    Swal.fire('Limit Exceeded', 'Only ' + max + ' boxes available.', 'warning');
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

            $('#other_charges').on('input change', function () {
                updateUI();
            });

            $(document).on('click', '.btn-plus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                const max = parseInt(input.attr('max'));
                if (current < max) input.val(current + 1).trigger('change');
            });

            $(document).on('click', '.btn-minus', function () {
                const input = $(this).closest('.quantity-control').find('.box-qty-input');
                const current = parseInt(input.val()) || 0;
                if (current > 0) input.val(current - 1).trigger('change');
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

                let discountPercent = parseFloat($('#discountInput').val()) || 0;
                let gstPercent = parseFloat($('#gstInput').val()) || 0;
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
                                discount_percentage: $('#discountPercentage').val(),
                                discount_amount: $('#discountAmountInput').val(),
                                gst_percentage: $('#gstPercentage').val(),
                                gst_amount: $('#gstAmountInput').val(),
                                other_charges: otherCharges,
                                expected_dispatch_date: expectedDate,
                                remark: $('#remark').val(),
                                booking_station: $('#booking_station').val(),
                                transport: $('#transport').val()
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
        });
    </script>
@endpush