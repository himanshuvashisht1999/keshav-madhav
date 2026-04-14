<table border="1">
    <thead>
        <tr>
            <th colspan="5" style="font-size:16px;font-weight:bold">
                Fabric Stock by Warehouse : {{ $fabric->name }}
            </th>
        </tr>
        <tr>
            <td colspan="5">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Sr No</th>
            <th>Warehouse</th>
            <th align="right">Total Received</th>
            <th align="right">Total Issued</th>
            <th align="right">Remaining Qty</th>
        </tr>
    </thead>
    <tbody>
        @php $sr = 1; @endphp
        @foreach($data as $row)
        <tr>
            <td>{{ $sr++ }}</td>
            <td>{{ $row->master_fabric_warehouse?->cutting_master_name ?? 'Unknown' }}</td>
            <td align="right">{{ number_format($row->total_received, 2) }}</td>
            <td align="right">{{ number_format($row->total_issued, 2) }}</td>
            <td align="right" style="font-weight:bold">{{ number_format($row->total_remaining, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
