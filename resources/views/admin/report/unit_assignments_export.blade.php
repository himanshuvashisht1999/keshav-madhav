<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 14px; font-weight: bold;">
                Unit Assignments Report - {{ now()->format('d M Y h:i A') }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #f2f2f2; font-weight: bold;">Date</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Unit Person</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Stage</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Lot No</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Order No</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Assigned Qty</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Pending Qty</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Start Time</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">End Time</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Estimated Time</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assignments as $item)
            <tr>
                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                <td>
                    @if($type === 'cutting')
                        {{ $item->stage_master_unit->name ?? '-' }}
                    @else
                        {{ $item->getToUnitMaster->name ?? '-' }}
                    @endif
                </td>
                <td>
                    @if($type === 'cutting')
                        Cutting
                    @else
                        {{ $item->to_stage->name ?? '-' }}
                    @endif
                </td>
                <td>{{ $type === 'cutting' ? ($item->design_number ?? '-') : ($item->lot_no ?? '-') }}</td>
                <td>
                    @if($type === 'cutting')
                        {{ $item->orderMain->sku ?? '-' }}
                    @else
                        {{ $item->sku ?? ($item->orderProduct->orderMain->sku ?? '-') }}
                    @endif
                </td>
                <td>{{ $item->assigned_qty ?? 0 }}</td>
                <td>{{ $item->pending_qty ?? 0 }}</td>
                <td>{{ $item->start_time ? $item->start_time->format('d-m-Y h:i A') : '-' }}</td>
                <td>{{ $item->end_time ? $item->end_time->format('d-m-Y h:i A') : '-' }}</td>
                <td>{{ $item->estimated_time ? $item->estimated_time->format('d-m-Y h:i A') : '-' }}</td>
                <td>{{ $item->status_text ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>