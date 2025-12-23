<table border="1">
    <thead>
        <tr>
            <th colspan="11" style="font-size:16px;font-weight:bold">
                Purchase Order Report
            </th>
        </tr>
        <tr>
            <td colspan="11">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Supplier</th>
            <th>Fabric</th>
            <th>Ordered Qty</th>
            <th>Received Qty</th>
            <th>Remaining Qty</th>
            <th>Receipt Date</th>
            <th>Warehouse</th>
            <th>Roll No</th>
            <th>Received Qty</th>
        </tr>
    </thead>

    <tbody>
        @foreach($orders as $po)
            @foreach($po->items as $item)

                @php
                    $receivedQty = $item->meter - $item->remaining_quantity;
                    $rows = $receipts[$item->id] ?? collect();
                @endphp

                @foreach($rows as $receipt)
                    <tr>
                        <td>{{ $po->sku }}</td>
                        <td>{{ \Carbon\Carbon::parse($po->date)->format('d M Y') }}</td>
                        <td>{{ $po->vendor?->name }}</td>
                        <td>{{ $item->fabric_sku }}</td>

                        <td>{{ number_format($item->meter,2) }}</td>
                        <td>{{ number_format($receivedQty,2) }}</td>
                        <td>{{ number_format($item->remaining_quantity,2) }}</td>

                        <td>{{ \Carbon\Carbon::parse($receipt->created_at)->format('d M Y') }}</td>
                        <td>{{ $receipt->master_fabric_warehouse?->cutting_master_name }}</td>
                        <td>{{ $receipt->roll_number }}</td>
                        <td>{{ number_format($receipt->meter,2) }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endforeach
    </tbody>
</table>
