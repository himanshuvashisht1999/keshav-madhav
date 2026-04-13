<tr>
    <td class="text-muted text-center">{{ $index }}</td>
    <td>{{ $row->product_name }}</td>
    <td><strong>{{ $row->design_number }}</strong></td>
    <td>{{ $row->size_set_name }}</td>
    <td>{{ $row->color_name }}</td>
    <td>
        @php
            $wh = $row->rack->storeroom->name ?? 'N/A';
            $rk = $row->rack->name ?? 'N/A';
        @endphp
        {{ $wh }} / {{ $rk }}
    </td>
    <td class="text-center">
        <span class="badge badge-info px-2 py-1">{{ $row->total_boxes }}</span>
    </td>
    <td class="text-center">{{ $row->quantity }}</td>
    <td class="text-center">
        <a href="{{ route('admin.inventory.warehouse_stock.show', $row->id) }}" class="btn btn-xs btn-primary shadow-xs mr-1" title="View">
            <i class="fas fa-eye"></i>
        </a>
        <button type="button" class="btn btn-xs btn-danger shadow-xs btn-delete-boxes" title="Delete Boxes" 
            data-id="{{ $row->id }}" 
            data-product-id="{{ $row->product_id }}"
            data-design-no="{{ $row->design_number }}"
            data-color-id="{{ $row->color_id }}"
            data-size-set-id="{{ $row->size_set_id }}"
            data-fitting-id="{{ $row->fitting_id ?? '' }}"
            data-pattern-id="{{ $row->pattern_id ?? '' }}"
            data-available-boxes="{{ $row->total_boxes }}"
            data-rack-id="{{ $row->rack_id }}">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
