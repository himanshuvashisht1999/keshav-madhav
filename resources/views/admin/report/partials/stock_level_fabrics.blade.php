<div class="erp-card p-0">
    <div class="table-responsive">
        <table class="table table-sm table-hover erp-table mb-0">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Fabric name</th>
                    <th>Supplier</th>
                    <th class="text-right">Total received</th>
                    <th class="text-right">Total issued</th>
                    <th class="text-right">Remaining qty</th>
                    <th class="text-center" style="width: 100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                @forelse($data as $row)
                <tr>
                    <td class="text-center text-muted">{{ $sr++ }}</td>
                    <td><strong>{{ $row->name }}</strong></td>
                    <td>{{ $row->vendor_name ?? '-' }}</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($row->total_received, 2) }}</td>
                    <td class="text-right font-weight-bold text-danger">{{ number_format($row->total_issued, 2) }}</td>
                    <td class="text-right font-weight-bold text-dark">{{ number_format($row->total_remaining, 2) }}</td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('admin.report.stock', ['fabric_id' => $row->id]) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="View warehouses">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No fabric stock found.</td>
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
