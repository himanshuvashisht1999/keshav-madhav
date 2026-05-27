<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-report">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Fabric Name</th>
                        <th>Supplier</th>
                        <th class="text-end">Total Received</th>
                        <th class="text-end">Total Issued</th>
                        <th class="text-end">Remaining Qty</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                    @forelse($data as $row)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td class="fw-bold text-primary">{{ $row->name }}</td>
                        <td>{{ $row->vendor_name ?? '-' }}</td>
                        <td class="text-end text-success">{{ number_format($row->total_received, 2) }}</td>
                        <td class="text-end text-danger">{{ number_format($row->total_issued, 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($row->total_remaining, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('owner.stock', ['fabric_id' => $row->id]) }}" class="btn btn-sm btn-outline-primary" title="View Warehouses">
                                <i class="fas fa-eye"></i> View
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
        <div class="mt-3">
            {{ $data->links() }}
        </div>
    </div>
</div>
