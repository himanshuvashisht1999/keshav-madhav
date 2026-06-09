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
</style>

<div class="content-wrapper">

{{-- ================= HEADER ================= --}}

<section class="content-header">
    <div class="container-fluid">
        <div class="report-header">
            <div>
                <div class="report-meta">Report</div>
            </div>
            <div>
                <h3>Sales Man Report</h3>
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
<form method="GET" action="{{ route('admin.reports.salesManReport') }}" id="filterFormOverview">
<div class="row g-2 align-items-end">

    <div class="col-md-3">
        <label>Date From</label>
        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="document.getElementById('filterFormOverview').submit()">
    </div>

    <div class="col-md-3">
        <label>Date To</label>
        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="document.getElementById('filterFormOverview').submit()">
    </div>

    <div class="col-md-2">
        <a href="{{ route('admin.reports.salesManReport') }}" class="btn btn-secondary">
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
    <th>Sales Man</th>
    <th class="text-center">Total Orders</th>
    <th class="text-right">Order Amount</th>
    <th class="text-right">Dispatched Amount</th>
    <th class="text-right">Balance Pending</th>
    <th class="text-center">Action</th>
</tr>
</thead>

<tbody>
@forelse($salesMen as $index => $salesMan)
    @php
        $totalOrders = $salesMan->orders->count();
        $orderAmount = $salesMan->orders->sum('grand_total');
        $dispatchedAmount = 0;
        foreach($salesMan->orders as $order) {
            $dispatchedAmount += $order->dispatches->sum('grand_total');
        }
        $balance = $orderAmount - $dispatchedAmount;
    @endphp
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>
            <strong>{{ $salesMan->name }}</strong>
            <div class="small text-muted">{{ $salesMan->phone }}</div>
        </td>
        <td class="text-center">
            <span class="badge badge-info px-2 py-1">{{ $totalOrders }}</span>
        </td>
        <td class="text-right text-dark font-weight-bold">
            ₹ {{ number_format($orderAmount, 2) }}
        </td>
        <td class="text-right text-success font-weight-bold">
            ₹ {{ number_format($dispatchedAmount, 2) }}
        </td>
        <td class="text-right text-danger font-weight-bold">
            ₹ {{ number_format($balance, 2) }}
        </td>
        <td class="text-center">
            <a href="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-eye"></i> View Detail
            </a>
        </td>
    </tr>
@empty
<tr>
    <td colspan="7" class="text-center text-muted py-4">
        No sales men found
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
