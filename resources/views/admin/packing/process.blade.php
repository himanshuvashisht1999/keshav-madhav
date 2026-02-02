@extends('admin.layouts.app')

@section('content')

<style>
    .content-wrapper h4,
    .content-wrapper .h4 {
        font-size: 1.00rem !important;
        font-weight: 500;
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
                        <a id="fileLink"
                            href="{{asset('/assets/products/'.$order->corporate_order_file)}}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-alt mr-1"></i> Sales Order File
                        </a>
                    </div>
                @endif
                <div class="col-md-2 text-right">
                    <a href=""
                        id="fileLink"
                        target="_blank"
                        rel="noopener noreferrer"
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
                            {{-- <button class="btn btn-sm btn-outline-primary" onclick="openCreateSetModal()" id="btnCreateSet" disabled>
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
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Packed Structure (Storeroom)</h3>
                            <div>
                                <button class="btn btn-light btn-sm" onclick="openCreateCartonModal()" id="btnCreateCarton" @if(!$order) disabled @endif>
                                    <i class="fas fa-plus"></i> New Carton
                                </button>
                                <button class="btn btn-success btn-sm ms-2" onclick="finalizePacking()" id="btnFinalize" @if(!$order) disabled @endif>
                                    <i class="fas fa-check"></i> Finalize
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="packing-structure-area" style="overflow-y: auto; max-height: 600px;">
                            <div class="text-center text-muted mt-5">
                                <p>No cartons created yet.</p>
                                <button class="btn btn-outline-primary btn-sm" onclick="openCreateCartonModal()" id="btnCreateFirstCarton" @if(!$order) disabled @endif>Create First Carton</button>
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
                                    <option value="{{ $store->id }}" data-racks="{{ $store->racks }}">{{ $store->name }}</option>
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
                                <button type="button" class="btn btn-outline-primary active mr-2" id="btn-tab-sets" onclick="switchPackTab('sets')">Pack Sets</button>
                                <button type="button" class="btn btn-outline-secondary" id="btn-tab-loose" onclick="switchPackTab('loose')">Pack Loose Items</button>
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
                <button type="button" class="btn btn-primary" onclick="submitCreateSet()">Create Sets</button>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let ORDER_ID = {{ $order ? $order->id : 'null' }}; // Mutable
    const SLIP_ID = {{ $slip->id }};
    let ORDER_ITEMS = []; 
    let ORDER_SETS = @json($order_sets ?? []);
    const PACKED_DATA = @json($packed_quantities ?? []);
    const EXISTING_PACKING = @json($packing);

    // Initial Population of ORDER_ITEMS from Sets if available
    if(ORDER_SETS.length > 0 && ORDER_ITEMS.length === 0) {
        ORDER_SETS.forEach(set => {
             let details = set.details_data || set.details;
             if(details) {
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
    if(ORDER_ITEMS.length > 0) {
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
    
    $(document).ready(function() {
        if(ORDER_ID) {
            renderAvailableItems();
        }
        renderStructure();
        
        // Initialize Select2 if available
        if($('.select2').length > 0) {
            $('.select2').select2();
        }
        
        // Handle Order Selection
        $('#orderSelect').on('change', function() {
            let orderId = $(this).val();
            if(orderId) {
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
        
        $.get("{{ route('admin.packing.orderDeps', '') }}/" + orderId, function(response) {
            if(response.status === 'success') {
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
        $('#btnCreateCarton, #btnFinalize, #btnCreateFirstCarton').prop('disabled', disable);
    }
    
    function renderStructure() {
        let html = '';
        
        // Unpacked Boxes
        if(packedStructure.boxes.length > 0) {
            html += `<h5>Unpacked Boxes</h5><div class="list-group mb-3">`;
            packedStructure.boxes.forEach(box => {
                html += `<div class="list-group-item list-group-item-warning">
                    <i class="fas fa-box"></i> Box #${box.box_no}
                </div>`;
            });
            html += `</div>`;
        }
        
        // Cartons
        if(packedStructure.cartons.length > 0) {
            html += `<h5>Cartons</h5><div class="accordion" id="cartonAccordion">`;
            packedStructure.cartons.forEach((carton, index) => {
                html += `
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-toggle="collapse" data-target="#carton${index}">
                            <i class="fas fa-box-open me-2"></i> Carton #${carton.carton_no}
                        </button>
                    </h2>
                    <div id="carton${index}" class="accordion-collapse collapse" data-parent="#cartonAccordion">
                        <div class="accordion-body">
                            <!-- Boxes in Carton -->
                            ${carton.boxes.length > 0 ? '<h6><small>Boxes:</small></h6><ul>' + carton.boxes.map(b => `<li>Box #${b.box_no}</li>`).join('') + '</ul>' : ''}
                            
                            <!-- Loose Items in Carton -->
                            ${carton.items && carton.items.length > 0 ? '<h6><small>Loose Items:</small></h6><ul>' + carton.items.map(i => `<li>Size: ${resolveSizeName(i.size_id)} (Qty: ${i.quantity})</li>`).join('') + '</ul>' : ''}
                        </div>
                    </div>
                </div>`;
            });
            html += `</div>`;
        }
        
        if(html === '') {
            html = `<div class="text-center text-muted mt-5">
                        <p>No cartons or boxes created yet.</p>
                        <button class="btn btn-outline-primary btn-sm" onclick="openCreateCartonModal()" id="btnCreateFirstCarton" ${!ORDER_ID ? 'disabled' : ''}>Create First Carton</button>
                    </div>`;
        }
        
        $('#packing-structure-area').html(html);
    }

    function renderAvailableItems() {
        let html = '';
        let modalSetsHtml = '';
        
        // Render SETS in Left Panel
        if(ORDER_SETS && ORDER_SETS.length > 0) {
             ORDER_SETS.forEach((set, index) => {
                
                let remainingSets = set.set_quantity - set.packed_sets;
                if(remainingSets < 0) remainingSets = 0;
                let minRemaining = null; // important
                ORDER_ITEMS.forEach(item => {

                    if (item.order_products_set_id == set.id) {

                        let packed = parseInt(item.packed_qty) || 0;
                        let total  = parseInt(item.total_quantity) || 0;
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
                    <strong>Set #${index+1}</strong> <small class="text-muted">(Qty: ${set.set_quantity})</small>
                    <span class="badge ${remainingSets > 0 ? 'bg-primary' : 'bg-success'} float-right">Rem: ${remainingSets} Sets</span>
                </li>`;
                
                // Details
                if(set.details_data || set.details) {
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
                 if(remainingSets > 0) {
                     let compositionText = (set.details_data || set.details).map(d => `${d.size}(${d.qty_per_set} pcs)`).join(', ');
                     modalSetsHtml += `
                     <div class="card mb-2 p-2 border-left-primary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                 <strong>Set #${index+1}</strong> <small class="text-muted">(${compositionText}), <br>Barcode -${set.bar_code}, Design No - ${set.design_number}, Colour - ${set?.colors?.name ?? ''}, </small><br>
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
        if((!ORDER_SETS || ORDER_SETS.length === 0) && ORDER_ITEMS.length > 0) {
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
        
        if(html === '') {
             html = '<li class="list-group-item text-muted text-center">No items found.</li>';
        }

        $('#available-items-list').html(html);
        $('#cartonSetsContainer').html(modalSetsHtml || '<p class="text-muted text-center py-2">No full sets available to pack.</p>');
        
        // Also populate loose items table (optional/fallback)
        let modalHtml = '';
        
        if(ORDER_ITEMS && ORDER_ITEMS.length > 0) {
            ORDER_ITEMS.forEach(item => {
                let packed = parseInt(item.packed_qty) || 0;
                let total = parseInt(item.total_quantity);
                let remaining = total - packed;
                if(remaining > 0) {
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
        if(!ORDER_ID) return;
        $('#createBoxModal').modal('show');
    }
    
    function openCreateCartonModal() {
        if(!ORDER_ID) return; 
        
        // Populate Unpacked Boxes list
        let html = '';
        if(packedStructure.boxes.length > 0) {
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
        if(ORDER_SETS && ORDER_SETS.length > 0) {
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

    function submitCreateBox() {
        let items = [];
        $('#boxItemsTable .item-pack-qty').each(function() {
            let val = $(this).val();
            if(val > 0) {
                items.push({
                    size_id: $(this).data('size-id'),
                    quantity: val
                });
            }
        });
        
        if(items.length === 0) {
            alert("Select at least one item");
            return;
        }

        $.post("{{ route('admin.packing.saveBox') }}", {
            _token: "{{ csrf_token() }}",
            slip_id: SLIP_ID,
            order_id: ORDER_ID,
            box_no: $('input[name="box_no"]').val(),
            items: items
        }, function(response) {
            if(response.status === 'success') {
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

        if(!cartonNo || cartonNo.trim() === '') {
            alert("Please enter a Carton Number.");
            return;
        }
        if(!storeId) {
            alert("Please select a Store Room.");
            return;
        }
        if(!rackId) {
            alert("Please select a Rack.");
            return;
        }

        let sets = [];
        let error = false;

        $('#cartonSetsContainer .set-pack-qty').each(function() {
            let val = parseInt($(this).val()) || 0;
            let max = parseInt($(this).attr('max')) || 0;
            
            if(val > max) {
                 alert(`Error: You cannot pack ${val} sets. Only ${max} remaining.`);
                 error = true;
                 return false;
            }

            if(val > 0) {
                 sets.push({
                     set_id: $(this).data('set-id'),
                     quantity: val
                 });
            }
        });
        
        if(error) return;
        
        let items = []; 
        $('#cartonItemsTable .item-pack-qty').each(function() {
            let val = parseInt($(this).val()) || 0;
            let max = parseInt($(this).attr('max')) || 0;

            if(val > max) {
                 alert(`Error: You cannot pack ${val} items for size ${$(this).data('size-id')}. Only ${max} remaining.`);
                 error = true; // Use simple var validation
                 return false; 
            }
            // Ideally we need looking up size name for better error, but this stops the negative data.

            if(val > 0) {
                items.push({
                    size_id: $(this).data('size-id'),
                    quantity: val
                });
            }
        });

        if(error) return;

        // Boxes
        let boxIds = [];
        $('.box-select:checked').each(function() {
            boxIds.push($(this).val());
        });

        if(items.length === 0 && boxIds.length === 0 && sets.length === 0) {
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
        }, function(response) {
            if(response.status === 'success') {
                $('#createCartonModal').modal('hide');
                alert("Carton Created Successfully");
                location.reload(); 
            } else {
                if(response.status === 'exists') {
                    alert(response.message);
                } else {
                    alert("Error: " + response.message);
                }
            }
        });
    }
    
    function finalizePacking() {
        if(!EXISTING_PACKING || !EXISTING_PACKING.id) {
             alert("No packing session found to finalize.");
             return;
        }

        if(!confirm("Are you sure you want to finalize this packing? This will mark it as complete.")) {
            return;
        }

        $.post("{{ route('admin.packing.finalize') }}", {
             _token: "{{ csrf_token() }}",
             packing_main_id: EXISTING_PACKING.id
        }, function(response) {
             if(response.status === 'success') {
                 alert("Packing Finalized Successfully!");
                 window.location.href = "{{ route('admin.uploaded-slips.index') }}";
             } else { 
                 alert("Error: " + response.message);
             }
        });
    }

    function switchPackTab(tab) {
        if(tab === 'sets') {
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
    function resolveSizeName(sizeId) {
        // Try to find in ORDER_ITEMS
        // Note: ORDER_ITEMS might have 'id' matching 'size_id' (which is detail_id).
        // Or 'size' (the name).
        
        // Strategy: iterate ORDER_ITEMS, check id.
        let found = ORDER_ITEMS.find(i => i.id == sizeId);
        if(found) return found.size;
        
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



