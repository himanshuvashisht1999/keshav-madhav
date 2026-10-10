@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-truck"></i> Fabric shipment history
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-layer-group text-muted"></i> Fabric: <strong>{{ $fabric->name }}</strong></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-barcode text-muted"></i> SKU: <code>{{ $fabric->sku ?? '-' }}</code></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Date: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <span class="erp-stat-pill mr-2">
                Total rolls: <strong>{{ $shipments->count() }}</strong>
            </span>
            <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to fabrics
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise_shipments') }}" class="mb-0">
            <input type="hidden" name="fabric_id" value="{{ $fabric->id }}">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Supplier</label>
                    <select name="vendor_id" class="form-control form-control-sm erp-filter-select">
                        <option value="">All suppliers</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ ($filters['vendor_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-2 col-sm-6 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.purchase_order_fabric_wise_shipments', ['fabric_id' => $fabric->id]) }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Received date</th>
                        <th>Roll number</th>
                        <th>Shipment no</th>
                        <th>PO number</th>
                        <th>Supplier</th>
                        <th class="text-right">Meters</th>
                        <th class="text-right">Rate / meter</th>
                        <th class="text-right">Total amount</th>
                        <th>Warehouse</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalMeters = 0;
                        $totalVal = 0;
                    @endphp
                    @forelse($shipments as $item)
                        @php
                            $totalMeters += $item->meter;
                            $itemTotal = $item->meter * $item->price_per_meter;
                            $totalVal += $itemTotal;
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                            <td><span class="badge badge-light border">{{ $item->roll_number ?? '-' }}</span></td>
                            <td><code>{{ $item->shipment_number ?? '-' }}</code></td>
                            <td><code>{{ $item->purchase_order->sku ?? '-' }}</code></td>
                            <td><strong>{{ $item->fabric_receipt->vendor->name ?? ($item->purchase_order->vendor->name ?? '-') }}</strong></td>
                            <td class="text-right font-weight-bold">{{ number_format($item->meter, 2) }}</td>
                            <td class="text-right">₹ {{ number_format($item->price_per_meter, 2) }}</td>
                            <td class="text-right font-weight-bold text-success">₹ {{ number_format($itemTotal, 2) }}</td>
                            <td>{{ $item->master_fabric_warehouse->cutting_master_name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                No shipment records found for this fabric.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($shipments->count() > 0)
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($totalMeters, 2) }}</th>
                            <th></th>
                            <th class="text-right text-success font-weight-bold">₹ {{ number_format($totalVal, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
