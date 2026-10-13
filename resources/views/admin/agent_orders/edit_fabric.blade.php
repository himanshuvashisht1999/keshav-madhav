@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <!-- HEADER BAR -->
            <div class="erp-header-bar mb-2">
                <div style="min-width: 0;">
                    <div class="erp-header-title text-truncate">
                        <i class="fas fa-edit text-primary"></i> Edit Fabric Order <span class="erp-party-name">#ORD-{{ $order->id }}</span>
                    </div>
                    <div class="erp-page-subtitle text-truncate">
                        <i class="fas fa-store mr-1"></i> {{ $shop->name }}
                        &nbsp;|&nbsp; <i class="fas fa-user-tie mr-1"></i>
                        {{ ($agent && $agent->id === 'direct') ? 'Direct Sale (No Agent)' : 'Agent: ' . ($agent->name ?? 'N/A') }}
                    </div>
                </div>
                <div class="erp-header-actions">
                    <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Order
                    </a>
                    <button type="button" class="btn-erp btn-erp-outline open-review-modal-btn" id="topReviewBtn" title="Review the selected rolls and update the order">
                        <i class="fas fa-arrow-circle-right mr-1"></i> Review &amp; Update <span class="badge badge-primary ml-1" id="topRollsBadge">0 Rolls</span>
                    </button>
                </div>
            </div>

            <!-- SELECTION BAR -->
            <div class="erp-filter-bar mb-2">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-1 fo-col-fabric">
                        <label class="erp-filter-label"><i class="fas fa-layer-group mr-1"></i> 1. Select Fabric</label>
                        <select id="fabricSelector" class="form-control select2">
                            <option value="">-- Choose Fabric --</option>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}" data-name="{{ $fabric->name }}">
                                    {{ $fabric->name }} ({{ number_format($fabric->total_meters, 2) }} m)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-ruler mr-1"></i> 2. Target Meter</label>
                        <input type="number" id="targetTotalMeter" class="form-control font-weight-bold" placeholder="0.00" step="0.01">
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-rupee-sign mr-1"></i> 3. Selling Price/m</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                            <input type="number" id="defaultPricePerMeter" class="form-control font-weight-bold" placeholder="0.00" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-4 mb-1 fo-col-rolls">
                        <label class="erp-filter-label"><i class="fas fa-scroll mr-1"></i> 4. Select Rolls</label>
                        <select id="rollSelector" class="form-control select2" multiple="multiple"
                            data-placeholder="Choose Fabric first..." disabled>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row fo-main">
                <!-- ORDER ITEMS -->
                <div class="col-12" style="margin-bottom: 75px;">
                    <div class="erp-card fo-items-card">
                        <div class="erp-card-header">
                            <h6 class="erp-card-title"><i class="fas fa-shopping-basket"></i> Order Items List</h6>
                            <span class="erp-stat-pill" id="totalRollsHeader">0 Rolls Added</span>
                        </div>
                        <div class="table-responsive">
                            <table class="erp-table table-bordered table-hover mb-0" id="masterTable">
                                <thead>
                                    <tr>
                                        <th>Fabric</th>
                                        <th width="140">Roll No</th>
                                        <th class="text-center" width="110">Avail. m</th>
                                        <th width="170">Order Meter</th>
                                        <th width="150" class="text-right">Price/m</th>
                                        <th width="150" class="text-right">Total</th>
                                        <th width="50" class="text-center">#</th>
                                    </tr>
                                </thead>
                                <tbody id="masterTableBody">
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center py-5">
                                            <div class="fo-empty">
                                                <i class="fas fa-layer-group fa-3x mb-3 d-block"></i>
                                                Select fabric and rolls above to start building your order.
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating bottom summary bar -->
        <div class="fixed-bottom bg-white border-top py-2 px-3" id="fabricSummaryBar" style="z-index: 1040; left: 250px;">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="mr-3">
                        <span class="erp-sum-label mr-1">Rolls:</span>
                        <span class="badge badge-primary px-2 py-1" style="font-size: 13px;"><span id="bottomRolls">0</span></span>
                    </div>
                    <div class="mr-3 border-left pl-3">
                        <span class="erp-sum-label mr-1">Meters:</span>
                        <span class="font-weight-bold"><span id="bottomMeters">0.00</span> m</span>
                    </div>
                    <div class="mr-3 border-left pl-3 d-none d-sm-inline-block">
                        <span class="erp-sum-label mr-1">Subtotal:</span>
                        <span class="font-weight-bold">₹<span id="bottomSubTotal">0.00</span></span>
                    </div>
                    <div class="mr-3 border-left pl-3">
                        <span class="erp-sum-label mr-1">Est. Grand Total:</span>
                        <span class="erp-sum-total">₹<span id="bottomGrandTotal">0.00</span></span>
                    </div>
                    <div class="border-left pl-3">
                        <span class="erp-sum-label mr-1">Targets:</span>
                        <span class="badge-erp-status badge-erp-secondary" id="bottomTargets">Not set</span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn-erp btn-erp-primary open-review-modal-btn">
                        Next: Review &amp; Update Order <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Review & Place Order Modal -->
        <div class="modal fade erp-modal" id="fabricCheckoutModal" tabindex="-1" role="dialog" aria-labelledby="fabricCheckoutModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 920px;">
                <div class="modal-content border-0">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0" id="fabricCheckoutModalLabel">
                                <i class="fas fa-clipboard-check mr-2"></i> Review &amp; Update Fabric Order #ORD-{{ $order->id }}
                            </h5>
                            <small class="erp-modal-sub">
                                Party: <strong>{{ $shop->name }}</strong> &nbsp;•&nbsp;
                                {{ ($agent && $agent->id === 'direct') ? 'Direct Sale (No Agent)' : 'Agent: ' . ($agent->name ?? 'N/A') }}
                            </small>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-3">
                        <div class="row">
                            <!-- Left: targets -->
                            <div class="col-md-5 mb-3 mb-md-0 border-right">
                                <div id="fabricTargetsSummary">
                                    <div class="erp-section-title mb-2"><i class="fas fa-bullseye mr-1"></i> Fabric Target Coverage</div>
                                    <div id="targetList" class="small erp-soft-box p-2">
                                        <div class="text-muted text-center py-2 italicText">No targets set yet</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                    <span class="erp-sum-label">Total Rolls:</span>
                                    <span class="badge badge-primary px-2 py-1" style="font-size: 13px;"><span id="modalRolls">0</span> Rolls &bull; <span id="modalMeters">0.00</span> m</span>
                                </div>
                            </div>

                            <!-- Right: money -->
                            <div class="col-md-7">
                                <span class="erp-section-title d-block mb-2"><i class="fas fa-calculator mr-1"></i> Financial Summary</span>
                                <div class="erp-soft-box p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="erp-sum-label">Subtotal:</span>
                                        <span class="font-weight-bold h6 mb-0">₹<span id="subTotalAmount">0.00</span></span>
                                    </div>
                                    <div class="row align-items-center mb-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-sm" title="Discount Percentage">
                                                <div class="input-group-prepend"><span class="input-group-text">Disc %</span></div>
                                                <input type="number" id="discountPercentage" class="form-control text-right font-weight-bold" value="{{ $order->discount_percentage }}" min="0" max="100" step="any">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm" title="Discount Amount">
                                                <div class="input-group-prepend"><span class="input-group-text">Disc ₹</span></div>
                                                <input type="number" id="discountAmountInput" class="form-control text-right font-weight-bold" value="{{ $order->discount_amount }}" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-2">
                                        <span class="erp-sum-label">Taxable Amount:</span>
                                        <span class="font-weight-bold h6 mb-0">₹<span id="taxableAmount">0.00</span></span>
                                    </div>
                                    <div class="row align-items-center mb-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-sm" title="GST Percentage">
                                                <div class="input-group-prepend"><span class="input-group-text">GST %</span></div>
                                                <input type="number" id="gstPercentage" class="form-control text-right font-weight-bold" value="{{ $order->gst_percentage }}" min="0" max="100" step="any">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm" title="GST Amount">
                                                <div class="input-group-prepend"><span class="input-group-text">GST ₹</span></div>
                                                <input type="number" id="gstAmountInput" class="form-control text-right font-weight-bold" value="{{ $order->gst_amount }}" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-3">
                                        <div class="col-6"><span class="erp-sum-label">Other Charges:</span></div>
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                                <input type="number" id="other_charges" class="form-control text-right font-weight-bold" value="{{ $order->other_charges ?? 0 }}" min="0" step="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="erp-grand-box text-center">
                                        <small class="text-uppercase font-weight-bold d-block">Grand Total</small>
                                        <span class="erp-grand-val">₹<span id="grandTotalAmount">0.00</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logistics -->
                        <div class="border-top pt-3 mt-3">
                            <span class="erp-section-title d-block mb-2"><i class="fas fa-truck mr-1"></i> Order &amp; Logistics Details</span>
                            <div class="row">
                                <div class="col-md-3 col-6 mb-2">
                                    <label class="erp-label">Expected Dispatch</label>
                                    <input type="date" id="expectedDispatchDate" class="form-control form-control-sm" value="{{ $order->expected_dispatch_date ? date('Y-m-d', strtotime($order->expected_dispatch_date)) : '' }}">
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <label class="erp-label">Status</label>
                                    <select id="orderStatus" class="form-control form-control-sm">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                        <option value="delayed" {{ $order->status == 'delayed' ? 'selected' : '' }}>DELAYED</option>
                                        <option value="partially_dispatched" {{ $order->status == 'partially_dispatched' ? 'selected' : '' }}>PARTIALLY DISPATCHED</option>
                                        <option value="dispatched" {{ $order->status == 'dispatched' ? 'selected' : '' }}>DISPATCHED</option>
                                    </select>
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <label class="erp-label">Booking Station</label>
                                    <input type="text" id="booking_station" class="form-control form-control-sm" placeholder="e.g. Surat" value="{{ $order->booking_station }}">
                                </div>
                                <div class="col-md-3 col-6 mb-2">
                                    <label class="erp-label">Transport</label>
                                    <input type="text" id="transport" class="form-control form-control-sm" placeholder="Transport Name" value="{{ $order->transport }}">
                                </div>
                                <div class="col-12 mb-1">
                                    <label class="erp-label">Order Remark / Notes</label>
                                    @php
                                        $previousRemarks = \DB::table('agent_orders')->whereNotNull('remark')->where('remark', '!=', '')->distinct()->pluck('remark');
                                    @endphp
                                    <input type="text" id="orderRemark" class="form-control form-control-sm" list="previous_remarks_list" placeholder="Instructions..." value="{{ $order->remark }}" autocomplete="off">
                                    <datalist id="previous_remarks_list">
                                        @foreach($previousRemarks as $rem)
                                            <option value="{{ $rem }}">
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Selection
                        </button>
                        <button type="button" class="btn-erp btn-erp-primary px-4 update-fabric-order-btn" id="placeOrderBtn" disabled title="Fulfill targets to enable">
                            UPDATE ORDER <i class="fas fa-save ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ---- Fabric order create: brand look ---- */
        .erp-page .erp-party-name { color: var(--erp-green-primary); }
        .erp-page .erp-filter-bar .select2-container--bootstrap4 .select2-selection--multiple { min-height: 34px; padding: 0 6px; }
        .erp-page .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background-color: var(--erp-green-primary); border-color: var(--erp-green-hover); border-radius: 4px; color: #fff; padding: 1px 8px; margin-top: 4px; font-size: 12px;
        }
        .erp-page .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove { color: var(--brand-yellow, #fcee21); }
        .erp-page .erp-filter-bar .input-group-text { background: var(--erp-green-light); color: var(--erp-green-primary); font-weight: 700; }
        .erp-page .erp-section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: var(--erp-green-primary); border-bottom: 1px solid var(--erp-green-border); padding-bottom: 3px; }
        .erp-page .erp-sum-label { font-size: 12px; font-weight: 700; color: var(--erp-text-subtle); }
        .erp-page .erp-soft-box { background: var(--erp-green-light); border: 1px solid var(--erp-green-border); border-radius: 6px; }
        .erp-page .erp-grand-box { background: var(--erp-green-primary); color: #fff; border-radius: 6px; padding: 8px; border-bottom: 4px solid var(--brand-yellow, #fcee21); }
        .erp-page .erp-grand-box small { color: rgba(255,255,255,.75); letter-spacing: .5px; }
        .erp-page .erp-grand-val { font-size: 24px; font-weight: 800; color: var(--brand-yellow, #fcee21); }
        .erp-page .fo-summary .input-group-text { font-size: 11px; font-weight: 700; padding: 0 6px; background: #fff; color: var(--erp-green-primary); }
        .erp-page .fo-summary .form-control { height: 33px; }
        .erp-page .fo-summary .input-group-sm .form-control { height: 30px; padding: 2px 6px; }
        .erp-page .fo-items-card { min-height: 420px; }
        .erp-page .fo-empty { color: var(--erp-text-subtle); font-size: 14px; }
        .erp-page .fo-empty i { color: var(--erp-green-border); opacity: .7; }
        .erp-page #masterTable td, .erp-page #masterTable th { vertical-align: middle !important; padding: 5px 8px !important; }
        .erp-page #masterTable .order-meter, .erp-page #masterTable .roll-price { height: 30px; font-weight: 700; }
        .erp-page #masterTable .input-group-text { background: var(--erp-green-light); color: var(--erp-green-primary); font-weight: 700; padding: 0 8px; }
        .erp-page .badge-fabric { background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-weight: 700; border-radius: 10px; }
        .erp-page #masterTable .remove-item { width: 26px; height: 26px; padding: 0; line-height: 1; color: #b91c1c; border-color: #fca5a5; }
        .erp-page #masterTable .remove-item:hover { background: #b91c1c; color: #fff; }
        .erp-page .badge-success { background: #36b54a; }
        .erp-page .badge-warning { background: var(--brand-yellow, #fcee21); color: var(--erp-green-primary); }
                        /* ---- fabric order: summary bar + modal (same look as the item order page) ---- */
        .erp-page .erp-sum-total { font-size: 18px; font-weight: 800; color: var(--erp-green-primary); }
        #fabricSummaryBar { border-top: 3px solid var(--brand-yellow, #fcee21) !important; box-shadow: 0 -4px 15px rgba(5,66,28,.12) !important; transition: left .3s; }
        @media (max-width: 991px) { #fabricSummaryBar { left: 0 !important; } }
        #fabricCheckoutModal .modal-content { border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(5,66,28,.25); }
        #fabricCheckoutModal .modal-header { background: var(--erp-green-primary); color: #fff; padding: 10px 16px; border-bottom: 4px solid var(--brand-yellow, #fcee21); align-items: center; }
        #fabricCheckoutModal .modal-title { font-size: 15px; font-weight: 700; color: #fff; }
        #fabricCheckoutModal .erp-modal-sub, #fabricCheckoutModal .erp-modal-sub strong { color: rgba(255,255,255,.85); }
        #fabricCheckoutModal .modal-header .close { color: #fff; opacity: .9; text-shadow: none; }
        #fabricCheckoutModal .modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 8px 16px; }
        #fabricCheckoutModal .form-control { height: 33px; font-size: 13px; border-radius: 4px; border: 1px solid #cbd5e1; }
        #fabricCheckoutModal .form-control:focus { border-color: var(--erp-green-primary); box-shadow: 0 0 0 2px rgba(5,66,28,.15); }
        #fabricCheckoutModal .input-group-text { background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid #cbd5e1; font-size: 12px; font-weight: 700; }
        #fabricCheckoutModal .erp-label { display: block; font-size: 12px; font-weight: 700; color: var(--erp-green-primary); margin-bottom: 3px; }
        #fabricCheckoutModal .erp-section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: var(--erp-green-primary); }
        #fabricCheckoutModal .erp-sum-label { font-size: 12px; font-weight: 700; color: var(--erp-text-subtle); }
        #fabricCheckoutModal .erp-soft-box { background: var(--erp-green-light); border: 1px solid var(--erp-green-border); border-radius: 6px; }
        #fabricCheckoutModal .erp-grand-box { background: var(--erp-green-primary); color: #fff; border-radius: 6px; padding: 8px; border-bottom: 4px solid var(--brand-yellow, #fcee21); }
        #fabricCheckoutModal .erp-grand-box small { color: rgba(255,255,255,.75); letter-spacing: .5px; }
        #fabricCheckoutModal .erp-grand-val { font-size: 24px; font-weight: 800; color: var(--brand-yellow, #fcee21); }
        #fabricCheckoutModal .badge-primary { background: var(--erp-yellow-bg, #fffde0); color: var(--erp-green-primary); border: 1px solid var(--erp-yellow-badge-border); }
        #fabricCheckoutModal .badge-success { background: #36b54a; }
        #fabricCheckoutModal .badge-warning { background: var(--brand-yellow, #fcee21); color: var(--erp-green-primary); }
        #fabricCheckoutModal .update-fabric-order-btn:disabled { opacity: .55; cursor: not-allowed; }
        .roll-item { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        const gstPercent = {{ $gst_percentage }};
        const allItems = new Map(); // Global storage for all added rolls: ID -> {fabric_id, data}
        const fabricTargets = new Map(); // fabric_id -> {name, target, price}
        let fabricData = {}; // Cache for rolls data fetched via AJAX
        let discount_mode = 'percentage';
        let gst_mode = 'percentage';

        // Load existing items
        @foreach($existing_items as $item)
            allItems.set("{{ $item->fabric_receipt_detail_id }}", {
                fabric_id: "{{ $item->fabric_id }}",
                fabric_name: "{{ $item->fabric_name }}",
                roll_id: "{{ $item->fabric_receipt_detail_id }}",
                roll_no: "{{ $item->roll_number }}",
                batch: "{{ $item->batch_no }}",
                avail_meter: parseFloat("{{ $item->avail_now }}") + parseFloat("{{ $item->meter }}"), // Total avail at time of selection
                price: parseFloat("{{ $item->selling_price }}"),
                order_meter: parseFloat("{{ $item->meter }}"),
                is_dispatched: {{ (!empty($item->dispatched_at) || $item->status === 'dispatched') ? 'true' : 'false' }}
            });
            addRollToTable("{{ $item->fabric_receipt_detail_id }}", allItems.get("{{ $item->fabric_receipt_detail_id }}"));
            
            // Infer target as sum of existing meters for that fabric
            if (!fabricTargets.has("{{ $item->fabric_id }}")) {
                fabricTargets.set("{{ $item->fabric_id }}", {
                    name: "{{ $item->fabric_name }}",
                    target: 0,
                    price: parseFloat("{{ $item->selling_price }}")
                });
            }
            fabricTargets.get("{{ $item->fabric_id }}").target += parseFloat("{{ $item->meter }}");
        @endforeach

        calculateTotals();

        // 0. Handle Target & Price Changes
        $(document).on('input', '#targetTotalMeter, #defaultPricePerMeter', function() {
            const fabricId = $('#fabricSelector').val();
            const fabricName = $('#fabricSelector option:selected').data('name');
            if (fabricId) {
                fabricTargets.set(fabricId.toString(), {
                    name: fabricName,
                    target: parseFloat($('#targetTotalMeter').val()) || 0,
                    price: parseFloat($('#defaultPricePerMeter').val()) || 0
                });
                calculateTotals();
            }
        });

        // 1. Fabric Selection -> Fetch Rolls
        $('#fabricSelector').on('change', function() {
            const fabricId = $(this).val();
            const rollSelector = $('#rollSelector');
            const targetInput = $('#targetTotalMeter');
            const priceInput = $('#defaultPricePerMeter');

            if (!fabricId) {
                rollSelector.empty().prop('disabled', true).trigger('change');
                targetInput.val('');
                priceInput.val('');
                return;
            }

            // Restore target & price for this fabric if exists
            const existing = fabricTargets.get(fabricId.toString());
            targetInput.val(existing ? existing.target : '');
            priceInput.val(existing ? existing.price : '');

            rollSelector.prop('disabled', true).html('<option>Loading rolls...</option>');
            
            $.get("{{ route('admin.agent-orders.get-fabric-rolls', '') }}/" + fabricId, function(data) {
                rollSelector.empty().prop('disabled', false);
                fabricData = {}; // Clear current cache
                
                data.forEach(roll => {
                    fabricData[roll.id] = roll;
                    // Only show rolls not already in table
                    if (!allItems.has(roll.id.toString())) {
                        const rollText = roll.roll_number + (roll.batch_no ? ' [Batch: ' + roll.batch_no + ']' : '') + ' (' + roll.remaining_quantity + ' m)';
                        rollSelector.append(`<option value="${roll.id}">${rollText}</option>`);
                    }
                });
                
                if (data.length === 0) {
                     rollSelector.html('<option disabled>No rolls available</option>').prop('disabled', true);
                }
                
                rollSelector.trigger('change');
            });
        });

        // 2. Reactive Roll Selection
        $('#rollSelector').on('select2:select', function(e) {
            const id = e.params.data.id;
            const roll = fabricData[id];
            const fabricId = $('#fabricSelector').val();
            const fabricName = $('#fabricSelector option:selected').data('name');

            if (roll && !allItems.has(id.toString())) {
                const targetObj = fabricTargets.get(fabricId.toString());
                const target = targetObj ? targetObj.target : 0;
                
                let currentTotalForFab = 0;
                $('.roll-item[data-fabric-id="'+fabricId+'"]').each(function() {
                    currentTotalForFab += parseFloat($(this).find('.order-meter').val()) || 0;
                });

                if (target > 0 && currentTotalForFab >= target) {
                    Swal.fire('Target Reached', 'You have already reached the target for ' + fabricName, 'warning');
                }

                let suggestedMeter = roll.remaining_quantity;
                if (target > 0) {
                    const remaining = target - currentTotalForFab;
                    if (remaining > 0) {
                        suggestedMeter = Math.min(roll.remaining_quantity, remaining);
                    } else {
                        suggestedMeter = 0;
                    }
                }

                const item = {
                    fabric_id: fabricId,
                    fabric_name: fabricName,
                    roll_id: roll.id,
                    roll_no: roll.roll_number,
                    batch: roll.batch_no,
                    avail_meter: parseFloat(roll.remaining_quantity) || 0,
                    price: targetObj ? targetObj.price : (parseFloat(roll.price_per_meter) || 0),
                    order_meter: parseFloat(suggestedMeter) || 0
                };
                allItems.set(id.toString(), item);
                addRollToTable(id, item);
                calculateTotals();
            }
        });

        $('#rollSelector').on('select2:unselect', function(e) {
            const id = e.params.data.id.toString();
            $(`#row_${id}`).remove();
            allItems.delete(id);
            if (allItems.size === 0) $('#emptyRow').show();
            calculateTotals();
        });

        function addRollToTable(id, item) {
            $('#emptyRow').hide();
            const avail = parseFloat(item.avail_meter) || 0;
            let order = avail;
            if (item.order_meter !== undefined && item.order_meter !== null) {
                order = parseFloat(item.order_meter);
            }
            const isDisp = item.is_dispatched === true;

            const html = `
                <tr id="row_${id}" class="roll-item" data-id="${id}" data-fabric-id="${item.fabric_id}" data-dispatched="${isDisp ? 1 : 0}">
                    <td>
                        <div class="badge badge-fabric border px-2 py-1">${item.fabric_name}</div>
                        ${isDisp ? '<span class="badge badge-warning text-dark ml-1 font-weight-bold"><i class="fas fa-truck mr-1"></i>Dispatched</span>' : ''}
                    </td>
                    <td><span class="font-weight-bold">#${item.roll_no}</span><br><small class="text-muted">${item.batch || 'No Batch'}</small></td>
                    <td class="text-center font-weight-bold">${avail.toFixed(2)}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control order-meter" value="${order.toFixed(2)}" min="${isDisp ? order.toFixed(2) : 0}" max="${avail}" step="0.01" ${isDisp ? 'readonly' : ''}>
                            <div class="input-group-append"><span class="input-group-text">m</span></div>
                        </div>
                        ${isDisp ? '<small class="text-muted font-italic" style="font-size:10px;">Locked</small>' : ''}
                    </td>
                    <td class="text-right">
                        <input type="number" class="form-control form-control-sm text-right roll-price" value="${(parseFloat(item.price) || 0).toFixed(2)}" step="0.01" ${isDisp ? 'readonly' : ''}>
                    </td>
                    <td class="text-right font-weight-bold text-dark">₹<span class="row-total">0.00</span></td>
                    <td class="text-center">
                        ${isDisp 
                            ? '<button type="button" class="btn btn-xs btn-secondary" disabled title="Already dispatched roll cannot be removed"><i class="fas fa-lock"></i></button>' 
                            : `<button type="button" class="btn btn-xs btn-outline-danger remove-item" data-id="${id}"><i class="fas fa-trash-alt"></i></button>`
                        }
                    </td>
                </tr>
            `;
            $('#masterTableBody').append(html);
        }

        $(document).on('click', '.remove-item', function() {
            const id = $(this).data('id').toString();
            const item = allItems.get(id);
            if (item && item.is_dispatched) {
                Swal.fire('Locked', 'This roll has already been dispatched and cannot be removed.', 'warning');
                return;
            }
            const fabricRow = $(`#row_${id}`);
            const fabricId = fabricRow.data('fabric-id')?.toString();
            const currentFabricId = $('#fabricSelector').val()?.toString();

            // 1. Remove from table
            fabricRow.remove();
            
            // 2. Remove from global storage
            allItems.delete(id);

            // 3. If no rolls left for this fabric, remove the target requirement too
            let hasMoreRolls = false;
            allItems.forEach(item => {
                if (item.fabric_id.toString() === fabricId) hasMoreRolls = true;
            });
            if (!hasMoreRolls) {
                fabricTargets.delete(fabricId);
            }

            // 4. If it belongs to current fabric, unselect in dropdown
            if (fabricId === currentFabricId) {
                const selectedValues = $('#rollSelector').val() || [];
                const updatedValues = selectedValues.filter(val => val !== id);
                $('#rollSelector').val(updatedValues).trigger('change');
            }

            if (allItems.size === 0) $('#emptyRow').show();
            calculateTotals();
        });


            function calculateTotals() {
                let subTotal = 0;
                let rollCount = 0;
                const fabricAggr = {};

                $('.roll-item').each(function() {
                    const fid = $(this).data('fabric-id').toString();
                    const meter = parseFloat($(this).find('.order-meter').val()) || 0;
                    const price = parseFloat($(this).find('.roll-price').val()) || 0;
                    const total = meter * price;
                    
                    $(this).find('.row-total').text(total.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                    
                    subTotal += total;
                    rollCount++;
                    
                    if (!fabricAggr[fid]) fabricAggr[fid] = 0;
                    fabricAggr[fid] += meter;
                });

                const targetList = $('#targetList');
                targetList.empty();
                let allTargetsMet = true;

                fabricTargets.forEach((data, fid) => {
                    const actual = fabricAggr[fid] || 0;
                    const target = data.target;
                    if (target <= 0) return;

                    const diff = actual - target;
                    let statusHtml = '';
                    if (Math.abs(diff) < 0.01) {
                        statusHtml = '<span class="badge badge-success">OK</span>';
                    } else if (diff > 0) {
                        statusHtml = `<span class="badge badge-danger">+${diff.toFixed(2)}m OVER</span>`;
                        allTargetsMet = false;
                    } else {
                        statusHtml = `<span class="badge badge-warning">${Math.abs(diff).toFixed(2)}m SHORT</span>`;
                        allTargetsMet = false;
                    }

                    targetList.append(`
                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                            <div>
                                <div class="font-weight-bold">${data.name}</div>
                                <div class="text-muted small">${actual.toFixed(2)} / ${target.toFixed(2)} m</div>
                            </div>
                            <div>${statusHtml}</div>
                        </div>
                    `);
                });

                if (targetList.is(':empty')) {
                    targetList.html('<div class="text-muted text-center py-2 italicText">No targets set yet</div>');
                }

                const otherCharges = parseFloat($('#other_charges').val()) || 0;
                let discountAmount = 0;
                let discountPercent = parseFloat($('#discountPercentage').val()) || 0;

                if (discount_mode === 'amount') {
                    discountAmount = parseFloat($('#discountAmountInput').val()) || 0;
                    if (!$('#discountPercentage').is(':focus') && subTotal > 0) {
                        $('#discountPercentage').val((discountAmount / subTotal * 100).toFixed(6));
                    }
                } else {
                    discountAmount = subTotal * (discountPercent / 100);
                    if (!$('#discountAmountInput').is(':focus')) {
                        $('#discountAmountInput').val(discountAmount.toFixed(2));
                    }
                }

                const taxableAmount = subTotal - discountAmount;
                let gstAmount = 0;
                let gstPercent = parseFloat($('#gstPercentage').val()) || 0;

                if (gst_mode === 'amount') {
                    gstAmount = parseFloat($('#gstAmountInput').val()) || 0;
                    if (!$('#gstPercentage').is(':focus') && taxableAmount > 0) {
                        $('#gstPercentage').val((gstAmount / taxableAmount * 100).toFixed(6));
                    }
                } else {
                    gstAmount = taxableAmount * (gstPercent / 100);
                    if (!$('#gstAmountInput').is(':focus')) {
                        $('#gstAmountInput').val(gstAmount.toFixed(2));
                    }
                }

                const grandTotal = Math.ceil(taxableAmount + gstAmount + otherCharges);

                $('#totalRollsHeader').text(rollCount + ' Rolls Added');
                $('#subTotalAmount').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                $('#taxableAmount').text(taxableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                $('#gstAmount').text(gstAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                $('#grandTotalAmount').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

                // bottom bar / header / modal mirrors
                let totalMeters = 0;
                $('.roll-item').each(function () { totalMeters += parseFloat($(this).find('.order-meter').val()) || 0; });
                $('#bottomRolls, #modalRolls').text(rollCount);
                $('#topRollsBadge').text(rollCount + (rollCount === 1 ? ' Roll' : ' Rolls'));
                $('#bottomMeters, #modalMeters').text(totalMeters.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#bottomSubTotal').text(subTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                $('#bottomGrandTotal').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                const tgtBadge = $('#bottomTargets');
                let tgtCount = 0; fabricTargets.forEach(d => { if (d.target > 0) tgtCount++; });
                if (tgtCount === 0) { tgtBadge.attr('class', 'badge-erp-status badge-erp-secondary').text('Not set'); }
                else if (allTargetsMet) { tgtBadge.attr('class', 'badge-erp-status badge-erp-success').text('Met'); }
                else { tgtBadge.attr('class', 'badge-erp-status badge-erp-warning').text('Not met'); }

                const canUpdateOrder = rollCount > 0 && allTargetsMet;
                $('#placeOrderBtn').prop('disabled', !canUpdateOrder);
                
                if (rollCount > 0 && !allTargetsMet) {
                    $('#placeOrderBtn').attr('title', 'All targets must be met exactly to update order');
                } else {
                    $('#placeOrderBtn').removeAttr('title');
                }
            }

        $(document).on('input', '.order-meter, .roll-price, #targetTotalMeter, #other_charges', function() {
            if ($(this).hasClass('order-meter')) {
                const max = parseFloat($(this).attr('max')) || 0;
                let val = parseFloat($(this).val()) || 0;
                if (val > max) $(this).val(max);
            }
            calculateTotals();
        });

        $(document).on('input', '#discountPercentage', function() {
            discount_mode = 'percentage';
            calculateTotals();
        });

        $(document).on('input', '#discountAmountInput', function() {
            discount_mode = 'amount';
            calculateTotals();
        });

        $(document).on('input', '#gstPercentage', function() {
            gst_mode = 'percentage';
            calculateTotals();
        });

        $(document).on('input', '#gstAmountInput', function() {
            gst_mode = 'amount';
            calculateTotals();
        });

        // Open the review / amounts modal (like the item order page)
        $(document).on('click', '.open-review-modal-btn', function () {
            if ($('.roll-item').length === 0) {
                Swal.fire('No rolls selected', 'Please select a fabric and add at least one roll first.', 'warning');
                return;
            }
            calculateTotals();
            $('#fabricCheckoutModal').modal('show');
        });

        $('.update-fabric-order-btn').click(function() {
            const btn = $(this);
            
            const fabricAggr = {};
            $('.roll-item').each(function() {
                const fid = $(this).data('fabric-id').toString();
                const meter = parseFloat($(this).find('.order-meter').val()) || 0;
                fabricAggr[fid] = (fabricAggr[fid] || 0) + meter;
            });

            let validationErrors = [];
            fabricTargets.forEach((data, fid) => {
                const actual = fabricAggr[fid] || 0;
                const target = data.target;
                if (target <= 0) return;
                const diff = actual - target;
                if (Math.abs(diff) >= 0.01) {
                    validationErrors.push(`${data.name}: Target is ${target}m but selected ${actual.toFixed(2)}m (${diff > 0 ? 'OVER' : 'SHORT'})`);
                }
            });

            if (validationErrors.length > 0) {
                Swal.fire({ title: 'Target Mismatch', html: `<ul class="text-left small">${validationErrors.map(e => `<li>${e}</li>`).join('')}</ul>`, icon: 'error' });
                return;
            }

            const selections = [];
            $('.roll-item').each(function() {
                selections.push({
                    fabric_id: $(this).data('fabric-id'),
                    roll_id: $(this).data('id'),
                    meter: parseFloat($(this).find('.order-meter').val()),
                    price: parseFloat($(this).find('.roll-price').val())
                });
            });

            if (selections.length === 0) return;

            Swal.fire({
                title: 'Update Order?',
                text: "Existing items will be replaced with this selection.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                confirmButtonText: 'Yes, Update Order'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-sync fa-spin"></i> Updating...');
                    $.ajax({
                        url: "{{ route('admin.agent-orders.update', $order->id) }}",
                        method: "PUT",
                        data: {
                            _token: "{{ csrf_token() }}",
                            sale_type: "fabric",
                            rolls: selections,
                            discount_percentage: $('#discountPercentage').val(),
                            discount_amount: $('#discountAmountInput').val(),
                            gst_percentage: $('#gstPercentage').val(),
                            gst_amount: $('#gstAmountInput').val(),
                            other_charges: $('#other_charges').val(),
                            expected_dispatch_date: $('#expectedDispatchDate').val(),
                            status: $('#orderStatus').val(),
                            booking_station: $('#booking_station').val(),
                            transport: $('#transport').val(),
                            remark: $('#orderRemark').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Updated!', response.message, 'success').then(() => {
                                    window.location.href = response.redirect_url;
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE ORDER');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Server error', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE ORDER');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
