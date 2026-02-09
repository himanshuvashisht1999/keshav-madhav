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
                        <div class="col-md-4 col-6 mb-2">
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
                        <div class="col-md-4 col-6 mb-2">
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
                        <div class="col-md-4 col-12 mb-2">
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
                                            <div class="text-dark font-weight-500">{{ $variation->design_number }}</div>
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
    <div class="fixed-bottom bg-white shadow-lg border-top p-3 animate__animated animate__fadeInUp" id="summaryBar"
        style="z-index: 1050; display: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3 d-none d-md-block border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Shop</small>
                    <span class="text-dark font-weight-bold truncate d-block">{{ $shop->name }}</span>
                </div>
                <div class="col-md-2 col-4 text-center border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Order Summary</small>
                    <span class="h5 font-weight-bold text-dark mb-0" id="selectedCount">0</span>
                    <small class="text-muted ml-1">Boxes</small>
                </div>
                <div class="col-md-3 col-8 pl-md-4">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Est. Total</small>
                    <span class="h4 font-weight-bold text-primary mb-0">₹<span id="totalAmount">0</span></span>
                    <small class="text-muted ml-1 font-weight-bold" id="totalQtyVal">0 Pcs</small>
                </div>
                <div class="col-md-4 col-12 mt-3 mt-md-0">
                    <button type="button" id="placeOrderBtn"
                        class="btn btn-success btn-block btn-lg py-2 font-weight-bold shadow-sm">
                        Confirm Order <i class="fas fa-check-circle ml-2"></i>
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
                let totalAmount = 0;

                cart.forEach((item, key) => {
                    if (item.qty > 0) {
                        totalBoxes += item.qty;
                        totalPieces += (item.qty * item.pcs_per_box);
                        totalAmount += (item.qty * item.pcs_per_box * item.unit_price);
                    }
                });

                $('#selectedCount').text(totalBoxes);
                $('#totalAmount').text(totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#totalQtyVal').text(totalPieces + ' Pcs total (' + totalBoxes + ' boxes)');

                // Summary bar behavior
                if (totalBoxes > 0) {
                    $('#summaryBar').fadeIn();
                } else {
                    $('#summaryBar').fadeOut();
                }

                // Sync inputs and row classes
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

            // Initial UI Update
            updateUI();

            // Place Order AJAX
            $('#placeOrderBtn').click(function () {
                const btn = $(this);
                const originalText = btn.html();

                let variations = [];
                cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

                if (variations.length === 0) {
                    Swal.fire('Empty Selection', 'Please select at least one item.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Place Order?',
                    text: "Order Summary: " + $('#selectedCount').text() + " boxes total.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Yes, Place Order'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                        $.ajax({
                            url: "{{ route('agent.orders.store') }}",
                            method: "POST",
                            data: {
                                shop_id: "{{ $shop->id }}",
                                variations: variations,
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