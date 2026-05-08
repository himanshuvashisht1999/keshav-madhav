<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Production Purchase Order - {{ $po->po_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; margin: 0; padding: 0; }
        .invoice-box { padding: 30px; }
        .invoice-header { border-bottom: 2px solid #007bff; padding-bottom: 15px; margin-bottom: 20px; }
        .company-details h2 { margin: 0; font-weight: bold; color: #007bff; font-size: 24px; }
        .vendor-box, .po-box { background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px solid #dee2e6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background: #007bff; color: #fff; padding: 10px; border: 1px solid #ddd; }
        .table td { padding: 8px; border: 1px solid #ddd; text-align: center; }
        .grand-total { font-weight: bold; background: #f1f1f1; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-primary { color: #007bff; }
        .mb-0 { margin-bottom: 0; }
        .mt-4 { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        {{-- Header --}}
        <table style="width: 100%;" class="invoice-header">
            <tr>
                <td class="company-details">
                    <h2>{{ $general_setting->website_name ?? 'SNAPKID' }}</h2>
                    <p class="mb-0">{{ $general_setting->address ?? '' }}</p>
                    <p class="mb-0">{{ $general_setting->email ?? '' }}</p>
                    <p class="mb-0"><b>Phone:</b> {{ $general_setting->phone ?? '' }}</p>
                </td>
                <td class="text-right">
                    @if($general_setting && $general_setting->logo)
                        {{-- In PDF we often need absolute path or base64. Using path for now --}}
                        <img src="{{ public_path(str_replace(url('/'), '', $general_setting->logo)) }}" height="80" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>

        {{-- Vendor & PO Info --}}
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 50%; padding-right: 10px;">
                    <div class="vendor-box">
                        <h5 class="text-primary" style="margin-top:0; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><b>VENDOR DETAILS</b></h5>
                        <p class="mb-0"><b>Name:</b> 
                            @if($po->vendor)
                                {{ $po->vendor->name }}
                            @elseif($po->customer)
                                {{ $po->customer->name }}
                            @endif
                        </p>
                        <p class="mb-0"><b>Mobile:</b> 
                            @if($po->vendor)
                                {{ $po->vendor->mobile ?? 'N/A' }}
                            @elseif($po->customer)
                                {{ $po->customer->mobile ?? 'N/A' }}
                            @endif
                        </p>
                        <p class="mb-0"><b>Address:</b> 
                            @if($po->vendor)
                                {{ $po->vendor->address ?? 'N/A' }}
                            @elseif($po->customer)
                                {{ $po->customer->address ?? 'N/A' }}
                            @endif
                        </p>
                    </div>
                </td>
                <td style="width: 50%; padding-left: 10px;">
                    <div class="po-box">
                        <h5 class="text-primary text-right" style="margin-top:0; border-bottom: 1px solid #ddd; padding-bottom: 5px;"><b>PO INFO</b></h5>
                        <p class="mb-0 text-right"><b>PO Number:</b> {{ $po->po_number }}</p>
                        <p class="mb-0 text-right"><b>Order No:</b> {{ $po->orderMain->sku ?? 'N/A' }}</p>
                        <p class="mb-0 text-right"><b>Date:</b> {{ $po->created_at->format('j M Y') }}</p>
                        <p class="mb-0 text-right"><b>Delivery Date:</b> {{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : 'N/A' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Items --}}
        <h5 class="mt-4 mb-3 text-primary">Order Items</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Design</th>
                    <th>Product Details</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Total</th>
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
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $item->productSet->design_number ?? 'N/A' }}</strong></td>
                        <td class="text-left">
                            <small>
                                <b>Color:</b> {{ $item->productSet->colors->name ?? 'N/A' }} | <b>Size:</b> {{ $item->productSet->size_set_name ?? 'N/A' }}<br>
                                <b>Fabric:</b> {{ $item->fabric_names }}<br>
                                <b>Pattern:</b> {{ $item->pattern->name ?? '-' }} | <b>Fitting:</b> {{ $item->master_fitting->name ?? '-' }}<br>
                                <b>Belt:</b> {{ $item->belt ?? '-' }}
                            </small>
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->rate, 2) }}</td>
                        <td>{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total">
                    <td colspan="3" class="text-right">Total</td>
                    <td>{{ $totalQty }}</td>
                    <td></td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4">
            <h5 class="text-primary"><b>Remark</b></h5>
            <div style="background:#f1f1f1; padding:10px; border-radius:6px;">
                {{ $po->remark ?? 'N/A' }}
            </div>
        </div>

        {{-- Footer --}}
        <table style="width: 100%; margin-top: 50px;">
            <tr>
                <td>
                    <p><b>Authorized Signature</b></p>
                    <br><br>
                    <p>____________________</p>
                </td>
                <td class="text-right">
                    <p class="text-primary"><b>Thank you for your business!</b></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
