{{--
    Fabric stock, level 3 (shipments of one fabric): one <tr> per roll (also returned by ajax "Load more").
    With a warehouse filter, a roll received there but moved away shows 0 in stock here (as on the admin page)
    with a "Moved to X" tag, so the In stock column adds up to what the warehouse holds now.
--}}
@php
    $whSet = array_map('intval', array_filter((array) request('warehouse_id'), fn($v) => $v !== null && $v !== ''));
@endphp
@foreach($data as $row)
    @php
        $receipt = $row->fabric_receipt;
        $receivedAt = $receipt?->cutting_master?->cutting_master_name ?? ($row->master_fabric_warehouse?->cutting_master_name ?? '-');
        $nowAt = $row->master_fabric_warehouse?->cutting_master_name ?? '-';
        $movedOut = !empty($whSet) && $receipt
            && in_array((int) $receipt->master_fabric_warehouse_id, $whSet, true)
            && !in_array((int) $row->master_fabric_warehouse_id, $whSet, true);
        $returned = $row->returns ? (float) $row->returns->sum('return_meter') : 0;
        $inStock = $movedOut ? 0 : (float) $row->remaining_quantity;
        $rate = (float) $row->price_per_meter;
    @endphp
    <tr>
        <td><a href="{{ route('owner.report.stock.rolls.tracking', ['frd_id' => $row->id]) }}">{{ $row->roll_number ?: '-' }}</a></td>
        <td>{{ ($receipt?->created_at ?? $row->created_at)?->format('d M Y') }}</td>
        <td>{{ $receivedAt }}</td>
        <td>
            {{ $nowAt }}
            @if($movedOut)
                <span class="ow-badge yellow">Moved to {{ $nowAt }}</span>
            @endif
        </td>
        <td class="wrap">{{ $receipt?->vendor?->name ?? '-' }}</td>
        <td>{{ $receipt?->bill_no ?: '-' }}</td>
        <td>{{ $row->shipment_number ?: '-' }}</td>
        <td class="num">{{ number_format($rate, 2) }}</td>
        <td class="num">{{ number_format($rate * (float) $row->meter, 2) }}</td>
        <td class="num">{{ number_format($row->meter, 2) }}</td>
        <td class="num">{{ $returned > 0 ? number_format($returned, 2) : '-' }}</td>
        <td class="num"><strong>{{ number_format($inStock, 2) }}</strong></td>
    </tr>
@endforeach
