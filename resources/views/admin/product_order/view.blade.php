@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="erp-header-title mb-0">
                <i class="fas fa-file-invoice text-warning mr-1"></i> Production Order Details: <span class="text-primary">{{ $data->sku }}</span>
            </h5>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.index') }}" class="btn btn-xs btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Order Info Card -->
    <div class="erp-card mb-2" style="border-top: 3px solid #f59e0b;">
        <div class="erp-card-header py-2 px-3">
            <span class="erp-card-title mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-info-circle text-muted mr-1"></i> Order Information
            </span>
        </div>
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Order SKU</small>
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $data->sku }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Customer</small>
                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $data->customer->name ?? '-' }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Created Date</small>
                    <span class="text-dark" style="font-size: 13px;">{{ getformatDateTime($data->created_at) }}</span>
                </div>
                <div class="col-md-3 col-6 mb-2">
                    <small class="text-muted d-block" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Expected Delivery Date</small>
                    <span class="text-dark font-weight-bold" style="font-size: 13px;">{{ getformatDate($data->expected_delivery_date) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ordered Products -->
    <div class="erp-card">
        <div class="erp-card-header py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="erp-card-title mb-0 font-weight-bold" style="font-size: 13px;">
                <i class="fas fa-box text-muted mr-1"></i> Ordered Products & Bill of Materials
            </span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-bordered erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th style="width: 180px;">PRODUCT SKU</th>
                        <th style="width: 100px;" class="text-center">QTY (PCS)</th>
                        <th>DETAILS & PRODUCTION STAGES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data->products as $index => $product)
                        <tr>
                            <td class="text-center font-weight-bold align-top" style="font-size: 12px;">{{ $index + 1 }}</td>
                            <td class="align-top">
                                <span class="font-weight-bold text-dark d-block" style="font-size: 13px;">{{ $product->product_sku }}</span>
                            </td>
                            <td class="text-center align-top font-weight-bold text-dark" style="font-size: 13px;">
                                {{ $product->quantity }}
                            </td>
                            <td>
                                <!-- Fabric Details -->
                                <div class="mb-3">
                                    <span class="text-secondary font-weight-bold d-block mb-1" style="font-size: 12px;">
                                        <i class="fas fa-scroll mr-1 text-warning"></i> Bill of Materials (BOM)
                                    </span>
                                    @foreach($product->product_details as $detail)
                                        <div class="border rounded p-2 mb-2 bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong class="text-dark" style="font-size: 12px;">Fabric SKU:</strong> <span class="text-primary">{{ $detail->fabric_sku }}</span> | 
                                                    <strong class="text-dark" style="font-size: 12px;">Per Product:</strong> {{ $detail->meter }} m | 
                                                    <strong class="text-dark" style="font-size: 12px;">Total Required:</strong> <span class="font-weight-bold">{{ $detail->total_meter }} m</span>
                                                </div>
                                            </div>

                                            <!-- Fabric Stock Usage -->
                                            @if($detail->product_detail_stocks->count() > 0)
                                                <table class="table table-sm table-bordered mt-2 mb-0 bg-white" style="font-size: 11px;">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Stock Roll #</th>
                                                            <th>Used Meter</th>
                                                            <th style="width: 80px;" class="text-center">QR Code</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($detail->product_detail_stocks as $stock)
                                                            <tr>
                                                                <td class="font-weight-bold">{{ $stock->stock->unique_number }}</td>
                                                                <td>{{ $stock->meter }} m</td>
                                                                <td class="text-center">
                                                                    <a href="{{ $stock->stock->qrcode }}" target="_blank" class="btn btn-xs btn-outline-info py-0">View</a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                @if($product->status == 2)
                                <!-- Product Stages -->
                                @if($product->order_stages->count() > 0)
                                    <div class="mt-3 pt-2 border-top">
                                        <span class="text-secondary font-weight-bold d-block mb-2" style="font-size: 12px;">
                                            <i class="fas fa-tasks mr-1 text-primary"></i> Production Stages
                                        </span>
                                        <table class="table table-bordered table-sm mb-2" style="font-size: 11px;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 30px;">#</th>
                                                    <th>Stage</th>
                                                    <th style="width: 80px;" class="text-center">Total</th>
                                                    <th style="width: 80px;" class="text-center">Completed</th>
                                                    <th style="width: 80px;" class="text-center">Pending</th>
                                                    <th style="width: 90px;" class="text-center">Status</th>
                                                    <th style="width: 90px;" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->order_stages as $key => $stage)
                                                    <tr>
                                                        <td class="text-center">{{ $key + 1 }}</td>
                                                        <td class="font-weight-bold">{{ $stage->stage->name ?? 'N/A' }}</td>
                                                        <td class="text-center">{{ $stage->total_qty }}</td>
                                                        <td class="text-center text-success font-weight-bold">{{ $stage->completed_qty }}</td>
                                                        <td class="text-center text-danger font-weight-bold">{{ $stage->pending_qty }}</td>
                                                        <td class="text-center">
                                                            @if($stage->status == 0)
                                                                <span class="badge badge-secondary">Pending</span>
                                                            @elseif($stage->status == 1)
                                                                <span class="badge badge-info">In Progress</span>
                                                            @elseif($stage->status == 2)
                                                                <span class="badge badge-success">Completed</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($stage->pending_qty > 0 && $stage->status != 2)
                                                                <button class="btn btn-xs btn-outline-primary" data-toggle="modal"
                                                                    data-target="#stageModal{{ $stage->id }}">
                                                                    <i class="fas fa-paper-plane mr-1"></i> Transfer
                                                                </button>
                                                            @else
                                                                <span class="text-muted">--</span>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <!-- Modal -->
                                                    <div class="modal fade" id="stageModal{{ $stage->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <form method="POST" action="{{ route('admin.product_order.transfer') }}">
                                                                    @csrf
                                                                    <div class="modal-header py-2 px-3 text-white" style="background:#1e293b;">
                                                                        <h6 class="modal-title font-weight-bold" style="font-size: 13px;">
                                                                            <i class="fas fa-paper-plane text-warning mr-1"></i> Transfer to Next Stage
                                                                        </h6>
                                                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                                    </div>
                                                                    <div class="modal-body p-3">
                                                                        <input type="hidden" name="order_product_id" value="{{ $product->id }}">
                                                                        <input type="hidden" name="from_stage_id" value="{{ $stage->stage_id }}">

                                                                        <div class="form-group mb-2">
                                                                            <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Quantity to Transfer <span class="text-danger">*</span></label>
                                                                            <input type="number" name="quantity" class="form-control form-control-sm erp-input" step="1" min="1" max="{{ $stage->pending_qty }}" required>
                                                                            <small class="text-muted">Max pending: {{ $stage->pending_qty }} pcs</small>
                                                                        </div>

                                                                        <div class="form-group mb-0">
                                                                            <label class="erp-filter-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase;">Remarks (optional)</label>
                                                                            <textarea name="remarks" class="form-control form-control-sm erp-input" rows="2"></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer py-2 px-3 bg-light">
                                                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-xs btn-erp-primary">
                                                                            <i class="fas fa-check-circle mr-1"></i> Confirm Transfer
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Stage Progress -->
                                        @php
                                            $totalStages = $product->order_stages->count();
                                            $completedStages = $product->order_stages->where('status', 2)->count();
                                            $progress = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;
                                        @endphp

                                        <div class="mt-2">
                                            <div class="d-flex justify-content-between mb-1" style="font-size: 11px;">
                                                <small class="text-muted font-weight-bold">Production Progress</small>
                                                <small class="font-weight-bold text-success">{{ $progress }}%</small>
                                            </div>
                                            <div class="progress" style="height: 6px; background: #e2e8f0;">
                                                <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4" style="font-size: 12px;">No products found for this order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
