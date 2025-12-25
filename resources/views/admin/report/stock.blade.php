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

.fabric-cell {
    background: #f8f9fa;
    font-weight: 600;
    vertical-align: middle;
}

.expand-btn {
    font-size: 13px;
}
</style>

<div class="content-wrapper">

    {{-- HEADER --}}

    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>
                    <div class="report-meta">Report No : RJ 2</div>
                </div>
                <div>
                    <h3>Fabric Stock Report</h3>
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

                            <div class="col-md-2">
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

                            <div class="col-md-2">
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
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('admin.report.stock.export', request()->query()) }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-file-excel"></i> Export
                                </a>
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
                                        <button class="btn btn-sm btn-outline-primary expand-btn" onclick="openStockModal(
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

                <!-- SUMMARY -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Fabric :</strong>
                        <span id="modalFabricSku"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Warehouse :</strong>
                        <span id="modalWarehouse"></span>
                    </div>
                    <div class="col-md-4">
                        <strong>Total Remaining :</strong>
                        <span id="modalTotalQty" class="fw-bold"></span>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Roll No</th>
                                <th class="text-end">Remaining Qty</th>
                                <th>Shipment No</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th>PO Number</th>
                            </tr>
                        </thead>
                        <tbody id="modalStockTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
function openStockModal(fabricSku, warehouseId, cuttingMasterName) {

    document.getElementById('modalFabricSku').innerText = fabricSku;
    document.getElementById('modalWarehouse').innerText = cuttingMasterName ?? '';
    document.getElementById('modalTotalQty').innerText = '0';

    document.getElementById('modalStockTable').innerHTML = `
        <tr>
            <td colspan="5" class="text-center text-muted">Loading...</td>
        </tr>
    `;

    fetch(`{{ route('admin.report.stock.roll.details') }}?fabric_sku=${fabricSku}&warehouse_id=${warehouseId}`)
        .then(res => res.json())
        .then(data => {

            if (!data.length) {
                document.getElementById('modalStockTable').innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">No stock available</td>
                    </tr>
                `;
                return;
            }

            let rowsHtml = '';
            let totalQty = 0;

            data.forEach(ship => {
                ship.rolls.forEach(r => {

                    totalQty += parseFloat(r.remaining_quantity ?? 0);

                    rowsHtml += `
                        <tr>
                            <td>${r.roll_number ?? '-'}</td>
                            <td class="text-end fw-bold">${Number(r.remaining_quantity).toFixed(2)}</td>
                            <td>${ship.shipment_number ?? '-'}</td>
                            <td>${ship.supplier ?? '-'}</td>
                            <td>${ship.receipt_date ?? '-'}</td>
                            <td>${ship.po_number ?? '-'}</td>
                        </tr>
                    `;
                });
            });

            document.getElementById('modalStockTable').innerHTML = rowsHtml;
            document.getElementById('modalTotalQty').innerText = totalQty.toFixed(2);
        });

    new bootstrap.Modal(document.getElementById('stockModal')).show();
}
</script>


@endsection