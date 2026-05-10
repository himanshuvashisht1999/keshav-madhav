<!DOCTYPE html>
<html>

<head>
    <title>Unit Assignments Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
        }

        .meta {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        .footer-total {
            background-color: #f9f9f9;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Unit Assignments Report</h2>
        <div class="meta">Generated on: {{ now()->format('d M Y h:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <!-- <th>Date</th> -->
                <th>Unit Person</th>
                @if($productionStatus)
                    <th>Lot No</th>
                    <th>Order No</th>
                    <th>Total Quantity</th>
                    <th>Lot Date</th>
                @else
                    <th>Stage</th>
                    <th>Lot No</th>
                    <th>Order No</th>
                    <th>Assigned Qty</th>
                    <th>Pending Qty</th>
                    <th>Start Date</th>
                    <th>Completed Date</th>
                    <th>Estimated Date</th>
                @endif
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalAssigned = 0;
                $totalPending = 0;
            @endphp
            @foreach($assignments as $item)
                @php 
                    $totalAssigned += ($item->assigned_qty ?? 0);
                    $totalPending += ($item->pending_qty ?? 0);
                @endphp
                <tr>
                    <!-- <td>{{ $item->created_at->format('d-m-Y') }}</td> -->
                    <td>
                        @if($type === 'cutting' || $item->transaction_type === 'cutting_lot')
                            {{ $item->stage_master_unit->name ?? '-' }}
                        @else
                            {{ $item->getToUnitMaster->name ?? $item->stage_master_unit->name ?? '-' }}
                        @endif
                    </td>
                    @if(!$productionStatus)
                    <td>
                        @if($type === 'cutting' || $item->transaction_type === 'cutting_lot')
                            Cutting
                        @else
                            {{ $item->to_stage->name ?? $item->from_stage->name ?? '-' }}
                        @endif
                    </td>
                    @endif
                    <td>{{ $type === 'cutting' || $item->transaction_type === 'cutting_lot' ? ($item->design_number ?? '-') : ($item->lot_no ?? '-') }}</td>
                    <td>
                        @if($type === 'cutting' || $item->transaction_type === 'cutting_lot')
                            {{ $item->orderMain->sku ?? '-' }}
                        @else
                            {{ $item->sku ?? ($item->orderProduct->orderMain->sku ?? '-') }}
                        @endif
                    </td>
                    @if($productionStatus)
                        <td>{{ $item->assigned_qty ?? 0 }}</td>
                        <td>{{ $item->production_date ?? '-' }}</td>
                    @else
                        <td>{{ $item->assigned_qty ?? 0 }}</td>
                        <td>{{ $item->pending_qty ?? 0 }}</td>
                        <td>{{ $item->start_time ? $item->start_time->format('d-m-Y') : '-' }}</td>
                        <td>{{ $item->end_time ? $item->end_time->format('d-m-Y') : '-' }}</td>
                        <td>{{ $item->estimated_time ? $item->estimated_time->format('d-m-Y') : '-' }}</td>
                    @endif
                    <td>{{ $item->status_text ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-total">
                <td colspan="{{ $productionStatus ? 3 : 4 }}" style="text-align: right;">Grand Total:</td>
                <td>{{ number_format($totalAssigned) }}</td>
                @if(!$productionStatus)
                    <td>{{ number_format($totalPending) }}</td>
                @endif
                <td colspan="{{ $productionStatus ? 2 : 4 }}"></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>