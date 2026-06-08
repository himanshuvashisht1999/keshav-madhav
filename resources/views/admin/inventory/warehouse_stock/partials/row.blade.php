<tr>
    <td class="text-muted text-center">{{ $index }}</td>
    <td>{{ $row->product->series->name ?? '' }} {{ $row->product->name_of_garment ?? 'N/A' }}</td>
    <td><strong>{{ $row->product->design_number ?? 'N/A' }}</strong></td>
    <td>{{ $row->sizeSet->name ?? 'N/A' }}</td>
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
        <a href="{{ route('admin.inventory.warehouse_stock.show', [$row->product_id ?? 0, $row->size_set_id ?? 0, $row->rack_id ?? 0]) }}" class="btn btn-xs btn-primary shadow-xs mr-1" title="View">
            <i class="fas fa-eye"></i>
        </a>
    </td>
</tr>
