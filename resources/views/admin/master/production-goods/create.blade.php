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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Type</label>
                                    <select name="type_of_garment" id="type_of_garment" class="form-control" required>
                                        <option value="">Select Product type</option>
                                        
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
                                    <label for="exampleInputEmail1">Product Name</label>
                                    <input type="text" name="name_of_garment" class="form-control" placeholder="Enter name of product" value="{{old('name_of_garment')}}">
                                    @if ($errors->has('name_of_garment'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name_of_garment') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            

                            
                             
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Printing</label>
                                    <select name="is_printing" class="form-control select2" style="width: 100%;">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Embroidery</label>
                                    <select name="is_embroidery" class="form-control select2" style="width: 100%;">
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                            </div> -->

                            <div class="col-md-6">
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
                            
                            
                           

                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU">
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric</label>
                                    <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" multiple>
                                        @foreach($fabrics as $single_data)
                                        <option value="{{$single_data->sku}}" {{old('fabric_sku') == $single_data->sku ? 'selected' : ''}}>{{$single_data->sku}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('fabric_sku'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('fabric_sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>   -->

                            <div class="col-md-12">
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

                            

                            <!-- <div class="col-md-12">
                                <label>Fabric Details</label>
                                <div id="fabric-container">
                                    <div class="fabric-row row mb-2">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" required>
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
                                            <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success add-fabric"><i class="fa fa-plus"></i></button>
                                        </div>
                                    </div>
                                </div>


                            </div> -->

                                          
                           
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
    function generateSKU() {
        let type_of_garment = $("select[name='type_of_garment'] option:selected").text().trim();
        let name_of_garment = $("input[name='name_of_garment']").val().trim();
         let product_color = $("select[name='master_color_id'] option:selected").text().trim();
        let master_size_id = $("select[name='master_size_id'] option:selected").text().trim();
        let garment_pattern = $("select[name='garment_pattern'] option:selected").text().trim();

   

        // Remove special characters and uppercase
        type_of_garment = type_of_garment.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        name_of_garment = name_of_garment.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        product_color = product_color.replace(/[^a-zA-Z0-9]/g, '').toUpperCase()
        garment_pattern = garment_pattern.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_size_id = master_size_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        let sku = type_of_garment + '-' + name_of_garment + '-' + garment_pattern + '-' + product_color + '-' + master_size_id;

        let skuInput = $("#sku");
        if (!skuInput.data('edited') || skuInput.val() === "") {
            skuInput.val(sku);
        }
    }

    $(document).ready(function() {
        // Name input
        $("select[name='type_of_garment']").on("change", generateSKU);
        $("input[name='name_of_garment']").on("input", generateSKU);
        $("select[name='garment_pattern']")
            .on("change", generateSKU);
        $("select[name='master_color_id']")
            .on("change", generateSKU);
        // All select fields
        $("select[name='master_size_id']")
            .on("change", generateSKU);

        // Mark SKU as manually edited
        $("#sku").on("input", function() {
            $(this).data('edited', true);
        });
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
$(document).ready(function () {
    // Initialize Select2 for existing selects
    $('.select2').select2();

    // Add new stage row
    let indexRow = 0;
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
                        <input type="radio" name="printing_stage_after" class="printing-radio" id="is_printing_${indexRow}" >
                        <label for="is_printing_${indexRow}">Printing</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <input type="radio" class="embroidery-radio" name="embroidery_stage_after" id="is_embroidery_${indexRow}" >
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

    // $(document).on('change', '.stage-select', function () {
    //     let id = $(this).val();                // selected stage id
    //     let index = $(this).data('index');     // row index

    //     // Set value in both radio buttons
    //     $(`#is_printing_${index}`).val(id);
    //     $(`#is_embroidery_${index}`).val(id);
    // });

    // $(document).on('click', '.printing-radio, .embroidery-radio', function () {
    //     let index = $(this).data('index');
    //     let stageId = $(`select[data-index="${index}"]`).val();
    //     $(this).val(stageId);
    // });

    $(document).on('click', '.printing-radio, .embroidery-radio', function () {
        let row = $(this).closest('.stage-row'); 
        let stageId = row.find('.stage-select').val();  // correct stage value

        $(this).val(stageId);
    });

    $(document).on('change', '.stage-select', function () {
        let stageId = $(this).val();
        let row = $(this).closest('.stage-row'); // <-- correct row

        // ONLY this row's radio values update
        row.find('.printing-radio').val(stageId);
        row.find('.embroidery-radio').val(stageId);
    });
});
</script>

@endsection
