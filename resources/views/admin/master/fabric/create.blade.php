@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Fabric</li>
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
                    <h3 class="card-title">Create Fabric</h3>
                </div>
                <form action="{{route('admin.master.fabric.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Fabric Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter fabric name" value="{{old('name')}}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric Dye</label>
                                    <select name="dye_id" class="form-control select2" style="width: 100%;">
                                        @foreach($fab_dye_data as $single_data)
                                        <option value="{{$single_data->id}}" {{old('dye_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('dye_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('dye_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric Width</label>
                                    <select name="width_id" class="form-control select2" style="width: 100%;">
                                        @foreach($fab_width_data as $single_data)
                                        <option value="{{$single_data->id}}" {{old('width_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('width_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('width_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric Weave Type</label>
                                    <select name="weave_type_id" class="form-control select2" style="width: 100%;">
                                        @foreach($fab_weave_data as $single_data)
                                        <option value="{{$single_data->id}}" {{old('weave_type_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('weave_type_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('weave_type_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric GSM</label>
                                    <select name="gsm_id" class="form-control select2" style="width: 100%;">
                                        @foreach($fab_gsm_data as $single_data)
                                        <option value="{{$single_data->id}}" {{old('gsm_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('gsm_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('gsm_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric Composition</label>
                                    <select name="composition_id" class="form-control select2" style="width: 100%;">
                                        @foreach($fab_composition_data as $single_data)
                                        <option value="{{$single_data->id}}" {{old('composition_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('composition_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('composition_id') }}
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
        let name = document.querySelector("input[name='name']").value.trim();
        let dye = document.querySelector("select[name='dye_id'] option:checked").text.trim();
        let width = document.querySelector("select[name='width_id'] option:checked").text.trim();
        let gsm = document.querySelector("select[name='gsm_id'] option:checked").text.trim();

        // Get first 4 letters of fabric name
        let part1 = name.replace(/[^a-zA-Z]/g, '').substring(0, 4);

        // Get first 3 letters of dye
        let part2 = dye.replace(/[^a-zA-Z]/g, '').substring(0, 4);

        // Get first 3 letters of width
        let part3 = width.replace(/[^a-zA-Z0-9]/g, '').substring(0, 4);
        let part4 = gsm.replace(/[^a-zA-Z0-9]/g, '');

        // Combine
        let sku = part1 + "-" + part2 + "-" + part3 + "-" + part4;

        let skuInput = document.getElementById("sku");

        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // When page is ready
    $(document).ready(function() {
        // Trigger SKU generation when typing name
        $("input[name='name']").on("input", generateSKU);

        // Trigger SKU when changing select2 dropdowns
        $('select[name="dye_id"]').on("change", generateSKU);
        $('select[name="width_id"]').on("change", generateSKU);

        // Mark SKU as edited if user types
        $("#sku").on("input", function() {
            this.dataset.edited = true;
        });
    });
</script>


@endsection
