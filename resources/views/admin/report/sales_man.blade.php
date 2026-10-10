@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Sales Man Report</h1>
        </div>
        <div class="erp-report-meta">
            Date : {{ now()->format('d M Y h:i A') }}
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.reports.salesManReport') }}" id="filterFormOverview">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Date From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" onchange="document.getElementById('filterFormOverview').submit()">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Date To</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" onchange="document.getElementById('filterFormOverview').submit()">
                    </div>

                    <div class="col-md-3 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.reports.salesManReport') }}" class="btn-erp btn-erp-secondary">
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
                            <th>Sales Man</th>
                            <th class="text-center">Total Orders</th>
                            <th class="text-right">Order Amount</th>
                            <th class="text-right">Dispatched Amount</th>
                            <th class="text-right">Balance Pending</th>
                            <th class="text-center" style="width: 120px;">Action</th>
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
                                <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $salesMan->name }}</strong>
                                    <div class="text-muted small">{{ $salesMan->phone }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">{{ $totalOrders }}</span>
                                </td>
                                <td class="text-right font-weight-bold">
                                    ₹ {{ number_format($orderAmount, 2) }}
                                </td>
                                <td class="text-right text-success font-weight-bold">
                                    ₹ {{ number_format($dispatchedAmount, 2) }}
                                </td>
                                <td class="text-right text-danger font-weight-bold">
                                    ₹ {{ number_format($balance, 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.reports.salesManReportDetail', $salesMan->id) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" class="btn-erp btn-erp-outline" title="View Detail">
                                        <i class="fas fa-eye mr-1"></i> View Detail
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
@endsection
