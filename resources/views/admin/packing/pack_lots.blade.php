@extends('admin.layouts.app')

@section('content')
<style>
    /* Enterprise ERP Design System */
    :root {
        --erp-bg: #f4f6f9;
        --erp-card-bg: #ffffff;
        --erp-border: #e0e4e8;
        --erp-primary: #0056b3;
        --erp-text-main: #333333;
        --erp-text-muted: #6c757d;
        --erp-header-bg: #f8f9fa;
        --erp-radius: 4px;
        --erp-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .content-wrapper {
        background-color: var(--erp-bg);
    }

    .erp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: var(--erp-card-bg);
        border-bottom: 1px solid var(--erp-border);
        margin-bottom: 1rem;
    }

    .erp-header h1 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin: 0;
    }

    .erp-card {
        background: var(--erp-card-bg);
        border: 1px solid var(--erp-border);
        border-radius: var(--erp-radius);
        box-shadow: var(--erp-shadow);
        margin-bottom: 1.5rem;
    }

    .erp-card-header {
        background-color: var(--erp-header-bg);
        border-bottom: 1px solid var(--erp-border);
        padding: 0.75rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .erp-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--erp-text-main);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .erp-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .erp-table th {
        background-color: #f1f3f5;
        color: #495057;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        padding: 0.75rem;
        border-bottom: 2px solid #dee2e6;
        border-top: none;
    }

    .erp-table td {
        padding: 0.75rem;
        font-size: 0.9rem;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        color: #333;
    }

    .erp-badge {
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 0.25rem;
    }

    .erp-badge-success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    
    .btn-erp {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border-radius: var(--erp-radius);
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: background-color 0.15s ease-in-out;
    }
    .btn-erp-default {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #333;
    }
</style>

<div class="content-wrapper">
    <!-- Top Header Bar -->
    <div class="erp-header shadow-sm">
        <h1>
            <i class="fas fa-box-open mr-2 text-primary"></i> 
            Step 2: Pack Selected Lots | <span class="text-muted" style="font-size: 1rem; font-weight: normal;">Slip #{{ $slip_id }}</span>
        </h1>
        <div class="d-flex" style="gap: 8px;">
            @php $slip = \App\Models\ProductionSlipDigitization::find($slip_id); @endphp
            @if($slip && $slip->slip_file)
                <button type="button" class="btn btn-erp btn-info text-white" data-toggle="modal" data-target="#slipModal">
                    <i class="fas fa-image mr-1"></i> View Production Slip
                </button>
            @endif
            <button type="button" id="btnResetSlip" class="btn btn-erp btn-outline-danger">
                <i class="fas fa-trash-restore mr-1"></i> Reset Slip
            </button>
            <button type="button" id="btnFinalizePacking" class="btn btn-erp btn-success text-white">
                <i class="fas fa-check-circle mr-1"></i> Finalize Packing
            </button>
            <button type="button" class="btn btn-erp btn-erp-default" onclick="handleBackToSelection()">
                <i class="fas fa-arrow-left text-muted"></i> Back to Selection
            </button>
        </div>
    </div>

    <div class="container-fluid px-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @php
            $consolidated_sizes = [];
            foreach ($lots_data as $lot) {
                if (isset($set_details[$lot->set_id])) {
                    $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                    
                    // Sum up deductions for this lot in the current slip session first
                    $packed_for_lot = 0;
                    if (isset($packed_by_lot_size[$lot->lot_no])) {
                        $packed_for_lot = $packed_by_lot_size[$lot->lot_no]->sum('total');
                    }
                    $rework_for_lot = 0;
                    if (isset($rework_by_lot_size[$lot->lot_no])) {
                        $rework_for_lot = $rework_by_lot_size[$lot->lot_no]->sum('total');
                    }
                    $outflow_for_lot = 0;
                    if (isset($outflow_by_lot_size[$lot->lot_no])) {
                        $outflow_for_lot = $outflow_by_lot_size[$lot->lot_no]->sum('total');
                    }
                    
                    $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                    
                    foreach ($set_details[$lot->set_id] as $detail) {
                        $sizeName = trim(strtoupper($detail->size));
                        
                        $incoming_qty = !empty($lot->incoming_sizes) 
                            ? (int) ($lot->incoming_sizes[$sizeName] ?? 0) 
                            : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                        
                        $packed_qty = 0;
                        if (isset($packed_by_lot_size[$lot->lot_no])) {
                            $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                            if ($item) $packed_qty = $item->total;
                        }

                        $rework_qty = 0;
                        if (isset($rework_by_lot_size[$lot->lot_no])) {
                            $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                            if ($item) $rework_qty = $item->total;
                        }

                        $outflow_qty = 0;
                        if (isset($outflow_by_lot_size[$lot->lot_no])) {
                            $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                            if ($item) $outflow_qty = $item->total;
                        }

                        $live_remaining_for_size = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);

                        if (!isset($consolidated_sizes[$sizeName])) {
                            $consolidated_sizes[$sizeName] = [
                                'size' => $detail->size,
                                'breakdown' => 0,
                                'live' => 0
                            ];
                        }
                        $consolidated_sizes[$sizeName]['breakdown'] += $live_remaining_for_size;
                        $consolidated_sizes[$sizeName]['live'] += $live_remaining_for_size;
                    }
                }
            }
            ksort($consolidated_sizes);
        @endphp

        <!-- Permanent Consolidated Size Breakdown Bar (Sticky Page-level) -->
        <div class="card border shadow-sm mb-3" style="position: sticky; top: 0; z-index: 1040; background-color: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);">
            <div class="card-body px-3 py-2">
                <div class="d-flex align-items-center flex-wrap" style="gap: 12px; font-size: 0.82rem;">
                    <span class="font-weight-bold text-secondary"><i class="fas fa-chart-pie mr-1 text-info"></i> Consolidated Size Wise Stocks:</span>
                    <div class="d-flex flex-wrap" style="gap: 8px;">
                        @foreach($consolidated_sizes as $szName => $data)
                        @php
                            $cleanSize = str_replace([' ', '.'], '-', trim(strtoupper($data['size'])));
                        @endphp
                        <div class="border rounded px-2 py-1 bg-light d-flex align-items-center" style="gap: 5px;">
                            <span class="font-weight-bold text-dark">{{ strtoupper($data['size']) }}:</span>
                            <span class="badge badge-light text-primary border py-0 px-1">Lot: <span class="consolidated-lot-val" data-size="{{ $szName }}">{{ $data['breakdown'] }}</span></span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="card erp-card border shadow-sm mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3" data-toggle="collapse" data-target="#collapseLotsTable" aria-expanded="false" aria-controls="collapseLotsTable" style="cursor: pointer;">
                <span class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                    <i class="fas fa-list mr-1 text-primary"></i> 
                    Selected Lots Summary ({{ count($lots_data) }} Lots Selected — Total Pending: {{ collect($lots_data)->sum('remaining_quantity') }} pcs)
                </span>
                <span class="btn btn-sm btn-link text-primary font-weight-bold p-0 text-decoration-none">
                    <i class="fas fa-chevron-down mr-1"></i> Toggle Detailed Breakdown
                </span>
            </div>
            <div id="collapseLotsTable" class="collapse">
                <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table erp-table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                     <thead class="thead-light">
                        <tr>
                            <th width="15%">Lot NO</th>
                            <th width="25%">Design / Profile</th>
                            <th width="15%" class="text-center">Total Pending</th>
                             <th width="45%">Sizes Wise Breakdown</th>
                        </tr>
                    </thead>
                    @php
                        $grand_total_pending = 0;
                        $grand_total_breakdown = 0;
                        $grand_total_remaining = 0;
                    @endphp
                    <tbody>
                        @foreach($lots_data as $lot)
                        @php
                            $packed_for_lot = 0;
                            if (isset($packed_by_lot_size[$lot->lot_no])) {
                                $packed_for_lot = $packed_by_lot_size[$lot->lot_no]->sum('total');
                            }
                            $rework_for_lot = 0;
                            if (isset($rework_by_lot_size[$lot->lot_no])) {
                                $rework_for_lot = $rework_by_lot_size[$lot->lot_no]->sum('total');
                            }
                            $outflow_for_lot = 0;
                            if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                $outflow_for_lot = $outflow_by_lot_size[$lot->lot_no]->sum('total');
                            }
                            
                            $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                            $total_breakdown_qty = 0;
                            $total_live_remaining = 0;
                            $sizes_data = [];

                            if (isset($set_details[$lot->set_id])) {
                                $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                                foreach($set_details[$lot->set_id] as $detail) {
                                    $sizeName = trim(strtoupper($detail->size));
                                    
                                    $incoming_qty = !empty($lot->incoming_sizes) 
                                        ? (int) ($lot->incoming_sizes[$sizeName] ?? 0) 
                                        : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                                    
                                    $packed_qty = 0;
                                    if (isset($packed_by_lot_size[$lot->lot_no])) {
                                        $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                        if ($item) $packed_qty = $item->total;
                                    }

                                    $rework_qty = 0;
                                    if (isset($rework_by_lot_size[$lot->lot_no])) {
                                        $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                        if ($item) $rework_qty = $item->total;
                                    }

                                    $outflow_qty = 0;
                                    if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                        $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                        if ($item) $outflow_qty = $item->total;
                                    }

                                    $live_remaining_for_size = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);
                                    $total_live_remaining += $live_remaining_for_size;
                                    $total_breakdown_qty += $live_remaining_for_size;
                                    
                                    if ($incoming_qty > 0 || $live_remaining_for_size > 0) {
                                        $sizes_data[] = [
                                            'size' => $detail->size,
                                            'breakdown' => $live_remaining_for_size,
                                            'live' => $live_remaining_for_size,
                                            'clean_size' => str_replace([' ', '.'], '-', trim(strtoupper($detail->size)))
                                        ];
                                    }
                                }
                            }
                            
                            $grand_total_pending += $total_breakdown_qty;
                            $grand_total_breakdown += $total_breakdown_qty;
                            $grand_total_remaining += $total_live_remaining;
                        @endphp
                        <tr>
                            <td class="font-weight-bold align-middle">
                                <span class="erp-badge erp-badge-info">LOT-{{ str_pad($lot->lot_no, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="align-middle">
                                <strong>{{ $lot->design_number }}</strong><br>
                                <span class="text-muted">{{ $lot->size_set_name }}</span><br>
                                <span class="badge badge-light border mt-1">{{ $lot->color_name }}</span>
                            </td>
                            <td class="text-center align-middle font-weight-bold text-success" style="font-size: 1.1rem;">
                                {{ $total_breakdown_qty }}
                            </td>
                            <td class="align-middle">
                                @if(!empty($sizes_data))
                                    <div class="d-flex flex-wrap" style="gap: 6px;">
                                        @foreach($sizes_data as $sd)
                                        <div class="border rounded px-2 py-1 bg-white border-secondary d-flex align-items-center" style="gap: 5px; font-size: 0.8rem; line-height: 1.2;">
                                            <span class="font-weight-bold text-dark">{{ strtoupper($sd['size']) }}:</span>
                                            <span class="badge badge-light text-primary border py-0 px-1" title="Lot Qty Breakdown">Lot: {{ $sd['breakdown'] }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    
                                    <div class="w-100 mt-1 border-top pt-1 d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                                        <span class="text-muted">Sizes: {{ count($sizes_data) }}</span>
                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <span>Total Breakdown: <strong class="text-primary">{{ $total_breakdown_qty }} pcs</strong></span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">No size data</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="2" class="text-right align-middle" style="font-size: 0.9rem;">Grand Total Selected Lots:</td>
                            <td class="text-center align-middle text-success font-weight-bold" style="font-size: 1.15rem;">
                                {{ $grand_total_breakdown }}
                            </td>
                            <td class="align-middle">
                                <div class="d-flex justify-content-end align-items-center" style="gap: 15px;">
                                    <span>Grand Total Breakdown: <strong class="text-primary" style="font-size: 0.95rem;">{{ $grand_total_breakdown }} pcs</strong></span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @php
            $isDomesticOrder = isset($order) && strtolower(trim($order->order_type)) === 'domestic';
            $domesticTitle = $isDomesticOrder ? 'Domestic Packing Operations' : 'Divert to Domestic';
            $domesticSubmitBtn = $isDomesticOrder ? 'Submit Packing' : 'Submit Diversion';

            $saved_rework_total = 0;
            if (isset($saved_reworks) && !empty($saved_reworks)) {
                foreach ($saved_reworks as $srw) {
                    if ($srw->details) {
                        $saved_rework_total += (int) $srw->details->sum('quantity');
                    }
                }
            }
            $saved_dead_total = isset($saved_dead) && !empty($saved_dead) ? (int) $saved_dead->sum('quantity') : 0;
            $saved_sampling_total = isset($saved_sampling) && !empty($saved_sampling) ? (int) $saved_sampling->sum('quantity') : 0;
            $saved_debit_total = isset($saved_debit) && !empty($saved_debit) ? (int) $saved_debit->sum('quantity') : 0;
        @endphp

        <!-- Packing Action Tabs -->
        <div class="erp-card mt-4">
            <div class="card-header bg-white p-0" style="border-bottom: 2px solid var(--erp-border);">
                <ul class="nav nav-tabs border-0" id="packingTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="tab-packing-tab" data-toggle="tab" href="#tab-packing" role="tab">
                            <i class="fas fa-box text-primary"></i> {{ $isDomesticOrder ? 'Domestic Packing' : 'Packing' }}
                        </a>
                    </li>
                    @if(!$isDomesticOrder)
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-domestic-tab" data-toggle="tab" href="#tab-domestic" role="tab">
                            <i class="fas fa-random text-info"></i> Divert to Domestic
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-planner-tab" data-toggle="tab" href="#tab-planner" role="tab">
                            <i class="fas fa-layer-group text-info"></i> Multi-Carton Planner
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold d-flex align-items-center" id="tab-rework-tab" data-toggle="tab" href="#tab-rework" role="tab">
                            <i class="fas fa-exclamation-triangle text-danger mr-1"></i> Defect / Rework
                            <span id="reworkTabBadge" class="badge badge-danger ml-1 font-weight-bold" style="{{ $saved_rework_total > 0 ? '' : 'display:none;' }} font-size: 0.72rem; padding: 2px 6px;">{{ $saved_rework_total > 0 ? $saved_rework_total . ' Pcs' : '0 Pcs' }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold d-flex align-items-center" id="tab-damage-tab" data-toggle="tab" href="#tab-damage" role="tab">
                            <i class="fas fa-skull-crossbones text-dark mr-1"></i> Dead / Damage
                            <span id="damageTabBadge" class="badge badge-dark ml-1 font-weight-bold" style="{{ $saved_dead_total > 0 ? '' : 'display:none;' }} font-size: 0.72rem; padding: 2px 6px;">{{ $saved_dead_total > 0 ? $saved_dead_total . ' Pcs' : '0 Pcs' }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold d-flex align-items-center" id="tab-sampling-tab" data-toggle="tab" href="#tab-sampling" role="tab">
                            <i class="fas fa-flask text-primary mr-1"></i> Sampling
                            <span id="samplingTabBadge" class="badge badge-primary ml-1 font-weight-bold" style="{{ $saved_sampling_total > 0 ? '' : 'display:none;' }} font-size: 0.72rem; padding: 2px 6px;">{{ $saved_sampling_total > 0 ? $saved_sampling_total . ' Pcs' : '0 Pcs' }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold d-flex align-items-center" id="tab-debit-tab" data-toggle="tab" href="#tab-debit" role="tab">
                            <i class="fas fa-minus-circle text-warning mr-1"></i> Debit
                            <span id="debitTabBadge" class="badge badge-warning text-dark ml-1 font-weight-bold" style="{{ $saved_debit_total > 0 ? '' : 'display:none;' }} font-size: 0.72rem; padding: 2px 6px;">{{ $saved_debit_total > 0 ? $saved_debit_total . ' Pcs' : '0 Pcs' }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="packingTabsContent">
                    <div class="tab-pane fade show active" id="tab-packing" role="tabpanel">
                        @if($isDomesticOrder)
                            @include('admin.packing.partials.domestic_packing_form')
                        @else
                            <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-box"></i> Packing Operations</h5>
                            <p class="text-muted">Standard packing interface (Create Carton) will be implemented here.</p>
                        @endif
                    </div>
                    @if(!$isDomesticOrder)
                    <div class="tab-pane fade" id="tab-domestic" role="tabpanel">
                        @include('admin.packing.partials.domestic_packing_form')
                    </div>
                    @endif
                    <div class="tab-pane fade" id="tab-planner" role="tabpanel">
                        <h5 class="text-info border-bottom pb-2 mb-3"><i class="fas fa-layer-group"></i> Multi-Carton Planner</h5>
                        
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold mb-3 small text-uppercase text-primary">Range Quick Add</h6>
                                <div class="row align-items-end">
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Start Carton NO</label>
                                        <input type="number" id="plannerStart" class="form-control form-control-sm" placeholder="e.g. 1">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">End Carton NO</label>
                                        <input type="number" id="plannerEnd" class="form-control form-control-sm" placeholder="e.g. 10">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Type</label>
                                        <select id="plannerType" class="form-control form-control-sm select2">
                                            <option value="Box" selected>Box</option>
                                            <option value="Loose">Loose</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Design</label>
                                        <select id="plannerDesign" class="form-control form-control-sm select2">
                                            <option value="">Select Design</option>
                                            @foreach($unique_designs as $design)
                                            <option value="{{ $design }}">{{ $design }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2" id="sizeSetCol">
                                        <label class="small font-weight-bold">Size Set</label>
                                        <select id="plannerSizeSet" class="form-control form-control-sm select2" disabled>
                                            <option value="">Select Size Set</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2" id="singleSizeCol" style="display: none;">
                                        <label class="small font-weight-bold">Size</label>
                                        <select id="plannerSingleSize" class="form-control form-control-sm select2">
                                            <option value="">Select Size</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Color</label>
                                        <select id="plannerColor" class="form-control form-control-sm select2">
                                            <option value="">Select Color</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Qty / Carton</label>
                                        <input type="number" id="plannerQty" class="form-control form-control-sm" value="1">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">MRP</label>
                                        <input type="number" step="0.01" id="plannerMrp" class="form-control form-control-sm" placeholder="0.00">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Price</label>
                                        <input type="number" step="0.01" id="plannerPrice" class="form-control form-control-sm" placeholder="0.00">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Warehouse</label>
                                        <select id="plannerWarehouse" class="form-control form-control-sm select2">
                                            <option value="">Select Store Room</option>
                                            @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Rack (Optional)</label>
                                        <select id="plannerRack" class="form-control form-control-sm select2">
                                            <option value="">Select Rack</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Barcode (Optional)</label>
                                        <input type="text" id="plannerBarcode" class="form-control form-control-sm" placeholder="Optional">
                                    </div>
                                    <div class="col-md-12 d-flex justify-content-end mt-2">
                                        <button class="btn btn-primary btn-sm px-4" id="btnSavePlanDirect">
                                            <i class="fas fa-save mr-1"></i> Save Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <hr class="my-4">

                        @php
                            $saved_grand_total_pieces = 0;
                            $saved_grand_total_sets = 0;
                            foreach ($saved_cartons as $sc) {
                                $total_qty = $sc->items->sum('quantity');
                                $no_of_pcs = 1;
                                if ($sc->items->first() && 
                                    $sc->items->first()->detail && 
                                    $sc->items->first()->detail->orderProductSet && 
                                    $sc->items->first()->detail->orderProductSet->size_measurement) {
                                    $no_of_pcs = $sc->items->first()->detail->orderProductSet->size_measurement->no_of_pcs ?: 1;
                                }
                                $total_sets = $no_of_pcs > 0 ? ($total_qty / $no_of_pcs) : 0;
                                $saved_grand_total_pieces += $total_qty;
                                $saved_grand_total_sets += $total_sets;
                            }
                        @endphp
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0">Saved Cartons <span class="badge badge-secondary">{{ $saved_cartons->count() }}</span> — Total Sets: <span class="badge badge-info">{{ round($saved_grand_total_sets, 2) }}</span></h6>
                            <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-cartons" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
                        </div>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th width="3%" class="text-center"><input type="checkbox" class="select-all-cartons"></th>
                                        <!-- <th>ID</th> -->
                                        <th>Carton NO</th>
                                        <th>Total Sets</th>
                                        <th>Total Pcs</th>
                                        <th>Design</th>
                                        <th>Size Set</th>
                                        <th>Color</th>
                                        <th>MRP</th>
                                        <th>Price</th>
                                        <!-- <th>Barcode</th> -->
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_cartons as $sc)
                                    @php
                                        $design = 'N/A';
                                        $size_set = 'N/A';
                                        $color = 'N/A';
                                        $total_qty = $sc->items->sum('quantity');

                                        $no_of_pcs = 1;
                                        if ($sc->items->first() && 
                                            $sc->items->first()->detail && 
                                            $sc->items->first()->detail->orderProductSet && 
                                            $sc->items->first()->detail->orderProductSet->size_measurement) {
                                            $no_of_pcs = $sc->items->first()->detail->orderProductSet->size_measurement->no_of_pcs ?: 1;
                                        }
                                        $total_sets = $no_of_pcs > 0 ? ($total_qty / $no_of_pcs) : 0;

                                        if ($sc->note) {
                                            $meta = json_decode($sc->note, true);
                                            if (is_array($meta)) {
                                                $design = $meta['design'] ?? 'N/A';
                                                $size_set = $meta['size_set_name'] ?? 'N/A';
                                                $color = $meta['color_name'] ?? 'N/A';
                                            }
                                        } else {
                                            $first_item = $sc->items->first();
                                            if ($first_item) {
                                                $lot_info = collect($lots_data)->firstWhere('lot_no', $first_item->lot_no);
                                                if ($lot_info) {
                                                    $design = $lot_info->design_number;
                                                    $size_set = $lot_info->size_set_name;
                                                    $color = $lot_info->color_name;
                                                }
                                            }
                                        }

                                        $mrp = $sc->items->first() ? $sc->items->first()->mrp : 0;
                                        $price = $sc->items->first() ? $sc->items->first()->selling_price : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="carton-chk" value="{{ $sc->id }}"></td>
                                        <!-- <td>{{ $sc->id }}</td> -->
                                        <td class="font-weight-bold">{{ $sc->carton_no }}</td>
                                        <td class="font-weight-bold text-info">{{ round($total_sets, 2) }}</td>
                                        <td>{{ $total_qty }}</td>
                                        <td>{{ $design }}</td>
                                        <td>{{ $size_set }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ number_format($mrp, 2) }}</td>
                                        <td>{{ number_format($price, 2) }}</td>
                                        <!-- <td>{{ $sc->barcode ?? 'N/A' }}</td> -->
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-carton" data-id="{{ $sc->id }}">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">No cartons saved yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($saved_cartons->count() > 0)
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="text-right">Grand Total:</td>
                                        <td class="text-info">{{ round($saved_grand_total_sets, 2) }} Sets</td>
                                        <td>{{ $saved_cartons->count() }} Cartons</td>
                                        <td class="text-success">{{ $saved_grand_total_pieces }} Pcs</td>
                                        <td colspan="5"></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="tab-rework" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="text-danger mb-0"><i class="fas fa-exclamation-triangle"></i> Defect / Rework</h5>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="badge badge-light border text-danger px-2 py-1 font-weight-bold" style="{{ $saved_rework_total > 0 ? '' : 'display:none;' }} font-size: 0.85rem;">
                                    Saved: <strong id="reworkSavedDisplay">{{ $saved_rework_total }} Pcs</strong>
                                </span>
                                <span class="text-muted font-weight-bold" style="font-size: 0.88rem;">Total Defect Quantity:</span>
                                <span id="reworkTopTotalBadge" class="badge badge-danger px-3 py-1 font-weight-bold" style="font-size: 0.95rem; border-radius: 6px;">{{ $saved_rework_total }} Pcs</span>
                            </div>
                        </div>
                        
                        <form id="reworkForm">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Select Stage <span class="text-danger">*</span></label>
                                    <select id="reworkStage" class="form-control form-control-sm select2" required>
                                        <option value="">-- Select Stage --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Select Unit <span class="text-danger">*</span></label>
                                    <select id="reworkUnit" class="form-control form-control-sm select2" required>
                                        <option value="">-- Select Unit --</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Remarks</label>
                                    <input type="text" id="reworkRemarks" class="form-control form-control-sm" placeholder="Optional notes...">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" id="btnSaveRework" class="btn btn-sm btn-danger w-100">
                                        <i class="fas fa-save"></i> Save Rework
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table erp-table table-sm table-bordered">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th>LOT NO</th>
                                            <th>DESIGN / COLOR</th>
                                            <th>SIZE WISE DEFECT QTY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lots_data as $lot)
                                            <tr>
                                                <td class="font-weight-bold">{{ $lot->lot_no }}</td>
                                                <td>
                                                    <div>{{ $lot->design_number }}</div>
                                                    <small class="text-muted">{{ $lot->color_name }}</small>
                                                </td>
                                                <td>
                                                    @if(isset($set_details[$lot->set_id]))
                                                        @php
                                                            $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                                                            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                                                        @endphp
                                                        <div class="d-flex flex-column" style="gap: 5px; max-width: 280px;">
                                                            <div class="row no-gutters font-weight-bold text-muted border-bottom pb-1 mb-1" style="font-size: 0.75rem;">
                                                                <div class="col-7">Size</div>
                                                                <div class="col-5 text-center">Qty</div>
                                                            </div>
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $incoming_qty = !empty($lot->incoming_sizes) 
                                                                        ? (int) ($lot->incoming_sizes[$sizeName] ?? 0) 
                                                                        : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                                                                    if ($incoming_qty <= 0) continue;

                                                                    $packed_qty = 0;
                                                                    if (isset($packed_by_lot_size[$lot->lot_no])) {
                                                                        $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $packed_qty = $item->total;
                                                                    }
                                                                    $rework_qty = 0;
                                                                    if (isset($rework_by_lot_size[$lot->lot_no])) {
                                                                        $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $rework_qty = $item->total;
                                                                    }
                                                                    $outflow_qty = 0;
                                                                    if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                                                        $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $outflow_qty = $item->total;
                                                                    }
                                                                    $live_remaining_for_size = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);

                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $inputId = "rework-input-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="row no-gutters align-items-center mb-1" style="font-size: 0.8rem;">
                                                                    <div class="col-7 text-dark font-weight-bold">{{ $sizeName }} <span class="badge badge-light text-muted border py-0 px-1 ml-1" style="font-size: 0.7rem;">Max: {{ $live_remaining_for_size }}</span></div>
                                                                    <div class="col-5">
                                                                        <input type="number" 
                                                                            id="{{ $inputId }}" 
                                                                            class="form-control form-control-sm text-center font-weight-bold rework-qty-input border py-0" 
                                                                            style="height: 26px;"
                                                                            data-transaction-id="{{ $lot->transaction_id }}"
                                                                            data-lot-no="{{ $lot->lot_no }}"
                                                                            data-detail-id="{{ $detail->id }}"
                                                                            data-size-name="{{ $sizeName }}"
                                                                            data-max="{{ $live_remaining_for_size }}"
                                                                            max="{{ $live_remaining_for_size }}"
                                                                            min="0" 
                                                                            placeholder="0">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <hr class="my-4">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0 text-danger">Saved Rework Records <span class="badge badge-secondary">{{ count($saved_reworks ?? []) }}</span></h6>
                            <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-rework" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="3%" class="text-center"><input type="checkbox" class="select-all-rework"></th>
                                        <th>LOT NO</th>
                                        <th>To Stage</th>
                                        <th>To Unit</th>
                                        <th>Size : Qty</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_reworks ?? [] as $rw)
                                        <tr>
                                            <td class="text-center"><input type="checkbox" class="rework-chk" value="{{ $rw->id }}"></td>
                                            <td class="font-weight-bold">{{ $rw->lot_no }}</td>
                                            <td>{{ $rw->toStage->name ?? 'N/A' }}</td>
                                            <td>{{ $rw->toUnit->name ?? 'N/A' }}</td>
                                            <td>
                                                @foreach($rw->details as $d)
                                                    <span class="badge badge-light border">{{ $d->size ?? 'Unknown' }}: {{ $d->quantity }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $rw->remarks ?? '-' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-rework" data-id="{{ $rw->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-3">No rework records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-damage" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="text-dark mb-0"><i class="fas fa-skull-crossbones"></i> Dead / Damage</h5>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="badge badge-light border text-dark px-2 py-1 font-weight-bold" style="{{ $saved_dead_total > 0 ? '' : 'display:none;' }} font-size: 0.85rem;">
                                    Saved: <strong id="damageSavedDisplay">{{ $saved_dead_total }} Pcs</strong>
                                </span>
                                <span class="text-muted font-weight-bold" style="font-size: 0.88rem;">Total Dead Stock:</span>
                                <span id="damageTopTotalBadge" class="badge badge-dark px-3 py-1 font-weight-bold" style="font-size: 0.95rem; border-radius: 6px;">{{ $saved_dead_total }} Pcs</span>
                            </div>
                        </div>
                        
                        <form id="damageForm">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Select Storeroom / Rack <span class="text-danger">*</span></label>
                                    <select id="damageRack" class="form-control form-control-sm select2" required>
                                        <option value="">-- Select Rack --</option>
                                        @foreach($storerooms as $store)
                                            <optgroup label="{{ $store->name }}">
                                                @foreach($store->racks as $rack)
                                                    <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Remarks</label>
                                    <input type="text" id="damageRemarks" class="form-control form-control-sm" placeholder="Optional notes...">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" id="btnSaveDamage" class="btn btn-sm btn-dark w-100">
                                        <i class="fas fa-save"></i> Save Dead Stock
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table erp-table table-sm table-bordered">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th>LOT NO</th>
                                            <th>DESIGN / COLOR</th>
                                            <th>SIZE WISE DAMAGE QTY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lots_data as $lot)
                                            <tr>
                                                <td class="font-weight-bold">{{ $lot->lot_no }}</td>
                                                <td>
                                                    <div>{{ $lot->design_number }}</div>
                                                    <small class="text-muted">{{ $lot->color_name }}</small>
                                                </td>
                                                <td>
                                                    @if(isset($set_details[$lot->set_id]))
                                                        @php
                                                            $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                                                            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                                                        @endphp
                                                        <div class="d-flex flex-column" style="gap: 5px; max-width: 280px;">
                                                            <div class="row no-gutters font-weight-bold text-muted border-bottom pb-1 mb-1" style="font-size: 0.75rem;">
                                                                <div class="col-7">Size</div>
                                                                <div class="col-5 text-center">Qty</div>
                                                            </div>
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $incoming_qty = !empty($lot->incoming_sizes) 
                                                                        ? (int) ($lot->incoming_sizes[$sizeName] ?? 0) 
                                                                        : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                                                                    if ($incoming_qty <= 0) continue;

                                                                    $packed_qty = 0;
                                                                    if (isset($packed_by_lot_size[$lot->lot_no])) {
                                                                        $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $packed_qty = $item->total;
                                                                    }
                                                                    $rework_qty = 0;
                                                                    if (isset($rework_by_lot_size[$lot->lot_no])) {
                                                                        $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $rework_qty = $item->total;
                                                                    }
                                                                    $outflow_qty = 0;
                                                                    if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                                                        $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $outflow_qty = $item->total;
                                                                    }
                                                                    $live_remaining_for_size = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);

                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $inputId = "damage-input-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="row no-gutters align-items-center mb-1" style="font-size: 0.8rem;">
                                                                    <div class="col-7 text-dark font-weight-bold">{{ $sizeName }} <span class="badge badge-light text-muted border py-0 px-1 ml-1" style="font-size: 0.7rem;">Max: {{ $live_remaining_for_size }}</span></div>
                                                                    <div class="col-5">
                                                                        <input type="number" 
                                                                            id="{{ $inputId }}" 
                                                                            class="form-control form-control-sm text-center font-weight-bold damage-qty-input border py-0" 
                                                                            style="height: 26px;"
                                                                            data-transaction-id="{{ $lot->transaction_id }}"
                                                                            data-lot-no="{{ $lot->lot_no }}"
                                                                            data-detail-id="{{ $detail->id }}"
                                                                            data-size-name="{{ $sizeName }}"
                                                                            data-max="{{ $live_remaining_for_size }}"
                                                                            max="{{ $live_remaining_for_size }}"
                                                                            min="0" 
                                                                            placeholder="0">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <hr class="my-4">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0 text-dark">Saved Dead Records <span class="badge badge-secondary">{{ count($saved_dead ?? []) }}</span></h6>
                            <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-dead" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="3%" class="text-center"><input type="checkbox" class="select-all-dead"></th>
                                        <th>LOT NO</th>
                                        <th>Storeroom / Rack</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_dead ?? [] as $sd)
                                        <tr>
                                            <td class="text-center"><input type="checkbox" class="dead-chk" value="{{ $sd->id }}"></td>
                                            <td class="font-weight-bold">{{ $sd->lot_no }}</td>
                                            <td>
                                                {{ $sd->rack->storeroom->name ?? 'N/A' }} / {{ $sd->rack->name ?? 'N/A' }}
                                            </td>
                                            <td><span class="badge badge-light border">{{ $sd->size->size ?? 'Unknown' }}</span></td>
                                            <td>{{ $sd->quantity }}</td>
                                            <td>{{ $sd->remarks ?? '-' }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-outflow" data-id="{{ $sd->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-3">No dead records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-sampling" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="text-primary mb-0"><i class="fas fa-flask"></i> Sampling</h5>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="badge badge-light border text-primary px-2 py-1 font-weight-bold" style="{{ $saved_sampling_total > 0 ? '' : 'display:none;' }} font-size: 0.85rem;">
                                    Saved: <strong id="samplingSavedDisplay">{{ $saved_sampling_total }} Pcs</strong>
                                </span>
                                <span class="text-muted font-weight-bold" style="font-size: 0.88rem;">Total Sampling:</span>
                                <span id="samplingTopTotalBadge" class="badge badge-primary px-3 py-1 font-weight-bold" style="font-size: 0.95rem; border-radius: 6px;">{{ $saved_sampling_total }} Pcs</span>
                            </div>
                        </div>
                        
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body p-3">
                                <h6 class="font-weight-bold mb-3 small text-uppercase text-primary">Quick Add Sampling</h6>
                                <div class="row align-items-end">
                                     <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Design</label>
                                        <select id="samplingDesign" class="form-control form-control-sm select2">
                                            <option value="">Select Design</option>
                                            @foreach($unique_designs as $design)
                                            <option value="{{ $design }}">{{ $design }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Size Set</label>
                                        <select id="samplingSizeSet" class="form-control form-control-sm select2" disabled>
                                            <option value="">Select Size Set</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Color</label>
                                        <select id="samplingColor" class="form-control form-control-sm select2" disabled>
                                            <option value="">Select Color</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Quantity (Sets)</label>
                                        <input type="number" id="samplingQty" class="form-control form-control-sm" min="1" placeholder="e.g. 5" disabled>
                                        <small id="samplingQtyInfo" class="text-muted d-block mt-1" style="font-size: 0.72rem;">Select size set first</small>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Storage Rack</label>
                                        <select id="samplingRack" class="form-control form-control-sm select2">
                                            <option value="">Select Storage</option>
                                            @foreach($storerooms as $store)
                                                <optgroup label="{{ $store->name }}">
                                                    @foreach($store->racks as $rack)
                                                        <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="button" id="btnSaveSamplingDirect" class="btn btn-sm btn-primary w-100 font-weight-bold text-uppercase">
                                            <i class="fas fa-save"></i> Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0 text-primary">Saved Sampling Records <span class="badge badge-secondary">{{ count($saved_sampling ?? []) }}</span></h6>
                            <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-sampling" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
                        </div>
                        <div class="table-responsive bg-white rounded shadow-sm border mb-3" style="max-height: 400px; overflow-y: auto;">
                            <table class="table erp-table table-sm table-bordered text-center align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="3%" class="text-center"><input type="checkbox" class="select-all-sampling-outflow"></th>
                                        <th>LOT NO</th>
                                        <th>Design</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Storeroom / Rack</th>
                                        <th>Qty</th>
                                        <th>Remarks</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_sampling ?? [] as $ss)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="sampling-outflow-chk" value="{{ $ss->id }}"></td>
                                        <td class="font-weight-bold">{{ $ss->lot_no }}</td>
                                        <td>{{ $ss->product->design_number ?? 'N/A' }}</td>
                                        <td><span class="badge badge-secondary">{{ $ss->color->name ?? 'N/A' }}</span></td>
                                        <td><span class="badge badge-light border">{{ $ss->size->size ?? 'N/A' }}</span></td>
                                        <td>
                                            @if($ss->rack)
                                                <span class="badge badge-info">{{ $ss->rack->storeroom->name ?? '' }} / {{ $ss->rack->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $ss->quantity }}</strong></td>
                                        <td>{{ $ss->remarks ?? '-' }}</td>
                                        <td>
                                            <button class="btn btn-xs btn-outline-danger btn-delete-outflow" data-id="{{ $ss->id }}">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-muted py-4">No sampling records saved for this slip yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-debit" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h5 class="text-warning mb-0"><i class="fas fa-minus-circle"></i> Debit</h5>
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <span class="badge badge-light border text-warning px-2 py-1 font-weight-bold" style="{{ $saved_debit_total > 0 ? '' : 'display:none;' }} font-size: 0.85rem;">
                                    Saved: <strong id="debitSavedDisplay">{{ $saved_debit_total }} Pcs</strong>
                                </span>
                                <span class="text-muted font-weight-bold" style="font-size: 0.88rem;">Total Debit Quantity:</span>
                                <span id="debitTopTotalBadge" class="badge badge-warning text-dark px-3 py-1 font-weight-bold" style="font-size: 0.95rem; border-radius: 6px;">{{ $saved_debit_total }} Pcs</span>
                            </div>
                        </div>
                        
                        <form id="debitForm">
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>Stage <span class="text-danger">*</span></label>
                                    <select id="debitStage" class="form-control form-control-sm select2" required>
                                        <option value="">-- Stage --</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Unit <span class="text-danger">*</span></label>
                                    <select id="debitUnit" class="form-control form-control-sm select2" required disabled>
                                        <option value="">-- Unit --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Rack <span class="text-danger">*</span></label>
                                    <select id="debitRack" class="form-control form-control-sm select2" required>
                                        <option value="">-- Select Rack --</option>
                                        @foreach($storerooms as $store)
                                            <optgroup label="{{ $store->name }}">
                                                @foreach($store->racks as $rack)
                                                    <option value="{{ $rack->id }}">{{ $rack->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Global Rate/Pc</label>
                                    <input type="number" id="debitGlobalRate" class="form-control form-control-sm" placeholder="₹" min="0">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" id="btnSaveDebit" class="btn btn-sm btn-warning w-100">
                                        <i class="fas fa-save"></i> Save Debit
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label>Overall Discount</label>
                                    <input type="number" id="debitDiscount" class="form-control form-control-sm" placeholder="₹" min="0" value="0">
                                </div>
                                <div class="col-md-10">
                                    <label>Remarks</label>
                                    <input type="text" id="debitRemarks" class="form-control form-control-sm" placeholder="Optional notes...">
                                </div>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table erp-table table-sm table-bordered">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th>LOT NO</th>
                                            <th>DESIGN / COLOR</th>
                                            <th>SIZE WISE DEBIT (QTY & RATE)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lots_data as $lot)
                                            <tr>
                                                <td class="font-weight-bold">{{ $lot->lot_no }}</td>
                                                <td>
                                                    <div>{{ $lot->design_number }}</div>
                                                    <small class="text-muted">{{ $lot->color_name }}</small>
                                                </td>
                                                <td>
                                                    @if(isset($set_details[$lot->set_id]))
                                                        @php
                                                            $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                                                            $packed_for_lot = isset($packed_by_lot_size[$lot->lot_no]) ? $packed_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $rework_for_lot = isset($rework_by_lot_size[$lot->lot_no]) ? $rework_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $outflow_for_lot = isset($outflow_by_lot_size[$lot->lot_no]) ? $outflow_by_lot_size[$lot->lot_no]->sum('total') : 0;
                                                            $starting_lot_qty = $lot->remaining_quantity + $packed_for_lot + $rework_for_lot + $outflow_for_lot;
                                                        @endphp
                                                        <div class="d-flex flex-column" style="gap: 5px; max-width: 340px;">
                                                            <div class="row no-gutters font-weight-bold text-muted border-bottom pb-1 mb-1" style="font-size: 0.75rem;">
                                                                <div class="col-5">Size</div>
                                                                <div class="col-3 text-center">Qty</div>
                                                                <div class="col-4 text-center">Rate (₹)</div>
                                                            </div>
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $incoming_qty = !empty($lot->incoming_sizes) 
                                                                        ? (int) ($lot->incoming_sizes[$sizeName] ?? 0) 
                                                                        : ($total_set_qty > 0 ? floor($starting_lot_qty * ($detail->total_quantity / $total_set_qty)) : 0);
                                                                    if ($incoming_qty <= 0) continue;

                                                                    $packed_qty = 0;
                                                                    if (isset($packed_by_lot_size[$lot->lot_no])) {
                                                                        $item = $packed_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $packed_qty = $item->total;
                                                                    }
                                                                    $rework_qty = 0;
                                                                    if (isset($rework_by_lot_size[$lot->lot_no])) {
                                                                        $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $rework_qty = $item->total;
                                                                    }
                                                                    $outflow_qty = 0;
                                                                    if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                                                        $item = $outflow_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                                        if ($item) $outflow_qty = $item->total;
                                                                    }
                                                                    $live_remaining_for_size = max(0, $incoming_qty - $packed_qty - $rework_qty - $outflow_qty);

                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $qtyId = "debit-qty-{$lot->transaction_id}-{$cleanSize}";
                                                                    $rateId = "debit-rate-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="row no-gutters align-items-center mb-1" style="font-size: 0.8rem;">
                                                                    <div class="col-5 text-dark font-weight-bold">{{ $sizeName }} <span class="badge badge-light text-muted border py-0 px-1 ml-1" style="font-size: 0.7rem;">Max: {{ $live_remaining_for_size }}</span></div>
                                                                    <div class="col-3 px-1">
                                                                        <input type="number" id="{{ $qtyId }}" class="form-control form-control-sm text-center debit-qty-input border py-0" 
                                                                            style="height: 26px;"
                                                                            data-transaction-id="{{ $lot->transaction_id }}"
                                                                            data-lot-no="{{ $lot->lot_no }}"
                                                                            data-detail-id="{{ $detail->id }}"
                                                                            data-size-name="{{ $sizeName }}"
                                                                            data-max="{{ $live_remaining_for_size }}"
                                                                            max="{{ $live_remaining_for_size }}"
                                                                            min="0" placeholder="0">
                                                                    </div>
                                                                    <div class="col-4 px-1">
                                                                        <div class="input-group input-group-sm">
                                                                            <div class="input-group-prepend" style="height: 26px;"><span class="input-group-text px-1" style="font-size: 0.7rem; border-radius: 4px 0 0 4px; display: flex; align-items: center; justify-content: center;">₹</span></div>
                                                                            <input type="number" id="{{ $rateId }}" class="form-control form-control-sm text-center debit-rate-input border py-0 px-1" 
                                                                                style="height: 26px; border-radius: 0 4px 4px 0;"
                                                                                min="0" placeholder="0">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <hr class="my-4">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0 text-warning">Saved Debit Records <span class="badge badge-secondary">{{ count($saved_debit ?? []) }}</span></h6>
                            <button type="button" class="btn btn-xs btn-danger ml-3 btn-bulk-delete-debit" style="display:none;"><i class="fas fa-trash-alt"></i> Delete Selected (<span class="selected-count">0</span>)</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="3%" class="text-center"><input type="checkbox" class="select-all-debit"></th>
                                        <th>LOT NO</th>
                                        <th>Responsible</th>
                                        <th>Rack</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Rate/Pc</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_debit ?? [] as $sd)
                                        <tr>
                                            <td class="text-center"><input type="checkbox" class="debit-chk" value="{{ $sd->id }}"></td>
                                            <td class="font-weight-bold">{{ $sd->lot_no }}</td>
                                            <td>
                                                <small>{{ $sd->responsibleStage->name ?? 'N/A' }} <br> <b>{{ $sd->responsibleUnit->name ?? 'N/A' }}</b></small>
                                            </td>
                                            <td><small>{{ $sd->rack->name ?? 'N/A' }}</small></td>
                                            <td><span class="badge badge-light border">{{ $sd->size->size ?? 'Unknown' }}</span></td>
                                            <td>{{ $sd->quantity }}</td>
                                            <td>₹{{ number_format($sd->per_piece_amount, 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-outflow" data-id="{{ $sd->id }}">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted py-3">No debit records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Production Slip Modal -->
<div class="modal fade" id="slipModal" tabindex="-1" role="dialog" aria-labelledby="slipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content text-dark">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="slipModalLabel">
                    <i class="fas fa-file-invoice mr-1 text-primary"></i> Production Slip Image
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center bg-light">
                @if(isset($slip) && $slip->slip_file)
                    <div class="img-wrapper p-2 bg-white border rounded shadow-sm d-inline-block">
                        <img src="{{ asset('assets/production_slips/' . $slip->slip_file) }}" class="img-fluid rounded" alt="Production Slip" style="max-height: 75vh; object-fit: contain; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-search-plus mr-1"></i> Click on the image to open in full size
                    </div>
                @else
                    <div class="alert alert-warning my-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i> No production slip file found for this session.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const LOTS_DATA = {!! json_encode($lots_data) !!};
    const SET_DETAILS = {!! json_encode($set_details) !!};
    const PACKED_BY_LOT_SIZE = {!! json_encode($packed_by_lot_size) !!};
    const REWORK_BY_LOT_SIZE = {!! json_encode($rework_by_lot_size) !!};
    const OUTFLOW_BY_LOT_SIZE = {!! json_encode($outflow_by_lot_size) !!};
    const SLIP_ID = "{{ $slip_id }}";

    // Planner Variables
    let expandedLots = [];

    function initAvailableSizes() {
        expandedLots = [];
        LOTS_DATA.forEach(lot => {
            let setId = lot.set_id;
            if (SET_DETAILS[setId]) {
                let totalSetQty = SET_DETAILS[setId].reduce((sum, d) => sum + parseInt(d.total_quantity || 0), 0);
                if (totalSetQty > 0) {
                    // Sum up all packed, rework and outflow for this lot in the current session
                    let totalPackedForLot = 0;
                    if (PACKED_BY_LOT_SIZE[lot.lot_no]) {
                        totalPackedForLot = PACKED_BY_LOT_SIZE[lot.lot_no].reduce((sum, p) => sum + (parseInt(p.total) || 0), 0);
                    }
                    let totalReworkForLot = 0;
                    if (REWORK_BY_LOT_SIZE[lot.lot_no]) {
                        totalReworkForLot = REWORK_BY_LOT_SIZE[lot.lot_no].reduce((sum, r) => sum + (parseInt(r.total) || 0), 0);
                    }
                    let totalOutflowForLot = 0;
                    if (OUTFLOW_BY_LOT_SIZE[lot.lot_no]) {
                        totalOutflowForLot = OUTFLOW_BY_LOT_SIZE[lot.lot_no].reduce((sum, o) => sum + (parseInt(o.total) || 0), 0);
                    }

                    let startingLotQty = parseInt(lot.remaining_quantity || 0) + totalPackedForLot + totalReworkForLot + totalOutflowForLot;

                    SET_DETAILS[setId].forEach(detail => {
                        let sizeName = detail.size.toString().trim().toUpperCase();
                        
                        let hasIncomingSizes = lot.incoming_sizes && Object.keys(lot.incoming_sizes).length > 0;
                        let incomingQty = hasIncomingSizes
                            ? parseInt(lot.incoming_sizes[sizeName] || 0)
                            : Math.floor(startingLotQty * (parseInt(detail.total_quantity || 0) / totalSetQty));

                        if (hasIncomingSizes && incomingQty <= 0) {
                            return; // Do not add non-existent sizes for this lot
                        }

                        // Find packed quantity
                        let packedQty = 0;
                        if (PACKED_BY_LOT_SIZE[lot.lot_no]) {
                            let match = PACKED_BY_LOT_SIZE[lot.lot_no].find(p => p.size && p.size.toString().trim().toUpperCase() === sizeName);
                            if (match) packedQty = parseInt(match.total) || 0;
                        }

                        // Find rework quantity
                        let reworkQty = 0;
                        if (REWORK_BY_LOT_SIZE[lot.lot_no]) {
                            let match = REWORK_BY_LOT_SIZE[lot.lot_no].find(r => r.size && r.size.toString().trim().toUpperCase() === sizeName);
                            if (match) reworkQty = parseInt(match.total) || 0;
                        }

                        // Find outflow quantity
                        let outflowQty = 0;
                        if (OUTFLOW_BY_LOT_SIZE[lot.lot_no]) {
                            let match = OUTFLOW_BY_LOT_SIZE[lot.lot_no].find(o => o.size && o.size.toString().trim().toUpperCase() === sizeName);
                            if (match) outflowQty = parseInt(match.total) || 0;
                        }

                        let pendingForSize = Math.max(0, incomingQty - packedQty - reworkQty - outflowQty);

                        if (pendingForSize >= 0) {
                            expandedLots.push({
                                transaction_id: lot.transaction_id,
                                lot_no: lot.lot_no,
                                color_id: lot.color_id,
                                size: sizeName,
                                size_id: detail.id,
                                remaining_quantity: pendingForSize
                            });
                        }
                    });
                }
            }
        });
        
        // Populate plannerSingleSize select with unique sizes from expandedLots
        let uniqueSizes = [...new Set(expandedLots.map(l => l.size))].sort();
        let $singleSizeSelect = $('#plannerSingleSize');
        if ($singleSizeSelect.length) {
            $singleSizeSelect.html('<option value="">Select Size</option>');
            uniqueSizes.forEach(size => {
                $singleSizeSelect.append(`<option value="${size}">${size}</option>`);
            });
            $singleSizeSelect.trigger('change');
        }
    }

    function updateLiveRemainingUI() {
        // Live tracking display is removed
    }

    $(document).ready(function() {
        // Tab persistence logic
        let activeTab = localStorage.getItem('activePackingTab');
        if (activeTab) {
            $(`#packingTabs a[href="${activeTab}"]`).tab('show');
        }
        $('#packingTabs a').on('shown.bs.tab', function (e) {
            localStorage.setItem('activePackingTab', $(e.target).attr('href'));
        });

        $('.select2').select2({ width: '100%' });
        initAvailableSizes();

        $('#plannerType').change(function() {
            let type = $(this).val();
            if (type === 'Loose') {
                $('#sizeSetCol').hide();
                $('#singleSizeCol').show();
                $('#plannerSizeSet').val('').trigger('change');
                fetchPlannerMasterData();
            } else {
                $('#sizeSetCol').show();
                $('#singleSizeCol').hide();
                $('#plannerSingleSize').val('').trigger('change');
                fetchPlannerMasterData();
            }
        });

        // Initial Saved Totals from Server
        const SAVED_REWORK_TOTAL = {{ $saved_rework_total ?? 0 }};
        const SAVED_DEAD_TOTAL = {{ $saved_dead_total ?? 0 }};
        const SAVED_SAMPLING_TOTAL = {{ $saved_sampling_total ?? 0 }};
        const SAVED_DEBIT_TOTAL = {{ $saved_debit_total ?? 0 }};

        // Live Outflow / Tab Totals Calculation (Defect/Rework, Dead/Damage, Sampling, Debit)
        function updateOutflowTotals() {
            // 1. Rework / Defect Total
            let reworkAdding = 0;
            $('.rework-qty-input').each(function() {
                reworkAdding += parseInt($(this).val()) || 0;
            });
            let reworkTotal = SAVED_REWORK_TOTAL + reworkAdding;
            if (reworkAdding > 0) {
                $('#reworkTopTotalBadge').text(reworkTotal + ' Pcs (' + (SAVED_REWORK_TOTAL > 0 ? SAVED_REWORK_TOTAL + ' Saved + ' : '') + reworkAdding + ' Adding)');
                $('#reworkTabBadge').text(SAVED_REWORK_TOTAL > 0 ? (SAVED_REWORK_TOTAL + ' (+' + reworkAdding + ') Pcs') : (reworkAdding + ' Pcs')).show();
            } else {
                $('#reworkTopTotalBadge').text(SAVED_REWORK_TOTAL + ' Pcs');
                if (SAVED_REWORK_TOTAL > 0) {
                    $('#reworkTabBadge').text(SAVED_REWORK_TOTAL + ' Pcs').show();
                } else {
                    $('#reworkTabBadge').hide();
                }
            }

            // 2. Dead / Damage Total
            let damageAdding = 0;
            $('.damage-qty-input').each(function() {
                damageAdding += parseInt($(this).val()) || 0;
            });
            let damageTotal = SAVED_DEAD_TOTAL + damageAdding;
            if (damageAdding > 0) {
                $('#damageTopTotalBadge').text(damageTotal + ' Pcs (' + (SAVED_DEAD_TOTAL > 0 ? SAVED_DEAD_TOTAL + ' Saved + ' : '') + damageAdding + ' Adding)');
                $('#damageTabBadge').text(SAVED_DEAD_TOTAL > 0 ? (SAVED_DEAD_TOTAL + ' (+' + damageAdding + ') Pcs') : (damageAdding + ' Pcs')).show();
            } else {
                $('#damageTopTotalBadge').text(SAVED_DEAD_TOTAL + ' Pcs');
                if (SAVED_DEAD_TOTAL > 0) {
                    $('#damageTabBadge').text(SAVED_DEAD_TOTAL + ' Pcs').show();
                } else {
                    $('#damageTabBadge').hide();
                }
            }

            // 3. Debit Total
            let debitAdding = 0;
            $('.debit-qty-input').each(function() {
                debitAdding += parseInt($(this).val()) || 0;
            });
            let debitTotal = SAVED_DEBIT_TOTAL + debitAdding;
            if (debitAdding > 0) {
                $('#debitTopTotalBadge').text(debitTotal + ' Pcs (' + (SAVED_DEBIT_TOTAL > 0 ? SAVED_DEBIT_TOTAL + ' Saved + ' : '') + debitAdding + ' Adding)');
                $('#debitTabBadge').text(SAVED_DEBIT_TOTAL > 0 ? (SAVED_DEBIT_TOTAL + ' (+' + debitAdding + ') Pcs') : (debitAdding + ' Pcs')).show();
            } else {
                $('#debitTopTotalBadge').text(SAVED_DEBIT_TOTAL + ' Pcs');
                if (SAVED_DEBIT_TOTAL > 0) {
                    $('#debitTabBadge').text(SAVED_DEBIT_TOTAL + ' Pcs').show();
                } else {
                    $('#debitTabBadge').hide();
                }
            }

            // 4. Sampling Total
            let samplingSets = parseInt($('#samplingQty').val()) || 0;
            let piecesPerSet = 1;
            let requiredSizesStr = $('#samplingSizeSet option:selected').attr('data-sizes');
            if (requiredSizesStr) {
                try {
                    let parsed = JSON.parse(requiredSizesStr);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        piecesPerSet = parsed.length;
                    }
                } catch(e) {}
            }
            let samplingAdding = samplingSets * piecesPerSet;
            let samplingTotal = SAVED_SAMPLING_TOTAL + samplingAdding;
            if (samplingAdding > 0) {
                $('#samplingTopTotalBadge').text(samplingTotal + ' Pcs (' + (SAVED_SAMPLING_TOTAL > 0 ? SAVED_SAMPLING_TOTAL + ' Saved + ' : '') + samplingAdding + ' Adding)');
                $('#samplingTabBadge').text(SAVED_SAMPLING_TOTAL > 0 ? (SAVED_SAMPLING_TOTAL + ' (+' + samplingAdding + ') Pcs') : (samplingAdding + ' Pcs')).show();
            } else {
                $('#samplingTopTotalBadge').text(SAVED_SAMPLING_TOTAL + ' Pcs');
                if (SAVED_SAMPLING_TOTAL > 0) {
                    $('#samplingTabBadge').text(SAVED_SAMPLING_TOTAL + ' Pcs').show();
                } else {
                    $('#samplingTabBadge').hide();
                }
            }
        }

        // Dynamically cap inputs and update live remaining UI & Tab Totals
        $(document).on('input keyup change', '.rework-qty-input, .damage-qty-input, .sampling-qty-input, .debit-qty-input, #samplingQty, #samplingSizeSet', function() {
            let val = parseInt($(this).val()) || 0;
            let transId = $(this).data('transaction-id');
            let sizeName = String($(this).data('size-name')).toUpperCase();
            
            if (transId && sizeName) {
                let lot = expandedLots.find(l => l.transaction_id == transId && l.size === sizeName);
                if (lot) {
                    if (val > lot.remaining_quantity) {
                        $(this).val(lot.remaining_quantity);
                    }
                    if (val < 0) {
                        $(this).val(0);
                    }
                }
            }
            updateLiveRemainingUI();
            updateOutflowTotals();
        });

        // Initialize totals on load
        updateOutflowTotals();

        // ---------------- REWORK TAB LOGIC ----------------

        // Fetch Rework Stages
        // Fetch Rework Stages (Shared for Rework and Debit)
        $.get("{{ route('admin.packing.reworkStages') }}", function(res) {
            if(res.status === 'success') {
                let options = '<option value="">-- Stage --</option>';
                res.stages.forEach(s => {
                    options += `<option value="${s.id}">${s.name}</option>`;
                });
                $('#reworkStage').html(options).trigger('change');
                $('#debitStage').html(options).trigger('change');
            }
        });

        // Fetch Units on Stage Change
        $('#reworkStage').change(function() {
            let stageId = $(this).val();
            let $unit = $('#reworkUnit');
            $unit.html('<option value="">-- Select Unit --</option>');
            if(!stageId) return;
            $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function(res) {
                if(res.status === 'success') {
                    res.units.forEach(u => {
                        $unit.append(`<option value="${u.id}">${u.name}</option>`);
                    });
                }
            });
        });

        // Save Rework
        $('#btnSaveRework').click(function() {
            let to_stage_id = $('#reworkStage').val();
            let to_unit_id = $('#reworkUnit').val();
            let remarks = $('#reworkRemarks').val();
            
            if(!to_stage_id || !to_unit_id) {
                alert('Please select Stage and Unit');
                return;
            }

            let items = [];
            $('.rework-qty-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                if (val > 0) {
                    items.push({
                        detail_id: $(this).data('detail-id'),
                        lot_no: $(this).data('lot-no'),
                        qty: val
                    });
                }
            });

            if(items.length === 0) {
                alert('Please enter defect quantity for at least one size.');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.reassignRework') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    to_stage_id: to_stage_id,
                    to_unit_id: to_unit_id,
                    remarks: remarks,
                    items: items
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Defect/Rework successfully reassigned.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Rework');
                    }
                },
                error: function() {
                    alert('Error saving rework');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Rework');
                }
            });
        });

        // Delete Rework
        $(document).on('click', '.btn-delete-rework', function() {
            if(!confirm('Are you sure you want to delete this rework record?')) return;
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.packing.deleteRework', '') }}/" + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                    }
                },
                error: function() {
                    alert('Error deleting rework');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                }
            });
        });

        // ---------------- DEAD / DAMAGE TAB LOGIC ----------------
        $('#btnSaveDamage').click(function() {
            let rack_id = $('#damageRack').val();
            let remarks = $('#damageRemarks').val();
            
            if(!rack_id) {
                alert('Please select a Storeroom / Rack');
                return;
            }

            let items = [];
            $('.damage-qty-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                if (val > 0) {
                    items.push({
                        detail_id: $(this).data('detail-id'),
                        lot_no: $(this).data('lot-no'),
                        qty: val
                    });
                }
            });

            if(items.length === 0) {
                alert('Please enter damage quantity for at least one size.');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.recordDeadStock') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    rack_id: rack_id,
                    remarks: remarks,
                    items: items
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Dead/Damage stock successfully saved.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Dead Stock');
                    }
                },
                error: function() {
                    alert('Error saving dead stock');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Dead Stock');
                }
            });
        });

        // Delete Dead Stock Outflow
        $(document).on('click', '.btn-delete-outflow', function() {
            if(!confirm('Are you sure you want to delete this dead record?')) return;
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.packing.deleteOutflow', '') }}/" + id,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                    }
                },
                error: function() {
                    alert('Error deleting dead stock record');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                }
            });
        });

        // ---------------- SAMPLING TAB LOGIC ----------------
        $('#btnSaveSampling').click(function() {
            let rack_id = $('#samplingRack').val();
            let remarks = $('#samplingRemarks').val();
            
            if(!rack_id) {
                alert('Please select a Storeroom / Rack');
                return;
            }

            let items = [];
            $('.sampling-qty-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                if (val > 0) {
                    items.push({
                        detail_id: $(this).data('detail-id'),
                        lot_no: $(this).data('lot-no'),
                        qty: val
                    });
                }
            });

            if(items.length === 0) {
                alert('Please enter sampling quantity for at least one size.');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.recordSamplingStock') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    rack_id: rack_id,
                    remarks: remarks,
                    items: items
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Sampling stock successfully saved.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Sampling Stock');
                    }
                },
                error: function() {
                    alert('Error saving sampling stock');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Sampling Stock');
                }
            });
        });

        // ---------------- DEBIT TAB LOGIC ----------------
        
        // Fetch Stages and Units (Stage fetched alongside Rework above)
        $('#debitStage').change(function() {
            let stageId = $(this).val();
            if(!stageId) {
                $('#debitUnit').html('<option value="">-- Unit --</option>').prop('disabled', true).trigger('change');
                return;
            }
            $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function(res) {
                if(res.status === 'success') {
                    let options = '<option value="">-- Unit --</option>';
                    res.units.forEach(function(un) {
                        options += `<option value="${un.id}">${un.name}</option>`;
                    });
                    $('#debitUnit').html(options).prop('disabled', false).trigger('change');
                }
            });
        });



        // Global Rate apply
        $('#debitGlobalRate').on('input', function() {
            let rate = $(this).val();
            $('.debit-rate-input').val(rate);
        });

        $('#btnSaveDebit').click(function() {
            let stage_id = $('#debitStage').val();
            let unit_id = $('#debitUnit').val();
            let rack_id = $('#debitRack').val();
            let remarks = $('#debitRemarks').val();
            let discount = parseFloat($('#debitDiscount').val()) || 0;
            
            if(!stage_id || !unit_id || !rack_id) {
                alert('Please select Stage, Unit, and Rack.');
                return;
            }

            let items = [];
            let totalAmount = 0;
            
            $('.debit-qty-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                if (val > 0) {
                    let rate = parseFloat($(this).closest('.row').find('.debit-rate-input').val()) || 0;
                    
                    items.push({
                        detail_id: $(this).data('detail-id'),
                        lot_no: $(this).data('lot-no'),
                        qty: val,
                        per_piece_amount: rate
                    });
                    
                    totalAmount += (val * rate);
                }
            });

            if(items.length === 0) {
                alert('Please enter debit quantity for at least one size.');
                return;
            }

            let finalAmount = totalAmount - discount;
            if (finalAmount < 0) finalAmount = 0;

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.recordUnitDebit') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    stage_id: stage_id,
                    unit_id: unit_id,
                    rack_id: rack_id,
                    remarks: remarks,
                    discount: discount,
                    total_amount: finalAmount,
                    items: items
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Debit record successfully saved.');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Debit');
                    }
                },
                error: function() {
                    alert('Error saving debit');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Debit');
                }
            });
        });

        function fetchPlannerMasterData() {
            let design = $('#plannerDesign').val();
            let type = $('#plannerType').val();
            let sizeSetId = $('#plannerSizeSet').val();
            
            if (type === 'Loose') {
                let singleSize = $('#plannerSingleSize').val();
                sizeSetId = singleSize ? 'loose_' + singleSize : '';
            }
            
            if (!design) return;
            if (type !== 'Loose' && !sizeSetId) return;

            $.ajax({
                url: "{{ route('admin.packing.apiGetMasterData', $slip_id) }}",
                data: { design_number: design, size_set_id: sizeSetId },
                success: function(response) {
                    if (response.status === 'success') {
                        if (response.mrp && parseFloat(response.mrp) > 0) {
                            $('#plannerMrp').val(response.mrp);
                        } else {
                            $('#plannerMrp').val('');
                        }
                        
                        if (response.price && parseFloat(response.price) > 0) {
                            $('#plannerPrice').val(response.price);
                        } else {
                            $('#plannerPrice').val('');
                        }
                        
                        let $colorSelect = $('#plannerColor');
                        $colorSelect.html('<option value="">Select Color</option>').trigger('change');
                        if (response.colors && response.colors.length > 0) {
                            response.colors.forEach(function(c) {
                                $colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                        }
                        $colorSelect.trigger('change');
                    }
                }
            });
        }

        // 1. When Design changes, fetch valid Size Sets
        $('#plannerDesign').change(function() {
            let design = $(this).val();
            let $sizeSetSelect = $('#plannerSizeSet');
            $sizeSetSelect.html('<option value="">Select Size Set</option>').prop('disabled', true).trigger('change');
            $('#plannerMrp').val('');
            $('#plannerPrice').val('');
            
            if (!design) return;

            // Fetch size sets for this design via API
            $.ajax({
                url: "{{ route('admin.packing.apiGetSizeSets', $slip_id) }}",
                data: { design_number: design, for_planner: 1 },
                success: function(response) {
                    if (response.status === 'success' && response.size_sets) {
                        response.size_sets.forEach(set => {
                            let sizesJson = JSON.stringify(set.required_sizes).replace(/"/g, '&quot;');
                            $sizeSetSelect.append(`<option value="${set.id}" data-sizes="${sizesJson}">${set.name} (${set.no_of_pcs} pcs)</option>`);
                        });
                        $sizeSetSelect.prop('disabled', false).trigger('change');
                    }
                }
            });

            if ($('#plannerType').val() === 'Loose') {
                fetchPlannerMasterData();
            }
        });

        // 2. When Size Set changes, fetch MRP/Price
        $('#plannerSizeSet').change(function() {
            fetchPlannerMasterData();
        });

        // When Single Size changes (for Loose type), fetch MRP/Price/Colors
        $('#plannerSingleSize').change(function() {
            fetchPlannerMasterData();
        });
        
        // Storage Rack logic
        $('#plannerWarehouse').change(function() {
            let warehouseId = $(this).val();
            let $rackSelect = $('#plannerRack');
            $rackSelect.html('<option value="">Select Rack</option>').trigger('change');
            
            if (!warehouseId) return;
            
            $.ajax({
                url: "{{ route('admin.inventory.warehouse_stock.racks', '') }}/" + warehouseId,
                type: 'GET',
                success: function(data) {
                    if (data && data.length > 0) {
                        data.forEach(function(rack) {
                            $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                        });
                    }
                    $rackSelect.trigger('change');
                }
            });
        });
        // 3. Add Range logic
        $('#btnSavePlanDirect').click(function() {
            let start = parseInt($('#plannerStart').val());
            let end = parseInt($('#plannerEnd').val());
            let design = $('#plannerDesign').val();
            let type = $('#plannerType').val();
            let sizeSetId = $('#plannerSizeSet').val();
            let sizeSetName = $('#plannerSizeSet option:selected').text();
            let singleSize = $('#plannerSingleSize').val();
            let colorId = $('#plannerColor').val();
            let colorName = $('#plannerColor option:selected').text();
            let qty = parseInt($('#plannerQty').val());
            let mrp = parseFloat($('#plannerMrp').val()) || 0;
            let price = parseFloat($('#plannerPrice').val()) || 0;
            let warehouseId = $('#plannerWarehouse').val();
            let warehouseName = $('#plannerWarehouse option:selected').text();
            let rackId = $('#plannerRack').val();
            let barcode = $('#plannerBarcode').val();
            
            if (type === 'Loose') {
                if (!start || !end || !design || !singleSize || !warehouseId || !qty) {
                    alert('Please fill all required fields (Start, End, Design, Size, Qty, Warehouse).');
                    return;
                }
            } else {
                if (!start || !end || !design || !sizeSetId || !warehouseId || !qty) {
                    alert('Please fill all required fields (Start, End, Design, Size Set, Qty, Warehouse).');
                    return;
                }
            }

            if (start > end) {
                alert('Start carton cannot be greater than end carton.');
                return;
            }

            let requiredDetails = [];
            if (type === 'Loose') {
                sizeSetId = 'loose_' + singleSize;
                sizeSetName = 'Loose (' + singleSize + ')';
                requiredDetails = [{ size: singleSize, total_quantity: 1 }];
            } else {
                // Figure out the required sizes for 1 carton (qty * set required sizes)
                let $selectedOption = $('#plannerSizeSet option:selected');
                let requiredSizesJson = $selectedOption.attr('data-sizes');
                
                if (!requiredSizesJson) {
                    alert('Invalid size set data.');
                    return;
                }
                
                let requiredSizesArray = [];
                try {
                    requiredSizesArray = JSON.parse(requiredSizesJson.replace(/&quot;/g, '"'));
                } catch (e) {
                    alert('Failed to parse sizes.');
                    return;
                }
                
                requiredDetails = requiredSizesArray.map(size => {
                    return { size: size, total_quantity: 1 };
                });
            }

            // Total sets per carton is 'qty'
            let setsPerCarton = qty;
            
            let tempLots = JSON.parse(JSON.stringify(expandedLots));
            let rangeSucceeded = true;
            
            let planToSubmit = [];

            for (let i = start; i <= end; i++) {
                // Determine sizes needed for THIS carton
                let cartonItems = [];
                let sizesFulfilled = true;

                requiredDetails.forEach(detail => {
                    let neededPcs = detail.total_quantity * setsPerCarton; // e.g. 1 S per set * 1 set = 1 pc
                    let sizeName = detail.size.toString().trim().toUpperCase();
                    
                    // Filter matching lots with remaining stock in simulated temp memory
                    let matchingLots = tempLots.filter(l => l.size === sizeName && l.color_id == colorId && l.remaining_quantity > 0);
                    if (matchingLots.length === 0) {
                        matchingLots = tempLots.filter(l => l.size === sizeName && l.remaining_quantity > 0);
                    }

                    let totalAvailable = matchingLots.reduce((sum, l) => sum + l.remaining_quantity, 0);
                    if (totalAvailable < neededPcs) {
                        sizesFulfilled = false;
                    } else {
                        let remainingToDeduct = neededPcs;
                        for (let lot of matchingLots) {
                            if (remainingToDeduct <= 0) break;
                            
                            let deduct = Math.min(lot.remaining_quantity, remainingToDeduct);
                            
                            // Deduct from simulated lot copy
                            lot.remaining_quantity -= deduct;
                            remainingToDeduct -= deduct;

                            cartonItems.push({
                                size_id: lot.size_id,
                                size_name: sizeName,
                                quantity: deduct,
                                transaction_id: lot.transaction_id,
                                lot_no: lot.lot_no
                            });
                        }
                    }
                });

                if (!sizesFulfilled) {
                    alert(`Not enough pending quantity for Carton ${i}. Stopping range addition.`);
                    rangeSucceeded = false;
                    break;
                }

                planToSubmit.push({
                    carton_no: i,
                    design: design,
                    size_set_id: sizeSetId,
                    size_set_name: sizeSetName,
                    color_id: colorId,
                    color_name: colorName,
                    qty: qty,
                    mrp: mrp,
                    price: price,
                    warehouse_id: warehouseId,
                    warehouse_name: warehouseName,
                    rack_id: rackId,
                    barcode: barcode,
                    items: cartonItems // Sizes required for this carton
                });
            }

            if (rangeSucceeded) {
                if (planToSubmit.length === 0) {
                    alert('No cartons planned.');
                    return;
                }

                let $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

                $.ajax({
                    url: "{{ route('admin.packing.apiSaveCartonPlan', $slip_id) }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cartons: planToSubmit
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Plan');
                        }
                    },
                    error: function(err) {
                        alert('An error occurred while saving.');
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Plan');
                    }
                });
            }
        });

        $(document).on('click', '.btn-delete-carton', function() {
            if (!confirm('Are you sure you want to delete this carton?')) return;
            
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.packing.apiDeleteCarton', ['slip_id' => $slip_id, 'carton_id' => 'PLACEHOLDER']) }}".replace('PLACEHOLDER', id),
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(res.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                    }
                },
                error: function() {
                    alert('Error deleting carton');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                }
            });
        });

        // ---------------- DOMESTIC DIVERSION LOGIC ---------------- //

        // 1. Design change -> set product id, fetch size sets
        $('#domesticDesign').change(function() {
            let productId = $(this).find('option:selected').data('product-id');
            $(this).data('product-id', productId);
            
            let design = $(this).val();
            let $sizeSet = $('#domesticSizeSet');
            let $color = $('#domesticColor');
            
            $sizeSet.html('<option value="">Select Size Set</option>').prop('disabled', true).trigger('change');
            $color.html('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
            
            if (design) {
                $.ajax({
                    url: "{{ route('admin.packing.apiGetSizeSets', $slip_id) }}",
                    data: { design_number: design, for_sampling: 0, for_planner: 0 },
                    success: function(res) {
                        if (res.status === 'success' && res.size_sets) {
                            res.size_sets.forEach(set => {
                                let sizesJson = '';
                                if(set.required_sizes && set.required_sizes.length) {
                                    sizesJson = set.required_sizes.join(',');
                                }
                                $sizeSet.append(`<option value="${set.id}" data-sizes="${sizesJson}">${set.name} (${set.no_of_pcs} pcs)</option>`);
                            });
                            $sizeSet.prop('disabled', false).trigger('change');
                        }
                    }
                });
            }
            updateDomesticMaxQty();
        });

        // 2. Size Set change -> fetch colors
        $('#domesticSizeSet').change(function() {
            let design = $('#domesticDesign').val();
            let sizeSetId = $(this).val();
            let $color = $('#domesticColor');
            
            $color.html('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
            
            if (design && sizeSetId) {
                $.ajax({
                    url: "{{ route('admin.packing.apiGetMasterData', $slip_id) }}",
                    data: { design_number: design, size_set_id: sizeSetId, strict_colors: 1 },
                    success: function(res) {
                        if (res.status === 'success' && res.colors) {
                            res.colors.forEach(c => {
                                $color.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            $color.prop('disabled', false).trigger('change');
                        }
                    }
                });
            }
            updateDomesticMaxQty();
        });

        $('#domesticColor').change(function() {
            updateDomesticMaxQty();
        });

        function updateDomesticMaxQty() {
            let design = $('#domesticDesign').val();
            let sizeSetId = $('#domesticSizeSet').val();
            let colorId = $('#domesticColor').val();
            let $qty = $('#domesticQty');
            let $qtyInfo = $('#domesticQtyInfo');

            let queuedQty = 0;

            $qty.val('').prop('disabled', true).removeAttr('max').removeClass('is-invalid is-valid');
            $qtyInfo.text('Select Design, Size Set, and Color first').removeClass('text-success text-danger text-warning').addClass('text-muted');

            if (!design || !sizeSetId || !colorId) return;

            let requiredSizesStr = $('#domesticSizeSet option:selected').attr('data-sizes');
            if (!requiredSizesStr) {
                $qtyInfo.text('No size configuration found').addClass('text-danger');
                return;
            }
            let requiredSizesArray = requiredSizesStr.split(',').map(s => s.trim().toUpperCase());

            // Count occurrences of each size in the set
            let sizeCounts = {};
            requiredSizesArray.forEach(size => {
                sizeCounts[size] = (sizeCounts[size] || 0) + 1;
            });

            // Calculate max sets based on expandedLots (Remaining Live stock)
            let maxSets = null;
            let sizeAvailabilityDetails = [];

            for (let sizeName in sizeCounts) {
                let requiredPerSet = sizeCounts[sizeName];
                // Sum remaining_quantity for this size in expandedLots (matching planner fallback to any color/lot)
                let availableForSize = expandedLots
                    .filter(l => l.size === sizeName)
                    .reduce((sum, l) => sum + l.remaining_quantity, 0);

                let setsPossible = Math.floor(availableForSize / requiredPerSet);
                if (maxSets === null || setsPossible < maxSets) {
                    maxSets = setsPossible;
                }
                sizeAvailabilityDetails.push(`${sizeName}: ${availableForSize} pcs`);
            }

            maxSets = Math.max(0, (maxSets || 0) - queuedQty);

            $qty.attr('max', maxSets).prop('disabled', maxSets <= 0);
            if (maxSets > 0) {
                $qtyInfo.text(`Available: ${maxSets} sets (${sizeAvailabilityDetails.join(', ')})`)
                    .removeClass('text-danger text-muted text-warning').addClass('text-success');
            } else {
                $qtyInfo.text(`No complete sets available (${sizeAvailabilityDetails.join(', ')})`)
                    .removeClass('text-success text-muted text-warning').addClass('text-danger');
            }
        }

        // 2. Size Set change
        $('#domesticSizeSet').change(function() {
            updateDomesticMaxQty();
        });

        // Trigger max qty update on color selection change
        $('#domesticColor').change(updateDomesticMaxQty);

        // Real-time cap on qty input
        $(document).on('input', '#domesticQty', function() {
            let val = parseInt($(this).val()) || 0;
            let max = parseInt($(this).attr('max')) || 0;
            let $info = $('#domesticQtyInfo');

            if (max <= 0) return;

            if (val > max) {
                $(this).val(max);
                val = max;
            }

            let remaining = max - val;
            let pct = (val / max) * 100;

            if (val <= 0) {
                $info.text(`Available: ${max} sets`).removeClass('text-danger text-warning').addClass('text-success');
            } else if (pct >= 100) {
                $info.text(`⚠ Max reached: ${max} sets`).removeClass('text-success text-warning').addClass('text-danger');
            } else if (pct >= 75) {
                $info.text(`${remaining} sets remaining`).removeClass('text-success text-danger').addClass('text-warning');
            } else {
                $info.text(`${remaining} sets remaining`).removeClass('text-danger text-warning').addClass('text-success');
            }
        });

        // Save Directly
        $('#btnSaveDomesticDirect').click(function() {
            let design = $('#domesticDesign').val();
            let sizeSetId = $('#domesticSizeSet').val();
            let sizeSetName = $('#domesticSizeSet option:selected').text();
            let colorId = $('#domesticColor').val();
            let colorName = $('#domesticColor option:selected').text();
            let qty = parseInt($('#domesticQty').val());
            let rackId = $('#domesticRack').val();
            let rackName = $('#domesticRack option:selected').text();
            let productId = $('#domesticDesign').data('product-id');

            if (!design || !sizeSetId || !colorId || !qty || qty < 1) {
                alert('Please select Design, Size Set, Color, and enter a valid Quantity.');
                return;
            }

            if (!productId) {
                alert('Could not find product for the selected Design + Size Set. Please try again.');
                return;
            }

            let requiredSizesStr = $('#domesticSizeSet option:selected').attr('data-sizes');
            if (!requiredSizesStr) {
                alert('No size configuration found for this size set.');
                return;
            }
            let requiredSizesArray = requiredSizesStr.split(',').map(s => s.trim().toUpperCase());

            // Prepare list of size name counts in this size set
            let sizeCounts = {};
            requiredSizesArray.forEach(size => {
                sizeCounts[size] = (sizeCounts[size] || 0) + 1;
            });

            // We need to check if sufficient remaining live stock is available in expandedLots
            let tempLots = JSON.parse(JSON.stringify(expandedLots));
            let deductedItems = [];
            let stockAvailable = true;

            for (let sizeName in sizeCounts) {
                let neededPcs = sizeCounts[sizeName] * qty;
                let remNeeded = neededPcs;

                // Match exact color first
                let matchedLots = tempLots.filter(l => l.size === sizeName && l.color_id == colorId && l.remaining_quantity > 0);
                // Fallback to any lot if needed
                if (matchedLots.length === 0) {
                    matchedLots = tempLots.filter(l => l.size === sizeName && l.remaining_quantity > 0);
                }

                for (let j = 0; j < matchedLots.length; j++) {
                    if (remNeeded <= 0) break;
                    let lot = matchedLots[j];
                    let deduct = Math.min(lot.remaining_quantity, remNeeded);
                    lot.remaining_quantity -= deduct;
                    remNeeded -= deduct;
                    deductedItems.push({
                        transaction_id: lot.transaction_id,
                        size_name: sizeName,
                        quantity: deduct
                    });
                }

                if (remNeeded > 0) {
                    stockAvailable = false;
                    alert(`Not enough stock for size: ${sizeName}. Missing ${remNeeded} pcs.`);
                    break;
                }
            }

            if (!stockAvailable) {
                return;
            }

            let singleItemQueue = [{
                design_number: design,
                size_set_id: sizeSetId,
                size_set_name: sizeSetName,
                color_id: colorId,
                color_name: colorName,
                quantity: qty,
                rack_id: rackId,
                rack_name: rackId ? rackName : '',
                product_id: productId,
                items: deductedItems
            }];

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.saveDomesticBulk') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    boxes: singleItemQueue
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Domestic diversion saved successfully.');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
                    }
                },
                error: function(err) {
                    alert('An error occurred while submitting.');
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save');
                }
            });
        });

        // Delete Domestic Box/Diversion
        $(document).on('click', '.btn-delete-domestic', function() {
            if (!confirm('Are you sure you want to delete this domestic entry? This will restore all quantities back to stock.')) return;
            
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.packing.deleteDomesticBox', 'PLACEHOLDER') }}".replace('PLACEHOLDER', id),
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        alert(res.message || 'Deleted successfully.');
                        window.location.reload();
                    } else {
                        alert(res.message || 'Failed to delete domestic entry.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Delete');
                    }
                },
                error: function(xhr) {
                    let msg = 'Error deleting domestic entry.';
                    try { msg = xhr.responseJSON.message || msg; } catch(e){}
                    alert(msg);
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Delete');
                }
            });
        });

        // ---------------- SAMPLING DIVERSION LOGIC ---------------- //

        $('#samplingDesign').change(function() {
            let design = $(this).val();
            let $sizeSet = $('#samplingSizeSet');
            let $color = $('#samplingColor');
            $sizeSet.html('<option value="">Select Size Set</option>').prop('disabled', true).trigger('change');
            $color.html('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
            $('#samplingDesign').data('product-id', '');
            $('#samplingDesign').data('max-sets', 0);
            $('#samplingQty').val('').prop('disabled', true).removeAttr('max');
            $('#samplingQtyInfo').text('Select size set first').removeClass('text-success text-danger text-warning').addClass('text-muted');

            if (!design) return;

            $.ajax({
                url: "{{ route('admin.packing.apiGetSizeSets', $slip_id) }}",
                data: { design_number: design, for_sampling: 1 },
                success: function(res) {
                    if (res.status === 'success' && res.size_sets) {
                        res.size_sets.forEach(set => {
                            let sizesJson = JSON.stringify(set.required_sizes).replace(/"/g, '&quot;');
                            $sizeSet.append(`<option value="${set.id}" data-sizes="${sizesJson}">${set.name} (${set.no_of_pcs} pcs)</option>`);
                        });
                        $sizeSet.prop('disabled', false).trigger('change');
                    }
                }
            });
        });

        $('#samplingSizeSet').change(function() {
            let design = $('#samplingDesign').val();
            let sizeSetId = $(this).val();
            let $color = $('#samplingColor');
            $color.html('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
            $('#samplingDesign').data('product-id', '');
            updateSamplingMaxQty();

            if (!design || !sizeSetId) return;

            $.ajax({
                url: "{{ route('admin.packing.apiGetMasterData', $slip_id) }}",
                data: { design_number: design, size_set_id: sizeSetId, for_sampling: 1 },
                success: function(res) {
                    if (res.status === 'success') {
                        if (res.colors) {
                            res.colors.forEach(c => {
                                $color.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            $color.prop('disabled', false).trigger('change');
                        }
                        $('#samplingDesign').data('product-id', res.product_id);
                        
                        let minSets = 999999;
                        if (res.available_balances) {
                            let requiredSizesStr = $('#samplingSizeSet option:selected').attr('data-sizes');
                            let requiredSizes = [];
                            try { requiredSizes = JSON.parse(requiredSizesStr); } catch(e) {}
                            
                            requiredSizes.forEach(sz => {
                                let bal = res.available_balances[sz] || 0;
                                minSets = Math.min(minSets, bal);
                            });
                        }
                        if (minSets === 999999) minSets = 0;
                        $('#samplingDesign').data('max-sets', minSets);
                        
                        updateSamplingMaxQty();
                    }
                }
            });
        });

        $('#samplingColor').change(updateSamplingMaxQty);

        function updateSamplingMaxQty() {
            let design = $('#samplingDesign').val();
            let sizeSetId = $('#samplingSizeSet').val();
            let colorId = $('#samplingColor').val();
            let $qty = $('#samplingQty');
            let $qtyInfo = $('#samplingQtyInfo');

            $qty.val('').prop('disabled', true).removeAttr('max').removeClass('is-invalid is-valid');
            $qtyInfo.text('Select Design, Size Set, and Color first').removeClass('text-success text-danger text-warning').addClass('text-muted');

            if (!design || !sizeSetId || !colorId) return;

            let maxSets = parseInt($('#samplingDesign').data('max-sets')) || 0;
            let remainingSets = Math.max(0, maxSets);

            $qty.attr('max', remainingSets).prop('disabled', remainingSets <= 0);
            if (remainingSets > 0) {
                $qtyInfo.text(`Available: ${remainingSets} sets`).removeClass('text-danger text-warning text-muted').addClass('text-success');
            } else {
                $qtyInfo.text('No complete sets available').removeClass('text-success text-warning text-muted').addClass('text-danger');
            }
        }

        $('#btnSaveSamplingDirect').click(function() {
            let design = $('#samplingDesign').val();
            let sizeSetId = $('#samplingSizeSet').val();
            let sizeSetName = $('#samplingSizeSet option:selected').text();
            let colorId = $('#samplingColor').val();
            let colorName = $('#samplingColor option:selected').text();
            let qty = parseInt($('#samplingQty').val());
            let rackId = $('#samplingRack').val();
            let rackName = $('#samplingRack option:selected').text();
            let productId = $('#samplingDesign').data('product-id');

            if (!design || !sizeSetId || !colorId || !qty || qty <= 0) {
                alert('Please select Design, Size Set, Color and enter Qty.');
                return;
            }

            let maxSets = parseInt($('#samplingQty').attr('max')) || 0;
            if (qty > maxSets) {
                alert(`Cannot add. Only ${maxSets} sets remaining.`);
                return;
            }

            let singleSamplingItem = [{
                product_id: productId,
                design_number: design,
                size_set_id: sizeSetId,
                size_set_name: sizeSetName,
                color_id: colorId,
                color_name: colorName,
                quantity: qty,
                rack_id: rackId,
                rack_name: rackName
            }];

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.packing.saveSamplingBulk') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    order_id: "{{ $order->id ?? '' }}",
                    slip_id: "{{ $slip_id }}",
                    boxes: singleSamplingItem
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Sampling saved successfully.');
                        localStorage.setItem('activePackingTab', '#tab-sampling');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        alert(res.message || 'Failed to save sampling.');
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
                    }
                },
                error: function() {
                    alert('Error saving sampling.');
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
                }
            });
        });

        // Back to Selection Guard
        window.handleBackToSelection = function() {
            if (confirm('Lots are already locked in this session. To select different lots, please use "Reset Slip" to clear and start fresh. Do you want to reset this slip now?')) {
                $('#btnResetSlip').click();
            }
        };

        // Reset Slip Functionality
        $('#btnResetSlip').click(function() {
            if (!confirm('Are you absolutely sure you want to reset this slip? This will delete all cartons, domestic boxes, outflows, reworks, and completely restore all stock and order balances.')) {
                return;
            }
            
            let $btn = $(this);
            let originalHtml = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Resetting...').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('admin.packing.reset_slip', $slip_id) }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.href = res.redirect_url || "{{ route('admin.packing.processNew', $slip_id) }}";
                    } else {
                        toastr.error(res.message || 'Failed to reset slip.');
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to reset slip.');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Finalize Packing Functionality
        $('#btnFinalizePacking').click(function() {
            if (!confirm('Are you sure you want to finalize this packing session? This will lock all cartons, boxes, and update the slip stage status.')) {
                return;
            }
            
            let $btn = $(this);
            let originalHtml = $btn.html();
            $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Finalizing...').prop('disabled', true);
            
            $.ajax({
                url: "{{ route('admin.packing.finalize') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    packing_main_id: "{{ $packing->id }}",
                    completion_date: new Date().toISOString().split('T')[0]
                },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message || 'Packing session successfully finalized.');
                        let viewUrl = res.redirect_url || "{{ route('admin.packing.view', $packing->id) }}";
                        setTimeout(function() {
                            window.location.href = viewUrl;
                        }, 400);
                    } else {
                        toastr.error(res.message || 'Failed to finalize packing.');
                        $btn.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to finalize packing.');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            });
        });

        // Generic helper function to manage checkbox state and show/hide Delete Selected buttons
        function setupBulkDelete(selectAllClass, chkClass, deleteBtnClass) {
            $(document).on('change', selectAllClass, function() {
                let isChecked = $(this).is(':checked');
                $(chkClass).prop('checked', isChecked).trigger('change');
            });

            $(document).on('change', chkClass, function() {
                let totalSelected = $(chkClass + ':checked').length;
                let $btn = $(deleteBtnClass);
                
                if (totalSelected > 0) {
                    $btn.find('.selected-count').text(totalSelected);
                    $btn.fadeIn(150);
                } else {
                    $btn.fadeOut(150);
                }
                
                // Update select-all check state
                let totalCheckboxes = $(chkClass).length;
                $(selectAllClass).prop('checked', totalSelected === totalCheckboxes && totalCheckboxes > 0);
            });
        }

        // Setup for all 5 listings
        setupBulkDelete('.select-all-cartons', '.carton-chk', '.btn-bulk-delete-cartons');
        setupBulkDelete('.select-all-rework', '.rework-chk', '.btn-bulk-delete-rework');
        setupBulkDelete('.select-all-dead', '.dead-chk', '.btn-bulk-delete-dead');
        setupBulkDelete('.select-all-debit', '.debit-chk', '.btn-bulk-delete-debit');
        setupBulkDelete('.select-all-domestic', '.domestic-chk', '.btn-bulk-delete-domestic');
        setupBulkDelete('.select-all-sampling-outflow', '.sampling-outflow-chk', '.btn-bulk-delete-sampling');

        // Handle Cartons bulk delete
        $(document).on('click', '.btn-bulk-delete-cartons', function() {
            let ids = $('.carton-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected cartons?`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteCartons', $slip_id) }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected cartons.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

        // Handle Rework bulk delete
        $(document).on('click', '.btn-bulk-delete-rework', function() {
            let ids = $('.rework-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected rework records?`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteRework') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected rework records.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

        // Handle Dead Stock bulk delete
        $(document).on('click', '.btn-bulk-delete-dead', function() {
            let ids = $('.dead-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected dead stock records?`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteOutflow') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected dead stock records.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

        // Handle Debit bulk delete
        $(document).on('click', '.btn-bulk-delete-debit', function() {
            let ids = $('.debit-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected debit records?`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteOutflow') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected debit records.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

        // Handle Domestic bulk delete
        $(document).on('click', '.btn-bulk-delete-domestic', function() {
            let ids = $('.domestic-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected domestic entries? This will restore stock.`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteDomestic') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected domestic entries.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

        // Handle Sampling bulk delete
        $(document).on('click', '.btn-bulk-delete-sampling', function() {
            let ids = $('.sampling-outflow-chk:checked').map(function() { return $(this).val(); }).get();
            if (!confirm(`Are you sure you want to delete ${ids.length} selected sampling records?`)) return;
            
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
            
            $.ajax({
                url: "{{ route('admin.packing.bulkDeleteOutflow') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', ids: ids },
                success: function(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        window.location.reload();
                    } else {
                        toastr.error(res.message || 'Failed to delete selected sampling records.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                    }
                },
                error: function() {
                    toastr.error('Error occurred during bulk deletion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt mr-1"></i> Delete Selected');
                }
            });
        });

    });
</script>

<style>
    .nav-tabs .nav-link {
        color: #495057;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        background-color: #f8f9fa;
        color: var(--erp-primary);
    }
    .nav-tabs .nav-link.active {
        color: var(--erp-primary);
        border: none;
        border-bottom: 2px solid var(--erp-primary);
        background-color: transparent;
    }
    .size-qty-card {
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #fff;
        overflow: hidden;
        width: 140px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .size-qty-card:focus-within {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .size-qty-label {
        background-color: #f1f3f5;
        color: #495057;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.35rem 0.5rem;
        white-space: nowrap;
        border-right: 1px solid #ced4da;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .size-qty-input-custom {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        text-align: center;
        font-weight: bold;
        color: #212529;
        font-size: 0.85rem;
        padding: 0.25rem;
        width: 100%;
        height: auto !important;
        background: transparent;
    }
    .size-qty-input-custom::-webkit-outer-spin-button,
    .size-qty-input-custom::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .size-qty-input-custom[type=number] {
        -moz-appearance: textfield;
    }
</style>
@endpush
