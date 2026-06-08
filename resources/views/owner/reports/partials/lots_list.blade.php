@foreach($data as $row)
    @isset($row['lot_no'])
    <a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}" class="lot-card">
        <div class="lot-id">
            Lot #{{ $row['lot_no'] }}
            <span class="qty-tag">{{ $row['lot_quantity'] ?? '0' }} Pcs</span>
        </div>
        <div class="order-info">
            <i class="fas fa-hashtag"></i> {{ $row['order_no'] }}
            <span class="mx-2 opacity-25">|</span>
            <i class="far fa-calendar-alt"></i> {{ now()->format('d M') }}
        </div>
        <span class="customer-name">
            <i class="far fa-user mr-2 text-warning"></i> {{ $row['customer_name'] }}
            <br>
            <i class="fas fa-layer-group mr-2 text-info mt-2"></i> Stage: {{ $row['last_current_stage'] ?? 'N/A' }}
        </span>
        <div class="action-link">
            View Progress <i class="fas fa-arrow-right"></i>
        </div>
    </a>
    @endisset
@endforeach
