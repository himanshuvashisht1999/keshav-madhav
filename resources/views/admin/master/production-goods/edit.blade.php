@extends('admin.layouts.app')

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        .content-wrapper {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            font-size: 12px;
            padding: 6px 10px 30px 10px;
        }

        /* COMPACT ERP TOP BAR */
        .erp-topbar {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        /* HIGH-DENSITY CARD */
        .hd-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .hd-card-header {
            padding: 5px 10px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hd-card-title {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hd-card-body {
            padding: 8px 10px;
        }

        /* FORM ELEMENTS (ULTRA COMPACT) */
        .hd-form-group {
            margin-bottom: 4px;
        }

        .hd-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            color: #475569;
            margin-bottom: 1px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hd-label .action-links a {
            font-size: 10px;
            font-weight: 600;
            text-transform: none;
            color: #4f46e5;
            margin-left: 4px;
        }

        .hd-input {
            height: 29px !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            padding: 2px 8px !important;
            line-height: 1.3 !important;
        }

        .hd-input:focus {
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15) !important;
            outline: none;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: 29px !important;
            border-radius: 4px !important;
            border: 1px solid #cbd5e1 !important;
            padding: 2px 6px !important;
            font-size: 12px !important;
            line-height: 23px !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 23px !important;
            padding-left: 2px !important;
            font-size: 12px !important;
        }

        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 27px !important;
            right: 4px !important;
        }

        /* COMPACT SIZE SET BLOCK */
        .size-set-block {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            margin-bottom: 8px;
            overflow: visible;
        }

        .size-set-header {
            padding: 4px 8px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Ensure Select2 Dropdowns float comfortably and show 8-10 options */
        .select2-container--bootstrap4 .select2-dropdown {
            z-index: 9999 !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
            border-radius: 4px !important;
        }

        .select2-container--bootstrap4 .select2-results > .select2-results__options {
            max-height: 280px !important;
            overflow-y: auto !important;
        }

        .select2-container--bootstrap4 .select2-results__option {
            padding: 5px 10px !important;
            font-size: 12px !important;
        }

        .size-set-body {
            padding: 6px 8px;
        }

        /* COMPACT COLOR TABLE */
        .hd-color-table {
            width: 100%;
            margin-bottom: 4px;
            border-collapse: collapse;
        }

        .hd-color-table th {
            background: #f8fafc;
            color: #475569;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 6px;
            border: 1px solid #e2e8f0;
        }

        .hd-color-table td {
            padding: 3px 6px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .barcode-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 10.5px;
            font-weight: 600;
        }

        .thumb-preview-mini {
            width: 26px;
            height: 26px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
            display: inline-block;
            vertical-align: middle;
        }

        .thumb-preview-card {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        /* CUSTOM RATIO MODAL */
        .modal-ratio {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-ratio-content {
            background: #fff;
            width: 380px;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border: 1px solid #cbd5e1;
        }

        .size-row-ratio {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-top: 4px;
        }

        .counter-ratio button {
            width: 24px;
            height: 24px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #0f172a;
            border-radius: 3px;
            font-weight: 700;
            font-size: 12px;
            line-height: 1;
            cursor: pointer;
        }

        .counter-ratio button:hover {
            background: #4f46e5;
            color: #fff;
        }
    </style>

    <div class="content-wrapper">
        <form action="{{ route('admin.master.production-goods.update') }}" method="post" enctype="multipart/form-data" id="editProductForm">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="hidden" name="company_id" value="2" id="company_id">

            <!-- 1. COMPACT TOP ACTION BAR -->
            <div class="erp-topbar">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.master.production-goods.index') }}" class="btn btn-outline-secondary btn-xs mr-2 font-weight-bold" title="Back to Products">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <span class="badge badge-primary px-2 py-1 mr-2" style="font-size: 11px;">
                        <i class="fas fa-hashtag"></i> {{ $data->design_number }}
                    </span>
                    <strong class="text-dark mr-3" style="font-size: 13px;">
                        {{ $data->name_of_garment ?? 'Product Specification' }}
                    </strong>
                    <span class="badge badge-light border text-muted mr-1"><strong id="hud-total-sets">0</strong> Sets</span>
                    <span class="badge badge-light border text-muted mr-1"><strong id="hud-total-colors">0</strong> Colors</span>
                    <span class="badge badge-light border text-muted"><strong id="hud-total-images">{{ $data->product_images ? $data->product_images->count() : 0 }}</strong> Photos</span>
                </div>

                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm" style="height: 28px; line-height: 14px;">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </div>

            <!-- 2. BASIC PRODUCT SPECIFICATIONS (DENSE 4-COL GRID) -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h3 class="hd-card-title text-primary">
                        <i class="fas fa-tshirt"></i> 1. Product Specifications
                    </h3>
                    @if($data->is_locked_in_inventory)
                        <span class="badge badge-warning text-dark px-2 py-0" style="font-size: 10px;">
                            <i class="fas fa-lock mr-1"></i> Locked in Inventory
                        </span>
                    @endif
                </div>
                <div class="hd-card-body">
                    <div class="row">
                        <!-- Design Number -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Design Number *</span>
                                    @if($data->is_locked_in_inventory)
                                        <i class="fas fa-lock text-muted" title="Locked"></i>
                                    @endif
                                </label>
                                <input type="text" name="design_number" class="form-control hd-input font-weight-bold text-primary"
                                    value="{{ $data->design_number }}"
                                    {{ $data->is_locked_in_inventory ? 'disabled' : '' }}>
                                @if($data->is_locked_in_inventory)
                                    <input type="hidden" name="design_number" value="{{ $data->design_number }}">
                                @endif
                            </div>
                        </div>

                        <!-- Product Name -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Product / Garment Name</span>
                                </label>
                                <input type="text" name="name_of_garment" class="form-control hd-input font-weight-bold"
                                    value="{{ $data->name_of_garment }}" placeholder="e.g. Basic T-Shirt">
                            </div>
                        </div>

                        <!-- Series Name -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Series Name</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.series.create') }}" target="_blank" title="Create New Series"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshSeriesBtn" title="Refresh Series"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="master_series_id" id="master_series_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Series</option>
                                    @foreach($series_names as $series)
                                        <option value="{{ $series->id }}" {{ $data->master_series_id == $series->id ? 'selected' : '' }}>{{ $series->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Brand</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.brand.create') }}" target="_blank" title="Create New Brand"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshBrandBtn" title="Refresh Brands"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="brand_id" id="brand_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ $data->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Fitting -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Fitting</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.fitting.create') }}" target="_blank" title="New"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshFittingBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="master_product_fitting_id" id="master_product_fitting_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Fitting</option>
                                    @foreach($fittings as $fit)
                                        <option value="{{ $fit->id }}" {{ $data->master_product_fitting_id == $fit->id ? 'selected' : '' }}>{{ $fit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Pattern -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Pattern</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.pattern.create') }}" target="_blank" title="New"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshPatternBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="master_pattern_id" id="master_pattern_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Pattern</option>
                                    @foreach($garment_patterns as $p)
                                        <option value="{{ $p->id }}" {{ $data->master_pattern_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Product Nature -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Product Nature</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.product-nature.create') }}" target="_blank" title="New"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshProductNatureBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="product_nature_id" id="product_nature_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Nature</option>
                                    @foreach($product_natures as $pn)
                                        <option value="{{ $pn->id }}" {{ $data->product_nature_id == $pn->id ? 'selected' : '' }}>{{ $pn->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Fabric Type -->
                        <div class="col-md-3 col-6">
                            <div class="form-group hd-form-group">
                                <label class="hd-label">
                                    <span>Fabric Type</span>
                                    <span class="action-links">
                                        <a href="{{ route('admin.master.fabric-type.create') }}" target="_blank" title="New"><i class="fas fa-plus"></i></a>
                                        <a href="javascript:void(0)" id="refreshFabricTypeBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                    </span>
                                </label>
                                <select name="fabric_type_id" id="fabric_type_id" class="form-control select2" style="width: 100%;">
                                    <option value="">Select Fabric Type</option>
                                    @foreach($fabric_types as $ft)
                                        <option value="{{ $ft->id }}" {{ $data->fabric_type_id == $ft->id ? 'selected' : '' }}>{{ $ft->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. SIZE SETS & PRICING MATRIX (COMPACT ACCORDION / CARDS) -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h3 class="hd-card-title text-primary">
                        <i class="fas fa-layer-group"></i> 2. Size Sets & Pricing Matrix
                    </h3>
                    <button type="button" class="btn btn-primary btn-xs font-weight-bold add-size-set">
                        <i class="fas fa-plus mr-1"></i> Add Size Set
                    </button>
                </div>
                <div class="hd-card-body p-2" id="size-set-container">
                    @php $sIdx = 0; @endphp
                    @forelse($data->variants as $variant)
                        <div class="size-set-block">
                            <!-- Size Set Header -->
                            <div class="size-set-header">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-primary mr-2" style="font-size: 10px;">
                                        Set #<span class="set-idx-label">{{ $sIdx + 1 }}</span>
                                    </span>
                                    <strong class="text-dark set-title-preview" style="font-size: 12px;">
                                        {{ $variant->sizeSet ? $variant->sizeSet->name . ' (' . $variant->sizeSet->no_of_pcs . ' Pcs)' : 'Size Set' }}
                                    </strong>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-outline-primary btn-xs openCustomSizeBtn mr-2"
                                        style="{{ $variant->master_size_measurement_id ? '' : 'display:none;' }}">
                                        <i class="fas fa-sliders-h mr-1"></i> Ratio
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-xs remove-size-set"
                                        {{ $variant->is_locked_in_inventory ? 'disabled title=Locked_in_Inventory' : '' }}>
                                        <i class="fas fa-trash-alt mr-1"></i> Remove
                                    </button>
                                </div>
                            </div>

                            <!-- Size Set Body -->
                            <div class="size-set-body">
                                <input type="hidden" name="variant_ids[]" value="{{ $variant->id }}">
                                
                                <div class="row align-items-center mb-2">
                                    <!-- Size Set Dropdown -->
                                    <div class="col-md-5">
                                        <div class="form-group hd-form-group mb-0">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="hd-label mb-0">Size Set *</label>
                                                <span class="action-links">
                                                    <a href="{{ route('admin.master.size-measurement.index') }}" target="_blank" title="New"><i class="fas fa-plus"></i> New</a>
                                                    <a href="javascript:void(0)" class="refreshSizeSetBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                                </span>
                                            </div>
                                            <select name="size_sets[]" class="form-control select2 size-set-select"
                                                {{ $variant->is_locked_in_inventory ? 'disabled' : '' }}>
                                                <option value="">Select Size Set</option>
                                                @foreach($sizes as $size)
                                                    <option value="{{ $size->id }}"
                                                        data-set-group="{{ $size->size_group }}"
                                                        data-pcs="{{ $size->no_of_pcs }}"
                                                        {{ $variant->master_size_measurement_id == $size->id ? 'selected' : '' }}>
                                                        {{ $size->name }} ({{ $size->no_of_pcs }} Pcs)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if($variant->is_locked_in_inventory)
                                                <input type="hidden" name="size_sets[]" value="{{ $variant->master_size_measurement_id }}">
                                            @endif
                                        </div>
                                    </div>

                                    <!-- MRP -->
                                    <div class="col-md-3">
                                        <div class="form-group hd-form-group mb-0">
                                            <label class="hd-label mb-1">MRP (₹)</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light text-muted border-right-0 py-0 px-2" style="font-size: 11px;">₹</span>
                                                </div>
                                                <input type="number" name="mrps[]" class="form-control hd-input mrp-input border-left-0 font-weight-bold"
                                                    value="{{ $variant->mrp }}" placeholder="0.00" step="0.01">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Set Image -->
                                    <div class="col-md-4">
                                        <div class="form-group hd-form-group mb-0">
                                            <label class="hd-label mb-1">Set Photo (Optional)</label>
                                            <div class="d-flex align-items-center">
                                                <input type="file" name="size_set_images[]" class="form-control-file size-set-image-input" accept="image/*" style="font-size: 11px; flex: 1;">
                                                @if($variant->image)
                                                    <a href="{{ asset('assets/products/' . $variant->image) }}" target="_blank" class="ml-1" title="View Full Photo">
                                                        <img src="{{ asset('assets/products/' . $variant->image) }}" class="thumb-preview-mini">
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nested Colors Table -->
                                <table class="hd-color-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 38%;">Color Variant</th>
                                            <th style="width: 25%;">SKU Barcode</th>
                                            <th style="width: 32%;">Color Photo</th>
                                            <th style="width: 5%; text-align: center;"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="color-items-container">
                                        @php $cIdx = 0; @endphp
                                        @forelse($variant->items as $item)
                                            <tr class="color-item-row">
                                                <input type="hidden" name="variant_item_ids[{{ $sIdx }}][{{ $cIdx }}]" value="{{ $item->id }}">
                                                
                                                <!-- Color Dropdown -->
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div style="flex: 1;">
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
                                                        </div>
                                                        <a href="javascript:void(0)" class="refreshColorBtn ml-1 text-info" title="Refresh Colors"><i class="fas fa-sync-alt" style="font-size: 10px;"></i></a>
                                                    </div>
                                                    @if($item->is_locked_in_inventory)
                                                        <input type="hidden" name="variant_colors[{{ $sIdx }}][{{ $cIdx }}]" value="{{ $item->master_color_id }}">
                                                    @endif
                                                </td>

                                                <!-- Barcode -->
                                                <td>
                                                    @if($item->barcode)
                                                        <span class="barcode-badge"><i class="fas fa-barcode mr-1"></i>{{ $item->barcode }}</span>
                                                    @else
                                                        <span class="text-muted" style="font-size: 10px;">Auto on save</span>
                                                    @endif
                                                </td>

                                                <!-- Image -->
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <input type="file" name="variant_images[{{ $sIdx }}][{{ $cIdx }}]" class="form-control-file variant-image-input" accept="image/*" style="font-size: 11px; flex: 1;">
                                                        @if($item->image)
                                                            <a href="{{ asset('assets/products/' . $item->image) }}" target="_blank" class="ml-1" title="View Color Photo">
                                                                <img src="{{ asset('assets/products/' . $item->image) }}" class="thumb-preview-mini">
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Delete -->
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs text-danger remove-color-item p-0" title="Delete Color"
                                                        {{ $item->is_locked_in_inventory ? 'disabled title=Locked_in_Inventory' : '' }}>
                                                        <i class="fas fa-times-circle" style="font-size: 14px;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @php $cIdx++; @endphp
                                        @empty
                                            <!-- Fallback if variant has 0 items -->
                                            <tr class="color-item-row">
                                                <input type="hidden" name="variant_item_ids[{{ $sIdx }}][0]" value="">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div style="flex: 1;">
                                                            <select name="variant_colors[{{ $sIdx }}][0]" class="form-control select2 color-select" style="width: 100%;">
                                                                <option value="">Select Color</option>
                                                                @foreach($colors as $color)
                                                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <a href="javascript:void(0)" class="refreshColorBtn ml-1 text-info" title="Refresh Colors"><i class="fas fa-sync-alt" style="font-size: 10px;"></i></a>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted" style="font-size: 10px;">Auto on save</span></td>
                                                <td>
                                                    <input type="file" name="variant_images[{{ $sIdx }}][0]" class="form-control-file variant-image-input" accept="image/*" style="font-size: 11px;">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs text-danger remove-color-item p-0" disabled>
                                                        <i class="fas fa-times-circle" style="font-size: 14px;"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-primary add-color-item font-weight-bold" data-set-index="{{ $sIdx }}">
                                        <i class="fas fa-plus mr-1"></i> Add Color
                                    </button>
                                </div>
                            </div>
                        </div>
                        @php $sIdx++; @endphp
                    @empty
                        <div id="no-variants-message" class="p-3 border rounded text-center bg-light text-muted">
                            <span class="mr-2">No size sets defined.</span>
                            <button type="button" class="btn btn-primary btn-xs add-size-set">
                                <i class="fas fa-plus mr-1"></i> Add Size Set
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 4. PRODUCT GALLERY (COMPACT TABLE/GRID) -->
            <div class="hd-card">
                <div class="hd-card-header">
                    <h3 class="hd-card-title text-primary">
                        <i class="fas fa-images"></i> 3. Product Gallery & Photos
                    </h3>
                    <button type="button" class="btn btn-primary btn-xs font-weight-bold add-new-product-image-btn">
                        <i class="fas fa-plus mr-1"></i> Add Photo
                    </button>
                </div>
                <div class="hd-card-body p-2">
                    <div id="deleted-images-holder"></div>

                    <!-- Existing Images -->
                    @if($data->product_images && $data->product_images->count() > 0)
                        <div class="mb-2" id="existing-product-images-container">
                            <table class="table table-sm table-bordered mb-2" style="font-size: 11px;">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th style="width: 50px;">Preview</th>
                                        <th style="width: 45%;">Caption / Title</th>
                                        <th style="width: 45%;">Replace Photo</th>
                                        <th style="width: 40px; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data->product_images as $img)
                                        <tr class="existing-image-row" id="existing-image-{{ $img->id }}">
                                            <td class="text-center p-1">
                                                <a href="{{ $img->image }}" target="_blank" title="View Full Photo">
                                                    <img src="{{ $img->image }}" class="thumb-preview-card">
                                                </a>
                                            </td>
                                            <td>
                                                <input type="text" name="existing_image_titles[{{ $img->id }}]" class="form-control hd-input" value="{{ $img->title }}" placeholder="e.g. Front Photo">
                                            </td>
                                            <td>
                                                <input type="file" name="existing_images[{{ $img->id }}]" class="form-control-file existing-image-file-input" accept="image/*" style="font-size: 11px;">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs text-danger remove-existing-image-btn p-0" data-id="{{ $img->id }}" title="Delete">
                                                    <i class="fas fa-trash-alt" style="font-size: 13px;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- New Images Container -->
                    <div id="new-product-images-container">
                        @if(!$data->product_images || $data->product_images->count() == 0)
                            <div class="new-product-image-row border rounded p-2 mb-1 bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <input type="text" name="new_product_image_titles[]" class="form-control hd-input" placeholder="Photo Caption / Title">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="file" name="new_product_images[]" class="form-control-file new-product-image-file-input" accept="image/*" style="font-size: 11px;">
                                    </div>
                                    <div class="col-md-1 text-right">
                                        <button type="button" class="btn btn-xs text-danger remove-new-product-image-btn" title="Remove">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- BOTTOM SAVE ACTION -->
            <div class="text-center py-2">
                <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm" style="font-size: 13px;">
                    <i class="fas fa-save mr-1"></i> Update Product Specification
                </button>
                <a href="{{ route('admin.master.production-goods.index') }}" class="btn btn-outline-secondary px-4 py-2 ml-2" style="font-size: 13px;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <!-- CUSTOM SIZE RATIO MODAL -->
    <div class="modal-ratio" id="sizeRatioModal">
        <div class="modal-ratio-content">
            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                <strong class="text-dark" style="font-size: 13px;"><i class="fas fa-sliders-h text-primary mr-1"></i> Update Size Ratio</strong>
                <button type="button" class="close text-muted" onclick="closeRatioModal()" style="font-size: 1.2rem; line-height: 1;">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="d-flex justify-content-between align-items-center p-1 mb-2 bg-light rounded border">
                    <span class="text-muted small font-weight-bold">Size Set:</span>
                    <strong id="ratio_size_name" class="text-primary"></strong>
                </div>
                <div id="ratioSizeList" class="my-2" style="max-height: 220px; overflow-y: auto;"></div>
                <div class="d-flex justify-content-between align-items-center p-1 mt-2 bg-light rounded border">
                    <span class="text-muted small font-weight-bold">Ratio Group:</span>
                    <strong id="ratioGroupText" class="text-dark">—</strong>
                </div>
                <input type="hidden" id="ratio_val_hidden">
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top">
                <button type="button" class="btn btn-outline-secondary btn-xs px-3 mr-2" onclick="closeRatioModal()">Close</button>
                <button type="button" class="btn btn-primary btn-xs px-3 font-weight-bold" onclick="saveRatioGroup()">Save Ratio</button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Initialize select2 on elements (attaches to body so dropdown floats freely over other elements)
            function initSelect2($elements) {
                $elements.each(function () {
                    var $el = $(this);
                    var opts = {
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownAutoWidth: true
                    };
                    if ($el.closest('.modal').length) {
                        opts.dropdownParent = $el.closest('.modal');
                    }
                    $el.select2(opts);
                });
            }

            initSelect2($('.select2'));

            // Design Number Validation
            $('input[name="design_number"]').on('blur', function() {
                var designNumber = $(this).val();
                var productId = $('input[name="id"]').val();
                var $input = $(this);
                var $formGroup = $input.closest('.form-group');
                
                $formGroup.find('.design-number-error').remove();
                
                if (designNumber && !$input.prop('disabled')) {
                    $.ajax({
                        url: "{{ route('admin.master.production-goods.check-design-number') }}",
                        type: "GET",
                        data: { design_number: designNumber, id: productId },
                        success: function (data) {
                            if (data.exists) {
                                $input.addClass('is-invalid');
                                $formGroup.append('<span class="text-danger small design-number-error font-weight-bold mt-1 d-block"><i class="fas fa-exclamation-circle mr-1"></i> This design number already exists.</span>');
                            } else {
                                $input.removeClass('is-invalid');
                            }
                        }
                    });
                } else {
                    $input.removeClass('is-invalid');
                }
            });

            // Consolidated logic for Update Ratio button visibility
            function toggleRatioBtn($select) {
                let btn = $select.closest('.size-set-block').find('.openCustomSizeBtn');
                if ($select.val()) {
                    btn.show();
                } else {
                    btn.hide();
                }
            }

            $(document).on('change select2:select', '.size-set-select', function () {
                toggleRatioBtn($(this));
                let selectedText = $(this).find('option:selected').text();
                if ($(this).val()) {
                    $(this).closest('.size-set-block').find('.set-title-preview').text(selectedText);
                } else {
                    $(this).closest('.size-set-block').find('.set-title-preview').text('Size Set');
                }
            });

            // Refresh Handlers
            $('#refreshSeriesBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.series.all_series') }}", function(data) {
                    var select = $('#master_series_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Series</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $('#refreshBrandBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.brand.all_brands') }}", function(data) {
                    var select = $('#brand_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Brand</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $('#refreshFittingBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.fitting.all_fittings') }}", function(data) {
                    var select = $('#master_product_fitting_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Fitting</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $('#refreshPatternBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.pattern.all_patterns') }}", function(data) {
                    var select = $('#master_pattern_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Pattern</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $('#refreshProductNatureBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.product-nature.all_product_natures') }}", function(data) {
                    var select = $('#product_nature_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Nature</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $('#refreshFabricTypeBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.fabric-type.all_fabric_types') }}", function(data) {
                    var select = $('#fabric_type_id');
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Fabric Type</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $(document).on('click', '.refreshSizeSetBtn', function() {
                var btn = $(this);
                var select = btn.closest('.hd-form-group').find('select');
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.master.size.all_sizes') }}", function(data) {
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Size Set</option>');
                    data.forEach(function(item) { 
                        var option = $('<option></option>').attr('value', item.id)
                            .attr('data-set-group', item.size_group)
                            .attr('data-pcs', item.no_of_pcs)
                            .text(item.name + ' (' + item.no_of_pcs + ' Pcs)');
                        select.append(option); 
                    });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt"></i>'); });
            });

            $(document).on('click', '.refreshColorBtn', function() {
                var btn = $(this);
                var select = btn.closest('td').find('select');
                btn.html('<i class="fas fa-spinner fa-spin" style="font-size: 10px;"></i>');
                $.getJSON("{{ route('admin.master.colors.all_colors') }}", function(data) {
                    var currentVal = select.val();
                    select.empty().append('<option value="">Select Color</option>');
                    data.forEach(function(item) { select.append('<option value="'+item.id+'">'+item.name+'</option>'); });
                    if(currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt" style="font-size: 10px;"></i>');
                }).fail(function() { btn.html('<i class="fas fa-sync-alt" style="font-size: 10px;"></i>'); });
            });

            // Prevent duplicate size set selections across different blocks
            function updateSizeSetOptions() {
                let selectedValues = [];
                $('.size-set-select').each(function() {
                    let val = $(this).val();
                    if (val) {
                        selectedValues.push(val);
                    }
                });

                $('.size-set-select').each(function() {
                    let currentSelect = $(this);
                    let currentValue = currentSelect.val();

                    currentSelect.find('option').each(function() {
                        let optionVal = $(this).val();
                        if (optionVal) {
                            if (selectedValues.includes(optionVal) && optionVal !== currentValue) {
                                $(this).prop('disabled', true);
                            } else {
                                $(this).prop('disabled', false);
                            }
                        }
                    });
                });
            }

            // Prevent duplicate color selections within the same size set block
            function updateColorOptions() {
                $('.size-set-block').each(function() {
                    let block = $(this);
                    let selectedColors = [];
                    
                    block.find('.color-select').each(function() {
                        let val = $(this).val();
                        if (val) {
                            selectedColors.push(val);
                        }
                    });

                    block.find('.color-select').each(function() {
                        let currentSelect = $(this);
                        let currentValue = currentSelect.val();

                        currentSelect.find('option').each(function() {
                            let optionVal = $(this).val();
                            if (optionVal) {
                                if (selectedColors.includes(optionVal) && optionVal !== currentValue) {
                                    $(this).prop('disabled', true);
                                } else {
                                    $(this).prop('disabled', false);
                                }
                            }
                        });
                    });
                });
            }

            $(document).on('change', '.size-set-select', updateSizeSetOptions);
            $(document).on('change', '.color-select', updateColorOptions);

            // Re-indexing and HUD update logic
            function reindexAll() {
                let totalSets = 0;
                let totalColors = 0;

                $('.size-set-block').each(function (sIdx) {
                    totalSets++;
                    $(this).find('.set-idx-label').text(sIdx + 1);
                    $(this).find('.add-color-item').attr('data-set-index', sIdx);
                    $(this).find('input[name="variant_ids[]"]').val($(this).find('input[name="variant_ids[]"]').val() || '');
                    $(this).find('.size-set-select').attr('name', 'size_sets[]');
                    $(this).find('.mrp-input').attr('name', 'mrps[]');
                    $(this).find('.size-set-image-input').attr('name', 'size_set_images[]');

                    $(this).find('.color-item-row').each(function (cIdx) {
                        totalColors++;
                        $(this).find('input[name^="variant_item_ids"]').attr('name', `variant_item_ids[${sIdx}][${cIdx}]`);
                        $(this).find('.color-select').attr('name', `variant_colors[${sIdx}][${cIdx}]`);
                        $(this).find('input[type="hidden"][name^="variant_colors"]').attr('name', `variant_colors[${sIdx}][${cIdx}]`);
                        $(this).find('.variant-image-input').attr('name', `variant_images[${sIdx}][${cIdx}]`);
                    });

                    let colorRows = $(this).find('.color-item-row');
                    colorRows.find('.remove-color-item').prop('disabled', colorRows.length === 1);
                });

                let setBlocks = $('.size-set-block');
                setBlocks.find('.remove-size-set').prop('disabled', setBlocks.length === 1);

                // Update HUD Numbers
                $('#hud-total-sets').text(totalSets);
                $('#hud-total-colors').text(totalColors);

                let totalImages = $('#existing-product-images-container .existing-image-row').length + 
                                  $('#new-product-images-container .new-product-image-row').length;
                $('#hud-total-images').text(totalImages);

                updateSizeSetOptions();
                updateColorOptions();
            }

            // Initial check and run
            reindexAll();

            // Add Size Set Block
            $('.add-size-set').on('click', function () {
                $('#no-variants-message').remove();
                let sIdx = $('.size-set-block').length;
                let blockHtml = `
                    <div class="size-set-block">
                        <div class="size-set-header">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-primary mr-2" style="font-size: 10px;">
                                    Set #<span class="set-idx-label">${sIdx + 1}</span>
                                </span>
                                <strong class="text-dark set-title-preview" style="font-size: 12px;">Size Set</strong>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-outline-primary btn-xs openCustomSizeBtn mr-2" style="display:none;">
                                    <i class="fas fa-sliders-h mr-1"></i> Ratio
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs remove-size-set">
                                    <i class="fas fa-trash-alt mr-1"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="size-set-body">
                            <input type="hidden" name="variant_ids[]" value="">

                            <div class="row align-items-center mb-2">
                                <div class="col-md-5">
                                    <div class="form-group hd-form-group mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="hd-label mb-0">Size Set *</label>
                                            <span class="action-links">
                                                <a href="{{ route('admin.master.size-measurement.index') }}" target="_blank" title="New"><i class="fas fa-plus"></i> New</a>
                                                <a href="javascript:void(0)" class="refreshSizeSetBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                            </span>
                                        </div>
                                        <select name="size_sets[]" class="form-control select2 size-set-select" style="width: 100%;">
                                            <option value="">Select Size Set</option>
                                            @foreach($sizes as $size)
                                                <option value="{{ $size->id }}" data-set-group="{{ $size->size_group }}" data-pcs="{{ $size->no_of_pcs }}">{{ $size->name }} ({{ $size->no_of_pcs }} Pcs)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group hd-form-group mb-0">
                                        <label class="hd-label mb-1">MRP (₹)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light text-muted border-right-0 py-0 px-2" style="font-size: 11px;">₹</span>
                                            </div>
                                            <input type="number" name="mrps[]" class="form-control hd-input mrp-input border-left-0 font-weight-bold" placeholder="0.00" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group hd-form-group mb-0">
                                        <label class="hd-label mb-1">Set Photo (Optional)</label>
                                        <input type="file" name="size_set_images[]" class="form-control-file size-set-image-input" accept="image/*" style="font-size: 11px;">
                                    </div>
                                </div>
                            </div>

                            <table class="hd-color-table">
                                <thead>
                                    <tr>
                                        <th style="width: 38%;">Color Variant</th>
                                        <th style="width: 25%;">SKU Barcode</th>
                                        <th style="width: 32%;">Color Photo</th>
                                        <th style="width: 5%; text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody class="color-items-container">
                                    <tr class="color-item-row">
                                        <input type="hidden" name="variant_item_ids[${sIdx}][0]" value="">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div style="flex: 1;">
                                                    <select name="variant_colors[${sIdx}][0]" class="form-control select2 color-select" style="width: 100%;">
                                                        <option value="">Select Color</option>
                                                        @foreach($colors as $color)
                                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <a href="javascript:void(0)" class="refreshColorBtn ml-1 text-info" title="Refresh Colors"><i class="fas fa-sync-alt" style="font-size: 10px;"></i></a>
                                            </div>
                                        </td>
                                        <td><span class="text-muted" style="font-size: 10px;">Auto on save</span></td>
                                        <td>
                                            <input type="file" name="variant_images[${sIdx}][0]" class="form-control-file variant-image-input" accept="image/*" style="font-size: 11px;">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs text-danger remove-color-item p-0" disabled>
                                                <i class="fas fa-times-circle" style="font-size: 14px;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div>
                                <button type="button" class="btn btn-xs btn-outline-primary add-color-item font-weight-bold" data-set-index="${sIdx}">
                                    <i class="fas fa-plus mr-1"></i> Add Color
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#size-set-container').append(blockHtml);
                initSelect2($('#size-set-container .size-set-block:last .select2'));
                reindexAll();
            });

            // Add Another Color Row
            $(document).on('click', '.add-color-item', function () {
                let sIdx = $(this).attr('data-set-index');
                let container = $(this).closest('.size-set-block').find('.color-items-container');
                let cIdx = container.find('.color-item-row').length;
                let rowHtml = `
                    <tr class="color-item-row">
                        <input type="hidden" name="variant_item_ids[${sIdx}][${cIdx}]" value="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="flex: 1;">
                                    <select name="variant_colors[${sIdx}][${cIdx}]" class="form-control select2 color-select" style="width: 100%;">
                                        <option value="">Select Color</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <a href="javascript:void(0)" class="refreshColorBtn ml-1 text-info" title="Refresh Colors"><i class="fas fa-sync-alt" style="font-size: 10px;"></i></a>
                            </div>
                        </td>
                        <td><span class="text-muted" style="font-size: 10px;">Auto on save</span></td>
                        <td>
                            <input type="file" name="variant_images[${sIdx}][${cIdx}]" class="form-control-file variant-image-input" accept="image/*" style="font-size: 11px;">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs text-danger remove-color-item p-0" title="Delete Color">
                                <i class="fas fa-times-circle" style="font-size: 14px;"></i>
                            </button>
                        </td>
                    </tr>
                `;
                container.append(rowHtml);
                initSelect2(container.find('.color-item-row:last .select2'));
                reindexAll();
            });

            // Remove Size Set Block
            $(document).on('click', '.remove-size-set', function () {
                if ($('.size-set-block').length > 1) {
                    $(this).closest('.size-set-block').remove();
                    reindexAll();
                }
            });

            // Remove Color Item
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
                window.currentSizeSelect = $(this).closest('.size-set-block').find('.size-set-select');
                let option = window.currentSizeSelect.find(':selected');
                let setGroup = option.data('set-group') || "";
                let setSizeName = option.text();

                $('#ratio_size_name').text(setSizeName);
                loadRatioSizeGroup(setGroup);
                $('#sizeRatioModal').css('display', 'flex');
            });

            window.closeRatioModal = function () {
                $('#sizeRatioModal').hide();
            };

            window.loadRatioSizeGroup = function (group) {
                window.sizeCounts = {};
                if (group) {
                    group.toString().split(',').forEach(size => {
                        window.sizeCounts[size] = (window.sizeCounts[size] || 0) + 1;
                    });
                }
                renderRatioSizes();
            };

            window.changeRatioCount = function (size, change) {
                window.sizeCounts[size] = (window.sizeCounts[size] || 0) + change;
                if (window.sizeCounts[size] < 0) window.sizeCounts[size] = 0;
                renderRatioSizes();
            };

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
                            <strong class="text-dark">${size}</strong>
                            <div class="counter-ratio">
                                <button type="button" onclick="changeRatioCount('${size}', -1)">-</button>
                                <span class="font-weight-bold px-2">${count}</span>
                                <button type="button" onclick="changeRatioCount('${size}', 1)">+</button>
                            </div>
                        </div>
                    `;
                });

                let groupText = document.getElementById('ratioGroupText');
                if (groupText) groupText.innerText = group.join(',');

                let hiddenVal = document.getElementById('ratio_val_hidden');
                if (hiddenVal) hiddenVal.value = getCalculatedRatio(group.join(','));
            };

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
                            let $existingOption = window.currentSizeSelect.find(`option[value="${response.new_size_set_id}"]`);
                            let optionText = response.new_size_name + " (" + response.no_of_pcs + " Pcs)";
                            if ($existingOption.length === 0) {
                                let newOption = new Option(optionText, response.new_size_set_id, true, true);
                                $(newOption).attr('data-set-group', response.new_size_group);
                                $(newOption).attr('data-pcs', response.no_of_pcs);
                                window.currentSizeSelect.append(newOption).trigger('change');
                            } else {
                                let newOption = new Option(optionText, response.new_size_set_id, true, true);
                                $(newOption).attr('data-set-group', response.new_size_group);
                                $(newOption).attr('data-pcs', response.no_of_pcs);
                                $existingOption.replaceWith(newOption);
                                window.currentSizeSelect.trigger('change');
                            }
                        }
                        closeRatioModal();
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert("Error saving ratio.");
                    }
                });
            };

            // Gallery Images
            $('.add-new-product-image-btn').on('click', function() {
                let rowHtml = `
                    <div class="new-product-image-row border rounded p-2 mb-1 bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <input type="text" name="new_product_image_titles[]" class="form-control hd-input" placeholder="Photo Caption / Title">
                            </div>
                            <div class="col-md-6">
                                <input type="file" name="new_product_images[]" class="form-control-file new-product-image-file-input" accept="image/*" style="font-size: 11px;">
                            </div>
                            <div class="col-md-1 text-right">
                                <button type="button" class="btn btn-xs text-danger remove-new-product-image-btn" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#new-product-images-container').append(rowHtml);
                reindexAll();
            });

            $(document).on('click', '.remove-new-product-image-btn', function() {
                $(this).closest('.new-product-image-row').remove();
                reindexAll();
            });

            $(document).on('click', '.remove-existing-image-btn', function() {
                let imgId = $(this).data('id');
                if (confirm('Are you sure you want to delete this product image?')) {
                    $('#deleted-images-holder').append('<input type="hidden" name="delete_image_ids[]" value="' + imgId + '">');
                    $('#existing-image-' + imgId).remove();
                    reindexAll();
                }
            });
        });
    </script>
@endsection