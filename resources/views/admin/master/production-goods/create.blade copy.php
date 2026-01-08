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
                        <li class="breadcrumb-item active">Create Product Specification</li>
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
                    <h3 class="card-title">Create Product Specification</h3>
                </div>
                <form action="{{route('admin.master.production-goods.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Company</label>
                                    <select name="company_id" id="company_id" class="form-control" required>                                        
                                        <option value="2" selected>Royal Jeans</option>
                                        <option value="1">General</option>
                                    </select>
                                </div>
                                @if ($errors->has('company_id'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('company_id') }}
                                    </span>
                                @endif
                            </div> -->
                            <input type="hidden" name="company_id" value="2" id="company_id">
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Type</label>
                                    <select name="type_of_garment" id="type_of_garment" class="form-control" required>
                                        <!-- <option value="">Select Product type</option> -->
                                        
                                        @foreach($product_types as $product)
                                            <option value="{{ $product->sku }}"
                                                {{ old('type_of_garment') == $product->sku ? 'selected' : '' }}>
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @if ($errors->has('type_of_garment'))
                                    <span class="invalid-feedback d-block">
                                        {{ $errors->first('type_of_garment') }}
                                    </span>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Design Number</label>
                                    <input type="text" name="design_number" class="form-control" placeholder="Enter design number" value="{{old('design_number')}}">
                                    @if ($errors->has('design_number'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('design_number') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Name</label>
                                    <input type="text" name="name_of_garment" class="form-control" placeholder="Enter name of product" value="{{old('name_of_garment')}}">
                                    @if ($errors->has('name_of_garment'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name_of_garment') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            

                            
                             
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Product Size</label>
                                    <select name="master_size_id" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($sizes as $single_data)
                                        <option value="{{$single_data->id}}" {{old('master_size_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_size_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_size_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>  
                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Product Color</label>
                                    <select name="master_color_id" id="master_color_id" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($colors as $single_data)
                                        <option value="{{$single_data->id}}" {{old('master_color_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_color_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_color_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="is_printing" value="1">
                            <input type="hidden" name="is_embroidery" value="1">

                            <div class="col-md-6 general">
                                <div class="form-group">
                                    <label>Product Pattern</label>
                                    <select name="garment_pattern" class="form-control select2" style="width: 100%;" id="garment_pattern">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($garment_patterns as $single_data)
                                        <option value="{{$single_data->sku}}" {{old('garment_pattern') == $single_data->sku ? 'selected' : ''}}>{{$single_data->sku}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('garment_pattern'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('garment_pattern') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            
                           

                            <input type="hidden" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" >
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" >
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div> -->

                            {{-- Main Image --}}
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
                                <div class="mt-2">
                                    <img id="main_image_preview" src="#" alt="Main image preview"
                                        class="img-thumbnail" style="max-height: 160px; display:none;">
                                </div>

                            </div>

                            {{-- Other Images --}}
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

                                    <div id="other_images_preview" class="mt-2 d-flex flex-wrap" style="gap:8px;"></div>
                                </div>
                            </div>



                            <div class="col-md-12 stages-wrapper">
                                <label>Production Stages (in order)</label>
                                <div id="stages-container">
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
                                                <input type="radio" name="printing_stage_after" class="printing-radio"   id="is_printing">
                                                <label for="is_printing">Printing</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="radio" name="embroidery_stage_after" id="is_embroidery" class="embroidery-radio"  >
                                                <label for="is_embroidery">Embroidery</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success add-stage"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            

                            <div class="col-md-12">
                                <label>Fabric Details</label>
                                <div id="fabric-container">
                                    <div class="fabric-row row mb-2">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="fabric_sku[]" class="form-control select2" style="width: 100%;">
                                                    <option value="">Select Fabric</option>
                                                    @foreach($fabrics as $single_data)
                                                        <option value="{{$single_data->sku}}" {{ old('fabric_sku') == $single_data->sku ? 'selected' : '' }}>
                                                            {{$single_data->sku}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success add-fabric"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
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
<script>
    function normalizeSKU(text) {
        return text
            ? text.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()
            : '';
    }

    function generateSKU() {
        let companyId      = $('#company_id').val();
        let nameOfGarment  = $("input[name='name_of_garment']").val().trim();
        let designNumber   = $("input[name='design_number']").val().trim();
        let typeText       = $("#type_of_garment option:selected").text().trim();
        let colorText      = $("#master_color_id option:selected").text().trim();
        let sizeText       = $("select[name='master_size_id'] option:selected").text().trim();
        let patternText    = $("#garment_pattern option:selected").text().trim();

        // Normalise all parts
        nameOfGarment = normalizeSKU(nameOfGarment);
        designNumber  = normalizeSKU(designNumber);
        typeText      = normalizeSKU(typeText);
        colorText     = normalizeSKU(colorText);
        sizeText      = normalizeSKU(sizeText);
        patternText   = normalizeSKU(patternText);

        let sku = '';

        if (companyId === '2') {
            // Royal Jeans: DESIGN-NAME
            sku = [designNumber, nameOfGarment].filter(Boolean).join('-');
        } else if (companyId === '1') {
            // General: TYPE-NAME-PATTERN-COLOR-SIZE
            sku = [typeText, nameOfGarment, patternText, colorText, sizeText]
                    .filter(Boolean)
                    .join('-');
        } else {
            // fallback (treat like General)
            sku = [typeText, nameOfGarment, patternText, colorText, sizeText]
                    .filter(Boolean)
                    .join('-');
        }

        let skuInput = $("#sku");
        if (!skuInput.data('edited') || skuInput.val() === "") {
            skuInput.val(sku);
        }
    }

    $(document).ready(function() {
        // Trigger SKU generation on relevant field changes
        $("#company_id").on("change", generateSKU);
        $("input[name='design_number']").on("input", generateSKU);
        $("input[name='name_of_garment']").on("input", generateSKU);
        $("#type_of_garment").on("change", generateSKU);
        $("#garment_pattern").on("change", generateSKU);
        $("#master_color_id").on("change", generateSKU);
        $("select[name='master_size_id']").on("change", generateSKU);

        // Mark SKU as manually edited
        $("#sku").on("input", function() {
            $(this).data('edited', true);
        });

        // Initial generation on page load
        generateSKU();
    });
</script>

<script>
    $(document).ready(function () {

    // Add More Fabric
    $(document).on('click', '.add-fabric', function () {
        let newRow = `
            <div class="fabric-row row mb-2">
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
<script>
    // make indexRow/global so all functions can use it
    let indexRow = 0;

    // Template for a single stage row
    function addStageRowTemplate() {
        indexRow++;
        return `
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
    }

    // Auto-fill all stages for Royal
    function autoFillRoyalStages() {
        // clear existing rows
        $('#stages-container').empty();

        const productStages = @json($product_stages);

        productStages.forEach(function (stage, idx) {
            let rowHtml = addStageRowTemplate();
            $('#stages-container').append(rowHtml);

            let $row = $('#stages-container .stage-row').last();

            // Set the select value to this stage
            $row.find('.stage-select').val(stage.id).trigger('change');

            // For the first row: auto check both printing & embroidery
            if (idx === 0) {
                $row.find('.printing-radio').prop('checked', true);
                $row.find('.embroidery-radio').prop('checked', true);
            }
        });

        // Re-init select2 for new selects
        $('.select2').select2();
    }

    $(document).ready(function () {
        // Initialize Select2 for existing selects
        $('.select2').select2();

        // Add new stage row manually (for General or if needed)
        $(document).on('click', '.add-stage', function () {
            let newRow = addStageRowTemplate();
            $('#stages-container').append(newRow);
            $('.select2').select2(); // re-init Select2 for new rows
        });

        // Remove stage row
        $(document).on('click', '.remove-stage', function () {
            $(this).closest('.stage-row').remove();
        });

        // When clicking printing/embroidery radio, set its value to current row stage id
        $(document).on('click', '.printing-radio, .embroidery-radio', function () {
            let row = $(this).closest('.stage-row');
            let stageId = row.find('.stage-select').val();  // correct stage value
            $(this).val(stageId);
        });

        // When changing stage select, update radio values for that row
        $(document).on('change', '.stage-select', function () {
            let stageId = $(this).val();
            let row = $(this).closest('.stage-row'); // <-- correct row

            // ONLY this row's radio values update
            row.find('.printing-radio').val(stageId);
            row.find('.embroidery-radio').val(stageId);
        });
    });
</script>


<script>
    $(document).ready(function () {

        function toggleCompanyFields() {
            var companyId = $('#company_id').val();

            if (companyId == '2') { 
                // ROYAL JEANS

                // Hide and disable all general fields
                $('.general')
                    .hide()
                    .find('input, select, textarea')
                    .prop('disabled', true);

                // Hide Production Stages (because they will auto-fill)
                $('.stages-wrapper').hide();

                // Auto-fill Royal stages
                autoFillRoyalStages();

            } else if (companyId == '1') { 
                // GENERAL

                // Show general fields
                $('.general')
                    .show()
                    .find('input, select, textarea')
                    .prop('disabled', false);

                // Show Production Stages again
                $('.stages-wrapper').show();

            } else {
                // Default fallback
                $('.general')
                    .show()
                    .find('input, select, textarea')
                    .prop('disabled', false);

                $('.stages-wrapper').show();
            }
        }

        $('#company_id').on('change', function () {
            toggleCompanyFields();
            generateSKU();
        });

        toggleCompanyFields();
    });
</script>

<script>
    $(document).ready(function () {
        // Main image preview
        $('#main_image').on('change', function (e) {
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

                    const wrapper = $('<div>').css({
                        position: 'relative'
                    }).append(img);

                    container.append(wrapper);
                };
                reader.readAsDataURL(file);
            });
        });
    });
</script>



@endsection
