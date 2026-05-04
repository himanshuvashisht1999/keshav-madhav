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

        .summary-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid #343a40;
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                        <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div>
                        <h3>Fabric Purchase History</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                
                <div class="summary-box">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Fabric: <strong>{{ $fabric->name }}</strong></h5>
                            <p class="mb-0 text-muted">SKU: {{ $fabric->sku ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 class="text-primary">Total Records: <strong>{{ $history->count() }}</strong></h5>
                        </div>
                    </div>
                </div>

                {{-- ================= FILTERS ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise') }}">
                            <input type="hidden" name="fabric_id" value="{{ $fabric->id }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Supplier</label>
                                    <select name="vendor_id" class="form-control">
                                        <option value="">All Suppliers</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ ($filters['vendor_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="start_date" class="form-control"
                                        value="{{ $filters['start_date'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="end_date" class="form-control"
                                        value="{{ $filters['end_date'] ?? '' }}">
                                </div>
                                <div class="col-md-3 d-flex gap-1">
                                    <button class="btn btn-primary flex-fill">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('admin.report.purchase_order_fabric_wise', ['fabric_id' => $fabric->id]) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card report-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-report">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>PO Date</th>
                                        <th>PO Number</th>
                                        <th>Vendor / Supplier</th>
                                        <th class="text-end">Meters</th>
                                        <th class="text-end">Rate (Per Meter)</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-center">PO Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $totalMeters = 0;
                                        $totalVal = 0;
                                    @endphp
                                    @forelse($history as $item)
                                        @php
                                            $totalMeters += $item->meter;
                                            $itemTotal = $item->meter * $item->price;
                                            $totalVal += $itemTotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->purchaseOrder->date)->format('d M Y') }}</td>
                                            <td class="fw-bold text-primary">{{ $item->purchaseOrder->sku }}</td>
                                            <td>{{ $item->purchaseOrder->vendor->name ?? '-' }}</td>
                                            <td class="text-end">{{ number_format($item->meter, 2) }}</td>
                                            <td class="text-end">₹ {{ number_format($item->price, 2) }}</td>
                                            <td class="text-end fw-bold">₹ {{ number_format($itemTotal, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $item->purchaseOrder->status == 1 ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $item->purchaseOrder->status == 1 ? 'Active' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-info view-shipments" data-id="{{ $item->id }}" data-po="{{ $item->purchaseOrder->sku }}">
                                                    <i class="fas fa-truck"></i> Shipments
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                No purchase records found for this fabric.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($history->count() > 0)
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="4" class="text-end">TOTAL</th>
                                            <th class="text-end">{{ number_format($totalMeters, 2) }}</th>
                                            <th>-</th>
                                            <th class="text-end text-success">₹ {{ number_format($totalVal, 2) }}</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Shipment Modal -->
    <div class="modal fade" id="shipmentModal" tabindex="-1" aria-labelledby="shipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="shipmentModalLabel">Shipment Details - <span id="modalPoSku"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Roll No</th>
                                    <th>Shipment No</th>
                                    <th class="text-end">Meters</th>
                                    <th class="text-end">Price/Meter</th>
                                    <th class="text-end">Total</th>
                                    <th>Warehouse</th>
                                    <th>Received Date</th>
                                </tr>
                            </thead>
                            <tbody id="shipmentDataBody">
                                <!-- Data will be loaded here via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.view-shipments').on('click', function() {
            const itemId = $(this).data('id');
            const poSku = $(this).data('po');
            $('#modalPoSku').text(poSku);
            $('#shipmentDataBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
            $('#shipmentModal').modal('show');

            $.ajax({
                url: "{{ route('admin.report.purchase_order.item.details') }}",
                type: 'GET',
                data: { purchase_order_item_id: itemId },
                success: function(response) {
                    let html = '';
                    if (response.length > 0) {
                        let totalMeters = 0;
                        let grandTotal = 0;
                        response.forEach(function(row) {
                            const meters = parseFloat(row.meter) || 0;
                            const price = parseFloat(row.price_per_meter) || 0;
                            const total = meters * price;
                            totalMeters += meters;
                            grandTotal += total;

                            html += `<tr>
                                <td>${row.roll_number || '-'}</td>
                                <td>${row.shipment_number || '-'}</td>
                                <td class="text-end">${meters.toFixed(2)}</td>
                                <td class="text-end">₹ ${price.toFixed(2)}</td>
                                <td class="text-end fw-bold">₹ ${total.toFixed(2)}</td>
                                <td>${row.master_fabric_warehouse ? row.master_fabric_warehouse.cutting_master_name : '-'}</td>
                                <td>${new Date(row.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                            </tr>`;
                        });
                        html += `<tr class="table-secondary">
                            <th colspan="2" class="text-end">TOTAL</th>
                            <th class="text-end">${totalMeters.toFixed(2)}</th>
                            <th></th>
                            <th class="text-end">₹ ${grandTotal.toFixed(2)}</th>
                            <th colspan="2"></th>
                        </tr>`;
                    } else {
                        html = '<tr><td colspan="7" class="text-center text-muted">No shipments found for this item</td></tr>';
                    }
                    $('#shipmentDataBody').html(html);
                },
                error: function() {
                    $('#shipmentDataBody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load data</td></tr>');
                }
            });
        });
    });
</script>
@endsection
