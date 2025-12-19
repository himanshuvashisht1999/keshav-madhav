@extends('admin.layouts.app')

@section('content')
<style>
    .report-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:15px;
    }
    .report-header h3{
        font-weight:600;
        margin:0;
    }
    .report-meta{
        font-size:14px;
        color:#6c757d;
    }
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
    .fabric-cell{
        background:#f8f9fa;
        font-weight:600;
        vertical-align:middle;
    }
    .expand-btn{
        font-size:13px;
    }
</style>

<div class="content-wrapper">

{{-- HEADER --}}
<section class="content-header">
<div class="container-fluid">
    <div class="report-header">
        <div>
            <h3>Fabric Stock Report</h3>
            <div class="report-meta">Remaining Stock – Warehouse wise</div>
        </div>
        <div class="report-meta">
            Date : {{ now()->format('d M Y h:i A') }}
        </div>
    </div>
</div>
</section>

<section class="content">
<div class="container-fluid">

{{-- FILTERS --}}
<div class="card mb-3">
<div class="card-body">
<form method="GET" action="{{ route('admin.report.stock') }}">
<div class="row g-2">

    <div class="col-md-3">
        <label>Warehouse</label>
        <select name="warehouse_id" class="form-control">
            <option value="">All Warehouses</option>
            @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}"
                    {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                    {{ $warehouse->cutting_master_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label>Fabric SKU</label>
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
        <label>Qty From</label>
        <input type="number" name="meter_from" class="form-control"
               value="{{ $filters['meter_from'] ?? '' }}">
    </div>

    <div class="col-md-2">
        <label>Qty To</label>
        <input type="number" name="meter_to" class="form-control"
               value="{{ $filters['meter_to'] ?? '' }}">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">
            <i class="fas fa-filter"></i> Apply
        </button>
    </div>

</div>
</form>
</div>
</div>

{{-- TABLE --}}
<div class="card report-card">
<div class="card-body">
<div class="table-responsive">

<table class="table table-bordered table-report">
<thead>
<tr>
    <th>#</th>
    <th>Fabric SKU</th>
    <th>Warehouse</th>
    <th class="text-end">Remaining Qty</th>
    <th class="text-center">Action</th>
</tr>
</thead>

<tbody>
@php $sr = 1; @endphp

@forelse($data as $fabricSku => $rows)

    @php $rowspan = $rows->count(); @endphp

    @foreach($rows as $index => $row)

    <tr>

        {{-- FABRIC (ONLY ONCE) --}}
        @if($index === 0)
            <td rowspan="{{ $rowspan }}" class="fabric-cell">{{ $sr }}</td>
            <td rowspan="{{ $rowspan }}" class="fabric-cell">
                {{ $fabricSku }}
            </td>
        @endif

        {{-- WAREHOUSE --}}
        <td>
            <i class="fas fa-warehouse text-primary me-2"></i>
            {{ $row->master_fabric_warehouse?->cutting_master_name }}
        </td>

        <td class="text-end fw-bold">
            {{ number_format($row->total_remaining,2) }}
        </td>

        <td class="text-center">
            <button class="btn btn-sm btn-outline-primary expand-btn"
                onclick="openStockModal(
                    '{{ $fabricSku }}',
                    '{{ $row->master_fabric_warehouse_id }}',
                    '{{ $row->master_fabric_warehouse?->cutting_master_name }}'
                )">
                View
            </button>
        </td>
    </tr>

    @endforeach

    @php $sr++; @endphp

@empty
<tr>
    <td colspan="5" class="text-center text-muted">
        No stock data found
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
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Stock Roll Details
                </h5>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
            </div>

            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Fabric:</strong>
                        <span id="modalFabricSku"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Warehouse:</strong>
                        <span id="modalWarehouse"></span>
                    </div>
                </div>

                <div id="modalStockTable"></div>

            </div>
        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
function openStockModal(fabricSku, warehouseId,cutting_master_name)
{
    document.getElementById('modalFabricSku').innerText = fabricSku;
    document.getElementById('modalWarehouse').innerText = cutting_master_name ?? '';
    document.getElementById('modalStockTable').innerHTML =
        '<div class="text-center py-3">Loading...</div>';

    fetch(`{{ route('admin.report.stock.roll.details') }}?fabric_sku=${fabricSku}&warehouse_id=${warehouseId}`)
        .then(res => res.json())
        .then(data => {

            let html = `
                <table class="table table-bordered table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Roll No</th>
                            <th>Remaining Qty</th>
                            <th>Barcode / QR No</th>
                            <th>Barcode</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.forEach(r => {
                html += `
                    <tr>
                        <td>${r.roll_number ?? '-'}</td>
                        <td><strong>${r.remaining_quantity}</strong></td>
                        <td>${r.qrcode_number ?? '-'}</td>
                        <td>
                            <img src="${r.barcode}"
                                 style="height:40px;cursor:pointer"
                                 onclick="window.open(this.src,'_blank')">
                        </td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            document.getElementById('modalStockTable').innerHTML = html;
        });

    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
</script>

@endsection
