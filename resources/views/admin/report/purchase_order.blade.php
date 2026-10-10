@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-file-invoice"></i> Purchase order report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-hashtag text-muted"></i> Report no: RJ 1</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Generated: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.purchase_order.export', request()->query()) }}" class="btn-erp btn-erp-outline" style="border-color: #22c55e; color: #166534;" title="Export report to Excel">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export Excel
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.purchase_order') }}" class="mb-0">
            <div class="form-row align-items-end">

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">PO number</label>
                    <input type="text" name="sku" class="form-control form-control-sm erp-filter-input" placeholder="PO/..."
                        value="{{ $filters['sku'] ?? '' }}">
                </div>

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

                <div class="col-md-2 col-sm-4 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm erp-filter-select">
                        <option value="">All statuses</option>
                        <option value="open" {{ ($filters['status'] ?? '') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ ($filters['status'] ?? '') == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="delayed" {{ ($filters['status'] ?? '') == 'delayed' ? 'selected' : '' }}>Delayed</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-4 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['start_date'] ?? '' }}">
                </div>

                <div class="col-md-2 col-sm-4 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['end_date'] ?? '' }}">
                </div>

                <div class="col-md-1 col-sm-12 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filters">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.purchase_order') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filters">
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
                        <th>Order date</th>
                        <th>Supplier</th>
                        <th>PO number</th>
                        <th class="text-right">Ordered qty</th>
                        <th class="text-right">Received qty</th>
                        <th class="text-right">Remaining qty</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Delayed</th>
                        <th class="text-center" style="width: 100px;">Action</th>
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

                        <tr class="{{ $isDelayed ? 'erp-row-delayed' : '' }}">
                            <td class="text-center text-muted">{{ $sr++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</td>
                            <td><strong>{{ $po->vendor?->name ?? '-' }}</strong></td>
                            <td><code>{{ $po->sku }}</code></td>
                            <td class="text-right font-weight-bold">{{ number_format($totalOrdered, 2) }}</td>
                            <td class="text-right font-weight-bold text-success">{{ number_format($totalReceived, 2) }}</td>
                            <td class="text-right font-weight-bold {{ $totalRemaining > 0 ? 'text-danger' : 'text-muted' }}">
                                {{ number_format($totalRemaining, 2) }}
                            </td>
                            <td class="text-center">
                                @if($totalRemaining == 0 || $po->is_closed)
                                    <span class="erp-badge-status erp-badge-status-closed">Closed</span>
                                @else
                                    <span class="erp-badge-status erp-badge-status-open">Open</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isDelayed)
                                    <span class="erp-badge-status erp-badge-status-delayed">Yes</span>
                                @else
                                    <span class="erp-badge-status erp-badge-status-neutral">No</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.report.purchase_order', array_merge(request()->query(), ['purchase_order_id' => $po->id])) }}"
                                    class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                @if(!$po->is_closed && $totalRemaining > 0)
                                <form action="{{ route('admin.report.purchase_order.close', $po->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to close this Purchase Order?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 ml-1" style="font-size: 11px;" title="Close PO">
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

        @if($data->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $data->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection