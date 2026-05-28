@foreach($data as $row)
    <a href="{{ route('owner.stock', ['fabric_id' => $row->id, 'warehouse_id' => request('warehouse_id')]) }}" class="stock-card">
        <div class="fabric-title">
            {{ $row->name }}
            <span class="vendor-badge">{{ $row->vendor_name ?: 'Global' }}</span>
        </div>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Received</span>
                <span class="stat-value">{{ number_format($row->total_received, 1) }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Stock</span>
                <span class="stat-value remaining">{{ number_format($row->total_remaining, 1) }}</span>
            </div>
        </div>
        <div class="mt-3 text-right">
            <i class="fas fa-chevron-right text-muted opacity-50"></i>
        </div>
    </a>
@endforeach
