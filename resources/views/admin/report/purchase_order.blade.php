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

        .order-cell {
            background: #f8f9fa;
            font-weight: 600;
            vertical-align: middle !important;
        }

        .delayed-row {
            background: #fff4f4;
        }

        .expand-btn {
            font-size: 13px;
        }
    </style>

    <div class="content-wrapper">

        {{-- ================= HEADER ================= --}}

        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                        <div class="report-meta">Report No : RJ 1</div>
                    </div>
                    <div>
                        <h3>Purchase Order Report</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                {{-- ================= FILTERS ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.report.purchase_order') }}">
                            <div class="row g-2 align-items-end">

                                <div class="col-md-2">
                                    <label class="form-label">PO Number</label>
                                    <input type="text" name="sku" class="form-control" placeholder="PO/..."
                                        value="{{ $filters['sku'] ?? '' }}">
                                </div>

                                <div class="col-md-2">
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
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="open" {{ ($filters['status'] ?? '') == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="closed" {{ ($filters['status'] ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                                        <option value="delayed" {{ ($filters['status'] ?? '') == 'delayed' ? 'selected' : '' }}>Delayed</option>
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

                                <div class="col-md-2 d-flex gap-1">
                                    <button class="btn btn-primary flex-fill">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('admin.report.purchase_order') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                    <a href="{{ route('admin.report.purchase_order.export', request()->query()) }}"
                                        class="btn btn-success">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="card report-card">
                    <div class="card-body">
                        <div class="table-responsive">

                            <table class="table table-bordered table-report">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order Date</th>
                                        <th>Supplier</th>
                                        <th>PO Number</th>
                                        <th class="text-end">Ordered Qty</th>
                                        <th class="text-end">Received Qty</th>
                                        <th class="text-end">Remaining Qty</th>
                                        <th>Status</th>
                                        <th>Delayed</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp

                                    @forelse($data as $po)

                                        @php
                                            $totalOrdered = $po->items->sum('meter');
                                            $totalReceived = $po->items->sum(function($item) {
                                                return $item->receipts->sum('meter');
                                            });
                                            $totalRemaining = max(0, $totalOrdered - $totalReceived);

                                            $isDelayed = $totalRemaining > 0 && \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($po->delivery_date));
                                        @endphp

                                        <tr class="{{ $isDelayed ? 'delayed-row' : '' }}">

                                            <td>{{ $sr++ }}</td>
                                            <td>{{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</td>
                                            <td class="fw-bold">{{ $po->vendor?->name ?? '-' }}</td>
                                            <td>{{ $po->sku }}</td>

                                            <td class="text-end text-primary">{{ number_format($totalOrdered, 2) }}</td>
                                            <td class="text-end text-success">{{ number_format($totalReceived, 2) }}</td>
                                            <td class="text-end text-danger">{{ number_format($totalRemaining, 2) }}</td>

                                            <td>
                                                <span
                                                    class="badge {{ ($totalRemaining == 0 || $po->is_closed) ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ ($totalRemaining == 0 || $po->is_closed) ? 'Closed' : 'Open' }}
                                                </span>
                                            </td>

                                            <td>
                                                <span class="badge {{ $isDelayed ? 'bg-danger' : 'bg-success' }}">
                                                    {{ $isDelayed ? 'Yes' : 'No' }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <a href="{{ route('admin.report.purchase_order', array_merge(request()->query(), ['purchase_order_id' => $po->id])) }}"
                                                    class="btn btn-sm btn-outline-primary expand-btn">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if(!$po->is_closed && $totalRemaining > 0)
                                                <form action="{{ route('admin.report.purchase_order.close', $po->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to close this Purchase Order?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger expand-btn ms-1" title="Close PO">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                No purchase orders found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $data->appends(request()->query())->links() }}
                        </div>

                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection