<table border="1">
    <thead>
        <tr>
            <th colspan="8" style="font-size:16px;font-weight:bold">
                Order Tracking Report
            </th>
        </tr>
        <tr>
            <td colspan="8">
                Exported At: {{ $exportedAt->format('d M Y h:i A') }}
            </td>
        </tr>
        <tr style="background:#eee;font-weight:bold">
            <th>Order No</th>
            <th>Customer</th>
            <th>Total Pcs</th>
            <th>Promised Delivery</th>
            <th>Lot No</th>
            <th>Pcs / Lot</th>
            <th>Delay</th>
            <th>Expected Delivery</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $orderNo => $lots)

            @php
                $firstRow = true;
                $order = $lots->first();
            @endphp

            @foreach($lots as $lot)
                <tr>
                    {{-- ORDER LEVEL (ONLY ONCE) --}}
                    <td>{{ $firstRow ? $order['order_no'] : '' }}</td>
                    <td>{{ $firstRow ? $order['customer'] : '' }}</td>
                    <td>{{ $firstRow ? $order['total_pcs_in_order'] : '' }}</td>
                    <td>
                        {{ $firstRow
                            ? \Carbon\Carbon::parse($order['expected_delivery_date'])->format('d M Y')
                            : '' }}
                    </td>

                    {{-- LOT LEVEL --}}
                    <td>{{ $lot['lot_no'] }}</td>
                    <td>{{ $lot['pieces_in_lot'] }}</td>
                    <td>{{ $lot['isDelayed'] }}</td>
                    <td>{{ $lot['expected_time'] }}</td>
                </tr>

                @php $firstRow = false; @endphp
            @endforeach

        @endforeach
    </tbody>
</table>
