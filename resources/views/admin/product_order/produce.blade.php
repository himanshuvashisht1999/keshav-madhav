@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header py-3 border-bottom bg-white shadow-sm mb-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-industry mr-1"></i> First Stage
            </h5>
            <a href="{{ route('admin.product_order.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </section>

    <!-- MAIN -->
    <section class="content">
        <div class="container-fluid">

            <!-- ORDER SUMMARY -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-secondary">
                        <i class="fas fa-file-invoice mr-1"></i> Order Summary
                    </h6>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Order No</small>
                            <span class="fw-bold">{{ $data->sku }}</span>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Customer</small>
                            <span class="fw-bold">{{ $data->customer->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Ordered Date</small>
                            <span>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Expected Delivery Date</small>
                            <span>{{ \Carbon\Carbon::parse($data->expected_delivery_date)->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS LIST -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-secondary">
                        <i class="fas fa-box mr-1"></i> Products
                    </h6>

                    @forelse($data->products as $product)
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">{{ $product->product_sku }}</h6>
                                <small class="text-muted">Quantity: {{ $product->quantity }}</small>
                            </div>
                            <div>
                                @if($product->status == 1)
                                    <span class="badge badge-primary px-3 py-1">Pending</span>
                                @elseif($product->status == 3)
                                    <span class="badge badge-success px-3 py-1">Completed</span>
                                @else
                                    <span class="badge badge-warning text-dark px-3 py-1">In Progress</span>
                                @endif
                            </div>
                        </div>

                        @if($product->product_details->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-2">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fabric SKU</th>
                                        <th>Meter / Product</th>
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
                        </div>
                        @endif

                        <div class="text-right">
                            @if($product->status == 2)
                                <a href="{{ route('admin.product_order.issueSlip', ['id' => $product->id]) }}"
                                   class="btn btn-sm btn-outline-success">
                                   <i class="fas fa-file-download mr-1"></i> Download Slip
                                </a>
                            @else
                                <a href="{{ route('admin.product_order.issueFabric', ['id' => $product->id]) }}"
                                   class="btn btn-sm btn-primary">
                                   <i class="fas fa-arrow-right mr-1"></i> Issue Fabric
                                </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-light text-center mb-0">
                        <i class="fas fa-box-open mr-1"></i> No product found for this order.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
