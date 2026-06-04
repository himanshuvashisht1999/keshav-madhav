<!DOCTYPE html>
<html>
<head>
    <title>Product Master Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        h2 { text-align: center; margin-bottom: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Product Master Report</h2>
    <p class="text-center">Exported At: {{ $exportedAt->format('d-m-Y H:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Design Number</th>
                <th>Brand</th>
                <th>Product Name</th>
                <th>Fitting</th>
                <th>Pattern</th>
                <th>Nature</th>
                <th>Fabric Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if($data->count() > 0)
                @foreach($data as $key => $row)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $row->design_number }}</td>
                        <td>{{ $row->brand ? $row->brand->name : '-' }}</td>
                        <td>{{ trim(($row->series ? $row->series->name : '') . ' ' . $row->name_of_garment) }}</td>
                        <td>{{ $row->fitting ? $row->fitting->name : '-' }}</td>
                        <td>{{ $row->pattern ? $row->pattern->name : '-' }}</td>
                        <td>{{ $row->productNature ? $row->productNature->name : '-' }}</td>
                        <td>{{ $row->fabricType ? $row->fabricType->name : '-' }}</td>
                        <td>{{ $row->status == 1 ? 'Published' : 'Pending' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="text-center">No records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
