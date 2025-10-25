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
                    <strong><i class="fas fa-info-circle mr-1"></i>Production Order Information</strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped mb-0">
                        <tr>
                            <th width="20%">Order SKU</th>
                            <td>{{ $data->sku }}</td>
                            <th width="20%">Customer</th>
                            <td>{{ $data->customer->name}}</td>
                            
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d M Y, h:i A') }}</td>
                            <th width="20%">Expected Delivery Date</th>
                            <td>{{ \Carbon\Carbon::parse($data->expected_delivery_date)->format('d M Y') }}</td>
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
                                <th width="5%">#</th>
                                <th width="20%">Product SKU</th>
                                <th width="10%">Quantity</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->products as $index => $product)
                                <tr>
                                    <td class="align-top">{{ $index + 1 }}</td>
                                    <td class="align-top">{{ $product->product_sku }}</td>
                                    <td class="align-top">{{ $product->quantity }}</td>
                                    <td>

                                        <!-- Fabric Details -->
                                        <div class="mb-3">
                                            <h6 class="text-primary"><i class="fas fa-scroll mr-1"></i> Bill of Materials</h6>
                                            @foreach($product->product_details as $detail)
                                                <div class="border rounded p-2 mb-2 bg-light">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>Fabric SKU:</strong> {{ $detail->fabric_sku }} <br>
                                                            <strong>Meter per Product:</strong> {{ $detail->meter }} <br>
                                                            <strong>Total Required Meter:</strong> {{ $detail->total_meter }}
                                                        </div>
                                                    </div>

                                                    <!-- Fabric Stock Usage -->
                                                    @if($detail->product_detail_stocks->count() > 0)
                                                        <table class="table table-sm table-bordered mt-2 mb-0 bg-white">
                                                            <thead>
                                                                <tr class="bg-light">
                                                                    <th>Stock</th>
                                                                    <th>Used Meter</th>
                                                                    <th>QR Code</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($detail->product_detail_stocks as $stock)
                                                                    <tr>
                                                                        <td>{{ $stock->stock->unique_number }}</td>
                                                                        <td>{{ $stock->meter }}</td>
                                                                        <td><a href="{{ $stock->stock->qrcode }}" target="_blank" class="text-primary">View</a></td>
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
                                            <div class="mt-4">
                                                <h6 class="text-primary mb-3"><i class="fas fa-tasks mr-1"></i> Production Stages</h6>
                                                <table class="table table-bordered table-sm mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Stage</th>
                                                            <th>Total Qty</th>
                                                            <th>Completed Qty</th>
                                                            <th>Pending Qty</th>
                                                            <th>Status</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($product->order_stages as $key => $stage)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td>{{ $stage->stage->name ?? 'N/A' }}</td>
                                                                <td>{{ $stage->total_qty }}</td>
                                                                <td>{{ $stage->completed_qty }}</td>
                                                                <td>{{ $stage->pending_qty }}</td>
                                                                <td>
                                                                    @if($stage->status == 0)
                                                                        <span class="badge badge-secondary">Pending</span>
                                                                    @elseif($stage->status == 1)
                                                                        <span class="badge badge-info">In Progress</span>
                                                                    @elseif($stage->status == 2)
                                                                        <span class="badge badge-success">Completed</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <!-- Action Button (for Next Stage Transfer) -->
                                                                    @if($stage->pending_qty > 0 && $stage->status != 2)
                                                                        <button class="btn btn-sm btn-outline-primary" data-toggle="modal"
                                                                            data-target="#stageModal{{ $stage->id }}">
                                                                            <i class="fas fa-paper-plane"></i> Transfer
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
                                                                            <div class="modal-header bg-primary text-white">
                                                                                <h5 class="modal-title">Transfer to Next Stage</h5>
                                                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <input type="hidden" name="order_product_id" value="{{ $product->id }}">
                                                                                <input type="hidden" name="from_stage_id" value="{{ $stage->stage_id }}">

                                                                                <div class="form-group">
                                                                                    <label>Quantity to Transfer</label>
                                                                                    <input type="number" name="quantity" class="form-control" max="{{ $stage->pending_qty }}" required>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                    <label>Remarks (optional)</label>
                                                                                    <textarea name="remarks" class="form-control" rows="2"></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                                    <i class="fas fa-check-circle"></i> Confirm Transfer
                                                                                </button>
                                                                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
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

                                                <div class="mt-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small class="text-muted">Production Progress</small>
                                                        <small><strong>{{ $progress }}%</strong></small>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
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
