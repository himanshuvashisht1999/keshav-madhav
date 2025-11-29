@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Packaging</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Packaging</li>
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
                    <h3 class="card-title">Create Packaging</h3>
                </div>
                <form action="{{route('admin.warehouse.packagingStore')}}" method="post">
                    @csrf
                    <input type="hidden" name="order_id" value="{{$order_data->id}}">
                    <div class="card-body">
                        <div class="row">
                            

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Product Type</label>
                                    <select name="product_type_sku" class="form-control select2" style="width: 100%;" required>
                                        @foreach($product_types as $product_type)
                                        <option value="{{$product_type}}">{{$product_type}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('product_type_sku'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('product_type_sku') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Quantity (Per Box)</label>
                                    <input type="number" name="quantity" class="form-control" placeholder="Enter quantity" value="{{old('quantity')}}">
                                    @if ($errors->has('quantity'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('quantity') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Description</label>
                                    <textarea name="description" id="" class="form-control" ></textarea>
                                    @if ($errors->has('description'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('description') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            
                            <div class="col-md-12">
                                <div class="" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            {{-- PACKAGE LIST TABLE --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Existing Packages</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($package_data->count())
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Main Order ID</th>
                                    <th>Product Type SKU</th>
                                    <th>Quantity (Per Box)</th>
                                    <th>Description</th>
                                    <th>Total Boxes</th>
                                    <th>Total Items</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package_data as $index => $package)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $package->order_main_id }}</td>
                                        <td>{{ $package->product_type_sku }}</td>
                                        <td>{{ $package->quantity }}</td>
                                        <td>{{ $package->description }}</td>

                                        {{-- Count boxes for this package --}}
                                        <td>{{ $package->package_boxes->count() }}</td>

                                        {{-- Count total items through relation --}}
                                        <td>
                                            {{ $package->package_boxes->sum(function($box) {
                                                return $box->package_boxes_items->count();
                                            }) }}
                                        </td>

                                        <td>
                                            <a href="{{ route('admin.warehouse.packagingShow', ['package_id' => $package->id]) }}"
                                               class="btn btn-sm btn-info">
                                                Show
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="p-3 mb-0">No packages created yet for this order.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>


@endsection
