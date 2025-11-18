@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Receipt Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Item Receipt Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Item Receipt Detail</h3>
                </div>
                <form action="{{route('admin.item_receipt.storeDetail')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}" >
                    <div class="card-body">
                        <div class="row">                         

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Item SKU</label>
                                    <select name="item_sku" id="item_sku" class="form-control select2" style="width: 100%;">
                                        @foreach($items as $single_data)
                                        <option value="{{$single_data->sku}}" >{{$single_data->sku}}</option>
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
                                    <label for="box">Boxes</label>
                                    <input type="number" name="box" id="box" class="form-control" step="1" min="1" placeholder="Enter boxes" >
                                    @if ($errors->has('box'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('box') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity (per box)</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter quantity" step="1" min="1">
                                    @if ($errors->has('quantity'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('quantity') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Purchase Order</label>
                                    <select name="purchase_order_id" id="purchase_order_id" class="form-control select2" style="width: 100%;">
                                        <option value="0">NIL</option>
                                        @foreach($purchase_orders as $single_data)
                                        <option value="{{$single_data->id}}">{{$single_data->sku}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('purchase_order_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('purchase_order_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-success">
                                        Save & Add New
                                    </button>
                                    <a href="{{ route('admin.item_receipt.index') }}" class="btn btn-danger">
                                        Exit Without Save
                                    </a>
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
