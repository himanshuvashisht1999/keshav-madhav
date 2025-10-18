@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Products</h1>
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

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Product</h3>
                </div>
                <form action="{{route('admin.master.production-goods.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_of_garment">Product Type</label>
                                    <input list="garment_types" name="type_of_garment" id="type_of_garment"
                                        class="form-control" placeholder="Select or type garment type"
                                        value="{{$data->type_of_garment}}">

                                    <datalist id="garment_types">
                                        @foreach($garment_types as $garment)
                                            <option value="{{ $garment->type_of_garment }}">
                                        @endforeach
                                    </datalist>

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
                            
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="garment_pattern">Garment Pattern</label>
                                    <input list="garment_patterns" name="garment_pattern" id="garment_pattern"
                                        class="form-control" placeholder="Select or type garment type"
                                        value="{{$data->garment_pattern }}">

                                    <datalist id="garment_patterns">
                                        @foreach($garment_patterns as $single)
                                            <option value="{{ $single->garment_pattern }}">
                                        @endforeach
                                    </datalist>

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
                                    @foreach($data->product_stages as $key=>$single_stage)
                                    <div class="stage-row row mb-2">
                                        <div class="col-md-10">
                                            <div class="form-group">
                                                <select name="product_stage_id[]" class="form-control select2 stage-select" style="width: 100%;" required>
                                                    <option value="">Select Stage</option>
                                                    @foreach($product_stages as $stage)
                                                        <option value="{{ $stage->id }}"   {{ $stage->id == $single_stage->master_stage_id ? 'selected' : '' }}>{{ $stage->name }}</option>
                                                    @endforeach
                                                </select>
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

                            <div class="col-md-12">
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
    $(document).on('click', '.add-stage', function () {
        let newRow = `
            <div class="stage-row row mb-2">
                <div class="col-md-10">
                    <div class="form-group">
                        <select name="product_stage_id[]" class="form-control select2 stage-select" style="width: 100%;" required>
                            <option value="">Select Stage</option>
                            @foreach($product_stages as $stage)
                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                            @endforeach
                        </select>
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
});
</script>

@endsection
