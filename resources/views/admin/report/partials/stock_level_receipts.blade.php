<div class="card shadow-sm border-success">
    <div class="card-header bg-success text-white">
        <h5 class="m-0"><i class="fas fa-arrow-down"></i> Fabric Shipments (Receipts)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-report">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Date</th>
                        <th>Warehouse</th>
                        <th>Supplier</th>
                        <th>Bill No</th>
                        <th>Shipment No</th>
                        <th>Roll No</th>
                        <th class="text-end">Price/Mtr</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Received Qty</th>
                        <th class="text-end text-danger">Returned Qty</th>
                        <th class="text-end">Remaining Qty</th>
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
                        <td>{{ $sr++ }}</td>
                        <td>{{ optional($row->fabric_receipt)->created_at ? $row->fabric_receipt->created_at->format('d M Y') : $row->created_at->format('d M Y') }}</td>
                        <td>
                            <i class="fas fa-warehouse text-primary me-1"></i>
                            <strong>{{ $receivingWh }}</strong>
                            @if($isTransferredOut)
                                <br><span class="badge bg-warning text-dark"><i class="fas fa-exchange-alt"></i> Transferred to {{ $row->master_fabric_warehouse?->cutting_master_name }}</span>
                            @endif
                        </td>
                        <td>{{ $row->fabric_receipt->vendor->name ?? '-' }}</td>
                        <td>{{ $row->fabric_receipt->bill_no ?? '-' }}</td>
                        <td>{{ $row->shipment_number ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $row->roll_number }}</span></td>
                        <td class="text-end">{{ number_format($row->price_per_meter, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->price_per_meter * $row->meter, 2) }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($row->meter, 2) }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($returnedQty, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($displayRemaining, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">No shipments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
