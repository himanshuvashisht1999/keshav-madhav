<tr>
    <td class="text-center text-muted">{{ $index }}</td>
    <td>{{ trim($row->product_name) ?: $row->design_number }}</td>
    <td>{{ $row->design_number }}</td>
    <td>{{ $row->size_set_name }}</td>
    <td>{{ $row->fitting_name }}</td>
    <td>{{ $row->pattern_name }}</td>
    <td>₹{{ number_format($row->mrp ?? 0, 2) }}</td>
    <td class="text-center font-weight-bold text-success">{{ $row->total_boxes }}</td>
    <td class="text-center font-weight-bold text-primary">
        @php
            echo (int)$row->total_order;
        @endphp
    </td>
    <td class="text-center">
        @php
            $params = [
                'product_id' => $row->product_id,
                'size_set_id' => $row->size_set_id,
                'fitting_id' => $row->fitting_id,
                'pattern_id' => $row->pattern_id,
            ];
        @endphp
        <a href="{{ route('admin.inventory.show', $params) }}" class="btn btn-primary btn-sm btn-icon" title="View Details">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
