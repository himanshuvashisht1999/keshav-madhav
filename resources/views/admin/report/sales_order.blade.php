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

    .order-cell{
        background:#f8f9fa;
        font-weight:600;
        vertical-align:middle !important;
    }

    .badge-stage{
        background:#e7f1ff;
        color:#0d6efd;
        font-weight:500;
    }

    .delay-link{
        cursor:pointer;
        font-weight:600;
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
                <h3>Sales Order</h3>
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
<form method="GET" action="{{ route('admin.report.sales-order') }}">
<div class="row g-2 align-items-end">

    <div class="col-md-2">
        <label>Order No</label>
        <input type="text" name="order_no" class="form-control"
               value="{{ request('order_no') }}">
    </div>

    <div class="col-md-2">
        <label>Lot No</label>
        <input type="text" name="lot_no" class="form-control"
               value="{{ request('lot_no') }}">
    </div>

    <div class="col-md-2">
        <label>Delay</label>
        <select name="delay_status" class="form-control">
            <option value="">All</option>
            <option value="Yes" {{ request('delay_status')=='Yes'?'selected':'' }}>Delayed</option>
            <option value="No" {{ request('delay_status')=='No'?'selected':'' }}>On Time</option>
        </select>
    </div>

    <div class="col-md-2">
        <label>Date From</label>
        <input type="date" name="date_from" class="form-control"
               value="{{ request('date_from') }}">
    </div>

    <div class="col-md-2">
        <label>Date To</label>
        <input type="date" name="date_to" class="form-control"
               value="{{ request('date_to') }}">
    </div>

    <div class="col-md-1">
        <button class="btn btn-primary">
            <!-- <i class="fas fa-filter"></i> -->
            Filter
        </button>
    </div>
    <div class="col-md-1">
        <a href="{{ route('admin.report.sales-order.export', request()->query()) }}"
        class="btn btn-secondary">
            <!-- <i class="fas fa-file-excel"></i> -->
            Export
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
    <th>Customer</th>
    <th>Order No</th>
    <th>Total Pcs</th>
    <th>Action</th>
    <th>Lot No</th>
    <th>Pcs / Lot</th>
    <th>Current Stage</th>
    <th>Delay</th>
</tr>
</thead>

<tbody>
@php $sr = 1; @endphp

@forelse($data as $orderNo => $lots)

    @php
        $rowspan = count($lots);
        $order = $lots->first();
    @endphp

    @foreach($lots as $index => $lot)
        <tr>

            {{-- ORDER LEVEL --}}
            @if($index === 0)
                <td rowspan="{{ $rowspan }}" class="order-cell">{{ $sr }}</td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ \Carbon\Carbon::parse($order['order_date'])->format('d M Y') }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ $order['customer'] }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ $order['order_no'] }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell text-center">
                    {{ $order['total_pcs_in_order'] }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell text-center">
                    <a href="{{ route('admin.report.sales-order.detail', $order['order_id']) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Details
                    </a>
                </td>
            @endif

            {{-- LOT LEVEL --}}
            <td>{{ $lot['lot_no'] }}</td>
            <td class="text-center">{{ $lot['pieces_in_lot'] }}</td>
            <td>
                <span class="badge badge-stage">
                    {{ $lot['stage_name'] }}
                </span>
            </td>
            <td>
                <span class="delay-link {{ $lot['isDelayed']=='Yes' ? 'text-danger' : 'text-success' }}"
                      data-lot="{{ $lot['lot_no'] }}"
                      data-allowed="{{ $lot['allowed_till_datetime'] }}"
                      data-current="{{ $lot['current_datetime'] }}"
                      data-status="{{ $lot['isDelayed'] }}">
                    {{ $lot['isDelayed'] }}
                </span>
            </td>
        </tr>
    @endforeach

    @php $sr++; @endphp

@empty
<tr>
    <td colspan="9" class="text-center text-muted">
        No sales orders found
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

{{-- ================= DELAY MODAL ================= --}}
<div class="modal fade" id="delayModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Lot Delay Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<table class="table table-sm table-bordered mb-0">
<tr>
    <th>Lot Number</th>
    <td id="modalLot"></td>
</tr>
<tr>
    <th>Allowed Till</th>
    <td id="modalAllowed"></td>
</tr>
<tr>
    <th>Current Time</th>
    <td id="modalCurrent"></td>
</tr>
<tr>
    <th>Status</th>
    <td id="modalStatus"></td>
</tr>
</table>
</div>

</div>
</div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    function formatDateTime(dateTimeStr) {
        if (!dateTimeStr) return '-';
        const date = new Date(dateTimeStr.replace(' ', 'T'));
        return date.toLocaleString('en-IN', {
            day:'2-digit', month:'short', year:'numeric',
            hour:'2-digit', minute:'2-digit', hour12:true
        });
    }

    document.querySelectorAll('.delay-link').forEach(el => {
        el.addEventListener('click', function () {

            document.getElementById('modalLot').innerText = this.dataset.lot;
            document.getElementById('modalAllowed').innerText =
                formatDateTime(this.dataset.allowed);
            document.getElementById('modalCurrent').innerText =
                formatDateTime(this.dataset.current);

            document.getElementById('modalStatus').innerHTML =
                this.dataset.status === 'Yes'
                    ? '<span class="badge bg-danger">Delayed</span>'
                    : '<span class="badge bg-success">On Time</span>';

            new bootstrap.Modal(
                document.getElementById('delayModal')
            ).show();
        });
    });

});
</script>

@endsection
