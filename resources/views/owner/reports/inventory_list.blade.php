{{-- Ready stock: one <tr> per product + size set + colour (also returned by ajax "Load more") --}}
@foreach($rows as $row)
    <tr>
        <td>{{ $row->design_number ?: '-' }}</td>
        <td class="wrap">{{ $row->product_name ?: '-' }}</td>
        <td>{{ $row->size_set_name ?: '-' }}</td>
        <td>{{ $row->color_name ?: '-' }}</td>
        <td>{{ $row->fitting_name ?: '-' }}</td>
        <td>{{ $row->pattern_name ?: '-' }}</td>
        <td class="num">{{ $row->mrp !== null ? number_format($row->mrp, 2) : '-' }}</td>
        <td class="num"><strong>{{ number_format($row->total_boxes) }}</strong></td>
        <td class="num">{{ number_format($row->pieces) }}</td>
        <td class="num">{{ (int) $row->total_order > 0 ? number_format($row->total_order) : '-' }}</td>
    </tr>
@endforeach
