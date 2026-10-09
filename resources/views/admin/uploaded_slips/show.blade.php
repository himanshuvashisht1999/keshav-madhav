@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- 1. SLIM ERP HEADER BAR -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title d-flex align-items-center flex-wrap" style="gap: 8px;">
            <i class="fas fa-file-invoice" style="color: var(--erp-green-primary);"></i>
            <span>Production Slip:</span>
            <span class="erp-badge-yellow px-2 py-0.5 rounded font-weight-bold" style="font-size: 0.95rem;">#{{ $slip->id }}</span>
            @if($slip->status == 1)
                <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle mr-1"></i> Digitized
                </span>
            @elseif($slip->status == 2)
                <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;">
                    <i class="fas fa-ban mr-1"></i> Skipped
                </span>
            @else
                <span class="badge px-2 py-1 font-weight-bold" style="font-size: 0.72rem; border-radius: 4px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                    <i class="fas fa-clock mr-1"></i> Pending
                </span>
            @endif
        </div>
        <div class="erp-header-actions d-flex align-items-center flex-wrap" style="gap: 6px;">
            <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <a href="{{ route('admin.uploaded-slips.download', $slip->id) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
        </div>
    </div>

    @php
        function is_lot_deletable($lot, $printings, $stage_transactions) {
            $lp = $printings->where('lot_no', $lot->lot_no);
            foreach($lp as $p) {
                if ($p->remaining_quantity != $p->quantity) return false;
            }
            $lt = $stage_transactions->where('lot_no', $lot->lot_no)->where('from_stage_id', 3);
            foreach($lt as $t) {
                if ($t->remaining_quantity != $t->quantity) return false;
            }
            return true;
        }
        function is_transaction_deletable($tx) {
            return ($tx->remaining_quantity == $tx->quantity);
        }

        $all_sizes = [];
        foreach($rolls as $r) { foreach($r->fabricRollAssigningsDetail as $sd) { $all_sizes[] = $sd->size; } }
        foreach($printings as $p) { foreach($p->details as $rs) { $all_sizes[] = $rs->size; } }
        foreach($stage_transactions as $st) { foreach($st->details as $rs) { $all_sizes[] = $rs->size; } }
        $all_sizes = array_unique(array_filter($all_sizes));
        if (count($all_sizes) > 0) {
            natsort($all_sizes);
            $all_sizes = array_values($all_sizes);
            $actual_range = $all_sizes[0] . '-' . $all_sizes[count($all_sizes)-1];
        } else {
            $actual_range = '-';
        }

        $toDestinations = collect();
        foreach($printings as $p) {
            $stageName = $p->to_stage ? $p->to_stage->name : null;
            $unitName = $p->getToUnitMaster ? $p->getToUnitMaster->name : null;
            if ($stageName) {
                $toDestinations->push($stageName . ($unitName ? ' (' . $unitName . ')' : ''));
            }
        }
        foreach($stage_transactions as $st) {
            $stageName = $st->to_stage ? $st->to_stage->name : null;
            $unitName = $st->getToUnitMaster ? $st->getToUnitMaster->name : null;
            if ($stageName) {
                $toDestinations->push($stageName . ($unitName ? ' (' . $unitName . ')' : ''));
            }
        }
        $toDisplay = $toDestinations->unique()->filter()->implode(' / ') ?: '-';
    @endphp

    <!-- 2. SLIP METADATA STRIP -->
    <div class="erp-card mb-3" style="border-top: 3px solid var(--erp-green-primary); background: #ffffff;">
        <div class="erp-card-body p-2 px-3">
            <div class="row align-items-center">
                <div class="col-md-3 col-sm-6 py-1">
                    <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                        <i class="fas fa-hashtag mr-1" style="color: var(--erp-green-primary);"></i> Slip ID & Date
                    </div>
                    <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-green-primary);">
                        #{{ $slip->id }} <span class="text-muted font-normal text-xs ml-1">({{ $slip->created_at->format('d M, Y') }})</span>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 py-1">
                    <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                        <i class="fas fa-layer-group mr-1" style="color: var(--erp-green-primary);"></i> From Stage & Unit
                    </div>
                    <div class="font-weight-bold mt-1 text-truncate" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                        {{ $slip->fromStage?->name ?? '-' }}
                        @if($slip->getUnitMaster)
                            <span class="text-muted text-xs ml-1">({{ $slip->getUnitMaster->name }})</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 py-1">
                    <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                        <i class="fas fa-industry mr-1" style="color: var(--erp-green-primary);"></i> To Stage & Unit
                    </div>
                    <div class="font-weight-bold mt-1 text-truncate" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);" title="{{ $toDisplay }}">
                        {{ $toDisplay }}
                    </div>
                </div>
                <div class="col-md-2 col-sm-6 py-1 text-md-right">
                    <div class="text-uppercase font-weight-bold" style="font-size: var(--erp-font-xs); color: var(--erp-text-muted);">
                        <i class="fas fa-bullseye mr-1" style="color: var(--erp-green-primary);"></i> Slip Target
                    </div>
                    <div class="font-weight-bold mt-1" style="font-size: var(--erp-font-sm); color: var(--erp-green-primary);">
                        {{ $slip->total_pieces ?? '-' }} <span class="text-xs text-muted">pcs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

                {{-- ================= TYPE 1 : ROLLS ================= --}}
                @if(count($lots) > 0)
                    @foreach($lots as $index => $lot)
                        <div class="mb-5 p-4 border rounded bg-white shadow-sm" style="border-top: 5px solid #10b981 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-dark mb-0">Digitization Session #{{ $index + 1 }}</h4>
                                <div>
                                    @if(is_lot_deletable($lot, $printings, $stage_transactions))
                                        <a href="{{ route('admin.uploaded-slips.delete-session', ['type' => 'lot', 'id' => $lot->id]) }}" 
                                           class="btn btn-sm btn-outline-danger border shadow-xs me-2"
                                           onclick="return confirm('Are you sure you want to delete this session and restore used quantities?')">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted border py-2 me-2" title="Lot has been moved to Printing/Stitching">
                                            <i class="fas fa-lock me-1"></i> Read-only
                                        </span>
                                    @endif
                                    <span class="badge bg-success fs-6 px-3 py-2">Stage: Cutting</span>
                                </div>
                            </div>
                            
                            {{-- Specific Order Info for this Lot --}}
                            @if($lot->orderProductSet)
                            <div class="row g-3 mb-4 p-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Lot No</div>
                                    <div class="fw-bold">#{{ $lot->lot_no }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Order No</div>
                                    <div class="fw-bold text-success">{{ $lot->orderMain?->sku ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Design</div>
                                    <div class="fw-semibold">{{ $lot->orderProductSet->design_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Fabric</div>
                                    @php
                                        $actual_fabrics = collect();
                                        $currentRolls = $rolls->where('order_lot_id', $lot->id);
                                        foreach($currentRolls as $roll) {
                                            if ($roll->fabricReceiptDetail && $roll->fabricReceiptDetail->fabric) {
                                                $actual_fabrics->push($roll->fabricReceiptDetail->fabric->name);
                                            }
                                        }
                                        $fabric_display = $actual_fabrics->unique()->implode(', ') ?: ($lot->orderProductSet->fabric?->name ?? '-');
                                    @endphp
                                    <div class="fw-semibold">{{ $fabric_display }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Color</div>
                                    <div class="fw-semibold">{{ $lot->orderProductSet->colors?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="text-muted small text-uppercase">Production Date</div>
                                    <div class="small">{{ getformatDateTime($lot->production_datetime) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- ROLLS DETAILS (Summary Table) --}}
                            <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-scroll text-muted me-2"></i> Rolls Details</h6>
                                    @php $currentRolls = $rolls->where('order_lot_id', $lot->id); @endphp
                                    <span class="badge badge-info px-3">Total Rolls: {{ $currentRolls->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0">
                                        <tbody>
                                            @foreach($currentRolls as $roll)
                                                <tr class="border-bottom">
                                                    <td class="ps-4 fw-bold text-dark" style="font-size: 1.1rem;">{{ $roll->roll_no }}</td>
                                                    <td class="pe-4 fw-bold text-muted" style="font-size: 1rem;">{{ $roll->meter }} m</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- ONE TIME PRODUCTION BREAKDOWN (Consolidated) --}}
                            @php
                                $consolidated = [];
                                foreach($currentRolls as $r) {
                                    foreach($r->fabricRollAssigningsDetail as $sd) {
                                        $consolidated[$sd->size] = ($consolidated[$sd->size] ?? 0) + $sd->quantity;
                                    }
                                }
                            @endphp

                            @if(count($consolidated) > 0)
                                <div class="card shadow-sm border-0 mt-4 mb-4" style="background: #f8fafc; border-radius: 12px; border-top: 4px solid #17a2b8 !important;">
                                    <div class="card-header bg-white py-3">
                                        <h6 class="mb-0 text-info fw-bold uppercase"><i class="fas fa-layer-group me-2"></i> Production Size Set & Quantities</h6>
                                    </div>
                                    <div class="card-body py-4">
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3">
                                            @foreach($consolidated as $sz => $qty)
                                                <div class="col">
                                                    <div class="text-center p-3 bg-white rounded shadow-sm border h-100" style="border-top: 3px solid #17a2b8 !important;">
                                                        <div class="text-black small uppercase fw-black mb-1" style="font-size: 11px; letter-spacing: 0.5px;">SIZE {{ $sz }}</div>
                                                        <div class="fw-bold text-dark fs-2">{{ $qty }}</div>
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif

                {{-- ================= TYPE 2 : PRINTING ================= --}}
                @if(count($printings) > 0)
                    @foreach($printings as $index => $printing)
                        <div class="mb-4 p-3 border rounded bg-white shadow-sm" style="border-top: 4px solid var(--erp-green-primary) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Digitization Session #{{ $index + 1 }}</h5>
                                <div>
                                    @if(is_transaction_deletable($printing))
                                        <a href="{{ route('admin.uploaded-slips.delete-session', ['type' => 'printing', 'id' => $printing->id]) }}" 
                                           class="btn-erp btn-erp-outline text-danger mr-2"
                                           onclick="return confirm('Are you sure you want to delete this session and restore quantities?')">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </a>
                                    @else
                                        <span class="badge px-2 py-1 text-muted border mr-2" style="background: #f8fafc;" title="Quantity has been moved to further stages">
                                            <i class="fas fa-lock mr-1"></i> Read-only
                                        </span>
                                    @endif
                                    <span class="badge px-2.5 py-1 font-weight-bold" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; font-size: 0.85rem;">Stage: Printing</span>
                                </div>
                            </div>

                            {{-- Specific Order Info for this Printing Session --}}
                            @if($printing->orderProduct?->orderProductSet)
                            @php $ops = $printing->orderProduct->orderProductSet; @endphp
                            <div class="row g-2 mb-3 p-2.5 rounded" style="background: #edf7e4; border: 1px solid #c3e6cb;">
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Lot No</div>
                                    <div class="font-weight-bold text-dark">#{{ $printing->lot_no }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Order No</div>
                                    <div class="font-weight-bold" style="color: var(--erp-green-primary);">{{ $ops->orderMain?->sku ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Design</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->design_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Fabric</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->fabric?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Color</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->colors?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 text-md-right">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Production Date</div>
                                    <div class="small font-weight-bold text-dark">{{ getformatDateTime($printing->production_datetime) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- PRINTING DETAILS (Unified Layout) --}}
                            <div class="erp-card bg-white mb-2" style="border-top: 3px solid var(--erp-green-primary) !important;">
                                <div class="card-header bg-white py-2 px-3 border-bottom">
                                    <h6 class="mb-0 font-weight-bold" style="color: #05421c;"><i class="fas fa-print mr-2 text-warning"></i> Printing Allocation Summary</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-2"><strong>Lot No:</strong> <span class="text-dark font-weight-bold">#{{ $printing->lot_no }}</span></div>
                                        <div class="col-md-3"><strong>Date:</strong> <span class="text-dark">{{ \Carbon\Carbon::parse($printing->production_datetime)->format('d M Y') }}</span></div>
                                        <div class="col-md-5"><strong>Transfer:</strong> 
                                            <span class="font-weight-bold" style="color: var(--erp-green-primary);">{{ $printing->to_stage?->name }}</span> 
                                            @if($printing->getToUnitMaster && !empty(trim($printing->getToUnitMaster->name)))
                                                <span class="text-muted small">({{ $printing->getToUnitMaster->name }})</span>
                                            @endif
                                        </div>
                                        <div class="col-md-2 text-md-right"><strong>Total Pieces:</strong> <span class="badge px-2.5 py-1 font-weight-bold" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;">{{ $printing->quantity }} pcs</span></div>
                                    </div>

                                    @php
                                        $consolidated = [];
                                        foreach($printing->details as $rs) {
                                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                                        }
                                    @endphp

                                    @if(count($consolidated) > 0)
                                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-2">
                                            @foreach($consolidated as $sz => $qty)
                                                <div class="col mb-2">
                                                    <div class="text-center p-2 bg-white border rounded shadow-xs" style="border-top: 3px solid var(--erp-green-primary) !important;">
                                                        <div class="text-muted small text-uppercase font-weight-bold mb-1" style="font-size: 11px;">SIZE {{ $sz }}</div>
                                                        <div class="font-weight-bold text-dark h4 mb-0">{{ $qty }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- ================= TYPE 3 : OTHER ================= --}}
                @if(count($stage_transactions) > 0)
                    @foreach($stage_transactions as $index => $transaction)
                        <div class="mb-4 p-3 border rounded bg-white shadow-sm" style="border-top: 4px solid var(--erp-yellow-bright) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-dark mb-0">Digitization Session #{{ $index + 1 }}</h5>
                                <div>
                                    @php 
                                        if ($transaction instanceof \App\Models\OrderPrintingToStichingTransaction) {
                                            $type = 'printing_stitching';
                                        } elseif ($transaction instanceof \App\Models\OrderGodamStageTransaction) {
                                            $type = 'godam';
                                        } else {
                                            $type = 'transfer';
                                        }
                                    @endphp
                                    @if(is_transaction_deletable($transaction))
                                        <a href="{{ route('admin.uploaded-slips.delete-session', ['type' => $type, 'id' => $transaction->id]) }}" 
                                           class="btn-erp btn-erp-outline text-danger mr-2"
                                           onclick="return confirm('Are you sure you want to delete this session and restore quantities?')">
                                            <i class="fas fa-trash-alt mr-1"></i> Delete
                                        </a>
                                    @else
                                        <span class="badge px-2 py-1 text-muted border mr-2" style="background: #f8fafc;" title="Quantity has been moved to further stages">
                                            <i class="fas fa-lock mr-1"></i> Read-only
                                        </span>
                                    @endif
                                    <span class="badge px-2.5 py-1 font-weight-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.85rem;">Stage: Transfer</span>
                                </div>
                            </div>

                            {{-- Specific Order Info for this Transfer --}}
                            @if($transaction->orderProduct?->orderProductSet)
                            @php $ops = $transaction->orderProduct->orderProductSet; @endphp
                            <div class="row g-2 mb-3 p-2.5 rounded" style="background: #fef3c7; border: 1px solid #fde68a;">
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Lot No</div>
                                    <div class="font-weight-bold text-dark">#{{ $transaction->lot_no }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Order No</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->orderMain?->sku ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Design</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->design_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Fabric</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->fabric?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Color</div>
                                    <div class="font-weight-bold text-dark">{{ $ops->colors?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 text-md-right">
                                    <div class="text-muted small text-uppercase font-weight-bold" style="font-size: 11px;">Production Date</div>
                                    <div class="small font-weight-bold text-dark">{{ getformatDateTime($transaction->production_datetime) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- STAGE MOVEMENT DETAILS (Unified Layout) --}}
                            <div class="erp-card bg-white mb-2" style="border-top: 3px solid var(--erp-yellow-bright) !important;">
                                <div class="card-header bg-white py-2 px-3 border-bottom">
                                    <h6 class="mb-0 font-weight-bold" style="color: #92400e;"><i class="fas fa-exchange-alt mr-2 text-warning"></i> Stage Movement Summary</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row mb-3 align-items-center">
                                        <div class="col-md-2"><strong>Lot No:</strong> <span class="text-dark font-weight-bold">#{{ $transaction->lot_no }}</span></div>
                                        <div class="col-md-3"><strong>Date:</strong> <span class="text-dark">{{ \Carbon\Carbon::parse($transaction->production_datetime)->format('d M Y') }}</span></div>
                                        <div class="col-md-5"><strong>Transfer:</strong> 
                                            <span class="font-weight-bold" style="color: #92400e;">{{ $transaction->to_stage?->name }}</span> 
                                            @if($transaction->getToUnitMaster && !empty(trim($transaction->getToUnitMaster->name)))
                                                <span class="text-muted small">({{ $transaction->getToUnitMaster->name }})</span>
                                            @endif
                                        </div>
                                        <div class="col-md-2 text-md-right"><strong>Total Pieces:</strong> <span class="badge px-2.5 py-1 font-weight-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">{{ $transaction->quantity }} pcs</span></div>
                                    </div>

                                    @php
                                        $consolidated = [];
                                        foreach($transaction->details as $rs) {
                                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                                        }
                                    @endphp

                                    @if(count($consolidated) > 0)
                                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-2">
                                            @foreach($consolidated as $sz => $qty)
                                                <div class="col mb-2">
                                                    <div class="text-center p-2 bg-white border rounded shadow-xs" style="border-top: 3px solid var(--erp-yellow-bright) !important;">
                                                        <div class="text-muted small text-uppercase font-weight-bold mb-1" style="font-size: 11px;">SIZE {{ $sz }}</div>
                                                        <div class="font-weight-bold text-dark h4 mb-0">{{ $qty }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- ================= UNIT MOVEMENT & LOSSES (Packing Slips) ================= --}}
                @if($outflows->isNotEmpty() || $reworks->isNotEmpty())
                    <div class="erp-card bg-white mb-4" style="border-top: 4px solid #ef4444 !important; border-radius: 8px;">
                        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 font-weight-bold" style="color: #991b1b;">
                                    <i class="fas fa-exchange-alt mr-2 text-danger"></i> 
                                    Unit Movement & Losses Log
                                </h6>
                                <p class="mb-0 text-muted small">Items categorized as Rework, Dead pcs, Sampling or Debits linked to this slip session.</p>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table erp-table align-middle mb-0 text-sm">
                                    <thead>
                                        <tr>
                                            <th class="ps-3 py-2 text-muted small text-uppercase">Type</th>
                                            <th class="py-2 text-muted small text-uppercase">Item / Color / Size</th>
                                            <th class="py-2 text-muted small text-uppercase text-center">Qty</th>
                                            <th class="py-2 text-muted small text-uppercase">Destination / Reason</th>
                                            <th class="py-2 text-muted small text-uppercase">Remarks</th>
                                            <th class="pe-3 py-2 text-muted small text-uppercase text-right">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Outflows: Dead, Sampling, Debit --}}
                                        @foreach($outflows as $o)
                                            <tr class="align-middle border-bottom">
                                                <td class="ps-3">
                                                    @php
                                                        $style = 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;'; $icon = 'fa-skull-crossbones';
                                                        if($o->type == 'sampling') { $style = 'background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb;'; $icon = 'fa-flask'; }
                                                        if($o->type == 'debit') { $style = 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;'; $icon = 'fa-minus-circle'; }
                                                    @endphp
                                                    <span class="badge text-uppercase px-2 py-1 font-weight-bold" style="font-size: 10px; border-radius: 4px; {{ $style }}">
                                                        <i class="fas {{ $icon }} mr-1"></i> {{ $o->type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold text-dark">{{ $o->product->design_number ?? 'N/A' }}</div>
                                                    <div class="text-muted small">{{ $o->color->name ?? 'N/A' }} | <strong>{{ $o->size->size ?? 'N/A' }}</strong></div>
                                                </td>
                                                <td class="text-center font-weight-bold" style="color: var(--erp-green-primary);">
                                                    {{ $o->quantity }} <span class="text-muted font-normal text-xs">pcs</span>
                                                </td>
                                                <td>
                                                    @if($o->type == 'debit')
                                                        <div class="small">
                                                            <strong>{{ $o->responsibleStage->name ?? '' }}</strong> <span class="text-muted mx-1">→</span> <strong>{{ $o->responsibleUnit->name ?? 'N/A' }}</strong>
                                                        </div>
                                                        <span class="badge px-1.5 py-0.5 mt-1" style="background: #fee2e2; color: #991b1b; font-size: 10px;">Rs. {{ number_format($o->total_amount, 2) }}</span>
                                                    @else
                                                        <div class="small text-muted">{{ $o->rack->storeroom->name ?? 'N/A' }} / Rack: {{ $o->rack->name ?? 'N/A' }}</div>
                                                        <span class="badge border px-1.5 py-0.5 mt-1 text-dark" style="background: #f8fafc; font-size: 10px;">Location: {{ $o->responsibleUnit->name ?? 'Main' }}</span>
                                                    @endif
                                                </td>
                                                <td><div class="text-muted small italic">{{ $o->remarks ?: '—' }}</div></td>
                                                <td class="pe-3 text-right">
                                                    <div class="font-weight-bold small text-dark">{{ $o->created_at->format('d M, Y') }}</div>
                                                    <div class="text-muted small mb-1" style="font-size: 10px;">{{ $o->created_at->format('h:i A') }}</div>
                                                    <a href="{{ route('admin.uploaded-slips.outflow-receipt', $o->id) }}" target="_blank" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 10px;">
                                                        <i class="fas fa-print mr-1"></i> Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Reworks --}}
                                        @foreach($reworks as $r)
                                            @foreach($r->details as $rd)
                                                <tr class="align-middle border-bottom" style="background-color: #fafbfc;">
                                                    <td class="ps-3">
                                                        <span class="badge text-uppercase px-2 py-1 font-weight-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 10px; border-radius: 4px;">
                                                            <i class="fas fa-tools mr-1"></i> REWORK
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark">Defect/Repair Alteration</div>
                                                        <div class="text-muted small">Size: <strong>{{ $rd->size }}</strong></div>
                                                    </td>
                                                    <td class="text-center font-weight-bold text-danger">
                                                        {{ $rd->quantity }} <span class="text-muted font-normal text-xs">pcs</span>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <strong>{{ $r->toStage->name ?? 'N/A' }}</strong> <i class="fas fa-arrow-right mx-1 small text-muted"></i> <strong>{{ $r->toUnit->name ?? 'N/A' }}</strong>
                                                        </div>
                                                    </td>
                                                    <td><div class="text-muted small italic">{{ $r->remarks ?: 'Defect rework order' }}</div></td>
                                                    <td class="pe-3 text-right">
                                                        <div class="font-weight-bold small text-dark">{{ $r->created_at->format('d M, Y') }}</div>
                                                        <div class="text-muted small mb-1" style="font-size: 10px;">{{ $r->created_at->format('h:i A') }}</div>
                                                        <span class="badge border px-1.5 py-0.5 text-muted" style="background: #f8fafc; font-size: 9px;">Slip Ref #{{ $r->id }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ================= PACKING DETAILS ================= --}}
                @if($packing_details->isNotEmpty())
                    @foreach($packing_details as $index => $packing)
                        <div class="mb-4 p-3 border rounded bg-white shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0 text-dark">Digitization Session #{{ $index + 1 }} - Packing</h5>
                                @if(strtolower(trim($packing->order?->order_type)) == 'domestic')
                                    <a href="{{ route('admin.packing.downloadSlipBarcode', $packing->id) }}" class="btn-erp btn-erp-outline btn-sm">
                                        <i class="fas fa-barcode mr-1"></i> Barcodes (TXT)
                                    </a>
                                @else
                                    <button type="button" class="btn-erp btn-erp-primary btn-sm" data-toggle="modal" data-target="#corpExcelModal{{ $packing->id }}">
                                        <i class="fas fa-file-excel mr-1 text-warning"></i> Corporate Excel
                                    </button>

                                    <!-- Excel Config Modal -->
                                    <div class="modal fade" id="corpExcelModal{{ $packing->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <form action="{{ route('admin.uploaded-slips.corporate-excel', $packing->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                                                    <div class="modal-header" style="background: #05421c; color: #fff;">
                                                        <h5 class="modal-title font-weight-bold"><i class="fas fa-file-excel mr-2 text-warning"></i> Excel Export Configuration</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="row g-3 mb-4 text-left">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small font-weight-bold text-muted text-uppercase">PO NUMBER</label>
                                                                <input type="text" name="po_no" class="form-control form-control-sm erp-input" value="{{ $packing->order?->sku }}" required>
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label small font-weight-bold text-muted text-uppercase">VENDOR CODE</label>
                                                                <input type="text" name="v_cd" class="form-control form-control-sm erp-input" value="200337">
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label small font-weight-bold text-muted text-uppercase">VENDOR NAME</label>
                                                                <input type="text" name="v_nm" class="form-control form-control-sm erp-input" value="KESHAV MADHAV ENT.">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label small font-weight-bold text-muted text-uppercase">CONTRACT NO</label>
                                                                <input type="text" name="cont_no" class="form-control form-control-sm erp-input" value="9413544380">
                                                            </div>
                                                            <div class="col-md-8 mb-3">
                                                                <label class="form-label small font-weight-bold text-muted text-uppercase">BORA DESCRIPTION</label>
                                                                <input type="text" name="bora_desc" class="form-control form-control-sm erp-input" value="BOYS_JEANS" required>
                                                            </div>
                                                        </div>

                                                        <h6 class="font-weight-bold border-bottom pb-2 mb-3 text-left" style="color: #05421c;">Carton Weights (BORA/HU WT-V2)</h6>
                                                        <div class="table-responsive">
                                                            <table class="table erp-table table-sm align-middle">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="30%">Carton #</th>
                                                                        <th width="40%">Weight (KG)</th>
                                                                        <th width="30%">Qty (Total Pcs)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($packing->cartons as $carton)
                                                                        <tr>
                                                                            <td><strong style="color: var(--erp-green-primary);">#{{ $carton->carton_no }}</strong></td>
                                                                            <td>
                                                                                <input type="number" step="0.01" name="weights[{{ $carton->id }}]" class="form-control form-control-sm erp-input" placeholder="e.g. 17.15" required>
                                                                            </td>
                                                                            <td class="font-weight-bold">{{ $carton->items->sum('quantity') }} Pcs</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light p-3">
                                                        <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn-erp btn-erp-primary">Generate Excel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @foreach($packing->cartons as $carton)
                                    <div class="col-md-6 mb-3">
                                        <div class="erp-card bg-white h-100 shadow-sm"
                                            style="border-radius: 8px; border-left: 4px solid var(--erp-green-primary) !important;">
                                            <div class="card-header bg-white py-2 px-3 border-bottom-0 pb-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold" style="color: var(--erp-green-primary);">Carton #{{ $carton->carton_no }}</h6>
                                                        <span class="text-muted small">ID: {{ $carton->id }}</span>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="h4 mb-0 font-weight-bold" style="color: var(--erp-green-primary);">{{ $carton->items->sum('quantity') }}</div>
                                                        <div class="text-uppercase text-muted font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">Total Items</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body pt-2">
                                                @php
                                                    $summary = [];
                                                    foreach ($carton->items as $item) {
                                                        $name = $item->detail ? $item->detail->size : ($item->size ? $item->size->name : 'ID:' . $item->size_id);
                                                        $summary[$name] = ($summary[$name] ?? 0) + $item->quantity;
                                                    }
                                                @endphp

                                                @if(count($summary) > 0)
                                                    <div class="p-3 bg-white rounded border mb-3">
                                                        <label class="text-uppercase text-muted fw-bold d-block mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                                                             <i class="fas fa-boxes me-1 text-success"></i> Contents
                                                        </label>
                                                        <div class="row g-2">
                                                            @foreach($summary as $name => $total_qty)
                                                                <div class="col-6">
                                                                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light" style="border-left: 3px solid #05421c !important;">
                                                                        <span class="fw-bold text-dark small">{{ $name }}</span>
                                                                        <span class="badge px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb;">{{ number_format($total_qty, 0) }} Pcs</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-center py-4 text-muted small italic">
                                                        No itemized breakdown found.
                                                    </div>
                                                @endif

                                                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center px-2">
                                                    <span class="text-muted small">Total Pieces in Carton:</span>
                                                    <span class="fw-bold text-dark h5 mb-0">{{ array_sum($summary) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- ================= SLIP IMAGE (LAST) ================= --}}
                @if($slip->slip_file)
                    <div class="card shadow-sm mt-1">
                        <div class="card-header bg-light">
                            <strong>Original Slip Image</strong>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}"
                                class="img-fluid rounded border" style="max-height: 700px;">
                        </div>
                    </div>
                @endif

</div>
@endsection