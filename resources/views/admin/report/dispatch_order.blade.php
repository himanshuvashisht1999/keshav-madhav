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
        white-space:nowrap;
        vertical-align:middle;
    }

    .order-cell{
        background:#f8f9fa;
        font-weight:600;
        vertical-align:middle !important;
    }

    .delayed-row{
        background:#fff4f4;
    }

    .expand-btn{
        font-size:13px;
    }
</style>

<div class="content-wrapper">

{{-- ================= HEADER ================= --}}

<section class="content-header">
    <div class="container-fluid">
        <div class="report-header">
            <div>
                <div class="report-meta">Report No : RJ 3</div>
            </div>
            <div>
                <h3>Dispatch Order Report</h3>
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

                <div class="col-md-3">
                    <label class="form-label">PO Number</label>
                    <input type="text"
                           name="sku"
                           class="form-control"
                           placeholder="PO/..."
                           value="{{ $filters['sku'] ?? '' }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fabric SKU</label>
                    <select name="fabric_sku" class="form-control">
                        <option value="">All Fabrics</option>
                        @foreach($fabrics as $fabric)
                            <option value="{{ $fabric->sku }}"
                                {{ ($filters['fabric_sku'] ?? '') == $fabric->sku ? 'selected' : '' }}>
                                {{ $fabric->sku }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                </div>

                <div class="col-md-2">
                    <a href="{{ route('admin.report.purchase_order') }}"
                       class="btn btn-outline-secondary w-100">
                        Reset
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
    <th>Fabric</th>
    <th>Ordered</th>
    <th>Received</th>
    <th>Remaining</th>
    <th>Status</th>
    <th>Delayed</th>
    <th class="text-center">Action</th>
</tr>
</thead>

<tbody>
@php $sr = 1; @endphp

@forelse($data as $po)

    @php
        $rowspan = $po->items->count();
    @endphp

    @foreach($po->items as $index => $item)

        @php
            $receivedQty = $item->meter - $item->remaining_quantity;
            $isDelayed =
                $item->remaining_quantity > 0 &&
                \Carbon\Carbon::now()->gt(
                    \Carbon\Carbon::parse($po->delivery_date)
                );
        @endphp

        <tr class="{{ $isDelayed ? 'delayed-row' : '' }}">

            {{-- ORDER LEVEL (ONLY ON FIRST ROW) --}}
            @if($index === 0)
                <td rowspan="{{ $rowspan }}" class="order-cell">{{ $sr }}</td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ $po->vendor?->name ?? '-' }}
                </td>
                <td rowspan="{{ $rowspan }}" class="order-cell">
                    {{ $po->sku }}
                </td>
            @endif

            {{-- FABRIC LEVEL --}}
            <td>{{ $item->fabric_sku }}</td>

            <td class="text-end">{{ number_format($item->meter,2) }}</td>

            <td class="text-end text-success">
                {{ number_format($receivedQty,2) }}
            </td>

            <td class="text-end text-danger">
                {{ number_format($item->remaining_quantity,2) }}
            </td>

            <td>
                <span class="badge
                    {{ $item->remaining_quantity == 0 ? 'bg-success' : 'bg-warning' }}">
                    {{ $item->remaining_quantity == 0 ? 'Closed' : 'Open' }}
                </span>
            </td>

            <td>
                <span class="badge {{ $isDelayed ? 'bg-danger' : 'bg-success' }}">
                    {{ $isDelayed ? 'Yes' : 'No' }}
                </span>
            </td>

            <td class="text-center">
                <button class="btn btn-sm btn-outline-primary expand-btn"
                    onclick="openPoItemModal(
                        '{{ $item->id }}',
                        '{{ $po->sku }}',
                        '{{ $item->fabric_sku }}'
                    )">
                    View
                </button>
            </td>
        </tr>

    @endforeach

    @php $sr++; @endphp

@empty
<tr>
    <td colspan="11" class="text-center text-muted">
        No purchase orders found
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

{{-- ================= MODAL ================= --}}
<div class="modal fade" id="poItemModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Purchase Order Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>PO Number:</strong>
                        <span id="modalPoNo"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Fabric:</strong>
                        <span id="modalFabric"></span>
                    </div>
                </div>

                <div id="modalItemTable"></div>

            </div>
        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
function openPoItemModal(poItemId, poNo, fabricSku)
{
    document.getElementById('modalPoNo').innerText = poNo;
    document.getElementById('modalFabric').innerText = fabricSku;
    document.getElementById('modalItemTable').innerHTML =
        '<div class="text-center py-3">Loading...</div>';

    fetch(`{{ route('admin.report.purchase_order.item.details') }}?purchase_order_item_id=${poItemId}`)
        .then(res => res.json())
        .then(data => {

            let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Receipt Date</th>
                            <th>Warehouse</th>
                            <th>Roll No</th>
                            <th>Received Qty</th>
                            <th>Barcode</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(r => {
                html += `
                    <tr>
                        <td>${new Date(r.created_at).toLocaleDateString()}</td>
                        <td>${r.master_fabric_warehouse?.cutting_master_name ?? '-'}</td>
                        <td>${r.roll_number ?? '-'}</td>
                        <td><strong>${r.meter}</strong></td>
                        <td>
                            <img src="${r.barcode}"
                                 style="height:40px;cursor:pointer"
                                 onclick="window.open(this.src,'_blank')">
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            document.getElementById('modalItemTable').innerHTML = html;
        });

    new bootstrap.Modal(document.getElementById('poItemModal')).show();
}
</script>

@endsection
