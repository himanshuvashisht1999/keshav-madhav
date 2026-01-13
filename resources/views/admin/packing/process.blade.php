@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Process Packing: {{ $order->sku }}</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="{{ route('admin.packing.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- LEFT PANEL: AVAILABLE ITEMS -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h3 class="card-title">Available Items (From Slip)</h3>
                        </div>
                        <div class="card-body p-0" style="overflow-y: auto; max-height: 600px;">
                            <ul class="list-group list-group-flush" id="available-items-list">
                                <li class="list-group-item text-muted text-center">Loading items...</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- RIGHT PANEL: PACKING STRUCTURE -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Packed Structure</h3>
                            <div>
                                <button class="btn btn-light btn-sm" onclick="openCreateCartonModal()">
                                    <i class="fas fa-plus"></i> New Carton
                                </button>
                                <button class="btn btn-success btn-sm ms-2" onclick="finalizePacking()">
                                    <i class="fas fa-check"></i> Finalize
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="packing-structure-area" style="overflow-y: auto; max-height: 600px;">
                            <div class="text-center text-muted mt-5">
                                <p>No cartons created yet.</p>
                                <button class="btn btn-outline-primary btn-sm" onclick="openCreateCartonModal()">Create First Carton</button>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createBoxForm">
                    <div class="mb-3">
                        <label>Box Number</label>
                        <input type="text" class="form-control" name="box_no" required>
                    </div>
                    <h6>Select Items to Pack in Box</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item/Size</th>
                                <th>Available</th>
                                <th>Pack Qty</th>
                            </tr>
                        </thead>
                        <tbody id="boxItemsTable">
                            <!-- Populated by JS -->
                        </tbody>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCartonForm">
                    <div class="mb-3">
                        <label>Carton Number</label>
                        <input type="text" class="form-control" name="carton_no" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Add Unpacked Boxes</h6>
                            <div id="unpackedBoxesList">
                                <!-- Checkboxes for boxes -->
                            </div>
                        </div>
                        <div class="col-md-6">
                             <h6>OR Add Loose Items</h6>
                             <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item/Size</th>
                                        <th>Pack Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="cartonItemsTable">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
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

@push('scripts')
<script>
    const ORDER_ID = {{ $order->id }};
    const SLIP_ID = {{ $slip->id }};
    const ORDER_ITEMS = @json($order->OrderProductSets->flatMap->product_set_details);
    const EXISTING_PACKING = @json($packing);
    
    // Structure State
    let packedStructure = {
        cartons: EXISTING_PACKING ? EXISTING_PACKING.cartons : [],
        boxes: EXISTING_PACKING ? EXISTING_PACKING.boxes : [] // Unpacked boxes
    };
    
    $(document).ready(function() {
        renderAvailableItems();
        renderStructure();
    });
    
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
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#carton${index}">
                            <i class="fas fa-box-open me-2"></i> Carton #${carton.carton_no}
                        </button>
                    </h2>
                    <div id="carton${index}" class="accordion-collapse collapse" data-bs-parent="#cartonAccordion">
                        <div class="accordion-body">
                            <!-- Boxes in Carton -->
                            ${carton.boxes.length > 0 ? '<h6><small>Boxes:</small></h6><ul>' + carton.boxes.map(b => `<li>Box #${b.box_no}</li>`).join('') + '</ul>' : ''}
                            
                            <!-- Loose Items in Carton -->
                            ${carton.items && carton.items.length > 0 ? '<h6><small>Loose Items:</small></h6><ul>' + carton.items.map(i => `<li>Size ID: ${i.size_id} (Qty: ${i.quantity})</li>`).join('') + '</ul>' : ''}
                        </div>
                    </div>
                </div>`;
            });
            html += `</div>`;
        }
        
        if(html === '') {
            html = `<div class="text-center text-muted mt-5">
                        <p>No cartons or boxes created yet.</p>
                        <p>Start by creating a box or a carton.</p>
                    </div>`;
        }
        
        $('#packing-structure-area').html(html);
    }

    function renderAvailableItems() {
        let html = '';
        ORDER_ITEMS.forEach(item => {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                Size: ${item.size}
                <span class="badge bg-primary rounded-pill">${item.total_quantity}</span>
            </li>`;
        });
        $('#available-items-list').html(html);
        
        // Also populate modal tables
        let modalHtml = '';
        ORDER_ITEMS.forEach(item => {
            // Need to update MAX based on packed quantity? 
            // For now, user can overpack logic (as per request not strictly limited yet)
            // But we can check balance if needed.
            modalHtml += `<tr>
                <td>${item.size}</td>
                <td>${item.total_quantity}</td>
                <td><input type="number" class="form-control form-control-sm item-pack-qty" data-size-id="${item.id}" max="${item.total_quantity}"></td>
            </tr>`;
        });
        $('#boxItemsTable').html(modalHtml);
        $('#cartonItemsTable').html(modalHtml);
    }
    
    function openCreateBoxModal() {
        $('#createBoxModal').modal('show');
    }
    
    function openCreateCartonModal() {
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
        
        $('#createCartonModal').modal('show');
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
        let items = [];
        $('#cartonItemsTable .item-pack-qty').each(function() {
            let val = $(this).val();
            if(val > 0) {
                items.push({
                    size_id: $(this).data('size-id'),
                    quantity: val
                });
            }
        });
        
        // Boxes
        let boxIds = [];
        $('.box-select:checked').each(function() {
            boxIds.push($(this).val());
        });

        if(items.length === 0 && boxIds.length === 0) {
             alert("Select at least one box or item");
             return;
        }

        $.post("{{ route('admin.packing.saveCarton') }}", {
            _token: "{{ csrf_token() }}",
            slip_id: SLIP_ID,
            order_id: ORDER_ID,
            carton_no: $('input[name="carton_no"]').val(),
            items: items,
            box_ids: boxIds
        }, function(response) {
            if(response.status === 'success') {
                $('#createCartonModal').modal('hide');
                alert("Carton Created Successfully");
                location.reload(); 
            } else {
                alert("Error: " + response.message);
            }
        });
    }
    
    function finalizePacking() {
        // call AJAX finalize
         alert("Not implemented fully yet");
    }

</script>
@endpush
@endsection

