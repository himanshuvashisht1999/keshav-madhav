@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark"><i class="fas fa-edit mr-2 text-warning"></i>Edit Fabric Order: #{{ $order->id }}</h1>
                    <p class="text-muted small mb-0"><i class="fas fa-store mr-1"></i> {{ $shop->name }} | <i class="fas fa-user-tie mr-1"></i> {{ ($agent && $agent->id === 'direct') ? 'Direct Sale' : 'Agent: ' . ($agent->name ?? 'N/A') }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.agent-orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Listing
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- MAIN SELECTION CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted uppercase">1. Select Fabric</label>
                                    <select id="fabricSelector" class="form-control select2">
                                        <option value="">-- Choose Fabric --</option>
                                        @foreach($fabrics as $fabric)
                                            <option value="{{ $fabric->id }}" data-name="{{ $fabric->name }}">
                                                {{ $fabric->name }} ({{ number_format($fabric->total_meters, 2) }} m)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted uppercase">2. Target Meter</label>
                                    <div class="input-group">
                                        <input type="number" id="targetTotalMeter" class="form-control font-weight-bold border-primary" placeholder="0.00" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted uppercase">3. Selling Price/m</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">₹</span></div>
                                        <input type="number" id="defaultPricePerMeter" class="form-control font-weight-bold border-success" placeholder="0.00" step="0.01">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-md-0">
                                    <label class="small font-weight-bold text-muted uppercase">4. Select Rolls</label>
                                    <select id="rollSelector" class="form-control select2" multiple="multiple" data-placeholder="Choose Fabric first..." disabled>
                                    </select>
                                </div>
                            </div>
                        </div>
                </div>
            </div>

            <div class="row">
                <!-- Data Table -->
                <div class="col-md-9">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; min-height: 500px;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-shopping-basket mr-2 text-primary"></i>Order Items List</h6>
                            <span class="badge badge-info py-2 px-3 rounded-pill" id="totalRollsHeader">0 Rolls Added</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="masterTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Fabric</th>
                                            <th width="120">Roll No</th>
                                            <th class="text-center">Avail. m</th>
                                            <th width="130">Order Meter</th>
                                            <th width="130" class="text-right">Price/m</th>
                                            <th width="130" class="text-right">Total</th>
                                            <th width="50" class="text-center">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="masterTableBody">
                                        <tr id="emptyRow">
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-layer-group fa-3x mb-3 d-block opacity-25"></i>
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

                <!-- Right Summary Column -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px; border-radius: 15px; overflow: hidden;">
                         <div class="card-header bg-dark text-white py-3">
                            <h6 class="mb-0 font-weight-bold">Summary & Checkout</h6>
                        </div>
                        <div class="card-body">
                            <!-- Dynamic Per-Fabric Targets Summary -->
                            <div id="fabricTargetsSummary" class="mb-4">
                                <label class="small font-weight-bold text-muted uppercase border-bottom pb-1 mb-2 d-block">Fabric Target Coverage</label>
                                <div id="targetList" class="small">
                                    <div class="text-muted text-center py-2 italicText">No targets set yet</div>
                                </div>
                            </div>

                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="font-weight-bold">₹<span id="subTotalAmount">0.00</span></span>
                                </li>
                                <li class="list-group-item px-0 py-1 border-0">
                                    <div class="input-group input-group-sm mb-1" title="Discount Percentage">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text py-0 px-1" style="font-size: 10px; width: 45px;">Disc %</span>
                                        </div>
                                        <input type="number" id="discountPercentage" class="form-control text-right h-auto py-1 px-1" 
                                            style="font-weight: bold;" value="{{ $order->discount_percentage }}" min="0" max="100" step="any">
                                    </div>
                                    <div class="input-group input-group-sm" title="Discount Amount">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text py-0 px-1" style="font-size: 10px; width: 45px;">Disc ₹</span>
                                        </div>
                                        <input type="number" id="discountAmountInput" class="form-control text-right h-auto py-1 px-1" 
                                            style="font-weight: bold;" value="{{ $order->discount_amount }}" min="0">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-1 border-0">
                                    <span class="text-muted small">Taxable:</span>
                                    <span class="font-weight-bold small">₹<span id="taxableAmount">0.00</span></span>
                                </li>
                                <li class="list-group-item px-0 py-1 border-0">
                                    <div class="input-group input-group-sm mb-1" title="GST Percentage">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text py-0 px-1" style="font-size: 10px; width: 45px;">GST %</span>
                                        </div>
                                        <input type="number" id="gstPercentage" class="form-control text-right h-auto py-1 px-1" 
                                            style="font-weight: bold;" value="{{ $order->gst_percentage }}" min="0" max="100" step="any">
                                    </div>
                                    <div class="input-group input-group-sm" title="GST Amount">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text py-0 px-1" style="font-size: 10px; width: 45px;">GST ₹</span>
                                        </div>
                                        <input type="number" id="gstAmountInput" class="form-control text-right h-auto py-1 px-1" 
                                            style="font-weight: bold;" value="{{ $order->gst_amount }}" min="0">
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0 align-items-center">
                                    <span class="text-muted">Other Charges:</span>
                                    <input type="number" id="other_charges" class="form-control form-control-sm text-right w-50 border-info" value="{{ $order->other_charges ?? 0 }}" min="0" step="1">
                                </li>
                                <li class="list-group-item d-flex justify-content-between px-0 py-3 border-top mt-2">
                                    <span class="h5 mb-0 font-weight-bold">Grand Total:</span>
                                    <span class="h5 mb-0 font-weight-bold text-primary">₹<span id="grandTotalAmount">0.00</span></span>
                                </li>
                            </ul>

                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Expected Dispatch</label>
                                <input type="date" id="expectedDispatchDate" class="form-control" value="{{ $order->expected_dispatch_date ? date('Y-m-d', strtotime($order->expected_dispatch_date)) : '' }}">
                            </div>


                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted uppercase">Status</label>
                                <select id="orderStatus" class="form-control">
                                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="delayed" {{ $order->status == 'delayed' ? 'selected' : '' }}>DELAYED</option>
                                    <option value="dispatched" {{ $order->status == 'dispatched' ? 'selected' : '' }}>DISPATCHED</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6 pr-1">
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted uppercase">Station</label>
                                        <input type="text" id="booking_station" class="form-control" placeholder="Booking" value="{{ $order->booking_station }}">
                                    </div>
                                </div>
                                <div class="col-6 pl-1">
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted uppercase">Transport</label>
                                        <input type="text" id="transport" class="form-control" placeholder="Carrier" value="{{ $order->transport }}">
                                    </div>
                                </div>
                            </div>

                            <label class="small font-weight-bold text-muted uppercase">Notes / Remark</label>
                            @php
                                $previousRemarks = \DB::table('agent_orders')->whereNotNull('remark')->where('remark', '!=', '')->distinct()->pluck('remark');
                            @endphp
                            <input type="text" id="orderRemark" class="form-control mb-4" list="previous_remarks_list" placeholder="Instructions..." value="{{ $order->remark }}" autocomplete="off">
                            <datalist id="previous_remarks_list">
                                @foreach($previousRemarks as $rem)
                                    <option value="{{ $rem }}">
                                @endforeach
                            </datalist>

                            <button type="button" class="btn btn-warning btn-lg btn-block shadow-lg rounded-pill update-fabric-order-btn" id="placeOrderBtn" disabled title="Fulfill targets to enable">
                                <i class="fas fa-save mr-2"></i> UPDATE ORDER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .select2-container--bootstrap4 .select2-selection { border-radius: 10px; min-height: 45px; display: flex; align-items: center; padding-left: 10px; border: 1px solid #ced4da; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { background-color: #007bff; border-color: #0069d9; border-radius: 5px; color: white; padding: 2px 8px; margin-top: 6px; }
    .form-control:focus { box-shadow: none; border-color: #007bff; }
    .card { border-radius: 15px !important; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    .table td, .table th { vertical-align: middle !important; }
    .roll-item { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .badge-fabric { background-color: #e9ecef; color: #495057; font-weight: 700; }
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
                order_meter: parseFloat("{{ $item->meter }}")
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

            const html = `
                <tr id="row_${id}" class="roll-item" data-id="${id}" data-fabric-id="${item.fabric_id}">
                    <td><div class="badge badge-fabric border px-2 py-1">${item.fabric_name}</div></td>
                    <td><span class="font-weight-bold">#${item.roll_no}</span><br><small class="text-muted">${item.batch || 'No Batch'}</small></td>
                    <td class="text-center font-weight-bold">${avail.toFixed(2)}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control order-meter" value="${order.toFixed(2)}" min="0" max="${avail}" step="0.01">
                            <div class="input-group-append"><span class="input-group-text">m</span></div>
                        </div>
                    </td>
                    <td class="text-right">
                        <input type="number" class="form-control form-control-sm text-right roll-price" value="${(parseFloat(item.price) || 0).toFixed(2)}" step="0.01">
                    </td>
                    <td class="text-right font-weight-bold text-dark">₹<span class="row-total">0.00</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-outline-danger remove-item" data-id="${id}"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `;
            $('#masterTableBody').append(html);
        }

        $(document).on('click', '.remove-item', function() {
            const id = $(this).data('id').toString();
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
