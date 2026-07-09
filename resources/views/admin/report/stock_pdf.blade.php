<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report - {{ $filename }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #333; }
        .meta { margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 20px; font-size: 9px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ strtoupper(str_replace('-', ' ', $filename)) }}</h2>
    </div>

    <div class="meta">
        <strong>Exported At:</strong> {{ $exportedAt->format('d M Y h:i A') }}<br>
        @if(isset($fabric_name))
            <strong>Fabric:</strong> {{ $fabric_name }}
        @endif
    </div>

    @if($level === 'fabrics')
        <table>
            <thead>
                <tr>
                    <th width="5%">Sr</th>
                    <th>Fabric Name</th>
                    <th class="text-right">Total Received</th>
                    <th class="text-right">Total Issued</th>
                    <th class="text-right">Remaining Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $row->name }}</td>
                    <td class="text-right">{{ number_format($row->total_received, 2) }}</td>
                    <td class="text-right">{{ number_format($row->total_issued, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row->total_remaining, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($level === 'warehouses')
        <table>
            <thead>
                <tr>
                    <th width="5%">Sr</th>
                    <th>Warehouse / Slot</th>
                    <th class="text-right">Total Received</th>
                    <th class="text-right">Total Issued</th>
                    <th class="text-right">Remaining Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->master_fabric_warehouse->cutting_master_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($row->total_received, 2) }}</td>
                    <td class="text-right">{{ number_format($row->total_issued, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row->total_remaining, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($level === 'receipts')
        <table>
            <thead>
                <tr>
                    <th width="5%">Sr</th>
                    <th>Date</th>
                    <th>Warehouse</th>
                    <th>Fabric</th>
                    <th>Supplier</th>
                    <th>PO / Shipment</th>
                    <th>Roll No</th>
                    <th class="text-right">Recv Qty</th>
                    <th class="text-right">Rem Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($row->fabric_receipt)->created_at ? $row->fabric_receipt->created_at->format('d-m-Y') : $row->created_at->format('d-m-Y') }}</td>
                    <td>{{ $row->master_fabric_warehouse?->cutting_master_name }}</td>
                    <td class="font-bold">{{ $row->fabric->name ?? ($fabric->name ?? '-') }}</td>
                    <td>{{ $row->fabric_receipt->vendor->name ?? '-' }}</td>
                    <td>{{ $row->purchase_order?->sku ?? '-' }} / {{ $row->shipment_number ?? '-' }}</td>
                    <td>{{ $row->roll_number }}</td>
                    <td class="text-right font-bold">{{ number_format($row->meter, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($row->remaining_quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($level === 'usages')
        <table>
            <thead>
                <tr>
                    <th width="5%">Sr</th>
                    <th>Date</th>
                    <th>Roll No</th>
                    <th>Lot / Order No</th>
                    <th>Fabric</th>
                    <th>Design / Color</th>
                    <th>Stage Unit</th>
                    <th class="text-right">Used Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->created_at->format('d-m-Y') }}</td>
                    <td>{{ $row->roll_no }}</td>
                    <td class="font-bold">{{ $row->lot_no }} / {{ $row->order_no }}</td>
                    <td>{{ $fabric->name ?? '-' }}</td>
                    <td>{{ $row->orderProductSet?->design_number ?? '-' }} / {{ $row->orderProductSet?->colors?->name ?? '-' }}</td>
                    <td>{{ $row->stageMasterUnit?->name ?? '-' }}</td>
                    <td class="text-right font-bold" style="color: #d32f2f;">{{ number_format($row->meter, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif(isset($ledger))
        <table>
            <thead>
                <tr>
                    <th width="5%">Sr</th>
                    <th width="15%">Date</th>
                    <th width="20%">Warehouse</th>
                    <th width="20%">Details</th>
                    <th class="text-right" width="13%">Recv (+)</th>
                    <th class="text-right" width="13%">Issue (-)</th>
                    <th class="text-right" width="14%">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $balance = 0; @endphp
                @foreach($ledger as $index => $row)
                @php 
                    $recv = $row->type == 'receipt' ? $row->quantity : 0;
                    $issue = $row->type == 'issue' ? $row->quantity : 0;
                    $balance += ($recv - $issue);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }}</td>
                    <td>{{ $row->warehouse_name }}</td>
                    <td>{{ $row->reference }}</td>
                    <td class="text-right" style="color: green">{{ $recv > 0 ? number_format($recv, 2) : '-' }}</td>
                    <td class="text-right" style="color: red">{{ $issue > 0 ? number_format($issue, 2) : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($balance, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated by Surgical Jeans ERP - {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
