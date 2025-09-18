<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">
    <?php

        $general_setting_data = App\Models\GeneralSettings::where('status',1)->first();
    ?>

    <table width="700" align="center" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.1); overflow:hidden;">
        <!-- Header -->
        <tr>
            <td style="background:#2c3e50; padding:20px; color:#fff;">
                <img src="{{$general_setting_data->logo}}" alt="Company Logo" style="height:50px;">
                <h2 style="margin:0; font-size:20px;">Purchase Order #{{$save_purchase->purchase_order_id}}</h2>
            </td>
        </tr>

        <!-- Company & Vendor Info -->
        <tr>
            <td style="padding:20px;">
                <table width="100%">
                    <tr>
                        <td valign="top" width="50%">
                            <h3 style="margin:0; color:#2c3e50;">Company Info</h3>
                            <p style="margin:4px 0;">{{$general_setting_data->website_name}}</p>
                            <p style="margin:4px 0;">{{$general_setting_data->address}}</p>
                            <p style="margin:4px 0;">{{$general_setting_data->email}}</p>
                        </td>
                        <td valign="top" width="50%">
                            <h3 style="margin:0; color:#2c3e50;">Vendor Info</h3>
                            <p style="margin:4px 0;"><strong>{{ $vendor->name }}</strong></p>
                            <p style="margin:4px 0;">{{ $vendor->address }}</p>
                            <p style="margin:4px 0;">{{ $vendor->email }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Order Info -->
        <tr>
            <td style="padding:20px;">
                <h3 style="margin-bottom:10px; color:#2c3e50;">Order Details</h3>
                <table width="100%" border="1" cellspacing="0" cellpadding="8" style="border-collapse: collapse; border-color:#ddd; text-align:left;">
                    <thead style="background:#2c3e50; color:#fff;">
                        <tr>
                            <th>Item</th>
                            <th>Specifications</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($purchaseItems as $item)
                            @php $total = $item->quantity * $item->price; $grandTotal += $total; @endphp
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>
                                    @if($item->types->count())
                                    <ul style="margin:0; padding-left: 15px;">
                                        @foreach($item->types as $type)
                                            <li><strong>{{ $type->master_key }}:</strong> {{ $type->master_value }}</li>
                                        @endforeach
                                    </ul>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>₹ {{ number_format($item->price,2) }}</td>
                                <td>₹ {{ number_format($total,2) }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight:bold;">
                            <td colspan="3" align="right">Grand Total</td>
                            <td>₹ {{ number_format($grandTotal,2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f4f6f8; padding:20px; text-align:center; font-size:12px; color:#888;">
                <p style="margin:0;">Thank you for doing business with us.</p>
                <p style="margin:0;">© 2025 {{$general_setting_data->website_name}}</p>
            </td>
        </tr>
    </table>

</body>
</html>
