<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Summary Export</title>
</head>
<body>
    <h2>Inventory Summary Report</h2>
    <p><strong>Generated On:</strong> {{ $exportedAt->format('d M Y, h:i A') }}</p>
    
    <table border="1">
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
                <th>Total Boxes</th>
                <th>Total Order (Pcs)</th>
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
                    <td>{{ $index + 1 }}</td>
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
                    <td>{{ $row->total_boxes }}</td>
                    <td>{{ $row->total_order }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No inventory found</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align:right">Grand Total:</th>
                <th>{{ $grandTotalBoxes }}</th>
                <th>{{ $grandTotalOrder }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
