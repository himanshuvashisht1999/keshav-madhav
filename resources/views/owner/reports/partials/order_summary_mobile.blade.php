@foreach ($salesOrders as $row)
    <div class="order-card">
        <div class="card-header-top">
            <div class="sku-label">#{{ $row['order_no'] }}</div>
            @php
                $remaining = $row['total_pcs'] - $row['scanned_pcs'];
            @endphp
            <div
                class="status-pill 
                                @if($remaining <= 0) sp-closed @elseif($row['scanned_pcs'] > 0) sp-partial @else sp-active @endif">
                @if($remaining <= 0) COMPLETED @elseif($row['scanned_pcs'] > 0) PARTIAL @else ACTIVE @endif
            </div>
        </div>

        <div class="card-grid">
            <div class="info-item">
                <label>Customer</label>
                <div class="value">{{ \Illuminate\Support\Str::limit($row['customer'], 15) }}</div>
            </div>
            <div class="info-item">
                <label>Design</label>
                <div class="value">
                    @if(count($row['designs']) === 1)
                        {{ array_key_first($row['designs']) }}
                    @elseif(count($row['designs']) > 1)
                        <a href="javascript:void(0)" class="badge badge-info text-white" style="font-size: 10px; cursor: pointer; text-decoration: none;" onclick='showDesignsModal(@json($row['designs']))'>Multiple</a>
                    @else
                        -
                    @endif
                </div>
            </div>
            <div class="info-item">
                <label>Quantity</label>
                <div class="value">{{ $row['total_pcs'] }} Pcs</div>
            </div>
        </div>

        <div class="card-action">
            <div class="date-text">
                <i class="far fa-calendar-alt"></i> {{ date('d M Y', strtotime($row['created_at'])) }}
            </div>
            <a href="{{ route('owner.order-summary.view', $row['id']) }}" class="btn-view-app">
                Details <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
@endforeach
