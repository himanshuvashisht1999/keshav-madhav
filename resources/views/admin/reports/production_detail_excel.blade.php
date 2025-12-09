<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Production Detail - {{ $orderMain->sku }}</title>
</head>
<body>

<table border="1">
    <tr>
        <th colspan="4" style="font-size:16px; font-weight:bold;">
            Production Detail - {{ $orderMain->sku }}
        </th>
    </tr>
    <tr>
        <th>Main Order SKU</th>
        <td>{{ $orderMain->sku }}</td>
        <th>Customer</th>
        <td>{{ optional($orderMain->customer)->name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Expected Delivery Date</th>
        <td>{{ $orderMain->expected_delivery_date }}</td>
        <th>Status</th>
        <td>{{ $orderMain->status == 2 ? 'Completed' : ''}}</td>
    </tr>
    <tr>
        <th>Created At</th>
        <td>{{ $orderMain->created_at }}</td>
        <th></th>
        <td></td>
    </tr>
</table>

<br><br>

{{-- ORDERS & PRODUCTS --}}
@foreach($orderMain->orders as $orderIndex => $order)
    <table border="1">
        <tr>
            <th colspan="8" style="background:#dddddd;">
                Order #{{ $orderIndex + 1 }} - {{ $order->sku }}
            </th>
        </tr>
        <tr>
            <th>Expected Delivery</th>
            <td>{{ $order->expected_delivery_date }}</td>
            <th></th>
            <td></td>
        </tr>
    </table>

    <br>

    @foreach($order->products as $productIndex => $orderProduct)
        {{-- PRODUCT HEADER --}}
        <table border="1">
            <tr>
                <th colspan="8" style="background:#f2f2f2;">
                    Product #{{ $productIndex + 1 }} - {{ $orderProduct->product_sku }}
                </th>
            </tr>
            <tr>
                <th>Type of Garment</th>
                <td>{{ $orderProduct->product_type_sku }}</td>
                <th>Ordered Qty</th>
                <td>{{ $orderProduct->quantity }}</td>
                <th>Status</th>
                <td>{{ $orderProduct->status }}</td>
            </tr>
        </table>

        <br>

        {{-- FABRIC / BOM --}}
        <table border="1">
            <tr>
                <th colspan="6" style="background:#e8f5e9;">Fabric / BOM</th>
            </tr>
            <tr>
                <th>Fabric SKU</th>
                <th>Meter per Pc</th>
                <th>Order Qty</th>
                <th>Total Meter</th>
                <th>Issued Roll IDs</th>
                <th>Issued Meters</th>
            </tr>

            @forelse($orderProduct->product_details as $detail)
                @php
                    $stocks = $detail->product_detail_stocks;
                    $rowspan = max($stocks->count(), 1);
                @endphp

                @if($stocks->count())
                    @foreach($stocks as $stockIndex => $stockRow)
                        <tr>
                            @if($stockIndex == 0)
                                <td rowspan="{{ $rowspan }}">{{ $detail->fabric_sku }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $detail->meter }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $detail->order_quantity }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $detail->total_meter }}</td>
                            @endif
                            <td>{{ $stockRow->fabric_stock_id }}</td>
                            <td>{{ $stockRow->meter }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $detail->fabric_sku }}</td>
                        <td>{{ $detail->meter }}</td>
                        <td>{{ $detail->order_quantity }}</td>
                        <td>{{ $detail->total_meter }}</td>
                        <td colspan="2">No issue entry</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6">No BOM / fabric details.</td>
                </tr>
            @endforelse
        </table>

        <br>

        {{-- STAGES --}}
        <table border="1">
            <tr>
                <th colspan="6" style="background:#e3f2fd;">Stages</th>
            </tr>
            <tr>
                <th>#</th>
                <th>Stage</th>
                <th>Total Qty</th>
                <th>Completed</th>
                <th>Pending</th>
                <th>Status</th>
            </tr>
            @forelse($orderProduct->order_stages as $idx => $stageRow)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ optional($stageRow->stage)->name ?? $stageRow->stage_id }}</td>
                    <td>{{ $stageRow->total_qty }}</td>
                    <td>{{ $stageRow->completed_qty }}</td>
                    <td>{{ $stageRow->pending_qty }}</td>
                    <td>{{ $stageRow->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No stages.</td>
                </tr>
            @endforelse
        </table>

        <br>

        {{-- STAGE TRANSACTIONS --}}
        <table border="1">
            <tr>
                <th colspan="8" style="background:#fff3e0;">Stage Transactions</th>
            </tr>
            <tr>
                <th>Txn SKU</th>
                <th>From Stage</th>
                <th>To Stage</th>
                <th>Quantity</th>
                <th>Remaining Qty</th>
                <th>Lot No</th>
                <th>Remarks</th>
                <th>Date</th>
            </tr>
            @forelse($orderProduct->order_stage_trnsactions as $txn)
                <tr>
                    <td>{{ $txn->sku }}</td>
                    <td>{{ optional($txn->from_stage)->name ?? $txn->from_stage_id }}</td>
                    <td>{{ optional($txn->to_stage)->name ?? $txn->to_stage_id }}</td>
                    <td>{{ $txn->quantity }}</td>
                    <td>{{ $txn->remaining_quantity }}</td>
                    <td>{{ $txn->lot_no }}</td>
                    <td>{{ $txn->remarks }}</td>
                    <td>{{ $txn->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">No transactions.</td>
                </tr>
            @endforelse
        </table>

        <br><br>
    @endforeach
@endforeach

{{-- PACKAGING --}}
@if($orderMain->packages && $orderMain->packages->count())
    @foreach($orderMain->packages as $packageIndex => $package)
        <table border="1">
            <tr>
                <th colspan="6" style="background:#c8e6c9;">
                    Packaging - Package #{{ $packageIndex + 1 }} (ID: {{ $package->id }})
                </th>
            </tr>
            <tr>
                <th>Box ID</th>
                <th>Quantity in Box</th>
                <th>Warehouse ID</th>
                <th>Block ID</th>
                <th>Products (SKU)</th>
                <th>Description</th>
            </tr>

            @forelse($package->package_boxes as $box)
                <tr>
                    <td>{{ $box->id }}</td>
                    <td>{{ $box->quantity }}</td>
                    <td>{{ $box->warehouse_id }}</td>
                    <td>{{ $box->master_warehouse_block_id }}</td>
                    <td>
                        @if($box->package_boxes_items->count())
                            @foreach($box->package_boxes_items as $item)
                                {{ $item->product_sku }}@if(!$loop->last), @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $box->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No boxes in this package.</td>
                </tr>
            @endforelse
        </table>

        <br><br>
    @endforeach
@endif

</body>
</html>
