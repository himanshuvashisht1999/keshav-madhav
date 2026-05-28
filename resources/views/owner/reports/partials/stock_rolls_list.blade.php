@foreach($rolls as $r)
<a href="{{ route('owner.report.stock.rolls.tracking', ['fabric_id' => $r->fabric_id, 'roll_no' => $r->roll_number]) }}" class="roll-card">
    <div class="roll-header">
        <div class="d-flex align-items-center gap-2">
            <span class="roll-no">#{{ $r->roll_number }}</span>
            <span class="fabric-name">{{ $r->fabric->name ?? 'N/A' }}</span>
        </div>
        <div class="qty-badge">
            <span class="qty-value {{ $r->remaining_quantity > 0 ? 'text-success' : 'text-muted' }}">
                {{ number_format($r->remaining_quantity, 1) }}m
            </span>
            <span class="qty-label">Available</span>
        </div>
    </div>
    
    <div class="roll-meta">
        <div class="meta-item">
            <i class="fas fa-warehouse opacity-50"></i>
            <span>{{ \Illuminate\Support\Str::limit($r->master_fabric_warehouse?->cutting_master_name ?? 'N/A', 15) }}</span>
        </div>
        <div class="meta-item justify-content-end">
            <i class="fas fa-truck opacity-50"></i>
            <span>{{ \Illuminate\Support\Str::limit($r->fabric_receipt->vendor->name ?? '-', 15) }}</span>
        </div>
        <div class="meta-item">
            <i class="fas fa-file-invoice opacity-50"></i>
            <span>PO: {{ $r->purchase_order?->sku ?? '-' }}</span>
        </div>
        <div class="meta-item justify-content-end">
            <i class="fas fa-ship opacity-50"></i>
            <span>Ship: {{ $r->shipment_number ?? '-' }}</span>
        </div>
        <div class="meta-item">
            <i class="fas fa-arrow-down text-muted"></i>
            <span>Recv: {{ number_format($r->meter, 1) }}m</span>
        </div>
        <div class="meta-item justify-content-end">
            <i class="fas fa-history text-primary"></i>
            <span class="text-primary font-weight-bold">View History</span>
        </div>
    </div>
</a>
@endforeach
