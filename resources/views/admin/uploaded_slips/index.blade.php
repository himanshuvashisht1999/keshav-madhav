@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- 1. SLIM ERP HEADER BAR -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas {{ !empty($is_packing) ? 'fa-box-open' : 'fa-file-invoice' }} text-primary"></i>
            <span>{{ !empty($is_packing) ? 'Uploaded Slips (Packing)' : 'Uploaded Slips' }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.reports.slips') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-chart-bar"></i> Slip-wise Report
            </a>
            @if(!empty($is_packing))
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-file-upload"></i> All Slips
                </a>
            @else
                <a href="{{ route('admin.uploaded-slips.packing') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-box-open"></i> Packing Slips
                </a>
            @endif
        </div>
    </div>

    <!-- 2. COMPACT ERP FILTER BAR -->
    <div class="erp-filter-bar mb-2">
        <form method="GET" action="{{ !empty($is_packing) ? route('admin.uploaded-slips.packing') : route('admin.uploaded-slips.index') }}">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Lot Number</label>
                    <input type="text" name="lot_no" class="form-control erp-input" placeholder="Search Lot..." value="{{ request('lot_no') }}">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-file-invoice mr-1"></i> Bill No</label>
                    <input type="text" name="bill_number" class="form-control erp-input" placeholder="Search Bill..." value="{{ request('bill_number') }}">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-sign-out-alt mr-1"></i> From Stage</label>
                    <select name="from_stage_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL STAGES --</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ request('from_stage_id') == $stage->id ? 'selected' : '' }}>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-sign-in-alt mr-1"></i> To Stage</label>
                    <select name="to_stage_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL STAGES --</option>
                        @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ request('to_stage_id') == $stage->id ? 'selected' : '' }}>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-industry mr-1"></i> Unit</label>
                    <select name="stage_master_unit_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL UNITS --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('stage_master_unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-info-circle mr-1"></i> Status</label>
                    <select name="status" class="form-control erp-input">
                        <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>-- ALL STATUSES --</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                        <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Digitized</option>
                        <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Skipped</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Date</label>
                    <input type="date" name="date" class="form-control erp-input" value="{{ request('date') }}">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ !empty($is_packing) ? route('admin.uploaded-slips.packing') : route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- 3. ERP TABLE CARD -->
    <div class="erp-card bg-white mb-2" style="border-radius: 8px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0 text-sm">
                    <thead>
                        <tr>
                            <th width="90" class="text-center">Slip ID</th>
                            <th width="100">Date</th>
                            <th>From Stage</th>
                            <th>Unit</th>
                            <th>Lot No & Quantities</th>
                            <th>Bill / Slip Target</th>
                            <th>To Stage</th>
                            <th class="text-center" width="100">Status</th>
                            <th class="text-right pr-3" width="160">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slips as $slip)
                        <tr class="border-bottom">
                            <td class="text-center">
                                <div class="font-weight-bold" style="color: var(--erp-green-primary); font-size: 13px;">
                                    #{{ $slip->id }}
                                </div>
                                @if(!empty($slip->slip_file))
                                    <button type="button" 
                                            class="btn btn-xs py-0 px-1 mt-1 btn-view-slip-photo"
                                            style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; border-radius: 4px; font-size: 10px; font-weight: 600;"
                                            data-slip-id="{{ $slip->id }}"
                                            data-slip-url="{{ asset('assets/production_slips/' . $slip->slip_file) }}"
                                            data-slip-date="{{ $slip->created_at->format('d M Y, h:i A') }}"
                                            data-slip-unit="{{ $slip->getUnitMaster->name ?? '-' }}"
                                            data-slip-from="{{ $slip->fromStage->name ?? '-' }}"
                                            data-slip-to="{{ $slip->toStage->name ?? '-' }}"
                                            data-slip-bill="{{ $slip->bill_number ?? '-' }}"
                                            data-slip-pieces="{{ $slip->total_pieces ?? '-' }}"
                                            data-slip-show-url="{{ route('admin.uploaded-slips.show', $slip->id) }}"
                                            title="View Unit Uploaded Slip Image">
                                        <i class="fas fa-image mr-1"></i> Slip
                                    </button>
                                @endif
                            </td>
                            <td class="text-muted font-weight-normal">
                                {{ $slip->created_at->format('d M Y') }}
                            </td>
                            <td class="font-weight-semibold text-dark">
                                {{ $slip->fromStage->name ?? '-' }}
                            </td>
                            <td class="text-muted">
                                {{ $slip->getUnitMaster->name ?? '-' }}
                            </td>
                            <td>
                                @php
                                    $lotMap = [];
                                    
                                    // 1. Cutting lots (Type 1)
                                    if ($slip->orderLots && $slip->orderLots->isNotEmpty()) {
                                        foreach ($slip->orderLots as $ol) {
                                            $qty = 0;
                                            if ($slip->fabricRollAssignings) {
                                                foreach ($slip->fabricRollAssignings->where('order_lot_id', $ol->id) as $roll) {
                                                    if ($roll->fabricRollAssigningsDetail) {
                                                        $qty += $roll->fabricRollAssigningsDetail->sum('quantity');
                                                    }
                                                }
                                            }
                                            $lNo = (string)$ol->lot_no;
                                            $lotMap[$lNo] = ($lotMap[$lNo] ?? 0) + $qty;
                                        }
                                    }

                                    // 2. Printing stage transactions (Type 2)
                                    if ($slip->orderPrintingStageTransaction && $slip->orderPrintingStageTransaction->isNotEmpty()) {
                                        foreach ($slip->orderPrintingStageTransaction as $pt) {
                                            $lNo = (string)$pt->lot_no;
                                            $lotMap[$lNo] = ($lotMap[$lNo] ?? 0) + ($pt->quantity ?? 0);
                                        }
                                    }

                                    // 3. Stage transactions (Type 3: Stitching / Transfers / etc.)
                                    if ($slip->orderStageTransaction && $slip->orderStageTransaction->isNotEmpty()) {
                                        foreach ($slip->orderStageTransaction as $st) {
                                            $lNo = (string)$st->lot_no;
                                            $lotMap[$lNo] = ($lotMap[$lNo] ?? 0) + ($st->quantity ?? 0);
                                        }
                                    }

                                    // 4. Printing to Stitching transactions
                                    if ($slip->orderPrintingToStichingTransaction && $slip->orderPrintingToStichingTransaction->isNotEmpty()) {
                                        foreach ($slip->orderPrintingToStichingTransaction as $pst) {
                                            $lNo = (string)$pst->lot_no;
                                            $lotMap[$lNo] = ($lotMap[$lNo] ?? 0) + ($pst->quantity ?? 0);
                                        }
                                    }

                                    // 5. Godam stage transactions
                                    if ($slip->orderGodamStageTransaction && $slip->orderGodamStageTransaction->isNotEmpty()) {
                                        foreach ($slip->orderGodamStageTransaction as $gt) {
                                            $lNo = (string)$gt->lot_no;
                                            $lotMap[$lNo] = ($lotMap[$lNo] ?? 0) + ($gt->quantity ?? 0);
                                        }
                                    }

                                    // Fallback: If no linked sessions exist, but slip has lot_no
                                    if (empty($lotMap) && !empty($slip->lot_no)) {
                                        $lotMap[(string)$slip->lot_no] = 0;
                                    }

                                    $totalSlipQty = array_sum($lotMap);
                                @endphp

                                @if(!empty($lotMap))
                                    @foreach($lotMap as $lot => $lotQty)
                                        <span class="badge mr-1 mb-1 px-1.5 py-0.5" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-size: 11px; font-weight: 600;">
                                            #{{ $lot }} @if($lotQty > 0) ({{ $lotQty }} pcs) @endif
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge border px-2 py-1 text-dark" style="background: #f8fafc; font-weight: 500;">
                                    {{ $slip->bill_number ?? '-' }}
                                    @if(isset($totalSlipQty) && $totalSlipQty > 0)
                                        ({{ $totalSlipQty }})
                                    @endif
                                </span>
                                @if(!empty($slip->total_pieces))
                                    <div class="mt-1 font-weight-bold" style="font-size: 11px; {{ isset($totalSlipQty) && $totalSlipQty == $slip->total_pieces ? 'color: var(--erp-green-primary);' : 'color: #92400e;' }}">
                                        Target: {{ $slip->total_pieces }} pcs
                                        @if(isset($totalSlipQty) && $totalSlipQty == $slip->total_pieces)
                                            <i class="fas fa-check-circle" style="color: var(--erp-green-primary);" title="Total pieces matched"></i>
                                        @else
                                            <i class="fas fa-hourglass-half text-warning" title="Pieces not yet fully digitized"></i>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($slip->toStage)
                                    <span class="font-weight-semibold text-dark">{{ $slip->toStage->name }}</span>
                                @else
                                    @php
                                        $firstTx = $slip->orderStageTransaction->first() ?? 
                                                   $slip->orderPrintingStageTransaction->first() ?? 
                                                   $slip->orderPrintingToStichingTransaction->first() ??
                                                   $slip->orderGodamStageTransaction->first();
                                        
                                        $nextStage = $firstTx->to_stage ?? null;
                                        $nextUnit = $firstTx->getToUnitMaster ?? null;
                                        
                                        $sessionCount = ($slip->orderStageTransaction->count() + 
                                                        $slip->orderPrintingStageTransaction->count() + 
                                                        $slip->orderPrintingToStichingTransaction->count() +
                                                        $slip->orderGodamStageTransaction->count());
                                    @endphp
                                    @if($nextStage)
                                        <span class="font-weight-semibold text-dark">{{ $nextStage->name }}</span>
                                        @if($nextUnit)
                                            <div class="text-muted text-xs">({{ $nextUnit->name }})</div>
                                        @endif
                                        @if($sessionCount > 1)
                                            <span class="badge px-1.5 py-0.5 mt-1" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-size: 10px;">+{{ $sessionCount - 1 }} More</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                @endif
                            </td>

                            <td class="text-center">
                                @if($slip->status == 0)
                                    <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                        <i class="fas fa-clock mr-1"></i> Pending
                                    </span>
                                @elseif($slip->status == 2)
                                    <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                                        <i class="fas fa-ban mr-1"></i> Skipped
                                    </span>
                                @else
                                    <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">
                                        <i class="fas fa-check-circle mr-1"></i> Digitized
                                    </span>
                                @endif
                            </td>
                            <td class="text-right pr-3">
                                <div class="d-flex justify-content-end align-items-center" style="gap: 4px;">
                                    @php
                                        $from_stage_id = $slip->from_stage_id;
                                        $actionRoute = '#';
                                        if($from_stage_id == 3) {
                                            $actionRoute = route('admin.order_digitalization.cutting-master', ['slip_id' => $slip->id]);
                                        } elseif($from_stage_id == 11) {
                                            $actionRoute = route('admin.packing.processNew', [$slip->id]);
                                        } else {
                                            $actionRoute = route('admin.order_digitalization.create-slips-production', ['slip_id' => $slip->id]);
                                        }
                                        
                                        $totalSessions = $slip->orderLots->count() + 
                                                        $slip->orderStageTransaction->count() + 
                                                        $slip->orderPrintingStageTransaction->count() + 
                                                        $slip->orderPrintingToStichingTransaction->count() +
                                                        $slip->orderGodamStageTransaction->count() +
                                                        $slip->fabricRollAssignings->count() +
                                                        $slip->parts->count() +
                                                        ($slip->packingMain ? 1 : 0);
                                    @endphp
                                    
                                    @if(!empty($slip->slip_file))
                                        <button type="button" 
                                                class="erp-action-btn btn-view-slip-photo" 
                                                style="color: #0284c7; border-color: #bae6fd; background: #f0f9ff;"
                                                data-slip-id="{{ $slip->id }}"
                                                data-slip-url="{{ asset('assets/production_slips/' . $slip->slip_file) }}"
                                                data-slip-date="{{ $slip->created_at->format('d M Y, h:i A') }}"
                                                data-slip-unit="{{ $slip->getUnitMaster->name ?? '-' }}"
                                                data-slip-from="{{ $slip->fromStage->name ?? '-' }}"
                                                data-slip-to="{{ $slip->toStage->name ?? '-' }}"
                                                data-slip-bill="{{ $slip->bill_number ?? '-' }}"
                                                data-slip-pieces="{{ $slip->total_pieces ?? '-' }}"
                                                data-slip-show-url="{{ route('admin.uploaded-slips.show', $slip->id) }}"
                                                title="View Uploaded Slip Photo in Modal">
                                            <i class="fas fa-file-image"></i>
                                        </button>
                                    @endif

                                    @if($slip->status == 1 || $totalSessions > 0)
                                        <a href="{{ route('admin.uploaded-slips.show', $slip->id) }}" class="erp-action-btn erp-btn-view" title="View Digitization Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif

                                    @if($slip->status == 0)
                                        <a href="{{ $actionRoute }}" class="erp-action-btn erp-btn-edit" title="Digitize More/Start">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endif

                                    @if($slip->status == 0)
                                        <form action="{{ route('admin.uploaded-slips.finalize', $slip->id) }}" method="POST" onsubmit="return confirm('Mark this slip as Finalized?');" style="display:inline-block; margin: 0;">
                                            @csrf
                                            <button type="submit" class="erp-action-btn" style="color: #05421c; border-color: #c3e6cb; background: #edf7e4;" title="Mark Finalized">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($slip->status == 2)
                                        <form action="{{ route('admin.uploaded-slips.restore', $slip->id) }}" method="POST" onsubmit="return confirm('Restore this skipped slip to Pending?');" style="display:inline-block; margin: 0;">
                                            @csrf
                                            <button type="submit" class="erp-action-btn" style="color: #05421c; border-color: #c3e6cb; background: #fff;" title="Restore to Pending">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($totalSessions == 0)
                                        <form action="{{ route('admin.uploaded-slips.destroy', $slip->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slip?');" style="display:inline-block; margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="erp-action-btn erp-btn-delete" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">No slips found matching your criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- PAGINATION -->
        <div class="card-footer bg-white d-flex justify-content-end py-2 px-3 border-top">
            {{ $slips->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- ================= UNIT SLIP PHOTO PREVIEW MODAL ================= -->
<div class="modal fade" id="unitSlipModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 950px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px; overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header bg-dark text-white py-2 px-3 align-items-center">
                <div class="d-flex align-items-center flex-wrap">
                    <i class="fas fa-receipt text-warning mr-2" style="font-size: 1.1rem;"></i>
                    <h6 class="modal-title font-weight-bold mb-0 mr-3">
                        Unit Uploaded Slip <span class="text-warning font-weight-bold" id="unitSlipModalId">#</span>
                    </h6>
                    <span id="unitSlipModalUnit" class="badge badge-light border mr-1 text-dark" style="font-size: 11px;">-</span>
                    <span id="unitSlipModalFrom" class="badge badge-info mr-1" style="font-size: 11px;">-</span>
                    <span id="unitSlipModalDate" class="badge badge-secondary mr-1" style="font-size: 11px;">-</span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Subheader Details & Quick Toolbar -->
            <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom flex-wrap" style="background: #f8fafc; font-size: 12px;">
                <div class="d-flex align-items-center flex-wrap text-muted mr-2 my-1">
                    <span class="mr-3"><strong>Bill No:</strong> <span id="unitSlipModalBill" class="text-dark font-weight-bold">-</span></span>
                    <span class="mr-3"><strong>Target:</strong> <span id="unitSlipModalPieces" class="text-dark font-weight-bold">-</span> pcs</span>
                    <span><strong>To Stage:</strong> <span id="unitSlipModalTo" class="text-dark font-weight-bold">-</span></span>
                </div>
                <!-- Image Controls Toolbar -->
                <div class="btn-group btn-group-sm my-1" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="slipZoomInBtn" title="Zoom In">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="slipZoomOutBtn" title="Zoom Out">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="slipRotateLeftBtn" title="Rotate Counter-Clockwise">
                        <i class="fas fa-undo"></i> 90°
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="slipRotateRightBtn" title="Rotate Clockwise">
                        <i class="fas fa-redo"></i> 90°
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="slipResetBtn" title="Reset View">
                        <i class="fas fa-sync-alt"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Modal Body with Image Display -->
            <div class="modal-body p-3 text-center position-relative" style="background: #0f172a; min-height: 420px; max-height: 75vh; overflow: auto; display: flex; align-items: center; justify-content: center;">
                <div id="slipLoadingSpinner" class="text-white py-5">
                    <div class="spinner-border text-light mb-2" role="status"></div>
                    <div>Loading slip image...</div>
                </div>
                <img id="unitSlipModalImg" 
                     src="" 
                     alt="Uploaded Slip" 
                     class="img-fluid rounded shadow" 
                     style="max-height: 70vh; max-width: 100%; object-fit: contain; transition: transform 0.2s ease; cursor: grab; display: none;">
                <div id="slipErrorNotice" class="text-white py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6 class="font-weight-bold">Slip Image Not Available</h6>
                    <p class="text-muted small mb-0" id="slipErrorPath"></p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer py-2 px-3 justify-content-between bg-light">
                <div>
                    <a id="unitSlipModalOpenNewTab" href="#" target="_blank" class="btn btn-sm btn-primary font-weight-bold mr-2">
                        <i class="fas fa-external-link-alt mr-1"></i> Open Full Tab
                    </a>
                    <a id="unitSlipModalDownload" href="#" download class="btn btn-sm btn-outline-secondary mr-2">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <a id="unitSlipModalShowLink" href="#" class="btn btn-sm btn-outline-success font-weight-bold">
                        <i class="fas fa-info-circle mr-1"></i> Digitization Details
                    </a>
                </div>
                <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'default',
                width: '100%'
            });
        }

        let currentSlipRotation = 0;
        let currentSlipScale = 1;

        function applySlipTransform() {
            $('#unitSlipModalImg').css('transform', `rotate(${currentSlipRotation}deg) scale(${currentSlipScale})`);
        }

        $(document).on('click', '.btn-view-slip-photo', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const slipId = $btn.data('slip-id');
            const slipUrl = $btn.data('slip-url');
            const slipDate = $btn.data('slip-date');
            const slipUnit = $btn.data('slip-unit');
            const slipFrom = $btn.data('slip-from');
            const slipTo = $btn.data('slip-to');
            const slipBill = $btn.data('slip-bill');
            const slipPieces = $btn.data('slip-pieces');
            const showUrl = $btn.data('slip-show-url');

            // Reset transform
            currentSlipRotation = 0;
            currentSlipScale = 1;
            applySlipTransform();

            // Populate modal texts
            $('#unitSlipModalId').text('#' + slipId);
            $('#unitSlipModalUnit').text(slipUnit || '-');
            $('#unitSlipModalFrom').text('From: ' + (slipFrom || '-'));
            $('#unitSlipModalDate').text(slipDate || '-');
            $('#unitSlipModalBill').text(slipBill || '-');
            $('#unitSlipModalPieces').text(slipPieces || '-');
            $('#unitSlipModalTo').text(slipTo || '-');
            $('#unitSlipModalOpenNewTab').attr('href', slipUrl);
            $('#unitSlipModalDownload').attr('href', slipUrl);

            if (showUrl) {
                $('#unitSlipModalShowLink').attr('href', showUrl).show();
            } else {
                $('#unitSlipModalShowLink').hide();
            }

            // Image state management
            $('#slipLoadingSpinner').show();
            $('#slipErrorNotice').hide();
            $('#unitSlipModalImg').hide();

            const img = new Image();
            img.onload = function() {
                $('#slipLoadingSpinner').hide();
                $('#unitSlipModalImg').attr('src', slipUrl).show();
            };
            img.onerror = function() {
                $('#slipLoadingSpinner').hide();
                $('#slipErrorPath').text(slipUrl);
                $('#slipErrorNotice').show();
            };
            img.src = slipUrl;

            $('#unitSlipModal').modal('show');
        });

        $('#slipRotateRightBtn').on('click', function() {
            currentSlipRotation = (currentSlipRotation + 90) % 360;
            applySlipTransform();
        });

        $('#slipRotateLeftBtn').on('click', function() {
            currentSlipRotation = (currentSlipRotation - 90) % 360;
            applySlipTransform();
        });

        $('#slipZoomInBtn').on('click', function() {
            if (currentSlipScale < 3) {
                currentSlipScale += 0.25;
                applySlipTransform();
            }
        });

        $('#slipZoomOutBtn').on('click', function() {
            if (currentSlipScale > 0.5) {
                currentSlipScale -= 0.25;
                applySlipTransform();
            }
        });

        $('#slipResetBtn').on('click', function() {
            currentSlipRotation = 0;
            currentSlipScale = 1;
            applySlipTransform();
        });
    });
</script>
@endsection
