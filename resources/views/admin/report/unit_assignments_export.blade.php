<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 14px; font-weight: bold;">
                Unit Assignments Report - {{ now()->format('d M Y h:i A') }}
            </th>
        </tr>
        <tr>
            <!-- <th style="background-color: #f2f2f2; font-weight: bold;">Date</th> -->
            <th style="background-color: #f2f2f2; font-weight: bold;">Unit Person</th>
            @if($productionStatus)
                <th style="background-color: #f2f2f2; font-weight: bold;">Lot No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Design No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Order No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Total Quantity</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Lot Date</th>
            @else
                <th style="background-color: #f2f2f2; font-weight: bold;">Stage</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Lot No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Design No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Order No</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Assigned Qty</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Pending Qty</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Start Date</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Completed Date</th>
                <th style="background-color: #f2f2f2; font-weight: bold;">Estimated Date</th>
            @endif
            <th style="background-color: #f2f2f2; font-weight: bold;">Status</th>
        </tr>
        </tr>
    </thead>
    <tbody>
        @foreach($assignments as $item)
            <tr>
                <!-- <td>{{ $item->created_at->format('d-m-Y') }}</td> -->
                <td>
                    @if($type === 'cutting')
                        {{ $item->stage_master_unit->name ?? '-' }}
                    @else
                        {{ $item->getToUnitMaster->name ?? '-' }}
                    @endif
                </td>
                @if(!$productionStatus)
                <td>
                    @if($type === 'cutting' || $item->transaction_type === 'cutting_lot')
                        Cutting
                    @else
                        {{ $item->to_stage->name ?? '-' }}
                    @endif
                </td>
                @endif
                <td>{{ $type === 'cutting' || $item->transaction_type === 'cutting_lot' ? ($item->design_number ?? '-') : ($item->lot_no ?? '-') }}</td>
                <td>{{ $item->design_number ?? '-' }}</td>
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
</table>