<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Summary Export</title>
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
    <h2>Inventory Summary Report</h2>
    <p><strong>Generated On:</strong> {{ now()->format('d M Y, h:i A') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Design No.</th>
                <th>Product Name</th>
                <th>Series</th>
                <th>Size Set</th>
                <th>Fitting</th>
                <th>Pattern</th>
                <th>MRP</th>
                <th class="text-center">Total Boxes</th>
                <th class="text-center">Total Order (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalBoxes = 0;
                $grandTotalOrder = 0;
            @endphp
            @forelse($data as $index => $row)
                @php
                    $grandTotalBoxes += (int) $row->total_boxes;
                    $grandTotalOrder += (int) $row->total_order;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->design_number ?? 'N/A' }}</td>
                    <td>{{ $row->product_name ?? 'N/A' }}</td>
                    <td>{{ $row->series_name ?? 'N/A' }}</td>
                    @if(isset($row->color_name))
                        <td>{{ $row->size_set_name ?? 'N/A' }} - {{ $row->color_name }}</td>
                    @else
                        <td>{{ $row->size_set_name ?? 'N/A' }}</td>
                    @endif
                    <td>{{ $row->fitting_name ?? 'N/A' }}</td>
                    <td>{{ $row->pattern_name ?? 'N/A' }}</td>
                    <td>{{ $row->mrp ?? '0' }}</td>
                    <td class="text-center">{{ $row->total_boxes }}</td>
                    <td class="text-center">{{ $row->total_order }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-right">Grand Total:</th>
                <th class="text-center">{{ $grandTotalBoxes }}</th>
                <th class="text-center">{{ $grandTotalOrder }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
