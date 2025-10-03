@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Garments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Garments</li>
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
                    <h3 class="card-title">Edit Garments</h3>
                </div>
                <form action="{{route('admin.master.production-goods.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type_of_garment">Garment Type</label>
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
                                    <label for="exampleInputEmail1">Garment Name</label>
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
                                    <label>Garment Size</label>
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
                            <?php
                                $selected_fabrics = $data->bill_of_materials->pluck('fabric_sku')->toArray();
                            ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fabric</label>
                                    <select name="fabric_sku[]" class="form-control select2" style="width: 100%;" multiple>
                                        <!-- <option value="">Select</option> -->
                                        @foreach($fabrics as $single_data)

                                        <option value="{{$single_data->sku}}" {{ in_array($single_data->sku, $selected_fabrics) ? 'selected' : '' }}>{{$single_data->sku}}</option>
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
