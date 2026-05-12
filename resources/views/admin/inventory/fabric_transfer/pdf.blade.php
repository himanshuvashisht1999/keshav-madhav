<!DOCTYPE html>
<html>
<head>
    <title>Fabric Transfer - {{ $transfer->transfer_no }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .voucher-container { border: 1px solid #ddd; padding: 20px; min-height: 900px; position: relative; }
        
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; color: #000; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #555; }

        .info-section { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .info-section td { padding: 8px; border: 1px solid #eee; vertical-align: top; }
        .label { font-weight: bold; color: #555; width: 120px; background-color: #f9f9f9; }
        .value { color: #000; font-weight: 500; }

        .items-title { background: #444; color: #fff; padding: 5px 10px; font-size: 12px; margin-bottom: 0; text-transform: uppercase; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 0; }
        .items-table th { background-color: #f2f2f2; border: 1px solid #ccc; padding: 10px 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .items-table td { border: 1px solid #eee; padding: 8px; text-align: left; }
        .items-table tr:nth-child(even) { background-color: #fafafa; }
        
        .total-row { background-color: #f2f2f2 !important; font-weight: bold; }
        .total-row td { border-top: 2px solid #444 !important; }

        .remarks-box { margin-top: 20px; padding: 10px; border: 1px dashed #ccc; background: #fffcf0; }
        .remarks-box strong { display: block; margin-bottom: 5px; color: #666; font-size: 10px; text-transform: uppercase; }

        .signature-section { margin-top: 60px; width: 100%; }
        .signature-box { text-align: center; width: 33.33%; }
        .signature-line { border-top: 1px solid #444; width: 80%; margin: 0 auto 5px; }
        .signature-text { font-size: 10px; text-transform: uppercase; color: #666; font-weight: bold; }

        .footer { position: absolute; bottom: 10px; left: 20px; right: 20px; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="voucher-container">
        <div class="header">
            <h1>Fabric Transfer Voucher</h1>
            <p>Voucher No: <strong>{{ $transfer->transfer_no }}</strong></p>
        </div>

        <table class="info-section">
            <tr>
                <td class="label">Transfer Date</td>
                <td class="value" colspan="3">{{ \Carbon\Carbon::parse($transfer->transfer_date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">From Warehouse</td>
                <td class="value">{{ $transfer->fromWarehouse->cutting_master_name }}</td>
                <td class="label">To Warehouse</td>
                <td class="value">{{ $transfer->toWarehouse->cutting_master_name }}</td>
            </tr>
        </table>

        @if($transfer->remarks)
        <div class="remarks-box">
            <strong>Remarks / Notes:</strong>
            {{ $transfer->remarks }}
        </div>
        @endif

        <div style="margin-top: 25px;">
            <div class="items-title">Transferred Items Details</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">Sr.</th>
                        <th>Fabric Description</th>
                        <th style="width: 120px;">Roll Number</th>
                        <th style="width: 120px; text-align: right;">Quantity (Mtr)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMtr = 0; @endphp
                    @foreach($transfer->items as $index => $item)
                        <tr>
                            <td style="text-align: center;">{{ $index + 1 }}</td>
                            <td>{{ $item->fabric->name }}</td>
                            <td><span style="background:#eee; padding: 2px 4px;">{{ $item->fabricReceiptDetail->roll_number }}</span></td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($item->meter, 2) }} mtr</td>
                        </tr>
                        @php $totalMtr += $item->meter; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Transferred Quantity</td>
                        <td style="text-align: right; font-size: 13px;">{{ number_format($totalMtr, 2) }} mtr</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <table class="signature-section">
            <tr>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-text">Authorized Signature</div>
                </td>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-text">Receiver's Signature</div>
                </td>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-text">Store Keeper</div>
                </td>
            </tr>
        </table>

        <div class="footer">
            This is a computer-generated document. Printed on {{ date('d M Y h:i A') }}
        </div>
    </div>
</body>
</html>
