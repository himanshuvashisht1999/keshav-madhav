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
                                        @foreach($fab_dye_data as $single_data)
                                        <option value="{{$single_data->id}}" {{$data->dye_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->width_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->weave_type_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->gsm_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                        <option value="{{$single_data->id}}" {{$data->composition_id == $single_data->id ? 'selected' : ''}}>{{$single_data->sku}}</option>
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
                                    <input type="text" name="sku" id="sku_n" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
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



@endsection
