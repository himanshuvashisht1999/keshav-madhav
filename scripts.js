
            let ORDER_ID = {{ $order->id ?? 'null' }};
            const SLIP_ID = {{ $slip->id }};
            @php
                $orderItemsArray = isset($order_sets) ? $order_sets->flatMap(fn($s) => $s->details_data ?? $s->details ?? []) : [];
            @endphp
            let ORDER_ITEMS = @json($orderItemsArray);
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
                                'rack' => $c->rack ? [
                                    'name' => $c->rack->name,
                                    'storeroom' => $c->rack->storeroom ? ['name' => $c->rack->storeroom->name] : null
                                ] : null,
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
                                })->toArray() : [],
                                'items' => $c->items ? $c->items->map(function($i) { return ['size_id' => $i->size_id, 'quantity' => $i->quantity]; })->toArray() : []
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
            let UNIT_LOTS = @json($unit_lots ?? []);

            // --- CORPORATE MULTI-CARTON PLANNER ---
            function openMultiCartonPlanner() {
                if (!ORDER_ID) return;
                renderPlannerInventory();

                // Populate Range Design dropdown
                let uniqueDesigns = [...new Set(UNIT_LOTS.map(l => l.design_number))];
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

                if (type === 'set') {
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

                    if (type === 'set' && rType === 'set' && rSetId == setId) {
                        plannedQty += rQty;
                    } else if (type === 'loose' && rType === 'loose' && rSizeId == sizeId) {
                        plannedQty += rQty;
                    }
                });

                let remaining = initialAvailable - plannedQty;
                let unitLabel = type === 'set' ? 'Boxes Left' : 'Pcs Left';
                
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
                if (UNIT_LOTS && UNIT_LOTS.length > 0) {
                    $list.append('<div class="mb-3"><strong class="small text-dark font-weight-bold">LOTS</strong></div>');
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
                } else {
                    $list.append('<div class="text-muted small">No lots available for packing at this unit.</div>');
                }
            }

            function addPlannerRow(data = null) {
                let highest = 0;
                $('#plannerTableBody .planner-carton-no').each(function () {
                    let val = parseInt($(this).val()) || 0;
                    if (val > highest) highest = val;
                });
                let nextCartonNo = data ? data.carton_no : (highest > 0 ? highest + 1 : '');

                let uniqueDesigns = [...new Set(UNIT_LOTS.map(l => l.design_number))];

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

            // --- DOMESTIC PACKING LOGIC ---
            function openDomesticPackingModal() {
                if (!ORDER_ID) return;
                renderDomesticInventory();

                let $domDesign = $('#domDesign');
                $domDesign.html('<option value="">Select Design</option>');
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                DOMESTIC_MASTERS.products.forEach(p => {
                    let dNum = p.design_number || 'N/A';
                    if (!validDesigns.includes(dNum)) return;

                    let series = p.series ? p.series.name : '';
                    let garment = p.name_of_garment || '';
                    let dLabel = `${dNum} (${series} ${garment})`;

                    $domDesign.append(`<option value="${p.id}" data-design="${dNum}">${dLabel}</option>`);
                });

                // Initialize select2
                $domDesign.select2({
                    dropdownParent: $('#domesticPackingModal'),
                    width: '100%'
                });

                $('#domesticTableBody').empty();
                $('#domesticPackingModal').modal('show');
            }

            function updateDomSizeSets() {
                let pid = $('#domDesign').val();
                let $ss = $('#domSizeSet');
                let $preview = $('#domSizePreview');
                let $badges = $('#domSizeBadges');

                $ss.html('<option value="">Select Size Set</option>');
                $preview.hide();
                $badges.empty();
                if (!pid) return;

                $.get("{{ route('admin.inventory.get_size_sets_by_product', '') }}/" + pid, function (res) {
                    if (res.status === 'success') {
                        // Only show size sets that are in the current order
                        let orderSizeSetIds = ORDER_SETS.map(os => parseInt(os.set_size));
                        res.size_sets.forEach(ss => {
                            if (orderSizeSetIds.includes(parseInt(ss.id))) {
                                $ss.append(`<option value="${ss.id}" data-sizes='${JSON.stringify(ss.sizes || [])}'>${ss.name}</option>`);
                            }
                        });
                    }
                });

                updateDomAttributes();
            }

            $(document).on('change', '#domSizeSet', function () {
                let $ss = $(this);
                let $preview = $('#domSizePreview');
                let $badges = $('#domSizeBadges');
                let selected = $ss.find(':selected');

                $badges.empty();
                if (selected.val()) {
                    let sizes = JSON.parse(selected.attr('data-sizes') || '[]');
                    if (sizes.length > 0) {
                        sizes.forEach(sz => {
                            $badges.append(`<span class="badge badge-soft-info mr-1 px-2 py-1">${sz}</span>`);
                        });
                        $preview.show();
                    } else {
                        $preview.hide();
                    }
                } else {
                    $preview.hide();
                }
                updateDomColors();
            });

            function updateDomColors() {
                let productId = $('#domDesign').val();
                let sizeSetId = $('#domSizeSet').val();
                let $color = $('#domColor');
                $color.html('<option value="">Select Color</option>');
                if (!productId || !sizeSetId) return;

                $.get("{{ route('admin.inventory.get_colors_by_product_size', ['', '']) }}/" + productId + "/" + sizeSetId, function (res) {
                    if (res.status === 'success') {
                        let seen = new Set();
                        res.colors.forEach(c => {
                            let colorName = (c.name || '').trim().toUpperCase();
                            if (!seen.has(colorName)) {
                                seen.add(colorName);
                                $color.append(`<option value="${c.id}">${c.name}</option>`);
                            }
                        });
                    }
                });
                updateDomAttributes();
            }

            function updateDomAttributes() {
                let productId = $('#domDesign').val();
                let sizeSetId = $('#domSizeSet').val();
                if (!productId || !sizeSetId) return;

                $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (res) {
                    if (res.success) {
                        window.currentDomProduct = res;
                    }
                });
            }

            function updateDomRacks() {
                let $store = $('#domStore');
                let $rack = $('#domRack');
                let selected = $store.find(':selected');
                $rack.html('<option value="">Rack</option>');
                if (selected.val()) {
                    let racks = JSON.parse(selected.attr('data-racks') || '[]');
                    racks.forEach(r => $rack.append(`<option value="${r.id}">${r.name}</option>`));
                }
            }

            function addDomesticToPlanner() {
                let pid = $('#domDesign').val();
                let ssid = $('#domSizeSet').val();
                let cid = $('#domColor').val();
                let qty = parseInt($('#domQty').val()) || 1;
                let storeId = $('#domStore').val();
                let rackId = $('#domRack').val();

                if (!pid || !ssid || !cid || !rackId) { alert("Please fill all details."); return; }
                if (!window.currentDomProduct) { alert("Product details still loading, please wait..."); return; }

                let design = $('#domDesign option:selected').data('design');
                let sizeSet = $('#domSizeSet option:selected').text();
                let color = $('#domColor option:selected').text();
                let store = $('#domStore option:selected').text();
                let rack = $('#domRack option:selected').text();

                let pattern = window.currentDomProduct.pattern_name || 'N/A';
                let fitting = window.currentDomProduct.fitting_name || 'N/A';
                let pattern_id = window.currentDomProduct.pattern_id;
                let fitting_id = window.currentDomProduct.fitting_id;

                let mrp = 0;
                let v = window.currentDomProduct.variants.find(v => v.size_set_id == ssid);
                if (v) mrp = v.mrp;

                let row = `
                            <tr class="align-middle" 
                                data-pid="${pid}" data-ssid="${ssid}" data-cid="${cid}" data-qty="${qty}" 
                                data-rack="${rackId}" data-pattern="${pattern_id}" data-fitting="${fitting_id}" 
                                data-mrp="${mrp}">
                                <td>${$('#domesticTableBody tr').length + 1}</td>
                                <td class="font-weight-bold text-dark">${design}</td>
                                <td>${sizeSet}</td>
                                <td>${color}</td>
                                <td><span class="badge badge-soft-secondary border-0 small font-weight-normal">${pattern}</span></td>
                                <td><span class="badge badge-soft-secondary border-0 small font-weight-normal">${fitting}</span></td>
                                <td class="text-center font-weight-bold text-info">${qty} Boxes</td>
                                <td><div class="text-xs font-weight-bold">${store}</div><div class="text-xs text-muted">${rack}</div></td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-link text-danger p-0" onclick="$(this).closest('tr').remove()"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                $('#domesticTableBody').append(row);

                // Success feedback
                toastr.success("Box plan added.");
            }

            function submitDomesticPacking() {
                let rows = $('#domesticTableBody tr');
                if (rows.length === 0) { alert("Please add at least one box to the plan."); return; }

                let $btn = $('button[onclick="submitDomesticPacking()"]');
                $btn.prop('disabled', true).text('Saving all boxes...');

                let boxes = [];
                rows.each(function () {
                    let r = $(this);
                    boxes.push({
                        product_id: r.data('pid'),
                        size_set_id: r.data('ssid'),
                        color_id: r.data('cid'),
                        pattern_id: r.data('pattern'),
                        fitting_id: r.data('fitting'),
                        mrp: r.data('mrp'),
                        quantity: r.data('qty'),
                        rack_id: r.data('rack')
                    });
                });

                let targetRoute = (ORDER_TYPE === 'domestic') 
                    ? "{{ route('admin.packing.saveDomesticBulk') }}" 
                    : "{{ route('admin.packing.saveCorporateDomesticBulk') }}";

                $.post(targetRoute, {
                    _token: "{{ csrf_token() }}",
                    slip_id: SLIP_ID,
                    order_id: ORDER_ID,
                    boxes: boxes
                }, function (res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(res.message);
                        $btn.prop('disabled', false).text('PROCESS ALL BOXES');
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

            function renderDomesticInventory() {
                let $list = $('#domesticInventoryList');
                $list.html('<p class="small text-muted">Calculating...</p>');

                let html = '';
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                ORDER_SETS.forEach(set => {
                    if (!validDesigns.includes(set.design_number)) return;

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
                    let orderType = $(this).find(':selected').data('type');

                    if (orderId) {
                        if (orderType === 'domestic') {
                            window.location.href = "{{ route('admin.packing.processDomestic', $slip->id) }}?order_id=" + orderId;
                            return;
                        }
                        fetchOrderDetails(orderId);
                    } else {
                        ORDER_ID = null;
                        ORDER_TYPE = "";
                        ORDER_ITEMS = [];
                        $('#btnDomesticPacking').hide();
                        $('#btnCorporatePacking').hide();
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
                        UNIT_LOTS = response.unit_lots || [];
                        ORDER_TYPE = (response.order && response.order.order_type) ? response.order.order_type.toLowerCase() : "";

                        // Update UI mode
                        if (ORDER_TYPE === 'domestic') {
                            $('#btnDomesticPacking').removeClass('d-none').show().html('<i class="fas fa-box mr-1"></i> Domestic Packing');
                            $('#btnCorporatePacking').hide();
                        } else {
                            $('#btnCorporatePacking').removeClass('d-none').show();
                            $('#btnDomesticPacking').removeClass('d-none').show().html('<i class="fas fa-random mr-1"></i> Divert to Domestic');
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

                // Cartons
                if (packedStructure.cartons.length > 0) {
                    html += `<div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h5 class="mb-0 text-muted" style="letter-spacing: 0.5px; font-weight: 600;">CARTONS</h5>
                                                        <span class="badge badge-primary badge-pill">${packedStructure.cartons.length} Total</span>
                                                    </div>`;

                    html += `<div class="table-responsive bg-white rounded shadow-sm border">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="bg-light text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="py-3 px-3">Carton No</th>
                                            <th class="py-3 px-3 text-center">Total Boxes</th>
                                            <th class="py-3 px-3">Design No</th>
                                            <th class="py-3 px-3">Color</th>
                                            <th class="py-3 px-3">Size Set</th>
                                            <th class="py-3 px-3">Location (Wh - Rack)</th>
                                            <th class="py-3 px-3 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                    packedStructure.cartons.forEach((carton, index) => {
                        let designNo = 'N/A';
                        let colorName = 'N/A';
                        let sizeSetName = 'N/A';

                        if (carton.boxes && carton.boxes.length > 0) {
                            let firstBox = carton.boxes[0];
                            if (firstBox.domestic_inventory && firstBox.domestic_inventory.product) {
                                designNo = firstBox.domestic_inventory.product.design_number || 'N/A';
                                colorName = firstBox.domestic_inventory.color ? firstBox.domestic_inventory.color.name : 'N/A';
                                sizeSetName = firstBox.domestic_inventory.size_set ? firstBox.domestic_inventory.size_set.name : 'N/A';
                            } else if (firstBox.items && firstBox.items.length > 0 && firstBox.items[0].detail && firstBox.items[0].detail.order_product_set) {
                                let ops = firstBox.items[0].detail.order_product_set;
                                designNo = ops.design_number || 'N/A';
                                colorName = ops.colors ? ops.colors.name : 'N/A';
                                sizeSetName = ops.size_measurement ? ops.size_measurement.name : 'N/A';
                            }
                        } else if (carton.items && carton.items.length > 0 && carton.items[0].detail && carton.items[0].detail.order_product_set) {
                            let ops = carton.items[0].detail.order_product_set;
                            designNo = ops.design_number || 'N/A';
                            colorName = ops.colors ? ops.colors.name : 'N/A';
                            sizeSetName = ops.size_measurement ? ops.size_measurement.name : 'N/A';
                        }

                        let locationStr = 'N/A';
                        if (carton.rack) {
                            let rackName = carton.rack.name || '';
                            let warehouseName = (carton.rack.storeroom && carton.rack.storeroom.name) ? carton.rack.storeroom.name : '';
                            locationStr = warehouseName ? `${warehouseName} - ${rackName}` : rackName;
                        }

                        let deleteBtn = '';
                        if (EXISTING_PACKING && EXISTING_PACKING.status === 0) {
                            deleteBtn = `<button class="btn btn-link text-danger p-0" onclick="deleteCarton(${carton.id}, event)" title="Delete Carton"><i class="fas fa-trash-alt"></i></button>`;
                        }

                        html += `
                                        <tr>
                                            <td class="py-2 px-3 font-weight-bold align-middle">#${carton.carton_no}</td>
                                            <td class="py-2 px-3 text-center align-middle"><span class="badge badge-soft-primary">${carton.boxes.length}</span></td>
                                            <td class="py-2 px-3 align-middle">${designNo}</td>
                                            <td class="py-2 px-3 align-middle">${colorName}</td>
                                            <td class="py-2 px-3 align-middle">${sizeSetName}</td>
                                            <td class="py-2 px-3 align-middle text-info"><i class="fas fa-map-marker-alt mr-1"></i>${locationStr}</td>
                                            <td class="py-2 px-3 text-right align-middle">${deleteBtn}</td>
                                        </tr>`;
                    });

                    html += `       </tbody>
                                </table>
                            </div>`;
                }

                // Boxes (Unpacked)
                if (packedStructure.boxes && packedStructure.boxes.length > 0) {
                    html += `<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                        <h5 class="mb-0 text-muted small font-weight-bold" style="letter-spacing: 0.5px;">UNPACKED BOXES</h5>
                                        <span class="badge badge-warning badge-pill">${packedStructure.boxes.length} Out of Carton</span>
                                    </div>`;

                    packedStructure.boxes.forEach(box => {
                        html += `
                                <div class="card mb-2 border-0 shadow-sm p-3" style="border-radius: 12px; background: #fffcf5; border-left: 4px solid #ffc107 !important;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0 font-weight-bold"><i class="fas fa-box text-warning mr-2"></i>Box #${box.box_no}</h6>
                                            <small class="text-muted">Waiting to be placed in a carton</small>
                                            ${(box.domestic_inventory) ? `
                                                <div class="mt-2 text-primary font-weight-bold small">
                                                    <i class="fas fa-barcode mr-1"></i> Barcode : ${box.domestic_inventory.barcode}
                                                    <a href="/admin/packing/download-domestic-barcode-txt/${box.domestic_inventory.id}" class="btn btn-outline-primary btn-xs ml-2 px-2 py-0"><i class="fas fa-file-download mr-1"></i> TXT File </a>
                                                </div>
                                            ` : ''}
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openCreateCartonModal()">
                                            <i class="fas fa-plus mr-1"></i> Add to Carton
                                        </button>
                                    </div>
                                </div>`;
                    });
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

                        // html += removed to simplify left panel display

                        // Details
                        if (set.details_data || set.details) {
                            let details = set.details_data || set.details;
                            details.forEach(item => {
                                let packed = parseInt(item.packed_qty) || 0;
                                let total = parseInt(item.total_quantity);
                                let remaining = total - packed;
                                let availableAtUnit = parseInt(item.unit_available_qty) || 0;
                                let badgeClass = availableAtUnit === 0 ? 'bg-warning' : 'bg-info';

                                // html += removed to simplify left panel display
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

                // --- NEW SIMPLE LEFT PANEL: SHOW LOTS ---
                if (UNIT_LOTS && UNIT_LOTS.length > 0) {
                    UNIT_LOTS.forEach(lot => {
                        html += `
                            <li class="list-group-item bg-light pb-2 pt-2 mb-2" style="border-radius: 8px; border: 1px solid #e5e7eb;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <span class="badge bg-dark mr-1">Lot ${lot.lot_no}</span>
                                        <strong class="text-primary">#${lot.design_number}</strong>
                                        <span class="badge badge-light border text-secondary small ml-1">[${lot.size_set_name || 'N/A'}]</span>
                                    </div>
                                    <div>
                                        <span class="badge bg-info shadow-sm p-2" style="font-size: 13px;">
                                            Qty: ${lot.quantity}
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Available at unit</small>
                                    <span class="badge bg-secondary-light border text-dark font-weight-normal small px-2">Rem: ${lot.remaining_quantity}</span>
                                </div>
                            </li>`;
                    });
                } else {
                    html = '<li class="list-group-item text-muted text-center">No lots available for packing at this unit.</li>';
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

                if (!confirm('Are you sure you want to finalize this packing session? No more changes allowed.')) return;

                $.post("{{ route('admin.packing.finalize') }}", {
                    _token: "{{ csrf_token() }}",
                    packing_main_id: EXISTING_PACKING.id,
                    completion_date: completionDate
                }, function (response) {
                    if (response.status === 'success') {
                        if (response.order_type === 'domestic' && response.packing_main_id) {
                            let downloadUrl = "{{ route('admin.packing.downloadSlipBarcode', ':id') }}".replace(':id', response.packing_main_id);
                            window.open(downloadUrl, '_blank');
                        }

                        setTimeout(function() {
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
                
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;
                        
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

                // 1. Fetch Stages for Debit
                $.get("{{ route('admin.packing.reworkStages') }}", function (response) {
                    if (response.status === 'success') {
                        let $stageSelect = $('#debitStage');
                        $stageSelect.html('<option value="">Select Stage</option>');
                        response.stages.forEach(stage => {
                            $stageSelect.append(`<option value="${stage.id}">${stage.name}</option>`);
                        });
                    }
                });

                // 2. Populate Items
                let $list = $('#debitItemsList');
                $list.empty();
                
                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

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

                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

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

                let validDesigns = UNIT_LOTS ? UNIT_LOTS.map(l => l.design_number) : [];

                if (ORDER_ITEMS && ORDER_ITEMS.length > 0) {
                    ORDER_ITEMS.forEach(item => {
                        if (!validDesigns.includes(item.design_number)) return;

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
            if (ORDER_TYPE === 'domestic') {
                $(document).on('change', '#dom_design', function () {
                    let productId = $(this).val();
                    let $sizeSet = $('#dom_size_set');
                    let $color = $('#dom_color');

                    $sizeSet.html('<option value="">Loading...</option>').prop('disabled', true);
                    $color.html('<option value="">Select Size Set First</option>').prop('disabled', true);

                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (res) {
                        if (res.success) {
                            $('#dom_pattern_name').val(res.pattern_name);
                            $('#dom_pattern_id').val(res.pattern_id);
                            $('#dom_fitting_name').val(res.fitting_name);
                            $('#dom_fitting_id').val(res.fitting_id);

                            $sizeSet.html('<option value="">-- Select Size Set --</option>');
                            res.variants.forEach(v => {
                                $sizeSet.append(`<option value="${v.size_set_id}" data-mrp="${v.mrp}" data-colors='${JSON.stringify(v.colors)}'>${v.size_set_name}</option>`);
                            });
                            $sizeSet.prop('disabled', false);
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
                    $color.prop('disabled', false);
                });

                $('#btnSaveDomesticBox').on('click', function () {
                    let data = {
                        _token: "{{ csrf_token() }}",
                        slip_id: SLIP_ID,
                        order_id: ORDER_ID,
                        product_id: $('#dom_design').val(),
                        size_set_id: $('#dom_size_set').val(),
                        color_id: $('#dom_color').val(),
                        pattern_id: $('#dom_pattern_id').val(),
                        fitting_id: $('#dom_fitting_id').val(),
                        mrp: $('#dom_mrp').val(),
                        selling_price: $('#dom_mrp').val(), // fallback
                        quantity: $('#dom_qty').val(),
                        rack_id: $('#dom_rack').val()
                    };

                    if (!data.product_id || !data.size_set_id || !data.color_id || !data.rack_id) {
                        alert("Please fill all required fields including storage Rack.");
                        return;
                    }

                    let targetRoute = (ORDER_TYPE === 'domestic') 
                        ? "{{ route('admin.packing.saveDomesticBulk') }}" 
                        : "{{ route('admin.packing.saveCorporateDomesticBulk') }}";

                    $.post(targetRoute, {
                        _token: "{{ csrf_token() }}",
                        slip_id: SLIP_ID,
                        order_id: ORDER_ID,
                        boxes: [data] // Send as array of 1
                    }, function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            setTimeout(() => location.reload(), 800);
                        } else {
                            toastr.error(res.message);
                        }
                    });
                });
            }
        