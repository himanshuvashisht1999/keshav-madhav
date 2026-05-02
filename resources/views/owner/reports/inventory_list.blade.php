<div class="d-flex justify-content-between align-items-center mb-4">
    <span class="text-muted font-weight-bold" style="font-size: 13px;">{{ $data->total() }} STOCK GROUPS</span>
</div>

@foreach($data as $row)
    <div class="inventory-card">
        <div class="stock-badge">
            <span class="boxes-val">{{ number_format($row->total_boxes) }}</span>
            <span class="boxes-label">Boxes</span>
        </div>

        <div class="product-name">{{ $row->product_name }}</div>
        <span class="design-badge">#{{ $row->design_number }}</span>
        
        <div class="d-block">
            <span class="mrp-tag">MRP: ₹{{ number_format($row->mrp, 2) }}</span>
        </div>

        <div class="meta-grid">
            <div class="meta-item">
                <label>Size Set</label>
                <span>{{ $row->size_set_name }}</span>
            </div>
            <div class="meta-item">
                <label>Color</label>
                <span>{{ $row->color_name }}</span>
            </div>
            <div class="meta-item">
                <label>Fitting</label>
                <span>{{ $row->fitting_name ?: 'Standard' }}</span>
            </div>
            <div class="meta-item">
                <label>Pattern</label>
                <span>{{ $row->pattern_name ?: 'Plain' }}</span>
            </div>
        </div>
    </div>
@endforeach

@if($data->isEmpty())
    <div class="text-center py-5 opacity-25">
        <i class="fas fa-search-minus fa-4x mb-3"></i>
        <p class="h5 font-weight-bold">No results found</p>
    </div>
@endif

<div class="mt-4">
    {{ $data->links('pagination::simple-bootstrap-4') }}
</div>
