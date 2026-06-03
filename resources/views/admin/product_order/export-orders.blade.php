<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th>ID</th>
            <th>PO No</th>
            <th>Order No</th>
            <th>Customer</th>
            <th>Order Type</th>
            <th>Order Date</th>
            <th>Expected Delivery Date</th>
            <th>Total Pcs</th>
            <th>Dispatch Pcs</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($orders as $key => $order)
            @php
                $stats = getOrderDispatchData($order->id);
                $total = $stats['total'] ?? 0;
                $packed = $stats['packed'] ?? 0;
                $remaining = $stats['remaining'] ?? 0;

                $statusText = 'In Progress';
                if ($remaining === 0) {
                    $statusText = 'Completed';
                } elseif ($packed > 0) {
                    $statusText = 'Partial';
                }
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $order->po_number ?? '-' }}</td>
                <td>{{ $order->sku }}</td>
                <td>{{ $order->customer->name ?? '-' }}</td>
                <td>{{ ucfirst($order->order_type) }}</td>
                <td>{{ $order->created_at ? getformatDate($order->created_at) : '-' }}</td>
                <td>{{ getformatDate($order->expected_delivery_date) }}</td>
                <td>{{ $total }}</td>
                <td>{{ $packed }}</td>
                <td>{{ $statusText }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
