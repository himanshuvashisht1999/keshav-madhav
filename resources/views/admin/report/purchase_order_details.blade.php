@extends('admin.layouts.app')
@section('title', 'Purchase Order Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="m-0">
                        <a href="{{ route('admin.report.purchase_order') }}" class="text-dark text-decoration-none">Purchase Order Report</a>
                        <span class="text-muted"> / {{ $po->sku }}</span>
                    </h3>
                </div>
                <div>
                    <a href="{{ route('admin.report.purchase_order') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Main</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="m-0"><i class="fas fa-info-circle"></i> Basic Info</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th style="width: 40%;">Order No:</th>
                                    <td>{{ $po->sku }}</td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td>{{ $po->vendor?->name ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($po->items->sum('remaining_quantity') == 0)
                                            <span class="badge bg-success">Closed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Open</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="m-0"><i class="fas fa-shopping-cart"></i> Requested Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fabric Name (Ordered)</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Ordered Qty</th>
                                    <th class="text-end">Received Qty</th>
                                    <th class="text-end">Remaining Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $sr = 1; 
                                    $receivedTotals = $receipts->groupBy('purchase_order_item_id')->map(function($group) {
                                        return $group->sum('meter');
                                    });
                                @endphp
                                @foreach($po->items as $item)
                                    @php
                                        $actualReceived = $receivedTotals->get($item->id, 0);
                                        $actualRemaining = max(0, $item->meter - $actualReceived);
                                    @endphp
                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td class="fw-bold text-primary">{{ $item->fabric?->name ?? 'Unknown Fabric' }}</td>
                                        <td class="fw-bold text-primary">{{ $item->price }}/mtr</td>
                                        <td class="text-end text-primary fw-bold">{{ number_format($item->meter, 2) }}</td>
                                        <td class="text-end text-success fw-bold">{{ number_format($actualReceived, 2) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($actualRemaining, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="m-0"><i class="fas fa-truck"></i> Received Shipments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Receipt Date</th>
                                    <th>Shipment No</th>
                                    <th>Fabric Name</th>
                                    <th>Warehouse</th>
                                    <th>Roll No</th>
                                    <th class="text-end">Received Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = 1; @endphp
                                @forelse($receipts as $r)
                                    <tr>
                                        <td>{{ $sr++ }}</td>
                                        <td>{{ $r->created_at->format('d M Y') }}</td>
                                        <td>{{ $r->fabric_receipt?->shipment_number ?? $r->shipment_number ?? '-' }}</td>
                                        <td class="fw-bold">{{ $r->fabric?->name ?? 'Unknown Fabric' }}</td>
                                        <td>{{ $r->master_fabric_warehouse?->cutting_master_name ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $r->roll_number ?? '-' }}</span></td>
                                        <td class="text-end fw-bold text-success">{{ number_format($r->meter, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No shipments received yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
