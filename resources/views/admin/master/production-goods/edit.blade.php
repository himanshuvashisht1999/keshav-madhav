@extends('admin.layouts.app')
@section('content')
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
                <form action="{{route('admin.master.production-goods.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="card-body">
                        <div class="row">
                            <input type="hidden" name="company_id" value="2" id="company_id">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Design Number</label>
                                    <input type="text" name="design_number" class="form-control" value="{{ $data->design_number }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Series Name</label>
                                    <select name="master_series_id" id="master_series_id" class="form-control select2" style="width: 100%;">
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
                                    <input type="text" name="name_of_garment" class="form-control" value="{{ $data->name_of_garment }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fitting</label>
                                    <select name="master_product_fitting_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Fitting</option>
                                        @foreach($fittings as $fit)
                                            <option value="{{ $fit->id }}" {{ $data->master_product_fitting_id == $fit->id ? 'selected' : '' }}>{{ $fit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Pattern</label>
                                    <select name="master_pattern_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Pattern</option>
                                        @foreach($garment_patterns as $p)
                                            <option value="{{ $p->id }}" {{ $data->master_pattern_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="card card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title">Size Sets & Pricing</h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-primary btn-sm add-size-set">Add More Size Set</button>
                                        </div>
                                    </div>
                                    <div class="card-body" id="size-set-container">
                                        @php $sIdx = 0; @endphp
                                        @forelse($data->variants as $variant)
                                            <div class="size-set-block mb-4 p-3 border rounded bg-light">
                                                <div class="row align-items-end mb-3">
                                                    <div class="col-md-4">
                                                        <input type="hidden" name="variant_ids[]" value="{{ $variant->id }}">
                                                        <div class="form-group mb-0">
                                                            <label>Size Set</label>
                                                            <select name="size_sets[]" class="form-control select2 size-set-select">
                                                                <option value="">Select Size Set</option>
                                                                @foreach($sizes as $size)
                                                                    <option value="{{ $size->id }}" {{ $variant->master_size_measurement_id == $size->id ? 'selected' : '' }}>
                                                                        {{ $size->name }} ({{ $size->set_size }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group mb-0">
                                                            <label>MRP (for this set)</label>
                                                            <input type="number" name="mrps[]" class="form-control mrp-input" value="{{ $variant->mrp }}" step="0.01">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group mb-0">
                                                            <label>Set Image</label>
                                                            <input type="file" name="size_set_images[]" class="form-control-file size-set-image-input" accept="image/*">
                                                            @if($variant->image)
                                                                <a href="{{ asset('assets/products/'.$variant->image) }}" target="_blank">
                                                                    <img src="{{ asset('assets/products/'.$variant->image) }}" class="img-thumbnail mt-1" style="max-height: 40px;">
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2 text-right">
                                                        <button type="button" class="btn btn-danger btn-sm remove-size-set"><i class="fa fa-trash"></i> Remove Set</button>
                                                    </div>
                                                </div>

                                                <div class="color-items-container ml-4">
                                                    <h6>Colors & Images</h6>
                                                    @php $cIdx = 0; @endphp
                                                    @foreach($variant->items as $item)
                                                        <div class="color-item-row row mb-2 align-items-center border-bottom pb-2">
                                                            <input type="hidden" name="variant_item_ids[{{ $sIdx }}][{{ $cIdx }}]" value="{{ $item->id }}">
                                                            <div class="col-md-4">
                                                                <label class="small">Color</label>
                                                                <select name="variant_colors[{{ $sIdx }}][{{ $cIdx }}]" class="form-control select2 color-select">
                                                                    <option value="">Select Color</option>
                                                                    @foreach($colors as $color)
                                                                        <option value="{{ $color->id }}" {{ $item->master_color_id == $color->id ? 'selected' : '' }}>
                                                                            {{ $color->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="small text-muted d-block">Change Image</label>
                                                                <input type="file" name="variant_images[{{ $sIdx }}][{{ $cIdx }}]" class="form-control-file d-inline variant-image-input" accept="image/*" style="width: auto;">
                                                                @if($item->image)
                                                                    <a href="{{ asset('assets/products/'.$item->image) }}" target="_blank">
                                                                        <img src="{{ asset('assets/products/'.$item->image) }}" class="img-thumbnail ml-2" style="max-height: 40px;">
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-2 text-right">
                                                                <button type="button" class="btn btn-warning btn-sm remove-color-item"><i class="fa fa-times"></i></button>
                                                            </div>
                                                        </div>
                                                        @php $cIdx++; @endphp
                                                    @endforeach
                                                </div>
                                                <div class="ml-4 mt-2">
                                                    <button type="button" class="btn btn-info btn-sm add-color-item" data-set-index="{{ $sIdx }}"><i class="fa fa-plus"></i> Add Another Color</button>
                                                </div>
                                            </div>
                                            @php $sIdx++; @endphp
                                        @empty
                                            <div class="size-set-block mb-4 p-3 border rounded bg-light">
                                                <p class="text-muted">No variants defined. Click "Add More" to create one.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 text-right mt-3">
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow">Update Product Specification</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        function reindexAll() {
            $('.size-set-block').each(function(sIdx) {
                $(this).find('.add-color-item').attr('data-set-index', sIdx);
                $(this).find('input[name="variant_ids[]"]').val($(this).find('input[name="variant_ids[]"]').val() || ''); // Keep if exists, else empty
                $(this).find('.size-set-select').attr('name', 'size_sets[]');
                $(this).find('.mrp-input').attr('name', 'mrps[]');
                $(this).find('.size-set-image-input').attr('name', 'size_set_images[]');

                $(this).find('.color-item-row').each(function(cIdx) {
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

        $('.add-size-set').on('click', function() {
            let sIdx = $('.size-set-block').length;
            let blockHtml = `
                <div class="size-set-block mb-4 p-3 border rounded bg-light">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4 text-left">
                            <input type="hidden" name="variant_ids[]" value="">
                            <label>Size Set</label>
                            <select name="size_sets[]" class="form-control select2 size-set-select">
                                <option value="">Select Size Set</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}">{{ $size->name }} ({{ $size->set_size }})</option>
                                @endforeach
                            </select>
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
                                <select name="variant_colors[${sIdx}][0]" class="form-control select2 color-select">
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
            $('#size-set-container .size-set-block:last .select2').select2({ theme: 'bootstrap4' });
            reindexAll();
        });

        $(document).on('click', '.add-color-item', function() {
            let sIdx = $(this).attr('data-set-index');
            let container = $(this).closest('.size-set-block').find('.color-items-container');
            let cIdx = container.find('.color-item-row').length;
            let rowHtml = `
                <div class="color-item-row row mb-2 align-items-center border-bottom pb-2">
                    <input type="hidden" name="variant_item_ids[${sIdx}][${cIdx}]" value="">
                    <div class="col-md-4">
                        <select name="variant_colors[${sIdx}][${cIdx}]" class="form-control select2 color-select">
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
            container.find('.color-item-row:last .select2').select2({ theme: 'bootstrap4' });
            reindexAll();
        });

        $(document).on('click', '.remove-size-set', function() {
            if ($('.size-set-block').length > 1) {
                $(this).closest('.size-set-block').remove();
                reindexAll();
            }
        });

        $(document).on('click', '.remove-color-item', function() {
            let container = $(this).closest('.color-items-container');
            if (container.find('.color-item-row').length > 1) {
                $(this).closest('.color-item-row').remove();
                reindexAll();
            }
        });
    });
</script>
@endsection
