@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6 text-right">
                    <h1 class="mb-0">First Stage</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.product_order.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="content mt-3">
        <div class="container-fluid">

            <!-- Order Info -->
            <div class="bg-white rounded p-3 mb-4 border">
                <h6 class="mb-3 text-primary font-weight-bold">
                    <i class="fas fa-info-circle mr-1"></i> Order Information
                </h6>
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th width="20%">Order SKU:</th>
                            <td>{{ $data->sku }}</td>
                            <th width="20%">Customer:</th>
                            <td>{{ $data->customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Created Date:</th>
                            <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, h:i A') }}</td>
                            <th>Expected Delivery:</th>
                            <td>{{ \Carbon\Carbon::parse($data->expected_delivery_date)->format('d M Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Products -->
            <div class="bg-white rounded p-3 border">
                <h6 class="mb-3 text-primary font-weight-bold">
                    <i class="fas fa-box mr-1"></i> Ordered Products
                </h6>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="thead-light">
                            <tr>
                                <th>Product SKU</th>
                                <th>Quantity</th>
                                <th>Fabric Details</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->products as $product)
                            <tr>
                                <td>{{ $product->product_sku }}</td>
                                <td class="text-center">{{ $product->quantity }}</td>
                                <td>
                                    @if($product->product_details->count() > 0)
                                        <table class="table table-sm mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Fabric SKU</th>
                                                    <th>Meter per Product</th>
                                                    <th>Total Meter</th>
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
                                        <span class="text-muted">No fabric details.</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->status == 2)
                                        <span class="badge badge-success">Issued</span><br>
                                        <a href="{{ route('admin.product_order.issueSlip', ['id' => $product->id]) }}" class="small">View Slip</a>
                                    @else
                                        <a href="{{ route('admin.product_order.issueFabric', ['id' => $product->id]) }}" class="btn btn-sm btn-outline-primary">Issue</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No products found for this order.</td>
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
