@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- 1. SLIM ERP HEADER BAR -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title d-flex align-items-center flex-wrap" style="gap: 8px;">
            <i class="fas {{ !empty($is_packing) ? 'fa-box-open' : 'fa-file-invoice' }}" style="color: var(--erp-green-primary);"></i>
            <span>{{ !empty($is_packing) ? 'Uploaded Slips (Packing)' : 'Uploaded Slips' }}</span>
            <span class="erp-badge-yellow px-2 py-0.5 rounded font-weight-bold" style="font-size: 0.85rem;">
                {{ $slips->total() }} Total
            </span>
        </div>
        <div class="erp-header-actions d-flex align-items-center flex-wrap" style="gap: 6px;">
            <a href="{{ route('admin.reports.slips') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-chart-bar mr-1"></i> Slip-wise Report
            </a>
            @if(!empty($is_packing))
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-file-upload mr-1"></i> All Slips
                </a>
            @else
                <a href="{{ route('admin.uploaded-slips.packing') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-box-open mr-1"></i> Packing Slips
                </a>
            @endif
        </div>
    </div>

    <!-- 2. ERP FILTER CARD -->
    <div class="erp-card mb-2" style="border-top: 3px solid var(--erp-yellow-bright); background: #ffffff;">
        <div class="erp-card-body p-2 px-3">
            <form method="GET" action="{{ !empty($is_packing) ? route('admin.uploaded-slips.packing') : route('admin.uploaded-slips.index') }}">
                <div class="row align-items-end g-2">
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">Lot Number</label>
                        <input type="text" name="lot_no" class="form-control form-control-sm erp-input" placeholder="Search Lot..." value="{{ request('lot_no') }}">
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">Bill No</label>
                        <input type="text" name="bill_number" class="form-control form-control-sm erp-input" placeholder="Search Bill..." value="{{ request('bill_number') }}">
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">From Stage</label>
                        <select name="from_stage_id" class="form-control select2 form-control-sm erp-input">
                            <option value="">-- All Stages --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('from_stage_id') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">To Stage</label>
                        <select name="to_stage_id" class="form-control select2 form-control-sm erp-input">
                            <option value="">-- All Stages --</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('to_stage_id') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">Unit</label>
                        <select name="stage_master_unit_id" class="form-control select2 form-control-sm erp-input">
                            <option value="">-- All Units --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('stage_master_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">Status</label>
                        <select name="status" class="form-control select2 form-control-sm erp-input">
                            <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>-- Show All --</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Digitized</option>
                            <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Skipped</option>
                        </select>
                    </div>
                    <div class="col-md col-sm-6 mb-1">
                        <label class="small font-weight-bold text-muted mb-1 text-uppercase" style="font-size: 11px;">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm erp-input" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-auto mb-1 text-right">
                        <button type="submit" class="btn-erp btn-erp-primary mr-1" title="Apply Filter">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ !empty($is_packing) ? route('admin.uploaded-slips.packing') : route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. ERP TABLE CARD -->
    <div class="erp-card bg-white mb-2" style="border-radius: 8px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0 text-sm">
                    <thead>
                        <tr>
                            <th width="70" class="text-center">Slip ID</th>
                            <th width="100">Date</th>
                            <th>From Stage</th>
                            <th>Unit</th>
                            <th>Lot No & Quantities</th>
                            <th>Bill / Slip Target</th>
                            <th>To Stage</th>
                            <th class="text-center" width="100">Status</th>
                            <th class="text-right pr-3" width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slips as $slip)
                        <tr class="border-bottom">
                            <td class="text-center font-weight-bold" style="color: var(--erp-green-primary);">
                                #{{ $slip->id }}
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
                                                        ($slip->packingMain ? 1 : 0);
                                    @endphp
                                    
                                    @if($slip->status == 1 || $totalSessions > 0)
                                        <a href="{{ route('admin.uploaded-slips.show', $slip->id) }}" class="erp-action-btn erp-btn-view" title="View Details">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default',
            width: '100%'
        });
    });
</script>
@endsection
