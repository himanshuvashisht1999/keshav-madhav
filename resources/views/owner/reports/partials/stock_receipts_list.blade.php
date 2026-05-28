@foreach($data as $row)
    <div class="stock-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="font-weight-bold" style="font-size: 16px;">{{ $row->fabric_receipt?->sku ?: 'Shipment #' . $row->id }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ $row->created_at->format('d M Y, h:i A') }}</div>
            </div>
            <span class="vendor-badge">{{ $row->fabric_receipt?->vendor?->name ?: 'Direct' }}</span>
        </div>
        
        <div class="p-3 rounded-xl bg-light mb-3 d-flex justify-content-around">
            <div class="text-center">
                <span class="stat-label">Roll No</span>
                <span class="font-weight-black">{{ $row->roll_number }}</span>
            </div>
            <div class="text-center">
                <span class="stat-label">Received</span>
                <span class="font-weight-black">{{ number_format($row->meter, 1) }}m</span>
            </div>
            <div class="text-center">
                <span class="stat-label">In Stock</span>
                <span class="font-weight-black text-success">{{ number_format($row->remaining_quantity, 1) }}m</span>
            </div>
        </div>

        <div style="font-size: 12px; color: var(--text-muted);">
            <i class="fas fa-warehouse mr-1"></i> {{ $row->master_fabric_warehouse?->cutting_master_name ?: '-' }}
            <span class="mx-2">|</span>
            <i class="fas fa-barcode mr-1"></i> {{ $row->qrcode_number ?: '-' }}
        </div>
    </div>
@endforeach
