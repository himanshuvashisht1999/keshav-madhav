@extends('admin.layouts.app')
@section('content')
    @php
        $isDomestic = ($session->order && strtolower(trim($session->order->order_type)) === 'domestic') || $session->domesticInventories->count() > 0;
        
        // ── 1. Grouping for Consolidated Shipping Summary ──────────────────
        $consolidatedGroups = [];
        $uniqueLocations = [];
        
        if ($isDomestic) {
            foreach ($session->domesticInventories as $dom) {
                $loc = $dom->rack 
                    ? ($dom->rack->storeroom->name . ' / ' . $dom->rack->name) 
                    : 'N/A';
                
                if ($loc !== 'N/A' && !in_array($loc, $uniqueLocations)) {
                    $uniqueLocations[] = $loc;
                }
                
                $design = $dom->product->design_number ?? 'N/A';
                $sizeSet = $dom->sizeSet->name ?? 'N/A';
                $color = $dom->color->name ?? 'N/A';
                
                $key = $design . '||' . $sizeSet . '||' . $color . '||' . $loc;
                if (!isset($consolidatedGroups[$key])) {
                    $consolidatedGroups[$key] = [
                        'design' => $design,
                        'size_set' => $sizeSet,
                        'color' => $color,
                        'location' => $loc,
                        'pcs_per_box' => $dom->quantity,
                        'total_packages' => 0,
                        'total_pcs' => 0,
                        'package_references' => []
                    ];
                }
                $consolidatedGroups[$key]['total_packages'] += $dom->total_boxes;
                $consolidatedGroups[$key]['total_pcs'] += ($dom->quantity * $dom->total_boxes);
                if (!in_array($dom->box_no, $consolidatedGroups[$key]['package_references'])) {
                    $consolidatedGroups[$key]['package_references'][] = $dom->box_no;
                }
            }
        } else {
            foreach ($session->cartons as $carton) {
                $loc = $carton->rack 
                    ? ($carton->rack->storeroom->name . ' / ' . $carton->rack->name) 
                    : 'N/A';
                
                if ($loc !== 'N/A' && !in_array($loc, $uniqueLocations)) {
                    $uniqueLocations[] = $loc;
                }
                
                foreach ($carton->items as $item) {
                    $set = $item->detail ? $item->detail->orderProductSet : null;
                    $design = $set->design_number ?? 'N/A';
                    $sizeSet = $set->size_measurement->name ?? 'N/A';
                    
                    // Pull color from relation
                    $color = $set->colors->name ?? 'N/A';
                    
                    $key = $design . '||' . $sizeSet . '||' . $color . '||' . $loc;
                    if (!isset($consolidatedGroups[$key])) {
                        $consolidatedGroups[$key] = [
                            'design' => $design,
                            'size_set' => $sizeSet,
                            'color' => $color,
                            'location' => $loc,
                            'total_packages' => 0,
                            'total_pcs' => 0,
                            'package_references' => []
                        ];
                    }
                    $consolidatedGroups[$key]['total_pcs'] += $item->quantity;
                    if (!in_array($carton->carton_no, $consolidatedGroups[$key]['package_references'])) {
                        $consolidatedGroups[$key]['package_references'][] = $carton->carton_no;
                        $consolidatedGroups[$key]['total_packages']++;
                    }
                }
            }
            foreach ($consolidatedGroups as &$g) {
                sort($g['package_references']);
            }
        }
        
        $totalCartonsOrBoxes = $isDomestic ? $session->domesticInventories->sum('total_boxes') : $session->cartons->count();
        $totalItems = $isDomestic 
            ? $session->domesticInventories->sum(function($d) { return $d->quantity * $d->total_boxes; })
            : $session->items->sum('quantity');
        $outflowItems = ($session->outflows ? $session->outflows->where('type', '!=', 'packing_divert')->sum('quantity') : 0) + ($session->reworks ? $session->reworks->sum('quantity') : 0);
    @endphp

    <div class="content-wrapper erp-page p-2">
        <!-- 1. SLIM ERP HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title d-flex align-items-center flex-wrap" style="gap: 8px;">
                <i class="fas fa-boxes" style="color: var(--erp-green-primary);"></i>
                <span>Packing Session</span>
                <span class="erp-badge-yellow px-2 py-0.5 rounded font-weight-bold" style="font-size: 0.95rem;">#{{ $session->id }}</span>
                <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; {{ $session->status == 1 ? 'background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;' : 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;' }}">
                    <i class="fas {{ $session->status == 1 ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                    {{ $session->status == 1 ? 'Finalized' : 'In-Progress' }}
                </span>
                <span class="badge px-2 py-1 font-weight-bold text-uppercase" style="font-size: 0.70rem; border-radius: 4px; background: #f1f5f9; color: #05421c; border: 1px solid #cbd5e1;">
                    {{ $isDomestic ? 'Domestic' : 'Corporate' }}
                </span>
            </div>
            <!-- Actions panel -->
            <div class="erp-header-actions d-flex align-items-center" style="gap: 6px;">
                <a href="{{ route('admin.packing.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                @php 
                    $isDomesticOrder = ($session->order && strtolower(trim($session->order->order_type)) === 'domestic');
                    $cartonIds = $session->cartons->pluck('id')->toArray();
                    $isDispatched = $session->cartons->where('status', 2)->count() > 0 
                        || (!empty($cartonIds) && \App\Models\OrderDispatchDetails::whereIn('carton_packing_id', $cartonIds)->exists());
                @endphp
                @if($session->domesticInventories->count() > 0)
                <a href="{{ route('admin.packing.downloadPrn', $session->id) }}" class="btn-erp btn-erp-outline" 
                   title="Download PRN file for Barcode Printer">
                    <i class="fas fa-barcode mr-1" style="color: #05421c;"></i> 
                    {{ $isDomesticOrder ? 'PRN Barcodes' : 'PRN Diverted' }}
                </a>
                @endif
                <button type="button" id="btnDeletePacking" 
                   class="btn-erp btn-erp-outline text-danger {{ $isDispatched ? 'disabled' : '' }}" 
                   style="border-color: #fca5a5;"
                   data-dispatched="{{ $isDispatched ? '1' : '0' }}"
                   title="{{ $isDispatched ? 'Cannot delete: Packing session items have already been dispatched' : 'Delete Packing Session and restore inventory' }}">
                    <i class="fas fa-trash-alt mr-1"></i> Delete Packing
                </button>
            </div>
        </div>

        <!-- 2. SESSION METADATA STRIP -->
        <div class="erp-card mb-3" style="border-top: 3px solid var(--erp-green-primary); background: #ffffff;">
            <div class="erp-card-body p-2 px-3">
                <div class="row align-items-center">
                    <div class="col-md-3 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-user-tie mr-1" style="color: var(--erp-green-primary);"></i> Customer
                        </div>
                        <div class="font-weight-bold mt-1 text-truncate" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);" title="{{ $session->order->customer->name ?? 'N/A' }}">
                            {{ $session->order->customer->name ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-file-invoice mr-1" style="color: var(--erp-green-primary);"></i> Order SKU
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-green-primary);">
                            {{ $session->order->sku ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-calendar-alt mr-1" style="color: var(--erp-green-primary);"></i> Packing Date
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                            {{ date('d M, Y', strtotime($session->packing_date)) }}
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-tag mr-1" style="color: var(--erp-green-primary);"></i> Cutting Slip
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                            #{{ $session->slip_id }}
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 py-1">
                        <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                            <i class="fas fa-layer-group mr-1" style="color: var(--erp-green-primary);"></i> Production Lots
                        </div>
                        <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-xs); color: var(--erp-text-heading);">
                            @php
                                $lotNumbers = $session->items->pluck('lot_no')->filter()->unique()->values()->toArray();
                            @endphp
                            @if(count($lotNumbers) > 0)
                                @foreach($lotNumbers as $lot)
                                    <span class="badge mr-1 px-1.5 py-0.5" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid #c3e6cb; font-size: 11px;">#{{ $lot }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. QUICK STATS SUMMARY -->
        <section class="mb-3">
            <div class="row">
                <!-- Carton/Box Stat -->
                <div class="col-md-4 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 8px; border-left: 4px solid #05421c;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px; font-size: 1.1rem; background: #05421c;">
                            <i class="fas fa-archive text-warning"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">
                                {{ $isDomestic ? 'Total Boxes' : 'Total Cartons' }}
                            </span>
                            <h4 class="font-weight-bold mb-0 text-dark">{{ $totalCartonsOrBoxes }}</h4>
                        </div>
                    </div>
                </div>
                <!-- Packed Pieces Stat -->
                <div class="col-md-4 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 8px; border-left: 4px solid #10b981;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px; font-size: 1.1rem; background: #10b981;">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Packed Pieces</span>
                            <h4 class="font-weight-bold mb-0" style="color: #05421c;">{{ $totalItems }} <span class="text-xs text-muted font-normal">pcs</span></h4>
                        </div>
                    </div>
                </div>
                <!-- Outflow Stat -->
                <div class="col-md-4 mb-2">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 8px; border-left: 4px solid #ef4444;">
                        <div class="icon-box text-white mr-3 rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 42px; height: 42px; font-size: 1.1rem; background: #ef4444;">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Debit / Sampling / Rework</span>
                            <h4 class="font-weight-bold mb-0 text-dark">{{ $outflowItems }} <span class="text-xs text-muted font-normal">pcs</span></h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. CONSOLIDATED PACKING SUMMARY -->
        <section class="mb-3">
            <div class="erp-card bg-white" style="border-radius: 8px;">
                <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <h6 class="font-weight-bold mb-0 text-nowrap d-flex align-items-center" style="color: var(--erp-green-primary);">
                        <i class="fas fa-list-alt mr-2 text-warning"></i> Consolidated Packing List Summary
                        <span class="badge ml-2 px-2 py-0.5" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-size: 0.72rem; font-weight: 600;">
                            {{ count($consolidatedGroups) }} Groups
                        </span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0 text-center text-sm">
                            <thead>
                                <tr>
                                    <th>Design Number</th>
                                    <th>Size Set</th>
                                    <th>Color</th>
                                    <th>Storage Location</th>
                                    <th>Total Packages</th>
                                    <th>Total Quantity</th>
                                    <th class="text-left pl-3">Package References</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consolidatedGroups as $group)
                                    <tr class="border-bottom">
                                        <td class="py-2.5 font-weight-bold text-dark">{{ $gDec = $group['design'] }}</td>
                                        <td class="py-2.5 font-weight-bold text-muted">{{ $group['size_set'] }}</td>
                                        <td class="py-2.5"><span class="badge badge-light border">{{ $group['color'] }}</span></td>
                                        <td class="py-2.5">
                                            <span class="badge border px-2 py-1 text-dark" style="background: #f8fafc; font-weight: 500; font-size: 0.8rem; border-color: #cbd5e1 !important;">
                                                <i class="fas fa-warehouse mr-1" style="color: var(--erp-green-primary); font-size: 10px;"></i>{{ $group['location'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 font-weight-bold" style="color: #05421c;">
                                            {{ $group['total_packages'] }} {{ $isDomestic ? 'Boxes' : 'Cartons' }}
                                        </td>
                                        <td class="py-2 font-weight-bold" style="color: #05421c; font-size: 0.95rem;">
                                            {{ $group['total_pcs'] }} <span class="text-xs text-muted font-normal">pcs</span>
                                        </td>
                                        <td class="py-2 text-left text-xs text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ implode(', ', $group['package_references']) }}">
                                            @if($isDomestic)
                                                {{ implode(', ', $group['package_references']) }}
                                            @else
                                                {{ count($group['package_references']) > 0 ? '#' . implode(', #', $gRefs = $group['package_references']) : 'N/A' }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-muted py-4">No packing data to summarize.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. DETAILED CARTON / BOX RECORDS GRID -->
        <section class="mb-3">
            <div class="erp-card bg-white" style="border-radius: 8px;">
                <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <h6 class="font-weight-bold mb-0 text-nowrap d-flex align-items-center" style="color: var(--erp-green-primary);">
                        <i class="fas fa-boxes mr-2 text-warning"></i> Detailed Package Log
                        <span class="badge ml-2 px-2 py-0.5" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-size: 0.72rem; font-weight: 600;">
                            {{ $isDomestic ? $session->domesticInventories->count() . ' Boxes' : $session->cartons->count() . ' Cartons' }}
                        </span>
                    </h6>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <!-- Search bar -->
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" id="cartonSearch" class="form-control form-control-sm border-left-0 erp-input" placeholder="Search No, Design..." style="border-color: #cbd5e1;">
                        </div>
                        <!-- Location Filter -->
                        <select id="locationFilter" class="form-control form-control-sm erp-input" style="width: 180px; border-color: #cbd5e1;">
                            <option value="">All Locations</option>
                            @foreach($uniqueLocations as $uloc)
                                <option value="{{ $uloc }}">{{ $uloc }}</option>
                            @endforeach
                        </select>
                        <!-- All bar download -->
                        @if($isDomestic && $session->domesticInventories->count() > 0)
                            <a href="{{ route('admin.packing.downloadAllDomesticBarcode', $session->slip_id) }}" class="btn-erp btn-erp-outline btn-sm">
                                <i class="fas fa-download mr-1"></i> Barcodes (TXT)
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table erp-table align-middle mb-0 text-center text-sm" id="detailsTable">
                            <thead style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    @if($isDomestic)
                                        <th>Box Sequence</th>
                                        <th>Design</th>
                                        <th>Size Set</th>
                                        <th>Color</th>
                                        <th>Pcs/Box</th>
                                        <th>Total Boxes</th>
                                        <th>Total Pieces</th>
                                        <th>Storage Location</th>
                                        <th>Barcode</th>
                                    @else
                                        <th width="12%">Carton #</th>
                                        <th class="text-left pl-3" width="45%">Design & Size Set Summary</th>
                                        <th width="20%">Total Pieces</th>
                                        <th width="23%">Storage Location</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                    @if($isDomestic)
                                        @forelse($session->domesticInventories as $dom)
                                            @php
                                                $dloc = $dom->rack 
                                                    ? ($dom->rack->storeroom->name . ' / ' . $dom->rack->name) 
                                                    : 'N/A';
                                            @endphp
                                            <tr class="border-bottom carton-detail-row" data-location="{{ $dloc }}">
                                                <td class="py-2.5 font-weight-bold" style="color: var(--erp-green-primary);">
                                                    {{ $dom->box_no }} 
                                                    <span class="d-block text-xs text-muted font-weight-normal mt-0.5">(Carton #{{ $dom->carton_no }})</span>
                                                </td>
                                                <td class="py-2.5 font-weight-bold text-dark">
                                                    {{ $dom->product->design_number ?? 'N/A' }}
                                                    @php
                                                        $carton = $session->cartons->firstWhere('id', $dom->packing_carton_id);
                                                        $cartonLots = $carton ? $carton->items->pluck('lot_no')->filter()->unique()->values()->toArray() : [];
                                                    @endphp
                                                    @if(count($cartonLots) > 0)
                                                        <div class="mt-1">
                                                            @foreach($cartonLots as $clot)
                                                                <span class="badge px-1.5 py-0.5 ml-0.5" style="font-size: 11px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-weight: 600;">Lot #{{ $clot }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 font-weight-bold text-muted">{{ $dom->sizeSet->name ?? 'N/A' }}</td>
                                                <td class="py-2.5"><span class="badge badge-light border">{{ $dom->color->name ?? 'N/A' }}</span></td>
                                                <td class="py-2.5">{{ $dom->quantity }} pcs</td>
                                                <td class="py-2.5 font-weight-bold" style="color: var(--erp-green-primary);">{{ $dom->total_boxes }}</td>
                                                <td class="py-2.5 font-weight-bold" style="color: var(--erp-green-primary);">{{ $dom->quantity * $dom->total_boxes }} <span class="text-xs text-muted font-normal">pcs</span></td>
                                                <td class="py-2.5">
                                                    @if($dom->rack)
                                                        <span class="badge border px-2 py-1 text-dark" style="background: #f8fafc; font-weight: 500; font-size: 0.8rem; border-color: #cbd5e1 !important;">
                                                            <i class="fas fa-warehouse mr-1" style="color: var(--erp-green-primary); font-size: 10px;"></i>{{ $dloc }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="py-2.5 font-mono"><code>{{ $dom->barcode }}</code></td>
                                            </tr>
                                        @empty
                                            <tr id="emptyDetailRow">
                                                <td colspan="9" class="text-muted py-5">No domestic box data logs.</td>
                                            </tr>
                                        @endforelse
                                    @else
                                        @forelse($session->cartons as $carton)
                                            @php
                                                $cloc = $carton->rack 
                                                    ? ($carton->rack->storeroom->name . ' / ' . $carton->rack->name) 
                                                    : 'N/A';
                                                $summaryData = $carton->items->map(function ($item) {
                                                    $set = $item->detail ? $item->detail->orderProductSet : null;
                                                    return [
                                                        'design' => $set->design_number ?? 'N/A',
                                                        'size_set' => $set->size_measurement->name ?? 'N/A',
                                                        'lot_no' => $item->lot_no
                                                    ];
                                                })->unique()->values();
                                            @endphp
                                            <tr class="border-bottom carton-detail-row" data-location="{{ $cloc }}">
                                                <td class="py-2.5 font-weight-bold" style="color: var(--erp-green-primary); font-size: 0.95rem;">#{{ $carton->carton_no }}</td>
                                                <td class="py-2.5 text-left">
                                                    @foreach($summaryData as $sum)
                                                        <div class="mb-0.5">
                                                            <strong class="text-dark">{{ $sum['design'] }}</strong>
                                                            <span class="text-muted ml-1.5 font-weight-normal">[{{ $sum['size_set'] }}]</span>
                                                            @if($sum['lot_no'])
                                                                <span class="badge ml-1 px-1.5 py-0.5" style="font-size: 11px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-weight: 600;">Lot #{{ $sum['lot_no'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td class="py-2.5 font-weight-bold" style="color: var(--erp-green-primary); font-size: 0.95rem;">
                                                    {{ $carton->items->sum('quantity') }} <span class="text-xs text-muted font-normal">pcs</span>
                                                </td>
                                                <td class="py-2.5">
                                                    @if($carton->rack)
                                                        <span class="badge border px-2 py-1 text-dark" style="background: #f8fafc; font-weight: 500; font-size: 0.8rem; border-color: #cbd5e1 !important;">
                                                            <i class="fas fa-warehouse mr-1" style="color: var(--erp-green-primary); font-size: 10px;"></i>{{ $cloc }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="emptyDetailRow">
                                                <td colspan="4" class="text-muted py-5">No packed corporate cartons found.</td>
                                            </tr>
                                        @endforelse
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. OUTFLOWS & ADJUSTMENTS SECTION -->
        @php
            $sessionOutflows = $session->outflows ? $session->outflows->where('type', '!=', 'packing_divert') : collect();
            $sessionReworks = $session->reworks ?? collect();
            $hasAdjustments = ($sessionOutflows->count() > 0) || ($sessionReworks->count() > 0);
        @endphp
        @if($hasAdjustments)
            <section class="mb-3">
                <div class="erp-card bg-white" style="border-radius: 8px;">
                    <div class="erp-card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                            <i class="fas fa-exchange-alt mr-2 text-warning"></i> Outflows, Reworks & Stock Adjustments
                        </h6>
                        <span class="badge border text-dark px-2 py-1 font-weight-bold" style="background: #edf7e4; color: #05421c; border-color: #c3e6cb !important;">
                            Total: {{ $sessionOutflows->sum('quantity') + $sessionReworks->sum('quantity') }} pcs
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table erp-table align-middle mb-0 text-center text-sm">
                                <thead>
                                    <tr>
                                        <th>Adjustment Type</th>
                                        <th>Lot No</th>
                                        <th>Design Number</th>
                                        <th>Size & Color</th>
                                        <th>Quantity</th>
                                        <th>Responsible Stage / Unit</th>
                                        <th class="text-left pl-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sessionOutflows as $out)
                                        <tr class="border-bottom">
                                            <td class="py-2">
                                                @php
                                                    $bgStyle = 'background: #f1f5f9; color: #475569;';
                                                    if ($out->type == 'debit') $bgStyle = 'background: #fee2e2; color: #991b1b;';
                                                    if ($out->type == 'sampling') $bgStyle = 'background: #edf7e4; color: #05421c;';
                                                    if ($out->type == 'dead') $bgStyle = 'background: #f3f4f6; color: #1f2937;';
                                                @endphp
                                                <span class="badge px-2 py-1 text-uppercase font-weight-bold text-xs" style="border-radius: 4px; {{ $bgStyle }}">
                                                    {{ $out->type }}
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                @if($out->lot_no)
                                                    <span class="badge px-1.5 py-0.5 font-weight-semibold" style="font-size: 11px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">#{{ $out->lot_no }}</span>
                                                @else
                                                    <span class="text-muted text-xs">N/A</span>
                                                @endif
                                            </td>
                                            <td class="py-2">
                                                <strong class="text-dark">{{ $out->product->design_number ?? 'N/A' }}</strong>
                                                <span class="d-block text-muted text-xs">{{ $out->product->name ?? 'Garment' }}</span>
                                            </td>
                                            <td class="py-2">
                                                <div class="text-dark font-weight-bold">Size: {{ $out->size->size ?? 'N/A' }}</div>
                                                <div class="text-muted text-xs">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-2 font-weight-bold text-dark">
                                                {{ $out->quantity }} <span class="text-xs text-muted font-normal">PCS</span>
                                            </td>
                                            <td class="py-2 text-muted">{{ $out->responsibleUnit->name ?? 'N/A' }}</td>
                                            <td class="py-2 text-left text-muted text-xs">{{ $out->remarks ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach

                                    @foreach($sessionReworks as $rw)
                                        @php
                                            $product = $rw->lot_info->orderProductSet->product ?? null;
                                            $color = $rw->lot_info->orderProductSet->colors ?? null;
                                            $sizeSet = $rw->lot_info->orderProductSet->size_measurement ?? null;
                                            $sizeDetails = $rw->details->map(function($d) { return $d->size . ' (' . $d->quantity . ')'; })->implode(', ');
                                        @endphp
                                        <tr class="border-bottom">
                                            <td class="py-2">
                                                <span class="badge px-2 py-1 text-uppercase font-weight-bold text-xs" style="border-radius: 4px; background: #fef3c7; color: #92400e;">
                                                    <i class="fas fa-tools mr-1"></i> Rework
                                                </span>
                                            </td>
                                            <td class="py-2">
                                                <span class="badge px-1.5 py-0.5 font-weight-semibold" style="font-size: 11px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">#{{ $rw->lot_no }}</span>
                                            </td>
                                            <td class="py-2">
                                                <strong class="text-dark">{{ $product->design_number ?? ($rw->lot_info->orderProductSet->design_number ?? 'N/A') }}</strong>
                                                <span class="d-block text-muted text-xs">{{ $product->name ?? 'Garment' }}</span>
                                            </td>
                                            <td class="py-2">
                                                <div class="text-dark font-weight-bold">Sizes: {{ $sizeDetails ?: 'N/A' }}</div>
                                                <div class="text-muted text-xs">Color: {{ $color->name ?? 'N/A' }}</div>
                                            </td>
                                            <td class="py-2 font-weight-bold text-danger">
                                                {{ $rw->quantity }} <span class="text-xs text-muted font-normal">PCS</span>
                                            </td>
                                            <td class="py-2">
                                                <div class="text-dark font-weight-bold">{{ $rw->toStage->name ?? 'Stage #' . $rw->to_stage_id }}</div>
                                                <div class="text-muted text-xs">Unit: {{ $rw->toUnit->name ?? 'Unit #' . $rw->sub_stage_id_to }}</div>
                                            </td>
                                            <td class="py-2 text-left text-muted text-xs">{{ $rw->remarks ?: 'Defect return for rework' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .stat-card {
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 66, 28, 0.08) !important;
        }
        .font-mono {
            font-family: monospace;
        }
    </style>

    <!-- Client-Side Real-Time Filter Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('cartonSearch');
            const locationSelect = document.getElementById('locationFilter');
            const tableRows = document.querySelectorAll('.carton-detail-row');
            const emptyRow = document.getElementById('emptyDetailRow');

            function filterTable() {
                const query = searchInput.value.toLowerCase().trim();
                const location = locationSelect.value.toLowerCase().trim();
                let visibleCount = 0;

                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const rowLocation = row.getAttribute('data-location').toLowerCase();

                    const matchesSearch = text.includes(query);
                    const matchesLocation = location === '' || rowLocation.includes(location);

                    if (matchesSearch && matchesLocation) {
                        row.style.setProperty('display', '', 'important');
                        visibleCount++;
                    } else {
                        row.style.setProperty('display', 'none', 'important');
                    }
                });

                if (emptyRow) {
                    if (visibleCount === 0) {
                        emptyRow.style.setProperty('display', '', 'important');
                        emptyRow.innerHTML = `<td colspan="${emptyRow.children.length}" class="text-muted py-5">No packages match the search criteria.</td>`;
                    } else {
                        emptyRow.style.setProperty('display', 'none', 'important');
                    }
                }
            }

            if (searchInput) searchInput.addEventListener('input', filterTable);
            if (locationSelect) locationSelect.addEventListener('change', filterTable);

            // Delete Packing Session
            const deleteBtn = document.getElementById('btnDeletePacking');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (deleteBtn.getAttribute('data-dispatched') === '1') {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Cannot delete: This packing session has already been dispatched.');
                        } else if (typeof Toast !== 'undefined') {
                            Toast.fire({ icon: 'error', title: 'Cannot delete: This packing session has already been dispatched.' });
                        } else {
                            alert('Cannot delete: This packing session has already been dispatched.');
                        }
                        return;
                    }

                    if (!confirm('Are you absolutely sure you want to delete this packing session? This will delete all cartons, domestic boxes, outflows, reworks, and restore stock to the previous stage.')) {
                        return;
                    }

                    const originalHtml = deleteBtn.innerHTML;
                    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';
                    deleteBtn.disabled = true;

                    $.ajax({
                        url: "{{ route('admin.packing.deleteSession', $session->id) }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status === 'success') {
                                if (typeof toastr !== 'undefined') {
                                    toastr.success(res.message);
                                } else if (typeof Toast !== 'undefined') {
                                    Toast.fire({ icon: 'success', title: res.message });
                                } else {
                                    alert(res.message);
                                }
                                setTimeout(function() {
                                    window.location.href = res.redirect_url || "{{ route('admin.packing.index') }}";
                                }, 1000);
                            } else {
                                if (typeof toastr !== 'undefined') {
                                    toastr.error(res.message || 'Failed to delete packing session.');
                                } else {
                                    alert(res.message || 'Failed to delete packing session.');
                                }
                                deleteBtn.innerHTML = originalHtml;
                                deleteBtn.disabled = false;
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON?.message || 'Failed to delete packing session.';
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                            deleteBtn.innerHTML = originalHtml;
                            deleteBtn.disabled = false;
                        }
                    });
                });
            }
        });
    </script>
@endsection