@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">

        {{-- HEADER --}}

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0">Production Slip Details</h2>
                <!-- <small class="text-muted">Read-only audit view</small> -->
            </div>

            <div>
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-outline-secondary">
                    ← Back
                </a>
                <a href="{{ route('admin.uploaded-slips.download', $slip->id) }}" class="btn btn-outline-primary ms-2">
                    ⬇ Download PDF
                </a>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @php
                    function is_lot_deletable($lot, $printings, $stage_transactions) {
                        // Check if any printing records for this lot have moved forward
                        $lp = $printings->where('lot_no', $lot->lot_no);
                        foreach($lp as $p) {
                            if ($p->remaining_quantity != $p->quantity) return false;
                        }

                        // Check if any stage transactions from Cutting (stage 3) for this lot have moved forward
                        $lt = $stage_transactions->where('lot_no', $lot->lot_no)->where('from_stage_id', 3);
                        foreach($lt as $t) {
                            if ($t->remaining_quantity != $t->quantity) return false;
                        }

                        return true;
                    }
                    function is_transaction_deletable($tx) {
                        return ($tx->remaining_quantity == $tx->quantity);
                    }
                @endphp

                @php
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
                @endphp

                {{-- ================= SLIP SUMMARY ================= --}}
                <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; border-left: 5px solid #6366f1;">
                    <div class="card-body py-4">
                        <div class="row g-4">
                            <div class="col-md-2 border-end">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Slip ID</div>
                                <div class="h4 mb-0 fw-bold text-indigo">#{{ $slip->id }}</div>
                            </div>
                            <div class="col-md-3 border-end">
                                <div class="text-muted small text-uppercase fw-bold mb-1">From Stage</div>
                                <div class="h5 mb-0 fw-bold">{{ $slip->fromStage?->name ?? '-' }}</div>
                                <div class="text-muted small mt-1">{{ getformatDateTime($slip->created_at) }}</div>
                            </div>
                            <div class="col-md-3 border-end">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Unit / Warehouse</div>
                                <div class="h6 mb-0 fw-bold text-dark">{{ $slip->getUnitMaster?->name }}</div>
                                <div class="text-muted small">{{ $slip->getUnitMaster?->masterFabricWarehouse?->cutting_master_name }}</div>
                            </div>
                            <div class="col-md-2 border-end text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Slip Range</div>
                                <div class="h4 mb-0 fw-bold text-primary">{{ $actual_range }}</div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Total Pieces</div>
                                <div class="h4 mb-0 fw-bold text-dark">{{ $pcs_in_set }}</div>
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
                                    <div class="fw-semibold">{{ $lot->orderProductSet->fabric?->name ?? '-' }}</div>
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
                                            <div class="col">
                                                <div class="text-center p-3 bg-info text-white rounded shadow-sm border-info h-100">
                                                    <div class="text-white small uppercase fw-black mb-1" style="font-size: 11px; letter-spacing: 0.5px;">TOTAL</div>
                                                    <div class="fw-black fs-2 text-white">{{ array_sum($consolidated) }}</div>
                                                </div>
                                            </div>
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
                        <div class="mb-5 p-4 border rounded bg-white shadow-sm" style="border-top: 5px solid #3b82f6 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-dark mb-0">Digitization Session #{{ $index + 1 }}</h4>
                                <div>
                                    @if(is_transaction_deletable($printing))
                                        <a href="{{ route('admin.uploaded-slips.delete-session', ['type' => 'printing', 'id' => $printing->id]) }}" 
                                           class="btn btn-sm btn-outline-danger border shadow-xs me-2"
                                           onclick="return confirm('Are you sure you want to delete this session and restore quantities?')">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted border py-2 me-2" title="Quantity has been moved to further stages">
                                            <i class="fas fa-lock me-1"></i> Read-only
                                        </span>
                                    @endif
                                    <span class="badge bg-primary fs-6 px-3 py-2">Stage: Printing</span>
                                </div>
                            </div>

                            {{-- Specific Order Info for this Printing Session --}}
                            @if($printing->orderProduct?->orderProductSet)
                            @php $ops = $printing->orderProduct->orderProductSet; @endphp
                            <div class="row g-3 mb-4 p-3 rounded" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Lot No</div>
                                    <div class="fw-bold">#{{ $printing->lot_no }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Order No</div>
                                    <div class="fw-bold text-primary">{{ $ops->orderMain?->sku ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Design</div>
                                    <div class="fw-semibold">{{ $ops->design_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Fabric</div>
                                    <div class="fw-semibold">{{ $ops->fabric?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Color</div>
                                    <div class="fw-semibold">{{ $ops->colors?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="text-muted small text-uppercase">Production Date</div>
                                    <div class="small">{{ getformatDateTime($printing->production_datetime) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- PRINTING DETAILS (Unified Layout) --}}
                            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #007bff !important;">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 text-primary fw-bold uppercase"><i class="fas fa-print me-2"></i> Printing Allocation Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-3"><strong>Lot No:</strong> <span class="text-dark">#{{ $printing->lot_no }}</span></div>
                                        <div class="col-md-3"><strong>Date:</strong> {{ getformatDateTime($printing->production_datetime) }}</div>
                                        <div class="col-md-3"><strong>Route:</strong> <span class="text-muted small">{{ $printing->from_stage?->name }}</span> <i class="fas fa-arrow-right mx-1 small"></i> <span class="text-primary fw-bold">{{ $printing->to_stage?->name }}</span> <span class="text-muted small">({{ $printing->getToUnitMaster?->name }})</span></div>
                                        <div class="col-md-3 text-end"><strong>Total Pieces:</strong> <span class="badge bg-primary px-3 fs-6">{{ $printing->quantity }}</span></div>
                                    </div>

                                    @php
                                        $consolidated = [];
                                        foreach($printing->details as $rs) {
                                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                                        }
                                    @endphp

                                    @if(count($consolidated) > 0)
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3">
                                            @foreach($consolidated as $sz => $qty)
                                                <div class="col">
                                                    <div class="text-center p-3 bg-white border rounded h-100 shadow-xs" style="border-top: 3 solid #007bff !important;">
                                                        <div class="text-black small uppercase fw-black mb-1" style="font-size: 11px;">SIZE {{ $sz }}</div>
                                                        <div class="fw-bold text-dark fs-3">{{ $qty }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="col">
                                                <div class="text-center p-3 bg-primary text-white rounded border-primary h-100 shadow-sm">
                                                    <div class="text-white small uppercase fw-black mb-1" style="font-size: 11px;">TOTAL</div>
                                                    <div class="fw-black fs-3">{{ array_sum($consolidated) }}</div>
                                                </div>
                                            </div>
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
                        <div class="mb-5 p-4 border rounded bg-white shadow-sm" style="border-top: 5px solid #f59e0b !important;">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-dark mb-0">Digitization Session #{{ $index + 1 }}</h4>
                                <div>
                                    @php 
                                        $type = ($transaction instanceof \App\Models\OrderPrintingToStichingTransaction) ? 'printing_stitching' : 'transfer';
                                    @endphp
                                    @if(is_transaction_deletable($transaction))
                                        <a href="{{ route('admin.uploaded-slips.delete-session', ['type' => $type, 'id' => $transaction->id]) }}" 
                                           class="btn btn-sm btn-outline-danger border shadow-xs me-2"
                                           onclick="return confirm('Are you sure you want to delete this session and restore quantities?')">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted border py-2 me-2" title="Quantity has been moved to further stages">
                                            <i class="fas fa-lock me-1"></i> Read-only
                                        </span>
                                    @endif
                                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">Stage: Transfer</span>
                                </div>
                            </div>

                            {{-- Specific Order Info for this Transfer --}}
                            @if($transaction->orderProduct?->orderProductSet)
                            @php $ops = $transaction->orderProduct->orderProductSet; @endphp
                            <div class="row g-3 mb-4 p-3 rounded" style="background: #fffbeb; border: 1px solid #fef3c7;">
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Lot No</div>
                                    <div class="fw-bold">#{{ $transaction->lot_no }}</div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-muted small text-uppercase fw-bold">Order No</div>
                                    <div class="fw-bold text-warning">{{ $ops->orderMain?->sku ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Design</div>
                                    <div class="fw-semibold">{{ $ops->design_number ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Fabric</div>
                                    <div class="fw-semibold">{{ $ops->fabric?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 border-start ps-3">
                                    <div class="text-muted small text-uppercase">Color</div>
                                    <div class="fw-semibold">{{ $ops->colors?->name ?? '-' }}</div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="text-muted small text-uppercase">Production Date</div>
                                    <div class="small">{{ getformatDateTime($transaction->production_datetime) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            {{-- STAGE MOVEMENT DETAILS (Unified Layout) --}}
                            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #ffc107 !important;">
                                <div class="card-header bg-white py-3">
                                    <h6 class="mb-0 text-warning fw-bold uppercase"><i class="fas fa-exchange-alt me-2"></i> Stage Movement Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-3"><strong>Lot No:</strong> <span class="text-dark">#{{ $transaction->lot_no }}</span></div>
                                        <div class="col-md-3"><strong>Date:</strong> {{ getformatDateTime($transaction->production_datetime) }}</div>
                                        <div class="col-md-3"><strong>Transfer:</strong> <span class="text-muted small">{{ $transaction->from_stage?->name }}</span> <i class="fas fa-arrow-right mx-1 small"></i> <span class="text-warning fw-bold">{{ $transaction->to_stage?->name }}</span> <span class="text-muted small">({{ $transaction->getToUnitMaster?->name }})</span></div>
                                        <div class="col-md-3 text-end"><strong>Total Pieces:</strong> <span class="badge bg-warning text-dark px-3 fs-6">{{ $transaction->quantity }}</span></div>
                                    </div>

                                    @php
                                        $consolidated = [];
                                        foreach($transaction->details as $rs) {
                                            $consolidated[$rs->size] = ($consolidated[$rs->size] ?? 0) + $rs->quantity;
                                        }
                                    @endphp

                                    @if(count($consolidated) > 0)
                                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3">
                                            @foreach($consolidated as $sz => $qty)
                                                <div class="col">
                                                    <div class="text-center p-3 bg-white border rounded h-100 shadow-xs" style="border-top: 3px solid #ffc107 !important;">
                                                        <div class="text-black small uppercase fw-black mb-1" style="font-size: 11px;">SIZE {{ $sz }}</div>
                                                        <div class="fw-bold text-dark fs-3">{{ $qty }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="col">
                                                <div class="text-center p-3 bg-warning text-dark rounded border-warning h-100 shadow-sm">
                                                    <div class="text-black small uppercase fw-black mb-1" style="font-size: 11px;">TOTAL</div>
                                                    <div class="fw-black fs-3">{{ array_sum($consolidated) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif

                {{-- ================= UNIT MOVEMENT & LOSSES (Packing Slips) ================= --}}
                @if($outflows->isNotEmpty() || $reworks->isNotEmpty())
                    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; border-top: 5px solid #ef4444 !important;">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">
                                    <i class="fas fa-exchange-alt me-2 text-danger"></i> 
                                    Unit Movement & Losses Log
                                </h5>
                                <p class="mb-0 text-muted small">Items categorized as Rework, Dead pcs, Sampling or Debits linked to this slip session.</p>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">Type</th>
                                            <th class="py-3 text-muted small text-uppercase fw-bold">Item / Color / Size</th>
                                            <th class="py-3 text-muted small text-uppercase text-center fw-bold">Qty</th>
                                            <th class="py-3 text-muted small text-uppercase fw-bold">Destination / Reason</th>
                                            <th class="py-3 text-muted small text-uppercase fw-bold">Remarks</th>
                                            <th class="pe-4 py-3 text-muted small text-uppercase text-end fw-bold">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Outflows: Dead, Sampling, Debit --}}
                                        @foreach($outflows as $o)
                                            <tr class="align-middle border-bottom">
                                                <td class="ps-4">
                                                    @php
                                                        $badge = 'bg-danger'; $icon = 'fa-skull-crossbones';
                                                        if($o->type == 'sampling') { $badge = 'bg-primary'; $icon = 'fa-flask'; }
                                                        if($o->type == 'debit') { $badge = 'bg-warning text-dark'; $icon = 'fa-minus-circle'; }
                                                    @endphp
                                                    <span class="badge {{ $badge }} text-uppercase px-2 py-1 shadow-sm" style="font-size: 10px;">
                                                        <i class="fas {{ $icon }} me-1"></i> {{ $o->type }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $o->product->design_number ?? 'N/A' }}</div>
                                                    <div class="text-muted small">{{ $o->color->name ?? 'N/A' }} | <strong>{{ $o->size->size ?? 'N/A' }}</strong></div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="h6 mb-0 fw-bold text-dark">{{ $o->quantity }}</div>
                                                    <small class="text-muted small">pcs</small>
                                                </td>
                                                <td>
                                                    @if($o->type == 'debit')
                                                        <div class="small">
                                                            <strong>{{ $o->responsibleStage->name ?? '' }}</strong> <span class="text-muted mx-1">→</span> <strong>{{ $o->responsibleUnit->name ?? 'N/A' }}</strong>
                                                        </div>
                                                        <div class="badge bg-soft-danger text-danger mt-1">Rs. {{ number_format($o->total_amount, 2) }}</div>
                                                    @else
                                                        <div class="small text-muted">{{ $o->rack->storeroom->name ?? 'N/A' }} / Rack: {{ $o->rack->name ?? 'N/A' }}</div>
                                                        <div class="badge bg-light text-muted mt-1 px-2 py-0 border" style="font-size: 9px;">Location: {{ $o->responsibleUnit->name ?? 'Main' }}</div>
                                                    @endif
                                                </td>
                                                <td><div class="text-muted small italic">{{ $o->remarks ?: '—' }}</div></td>
                                                <td class="pe-4 text-end">
                                                    <div class="fw-bold small text-dark">{{ $o->created_at->format('d M, Y') }}</div>
                                                    <div class="text-muted small mb-1" style="font-size: 10px;">{{ $o->created_at->format('h:i A') }}</div>
                                                    <a href="{{ route('admin.uploaded-slips.outflow-receipt', $o->id) }}" target="_blank" class="btn btn-xs btn-outline-dark px-2 rounded-pill shadow-xs" style="font-size: 9px; padding-top: 1px; padding-bottom: 1px;">
                                                        <i class="fas fa-print me-1"></i> Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Reworks --}}
                                        @foreach($reworks as $r)
                                            @foreach($r->details as $rd)
                                                <tr class="align-middle border-bottom" style="background-color: #fafbfc;">
                                                    <td class="ps-4">
                                                        <span class="badge bg-info text-uppercase px-2 py-1 shadow-sm" style="font-size: 10px;">
                                                            <i class="fas fa-tools me-1"></i> REWORK
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-info italic">Defect/Repair Alteration</div>
                                                        <div class="text-muted small">Size: <strong>{{ $rd->size }}</strong></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="h6 mb-0 fw-bold text-info">{{ $rd->quantity }}</div>
                                                        <small class="text-muted small">pcs</small>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <strong>{{ $r->toStage->name ?? 'N/A' }}</strong> <i class="fas fa-arrow-right mx-1 small text-muted"></i> <strong>{{ $r->toUnit->name ?? 'N/A' }}</strong>
                                                        </div>
                                                    </td>
                                                    <td><div class="text-muted small italic">{{ $r->remarks ?: 'Defect rework order' }}</div></td>
                                                    <td class="pe-4 text-end">
                                                        <div class="fw-bold small text-dark">{{ $r->created_at->format('d M, Y') }}</div>
                                                        <div class="text-muted small mb-1" style="font-size: 10px;">{{ $r->created_at->format('h:i A') }}</div>
                                                        <span class="badge bg-soft-secondary text-muted px-2" style="font-size: 8px;">Slip Ref #{{ $r->id }}</span>
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
                                    <a href="{{ route('admin.packing.downloadSlipBarcode', $packing->id) }}" class="btn btn-sm btn-primary px-3 shadow-xs" style="border-radius: 6px;">
                                        <i class="fas fa-barcode mr-1"></i> Download Barcode TXT
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-success px-3 shadow-xs" data-toggle="modal" data-target="#corpExcelModal{{ $packing->id }}" style="border-radius: 6px;">
                                        <i class="fas fa-file-excel me-1"></i> Corporate Excel
                                    </button>

                                    <!-- Excel Config Modal -->
                                    <div class="modal fade" id="corpExcelModal{{ $packing->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <form action="{{ route('admin.uploaded-slips.corporate-excel', $packing->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">Excel Export Configuration</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3 mb-4 text-left">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label small fw-bold">PO NUMBER</label>
                                                                <input type="text" name="po_no" class="form-control" value="{{ $packing->order?->sku }}" required>
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label small fw-bold">VENDOR CODE (V CD)</label>
                                                                <input type="text" name="v_cd" class="form-control" value="200337">
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label small fw-bold">V NM</label>
                                                                <input type="text" name="v_nm" class="form-control" value="KESHAV MADHAV ENT.">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label small fw-bold">CONTRACT NO (CONT NO)</label>
                                                                <input type="text" name="cont_no" class="form-control" value="9413544380">
                                                            </div>
                                                            <div class="col-md-8 mb-3">
                                                                <label class="form-label small fw-bold">BORA DESCRIPTION</label>
                                                                <input type="text" name="bora_desc" class="form-control" value="BOYS_JEANS" required>
                                                            </div>
                                                        </div>

                                                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-left">Carton Weights (BORA/HU WT-V2)</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover align-middle border">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th width="30%">Carton #</th>
                                                                        <th width="40%">Weight (KG)</th>
                                                                        <th width="30%">Qty (Total Pcs)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($packing->cartons as $carton)
                                                                        <tr>
                                                                            <td><strong>#{{ $carton->carton_no }}</strong></td>
                                                                            <td>
                                                                                <input type="number" step="0.01" name="weights[{{ $carton->id }}]" class="form-control form-control-sm" placeholder="e.g. 17.15" required>
                                                                            </td>
                                                                            <td>{{ $carton->items->sum('quantity') }} Pcs</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success px-4">Generate Excel</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @foreach($packing->cartons as $carton)
                                    <div class="col-md-6 mb-4">
                                        <div class="card shadow-sm border-0 h-100"
                                            style="border-radius: 12px; border-left: 5px solid #007bff !important; background: #fafbfc;">
                                            <div class="card-header bg-white py-3 border-bottom-0 pb-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.1rem;">Carton #{{ $carton->carton_no }}</h6>
                                                        <span class="text-muted small">ID: {{ $carton->id }}</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="h4 mb-0 fw-bold text-primary">{{ $carton->boxes->count() }}</div>
                                                        <div class="text-uppercase text-muted fw-bold" style="font-size: 10px; letter-spacing: 1px;">Total Boxes</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body pt-2">
                                                @php
                                                    $boxed_summary = []; $loose_summary = [];
                                                    $total_boxes = $carton->boxes->count();
                                                    foreach ($carton->items as $item) {
                                                        $name = $item->detail ? $item->detail->size : ($item->size ? $item->size->name : 'ID:' . $item->size_id);
                                                        if($item->packing_box_id) {
                                                            $boxed_summary[$name] = ($boxed_summary[$name] ?? 0) + $item->quantity;
                                                        } else {
                                                            $loose_summary[$name] = ($loose_summary[$name] ?? 0) + $item->quantity;
                                                        }
                                                    }
                                                @endphp

                                                <!-- 1. Boxed Items Summary (Divided by Boxes) -->
                                                @if(count($boxed_summary) > 0 && $total_boxes > 0)
                                                    <div class="p-3 bg-white rounded border mb-3">
                                                        <label class="text-uppercase text-muted fw-bold d-block mb-3" style="font-size: 11px; letter-spacing: 0.5px;">
                                                            <i class="fas fa-boxes me-1 text-primary"></i> Contents (Per Box)
                                                        </label>
                                                        <div class="row g-2">
                                                            @foreach($boxed_summary as $name => $total_qty)
                                                                @php $per_box = $total_qty / $total_boxes; @endphp
                                                                <div class="col-6">
                                                                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border-start border-primary" style="border-left-width: 3px !important;">
                                                                        <span class="fw-bold text-dark small">{{ $name }}</span>
                                                                        <span class="badge bg-white text-primary border px-2 py-1">{{ number_format($per_box, 0) }} Pcs</span>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- 2. Loose Items Summary (Actual piece count) -->
                                                @if(count($loose_summary) > 0)
                                                    <div class="p-3 bg-white rounded border border-warning" style="border-left: 4px solid #f59e0b !important; background-color: #fffbeb !important;">
                                                        <label class="text-uppercase text-warning fw-bold d-block mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                                                            <i class="fas fa-layer-group me-1"></i> Loose Packing Detail
                                                        </label>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($loose_summary as $name => $qty)
                                                                <div class="bg-white border rounded px-2 py-1 shadow-xs small">
                                                                    <span class="text-muted">Size {{ $name }}:</span> <span class="fw-bold text-dark">{{ $qty }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(count($boxed_summary) == 0 && count($loose_summary) == 0)
                                                    <div class="text-center py-4 text-muted small italic">
                                                        No itemized breakdown found.
                                                    </div>
                                                @endif

                                                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center px-2">
                                                    <span class="text-muted small">Total Pieces in Carton:</span>
                                                    <span class="fw-bold text-dark h5 mb-0">{{ array_sum($boxed_summary) + array_sum($loose_summary) }}</span>
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
        </section>
    </div>
@endsection