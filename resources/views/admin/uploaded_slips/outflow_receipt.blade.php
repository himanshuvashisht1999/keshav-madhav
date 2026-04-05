<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt #{{ $outflow->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .receipt-card { border: 2px solid #555; padding: 15px; border-radius: 8px; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: bold; margin: 0; }
        .receipt-title { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-top: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px 5px; border-bottom: 1px solid #f9f9f9; vertical-align: top; }
        .label { color: #888; font-size: 10px; text-transform: uppercase; display: block; font-weight: bold; }
        .value { font-size: 13px; font-weight: bold; color: #000; }
        .type-badge { display: inline-block; padding: 3px 10px; background: #000; color: #fff; border-radius: 3px; font-size: 11px; font-weight: 900; }
        .qty-circle { width: 50px; height: 50px; border: 3px solid #000; border-radius: 50%; line-height: 50px; text-align: center; font-size: 24px; font-weight: 900; margin: 0 auto; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .footer { margin-top: 30px; }
        .sig-row { width: 100%; margin-top: 40px; }
        .sig-box { border-top: 1px solid #000; width: 40%; text-align: center; padding-top: 5px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="receipt-card">
        <div class="header">
            <div class="company-name">{{ $general_setting->website_name ?? 'SNAPKID' }}</div>
            <div style="font-size: 10px; color: #666;">{{ $general_setting->address ?? '' }}</div>
            <div class="receipt-title">Transaction Acknowledgement</div>
        </div>

        <table class="info-table">
            <tr>
                <td width="50%">
                    <span class="label">Slip ID Ref</span>
                    <span class="value">#{{ $outflow->slip_id }}</span>
                </td>
                <td width="50%" align="right">
                    <span class="label">Date & Time</span>
                    <span class="value">{{ $outflow->created_at->format('d M, Y - h:i A') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Design Number</span>
                    <span class="value">{{ $outflow->product->design_number ?? 'N/A' }}</span>
                </td>
                <td align="right">
                    <span class="label">Color / Size</span>
                    <span class="value">{{ $outflow->color->name ?? 'N/A' }} | Size: {{ $outflow->size->size ?? 'N/A' }}</span>
                </td>
            </tr>
        </table>

        <div class="summary-box">
            <div style="margin-bottom: 10px;">
                <span class="type-badge">{{ strtoupper($outflow->type) }}</span>
            </div>
            <div class="label">Quantity Accounted</div>
            <div class="qty-circle">{{ $outflow->quantity }}</div>
            <div style="margin-top: 5px; font-weight: bold;">Pieces</div>
        </div>

        <table class="info-table">
            @if($outflow->type == 'debit')
            <tr>
                <td colspan="2">
                    <span class="label">Total Debit Amount</span>
                    <span class="value" style="color: #c53030;">Rs. {{ number_format($outflow->total_amount, 2) }}</span>
                </td>
            </tr>
            @endif
            <tr>
                <td width="50%">
                    @if($outflow->type != 'dead')
                        <span class="label">Responsible Stage</span>
                        <span class="value">{{ $outflow->responsibleStage->name ?? 'Inventory' }}</span>
                    @else
                        &nbsp;
                    @endif
                </td>
                <td width="50%" align="right">
                    <span class="label">Unit / Person</span>
                    <span class="value">{{ $outflow->responsibleUnit->name ?? 'Main Warehouse' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Remarks / Description</span>
                    <span class="value" style="font-size: 11px; color: #444; font-weight: normal; font-style: italic;">
                        {{ $outflow->remarks ?: 'No additional notes provided.' }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="footer">
            <table class="sig-row">
                <tr>
                    <td class="sig-box">Authorised Signatory</td>
                    <td width="20%"></td>
                    <td class="sig-box">Receiving Party</td>
                </tr>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 20px; font-size: 9px; color: #aaa;">
            Reference ID: TXN-{{ str_pad($outflow->id, 6, '0', STR_PAD_LEFT) }} | System Generated Slip
        </div>
    </div>
</body>
</html>
