<tr>
    <td class="text-center text-muted">{{ $index }}</td>
    <td>{{ trim($row->product_name) ?: $row->design_number }}</td>
    <td>{{ $row->design_number }}</td>
    <td>{{ $row->size_set_name }}</td>
    <td>{{ $row->color_name }}</td>
    <td>{{ $row->fitting_name }}</td>
    <td>{{ $row->pattern_name }}</td>
    <td>₹{{ number_format($row->mrp ?? 0, 2) }}</td>
    <td class="text-center font-weight-bold text-success">{{ $row->total_boxes }}</td>
    <td class="text-center font-weight-bold text-primary">
        @php
            $agentOrderBoxes = \App\Models\AgentOrderItem::whereHas('order', function ($q) {
                $q->where('status', '!=', 'dispatched');
            })
            ->where('design_number', $row->design_number)
            ->where('color_id', $row->color_id)
            ->where('size_set_id', $row->size_set_id)
            ->when($row->fitting_id, function ($q, $val) {
                return $q->where('fitting_id', $val);
            }, function ($q) {
                return $q->whereNull('fitting_id');
            })
            ->when($row->pattern_id, function ($q, $val) {
                return $q->where('pattern_id', $val);
            }, function ($q) {
                return $q->whereNull('pattern_id');
            })
            ->sum('box_qty');
            echo (int)$agentOrderBoxes;
        @endphp
    </td>
    <td class="text-center">
        @php
            $params = [
                'product_id' => $row->product_id,
                'size_set_id' => $row->size_set_id,
                'color_id' => $row->color_id,
                'fitting_id' => $row->fitting_id,
                'pattern_id' => $row->pattern_id,
            ];
        @endphp
        <a href="{{ route('admin.inventory.show', $params) }}" class="btn btn-primary btn-sm btn-icon" title="View Details">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
