@extends('admin.layouts.app')
@section('title', 'Purchase Order Details')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-file-invoice"></i> Purchase order details
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-barcode text-muted"></i> PO: <code>{{ $po->sku }}</code></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-calendar-alt text-muted"></i> Date: {{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-truck text-muted"></i> Supplier: <strong>{{ $po->vendor?->name ?? 'Unknown' }}</strong></span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.purchase_order') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to report
            </a>
        </div>
    </div>

    {{-- Basic Info Card --}}
    <div class="row">
        <div class="col-md-4 mb-2">
            <div class="erp-card h-100 p-0">
                <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fas fa-info-circle text-success mr-1"></i> Order overview
                    </h6>
                    @if($po->items->sum('remaining_quantity') == 0)
                        <span class="erp-badge-status erp-badge-status-closed">Closed</span>
                    @else
                        <span class="erp-badge-status erp-badge-status-open">Open</span>
                    @endif
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                        <tr>
                            <th class="text-muted font-weight-normal py-1" style="width: 40%;">Order no:</th>
                            <td class="font-weight-bold text-dark py-1"><code>{{ $po->sku }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Date:</th>
                            <td class="font-weight-bold text-dark py-1">{{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Supplier:</th>
                            <td class="font-weight-bold text-dark py-1">{{ $po->vendor?->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Delivery date:</th>
                            <td class="font-weight-bold text-dark py-1">{{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('d M Y') : '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-2">
            <div class="erp-card h-100 p-0">
                <div class="px-3 py-2 border-bottom bg-light">
                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fas fa-shopping-cart text-success mr-1"></i> Requested items
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover erp-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Fabric name</th>
                                <th class="text-right">Price</th>
                                <th class="text-right">Ordered qty</th>
                                <th class="text-right">Received qty</th>
                                <th class="text-right">Remaining qty</th>
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
                                    <td class="text-center text-muted">{{ $sr++ }}</td>
                                    <td><strong>{{ $item->fabric?->name ?? 'Unknown fabric' }}</strong></td>
                                    <td class="text-right">₹ {{ number_format($item->price, 2) }}/m</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->meter, 2) }}</td>
                                    <td class="text-right font-weight-bold text-success">{{ number_format($actualReceived, 2) }}</td>
                                    <td class="text-right font-weight-bold {{ $actualRemaining > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($actualRemaining, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Received Shipments Card --}}
    <div class="erp-card p-0">
        <div class="px-3 py-2 border-bottom bg-light">
            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                <i class="fas fa-truck text-success mr-1"></i> Received shipments
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">#</th>
                        <th>Receipt date</th>
                        <th>Shipment no</th>
                        <th>Fabric name</th>
                        <th>Warehouse</th>
                        <th class="text-center">Roll no</th>
                        <th class="text-right">Received qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = 1; @endphp
                    @forelse($receipts as $r)
                        <tr>
                            <td class="text-center text-muted">{{ $sr++ }}</td>
                            <td>{{ $r->created_at->format('d M Y') }}</td>
                            <td><code>{{ $r->fabric_receipt?->shipment_number ?? $r->shipment_number ?? '-' }}</code></td>
                            <td><strong>{{ $r->fabric?->name ?? 'Unknown fabric' }}</strong></td>
                            <td>{{ $r->master_fabric_warehouse?->cutting_master_name ?? '-' }}</td>
                            <td class="text-center"><span class="badge badge-light border">{{ $r->roll_number ?? '-' }}</span></td>
                            <td class="text-right font-weight-bold text-success">{{ number_format($r->meter, 2) }} m</td>
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
@endsection
