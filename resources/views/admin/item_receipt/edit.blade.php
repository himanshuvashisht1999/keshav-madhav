@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Receipt</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Item Receipt</li>
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
                    <h3 class="card-title">Edit Item Receipt</h3>
                </div>
                <form action="{{route('admin.item_receipt.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="receipt_id" value="{{$data->id}}" >
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor</label>
                                    <select name="vendor_id" class="form-control select2" style="width: 100%;">
                                        @foreach($vendors as $single_data)
                                        <option value="{{$single_data->id}}" {{$data->vendor_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('vendor_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('vendor_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="truck_number">Truck Number</label>
                                    <input type="text" name="truck_number" id="truck_number" class="form-control" placeholder="Enter truck number" value="{{$data->truck_number}}">
                                    @if ($errors->has('truck_number'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('truck_number') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Date & Time</label>
                                    <input type="datetime-local" name="time" class="form-control" placeholder="Enter time" value="{{$data->time}}">
                                    @if ($errors->has('time'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('time') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="box">Boxes</label>
                                    <input type="number" name="box" id="box" class="form-control" placeholder="Enter boxes" step="1" min="1" value="{{$data->box}}">
                                    @if ($errors->has('box'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('box') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="received_by">Received By</label>
                                    <input type="text" name="received_by" id="received_by" class="form-control" placeholder="Enter received by" value="{{$data->received_by}}">
                                    @if ($errors->has('received_by'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('received_by') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Proceed</button>
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
