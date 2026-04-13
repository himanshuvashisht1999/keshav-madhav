@extends('admin.layouts.app')

@section('title', 'Create Direct Sales Order')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shopping-basket mr-2"></i>Direct Sales: {{ $shop->name }}</h1>
                    <p class="text-muted small mb-0"><i class="fas fa-store mr-1"></i> Warehouse Direct Purchase</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.direct-sales.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-exchange-alt mr-1"></i> Change Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Section -->
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.direct-sales.create') }}" id="filterForm">
                        <input type="hidden" name="master_customer_id" value="{{ $shop->id }}">
                        
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
                                <a href="{{ route('admin.direct-sales.create', ['master_customer_id' => $shop->id]) }}" 
                                   class="btn btn-secondary btn-sm px-3">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Inventory Display -->
            <div class="card shadow-sm border-0 overflow-hidden mb-5" style="border-radius: 12px; margin-bottom: 120px !important;">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0 text-dark">
                            <i class="fas fa-boxes mr-2 text-primary"></i> Available Stock
                        </h6>
                        <span class="badge badge-light border text-muted px-3 py-2">
                            {{ $boxes->total() }} Variant Rows
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="pl-4">Design / Product</th>
                                    <th>Variant</th>
                                    <th class="text-center">Pcs/Box</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-right">Price</th>
                                    <th width="150" class="text-center px-4">Sales Qty (Boxes)</th>
                                </tr>
                            </thead>
                            <tbody id="variation-container">
                                @forelse($boxes as $variation)
                                    @php
                                        $vKey = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
                                    @endphp
                                    <tr class="variation-row" 
                                        data-key="{{ $vKey }}"
                                        data-product-id="{{ $variation->product_id }}"
                                        data-color-id="{{ $variation->color_id }}"
                                        data-size-set-id="{{ $variation->size_set_id }}"
                                        data-pcs="{{ $variation->pcs_per_box }}"
                                        data-price="{{ $variation->unit_price }}">
                                        <td class="pl-4">
                                            <div class="font-weight-bold text-dark">{{ $variation->design_number }}</div>
                                            <small class="text-muted">{{ $variation->series_name }} {{ $variation->name_of_garment }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-outline-secondary mr-1">{{ $variation->color_name }}</span>
                                            <span class="badge badge-light">{{ $variation->size_set_name }}</span>
                                            <div class="mt-1 small">
                                                <span class="text-muted">Pattern: {{ $variation->pattern_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold">{{ number_format($variation->pcs_per_box) }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-{{ $variation->available_boxes > 5 ? 'success' : 'warning' }} px-2">
                                                {{ $variation->available_boxes }} boxes
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <div class="font-weight-bold text-success">₹{{ number_format($variation->unit_price, 2) }}</div>
                                            @if($variation->mrp > $variation->unit_price)
                                                <del class="text-muted small">MRP: ₹{{ number_format($variation->mrp, 2) }}</del>
                                            @endif
                                        </td>
                                        <td class="px-4">
                                            <div class="quantity-control d-flex align-items-center">
                                                <button type="button" class="btn btn-sm btn-light btn-minus border-right"><i class="fas fa-minus"></i></button>
                                                <input type="number" class="form-control form-control-sm text-center box-qty-input border-0" 
                                                       value="0" min="0" max="{{ $variation->available_boxes }}" 
                                                       placeholder="0" style="padding: 0; width: 50px;">
                                                <button type="button" class="btn btn-sm btn-light btn-plus border-left"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No stock found for selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Sticky Summary Bar -->
    <div class="fixed-bottom bg-white shadow-lg border-top p-3 animate__animated animate__fadeInUp" 
         id="summaryBar" style="z-index: 1050; left: 250px; display: none;">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-2 border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Total Selected</small>
                    <span class="h5 font-weight-bold text-dark mb-0" id="selectedCount">0</span>
                    <small class="text-muted ml-1">Boxes</small>
                </div>
                
                <div class="col-md-3 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Subtotal:</small>
                        <span class="font-weight-bold">₹<span id="subTotalAmount">0</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted font-weight-bold">Discount (%):</small>
                        <input type="number" id="discountPercentage" class="form-control form-control-sm text-right h-auto py-0 px-1" 
                            style="width: 60px; font-weight: bold;" value="0" min="0" max="100" step="0.1">
                    </div>
                </div>

                <div class="col-md-2 border-right">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Taxable:</small>
                        <span class="font-weight-bold">₹<span id="taxableAmount">0</span></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted font-weight-bold">GST ({{ $gst_percentage }}%):</small>
                        <span class="font-weight-bold text-danger">+₹<span id="gstAmount">0</span></span>
                    </div>
                </div>

                <div class="col-md-2 text-center border-right">
                    <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Final Amount</small>
                    <span class="h4 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmount">0</span></span>
                </div>

                <div class="col-md-3">
                    <button type="button" class="btn btn-success btn-lg btn-block py-2 font-weight-bold shadow-sm finalize-order-btn">
                        Finalize & Sell <i class="fas fa-check-double ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .variation-row { transition: background 0.2s; }
    .variation-row:hover { background-color: #f8f9fa; }
    .variation-row.has-qty { background-color: #e3f2fd; }
    .quantity-control { box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-radius: 6px; overflow: hidden; border: 1px solid #dee2e6; }
    .quantity-control input { font-weight: bold; }
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

        let cart = new Map();
        const storageKey = 'admin_direct_sales_cart_{{ $shop->id }}';
        
        // Sync with session storage
        const saved = sessionStorage.getItem(storageKey);
        if (saved) {
            const data = JSON.parse(saved);
            Object.keys(data).forEach(key => cart.set(key, data[key]));
        }

        function updateUI() {
            let totalBoxes = 0;
            let subTotal = 0;

            cart.forEach((item) => {
                if (item.qty > 0) {
                    totalBoxes += item.qty;
                    subTotal += (item.qty * item.pcs_per_box * item.unit_price);
                }
            });

            const discountPercent = parseFloat($('#discountPercentage').val()) || 0;
            const discountAmount = subTotal * (discountPercent / 100);
            const taxableAmount = subTotal - discountAmount;
            const gstPercent = {{ $gst_percentage }};
            const gstAmount = taxableAmount * (gstPercent / 100);
            const grandTotal = taxableAmount + gstAmount;

            $('#selectedCount').text(totalBoxes);
            $('#subTotalAmount').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#taxableAmount').text(taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#gstAmount').text(gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
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
                Swal.fire('Limit Exceeded', 'Only ' + max + ' available in inventory.', 'warning');
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

        $('.finalize-order-btn').click(function() {
            const btn = $(this);
            let variations = [];
            cart.forEach((item) => { if (item.qty > 0) variations.push(item); });

            if (variations.length === 0) {
                Swal.fire('Empty Order', 'Please select at least one item.', 'info');
                return;
            }

            Swal.fire({
                title: 'Finalize Direct Sale?',
                text: "Inventory will be deducted immediately and an invoice will be generated.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Sell Now'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                    $.ajax({
                        url: "{{ route('admin.direct-sales.store') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            master_customer_id: "{{ $shop->id }}",
                            variations: variations,
                            discount_percentage: $('#discountPercentage').val(),
                            gst_percentage: {{ $gst_percentage }}
                        },
                        success: function(response) {
                            if (response.success) {
                                sessionStorage.removeItem(storageKey);
                                Swal.fire('Sold!', response.message, 'success').then(() => {
                                    window.location.href = response.redirect_url;
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                btn.prop('disabled', false).html('Finalize & Sell <i class="fas fa-check-double ml-2"></i>');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Transaction failed', 'error');
                            btn.prop('disabled', false).html('Finalize & Sell <i class="fas fa-check-double ml-2"></i>');
                        }
                    });
                }
            });
        });

        updateUI();
    });
</script>
@endpush
