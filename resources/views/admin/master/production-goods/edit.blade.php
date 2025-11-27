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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Type</label>
                                    <select name="type_of_garment" id="type_of_garment" class="form-control" required>
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
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Name</label>
                                    <input type="text" name="name_of_garment" class="form-control" placeholder="Enter name of garment" value="{{$data->name_of_garment}}">
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
                                        <option value="{{$single_data->id}}" {{$data->master_size_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                    <label>Color</label>
                                    <select name="master_color_id" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($colors as $single_data)
                                        <option value="{{$single_data->id}}" {{$data->master_color_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                            <!-- <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Printing</label>
                                    <select name="is_printing" class="form-control select2" style="width: 100%;">
                                        <option value="0" {{$data->is_printing == 0 ? 'selected' : ''}}>No</option>
                                        <option value="1" {{$data->is_printing == 1 ? 'selected' : ''}}>Yes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Is Embroidery</label>
                                    <select name="is_embroidery" class="form-control select2" style="width: 100%;">
                                        <option value="0" {{$data->is_printing == 0 ? 'selected' : ''}}>No</option>
                                        <option value="1" {{$data->is_printing == 1 ? 'selected' : ''}}>Yes</option>
                                    </select>
                                </div>
                            </div> -->
                            
                            
                           

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Pattern</label>
                                    <select name="garment_pattern" class="form-control select2" style="width: 100%;" id="garment_pattern">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($garment_patterns as $single_data)
                                        <option value="{{$single_data->sku}}" {{$data->garment_pattern == $single_data->sku ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label>Production Stages (in order)</label>
                                <div id="stages-container">
                                    @foreach($data->product_stages->whereNotIn('master_stage_id',[1,2]) as $key=>$single_stage)
                                    <div class="stage-row row mb-2" data-printing="{{ $data->printing_stage_after ?? '' }}"     data-embroidery="{{ $data->embroidery_stage_after ?? '' }}" >
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select name="product_stage_id[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Stage</option>
                                                    @foreach($product_stages as $stage)
                                                        <option value="{{ $stage->id }}"   {{ $stage->id == $single_stage->master_stage_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="radio" name="printing_stage_after" class="printing-radio" id="is_printing_{{$key}}" >
                                                <label for="is_printing_{{$key}}">Printing</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="radio" class="embroidery-radio" name="embroidery_stage_after" id="is_embroidery_{{$key}}" >
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

                            <!-- <div class="col-md-12">
                                <label>Fabric Details</label>
                                <div id="fabric-container">
                                    @foreach($data->bill_of_materials as $key=>$single_bom)
                                    <div class="fabric-row row mb-2">
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
                                            <input type="number" name="fabric_meter[]" class="form-control" placeholder="Enter meter" step="0.01" min="0" value="{{$single_bom->meter}}" required>
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

    // after edit load then selected radio button 
    $(document).ready(function () {
        $('.stage-row').each(function () {
            let row = $(this);
            let stageId = row.find('.stage-select').val();

            row.find('.printing-radio').val(stageId);
            row.find('.embroidery-radio').val(stageId);

            // match saved value
            let savedPrint = row.data('printing');
            let savedEmb = row.data('embroidery');
            console.log()
            if (savedPrint == stageId) {
                row.find('.printing-radio').prop('checked', true);
            }

            if (savedEmb == stageId) {
                row.find('.embroidery-radio').prop('checked', true);
            }
        });
    });

});
</script>

@endsection
