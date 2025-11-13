@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Attribute</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Edit Item Attribute</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Item Attribute</h3>
                </div>
                <form action="{{route('admin.master.item-attributes.update')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item_id" value="{{$item_id}}">
                    <input type="hidden" name="id" value="{{$data->id}}">
                    <div class="card-body">
                        <div class="row">
                            <!-- ATTRIBUTE DROPDOWN -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Item Attribute</label>
                                    <select name="item_attribute_id" id="item_attribute_id" class="form-control select2" style="width: 100%;">
                                        @foreach($attributes as $single_data)
                                            <option value="{{$single_data->id}}" data-sku="{{$single_data->sku}}" {{$data->item_attribute_id == $single_data->id ? 'selected' : ''}}>
                                                {{$single_data->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('item_attribute_id'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('item_attribute_id') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- VALUE INPUT -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value">Value</label>
                                    <input type="text" name="value" id="value" class="form-control" placeholder="Enter value" value="{{$data->value}}" required>
                                    @if ($errors->has('value'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('value') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- SKU FIELD -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" value="{{$data->sku}}" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
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
