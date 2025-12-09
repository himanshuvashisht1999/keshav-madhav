{{-- resources/views/admin/reports/production_detail.blade.php --}}
@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- PAGE HEADER --}}
    <section class="content-header">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0">Production Detail</h3>
            <div>
                <a href="{{ route('admin.reports.productionDetail', ['id' => $orderMain->id]) }}"
                   class="btn btn-sm btn-secondary">
                    Refresh
                </a>
                {{-- 
                <a href="{{ route('admin.reports.productionExcel', ['id' => $orderMain->id]) }}"
                   class="btn btn-sm btn-success">
                    Download Excel
                </a> 
                --}}
            </div>
        </div>
        <small class="text-muted">Product wise → Stage wise overview</small>
    </section>

    <section class="content">

        {{-- ===== TOP SUMMARY CARDS ===== --}}
        @php
            $allProducts            = $orderMain->order_products ?? collect();
            $totalProducts          = $allProducts->count();
            $totalQty               = $allProducts->sum('quantity');
            $totalCompletedProducts = $allProducts->where('status', 3)->count();

            $package    = $orderMain->package ?? null; // hasOne
            $totalBoxes = $package ? $package->package_boxes->count() : 0;
        @endphp

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h4>{{ $orderMain->sku }}</h4>
                        <p>Main Order SKU</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h4>{{ $totalProducts }}</h4>
                        <p>Total Products</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h4>{{ $totalCompletedProducts }}/{{ $totalProducts }}</h4>
                        <p>Products Completed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h4>{{ $totalBoxes }}</h4>
                        <p>Boxes (Packed)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ORDER MAIN SUMMARY CARD ===== --}}
        <div class="card mb-3">
            <div class="card-header">
                <strong>Order Summary</strong>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Main Order SKU</label>
                        <div>{{ $orderMain->sku }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Customer</label>
                        <div>{{ optional($orderMain->customer)->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Expected Delivery</label>
                        <div>{{ $orderMain->expected_delivery_date }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Status</label>
                        @php
                            $statusText = [
                                0 => 'Inactive',
                                1 => 'In Progress',
                                2 => 'Completed',
                                3 => 'Closed',
                            ][$orderMain->status] ?? $orderMain->status;
                        @endphp
                        <div>
                            <span class="badge badge-primary">{{ $statusText }}</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Created At</label>
                        <div>{{ $orderMain->created_at }}</div>
                    </div>
                    <div class="col-md-3">
                        <label class="text-muted mb-0">Total Quantity</label>
                        <div>{{ $totalQty }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PRODUCT ACCORDION (DIRECTLY FROM order_products) ===== --}}
        <div id="productAccordionMain">

            @forelse($allProducts as $productIndex => $orderProduct)

                @php
                    $pStatusText = [
                        1 => 'In Progress',
                        2 => 'Fabric Issued',
                        3 => 'Production Completed',
                    ][$orderProduct->status] ?? $orderProduct->status;
                @endphp

                <div class="card mb-2">
                    <div class="card-header p-2" id="headingProduct{{ $orderProduct->id }}">
                        <h5 class="mb-0 d-flex justify-content-between align-items-center">
                            <button class="btn btn-link p-0" type="button"
                                    data-toggle="collapse"
                                    data-target="#collapseProduct{{ $orderProduct->id }}"
                                    aria-expanded="{{ $productIndex === 0 ? 'true' : 'false' }}"
                                    aria-controls="collapseProduct{{ $orderProduct->id }}">
                                <strong>{{ $productIndex + 1 }}.</strong>
                                &nbsp;SKU: {{ $orderProduct->product_sku }}
                                &nbsp;| Type: {{ $orderProduct->product_type_sku }}
                                &nbsp;| Qty: {{ $orderProduct->quantity }}
                            </button>
                            <span class="badge badge-info">{{ $pStatusText }}</span>
                        </h5>
                    </div>

                    <div id="collapseProduct{{ $orderProduct->id }}"
                         class="collapse {{ $productIndex === 0 ? 'show' : '' }}"
                         aria-labelledby="headingProduct{{ $orderProduct->id }}"
                         data-parent="#productAccordionMain">
                        <div class="card-body">

                            {{-- TABS: BOM / STAGES / TRANSACTIONS --}}
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab"
                                       href="#bom-{{ $orderProduct->id }}" role="tab">
                                        Fabric / BOM
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab"
                                       href="#stages-{{ $orderProduct->id }}" role="tab">
                                        Stages
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab"
                                       href="#txn-{{ $orderProduct->id }}" role="tab">
                                        Stage Transactions
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content pt-3">

                                {{-- TAB 1: FABRIC / BOM --}}
                                <div class="tab-pane fade show active"
                                     id="bom-{{ $orderProduct->id }}"
                                     role="tabpanel">
                                    @if($orderProduct->product_details->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
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
                                                                <ul class="mb-0 pl-3">
                                                                    @foreach($detail->product_detail_stocks as $stockRow)
                                                                        <li>
                                                                            Roll ID: {{ $stockRow->fabric_stock_id }},
                                                                            Issued: {{ $stockRow->meter }} m
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <span class="text-muted">
                                                                    No issue entry
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No BOM / fabric details.</p>
                                    @endif
                                </div>

                                {{-- TAB 2: STAGES --}}
                                <div class="tab-pane fade"
                                     id="stages-{{ $orderProduct->id }}"
                                     role="tabpanel">
                                    @if($orderProduct->order_stages->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
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
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No stages defined for this product.</p>
                                    @endif
                                </div>

                                {{-- TAB 3: STAGE TRANSACTIONS --}}
                                <div class="tab-pane fade"
                                     id="txn-{{ $orderProduct->id }}"
                                     role="tabpanel">
                                    @if($orderProduct->order_stage_trnsactions->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="thead-light">
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
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">No transactions recorded.</p>
                                    @endif
                                </div>

                            </div>{{-- /tab-content --}}
                        </div>{{-- /card-body --}}
                    </div>{{-- /collapse product --}}
                </div>{{-- /product card --}}

            @empty
                <div class="alert alert-info">
                    No products found for this order.
                </div>
            @endforelse

        </div>{{-- /productAccordionMain --}}

        {{-- ===== PACKAGING SECTION ===== --}}
        <div class="card mt-4">
            <div class="card-header">
                <strong>Packaging / Boxes</strong>
            </div>
            <div class="card-body">
                @php
                    $package = $orderMain->package ?? null;
                @endphp

                @if($package)
                    <div class="border rounded p-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Package ID: {{ $package->id }}</h6>
                            <small class="text-muted">
                                Boxes: {{ $package->package_boxes->count() }}
                            </small>
                        </div>

                        @if($package->package_boxes->count())
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
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
                                                    <ul class="mb-0 pl-3">
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
                            </div>
                        @else
                            <p class="text-muted mb-0">No boxes for this package.</p>
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-0">No packaging data available.</p>
                @endif
            </div>
        </div>

    </section>
</div>
@endsection
