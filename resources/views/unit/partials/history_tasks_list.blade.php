{{-- One spreadsheet row per received / sent task (also returned on its own for "load more") --}}
@foreach($tasks as $task)
    @php
        $isReceived = $task['event_type'] == 'received';
        $endLate = !empty($task['end_date']) && empty($task['complete_date']) && now() > $task['end_date'];
        $href = null;
        if (!empty($task['slip_id'])) {
            $href = route('unit.view.slip', ['type' => 'production', 'id' => $task['slip_id']]);
        }
    @endphp
    <tr class="hs-row" @if($href) data-href="{{ $href }}" @endif>
        <td class="xl-nowrap">{{ \Carbon\Carbon::parse($task['created_at'])->format('d M Y') }}<div class="hs-sub">{{ \Carbon\Carbon::parse($task['created_at'])->format('h:i A') }}</div></td>
        <td class="xl-nowrap">
            @if($isReceived)
                <span class="hs-dir hs-in"><i class="fas fa-arrow-down"></i> Received</span>
            @else
                <span class="hs-dir hs-out"><i class="fas fa-arrow-up"></i> Sent</span>
            @endif
        </td>
        <td class="hs-lots">{{ ($task['lot_no'] && $task['lot_no'] !== '-') ? $task['lot_no'] : '-' }}</td>
        <td class="xl-num xl-strong">{{ number_format((float) $task['quantity']) }}</td>
        <td>
            @if($task['status'] == 1)
                <span class="xl-pill xl-pill-done">Completed</span>
            @else
                <span class="xl-pill xl-pill-open">In progress</span>
            @endif
        </td>
        <td>{{ $task['design_no'] }}</td>
        <td class="xl-nowrap">{{ $task['size_sets'] }}</td>
        <td class="xl-nowrap"><span class="hs-sub">{{ $isReceived ? 'From' : 'To' }}</span> {{ $task['from_stage'] }}</td>
        <td class="xl-nowrap">{{ !empty($task['start_date']) ? date('d M Y', strtotime($task['start_date'])) : '-' }}</td>
        <td class="xl-nowrap {{ $endLate ? 'hs-late' : '' }}">{{ !empty($task['end_date']) ? date('d M Y', strtotime($task['end_date'])) : '-' }}</td>
        <td>{{ $task['customer'] }}</td>
        <td class="text-center xl-nowrap">
            @if($href)
                <a href="{{ $href }}" class="xl-btn xl-btn-sm xl-btn-outline"><i class="fas fa-file-invoice"></i> Slip</a>
            @elseif($isReceived && $task['status'] == 0 && isset($task['id']))
                <a href="{{ route('unit.assignments.details', ['type' => $task['type'], 'id' => $task['id']]) }}" class="xl-btn xl-btn-sm"><i class="fas fa-play"></i> Process</a>
            @endif
        </td>
    </tr>
@endforeach
