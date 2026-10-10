<div class="erp-card p-0">
    <div class="px-3 py-2 border-bottom bg-light d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
            <i class="fas fa-arrow-up text-danger mr-1"></i> Fabric usages (issued)
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover erp-table mb-0">
            <thead>
                <tr>
                    <th style="width: 45px;" class="text-center">#</th>
                    <th>Date</th>
                    <th class="text-center">Roll no</th>
                    <th>Lot no</th>
                    <th>Order no</th>
                    <th>Design</th>
                    <th>Color</th>
                    <th>Stage unit</th>
                    <th class="text-right">Used qty</th>
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
                    <td class="text-center text-muted">{{ $sr++ }}</td>
                    <td>{{ $formattedDate }}</td>
                    <td class="text-center"><span class="badge badge-light border">{{ $row->roll_no }}</span></td>
                    <td>
                        @if(isset($row->type_badge))
                            @if($row->type_badge === 'Transfer Out')
                                <span class="erp-badge-status erp-badge-status-open mb-1"><i class="fas fa-exchange-alt mr-1"></i> Transfer out</span><br>
                            @elseif($row->type_badge === 'Stock Disposal')
                                <span class="erp-badge-status erp-badge-status-delayed mb-1"><i class="fas fa-trash-alt mr-1"></i> Disposal</span><br>
                            @elseif($row->type_badge === 'Vendor Return')
                                <span class="erp-badge-status erp-badge-status-neutral mb-1"><i class="fas fa-undo mr-1"></i> Return</span><br>
                            @elseif($row->type_badge === 'Direct Sale')
                                <span class="erp-badge-status erp-badge-status-info mb-1"><i class="fas fa-shopping-cart mr-1"></i> Sale</span><br>
                            @else
                                <span class="erp-badge-status erp-badge-status-delayed mb-1"><i class="fas fa-cut mr-1"></i> Cutting</span><br>
                            @endif
                        @endif
                        <strong>{{ $row->lot_no }}</strong>
                    </td>
                    <td><code>{{ $row->order_no }}</code></td>
                    <td>{{ $row->orderProductSet?->design_number ?? '-' }}</td>
                    <td>{{ $row->orderProductSet?->colors?->name ?? '-' }}</td>
                    <td>{{ $row->stageMasterUnit?->name ?? '-' }}</td>
                    <td class="text-right font-weight-bold text-danger">{{ number_format($row->meter, 2) }} m</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No usages found.</td>
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
