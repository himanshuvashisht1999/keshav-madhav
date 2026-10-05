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
                    @php
                        $formattedDate = $row->created_at instanceof \Carbon\Carbon 
                            ? $row->created_at->format('d M Y') 
                            : ($row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '-');
                    @endphp
                    <tr>
                        <td>{{ $sr++ }}</td>
                        <td>{{ $formattedDate }}</td>
                        <td><span class="badge bg-secondary">{{ $row->roll_no }}</span></td>
                        <td>
                            @if(isset($row->type_badge))
                                @if($row->type_badge === 'Transfer Out')
                                    <span class="badge bg-primary mb-1"><i class="fas fa-exchange-alt"></i> Transfer Out</span><br>
                                @elseif($row->type_badge === 'Stock Disposal')
                                    <span class="badge bg-warning text-dark mb-1"><i class="fas fa-trash-alt"></i> Disposal</span><br>
                                @elseif($row->type_badge === 'Vendor Return')
                                    <span class="badge bg-secondary mb-1"><i class="fas fa-undo"></i> Return</span><br>
                                @elseif($row->type_badge === 'Direct Sale')
                                    <span class="badge bg-info mb-1"><i class="fas fa-shopping-cart"></i> Sale</span><br>
                                @else
                                    <span class="badge bg-danger mb-1"><i class="fas fa-cut"></i> Cutting</span><br>
                                @endif
                            @endif
                            <span class="fw-bold">{{ $row->lot_no }}</span>
                        </td>
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
