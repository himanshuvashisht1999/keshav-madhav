@extends('admin.layouts.app')
@section('content')
    <style>
        .modal-ratio {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-ratio-content {
            background: #fff;
            width: 420px;
            padding: 20px;
            border-radius: 8px;
        }

        .size-row-ratio {
            display: flex;
            justify-content: space-between;
            padding: 6px;
            background: #eef2f7;
            margin-top: 6px;
        }

        .counter-ratio button {
            width: 28px;
            height: 28px;
            border: none;
            background: #28a745;
            color: #fff;
        }

        .open-ratio-label {
            cursor: pointer;
            color: #007bff;
            font-weight: 600;
            text-decoration: underline;
            font-size: 13px;
            margin-top: 5px;
            display: inline-block;
        }
    </style>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Edit Product Specification</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Edit Product</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <form action="{{route('admin.master.production-goods.update')}}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $data->id }}">
                        <div class="card-body">
                            <div class="row">
                                <input type="hidden" name="company_id" value="2" id="company_id">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Design Number</label>
                                        <input type="text" name="design_number" class="form-control"
                                            value="{{ $data->design_number }}"
                                            {{ $data->is_locked_in_inventory ? 'disabled' : '' }}>
                                        @if($data->is_locked_in_inventory)
                                            <input type="hidden" name="design_number" value="{{ $data->design_number }}">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Series Name</label>
                                        <select name="master_series_id" id="master_series_id" class="form-control select2"
                                            style="width: 100%;">
                                            <option value="">Select Series</option>
                                            @foreach($series_names as $series)
                                                <option value="{{ $series->id }}" {{ $data->master_series_id == $series->id ? 'selected' : '' }}>{{ $series->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Brand</label>
                                        <select name="brand_id" class="form-control select2" style="width: 100%;">
                                            <option value="">Select Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ $data->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Product Name</label>
                                        <input type="text" name="name_of_garment" class="form-control"
                                            value="{{ $data->name_of_garment }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fitting</label>
                                        <select name="master_product_fitting_id"
                                            class="form-control select2"
                                            style="width: 100%;" {{ $data->is_locked_in_inventory ? 'disabled' : '' }}>
                                            <option value="">Select Fitting</option>
                                            @foreach($fittings as $fit)
                                                <option value="{{ $fit->id }}" {{ $data->master_product_fitting_id == $fit->id ? 'selected' : '' }}>{{ $fit->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($data->is_locked_in_inventory)
                                            <input type="hidden" name="master_product_fitting_id"
                                                value="{{ $data->master_product_fitting_id }}">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Pattern</label>
                                        <select name="master_pattern_id" class="form-control select2"
                                            style="width: 100%;" {{ $data->is_locked_in_inventory ? 'disabled' : '' }}>
                                            <option value="">Select Pattern</option>
                                            @foreach($garment_patterns as $p)
                                                <option value="{{ $p->id }}" {{ $data->master_pattern_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                        @if($data->is_locked_in_inventory)
                                            <input type="hidden" name="master_pattern_id"
                                                value="{{ $data->master_pattern_id }}">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="card card-secondary">
                                        <div class="card-header">
                                            <h3 class="card-title">Size Sets & Pricing</h3>
                                            <div class="card-tools">
                                                <button type="button" class="btn btn-primary btn-sm add-size-set">Add More
                                                    Size Set</button>
                                            </div>
                                        </div>
                                        <div class="card-body" id="size-set-container">
                                            @php $sIdx = 0; @endphp
                                            @forelse($data->variants as $variant)
                                                <div class="size-set-block mb-4 p-3 border rounded bg-light">
                                                    <div class="row align-items-end mb-3">
                                                        <div class="col-md-4">
                                                            <input type="hidden" name="variant_ids[]"
                                                                value="{{ $variant->id }}">
                                                            <div class="form-group mb-0">
                                                                <label>Size Set</label>
                                                                <select name="size_sets[]"
                                                                    class="form-control select2 size-set-select"
                                                                    {{ $variant->is_locked_in_inventory ? 'disabled' : '' }}>
                                                                    <option value="">Select Size Set</option>
                                                                    @foreach($sizes as $size)
                                                                        <option value="{{ $size->id }}"
                                                                            data-set-group="{{ $size->size_group }}"
                                                                            data-pcs="{{ $size->no_of_pcs }}" {{ $variant->master_size_measurement_id == $size->id ? 'selected' : '' }}>
                                                                            {{ $size->name }} ({{ $size->no_of_pcs }} Pcs)
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @if($variant->is_locked_in_inventory)
                                                                    <input type="hidden" name="size_sets[]"
                                                                        value="{{ $variant->master_size_measurement_id }}">
                                                                @endif
                                                                <div class="open-ratio-label openCustomSizeBtn"
                                                                    style="{{ $variant->master_size_measurement_id ? '' : 'display:none;' }}">
                                                                    Update Ratio
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group mb-0">
                                                                <label>MRP (for this set)</label>
                                                                <input type="number" name="mrps[]"
                                                                    class="form-control mrp-input" value="{{ $variant->mrp }}"
                                                                    step="0.01">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group mb-0">
                                                                <label>Set Image</label>
                                                                <input type="file" name="size_set_images[]"
                                                                    class="form-control-file size-set-image-input"
                                                                    accept="image/*">
                                                                @if($variant->image)
                                                                    <a href="{{ asset('assets/products/' . $variant->image) }}"
                                                                        target="_blank">
                                                                        <img src="{{ asset('assets/products/' . $variant->image) }}"
                                                                            class="img-thumbnail mt-1" style="max-height: 40px;">
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-right">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-size-set"
                                                                {{ $variant->is_locked_in_inventory ? 'disabled title=Locked_in_Inventory' : '' }}><i
                                                                    class="fa fa-trash"></i> Remove Set</button>
                                                        </div>
                                                    </div>

                                                    <div class="color-items-container ml-4">
                                                        <h6>Colors & Images</h6>
                                                        @php $cIdx = 0; @endphp
                                                        @foreach($variant->items as $item)
                                                            <div
                                                                class="color-item-row row mb-2 align-items-center border-bottom pb-2">
                                                                <input type="hidden"
                                                                    name="variant_item_ids[{{ $sIdx }}][{{ $cIdx }}]"
                                                                    value="{{ $item->id }}">
                                                                <div class="col-md-4">
                                                                    <label class="small">Color</label>
                                                                    <select name="variant_colors[{{ $sIdx }}][{{ $cIdx }}]"
                                                                        class="form-control select2 color-select"
                                                                        {{ $item->is_locked_in_inventory ? 'disabled' : '' }}>
                                                                        <option value="">Select Color</option>
                                                                        @foreach($colors as $color)
                                                                            <option value="{{ $color->id }}" {{ $item->master_color_id == $color->id ? 'selected' : '' }}>
                                                                                {{ $color->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    @if($item->is_locked_in_inventory)
                                                                        <input type="hidden"
                                                                            name="variant_colors[{{ $sIdx }}][{{ $cIdx }}]"
                                                                            value="{{ $item->master_color_id }}">
                                                                    @endif
                                                                    @if($item->barcode)
                                                                        <div class="mt-1 small">
                                                                            <strong>Barcode:</strong> <span
                                                                                class="badge badge-info">{{ $item->barcode }}</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="small text-muted d-block">Change Image</label>
                                                                    <input type="file"
                                                                        name="variant_images[{{ $sIdx }}][{{ $cIdx }}]"
                                                                        class="form-control-file d-inline variant-image-input"
                                                                        accept="image/*" style="width: auto;">
                                                                    @if($item->image)
                                                                        <a href="{{ asset('assets/products/' . $item->image) }}"
                                                                            target="_blank">
                                                                            <img src="{{ asset('assets/products/' . $item->image) }}"
                                                                                class="img-thumbnail ml-2" style="max-height: 40px;">
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <div class="col-md-2 text-right">
                                                                    <button type="button"
                                                                        class="btn btn-warning btn-sm remove-color-item"
                                                                        {{ $item->is_locked_in_inventory ? 'disabled title=Locked_in_Inventory' : '' }}><i
                                                                            class="fa fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                            @php $cIdx++; @endphp
                                                        @endforeach
                                                    </div>
                                                    <div class="ml-4 mt-2">
                                                        <button type="button" class="btn btn-info btn-sm add-color-item"
                                                            data-set-index="{{ $sIdx }}"><i class="fa fa-plus"></i> Add Another
                                                            Color</button>
                                                    </div>
                                                </div>
                                                @php $sIdx++; @endphp
                                            @empty
                                                <div class="size-set-block mb-4 p-3 border rounded bg-light">
                                                    <p class="text-muted">No variants defined. Click "Add More" to create one.
                                                    </p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 text-right mt-3">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">Update Product
                                        Specification</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('scripts')
    <!-- CUSTOM SIZE MODAL -->
    <div class="modal-ratio" id="sizeRatioModal">
        <div class="modal-ratio-content">
            <div class="modal-header">
                <h4>Update Ratio</h4>
                <span class="close" onclick="closeRatioModal()" style="cursor:pointer;">&times;</span>
            </div>
            <div class="modal-body">
                <div class="output mb-2">
                    <span>Size Name :</span>
                    <strong id="ratio_size_name"></strong>
                </div>
                <div id="ratioSizeList"></div>
                <div class="output mt-3">
                    <span>Size Group:</span>
                    <strong id="ratioGroupText">—</strong>
                </div>
                <input type="hidden" id="ratio_val_hidden">
            </div>
            <div class="modal-footer mt-3">
                <button type="button" class="btn btn-secondary" onclick="closeRatioModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveRatioGroup()">Save</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Consolidated logic for Update Ratio visibility
            function toggleRatioBtn($select) {
                let btn = $select.siblings('.openCustomSizeBtn');
                if ($select.val()) {
                    btn.show();
                } else {
                    btn.hide();
                }
            }

            $(document).on('change select2:select', '.size-set-select', function () {
                toggleRatioBtn($(this));
            });

            // Initial check for pre-selected values
            $('.size-set-select').each(function () {
                toggleRatioBtn($(this));
            });

            // Dynamic Rows Logic
            function reindexAll() {
                $('.size-set-block').each(function (sIdx) {
                    $(this).find('.add-color-item').attr('data-set-index', sIdx);
                    $(this).find('input[name="variant_ids[]"]').val($(this).find('input[name="variant_ids[]"]').val() || '');
                    $(this).find('.size-set-select').attr('name', 'size_sets[]');
                    $(this).find('.mrp-input').attr('name', 'mrps[]');
                    $(this).find('.size-set-image-input').attr('name', 'size_set_images[]');

                    $(this).find('.color-item-row').each(function (cIdx) {
                        $(this).find('input[name^="variant_item_ids"]').attr('name', `variant_item_ids[${sIdx}][${cIdx}]`);
                        $(this).find('.color-select').attr('name', `variant_colors[${sIdx}][${cIdx}]`);
                        $(this).find('.variant-image-input').attr('name', `variant_images[${sIdx}][${cIdx}]`);
                    });
                    let colorRows = $(this).find('.color-item-row');
                    colorRows.find('.remove-color-item').prop('disabled', colorRows.length === 1);
                });
                let setBlocks = $('.size-set-block');
                setBlocks.find('.remove-size-set').prop('disabled', setBlocks.length === 1);
            }

            // Add Size Set
            $('.add-size-set').on('click', function () {
                let sIdx = $('.size-set-block').length;
                let blockHtml = `
                                    <div class="size-set-block mb-4 p-3 border rounded bg-light">
                                        <div class="row align-items-end mb-3">
                                            <div class="col-md-4 text-left">
                                                <input type="hidden" name="variant_ids[]" value="">
                                                <label>Size Set</label>
                                                 <select name="size_sets[]" class="form-control select2 size-set-select" style="width: 100%;">
                                                    <option value="">Select Size Set</option>
                                                    @foreach($sizes as $size)
                                                        <option value="{{ $size->id }}" data-set-group="{{ $size->size_group }}" data-pcs="{{ $size->no_of_pcs }}">{{ $size->name }} ({{ $size->no_of_pcs }} Pcs)</option>
                                                    @endforeach
                                                </select>
                                                <div class="open-ratio-label openCustomSizeBtn" style="display:none;">
                                                    Update Ratio
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                 <label>MRP</label>
                                                <input type="number" name="mrps[]" class="form-control mrp-input" placeholder="0.00" step="0.01">
                                            </div>
                                            <div class="col-md-3">
                                                 <label>Set Image</label>
                                                <input type="file" name="size_set_images[]" class="form-control-file size-set-image-input" accept="image/*">
                                            </div>
                                            <div class="col-md-2 text-right">
                                                <button type="button" class="btn btn-danger btn-sm remove-size-set"><i class="fa fa-trash"></i> Remove Set</button>
                                            </div>
                                        </div>
                                        <div class="color-items-container ml-4">
                                            <h6>Colors & Images</h6>
                                            <div class="color-item-row row mb-2 align-items-center border-bottom pb-2">
                                                <div class="col-md-4">
                                                    <select name="variant_colors[${sIdx}][0]" class="form-control select2 color-select" style="width: 100%;">
                                                        <option value="">Select Color</option>
                                                        @foreach($colors as $color)
                                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="file" name="variant_images[${sIdx}][0]" class="form-control-file variant-image-input" accept="image/*">
                                                </div>
                                                <div class="col-md-2 text-right">
                                                    <button type="button" class="btn btn-warning btn-sm remove-color-item" disabled><i class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4 mt-2">
                                            <button type="button" class="btn btn-info btn-sm add-color-item" data-set-index="${sIdx}"><i class="fa fa-plus"></i> Add Another Color</button>
                                        </div>
                                    </div>
                                `;
                $('#size-set-container').append(blockHtml);
                $('#size-set-container .size-set-block:last .select2').each(function () {
                    var $dropdownParent = $(this).closest('.modal').length ? $(this).closest('.modal') : $(this).parent();
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownAutoWidth: true,
                        dropdownParent: $dropdownParent
                    });
                });
                reindexAll();
            });

            $(document).on('click', '.add-color-item', function () {
                let sIdx = $(this).attr('data-set-index');
                let container = $(this).closest('.size-set-block').find('.color-items-container');
                let cIdx = container.find('.color-item-row').length;
                let rowHtml = `
                                    <div class="color-item-row row mb-2 align-items-center border-bottom pb-2">
                                        <input type="hidden" name="variant_item_ids[${sIdx}][${cIdx}]" value="">
                                        <div class="col-md-4">
                                            <select name="variant_colors[${sIdx}][${cIdx}]" class="form-control select2 color-select" style="width: 100%;">
                                                <option value="">Select Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="file" name="variant_images[${sIdx}][${cIdx}]" class="form-control-file variant-image-input" accept="image/*">
                                        </div>
                                        <div class="col-md-2 text-right">
                                            <button type="button" class="btn btn-warning btn-sm remove-color-item"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                `;
                container.append(rowHtml);
                container.find('.color-item-row:last .select2').each(function () {
                    var $dropdownParent = $(this).closest('.modal').length ? $(this).closest('.modal') : $(this).parent();
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownAutoWidth: true,
                        dropdownParent: $dropdownParent
                    });
                });
                reindexAll();
            });

            $(document).on('click', '.remove-size-set', function () {
                if ($('.size-set-block').length > 1) {
                    $(this).closest('.size-set-block').remove();
                    reindexAll();
                }
            });

            $(document).on('click', '.remove-color-item', function () {
                let container = $(this).closest('.color-items-container');
                if (container.find('.color-item-row').length > 1) {
                    $(this).closest('.color-item-row').remove();
                    reindexAll();
                }
            });

            // Ratio Modal Logic
            window.currentSizeSelect = null;
            window.sizeCounts = {};

            $(document).on('click', '.openCustomSizeBtn', function () {
                window.currentSizeSelect = $(this).siblings('.size-set-select');
                let option = window.currentSizeSelect.find(':selected');
                let setGroup = option.data('set-group') || "";
                let setSizeName = option.text();

                $('#ratio_size_name').text(setSizeName);
                loadRatioSizeGroup(setGroup);
                $('#sizeRatioModal').css('display', 'flex');
            });

            window.closeRatioModal = function () {
                $('#sizeRatioModal').hide();
            }

            window.loadRatioSizeGroup = function (group) {
                window.sizeCounts = {};
                if (group) {
                    group.toString().split(',').forEach(size => {
                        window.sizeCounts[size] = (window.sizeCounts[size] || 0) + 1;
                    });
                }
                renderRatioSizes();
            }

            window.changeRatioCount = function (size, change) {
                window.sizeCounts[size] = (window.sizeCounts[size] || 0) + change;
                if (window.sizeCounts[size] < 0) window.sizeCounts[size] = 0;
                renderRatioSizes();
            }

            window.renderRatioSizes = function () {
                let list = document.getElementById('ratioSizeList');
                if (!list) return;
                list.innerHTML = '';
                let group = [];

                Object.keys(window.sizeCounts).sort((a, b) => a - b).forEach(size => {
                    let count = window.sizeCounts[size];
                    for (let i = 0; i < count; i++) group.push(size);

                    list.innerHTML += `
                                        <div class="size-row-ratio">
                                            <strong>${size}</strong>
                                            <div class="counter-ratio">
                                                <button type="button" onclick="changeRatioCount('${size}', -1)">-</button>
                                                <span>${count}</span>
                                                <button type="button" onclick="changeRatioCount('${size}', 1)">+</button>
                                            </div>
                                        </div>
                                    `;
                });

                let groupText = document.getElementById('ratioGroupText');
                if (groupText) groupText.innerText = group.join(',');

                let hiddenVal = document.getElementById('ratio_val_hidden');
                if (hiddenVal) hiddenVal.value = getCalculatedRatio(group.join(','));
            }

            function getCalculatedRatio(sizeString) {
                if (!sizeString) return "";
                let sizes = sizeString.split(',');
                let countMap = {};
                sizes.forEach(size => { countMap[size] = (countMap[size] || 0) + 1; });
                return Object.keys(countMap).sort((a, b) => a - b).map(size => countMap[size]).join(',');
            }

            window.saveRatioGroup = function () {
                let finalGroup = $('#ratioGroupText').text();
                if (!finalGroup || !window.currentSizeSelect) {
                    closeRatioModal();
                    return;
                }

                let option = window.currentSizeSelect.find(':selected');
                let set_size_id = window.currentSizeSelect.val();
                let set_size_name = option.text().split('(')[0].trim();

                $.ajax({
                    url: "{{ route('admin.sales_order.saveCustomSetSize') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        set_size_id: set_size_id,
                        set_size_name: set_size_name,
                        finalGroup: finalGroup,
                        customer_id: 1
                    },
                    success: function (response) {
                        if (response.new_size_set_id) {
                            if (window.currentSizeSelect.find(`option[value="${response.new_size_set_id}"]`).length === 0) {
                                let newOption = new Option(response.new_size_name + " (" + response.new_size_group + ")", response.new_size_set_id, true, true);
                                $(newOption).attr('data-set-group', response.new_size_group);
                                $(newOption).attr('data-pcs', response.no_of_pcs);
                                window.currentSizeSelect.append(newOption).trigger('change');
                            } else {
                                window.currentSizeSelect.val(response.new_size_set_id).trigger('change');
                            }
                        }
                        closeRatioModal();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert("Error saving ratio.");
                    }
                });
            }
        });
    </script>
@endsection