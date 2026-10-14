{{-- One spreadsheet row per digitized slip (also returned on its own for "load more") --}}
@foreach($slips as $slip)
    @php
        $hasSessions = $slip['type'] == 'production' && isset($slip['sessions']) && $slip['sessions']->isNotEmpty();
        if ($hasSessions) {
            $lotText = $slip['sessions']->map(fn($s) => $s['lot_no'] . ' (' . $s['pieces'] . ')')->unique()->implode(', ');
            $designText = $slip['sessions']->pluck('design_no')->filter(fn($v) => $v && $v !== '-')->unique()->implode(', ') ?: '-';
            $sizeText = $slip['sessions']->pluck('size_sets')->filter(fn($v) => $v && $v !== '-')->unique()->implode(', ') ?: '-';
            $customerText = $slip['sessions']->pluck('customer')->filter(fn($v) => $v && $v !== '-')->unique()->implode(', ') ?: '-';
            $workText = $slip['sessions']->pluck('type')->unique()->implode(', ');
        } else {
            $lotText = $slip['lot_no'] ?? '-';
            $designText = $slip['design_no'] ?? '-';
            $sizeText = $slip['size_sets'] ?? '-';
            $customerText = $slip['customer'] ?? '-';
            $workText = '-';
        }
        $canDelete = $slip['status'] == 0 && (!isset($slip['sessions']) || $slip['sessions']->isEmpty());
    @endphp
    <tr class="hs-row" data-href="{{ route('unit.view.slip', ['type' => $slip['type'], 'id' => $slip['id']]) }}">
        <td class="xl-nowrap">{{ \Carbon\Carbon::parse($slip['created_at'])->format('d M Y') }}<div class="hs-sub">{{ \Carbon\Carbon::parse($slip['created_at'])->format('h:i A') }}</div></td>
        <td>
            @if($slip['status'] == 1)
                <span class="xl-pill xl-pill-done">Done</span>
            @else
                <span class="xl-pill xl-pill-open">Waiting</span>
            @endif
        </td>
        <td class="hs-lots">{{ $lotText }}</td>
        <td class="xl-num xl-strong">{{ number_format((float) $slip['pieces']) }}</td>
        <td class="xl-nowrap">
            <i class="fas {{ $slip['type'] == 'fabric' ? 'fa-layer-group' : 'fa-box' }} hs-icon"></i> {{ ucfirst($slip['type']) }}
            @if($workText !== '-')<div class="hs-sub">{{ $workText }}</div>@endif
        </td>
        <td>{{ $designText }}</td>
        <td class="hs-size">{{ $sizeText }}</td>
        <td>{{ $customerText }}</td>
        <td class="text-center xl-nowrap">
            @if($canDelete)
                <form action="{{ route('unit.delete.slip', ['type' => $slip['type'], 'id' => $slip['id']]) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="hs-del" title="Delete this slip"
                            onclick="event.preventDefault(); event.stopPropagation(); if(confirm('Are you sure you want to delete this slip?')) { this.closest('form').submit(); }">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @endif
            <i class="fas fa-chevron-right hs-go"></i>
        </td>
    </tr>
@endforeach
