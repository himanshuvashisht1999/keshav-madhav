@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">{{ isset($batch) ? 'Edit sample catalog' : 'Create sample catalog' }}</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item text-muted">Filter and select size sets to generate barcodes</span>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.inventory.sample-product.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to catalogs
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            {{-- ================= FILTER CARD ================= --}}
            <div class="card erp-card mb-3">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Brand</label>
                            <select id="brand_id" class="form-control form-control-sm select2">
                                <option value="">Brand</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Fitting</label>
                            <select id="fitting_id" class="form-control form-control-sm select2">
                                <option value="">Fitting</option>
                                @foreach($fittings as $fitting)
                                    <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Pattern</label>
                            <select id="pattern_id" class="form-control form-control-sm select2">
                                <option value="">Pattern</option>
                                @foreach($patterns as $pattern)
                                    <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Series</label>
                            <select id="series_id" class="form-control form-control-sm select2">
                                <option value="">Series</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Design no</label>
                            <select id="design_number" class="form-control form-control-sm select2">
                                <option value="">Design no</option>
                                @foreach($designNumbers as $dn)
                                    <option value="{{ $dn }}">{{ $dn }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label mb-1">Size set</label>
                            <select id="size_set_id" class="form-control form-control-sm select2">
                                <option value="">Size set</option>
                                @foreach($sizeSets as $ss)
                                    <option value="{{ $ss->id }}">{{ $ss->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3 mb-2">
                            <label class="erp-label mb-1">MRP from</label>
                            <input type="number" id="mrp_from" class="form-control form-control-sm" placeholder="Min MRP">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label mb-1">MRP to</label>
                            <input type="number" id="mrp_to" class="form-control form-control-sm" placeholder="Max MRP">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label mb-1">Qty from (boxes)</label>
                            <input type="number" id="qty_from" class="form-control form-control-sm" placeholder="Min qty">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label mb-1">Qty to (boxes)</label>
                            <input type="number" id="qty_to" class="form-control form-control-sm" placeholder="Max qty">
                        </div>
                    </div>
                    <div class="text-right mt-2">
                        <button type="button" id="btn-reset-filters" class="btn-erp btn-erp-outline mr-2">
                            <i class="fas fa-undo mr-1"></i> Reset filters
                        </button>
                        <button type="button" id="btn-filter" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Find products
                        </button>
                    </div>
                </div>
            </div>

            {{-- ================= PRODUCTS CONTAINER ================= --}}
            <div id="product-container" class="row">
                <div class="col-12 text-center py-5 text-muted bg-white rounded border">
                    <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                    <p class="mb-0">Use filters above to find products</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            {{-- ================= SIDEBAR ================= --}}
            <div class="card erp-card p-3 sticky-top" style="top: 15px;">
                <h5 class="font-weight-bold mb-3 text-dark d-flex align-items-center" style="font-size: 14px;">
                    <i class="fas fa-shopping-cart mr-2" style="color: var(--brand-dark-green, #05421c);"></i>
                    {{ isset($batch) ? 'Edit catalog' : 'Selected samples' }}
                </h5>

                <form
                    action="{{ isset($batch) ? route('admin.inventory.sample-product.update', $batch->id) : route('admin.inventory.sample-product.store') }}"
                    method="POST" id="sampleForm" class="mb-2">
                    @csrf
                    @if(isset($batch)) @method('PUT') @endif
                    <div id="hidden-inputs"></div>
                    <button type="submit" class="btn-erp btn-erp-primary w-100 py-2 mb-2" id="btn-submit"
                        style="display: none; justify-content: center; font-size: 13px;">
                        <i class="fas fa-save mr-1"></i> {{ isset($batch) ? 'Update catalog' : 'Generate catalog' }}
                    </button>
                </form>

                <div id="global-discount-container" class="mb-3" style="display: none;">
                    <label class="erp-label mb-1">Global discount %</label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="global-discount" class="form-control form-control-sm"
                            placeholder="Apply to all..." min="0" max="100" step="0.01">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <div id="selected-list" style="max-height: calc(100vh - 280px); overflow-y: auto;">
                    <div class="text-center py-4 text-muted border rounded border-dashed">
                        <p class="mb-0 small">No samples selected</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {
        let selectedItems = {};
        let productDetails = {};
        let sizeDetails = {};

        @if(isset($existingItems))
            const existingData = @json($existingItems);
            existingData.forEach(item => {
                selectedItems[item.productId + '-' + item.sizeId] = {
                    productId: item.productId,
                    sizeId: item.sizeId,
                    discount: item.discount,
                    mrp: item.mrp
                };
                productDetails[item.productId] = {
                    designNo: item.designNo,
                    garment: item.garment
                };
                sizeDetails[item.sizeId] = {
                    name: item.sizeName
                };
            });
            setTimeout(renderSelectedList, 100);
        @endif

        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        $('#btn-reset-filters').on('click', function() {
            $('#brand_id').val('').trigger('change');
            $('#fitting_id').val('').trigger('change');
            $('#pattern_id').val('').trigger('change');
            $('#series_id').val('').trigger('change');
            $('#design_number').val('').trigger('change');
            $('#size_set_id').val('').trigger('change');
            $('#mrp_from').val('');
            $('#mrp_to').val('');
            $('#qty_from').val('');
            $('#qty_to').val('');
            $('#product-container').html('<div class="col-12 text-center py-5 text-muted bg-white rounded border"><i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i><p class="mb-0">Use filters above to find products</p></div>');
        });

        $('#btn-filter').on('click', function () {
            let brand_id = $('#brand_id').val();
            let fitting_id = $('#fitting_id').val();
            let pattern_id = $('#pattern_id').val();
            let series_id = $('#series_id').val();
            let design_number = $('#design_number').val();
            let size_set_id = $('#size_set_id').val();
            let mrp_from = $('#mrp_from').val();
            let mrp_to = $('#mrp_to').val();
            let qty_from = $('#qty_from').val();
            let qty_to = $('#qty_to').val();

            if (!brand_id && !fitting_id && !pattern_id && !series_id && !design_number && !size_set_id && !mrp_from && !mrp_to && !qty_from && !qty_to) {
                toastr.error('Please select at least one filter');
                return;
            }

            $('#product-container').html('<div class="col-12 text-center py-5 bg-white rounded border"><i class="fas fa-spinner fa-spin fa-2x text-muted mb-2"></i><p class="mb-0 text-muted">Loading products...</p></div>');

            $.ajax({
                url: "{{ route('admin.inventory.sample-product.get-products') }}",
                type: 'GET',
                data: {
                    brand_id: brand_id,
                    fitting_id: fitting_id,
                    pattern_id: pattern_id,
                    series_id: series_id,
                    design_number: design_number,
                    size_set_id: size_set_id,
                    mrp_from: mrp_from,
                    mrp_to: mrp_to,
                    qty_from: qty_from,
                    qty_to: qty_to
                },
                success: function (response) {
                    if (response.length === 0) {
                        $('#product-container').html('<div class="col-12 text-center py-5 text-muted bg-white rounded border"><i class="fas fa-search fa-3x mb-3 text-secondary d-block"></i><p class="mb-0">No products found</p></div>');
                        return;
                    }

                    let html = '';
                    response.forEach(product => {
                        let seriesName = product.series ? product.series.name : '';
                        let fullName = seriesName + ' ' + product.name_of_garment;

                        let imagePath = product.display_image;
                        let imageUrl = imagePath ? "{{ asset('assets/products') }}/" + imagePath : "{{ asset('assets/img/no-image.png') }}";

                        productDetails[product.id] = {
                            designNo: product.design_number,
                            garment: fullName
                        };

                        let sizeHtml = '';
                        if (Array.isArray(product.available_sizes)) {
                            product.available_sizes.forEach(size => {
                                if (!size) return;
                                sizeDetails[size.id] = { name: size.name };
                                let key = product.id + '-' + size.id;
                                let isChecked = !!selectedItems[key];
                                sizeHtml += `
                                    <label class="size-set-chip ${isChecked ? 'selected' : ''}" 
                                        data-product-id="${product.id}" 
                                        data-size-id="${size.id}" 
                                        data-mrp="${size.mrp}">
                                        <input type="checkbox" ${isChecked ? 'checked' : ''}>
                                        ${size.name}
                                    </label>
                                `;
                            });
                        }

                        let allSizesStock = product.all_sizes_color_stock || {};

                        function buildStockHtml(colorStockArr) {
                            if (!colorStockArr || colorStockArr.length === 0) return '';
                            let s = '<div class="mt-2 border-top pt-1 stock-section"><p class="x-small font-weight-bold text-muted mb-1 text-uppercase">Stock (boxes):</p><div class="d-flex flex-wrap">';
                            colorStockArr.forEach(stock => {
                                let colorName = stock.color ? stock.color.name : (stock.color_name || 'N/A');
                                s += `<span class="badge badge-light border mr-1 mb-1" style="font-size: 10px; color: var(--brand-dark-green, #05421c);">${colorName}: <b>${stock.total_boxes}</b></span>`;
                            });
                            s += '</div></div>';
                            return s;
                        }

                        let initialStockData = product.color_stock;
                        let stockHtml = buildStockHtml(initialStockData);

                        html += `
                        <div class="col-md-6 mb-3">
                            <div class="catalog-product-card h-100" data-product-id="${product.id}" data-all-stock='${JSON.stringify(allSizesStock)}'>
                                <div class="d-flex">
                                    <img src="${imageUrl}" class="rounded border" style="width: 75px; height: 75px; object-fit: cover;">
                                    <div class="ml-2 flex-grow-1" style="min-width: 0;">
                                        <h6 class="mb-0 font-weight-bold text-truncate text-dark" style="font-size: 13px;" title="${fullName}">${fullName}</h6>
                                        <p class="x-small font-weight-bold mb-1" style="color: var(--brand-dark-green, #05421c);"># ${product.design_number}</p>
                                        <div class="d-flex flex-wrap">
                                            ${sizeHtml}
                                        </div>
                                        <div class="stock-display">${stockHtml}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    $('#product-container').html(html);
                },
                error: function () {
                    toastr.error('Error loading products');
                }
            });
        });

        $(document).on('click', '.size-set-chip', function () {
            let chip = $(this);
            let checkbox = chip.find('input');
            let productId = chip.data('product-id');
            let sizeId = chip.data('size-id');
            let mrp = chip.data('mrp');
            let key = productId + '-' + sizeId;

            checkbox.prop('checked', !checkbox.prop('checked'));
            chip.toggleClass('selected', checkbox.prop('checked'));

            if (checkbox.prop('checked')) {
                selectedItems[key] = { productId, sizeId, mrp, discount: 0 };
            } else {
                delete selectedItems[key];
            }

            // Aggregate stock for all selected sizes on this product card
            let card = chip.closest('.catalog-product-card');
            let allStock = card.data('all-stock') || {};
            let stockDisplay = card.find('.stock-display');

            let selectedSizeIds = [];
            let selectedSizeNames = [];
            card.find('.size-set-chip input:checked').each(function() {
                let parentChip = $(this).closest('.size-set-chip');
                selectedSizeIds.push(String(parentChip.data('size-id')));
                selectedSizeNames.push(parentChip.text().trim());
            });

            if (selectedSizeIds.length === 0) {
                let allCombined = {};
                Object.values(allStock).forEach(function(sizeStockArr) {
                    (sizeStockArr || []).forEach(function(stock) {
                        let cn = stock.color_name || 'N/A';
                        allCombined[cn] = (allCombined[cn] || 0) + parseInt(stock.total_boxes);
                    });
                });
                let entries = Object.entries(allCombined);
                if (entries.length > 0) {
                    let s = '<div class="mt-2 border-top pt-1"><p class="x-small font-weight-bold text-muted mb-1 text-uppercase">Stock (boxes):</p><div class="d-flex flex-wrap">';
                    entries.forEach(function([cn, boxes]) {
                        s += `<span class="badge badge-light border mr-1 mb-1" style="font-size: 10px; color: var(--brand-dark-green, #05421c);">${cn}: <b>${boxes}</b></span>`;
                    });
                    s += '</div></div>';
                    stockDisplay.html(s);
                } else {
                    stockDisplay.html('');
                }
            } else {
                let merged = {};
                selectedSizeIds.forEach(function(sid) {
                    let sizeStockArr = allStock[sid] || [];
                    sizeStockArr.forEach(function(stock) {
                        let cn = stock.color_name || 'N/A';
                        merged[cn] = (merged[cn] || 0) + parseInt(stock.total_boxes);
                    });
                });

                let label = selectedSizeNames.join(' + ');
                let entries = Object.entries(merged);
                if (entries.length > 0) {
                    let s = '<div class="mt-2 border-top pt-1"><p class="x-small font-weight-bold text-muted mb-1 text-uppercase">Stock (boxes) - ' + label + ':</p><div class="d-flex flex-wrap">';
                    entries.forEach(function([cn, boxes]) {
                        s += `<span class="badge badge-light border mr-1 mb-1" style="font-size: 10px; color: var(--brand-dark-green, #05421c);">${cn}: <b>${boxes}</b></span>`;
                    });
                    s += '</div></div>';
                    stockDisplay.html(s);
                } else {
                    stockDisplay.html('<div class="mt-2 border-top pt-1"><p class="x-small text-muted mb-0">No stock for selected sizes</p></div>');
                }
            }

            renderSelectedList();
        });

        function renderSelectedList() {
            let html = '';
            let count = 0;
            let hiddenHtml = '';

            Object.keys(selectedItems).forEach(key => {
                let item = selectedItems[key];
                let p = productDetails[item.productId];
                let s = sizeDetails[item.sizeId];
                count++;

                html += `
                <div class="catalog-selected-item">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div style="flex: 1; min-width: 0;">
                            <div class="font-weight-bold text-dark text-truncate mb-0" style="font-size: 12.5px;">${p.garment}</div>
                            <div class="x-small text-muted">#${p.designNo} | ${s.name}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 ml-1 btn-remove-selected" data-key="${key}">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <div class="row no-gutters align-items-center mt-1">
                        <div class="col-6">
                            <div class="x-small font-weight-bold text-muted">MRP: ₹${item.mrp}</div>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control form-control-sm discount-input" placeholder="Disc %" value="${item.discount}" data-key="${key}" step="0.01" min="0" max="100">
                                <div class="input-group-append">
                                    <span class="input-group-text x-small">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
                hiddenHtml += `
                    <input type="hidden" name="items[${count}][product_id]" value="${item.productId}">
                    <input type="hidden" name="items[${count}][size_set_id]" value="${item.sizeId}">
                    <input type="hidden" name="items[${count}][discount_percent]" value="${item.discount}">
                `;
            });

            if (count === 0) {
                $('#selected-list').html('<div class="text-center py-4 text-muted border rounded border-dashed"><p class="mb-0 small">No samples selected</p></div>');
                $('#btn-submit').hide();
                $('#global-discount-container').hide();
            } else {
                $('#selected-list').html(html);
                $('#btn-submit').show();
                $('#global-discount-container').show();
            }
            $('#hidden-inputs').html(hiddenHtml);
        }

        $('#global-discount').on('input', function () {
            let globalDisc = parseFloat($(this).val()) || 0;

            Object.keys(selectedItems).forEach(key => {
                selectedItems[key].discount = globalDisc;
            });

            renderSelectedList();
            $('#global-discount').focus();
        });

        $(document).on('input', '.discount-input', function () {
            let key = $(this).data('key');
            let val = parseFloat($(this).val()) || 0;
            if (selectedItems[key]) {
                selectedItems[key].discount = val;
                $(`input[name$="[discount_percent]"]`).each(function (index) {
                    if (Object.keys(selectedItems)[index] === key) {
                        $(this).val(val);
                    }
                });
            }
        });

        $(document).on('click', '.btn-remove-selected', function () {
            let key = $(this).data('key');
            let item = selectedItems[key];

            delete selectedItems[key];

            if (item) {
                $(`.size-set-chip[data-product-id="${item.productId}"][data-size-id="${item.sizeId}"]`).removeClass('selected').find('input').prop('checked', false);
            }

            renderSelectedList();
        });
    });
</script>
@endsection