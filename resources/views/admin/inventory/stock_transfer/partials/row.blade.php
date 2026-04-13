<tr>
    <td class="text-center">
        <input type="checkbox" name="inventory_ids[]" value="{{ $row->id }}" class="inventory-checkbox">
    </td>
    <td>
        <div><strong class="text-dark">{{ $row->product_name }}</strong></div>
        <div class="small text-muted">{{ $row->design_number }}</div>
    </td>
    <td>
        <div>{{ $row->size_set_name }}</div>
        <div class="small text-muted">{{ $row->color_name }}</div>
    </td>
    <td>
        @php
            $wh = $row->rack->storeroom->name ?? 'N/A';
            $rk = $row->rack->name ?? 'N/A';
        @endphp
        {{ $wh }} / {{ $rk }}
    </td>
    <td class="text-center font-weight-bold">
        {{ $row->total_boxes }}
    </td>
    <td class="text-center">
        <input type="number" name="transfer_qty[{{ $row->id }}]" value="{{ $row->total_boxes }}" min="1" max="{{ $row->total_boxes }}" class="form-control form-control-sm qty-input" style="width: 80px; margin: 0 auto;">
    </td>
</tr>
