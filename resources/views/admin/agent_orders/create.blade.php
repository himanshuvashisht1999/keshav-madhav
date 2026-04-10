@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            @if(!isset($boxes))
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-plus-circle mr-2"></i>Create New Agent Order</h1>
                        <p class="text-muted">Initiate a new order by selecting an agent and a customer.</p>
                    </div>
                </div>
            @else
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-shopping-basket mr-2"></i>New Order: {{ $shop->name }}</h1>
                        <p class="text-muted small mb-0"><i class="fas fa-user-tie mr-1"></i> Agent: {{ $agent->name }}</p>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.agent-orders.create') }}" class="btn btn-outline-secondary btn-sm">
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
                                    
                                    <div class="form-group mb-4">
                                        <label class="text-muted small font-weight-bold text-uppercase">Select Sales Agent <span class="text-danger">*</span></label>
                                        <select name="sales_agent_id" id="agentSelect" class="form-control select2 @error('sales_agent_id') is-invalid @enderror" required>
                                            <option value="">-- Choose Agent --</option>
                                            <option value="direct" {{ request('sales_agent_id') == 'direct' ? 'selected' : '' }}>-- Direct (No Agent) --</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ old('sales_agent_id') == $agent->id ? 'selected' : '' }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="text-muted small font-weight-bold text-uppercase">Select Party / Customer <span class="text-danger">*</span></label>
                                        <select name="master_customer_id" id="customerSelect" class="form-control select2 @error('master_customer_id') is-invalid @enderror" required>
                                            <option value="">-- Choose Customer --</option>
                                            @foreach($shops as $shop)
                                                <option value="{{ $shop->id }}" {{ old('master_customer_id') == $shop->id ? 'selected' : '' }}>
                                                    {{ $shop->name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                            Next: Select Items <i class="fas fa-arrow-right ml-2"></i>
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
                        <div class="card shadow-sm border-0 mb-4 bg-light">
                            <div class="card-body p-3">
                                <form method="GET" action="{{ route('admin.agent-orders.create') }}" id="filterForm">
                                    <input type="hidden" name="sales_agent_id" value="{{ $agent->id }}">
                                    <input type="hidden" name="master_customer_id" value="{{ $shop->id }}">
                                    <input type="hidden" name="order_date" value="{{ request('order_date') }}">
                                    
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
                                            <a href="{{ route('admin.agent-orders.create', ['sales_agent_id' => $agent->id, 'master_customer_id' => $shop->id, 'order_date' => request('order_date')]) }}" 
                                               class="btn btn-secondary btn-sm px-3">
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
                                    <span class="badge badge-light border text-muted px-3 py-2">
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
                                                <th class="text-right">Price (Agent)</th>
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

                            <div class="col-md-2 border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Expected Dispatch</small>
                                <input type="date" id="expectedDispatchDate" class="form-control form-control-sm mt-1" 
                                    value="{{ date('Y-m-d', strtotime('+3 days')) }}" min="{{ date('Y-m-d') }}">
                            </div>

                            <div class="col-md-2 text-center border-right">
                                <small class="text-muted d-block uppercase tracking-wider font-weight-bold">Grand Total</small>
                                <span class="h4 font-weight-bold text-primary mb-0">₹<span id="grandTotalAmount">0</span></span>
                            </div>

                            <div class="col-md-3">
                                <button type="button" class="btn btn-success btn-lg btn-block py-2 font-weight-bold shadow-sm place-order-btn">
                                    Place Order <i class="fas fa-check-circle ml-2"></i>
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

        // Function to load shops
        function loadShops(agentId, selectedShopId = null) {
            const customerSelect = $('#customerSelect');
            customerSelect.empty().append('<option value="">-- Loading Shops... --</option>');
            
            if (agentId) {
                $.ajax({
                    url: "{{ route('admin.agent-orders.get-shops') }}",
                    method: "GET",
                    data: { 
                        agent_id: agentId === 'direct' ? '' : agentId,
                        is_direct: agentId === 'direct' ? 1 : 0
                    },
                    success: function(shops) {
                        customerSelect.empty().append('<option value="">-- Choose Customer --</option>');
                        shops.forEach(shop => {
                            const selected = (selectedShopId == shop.id) ? 'selected' : '';
                            customerSelect.append(`<option value="${shop.id}" ${selected}>${shop.name}</option>`);
                        });
                        customerSelect.trigger('change');
                    },
                    error: function() {
                        customerSelect.empty().append('<option value="">-- Error loading shops --</option>');
                    }
                });
            } else {
                customerSelect.empty().append('<option value="">-- Select Agent First --</option>');
            }
        }

        // Handle Agent Change
        $('#agentSelect').on('change', function() {
            loadShops($(this).val());
        });

        // Trigger on load if agent is already selected (e.g. from old input)
        if ($('#agentSelect').val()) {
            loadShops($('#agentSelect').val(), "{{ old('master_customer_id') }}");
        } else {
            $('#customerSelect').empty().append('<option value="">-- Select Agent First --</option>');
        }

        @if(isset($boxes))
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
                                master_customer_id: "{{ $shop->id }}",
                                order_date: "{{ request('order_date', date('Y-m-d')) }}",
                                variations: variations,
                                expected_dispatch_date: $('#expectedDispatchDate').val(),
                                discount_percentage: $('#discountPercentage').val()
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
