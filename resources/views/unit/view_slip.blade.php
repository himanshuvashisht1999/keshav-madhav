@extends('layouts.unit')

@section('title', 'Slip Details')

@section('header_icon')
    <a href="javascript:history.back()" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@push('styles')
<style>
    .vs-image { position: relative; padding: 6px; text-align: center; }
    .vs-image img { max-width: 100%; max-height: 360px; display: inline-block; cursor: zoom-in; border-radius: 4px; }
    .vs-zoom { position: absolute; right: 12px; bottom: 12px; background: rgba(5, 66, 28, 0.85); color: #fff; padding: 4px 9px; border-radius: 5px; font-size: 11px; font-weight: 700; pointer-events: none; }
    .vs-img-missing { display: none; padding: 26px 10px; color: var(--u-muted); }
    .vs-lots { color: var(--u-green); font-weight: 700; }
    .vs-total { font-size: 15px; font-weight: 800; color: var(--u-green); }
    .vs-sheet td:nth-child(2), .vs-sheet th:nth-child(2) { position: sticky; left: 0; z-index: 1; box-shadow: 2px 0 0 var(--u-line); }
    .vs-sheet thead th:nth-child(2) { z-index: 3; }
    .vs-w { min-width: 140px; }
    .vs-sizes { min-width: 200px; font-size: 12px; }

    .image-modal { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.95); z-index: 2000; align-items: center; justify-content: center; }
    .image-modal.active { display: flex; }
    .modal-image { max-width: 95%; max-height: 95%; object-fit: contain; }
    .close-modal { position: absolute; top: 16px; right: 16px; background: var(--u-yellow); color: var(--u-green); width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer; }
</style>
@endpush

@section('content')
    @php
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');

        // Turns a list of size-detail rows into [size => qty] and remembers every size seen (column order)
        $sizeMap = function ($details, array &$cols) {
            $out = [];
            foreach (collect($details ?? []) as $d) {
                $size = trim((string) ($d->size ?? '')) ?: '-';
                $out[$size] = ($out[$size] ?? 0) + (float) ($d->quantity ?? 0);
                if (!in_array($size, $cols, true)) $cols[] = $size;
            }
            return $out;
        };

        // Up to 8 sizes -> one column per size (spreadsheet); more -> one "Sizes" column with "size: qty" text
        $maxSizeCols = 8;
        $sizeText = fn(array $sizes) => collect($sizes)->map(fn($q, $s) => $s . ': ' . $fmt($q))->implode(', ') ?: '-';

        // Design / order info falls back to the lot when the session row has no product link
        $lotSetCache = [];
        $lotSet = function ($lotNo) use (&$lotSetCache) {
            if (!array_key_exists($lotNo, $lotSetCache)) {
                $lotSetCache[$lotNo] = \App\Models\OrderLot::where('lot_no', $lotNo)->with('orderProductSet.orderMain.customer', 'orderProductSet.colors')->first()?->orderProductSet;
            }
            return $lotSetCache[$lotNo];
        };

        // Printing / transfer sessions -> one sheet each (one row per session)
        $buildSheet = function ($sessions) use ($sizeMap, $lotSet) {
            $cols = [];
            $rows = [];
            foreach ($sessions as $s) {
                $ops = $s->orderProduct?->orderProductSet;
                if (!$ops && !empty($s->lot_no)) {
                    $ops = $lotSet($s->lot_no);
                }
                $rows[] = [
                    'lot_no' => $s->lot_no ?: '-',
                    'order_no' => $ops?->orderMain?->sku ?? '-',
                    'design_no' => $ops->design_number ?? '-',
                    'fabric' => $ops->fabric_names ?? '-',
                    'color' => $ops?->colors?->name ?? '-',
                    'customer' => $ops?->orderMain?->customer?->name ?? '-',
                    'sizes' => $sizeMap($s->details, $cols),
                    'pcs' => collect($s->details ?? [])->sum('quantity'),
                ];
            }
            return ['cols' => $cols, 'rows' => $rows];
        };

        $sheets = [];
        if ($printings->isNotEmpty()) {
            $sheets[] = ['title' => 'Printing Sessions', 'icon' => 'fa-print'] + $buildSheet($printings);
        }
        if ($stage_transactions->isNotEmpty()) {
            $sheets[] = ['title' => 'Transfer Sessions', 'icon' => 'fa-exchange-alt'] + $buildSheet($stage_transactions);
        }

        // Cutting sessions -> lot sheet + roll sheet
        $rollCols = [];
        $rollRows = [];
        $lotRows = [];
        foreach ($lots as $lot) {
            $lotRolls = $rolls->where('order_lot_id', $lot->id);
            $lotPcs = 0;
            foreach ($lotRolls as $roll) {
                $sizes = $sizeMap($roll->fabricRollAssigningsDetail, $rollCols);
                $pcs = $roll->fabricRollAssigningsDetail->sum('quantity');
                $lotPcs += $pcs;
                $rollRows[] = ['lot_no' => $lot->lot_no, 'roll_no' => $roll->roll_no, 'meter' => $roll->meter, 'sizes' => $sizes, 'pcs' => $pcs];
            }
            $lotRows[] = [
                'lot_no' => $lot->lot_no,
                'order_no' => $lot->orderMain?->sku ?? '-',
                'design_no' => $lot->orderProductSet->design_number ?? '-',
                'fabric' => $lot->orderProductSet->fabric_names ?? '-',
                'color' => $lot->orderProductSet?->colors?->name ?? '-',
                'customer' => $lot->orderMain?->customer?->name ?? '-',
                'pcs' => $lotPcs,
            ];
        }
    @endphp

    {{-- SLIP IMAGE --}}
    <div class="xl-panel vs-image">
        <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" alt="Production Slip" id="slipImage"
             onerror="this.style.display='none'; this.parentNode.querySelector('.vs-zoom').style.display='none'; this.parentNode.querySelector('.vs-img-missing').style.display='block';">
        <span class="vs-zoom"><i class="fas fa-search-plus"></i> Tap to zoom</span>
        <div class="vs-img-missing"><i class="fas fa-image"></i> Slip image not available.</div>
    </div>

    {{-- SLIP SUMMARY --}}
    <div class="xl-head">
        <h3><i class="fas fa-id-card"></i> Slip #{{ $slip->id }}</h3>
        @if($slip->status == 0)
            <span class="xl-pill xl-pill-open">Pending</span>
        @else
            <span class="xl-pill xl-pill-done">Approved</span>
        @endif
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                <tr><th>From Stage</th><td>{{ $slip->fromStage->name ?? '-' }}</td></tr>
                <tr><th>Lot No</th><td class="vs-lots">{{ $summary['lot_no'] ?: '-' }}</td></tr>
                <tr><th>Design No</th><td>{{ $summary['design'] ?: '-' }}</td></tr>
                <tr><th>Size Set</th><td>{{ $summary['size_group'] }}</td></tr>
                <tr><th>Pieces in Set</th><td>{{ $summary['pcs_in_set'] }}</td></tr>
                <tr><th>Size Range</th><td>{{ $actual_range }}</td></tr>
                <tr><th>Total Pieces</th><td class="vs-total">{{ $fmt($total_pcs) }}</td></tr>
                <tr><th>Order No</th><td>{{ $summary['order_no'] ?: '-' }}</td></tr>
                <tr><th>Customer</th><td>{{ $summary['customer'] ?: '-' }}</td></tr>
            </tbody>
        </table>
    </div>

    {{-- 1. CUTTING SESSIONS --}}
    @if($lots->isNotEmpty())
        <div class="xl-head">
            <h3><i class="fas fa-cut"></i> Cutting Sessions</h3>
            <span class="xl-sub">{{ $lots->count() }} lot(s)</span>
        </div>
        <div class="xl-wrap">
            <table class="vs-sheet">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lot No</th>
                        <th>Design No</th>
                        <th class="xl-num">Pcs</th>
                        <th>Order No</th>
                        <th>Fabric</th>
                        <th>Color</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lotRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="xl-strong xl-nowrap">{{ $row['lot_no'] }}</td>
                            <td class="xl-nowrap">{{ $row['design_no'] }}</td>
                            <td class="xl-num xl-strong">{{ $fmt($row['pcs']) }}</td>
                            <td class="xl-nowrap">{{ $row['order_no'] }}</td>
                            <td class="vs-w">{{ $row['fabric'] }}</td>
                            <td class="xl-nowrap">{{ $row['color'] }}</td>
                            <td class="vs-w">{{ $row['customer'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="xl-head">
            <h3><i class="fas fa-scroll"></i> Rolls Detail</h3>
            <span class="xl-sub">{{ count($rollRows) }} roll(s)</span>
        </div>
        @php
            $rollPivot = count($rollCols) <= $maxSizeCols;
            $rollSizeCols = $rollPivot ? $rollCols : [];
        @endphp
        <div class="xl-wrap">
            <table class="vs-sheet">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lot No</th>
                        <th>Roll No</th>
                        <th class="xl-num">Meter</th>
                        @foreach($rollSizeCols as $size)
                            <th class="xl-num">{{ $size }}</th>
                        @endforeach
                        <th class="xl-num">Pcs</th>
                        @unless($rollPivot)<th>Sizes</th>@endunless
                    </tr>
                </thead>
                <tbody>
                    @forelse($rollRows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="xl-strong xl-nowrap">{{ $row['lot_no'] }}</td>
                            <td class="xl-nowrap">{{ $row['roll_no'] }}</td>
                            <td class="xl-num">{{ $row['meter'] }}</td>
                            @foreach($rollSizeCols as $size)
                                <td class="xl-num">{{ isset($row['sizes'][$size]) ? $fmt($row['sizes'][$size]) : '-' }}</td>
                            @endforeach
                            <td class="xl-num xl-strong">{{ $fmt($row['pcs']) }}</td>
                            @unless($rollPivot)<td class="vs-sizes">{{ $sizeText($row['sizes']) }}</td>@endunless
                        </tr>
                    @empty
                        <tr><td colspan="5" class="xl-empty">No rolls digitized yet</td></tr>
                    @endforelse
                </tbody>
                @if(count($rollRows))
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            <td class="xl-num">{{ $fmt(collect($rollRows)->sum(fn($r) => (float) $r['meter'])) }}</td>
                            @foreach($rollSizeCols as $size)
                                <td class="xl-num">{{ $fmt(collect($rollRows)->sum(fn($r) => $r['sizes'][$size] ?? 0)) }}</td>
                            @endforeach
                            <td class="xl-num">{{ $fmt(collect($rollRows)->sum('pcs')) }}</td>
                            @unless($rollPivot)<td></td>@endunless
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif

    {{-- 2. PRINTING / 3. TRANSFER SESSIONS --}}
    @foreach($sheets as $sheet)
        @php
            $pivot = count($sheet['cols']) <= $maxSizeCols;
            $sheetSizeCols = $pivot ? $sheet['cols'] : [];
        @endphp
        <div class="xl-head">
            <h3><i class="fas {{ $sheet['icon'] }}"></i> {{ $sheet['title'] }}</h3>
            <span class="xl-sub">{{ count($sheet['rows']) }} session(s)</span>
        </div>
        <div class="xl-wrap">
            <table class="vs-sheet">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lot No</th>
                        <th>Design No</th>
                        @foreach($sheetSizeCols as $size)
                            <th class="xl-num">{{ $size }}</th>
                        @endforeach
                        <th class="xl-num">Pcs</th>
                        @unless($pivot)<th>Sizes</th>@endunless
                        <th>Order No</th>
                        <th>Fabric</th>
                        <th>Color</th>
                        <th>Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sheet['rows'] as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="xl-strong xl-nowrap">{{ $row['lot_no'] }}</td>
                            <td class="xl-nowrap">{{ $row['design_no'] }}</td>
                            @foreach($sheetSizeCols as $size)
                                <td class="xl-num">{{ isset($row['sizes'][$size]) ? $fmt($row['sizes'][$size]) : '-' }}</td>
                            @endforeach
                            <td class="xl-num xl-strong">{{ $fmt($row['pcs']) }}</td>
                            @unless($pivot)<td class="vs-sizes">{{ $sizeText($row['sizes']) }}</td>@endunless
                            <td class="xl-nowrap">{{ $row['order_no'] }}</td>
                            <td class="vs-w">{{ $row['fabric'] }}</td>
                            <td class="xl-nowrap">{{ $row['color'] }}</td>
                            <td class="vs-w">{{ $row['customer'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @if(count($sheet['rows']) > 1)
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            @foreach($sheetSizeCols as $size)
                                <td class="xl-num">{{ $fmt(collect($sheet['rows'])->sum(fn($r) => $r['sizes'][$size] ?? 0)) }}</td>
                            @endforeach
                            <td class="xl-num">{{ $fmt(collect($sheet['rows'])->sum('pcs')) }}</td>
                            @unless($pivot)<td></td>@endunless
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endforeach

    {{-- 4. PACKING DETAILS --}}
    @if($packing_details && $packing_details->cartons->count() > 0)
        <div class="xl-head">
            <h3><i class="fas fa-box-open"></i> Packing Summary</h3>
            <span class="xl-sub">{{ $packing_details->cartons->count() }} carton(s)</span>
        </div>
        <div class="xl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Carton No</th>
                        <th class="xl-num">Items</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packing_details->cartons as $carton)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="xl-strong">{{ $carton->carton_no }}</td>
                            <td class="xl-num">{{ $fmt($carton->items->sum('quantity')) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td class="xl-num">{{ $fmt($packing_details->cartons->sum(fn($c) => $c->items->sum('quantity'))) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- Image Zoom Modal -->
    <div class="image-modal" id="imageModal">
        <div class="close-modal" id="closeModal"><i class="fas fa-times"></i></div>
        <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" alt="Slip" class="modal-image">
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slipImage = document.getElementById('slipImage');
            const imageModal = document.getElementById('imageModal');
            const closeModal = document.getElementById('closeModal');

            if (slipImage && imageModal && closeModal) {
                slipImage.addEventListener('click', () => imageModal.classList.add('active'));
                closeModal.addEventListener('click', () => imageModal.classList.remove('active'));
                imageModal.addEventListener('click', (e) => {
                    if (e.target === imageModal) imageModal.classList.remove('active');
                });
            }
        });
    </script>
@endpush
