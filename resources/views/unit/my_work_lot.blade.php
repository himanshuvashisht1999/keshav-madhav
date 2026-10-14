@extends('layouts.unit')

@section('title', 'Lot ' . $lot['lot_no'])

@section('header_icon')
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('unit.pending-tasks') }}" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
<style>
    .wl-bal { font-size: 22px; font-weight: 900; color: #b45309; }
    .wl-done { color: #166534; font-weight: 800; }
    .wl-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
    .wl-actions .xl-btn { height: 46px; font-size: 15px; }
    .wl-note { background: #fffbd6; border: 1px solid #f3e27a; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #6b5a00; margin-top: 6px; }
    .wl-skip td { color: var(--u-muted); }
    .wl-from { min-width: 96px; font-size: 13px; line-height: 1.25; }
    body.unit-app .app-content .wl-recv th, body.unit-app .app-content .wl-recv td { padding-left: 7px !important; padding-right: 7px !important; }
    .wl-slip { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 6px; background: var(--u-green); color: #fff !important; text-decoration: none !important; font-weight: 700; white-space: nowrap; }
    .wl-slip i { color: var(--u-yellow); font-size: 10px; }
</style>
@endpush

@section('content')
    @php
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        $balance = $lot['total_pending'];
        $received = $lot['total_assigned'];
        $onlyCutting = $lot['counted_sources'] === ['cutting'];
        $isCounted = fn($item) => !$onlyCutting || $item->source === 'cutting';
        $items = $lot['items']->sortBy('created_at');
        $hasSkipped = $items->contains(fn($i) => !$isCounted($i));

        // pieces received per size (only the rows that are counted in the balance)
        $sizes = [];
        foreach ($items as $item) {
            if (!$isCounted($item)) continue;
            foreach (collect($item->size_rows ?? []) as $d) {
                $size = trim((string) ($d->size ?? '')) ?: '-';
                $sizes[$size] = ($sizes[$size] ?? 0) + (float) ($d->quantity ?? 0);
            }
        }
    @endphp

    <!-- Lot status -->
    <div class="xl-head" style="margin-top: 0;">
        <h3><i class="fas fa-box"></i> Lot {{ $lot['lot_no'] }}</h3>
        @if($balance <= 0)
            <span class="xl-pill xl-pill-done">Done</span>
        @elseif($lot['is_delayed'])
            <span class="xl-pill xl-pill-late">Late</span>
        @else
            <span class="xl-pill xl-pill-open">In work</span>
        @endif
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                <tr><th>Balance</th><td class="wl-bal">{{ $fmt($balance) }} <span class="xl-sub">pcs left</span></td></tr>
                <tr><th>Design No</th><td class="xl-strong">{{ $lot['design_no'] }}</td></tr>
                <tr><th>Size Set</th><td>{{ $lot['size_set'] }}</td></tr>
                <tr><th>Received</th><td>{{ $fmt($received) }} pcs</td></tr>
                <tr><th>Done</th><td class="wl-done">{{ $fmt($received - $balance) }} pcs</td></tr>
                <tr>
                    <th>Due Date</th>
                    <td class="{{ $lot['is_delayed'] && $balance > 0 ? 'xl-late' : '' }}">
                        {{ $lot['estimated_date'] ? $lot['estimated_date']->format('d M Y') : '-' }}
                        @if($lot['is_delayed'] && $balance > 0)
                            <span class="xl-pill xl-pill-late">Late</span>
                        @endif
                    </td>
                </tr>
                <tr><th>From</th><td>{{ $lot['sent_by'] }}</td></tr>
                <tr><th>Cut Pcs</th><td>{{ $fmt($lot['total_cutting_pieces']) }}</td></tr>
            </tbody>
        </table>
    </div>

    <div class="wl-actions">
        <a href="javascript:void(0)" class="xl-btn open-upload-modal" data-type="{{ $unit->master_stage_id == 3 ? '1' : '2' }}"
           data-product-set-id="" data-transaction-id="" data-transaction-type="production">
            <i class="fas fa-camera"></i> Send Slip
        </a>
        <a href="{{ route('unit.lot.details', ['lot_no' => $lot['lot_no']]) }}" class="xl-btn xl-btn-outline">
            <i class="fas fa-search"></i> Full Lot Info
        </a>
    </div>

    <!-- What came in -->
    <div class="xl-head">
        <h3><i class="fas fa-truck-loading"></i> Received</h3>
        <span class="xl-sub">{{ $items->count() }} time(s)</span>
    </div>
    <div class="xl-wrap">
        <table class="wl-recv">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From</th>
                    <th class="xl-num">Pcs</th>
                    <th class="xl-num">Left</th>
                    <th>Slip</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php $counted = $isCounted($item); @endphp
                    <tr class="{{ $counted ? '' : 'wl-skip' }}">
                        <td class="xl-nowrap">{{ $item->created_at ? $item->created_at->format('d M y') : '-' }}</td>
                        <td class="wl-from">
                            {{ $item->from_stage->name ?? ($item->source === 'godam' ? 'Godam' : '-') }}
                            @if($item->getFromUnitMaster?->name)
                                <div class="xl-sub">{{ $item->getFromUnitMaster->name }}</div>
                            @endif
                        </td>
                        <td class="xl-num xl-strong">{{ $fmt($item->quantity) }}</td>
                        <td class="xl-num">{{ $counted ? $fmt($item->remaining_quantity) : '-' }}</td>
                        <td>
                            @if($item->production_slip_digitization_id)
                                <a href="{{ route('unit.assignments.slip_details', ['slip_id' => $item->production_slip_digitization_id]) }}" class="wl-slip">
                                    #{{ $item->production_slip_digitization_id }} <i class="fas fa-chevron-right"></i>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($hasSkipped)
        <div class="wl-note">
            <i class="fas fa-info-circle"></i>
            Grey rows are the same pieces sent again (from Embroidery / Godam). They are not added again to your balance.
        </div>
    @endif

    <!-- By size -->
    @if(!empty($sizes))
        <div class="xl-head">
            <h3><i class="fas fa-ruler-horizontal"></i> Received by Size</h3>
        </div>
        <div class="xl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Size</th>
                        <th class="xl-num">Pcs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sizes as $size => $qty)
                        <tr>
                            <td class="xl-strong">{{ $size }}</td>
                            <td class="xl-num">{{ $fmt($qty) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="xl-num">{{ $fmt(array_sum($sizes)) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
@endsection
