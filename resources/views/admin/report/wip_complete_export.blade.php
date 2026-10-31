<table border="1">
    <thead>
        <tr>
            <th colspan="{{ 8 + count($master_stages) }}" style="text-align: center; font-size: 16px; font-weight: bold;">
                WIP Complete Report (Customer: {{ $data[0]['order']->customer->name ?? '' }})
            </th>
        </tr>
        <tr>
            <th>Order No</th>
            <th>Order Date</th>
            <th>Design No</th>
            <th>Season</th>
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
                <td>{{ $row['set']->season->name ?? '-' }}</td>
                <td>{{ $row['lot']->lot_no }}</td>
                <td>{{ $row['lot_quantity'] ?? 0 }}</td>
                @php
                    // status and stage from the lot's records (lotFacts); order_lots.status is never updated
                    $facts = $row['facts'];
                @endphp
                <td>
                    @if($facts && (int) $facts['stage_id'] === 12) Completed
                    @elseif($facts && $facts['is_delayed'] === 'Yes') Late
                    @else Running
                    @endif
                </td>
                <td>{{ $facts['stage_name'] ?? '-' }}</td>

                <!-- STAGE WISE STATUS -->
                @foreach($master_stages as $stage)
                    @php
                        $d = $row['stages'][$stage->id] ?? null;
                    @endphp
                    <td>
                        @if($d && $d['time_allocation'])
                            In: {{ $d['quantity'] }} | Out: {{ $d['done'] }}
                            WIP: {{ $d['remaining_quantity'] }}
                            Unit: {{ $d['unit_name'] ?? '-' }}@if(($d['unit_count'] ?? 0) > 1) +{{ $d['unit_count'] - 1 }} more @endif
                            Start: {{ $d['start_date'] ? \Carbon\Carbon::parse($d['start_date'])->format('d M y') : '-' }}
                            @php
                                $rem = (int)$d['remaining_quantity'];
                                $compDate = $d['completed_time'] ? \Carbon\Carbon::parse($d['completed_time']) : null;
                            @endphp
                            Completed: {{ ($rem == 0 && $compDate) ? $compDate->format('d M y') : '-' }}
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
