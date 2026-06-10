@extends('admin.layouts.app')

@section('content')
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        .content-wrapper {
            background-color: var(--bg-main);
            padding-bottom: 100px;
        }

        .premium-header {
            padding: 2rem 0;
            border-bottom: 1px solid var(--border);
            margin-bottom: 2rem;
            background: #fff;
        }

        .filter-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 2rem;
        }

        .product-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .product-card:hover {
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            transform: translateY(-2px);
        }

        .size-set-chip {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1px solid var(--border);
            margin: 0.25rem;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            background: #fff;
        }

        .size-set-chip.selected {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .size-set-chip input {
            display: none;
        }

        .btn-generate {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(99, 102, 241, 0.4);
            color: #fff;
        }
    </style>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="premium-header py-3 mb-4 d-flex justify-content-between align-items-center">
                <h1 class="h5 font-weight-bold text-dark mb-0">Create Sample Set</h1>
                <p class="text-muted small mb-0">Filter and select size sets to generate barcodes.</p>
            </div>

            <div class="row">
                <div class="col-md-9">
                    <div class="filter-card py-3 mb-3">
                        <div class="row">
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Brand</label>
                                <select id="brand_id" class="form-control select2">
                                    <option value="">Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Fitting</label>
                                <select id="fitting_id" class="form-control select2">
                                    <option value="">Fitting</option>
                                    @foreach($fittings as $fitting)
                                        <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Pattern</label>
                                <select id="pattern_id" class="form-control select2">
                                    <option value="">Pattern</option>
                                    @foreach($patterns as $pattern)
                                        <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Series</label>
                                <select id="series_id" class="form-control select2">
                                    <option value="">Series</option>
                                    @foreach($series as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Design No</label>
                                <select id="design_number" class="form-control select2">
                                    <option value="">Design No</option>
                                    @foreach($designNumbers as $dn)
                                        <option value="{{ $dn }}">{{ $dn }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">Size Set</label>
                                <select id="size_set_id" class="form-control select2">
                                    <option value="">Size Set</option>
                                    @foreach($sizeSets as $ss)
                                        <option value="{{ $ss->id }}">{{ $ss->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">MRP From</label>
                                <input type="number" id="mrp_from" class="form-control" placeholder="Min MRP">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="font-weight-bold x-small text-uppercase text-muted mb-1">MRP To</label>
                                <input type="number" id="mrp_to" class="form-control" placeholder="Max MRP">
                            </div>
                        </div>
                        <div class="text-right mt-2">
                            <button type="button" id="btn-filter" class="btn btn-primary px-4 btn-sm">
                                <i class="fas fa-search mr-2"></i> Find Products
                            </button>
                        </div>
                    </div>

                    <div id="product-container" class="row">
                        <!-- Products will be loaded here as col-md-6 -->
                        <div class="col-12 text-center py-5 text-muted bg-white rounded border">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <p>Use filters to find products</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="premium-sidebar">
                        <h5 class="h6 font-weight-bold mb-4 text-dark d-flex align-items-center">
                            <i class="fas fa-shopping-cart mr-2 text-primary"></i>
                            {{ isset($batch) ? 'Edit Sample Set' : 'Selected Sample Items' }}
                        </h5>

                        <form
                            action="{{ isset($batch) ? route('admin.inventory.fair-product.update', $batch->id) : route('admin.inventory.fair-product.store') }}"
                            method="POST" id="fairForm" class="mb-3">
                            @csrf
                            @if(isset($batch)) @method('PUT') @endif

                            <div class="form-group mb-3">
                                <label class="x-small font-weight-bold text-uppercase text-muted mb-1">Sales Agents</label>
                                <select name="sales_agent_ids[]" class="form-control select2" multiple>
                                    @foreach($salesAgents as $agent)
                                        <option value="{{ $agent->id }}" {{ isset($batch) && is_array($batch->sales_agent_ids) && in_array($agent->id, $batch->sales_agent_ids) ? 'selected' : '' }}>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="hidden-inputs"></div>
                            <button type="submit" class="btn btn-generate w-100 position-static py-2 mb-3" id="btn-submit"
                                style="display: none;">
                                <i class="fas fa-save mr-2"></i> {{ isset($batch) ? 'Update Sample Set' : 'Generate Sample Set' }}
                            </button>
                        </form>

                        <div id="global-discount-container" class="mb-3" style="display: none;">
                            <label class="x-small font-weight-bold text-uppercase text-muted mb-1">Global Discount %</label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="global-discount" class="form-control"
                                    placeholder="Type to apply to all..." min="0" max="100" step="0.01">
                                <div class="input-group-append">
                                    <span class="input-group-text x-small">%</span>
                                </div>
                            </div>
                        </div>

                        <div id="selected-list" style="flex: 1; overflow-y: auto;">
                            <div class="text-center py-4 text-muted border rounded border-dashed">
                                <p class="mb-0 small">Empty</p>
                            </div>
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
                        barcodeCount: item.barcodeCount || 1,
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

            $('#btn-filter').on('click', function () {
                let brand_id = $('#brand_id').val();
                let fitting_id = $('#fitting_id').val();
                let pattern_id = $('#pattern_id').val();
                let series_id = $('#series_id').val();
                let design_number = $('#design_number').val();
                let size_set_id = $('#size_set_id').val();
                let mrp_from = $('#mrp_from').val();
                let mrp_to = $('#mrp_to').val();

                if (!brand_id && !fitting_id && !pattern_id && !series_id && !design_number && !size_set_id && !mrp_from && !mrp_to) {
                    toastr.error('Please select at least one filter');
                    return;
                }

                $('#product-container').html('<div class="col-12 text-center py-5 bg-white rounded border"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');

                $.ajax({
                    url: "{{ route('admin.inventory.fair-product.get-products') }}",
                    type: 'GET',
                    data: {
                        brand_id: brand_id,
                        fitting_id: fitting_id,
                        pattern_id: pattern_id,
                        series_id: series_id,
                        design_number: design_number,
                        size_set_id: size_set_id,
                        mrp_from: mrp_from,
                        mrp_to: mrp_to
                    },
                    success: function (response) {
                        if (response.length === 0) {
                            $('#product-container').html('<div class="col-12 text-center py-5 text-muted bg-white rounded border"><i class="fas fa-search fa-3x mb-3"></i><p>No products found</p></div>');
                            return;
                        }

                        let html = '';
                        response.forEach(product => {
                            let seriesName = product.series ? product.series.name : '';
                            let fullName = seriesName + ' ' + product.name_of_garment;

                            let imagePath = product.display_image;
                            imageUrl = imagePath ? "{{ asset('assets/products') }}/" + imagePath : "{{ asset('assets/img/no-image.png') }}";


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
                                        <label class="size-set-chip py-1 px-3 small ${isChecked ? 'selected' : ''}" 
                                            data-product-id="${product.id}" 
                                            data-size-id="${size.id}" 
                                            data-mrp="${size.mrp}"
                                            style="font-size: 11px;">
                                            <input type="checkbox" ${isChecked ? 'checked' : ''}>
                                            ${size.name}
                                        </label>
                                    `;
                                });
                            }

                            let stockHtml = '';
                            if (product.color_stock && product.color_stock.length > 0) {
                                stockHtml = '<div class="mt-2 border-top pt-1"><p class="x-small font-weight-bold text-muted mb-1 text-uppercase">Stock (Boxes):</p><div class="d-flex flex-wrap">';
                                product.color_stock.forEach(stock => {
                                    stockHtml += `<span class="badge badge-light border mr-1 mb-1" style="font-size: 10px; color: #1b4332;">${stock.color.name}: <b>${stock.total_boxes}</b></span>`;
                                });
                                stockHtml += '</div></div>';
                            }

                            html += `
                            <div class="col-md-6 mb-3">
                                <div class="product-card p-2 border-0 shadow-sm h-100">
                                    <div class="d-flex">
                                        <img src="${imageUrl}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                        <div class="ml-2 flex-grow-1" style="min-width: 0;">
                                            <h6 class="mb-0 font-weight-bold text-truncate text-dark" title="${fullName}">${fullName}</h6>
                                            <p class="text-primary x-small font-weight-bold mb-1"># ${product.design_number}</p>
                                            <div class="d-flex flex-wrap">
                                                ${sizeHtml}
                                            </div>
                                            ${stockHtml}
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
                    selectedItems[key] = { productId, sizeId, mrp, discount: 0, barcodeCount: 1 };
                } else {
                    delete selectedItems[key];
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
                    <div class="selected-item p-3 mb-3 border rounded bg-white shadow-sm animate-in" style="border-left: 4px solid var(--primary) !important;">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div style="flex: 1; min-width: 0;">
                                <div class="font-weight-bold text-dark text-truncate mb-0" style="font-size: 13px;">${p.garment}</div>
                                <div class="x-small text-muted">#${p.designNo} | ${s.name}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-selected" data-key="${key}">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        <div class="row no-gutters align-items-center mt-2">
                            <div class="col-4">
                                <div class="x-small font-weight-bold text-muted text-uppercase">MRP: ₹${item.mrp}</div>
                            </div>
                            <div class="col-4 px-1">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control discount-input" placeholder="Disc %" value="${item.discount}" data-key="${key}" step="0.01" min="0" max="100">
                                    <div class="input-group-append">
                                        <span class="input-group-text x-small">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control barcode-count-input" title="Barcode Count" placeholder="Count" value="${item.barcodeCount}" data-key="${key}" step="1" min="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text x-small"><i class="fas fa-barcode"></i></span>
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
                        <input type="hidden" name="items[${count}][barcode_count]" value="${item.barcodeCount}">
                    `;
                });

                if (count === 0) {
                    $('#selected-list').html('<div class="text-center py-4 text-muted border rounded border-dashed"><p class="mb-0 small">Empty</p></div>');
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
                // Focus back to the global input after render
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

            $(document).on('input', '.barcode-count-input', function () {
                let key = $(this).data('key');
                let val = parseInt($(this).val()) || 1;
                if (selectedItems[key]) {
                    selectedItems[key].barcodeCount = val;
                    $(`input[name$="[barcode_count]"]`).each(function (index) {
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