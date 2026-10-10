<div class="erp-card p-0">
    <div class="table-responsive">
        <table class="table table-sm table-hover erp-table mb-0">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Warehouse</th>
                    <th class="text-right">Total received</th>
                    <th class="text-right">Total issued</th>
                    <th class="text-right">Remaining qty</th>
                    <th class="text-center" style="width: 180px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = 1; @endphp
                @forelse($data as $row)
                <tr>
                    <td class="text-center text-muted">{{ $sr++ }}</td>
                    <td>
                        <i class="fas fa-warehouse text-success mr-1"></i>
                        <strong>{{ $row->master_fabric_warehouse?->cutting_master_name ?? 'Unknown warehouse' }}</strong>
                    </td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($row->total_received, 2) }}</td>
                    <td class="text-right font-weight-bold text-danger">{{ number_format($row->total_issued, 2) }}</td>
                    <td class="text-right font-weight-bold text-dark">{{ number_format($row->total_remaining, 2) }}</td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('admin.report.stock', ['fabric_id' => request('fabric_id'), 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'receipts']) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" title="View shipments">
                            <i class="fas fa-arrow-down text-success mr-1"></i> Shipments
                        </a>
                        <a href="{{ route('admin.report.stock', ['fabric_id' => request('fabric_id'), 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'usages']) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="View usages">
                            <i class="fas fa-arrow-up text-danger mr-1"></i> Usages
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
