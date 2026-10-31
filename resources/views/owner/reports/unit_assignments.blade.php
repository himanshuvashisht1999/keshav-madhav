@extends('owner.layouts.app')

@section('title', 'Unit Assignments')

@section('content')
    @php
        $isCutting = $type === 'cutting' && !$productionStatus;
        $viewNow = $productionStatus ? 'open' : ($view ?? 'open');
        $selUnits = array_map('strval', (array) request('unit_id', []));
        $selDesigns = array_map('strval', (array) request('design_no', []));
        $showRange = $productionStatus || in_array($viewNow, ['open', 'delayed'], true);
        $minVal = request()->has('min_pending_qty') ? request('min_pending_qty') : ($min_pending_qty ?? 1);
        $filterCount = (request('stage_id') ? 1 : 0) + (count(array_filter($selUnits)) ? 1 : 0) + (request('view', 'open') !== 'open' ? 1 : 0)
            + (request('order_no') ? 1 : 0) + (request('lot_no') ? 1 : 0) + (count(array_filter($selDesigns)) ? 1 : 0)
            + (request('start_date') ? 1 : 0) + (request('end_date') ? 1 : 0) + ($productionStatus ? 1 : 0)
            + ($showRange && (string) $minVal !== '1' ? 1 : 0) + ($showRange && request('max_pending_qty') !== null && request('max_pending_qty') !== '' ? 1 : 0);
        $badgeTone = ['success' => 'green', 'danger' => 'red', 'warning' => 'yellow'];
        $viewLabel = ['all' => 'All tasks', 'open' => 'Pending tasks', 'closed' => 'Done tasks', 'delayed' => 'Delayed tasks'];
        $stageName = request('stage_id') ? optional($stages->firstWhere('id', (int) request('stage_id')))->name : null;
        $statusLabel = [
            'not_printing' => 'Lots not sent to printing', 'not_stitching' => 'Lots not sent to stitching',
            'not_both' => 'Lots sent to neither printing nor stitching', 'printing_sent_stitching_pending' => 'Printing sent, stitching pending',
        ];
        $sub = $productionStatus
            ? ($statusLabel[$productionStatus] ?? 'Lots') . ', with the pieces cut'
            : ($viewLabel[$viewNow] ?? 'Tasks') . ' · ' . ($stageName ?: 'all stages after cutting, printing and embroidery');
        $cols = $productionStatus ? 7 : ($isCutting ? 11 : 13);
    @endphp

    @include('owner.common.page_head', [
        'title' => 'Unit Assignments',
        'sub' => $sub,
        'back' => route('owner.lots'),
    ])

    @if($productionStatus)
        @include('owner.common.totals', ['totals' => [
            ['label' => 'Lots', 'value' => number_format($totals['tasks'])],
            ['label' => 'Pieces cut', 'value' => number_format($totals['assigned'])],
        ]])
    @else
        @include('owner.common.totals', ['totals' => array_values(array_filter([
            ['label' => 'Tasks', 'value' => number_format($totals['tasks'])],
            ['label' => 'Assigned (pcs)', 'value' => number_format($totals['assigned'])],
            ['label' => 'Received (pcs)', 'value' => number_format($totals['received']), 'tone' => 'word'],
            ['label' => 'Pending (pcs)', 'value' => number_format($totals['pending']), 'tone' => $totals['pending'] > 0 ? 'red' : null],
            ['label' => 'Delayed tasks', 'value' => number_format($totals['delayed']), 'tone' => $totals['delayed'] > 0 ? 'red' : null],
            $totals['rework_pending'] > 0 ? ['label' => 'Rework pending (pcs)', 'value' => number_format($totals['rework_pending']), 'tone' => 'yellow'] : null,
        ]))])
    @endif

    <details class="ow-filters" {{ $filterCount ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($filterCount)<span class="ow-filter-count">{{ $filterCount }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.reports.unit-assignments') }}" id="ua-filters">
            <div>
                <label>Stage</label>
                <select name="stage_id" id="ua-stage" class="form-control select2" data-allow-clear="1" data-placeholder="All stages">
                    <option value=""></option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" {{ (string) request('stage_id') === (string) $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Unit person</label>
                <select name="unit_id[]" id="ua-unit" class="form-control select2" multiple data-placeholder="All units">
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ in_array((string) $unit->id, $selUnits, true) ? 'selected' : '' }}>
                            {{ $unit->name }}{{ !request('stage_id') && $unit->masterStage ? ' (' . $unit->masterStage->name . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Show</label>
                <select name="view" class="form-control ua-auto" {{ $productionStatus ? 'disabled' : '' }}>
                    @foreach($viewLabel as $v => $label)
                        <option value="{{ $v }}" {{ $viewNow === $v ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Order no</label>
                <input type="text" class="form-control" name="order_no" value="{{ request('order_no') }}" placeholder="Exact order no">
            </div>
            <div>
                <label>Lot no</label>
                <input type="text" class="form-control" name="lot_no" value="{{ request('lot_no') }}" placeholder="Exact lot no">
            </div>
            <div>
                <label>Design no</label>
                <select name="design_no[]" class="form-control select2" multiple data-placeholder="All designs">
                    @foreach($designs as $design)
                        <option value="{{ $design }}" {{ in_array((string) $design, $selDesigns, true) ? 'selected' : '' }}>{{ $design }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>From</label>
                <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div>
                <label>To</label>
                <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div>
                <label>Lot status</label>
                <select name="production_status" class="form-control ua-auto">
                    <option value="">None</option>
                    @foreach($statusLabel as $v => $label)
                        <option value="{{ $v }}" {{ $productionStatus === $v ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($showRange)
                <div>
                    <label>{{ $productionStatus ? 'Pieces' : 'Pending pcs' }} from</label>
                    <input type="number" step="any" class="form-control" name="min_pending_qty" value="{{ $minVal }}" placeholder="Min">
                </div>
                <div>
                    <label>{{ $productionStatus ? 'Pieces' : 'Pending pcs' }} up to</label>
                    <input type="number" step="any" class="form-control" name="max_pending_qty" value="{{ request('max_pending_qty') }}" placeholder="Max">
                </div>
            @endif
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.reports.unit-assignments') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Slide the table sideways to see every column</div>
    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Unit person</th>
                    @if($productionStatus)
                        <th>Lot</th>
                        <th>Design</th>
                        <th>Order</th>
                        <th class="num">Pieces cut</th>
                        <th>Lot date</th>
                    @elseif($isCutting)
                        <th>Order</th>
                        <th>Design</th>
                        <th>Lot</th>
                        <th class="num">Assigned</th>
                        <th class="num">Received</th>
                        <th class="num">Pending</th>
                        <th>Start</th>
                        <th>Completed</th>
                        <th>Plan end</th>
                    @else
                        <th>From stage</th>
                        <th>Stage now</th>
                        <th>Lot</th>
                        <th>Design</th>
                        <th>Sent by</th>
                        <th class="num">Assigned</th>
                        <th class="num">Received</th>
                        <th class="num">Pending</th>
                        <th>Start</th>
                        <th>Completed</th>
                        <th>Plan end</th>
                    @endif
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $item)
                    @php
                        $unit = $isCutting ? ($item->stage_master_unit ?? null) : ($item->getToUnitMaster ?? ($item->stage_master_unit ?? null));
                        $phone = $unit->phone ?? null;
                        $assigned = (float) ($item->assigned_qty ?? 0);
                        $pending = (float) ($item->pending_qty ?? 0);
                        $received = max(0, $assigned - $pending);
                        $statusText = ($item->status_text ?? '-') === 'Delayed Done' ? 'Done late' : ($item->status_text ?? '-');
                        $tone = $badgeTone[$item->status_class ?? ''] ?? 'grey';
                    @endphp
                    <tr>
                        <td class="wrap">
                            {{ $unit->name ?? '-' }}
                            @if($phone)
                                <a href="https://wa.me/{{ $phone }}" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            @endif
                        </td>
                        @if($productionStatus)
                            <td>@if(!empty($item->lot_no))<a href="{{ route('owner.lot-details', $item->lot_no) }}">{{ $item->lot_no }}</a>@else - @endif</td>
                            <td>{{ $item->design_number ?? '-' }}</td>
                            <td>{{ $item->orderMain->sku ?? '-' }}</td>
                            <td class="num">{{ number_format($assigned) }}</td>
                            <td>{{ $item->production_date ?? '-' }}</td>
                        @elseif($isCutting)
                            @php $isLot = !empty($item->lot_no) && isset($realLots[\App\Services\Admin\ReportService::lotKeyOf($item->lot_no)]); @endphp
                            <td>{{ $item->orderMain->sku ?? '-' }}</td>
                            <td>{{ $item->design_number ?? '-' }}</td>
                            <td>@if($isLot)<a href="{{ route('owner.lot-details', $item->lot_no) }}">{{ $item->lot_no }}</a>@else - @endif</td>
                            <td class="num">{{ number_format($assigned) }}</td>
                            <td class="num">{{ number_format($received) }}</td>
                            <td class="num {{ $pending > 0 ? 'ow-dr' : '' }}">{{ number_format($pending) }}</td>
                            <td>{{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}</td>
                            <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                            <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                        @else
                            <td>
                                {{ $item->from_stage->name ?? '-' }}
                                @if(!empty($item->has_rework) && !empty($item->rework_from_stage) && $item->rework_from_stage !== ($item->from_stage->name ?? ''))
                                    <small class="d-block ow-dr">Rework from {{ $item->rework_from_stage }}</small>
                                @endif
                            </td>
                            <td>{{ $item->to_stage_name ?? ($item->to_stage->name ?? '-') }}</td>
                            <td>
                                @if(!empty($item->lot_no))<a href="{{ route('owner.lot-details', $item->lot_no) }}">{{ $item->lot_no }}</a>@else - @endif
                                @if(!empty($item->has_rework))<span class="ow-badge red" title="{{ $item->rework_remarks ?? 'Defect return for rework' }}">Rework</span>@endif
                            </td>
                            <td>{{ $item->design_number ?? '-' }}</td>
                            <td>
                                {{ $item->getFromUnitMaster->name ?? '-' }}
                                @if(!empty($item->has_rework) && !empty($item->rework_from_unit) && $item->rework_from_unit !== ($item->getFromUnitMaster->name ?? ''))
                                    <small class="d-block ow-dr">Rework: {{ $item->rework_from_unit }}</small>
                                @endif
                            </td>
                            <td class="num">
                                {{ number_format($assigned) }}
                                @if(!empty($item->has_rework) && !empty($item->rework_qty))
                                    <small class="d-block text-muted">{{ number_format($item->rework_qty) }} rework</small>
                                @endif
                            </td>
                            <td class="num">{{ number_format($received) }}</td>
                            <td class="num {{ $pending > 0 ? 'ow-dr' : '' }}">
                                {{ number_format($pending) }}
                                @if(!empty($item->has_rework) && !empty($item->rework_pending_qty) && $item->rework_pending_qty > 0)
                                    <small class="d-block">{{ number_format($item->rework_pending_qty) }} rework</small>
                                @endif
                            </td>
                            <td>
                                {{ $item->start_time ? $item->start_time->format('d M Y') : '-' }}
                                @if(!empty($item->has_rework) && !empty($item->rework_start_time) && $item->rework_start_time->format('d M Y') !== ($item->start_time ? $item->start_time->format('d M Y') : ''))
                                    <small class="d-block text-muted">Rework {{ $item->rework_start_time->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td>{{ $item->end_time ? $item->end_time->format('d M Y') : '-' }}</td>
                            <td>{{ $item->estimated_time ? $item->estimated_time->format('d M Y') : '-' }}</td>
                        @endif
                        <td><span class="ow-badge {{ $tone }}">{{ $statusText }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $cols }}" class="ow-empty">No tasks found for these filters</td></tr>
                @endforelse
            </tbody>
            @if($totals['tasks'] > 0)
                <tfoot>
                    <tr>
                        <td>Total</td>
                        @if($productionStatus)
                            <td colspan="3">{{ number_format($totals['tasks']) }} lots{{ $assignments->lastPage() > 1 ? ' (all pages)' : '' }}</td>
                            <td class="num">{{ number_format($totals['assigned']) }}</td>
                            <td colspan="2"></td>
                        @elseif($isCutting)
                            <td colspan="3">{{ number_format($totals['tasks']) }} tasks{{ $assignments->lastPage() > 1 ? ' (all pages)' : '' }}</td>
                            <td class="num">{{ number_format($totals['assigned']) }}</td>
                            <td class="num">{{ number_format($totals['received']) }}</td>
                            <td class="num">{{ number_format($totals['pending']) }}</td>
                            <td colspan="4"></td>
                        @else
                            <td colspan="5">{{ number_format($totals['tasks']) }} tasks{{ $assignments->lastPage() > 1 ? ' (all pages)' : '' }}</td>
                            <td class="num">{{ number_format($totals['assigned']) }}</td>
                            <td class="num">{{ number_format($totals['received']) }}</td>
                            <td class="num">{{ number_format($totals['pending']) }}</td>
                            <td colspan="4"></td>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($assignments->lastPage() > 1)
        <div class="small text-muted text-center">Showing {{ number_format($assignments->firstItem()) }}-{{ number_format($assignments->lastItem()) }} of {{ number_format($assignments->total()) }}</div>
        {{ $assignments->onEachSide(1)->links() }}
    @endif
@endsection

@push('scripts')
<script>
    $(function () {
        var $form = $('#ua-filters');
        // like the admin page: stage, show and lot status apply at once; a new stage clears the unit list
        $('#ua-stage').on('change', function () {
            $('#ua-unit').val(null);
            $form.trigger('submit');
        });
        $form.find('select.ua-auto').on('change', function () { $form.trigger('submit'); });
    });
</script>
@endpush
