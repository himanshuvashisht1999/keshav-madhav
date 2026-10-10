@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-history"></i> Fabric purchase history
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-layer-group text-muted"></i> Fabric: <strong>{{ $fabric->name }}</strong></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-barcode text-muted"></i> SKU: <code>{{ $fabric->sku ?? '-' }}</code></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Date: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <span class="erp-stat-pill mr-2">
                Total records: <strong>{{ $history->count() }}</strong>
            </span>
            <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to fabrics
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise') }}" class="mb-0">
            <input type="hidden" name="fabric_id" value="{{ $fabric->id }}">
            <div class="form-row align-items-end">
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Supplier</label>
                    <select name="vendor_id" class="form-control form-control-sm erp-filter-select">
                        <option value="">All suppliers</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ ($filters['vendor_id'] ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm erp-filter-input"
                        value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-2 col-sm-6 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.purchase_order_fabric_wise', ['fabric_id' => $fabric->id]) }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>PO date</th>
                        <th>PO number</th>
                        <th>Supplier</th>
                        <th class="text-right">Meters</th>
                        <th class="text-right">Rate / meter</th>
                        <th class="text-right">Total amount</th>
                        <th class="text-center">PO status</th>
                        <th class="text-center" style="width: 120px;">Action</th>
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
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->purchaseOrder->date)->format('d M Y') }}</td>
                            <td><code>{{ $item->purchaseOrder->sku }}</code></td>
                            <td><strong>{{ $item->purchaseOrder->vendor->name ?? '-' }}</strong></td>
                            <td class="text-right font-weight-bold">{{ number_format($item->meter, 2) }}</td>
                            <td class="text-right">₹ {{ number_format($item->price, 2) }}</td>
                            <td class="text-right font-weight-bold text-success">₹ {{ number_format($itemTotal, 2) }}</td>
                            <td class="text-center">
                                @if($item->purchaseOrder->status == 1)
                                    <span class="erp-badge-status erp-badge-status-closed">Active</span>
                                @else
                                    <span class="erp-badge-status erp-badge-status-neutral">Closed</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn-erp btn-erp-outline btn-sm py-0 px-2 view-shipments" data-id="{{ $item->id }}" data-po="{{ $item->purchaseOrder->sku }}" style="font-size: 11px;">
                                    <i class="fas fa-truck mr-1"></i> Shipments
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
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($totalMeters, 2) }}</th>
                            <th></th>
                            <th class="text-right text-success font-weight-bold">₹ {{ number_format($totalVal, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<!-- Shipment Modal -->
<div class="modal fade" id="shipmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title font-weight-bold" style="font-size: 13px;">
                    <i class="fas fa-truck text-success mr-1"></i> Shipment details: <span id="modalPoSku"></span>
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Roll no</th>
                                <th>Shipment no</th>
                                <th class="text-right">Meters</th>
                                <th class="text-right">Price / meter</th>
                                <th class="text-right">Total</th>
                                <th>Warehouse</th>
                                <th>Received date</th>
                            </tr>
                        </thead>
                        <tbody id="shipmentDataBody">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn-erp btn-erp-outline btn-sm" data-dismiss="modal">Close</button>
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
            $('#shipmentDataBody').html('<tr><td colspan="7" class="text-center py-3 text-muted">Loading...</td></tr>');
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
                                <td><span class="badge badge-light border">${row.roll_number || '-'}</span></td>
                                <td><code>${row.shipment_number || '-'}</code></td>
                                <td class="text-right font-weight-bold">${meters.toFixed(2)}</td>
                                <td class="text-right">₹ ${price.toFixed(2)}</td>
                                <td class="text-right font-weight-bold text-success">₹ ${total.toFixed(2)}</td>
                                <td>${row.master_fabric_warehouse ? row.master_fabric_warehouse.cutting_master_name : '-'}</td>
                                <td>${new Date(row.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                            </tr>`;
                        });
                        html += `<tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total</th>
                                <th class="text-right">${totalMeters.toFixed(2)}</th>
                                <th></th>
                                <th class="text-right text-success font-weight-bold">₹ ${grandTotal.toFixed(2)}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>`;
                    } else {
                        html = '<tr><td colspan="7" class="text-center text-muted py-3">No shipments found for this item</td></tr>';
                    }
                    $('#shipmentDataBody').html(html);
                },
                error: function() {
                    $('#shipmentDataBody').html('<tr><td colspan="7" class="text-center text-danger py-3">Failed to load data</td></tr>');
                }
            });
        });
    });
</script>
@endsection
