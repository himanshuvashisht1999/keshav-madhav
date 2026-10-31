{{-- Rolls list: one <tr> per roll (also returned by ajax "Load more"). The roll link tracks this exact roll row (frd_id). --}}
@foreach($rolls as $r)
    @php $issued = (float) $r->meter - (float) $r->remaining_quantity; @endphp
    <tr>
        <td><a href="{{ route('owner.report.stock.rolls.tracking', ['frd_id' => $r->id]) }}">{{ $r->roll_number ?: '-' }}</a></td>
        <td class="wrap">{{ $r->fabric->name ?? '-' }}</td>
        <td>{{ $r->master_fabric_warehouse?->cutting_master_name ?? '-' }}</td>
        <td class="wrap">{{ $r->fabric_receipt?->vendor?->name ?? '-' }}</td>
        <td>{{ $r->purchase_order?->sku ?? '-' }}</td>
        <td>{{ $r->shipment_number ?: '-' }}</td>
        <td class="num">{{ number_format($r->meter, 2) }}</td>
        <td class="num">{{ number_format($issued, 2) }}</td>
        <td class="num">
            @if((float) $r->remaining_quantity > 0)
                <strong>{{ number_format($r->remaining_quantity, 2) }}</strong>
            @else
                <span class="ow-badge grey">0.00</span>
            @endif
        </td>
    </tr>
@endforeach
