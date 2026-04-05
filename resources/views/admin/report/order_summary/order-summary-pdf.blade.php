<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Order Summary</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2,
        h3 {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Order Summary</h2>
    <p><strong>Order SKU:</strong> {{ $order->sku }}</p>

    <hr>

    <h3>Order Information</h3>
    <table>
        <tr>
            <th>Customer</th>
            <td>{{ $order->customer->name ?? 'N/A' }}</td>
            <th>Order Date</th>
            <td>{{ date('d M Y', strtotime($order->created_at)) }}</td>
        </tr>
        <tr>
            <th>Expected Delivery</th>
            <td>{{ date('d M Y', strtotime($order->expected_delivery_date)) }}</td>
            <th>Order Type</th>
            <td>{{ ucfirst($order->order_type) }}</td>
        </tr>
    </table>

    <h3>Product Details</h3>
    <table>
        <thead>
            <tr>
                <th>Barcode</th>
                <th>Design No</th>
                <th>Size</th>
                <th>Colour</th>
                <th>Fabric</th>
                <th>Set Qty</th>
                <th>Total Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderProductSets as $set)
                <tr>
                    <td>{{ $set->bar_code }}</td>
                    <td>{{ $set->design_number }}</td>
                    <td>{{ $set->size_measurement->name ?? '-' }}</td>
                    <td>{{ $set->colors->name ?? '-' }}</td>
                    <td>{{ $set->fabric->name ?? '-' }}</td>
                    <td class="text-right">{{ $set->set_quantity }}</td>
                    <td class="text-right">{{ $set->total_quantity }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total</th>
                <th class="text-right">{{ $order->orderProductSets->sum('set_quantity') }}</th>
                <th class="text-right">{{ $order->orderProductSets->sum('total_quantity') }}</th>
            </tr>
        </tfoot>
    </table>

    <h3>Lot Details</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Lot No</th>
                <th>Order No</th>
                <th>Customer</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lotsData as $i => $lot)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $lot['lot_no'] }}</td>
                    <td>{{ $lot['order_no'] }}</td>
                    <td>{{ $lot['customer_name'] }}</td>
                    <td class="text-right">{{ $lot['lot_quantity'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Packing Details</h3>
    <table>
        <thead>
            <tr>
                <th>Carton No</th>
                <th>Contents</th>
                <th>Total Items</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cartons as $carton)
                <tr>
                    <td>{{ $carton->carton_no }}</td>
                    <td>
                        @php
                            $summary = [];
                            foreach ($carton->items as $item) {
                                $name = $item->detail->size ?? $item->size_id;
                                if (!isset($summary[$name]))
                                    $summary[$name] = 0;
                                $summary[$name] += $item->quantity;
                            }
                            $text = [];
                            foreach ($summary as $k => $v)
                                $text[] = "Size: $k (Qty: $v)";
                        @endphp
                        {{ implode(', ', $text) }}
                    </td>
                    <td class="text-center">{{ $carton->items->sum('quantity') }}</td>
                    <td class="text-center">
                        {{ $carton->status == 2 ? 'Dispatched' : 'Packed' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Dispatch History</h3>
    <table>
        <thead>
            <tr>
                <th>Dispatch No</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dispatches as $dispatch)
                <tr>
                    <td>{{ $dispatch->sku }}</td>
                    <td>{{ date('d M Y', strtotime($dispatch->dispatch_date)) }}</td>
                    <td>{{ $dispatch->status == 2 ? 'Complete' : 'Complete' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>