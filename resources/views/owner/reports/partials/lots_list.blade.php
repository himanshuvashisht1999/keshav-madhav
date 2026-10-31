{{-- Rows of the owner Lots table (first page and "Load more"). Stage and pieces come from lotFacts. --}}
@foreach($data as $row)
    @isset($row['lot_no'])
        @php
            $stage = $row['last_current_stage'] ?? 'Cutting';
            $isDone = in_array($stage, ['Dispatched', 'Packed (stock)'], true);
            $isLate = ($row['is_delayed'] ?? 'No') === 'Yes';
            $orderDone = !$isDone && isset($completedOrders[$row['order_id']]); // order marked Completed: not in production
            $tone = $isDone ? 'green' : ($orderDone ? 'grey' : ($isLate ? 'red' : 'yellow'));
        @endphp
        <tr>
            <td><a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}">{{ $row['lot_no'] }}</a></td>
            <td>{{ $row['order_no'] ?: '-' }}</td>
            <td>{{ $row['customer_name'] ?: '-' }}</td>
            <td>{{ $row['season'] ?? '-' }}</td>
            <td>{{ $row['date'] }}</td>
            <td>
                <span class="ow-badge {{ $tone }}">{{ $stage }}</span>
                @if($orderDone)
                    <span class="ow-badge grey">Order completed</span>
                @endif
                @if($isLate && !$orderDone) {{-- a lot of a Completed order is not in production, so not delayed --}}
                    <span class="ow-badge red" title="Planned to finish this stage by {{ $row['allowed_till'] ? \Carbon\Carbon::parse($row['allowed_till'])->format('d M Y') : '-' }}">Delayed</span>
                @endif
            </td>
            <td class="num">{{ number_format($row['lot_quantity'] ?? 0) }}</td>
            <td class="num">{{ number_format($row['packed'] ?? 0) }}</td>
            <td class="num">{{ number_format($row['dispatched'] ?? 0) }}</td>
        </tr>
    @endisset
@endforeach
