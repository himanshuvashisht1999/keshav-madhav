@extends('admin.layouts.app')

@section('content')
    <style>
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .report-header h3 {
            font-weight: 600;
            margin: 0;
        }

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .table-report thead th {
            background: #343a40;
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
        }

        .summary-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid #17a2b8;
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                        <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div>
                        <h3>Complete Shipment History</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                
                <div class="summary-box">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Fabric: <strong>{{ $fabric->name }}</strong></h5>
                            <p class="mb-0 text-muted">SKU: {{ $fabric->sku ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 class="text-info">Total Received Rolls: <strong>{{ $shipments->count() }}</strong></h5>
                        </div>
                    </div>
                </div>

                {{-- ================= FILTERS ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise_shipments') }}">
                            <input type="hidden" name="fabric_id" value="{{ $fabric->id }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Supplier</label>
                                    <select name="vendor_id" class="form-control">
                                        <option value="">All Suppliers</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ ($filters['vendor_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ $filters['start_date'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ $filters['end_date'] ?? '' }}">
                                </div>
                                <div class="col-md-3 d-flex gap-1">
                                    <button class="btn btn-primary flex-fill">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('admin.report.purchase_order_fabric_wise_shipments', ['fabric_id' => $fabric->id]) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card report-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-report">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Received Date</th>
                                        <th>Roll Number</th>
                                        <th>Shipment No</th>
                                        <th>PO Number</th>
                                        <th>Vendor / Supplier</th>
                                        <th class="text-end">Meters</th>
                                        <th class="text-end">Rate (Per Meter)</th>
                                        <th class="text-end">Total Amount</th>
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
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                                            <td class="fw-bold">{{ $item->roll_number ?? '-' }}</td>
                                            <td>{{ $item->shipment_number ?? '-' }}</td>
                                            <td class="fw-bold text-primary">{{ $item->purchase_order->sku ?? '-' }}</td>
                                            <td>{{ $item->fabric_receipt->vendor->name ?? ($item->purchase_order->vendor->name ?? '-') }}</td>
                                            <td class="text-end">{{ number_format($item->meter, 2) }}</td>
                                            <td class="text-end">₹ {{ number_format($item->price_per_meter, 2) }}</td>
                                            <td class="text-end fw-bold">₹ {{ number_format($itemTotal, 2) }}</td>
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
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="6" class="text-end">TOTAL</th>
                                            <th class="text-end">{{ number_format($totalMeters, 2) }}</th>
                                            <th>-</th>
                                            <th class="text-end text-success">₹ {{ number_format($totalVal, 2) }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
