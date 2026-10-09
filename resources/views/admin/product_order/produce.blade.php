@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-industry text-warning mr-1"></i> First Stage Production: <span class="text-primary">{{ $data->sku }}</span>
            </h5>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.indexOrder') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="erp-card mb-2" style="border-top: 3px solid #f59e0b;">
        <div class="erp-card-header py-2 px-3">
            <span class="erp-card-title mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-file-invoice text-muted mr-1"></i> Order Summary
            </span>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Order No</small>
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $data->sku }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Customer</small>
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $data->customer->name ?? '-' }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Ordered Date</small>
                    <span class="text-dark" style="font-size: 13px;">{{ getformatDateTime($data->created_at) }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Expected Delivery Date</small>
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">{{ getformatDate($data->expected_delivery_date) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCTS LIST -->
    <div class="erp-card">
        <div class="erp-card-header py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="erp-card-title mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-box text-muted mr-1"></i> Products to Produce
            </span>
        </div>
        <div class="card-body p-3">
            @forelse($data->products as $product)
            <div class="border rounded p-3 mb-3 bg-white" style="border: 1px solid #e2e8f0; border-radius: 6px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <span class="font-weight-bold text-dark d-block" style="font-size: 14px;">{{ $product->product_sku }}</span>
                        <small class="text-muted font-weight-bold">Quantity: <span class="text-dark">{{ $product->quantity }} Pcs</span></small>
                    </div>
                    <div>
                        @if($product->status == 1)
                            <span class="badge badge-primary px-2 py-1" style="font-size: 11px;">Pending</span>
                        @elseif($product->status == 3)
                            <span class="badge badge-success px-2 py-1" style="font-size: 11px;">Completed</span>
                        @else
                            <span class="badge badge-warning text-dark px-2 py-1" style="font-size: 11px;">In Progress</span>
                        @endif
                    </div>
                </div>

                @if($product->product_details->count() > 0)
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered erp-table text-center mb-0" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>FABRIC SKU</th>
                                <th>METER / PRODUCT</th>
                                <th>TOTAL METER REQUIRED</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->product_details as $detail)
                            <tr>
                                <td class="font-weight-bold text-dark">{{ $detail->fabric_sku }}</td>
                                <td>{{ $detail->meter }} m</td>
                                <td class="font-weight-bold text-primary">{{ $detail->total_meter }} m</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="text-right pt-2">
                    @if($product->status == 2 || $product->status == 3)
                        <a href="{{ route('admin.product_order.issueSlip', ['id' => $product->id]) }}"
                           class="btn btn-xs btn-outline-success">
                           <i class="fas fa-file-download mr-1"></i> Download Slip
                        </a>
                    @else
                        <a href="{{ route('admin.product_order.issueFabric', ['id' => $product->id]) }}"
                           class="btn btn-xs btn-erp-primary">
                           <i class="fas fa-arrow-right mr-1"></i> Issue Fabric
                        </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted p-4" style="font-size: 12px;">
                <i class="fas fa-box-open fa-2x mb-2 d-block text-muted"></i>
                No products found for this order.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
