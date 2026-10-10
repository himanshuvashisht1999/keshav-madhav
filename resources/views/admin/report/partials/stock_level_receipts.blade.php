<div class="erp-card p-0">
    <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
            <i class="fas fa-arrow-down text-success mr-1"></i> Fabric shipments (receipts)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover erp-table mb-0">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Date</th>
                    <th>Warehouse</th>
                    <th>Supplier</th>
                    <th>Bill no</th>
                    <th>Shipment no</th>
                    <th class="text-center">Roll no</th>
                    <th class="text-right">Price / mtr</th>
                    <th class="text-right">Total amount</th>
                    <th class="text-right">Received qty</th>
                    <th class="text-right">Returned qty</th>
                    <th class="text-right">Remaining qty</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                @forelse($data as $row)
                @php 
                    $filterWh = request('warehouse_id');
                    if (is_array($filterWh)) $filterWh = reset($filterWh);
                    $returnedQty = $row->returns ? $row->returns->sum('return_meter') : 0; 
                    $receivingWh = $row->fabric_receipt->cutting_master?->cutting_master_name ?? $row->master_fabric_warehouse?->cutting_master_name;
                    $isTransferredOut = $filterWh && $row->fabric_receipt && $row->fabric_receipt->master_fabric_warehouse_id == $filterWh && $row->master_fabric_warehouse_id != $filterWh;
                    $displayRemaining = $isTransferredOut ? 0.00 : $row->remaining_quantity;
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $sr++ }}</td>
                    <td>{{ optional($row->fabric_receipt)->created_at ? $row->fabric_receipt->created_at->format('d M Y') : $row->created_at->format('d M Y') }}</td>
                    <td>
                        <i class="fas fa-warehouse text-success mr-1"></i>
                        <strong>{{ $receivingWh }}</strong>
                        @if($isTransferredOut)
                            <br><span class="erp-badge-status erp-badge-status-warning"><i class="fas fa-exchange-alt mr-1"></i> Transferred to {{ $row->master_fabric_warehouse?->cutting_master_name }}</span>
                        @endif
                    </td>
                    <td>{{ $row->fabric_receipt->vendor->name ?? '-' }}</td>
                    <td><code>{{ $row->fabric_receipt->bill_no ?? '-' }}</code></td>
                    <td><code>{{ $row->shipment_number ?? '-' }}</code></td>
                    <td class="text-center"><span class="badge badge-light border">{{ $row->roll_number }}</span></td>
                    <td class="text-right">₹ {{ number_format($row->price_per_meter, 2) }}</td>
                    <td class="text-right font-weight-bold">₹ {{ number_format($row->price_per_meter * $row->meter, 2) }}</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($row->meter, 2) }}</td>
                    <td class="text-right font-weight-bold text-danger">{{ number_format($returnedQty, 2) }}</td>
                    <td class="text-right font-weight-bold text-dark">{{ number_format($displayRemaining, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">No shipments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($data->hasPages())
        <div class="p-2 border-top bg-light">
            {{ $data->links() }}
        </div>
    @endif
</div>
