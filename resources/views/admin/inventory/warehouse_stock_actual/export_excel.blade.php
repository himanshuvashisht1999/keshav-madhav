<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warehouse Stock Actual Export</title>
</head>
<body>
    <h2>Warehouse Stock Actual (Physical Inventory) Report</h2>
    <p><strong>Generated On:</strong> {{ $exportedAt->format('d M Y, h:i A') }}</p>
    
    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Design No.</th>
                <th>Size Set</th>
                <th>Location (Warehouse / Rack)</th>
                <th>Actual Boxes</th>
                <th>Actual Quantity (Pcs)</th>
                @if(isset($withPrice) && $withPrice)
                <th>MRP</th>
                <th>Total Price</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalBoxes = 0;
                $grandTotalQty = 0;
                $grandTotalPrice = 0;
            @endphp
            @forelse($data as $index => $row)
                @php
                    $qty = $row->total_boxes * $row->quantity;
                    $grandTotalBoxes += $row->total_boxes;
                    $grandTotalQty += $qty;
                    
                    if (isset($withPrice) && $withPrice) {
                        $variant = \App\Models\ProductionGoodVariant::where('production_goods_id', $row->product_id)
                            ->where('master_size_measurement_id', $row->size_set_id)
                            ->first();
                        $unitPrice = $variant ? (float)$variant->mrp : 0;
                        $totalPrice = $unitPrice * $qty;
                        $grandTotalPrice += $totalPrice;
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ trim(($row->product->series->name ?? '') . ' ' . ($row->product->name_of_garment ?? 'N/A')) }}</td>
                    <td>{{ $row->product->design_number ?? 'N/A' }}</td>
                    <td>{{ $row->sizeSet->name ?? 'N/A' }}</td>
                    <td>
                        {{ $row->rack->storeroom->name ?? 'N/A' }} / {{ $row->rack->name ?? 'N/A' }}
                    </td>
                    <td>{{ $row->total_boxes }}</td>
                    <td>{{ $qty }}</td>
                    @if(isset($withPrice) && $withPrice)
                    <td>{{ $unitPrice }}</td>
                    <td>{{ $totalPrice }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($withPrice) && $withPrice) ? 9 : 7 }}">No actual inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="5">Total</td>
                <td>{{ $grandTotalBoxes }}</td>
                <td>{{ $grandTotalQty }}</td>
                @if(isset($withPrice) && $withPrice)
                <td>-</td>
                <td>{{ $grandTotalPrice }}</td>
                @endif
            </tr>
        </tfoot>
    </table>
</body>
</html>
