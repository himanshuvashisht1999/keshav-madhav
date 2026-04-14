<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size:16px;font-weight:bold">
                Fabric Usages : {{ $fabric->name }}
            </th>
        </tr>
        <tr>
            <td colspan="8">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Date</th>
            <th>Roll No</th>
            <th>Lot No</th>
            <th>Order No</th>
            <th>Design</th>
            <th>Color</th>
            <th>Stage Unit</th>
            <th align="right">Used Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->created_at->format('d M Y') }}</td>
            <td>{{ $row->roll_no }}</td>
            <td style="font-weight:bold">{{ $row->lot_no }}</td>
            <td>{{ $row->order_no }}</td>
            <td>{{ $row->orderProductSet?->design_number ?? '-' }}</td>
            <td>{{ $row->orderProductSet?->colors?->name ?? '-' }}</td>
            <td>{{ $row->stageMasterUnit?->name ?? '-' }}</td>
            <td align="right">{{ number_format($row->meter, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
