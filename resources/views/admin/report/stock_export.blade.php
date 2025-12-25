<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="font-size:16px;font-weight:bold">
                Fabric Stock Report
            </th>
        </tr>
        <tr>
            <td colspan="9">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Fabric SKU</th>
            <th>Warehouse</th>
            <th>Total Remaining</th>
            <th>Roll No</th>
            <th>Roll Remaining</th>
            <!-- <th>QR / Barcode No</th> -->
            <th>Shipment No</th>
            <th>Supplier</th>
            <th>Date</th>
            <th>PO Number</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $fabricSku => $rows)
            @foreach($rows as $row)

                @php
                    $key = $fabricSku . '_' . $row->master_fabric_warehouse_id;
                    $rollList = $rolls[$key] ?? collect();
                @endphp

                @foreach($rollList as $roll)
                    <tr>
                        <td>{{ $fabricSku }}</td>
                        <td>{{ $row->master_fabric_warehouse?->cutting_master_name }}</td>
                        <td>{{ number_format($row->total_remaining,2) }}</td>

                        <td>{{ $roll->roll_number }}</td>
                        <td>{{ $roll->remaining_quantity }}</td>
                        <!-- <td>{{ $roll->qrcode_number }}</td> -->
                        <td>{{ $roll->shipment_number ?? '-' }}</td>
                        <td>{{ $roll->fabric_receipt->vendor->name ?? '-' }}</td>
                        <td>
                            {{ optional($roll->fabric_receipt)->created_at?->format('d-m-Y') ?? '-' }}
                        </td>
                        <td>{{ $roll->purchase_order?->sku ?? '-' }}</td>
                    </tr>
                @endforeach

            @endforeach
        @endforeach
    </tbody>
</table>
