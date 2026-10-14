@extends('layouts.unit')

@section('title', 'Lot ' . $data['lot_no'])

@section('header_icon')
    <a href="{{ route('unit.lot.search') }}" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
<style>
    .ld-lot { font-size: 16px; font-weight: 800; color: var(--u-green); }
    .ld-slip { text-align: center; padding: 8px; }
    .ld-slip img { max-width: 100%; max-height: 460px; border: 1px solid var(--u-line); border-radius: 4px; }
</style>
@endpush

@section('content')
    @php
        $lot = $data['lots_data'][0] ?? null;
        $set = $lot->orderProductSet ?? null;
        $rolls = $data['rolls_data'] ?? collect();
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');

        // size-wise pieces across all rolls, in the order sizes first appear
        $sizeTotals = [];
        foreach ($rolls as $roll) {
            foreach ($roll->fabricRollAssigningsDetail ?? [] as $d) {
                $size = trim((string) ($d->size ?? '')) ?: '-';
                $sizeTotals[$size] = ($sizeTotals[$size] ?? 0) + (float) ($d->quantity ?? 0);
            }
        }
        $totalPcs = array_sum($sizeTotals);
        $totalMeter = $rolls->sum(fn($r) => (float) $r->meter);
        $slip = $lot->productionSlipDigitization ?? null;
    @endphp

    <!-- Lot summary -->
    <div class="xl-head">
        <h3><i class="fas fa-info-circle"></i> Lot Summary</h3>
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                <tr><th>Lot No</th><td class="ld-lot">{{ $lot->lot_no ?? '-' }}</td></tr>
                <tr><th>Design No</th><td>{{ $set->design_number ?? '-' }}</td></tr>
                <tr><th>Fabric</th><td>{{ $set?->fabric?->name ?? '-' }}</td></tr>
                <tr><th>Color</th><td>{{ $set?->colors?->name ?? '-' }}</td></tr>
                <tr><th>Pattern</th><td>{{ $set?->master_design_pattern?->name ?? '-' }}</td></tr>
                <tr><th>Fitting</th><td>{{ $set?->master_product_fitting?->name ?? '-' }}</td></tr>
                <tr><th>Production Unit</th><td>{{ $slip?->getUnitMaster?->name ?? '-' }}</td></tr>
                <tr><th>Total Pieces</th><td class="xl-strong">{{ $fmt($totalPcs) }}</td></tr>
                <tr><th>Fabric Used</th><td class="xl-strong">{{ $fmt($totalMeter) }} m</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Size wise quantity -->
    <div class="xl-head">
        <h3><i class="fas fa-ruler-horizontal"></i> Size Wise Quantity</h3>
    </div>
    <div class="xl-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Size</th>
                    <th class="xl-num">Pieces</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sizeTotals as $size => $qty)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="xl-strong">{{ $size }}</td>
                        <td class="xl-num">{{ $fmt($qty) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="xl-empty">No size details found.</td></tr>
                @endforelse
            </tbody>
            @if(count($sizeTotals))
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total Pieces</td>
                        <td class="xl-num">{{ $fmt($totalPcs) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <!-- Roll consumption -->
    <div class="xl-head">
        <h3><i class="fas fa-scroll"></i> Roll Consumption</h3>
        <span class="xl-sub">{{ $rolls->count() }} roll(s)</span>
    </div>
    <div class="xl-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Roll No</th>
                    <th class="xl-num">Meters Used</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rolls as $roll)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="xl-strong">{{ $roll->roll_no }}</td>
                        <td class="xl-num">{{ $fmt($roll->meter) }} m</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="xl-empty">No rolls found.</td></tr>
                @endforelse
            </tbody>
            @if($rolls->count())
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total Consumption</td>
                        <td class="xl-num">{{ $fmt($totalMeter) }} m</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($slip && $slip->slip_file)
        @php
            $ext = pathinfo($slip->slip_file, PATHINFO_EXTENSION);
            $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $slipUrl = asset('assets/production_slips/' . $slip->slip_file);
        @endphp
        <div class="xl-head">
            <h3><i class="fas fa-image"></i> Cutting Slip</h3>
        </div>
        <div class="xl-panel ld-slip">
            @if($isImage)
                <a href="{{ $slipUrl }}" target="_blank">
                    <img src="{{ $slipUrl }}" alt="Cutting Slip"
                         onerror="this.parentNode.style.display='none'; this.closest('.ld-slip').querySelector('.ld-missing').style.display='block';">
                </a>
                <div class="ld-missing xl-sub" style="display: none; padding: 20px;"><i class="fas fa-image"></i> Slip image file not found.</div>
            @else
                <a href="{{ $slipUrl }}" target="_blank" class="xl-btn"><i class="fas fa-file-pdf"></i> View Uploaded Document</a>
            @endif
        </div>
    @endif
@endsection
