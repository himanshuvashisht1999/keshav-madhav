<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-report">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Warehouse</th>
                        <th class="text-end">Total Received</th>
                        <th class="text-end">Total Issued</th>
                        <th class="text-end">Remaining Qty</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = 1; @endphp
                    @forelse($data as $row)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>
                            <i class="fas fa-warehouse text-primary me-2"></i>
                            {{ $row->master_fabric_warehouse?->cutting_master_name ?? 'Unknown Warehouse' }}
                        </td>
                        <td class="text-end text-success">{{ number_format($row->total_received, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($row->total_issued, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total_remaining, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.report.stock', ['fabric_id' => request('fabric_id'), 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'receipts']) }}" class="btn btn-sm btn-outline-success me-1" title="View Shipments">
                                <i class="fas fa-arrow-down"></i> Shipments
                            </a>
                            <a href="{{ route('admin.report.stock', ['fabric_id' => request('fabric_id'), 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'usages']) }}" class="btn btn-sm btn-outline-danger" title="View Usages">
                                <i class="fas fa-arrow-up"></i> Usages
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No warehouse data found for this fabric.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
