@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header pb-2">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <div class="d-flex align-items-center mb-1">
                        <a href="{{ route('admin.reports.slips') }}" class="btn btn-sm btn-outline-secondary mr-3 shadow-xs">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Slips Report
                        </a>
                        <h1 class="font-weight-bold text-dark m-0" style="font-size: 22px;">
                            Slip Report <span class="text-primary">#{{ $slip->id }}</span>
                            @if($slip->bill_number)
                                <span class="badge badge-light border text-muted ml-2 font-weight-normal" style="font-size: 13px;">
                                    Bill: <strong>{{ $slip->bill_number }}</strong>
                                </span>
                            @endif
                        </h1>
                    </div>
                    <p class="text-muted small mb-0">Detailed entry-by-entry production analysis, size matrix, and outflow tracking</p>
                </div>
                <div class="mt-2 mt-md-0">
                    <button onclick="window.print()" class="btn btn-sm btn-outline-dark shadow-sm px-3 mr-2 font-weight-bold">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                    <a href="{{ route('admin.reports.slips.pdf', $slip->id) }}" class="btn btn-sm btn-danger shadow-sm px-3 font-weight-bold">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- SLIP METADATA & KPI SUMMARY CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        
                        <!-- Left: Core Metadata -->
                        <div class="col-lg-7 border-right-lg pr-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6 mb-3">
                                    <div class="text-xs text-uppercase font-weight-bold text-muted mb-1">From Stage & Unit</div>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded p-2 mr-2 text-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark" style="font-size: 15px;">{{ $slip->fromStage->name ?? '-' }}</div>
                                            <small class="text-muted"><i class="fas fa-user-tie mr-1"></i>{{ $slip->getUnitMaster->name ?? 'Admin / In-house' }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <div class="text-xs text-uppercase font-weight-bold text-muted mb-1">Date & Time</div>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-info text-white rounded p-2 mr-2 text-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark" style="font-size: 15px;">{{ $slip->created_at->format('d M, Y') }}</div>
                                            <small class="text-muted">{{ $slip->created_at->format('h:i A') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <div class="text-xs text-uppercase font-weight-bold text-muted mb-1">Digitization Status</div>
                                    @if($slip->status == 1)
                                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-check-circle mr-1"></i> Digitized & Verified
                                        </span>
                                    @elseif($slip->status == 0)
                                        <span class="badge badge-warning text-dark px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-clock mr-1"></i> Pending Verification
                                        </span>
                                    @else
                                        <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-times-circle mr-1"></i> Skipped
                                        </span>
                                    @endif
                                </div>

                                <div class="col-sm-6 mb-2">
                                    <div class="text-xs text-uppercase font-weight-bold text-muted mb-1">Physical Slip Photo</div>
                                    @if($slip->image)
                                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold" data-toggle="modal" data-target="#slipPhotoModal">
                                            <i class="fas fa-image mr-1"></i> View Original Scan
                                        </button>
                                    @else
                                        <span class="text-muted small">No photo attached</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: KPI Summary Stats -->
                        <div class="col-lg-5 pl-lg-4 mt-3 mt-lg-0">
                            <div class="p-3 bg-light rounded-lg border">
                                <div class="text-xs text-uppercase font-weight-bold text-muted mb-2 tracking-wider">Slip Summary Metrics</div>
                                <div class="row text-center">
                                    <div class="col-4 border-right">
                                        <div class="text-xs text-muted font-weight-bold">TOTAL LOTS</div>
                                        <div class="h4 font-weight-bold text-primary mb-0">{{ $summary['total_lots'] }}</div>
                                    </div>
                                    <div class="col-4 border-right">
                                        <div class="text-xs text-muted font-weight-bold">ENTRIES</div>
                                        <div class="h4 font-weight-bold text-dark mb-0">{{ $summary['total_sessions'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs text-muted font-weight-bold">TOTAL PCS</div>
                                        <div class="h4 font-weight-bold text-success mb-0">{{ number_format($summary['total_pieces']) }}</div>
                                    </div>
                                </div>
                                @if($summary['total_moved_outflow'] > 0 || $summary['total_remaining_balance'] > 0)
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted font-weight-bold">Moved to Next Stages:</span>
                                        <span class="font-weight-bold text-dark">{{ number_format($summary['total_moved_outflow']) }} pcs</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mt-1">
                                        <span class="text-muted font-weight-bold">Current Remaining Balance:</span>
                                        <span class="font-weight-bold text-warning">{{ number_format($summary['total_remaining_balance']) }} pcs</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- MODAL: ORIGINAL SLIP PHOTO -->
            @if($slip->image)
                <div class="modal fade" id="slipPhotoModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-dark text-white py-2 px-3">
                                <h6 class="modal-title font-weight-bold mb-0">
                                    <i class="fas fa-file-image mr-2"></i>Physical Slip Scan - #{{ $slip->id }}
                                </h6>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body text-center p-2 bg-light">
                                <img src="{{ asset('storage/' . $slip->image) }}" class="img-fluid rounded shadow-sm" alt="Slip Photo" style="max-height: 85vh;">
                            </div>
                            <div class="modal-footer py-2 px-3 justify-content-between">
                                <a href="{{ asset('storage/' . $slip->image) }}" target="_blank" class="btn btn-sm btn-primary font-weight-bold">
                                    <i class="fas fa-external-link-alt mr-1"></i> Open Full Image
                                </a>
                                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- MULTI-ENTRY DETAIL SECTIONS -->

            {{-- 1. CUTTING SESSIONS (LOTS & FABRIC ROLLS) --}}
            @if($lots->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border-top: 4px solid #10b981 !important;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-cut text-success mr-2"></i>Cutting & Fabric Roll Entries 
                            <span class="badge badge-success font-weight-bold ml-2">{{ $lots->count() }} {{ Str::plural('Lot', $lots->count()) }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @foreach($lots as $idx => $lot)
                            @php
                                $set = $lot->orderProductSet;
                                $lotRolls = $rolls->where('lot_no', $lot->lot_no);
                                $totalLotPieces = 0;
                                foreach($lotRolls as $r) {
                                    if ($r->fabricRollAssigningsDetail) $totalLotPieces += $r->fabricRollAssigningsDetail->sum('quantity');
                                }
                            @endphp
                            <div class="card border rounded-lg mb-3 shadow-xs">
                                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-dark px-2 py-1 mr-2" style="font-size: 13px;">Lot #{{ $lot->lot_no }}</span>
                                        <strong class="text-dark">{{ $set->design_number ?? 'Design N/A' }}</strong>
                                        <span class="text-muted ml-2">({{ $set->orderMain->sku ?? 'SKU N/A' }} &bull; {{ $set->orderMain->customer->name ?? 'Customer N/A' }})</span>
                                    </div>
                                    <div>
                                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 13px;">
                                            Total Cut: {{ number_format($totalLotPieces) }} pcs
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <!-- Attributes Grid -->
                                    <div class="row g-2 mb-3 small">
                                        <div class="col-md-3"><strong>Fabric:</strong> <span class="text-muted">{{ $set->fabric_names ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Color:</strong> <span class="text-muted">{{ $set->colors->name ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Pattern:</strong> <span class="text-muted">{{ $set->master_design_pattern->name ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Fitting:</strong> <span class="text-muted">{{ $set->master_product_fitting->name ?? '-' }}</span></div>
                                    </div>

                                    <!-- Rolls & Size Breakdown Table -->
                                    @if($lotRolls->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead style="background: #f1f5f9; font-size: 11px; text-transform: uppercase;">
                                                    <tr>
                                                        <th>Roll No</th>
                                                        <th>Warehouse</th>
                                                        <th>Color</th>
                                                        <th>Size Breakdown (Pieces)</th>
                                                        <th class="text-right">Total Pcs</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 12px;">
                                                    @foreach($lotRolls as $roll)
                                                        <tr>
                                                            <td class="font-weight-bold text-dark">Roll #{{ $roll->fabric_roll_id ?? $roll->id }}</td>
                                                            <td>{{ $roll->stageMasterUnit->masterFabricWarehouse->cutting_master_name ?? '-' }}</td>
                                                            <td><span class="badge badge-light border">{{ $set->colors->name ?? '-' }}</span></td>
                                                            <td>
                                                                @if($roll->fabricRollAssigningsDetail)
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        @foreach($roll->fabricRollAssigningsDetail as $det)
                                                                            <span class="badge badge-secondary mr-1 mb-1" style="font-size: 11px;">
                                                                                Size {{ $det->size }}: <strong>{{ $det->quantity }}</strong>
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right font-weight-bold text-success">
                                                                {{ number_format($roll->fabricRollAssigningsDetail ? $roll->fabricRollAssigningsDetail->sum('quantity') : 0) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 2. PRINTING TRANSACTIONS --}}
            @if($printings->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border-top: 4px solid #3b82f6 !important;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-print text-primary mr-2"></i>Printing Stage Entries 
                            <span class="badge badge-primary font-weight-bold ml-2">{{ $printings->count() }} {{ Str::plural('Entry', $printings->count()) }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @foreach($printings as $pt)
                            @php
                                $set = $pt->orderProduct ? $pt->orderProduct->orderProductSet : null;
                            @endphp
                            <div class="card border rounded-lg mb-3 shadow-xs">
                                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-dark px-2 py-1 mr-2" style="font-size: 13px;">Lot #{{ $pt->lot_no }}</span>
                                        <strong class="text-dark">{{ $set->design_number ?? 'Design N/A' }}</strong>
                                        <span class="text-muted ml-2">({{ $set->orderMain->sku ?? 'SKU N/A' }} &bull; {{ $set->orderMain->customer->name ?? 'Customer N/A' }})</span>
                                    </div>
                                    <div>
                                        <span class="badge badge-info px-2 py-1 mr-2">To: {{ $pt->to_stage->name ?? 'Printing' }} ({{ $pt->getToUnitMaster->name ?? 'N/A' }})</span>
                                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 13px;">
                                            Qty: {{ number_format($pt->quantity) }} pcs
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    @if($pt->details && $pt->details->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase;">
                                                    <tr>
                                                        <th>Size</th>
                                                        <th>Color</th>
                                                        <th class="text-center">Assigned / Input Qty</th>
                                                        <th class="text-center">Moved Outflow</th>
                                                        <th class="text-right">Current Remaining</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 12px;">
                                                    @foreach($pt->details as $d)
                                                        <tr>
                                                            <td class="font-weight-bold">Size {{ $d->size }}</td>
                                                            <td><span class="badge badge-light border">{{ $set->colors->name ?? '-' }}</span></td>
                                                            <td class="text-center font-weight-bold">{{ $d->quantity }}</td>
                                                            <td class="text-center text-muted">{{ max(0, $d->quantity - ($d->remaining_quantity ?? $d->quantity)) }}</td>
                                                            <td class="text-right font-weight-bold text-primary">{{ $d->remaining_quantity ?? $d->quantity }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. INTERMEDIATE STAGE TRANSACTIONS (STITCHING, WASHING, FINISHING, GODAM) --}}
            @if($stage_transactions->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border-top: 4px solid #f59e0b !important;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-exchange-alt text-warning mr-2"></i>Stage Production & Transfer Entries
                            <span class="badge badge-warning text-dark font-weight-bold ml-2">{{ $stage_transactions->count() }} {{ Str::plural('Entry', $stage_transactions->count()) }}</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @foreach($stage_transactions as $st)
                            @php
                                $set = $st->orderProduct ? $st->orderProduct->orderProductSet : null;
                            @endphp
                            <div class="card border rounded-lg mb-3 shadow-xs">
                                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="my-1">
                                        <span class="badge badge-dark px-2 py-1 mr-2" style="font-size: 13px;">Lot #{{ $st->lot_no }}</span>
                                        <strong class="text-dark">{{ $set->design_number ?? 'Design N/A' }}</strong>
                                        <span class="text-muted ml-2">({{ $set->orderMain->sku ?? 'SKU N/A' }} &bull; {{ $set->orderMain->customer->name ?? 'Customer N/A' }})</span>
                                    </div>
                                    <div class="my-1">
                                        <span class="badge badge-light border text-dark px-2 py-1 mr-2">
                                            {{ $st->from_stage->name ?? 'Prev Stage' }} &rarr; <strong>{{ $st->to_stage->name ?? 'Next Stage' }}</strong>
                                            @if($st->getToUnitMaster) ({{ $st->getToUnitMaster->name }}) @endif
                                        </span>
                                        <span class="badge badge-success px-3 py-1 font-weight-bold" style="font-size: 13px;">
                                            Qty: {{ number_format($st->quantity) }} pcs
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2 mb-2 small">
                                        <div class="col-md-3"><strong>Fabric:</strong> <span class="text-muted">{{ $set->fabric_names ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Color:</strong> <span class="text-muted">{{ $set->colors->name ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Size Set:</strong> <span class="text-muted">{{ $set->size_measurement->name ?? '-' }}</span></div>
                                        <div class="col-md-3"><strong>Remarks:</strong> <span class="text-muted">{{ $st->remarks ?? '-' }}</span></div>
                                    </div>

                                    @if($st->details && $st->details->isNotEmpty())
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase;">
                                                    <tr>
                                                        <th>Size</th>
                                                        <th>Color</th>
                                                        <th class="text-center">Assigned / Inflow Qty</th>
                                                        <th class="text-center">Moved Outflow</th>
                                                        <th class="text-right">Current Remaining</th>
                                                    </tr>
                                                </thead>
                                                <tbody style="font-size: 12px;">
                                                    @foreach($st->details as $d)
                                                        <tr>
                                                            <td class="font-weight-bold">Size {{ $d->size }}</td>
                                                            <td><span class="badge badge-light border">{{ $set->colors->name ?? '-' }}</span></td>
                                                            <td class="text-center font-weight-bold">{{ $d->quantity }}</td>
                                                            <td class="text-center text-muted">{{ max(0, $d->quantity - ($d->remaining_quantity ?? $d->quantity)) }}</td>
                                                            <td class="text-right font-weight-bold text-primary">{{ $d->remaining_quantity ?? $d->quantity }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 4. PACKING DETAILS --}}
            @if($packing_details->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden; border-top: 4px solid #6366f1 !important;">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-boxes text-indigo mr-2"></i>Packing & Carton Details
                            <span class="badge badge-primary font-weight-bold ml-2">{{ $packing_details->count() }} Sessions</span>
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        @foreach($packing_details as $pm)
                            <div class="card border rounded-lg mb-3 shadow-xs">
                                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge badge-dark px-2 py-1 mr-2">Packing Session #{{ $pm->id }}</span>
                                        <strong>Order SKU: {{ $pm->order->sku ?? 'N/A' }}</strong> &bull; {{ $pm->order->customer->name ?? 'Customer N/A' }}
                                    </div>
                                    <div>
                                        <span class="badge badge-info px-2 py-1">Cartons: {{ $pm->cartons->count() }}</span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase;">
                                                <tr>
                                                    <th>Carton / Box No</th>
                                                    <th>Lots Packed</th>
                                                    <th>Design & Size Set</th>
                                                    <th class="text-right">Packed Pieces</th>
                                                </tr>
                                            </thead>
                                            <tbody style="font-size: 12px;">
                                                @foreach($pm->cartons as $carton)
                                                    @php
                                                        $cartonLots = $carton->items->pluck('lot_no')->filter()->unique()->values()->toArray();
                                                        $totalCartonQty = $carton->items->sum('quantity');
                                                    @endphp
                                                    <tr>
                                                        <td class="font-weight-bold">Carton #{{ $carton->carton_no }}</td>
                                                        <td>
                                                            @foreach($cartonLots as $clot)
                                                                <span class="badge badge-secondary mr-1">#{{ $clot }}</span>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach($carton->items->groupBy('order_product_set_detail_id') as $items)
                                                                @php $firstItem = $items->first(); @endphp
                                                                <div class="small">
                                                                    {{ $firstItem->detail->orderProductSet->design_number ?? '-' }} 
                                                                    [{{ $firstItem->detail->orderProductSet->size_measurement->name ?? '-' }}]
                                                                </div>
                                                            @endforeach
                                                        </td>
                                                        <td class="text-right font-weight-bold text-success">{{ number_format($totalCartonQty) }} pcs</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>
</div>
@endsection
