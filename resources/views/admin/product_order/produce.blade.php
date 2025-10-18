@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="mb-0">Production Order Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.product_order.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Section -->
    <section class="content">
        <div class="container-fluid">

            <!-- Order Info -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-info-circle mr-1"></i> Production Order Information</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped mb-0">
                        <tr>
                            <th width="20%">Order SKU</th>
                            <td>{{ $data->sku }}</td>
                            <th>Created Date</th>
                            <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, h:i A') }}</td>
                            
                        </tr>
                        <tr>
                            <th width="20%">Customer</th>
                            <td>{{ $data->customer->name}}</td>
                            <th width="20%">Expected Delivery Date</th>
                            <td>{{ \Carbon\Carbon::parse($data->expected_delivery_date)->format('d M Y') }}</td>
                            <!-- <th>Status</th>
                            <td>
                                @if($data->status == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td> -->
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ordered Products -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <strong><i class="fas fa-box mr-1"></i> Ordered Products</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <!-- <th width="5%">Order SKU</th> -->
                                <th width="20%">Product SKU</th>
                                <th width="10%">Quantity</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->products as $index => $product)
                                <tr>
                                    <!-- <td class="align-top">{{ $data->sku }}</td> -->
                                    <td class="align-top">{{ $product->product_sku }}</td>
                                    <td class="align-top">{{ $product->quantity }}</td>
                                    <td>
                                        <!-- Fabric Details -->
                                        <div class="mb-3">
                                            

                                            @if($product->product_details->count() > 0)
                                                <table class="table table-sm table-bordered mb-0 bg-light">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Fabric SKU</th>
                                                            <th>Meter per Product</th>
                                                            <th>Total Required Meter</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($product->product_details as $detail)
                                                            <tr>
                                                                <td>{{ $detail->fabric_sku }}</td>
                                                                <td>{{ $detail->meter }}</td>
                                                                <td>{{ $detail->total_meter }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @else
                                                <p class="text-muted mb-0">No fabric details available.</p>
                                            @endif
                                        </div>


                                    </td>
                                    <td><a href="{{route('admin.product_order.issueFabric',['id' => $product->id])}}">Issue</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No products found for this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
