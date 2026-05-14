<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Voucher - {{ $voucher->voucher_no }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .voucher-title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .company-name { font-size: 18px; margin-top: 5px; }
        
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { padding: 5px 0; }
        .label { font-weight: bold; color: #666; width: 150px; }
        .value { font-weight: bold; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #f2f2f2; border: 1px solid #ccc; padding: 10px; text-align: left; font-size: 12px; }
        .items-table td { border: 1px solid #ccc; padding: 10px; font-size: 12px; }
        .text-right { text-align: right; }
        
        .totals-row td { background: #f9f9f9; font-weight: bold; border-top: 2px solid #444; }
        
        .narration-section { margin-bottom: 40px; }
        .narration-label { font-weight: bold; margin-bottom: 5px; font-size: 13px; }
        .narration-text { border: 1px solid #eee; padding: 15px; background: #fafafa; font-style: italic; font-size: 12px; }
        
        .footer-table { width: 100%; margin-top: 60px; }
        .signature-box { border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 10px; font-size: 12px; }
        .signature-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="voucher-title">Journal Voucher</div>
        <div class="company-name">KESHAV MADHAV FASHION</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Voucher No:</td>
            <td class="value">{{ $voucher->voucher_no }}</td>
            <td class="label" style="text-align: right;">Date:</td>
            <td class="value" style="text-align: right;">{{ date('d-m-Y', strtotime($voucher->date)) }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Master Type</th>
                <th>Account / Party Name</th>
                <th class="text-right">Debit (DR)</th>
                <th class="text-right">Credit (CR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voucher->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->master_name }}</td>
                <td>{{ $item->party_name }}</td>
                <td class="text-right">{{ $item->type == 'debit' ? number_format($item->amount, 2) : '-' }}</td>
                <td class="text-right">{{ $item->type == 'credit' ? number_format($item->amount, 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right">{{ number_format($voucher->total_debit, 2) }}</td>
                <td class="text-right">{{ number_format($voucher->total_credit, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($voucher->narration)
    <div class="narration-section">
        <div class="narration-label">Narration:</div>
        <div class="narration-text">{{ $voucher->narration }}</div>
    </div>
    @endif

    <table class="footer-table">
        <tr>
            <td>
                <div class="signature-box">
                    <span class="signature-label">Prepared By</span>
                </div>
            </td>
            <td style="text-align: right;">
                <div class="signature-box" style="float: right;">
                    <span class="signature-label">Authorized Signatory</span>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
