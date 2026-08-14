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
        <div>
            <a href="{{ route('admin.packing.processNew', $slip_id) }}" class="btn btn-erp btn-erp-default">
                <i class="fas fa-arrow-left text-muted"></i> Back to Selection
            </a>
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

        <div class="erp-card p-0">
            <div class="table-responsive">
                <table class="table erp-table table-sm table-bordered mb-0" style="font-size: 0.85rem;">
                    <thead class="thead-light">
                        <tr>
                            <th width="15%">Lot NO</th>
                            <th width="20%">Design / Profile</th>
                            <th width="15%" class="text-center">Total Pending</th>
                            <th width="35%">Sizes Breakdown (Size : Qty)</th>
                            <th width="15%">Remaining (Live)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lots_data as $lot)
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
                                {{ $lot->remaining_quantity }}
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @if(isset($set_details[$lot->set_id]))
                                        @php
                                            $total_set_qty = $set_details[$lot->set_id]->sum('total_quantity');
                                        @endphp
                                        @foreach($set_details[$lot->set_id] as $detail)
                                        @php
                                            if($total_set_qty > 0) {
                                                $pending_for_size = floor($lot->remaining_quantity * ($detail->total_quantity / $total_set_qty));
                                            } else {
                                                $pending_for_size = 0;
                                            }
                                        @endphp
                                        <div style="border: 1px solid #dee2e6; border-radius: 3px; padding: 2px 8px; background: #f8f9fa;">
                                            <span class="text-muted mr-1">{{ $detail->size }}:</span> 
                                            <strong class="text-primary">{{ $pending_for_size }}</strong>
                                        </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No size data</span>
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-wrap" style="gap: 8px;">
                                    @if(isset($set_details[$lot->set_id]))
                                        @foreach($set_details[$lot->set_id] as $detail)
                                        @php
                                            $sizeName = trim(strtoupper($detail->size));
                                            $original_size_qty = 0;
                                            if ($total_set_qty > 0) {
                                                $original_size_qty = floor($lot->quantity * ($detail->total_quantity / $total_set_qty));
                                            }
                                            
                                            $packed_qty = 0;
                                            if (isset($packed_by_lot_size[$lot->lot_no])) {
                                                $item = $packed_by_lot_size[$lot->lot_no]->where('size_id', $detail->id)->first();
                                                if ($item) $packed_qty = $item->total;
                                            }

                                            $rework_qty = 0;
                                            if (isset($rework_by_lot_size[$lot->lot_no])) {
                                                $item = $rework_by_lot_size[$lot->lot_no]->where('size', $sizeName)->first();
                                                if ($item) $rework_qty = $item->total;
                                            }

                                            $outflow_qty = 0;
                                            if (isset($outflow_by_lot_size[$lot->lot_no])) {
                                                $item = $outflow_by_lot_size[$lot->lot_no]->where('size_id', $detail->id)->first();
                                                if ($item) $outflow_qty = $item->total;
                                            }

                                            $pending_for_size = max(0, $original_size_qty - $packed_qty - $rework_qty - $outflow_qty);
                                        @endphp
                                        <div class="border rounded px-2 py-1 bg-white border-success">
                                            <span class="text-muted mr-1">{{ strtoupper($detail->size) }}:</span>
                                            <strong class="text-success live-qty-span" id="live-qty-{{ $lot->transaction_id }}-{{ str_replace([' ', '.'], '-', trim(strtoupper($detail->size))) }}">{{ $pending_for_size }}</strong>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @php
            $isDomesticOrder = isset($order) && strtolower(trim($order->order_type)) === 'domestic';
            $domesticTitle = $isDomesticOrder ? 'Domestic Packing Operations' : 'Divert to Domestic';
            $domesticSubmitBtn = $isDomesticOrder ? 'Submit Packing' : 'Submit Diversion';
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
                        <a class="nav-link font-weight-bold" id="tab-rework-tab" data-toggle="tab" href="#tab-rework" role="tab">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Defect / Rework
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-damage-tab" data-toggle="tab" href="#tab-damage" role="tab">
                            <i class="fas fa-skull-crossbones text-dark"></i> Dead / Damage
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-sampling-tab" data-toggle="tab" href="#tab-sampling" role="tab">
                            <i class="fas fa-flask text-primary"></i> Sampling
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-debit-tab" data-toggle="tab" href="#tab-debit" role="tab">
                            <i class="fas fa-minus-circle text-warning"></i> Debit
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-manage-tab" data-toggle="tab" href="#tab-manage" role="tab">
                            <i class="fas fa-cog text-secondary"></i> Manage Slip
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
                                        <label class="small font-weight-bold">Design</label>
                                        <select id="plannerDesign" class="form-control form-control-sm">
                                            <option value="">Select Design</option>
                                            @foreach($unique_designs as $design)
                                            <option value="{{ $design }}">{{ $design }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Size Set</label>
                                        <select id="plannerSizeSet" class="form-control form-control-sm" disabled>
                                            <option value="">Select Size Set</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Color</label>
                                        <select id="plannerColor" class="form-control form-control-sm">
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
                                        <select id="plannerWarehouse" class="form-control form-control-sm">
                                            <option value="">Select Store Room</option>
                                            @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Rack (Optional)</label>
                                        <select id="plannerRack" class="form-control form-control-sm">
                                            <option value="">Select Rack</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="small font-weight-bold">Barcode (Optional)</label>
                                        <input type="text" id="plannerBarcode" class="form-control form-control-sm" placeholder="Optional">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button class="btn btn-primary btn-sm w-100" id="btnAddRange">
                                            <i class="fas fa-plus mr-1"></i> Add Range
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold mb-0">Carton Plan <span class="badge badge-success" id="plannerTotalPcs">Total Pcs: 0</span></h6>
                            <div>
                                <button class="btn btn-outline-danger btn-sm" id="btnClearPlan"><i class="fas fa-trash mr-1"></i> Clear Table</button>
                                <button class="btn btn-success btn-sm ml-2" id="btnSavePlan"><i class="fas fa-save mr-1"></i> Save Plan to Database</button>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table erp-table table-sm table-bordered" id="plannerTable">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th>Carton NO</th>
                                        <th>Design</th>
                                        <th>Size Set</th>
                                        <th>Color</th>
                                        <th>Qty</th>
                                        <th>MRP</th>
                                        <th>Price</th>
                                        <th>Warehouse / Rack</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="emptyPlanRow">
                                        <td colspan="9" class="text-center text-muted py-4">No cartons planned yet. Use the form above to add a range.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <h6 class="font-weight-bold mb-3">Saved Cartons <span class="badge badge-secondary">{{ $saved_cartons->count() }}</span></h6>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light sticky-top">
                                    <tr>
                                        <th>ID</th>
                                        <th>Carton NO</th>
                                        <th>Total Pcs</th>
                                        <th>Design</th>
                                        <th>Size Set</th>
                                        <th>Color</th>
                                        <th>Barcode</th>
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
                                    @endphp
                                    <tr>
                                        <td>{{ $sc->id }}</td>
                                        <td class="font-weight-bold">{{ $sc->carton_no }}</td>
                                        <td>{{ $total_qty }}</td>
                                        <td>{{ $design }}</td>
                                        <td>{{ $size_set }}</td>
                                        <td>{{ $color }}</td>
                                        <td>{{ $sc->barcode ?? 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-carton" data-id="{{ $sc->id }}">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No cartons saved yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="tab-pane fade" id="tab-rework" role="tabpanel">
                        <h5 class="text-danger border-bottom pb-2 mb-3"><i class="fas fa-exclamation-triangle"></i> Defect / Rework</h5>
                        
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
                                                        <div class="d-flex flex-wrap" style="gap: 10px;">
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $inputId = "rework-input-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text font-weight-bold">{{ $sizeName }}</span>
                                                                    </div>
                                                                    <input type="number" 
                                                                        id="{{ $inputId }}" 
                                                                        class="form-control rework-qty-input" 
                                                                        data-transaction-id="{{ $lot->transaction_id }}"
                                                                        data-lot-no="{{ $lot->lot_no }}"
                                                                        data-detail-id="{{ $detail->id }}"
                                                                        data-size-name="{{ $sizeName }}"
                                                                        min="0" 
                                                                        placeholder="0">
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
                        <h6 class="font-weight-bold mb-3 text-danger">Saved Rework Records <span class="badge badge-secondary">{{ count($saved_reworks ?? []) }}</span></h6>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
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
                                        <tr><td colspan="6" class="text-center text-muted py-3">No rework records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-damage" role="tabpanel">
                        <h5 class="text-dark border-bottom pb-2 mb-3"><i class="fas fa-skull-crossbones"></i> Dead / Damage</h5>
                        
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
                                                        <div class="d-flex flex-wrap" style="gap: 10px;">
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $inputId = "damage-input-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text font-weight-bold">{{ $sizeName }}</span>
                                                                    </div>
                                                                    <input type="number" 
                                                                        id="{{ $inputId }}" 
                                                                        class="form-control damage-qty-input" 
                                                                        data-transaction-id="{{ $lot->transaction_id }}"
                                                                        data-lot-no="{{ $lot->lot_no }}"
                                                                        data-detail-id="{{ $detail->id }}"
                                                                        data-size-name="{{ $sizeName }}"
                                                                        min="0" 
                                                                        placeholder="0">
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
                        <h6 class="font-weight-bold mb-3 text-dark">Saved Dead Records <span class="badge badge-secondary">{{ count($saved_dead ?? []) }}</span></h6>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
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
                                        <tr><td colspan="6" class="text-center text-muted py-3">No dead records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-sampling" role="tabpanel">
                        <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-flask"></i> Sampling</h5>
                        
                        <form id="samplingForm">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label>Select Storeroom / Rack <span class="text-danger">*</span></label>
                                    <select id="samplingRack" class="form-control form-control-sm select2" required>
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
                                    <input type="text" id="samplingRemarks" class="form-control form-control-sm" placeholder="Optional notes...">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" id="btnSaveSampling" class="btn btn-sm btn-primary w-100">
                                        <i class="fas fa-save"></i> Save Sampling Stock
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table erp-table table-sm table-bordered">
                                    <thead class="thead-light sticky-top">
                                        <tr>
                                            <th>LOT NO</th>
                                            <th>DESIGN / COLOR</th>
                                            <th>SIZE WISE SAMPLING QTY</th>
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
                                                        <div class="d-flex flex-wrap" style="gap: 10px;">
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $inputId = "sampling-input-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text font-weight-bold">{{ $sizeName }}</span>
                                                                    </div>
                                                                    <input type="number" 
                                                                        id="{{ $inputId }}" 
                                                                        class="form-control sampling-qty-input" 
                                                                        data-transaction-id="{{ $lot->transaction_id }}"
                                                                        data-lot-no="{{ $lot->lot_no }}"
                                                                        data-detail-id="{{ $detail->id }}"
                                                                        data-size-name="{{ $sizeName }}"
                                                                        min="0" 
                                                                        placeholder="0">
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
                        <h6 class="font-weight-bold mb-3 text-primary">Saved Sampling Records <span class="badge badge-secondary">{{ count($saved_sampling ?? []) }}</span></h6>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>LOT NO</th>
                                        <th>Storeroom / Rack</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($saved_sampling ?? [] as $sd)
                                        <tr>
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
                                        <tr><td colspan="6" class="text-center text-muted py-3">No sampling records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-debit" role="tabpanel">
                        <h5 class="text-warning border-bottom pb-2 mb-3"><i class="fas fa-minus-circle"></i> Debit</h5>
                        
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
                                                        <div class="d-flex flex-wrap" style="gap: 10px;">
                                                            @foreach($set_details[$lot->set_id] as $detail)
                                                                @php
                                                                    $sizeName = trim(strtoupper($detail->size));
                                                                    $cleanSize = preg_replace('/[\s\.]/', '-', $sizeName);
                                                                    $qtyId = "debit-qty-{$lot->transaction_id}-{$cleanSize}";
                                                                    $rateId = "debit-rate-{$lot->transaction_id}-{$cleanSize}";
                                                                @endphp
                                                                <div class="border border-warning rounded p-2 text-center" style="width: 260px; background: #fff;">
                                                                    <strong class="text-dark d-block mb-2">{{ $sizeName }}</strong>
                                                                    <div class="d-flex flex-nowrap" style="gap: 5px;">
                                                                        <div class="input-group input-group-sm" style="flex: 1; min-width: 0;">
                                                                            <input type="number" id="{{ $qtyId }}" class="form-control text-center debit-qty-input" 
                                                                                data-transaction-id="{{ $lot->transaction_id }}"
                                                                                data-lot-no="{{ $lot->lot_no }}"
                                                                                data-detail-id="{{ $detail->id }}"
                                                                                data-size-name="{{ $sizeName }}"
                                                                                min="0" placeholder="Qty">
                                                                        </div>
                                                                        <div class="input-group input-group-sm" style="flex: 1; min-width: 0;">
                                                                            <div class="input-group-prepend"><span class="input-group-text px-1">₹</span></div>
                                                                            <input type="number" id="{{ $rateId }}" class="form-control px-1 text-center debit-rate-input" min="0" placeholder="Rate">
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
                        <h6 class="font-weight-bold mb-3 text-warning">Saved Debit Records <span class="badge badge-secondary">{{ count($saved_debit ?? []) }}</span></h6>
                        <div class="table-responsive">
                            <table class="table erp-table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
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
                                        <tr><td colspan="7" class="text-center text-muted py-3">No debit records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-manage" role="tabpanel">
                        <h5 class="text-secondary border-bottom pb-2 mb-3"><i class="fas fa-cog"></i> Manage Slip</h5>
                        <div class="d-flex" style="gap: 15px;">
                            <button type="button" id="btnResetSlip" class="btn btn-outline-danger">
                                <i class="fas fa-trash-restore mr-1"></i> Reset Slip
                            </button>
                            <button type="button" id="btnFinalizePacking" class="btn btn-success">
                                <i class="fas fa-check mr-1"></i> Finalize Packing
                            </button>
                        </div>
                    </div>
                </div>
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

    let plannedCartons = [];
    let expandedLots = [];

    function initAvailableSizes() {
        expandedLots = [];
        LOTS_DATA.forEach(lot => {
            let setId = lot.set_id;
            if (SET_DETAILS[setId]) {
                let totalSetQty = SET_DETAILS[setId].reduce((sum, d) => sum + parseInt(d.total_quantity || 0), 0);
                if (totalSetQty > 0) {
                    SET_DETAILS[setId].forEach(detail => {
                        let sizeName = detail.size.toString().trim().toUpperCase();
                        
                        // Calculate original lot quantity for this size
                        let originalSizeQty = Math.floor(parseInt(lot.quantity || 0) * (parseInt(detail.total_quantity || 0) / totalSetQty));

                        // Find packed quantity
                        let packedQty = 0;
                        if (PACKED_BY_LOT_SIZE[lot.lot_no]) {
                            let match = PACKED_BY_LOT_SIZE[lot.lot_no].find(p => p.size_id == detail.id);
                            if (match) packedQty = parseInt(match.total) || 0;
                        }

                        // Find rework quantity
                        let reworkQty = 0;
                        if (REWORK_BY_LOT_SIZE[lot.lot_no]) {
                            let match = REWORK_BY_LOT_SIZE[lot.lot_no].find(r => r.size.toString().trim().toUpperCase() === sizeName);
                            if (match) reworkQty = parseInt(match.total) || 0;
                        }

                        // Find outflow quantity
                        let outflowQty = 0;
                        if (OUTFLOW_BY_LOT_SIZE[lot.lot_no]) {
                            let match = OUTFLOW_BY_LOT_SIZE[lot.lot_no].find(o => o.size_id == detail.id);
                            if (match) outflowQty = parseInt(match.total) || 0;
                        }

                        let pendingForSize = Math.max(0, originalSizeQty - packedQty - reworkQty - outflowQty);

                        if (pendingForSize > 0) {
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
    }

    function updateLiveRemainingUI() {
        $('.live-qty-span').text('0');
        expandedLots.forEach(lot => {
            let cleanSize = lot.size.replace(/[\s\.]/g, '-');
            let spanId = `#live-qty-${lot.transaction_id}-${cleanSize}`;
            
            // Calculate pending rework, damage, sampling, and debit typed in inputs
            let reworkInputId = `#rework-input-${lot.transaction_id}-${cleanSize}`;
            let reworkVal = parseInt($(reworkInputId).val()) || 0;
            
            let damageInputId = `#damage-input-${lot.transaction_id}-${cleanSize}`;
            let damageVal = parseInt($(damageInputId).val()) || 0;
            
            let samplingInputId = `#sampling-input-${lot.transaction_id}-${cleanSize}`;
            let samplingVal = parseInt($(samplingInputId).val()) || 0;

            let debitInputId = `#debit-qty-${lot.transaction_id}-${cleanSize}`;
            let debitVal = parseInt($(debitInputId).val()) || 0;
            
            let displayQty = lot.remaining_quantity - reworkVal - damageVal - samplingVal - debitVal;
            $(spanId).text(displayQty);
        });
    }

    $(document).ready(function() {
        initAvailableSizes();

        // Dynamically cap inputs and update live remaining UI
        $(document).on('input', '.rework-qty-input, .damage-qty-input, .sampling-qty-input, .debit-qty-input', function() {
            let val = parseInt($(this).val()) || 0;
            let transId = $(this).data('transaction-id');
            let sizeName = String($(this).data('size-name')).toUpperCase();
            
            let lot = expandedLots.find(l => l.transaction_id == transId && l.size === sizeName);
            if (lot) {
                if (val > lot.remaining_quantity) {
                    $(this).val(lot.remaining_quantity);
                }
                if (val < 0) {
                    $(this).val(0);
                }
            }
            updateLiveRemainingUI();
        });

        // ---------------- REWORK TAB LOGIC ----------------

        // Fetch Rework Stages
        // Fetch Rework Stages (Shared for Rework and Debit)
        $.get(`/admin/packing/rework-stages`, function(res) {
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
            $.get(`/admin/packing/stage-units/${stageId}`, function(res) {
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
                url: `/admin/packing/reassign-rework`,
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
                        window.location.reload();
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
                url: `/admin/packing/delete-rework/${id}`,
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
                url: `/admin/packing/record-dead-stock`,
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
                        window.location.reload();
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
                url: `/admin/packing/delete-outflow/${id}`,
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
                url: `/admin/packing/record-sampling-stock`,
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
                        window.location.reload();
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
            $.get(`/admin/packing/stage-units/${stageId}`, function(res) {
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
                    let transId = $(this).data('transaction-id');
                    let cleanSize = $(this).attr('id').split('-').pop(); // gets size part
                    let rate = parseFloat($(`#debit-rate-${transId}-${cleanSize}`).val()) || 0;
                    
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
                url: `/admin/packing/record-unit-debit`,
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
                        window.location.reload();
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

        // 1. When Design changes, fetch valid Size Sets
        $('#plannerDesign').change(function() {
            let design = $(this).val();
            let $sizeSetSelect = $('#plannerSizeSet');
            $sizeSetSelect.html('<option value="">Select Size Set</option>').prop('disabled', true);
            $('#plannerMrp').val('');
            $('#plannerPrice').val('');
            
            if (!design) return;

            // Fetch size sets for this design via API
            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/get-size-sets`,
                data: { design_number: design },
                success: function(response) {
                    if (response.status === 'success' && response.size_sets) {
                        response.size_sets.forEach(set => {
                            let sizesJson = JSON.stringify(set.required_sizes).replace(/"/g, '&quot;');
                            $sizeSetSelect.append(`<option value="${set.id}" data-sizes="${sizesJson}">${set.name}</option>`);
                        });
                        $sizeSetSelect.prop('disabled', false);
                    }
                }
            });
        });

        // 2. When Size Set changes, fetch MRP/Price
        $('#plannerSizeSet').change(function() {
            let design = $('#plannerDesign').val();
            let sizeSetId = $(this).val();
            
            if (!design || !sizeSetId) return;

            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/get-master-data`,
                data: { design_number: design, size_set_id: sizeSetId },
                success: function(response) {
                    if (response.status === 'success') {
                        if (response.mrp) $('#plannerMrp').val(response.mrp);
                        if (response.price) $('#plannerPrice').val(response.price);
                        
                        let $colorSelect = $('#plannerColor');
                        $colorSelect.html('<option value="">Select Color</option>');
                        if (response.colors && response.colors.length > 0) {
                            response.colors.forEach(function(c) {
                                $colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                        }
                    }
                }
            });
        });
        
        // Storage Rack logic
        $('#plannerWarehouse').change(function() {
            let warehouseId = $(this).val();
            let $rackSelect = $('#plannerRack');
            $rackSelect.html('<option value="">Select Rack</option>');
            
            if (!warehouseId) return;
            
            $.ajax({
                url: `/admin/inventory/warehouse-stock/racks/${warehouseId}`,
                type: 'GET',
                success: function(data) {
                    if (data && data.length > 0) {
                        data.forEach(function(rack) {
                            $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                        });
                    }
                }
            });
        });
        // 3. Add Range logic
        $('#btnAddRange').click(function() {
            let start = parseInt($('#plannerStart').val());
            let end = parseInt($('#plannerEnd').val());
            let design = $('#plannerDesign').val();
            let sizeSetId = $('#plannerSizeSet').val();
            let sizeSetName = $('#plannerSizeSet option:selected').text();
            let colorId = $('#plannerColor').val();
            let colorName = $('#plannerColor option:selected').text();
            let qty = parseInt($('#plannerQty').val());
            let mrp = parseFloat($('#plannerMrp').val()) || 0;
            let price = parseFloat($('#plannerPrice').val()) || 0;
            let warehouseId = $('#plannerWarehouse').val();
            let warehouseName = $('#plannerWarehouse option:selected').text();
            let rackId = $('#plannerRack').val();
            let barcode = $('#plannerBarcode').val();
            
            if (!start || !end || !design || !sizeSetId || !warehouseId || !qty) {
                alert('Please fill all required fields (Start, End, Design, Size Set, Qty, Warehouse).');
                return;
            }
            if (start > end) {
                alert('Start carton cannot be greater than end carton.');
                return;
            }

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
            
            let requiredDetails = requiredSizesArray.map(size => {
                return { size: size, total_quantity: 1 };
            });

            // Total sets per carton is 'qty'
            let setsPerCarton = qty;
            
            for (let i = start; i <= end; i++) {
                // Determine sizes needed for THIS carton
                let cartonItems = [];
                let sizesFulfilled = true;

                requiredDetails.forEach(detail => {
                    let neededPcs = detail.total_quantity * setsPerCarton; // e.g. 1 S per set * 1 set = 1 pc
                    let sizeName = detail.size.toString().trim().toUpperCase();
                    
                    // 1. match color first
                    let lot = expandedLots.find(l => l.size === sizeName && l.color_id == colorId && l.remaining_quantity >= neededPcs);
                    if (!lot) {
                        // 2. fallback to any lot
                        lot = expandedLots.find(l => l.size === sizeName && l.remaining_quantity >= neededPcs);
                    }
                    
                    if (!lot) {
                        sizesFulfilled = false;
                    } else {
                        // Deduct from available memory
                        lot.remaining_quantity -= neededPcs;
                        cartonItems.push({
                            size_id: lot.size_id,
                            size_name: sizeName,
                            quantity: neededPcs,
                            transaction_id: lot.transaction_id,
                            lot_no: lot.lot_no
                        });
                    }
                });

                if (!sizesFulfilled) {
                    alert(`Not enough pending quantity for Carton ${i}. Stopping range addition.`);
                    break;
                }

                plannedCartons.push({
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

            renderPlannerTable();
        });

        $('#btnClearPlan').click(function() {
            plannedCartons = [];
            initAvailableSizes(); // Reset memory calculation
            renderPlannerTable();
        });

        function renderPlannerTable() {
            let $tbody = $('#plannerTable tbody');
            $tbody.empty();

            if (plannedCartons.length === 0) {
                $tbody.append('<tr id="emptyPlanRow"><td colspan="9" class="text-center text-muted py-4">No cartons planned yet. Use the form above to add a range.</td></tr>');
                $('#plannerTotalPcs').text('Total Pcs: 0');
                return;
            }

            let totalPcs = 0;
            plannedCartons.forEach((carton, index) => {
                totalPcs += carton.qty;
                $tbody.append(`
                    <tr>
                        <td class="font-weight-bold">${carton.carton_no}</td>
                        <td>${carton.design}</td>
                        <td>${carton.size_set_name}</td>
                        <td>${carton.color_name}</td>
                        <td>${carton.qty} Sets</td>
                        <td>${carton.mrp}</td>
                        <td>${carton.price}</td>
                        <td>${carton.warehouse_name}</td>
                        <td>
                            <button class="btn btn-sm btn-danger py-0 px-2 btn-remove-carton" data-index="${index}"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                `);
            });
            $('#plannerTotalPcs').text(`Total Sets: ${totalPcs}`);
            updateLiveRemainingUI();
        }

        $(document).on('click', '.btn-remove-carton', function() {
            let index = $(this).data('index');
            // Re-add sizes back to available memory
            let carton = plannedCartons[index];
            carton.items.forEach(item => {
                let lot = expandedLots.find(l => l.transaction_id == item.transaction_id && l.size === item.size_name);
                if(lot) lot.remaining_quantity += item.quantity;
            });
            plannedCartons.splice(index, 1);
            renderPlannerTable();
        });

        $('#btnSavePlan').click(function() {
            if (plannedCartons.length === 0) {
                alert('No cartons to save.');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/save-carton-plan`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cartons: plannedCartons
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Plan to Database');
                    }
                },
                error: function(err) {
                    alert('An error occurred while saving.');
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Plan to Database');
                }
            });
        });

        $(document).on('click', '.btn-delete-carton', function() {
            if (!confirm('Are you sure you want to delete this carton?')) return;
            
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/delete-carton/${id}`,
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
        let domesticQueue = [];
        
        function renderDomesticTable() {
            let $tbody = $('#domesticTable tbody');
            $tbody.empty();

            if (domesticQueue.length === 0) {
                $tbody.append('<tr id="domesticEmptyRow"><td colspan="6" class="text-muted py-4">No items added to diversion queue yet.</td></tr>');
                return;
            }

            domesticQueue.forEach((item, index) => {
                let tr = `
                    <tr>
                        <td>${item.design_number}</td>
                        <td>${item.size_set_name}</td>
                        <td>${item.color_name}</td>
                        <td>${item.rack_name || 'N/A'}</td>
                        <td>${item.quantity}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-remove-domestic" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(tr);
            });
        }

        // 1. Design change → load filtered Size Sets via AJAX
        $('#domesticDesign').change(function() {
            let design = $(this).val();
            let $sizeSet = $('#domesticSizeSet');
            let $color = $('#domesticColor');

            // Reset dependents
            $sizeSet.html('<option value="">Select Size Set</option>').prop('disabled', true);
            $color.html('<option value="">Select Color</option>').prop('disabled', true);
            $('#domesticDesign').data('product-id', '');

            // Reset qty field
            $('#domesticQty').val('').prop('disabled', true).removeAttr('max');
            $('#domesticQtyInfo').text('Select size set first').removeClass('text-success text-danger text-warning').addClass('text-muted');

            if (!design) return;

            $sizeSet.html('<option value="">Loading...</option>').prop('disabled', true);
            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/get-size-sets`,
                type: 'GET',
                data: { design_number: design },
                success: function(res) {
                    $sizeSet.html('<option value="">Select Size Set</option>');
                    if (res.status === 'success' && res.size_sets.length > 0) {
                        res.size_sets.forEach(function(ss) {
                            $sizeSet.append(`<option value="${ss.id}" data-sizes="${(ss.required_sizes || []).join(',')}">${ss.name}</option>`);
                        });
                        $sizeSet.prop('disabled', false);
                    } else {
                        $sizeSet.html('<option value="">No Size Sets Found</option>');
                    }
                },
                error: function() {
                    $sizeSet.html('<option value="">Error loading size sets</option>');
                }
            });
        });

        function updateDomesticMaxQty() {
            let design = $('#domesticDesign').val();
            let sizeSetId = $('#domesticSizeSet').val();
            let colorId = $('#domesticColor').val();
            let $qty = $('#domesticQty');
            let $qtyInfo = $('#domesticQtyInfo');

            // Find current sets of this item already in the queue so we deduct them from what's available
            let queuedQty = domesticQueue
                .filter(item => item.design_number === design && item.size_set_id === sizeSetId && item.color_id == colorId)
                .reduce((sum, item) => sum + item.quantity, 0);

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

        // 2. Size Set change → load filtered Colors + product_id via AJAX
        $('#domesticSizeSet').change(function() {
            let design = $('#domesticDesign').val();
            let sizeSetId = $(this).val();
            let $color = $('#domesticColor');

            $color.html('<option value="">Select Color</option>').prop('disabled', true);
            $('#domesticDesign').data('product-id', '');

            updateDomesticMaxQty();

            if (!design || !sizeSetId) return;

            $color.html('<option value="">Loading...</option>').prop('disabled', true);

            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/get-master-data`,
                type: 'GET',
                data: { design_number: design, size_set_id: sizeSetId },
                success: function(res) {
                    $color.html('<option value="">Select Color</option>');
                    if (res.status === 'success') {
                        $('#domesticDesign').data('product-id', res.product_id);
                        if (res.colors && res.colors.length > 0) {
                            res.colors.forEach(function(c) {
                                $color.append(`<option value="${c.id}">${c.name}</option>`);
                            });
                            $color.prop('disabled', false);
                        } else {
                            $color.html('<option value="">No Colors Found</option>');
                        }
                    } else {
                        $color.html('<option value="">Not Found</option>');
                    }
                    updateDomesticMaxQty();
                },
                error: function() {
                    $color.html('<option value="">Error loading colors</option>');
                    updateDomesticMaxQty();
                }
            });
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

        // Add to Queue
        $('#btnAddDomestic').click(function() {
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

            // Apply deductions to actual expandedLots
            deductedItems.forEach(d => {
                let lot = expandedLots.find(l => l.transaction_id == d.transaction_id && l.size === d.size_name);
                if (lot) {
                    lot.remaining_quantity -= d.quantity;
                }
            });

            domesticQueue.push({
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
            });

            // Reset quantity and dropdowns
            $('#domesticQty').val('');
            
            renderDomesticTable();
            updateLiveRemainingUI();

            // Refresh available quantity label
            let maxSets = parseInt($('#domesticDesign').data('max-sets')) || 0;
            let currentInQueue = domesticQueue.filter(item => item.design_number === design && item.size_set_id === sizeSetId).reduce((sum, item) => sum + item.quantity, 0);
            let remainingSets = Math.max(0, maxSets - currentInQueue);
            $('#domesticQty').attr('max', remainingSets);
            $('#domesticQtyInfo').text(`${remainingSets} sets remaining`);
        });

        // Remove from Queue
        $(document).on('click', '.btn-remove-domestic', function() {
            let index = $(this).data('index');
            let item = domesticQueue[index];
            
            // Restore sizes back to available memory
            item.items.forEach(d => {
                let lot = expandedLots.find(l => l.transaction_id == d.transaction_id && l.size === d.size_name);
                if (lot) lot.remaining_quantity += d.quantity;
            });

            domesticQueue.splice(index, 1);
            renderDomesticTable();
            updateLiveRemainingUI();

            // Reset dropdown & qty
            $('#domesticDesign').trigger('change');
        });

        // Save Bulk
        $('#btnSaveDomesticBulk').click(function() {
            if (domesticQueue.length === 0) {
                alert('No items in the diversion queue.');
                return;
            }

            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...');

            $.ajax({
                url: `/admin/packing/save-domestic-bulk`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    order_id: '{{ $order->id }}',
                    slip_id: SLIP_ID,
                    boxes: domesticQueue
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Domestic diversion saved successfully.');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Submit Diversion');
                    }
                },
                error: function(err) {
                    alert('An error occurred while submitting.');
                    $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Submit Diversion');
                }
            });
        });

        // Delete Domestic Box/Diversion
        $(document).on('click', '.btn-delete-domestic', function() {
            if (!confirm('Are you sure you want to delete this domestic diversion? This will restore the quantities.')) return;
            
            let id = $(this).data('id');
            let $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: `/admin/packing/process-new/${SLIP_ID}/api/delete-domestic/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(res.message || 'Failed to delete domestic diversion.');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Delete');
                    }
                },
                error: function() {
                    alert('Error deleting domestic diversion.');
                    $btn.prop('disabled', false).html('<i class="fas fa-trash-alt"></i> Delete');
                }
            });
        });

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
                        window.location.reload();
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
                        window.location.reload();
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
</style>
@endpush
