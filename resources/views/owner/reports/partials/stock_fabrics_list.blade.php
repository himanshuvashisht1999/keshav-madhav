{{-- Fabric stock, level 1: one <tr> per fabric (also returned by ajax "Load more") --}}
@foreach($data as $row)
    <tr>
        <td><a href="{{ route('owner.stock', ['fabric_id' => $row->id]) }}">{{ $row->name }}</a></td>
        <td class="wrap">{{ $row->vendor_name ?: '-' }}</td>
        <td class="num">{{ number_format($row->total_received, 2) }}</td>
        <td class="num">{{ number_format($row->total_issued, 2) }}</td>
        <td class="num"><strong>{{ number_format($row->total_remaining, 2) }}</strong></td>
    </tr>
@endforeach
