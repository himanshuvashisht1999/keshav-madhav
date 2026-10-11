@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-industry text-primary"></i> First Stage Production &mdash; {{ $data->sku }}
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.warehouse.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

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
                            <span>{{ getformatDateTime($data->created_at) }}</span>
                        </div>
                        <div class="col-md-3 col-6 mb-2">
                            <small class="text-muted d-block">Expected Delivery Date</small>
                            <span>{{ getformatDate($data->expected_delivery_date) }}</span>
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
