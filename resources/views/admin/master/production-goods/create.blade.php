@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Production Goods</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Production Goods</li>
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
                    <h3 class="card-title">Create Production Goods</h3>
                </div>
                <form action="{{route('admin.master.production-goods.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Garment Name</label>
                                    <input type="text" name="name_of_garment" class="form-control" placeholder="Enter name of garment" value="{{old('name_of_garment')}}">
                                    @if ($errors->has('name_of_garment'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name_of_garment') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Material</label>
                                    <select name="master_material_id" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($materials as $single_data)
                                        <option value="{{$single_data->id}}" {{old('master_material_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_material_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_material_id') }}
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Measurement Size</label>
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
                                    <label>Design</label>
                                    <select name="master_design_id" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        @foreach($designs as $single_data)
                                        <option value="{{$single_data->id}}" {{old('master_design_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('master_design_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('master_design_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>  
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric</label>
                                    <select name="fabric_sku" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
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
        let name_of_garment = $("input[name='name_of_garment']").val().trim();
        let master_material_id = $("select[name='master_material_id'] option:selected").text().trim();
        let master_color_id = $("select[name='master_color_id'] option:selected").text().trim();
        let master_size_id = $("select[name='master_size_id'] option:selected").text().trim();
        let master_design_id = $("select[name='master_design_id'] option:selected").text().trim();
        let fabric_sku = $("select[name='fabric_sku'] option:selected").text().trim();
   

        // Remove special characters and uppercase
        name_of_garment = name_of_garment.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_material_id = master_material_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_color_id = master_color_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_size_id = master_size_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        master_design_id = master_design_id.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        fabric_sku = fabric_sku.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        let sku = name_of_garment + '-' + master_material_id + '-' + master_color_id + '-' + master_size_id + '-' + master_design_id;

        let skuInput = $("#sku");
        if (!skuInput.data('edited') || skuInput.val() === "") {
            skuInput.val(sku);
        }
    }

    $(document).ready(function() {
        // Name input
        $("input[name='name_of_garment']").on("input", generateSKU);

        // All select fields
        $("select[name='master_material_id'], select[name='master_color_id'], select[name='master_size_id'], select[name='master_design_id'], select[name='fabric_sku']")
            .on("change", generateSKU);

        // Mark SKU as manually edited
        $("#sku").on("input", function() {
            $(this).data('edited', true);
        });
    });

</script>


@endsection
