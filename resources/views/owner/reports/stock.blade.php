@extends('owner.layouts.app')

@section('content')
    <style>
        /* MOBILE APP LIST STYLES */
        @media (max-width: 991.98px) {
            .app-container {
                padding: 15px;
            }

            .stock-card {
                background: white;
                border: 1px solid #f1f5f9;
                border-radius: 16px;
                padding: 18px;
                margin-bottom: 16px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            }

            .card-header-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .sku-label {
                font-size: 15px;
                font-weight: 800;
                color: #1e293b;
            }

            .wh-pill {
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 0.5px;
                background: #f1f5f9;
                color: #64748b;
            }

            .card-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #f1f5f9;
                margin-bottom: 15px;
            }

            .info-item label {
                display: block;
                font-size: 10px;
                color: #94a3b8;
                text-transform: uppercase;
                font-weight: 700;
                margin-bottom: 2px;
            }

            .info-item .value {
                font-size: 15px;
                font-weight: 700;
                color: #334155;
            }

            .stock-value {
                color: var(--primary);
                font-weight: 900;
            }

            .card-action {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .stock-meta {
                font-size: 11px;
                color: #94a3b8;
                font-weight: 600;
            }

            .btn-view-app {
                background: var(--primary);
                color: white !important;
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 6px;
                text-decoration: none !important;
                box-shadow: 0 4px 10px rgba(111, 66, 193, 0.2);
                border: none;
            }

            /* Modal Styling */
            .modal-content {
                border-radius: 20px;
                border: none;
                overflow: hidden;
            }

            .modal-header {
                background: var(--primary);
                color: white;
                border: none;
                padding: 20px;
            }

            .modal-header .close {
                color: white;
                opacity: 1;
                text-shadow: none;
            }
        }

        @media (min-width: 992px) {
            .desktop-wrapper {
                padding: 25px;
            }

            .table-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                border: none;
            }
        }
    </style>

    <!-- MOBILE CONTENT -->
    <div class="mobile-only">
        <div class="app-container" style="padding-top: 20px;">
            <h5 class="mb-4 font-weight-bold" style="color: #1e293b;">Fabric Inventory</h5>

            <!-- Mobile Filters -->
            <div class="mb-3">
                <form action="{{ route('owner.stock') }}" method="GET">
                    <div class="input-group"
                        style="background: white; border-radius: 10px; border: 1px solid #eee; overflow: hidden;">
                        <select name="fabric_sku" class="form-control border-0" onchange="this.form.submit()"
                            style="font-size: 13px;">
                            <option value="">All Fabrics</option>
                            @foreach ($fabrics as $fabric)
                                <option value="{{ $fabric->sku }}" {{ request('fabric_sku') == $fabric->sku ? 'selected' : '' }}>
                                    {{ $fabric->sku }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @foreach ($stocks as $sku => $whStocks)
                @foreach ($whStocks as $stock)
                    <div class="stock-card">
                        <div class="card-header-top">
                            <div class="sku-label">{{ $sku }}</div>
                            <div class="wh-pill">
                                <i class="fas fa-warehouse"></i>
                                {{ \Illuminate\Support\Str::limit($stock->master_fabric_warehouse->cutting_master_name, 12) }}
                            </div>
                        </div>

                        <div class="card-grid">
                            <div class="info-item">
                                <label>Available Stock</label>
                                <div class="value stock-value">{{ number_format($stock->total_remaining, 2) }} Mtr</div>
                            </div>
                            <div class="info-item">
                                <label>Status</label>
                                <div class="value" style="font-size: 12px; color: #16a34a;">In Stock</div>
                            </div>
                        </div>

                        <div class="card-action">
                            <div class="stock-meta">
                                <i class="fas fa-box"></i> Roll Details Available
                            </div>
                            <button class="btn-view-app"
                                onclick="viewRollDetails('{{ $sku }}', '{{ $stock->master_fabric_warehouse_id }}')">
                                View Rolls <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- DESKTOP CONTENT -->
    <div class="desktop-only desktop-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-weight: 800; color: var(--text-main);">Fabric Stock Report</h2>
        </div>

        <div class="card table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Fabric SKU</th>
                                <th>Warehouse</th>
                                <th class="text-right">Remaining Qty (Mtrs)</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stocks as $sku => $whStocks)
                                @foreach ($whStocks as $stock)
                                    <tr>
                                        <td class="font-weight-bold">{{ $sku }}</td>
                                        <td>{{ $stock->master_fabric_warehouse->cutting_master_name }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($stock->total_remaining, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary" style="border-radius: 6px;"
                                                onclick="viewRollDetails('{{ $sku }}', '{{ $stock->master_fabric_warehouse_id }}')">
                                                View Rolls
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- UNIFIED MODAL -->
    <div class="modal fade" id="rollModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="rollModalTitle">Roll Details</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-0">
                    <div id="rollDetailsContent">
                        <div class="p-5 text-center loading-spinner">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function viewRollDetails(sku, whId) {
            $('#rollModalTitle').text(`SKU: ${sku} - Details`);
            $('#rollDetailsContent').html(
                '<div class="p-5 text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>');
            $('#rollModal').modal('show');

            fetch(`{{ route('owner.stock.roll.details') }}?fabric_sku=${sku}&warehouse_id=${whId}`)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (window.innerWidth < 992) {
                        // Mobile View for Modal
                        data.forEach(shipment => {
                            shipment.rolls.forEach(roll => {
                                html += `
                                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="font-weight-bold" style="font-size:14px;">Roll #${roll.roll_number}</div>
                                                <div class="text-xs text-muted">PO: ${shipment.po_number || 'N/A'}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-weight-bold text-primary">${roll.remaining_quantity}</div>
                                                <div class="text-xs text-muted">Meters</div>
                                            </div>
                                        </div>
                                    `;
                            });
                        });
                    } else {
                        // Desktop Table for Modal
                        html = `
                                <div class="p-3">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr class="bg-light">
                                                <th>Roll No</th>
                                                <th>PO No</th>
                                                <th class="text-right">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                        data.forEach(shipment => {
                            shipment.rolls.forEach(roll => {
                                html += `
                                        <tr>
                                            <td>${roll.roll_number}</td>
                                            <td>${shipment.po_number || 'N/A'}</td>
                                            <td class="text-right font-weight-bold">${roll.remaining_quantity}</td>
                                        </tr>
                                    `;
                            });
                        });
                        html += '</tbody></table></div>';
                    }
                    $('#rollDetailsContent').html(html);
                });
        }
    </script>
@endsection