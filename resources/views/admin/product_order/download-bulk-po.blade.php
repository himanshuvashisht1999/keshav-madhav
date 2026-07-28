<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Purchase Order - {{ $po->po_number }}</title>
    <style>
        @page { margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; line-height: 1.2; }
        .invoice-box { padding: 5px; }
        .invoice-header { border-bottom: 1.5px solid #007bff; padding-bottom: 5px; margin-bottom: 10px; }
        .company-details h2 { margin: 0; font-weight: bold; color: #007bff; font-size: 18px; }
        .company-details p { margin: 2px 0; font-size: 10px; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .table th { background: #007bff; color: #fff; padding: 6px 4px; border: 1px solid #ddd; text-align: center; font-size: 10px; }
        .table td { padding: 6px 5px; border: 1px solid #ddd; vertical-align: middle; font-size: 10px; }
        
        .vendor-box, .po-box { background: #f9f9f9; padding: 8px; border-radius: 4px; border: 1px solid #dee2e6; }
        .vendor-box p, .po-box p { margin: 2px 0; }
        .box-title { color: #007bff; font-size: 10px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #ddd; margin-bottom: 5px; padding-bottom: 2px; }
        
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-primary { color: #007bff; }
        .mb-0 { margin-bottom: 0; }
        .mt-2 { margin-top: 10px; }
        
        .grand-total { background: #f1f1f1; font-weight: bold; }
        .footer-table { width: 100%; margin-top: 20px; font-size: 10px; }
        
        .design-no { font-weight: bold; color: #007bff; }
        .item-details { color: #555; }
    </style>
</head>
<body>
    <div class="invoice-box">
        {{-- Header --}}
        <table style="width: 100%;" class="invoice-header">
            <tr>
                <td class="company-details" style="vertical-align: top;">
                    <h2>{{ $general_setting->website_name ?? 'SNAPKID' }}</h2>
                    <p>{{ $general_setting->address ?? '' }}</p>
                    <p>{{ $general_setting->email ?? '' }} | <b>Phone:</b> {{ $general_setting->phone ?? '' }}</p>
                </td>
                <td class="text-right" style="vertical-align: top;">
                    @if($general_setting && $general_setting->logo)
                        <img src="{{ public_path(str_replace(url('/'), '', $general_setting->logo)) }}" height="50" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>

        {{-- Vendor & PO Info --}}
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 50%; padding-right: 5px; vertical-align: top;">
                    <div class="vendor-box">
                        <div class="box-title">Vendor Details</div>
                        <p><b>Name:</b> {{ $po->vendor->name ?? ($po->customer->name ?? 'N/A') }}</p>
                        <p><b>Mobile:</b> {{ $po->vendor->mobile ?? ($po->customer->mobile ?? 'N/A') }}</p>
                        <p><b>Address:</b> {{ Str::limit($po->vendor->address ?? ($po->customer->address ?? 'N/A'), 60) }}</p>
                    </div>
                </td>
                <td style="width: 50%; padding-left: 5px; vertical-align: top;">
                    <div class="po-box">
                        <div class="box-title text-right">PO Info</div>
                        <p class="text-right"><b>PO Number:</b> {{ $po->po_number }}</p>
                        <!-- <p class="text-right"><b>Order No:</b> {{ $po->orderMain->sku ?? 'N/A' }}</p> -->
                        <p class="text-right"><b>Date:</b> {{ $po->created_at->format('j M Y') }}</p>
                        <p class="text-right"><b>Delivery Date:</b> {{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : 'N/A' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Items Table --}}
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">Design</th>
                    <th style="width: 45%;">Product Details</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 10%;">Rate</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQty = 0; $totalAmount = 0; @endphp
                @foreach($po->items as $index => $item)
                    @php 
                        $subtotal = $item->quantity * $item->rate;
                        $totalQty += $item->quantity;
                        $totalAmount += $subtotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center"><span class="design-no">{{ $item->productSet->design_number ?? 'N/A' }}</span></td>
                        <td class="text-left">
                            <span class="item-details">
                                @php
                                    $ratioStr = '-';
                                    if(!empty($item->productSet->size_measurement->size_group)) {
                                        $sizes = array_map('trim', explode(',', $item->productSet->size_measurement->size_group));
                                        $counts = array_count_values($sizes);
                                        $parts = [];
                                        foreach($counts as $s => $c) {
                                            $parts[] = $s . ':' . $c;
                                        }
                                        $ratioStr = implode(', ', $parts);
                                    }
                                @endphp
                                <b>Color:</b> {{ $item->productSet->colors->name ?? 'N/A' }} | <b>Size:</b> {{ $item->productSet->size_set_name ?? 'N/A' }} | <b>Ratio:</b> {{ $ratioStr }}<br>
                                <b>Fabric:</b> {{ $item->fabric_names }} | <b>Pattern:</b> {{ $item->pattern->name ?? '-' }}<br>
                                <b>Fitting:</b> {{ $item->master_fitting->name ?? '-' }} | <b>Belt:</b> {{ $item->belt ?? '-' }}
                                @if($item->remarks)
                                    <br><b>Remark:</b> {{ $item->remarks }}
                                @endif
                            </span>
                        </td>
                        <td class="text-center" style="font-weight: bold;">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="3" class="text-right">Grand Total</td>
                    <td class="text-center">{{ $totalQty }}</td>
                    <td></td>
                    <td class="text-right">{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($po->remark)
        <div class="mt-2">
            <p class="mb-0"><b>PO Remark:</b> {{ $po->remark }}</p>
        </div>
        @endif

        {{-- Footer --}}
        <table class="footer-table">
            <tr>
                <td style="width: 50%;">
                    <p><b>Authorized Signature</b></p>
                    <br><br>
                    <p>____________________</p>
                </td>
                <td class="text-right" style="width: 50%;">
                    <p class="text-primary" style="font-size: 11px;"><b>Thank you for your business!</b></p>
                    <p style="color: #999;">Generated on: {{ date('j M Y, h:i A') }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
