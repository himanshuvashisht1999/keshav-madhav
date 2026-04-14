<div class="card shadow-sm border-danger">
    <div class="card-header bg-danger text-white">
        <h5 class="m-0"><i class="fas fa-arrow-up"></i> Fabric Usages (Issued)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-report">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Date</th>
                        <th>Roll No</th>
                        <th>Lot No</th>
                        <th>Order No</th>
                        <th>Design</th>
                        <th>Color</th>
                        <th>Stage Unit</th>
                        <th class="text-end">Used Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                    @forelse($data as $row)
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $row->created_at->format('d M Y') }}</td>
                        <td><span class="badge bg-secondary">{{ $row->roll_no }}</span></td>
                        <td class="fw-bold">{{ $row->lot_no }}</td>
                        <td>{{ $row->order_no }}</td>
                        <td>{{ $row->orderProductSet?->design_number ?? '-' }}</td>
                        <td>{{ $row->orderProductSet?->colors?->name ?? '-' }}</td>
                        <td>{{ $row->stageMasterUnit?->name ?? '-' }}</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($row->meter, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No usages found.</td>
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
