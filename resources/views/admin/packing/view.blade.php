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

    <div class="content-wrapper bg-light pb-5">
        <!-- 1. ERP SYSTEM HEADER -->
        <section class="content-header py-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px;">
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <!-- Session Details -->
                            <div class="col-lg-7 p-4 d-flex align-items-center">
                                <div class="customer-avatar rounded-circle d-flex align-items-center justify-content-center mr-4 shadow-sm"
                                    style="width: 72px; height: 72px; font-size: 1.8rem; font-weight: 700; background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
                                    {{ substr($session->order->customer->name ?? 'C', 0, 1) }}
                                </div>
                                <div>
                                    <div class="d-flex align-items-center mb-1">
                                        <h1 class="m-0 font-weight-bold h4 text-dark">Packing Session #{{ $session->id }}</h1>
                                        <span class="badge badge-pill ml-3 px-3 py-1 font-weight-bold text-xs {{ $session->status == 1 ? 'badge-success shadow-sm' : 'badge-warning shadow-sm' }}">
                                            <i class="fas {{ $session->status == 1 ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                            {{ $session->status == 1 ? 'Finalized' : 'In-Progress' }}
                                        </span>
                                    </div>
                                    <p class="text-muted mb-0 small">
                                        <span class="mr-3"><i class="fas fa-file-invoice text-slate mr-1"></i> Order SKU: <strong>#{{ $session->order->sku ?? 'N/A' }}</strong></span>
                                        <span class="mr-3"><i class="fas fa-user-tie text-slate mr-1"></i> Customer: <strong>{{ $session->order->customer->name ?? 'N/A' }}</strong></span>
                                        <span class="mr-3"><i class="fas fa-calendar-alt text-slate mr-1"></i> Date: <strong>{{ date('d M, Y', strtotime($session->packing_date)) }}</strong></span>
                                        @php
                                            $lotNumbers = $session->items->pluck('lot_no')->filter()->unique()->values()->toArray();
                                        @endphp
                                        @if(count($lotNumbers) > 0)
                                            <span><i class="fas fa-layer-group text-slate mr-1"></i> Packed Lots: 
                                                @foreach($lotNumbers as $lot)
                                                    <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11px;">#{{ $lot }}</span>
                                                @endforeach
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <!-- Actions panel -->
                            <div class="col-lg-5 d-flex align-items-center justify-content-lg-end justify-content-start p-4 bg-white border-left text-nowrap">
                                <div class="mr-3">
                                    @php $isDomesticOrder = ($session->order && strtolower(trim($session->order->order_type)) === 'domestic'); @endphp
                                    @if($session->domesticInventories->count() > 0)
                                    <a href="{{ route('admin.packing.downloadPrn', $session->id) }}" class="btn btn-dark btn-sm font-weight-bold px-3 py-2 shadow-sm" 
                                       title="Download PRN file for Barcode Printer">
                                        <i class="fas fa-barcode mr-1"></i> 
                                        {{ $isDomesticOrder ? 'PRINT BARCODES (PRN)' : 'PRINT DIVERTED (PRN)' }}
                                    </a>
                                    @endif
                                </div>
                                <div class="text-right mr-3">
                                    <span class="text-uppercase text-muted d-block mb-0" style="font-size: 0.65rem; letter-spacing: 1px; font-weight: 800;">Slip Reference</span>
                                    <h5 class="font-weight-bold text-dark mb-0">#{{ $session->slip_id }}</h5>
                                </div>
                                <div class="text-center rounded p-2 border bg-light" style="min-width: 110px;">
                                    <span class="text-uppercase text-muted d-block mb-0" style="font-size: 0.6rem; letter-spacing: 1px; font-weight: 800;">Session Type</span>
                                    <span class="badge {{ $isDomestic ? 'badge-info' : 'badge-primary' }} text-uppercase font-weight-bold px-2 py-1 mt-1 text-xs">
                                        {{ $isDomestic ? 'Domestic' : 'Corporate' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. QUICK STATS SUMMARY -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="row">
                    <!-- Carton/Box Stat -->
                    <div class="col-md-4 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #0f172a;">
                            <div class="icon-box bg-soft-dark text-dark mr-3 rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="fas fa-archive text-slate"></i>
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
                    <div class="col-md-4 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #10b981;">
                            <div class="icon-box bg-soft-success text-success mr-3 rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Total Packed Pieces</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ $totalItems }} <span class="text-xs text-muted font-normal">pcs</span></h4>
                            </div>
                        </div>
                    </div>
                    <!-- Outflow Stat -->
                    <div class="col-md-4 mb-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center" style="border-radius: 12px; border-left: 5px solid #ef4444;">
                            <div class="icon-box bg-soft-danger text-danger mr-3 rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase text-xs font-weight-bold d-block">Debit / Sampling / Rework</span>
                                <h4 class="font-weight-bold mb-0 text-dark">{{ $outflowItems }} <span class="text-xs text-muted font-normal">pcs</span></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. CONSOLIDATED PACKING SUMMARY (SHIPPING LIST) -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="d-flex align-items-center">
                            <span class="header-indicator mr-2" style="width: 4px; height: 18px; background: #6366f1; display: inline-block; border-radius: 2px;"></span>
                            <h5 class="font-weight-bold text-dark mb-0">Consolidated Packing List Summary</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center text-sm">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="border-0 py-3">Design Number</th>
                                        <th class="border-0 py-3">Size Set</th>
                                        <th class="border-0 py-3">Color</th>
                                        <th class="border-0 py-3">Storage Location</th>
                                        <th class="border-0 py-3">Total Packages</th>
                                        <th class="border-0 py-3">Total Quantity</th>
                                        <th class="border-0 py-3 text-left">Package References</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($consolidatedGroups as $group)
                                        <tr class="border-bottom">
                                            <td class="py-3 font-weight-bold text-dark">{{ $gDec = $group['design'] }}</td>
                                            <td class="py-3 font-weight-bold text-slate">{{ $group['size_set'] }}</td>
                                            <td class="py-3"><span class="badge badge-light border">{{ $group['color'] }}</span></td>
                                            <td class="py-3">
                                                <span class="badge badge-info font-weight-normal px-2 py-1">{{ $group['location'] }}</span>
                                            </td>
                                            <td class="py-3 font-weight-bold text-primary">
                                                {{ $group['total_packages'] }} {{ $isDomestic ? 'Boxes' : 'Cartons' }}
                                            </td>
                                            <td class="py-3 font-weight-bold text-success" style="font-size: 1rem;">
                                                {{ $group['total_pcs'] }} <span class="text-xs text-muted font-normal">pcs</span>
                                            </td>
                                            <td class="py-3 text-left text-xs text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ implode(', ', $group['package_references']) }}">
                                                @if($isDomestic)
                                                    {{ implode(', ', $group['package_references']) }}
                                                @else
                                                    {{ count($group['package_references']) > 0 ? '#' . implode(', #', $gRefs = $group['package_references']) : 'N/A' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted py-5">No packing data to summarize.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. DETAILED CARTON / BOX RECORDS GRID -->
        <section class="content mb-4">
            <div class="container-fluid">
                <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                    <div class="card-header bg-white py-3 border-0">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-md-4 mb-2 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <span class="header-indicator mr-2" style="width: 4px; height: 18px; background: #0ea5e9; display: inline-block; border-radius: 2px;"></span>
                                    <h5 class="font-weight-bold text-dark mb-0">Detailed Package Log</h5>
                                </div>
                            </div>
                            <div class="col-md-8 d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 10px;">
                                <!-- Search bar -->
                                <div class="input-group input-group-sm" style="max-width: 240px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="cartonSearch" class="form-control border-left-0" placeholder="Search No, Design, SKU...">
                                </div>
                                <!-- Location Filter -->
                                <select id="locationFilter" class="form-control form-control-sm" style="max-width: 200px;">
                                    <option value="">All Locations</option>
                                    @foreach($uniqueLocations as $uloc)
                                        <option value="{{ $uloc }}">{{ $uloc }}</option>
                                    @endforeach
                                </select>
                                <!-- All bar download -->
                                @if($isDomestic && $session->domesticInventories->count() > 0)
                                    <a href="{{ route('admin.packing.downloadAllDomesticBarcode', $session->slip_id) }}" class="btn btn-sm btn-outline-info font-weight-bold">
                                        <i class="fas fa-download mr-1"></i> Barcodes (TXT)
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 text-center text-sm" id="detailsTable">
                                <thead class="bg-light text-muted small text-uppercase" style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        @if($isDomestic)
                                            <th class="border-0 py-3 bg-light">Box Sequence</th>
                                            <th class="border-0 py-3 bg-light">Design</th>
                                            <th class="border-0 py-3 bg-light">Size Set</th>
                                            <th class="border-0 py-3 bg-light">Color</th>
                                            <th class="border-0 py-3 bg-light">Pcs/Box</th>
                                            <th class="border-0 py-3 bg-light">Total Boxes</th>
                                            <th class="border-0 py-3 bg-light">Total Pieces</th>
                                            <th class="border-0 py-3 bg-light">Storage Location</th>
                                            <th class="border-0 py-3 bg-light">Barcode</th>
                                        @else
                                            <th class="border-0 py-3 bg-light" width="10%">Carton #</th>
                                            <th class="border-0 py-3 bg-light text-left" width="40%">Design & Size Set Summary</th>
                                            <th class="border-0 py-3 bg-light" width="20%">Total Pieces</th>
                                            <th class="border-0 py-3 bg-light" width="30%">Storage Location</th>
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
                                                <td class="py-3 font-weight-bold text-info">
                                                    {{ $dom->box_no }} 
                                                    <span class="d-block text-xs text-muted font-weight-normal mt-1">(Carton #{{ $dom->carton_no }})</span>
                                                </td>
                                                <td class="py-3 font-weight-bold text-dark">
                                                    {{ $dom->product->design_number ?? 'N/A' }}
                                                    @php
                                                        $carton = $session->cartons->firstWhere('id', $dom->packing_carton_id);
                                                        $cartonLots = $carton ? $carton->items->pluck('lot_no')->filter()->unique()->values()->toArray() : [];
                                                    @endphp
                                                    @if(count($cartonLots) > 0)
                                                        <div class="mt-1">
                                                            @foreach($cartonLots as $clot)
                                                                <span class="badge badge-secondary" style="font-size: 10px; font-weight: normal;">Lot #{{ $clot }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="py-3 font-weight-bold text-slate">{{ $dom->sizeSet->name ?? 'N/A' }}</td>
                                                <td class="py-3"><span class="badge badge-light border">{{ $dom->color->name ?? 'N/A' }}</span></td>
                                                <td class="py-3">{{ $dom->quantity }} pcs</td>
                                                <td class="py-3 font-weight-bold text-primary">{{ $dom->total_boxes }}</td>
                                                <td class="py-3 font-weight-bold text-success">{{ $dom->quantity * $dom->total_boxes }} pcs</td>
                                                <td class="py-3 text-muted">
                                                    @if($dom->rack)
                                                        <span class="badge badge-info font-weight-normal">{{ $dloc }}</span>
                                                    @else
                                                        <span class="text-muted small">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 font-mono"><code>{{ $dom->barcode }}</code></td>
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
                                                <td class="py-3 font-weight-bold text-primary">#{{ $carton->carton_no }}</td>
                                                <td class="py-3 text-left">
                                                    @foreach($summaryData as $sum)
                                                        <div class="mb-1">
                                                            <strong class="text-dark">{{ $sum['design'] }}</strong>
                                                            <span class="text-muted ml-2">[{{ $sum['size_set'] }}]</span>
                                                            @if($sum['lot_no'])
                                                                <span class="badge badge-secondary ml-1" style="font-size: 10px; font-weight: normal;">Lot #{{ $sum['lot_no'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td class="py-3 font-weight-bold text-dark" style="font-size: 0.95rem;">
                                                    {{ $carton->items->sum('quantity') }} <span class="text-xs text-muted font-normal">pcs</span>
                                                </td>
                                                <td class="py-3 text-muted">
                                                    @if($carton->rack)
                                                        <span class="badge badge-info font-weight-normal">{{ $cloc }}</span>
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
            <section class="content pb-5">
                <div class="container-fluid">
                    <div class="card border-0 shadow-sm bg-white" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 border-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="header-indicator mr-2" style="width: 4px; height: 18px; background: #ef4444; display: inline-block; border-radius: 2px;"></span>
                                    <h5 class="font-weight-bold text-dark mb-0">Outflows, Reworks & Stock Adjustments</h5>
                                </div>
                                <span class="badge badge-light border text-muted px-3 py-1 font-weight-bold">
                                    Total: {{ $sessionOutflows->sum('quantity') + $sessionReworks->sum('quantity') }} pcs
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center text-sm">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="border-0 py-3">Adjustment Type</th>
                                            <th class="border-0 py-3">Lot No</th>
                                            <th class="border-0 py-3">Design Number</th>
                                            <th class="border-0 py-3">Size & Color</th>
                                            <th class="border-0 py-3">Quantity</th>
                                            <th class="border-0 py-3">Responsible Stage / Unit</th>
                                            <th class="border-0 py-3 text-left">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessionOutflows as $out)
                                            <tr class="border-bottom">
                                                <td class="py-3">
                                                    @php
                                                        $badgeClass = 'badge-soft-info';
                                                        if ($out->type == 'debit') $badgeClass = 'badge-soft-danger';
                                                        if ($out->type == 'sampling') $badgeClass = 'badge-soft-primary';
                                                        if ($out->type == 'dead') $badgeClass = 'badge-soft-dark';
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }} px-3 py-2 text-uppercase font-weight-bold text-xs" style="border-radius: 20px;">
                                                        {{ $out->type }}
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    @if($out->lot_no)
                                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11px;">#{{ $out->lot_no }}</span>
                                                    @else
                                                        <span class="text-muted text-xs">N/A</span>
                                                    @endif
                                                </td>
                                                <td class="py-3">
                                                    <strong class="text-dark">{{ $out->product->design_number ?? 'N/A' }}</strong>
                                                    <span class="d-block text-muted text-xs">{{ $out->product->name ?? 'Garment' }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-dark font-weight-bold">Size: {{ $out->size->size ?? 'N/A' }}</div>
                                                    <div class="text-muted text-xs">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="py-3 font-weight-bold text-dark">
                                                    {{ $out->quantity }} <span class="text-xs text-muted font-normal">PCS</span>
                                                </td>
                                                <td class="py-3 text-muted">{{ $out->responsibleUnit->name ?? 'N/A' }}</td>
                                                <td class="py-3 text-left text-muted text-xs">{{ $out->remarks ?? 'N/A' }}</td>
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
                                                <td class="py-3">
                                                    <span class="badge badge-soft-warning px-3 py-2 text-uppercase font-weight-bold text-xs" style="border-radius: 20px;">
                                                        <i class="fas fa-tools mr-1"></i> Rework
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 11px;">#{{ $rw->lot_no }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <strong class="text-dark">{{ $product->design_number ?? ($rw->lot_info->orderProductSet->design_number ?? 'N/A') }}</strong>
                                                    <span class="d-block text-muted text-xs">{{ $product->name ?? 'Garment' }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-dark font-weight-bold">Sizes: {{ $sizeDetails ?: 'N/A' }}</div>
                                                    <div class="text-muted text-xs">Color: {{ $color->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="py-3 font-weight-bold text-danger">
                                                    {{ $rw->quantity }} <span class="text-xs text-muted font-normal">PCS</span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-dark font-weight-bold">{{ $rw->toStage->name ?? 'Stage #' . $rw->to_stage_id }}</div>
                                                    <div class="text-muted text-xs">Unit: {{ $rw->toUnit->name ?? 'Unit #' . $rw->sub_stage_id_to }}</div>
                                                </td>
                                                <td class="py-3 text-left text-muted text-xs">{{ $rw->remarks ?: 'Defect return for rework' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .content-wrapper {
            font-family: 'Outfit', sans-serif !important;
        }

        /* Soft Badge styles */
        .badge-soft-primary {
            background: rgba(99, 102, 241, 0.1) !important;
            color: #6366f1 !important;
        }
        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.1) !important;
            color: #f59e0b !important;
        }
        .badge-soft-success {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }
        .badge-soft-danger {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
        }
        .badge-soft-info {
            background: rgba(14, 165, 233, 0.1) !important;
            color: #0ea5e9 !important;
        }
        .badge-soft-dark {
            background: rgba(30, 41, 59, 0.1) !important;
            color: #1e293b !important;
        }

        /* Stat Card Hover Effects */
        .stat-card {
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08) !important;
        }
        
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .text-xs {
            font-size: 0.75rem !important;
        }
        .text-slate {
            color: #64748b !important;
        }
        .bg-light {
            background-color: #f8fafc !important;
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
        });
    </script>
@endsection