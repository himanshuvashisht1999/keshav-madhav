<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Purchase Order - {{ $po->sku }}</title>
    <style>
        @page {
            margin: 8mm 8mm 8mm 8mm;
            size: A4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #05421c;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .voucher-sheet {
            border: 1px solid #cbd5e1;
            border-top: 4px solid #fcee21;
            border-radius: 4px;
            padding: 12px 14px;
            background: #ffffff;
        }

        .company-header {
            width: 100%;
            border-bottom: 2px solid #05421c;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
        }

        .company-meta {
            font-size: 9.5px;
            color: #05421c;
            line-height: 1.4;
            font-weight: 600;
        }

        .info-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
        }

        .info-card-header {
            background: #edf7e4;
            border-bottom: 1px solid #8bc63e;
            padding: 4px 8px;
            font-size: 9.5px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .info-card-body {
            padding: 6px 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2.5px 0;
            font-size: 9.5px;
            vertical-align: top;
        }

        .info-label {
            width: 32%;
            font-weight: 700;
            color: #074e22;
            text-transform: uppercase;
            font-size: 8.5px;
        }

        .info-val {
            color: #05421c;
            font-weight: bold;
        }

        .sku-badge {
            display: inline-block;
            background: #fcee21;
            color: #05421c;
            border: 1px solid #e2d514;
            padding: 1px 6px;
            border-radius: 3px;
            font-weight: 800;
            font-size: 9.5px;
        }

        .section-title {
            font-size: 10.5px;
            font-weight: 800;
            color: #05421c;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 10px 0 5px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .items-table th {
            background: #edf7e4;
            color: #05421c;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 6px;
            border-top: 1px solid #05421c;
            border-bottom: 2px solid #05421c;
            border-right: 1px solid #cbd5e1;
            border-left: 1px solid #cbd5e1;
            text-align: center;
        }

        .items-table td {
            padding: 5px 6px;
            font-size: 9.5px;
            color: #05421c;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-weight: 600;
        }

        .items-table tfoot td {
            background: #edf7e4;
            color: #05421c;
            font-weight: 800;
            font-size: 10px;
            border-top: 2px solid #05421c;
            border-bottom: 2px solid #05421c;
            padding: 6px;
        }

        .signoff-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }

        .signoff-box {
            border-top: 1px solid #05421c;
            display: inline-block;
            width: 160px;
            text-align: center;
            padding-top: 4px;
            font-size: 9px;
            font-weight: bold;
            color: #05421c;
            text-transform: uppercase;
        }

        .print-date {
            text-align: center;
            font-size: 8px;
            color: #457855;
            margin-top: 15px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
    <div class="voucher-sheet">
        <!-- Header -->
        <table class="company-header" cellpadding="0" cellspacing="0">
            <tr>
                <td width="70%" valign="top">
                    <div class="company-name">SNAPKID</div>
                    <div class="company-meta">
                        <div>Production Purchase Order</div>
                    </div>
                </td>
                <td width="30%" align="right" valign="top">
                    <span class="sku-badge">{{ $po->sku }}</span>
                </td>
            </tr>
        </table>

        <!-- PO Assigned To & Information -->
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px;">
            <tr>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">PO Assigned To</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">Entity Name</td>
                                    <td class="info-val">: {{ $po->vendor_id ? ($po->vendor->name ?? '-') : ($po->customer->name ?? '-') }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Type</td>
                                    <td class="info-val">: {{ $po->vendor_id ? 'Vendor' : 'Customer' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Delivery Date</td>
                                    <td class="info-val">: {{ $po->till_allowed_time ? date('d-m-Y', strtotime($po->till_allowed_time)) : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                <td width="3%"></td>
                <td width="48.5%" valign="top">
                    <div class="info-card">
                        <div class="info-card-header">PO Details</div>
                        <div class="info-card-body">
                            <table class="info-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="info-label">PO SKU</td>
                                    <td class="info-val">: <span class="sku-badge">{{ $po->sku }}</span></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Date</td>
                                    <td class="info-val">: {{ date('d-m-Y', strtotime($po->created_at)) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Product Table -->
        <div class="section-title">Product & Quantity Details</div>
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>DESIGN NO</th>
                    <th>COLOUR</th>
                    <th>SIZE</th>
                    <th>PCS IN SET</th>
                    <th>QUANTITY</th>
                    <th>RATE</th>
                    <th>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center font-weight-bold">{{ $po->productSet->design_number ?? '-' }}</td>
                    <td class="text-center">{{ $po->productSet->colors->name ?? '-' }}</td>
                    <td class="text-center">{{ $po->productSet->set_size ?? '-' }}</td>
                    <td class="text-center">{{ $po->productSet->no_of_pcs ?? 0 }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $po->quantity }}</td>
                    <td class="text-right">{{ number_format($po->rate, 2) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($po->quantity * $po->rate, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">TOTAL:</td>
                    <td class="text-center">{{ $po->quantity }} Pcs</td>
                    <td></td>
                    <td class="text-right">Rs. {{ number_format($po->quantity * $po->rate, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if(!empty($po->remarks))
            <div style="margin-top: 10px; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 4px; background: #ffffff;">
                <b style="font-size: 8.5px; text-transform: uppercase; color: #05421c;">Remarks:</b>
                <span style="font-size: 9px; color: #05421c;">{{ $po->remarks }}</span>
            </div>
        @endif

        <!-- Sign-off Block -->
        <table class="signoff-table">
            <tr>
                <td width="33%" align="center">
                    <div class="signoff-box">Prepared By</div>
                </td>
                <td width="34%" align="center">
                    <div class="signoff-box">Receiver Sign</div>
                </td>
                <td width="33%" align="center">
                    <div class="signoff-box">Authorized Sign</div>
                </td>
            </tr>
        </table>

        <div class="print-date">
            This is a system generated production purchase order.
        </div>
    </div>
</body>
</html>
