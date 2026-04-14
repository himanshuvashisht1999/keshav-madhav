<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size:16px;font-weight:bold">
                Fabric Ledger Timeline Report : {{ $fabric_name }}
            </th>
        </tr>
        <tr>
            <td colspan="8">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Date</th>
            <th>Type</th>
            <th>Roll No</th>
            <th>Shipment & PO</th>
            <th>Supplier / Usage Reference</th>
            <th>Inwards (Receive)</th>
            <th>Outwards (Issue)</th>
            <th>Balance Qty</th>
        </tr>
    </thead>

    <tbody>
        @forelse($ledger as $l)
        <tr>
            <td>{{ \Carbon\Carbon::parse($l['date'])->format('d-m-Y H:i') }}</td>
            <td>{{ $l['type'] }}</td>
            <td>{{ $l['roll_number'] }}</td>
            <td>
                @if($l['type'] == 'Receipt')
                    Shipment: {{ $l['shipment_no'] }} / PO: {{ $l['po_number'] }}
                @else
                    -
                @endif
            </td>
            <td>{{ strip_tags($l['reference']) }}</td>
            <td>{{ $l['in'] > 0 ? $l['in'] : '' }}</td>
            <td>{{ $l['out'] > 0 ? $l['out'] : '' }}</td>
            <td>{{ $l['balance'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" align="center">No chronological ledger transactions found for this fabric.</td>
        </tr>
        @endforelse
    </tbody>
</table>
