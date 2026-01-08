@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Products Specification</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Product Specification</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Product Specification</h3>
                </div>
                <form action="{{route('admin.master.production-goods.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">

                            {{-- Company --}}
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_id">Company</label>
                                    <select name="company_id" id="company_id" class="form-control" required>
                                        <option value="2" {{ $data->company_id == 2 ? 'selected' : '' }}>Royal Jeans</option>
                                        <option value="1" {{ $data->company_id == 1 ? 'selected' : '' }}>General</option>
                                    </select>
                                </div>
                                @if ($errors->has('company_id'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('company_id') }}
                                    </span>
                                @endif
                            </div> -->
                            <input type="hidden" name="company_id" id="company_id" value="2">

                            {{-- Product Type (GENERAL only) --}}
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label for="type_of_garment">Product Type</label>
                                    <select name="type_of_garment" id="type_of_garment" class="form-control">
                                        <option value="">Select Product type</option>
                                        @foreach($product_types as $product)
                                            <option value="{{ $product->sku }}"
                                                {{ $data->type_of_garment == $product->sku ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('type_of_garment'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('type_of_garment') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Design Number --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="design_number">Design Number</label>
                                    <input type="text" name="design_number" id="design_number" class="form-control"
                                           placeholder="Enter design number" value="{{$data->design_number}}">
                                    @if ($errors->has('design_number'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('design_number') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Product Name --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name_of_garment">Product Name</label>
                                    <input type="text" name="name_of_garment" id="name_of_garment" class="form-control"
                                           placeholder="Enter name of garment" value="{{$data->name_of_garment}}">
                                    @if ($errors->has('name_of_garment'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('name_of_garment') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Product Size (GENERAL) --}}
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Product Size</label>
                                    <select name="master_size_id" class="form-control select2" style="width: 100%;">
                                        @foreach($sizes as $single_data)
                                            <option value="{{$single_data->id}}" {{$data->master_size_id == $single_data->id ? 'selected' : ''}}>
                                                {{$single_data->sku}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_size_id'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('master_size_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Product Color (GENERAL) --}}
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Color</label>
                                    <select name="master_color_id" class="form-control select2" style="width: 100%;">
                                        @foreach($colors as $single_data)
                                            <option value="{{$single_data->id}}" {{$data->master_color_id == $single_data->id ? 'selected' : ''}}>
                                                {{$single_data->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_color_id'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('master_color_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <input type="hidden" name="is_printing" value="{{$data->is_printing}}">
                            <input type="hidden" name="is_embroidery" value="{{$data->is_embroidery}}">

                            {{-- Product Pattern (GENERAL) --}}
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Product Pattern</label>
                                    <select name="garment_pattern" class="form-control select2" style="width: 100%;" id="garment_pattern">
                                        @foreach($garment_patterns as $single_data)
                                            <option value="{{$single_data->sku}}" {{$data->garment_pattern == $single_data->sku ? 'selected' : ''}}>
                                                {{$single_data->sku}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('garment_pattern'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('garment_pattern') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="sku" value="{{$data->sku}}">

                            {{-- SKU --}}
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control"
                                           placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div> -->

                            {{-- Main Image (Edit) --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="main_image">Main Image</label>
                                    <input type="file" name="main_image" id="main_image" class="form-control" accept="image/*">

                                    @if ($errors->has('main_image'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('main_image') }}
                                        </span>
                                    @endif

                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- NEW selected main image preview --}}
                                <div class="mt-2">
                                    <img id="main_image_preview" src="#" alt="Main image preview"
                                        class="img-thumbnail" style="max-height: 160px; display:none;">
                                </div>

                                {{-- OLD stored main image (under the preview) --}}
                                @php
                                    $mainImage = optional($data->images->where('is_main', 1)->first());
                                @endphp
                                <div class="mt-2">
                                    <!-- <label class="d-block mb-1">Current Main Image</label> -->
                                    <img
                                        src="{{ $mainImage ? $mainImage->image : asset('assets/products/default-image.png') }}"
                                        alt="Current main image"
                                        class="img-thumbnail"
                                        style="max-height: 160px;">
                                </div>
                            </div>

                            {{-- Other Images (Edit) --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="other_images">Other Images</label>
                                    <input type="file" name="other_images[]" id="other_images" class="form-control"
                                        accept="image/*" multiple>

                                    @if ($errors->has('other_images'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('other_images') }}
                                        </span>
                                    @endif
                                    @if ($errors->has('other_images.*'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('other_images.*') }}
                                        </span>
                                    @endif

                                    {{-- NEW selected other images preview --}}
                                    <div id="other_images_preview" class="mt-2 d-flex flex-wrap" style="gap:8px;"></div>

                                    {{-- OLD stored other images (under the preview) --}}
                                    <div class="mt-2 d-flex flex-wrap" style="gap:8px;">
                                        @foreach($data->images->where('is_main', 0) as $img)
                                            <div class="existing-image-wrapper position-relative" style="display:inline-block;">
                                                <img
                                                    src="{{ $img->image }}"
                                                    alt="Other image"
                                                    class="img-thumbnail"
                                                    style="max-height:120px; max-width:120px; object-fit:cover;">

                                                {{-- Cross button to delete --}}
                                                <button type="button"
                                                        class="btn btn-sm btn-danger existing-image-delete-btn"
                                                        data-id="{{ $img->id }}"
                                                        style="position:absolute; top:2px; right:2px; padding:0 6px; line-height:1;">
                                                    &times;
                                                </button>
                                            </div>
                                        @endforeach

                                        @if($data->images->where('is_main', 0)->isEmpty())
                                            <span class="text-muted">No other images uploaded yet.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>


                            {{-- Production Stages (GENERAL visible, ROYAL hidden like create) --}}
                            <div class="col-md-12 stages-wrapper">
                                <label>Production Stages (in order)</label>
                                <div id="stages-container">
                                    @foreach($data->product_stages->whereNotIn('master_stage_id',[1,2]) as $key=>$single_stage)
                                        <div class="stage-row row mb-2"
                                             data-printing="{{ $data->printing_stage_after ?? '' }}"
                                             data-embroidery="{{ $data->embroidery_stage_after ?? '' }}">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <select name="product_stage_id[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                        <option value="">Select Stage</option>
                                                        @foreach($product_stages as $stage)
                                                            <option value="{{ $stage->id }}" {{ $stage->id == $single_stage->master_stage_id ? 'selected' : '' }}>
                                                                {{ $stage->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="radio" name="printing_stage_after" class="printing-radio" id="is_printing_{{$key}}">
                                                    <label for="is_printing_{{$key}}">Printing</label>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <input type="radio" class="embroidery-radio" name="embroidery_stage_after" id="is_embroidery_{{$key}}">
                                                    <label for="is_embroidery_{{$key}}">Embroidery</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                @if($key==0)
                                                    <button type="button" class="btn btn-success add-stage"><i class="fa fa-plus"></i></button>
                                                @else
                                                    <button type="button" class="btn btn-danger remove-stage"><i class="fa fa-minus"></i></button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Fabric Details --}}
                            <div class="col-md-12">
                                <label>Fabric Details</label>
                                <div id="fabric-container">
                                    @foreach($data->bill_of_materials as $key=>$single_bom)
                                        <div class="fabric-row row mb-2">
                                            <input type="hidden" name="bom_id[]" value="{{ $single_bom->id }}">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" required>
                                                        <option value="">Select Fabric</option>
                                                        @foreach($fabrics as $single_data)
                                                            <option value="{{$single_data->sku}}" {{ $single_bom->fabric_sku == $single_data->sku ? 'selected' : '' }}>
                                                                {{$single_data->sku}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter"
                                                       step="0.01" min="0" value="{{$single_bom->meter}}" >
                                            </div>
                                            <div class="col-md-2">
                                                @if($key==0)
                                                    <button type="button" class="btn btn-success add-fabric"><i class="fa fa-plus"></i></button>
                                                @else
                                                    <button type="button" class="btn btn-danger remove-fabric"><i class="fa fa-minus"></i></button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

{{-- FABRIC JS --}}
<script>
    $(document).ready(function () {

        // Add More Fabric
        $(document).on('click', '.add-fabric', function () {
            let newRow = `
                <div class="fabric-row row mb-2">
                    <input type="hidden" name="bom_id[]" value="">
                    <div class="col-md-5">
                        <div class="form-group">
                            <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" required>
                                <option value="">Select Fabric</option>
                                @foreach($fabrics as $single_data)
                                    <option value="{{$single_data->sku}}">{{$single_data->sku}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-fabric"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            `;
            $('#fabric-container').append(newRow);
            $('.select2').select2(); // reinitialize Select2 for new elements
        });

        // Remove Fabric Row
        $(document).on('click', '.remove-fabric', function () {
            $(this).closest('.fabric-row').remove();
        });

    });
</script>

{{-- STAGES + RADIO INIT --}}
<script>
$(document).ready(function () {
    // Initialize Select2 for existing selects
    $('.select2').select2();

    // Add new stage row
    let indexRow = {{ $data->product_stages->whereNotIn('master_stage_id',[1,2])->count() }};
    $(document).on('click', '.add-stage', function () {
        indexRow++;
        let newRow = `
            <div class="stage-row row mb-2">
                <div class="col-md-4">
                    <div class="form-group">
                        <select name="product_stage_id[]" class="form-control select2 stage-select" style="width: 100%;" required>
                            <option value="">Select Stage</option>
                            @foreach($product_stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="radio" name="printing_stage_after" class="printing-radio" id="is_printing_${indexRow}">
                        <label for="is_printing_${indexRow}">Printing</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="radio" class="embroidery-radio" name="embroidery_stage_after" id="is_embroidery_${indexRow}">
                        <label for="is_embroidery_${indexRow}">Embroidery</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-stage"><i class="fa fa-minus"></i></button>
                </div>
            </div>
        `;
        $('#stages-container').append(newRow);
        $('.select2').select2(); // re-init Select2 for new rows
    });

    // Remove stage row
    $(document).on('click', '.remove-stage', function () {
        $(this).closest('.stage-row').remove();
    });

    // When clicking printing/embroidery radio, set value = current row stage id
    $(document).on('click', '.printing-radio, .embroidery-radio', function () {
        let row = $(this).closest('.stage-row');
        let stageId = row.find('.stage-select').val();
        $(this).val(stageId);
    });

    // When changing stage select, update radio values for that row
    $(document).on('change', '.stage-select', function () {
        let stageId = $(this).val();
        let row = $(this).closest('.stage-row');

        row.find('.printing-radio').val(stageId);
        row.find('.embroidery-radio').val(stageId);
    });

    // After edit load: pre-check saved printing/embroidery stages
    $('.stage-row').each(function () {
        let row = $(this);
        let stageId = row.find('.stage-select').val();

        row.find('.printing-radio').val(stageId);
        row.find('.embroidery-radio').val(stageId);

        let savedPrint = row.data('printing');
        let savedEmb   = row.data('embroidery');

        if (savedPrint == stageId) {
            row.find('.printing-radio').prop('checked', true);
        }
        if (savedEmb == stageId) {
            row.find('.embroidery-radio').prop('checked', true);
        }
    });
});
</script>

{{-- COMPANY TOGGLE (Royal vs General) --}}
<script>
    $(document).ready(function () {
        function toggleCompanyFields() {
            var companyId = $('#company_id').val();

            if (companyId == '2') { // Royal Jeans
                $('.general')
                    .hide()
                    .find('input, select, textarea')
                    .prop('disabled', true);

                // hide stages section (they are auto on Royal)
                $('.stages-wrapper').hide();
            } else { // General or others
                $('.general')
                    .show()
                    .find('input, select, textarea')
                    .prop('disabled', false);

                $('.stages-wrapper').show();
            }
        }

        $('#company_id').on('change', toggleCompanyFields);

        // Initial state on load
        toggleCompanyFields();
    });
</script>

<script>
    $(document).ready(function () {
        // Main image preview
        $('#main_image').on('change', function () {
            const input = this;
            const preview = $('#main_image_preview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.attr('src', e.target.result)
                           .show();
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.hide().attr('src', '#');
            }
        });

        // Other images preview
        $('#other_images').on('change', function () {
            const container = $('#other_images_preview');
            container.empty();

            const files = this.files;
            if (!files || !files.length) {
                return;
            }

            Array.from(files).forEach(function (file) {
                if (!file.type.match('image.*')) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = $('<img>')
                        .attr('src', e.target.result)
                        .addClass('img-thumbnail')
                        .css({
                            maxHeight: '120px',
                            maxWidth: '120px',
                            objectFit: 'cover'
                        });

                    const wrapper = $('<div>')
                        .css({ position: 'relative' })
                        .append(img);

                    container.append(wrapper);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>
<script>
    $(document).ready(function () {
        // Click on cross button to delete existing image
        $(document).on('click', '.existing-image-delete-btn', function () {
            const id = $(this).data('id');
            if (!id) return;

            // Append hidden input once per image ID
            if ($('#delete_image_' + id).length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'delete_image_ids[]',
                    value: id,
                    id: 'delete_image_' + id
                }).appendTo('form');
            }

            // Remove image from UI
            $(this).closest('.existing-image-wrapper').fadeOut(200, function () {
                $(this).remove();
            });
        });
    });
</script>


@endsection
