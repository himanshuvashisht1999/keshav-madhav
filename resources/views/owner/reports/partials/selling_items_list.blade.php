{{-- Rows of the Selling Items table (owner/reports/selling_items.blade.php) --}}
@forelse($items as $item)
    <tr>
        <td>{{ $item->design_number }}</td>
        <td class="num">{{ $item->rank }}</td>
        <td class="num">{{ number_format($item->sales_qty) }}</td>
        <td class="num">{{ number_format($item->agent_qty) }}</td>
        <td class="num"><b>{{ number_format($item->total_qty) }}</b></td>
    </tr>
@empty
    <tr><td colspan="5" class="ow-empty">No designs found for these filters</td></tr>
@endforelse
