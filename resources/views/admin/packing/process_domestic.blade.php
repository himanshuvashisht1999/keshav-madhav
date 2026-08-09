@extends('admin.layouts.app')

@section('content')

    <style>
        .content-wrapper h4,
        .content-wrapper .h4 {
            font-size: 1.00rem !important;
            font-weight: 500;
        }

        /* PREMIUM BULK MODAL STYLING */
        #bulkPackingModal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        #bulkPackingModal .modal-header {
            background: #f8f9fa;
            border-bottom: 1px solid #edf2f7;
            border-radius: 12px 12px 0 0;
            padding: 1.25rem 1.5rem;
        }

        #bulkPackingModal .modal-title {
            font-weight: 700;
            color: #2d3748;
            letter-spacing: -0.02em;
        }

        /* MODERN TABS (SEGMENTED CONTROL LOOK) */
        #bulk-pack-tabs {
            background: #edf2f7;
            padding: 4px;
            border-radius: 8px;
            border: none;
        }

        #bulk-pack-tabs .nav-link {
            border: none !important;
            border-radius: 6px !important;
            color: #718096 !important;
            transition: all 0.2s ease;
            font-weight: 600;
            background: transparent !important;
        }

        #bulk-pack-tabs .nav-link.active {
            background: #fff !important;
            color: #3182ce !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        #bulk-pack-tabs .nav-link:hover:not(.active) {
            background: rgba(0, 0, 0, 0.03) !important;
        }

        /* SUMMARY ACTION CARD */
        #bulkCalculationSummary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        #bulkCalculationSummary strong {
            color: #fff;
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        #bulkCalculationSummary .fa-magic {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* FORM REFINEMENT */
        #bulkPackingModal label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }

        #bulkPackingModal .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        #bulkPackingModal .form-control:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        #bulkPackingModal .form-control.is-invalid {
            border-color: #e53e3e !important;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.2) !important;
            background-image: none !important;
        }

        /* ACTION BUTTON */
        #btnSubmitBulkCreate {
            background: #3182ce;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            transition: all 0.2s;
        }

        #btnSubmitBulkCreate:hover {
            background: #2b6cb0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
        }
    </style>
    @php $is_domestic = (isset($order) && strtolower(trim($order->order_type ?? '')) == 'domestic'); @endphp
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h4>
                            @if($order)
                                <span class="text-muted">Customer:</span> {{ $order->customer->name ?? 'N/A' }}
                                <small class="ms-3 text-muted">Order: {{ $order->sku }}</small>
                            @else
                                <div class="d-flex align-items-center">
                                    <span class="text-muted me-2 mr-1 ">Select Order: </span>
                                    <select class="form-control select2" id="orderSelect" style="width: 300px;">
                                        <option value="">-- Select Order to Start Packing --</option>
                                        @foreach($active_orders as $ao)
                                            <option value="{{ $ao->id }}" data-type="{{ strtolower(trim($ao->order_type)) }}">
                                                {{-- #{{ $ao->id }} - {{ $ao->customer->name ?? 'Unknown' }} ({{ $ao->sku }}) --}}
                                                {{ $ao->customer->name ?? 'Unknown' }} ({{ $ao->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </h4>

                    </div>
                    <div class="col-md-4 text-right d-flex justify-content-end align-items-center" style="gap: 10px;">
                        @if($slip && $slip->slip_file)
                            <a href="{{ asset('assets/production_slips/' . $slip->slip_file) }}"
                                target="_blank" rel="noopener noreferrer" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-image mr-1"></i> View Slip Photo
                            </a>
                        @endif
                        @if($order)
                            <form action="{{ route('admin.packing.clearOrder', $slip->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to change the selected order? This is only possible if no cartons have been created.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-exchange-alt mr-1"></i> Change Order
                                </button>
                            </form>
                            <a id="fileLink" href="{{asset('/assets/products/' . $order->corporate_order_file)}}"
                                target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-alt mr-1"></i> Sales Order File
                            </a>
                        @endif
                        <a href="" id="fileLink_hidden" target="_blank" rel="noopener noreferrer"
                            class="btn btn-outline-primary btn-sm d-none">
                            <i class="fas fa-file-alt mr-1"></i> Sales Order File
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- ... existing structure ... -->
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="alert alert-info py-2 small">
                            DEBUG (v2.2): Order Type: "{{ $order->order_type ?? 'NULL' }}" | Order ID:
                            {{ $order->id ?? 'NULL' }} | Masters Count: {{ count($domestic_masters ?? []) }}
                        </div>
                    </div>
                    <!-- LEFT PANEL: AVAILABLE ITEMS -->
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 12px;">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0 font-weight-bold text-dark">
                                    <i class="fas fa-clipboard-list mr-2 text-primary"></i>
                                    Order Details & Items
                                </h5>
                            </div>
                            <div class="card-body p-0" style="overflow-y: auto; max-height: 700px;">
                                <ul class="list-group list-group-flush" id="available-items-list">
                                    <li class="list-group-item text-muted text-center">
                                        @if($order) Loading items... @else Please select an order first. @endif
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: PACKING STRUCTURE -->
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div
                                class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Packed Structure (Storeroom)</h3>
                                <div>
                                    {{-- <button class="btn btn-light btn-sm" onclick="openCreateCartonModal()"
                                        id="btnCreateCarton" @if(!$order) disabled @endif>
                                        <i class="fas fa-plus"></i> New Carton
                                    </button> --}}
                                    <button class="btn btn-outline-info btn-sm rounded-pill px-3 mr-2"
                                        id="btnDomesticPacking" onclick="openDomesticPackingModal()">
                                        <i class="fas fa-box mr-1"></i> Domestic Packing
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm rounded-pill px-3 mr-2"
                                        onclick="openReworkModal()">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Defect / Rework
                                    </button>
                                    <button class="btn btn-outline-dark btn-sm rounded-pill px-3 mr-2"
                                        onclick="openDeadStockModal()">
                                        <i class="fas fa-skull-crossbones mr-1"></i> Dead / Damage
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 mr-2"
                                        onclick="openSamplingModal()">
                                        <i class="fas fa-flask mr-1"></i> Sampling
                                    </button>
                                    <button class="btn btn-outline-warning btn-sm rounded-pill px-3 mr-2"
                                        onclick="openDebitModal()">
                                        <i class="fas fa-minus-circle mr-1"></i> Debit
                                    </button>
                                    {{-- <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"
                                        onclick="openBulkPackingModal()" @if(!$order) disabled @endif>
                                        <i class="fas fa-layer-group"></i> Bulk Packing
                                    </button> --}}
                                    <button class="btn btn-success btn-sm ms-2" onclick="finalizePacking()" id="btnFinalize"
                                        @if(!$order) disabled @endif>
                                        <i class="fas fa-check"></i> Finalize
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="packing-structure-area" style="overflow-y: auto; max-height: 600px;">
                                <div class="text-center text-muted mt-5">
                                    <p>No cartons created yet.</p>
                                    {{-- <button class="btn btn-outline-primary btn-sm" onclick="openCreateCartonModal()"
                                        id="btnCreateFirstCarton" @if(!$order) disabled @endif>Create First Carton</button>
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MOVEMENT SUMMARY ROW -->
                <div class="row mt-4 mb-5">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                            <div
                                class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 text-dark font-weight-bold">
                                        <i class="fas fa-exchange-alt mr-2 text-primary"></i>
                                        UNIT MOVEMENT & LOSS SUMMARY
                                    </h5>
                                    <p class="mb-0 text-muted small">Audit trail of pieces sent for Rework, Sampling, Dead
                                        Stock, or Unit Debits</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-soft-secondary px-3 py-2"
                                        style="border-radius: 20px; background: #f8f9fa; border: 1px solid #eee;">
                                        <i class="fas fa-info-circle mr-1 text-info"></i> SESSION LOG
                                    </span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="px-4 py-3 text-muted small text-uppercase font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Type</th>
                                                <th class="py-3 text-muted small text-uppercase font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Item / Size</th>
                                                <th class="py-3 text-muted small text-uppercase text-center font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Qty</th>
                                                <th class="py-3 text-muted small text-uppercase font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Destination / Accountability</th>
                                                <th class="py-3 text-muted small text-uppercase font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Remarks</th>
                                                <th class="py-3 text-muted small text-uppercase font-weight-bold"
                                                    style="letter-spacing: 0.5px;">Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $hasData = false; @endphp
                                            @forelse($outflows as $o)
                                                @php $hasData = true; @endphp
                                                <tr class="align-middle border-bottom">
                                                    <td class="px-4">
                                                        @php
                                                            $badge = 'badge-danger';
                                                            $icon = 'fa-skull-crossbones';
                                                            if ($o->type == 'sampling') {
                                                                $badge = 'badge-primary';
                                                                $icon = 'fa-flask';
                                                            }
                                                            if ($o->type == 'debit') {
                                                                $badge = 'badge-warning';
                                                                $icon = 'fa-minus-circle';
                                                            }
                                                         @endphp
                                                        <span class="badge {{ $badge }} text-uppercase px-2 py-1 shadow-sm"
                                                            style="font-size: 10px; border-radius: 4px;">
                                                            <i class="fas {{ $icon }} mr-1"></i> {{ $o->type }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold text-dark">
                                                            {{ $o->product->design_number ?? 'N/A' }}</div>
                                                        <div class="text-muted small">Color: {{ $o->color->name ?? 'N/A' }} |
                                                            Size: <span
                                                                class="text-primary font-weight-bold">{{ $o->size->size ?? 'N/A' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="h6 mb-0 font-weight-bold text-dark">{{ $o->quantity }}</span>
                                                        <small class="text-muted d-block small">pcs</small>
                                                    </td>
                                                    <td>
                                                        @if($o->type == 'debit')
                                                            <div class="small mb-1">
                                                                <i class="fas fa-user-tie mr-1 text-muted"></i>
                                                                <strong>{{ $o->responsibleStage->name ?? '' }}</strong>
                                                                <span class="text-muted mx-1">→</span>
                                                                <strong>{{ $o->responsibleUnit->name ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="badge badge-soft-danger px-2"
                                                                style="background: #fff5f5; border: 1px solid #ffdcdc;">
                                                                Debit Amount: <span
                                                                    class="font-weight-bold">₹{{ number_format($o->total_amount, 2) }}</span>
                                                            </div>
                                                        @else
                                                            <div class="small">
                                                                <i class="fas fa-warehouse mr-1 text-muted"></i>
                                                                <span class="text-muted">Storage:</span>
                                                                <strong>{{ $o->rack->storeroom->name ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="text-muted small mt-1 ml-4">
                                                                Rack: <strong>{{ $o->rack->name ?? 'N/A' }}</strong>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="text-muted small"
                                                            style="max-width: 250px; line-height: 1.4;">{{ $o->remarks ?: '—' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold small text-dark">
                                                            {{ $o->created_at->format('d M, Y') }}</div>
                                                        <div class="text-muted text-xs">{{ $o->created_at->format('h:i A') }}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                            @endforelse

                                            @foreach($reworks as $r)
                                                @php $hasData = true; @endphp
                                                @foreach($r->details as $rd)
                                                    <tr class="align-middle border-bottom" style="background-color: #fafbfc;">
                                                        <td class="px-4">
                                                            @php
                                                                $badgeClass = 'badge-info';
                                                                $badgeText = strtoupper($r->type ?? 'REWORK');
                                                                $badgeIcon = 'fa-tools';

                                                                if ($r->type == 'sampling') {
                                                                    $badgeClass = 'badge-primary';
                                                                    $badgeIcon = 'fa-flask';
                                                                } elseif ($r->type == 'damage' || $r->type == 'dead') {
                                                                    $badgeClass = 'badge-danger';
                                                                    $badgeIcon = 'fa-skull-crossbones';
                                                                } elseif ($r->type == 'debit') {
                                                                    $badgeClass = 'badge-warning';
                                                                    $badgeIcon = 'fa-minus-circle';
                                                                }
                                                             @endphp
                                                            <span class="badge {{ $badgeClass }} text-uppercase px-2 py-1 shadow-sm"
                                                                style="font-size: 10px; border-radius: 4px;">
                                                                <i class="fas {{ $badgeIcon }} mr-1"></i> {{ $badgeText }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div
                                                                class="font-weight-bold {{ ($r->type == 'rework') ? 'text-info' : 'text-dark' }} italic">
                                                                {{ ($r->type == 'rework') ? 'Sent for Rework / Alteration' : ($r->remarks ?: 'Inventory Move') }}
                                                            </div>
                                                            <div class="text-muted small">Size: <span
                                                                    class="text-dark font-weight-bold">{{ $rd->size }}</span></div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span
                                                                class="h6 mb-0 font-weight-bold text-info">{{ $rd->quantity }}</span>
                                                            <small class="text-muted d-block small">pcs</small>
                                                        </td>
                                                        <td>
                                                            <div class="small">
                                                                <i class="fas fa-level-up-alt fa-rotate-270 mr-1 text-muted"></i>
                                                                <span class="text-muted">Target Stage:</span>
                                                                <strong>{{ $r->toStage->name ?? 'N/A' }}</strong>
                                                            </div>
                                                            <div class="text-muted small mt-1 ml-3">
                                                                Target Unit: <strong>{{ $r->toUnit->name ?? 'N/A' }}</strong>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="text-muted small italic"
                                                                style="max-width: 250px; line-height: 1.4;">
                                                                {{ $r->note ?: 'Defect rework order' }}</div>
                                                        </td>
                                                        <td>
                                                            <div class="font-weight-bold small text-dark">
                                                                {{ $r->created_at->format('d M, Y') }}</div>
                                                            <div class="text-muted text-xs">{{ $r->created_at->format('h:i A') }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach

                                            @if(!$hasData)
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="py-4">
                                                            <i
                                                                class="fas fa-clipboard-list text-muted fa-3x mb-3 opacity-20"></i>
                                                            <h6 class="text-muted font-weight-normal">No outflows or loss data
                                                                recorded for this slip session.</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <!-- Modals -->
    <div class="modal fade" id="createBoxModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Box</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="createBoxForm">
                        <div class="mb-3">
                            <label>Box Number (Leave empty for auto-generation)</label>
                            <input type="text" class="form-control" name="box_no" placeholder="BX-YYMMDD-XXXX">
                        </div>
                        <h6>Select Items to Pack in Box</h6>
                        <table class="table table-sm">
                            <!-- ... -->
                            <tbody id="boxItemsTable"></tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="submitCreateBox()">Create Box</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createCartonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Carton</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="createCartonForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Carton Number</label>
                                <input type="text" class="form-control" name="carton_no" id="carton_no" required>
                            </div>
                            <div class="col-md-3">
                                <label>Store Room</label>
                                <select class="form-control" id="storeroomSelect" onchange="updateRackSelect()">
                                    <option value="">Select Store Room</option>
                                    @foreach($storerooms as $store)
                                        <option value="{{ $store->id }}" data-racks="{{ $store->racks }}">{{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Rack</label>
                                <select class="form-control" name="rack_id" id="rackSelect">
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Add Unpacked Boxes</h6>
                                <div id="unpackedBoxesList"></div>
                            </div>
                            <!-- ... -->
                            <div class="col-md-12">
                                <div class="d-flex border-bottom mb-3">
                                    <button type="button" class="btn btn-outline-primary active mr-2" id="btn-tab-sets"
                                        onclick="switchPackTab('sets')">Pack Sets</button>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-tab-loose"
                                        onclick="switchPackTab('loose')">Pack Loose Items</button>
                                </div>

                                <div id="tab-content-sets">
                                    <div id="cartonSetsContainer" style="max-height: 400px; overflow-y: auto;">
                                        <p class="text-muted small">Loading sets...</p>
                                    </div>
                                </div>

                                <div id="tab-content-loose" style="display: none;">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Barcode</th>
                                                <th>Design No</th>
                                                <th>Colour</th>
                                                <th>Size</th>
                                                <th>Remaining</th>
                                                <th>Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartonItemsTable"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="submitCreateCarton()">Create Carton</button>
                </div>
            </div>
        </div>
    </div>

    <!-- createSetModal -->
    <div class="modal fade" id="createSetModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Set</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="createSetForm">
                        <h6>Define Set Composition</h6>
                        <small class="text-muted">Enter quantity of each item per set.</small>
                        <table class="table table-sm mt-2">
                            <thead>
                                <tr>
                                    <th>Size</th>
                                    <th>Qty Per Set</th>
                                </tr>
                            </thead>
                            <tbody id="createSetTableBody"></tbody>
                        </table>

                        <div class="form-group mt-3">
                            <label>Total Sets to Make</label>
                            <input type="number" class="form-control" id="totalSetsToMake" min="1" value="1">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="submitCreateSet()">Create
                        Sets</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Finalize Session Modal -->
    <div class="modal fade" id="finalizeSessionModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-check-circle mr-2"></i> Finalize Domestic Packing
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning small py-2 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Once finalized, this domestic session will be closed and stock will be moved to inventory.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold text-dark mb-1">Completion Date & Time</label>
                        <input type="datetime-local" id="packing_completion_date" class="form-control" 
                               value="{{ date('Y-m-d\TH:i') }}">
                        <small class="text-muted">Select the actual date and time this domestic packing was completed.</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4 font-weight-bold" onclick="submitFinalize()">
                        <i class="fas fa-check mr-1"></i> Finalize Domestic
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Packing Modal -->
    <div class="modal fade" id="bulkPackingModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Packing</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <form id="bulkPackingForm">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="font-weight-bold">Carton Capacity (Boxes per Carton)</label>
                                <input type="number" class="form-control" name="boxes_per_carton" id="bulk_boxes_per_carton"
                                    value="1" min="1" required oninput="calculateBulkSummary()">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="d-block mb-3">Packing Strategy</label>
                            <div class="card shadow-none border-0 mb-0">
                                <div class="card-header p-1 bg-transparent border-0">
                                    <ul class="nav nav-pills nav-fill" id="bulk-pack-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link active py-2" href="#bulk-tab-sets" data-toggle="tab"
                                                onclick="switchBulkMode('set')">
                                                <i class="fas fa-layer-group mr-1"></i> By Set
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-2" href="#bulk-tab-loose" data-toggle="tab"
                                                onclick="switchBulkMode('loose')">
                                                <i class="fas fa-box mr-1"></i> Loose
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-2" href="#bulk-tab-all-sets" data-toggle="tab"
                                                onclick="switchBulkMode('full_sets')">
                                                <i class="fas fa-boxes mr-1"></i> All Sets
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link py-2" href="#bulk-tab-all-loose" data-toggle="tab"
                                                onclick="switchBulkMode('full_loose')">
                                                <i class="fas fa-pallet mr-1"></i> All Loose
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-body p-0 pt-3">
                                    <div class="tab-content">
                                        <!-- BY SET TAB -->
                                        <div class="tab-pane active" id="bulk-tab-sets">
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label>Select Set Pattern</label>
                                                    <select class="form-control shadow-sm" name="set_id" id="bulkSetSelect"
                                                        onchange="populateBulkSizeSet()">
                                                        <option value="">-- Select Set --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <label>Sets to Pack</label>
                                                        <span id="bulk_avail_sets"
                                                            class="badge badge-light text-primary border-0 pt-1 px-0">Avl:
                                                            0</span>
                                                    </div>
                                                    <input type="number" class="form-control shadow-sm" name="target_sets"
                                                        id="bulk_target_sets" value="0" min="0"
                                                        oninput="calculateBulkSummary()">
                                                </div>
                                                <div class="col-md-12" id="bulkPackingPreviewContainer"
                                                    style="display: none;">
                                                    <div class="card bg-light border-0 shadow-none">
                                                        <div class="card-body p-3">
                                                            <h6
                                                                class="font-weight-bold mb-2 small text-uppercase text-muted">
                                                                Box Specification</h6>
                                                            <div id="bulkPackingPreview"></div>
                                                            <input type="hidden" name="size_set" id="bulk_hidden_size_set">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- LOOSE TAB -->
                                        <div class="tab-pane" id="bulk-tab-loose">
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label>Select Size</label>
                                                    <select class="form-control shadow-sm" id="bulkLooseItemSelect"
                                                        onchange="calculateBulkSummary()">
                                                        <option value="">-- Select Item --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <label>Total Pcs</label>
                                                        <span id="bulk_avail_pieces"
                                                            class="badge badge-light text-primary border-0 pt-1 px-0">Avl:
                                                            0</span>
                                                    </div>
                                                    <input type="number" class="form-control shadow-sm"
                                                        id="bulk_target_pieces" value="0" min="0"
                                                        oninput="calculateBulkSummary()">
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="alert alert-light border small text-muted py-2">
                                                        <i class="fas fa-info-circle mr-1"></i> Standard: One piece per box
                                                        distribution.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ALL SETS TAB -->
                                        <div class="tab-pane" id="bulk-tab-all-sets">
                                            <div class="alert alert-light border py-3 small mb-0">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-info-circle mr-3 mt-1 text-info"></i>
                                                    <div>
                                                        <strong class="text-dark d-block mb-1">WHOLE ORDER (BY SET)</strong>
                                                        Packs every remaining set across all patterns in the current order
                                                        into standardized boxes & cartons.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- ALL LOOSE TAB -->
                                        <div class="tab-pane" id="bulk-tab-all-loose">
                                            <div class="alert alert-light border py-3 small mb-0">
                                                <div class="d-flex align-items-start">
                                                    <i class="fas fa-info-circle mr-3 mt-1 text-primary"></i>
                                                    <div>
                                                        <strong class="text-dark d-block mb-1">WHOLE ORDER (LOOSE)</strong>
                                                        Packs every remaining piece in the entire order into individual
                                                        boxes (1 pc/box).
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AUTOMATED CALCULATION SUMMARY -->
                        <div class="mb-4 d-none" id="bulkCalculationSummary">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-magic mr-2"></i>
                                    <span id="bulkSummaryText"
                                        style="font-size: 1.1rem; letter-spacing: -0.01em;">...</span>
                                </div>
                                <input type="hidden" name="total_boxes" id="bulk_hidden_total_boxes">
                                <input type="hidden" name="mode" id="bulk_hidden_mode" value="set">
                            </div>
                        </div>

                        <!-- SET-WISE STORAGE (Dynamic) -->
                        <div id="bulkSetWiseStorageContainer" class="mb-4 d-none">
                            <label class="font-weight-bold d-block mb-3 border-bottom pb-2">Set-wise Storage
                                Assignment</label>
                            <div id="bulkSetWiseStorageList" class="p-3 bg-light rounded shadow-sm border"
                                style="max-height: 250px; overflow-y: auto;">
                                <!-- Dynamic content will go here -->
                            </div>
                        </div>

                        <!-- GLOBAL STORAGE LOCATION -->
                        <div id="bulkGlobalStorageContainer" class="row">
                            <div class="col-md-6 text-left">
                                <label class="font-weight-bold">Storage Location (Global)</label>
                                <select class="form-control select2 shadow-sm" id="bulkStoreroomSelect"
                                    onchange="updateBulkRackSelect()">
                                    <option value="">Select Store Room</option>
                                    @foreach($storerooms as $store)
                                        <option value="{{ $store->id }}" data-racks="{{ $store->racks }}">
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 text-left">
                                <label class="font-weight-bold">Assigned Rack (Global)</label>
                                <select class="form-control select2 shadow-sm" name="rack_id" id="bulkRackSelect">
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light font-weight-bold text-muted px-4"
                        data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnSubmitBulkCreate" onclick="submitBulkPacking()">
                        Establish Packing Plan
                    </button>
                </div>
            </div>
        </div>
    </div>

    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="addRangeToPlanner()">
            <i class="fas fa-plus mr-1"></i> Add Range
        </button>
    </div>
    </div>
    <div class="row g-2 mt-2">
        <div class="col-md-3">
            <label class="small font-weight-bold mb-0">Warehouse</label>
            <select id="rangeStore" class="form-control form-control-sm" onchange="updateRangeRacks()">
                <option value="">Select Store Room</option>
                @foreach($storerooms as $store) <option value="{{ $store->id }}" data-racks="{{ $store->racks }}">
                {{ $store->name }}</option> @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="small font-weight-bold mb-0">Rack</label>
            <select id="rangeRack" class="form-control form-control-sm"></select>
        </div>
        <div class="col-md-3">
            <label class="small font-weight-bold mb-0">Barcode</label>
            <input type="text" id="rangeBarcode" class="form-control form-control-sm" placeholder="Optional">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-outline-danger btn-sm btn-block" onclick="$('#plannerTableBody').empty(); updateCartonPlanTotalQty();">
                <i class="fas fa-trash-alt mr-1"></i> Clear Table
            </button>
        </div>
    </div>
    </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 font-weight-bold">Carton Plan <span id="cartonPlanTotalQty" class="badge badge-success ml-2 font-weight-bold" style="font-size: 14px;">Total Pcs: 0</span></h5>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addPlannerRow()">
            <i class="fas fa-plus mr-1"></i> Add Single Carton
        </button>
    </div>

    <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
        <table class="table table-sm table-hover border rounded mb-0" id="plannerTable"
            style="table-layout: fixed; width: 100%;">
            <thead class="bg-light sticky-top" style="z-index: 10;">
                <tr class="small text-uppercase">
                    <th style="width: 5%">No</th>
                    <th style="width: 10%">Design</th>
                    <th style="width: 10%">Size Set</th>
                    <th style="width: 10%">Color</th>
                    <th style="width: 10%">Type</th>
                    <th style="width: 5%">Qty</th>
                    <th style="width: 8%">MRP</th>
                    <th style="width: 8%">Price</th>
                    <th style="width: 10%">Barcode</th>
                    <th style="width: 18%">Storage (Store / Rack)</th>
                    <th style="width: 6%"></th>
                </tr>
            </thead>
            <tbody id="plannerTableBody"></tbody>
        </table>
    </div>
    </div>
    </div>
    </div>
    <div class="modal-footer bg-light p-3">
        <div class="mr-auto text-muted small">
            <i class="fas fa-info-circle mr-1"></i> Overage packing is allowed for corporate orders.
        </div>
        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success px-5 shadow-sm font-weight-bold" onclick="submitMultiCartonPlan()">
            <i class="fas fa-check-circle mr-1"></i> PROCESS ALL CARTONS
        </button>
    </div>
    </div>
    </div>
    </div>

    <!-- DOMESTIC PACKING MODAL -->
    <div class="modal fade" id="domesticPackingModal" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 90%;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white p-3">
                    <h5 class="modal-title font-weight-bold mb-0"><i class="fas fa-box mr-2"></i> Domestic Packing</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row no-gutters">
                        <div class="col-md-2 bg-light border-right p-3">
                            <h6 class="font-weight-bold text-muted mb-3 small text-uppercase">Availability</h6>
                            <div id="domesticInventoryList" style="max-height: 700px; overflow-y: auto;"></div>
                        </div>
                        <div class="col-md-10 p-3">
                            <!-- Selected Lots Summary -->
                            <div id="selectedLotsSummary" class="card border-info shadow-sm mb-4 d-none">
                                <div class="card-body p-3 bg-info text-white rounded">
                                    <h6 class="font-weight-bold mb-2 text-uppercase">Selected Lot(s) Summary</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <small class="d-block font-weight-bold">Selected Lot(s)</small>
                                            <span id="summaryLotNumbers" class="h6 mb-0"></span>
                                            <div class="mt-1">
                                                <span id="summaryDesign" class="badge badge-light text-dark border"></span>
                                                <span id="summarySizeSet" class="badge badge-light text-dark border ml-1"></span>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <small class="d-block font-weight-bold">Total Unit Qty</small>
                                            <span id="summaryTotalQty" class="h6 mb-0"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="d-block font-weight-bold">Already Packed</small>
                                            <span id="summaryPacked" class="h6 mb-0"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="d-block font-weight-bold">Available For Now</small>
                                            <span id="summaryAvailable" class="h6 mb-0 font-weight-bolder text-warning"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Quick Add Area -->
                            <div class="card bg-info-light border-0 mb-4" style="background-color: #f0f7ff;">
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <div class="row no-gutters">
                                                <div class="col-6 pr-1">
                                                    <label class="small font-weight-bold mb-0">Start</label>
                                                    <input type="number" id="rangeStart"
                                                        class="form-control form-control-sm" value="1">
                                                </div>
                                                <div class="col-6">
                                                    <label class="small font-weight-bold mb-0">End</label>
                                                    <input type="number" id="rangeEnd" class="form-control form-control-sm"
                                                        value="10">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1 d-none">
                                            <label class="small font-weight-bold mb-0">Design</label>
                                            <select id="rangeDesign" class="form-control form-control-sm"
                                                onchange="updateRangeSizeSets()" disabled></select>
                                        </div>
                                        <div class="col-md-1 d-none">
                                            <label class="small font-weight-bold mb-0">Size Set</label>
                                            <select id="rangeSizeSet" class="form-control form-control-sm"
                                                onchange="updateRangeColors()" disabled></select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small font-weight-bold mb-0">Design</label>
                                            <select id="dom_design" class="form-control form-control-sm select2"
                                                style="width: 100%;">
                                                <option value="">Select Design</option>
                                                @foreach($domestic_masters['products'] as $p)
                                                    @php
                                                        $series = $p->series ? $p->series->name : '';
                                                        $garment = $p->name_of_garment ?? '';
                                                        $label = $p->design_number . ($series || $garment ? " ($series $garment)" : "");
                                                    @endphp
                                                    <option value="{{ $p->id }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small font-weight-bold mb-0">Size Set</label>
                                            <select id="dom_size_set" class="form-control form-control-sm select2" disabled
                                                style="width: 100%;">
                                                <option value="">Select Size Set</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small font-weight-bold mb-0">Color</label>
                                            <select id="dom_color" class="form-control form-control-sm select2" disabled
                                                style="width: 100%;">
                                                <option value="">Select Color</option>
                                            </select>
                                        </div>
                                        <input type="hidden" id="dom_pattern_id">
                                        <input type="hidden" id="dom_pattern_name">
                                        <input type="hidden" id="dom_fitting_id">
                                        <input type="hidden" id="dom_fitting_name">
                                        <div class="col-md-1 mt-2">
                                            <label class="small font-weight-bold mb-0">MRP</label>
                                            <input type="number" id="dom_mrp" class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="col-md-1 mt-2 d-none">
                                            <label class="small font-weight-bold mb-0">Sets/Box</label>
                                            <input type="number" id="dom_qty" class="form-control form-control-sm" value="1"
                                                title="Number of sets to pack in each box">
                                        </div>
                                        <div class="col-md-1 mt-2">
                                            <label class="small font-weight-bold mb-0">Boxes</label>
                                            <input type="number" id="dom_box_count" class="form-control form-control-sm"
                                                value="1" min="1" title="Number of boxes to add to plan">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small font-weight-bold mb-0">Store</label>
                                            <select id="domStore" class="form-control form-control-sm"
                                                onchange="updateDomRacks()">
                                                <option value="">Select</option>
                                                @foreach($storerooms as $store) <option value="{{ $store->id }}"
                                                data-racks="{{ $store->racks }}">{{ $store->name }}</option> @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="small font-weight-bold mb-0">Rack</label>
                                            <select id="dom_rack" class="form-control form-control-sm select2"
                                                style="width: 100%;">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-info btn-sm btn-block"
                                                id="btnSaveDomesticBox">Pack</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0 font-weight-bold text-secondary text-uppercase small">Boxes Plan</h6>
                                <button type="button" class="btn btn-link btn-sm text-danger"
                                    onclick="resetDomesticPlan()">Clear All</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover border bg-white" id="domesticTable">
                                    <thead class="bg-light">
                                        <tr class="small text-muted">
                                            <th>#</th>
                                            <th>Design</th>
                                            <th>Size Set</th>
                                            <th>Color</th>
                                            <th>Pattern</th>
                                            <th>Fitting</th>
                                            <th class="text-center d-none">Sets/Box</th>
                                            <th>Storage</th>
                                            <th style="width: 40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="domesticTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info px-5" onclick="submitDomesticPacking()">PROCESS ALL
                        BOXES</button>
                </div>
            </div>
        </div>
    </div>

    <!-- REWORK MODAL -->
    <div class="modal fade" id="reworkModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white p-3">
                    <h5 class="modal-title font-weight-bold mb-0"><i class="fas fa-tools mr-2"></i> Re-assign Pieces for
                        Rework</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body pb-0">
                    <div class="alert alert-warning py-2 mb-3">
                        <small><i class="fas fa-info-circle mr-1"></i> Use this to return defected pieces back to a previous
                            production unit for rework.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold small text-muted">TARGET STAGE</label>
                            <select id="reworkStage" class="form-control" onchange="updateReworkUnits()">
                                <option value="">Select Stage</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="font-weight-bold small text-muted">TARGET UNIT</label>
                            <select id="reworkUnit" class="form-control">
                                <option value="">Select Unit</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold small text-muted">SELECT ITEMS & QUANTITY</label>
                        <div class="table-responsive border rounded" style="max-height: 350px;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="py-2">Item Detail (Design/Color)</th>
                                        <th class="py-2">Size</th>
                                        <th class="py-2 text-center">Avl at Unit</th>
                                        <th class="py-2" style="width: 120px;">Rework Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="reworkItemsList">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="font-weight-bold small text-muted">REMARKS / DEFECT DETAILS</label>
                        <textarea id="reworkRemarks" class="form-control" rows="2"
                            placeholder="Describe why these pieces are being sent back..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-5 shadow-sm font-weight-bold"
                        onclick="submitReworkAssignment()">
                        <i class="fas fa-paper-plane mr-1"></i> ASSIGN REWORK
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let ORDER_ID = {{ $order->id ?? 'null' }};
            const SLIP_ID = {{ $slip->id }};
            let ORDER_ITEMS = @json(isset($order_sets) ? $order_sets->flatMap(fn($s) => $s->details_data ?? $s->details ?? []) : []);
            let ORDER_SETS = @json($order_sets ?? []);
            const PACKED_DATA = @json($packed_quantities ?? []);
            @php
                $mappedPacking = null;
                if ($packing) {
                    $mappedPacking = [
                        'id' => $packing->id,
                        'slip_id' => $packing->slip_id,
                        'status' => $packing->status,
                        'cartons' => $packing->cartons ? $packing->cartons->map(function($c) {
                            return [
                                'id' => $c->id,
                                'carton_no' => $c->carton_no,
                                'boxes' => $c->boxes ? $c->boxes->map(function($b) {
                                    $inv = $b->domesticInventory;
                                    $fb = ($b->items && $b->items->count() > 0 && $b->items[0]->detail && $b->items[0]->detail->orderProductSet) ? $b->items[0]->detail->orderProductSet : null;
                                    return [
                                        'id' => $b->id,
                                        'box_no' => $b->box_no,
                                        'domestic_inventory' => [
                                            'product' => ['design_number' => $inv->product->design_number ?? ($fb->product->design_number ?? 'N/A')],
                                            'size_set' => ['name' => $inv->sizeSet->name ?? ($fb->size_measurement->name ?? 'N/A')],
                                            'color' => ['name' => $inv->color->name ?? ($fb->colors->name ?? 'N/A')],
                                            'pattern' => ['name' => $inv->pattern->name ?? ($fb->master_design_pattern->name ?? '-')],
                                            'fitting' => ['name' => $inv->fitting->name ?? ($fb->master_product_fitting->name ?? '-')]
                                        ],
                                        'items' => $b->items ? $b->items->map(function($i) { return ['size_id' => $i->size_id, 'quantity' => $i->quantity]; })->toArray() : []
                                    ];
                                })->toArray() : []
                            ];
                        })->toArray() : [],
                        'boxes' => $packing->boxes ? $packing->boxes->map(function($b) {
                            $inv = $b->domesticInventory;
                            $fb = ($b->items && $b->items->count() > 0 && $b->items[0]->detail && $b->items[0]->detail->orderProductSet) ? $b->items[0]->detail->orderProductSet : null;
                            return [
                                'id' => $b->id,
                                'box_no' => $b->box_no,
                                'domestic_inventory' => [
                                    'product' => ['design_number' => $inv->product->design_number ?? ($fb->product->design_number ?? 'N/A')],
                                    'size_set' => ['name' => $inv->sizeSet->name ?? ($fb->size_measurement->name ?? 'N/A')],
                                    'color' => ['name' => $inv->color->name ?? ($fb->colors->name ?? 'N/A')],
                                    'pattern' => ['name' => $inv->pattern->name ?? ($fb->master_design_pattern->name ?? '-')],
                                    'fitting' => ['name' => $inv->fitting->name ?? ($fb->master_product_fitting->name ?? '-')]
                                ],
                                'items' => $b->items ? $b->items->map(function($i) { return ['size_id' => $i->size_id, 'quantity' => $i->quantity]; })->toArray() : []
                            ];
                        })->toArray() : []
                    ];
                }
            @endphp
            const EXISTING_PACKING = @json($mappedPacking);
            const UNIT_AVAILABLE = @json($unit_available ?? []);
            const CURRENT_UNIT_ID = {{ $slip->stage_master_unit_id ?? 'null' }};
            const ALL_STOREROOMS = @json($storerooms);
            let ORDER_TYPE = "{{ strtolower($order->order_type ?? '') }}";
            const DOMESTIC_MASTERS = @json($domestic_masters ?? []);
            const ALLOWED_SIZE_SET_IDS = @json($order ? $order->OrderProductSets->pluck('set_size')->unique()->toArray() : []);

            // --- CORPORATE MULTI-CARTON PLANNER ---
            function openMultiCartonPlanner() {
                if (!ORDER_ID) return;
                renderPlannerInventory();

                // Populate Range Design dropdown
                let uniqueDesigns = [...new Set(ORDER_SETS.map(s => s.design_number))];
                let $rangeDesign = $('#rangeDesign');
                $rangeDesign.html('<option value="">Select</option>');
                uniqueDesigns.forEach(d => {
                    $rangeDesign.append(`<option value="${d}">${d}</option>`);
                });

                $('#plannerTableBody').empty();
                $('#multiCartonPlannerModal').modal('show');
            }

            function updateRangeSizeSets() {
                let designId = $('#rangeDesign').val();
                let $sizeSet = $('#rangeSizeSet');
                $sizeSet.html('<option value="">Select</option>');

                if (designId) {
                    let setsForDesign = ORDER_SETS.filter(s => s.design_number == designId);
                    let uniqueSets = [];
                    setsForDesign.forEach(s => {
                        let sizeSetId = s.set_size;
                        if (!uniqueSets.some(us => us.id == sizeSetId)) {
                            let rawName = s.size_set_name || 'N/A';
                            let pcs = s.no_of_pcs ? ` (${s.no_of_pcs} Pcs)` : '';
                            uniqueSets.push({ id: sizeSetId, rawName: rawName, name: rawName + pcs });
                        }
                    });
                    uniqueSets.forEach(us => {
                        $sizeSet.append(`<option value="${us.id}" data-raw-name="${us.rawName}">${us.name}</option>`);
                    });
                }
                updateRangeColors();
            }

            function updateRangeColors() {
                let designId = $('#rangeDesign').val();
                let sizeSetId = $('#rangeSizeSet').val();
                let $color = $('#rangeColor');
                $color.html('<option value="">Select</option>');

                if (designId && sizeSetId) {
                    let filtered = ORDER_SETS.filter(s => s.design_number == designId && s.set_size == sizeSetId);
                    filtered.forEach(s => {
                        $color.append(`<option value="${s.id}">${s.color_name || 'N/A'}</option>`);
                    });
                }
                updateRangeTypeOptions();
            }

            function updateRangeTypeOptions() {
                let setId = $('#rangeColor').val();
                let type = $('#rangeType').val();
                let $sizeContainer = $('#rangeSizeContainer');
                let $sizeSelect = $('#rangeSize');
                let $qtyInput = $('#rangeQty');

                if (type === 'loose' && setId) {
                    $sizeContainer.removeClass('d-none');
                    $sizeSelect.html('<option value="">Select</option>');
                    ORDER_ITEMS.forEach(item => {
                        if (item.order_products_set_id == setId) {
                            $sizeSelect.append(`<option value="${item.id}" data-max="${item.unit_available_qty}">${item.size} (Avl: ${item.unit_available_qty})</option>`);
                        }
                    });
                    $qtyInput.removeAttr('max');
                } else if (type === 'set' && setId) {
                    $sizeContainer.addClass('d-none');
                    let sid = parseInt(setId);
                    let minAvailableSets = null;
                    ORDER_ITEMS.forEach(item => {
                        if (parseInt(item.order_products_set_id) === sid) {
                            let itemAvl = parseInt(item.unit_available_qty);
                            let perSet = parseFloat(item.qty_per_set) || 1;
                            let canMake = Math.floor(itemAvl / perSet);
                            if (minAvailableSets === null || canMake < minAvailableSets) minAvailableSets = canMake;
                        }
                    });
                    let maxSets = minAvailableSets !== null ? minAvailableSets : 0;
                    $qtyInput.attr('max', maxSets);
                    if (parseInt($qtyInput.val()) > maxSets) $qtyInput.val(maxSets);
                } else {
                    $sizeContainer.addClass('d-none');
                    $qtyInput.removeAttr('max');
                }
            }

            function validateUnitPackagingStock(input) {
                let maxAttr = input.getAttribute('max');
                if (maxAttr === null || maxAttr === '') return;

                let max = parseInt(maxAttr);
                let val = parseInt(input.value);

                if (!isNaN(max) && val > max) {
                    alert("Quantity cannot exceed available stock (" + max + ")");
                    input.value = max;
                }
                if (val < 0) input.value = 0;
            }

            $(document).on('change', '#rangeSize', function () {
                let max = $(this).find(':selected').data('max');
                if (max !== undefined) {
                    $('#rangeQty').attr('max', max);
                    if (parseInt($('#rangeQty').val()) > max) $('#rangeQty').val(max);
                } else {
                    $('#rangeQty').removeAttr('max');
                }
                updateRangeLiveRemainingQty();
            });

            $(document).on('change', '#rangeColor, #rangeType', function() {
                updateRangeLiveRemainingQty();
            });

            function updateRangeLiveRemainingQty() {
                let setId = $('#rangeColor').val();
                let type = $('#rangeType').val();
                let sizeId = $('#rangeSize').val();
                let $indicator = $('#rangeLiveRemaining');

                if (!setId) {
                    $indicator.addClass('d-none').text('');
                    return;
                }

                let initialAvailable = 0;

                if (type === 'set' || type === 'domestic') {
                    let sid = parseInt(setId);
                    let minSets = null;
                    ORDER_ITEMS.forEach(item => {
                        if (parseInt(item.order_products_set_id) === sid) {
                            let itemAvl = parseInt(item.unit_available_qty);
                            let perSet = parseFloat(item.qty_per_set) || 1;
                            let canMake = Math.floor(itemAvl / perSet);
                            if (minSets === null || canMake < minSets) minSets = canMake;
                        }
                    });
                    initialAvailable = minSets !== null ? minSets : 0;
                } else if (type === 'loose' && sizeId) {
                    let sid = parseInt(sizeId);
                    ORDER_ITEMS.forEach(item => {
                        if (parseInt(item.id) === sid) {
                            initialAvailable = parseInt(item.unit_available_qty);
                        }
                    });
                } else {
                    $indicator.addClass('d-none').text('');
                    return;
                }

                let plannedQty = 0;
                $('#plannerTableBody .planner-row').each(function() {
                    let rType = $(this).find('.planner-type').val();
                    let rSetId = $(this).find('.planner-color').val();
                    let rSizeId = $(this).find('.planner-content-id').val();
                    let rQty = parseInt($(this).find('.planner-qty').val()) || 0;

                    if ((type === 'set' || type === 'domestic') && (rType === 'set' || rType === 'domestic') && rSetId == setId) {
                        plannedQty += rQty;
                    } else if (type === 'loose' && rType === 'loose' && rSizeId == sizeId) {
                        plannedQty += rQty;
                    }
                });

                let remaining = initialAvailable - plannedQty;
                let unitLabel = (type === 'set' || type === 'domestic') ? 'Boxes Left' : 'Pcs Left';
                
                $indicator.removeClass('d-none badge-warning badge-success badge-danger');
                $indicator.text(remaining + ' ' + unitLabel);
                
                if (remaining <= 0) {
                    $indicator.addClass('badge-danger');
                } else {
                    $indicator.addClass('badge-warning');
                }
            }

        window.selectedGlobalLots = [];
        function handleLotSelectionChange(clickedElement) {
            if (clickedElement && $(clickedElement).is(':checked')) {
                let newDesign = String($(clickedElement).data('design'));
                let newSizeSet = String($(clickedElement).data('size-set'));

                $('.planner-lot-checkbox:checked').each(function() {
                    if (this !== clickedElement) {
                        let d = String($(this).data('design'));
                        let s = String($(this).data('size-set'));
                        if (d !== newDesign || s !== newSizeSet) {
                            $(this).prop('checked', false);
                        }
                    }
                });
            }

            window.selectedGlobalLots = [];
            let firstDesign = null;
            let firstSizeSet = null;

            $('.planner-lot-checkbox:checked').each(function() {
                let d = String($(this).data('design'));
                let s = String($(this).data('size-set'));
                
                if (!firstDesign) {
                    firstDesign = d;
                    firstSizeSet = s;
                }
                window.selectedGlobalLots.push($(this).val());
            });

            if (firstDesign) {
                $('#rangeDesign').val(firstDesign).trigger('change');
                setTimeout(() => {
                    let sizeSetVal = "";
                    $('#rangeSizeSet option').each(function() {
                        let raw = $(this).data('raw-name');
                        if (raw !== undefined && String(raw).trim() === String(firstSizeSet).trim()) {
                            sizeSetVal = $(this).val();
                        } else if (String($(this).text()).trim() === String(firstSizeSet).trim()) {
                            sizeSetVal = $(this).val();
                        }
                    });
                    if (sizeSetVal) {
                        $('#rangeSizeSet').val(sizeSetVal).trigger('change');
                    }
                }, 200);
            }

            if (window.selectedGlobalLots.length > 0) {
                let summaryLots = [];
                let sumTotal = 0;
                let sumRem = 0;
                let noOfPcs = 1;

                $('.planner-lot-checkbox:checked').each(function() {
                    summaryLots.push($(this).val());
                    sumTotal += parseFloat($(this).data('qty')) || 0;
                    sumRem += parseFloat($(this).data('rem')) || 0;
                });
                
                let sumPacked = sumTotal - sumRem;

                if (firstDesign && firstSizeSet) {
                    let setMatch = ORDER_SETS.find(s => s.design_number == firstDesign && (s.size_set_name == firstSizeSet || (s.size_measurement && s.size_measurement.name == firstSizeSet)));
                    if (setMatch && setMatch.no_of_pcs) {
                        noOfPcs = parseFloat(setMatch.no_of_pcs);
                    }
                }

                let totalBoxes = Math.floor(sumTotal / noOfPcs);
                let remBoxes = Math.floor(sumRem / noOfPcs);
                let packedBoxes = Math.floor(sumPacked / noOfPcs);

                $('#summaryLotNumbers').text(summaryLots.join(', '));
                $('#summaryDesign').text(firstDesign ? 'Design: ' + firstDesign : '');
                $('#summarySizeSet').text(firstSizeSet ? `Size: ${firstSizeSet} (${noOfPcs} Pcs)` : '');
                $('#summaryTotalQty').text(`${sumTotal} Pcs (${totalBoxes} Boxes)`);
                $('#summaryPacked').text(`${sumPacked} Pcs (${packedBoxes} Boxes)`);
                $('#summaryAvailable').text(`${sumRem} Pcs (${remBoxes} Boxes)`);
                
                $('#selectedLotsSummary').removeClass('d-none');
            } else {
                $('#selectedLotsSummary').addClass('d-none');
            }
        }

        function updateRangeRacks() {
                let storeSelect = document.getElementById('rangeStore');
                let rackSelect = document.getElementById('rangeRack');
                let selectedOption = storeSelect.options[storeSelect.selectedIndex];
                rackSelect.innerHTML = '<option value="">Select</option>';
                if ($(selectedOption).val()) {
                    let racks = $(selectedOption).data('racks') || [];
                    racks.forEach(rack => {
                        let option = document.createElement('option');
                        option.value = rack.id;
                        option.text = rack.name;
                        rackSelect.add(option);
                    });
                }
            }

            function addRangeToPlanner() {
                let start = parseInt($('#rangeStart').val());
                let end = parseInt($('#rangeEnd').val());
                let setId = $('#rangeColor').val();
                let designId = $('#rangeDesign').val();
                let type = $('#rangeType').val();
                let sizeId = $('#rangeSize').val();
                let qty = parseInt($('#rangeQty').val()) || 1;
                let mrp = $('#rangeMrp').val();
                let price = $('#rangePrice').val();
                let barcode = $('#rangeBarcode').val();
                let storeId = $('#rangeStore').val();
                let rackId = $('#rangeRack').val();

                if (!setId || isNaN(start) || isNaN(end) || !storeId || !rackId) {
                    alert("Please fill all details including store and rack.");
                    return;
                }

                for (let i = start; i <= end; i++) {
                    addPlannerRow({
                        carton_no: i,
                        set_id: setId,
                        design: designId,
                        type: type,
                        content_id: type === 'set' ? setId : sizeId,
                        qty: qty,
                        mrp: mrp,
                        price: price,
                        barcode: barcode,
                        store_id: storeId,
                        rack_id: rackId
                    });
                }
            }

            function renderPlannerInventory() {
                let $list = $('#plannerInventoryList');
                $list.html('');
                if (typeof UNIT_LOTS !== 'undefined' && UNIT_LOTS.length > 0) {
                    $list.append('<div class="mb-3"><strong class="small text-dark font-weight-bold">AVAILABLE LOTS</strong></div>');
                    UNIT_LOTS.forEach(lot => {
                        let isChecked = window.selectedGlobalLots && window.selectedGlobalLots.includes(lot.lot_no) ? 'checked' : '';
                        $list.append(`<div class="mb-2 pl-2 border-left border-info d-flex align-items-start">
                            <div class="mr-2 mt-1">
                                <input type="checkbox" class="planner-lot-checkbox" value="${lot.lot_no}" data-design="${lot.design_number}" data-size-set="${lot.size_set_name}" data-qty="${lot.quantity}" data-rem="${lot.remaining_quantity}" onchange="handleLotSelectionChange(this)" ${isChecked}>
                            </div>
                            <div>
                                <small class="d-block text-truncate font-weight-bold" title="${lot.design_number}">Lot ${lot.lot_no} (#${lot.design_number}) [${lot.size_set_name || 'N/A'}]</small>
                                <span class="badge badge-light border text-info small">Qty: ${lot.quantity}</span>
                                <span class="badge badge-light border text-muted small ml-1">Rem: ${lot.remaining_quantity}</span>
                            </div>
                        </div>`);
                    });
                }
                if (ORDER_SETS && ORDER_SETS.length > 0) {
                    $list.append('<div class="mb-3"><strong class="small text-dark font-weight-bold">SETS</strong></div>');
                    ORDER_SETS.forEach((set, idx) => {
                        let minSets = null;
                        let hasDetails = false;
                        ORDER_ITEMS.forEach(item => {
                            if (item.order_products_set_id == set.id) {
                                hasDetails = true;
                                let avl = parseInt(item.unit_available_qty) || 0;
                                let perSet = parseFloat(item.qty_per_set) || 1;
                                let canMake = Math.floor(avl / perSet);
                                if (minSets === null || canMake < minSets) {
                                    minSets = canMake;
                                }
                            }
                        });
                        let setsCount = hasDetails ? (minSets ?? 0) : 0;
                        $list.append(`<div class="mb-2 pl-2 border-left border-primary"><small class="d-block text-truncate font-weight-bold" title="${set.design_number}">${set.design_number} (${set.size_set_name || 'N/A'})</small><span class="badge badge-light border text-primary small">Full Boxes: ${setsCount}</span></div>`);
                    });
                }
                $list.append('<div class="mt-3 mb-3"><strong class="small text-dark font-weight-bold">LOOSE</strong></div>');
                ORDER_ITEMS.forEach(item => {
                    let avl = parseInt(item.unit_available_qty) || 0;
                    if (avl > 0) {
                        $list.append(`<div class="mb-2 pl-2 border-left border-success"><small class="d-block text-truncate">${item.size} (${item.design_number})</small><span class="badge badge-light border text-success small">Avl: ${avl}</span></div>`);
                    }
                });
            }

            function addPlannerRow(data = null) {
                let highest = 0;
                $('#plannerTableBody .planner-carton-no').each(function () {
                    let val = parseInt($(this).val()) || 0;
                    if (val > highest) highest = val;
                });
                let nextCartonNo = data ? data.carton_no : (highest > 0 ? highest + 1 : '');

                let uniqueDesigns = [...new Set(ORDER_SETS.map(s => s.design_number))];

                let html = `
                                            <tr class="planner-row">
                                                <td><input type="text" class="form-control form-control-sm planner-carton-no font-weight-bold" value="${nextCartonNo}"></td>
                                                <td>
                                                    <select class="form-control form-control-sm planner-design" style="min-width: 100px;" onchange="updateRowSizeSets(this)">
                                                        <option value="">Select</option>
                                                        ${uniqueDesigns.map(d => `<option value="${d}" ${data && ORDER_SETS.find(s => s.id == data.set_id)?.design_number == d ? 'selected' : ''}>${d}</option>`).join('')}
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm planner-size-set" style="min-width: 100px;" onchange="updateRowColors(this)">
                                                        <option value="">Select</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm planner-color" style="min-width: 100px;" onchange="updateRowTypeOptions(this)">
                                                        <option value="">Select</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm planner-type mb-1" onchange="updateRowTypeOptions(this)">
                                                        <option value="set" ${data && data.type == 'set' ? 'selected' : ''}>Box (Set)</option>
                                                        <option value="loose" ${data && data.type == 'loose' ? 'selected' : ''}>Loose</option>
                                                    </select>
                                                    <select class="form-control form-control-sm planner-content-id d-none" style="min-width: 80px;"></select>
                                                </td>
                                                <td><input type="number" class="form-control form-control-sm planner-qty" value="${data ? data.qty : 1}" min="1"></td>
                                                <td><input type="number" class="form-control form-control-sm planner-mrp" value="${data ? data.mrp : ''}" step="0.01"></td>
                                                <td><input type="number" class="form-control form-control-sm planner-price" value="${data ? data.price : ''}" step="0.01"></td>
                                                <td><input type="text" class="form-control form-control-sm planner-barcode" value="${data ? data.barcode : ''}"></td>
                                                <td>
                                                    <select class="form-control form-control-sm planner-storeroom mb-1" onchange="updatePlannerRackSelect(this)">
                                                        <option value="">Store</option>
                                                        ${ALL_STOREROOMS.map(s => `<option value="${s.id}" ${data && data.store_id == s.id ? 'selected' : ''} data-racks='${JSON.stringify(s.racks)}'>${s.name}</option>`).join('')}
                                                    </select>
                                                    <select class="form-control form-control-sm planner-rack" style="min-width: 80px;"><option value="">Rack</option></select>
                                                </td>
                                                <td class="text-right">
                                                    <button type="button" class="btn btn-link text-danger btn-sm p-0" onclick="$(this).closest('tr').remove(); updateCartonPlanTotalQty();"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>`;

                let $row = $(html);
                $('#plannerTableBody').append($row);

                // Initialize cascading dropdowns for the row
                if (data) {
                    let $designSelect = $row.find('.planner-design');
                    let setId = data.set_id;
                    let set = ORDER_SETS.find(s => s.id == setId);

                    updateRowSizeSets($designSelect[0]);
                    if (set) {
                        $row.find('.planner-size-set').val(set.set_size);
                        updateRowColors($row.find('.planner-size-set')[0]);
                        $row.find('.planner-color').val(setId);

                        updateRowTypeOptions($row.find('.planner-color')[0], data.content_id);
                    }

                    let $storeSelect = $row.find('.planner-storeroom');
                    updatePlannerRackSelect($storeSelect[0]);
                    $row.find('.planner-rack').val(data.rack_id);
                }

                updateCartonPlanTotalQty();
            }

            function updateCartonPlanTotalQty() {
                let total = 0;
                $('#plannerTableBody .planner-qty').each(function() {
                    let qty = parseInt($(this).val()) || 0;
                    let $row = $(this).closest('tr');
                    let type = $row.find('.planner-type').val();
                    let setId = $row.find('.planner-color').val();
                    
                    if (type === 'set' && setId) {
                        let set = ORDER_SETS.find(s => s.id == setId);
                        let pcsPerSet = set && set.no_of_pcs ? parseInt(set.no_of_pcs) : 7;
                        total += (qty * pcsPerSet);
                    } else {
                        total += qty;
                    }
                });
                $('#cartonPlanTotalQty').text('Total Pcs: ' + total);
                updateRangeLiveRemainingQty();
            }

            $(document).on('keyup change', '.planner-qty', function() {
                updateCartonPlanTotalQty();
            });

            function updateRowSizeSets(el) {
                let $row = $(el).closest('tr');
                let productId = $(el).val();
                let $sizeSet = $row.find('.planner-size-set');
                $sizeSet.html('<option value="">Select</option>');

                if (productId) {
                    let setsForDesign = ORDER_SETS.filter(s => s.design_number == productId);
                    let uniqueSets = [];
                    setsForDesign.forEach(s => {
                        let sizeSetId = s.set_size;
                        if (!uniqueSets.some(us => us.id == sizeSetId)) {
                            uniqueSets.push({ id: sizeSetId, name: s.size_set_name || 'N/A' });
                        }
                    });
                    uniqueSets.forEach(us => {
                        $sizeSet.append(`<option value="${us.id}">${us.name}</option>`);
                    });
                }
                updateRowColors($sizeSet[0]);
            }

            function updateRowColors(el) {
                let $row = $(el).closest('tr');
                let designId = $row.find('.planner-design').val();
                let sizeSetId = $(el).val();
                let $color = $row.find('.planner-color');
                $color.html('<option value="">Select</option>');

                if (designId && sizeSetId) {
                    let filtered = ORDER_SETS.filter(s => s.design_number == designId && s.set_size == sizeSetId);
                    filtered.forEach(s => {
                        $color.append(`<option value="${s.id}">${s.color_name || 'N/A'}</option>`);
                    });
                }
                updateRowTypeOptions($color[0]);
            }

            function updateRowTypeOptions(el, selectedContentId = null) {
                let $row = $(el).closest('tr');
                let setId = $row.find('.planner-color').val();
                let type = $row.find('.planner-type').val();
                let $contentSelect = $row.find('.planner-content-id');

                $contentSelect.html('').addClass('d-none');

                if (type === 'loose' && setId) {
                    $contentSelect.removeClass('d-none');
                    ORDER_ITEMS.forEach(item => {
                        if (item.order_products_set_id == setId) {
                            $contentSelect.append(`<option value="${item.id}" ${selectedContentId == item.id ? 'selected' : ''}>${item.size}</option>`);
                        }
                    });
                } else if (type === 'set' && setId) {
                    $contentSelect.append(`<option value="${setId}" selected>${setId}</option>`);
                }
                updateCartonPlanTotalQty();
            }

            function updatePlannerRackSelect(selectEl) {
                let $row = $(selectEl).closest('tr');
                let $rackSelect = $row.find('.planner-rack');
                let $selectedOption = $(selectEl).find('option:selected');
                $rackSelect.html('<option value="">Rack</option>');
                if ($selectedOption.val()) {
                    let racks = $selectedOption.data('racks') || [];
                    racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }

            function submitMultiCartonPlan() {
                let plan = [];
                let error = null;

                $('#plannerTableBody tr').each(function () {
                    let $row = $(this);
                    let cartonNo = $row.find('.planner-carton-no').val();
                    let setId = $row.find('.planner-color').val();
                    let type = $row.find('.planner-type').val();
                    let contentId = type === 'set' ? setId : $row.find('.planner-content-id').val();
                    let qty = parseInt($row.find('.planner-qty').val()) || 0;
                    let mrp = $row.find('.planner-mrp').val();
                    let price = $row.find('.planner-price').val();
                    let barcode = $row.find('.planner-barcode').val();
                    let rackId = $row.find('.planner-rack').val();

                    if (!cartonNo || !setId || !rackId || (type === 'loose' && !contentId) || qty <= 0) {
                        error = "Incomplete data in one or more rows.";
                        return false;
                    }

                    plan.push({
                        carton_no: cartonNo,
                        rack_id: rackId,
                        type: type,
                        content_id: contentId,
                        quantity: qty,
                        mrp: mrp,
                        price: price,
                        barcode: barcode,
                        selected_lots: window.selectedGlobalLots || []
                    });
                });

                if (error) { alert(error); return; }
                if (plan.length === 0) { alert("Add at least one carton to the plan."); return; }

                let $btn = $('button[onclick="submitMultiCartonPlan()"]');
                let originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

                $.ajax({
                    url: "{{ route('admin.packing.saveMultiCartonPlan') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        slip_id: SLIP_ID,
                        order_id: ORDER_ID,
                        plan: plan
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert("Error: " + response.message);
                            $btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function () {
                        alert("Something went wrong on the server.");
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }



            function deleteOutflow(id) {
                if (!confirm("Are you sure you want to delete this outflow entry and revert pieces to stock?")) return;
                let url = "{{ route('admin.packing.deleteOutflow', ':id') }}".replace(':id', id);
                $.post(url, { _token: "{{ csrf_token() }}" }, function (res) {
                    if (res.status === 'success') { location.reload(); }
                    else { alert("Error: " + res.message); }
                });
            }

            function deleteRework(id) {
                if (!confirm("Are you sure you want to delete this rework entry and revert pieces to stock?")) return;
                let url = "{{ route('admin.packing.deleteRework', ':id') }}".replace(':id', id);
                $.post(url, { _token: "{{ csrf_token() }}" }, function (res) {
                    if (res.status === 'success') { location.reload(); }
                    else { alert("Error: " + res.message); }
                });
            }



            // Structure State
            let packedStructure = {
                cartons: EXISTING_PACKING ? EXISTING_PACKING.cartons : [],
                boxes: EXISTING_PACKING ? EXISTING_PACKING.boxes : [] // Unpacked boxes
            };

            function deleteDomesticBox(id, event) {
                if (event) event.stopPropagation();
                if (!confirm("Are you sure you want to delete this domestic box? This will restore pieces back to unit stock.")) return;

                $.ajax({
                    url: "{{ route('admin.packing.deleteDomesticBox', ':id') }}".replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        slip_id: SLIP_ID
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            location.reload();
                        } else {
                            toastr.error(res.message);
                        }
                    }
                });
            }

            $(document).ready(function () {
                if (ORDER_ID) {
                    renderAvailableItems();
                }
                renderStructure();

                // Initialize Select2 with modal focus support
                $('.select2').each(function () {
                    let modal = $(this).closest('.modal');
                    $(this).select2({
                        dropdownParent: modal.length ? modal : null
                    });
                });

                // Handle Order Selection
                $('#orderSelect').on('change', function () {
                    let orderId = $(this).val();
                    let orderType = $(this).find(':selected').data('type');

                    if (orderId) {
                        if (orderType !== 'domestic') {
                            window.location.href = "{{ route('admin.packing.process', $slip->id) }}?order_id=" + orderId;
                            return;
                        }
                        // If it's already domestic and we are on domestic page, just reload with new order_id
                        window.location.href = "{{ route('admin.packing.processDomestic', $slip->id) }}?order_id=" + orderId;
                    }
                });

                $('#carton_no').on('blur', function () {
                    let cartonNo = $(this).val().trim();

                    if (cartonNo === '') return;

                    $.ajax({
                        url: "{{ route('admin.packing.check-carton-no') }}",
                        type: 'get',
                        cache: false,
                        data: {
                            carton_no: cartonNo,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (res) {
                            // console.log
                            if (res.exists) {
                                alert('Carton number already exists!');
                                $('#carton_no').val('').focus();
                            }
                        },
                        error: function () {
                            alert('Something went wrong while checking carton number');
                        }
                    });
                });


                $('#openFileBtn').on('click', function () {
                    window.open('', '_blank');
                });

            });

            function fetchOrderDetails(orderId) {
                $('#available-items-list').html('<li class="list-group-item text-muted text-center">Loading items...</li>');

                $.ajax({
                    url: "{{ route('admin.packing.orderDeps', '') }}/" + orderId,
                    type: 'GET',
                    data: { unit_id: CURRENT_UNIT_ID, slip_id: SLIP_ID },
                    cache: false,
                    success: function (response) {
                    if (response.status === 'success') {
                        ORDER_ID = orderId;
                        ORDER_ITEMS = response.items || [];
                        ORDER_SETS = response.sets || [];
                        ORDER_TYPE = (response.order && response.order.order_type) ? response.order.order_type.toLowerCase() : "";

                        // Update UI mode
                        if (ORDER_TYPE === 'domestic') {
                            $('#btnDomesticPacking').removeClass('d-none').show();
                            $('#btnCorporatePacking').hide();
                        } else {
                            $('#btnCorporatePacking').removeClass('d-none').show();
                            $('#btnDomesticPacking').hide();
                        }

                        // Restore existing packing session if found
                        if (response.packing) {
                            packedStructure.cartons = response.packing.cartons || [];
                            packedStructure.boxes = response.packing.boxes || [];
                        } else {
                            packedStructure.cartons = [];
                            packedStructure.boxes = [];
                        }

                        renderAvailableItems();
                        renderStructure();
                        disableActions(false);
                        if (response.order && response.order.corporate_order_file) {

                            let fileUrl = response.order.corporate_order_file;

                            // If backend sends only filename
                            if (!fileUrl.startsWith('http')) {
                                fileUrl = '/assets/products/' + fileUrl;
                            }

                            $('#fileLink').attr('href', fileUrl).removeClass('d-none').show();

                        } else {
                            // No file available
                            $('#fileLink').hide();
                        }
                    } else {
                        alert("Failed to load order details.");
                    }
                }
                });
            }

            function disableActions(disable) {
                $('#btnCreateCarton, #btnBulkPacking, #btnFinalize, #btnCreateFirstCarton').prop('disabled', disable);
            }

            function renderStructure() {
                let html = '';

                // Aggregating all boxes for domestic display (both unpacked and in cartons)
                let allBoxes = [...(packedStructure.boxes || [])];
                if (packedStructure.cartons && packedStructure.cartons.length > 0) {
                    packedStructure.cartons.forEach(carton => {
                        if (carton.boxes && carton.boxes.length > 0) {
                            allBoxes.push(...carton.boxes);
                        }
                    });
                }

                // In Domestic Packing, we ONLY show Boxes directly. No Cartons.
                if (allBoxes.length > 0) {
                    html += `<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                        <h5 class="mb-0 text-dark font-weight-bold" style="letter-spacing: 0.5px;"><i class="fas fa-boxes mr-2 text-primary"></i>PACKED DOMESTIC BOXES</h5>
                                        <div class="d-flex align-items-center">
                                             <a href="{{ route('admin.packing.downloadAllDomesticBarcode', $slip->id) }}" class="btn btn-primary btn-sm mr-3 shadow-sm" style="border-radius: 20px;">
                                                <i class="fas fa-barcode mr-1"></i> Download Barcode
                                             </a>
                                             <span class="badge badge-primary badge-pill px-3 py-1">${allBoxes.length} Total Boxes</span>
                                        </div>
                                    </div>`;

                    html += `<div class="row">`;
                    allBoxes.forEach(box => {
                        let inv = box.domestic_inventory;
                        let fallback = (box.items && box.items.length > 0 && box.items[0].detail?.order_product_set) ? box.items[0].detail.order_product_set : null;

                        let design = inv?.product?.design_number || fallback?.product?.design_number || 'N/A';
                        let sizeSet = inv?.size_set?.name || fallback?.size_measurement?.name || 'N/A';
                        let color = inv?.color?.name || fallback?.colors?.name || 'N/A';
                        let pattern = inv?.pattern?.name || fallback?.master_design_pattern?.name || '-';
                        let fitting = inv?.fitting?.name || fallback?.master_product_fitting?.name || '-';

                        html += `
                                <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; border-top: 3px solid #17a2b8 !important; background: #ffffff;">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <span class="small font-weight-bold text-dark"><i class="fas fa-box text-info mr-1"></i>#${box.box_no}</span>
                                                <button class="btn btn-link text-danger btn-xs p-0" onclick="deleteDomesticBox(${box.id}, event)" title="Delete Box">
                                                    <i class="fas fa-trash-alt" style="font-size: 10px;"></i>
                                                </button>
                                            </div>

                                            <div class="box-details-mini" style="font-size: 10.5px; line-height: 1.4;">
                                                <div class="mb-1 d-flex justify-content-between">
                                                    <span class="text-muted">Design:</span>
                                                    <span class="font-weight-bold text-truncate ml-1" style="max-width: 80px;">${design}</span>
                                                </div>
                                                <div class="mb-1 d-flex justify-content-between">
                                                    <span class="text-muted">Size Set:</span>
                                                    <span class="text-dark ml-1">${sizeSet}</span>
                                                </div>
                                                <div class="mb-1 d-flex justify-content-between">
                                                    <span class="text-muted">Color:</span>
                                                    <span class="text-dark ml-1">${color}</span>
                                                </div>
                                                <div class="mb-1 d-flex justify-content-between border-top pt-1 mt-1">
                                                    <span class="text-muted small">Patt/Fit:</span>
                                                    <span class="text-muted small ml-1 text-truncate" style="max-width: 70px;">
                                                        ${pattern}/${fitting}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                    });
                    html += `</div>`;
                }

                if (html === '') {
                    html = `<div class="text-center py-5 border rounded bg-light" style="border-style: dashed !important; border-width: 2px !important;">
                                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4816154-4017688.png" style="width: 120px; opacity: 0.6;" class="mb-3">
                                        <h6 class="text-muted">No boxes created yet for this domestic order</h6>
                                        <button class="btn btn-info btn-sm mt-3 px-4 shadow-sm" style="border-radius: 20px;" onclick="openDomesticPackingModal()">
                                            <i class="fas fa-box mr-1"></i> Start Domestic Packing
                                        </button>
                                    </div>`;
                }

                $('#packing-structure-area').html(html);
            }

            function renderAvailableItems() {
                let html = '';
                let modalSetsHtml = '';

                // Render SETS in Left Panel
                if (ORDER_SETS && ORDER_SETS.length > 0) {
                    ORDER_SETS.forEach((set, index) => {

                        let remainingSets = set.set_quantity - set.packed_sets;
                        if (remainingSets < 0) remainingSets = 0;
                        let minRemaining = null; // important
                        ORDER_ITEMS.forEach(item => {

                            if (item.order_products_set_id == set.id) {

                                let packed = parseInt(item.packed_qty) || 0;
                                let total = parseInt(item.total_quantity) || 0;
                                let remaining = total - packed;
                                // console.log(item.size);
                                // console.log(remaining);
                                if (remaining < 0) remaining = 0;

                                if (minRemaining === null || remaining < minRemaining) {
                                    minRemaining = remaining;
                                }
                            }
                        });
                        // console.log(minRemaining);
                        // Final remaining sets = min remaining of all sizes
                        remainingSets = minRemaining ?? 0;

                        // Calculate COMPLETE sets available at unit based on the size with the LEAST stock
                        let unitAvailableSets = null;
                        let details = set.details_data || set.details;
                        if (details) {
                            details.forEach(item => {
                                let avl = parseInt(item.unit_available_qty) || 0;
                                let perSet = parseInt(item.qty_per_set) || 1; // Number of pieces of this size in one set
                                let possibleFromThisSize = Math.floor(avl / perSet);
                                if (unitAvailableSets === null || possibleFromThisSize < unitAvailableSets) {
                                    unitAvailableSets = possibleFromThisSize;
                                }
                            });
                        }
                        unitAvailableSets = unitAvailableSets ?? 0;

                        let colorName = set.color_name || 'N/A';
                        let sizeSetTitle = set.size_set_name || '';

                        html += `
                                                        <li class="list-group-item bg-light border-bottom-0 pb-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong class="text-dark">#${set.design_number}</strong>
                                                                    <span class="badge badge-light border text-muted small ml-1 px-2">${colorName}</span>
                                                                    <small class="text-secondary ml-1">[${sizeSetTitle}]</small>
                                                                </div>
                                                                <div>
                                                                    <span class="badge ${unitAvailableSets > 0 ? 'bg-primary' : 'bg-warning'} shadow-sm p-2" style="border-radius: 8px;">
                                                                        <i class="fas fa-boxes mr-1"></i> Full Sets: ${unitAvailableSets}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="mt-1 d-flex justify-content-between align-items-center">
                                                                <small class="text-muted">Set #${index + 1} (Target: ${set.set_quantity})</small>
                                                                <span class="badge bg-secondary-light border text-dark font-weight-normal small px-2">Total Rem: ${remainingSets}</span>
                                                            </div>
                                                        </li>`;

                        // Details
                        if (set.details_data || set.details) {
                            let details = set.details_data || set.details;
                            details.forEach(item => {
                                let packed = parseInt(item.packed_qty) || 0;
                                let total = parseInt(item.total_quantity);
                                let remaining = total - packed;
                                let availableAtUnit = parseInt(item.unit_available_qty) || 0;
                                let badgeClass = availableAtUnit === 0 ? 'bg-warning' : 'bg-info';

                                html += `<li class="list-group-item d-flex justify-content-between align-items-center ps-4 py-1">
                                                                    <small class="text-dark">Size: <strong>${item.size}</strong> <span class="text-muted ml-1">(${set.design_number})</span></small>
                                                                    <span>
                                                                        <small class="text-muted mr-2">Total Rem: ${remaining}</small>
                                                                        <span class="badge ${badgeClass} badge-pill px-2">At Unit: ${availableAtUnit}</span> 
                                                                    </span>
                                                                </li>`;
                            });
                        }
                        // console.log(set);
                        // Modal Option for this Set
                        if (remainingSets > 0) {
                            let compositionText = (set.details_data || set.details).map(d => `${d.size}(${d.qty_per_set} pcs)`).join(', ');
                            modalSetsHtml += `
                                                                                                                                                                                                                         <div class="card mb-2 p-2 border-left-primary">
                                                                                                                                                                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                                                                                                                                                                <div>
                                                                          <strong>Set #${index + 1}</strong> <small class="text-muted">(${compositionText}), <br>Barcode -${set.bar_code}, Design No - ${set.design_number}, Colour - ${set?.colors?.name ?? ''}, </small><br>
                                                                          <small class="text-primary">Available at Unit: ${unitAvailableSets}</small> | <small class="text-muted">Total Rem: ${remainingSets}</small>
                                                                     </div>
                                                                     <div class="d-flex align-items-center">
                                                                         <input type="number" class="form-control form-control-sm set-pack-qty mr-2" style="width: 70px;" placeholder="Qty" max="${unitAvailableSets}" min="0" data-set-id="${set.id}">
                                                                         <span>Sets</span>
                                                                     </div>
                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                         </div>`;
                        }
                    });
                }
                // Create relation map
                const orderSetMap = ORDER_SETS.reduce((acc, set) => {
                    acc[set.id] = set;
                    return acc;
                }, {});
                // Fallback for flat items if no sets (Legacy)
                if ((!ORDER_SETS || ORDER_SETS.length === 0) && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let packed = parseInt(item.packed_qty) || 0;
                        let total = parseInt(item.total_quantity);
                        let remaining = total - packed;
                        let badgeClass = remaining === 0 ? 'bg-success' : 'bg-primary';

                        html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                                                                                                                                                                                                        Size: ${item.size}
                                                                                                                                                                                                                        <span class="badge ${badgeClass} badge-pill">${remaining} / ${total}</span>
                                                                                                                                                                                                                    </li>`;
                    });
                }

                if (html === '') {
                    html = '<li class="list-group-item text-muted text-center">No items found.</li>';
                }

                $('#available-items-list').html(html);
                $('#cartonSetsContainer').html(modalSetsHtml || '<p class="text-muted text-center py-2">No full sets available to pack.</p>');

                // Also populate loose items table (optional/fallback)
                let modalHtml = '';

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let packed = parseInt(item.packed_qty) || 0;
                        let total = parseInt(item.total_quantity);
                        let remaining = total - packed;
                        if (remaining > 0) {
                            const setData = orderSetMap[item.order_products_set_id];
                            let availableAtUnit = parseInt(item.unit_available_qty) || 0;
                            modalHtml += `<tr>
                                                                                                                                                                                                                            <td>${setData ? setData.bar_code : '-'}</td>
                                                                                                                                                                                                                            <td>${setData ? setData.design_number : '-'}</td>
                                                                                                                                                                                                                            <td>${setData && setData.colors ? setData.colors.name : '-'}</td>
                                                                                                                                                                                                                             <td>${item.size}</td>
                                                                                                                                                                                                                             <td>
                                                                                                                                                                                                                                <span class="text-primary">${availableAtUnit} at Unit</span><br>
                                                                                                                                                                                                                                <small class="text-muted">${remaining} total rem</small>
                                                                                                                                                                                                                             </td>
                                                                                                                                                                                                                             <td><input type="number" class="form-control form-control-sm item-pack-qty" data-size-id="${item.id}" max="${availableAtUnit}" min="0"></td>
                                                                                                                                                                                                                        </tr>`;
                        }
                    });
                }
                $('#cartonItemsTable').html(modalHtml);
                if (!modalSetsHtml || modalSetsHtml.trim() === '') {
                    switchPackTab('loose');
                }
            }

            function openCreateBoxModal() {
                if (!ORDER_ID) return;
                $('#createBoxModal').modal('show');
            }

            function openCreateCartonModal() {
                if (!ORDER_ID) return;

                // Populate Unpacked Boxes list
                let html = '';
                if (packedStructure.boxes.length > 0) {
                    packedStructure.boxes.forEach(box => {
                        html += `
                                                                                                                                                                                                                    <div class="form-check">
                                                                                                                                                                                                                        <input class="form-check-input box-select" type="checkbox" value="${box.id}" id="boxCheck${box.id}">
                                                                                                                                                                                                                        <label class="form-check-label" for="boxCheck${box.id}">
                                                                                                                                                                                                                            Box #${box.box_no}
                                                                                                                                                                                                                        </label>
                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                    `;
                    });
                } else {
                    html = '<p class="text-muted">No unpacked boxes available.</p>';
                }
                $('#unpackedBoxesList').html(html);

                $('#unpackedBoxesList').html(html);

                // Smart Tab Selection: If no sets, default to Loose Items
                if (ORDER_SETS && ORDER_SETS.length > 0) {
                    switchPackTab('sets');
                } else {
                    switchPackTab('loose');
                }

                $('#createCartonModal').modal('show');
            }

            function updateRackSelect() {
                let storeSelect = document.getElementById('storeroomSelect');
                let rackSelect = document.getElementById('rackSelect');
                let selectedOption = storeSelect.options[storeSelect.selectedIndex];

                rackSelect.innerHTML = '<option value="">Select Rack</option>';

                if (selectedOption.value) {
                    let racks = JSON.parse(selectedOption.getAttribute('data-racks'));
                    racks.forEach(rack => {
                        let option = document.createElement('option');
                        option.value = rack.id;
                        option.text = rack.name;
                        rackSelect.add(option);
                    });
                }
            }

            function updateBulkRackSelect() {
                let storeSelect = document.getElementById('bulkStoreroomSelect');
                let rackSelect = document.getElementById('bulkRackSelect');
                let selectedOption = storeSelect.options[storeSelect.selectedIndex];

                rackSelect.innerHTML = '<option value="">Select Rack</option>';

                if (selectedOption.value) {
                    let racks = JSON.parse(selectedOption.getAttribute('data-racks'));
                    racks.forEach(rack => {
                        let option = document.createElement('option');
                        option.value = rack.id;
                        option.text = rack.name;
                        rackSelect.add(option);
                    });
                }
            }
            let bulkMode = 'set';

            function switchBulkMode(mode) {
                bulkMode = mode;
                $('#bulk_hidden_mode').val(mode);

                if (mode === 'set' || mode === 'full_sets') {
                    $('#bulkSetWiseStorageContainer').removeClass('d-none');
                    $('#bulkGlobalStorageContainer').addClass('d-none');
                    renderBulkSetWiseStorage(mode);
                } else {
                    $('#bulkSetWiseStorageContainer').addClass('d-none');
                    $('#bulkGlobalStorageContainer').removeClass('d-none');
                }

                calculateBulkSummary();
            }

            function renderBulkSetWiseStorage(mode) {
                let $list = $('#bulkSetWiseStorageList');
                $list.empty();

                let setsToShow = [];
                if (mode === 'set') {
                    let selectedSetId = $('#bulkSetSelect').val();
                    if (selectedSetId) {
                        let set = ORDER_SETS.find(s => s.id == selectedSetId);
                        if (set) setsToShow.push(set);
                    }
                } else {
                    setsToShow = ORDER_SETS;
                }

                if (setsToShow.length === 0) {
                    $list.html('<p class="text-muted text-center mb-0">No sets to assign.</p>');
                    return;
                }

                setsToShow.forEach((set, idx) => {
                    let setIdx = ORDER_SETS.indexOf(set);
                    let html = `
                                                <div class="set-storage-row mb-3 p-2 border-bottom">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="font-weight-bold text-primary">Set #${setIdx + 1} (D# ${set.design_number})</span>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <select class="form-control form-control-sm storeroom-selector" 
                                                                    data-set-id="${set.id}"
                                                                    onchange="updateSetRackSelect(this)">
                                                                <option value="">Select Warehouse</option>
                                                                ${ALL_STOREROOMS.map(s => `<option value="${s.id}" data-racks='${JSON.stringify(s.racks)}'>${s.name}</option>`).join('')}
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select class="form-control form-control-sm rack-selector" 
                                                                    name="set_racks[${set.id}]" 
                                                                    data-set-id="${set.id}">
                                                                <option value="">Select Rack</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>`;
                    $list.append(html);
                });
            }

            function updateSetRackSelect(selectEl) {
                let setId = $(selectEl).data('set-id');
                let $rackSelect = $(`.rack-selector[data-set-id="${setId}"]`);
                let $selectedOption = $(selectEl).find('option:selected');

                $rackSelect.html('<option value="">Select Rack</option>');
                if ($selectedOption.val()) {
                    let racks = $selectedOption.data('racks') || [];
                    racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }

            function calculateBulkSummary() {
                let boxesPerCarton = parseInt($('#bulk_boxes_per_carton').val()) || 1;
                let totalBoxes = 0;

                if (bulkMode === 'set') {
                    let targetInput = $('#bulk_target_sets');
                    totalBoxes = parseInt(targetInput.val()) || 0;

                    // Use Total Pieces for availability display
                    let selectedSetId = $('#bulkSetSelect').val();
                    let avlPcs = 0;
                    let piecesPerSet = 7;
                    if (selectedSetId) {
                        let set = ORDER_SETS.find(s => s.id == selectedSetId);
                        piecesPerSet = set.no_of_pcs || 7;
                        ORDER_ITEMS.forEach(item => {
                            if (item.order_products_set_id == selectedSetId) {
                                avlPcs += (parseInt(item.unit_available_qty) || 0);
                            }
                        });
                    }
                    let avl = Math.ceil(avlPcs / piecesPerSet);
                    $('#bulk_avail_sets').text('At Unit: ' + avlPcs + ' Pcs');

                    if (totalBoxes > avl) {
                        totalBoxes = avl;
                        targetInput.val(avl);
                        targetInput.addClass('is-invalid');
                        setTimeout(() => targetInput.removeClass('is-invalid'), 1000);
                    } else {
                        targetInput.removeClass('is-invalid');
                    }
                } else if (bulkMode === 'loose') {
                    let targetInput = $('#bulk_target_pieces');
                    let selectedPiece = ORDER_ITEMS.find(i => i.id == $('#bulkLooseItemSelect').val());
                    let avl = 0;
                    if (selectedPiece) {
                        avl = parseInt(selectedPiece.unit_available_qty) || 0;
                    }
                    $('#bulk_avail_pieces').text('At Unit: ' + avl);
                    targetInput.attr('max', avl);

                    totalBoxes = parseInt(targetInput.val()) || 0;
                    if (totalBoxes > avl) {
                        totalBoxes = avl;
                        targetInput.val(avl);
                        targetInput.addClass('is-invalid');
                        setTimeout(() => targetInput.removeClass('is-invalid'), 1000);
                    } else {
                        targetInput.removeClass('is-invalid');
                    }
                } else if (bulkMode === 'full_sets') {
                    totalBoxes = 0;
                    if (ORDER_SETS && ORDER_SETS.length > 0) {
                        ORDER_SETS.forEach(set => {
                            let minRemaining = null;
                            ORDER_ITEMS.forEach(item => {
                                if (item.order_products_set_id == set.id) {
                                    let availableAtUnit = parseInt(item.unit_available_qty) || 0;
                                    let qtyPerSet = parseInt(item.qty_per_set) || (item.total_quantity / (set.set_quantity || 1)) || 1;
                                    let setsAtUnit = Math.floor(availableAtUnit / qtyPerSet);
                                    if (minRemaining === null || setsAtUnit < minRemaining) minRemaining = setsAtUnit;
                                }
                            });
                            totalBoxes += (minRemaining || 0);
                        });
                    }
                } else if (bulkMode === 'full_loose') {
                    totalBoxes = 0;
                    if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                        ORDER_ITEMS.forEach(item => {
                            let remaining = parseInt(item.unit_available_qty) || 0;
                            if (remaining > 0) totalBoxes += remaining;
                        });
                    }
                }

                if (totalBoxes > 0) {
                    let totalCartons = Math.ceil(totalBoxes / boxesPerCarton);
                    let fullCartons = Math.floor(totalBoxes / boxesPerCarton);
                    let lastCartonBoxes = totalBoxes % boxesPerCarton;

                    let modeLabel = (bulkMode.includes('loose') ? "Boxes (1 pc each)" : "Boxes (Sets)");
                    let text = `<strong>${totalBoxes}</strong> ${modeLabel} in <strong>${totalCartons}</strong> Cartons.`;
                    if (lastCartonBoxes > 0 && totalCartons > 1) {
                        let boxLabel = (lastCartonBoxes === 1 ? "box" : "boxes");
                        text += ` <br><small class="opacity-75">(${fullCartons} Full Cartons + 1 Partial Carton with ${lastCartonBoxes} ${boxLabel})</small>`;
                    }

                    $('#bulkSummaryText').html(text);
                    $('#bulk_hidden_total_boxes').val(totalBoxes);
                    $('#bulkCalculationSummary').removeClass('d-none');
                } else {
                    $('#bulk_hidden_total_boxes').val(0);
                    $('#bulkCalculationSummary').addClass('d-none');
                }
            }

            function openBulkPackingModal() {
                if (!ORDER_ID) return;

                // Reset
                bulkMode = 'set';
                $('#bulk_hidden_mode').val('set');
                $('#bulk-pack-tabs a[href="#bulk-tab-sets"]').tab('show');
                $('#bulkPackingPreviewContainer').hide();
                $('#bulkCalculationSummary').addClass('d-none');
                $('#bulkPackingForm')[0].reset();

                // Populate Sets
                let setSelect = $('#bulkSetSelect');
                setSelect.html('<option value="">-- Select Set --</option>');
                if (ORDER_SETS && ORDER_SETS.length > 0) {
                    ORDER_SETS.forEach((set, index) => {
                        setSelect.append(`<option value="${set.id}">Set #${index + 1} (D# ${set.design_number}, ${set.colors ? set.colors.name : ''})</option>`);
                    });
                }

                // Populate Loose Sizes
                let looseSelect = $('#bulkLooseItemSelect');
                looseSelect.html('<option value="">-- Select Item (Size) --</option>');
                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let remaining = (parseInt(item.total_quantity) - (parseInt(item.packed_qty) || 0));
                        if (remaining > 0) {
                            let design = item.design_number || 'N/A';
                            let color = item.color_name || 'N/A';
                            looseSelect.append(`<option value="${item.id}">${design} / ${color} / ${item.size} (${remaining} left)</option>`);
                        }
                    });
                }

                $('#bulkPackingModal').modal('show');
            }

            function populateBulkSizeSet() {
                let selectedOption = $('#bulkSetSelect option:selected');
                let setId = selectedOption.val();
                let $preview = $('#bulkPackingPreview');
                let $container = $('#bulkPackingPreviewContainer');
                let $hiddenInput = $('#bulk_hidden_size_set');

                if (!setId) {
                    $container.hide();
                    $hiddenInput.val('');
                    calculateBulkSummary();
                    return;
                }

                let set = ORDER_SETS.find(s => s.id == setId);
                if (set) {
                    let totalUnitAvlPcs = 0;
                    ORDER_ITEMS.forEach(item => {
                        if (item.order_products_set_id == set.id) {
                            totalUnitAvlPcs += (parseInt(item.unit_available_qty) || 0);
                        }
                    });
                    let avlCount = Math.ceil(totalUnitAvlPcs / (set.no_of_pcs || 7));
                    $('#bulk_avail_sets').text('At Unit: ' + avlCount);
                    $('#bulk_target_sets').attr('max', avlCount);

                    let html = '<table class="table table-sm table-bordered mb-0 bg-white"><thead><tr class="bg-secondary text-white"><th>Size</th><th>Qty</th></tr></thead><tbody>';
                    let pcsPerBox = 0;
                    let sizeSetArr = [];
                    let details = set.product_set_details || set.details_data || set.details || [];

                    details.forEach(d => {
                        let qty = parseInt(d.qty_per_set) || 0;
                        if (qty > 0) {
                            html += `<tr><td>${d.size}</td><td>${qty} pcs</td></tr>`;
                            pcsPerBox += qty;
                            for (let i = 0; i < qty; i++) {
                                sizeSetArr.push(d.size);
                            }
                        }
                    });

                    html += `</tbody><tfoot><tr class="font-weight-bold"><td>Pieces/Box</td><td>${pcsPerBox} pcs</td></tr></tfoot></table>`;
                    $preview.html(html);
                    $hiddenInput.val(sizeSetArr.join(','));
                    $container.show();

                    if (bulkMode === 'set') {
                        renderBulkSetWiseStorage('set');
                    }
                } else {
                    $container.hide();
                    $hiddenInput.val('');
                    $('#bulk_avail_sets').text('Avl: 0');
                }
                calculateBulkSummary();
            }

            function submitBulkPacking() {
                let formData = $('#bulkPackingForm').serializeArray();
                let data = {
                    _token: "{{ csrf_token() }}",
                    slip_id: SLIP_ID,
                    order_id: ORDER_ID,
                    items: []
                };
                formData.forEach(item => data[item.name] = item.value);

                if (bulkMode === 'set') {
                    if (!data.set_id) { alert("Please select a set."); return; }
                    let target = parseInt(data.target_sets) || 0;
                    let avl = parseInt($('#bulk_avail_sets').text().replace('Avl: ', '')) || 0;
                    // (Relaxed) Quantity Validation - Allow overages as requested by user
                    if (target > avl) {
                        console.warn("Quantity exceeds available (" + avl + "). Proceeding anyway.");
                    }
                    data.total_boxes = target;
                } else if (bulkMode === 'loose') {
                    let looseItemId = $('#bulkLooseItemSelect').val();
                    let looseQty = parseInt($('#bulk_target_pieces').val()) || 0;
                    let avl = parseInt($('#bulk_avail_pieces').text().replace('Avl: ', '')) || 0;
                    if (!looseItemId) { alert("Please select an item."); return; }
                    if (looseQty <= 0) { alert("Please enter pieces to pack."); return; }

                    // (Relaxed) Quantity Validation
                    if (looseQty > avl) {
                        console.warn("Quantity exceeds available (" + avl + "). Proceeding anyway.");
                    }

                    data.items.push({
                        detail_id: looseItemId,
                        qty_per_box: 1
                    });
                    data.total_boxes = looseQty;
                } else {
                    if (!data.total_boxes || data.total_boxes <= 0) {
                        alert("No remaining items to pack for the complete order.");
                        return;
                    }
                }

                if (!data.boxes_per_carton || data.boxes_per_carton <= 0) {
                    alert("Please enter carton capacity.");
                    return;
                }

                // Collection of set-wise racks
                if (bulkMode === 'set' || bulkMode === 'full_sets') {
                    data.set_racks = {};
                    $('.rack-selector').each(function () {
                        let setId = $(this).data('set-id');
                        let rackId = $(this).val();
                        if (setId) {
                            data.set_racks[setId] = rackId;
                        }
                    });

                    // Validation: ensure all visible rack selectors have a value
                    let allSelected = true;
                    $('.rack-selector').each(function () {
                        if (!$(this).val()) {
                            allSelected = false;
                            return false;
                        }
                    });

                    if (!allSelected) {
                        alert("Please select a Rack for each set.");
                        return;
                    }
                } else {
                    if (!$('#bulkStoreroomSelect').val()) {
                        alert("Please select a Store Room.");
                        return;
                    }
                    if (!$('#bulkRackSelect').val()) {
                        alert("Please select a Rack.");
                        return;
                    }
                }

                let $btn = $('button[onclick="submitBulkPacking()"]');
                $btn.prop('disabled', true).text('Processing...');

                $.ajax({
                    url: "{{ route('admin.packing.bulk-save') }}",
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(response.message);
                            $btn.prop('disabled', false).text('Bulk Create');
                        }
                    },
                    error: function () {
                        toastr.error("Something went wrong on the server.");
                        $btn.prop('disabled', false).text('Bulk Create');
                    }
                });
            }


            function submitCreateBox() {
                let items = [];
                $('#boxItemsTable .item-pack-qty').each(function () {
                    let val = $(this).val();
                    if (val > 0) {
                        items.push({
                            size_id: $(this).data('size-id'),
                            quantity: val
                        });
                    }
                });

                if (items.length === 0) {
                    alert("Select at least one item");
                    return;
                }

                $.post("{{ route('admin.packing.saveBox') }}", {
                    _token: "{{ csrf_token() }}",
                    slip_id: SLIP_ID,
                    order_id: ORDER_ID,
                    box_no: $('input[name="box_no"]').val(),
                    items: items
                }, function (response) {
                    if (response.status === 'success') {
                        $('#createBoxModal').modal('hide');
                        toastr.success("Box Created Successfully");
                        setTimeout(() => location.reload(), 800);
                    } else {
                        toastr.error(response.message);
                    }
                });
            }

            function submitCreateCarton() {
                // Validation
                let cartonNo = $('input[name="carton_no"]').val();
                let rackId = $('#rackSelect').val();
                let storeId = $('#storeroomSelect').val();

                if (!cartonNo || cartonNo.trim() === '') {
                    alert("Please enter a Carton Number.");
                    return;
                }
                if (!storeId) {
                    alert("Please select a Store Room.");
                    return;
                }
                if (!rackId) {
                    alert("Please select a Rack.");
                    return;
                }

                let sets = [];
                let error = false;

                $('#cartonSetsContainer .set-pack-qty').each(function () {
                    let val = parseInt($(this).val()) || 0;
                    let max = parseInt($(this).attr('max')) || 0;

                    if (val > max) {
                        console.warn(`Quantity exceeds available (${max}). Proceeding anyway.`);
                    }

                    if (val > 0) {
                        sets.push({
                            set_id: $(this).data('set-id'),
                            quantity: val
                        });
                    }
                });

                if (error) return;

                let items = [];
                $('#cartonItemsTable .item-pack-qty').each(function () {
                    let val = parseInt($(this).val()) || 0;
                    let max = parseInt($(this).attr('max')) || 0;

                    if (val > max) {
                        console.warn(`Quantity exceeds available (${max}). Proceeding anyway.`);
                    }
                    // Ideally we need looking up size name for better error, but this stops the negative data.

                    if (val > 0) {
                        items.push({
                            size_id: $(this).data('size-id'),
                            quantity: val
                        });
                    }
                });

                if (error) return;

                // Boxes
                let boxIds = [];
                $('.box-select:checked').each(function () {
                    boxIds.push($(this).val());
                });

                if (items.length === 0 && boxIds.length === 0 && sets.length === 0) {
                    alert("Select at least one set, box, or item to pack.");
                    return;
                }

                $.post("{{ route('admin.packing.saveCarton') }}", {
                    _token: "{{ csrf_token() }}",
                    slip_id: SLIP_ID,
                    order_id: ORDER_ID,
                    carton_no: cartonNo,
                    rack_id: rackId,
                    sets: sets,
                    items: items,
                    box_ids: boxIds
                }, function (response) {
                    if (response.status === 'success') {
                        $('#createCartonModal').modal('hide');
                        toastr.success("Carton Created Successfully");
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        if (response.status === 'exists') {
                            toastr.error(response.message);
                        } else {
                            toastr.error("Error: " + response.message);
                        }
                    }
                });
            }

            function finalizePacking() {
                $('#finalizeSessionModal').modal('show');
            }

            function submitFinalize() {
                let completionDate = $('#packing_completion_date').val();
                if (!completionDate) {
                    alert('Please select completion date and time.');
                    return;
                }

                if (!confirm('Are you sure you want to finalize this domestic packing session? No more changes allowed.')) return;

                $.post("{{ route('admin.packing.finalize') }}", {
                    _token: "{{ csrf_token() }}",
                    packing_main_id: EXISTING_PACKING.id,
                    completion_date: completionDate
                }, function (response) {
                    if (response.status === 'success') {
                        // 1. Trigger Barcode Download (If Domestic)
                        if (response.order_type === 'domestic' && response.packing_main_id) {
                            let downloadUrl = "{{ route('admin.packing.downloadSlipBarcode', ':id') }}".replace(':id', response.packing_main_id);
                            window.open(downloadUrl, '_blank');
                        }

                        setTimeout(function () {
                            alert("Packing Finalized Successfully!");
                            window.location.href = "{{ route('admin.uploaded-slips.index') }}";
                        }, 500);
                    } else {
                        alert("Error: " + response.message);
                    }
                });
            }

            function switchPackTab(tab) {
                if (tab === 'sets') {
                    resetForm('#createCartonForm');
                    $('#tab-content-sets').show();
                    $('#tab-content-loose').hide();
                    $('#btn-tab-sets').addClass('active btn-outline-primary').removeClass('btn-outline-secondary');
                    $('#btn-tab-loose').removeClass('active btn-outline-primary').addClass('btn-outline-secondary');
                } else {
                    resetForm('#createCartonForm');
                    $('#tab-content-sets').hide();
                    $('#tab-content-loose').show();
                    $('#btn-tab-loose').addClass('active btn-outline-primary').removeClass('btn-outline-secondary');
                    $('#btn-tab-sets').removeClass('active btn-outline-primary').addClass('btn-outline-secondary');
                }
            }

            function deleteCarton(cartonId, event) {
                if (event) event.stopPropagation();

                if (!confirm("Are you sure you want to delete this carton? All items will be released and returned to the previous production stage.")) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.deleteCarton') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        carton_id: cartonId
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success("Carton deleted successfully.");
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error("Something went wrong on the server.");
                    }
                });
            }
            function resolveSizeName(sizeId) {
                // Try to find in ORDER_ITEMS
                // Note: ORDER_ITEMS might have 'id' matching 'size_id' (which is detail_id).
                // Or 'size' (the name).

                // Strategy: iterate ORDER_ITEMS, check id.
                let found = ORDER_ITEMS.find(i => i.id == sizeId);
                if (found) return found.size;

                // Fallback: Check if it's a simple size match (less likely with new ID system but possible legacy)
                // If not found, return ID so we at least see something.
                return 'ID: ' + sizeId;
            }
            // $(document).on('blur', '.set-pack-qty', function () {
            //     let enteredQty = $(this).val();          // input value
            //     let setId = $(this).data('set-id');      // data-set-id
            //     let maxQty = $(this).attr('max');        // max attribute

            //     alert(
            //         'Set ID: ' + setId +
            //         '\nEntered Qty: ' + enteredQty +
            //         '\nMax Allowed: ' + maxQty
            //     );
            // });
            // function checkSetValidation(setId, $setQty){
            //     if(ORDER_ITEMS && ORDER_ITEMS.length > 0) {
            //         ORDER_ITEMS.forEach(item => {
            //             let packed = parseInt(item.packed_qty) || 0;
            //             let total = parseInt(item.total_quantity);
            //             let remaining = total - packed;
            //             if(remaining > 0 && remaining <= $setQty ) {

            //             }
            //         });
            //     }
            // }


            function resetForm(formSelector) {
                let $form = $(formSelector);

                if ($form.length) {
                    $form[0].reset();                     // inputs clear
                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').remove();
                }
            }

            // --- REWORK MANAGEMENT ---
            function openReworkModal() {
                if (!ORDER_ID) {
                    alert('Please select an order first.');
                    return;
                }

                // 1. Populate Items List
                let $list = $('#reworkItemsList');
                $list.empty();

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm rework-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit to return.</td></tr>');
                }

                // 2. Fetch Stages
                $.get("{{ route('admin.packing.reworkStages') }}", function (response) {
                    if (response.status === 'success') {
                        let $stageSelect = $('#reworkStage');
                        $stageSelect.html('<option value="">Select Stage</option>');
                        response.stages.forEach(s => {
                            $stageSelect.append(`<option value="${s.id}">${s.name}</option>`);
                        });
                    }
                });

                $('#reworkModal').modal('show');
            }

            function updateReworkUnits() {
                let stageId = $('#reworkStage').val();
                let $unitSelect = $('#reworkUnit');
                $unitSelect.html('<option value="">Loading...</option>');

                if (!stageId) {
                    $unitSelect.html('<option value="">Select Unit</option>');
                    return;
                }

                $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function (response) {
                    if (response.status === 'success') {
                        $unitSelect.html('<option value="">Select Unit</option>');
                        response.units.forEach(u => {
                            $unitSelect.append(`<option value="${u.id}">${u.name}</option>`);
                        });
                    } else {
                        $unitSelect.html('<option value="">Error loading units</option>');
                    }
                });
            }

            function submitReworkAssignment() {
                let stageId = $('#reworkStage').val();
                let unitId = $('#reworkUnit').val();
                let remarks = $('#reworkRemarks').val();

                if (!stageId || !unitId) {
                    alert('Please select target stage and unit.');
                    return;
                }

                let items = [];
                $('.rework-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Please enter quantity for at least one item.');
                    return;
                }

                if (!confirm('Are you sure you want to reassign these pieces for rework? This will reduce the available quantity at this unit.')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.reassignRework') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: ORDER_ID,
                        slip_id: SLIP_ID,
                        to_stage_id: stageId,
                        to_unit_id: unitId,
                        items: items,
                        remarks: remarks
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        alert('Something went wrong on the server.');
                    }
                });
            }

        </script>
    @endpush
    <!-- DEAD STOCK MODAL -->
    <div class="modal fade" id="deadStockModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-skull-crossbones mr-2"></i> Record Dead / Damaged Stock</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Select Pieces to Mark as Dead Stock</h6>
                            <div class="table-responsive border rounded" style="max-height: 400px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th class="text-center">Available</th>
                                            <th style="width: 120px;">Dead Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="deadStockItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
                            <h6>Storage Information</h6>
                            <div class="form-group">
                                <label>Warehouse</label>
                                <select id="deadStockWarehouse" class="form-control select2"
                                    onchange="updateDeadStockRacks()">
                                    <option value="">Select Warehouse</option>
                                    @foreach($storerooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label>Rack</label>
                                <select id="deadStockRack" class="form-control select2">
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label>Remarks (Optional)</label>
                                <textarea id="deadStockRemarks" class="form-control" rows="3"
                                    placeholder="Describe damage..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-dark px-4" onclick="submitDeadStock()">
                        <i class="fas fa-save mr-1"></i> Save to Dead Stock Inventory
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sampling Modal -->
    <div class="modal fade" id="samplingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-flask mr-2"></i> Record Sampling Pieces
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h6>Select Pieces for Sampling</h6>
                            <div class="table-responsive border rounded" style="max-height: 400px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th class="text-center">Available</th>
                                            <th style="width: 120px;">Sample Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="samplingItemsList">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4 border-left">
                            <h6>Storage Information</h6>
                            <div class="form-group">
                                <label>Warehouse</label>
                                <select id="samplingWarehouse" class="form-control" onchange="updateSamplingRacks()">
                                    <option value="">Select Warehouse</option>
                                    @foreach($storerooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label>Rack</label>
                                <select id="samplingRack" class="form-control">
                                    <option value="">Select Rack</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label>Remarks (Optional)</label>
                                <textarea id="samplingRemarks" class="form-control" rows="3"
                                    placeholder="Sampling purpose..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4" onclick="submitSampling()">
                        <i class="fas fa-save mr-1"></i> Save Sampling Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Debit Modal -->
    <div class="modal fade" id="debitModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title font-weight-bold text-dark">
                        <i class="fas fa-minus-circle mr-2"></i> Record Damage Debit
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-7">
                            <h6>1. Select Responsible Stage & Unit</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Responsible Stage</label>
                                    <select id="debitStage" class="form-control form-control-sm"
                                        onchange="updateDebitUnits()">
                                        <option value="">Select Stage</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Responsible Unit</label>
                                    <select id="debitUnit" class="form-control form-control-sm">
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                            </div>

                            <h6>2. Select Damaged Pieces</h6>
                            <div class="table-responsive border rounded mb-3" style="max-height: 250px;">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Design / Color</th>
                                            <th>Size</th>
                                            <th class="text-center">Avl</th>
                                            <th style="width: 70px;">Qty</th>
                                            <th style="width: 90px;">Rate(₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="debitItemsList"></tbody>
                                </table>
                            </div>

                            <h6>3. Storage Location</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Warehouse</label>
                                    <select id="debitWarehouse" class="form-control form-control-sm"
                                        onchange="updateDebitRacks()">
                                        <option value="">Select Warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Rack</label>
                                    <select id="debitRack" class="form-control form-control-sm">
                                        <option value="">Select Rack</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 border-left">
                            <h6>4. Deduction Details</h6>
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Discount (₹)</label>
                                <input type="number" id="debitDiscount" class="form-control form-control-sm"
                                    placeholder="0.00" oninput="calculateDebitTotal()">
                            </div>
                            <div class="form-group mt-3 bg-light p-3 rounded border">
                                <label class="font-weight-bold text-danger mb-1">TOTAL DEBIT AMOUNT</label>
                                <div class="h3 font-weight-bold text-dark mb-0">
                                    Rs. <span id="debitTotalDisplay">0.00</span>
                                </div>
                                <input type="hidden" id="debitAmount" value="0">
                            </div>

                            <div class="form-group mt-3">
                                <label class="small font-weight-bold">Reason / Remarks</label>
                                <textarea id="debitRemarks" class="form-control" rows="3"
                                    placeholder="Explain the defect..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning px-4 font-weight-bold text-dark" onclick="submitDebit()">
                        <i class="fas fa-save mr-1"></i> Confirm & Deduct Amount
                    </button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            function validateQtyInput(input) {
                let max = parseInt($(input).data('max')) || 0;
                let val = parseInt($(input).val()) || 0;
                if (val > max) {
                    alert(`Cannot enter more than available quantity (${max})`);
                    $(input).val(max);
                }
                if (val < 0) $(input).val(0);

                // Specific callback for debit to update totals
                if ($(input).hasClass('debit-qty-input')) {
                    calculateDebitTotal();
                }
            }

            function openDebitModal() {
                if (!ORDER_ID) {
                    alert('Please select an order first.');
                    return;
                }

                // 1. Fetch Stages (Production stages)
                $.get("{{ route('admin.packing.reworkStages') }}", function (response) {
                    if (response.status === 'success') {
                        let $stageSelect = $('#debitStage');
                        $stageSelect.html('<option value="">Select Stage</option>');
                        response.stages.forEach(stage => {
                            $stageSelect.append(`<option value="${stage.id}">${stage.name}</option>`);
                        });
                        $('#debitUnit').html('<option value="">Select Unit</option>');
                    }
                });

                // 2. Populate Items
                let $list = $('#debitItemsList');
                $list.empty();
                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle small">
                                                <div class="font-weight-bold">${item.design_number || 'N/A'}</div>
                                                <div class="text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle small">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center small">${avl}</td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm debit-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm debit-rate-input" 
                                                       placeholder="0" value="0" oninput="calculateDebitTotal()">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available for debit.</td></tr>');
                }

                // Reset fields
                $('#debitDiscount, #debitAmount').val(0);
                $('#debitTotalDisplay').text('0.00');
                $('#debitRemarks').val('');

                $('#debitModal').modal('show');
            }

            function updateDebitUnits() {
                let stageId = $('#debitStage').val();
                let $unitSelect = $('#debitUnit');
                $unitSelect.html('<option value="">Loading...</option>');

                if (!stageId) {
                    $unitSelect.html('<option value="">Select Unit</option>');
                    return;
                }

                $.get("{{ route('admin.packing.stageUnits', '') }}/" + stageId, function (response) {
                    if (response.status === 'success') {
                        $unitSelect.html('<option value="">Select Unit</option>');
                        response.units.forEach(unit => {
                            $unitSelect.append(`<option value="${unit.id}">${unit.name}</option>`);
                        });
                    } else {
                        $unitSelect.html('<option value="">Error loading units</option>');
                    }
                });
            }

            function updateDebitRacks() {
                let warehouseId = $('#debitWarehouse').val();
                let $rackSelect = $('#debitRack');
                $rackSelect.html('<option value="">Select Rack</option>');

                if (!warehouseId) return;

                let room = STOREROOMS.find(r => r.id == warehouseId);
                if (room && room.racks) {
                    room.racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }

            function calculateDebitTotal() {
                let subtotal = 0;
                $('#debitItemsList tr').each(function () {
                    let qty = parseInt($(this).find('.debit-qty-input').val()) || 0;
                    let rate = parseFloat($(this).find('.debit-rate-input').val()) || 0;
                    subtotal += (qty * rate);
                });

                let discount = parseFloat($('#debitDiscount').val()) || 0;
                let finalTotal = Math.max(0, subtotal - discount);

                $('#debitAmount').val(finalTotal.toFixed(2));
                $('#debitTotalDisplay').text(finalTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }

            function submitDebit() {
                let stageId = $('#debitStage').val();
                let unitId = $('#debitUnit').val();
                let rackId = $('#debitRack').val();
                let discount = $('#debitDiscount').val();
                let totalAmount = $('#debitAmount').val();
                let remarks = $('#debitRemarks').val();

                if (!stageId || !unitId || !rackId) {
                    alert('Please select stage, unit and storage rack.');
                    return;
                }

                let items = [];
                let hasValidRate = true;
                $('#debitItemsList tr').each(function () {
                    let $qtyInput = $(this).find('.debit-qty-input');
                    let $rateInput = $(this).find('.debit-rate-input');
                    let qty = parseInt($qtyInput.val()) || 0;
                    let rate = parseFloat($rateInput.val()) || 0;

                    if (qty > 0) {
                        if (rate <= 0) {
                            hasValidRate = false;
                        }
                        items.push({
                            detail_id: $qtyInput.data('id'),
                            qty: qty,
                            per_piece_amount: rate
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Please select at least one damaged item to debit.');
                    return;
                }

                if (!hasValidRate) {
                    alert('Please enter a valid rate for all selected pieces.');
                    return;
                }

                if (!confirm(`Confirm debit of ₹${totalAmount} to the selected unit? This will also remove the items and move them to warehouse.`)) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.recordUnitDebit') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: ORDER_ID,
                        slip_id: SLIP_ID,
                        stage_id: stageId,
                        unit_id: unitId,
                        rack_id: rackId,
                        discount: discount,
                        total_amount: totalAmount,
                        items: items,
                        remarks: remarks
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong on the server.');
                    }
                });
            }

            function openSamplingModal() {
                if (!ORDER_ID) {
                    alert('Please select an order first.');
                    return;
                }

                let $list = $('#samplingItemsList');
                $list.empty();

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm sampling-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit for sampling.</td></tr>');
                }

                $('#samplingModal').modal('show');
            }

            function updateSamplingRacks() {
                let warehouseId = $('#samplingWarehouse').val();
                let $rackSelect = $('#samplingRack');
                $rackSelect.html('<option value="">Select Rack</option>');

                if (!warehouseId) return;

                let room = STOREROOMS.find(r => r.id == warehouseId);
                if (room && room.racks) {
                    room.racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }

            function submitSampling() {
                let rackId = $('#samplingRack').val();
                let remarks = $('#samplingRemarks').val();

                if (!rackId) {
                    alert('Please select storage rack.');
                    return;
                }

                let items = [];
                $('.sampling-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Please enter quantity for at least one item.');
                    return;
                }

                if (!confirm('Are you sure you want to record these pieces for Sampling? This will remove them from active production.')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.recordSamplingStock') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: ORDER_ID,
                        slip_id: SLIP_ID,
                        rack_id: rackId,
                        items: items,
                        remarks: remarks
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong on the server.');
                    }
                });
            }

            function openDeadStockModal() {
                if (!ORDER_ID) {
                    alert('Please select an order first.');
                    return;
                }

                let $list = $('#deadStockItemsList');
                $list.empty();

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        let avl = item.unit_available_qty || 0;
                        if (avl > 0) {
                            $list.append(`
                                        <tr>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-dark">${item.design_number || 'N/A'}</div>
                                                <div class="small text-muted">${item.color_name || 'N/A'}</div>
                                            </td>
                                            <td class="align-middle">${item.size || 'N/A'}</td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-light border px-2">${avl} Pcs</span>
                                            </td>
                                            <td class="align-middle">
                                                <input type="number" class="form-control form-control-sm dead-qty-input" 
                                                       data-id="${item.id}" data-max="${avl}" 
                                                       min="0" max="${avl}" value="0" oninput="validateUnitPackagingStock(this)">
                                            </td>
                                        </tr>
                                    `);
                        }
                    });
                }

                if ($list.is(':empty')) {
                    $list.append('<tr><td colspan="4" class="text-center py-4 text-muted">No pieces available at this unit to mark as damage.</td></tr>');
                }

                $('#deadStockModal').modal('show');
            }

            const STOREROOMS = @json($storerooms);

            function updateDeadStockRacks() {
                let warehouseId = $('#deadStockWarehouse').val();
                let $rackSelect = $('#deadStockRack');
                $rackSelect.html('<option value="">Select Rack</option>');

                if (!warehouseId) return;

                let room = STOREROOMS.find(r => r.id == warehouseId);
                if (room && room.racks) {
                    room.racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }


            function updateDomRacks() {
                let warehouseId = $('#domStore').val();
                let $rackSelect = $('#dom_rack');
                $rackSelect.html('<option value="">Wait...</option>');

                if (!warehouseId) {
                    $rackSelect.html('<option value="">Select Rack</option>');
                    return;
                }

                let room = STOREROOMS.find(r => r.id == warehouseId);
                $rackSelect.html('<option value="">Select Rack</option>');
                if (room && room.racks) {
                    room.racks.forEach(rack => {
                        $rackSelect.append(`<option value="${rack.id}">${rack.name}</option>`);
                    });
                }
            }

            function submitDeadStock() {
                let rackId = $('#deadStockRack').val();
                let remarks = $('#deadStockRemarks').val();

                if (!rackId) {
                    alert('Please select storage rack.');
                    return;
                }

                let items = [];
                $('.dead-qty-input').each(function () {
                    let qty = parseInt($(this).val()) || 0;
                    if (qty > 0) {
                        items.push({
                            detail_id: $(this).data('id'),
                            qty: qty
                        });
                    }
                });

                if (items.length === 0) {
                    alert('Please enter quantity for at least one item.');
                    return;
                }

                if (!confirm('Are you sure you want to mark these pieces as Dead Stock? This will move them to permanent damage inventory and they will not be sellable.')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.packing.recordDeadStock') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_id: ORDER_ID,
                        slip_id: SLIP_ID,
                        rack_id: rackId,
                        items: items,
                        remarks: remarks
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        toastr.error('Something went wrong on the server.');
                    }
                });
            }

            // --- DOMESTIC PACKING LOGIC ---
            function openDomesticPackingModal() {
                if (!ORDER_ID) return;
                renderDomesticInventory();
                $('#domesticPackingModal').modal('show');
            }

            function renderDomesticInventory() {
                let $list = $('#domesticInventoryList');
                $list.html('<p class="small text-muted">Calculating...</p>');

                let html = '';
                ORDER_SETS.forEach(set => {
                    let minSets = null;
                    let hasDetails = false;
                    let sizeDetailsHtml = '';

                    ORDER_ITEMS.forEach(item => {
                        if (item.order_products_set_id == set.id) {
                            hasDetails = true;
                            let avl = parseInt(item.unit_available_qty) || 0;
                            let perSet = parseFloat(item.qty_per_set) || 1;
                            let canPick = Math.floor(avl / perSet);
                            if (minSets === null || canPick < minSets) minSets = canPick;

                            // Add size detail for this set
                            sizeDetailsHtml += `<div class="d-flex justify-content-between mb-1 py-1 border-bottom-dashed">
                                        <span class="text-muted">Size ${item.size}:</span>
                                        <span class="badge badge-light border px-2">${avl} Pcs</span>
                                    </div>`;
                        }
                    });

                    let count = hasDetails ? (minSets ?? 0) : 0;
                    if (count > 0 || sizeDetailsHtml) {
                        html += `<div class="mb-3 p-3 border rounded bg-white shadow-sm overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="font-weight-bold text-dark">${set.design_number}</span>
                                        <span class="badge badge-info">${set.size_set_name || 'N/A'}</span>
                                    </div>
                                    <div class="text-muted small mb-2 border-bottom pb-2">${set.color_name || 'No Color'}</div>
                                    <div class="size-breakdown mb-2" style="max-height: 120px; overflow-y: auto;">
                                        ${sizeDetailsHtml}
                                    </div>
                                    <div class="text-primary mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                                        <span class="small font-weight-bold">FULL BOXES:</span>
                                        <span class="h6 mb-0 font-weight-bold">${count}</span>
                                    </div>
                                </div>`;
                    }
                });
                $list.html(html || '<p class="small text-muted">No stock available.</p>');
            }

            if (ORDER_TYPE === 'domestic') {
                let domesticBoxesPlan = [];

                function updateDomesticPlanTable() {
                    let $body = $('#domesticTableBody');
                    $body.empty();
                    domesticBoxesPlan.forEach((box, index) => {
                        $body.append(`
                                    <tr class="small">
                                        <td>${index + 1}</td>
                                        <td>${box.designLabel}</td>
                                        <td>${box.sizeSetLabel}</td>
                                        <td>${box.colorLabel}</td>
                                        <td>${box.patternLabel || '-'}</td>
                                        <td>${box.fittingLabel || '-'}</td>
                                        <td class="text-center font-weight-bold d-none">${box.quantity}</td>
                                        <td>${box.storageLabel}</td>
                                        <td class="text-right">
                                            <button type="button" class="btn btn-link text-danger p-0" onclick="removeBoxFromPlan(${index})">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `);
                    });
                }

                window.removeBoxFromPlan = function (index) {
                    domesticBoxesPlan.splice(index, 1);
                    updateDomesticPlanTable();
                };

                window.resetDomesticPlan = function () {
                    if (confirm("Clear all planned boxes?")) {
                        domesticBoxesPlan = [];
                        updateDomesticPlanTable();
                    }
                };

                window.submitDomesticPacking = function () {
                    if (domesticBoxesPlan.length === 0) {
                        toastr.warning("No boxes in the plan.");
                        return;
                    }

                    let $btn = $(event.target);
                    let originalText = $btn.text();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

                    $.ajax({
                        url: "{{ route('admin.packing.saveDomesticBulk') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            slip_id: SLIP_ID,
                            order_id: ORDER_ID,
                            boxes: domesticBoxesPlan.map(b => b.raw_data)
                        },
                        success: function (res) {
                            if (res.status === 'success') {
                                toastr.success(res.message);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                toastr.error(res.message);
                                $btn.prop('disabled', false).text(originalText);
                            }
                        },
                        error: function () {
                            toastr.error("Server error occurred during bulk process.");
                            $btn.prop('disabled', false).text(originalText);
                        }
                    });
                };

                $(document).on('change', '#dom_design', function () {
                    let productId = $(this).val();
                    let $sizeSet = $('#dom_size_set');
                    let $color = $('#dom_color');

                    $sizeSet.html('<option value="">Loading...</option>').prop('disabled', true).trigger('change');
                    $color.html('<option value="">Select Size Set First</option>').prop('disabled', true).trigger('change');

                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (res) {
                        if (res.success) {
                            $('#dom_pattern_name').val(res.pattern_name);
                            $('#dom_pattern_id').val(res.pattern_id);
                            $('#dom_fitting_name').val(res.fitting_name);
                            $('#dom_fitting_id').val(res.fitting_id);

                            $sizeSet.html('<option value="">-- Select Size Set --</option>');
                            res.variants.forEach(v => {
                                // Convert both to numbers for reliable comparison
                                let isAllowed = ORDER_TYPE === 'domestic' || ALLOWED_SIZE_SET_IDS.length === 0 || ALLOWED_SIZE_SET_IDS.map(Number).includes(Number(v.size_set_id));
                                if (isAllowed) {
                                    $sizeSet.append(`<option value="${v.size_set_id}" data-mrp="${v.mrp}" data-colors='${JSON.stringify(v.colors)}'>${v.size_set_name}</option>`);
                                }
                            });
                            $sizeSet.prop('disabled', false).trigger('change');
                            window.dom_variants = res.variants;
                        }
                    });
                });

                $(document).on('change', '#dom_size_set', function () {
                    let sizeSetId = $(this).val();
                    let $color = $('#dom_color');
                    let selected = $(this).find(':selected');

                    $('#dom_mrp').val(selected.data('mrp'));

                    $color.html('<option value="">-- Select Color --</option>');
                    let colors = selected.data('colors') || [];
                    colors.forEach(c => {
                        $color.append(`<option value="${c.id}">${c.name}</option>`);
                    });
                    $color.prop('disabled', false).trigger('change');
                });

                function updateDomBoxMax() {
                    let designId = $('#dom_design').val();
                    let sizeSetId = $('#dom_size_set').val();
                    let colorId = $('#dom_color').val();

                    if (!designId || !sizeSetId || !colorId) return;

                    let selectedSet = ORDER_SETS.find(s => 
                        s.production_goods_id == designId && 
                        s.set_size == sizeSetId && 
                        s.color_id == colorId
                    );

                    let maxAvailable = 0;
                    if (selectedSet) {
                        let minSets = null;
                        ORDER_ITEMS.forEach(item => {
                            if (item.order_products_set_id == selectedSet.id) {
                                let avl = parseInt(item.unit_available_qty) || 0;
                                let perSet = parseFloat(item.qty_per_set) || 1;
                                let canPick = Math.floor(avl / perSet);
                                if (minSets === null || canPick < minSets) minSets = canPick;
                            }
                        });
                        maxAvailable = minSets ?? 0;
                    }

                    // Subtract already planned
                    let alreadyPlannedBoxes = 0;
                    if (typeof domesticBoxesPlan !== 'undefined') {
                        domesticBoxesPlan.forEach(box => {
                            if (box.raw_data.product_id == designId &&
                                box.raw_data.size_set_id == sizeSetId &&
                                box.raw_data.color_id == colorId) {
                                alreadyPlannedBoxes++;
                            }
                        });
                    }

                    let leftToPack = Math.max(0, maxAvailable - alreadyPlannedBoxes);
                    
                    let $boxCount = $('#dom_box_count');
                    $boxCount.attr('max', leftToPack);
                    
                    if (parseInt($boxCount.val()) > leftToPack) {
                        $boxCount.val(leftToPack > 0 ? leftToPack : '');
                    }
                }

                $(document).on('change', '#dom_color', updateDomBoxMax);
                $(document).on('input', '#dom_box_count', function() {
                    let max = parseInt($(this).attr('max'));
                    if (!isNaN(max) && parseInt($(this).val()) > max) {
                        $(this).val(max);
                        toastr.warning(`Maximum available boxes for this selection is ${max}`);
                    }
                });

                $('#btnSaveDomesticBox').on('click', function () {
                    let data = {
                        product_id: $('#dom_design').val(),
                        product_label: $('#dom_design option:selected').text(),
                        size_set_id: $('#dom_size_set').val(),
                        size_set_label: $('#dom_size_set option:selected').text(),
                        color_id: $('#dom_color').val(),
                        color_label: $('#dom_color option:selected').text(),
                        pattern_id: $('#dom_pattern_id').val() || null,
                        pattern_label: $('#dom_pattern_name').val() || '-',
                        fitting_id: $('#dom_fitting_id').val() || null,
                        fitting_label: $('#dom_fitting_name').val() || '-',
                        quantity: $('#dom_qty').val(),
                        store_label: $('#domStore option:selected').text(),
                        rack_id: $('#dom_rack').val(),
                        rack_label: $('#dom_rack option:selected').text()
                    };

                    if (!data.product_id || !data.size_set_id || !data.color_id || !data.rack_id) {
                        toastr.warning("Please fill all required fields including storage Rack.");
                        return;
                    }

                    let boxCount = parseInt($('#dom_box_count').val()) || 1;

                    // Validate against max available full boxes
                    let selectedSet = ORDER_SETS.find(s => 
                        s.production_goods_id == data.product_id && 
                        s.set_size == data.size_set_id && 
                        s.color_id == data.color_id
                    );

                    let maxAvailable = 0;
                    if (selectedSet) {
                        let minSets = null;
                        ORDER_ITEMS.forEach(item => {
                            if (item.order_products_set_id == selectedSet.id) {
                                let avl = parseInt(item.unit_available_qty) || 0;
                                let perSet = parseFloat(item.qty_per_set) || 1;
                                let canPick = Math.floor(avl / perSet);
                                if (minSets === null || canPick < minSets) minSets = canPick;
                            }
                        });
                        maxAvailable = minSets ?? 0;
                    }

                    // Count already planned boxes for this configuration
                    let alreadyPlannedBoxes = 0;
                    domesticBoxesPlan.forEach(box => {
                        if (box.raw_data.product_id == data.product_id &&
                            box.raw_data.size_set_id == data.size_set_id &&
                            box.raw_data.color_id == data.color_id) {
                            alreadyPlannedBoxes++;
                        }
                    });

                    if ((boxCount + alreadyPlannedBoxes) > maxAvailable) {
                        let leftToPack = Math.max(0, maxAvailable - alreadyPlannedBoxes);
                        toastr.error(`You can only pack ${leftToPack} more box(es). You already have ${alreadyPlannedBoxes} box(es) in the plan.`);
                        $('#dom_box_count').val(leftToPack > 0 ? leftToPack : 1);
                        return;
                    }

                    for (let i = 0; i < boxCount; i++) {
                        domesticBoxesPlan.push({
                            designLabel: data.product_label,
                            sizeSetLabel: data.size_set_label,
                            colorLabel: data.color_label,
                            patternLabel: data.pattern_label,
                            fittingLabel: data.fitting_label,
                            quantity: data.quantity,
                            storageLabel: data.store_label + ' / ' + data.rack_label,
                            raw_data: {
                                product_id: data.product_id,
                                size_set_id: data.size_set_id,
                                color_id: data.color_id,
                                pattern_id: data.pattern_id,
                                fitting_id: data.fitting_id,
                                quantity: data.quantity,
                                rack_id: data.rack_id
                            }
                        });
                    }

                    updateDomesticPlanTable();
                    toastr.info(`Added ${boxCount} box(es) to plan.`);
                });
            }
        </script>
    @endpush
@endsection