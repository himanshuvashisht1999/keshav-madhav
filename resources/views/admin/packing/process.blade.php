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
                                            <option value="{{ $ao->id }}">
                                                {{-- #{{ $ao->id }} - {{ $ao->customer->name ?? 'Unknown' }} ({{ $ao->sku }}) --}}
                                                {{ $ao->customer->name ?? 'Unknown' }} ({{ $ao->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </h4>

                    </div>
                    @if($order)
                        <div class="col-md-2 text-right">
                            <a id="fileLink" href="{{asset('/assets/products/' . $order->corporate_order_file)}}"
                                target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-alt mr-1"></i> Sales Order File
                            </a>
                        </div>
                    @endif
                    <div class="col-md-2 text-right">
                        <a href="" id="fileLink" target="_blank" rel="noopener noreferrer"
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
                    <!-- LEFT PANEL: AVAILABLE ITEMS -->
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Order Details & Items</h3>
                                {{-- <button class="btn btn-sm btn-outline-primary" onclick="openCreateSetModal()"
                                    id="btnCreateSet" disabled>
                                    <i class="fas fa-plus-circle"></i> Create Set
                                </button> --}}
                            </div>
                            <div class="card-body p-0" style="overflow-y: auto; max-height: 600px;">
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
                                    <button class="btn btn-light btn-sm" onclick="openCreateCartonModal()"
                                        id="btnCreateCarton" @if(!$order) disabled @endif>
                                        <i class="fas fa-plus"></i> New Carton
                                    </button>
                                    <button class="btn btn-warning btn-sm ms-2" onclick="openBulkPackingModal()"
                                        id="btnBulkPacking" @if(!$order) disabled @endif>
                                        <i class="fas fa-layer-group"></i> Bulk Packing
                                    </button>
                                    <button class="btn btn-success btn-sm ms-2" onclick="finalizePacking()" id="btnFinalize"
                                        @if(!$order) disabled @endif>
                                        <i class="fas fa-check"></i> Finalize
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" id="packing-structure-area" style="overflow-y: auto; max-height: 600px;">
                                <div class="text-center text-muted mt-5">
                                    <p>No cartons created yet.</p>
                                    <button class="btn btn-outline-primary btn-sm" onclick="openCreateCartonModal()"
                                        id="btnCreateFirstCarton" @if(!$order) disabled @endif>Create First Carton</button>
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
                            <label>Box Number</label>
                            <input type="text" class="form-control" name="box_no" required>
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
    <div class="modal fade" id="createSetModal" tabindex="-1">
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

    <!-- Bulk Packing Modal -->
    <div class="modal fade" id="bulkPackingModal" tabindex="-1">
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

                        <div class="row">
                            <div class="col-md-6">
                                <label class="font-weight-bold">Storage Location</label>
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
                            <div class="col-md-6">
                                <label class="font-weight-bold">Assigned Rack</label>
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

    @push('scripts')
        <script>
            let ORDER_ID = {{ $order->id ?? 'null' }};
            const SLIP_ID = {{ $slip->id }};
                            let ORDER_ITEMS = [];
                            let ORDER_SETS = @json($order_sets ?? []);
                            const PACKED_DATA = @json($packed_quantities ?? []);
                            const EXISTING_PACKING = @json($packing);

                            // Initial Population of ORDER_ITEMS from Sets if available
                            if (ORDER_SETS.length > 0 && ORDER_ITEMS.length === 0) {
                                ORDER_SETS.forEach(set => {
                                    let details = set.details_data || set.details;
                                    if (details) {
                                        details.forEach(d => {
                                            // We clone it to avoid reference issues if we modify it
                                            let item = JSON.parse(JSON.stringify(d));
                                            // Map fields if necessary, but standard fields should match
                                            // detail has: id, size, total_quantity, packed_qty (computed in controller)
                                            // Ensure packed_qty is set
                                            item.packed_qty = item.packed_qty || PACKED_DATA[item.id] || 0;
                                            ORDER_ITEMS.push(item);
                                        });
                                    }
                                });
                            }

                            // If loaded via AJAX later, we might need similar logic, but fetchOrderDetails handles it.

                            // Merge packed data into ORDER_ITEMS on init (redundant if handled above, but safe)
                            if (ORDER_ITEMS.length > 0) {
                                ORDER_ITEMS = ORDER_ITEMS.map(item => {
                                    item.packed_qty = PACKED_DATA[item.id] || item.packed_qty || 0; // Use item.id (detail id)
                                    return item;
                                });
                            }

                            // Structure State
                            let packedStructure = {
                                cartons: EXISTING_PACKING ? EXISTING_PACKING.cartons : [],
                                boxes: EXISTING_PACKING ? EXISTING_PACKING.boxes : [] // Unpacked boxes
                            };

                            $(document).ready(function () {
                                if (ORDER_ID) {
                                    renderAvailableItems();
                                }
                                renderStructure();

                                // Initialize Select2 if available
                                if ($('.select2').length > 0) {
                                    $('.select2').select2();
                                }

                                // Handle Order Selection
                                $('#orderSelect').on('change', function () {
                                    let orderId = $(this).val();
                                    if (orderId) {
                                        fetchOrderDetails(orderId);
                                    } else {
                                        ORDER_ID = null;
                                        ORDER_ITEMS = [];
                                        $('#available-items-list').html('<li class="list-group-item text-muted text-center">Please select an order first.</li>');
                                        disableActions(true);
                                    }
                                });

                                $('#carton_no').on('blur', function () {
                                    let cartonNo = $(this).val().trim();

                                    if (cartonNo === '') return;

                                    $.ajax({
                                        url: "{{ route('admin.packing.check-carton-no') }}",
                                        type: 'get',
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

                                $.get("{{ route('admin.packing.orderDeps', '') }}/" + orderId, function (response) {
                                    if (response.status === 'success') {
                                        ORDER_ID = orderId;
                                        ORDER_ITEMS = response.items || [];
                                        ORDER_SETS = response.sets || [];

                                        renderAvailableItems();
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
                                });
                            }

                            function disableActions(disable) {
                                $('#btnCreateCarton, #btnBulkPacking, #btnFinalize, #btnCreateFirstCarton').prop('disabled', disable);
                            }

                                                    function renderStructure() {
                let html = '';

                // Cartons
                if (packedStructure.cartons.length > 0) {
                    html += `<div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-muted" style="letter-spacing: 0.5px; font-weight: 600;">CARTONS</h5>
                        <span class="badge badge-primary badge-pill">${packedStructure.cartons.length} Total</span>
                    </div>`;
                    
                    html += `<div class="accordion" id="cartonAccordion">`;
                    packedStructure.cartons.forEach((carton, index) => {
                        // Aggregate loose items
                        let looseItemsSummary = {};
                        if (carton.items && carton.items.length > 0) {
                            carton.items.forEach(item => {
                                let name = resolveSizeName(item.size_id);
                                looseItemsSummary[name] = (looseItemsSummary[name] || 0) + parseInt(item.quantity);
                            });
                        }

                        html += `
                        <div class="card mb-3 border-0 shadow-sm overflow-hidden" style="border-radius: 12px; transition: transform 0.2s;">
                            <div class="card-header border-0 d-flex align-items-center p-0" id="heading${index}" style="background: #f8f9fa;">
                                <button class="btn btn-block text-left d-flex align-items-center py-3 px-4 border-0" type="button" data-toggle="collapse" data-target="#carton${index}" aria-expanded="false" style="box-shadow: none; background: transparent;">
                                    <div class="icon-shape bg-soft-primary text-primary mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 10px; background: rgba(var(--primary-rgb), 0.1);">
                                        <i class="fas fa-archive"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-dark font-weight-bold">Carton #${carton.carton_no}</h6>
                                        <p class="mb-0 text-muted small">${carton.boxes.length} Boxes | ${Object.keys(looseItemsSummary).length} Loose Size Sets</p>
                                    </div>
                                    <i class="fas fa-chevron-down text-muted small transition-all"></i>
                                </button>
                                ${(EXISTING_PACKING && EXISTING_PACKING.status === 0) ? `
                                <div class="px-3">
                                    <button class="btn btn-link text-danger p-2" onclick="deleteCarton(${carton.id}, event)" title="Delete Carton">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>` : ''}
                            </div>
                            <div id="carton${index}" class="collapse" data-parent="#cartonAccordion">
                                <div class="card-body p-4 bg-white border-top">
                                    ${carton.boxes.length > 0 ? `
                                    <div class="mb-4">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-box text-warning mr-2 small"></i>
                                            <span class="text-xs font-weight-bold text-uppercase tracking-wider text-muted">Contained Boxes</span>
                                        </div>
                                        <div class="d-flex flex-wrap" style="gap: 8px;">
                                            ${carton.boxes.map(b => `<span class="badge border text-dark py-2 px-3 bg-light" style="border-radius: 8px; font-weight: 500;"><i class="fas fa-barcode mr-1 opacity-50"></i> #${b.box_no}</span>`).join('')}
                                        </div>
                                    </div>` : ''}

                                    ${Object.keys(looseItemsSummary).length > 0 ? `
                                    <div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-th-large text-info mr-2 small"></i>
                                            <span class="text-xs font-weight-bold text-uppercase tracking-wider text-muted">Loose Pieces Summary</span>
                                        </div>
                                        <div class="table-responsive rounded border shadow-none">
                                            <table class="table table-sm mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="border-0 small text-uppercase py-2 px-3">Size</th>
                                                        <th class="border-0 small text-uppercase py-2 px-3 text-right">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${Object.entries(looseItemsSummary).map(([name, qty]) => `
                                                    <tr>
                                                        <td class="py-2 px-3 font-weight-bold text-dark">${name}</td>
                                                        <td class="py-2 px-3 text-right"><span class="badge badge-soft-info">${qty} Pcs</span></td>
                                                    </tr>`).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>` : ''}
                                </div>
                            </div>
                        </div>`;
                    });
                    html += `</div>`;
                }

                if (html === '') {
                    html = `<div class="text-center py-5 border rounded bg-light" style="border-style: dashed !important; border-width: 2px !important;">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4816154-4017688.png" style="width: 120px; opacity: 0.6;" class="mb-3">
                        <h6 class="text-muted">No cartons created for this order yet</h6>
                        <button class="btn btn-primary btn-sm mt-3 px-4 shadow-sm" style="border-radius: 20px;" onclick="openCreateCartonModal()" id="btnCreateFirstCarton" ${!ORDER_ID ? 'disabled' : ''}>
                            <i class="fas fa-plus mr-1"></i> Create First Carton
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
                                        html += `
                                                                                                                                                                                    <li class="list-group-item bg-light">
                                                                                                                                                                                        <strong>Set #${index + 1}</strong> <small class="text-muted">(Qty: ${set.set_quantity})</small>
                                                                                                                                                                                        <span class="badge ${remainingSets > 0 ? 'bg-primary' : 'bg-success'} float-right">Rem: ${remainingSets} Sets</span>
                                                                                                                                                                                    </li>`;

                                        // Details
                                        if (set.details_data || set.details) {
                                            let details = set.details_data || set.details;
                                            details.forEach(item => {
                                                let packed = parseInt(item.packed_qty) || 0;
                                                let total = parseInt(item.total_quantity);
                                                let remaining = total - packed;
                                                let badgeClass = remaining === 0 ? 'bg-success' : 'bg-secondary';

                                                html += `<li class="list-group-item d-flex justify-content-between align-items-center ps-4 py-1">
                                                                                                                                                                                                <small>Size: ${item.size}</small>
                                                                                                                                                                                                <span>
                                                                                                                                                                                                    <span class="badge ${badgeClass} badge-pill">${remaining}</span> 
                                                                                                                                                                                                    <small class="text-muted">/ ${total}</small>
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
                                                                                                                                                                                                     <small class="text-info">Available: ${remainingSets}</small>
                                                                                                                                                                                                </div>
                                                                                                                                                                                                <div class="d-flex align-items-center">
                                                                                                                                                                                                    <input type="number" class="form-control form-control-sm set-pack-qty mr-2" style="width: 70px;" placeholder="Qty" max="${remainingSets}" min="0" data-set-id="${set.id}">
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
                                            modalHtml += `<tr>
                                                                                                                                                                                            <td>${setData ? setData.bar_code : '-'}</td>
                                                                                                                                                                                            <td>${setData ? setData.design_number : '-'}</td>
                                                                                                                                                                                            <td>${setData && setData.colors ? setData.colors.name : '-'}</td>
                                                                                                                                                                                            <td>${item.size}</td>
                                                                                                                                                                                            <td>${remaining} <small class="text-muted">(${total})</small></td>
                                                                                                                                                                                            <td><input type="number" class="form-control form-control-sm item-pack-qty" data-size-id="${item.id}" max="${remaining}" min="0"></td>
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
                                        option.text = rack.name + (rack.capacity ? ` (Cap: ${rack.capacity})` : '');
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
                                        option.text = rack.name + (rack.capacity ? ` (Cap: ${rack.capacity})` : '');
                                        rackSelect.add(option);
                                    });
                                }
                            }
                            let bulkMode = 'set';

                            function switchBulkMode(mode) {
                                bulkMode = mode;
                                $('#bulk_hidden_mode').val(mode);
                                calculateBulkSummary();
                            }

                            function calculateBulkSummary() {
                                let boxesPerCarton = parseInt($('#bulk_boxes_per_carton').val()) || 1;
                                let totalBoxes = 0;

                                if (bulkMode === 'set') {
                                    let targetInput = $('#bulk_target_sets');
                                    totalBoxes = parseInt(targetInput.val()) || 0;
                                    let avl = parseInt($('#bulk_avail_sets').text().replace('Avl: ', '')) || 0;

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
                                        avl = parseInt(selectedPiece.total_quantity) - (parseInt(selectedPiece.packed_qty) || 0);
                                    }
                                    $('#bulk_avail_pieces').text('Avl: ' + avl);
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
                                            let details = set.details_data || set.details || [];
                                            details.forEach(d => {
                                                let packed = parseInt(d.packed_qty) || 0;
                                                let remaining = parseInt(d.total_quantity) - packed;
                                                let qtyPerSet = d.qty_per_set || (parseInt(d.total_quantity) / (set.set_quantity || 1));
                                                if (qtyPerSet > 0) {
                                                    let setsRem = Math.floor(remaining / qtyPerSet);
                                                    if (minRemaining === null || setsRem < minRemaining) minRemaining = setsRem;
                                                }
                                            });
                                            totalBoxes += (minRemaining || 0);
                                        });
                                    }
                                } else if (bulkMode === 'full_loose') {
                                    totalBoxes = 0;
                                    if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                                        ORDER_ITEMS.forEach(item => {
                                            let remaining = (parseInt(item.total_quantity) - (parseInt(item.packed_qty) || 0));
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
                                    let minRemaining = null;
                                    let details = set.details_data || set.details || [];
                                    details.forEach(d => {
                                        let packed = parseInt(d.packed_qty) || 0;
                                        let remaining = parseInt(d.total_quantity) - packed;
                                        let qtyPerSet = d.qty_per_set || (parseInt(d.total_quantity) / (set.set_quantity || 1));
                                        if (qtyPerSet > 0) {
                                            let setsRem = Math.floor(remaining / qtyPerSet);
                                            if (minRemaining === null || setsRem < minRemaining) minRemaining = setsRem;
                                        }
                                    });
                                    let avlCount = (minRemaining || 0);
                                    $('#bulk_avail_sets').text('Avl: ' + avlCount);
                                    $('#bulk_target_sets').attr('max', avlCount);

                                    let html = '<table class="table table-sm table-bordered mb-0 bg-white"><thead><tr class="bg-secondary text-white"><th>Size</th><th>Qty</th></tr></thead><tbody>';
                                    let totalPcs = 0;
                                    let sizeSetArr = [];

                                    details.forEach(d => {
                                        let qty = parseInt(d.qty_per_set) || 0;
                                        if (qty > 0) {
                                            html += `<tr><td>${d.size}</td><td>${qty} pcs</td></tr>`;
                                            totalPcs += qty;
                                            for (let i = 0; i < qty; i++) {
                                                sizeSetArr.push(d.size);
                                            }
                                        }
                                    });

                                    html += `</tbody><tfoot><tr class="font-weight-bold"><td>Pieces/Box</td><td>${totalPcs} pcs</td></tr></tfoot></table>`;
                                    $preview.html(html);
                                    $hiddenInput.val(sizeSetArr.join(','));
                                    $container.show();
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
                                    if (target <= 0) { alert("Please enter sets to pack."); return; }
                                    if (target > avl) { alert("Quantity exceeds available sets (" + avl + ")."); return; }
                                    data.total_boxes = target;
                                } else if (bulkMode === 'loose') {
                                    let looseItemId = $('#bulkLooseItemSelect').val();
                                    let looseQty = parseInt($('#bulk_target_pieces').val()) || 0;
                                    let avl = parseInt($('#bulk_avail_pieces').text().replace('Avl: ', '')) || 0;
                                    if (!looseItemId) { alert("Please select an item."); return; }
                                    if (looseQty <= 0) { alert("Please enter pieces to pack."); return; }
                                    if (looseQty > avl) { alert("Quantity exceeds available pieces (" + avl + ")."); return; }

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

                                if (!data.boxes_per_carton || data.boxes_per_carton <= 0) { alert("Please enter carton capacity."); return; }
                                if (!$('#bulkStoreroomSelect').val()) { alert("Please select a Store Room."); return; }
                                if (!data.rack_id) { alert("Please select a Rack."); return; }

                                let $btn = $('button[onclick="submitBulkPacking()"]');
                                $btn.prop('disabled', true).text('Processing...');

                                $.ajax({
                                    url: "{{ route('admin.packing.bulk-save') }}",
                                    type: 'POST',
                                    data: data,
                                    success: function (response) {
                                        if (response.status === 'success') {
                                            alert(response.message);
                                            location.reload();
                                        } else {
                                            alert("Error: " + response.message);
                                            $btn.prop('disabled', false).text('Bulk Create');
                                        }
                                    },
                                    error: function () {
                                        alert("Something went wrong on the server.");
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
                                        alert("Box Created Successfully");
                                        location.reload();
                                    } else {
                                        alert("Error: " + response.message);
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
                                        alert(`Error: You cannot pack ${val} sets. Only ${max} remaining.`);
                                        error = true;
                                        return false;
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
                                        alert(`Error: You cannot pack ${val} items for size ${$(this).data('size-id')}. Only ${max} remaining.`);
                                        error = true; // Use simple var validation
                                        return false;
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
                                        alert("Carton Created Successfully");
                                        location.reload();
                                    } else {
                                        if (response.status === 'exists') {
                                            alert(response.message);
                                        } else {
                                            alert("Error: " + response.message);
                                        }
                                    }
                                });
                            }

                            function finalizePacking() {
                                if (!EXISTING_PACKING || !EXISTING_PACKING.id) {
                                    alert("No packing session found to finalize.");
                                    return;
                                }

                                if (!confirm("Are you sure you want to finalize this packing? This will mark it as complete.")) {
                                    return;
                                }

                                $.post("{{ route('admin.packing.finalize') }}", {
                                    _token: "{{ csrf_token() }}",
                                    packing_main_id: EXISTING_PACKING.id
                                }, function (response) {
                                    if (response.status === 'success') {
                                        alert("Packing Finalized Successfully!");
                                        window.location.href = "{{ route('admin.uploaded-slips.index') }}";
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
                                            alert("Carton deleted successfully.");
                                            location.reload();
                                        } else {
                                            alert("Error: " + response.message);
                                        }
                                    },
                                    error: function () {
                                        alert("Something went wrong on the server.");
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

                        </script>
    @endpush
@endsection