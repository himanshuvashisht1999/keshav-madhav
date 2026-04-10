@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper bg-light">
        <!-- 1. PAGE HEADER (SESSION DATA) -->
        <section class="content-header py-4">
            <div class="container-fluid">
                <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 20px;">
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <div class="col-md-7 p-4 d-flex align-items-center">
                                <div class="customer-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-4"
                                    style="width: 70px; height: 70px; font-size: 1.8rem; font-weight: 700;">
                                    {{ substr($session->order->customer->name ?? 'C', 0, 1) }}
                                </div>
                                <div>
                                    <h1 class="m-0 font-weight-bold h3 text-dark">Packing Session #{{ $session->id }}</h1>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-file-invoice mr-1"></i> Order:
                                        <strong>#{{ $session->order->sku }}</strong> &bull;
                                        <i class="fas fa-user-tie ml-2 mr-1"></i> Customer:
                                        <strong>{{ $session->order->customer->name ?? 'N/A' }}</strong> &bull;
                                        <i class="fas fa-calendar-alt ml-2 mr-1"></i> Packing Date:
                                        {{ date('d M, Y', strtotime($session->packing_date)) }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-5 bg-primary d-flex align-items-center justify-content-end p-4 text-nowrap">
                                <div class="mr-4 text-center">
                                    <a href="{{ route('admin.packing.downloadPrn', $session->id) }}" class="btn btn-warning btn-sm font-weight-bold px-3 py-2 shadow-sm mb-0" 
                                       title="Download PRN file for Barcode Printer">
                                        <i class="fas fa-barcode mr-1"></i> PRINT BARCODES (PRN)
                                    </a>
                                </div>
                                <div class="text-right text-white mr-4">
                                    <span class="text-uppercase small font-weight-bold d-block mb-1"
                                        style="letter-spacing: 1px; opacity: 0.8;">Slip Reference</span>
                                    <h4 class="font-weight-bold mb-0">#{{ $session->slip_id }}</h4>
                                </div>
                                <div class="text-center bg-white rounded p-3" style="min-width: 140px;">
                                    <span
                                        class="text-uppercase x-small font-weight-bold d-block text-muted mb-1">Status</span>
                                    <h6
                                        class="font-weight-bold mb-0 {{ $session->status == 1 ? 'text-success' : 'text-warning' }}">
                                        <i
                                            class="fas {{ $session->status == 1 ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                        {{ $session->status == 1 ? 'Finalized' : 'In-Progress' }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. QUICK STATS (SESSION SUMMARY) -->
        <section class="content mb-4">
            <div class="container-fluid">
                @php
                    $totalCartons = $session->cartons->count();
                    $totalBoxes = $session->boxes->count();
                    $corpBoxes = $session->boxes->where('box_type', 'corporate')->count();
                    $domesticBoxes = $session->boxes->whereIn('box_type', ['domestic', 'corporate_domestic', 'packing_divert'])->count();
                    $totalItems = $session->items->sum('quantity');
                    $outflowItems = $session->outflows->where('type', '!=', 'packing_divert')->sum('quantity');
                @endphp
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3"
                            style="border-radius: 15px;">
                            <div class="icon-box bg-soft-warning text-warning mr-3 rounded-lg d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-archive"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase x-small font-weight-bold d-block">Total
                                    Cartons</span>
                                <h5 class="font-weight-bold mb-0">{{ $totalCartons }} Units</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3"
                            style="border-radius: 15px;">
                            <div class="icon-box bg-soft-info text-info mr-3 rounded-lg d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-boxes"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase x-small font-weight-bold d-block">Internal
                                    Boxes</span>
                                <h5 class="font-weight-bold mb-0">{{ $totalBoxes }} Total</h5>
                                <small class="text-muted">{{ $corpBoxes }} Corp | {{ $domesticBoxes }} Dom</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3"
                            style="border-radius: 15px;">
                            <div class="icon-box bg-soft-success text-success mr-3 rounded-lg d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase x-small font-weight-bold d-block">Packed Items</span>
                                <h5 class="font-weight-bold mb-0">{{ $totalItems }} Pieces</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3"
                            style="border-radius: 15px;">
                            <div class="icon-box bg-soft-danger text-danger mr-3 rounded-lg d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <div>
                                <span class="text-muted text-uppercase x-small font-weight-bold d-block">Debit /
                                    Sampling</span>
                                <h5 class="font-weight-bold mb-0">{{ $outflowItems }} Pieces</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-light border shadow-none mt-2 d-flex align-items-center justify-content-between"
                    style="border-radius: 12px; background: rgba(255,255,255,0.5);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-muted mr-3"></i>
                        <span class="text-muted small">This packing session was created from slip
                            <strong>#{{ $session->slip_id }}</strong> on
                            <strong>{{ date('d-m-Y H:i', strtotime($session->created_at)) }}</strong>.</span>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.uploaded-slips.show', $session->slip_id) }}"
                            class="btn btn-sm btn-link text-muted" target="_blank">
                            View Original Slip <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. DETAILED BREAKDOWN -->
        <section class="content pb-5">
            <div class="container-fluid">

                @php
                    $corpBoxTypes = ['corporate', 'planner', 'bulk'];
                    $domBoxTypes = ['domestic', 'corporate_domestic', 'packing_divert', 'domestic_planner'];

                    $corpCartons = $session->cartons()->whereHas('boxes', function ($q) use ($corpBoxTypes) {
                        $q->whereIn('box_type', $corpBoxTypes);
                    })->get();
                @endphp

                <!-- A. CORPORATE PACKING SECTION -->
                @if($corpCartons->count() > 0)
                    <div class="d-flex align-items-center mb-4 pt-2">
                        <div style="width: 30px; height: 2px; background: #007bff; margin-right: 15px;"></div>
                        <h4 class="font-weight-bold text-dark mb-0">Corporate Order Packing</h4>
                        <div class="flex-grow-1 ml-3" style="height: 2px; background: #dee2e6;"></div>
                    </div>

                    <div class="card shadow-sm border-0 bg-white mb-5" style="border-radius: 20px;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-primary text-white small text-uppercase">
                                        <tr>
                                            <th class="border-0 px-4 py-3">Carton #</th>
                                            <th class="border-0 py-3">Corp Boxes</th>
                                            <th class="border-0 py-3">Design & Size Set</th>
                                            <th class="border-0 py-3 text-center">Total Pcs</th>
                                            <th class="border-0 py-3 pr-4">Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($corpCartons as $carton)
                                            @php
                                                $corpBoxesInCarton = $carton->boxes->whereIn('box_type', $corpBoxTypes);
                                                $summaryData = $carton->items->filter(fn($i) => $i->box && in_array($i->box->box_type, $corpBoxTypes))->map(function ($item) {
                                                    $set = $item->detail ? $item->detail->orderProductSet : null;
                                                    return [
                                                        'design' => $set->design_number ?? 'N/A',
                                                        'size_set' => $set->size_measurement->name ?? 'N/A'
                                                    ];
                                                })->unique()->values();
                                            @endphp
                                            <tr class="border-bottom">
                                                <td class="px-4 py-3 font-weight-bold text-primary">#{{ $carton->carton_no }}</td>
                                                <td class="py-3">
                                                    <span class="badge badge-primary px-3 py-2">
                                                        {{ $corpBoxesInCarton->count() }} Boxes
                                                    </span>
                                                </td>
                                                <td class="py-3">
                                                    @foreach($summaryData as $sum)
                                                        <div class="small">
                                                            <span class="font-weight-bold">{{ $sum['design'] }}</span>
                                                            <span class="text-muted ml-1">[{{ $sum['size_set'] }}]</span>
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td class="py-3 text-center">
                                                    <span
                                                        class="h6 font-weight-bold text-dark">{{ $carton->items->filter(fn($i) => in_array(optional($i->box)->box_type, $corpBoxTypes))->sum('quantity') }}</span>
                                                </td>
                                                <td class="py-3 pr-4 text-muted small">
                                                    {{ $carton->rack->storeroom->name ?? 'N/A' }} /
                                                    {{ $carton->rack->name ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- B. DOMESTIC PACKING SECTION -->
                @php
                    $domBoxGroups = $session->boxes()->whereIn('box_type', $domBoxTypes)
                        ->with(['domesticInventory.product.series', 'domesticInventory.color', 'domesticInventory.sizeSet', 'items.detail.orderProductSet', 'carton.rack.storeroom'])
                        ->get()
                        ->groupBy('barcode');
                @endphp

                @if($domBoxGroups->count() > 0)
                    <div class="d-flex align-items-center mb-4 pt-2">
                        <div style="width: 30px; height: 2px; background: #17a2b8; margin-right: 15px;"></div>
                        <h4 class="font-weight-bold text-dark mb-0">Domestic Stock Packing</h4>
                        <div class="flex-grow-1 ml-3" style="height: 2px; background: #dee2e6;"></div>
                    </div>

                    <div class="card shadow-sm border-0 bg-white mb-5" style="border-radius: 20px;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-info text-white small text-uppercase">
                                        <tr>
                                            <th class="border-0 px-4 py-3">Barcode</th>
                                            <th class="border-0 py-3">Dom Boxes</th>
                                            <th class="border-0 py-3">Design & Detail</th>
                                            <th class="border-0 py-3 pr-4 text-center">Total Pcs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($domBoxGroups as $barcode => $group)
                                                @php
                                                    $firstBox = $group->first();
                                                    $di = $firstBox->domesticInventory;
                                                    $totalPcs = $group->flatMap->items->sum('quantity');
                                                    
                                                    $design = $di->product->design_number ?? ($firstBox->items->first()->detail->orderProductSet->design_number ?? 'D-Unk');
                                                    $prodName = $di->product_name ?? 'Domestic Stock';
                                                    $detailInfo = $di ? ($di->sizeSet->name . ' / ' . $di->color->name) : 'Misc Domestic';
                                                @endphp
                                                <tr class="border-bottom">
                                                    <td class="px-4 py-3">
                                                        <div class="font-weight-bold text-info">{{ $barcode }}</div>
                                                    </td>
                                                    <td class="py-3">
                                                        <span class="badge badge-info px-3 py-2 text-white">
                                                            {{ $group->count() }} Boxes
                                                        </span>
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="small">
                                                            <div class="font-weight-bold text-dark">{{ $prodName }}</div>
                                                            <div class="text-muted">
                                                                <span class="font-weight-bold">{{ $design }}</span>
                                                                <span class="ml-1">({{ $detailInfo }})</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <td class="py-3 pr-4 text-center">
                                                    <span class="h6 font-weight-bold text-dark">{{ $totalPcs }}</span>
                                                    <div class="x-small text-muted font-weight-bold">PCS</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- B. OUTFLOWS SECTION (DEBIT, SAMPLING, ETC) -->
                @php
                    $sessionOutflows = $session->outflows->where('type', '!=', 'packing_divert');
                @endphp
                @if($sessionOutflows->count() > 0)
                    <div class="d-flex align-items-center mb-4 pt-2">
                        <div style="width: 30px; height: 2px; background: #dee2e6; margin-right: 15px;"></div>
                        <h4 class="font-weight-bold text-dark mb-0">Outflows & Stock Adjustments</h4>
                        <div class="flex-grow-1 ml-3" style="height: 2px; background: #dee2e6;"></div>
                    </div>

                    <div class="card shadow-sm border-0 bg-white" style="border-radius: 20px;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="border-0 px-4 py-3">Type</th>
                                            <th class="border-0 py-3">Product / Design</th>
                                            <th class="border-0 py-3">Size & Color</th>
                                            <th class="border-0 py-3 text-center">Quantity</th>
                                            <th class="border-0 py-3">Responsible Unit</th>
                                            <th class="border-0 py-3 pr-4">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sessionOutflows as $out)
                                            <tr class="border-bottom">
                                                <td class="px-4 py-3">
                                                    @php
                                                        $badgeClass = 'badge-soft-info';
                                                        if ($out->type == 'debit')
                                                            $badgeClass = 'badge-soft-danger';
                                                        if ($out->type == 'sampling')
                                                            $badgeClass = 'badge-soft-primary';
                                                        if ($out->type == 'dead')
                                                            $badgeClass = 'badge-soft-dark';
                                                    @endphp
                                                    <span
                                                        class="badge {{ $badgeClass }} px-3 py-2 text-uppercase">{{ $out->type }}</span>
                                                </td>
                                                <td class="py-3">
                                                    <div class="font-weight-bold text-dark">
                                                        {{ $out->product->design_number ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $out->product->name ?? 'Grament' }}</small>
                                                </td>
                                                <td class="py-3">
                                                    <div class="text-dark small">Size:
                                                        <strong>{{ $out->size->size ?? 'N/A' }}</strong></div>
                                                    <div class="text-muted x-small">Color: {{ $out->color->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="py-3 text-center">
                                                    <span class="h6 font-weight-bold text-dark">{{ $out->quantity }}</span>
                                                    <div class="x-small text-muted font-weight-bold">PCS</div>
                                                </td>
                                                <td class="py-3">
                                                    <span class="small text-muted">{{ $out->responsibleUnit->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="py-3 pr-4">
                                                    <span class="small text-muted">{{ $out->remarks }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if($totalCartons == 0 && $session->outflows->count() == 0)
                    <div class="text-center py-5 bg-white shadow-sm" style="border-radius: 20px;">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4816154-4017688.png"
                            style="width: 200px; opacity: 0.5;" alt="Empty">
                        <p class="text-muted mt-3 h5">No packing data found in this session.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .content-wrapper {
            font-family: 'Outfit', sans-serif !important;
        }

        .bg-soft-primary {
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .bg-soft-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .bg-soft-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .bg-soft-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .bg-soft-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .bg-soft-dark {
            background: rgba(52, 58, 64, 0.1);
            color: #343a40;
        }

        .stat-card {
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .carton-card {
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .carton-card:hover {
            border-color: rgba(0, 123, 255, 0.2);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.05) !important;
        }

        .box-item {
            transition: background 0.2s ease;
        }

        .box-item:hover {
            background: #f8faff !important;
        }

        .x-small {
            font-size: 0.72rem;
        }
    </style>
@endsection