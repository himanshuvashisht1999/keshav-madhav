@php
    $general_setting = \App\Models\GeneralSettings::where('status',1)->first();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order #{{ $data->sku }}</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6f8; margin:0; padding:30px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:800px; margin:auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05);">
        <tr>
            <td style="padding:20px; background:#222; color:#fff;">
                <table width="100%">
                    <tr>
                        <td>
                            <h2 style="margin:0; color:#fff;">{{ $general_setting->website_name }}</h2>
                            <p style="margin:5px 0; font-size:13px; color:#ddd;">{{ $general_setting->address }}</p>
                            <p style="margin:0; font-size:13px; color:#ddd;">📧 {{ $general_setting->email }} | ☎ {{ $general_setting->phone }}</p>
                        </td>
                        <td align="right">
                            <img src="https://leapindia.net/assets/images/logo-tm-2-238x238.webp" height="60" alt="Logo" style="border-radius:6px; background:#fff; padding:5px;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Vendor + PO Info --}}
        <tr>
            <td style="padding:25px;">
                <table width="100%">
                    <tr>
                        <td valign="top" style="width:50%; background:#f9fafc; padding:15px; border-radius:8px;">
                            <h3 style="margin:0 0 10px; color:#222;">Vendor Details</h3>
                            <p style="margin:3px 0;"><b>Name:</b> {{ $data->vendor->name ?? 'N/A' }}</p>
                            <p style="margin:3px 0;"><b>Email:</b> {{ $data->vendor->email ?? 'N/A' }}</p>
                            <p style="margin:3px 0;"><b>Phone:</b> {{ $data->vendor->phone ?? 'N/A' }}</p>
                            <p style="margin:3px 0;"><b>Address:</b> {{ $data->vendor->address ?? 'N/A' }}</p>
                        </td>
                        <td valign="top" style="width:50%; background:#f9fafc; padding:15px; border-radius:8px; text-align:right;">
                            <h3 style="margin:0 0 10px; color:#222;">Purchase Order Info</h3>
                            <p style="margin:3px 0;"><b>PO Number:</b> {{ $data->sku }}</p>
                            <p style="margin:3px 0;"><b>PO Date:</b> {{ \Carbon\Carbon::parse($data->date)->format('d-m-Y') }}</p>
                            <p style="margin:3px 0;"><b>Delivery Date:</b> {{ \Carbon\Carbon::parse($data->delivery_date)->format('d-m-Y') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Items Table --}}
        <tr>
            <td style="padding:25px;">
                <h3 style="margin-bottom:15px; color:#222;">Order Items</h3>
                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="background:#222; color:#fff;">
                            <th align="center">#</th>
                            <!-- <th align="center">Item SKU</th> -->
                            <th align="center">Fabric</th>
                            <th align="center">Meter</th>
                            <th align="center">Price</th>
                            <th align="center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($data->items as $index => $item)
                            @php $total = $item->meter * $item->price; $grandTotal += $total; @endphp
                            <tr style="border-bottom:1px solid #ddd; background:#fdfdfd;">
                                <td align="center">{{ $index+1 }}</td>
                                <!-- <td align="center">{{ $item->sku }}</td> -->
                                <td align="center">{{ $item->fabric->sku }}</td>
                                <td align="center">{{ $item->meter }}</td>
                                <td align="center">{{ number_format($item->price, 2) }}</td>
                                <td align="center">{{ number_format($total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f1f1f1; font-weight:bold;">
                            <td colspan="4" align="right">Grand Total</td>
                            <td align="center">{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="padding:25px; text-align:center; font-size:13px; color:#555;">
                <p><b>Authorized Signature:</b> ____________________</p>
                <p style="margin-top:15px; color:#222;"><b>Thank you for your business!</b></p>
            </td>
        </tr>
    </table>

</body>
</html>
