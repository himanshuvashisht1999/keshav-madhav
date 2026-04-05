<!DOCTYPE html>
<html>
<head>
    <title>Stock Transfer Slip - {{ $transfer->transfer_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11pt; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #555; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 20pt; color: #444; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .label { font-weight: bold; width: 30%; color: #666; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th { background: #f2f2f2; border: 1px solid #ddd; padding: 10px 8px; text-align: left; text-transform: uppercase; font-size: 9pt; }
        .items-table td { border: 1px solid #ddd; padding: 10px 8px; font-size: 10pt; vertical-align: top; }
        .footer { margin-top: 50px; }
        .signature-section { width: 100%; margin-top: 50px; }
        .signature-box { width: 45%; display: inline-block; text-align: center; border-top: 1px solid #888; padding-top: 5px; font-size: 10pt; color: #555; }
        .right { text-align: right; }
        .mt-4 { margin-top: 20px; }
        .gray { color: #888; }
        .total-row { background: #fafafa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>STOCK TRANSFER SLIP</h1>
        <div class="gray mt-4">Keshav Madhav Warehouse Management System</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Transfer No:</td>
            <td><strong>{{ $transfer->transfer_no }}</strong></td>
            <td class="label">Date:</td>
            <td class="right">{{ $transfer->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Destination Storeroom:</td>
            <td>{{ $transfer->toStoreroom->name ?? 'N/A' }}</td>
            <td class="label">Destination Rack:</td>
            <td class="right">{{ $transfer->toRack->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Transferred By:</td>
            <td>{{ $transfer->user->name ?? 'System' }}</td>
            <td class="label">Status:</td>
            <td class="right">COMPLETED</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="40px">#</th>
                <th>Item Description</th>
                <th width="100px" style="text-align: center;">Total Boxes</th>
                <th width="100px" style="text-align: center;">Total Qty</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalBoxes = 0;
                $totalQty = 0;
            @endphp
            @foreach($items as $index => $item)
                @php
                    $prod = \App\Models\ProductionGoods::with('series')->find($item->product_id);
                    $color = \App\Models\MasterColor::find($item->color_id);
                    $sizeSet = \App\Models\MasterSizeMeasurement::find($item->size_set_id);
                    
                    $nameText = ($prod->series->name ?? '') . ' ' . ($prod->name_of_garment ?? '');
                    $designNo = $prod->design_number ?? '';
                    
                    $totalBoxes += $item->box_count;
                    $totalQty += $item->total_qty;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $designNo }} - {{ $nameText }}</strong><br>
                        <span class="gray" style="font-size: 9pt;">Color: {{ $color->name ?? 'N/A' }} | Size Set: {{ $sizeSet->name ?? 'N/A' }}</span>
                    </td>
                    <td style="text-align: center;">{{ $item->box_count }}</td>
                    <td style="text-align: center;">{{ number_format($item->total_qty) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align: right; padding-right: 15px;">TOTAL:</td>
                <td style="text-align: center;">{{ $totalBoxes }}</td>
                <td style="text-align: center;">{{ number_format($totalQty) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($transfer->notes)
        <div class="mt-4">
            <p><strong>Notes:</strong><br><span class="gray">{{ $transfer->notes }}</span></p>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-box" style="float: left;">
            Dispatched By (Sign)
        </div>
        <div class="signature-box" style="float: right;">
            Received By (Sign)
        </div>
    </div>
</body>
</html>
