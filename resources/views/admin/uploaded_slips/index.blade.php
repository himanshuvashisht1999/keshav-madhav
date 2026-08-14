@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <h1 class="m-0 font-weight-bold text-dark text-center">Uploaded Slips</h1>

    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- FILTER CARD -->
            <div class="card shadow-sm border-0">
                <div class="card-body bg-light rounded p-2">
                    <form method="GET" action="{{ route('admin.uploaded-slips.index') }}">
                        <div class="row align-items-end">
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Lot Number</label>
                                <input type="text" name="lot_no" class="form-control form-control-sm" placeholder="Search Lot..." value="{{ request('lot_no') }}">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Bill No</label>
                                <input type="text" name="bill_number" class="form-control form-control-sm" placeholder="Search Bill..." value="{{ request('bill_number') }}">
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">From Stage</label>
                                <select name="from_stage_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All Stages --</option>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}" {{ request('from_stage_id') == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">To Stage</label>
                                <select name="to_stage_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All Stages --</option>
                                    @foreach($stages as $stage)
                                        <option value="{{ $stage->id }}" {{ request('to_stage_id') == $stage->id ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Unit</label>
                                <select name="stage_master_unit_id" class="form-control select2 form-control-sm">
                                    <option value="">-- All Units --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ request('stage_master_unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Status</label>
                                <select name="status" class="form-control select2 form-control-sm">
                                    <option value="all" {{ request('status') === 'all' || !request()->has('status') ? 'selected' : '' }}>-- Show All --</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Digitized</option>
                                    <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Skipped</option>
                                </select>
                            </div>
                            <div class="col-md mb-2">
                                <label class="small font-weight-bold text-muted mb-1">Date</label>
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                            </div>
                            <div class="col-md-auto mb-2 text-right">
                                <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm mr-1">
                                    <i class="fas fa-filter"></i>
                                </button>
                                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn btn-sm btn-outline-secondary px-2 shadow-sm">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card shadow border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>From Stage</th>
                                    <th>Unit</th>
                                    <th>Lot No</th>
                                    <th>Bill No</th>
                                    <th>To Stage</th>
                                    <th>ID</th>

                                    <th class="text-center">Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($slips as $slip)
                                <tr>
                                    <td>{{ $slip->created_at->format('d M Y') }}</td>
                                    <td>
                                        {{ $slip->fromStage->name ?? '-' }}
                                    </td>
                                    <td>{{ $slip->getUnitMaster->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $allLots = collect();
                                            if($slip->lot_no) $allLots->push($slip->lot_no);
                                            
                                            // Gather lots from all linked sessions
                                            if($slip->orderLots->isNotEmpty()) $allLots = $allLots->merge($slip->orderLots->pluck('lot_no'));
                                            if($slip->orderPrintingStageTransaction->isNotEmpty()) $allLots = $allLots->merge($slip->orderPrintingStageTransaction->pluck('lot_no'));
                                            if($slip->orderStageTransaction->isNotEmpty()) $allLots = $allLots->merge($slip->orderStageTransaction->pluck('lot_no'));
                                            if($slip->orderPrintingToStichingTransaction->isNotEmpty()) $allLots = $allLots->merge($slip->orderPrintingToStichingTransaction->pluck('lot_no'));
                                            if($slip->orderGodamStageTransaction->isNotEmpty()) $allLots = $allLots->merge($slip->orderGodamStageTransaction->pluck('lot_no'));
                                            
                                            $distinctLots = $allLots->unique()->filter();
                                        @endphp

                                        @php $totalSlipQty = 0; @endphp
                                        @if($distinctLots->isNotEmpty())
                                            @foreach($distinctLots as $lot)
                                                @php
                                                    $lotQty = 0;
                                                    
                                                    // Gather quantities from various transactions
                                                    $lotQty += $slip->orderPrintingStageTransaction->where('lot_no', $lot)->sum('quantity');
                                                    $lotQty += $slip->orderStageTransaction->where('lot_no', $lot)->sum('quantity');
                                                    $lotQty += $slip->orderPrintingToStichingTransaction->where('lot_no', $lot)->sum('quantity');
                                                    $lotQty += $slip->orderGodamStageTransaction->where('lot_no', $lot)->sum('quantity');
                                                    
                                                    // Calculate from rolls if no transaction quantity (i.e. Cutting stage)
                                                    if ($lotQty == 0 && $slip->fabricRollAssignings) {
                                                        foreach($slip->fabricRollAssignings->where('lot_no', $lot) as $roll) {
                                                            if ($roll->fabricRollAssigningsDetail) {
                                                                $lotQty += $roll->fabricRollAssigningsDetail->sum('quantity');
                                                            }
                                                        }
                                                    }
                                                    $totalSlipQty += $lotQty;
                                                @endphp
                                                <span class="badge badge-info shadow-sm mb-1">#{{ $lot }} @if($lotQty > 0) ({{ $lotQty }}) @endif</span><br>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light shadow-sm">
                                            {{ $slip->bill_number ?? '-' }}
                                            @if(isset($totalSlipQty) && $totalSlipQty > 0)
                                                ({{ $totalSlipQty }})
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($slip->toStage)
                                            {{ $slip->toStage->name }}
                                        @else
                                            @php
                                                // Get the first available next stage/unit from any transaction session
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
                                                {{ $nextStage->name }}
                                                @if($nextUnit)
                                                    <br><small class="text-muted">({{ $nextUnit->name }})</small>
                                                @endif
                                                @if($sessionCount > 1)
                                                    <br><span class="badge badge-light border" style="font-size: 10px;">+{{ $sessionCount - 1 }} More Sessions</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="font-weight-bold text-primary">{{ $slip->id }}</td>

                                    <td class="text-center">
                                        @if($slip->status == 0)
                                            <span class="badge badge-warning px-2 py-1">Pending</span>
                                        @elseif($slip->status == 2)
                                            <span class="badge badge-danger px-2 py-1">Skipped</span>
                                        @else
                                            <span class="badge badge-success px-2 py-1">Digitized</span>
                                        @endif
                                    </td>
                                    <td class="text-right d-flex justify-content-end align-items-center">
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
                                            <a href="{{ route('admin.uploaded-slips.show', $slip->id) }}" class="btn btn-outline-success btn-sm shadow-sm mr-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif

                                        @if($slip->status == 0) {{-- Only if Pending --}}
                                            <a href="{{ $actionRoute }}" class="btn btn-primary btn-sm shadow-sm" title="Digitize More/Start">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        @endif

                                        @if($slip->status == 0)
                                            <form action="{{ route('admin.uploaded-slips.finalize', $slip->id) }}" method="POST" onsubmit="return confirm('Mark this slip as Finalized?');" style="display:inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info btn-sm shadow-sm ml-1" title="Finalize">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($totalSessions == 0)
                                            <form action="{{ route('admin.uploaded-slips.destroy', $slip->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this slip?');" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm ml-1" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No slips found matching your criteria.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- PAGINATION -->
                <div class="card-footer bg-white d-flex justify-content-end py-3">
                    {{ $slips->withQueryString()->links() }}
                </div>
            </div>

        </div>
    </section>
</div>

<style>
    .card { border-radius: 8px; }
    .form-control { border-radius: 6px; }
    .table thead th { 
        border: none; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.85rem; 
        letter-spacing: 0.5px; 
        background: #f3f4f6;
        color: #374151;
    }
    .table tbody td { vertical-align: middle; font-size: 0.95rem; }
    .select2-container .select2-selection--single { height: 38px; border: 1px solid #ced4da; border-radius: 6px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default', // Using default to match custom CSS overrides
            width: '100%'
        });
    });
</script>
@endsection
