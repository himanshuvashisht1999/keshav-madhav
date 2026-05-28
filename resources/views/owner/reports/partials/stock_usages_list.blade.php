@foreach($data as $row)
    <div class="stock-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="font-weight-bold" style="font-size: 15px;">{{ $row->order_no }}</div>
                <div class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</div>
            </div>
            <div class="text-right">
                <div class="font-weight-black text-danger" style="font-size: 16px;">-{{ number_format($row->meter, 1) }}m</div>
                <div class="stat-label">Consumed</div>
            </div>
        </div>

        <div class="p-3 rounded-xl bg-light" style="font-size: 12px;">
            <div class="row g-2">
                <div class="col-6">
                    <span class="text-muted">Lot No:</span> <strong>{{ $row->lot_no }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Roll No:</span> <strong>{{ $row->roll_no }}</strong>
                </div>
                <div class="col-6 mt-2">
                    <span class="text-muted">Stage:</span> <strong>{{ $row->stageMasterUnit?->name ?: '-' }}</strong>
                </div>
                <div class="col-12 mt-2 border-top pt-2">
                    <span class="text-muted">Details:</span> 
                    <span class="font-weight-bold">
                        {{ $row->orderProductSet?->design_number ?: '' }} 
                        ({{ $row->orderProductSet?->colors?->name ?: '' }})
                    </span>
                </div>
            </div>
        </div>
    </div>
@endforeach
