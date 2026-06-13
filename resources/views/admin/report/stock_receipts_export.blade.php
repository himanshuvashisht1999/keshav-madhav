<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size:16px;font-weight:bold">
                Fabric Shipments : {{ $fabric->name }}
            </th>
        </tr>
        <tr>
            <td colspan="8">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Date</th>
            <th>Warehouse</th>
            <th>Supplier</th>
            <th>Bill No</th>
            <th>Shipment No</th>
            <th>Roll No</th>
            <th align="right">Received Qty</th>
            <th align="right">Returned Qty</th>
            <th align="right">Remaining Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
        @php $returnedQty = $row->returns->sum('return_meter'); @endphp
        <tr>
            <td>{{ $row->created_at->format('d M Y') }}</td>
            <td>{{ $row->master_fabric_warehouse?->cutting_master_name }}</td>
            <td>{{ $row->fabric_receipt->vendor->name ?? '-' }}</td>
            <td>{{ $row->fabric_receipt->bill_no ?? '-' }}</td>
            <td>{{ $row->shipment_number ?? '-' }}</td>
            <td>{{ $row->roll_number }}</td>
            <td align="right">{{ number_format($row->meter, 2) }}</td>
            <td align="right">{{ number_format($returnedQty, 2) }}</td>
            <td align="right">{{ number_format($row->remaining_quantity, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
