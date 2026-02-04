@extends('owner.layouts.app')

@section('content')
    <style>
        /* ===== COMMON STYLE ===== */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-bottom: 1px solid #eee;
        }

        .report-header h3 {
            font-weight: 700;
            margin: 0;
            color: #1e3a8a;
        }

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            border: none;
            margin: 0 20px;
        }

        .table-report thead th {
            background: #1e3a8a;
            color: #fff !important;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            padding: 12px;
        }

        .table-report tbody td {
            vertical-align: middle;
            font-size: 14px;
            padding: 12px;
        }

        .fabric-cell {
            background: #f8f9fa;
            font-weight: 700;
            color: #1e3a8a;
        }

        /* MOBILE RESPONSIVE TWEAKS */
        @media (max-width: 991.98px) {
            .report-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                color: white;
                border: none;
            }

            .report-header h3 {
                color: white;
            }

            .report-card {
                margin: -30px 15px 20px;
            }

            /* App-style list for mobile */
            .mobile-stock-list {
                padding-top: 10px;
            }

            .stock-item {
                background: white;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .stock-item-title {
                font-weight: 800;
                color: #1e3a8a;
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }

            .stock-item-qty {
                color: #10b981;
                font-weight: 800;
            }

            .stock-item-meta {
                font-size: 12px;
                color: #64748b;
                margin-bottom: 8px;
            }
        }
    </style>

    <div class="report-header">
        <div>
            <h3>Fabric Stock Inventory</h3>
        </div>
        <div class="desktop-only text-muted">Date: {{ now()->format('d M Y') }}</div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <!-- FILTERS -->
            <form method="GET" action="{{ route('owner.stock') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-3 col-6 mb-2">
                        <label class="text-xs font-weight-bold">Warehouse</label>
                        <select name="warehouse_id" class="form-control form-control-sm">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ ($filters['warehouse_id'] ?? '') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->cutting_master_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <label class="text-xs font-weight-bold">Fabric SKU</label>
                        <select name="fabric_sku" class="form-control form-control-sm">
                            <option value="">All Fabrics</option>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->sku }}" {{ ($filters['fabric_sku'] ?? '') == $fabric->sku ? 'selected' : '' }}>
                                    {{ $fabric->sku }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-4 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                    </div>
                    <div class="col-md-2 col-4 mb-2 d-flex align-items-end">
                        <a href="{{ route('owner.stock') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                </div>
            </form>

            <!-- DESKTOP TABLE -->
            <div class="desktop-only">
                <div class="table-responsive">
                    <table class="table table-bordered table-report">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fabric SKU</th>
                                <th>Warehouse Location</th>
                                <th class="text-right">Total Remaining (Mtr)</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sr = 1; @endphp
                            @forelse($stocks as $fabricSku => $rows)
                                @php $rowspan = $rows->count(); @endphp
                                @foreach($rows as $idx => $row)
                                    <tr>
                                        @if($idx === 0)
                                            <td rowspan="{{ $rowspan }}" class="fabric-cell text-center">{{ $sr++ }}</td>
                                            <td rowspan="{{ $rowspan }}" class="fabric-cell">{{ $fabricSku }}</td>
                                        @endif
                                        <td><i class="fas fa-warehouse text-muted mr-2"></i>
                                            {{ $row->master_fabric_warehouse->cutting_master_name ?? 'Unknown' }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($row->total_remaining, 2) }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-xs btn-outline-info"
                                                onclick="viewRollDetails('{{ $fabricSku }}', '{{ $row->master_fabric_warehouse_id }}')">
                                                View Rolls
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No stock found matching filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOBILE LIST -->
            <div class="mobile-only mobile-stock-list">
                @forelse($stocks as $sku => $rows)
                    @foreach($rows as $row)
                        <div class="stock-item" onclick="viewRollDetails('{{ $sku }}', '{{ $row->master_fabric_warehouse_id }}')">
                            <div class="stock-item-title">
                                <span>{{ $sku }}</span>
                                <span class="stock-item-qty">{{ number_format($row->total_remaining, 2) }} Mtr</span>
                            </div>
                            <div class="stock-item-meta">
                                <i class="fas fa-warehouse mr-1"></i>
                                {{ $row->master_fabric_warehouse->cutting_master_name ?? 'Unknown' }}
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-info">View Roll Details <i class="fas fa-chevron-right ml-1"></i></span>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="text-center p-4 text-muted">No stock found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Stock Details Modal -->
    <div class="modal fade" id="rollModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">Stock Roll Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-white text-primary px-3 py-2" id="modalFabricLabel"></span>
                            <span class="badge bg-white text-muted px-3 py-2 ml-2" id="modalWarehouseLabel"></span>
                        </div>
                        <div class="h5 m-0 font-weight-bold text-success"><span id="modalTotalSpan">0.00</span> Mtr</div>
                    </div>
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-sm mb-0">
                            <thead class="bg-dark text-white text-xs">
                                <tr>
                                    <th>Roll #</th>
                                    <th>Shipment</th>
                                    <th>PO #</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th class="text-right">Remaining (M)</th>
                                </tr>
                            </thead>
                            <tbody id="rollsBody" class="text-sm"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        function viewRollDetails(sku, whId) {
            $('#modalFabricLabel').text(sku);
            $('#rollsBody').html('<tr><td colspan="6" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>');
            $('#rollModal').modal('show');

            // Reuse admin API but ensure owner can access or better, use owner route
            // Admin route works if middleware allows or if we created an owner one.
            // Let's check admin route: admin.report.stock.roll.details
            // I created owner.lot-details but not owner.stock-details. 
            // I'll use the admin one for now if possible, or create an owner one.
            // Actually, I'll create a new owner endpoint to be safe.

            fetch(`{{ route('owner.stock.roll.details') }}?fabric_sku=${sku}&warehouse_id=${whId}`)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    let total = 0;
                    data.forEach(ship => {
                        ship.rolls.forEach(r => {
                            total += parseFloat(r.remaining_quantity);
                            html += `<tr>
                                <td><b>${r.roll_number}</b></td>
                                <td>${ship.shipment_number}</td>
                                <td class="text-muted">${ship.po_number}</td>
                                <td>${ship.supplier}</td>
                                <td>${ship.receipt_date}</td>
                                <td class="text-right font-weight-bold text-success">${Number(r.remaining_quantity).toFixed(2)}</td>
                            </tr>`;
                        });
                    });
                    $('#rollsBody').html(html || '<tr><td colspan="6" class="text-center p-3">No rolls found</td></tr>');
                    $('#modalTotalSpan').text(total.toFixed(2));
                    $('#modalWarehouseLabel').text(data[0] ? data[0].warehouse : ''); // wh name
                });
        }
    </script>
@endsection