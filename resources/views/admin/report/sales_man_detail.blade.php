@extends('admin.layouts.app')

@section('content')
<style>
    .report-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:15px;
    }
    .report-header h3{ font-weight:600;margin:0; }

    .report-card{
        border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,.08);
    }

    .table-report thead th{
        background:#343a40;
        color:#fff;
        font-weight:600;
        vertical-align:middle;
        white-space:nowrap;
    }
    
    .status-badge {
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 85%;
    }
</style>

<div class="content-wrapper">

{{-- ================= HEADER ================= --}}

<section class="content-header">
    <div class="container-fluid">
        <div class="report-header">
            <div>
                <a href="{{ route('admin.reports.salesManReport') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <div class="report-meta">Sales Man: <span class="font-weight-bold text-dark">{{ $salesMan->name }}</span></div>
            </div>
            <div>
                <h3>Sales Man Detail Report</h3>
            </div>
            <div class="report-meta text-right">
                <div>Date : {{ now()->format('d M Y h:i A') }}</div>
                <div class="small">Phone: {{ $salesMan->phone }}</div>
            </div>
        </div>
    </div>
</section>

<section class="content">
<div class="container-fluid">

{{-- ================= FILTERS ================= --}}
<div class="card mb-3">
<div class="card-body">
<form method="GET" action="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}" id="filterForm">
<div class="row g-2 align-items-end">

    <div class="col-md-2">
        <label>Date From</label>
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="document.getElementById('filterForm').submit()">
    </div>

    <div class="col-md-2">
        <label>Date To</label>
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="document.getElementById('filterForm').submit()">
    </div>
    
    <div class="col-md-2">
        <label>Order No</label>
        <input type="text" name="order_no" class="form-control" value="{{ request('order_no') }}" placeholder="Order #" onchange="document.getElementById('filterForm').submit()">
    </div>
    
    <div class="col-md-2">
        <label>Type</label>
        <select name="order_type" class="form-control" onchange="document.getElementById('filterForm').submit()">
            <option value="">All (Order & Dispatch)</option>
            <option value="order" {{ request('order_type') == 'order' ? 'selected' : '' }}>Only Orders</option>
            <option value="dispatch" {{ request('order_type') == 'dispatch' ? 'selected' : '' }}>Only Dispatches</option>
        </select>
    </div>

    <div class="col-md-2">
        <label>Party Name</label>
        <input type="text" name="party_name" class="form-control" value="{{ request('party_name') }}" placeholder="Name" onchange="document.getElementById('filterForm').submit()">
    </div>

    <div class="col-md-2">
        <a href="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
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

<table class="table table-bordered table-report table-hover">
<thead>
<tr>
    <th>#</th>
    <th>Date</th>
    <th>Reference No</th>
    <th>Type</th>
    <th>Customer/Vendor</th>
    <th class="text-center">ITEMS COUNT</th>
    <th class="text-right">AMOUNT</th>
    <th class="text-center">STATUS</th>
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
    <tr class="bg-light" style="cursor: pointer;" onclick="window.open('{{ route('admin.agent-orders.show', $order->id) }}', '_blank')">
        <td>{{ $sr++ }}</td>
        <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
        <td>
            <a href="{{ route('admin.agent-orders.show', $order->id) }}" target="_blank" class="font-weight-bold" onclick="event.stopPropagation()">
                #{{ $order->id }}
            </a>
            <br>
            <small class="text-muted text-capitalize">{{ $order->order_type }}</small>
        </td>
        <td><span class="badge badge-primary">Order</span></td>
        <td>
            {{ $order->shop_name }}
            <br>
            <small class="text-muted text-capitalize">{{ $order->party_type }}</small>
        </td>
        <td class="text-center">{{ $itemCount }}</td>
        <td class="text-right font-weight-bold">
            ₹ {{ number_format($order->grand_total, 2) }}
            @if($dispatchedAmount > 0)
                <br><small class="text-success font-weight-normal">Disp: ₹ {{ number_format($dispatchedAmount, 2) }}</small>
            @endif
        </td>
        <td class="text-center">
            @if($order->status == 'dispatched')
                <span class="badge badge-success status-badge">Dispatched</span>
            @elseif($order->status == 'partially_dispatched')
                <span class="badge badge-warning status-badge">Partial Dispatch</span>
            @elseif($order->status == 'pending')
                <span class="badge badge-secondary status-badge">Pending</span>
            @else
                <span class="badge badge-info status-badge text-capitalize">{{ str_replace('_', ' ', $order->status) }}</span>
            @endif
        </td>
    </tr>
    @endif
    
    @if($showDispatch)
    @foreach($order->dispatches as $dispatch)
        <tr style="cursor: pointer;" onclick="window.open('{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}', '_blank')">
            <td>{{ $sr++ }}</td>
            <td>{{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') }}</td>
            <td>
                <a href="{{ route('admin.agent-orders.dispatches.show', $dispatch->id) }}" target="_blank" class="font-weight-bold" onclick="event.stopPropagation()">
                    LR: {{ $dispatch->lr_no ?: 'N/A' }}
                </a>
                <br>
                <small class="text-muted text-capitalize">Dispatch #{{ $dispatch->id }}</small>
            </td>
            <td><span class="badge badge-success">Dispatch</span></td>
            <td>
                {{ $dispatch->party ? $dispatch->party->name : 'N/A' }}
                <br>
                <small class="text-muted">{{ $dispatch->transport_name }}</small>
            </td>
            <td class="text-center">
                @php
                    $dispatchItemCount = \DB::table('agent_order_items')->where('agent_order_dispatch_id', $dispatch->id)->count() 
                                       + \DB::table('agent_order_fabric_items')->where('agent_order_dispatch_id', $dispatch->id)->count();
                @endphp
                {{ $dispatchItemCount }}
            </td>
            <td class="text-right text-success font-weight-bold">₹ {{ number_format($dispatch->grand_total, 2) }}</td>
            <td class="text-center">
                <span class="badge badge-success status-badge">Dispatched</span>
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
</section>
</div>
@endsection
