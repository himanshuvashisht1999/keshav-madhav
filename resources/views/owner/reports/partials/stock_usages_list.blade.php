{{-- Fabric stock, level 4 (usages of one fabric): one <tr> per movement out (also returned by ajax "Load more") --}}
@php
    $typeBadge = [
        'Cutting (Production)' => ['Cutting', 'green'],
        'Transfer Out' => ['Transfer out', 'grey'],
        'Stock Disposal' => ['Disposal', 'red'],
        'Vendor Return' => ['Vendor return', 'yellow'],
        'Direct Sale' => ['Sale', 'yellow'],
    ];
@endphp
@foreach($data as $row)
    @php
        [$label, $tone] = $typeBadge[$row->type_badge ?? ''] ?? [($row->type_badge ?? '-'), 'grey'];
        $date = $row->created_at instanceof \Carbon\Carbon ? $row->created_at : ($row->created_at ? \Carbon\Carbon::parse($row->created_at) : null);
    @endphp
    <tr>
        <td>{{ $date ? $date->format('d M Y') : '-' }}</td>
        <td><span class="ow-badge {{ $tone }}">{{ $label }}</span></td>
        <td>{{ $row->roll_no ?: '-' }}</td>
        <td>{{ $row->lot_no ?: '-' }}</td>
        <td>{{ $row->order_no ?: '-' }}</td>
        <td class="wrap">{{ $row->orderProductSet?->design_number ?? '-' }}</td>
        <td class="wrap">{{ $row->orderProductSet?->colors?->name ?? '-' }}</td>
        <td>{{ $row->stageMasterUnit?->name ?? '-' }}</td>
        <td class="num"><strong>{{ number_format($row->meter, 2) }}</strong></td>
    </tr>
@endforeach
