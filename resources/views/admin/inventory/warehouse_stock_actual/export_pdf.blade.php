<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warehouse Stock Actual Export</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Warehouse Stock Actual (Physical Inventory) Report</h2>
    <p><strong>Generated On:</strong> {{ now()->format('d M Y, h:i A') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Name</th>
                <th>Design No.</th>
                <th>Size Set</th>
                <th>Location (Warehouse / Rack)</th>
                <th class="text-center">Actual Boxes</th>
                <th class="text-center">Actual Quantity (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalBoxes = 0;
                $grandTotalQty = 0;
            @endphp
            @forelse($data as $index => $row)
                @php
                    $qty = $row->total_boxes * $row->quantity;
                    $grandTotalBoxes += $row->total_boxes;
                    $grandTotalQty += $qty;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ trim(($row->product->series->name ?? '') . ' ' . ($row->product->name_of_garment ?? 'N/A')) }}</td>
                    <td>{{ $row->product->design_number ?? 'N/A' }}</td>
                    <td>{{ $row->sizeSet->name ?? 'N/A' }}</td>
                    <td>
                        {{ $row->rack->storeroom->name ?? 'N/A' }} / {{ $row->rack->name ?? 'N/A' }}
                    </td>
                    <td class="text-center">{{ $row->total_boxes }}</td>
                    <td class="text-center">{{ $qty }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No actual inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-center">{{ $grandTotalBoxes }}</td>
                <td class="text-center">{{ $grandTotalQty }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
