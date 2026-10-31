{{-- Rows of the owner WIP Complete table (first page and "Load more"). Stage cells carry In / Done / WIP; the page shows one of them. --}}
@foreach($data as $row)
<tr>
<td><a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}">{{ $row['lot_no'] }}</a></td>
<td title="Order date {{ $row['order_date'] ? $row['order_date']->format('d M Y') : '-' }}">{{ $row['order_no'] ?: '-' }}</td>
@if($showCustomer)<td>{{ $row['customer'] ?: '-' }}</td>@endif
<td>{{ $row['design'] ?: '-' }}</td>
<td class="num">{{ number_format($row['pieces']) }}</td>
@foreach($stages as $stage)
@php $c = $row['cells'][(int) $stage->id] ?? null; @endphp
@if($c)
<td class="num wip-c" data-in="{{ $c['in'] }}" data-done="{{ $c['done'] }}" data-wip="{{ $c['wip'] }}" title="{{ $stage->name }}: In {{ number_format($c['in']) }} | Done {{ number_format($c['done']) }} | WIP {{ number_format($c['wip']) }}">@if($c['wip'] > 0)<span class="ow-badge yellow">{{ number_format($c['wip']) }}</span>@else<span class="text-muted">0</span>@endif</td>
@else
<td class="num text-muted">-</td>
@endif
@endforeach
<td class="num">{{ number_format($row['packed']) }}</td>
<td class="num">{{ number_format($row['dispatched']) }}</td>
<td><span class="ow-badge {{ $row['status'] === 'done' ? 'green' : 'yellow' }}">{{ $row['stage_name'] }}</span>@if($row['status'] === 'late') <span class="ow-badge red" title="Planned to finish {{ $row['stage_name'] }} by {{ $row['allowed_till'] ? \Carbon\Carbon::parse($row['allowed_till'])->format('d M Y') : '-' }}">Late</span>@endif</td>
</tr>
@endforeach
