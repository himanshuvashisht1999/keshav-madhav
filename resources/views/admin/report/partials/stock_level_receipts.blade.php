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
                        <th>PO Number</th>
                        <th>Shipment No</th>
                        <th>Roll No</th>
                        <th class="text-end">Received Qty</th>
                        <th class="text-end">Remaining Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                    @forelse($data as $row)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ optional($row->fabric_receipt)->created_at ? $row->fabric_receipt->created_at->format('d M Y') : $row->created_at->format('d M Y') }}</td>
                        <td>{{ $row->master_fabric_warehouse?->cutting_master_name }}</td>
                        <td>{{ $row->fabric_receipt->vendor->name ?? '-' }}</td>
                        <td>{{ $row->purchase_order?->sku ?? '-' }}</td>
                        <td>{{ $row->shipment_number ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $row->roll_number }}</span></td>
                        <td class="text-end fw-bold text-success">{{ number_format($row->meter, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->remaining_quantity, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No shipments found.</td>
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
