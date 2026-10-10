@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Sales Man Detail: {{ $salesMan->name }}</h1>
            <div class="erp-report-meta">
                <span>Phone: {{ $salesMan->phone }}</span>
                <span class="meta-divider">|</span>
                <span>Date : {{ now()->format('d M Y h:i A') }}</span>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.reports.salesManReport') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Date From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="document.getElementById('filterForm').submit()">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Date To</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="document.getElementById('filterForm').submit()">
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Order Number</label>
                        <input type="text" name="order_no" class="form-control" value="{{ request('order_no') }}" placeholder="Order #" onchange="document.getElementById('filterForm').submit()">
                    </div>
                    
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Type</label>
                        <select name="order_type" class="form-control select2" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All (Order & Dispatch)</option>
                            <option value="order" {{ request('order_type') == 'order' ? 'selected' : '' }}>Only Orders</option>
                            <option value="dispatch" {{ request('order_type') == 'dispatch' ? 'selected' : '' }}>Only Dispatches</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Party Name</label>
                        <input type="text" name="party_name" class="form-control" value="{{ request('party_name') }}" placeholder="Search Party" onchange="document.getElementById('filterForm').submit()">
                    </div>

                    <div class="col-md-2 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}" class="btn-erp btn-erp-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Date</th>
                            <th>Reference Number</th>
                            <th>Type</th>
                            <th>Customer / Vendor</th>
                            <th class="text-center">Items Count</th>
                            <th class="text-right">Amount</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $sr = 1; 
                            $showOrder = request('order_type') != 'dispatch';
                            $showDispatch = request('order_type') != 'order';
                        @endphp
                        @forelse($orders as $order)
                            @php
                                $dispatchedAmount = $order->dispatches->sum('grand_total');
                                $itemCount = $order->items->count() + $order->fabricItems->count();
                                $isFullyDispatched = $dispatchedAmount >= $order->grand_total;
                            @endphp
                            
                            @if($showOrder)
                                <tr>
                                    <td class="text-center text-muted font-weight-bold">{{ $sr++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.agent-orders.show', $order->id) }}" target="_blank" class="font-weight-bold">
                                            #{{ $order->id }}
                                        </a>
                                        <div class="text-muted small text-capitalize">{{ $order->order_type }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border font-weight-bold">Order</span>
                                    </td>
                                    <td>
                                        <strong>{{ $order->shop_name }}</strong>
                                        <div class="text-muted small text-capitalize">{{ $order->party_type }}</div>
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $itemCount }}</td>
                                    <td class="text-right font-weight-bold">
                                        ₹ {{ number_format($order->grand_total, 2) }}
                                        @if($dispatchedAmount > 0)
                                            <div class="text-success small font-weight-normal">Disp: ₹ {{ number_format($dispatchedAmount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($order->status == 'dispatched')
                                            <span class="erp-badge-status erp-badge-status-closed">Dispatched</span>
                                        @elseif($order->status == 'partially_dispatched')
                                            <span class="erp-badge-status erp-badge-status-open">Partial Dispatch</span>
                                        @elseif($order->status == 'pending')
                                            <span class="erp-badge-status erp-badge-status-neutral">Pending</span>
                                        @else
                                            <span class="erp-badge-status erp-badge-status-info text-capitalize">{{ str_replace('_', ' ', $order->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            
                            @if($showDispatch)
                                @foreach($order->dispatches as $dispatch)
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold">{{ $sr++ }}</td>
                                        <td>{{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}" target="_blank" class="font-weight-bold">
                                                LR: {{ $dispatch->lr_no ?: 'N/A' }}
                                            </a>
                                            <div class="text-muted small text-capitalize">Dispatch #{{ $dispatch->id }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border text-success font-weight-bold">Dispatch</span>
                                        </td>
                                        <td>
                                            <strong>{{ $dispatch->party ? $dispatch->party->name : 'N/A' }}</strong>
                                            <div class="text-muted small">{{ $dispatch->transport_name }}</div>
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            @php
                                                $dispatchItemCount = \DB::table('agent_order_items')->where('agent_order_dispatch_id', $dispatch->id)->count() 
                                                                   + \DB::table('agent_order_fabric_items')->where('agent_order_dispatch_id', $dispatch->id)->count();
                                            @endphp
                                            {{ $dispatchItemCount }}
                                        </td>
                                        <td class="text-right text-success font-weight-bold">₹ {{ number_format($dispatch->grand_total, 2) }}</td>
                                        <td class="text-center">
                                            <span class="erp-badge-status erp-badge-status-closed">Dispatched</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No orders found for this sales man in the selected date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
