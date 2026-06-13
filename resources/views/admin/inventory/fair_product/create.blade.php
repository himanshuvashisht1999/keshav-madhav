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

        .premium-sidebar {
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
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

        /* Premium Product Card Styles */
        .product-card-premium {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .product-card-premium:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
            border-color: #cbd5e1;
        }
        .badge-primary-light {
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #e0e7ff;
            font-size: 12px;
        }
        .size-item-premium {
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }
        .size-item-premium:hover {
            border-color: #cbd5e1;
        }
        .size-item-premium.selected {
            border-color: #6366f1;
            background-color: #fff !important;
            box-shadow: 0 0 0 1px #6366f1;
        }
        .color-pill-premium {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            user-select: none;
            font-weight: 600;
        }
        .color-pill-premium:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .color-pill-premium.active {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5;
        }
        .color-pill-premium.active .badge {
            background: #fff !important;
            color: #4f46e5 !important;
        }
    </style>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="premium-header py-3 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h5 font-weight-bold text-dark mb-0">{{ isset($batch) ? 'Edit Sample Set' : 'Create Sample Set' }}</h1>
                    <p class="text-muted small mb-0">Filter and select size sets to generate barcodes.</p>
                </div>
                <button type="button" class="btn btn-primary shadow-sm" data-toggle="modal" data-target="#selectedItemsModal">
                    <i class="fas fa-shopping-cart mr-2"></i> Selected Items <span class="badge badge-light text-primary ml-1" id="selected-count" style="font-size: 14px;">0</span>
                </button>
            </div>

            <div class="row">
                <div class="col-md-12">
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
                            <button type="button" id="btn-reset-filters" class="btn btn-secondary px-4 btn-sm mr-2">
                                <i class="fas fa-undo mr-2"></i> Reset Filters
                            </button>
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
            </div>

            <div class="modal fade" id="selectedItemsModal" tabindex="-1" role="dialog" aria-labelledby="selectedItemsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                        <div class="modal-header border-0 bg-light px-4 py-3">
                            <h5 class="modal-title font-weight-bold text-dark d-flex align-items-center" id="selectedItemsModalLabel">
                                <i class="fas fa-shopping-cart mr-2 text-primary"></i>
                                {{ isset($batch) ? 'Edit Sample Set' : 'Selected Sample Items' }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body p-4 bg-white">
                            <form
                                action="{{ isset($batch) ? route('admin.inventory.fair-product.update', $batch->id) : route('admin.inventory.fair-product.store') }}"
                                method="POST" id="fairForm" class="mb-3">
                                @csrf
                                @if(isset($batch)) @method('PUT') @endif

                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label class="x-small font-weight-bold text-uppercase text-muted mb-1">Sales Agents</label>
                                            <select name="sales_agent_ids[]" class="form-control select2-modal" multiple>
                                                @foreach($salesAgents as $agent)
                                                    <option value="{{ $agent->id }}" {{ isset($batch) && is_array($batch->sales_agent_ids) && in_array($agent->id, $batch->sales_agent_ids) ? 'selected' : '' }}>{{ $agent->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div id="global-discount-container" style="display: none;">
                                            <label class="x-small font-weight-bold text-uppercase text-muted mb-1">Global Discount %</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="global-discount" class="form-control"
                                                    placeholder="Type to apply to all..." min="0" max="100" step="0.01">
                                                <div class="input-group-append">
                                                    <span class="input-group-text x-small">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-right">
                                        <button type="submit" class="btn btn-generate position-static py-2 px-4 w-100" id="btn-submit"
                                            style="display: none;">
                                            <i class="fas fa-save mr-2"></i> {{ isset($batch) ? 'Update Sample Set' : 'Generate Sample Set' }}
                                        </button>
                                    </div>
                                </div>

                                <div id="hidden-inputs"></div>
                            </form>

                            <div class="table-responsive mt-4 border rounded">
                                <table class="table table-bordered table-hover bg-white mb-0" id="selected-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="x-small text-uppercase font-weight-bold">Product</th>
                                            <th class="x-small text-uppercase font-weight-bold">Design & Size</th>
                                            <th class="x-small text-uppercase font-weight-bold">MRP</th>
                                            <th class="x-small text-uppercase font-weight-bold">Discount %</th>
                                            <th class="x-small text-uppercase font-weight-bold">Barcode Count</th>
                                            <th class="x-small text-uppercase font-weight-bold text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-list">
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted border-dashed">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="fas fa-box-open fa-2x mb-2 opacity-50"></i>
                                                    <p class="mb-0 small">No items selected yet.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif

            let selectedItems = {};
            let productDetails = {};
            let sizeDetails = {};

            @if(isset($existingItems))
                const existingData = @json($existingItems);
                existingData.forEach(item => {
                    selectedItems[item.productId + '-' + item.sizeId] = {
                        productId: item.productId,
                        sizeId: item.sizeId,
                        colorIds: item.colorIds ? item.colorIds.map(id => parseInt(id)) : [],
                        discount: item.discount,
                        barcodeCount: item.barcodeCount || 1,
                        mrp: item.mrp
                    };
                });
                
                // Set product details for existing items without loading from API
                @foreach($existingItems as $item)
                    productDetails['{{ $item["productId"] }}'] = {
                        designNo: '{{ $item["designNo"] }}',
                        garment: '{!! addslashes($item["garment"]) !!}'
                    };
                    sizeDetails['{{ $item["sizeId"] }}'] = { name: '{{ $item["sizeName"] }}' };
                @endforeach
                
                // Update count badge initially
                $('#selected-count').text(Object.keys(selectedItems).length);
                renderSelectedList();
            @endif

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
            
            // Re-initialize select2 for modal elements after the modal is shown
            $('#selectedItemsModal').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $('#selectedItemsModal')
                });
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
                $('#product-container').html('<div class="col-12 text-center py-5 text-muted bg-white rounded border"><i class="fas fa-box-open fa-3x mb-3"></i><p>Use filters to find products</p></div>');
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
                                    let selectedColors = isChecked && selectedItems[key].colorIds ? selectedItems[key].colorIds.map(id => parseInt(id)) : [];

                                    let colorHtml = '';
                                    if (size.colors && size.colors.length > 0) {
                                        colorHtml = `<div class="colors-drawer mt-2 pt-2 border-top" style="${isChecked ? '' : 'display:none;'}">
                                            <p class="text-muted mb-2 font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">AVAILABLE COLORS</p>
                                            <div class="d-flex flex-wrap" style="gap: 6px;">`;
                                        size.colors.forEach(color => {
                                            let isColorChecked = selectedColors.includes(color.id);
                                            colorHtml += `
                                                <label class="color-pill-premium ${isColorChecked ? 'active' : ''}">
                                                    <input type="checkbox" class="color-checkbox d-none" value="${color.id}" data-key="${key}" ${isColorChecked ? 'checked' : ''}>
                                                    ${color.name}
                                                    <span class="badge ${isColorChecked ? 'badge-light' : 'badge-light text-muted border'} ml-1" style="font-size: 9.5px;">${color.total_boxes}</span>
                                                </label>
                                            `;
                                        });
                                        colorHtml += `</div></div>`;
                                    }

                                    sizeHtml += `
                                        <div class="size-item-premium mb-2 bg-white rounded p-2 ${isChecked ? 'selected' : ''}">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="custom-control custom-checkbox mb-0 size-set-chip" data-product-id="${product.id}" data-size-id="${size.id}" data-mrp="${size.mrp}" style="cursor: pointer; display: flex; align-items: center; width: 100%;">
                                                    <input type="checkbox" class="custom-control-input" ${isChecked ? 'checked' : ''}>
                                                    <span class="custom-control-label font-weight-bold text-dark" style="font-size: 13px;">${size.name}</span>
                                                </label>
                                                <span class="text-muted small font-weight-bold">₹${size.mrp}</span>
                                            </div>
                                            ${colorHtml}
                                        </div>
                                    `;
                                });
                            }

                            html += `
                            <div class="col-md-6 mb-4">
                                <div class="product-card-premium h-100 d-flex flex-column">
                                    <div class="d-flex align-items-start p-3 border-bottom bg-white">
                                        <img src="${imageUrl}" class="rounded shadow-sm" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #e2e8f0;">
                                        <div class="ml-3 flex-grow-1" style="min-width: 0;">
                                            <h5 class="mb-1 font-weight-bold text-dark text-truncate" style="font-size: 15px;" title="${fullName}">${fullName}</h5>
                                            <span class="badge badge-primary-light px-2 py-1"># ${product.design_number}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 flex-grow-1" style="background: #f8fafc;">
                                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Select Sizes & Colors</h6>
                                        <div class="sizes-list">
                                            ${sizeHtml}
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

            $(document).on('click', '.size-set-chip', function (e) {
                // Prevent event firing if clicked on custom-control-input directly,
                // because label click will trigger it anyway
                if ($(e.target).hasClass('color-checkbox') || $(e.target).closest('.colors-drawer').length > 0) {
                    return;
                }
                
                let chip = $(this);
                let container = chip.closest('.size-item-premium');
                let checkbox = chip.find('input[type="checkbox"]').first();
                
                // If the user clicked the label (not the checkbox itself), toggle the checkbox manually
                // Since this is a custom-control label, clicking it toggles the checkbox automatically.
                // We just need to sync our state. Wait for the event loop so the checkbox value is updated.
                setTimeout(() => {
                    let productId = chip.data('product-id');
                    let sizeId = chip.data('size-id');
                    let mrp = chip.data('mrp');
                    let key = productId + '-' + sizeId;

                    let isChecked = checkbox.prop('checked');
                    container.toggleClass('selected', isChecked);
                    
                    let colorOptions = container.find('.colors-drawer');

                    if (isChecked) {
                        selectedItems[key] = { productId, sizeId, mrp, discount: 0, barcodeCount: 1, colorIds: [] };
                        colorOptions.slideDown(200);
                    } else {
                        delete selectedItems[key];
                        colorOptions.slideUp(200);
                        colorOptions.find('.color-checkbox').prop('checked', false);
                        colorOptions.find('.color-pill-premium').removeClass('active');
                    }
                    renderSelectedList();
                }, 10);
            });

            $(document).on('change', '.color-checkbox', function(e) {
                let key = $(this).data('key');
                let colorId = parseInt($(this).val());
                let pill = $(this).closest('.color-pill-premium');
                
                if (!selectedItems[key]) return;
                
                if (!selectedItems[key].colorIds) {
                    selectedItems[key].colorIds = [];
                }
                
                if ($(this).prop('checked')) {
                    if (!selectedItems[key].colorIds.includes(colorId)) {
                        selectedItems[key].colorIds.push(colorId);
                    }
                    pill.addClass('active');
                    pill.find('.badge').removeClass('text-muted border').addClass('text-primary');
                } else {
                    selectedItems[key].colorIds = selectedItems[key].colorIds.filter(id => id !== colorId);
                    pill.removeClass('active');
                    pill.find('.badge').addClass('text-muted border').removeClass('text-primary');
                }
                renderHiddenInputsOnly();
            });

            function renderHiddenInputsOnly() {
                let hiddenHtml = '';
                let count = 0;
                Object.keys(selectedItems).forEach(key => {
                    let item = selectedItems[key];
                    count++;
                    hiddenHtml += `
                        <input type="hidden" name="items[${count}][product_id]" value="${item.productId}">
                        <input type="hidden" name="items[${count}][size_set_id]" value="${item.sizeId}">
                        <input type="hidden" name="items[${count}][discount_percent]" value="${item.discount}">
                        <input type="hidden" name="items[${count}][barcode_count]" value="${item.barcodeCount}">
                    `;
                    if (item.colorIds && item.colorIds.length > 0) {
                        item.colorIds.forEach(colorId => {
                            hiddenHtml += `<input type="hidden" name="items[${count}][color_ids][]" value="${colorId}">`;
                        });
                    } else {
                        hiddenHtml += `<input type="hidden" name="items[${count}][color_ids][]" value="">`;
                    }
                });
                $('#hidden-inputs').html(hiddenHtml);
            }

            function renderSelectedList() {
                let html = '';
                let count = 0;

                Object.keys(selectedItems).forEach(key => {
                    let item = selectedItems[key];
                    let p = productDetails[item.productId];
                    let s = sizeDetails[item.sizeId];
                    count++;

                    html += `
                    <tr>
                        <td class="align-middle">
                            <div class="font-weight-bold text-dark text-truncate mb-0" style="max-width: 250px;" title="${p.garment}">${p.garment}</div>
                        </td>
                        <td class="align-middle">
                            <div class="small text-muted">#${p.designNo} | ${s.name}</div>
                        </td>
                        <td class="align-middle">
                            <div class="small font-weight-bold text-muted text-uppercase">₹${item.mrp}</div>
                        </td>
                        <td class="align-middle">
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <input type="number" class="form-control discount-input" placeholder="Disc %" value="${item.discount}" data-key="${key}" step="0.01" min="0" max="100">
                                <div class="input-group-append">
                                    <span class="input-group-text small">%</span>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <input type="number" class="form-control barcode-count-input" title="Barcode Count" placeholder="Count" value="${item.barcodeCount}" data-key="${key}" step="1" min="1">
                                <div class="input-group-append">
                                    <span class="input-group-text small"><i class="fas fa-barcode"></i></span>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-selected" data-key="${key}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    `;
                });

                if (count === 0) {
                    $('#selected-list').html('<tr><td colspan="6" class="text-center py-5 text-muted border-dashed"><div class="d-flex flex-column align-items-center justify-content-center"><i class="fas fa-box-open fa-2x mb-2 opacity-50"></i><p class="mb-0 small">No items selected yet.</p></div></td></tr>');
                    $('#btn-submit').hide();
                    $('#global-discount-container').hide();
                } else {
                    $('#selected-list').html(html);
                    $('#btn-submit').show();
                    $('#global-discount-container').show();
                }
                
                $('#selected-count').text(count);
                renderHiddenInputsOnly();
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