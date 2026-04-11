<!DOCTYPE html>
<html>
<head>
    <title>Stock Transfer Slip - #{{ $history->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18pt; color: #333; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; width: 25%; color: #555; font-size: 9pt; }
        .comparison-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .comparison-table td { width: 45%; vertical-align: top; }
        .comparison-table .arrow-cell { width: 10%; text-align: center; vertical-align: middle; }
        .card { border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .card-header { font-weight: bold; border-bottom: 1px solid #eee; margin-bottom: 8px; padding-bottom: 5px; text-transform: uppercase; font-size: 9pt; }
        .old-card { border-left: 4px solid #dc3545; background-color: #fff9f9; }
        .new-card { border-left: 4px solid #28a745; background-color: #f9fff9; }
        .detail-row { margin-bottom: 5px; }
        .detail-label { font-size: 8pt; color: #777; display: block; }
        .detail-value { font-weight: bold; font-size: 10pt; }
        .signature-section { width: 100%; margin-top: 40px; }
        .signature-box { width: 45%; display: inline-block; text-align: center; border-top: 1px solid #000; padding-top: 5px; font-size: 9pt; }
        .right { text-align: right; }
        .gray { color: #888; }
        .mt-2 { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>STOCK TRANSFER SLIP</h1>
        <div class="gray mt-2">Keshav Madhav Warehouse Management System</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Log ID:</td>
            <td><strong>#{{ $history->id }}</strong></td>
            <td class="label" style="text-align: right;">Date:</td>
            <td class="right">{{ $history->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Quantity:</td>
            <td><strong>{{ $history->box_quantity }} Boxes</strong></td>
            <td class="label" style="text-align: right;">User:</td>
            <td class="right">{{ $history->user->name ?? 'System' }}</td>
        </tr>
    </table>

    <table class="comparison-table">
        <tr>
            <td>
                <div class="card old-card">
                    <div class="card-header" style="color: #dc3545;">FROM (Source)</div>
                    <div class="detail-row">
                        <span class="detail-label">Warehouse / Rack</span>
                        <span class="detail-value">{{ $history->oldRack->storeroom->name ?? 'N/A' }} / {{ $history->oldRack->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Design & Product</span>
                        <span class="detail-value">{{ $history->oldProduct->design_number ?? 'N/A' }} - {{ ($history->oldProduct->series->name ?? '') . ' ' . ($history->oldProduct->name_of_garment ?? '') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Color / Size Set</span>
                        <span class="detail-value">{{ $history->oldColor->name ?? 'N/A' }} / {{ $history->oldSizeSet->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fitting / Pattern</span>
                        <span class="detail-value">{{ $history->oldFitting->name ?? 'N/A' }} / {{ $history->oldPattern->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
            <td class="arrow-cell">
                <span style="font-size: 20pt; color: #999;">&rarr;</span>
            </td>
            <td>
                <div class="card new-card">
                    <div class="card-header" style="color: #28a745;">TO (Destination)</div>
                    <div class="detail-row">
                        <span class="detail-label">Warehouse / Rack</span>
                        <span class="detail-value">{{ $history->newRack->storeroom->name ?? 'N/A' }} / {{ $history->newRack->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Design & Product</span>
                        <span class="detail-value">{{ $history->newProduct->design_number ?? 'N/A' }} - {{ ($history->newProduct->series->name ?? '') . ' ' . ($history->newProduct->name_of_garment ?? '') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Color / Size Set</span>
                        <span class="detail-value">{{ $history->newColor->name ?? 'N/A' }} / {{ $history->newSizeSet->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fitting / Pattern</span>
                        <span class="detail-value">{{ $history->newFitting->name ?? 'N/A' }} / {{ $history->newPattern->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="signature-section">
        <div class="signature-box" style="float: left;">
            Authorized Signature
        </div>
        <div class="signature-box" style="float: right;">
            Receiver's Signature
        </div>
    </div>

    <div style="margin-top: 50px; font-size: 8pt; color: #aaa; text-align: center;">
        This is a system-generated stock transfer slip. Generated on {{ date('d M Y, h:i A') }}
    </div>
</body>
</html>
