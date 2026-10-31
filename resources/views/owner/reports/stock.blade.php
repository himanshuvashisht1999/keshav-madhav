@extends('owner.layouts.app')

@section('title', 'Fabric Stock')

@section('content')
@php
    $m = fn($v) => number_format((float) $v, 2);
    $t = $totals;
    $whSel = array_values(array_filter(array_map('strval', (array) request('warehouse_id')), fn($v) => $v !== ''));
    $whNames = $warehouses->whereIn('id', $whSel)->pluck('cutting_master_name')->implode(', ');
    $fabricId = request('fabric_id');
    $fabricName = isset($fabric) && $fabric ? $fabric->name : 'Fabric not found';
    // with a warehouse filter, moves from / to other warehouses are Received / Issued; moves between chosen ones are not
    // (so the figures can differ from the warehouse's row on the warehouse list, where moves have their own columns)
    $moveNote = !count($whSel) ? '' : (count($whSel) > 1
        ? '. Moves from / to other warehouses count as Received / Issued here (the warehouse list shows them as Moved in / out); moves between the chosen ones do not.'
        : '. Moves from / to other warehouses count as Received / Issued here (the warehouse list shows them as Moved in / out).');
@endphp

{{-- ============ Level 1: fabrics ============ --}}
@if($level === 'fabrics')
    @php
        $vendorSel = array_values(array_filter(array_map('strval', (array) request('vendor_id')), fn($v) => $v !== ''));
        $nFilters = (request()->filled('search') ? 1 : 0) + (count($whSel) ? 1 : 0) + (count($vendorSel) ? 1 : 0)
            + (request()->filled('qty_from') ? 1 : 0) + (request()->filled('qty_to') ? 1 : 0);
    @endphp
    @include('owner.common.page_head', [
        'title' => 'Fabric Stock',
        'sub' => $whNames ? 'At ' . $whNames . ' (metres)' . $moveNote : 'All warehouses (metres)',
        'back' => route('owner.dashboard'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.report.stock.rolls') . '"><i class="fas fa-barcode"></i> Rolls</a>'
            . '<a class="ow-btn is-outline is-sm" href="' . route('owner.ready-stock.index') . '"><i class="fas fa-boxes"></i> Ready stock</a>',
    ])
    @include('owner.common.totals', ['totals' => [
        ['label' => 'Fabrics', 'value' => number_format($data->total())],
        ['label' => 'Received (m)', 'value' => $m($t->sum_received)],
        ['label' => 'Issued (m)', 'value' => $m($t->sum_issued), 'tone' => 'yellow'],
        ['label' => 'In stock (m)', 'value' => $m($t->sum_remaining), 'tone' => 'word'],
    ]])

    <details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.stock') }}">
            <div>
                <label>Fabric name</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name...">
            </div>
            <div>
                <label>Warehouse</label>
                <select class="form-control select2" name="warehouse_id[]" multiple data-allow-clear="1" data-placeholder="All">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ in_array((string) $wh->id, $whSel, true) ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Supplier</label>
                <select class="form-control select2" name="vendor_id[]" multiple data-allow-clear="1" data-placeholder="All">
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ in_array((string) $v->id, $vendorSel, true) ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>In stock from (m)</label>
                <input type="number" step="0.01" class="form-control" name="qty_from" value="{{ request('qty_from') }}" placeholder="0.00">
            </div>
            <div>
                <label>In stock to (m)</label>
                <input type="number" step="0.01" class="form-control" name="qty_to" value="{{ request('qty_to') }}" placeholder="Any">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.stock') }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Fabric</th>
                    <th>Supplier</th>
                    <th class="num">Received (m)</th>
                    <th class="num">Issued (m)</th>
                    <th class="num">In stock (m)</th>
                </tr>
            </thead>
            <tbody id="stock-rows">
                @include('owner.reports.partials.stock_fabrics_list', ['data' => $data])
                @if($data->isEmpty())
                    <tr><td colspan="5" class="ow-empty">No fabric stock found</td></tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>Total ({{ number_format($data->total()) }})</td>
                    <td></td>
                    <td class="num">{{ $m($t->sum_received) }}</td>
                    <td class="num">{{ $m($t->sum_issued) }}</td>
                    <td class="num">{{ $m($t->sum_remaining) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @include('owner.reports.partials.stock_load_more', ['paginator' => $data, 'target' => 'stock-rows'])

{{-- ============ Level 2: warehouses of one fabric ============ --}}
@elseif($level === 'warehouses')
    @php
        $movedIn = round((float) ($t->sum_moved_in ?? 0), 2);
        $movedOut = round((float) ($t->sum_moved_out ?? 0), 2);
        $tiles = [
            ['label' => 'Warehouses', 'value' => number_format($data->count())],
            ['label' => 'Received (m)', 'value' => $m($t->sum_received)],
            ['label' => 'Issued (m)', 'value' => $m($t->sum_issued), 'tone' => 'yellow'],
            ['label' => 'In stock (m)', 'value' => $m($t->sum_remaining), 'tone' => 'word'],
        ];
        if ($movedIn == $movedOut) {
            if ($movedIn != 0) {
                $tiles[] = ['label' => 'Moved between warehouses (m)', 'value' => $m($movedIn), 'tone' => 'light'];
            }
        } else {
            $tiles[] = ['label' => 'Moved in (m)', 'value' => $m($movedIn), 'tone' => 'light'];
            $tiles[] = ['label' => 'Moved out (m)', 'value' => $m($movedOut), 'tone' => 'light'];
        }
    @endphp
    @include('owner.common.page_head', [
        'title' => $fabricName,
        'sub' => 'Stock by warehouse (metres). Received = from suppliers. Moves between warehouses have their own columns and are not Received or Issued.',
        'back' => route('owner.stock'),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.stock', ['fabric_id' => $fabricId, 'type' => 'receipts']) . '">Shipments</a>'
            . '<a class="ow-btn is-outline is-sm" href="' . route('owner.stock', ['fabric_id' => $fabricId, 'type' => 'usages']) . '">Usages</a>'
            . '<a class="ow-btn is-outline is-sm" href="' . route('owner.report.stock.rolls', ['fabric_id' => $fabricId]) . '">Rolls</a>',
    ])
    @include('owner.common.totals', ['totals' => $tiles])

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th class="num">Received (m)</th>
                    <th class="num">Moved in (m)</th>
                    <th class="num">Moved out (m)</th>
                    <th class="num">Issued (m)</th>
                    <th class="num">In stock (m)</th>
                    <th>Shipments</th>
                    <th>Usages</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td><a href="{{ route('owner.stock', ['fabric_id' => $fabricId, 'warehouse_id' => [$row->master_fabric_warehouse_id], 'type' => 'receipts']) }}">{{ $row->cutting_master_name ?: '-' }}</a></td>
                        <td class="num">{{ $m($row->total_received) }}</td>
                        <td class="num">{{ $m($row->total_moved_in) }}</td>
                        <td class="num">{{ $m($row->total_moved_out) }}</td>
                        <td class="num">{{ $m($row->total_issued) }}</td>
                        <td class="num"><strong>{{ $m($row->total_remaining) }}</strong></td>
                        <td><a href="{{ route('owner.stock', ['fabric_id' => $fabricId, 'warehouse_id' => [$row->master_fabric_warehouse_id], 'type' => 'receipts']) }}">Shipments</a></td>
                        <td><a href="{{ route('owner.stock', ['fabric_id' => $fabricId, 'warehouse_id' => [$row->master_fabric_warehouse_id], 'type' => 'usages']) }}">Usages</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ow-empty">No stock found for this fabric</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total ({{ number_format($data->count()) }})</td>
                    <td class="num">{{ $m($t->sum_received) }}</td>
                    <td class="num">{{ $m($movedIn) }}</td>
                    <td class="num">{{ $m($movedOut) }}</td>
                    <td class="num">{{ $m($t->sum_issued) }}</td>
                    <td class="num">{{ $m($t->sum_remaining) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

{{-- ============ Level 3: shipments (rolls received) of one fabric ============ --}}
@elseif($level === 'receipts')
    @php
        $nFilters = (count($whSel) ? 1 : 0) + (request()->filled('qty_from') ? 1 : 0) + (request()->filled('qty_to') ? 1 : 0);
    @endphp
    @include('owner.common.page_head', [
        'title' => $fabricName,
        'sub' => 'Shipments' . ($whNames ? ' at ' . $whNames . ' (received there or lying there now)' : ', all warehouses') . ' - metres' . ($whNames ? $moveNote : ''),
        'back' => route('owner.stock', ['fabric_id' => $fabricId]),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.stock', array_filter(['fabric_id' => $fabricId, 'warehouse_id' => $whSel, 'type' => 'usages'])) . '">Usages</a>',
    ])
    @include('owner.common.totals', ['totals' => [
        ['label' => 'Rolls', 'value' => number_format($data->total())],
        ['label' => 'Received (m)', 'value' => $m($t->sum_received)],
        ['label' => 'Issued (m)', 'value' => $m($t->sum_issued), 'tone' => 'yellow'],
        ['label' => 'In stock (m)', 'value' => $m($t->sum_remaining), 'tone' => 'word'],
    ]])

    <details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.stock') }}">
            <input type="hidden" name="fabric_id" value="{{ $fabricId }}">
            <input type="hidden" name="type" value="receipts">
            <div>
                <label>Warehouse</label>
                <select class="form-control select2" name="warehouse_id[]" multiple data-allow-clear="1" data-placeholder="All">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ in_array((string) $wh->id, $whSel, true) ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Left in roll from (m)</label>
                <input type="number" step="0.01" class="form-control" name="qty_from" value="{{ request('qty_from') }}" placeholder="0.00">
            </div>
            <div>
                <label>Left in roll to (m)</label>
                <input type="number" step="0.01" class="form-control" name="qty_to" value="{{ request('qty_to') }}" placeholder="Any">
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.stock', ['fabric_id' => $fabricId, 'type' => 'receipts']) }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Roll no</th>
                    <th>Date</th>
                    <th>Received at</th>
                    <th>Now at</th>
                    <th>Supplier</th>
                    <th>Bill no</th>
                    <th>Shipment no</th>
                    <th class="num">Rate (₹/m)</th>
                    <th class="num">Amount (₹)</th>
                    <th class="num">Received (m)</th>
                    <th class="num">Returned (m)</th>
                    <th class="num">In stock (m)</th>
                </tr>
            </thead>
            <tbody id="stock-rows">
                @include('owner.reports.partials.stock_receipts_list', ['data' => $data])
                @if($data->isEmpty())
                    <tr><td colspan="12" class="ow-empty">No shipments found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @include('owner.reports.partials.stock_load_more', ['paginator' => $data, 'target' => 'stock-rows'])

{{-- ============ Level 4: usages (metres out) of one fabric ============ --}}
@elseif($level === 'usages')
    @php
        $nFilters = count($whSel) ? 1 : 0;
        $typeOrder = ['Cutting (Production)' => 'Cutting', 'Direct Sale' => 'Sales', 'Vendor Return' => 'Vendor returns', 'Stock Disposal' => 'Disposals', 'Transfer Out' => 'Transfers out'];
        $tiles = [
            ['label' => 'Entries', 'value' => number_format($usage_count)],
            ['label' => 'Listed (m)', 'value' => $m($usage_meter), 'tone' => 'yellow'],
        ];
        foreach ($typeOrder as $key => $label) {
            if (!empty($usage_by_type[$key])) {
                $tiles[] = ['label' => $label . ' (m)', 'value' => $m($usage_by_type[$key]), 'tone' => 'light'];
            }
        }
        foreach ($usage_by_type as $key => $meter) {
            if (!isset($typeOrder[$key]) && $meter) {
                $tiles[] = ['label' => $key . ' (m)', 'value' => $m($meter), 'tone' => 'light'];
            }
        }
        if ($usage_customer_returns > 0) {
            $tiles[] = ['label' => 'Customer returns (m)', 'value' => $m($usage_customer_returns), 'tone' => 'light'];
            $tiles[] = ['label' => 'Net used (m)', 'value' => $m($usage_meter - $usage_customer_returns), 'tone' => 'yellow'];
        }
        $tiles[] = ['label' => 'Received (m)', 'value' => $m($t->sum_received)];
        $tiles[] = ['label' => 'Issued (m)', 'value' => $m($t->sum_issued), 'tone' => 'yellow'];
        $tiles[] = ['label' => 'In stock (m)', 'value' => $m($t->sum_remaining), 'tone' => 'word'];
    @endphp
    @include('owner.common.page_head', [
        'title' => $fabricName,
        'sub' => 'Metres out' . ($whNames ? ' of rolls lying at ' . $whNames . ' now (rows follow the roll\'s current warehouse, so they may not equal Issued)' . $moveNote : ', all warehouses') . ($usage_customer_returns > 0 ? ($whNames ? ' ' : '. ') . 'Customer returns put metres back: Net used = Listed - Customer returns.' : ''),
        'back' => route('owner.stock', ['fabric_id' => $fabricId]),
        'actions' => '<a class="ow-btn is-outline is-sm" href="' . route('owner.stock', array_filter(['fabric_id' => $fabricId, 'warehouse_id' => $whSel, 'type' => 'receipts'])) . '">Shipments</a>',
    ])
    @include('owner.common.totals', ['totals' => $tiles])

    <details class="ow-filters" {{ $nFilters ? 'open' : '' }}>
        <summary><i class="fas fa-filter"></i> Filters @if($nFilters)<span class="ow-filter-count">{{ $nFilters }}</span>@endif</summary>
        <form class="ow-filter-body" method="GET" action="{{ route('owner.stock') }}">
            <input type="hidden" name="fabric_id" value="{{ $fabricId }}">
            <input type="hidden" name="type" value="usages">
            <div>
                <label>Warehouse</label>
                <select class="form-control select2" name="warehouse_id[]" multiple data-allow-clear="1" data-placeholder="All">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ in_array((string) $wh->id, $whSel, true) ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ow-filter-actions">
                <button class="ow-btn"><i class="fas fa-search"></i> Apply</button>
                <a class="ow-btn is-outline" href="{{ route('owner.stock', ['fabric_id' => $fabricId, 'type' => 'usages']) }}">Reset</a>
            </div>
        </form>
    </details>

    <div class="ow-table-wrap">
        <table class="erp-table sticky-first">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Roll no</th>
                    <th>Lot / kind</th>
                    <th>Order / doc no</th>
                    <th>Design / detail</th>
                    <th>Colour / party</th>
                    <th>Unit</th>
                    <th class="num">Metres</th>
                </tr>
            </thead>
            <tbody id="stock-rows">
                @include('owner.reports.partials.stock_usages_list', ['data' => $data])
                @if($data->isEmpty())
                    <tr><td colspan="9" class="ow-empty">No usages found</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    @include('owner.reports.partials.stock_load_more', ['paginator' => $data, 'target' => 'stock-rows'])
@endif
@endsection
