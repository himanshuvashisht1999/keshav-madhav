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
                        <li class="breadcrumb-item active">Edit Fabric</li>
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
                    <h3 class="card-title">Edit Fabric</h3>
                </div>
                <form action="{{route('admin.master.fabric.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Fabric Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter fabric name" value="{{$data->name}}">
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
                                        @foreach($dye_data as $single_data)
                                        <option value="{{$single_data->id}}" {{$data->dye_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->width_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->weave_type_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->gsm_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->composition_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
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
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}">
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
        let phone = document.querySelector("input[name='phone']").value.trim();
        let address = document.querySelector("input[name='address']").value.trim();
        
        // Get first 4 letters of name (fresh every time)
        let part1 = name.replace(/[^a-zA-Z]/g, '').substring(0, 4);

        // Get last 3 digits of phone
        let part2 = phone.replace(/[^0-9]/g, '');
        part2 = part2.length >= 3 ? part2.slice(-3) : part2;

        // Get first 3 letters of address
        let part3 = address.replace(/[^a-zA-Z]/g, '').substring(0, 3);

        // Combine
        let sku = part1 + "-" + part2 + "-" + part3;

        let skuInput = document.getElementById("sku");

        // Only overwrite if user has not manually typed in SKU
        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // Attach auto-generate on typing (name, phone, address)
    document.querySelector("input[name='name']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    document.querySelector("input[name='phone']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    document.querySelector("input[name='address']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    // Mark as manually edited when user types in SKU
    document.getElementById("sku").addEventListener("input", function() {
        this.dataset.edited = true;
    });
</script>

@endsection
