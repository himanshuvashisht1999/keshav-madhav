{{-- resources/views/admin/reports/production_detail.blade.php --}}
@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    {{-- HEADER / BREADCRUMB --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Production Detail</h3>
        <a href="{{ route('admin.reports.productionDetail', ['id' => $orderMain->id]) }}" class="btn btn-sm btn-secondary">
            Refresh
        </a>
    </div>

    {{-- ORDER MAIN SUMMARY --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Order Summary (OrderMain)</strong>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <th style="width: 200px;">Main Order SKU</th>
                    <td>{{ $orderMain->sku }}</td>
                </tr>
                <tr>
                    <th>Customer</th>
                    <td>{{ optional($orderMain->customer)->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Expected Delivery Date</th>
                    <td>{{ $orderMain->expected_delivery_date }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @php
                            // adjust mapping as per your status codes
                            $statusText = [
                                0 => 'Inactive',
                                1 => 'In Progress',
                                2 => 'Completed',
                                3 => 'Closed',
                            ][$orderMain->status] ?? $orderMain->status;
                        @endphp
                        <span class="badge bg-primary">{{ $statusText }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Created At</th>
                    <td>{{ $orderMain->created_at }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ORDERS & PRODUCTS --}}
    @foreach($orderMain->orders as $order)
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Order: {{ $order->sku }}</strong>
                </div>
                <div>
                    <small>Expected Delivery: {{ $order->expected_delivery_date }}</small>
                </div>
            </div>

            <div class="card-body">
                {{-- Products for this order --}}
                @if($order->products->count())
                    @foreach($order->products as $orderProduct)
                        <div class="border rounded p-2 mb-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>Product SKU:</strong> {{ $orderProduct->product_sku }}<br>
                                    <strong>Type:</strong> {{ $orderProduct->product_type_sku }}<br>
                                    <strong>Ordered Qty:</strong> {{ $orderProduct->quantity }}
                                </div>
                                <div>
                                    @php
                                        $pStatusText = [
                                            1 => 'In Progress',
                                            2 => 'Fabric Issued',
                                            3 => 'Production Completed',
                                        ][$orderProduct->status] ?? $orderProduct->status;
                                    @endphp
                                    <span class="badge bg-info">{{ $pStatusText }}</span>
                                </div>
                            </div>

                            {{-- FABRIC / BOM SECTION --}}
                            <div class="mt-3">
                                <h6>Fabric / BOM</h6>
                                @if($orderProduct->product_details->count())
                                    <table class="table table-sm table-bordered mb-2">
                                        <thead>
                                        <tr>
                                            <th>Fabric SKU</th>
                                            <th>Meter per Pc</th>
                                            <th>Order Qty</th>
                                            <th>Total Meter</th>
                                            <th>Issued Rolls</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($orderProduct->product_details as $detail)
                                            <tr>
                                                <td>{{ $detail->fabric_sku }}</td>
                                                <td>{{ $detail->meter }}</td>
                                                <td>{{ $detail->order_quantity }}</td>
                                                <td>{{ $detail->total_meter }}</td>
                                                <td>
                                                    @if($detail->product_detail_stocks->count())
                                                        <ul class="mb-0">
                                                            @foreach($detail->product_detail_stocks as $stockRow)
                                                                <li>
                                                                    Roll ID: {{ $stockRow->fabric_stock_id }},
                                                                    Issued: {{ $stockRow->meter }} m
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">No issue entry</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">No BOM / fabric details.</p>
                                @endif
                            </div>

                            {{-- STAGES SECTION --}}
                            <div class="mt-3">
                                <h6>Stages</h6>
                                @if($orderProduct->order_stages->count())
                                    <table class="table table-sm table-bordered mb-2">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Stage</th>
                                            <th>Total Qty</th>
                                            <th>Completed</th>
                                            <th>Pending</th>
                                            <th>Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($orderProduct->order_stages as $index => $stageRow)
                                            @php
                                                $stageStatusText = [
                                                    0 => 'Pending',
                                                    1 => 'In Progress',
                                                    2 => 'Completed',
                                                ][$stageRow->status] ?? $stageRow->status;
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ optional($stageRow->stage)->name ?? 'N/A' }}</td>
                                                <td>{{ $stageRow->total_qty }}</td>
                                                <td>{{ $stageRow->completed_qty }}</td>
                                                <td>{{ $stageRow->pending_qty }}</td>
                                                <td>{{ $stageStatusText }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">No stages defined for this product.</p>
                                @endif
                            </div>

                            {{-- STAGE TRANSACTIONS SECTION --}}
                            <div class="mt-3">
                                <h6>Stage Transactions (Lots & Transfers)</h6>
                                @if($orderProduct->order_stage_trnsactions->count())
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                        <tr>
                                            <th>Transaction SKU</th>
                                            <th>From Stage</th>
                                            <th>To Stage</th>
                                            <th>Quantity</th>
                                            <th>Remaining Qty</th>
                                            <th>Lot No</th>
                                            <th>Remarks</th>
                                            <th>Date</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($orderProduct->order_stage_trnsactions as $txn)
                                            <tr>
                                                <td>{{ $txn->sku }}</td>
                                                <td>{{ optional($txn->from_stage)->name ?? $txn->from_stage_id }}</td>
                                                <td>{{ optional($txn->to_stage)->name ?? $txn->to_stage_id }}</td>
                                                <td>{{ $txn->quantity }}</td>
                                                <td>{{ $txn->remaining_quantity }}</td>
                                                <td>{{ $txn->lot_no }}</td>
                                                <td>{{ $txn->remarks }}</td>
                                                <td>{{ $txn->created_at }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted">No transactions recorded.</p>
                                @endif
                            </div>

                        </div> {{-- end product card --}}
                    @endforeach
                @else
                    <p class="text-muted">No products for this order.</p>
                @endif
            </div>
        </div>
    @endforeach

    {{-- PACKAGING SECTION --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Packaging / Boxes</strong>
        </div>
        <div class="card-body">
            @if($orderMain->packages && $orderMain->packages->count())
                @foreach($orderMain->packages as $package)
                    <div class="border rounded p-2 mb-3">
                        <h6>Package ID: {{ $package->id }}</h6>

                        @if($package->package_boxes->count())
                            <table class="table table-sm table-bordered">
                                <thead>
                                <tr>
                                    <th>Box ID</th>
                                    <th>Quantity in Box</th>
                                    <th>Warehouse</th>
                                    <th>Block</th>
                                    <th>Products in Box</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($package->package_boxes as $box)
                                    <tr>
                                        <td>{{ $box->id }}</td>
                                        <td>{{ $box->quantity }}</td>
                                        <td>{{ $box->warehouse?->name ?? '-' }}</td>
                                        <td>{{ $box->rack?->name ?? '-' }}</td>
                                        <td>
                                            @if($box->package_boxes_items->count())
                                                <ul class="mb-0">
                                                    @foreach($box->package_boxes_items as $item)
                                                        <li>{{ $item->product_sku }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">No items</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mb-0">No boxes for this package.</p>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="text-muted mb-0">No packaging data available.</p>
            @endif
        </div>
    </div>

</div>
@endsection
