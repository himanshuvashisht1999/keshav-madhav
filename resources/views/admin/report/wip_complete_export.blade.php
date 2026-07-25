<table border="1">
    <thead>
        <tr>
            <th colspan="14" style="text-align: center; font-size: 16px; font-weight: bold;">
                WIP Complete Report (Customer: {{ $data[0]['order']->customer->name ?? '' }})
            </th>
        </tr>
        <tr>
            <th>Order No</th>
            <th>Order Date</th>
            <th>Design No</th>
            <th>Lot No</th>
            <th>Lot Qty</th>
            <th>Lot Status</th>
            <th>Current Stage</th>
            @foreach($master_stages as $stage)
                <th>{{ $stage->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            <tr>
                <td>{{ $row['order']->sku ?? '-' }}</td>
                <td>{{ $row['order']->created_at ? $row['order']->created_at->format('d M, Y') : '-' }}</td>
                <td>{{ $row['set']->design_number ?? '-' }}</td>
                <td>{{ $row['lot']->lot_no }}</td>
                <td>{{ $row['lot_quantity'] ?? 0 }}</td>
                <td>
                    @if($row['lot']->status == 0) Pending
                    @elseif($row['lot']->status == 1) Processing
                    @elseif($row['lot']->status == 2) Completed
                    @else Cancelled
                    @endif
                </td>
                <td>{{ getLastCurrentStage($row['lot']->lot_no) }}</td>

                <!-- STAGE WISE STATUS -->
                @foreach($master_stages as $stage)
                    @php
                        $d = getLotDetails($row['lot']->lot_no, $stage->id);
                    @endphp
                    <td>
                        @if($d && $d['time_allocation'])
                            In: {{ $d['quantity'] }} | Out: {{ $d['quantity'] - $d['remaining_quantity'] }}
                            WIP: {{ $d['remaining_quantity'] }}
                            Assigned: {{ \Carbon\Carbon::parse($d['time_allocation'])->format('d M y') }}
                            Completed: {{ $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time'])->format('d M y') : '-' }}
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
