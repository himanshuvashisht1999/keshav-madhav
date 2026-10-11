@extends('admin.layouts.app')

@section('title', 'Edit Stock Disposal')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-trash-alt text-primary mr-2"></i> Update Disposal: {{ $main->disposal_no }}
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
            </div>
        </div>

        <form id="disposalForm">
            @csrf
            
            <!-- Stock Type Indicator Card -->
            <div class="erp-card mb-2 p-3 text-center">
                <label class="erp-label mb-2 font-weight-bold" style="font-size: 0.9rem;">Stock Type (Locked for existing record)</label>
                <div class="d-flex justify-content-center">
                    <div style="max-width: 350px; width: 100%;">
                        <span class="badge-erp-status {{ $main->item_type === 'fabric' ? 'badge-erp-info' : 'badge-erp-primary' }} p-2 w-100 font-weight-bold" style="font-size: 0.95rem;">
                            <i class="fas {{ $main->item_type === 'fabric' ? 'fa-scroll' : 'fa-boxes' }} mr-2"></i>
                            {{ $main->item_type === 'fabric' ? 'Fabric Roll' : 'Domestic Stock' }}
                        </span>
                    </div>
                </div>
                <input type="hidden" name="item_type" value="{{ $main->item_type }}">
            </div>

            <!-- Fabric Section -->
            @if($main->item_type === 'fabric')
            <div id="fabric_section">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="erp-card p-3 h-100">
                            <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                                <i class="fas fa-filter mr-1"></i> Warehouse & Fabric Filter
                            </h6>
                            <div class="form-group mb-3">
                                <label class="erp-label">Warehouse <span class="text-danger">*</span></label>
                                <select id="fabric_warehouse" name="warehouse_id" class="form-control form-control-sm erp-input select2">
                                    <option value="">Select warehouse</option>
                                    @foreach($fabricWarehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ optional($main->items->first())->fabricReceiptDetail->master_fabric_warehouse_id == $wh->id ? 'selected' : '' }}>
                                            {{ $wh->cutting_master_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="erp-label">Fabric <span class="text-danger">*</span></label>
                                <select id="fabric_id" class="form-control form-control-sm erp-input select2" multiple disabled>
                                    <option value="">Select fabric</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-2">
                        <div class="erp-card p-0 h-100">
                            <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                                    <i class="fas fa-scroll mr-1"></i> Select Rolls to Dispose
                                </h6>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="selectAllRolls">
                                    <label for="selectAllRolls" class="custom-control-label erp-label mb-0 cursor-pointer">Select all</label>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table erp-table table-bordered table-hover mb-0">
                                    <thead class="sticky-top bg-light">
                                        <tr>
                                            <th class="text-center" style="width: 45px;">#</th>
                                            <th>Roll no</th>
                                            <th>Remaining (Mtr)</th>
                                            <th style="width: 140px;">Dispose (Mtr)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rollsBody">
                                        @foreach($main->items as $item)
                                            @php $r = $item->fabricReceiptDetail; @endphp
                                            <tr>
                                                <td class="text-center"><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_{{ $r->id }}" value="{{ $r->id }}" checked><label for="roll_{{ $r->id }}" class="custom-control-label"></label></div></td>
                                                <td><span class="badge-erp-status badge-erp-info font-weight-bold">{{ $r->roll_number }}</span><div class="small text-muted">{{ $r->fabric->name }}</div></td>
                                                <td><strong>{{ $r->remaining_quantity + $item->quantity }}</strong> mtr <small class="text-success font-weight-bold">(Incl. current)</small></td>
                                                <td><input type="number" name="roll_qty[{{ $r->id }}]" class="form-control form-control-sm erp-input" max="{{ $r->remaining_quantity + $item->quantity }}" min="0.01" step="0.01" value="{{ $item->quantity }}"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Domestic Section -->
            @if($main->item_type === 'domestic')
            <div id="domestic_section">
                <div class="erp-card mb-2 p-3">
                    <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                        <i class="fas fa-box-open mr-1"></i> Select Domestic Stock
                    </h6>
                    <div class="form-row">
                        <div class="col-md-3 mb-2">
                            <label class="erp-label">Warehouse <span class="text-danger">*</span></label>
                            <select id="domestic_warehouse" name="warehouse_id" class="form-control form-control-sm erp-input select2">
                                <option value="">Select warehouse</option>
                                @foreach($storerooms as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label">Design no <span class="text-danger">*</span></label>
                            <select id="product_id" name="product_id" class="form-control form-control-sm erp-input select2">
                                <option value="">Select design</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->design_number }} ({{ $p->series->name ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label">Size set <span class="text-danger">*</span></label>
                            <select id="size_set_id" name="size_set_id" class="form-control form-control-sm erp-input select2" disabled>
                                <option value="">Select size</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label">Color <span class="text-danger">*</span></label>
                            <select id="color_id" name="color_id" class="form-control form-control-sm erp-input select2" disabled>
                                <option value="">Select color</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row align-items-end mt-2 pt-2 border-top">
                        <div class="col-md-3 mb-2">
                            <div class="p-2 rounded bg-light border">
                                <span class="erp-label d-block text-muted mb-0">Available stock</span>
                                <h5 class="mb-0 font-weight-bold text-success" id="available_qty_display">0 <small class="text-muted">Boxes</small></h5>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="erp-label">Quantity to dispose <span class="text-danger">*</span></label>
                            <input type="number" id="dispose_qty" class="form-control form-control-sm erp-input" placeholder="Enter boxes..." min="1" step="1">
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn-erp btn-erp-primary w-100" id="add_domestic_item">
                                <i class="fas fa-plus mr-1"></i> Add to List
                            </button>
                        </div>
                    </div>
                </div>

                <div class="erp-card mb-2 p-0">
                    <div class="p-2 border-bottom bg-light">
                        <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                            <i class="fas fa-list mr-1"></i> Items Added for Disposal
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Product/Design</th>
                                    <th>Warehouse</th>
                                    <th>Size/Color</th>
                                    <th class="text-center" style="width: 140px;">Quantity</th>
                                    <th class="text-center" style="width: 60px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="domesticItemsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Reason & Remarks Card -->
            <div class="erp-card mb-2 p-3">
                <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                    <i class="fas fa-clipboard-check mr-1"></i> Disposal Reason & Remarks
                </h6>
                <div class="form-row">
                    <div class="col-md-4 mb-2">
                        <label class="erp-label">Reason for disposal <span class="text-danger">*</span></label>
                        <select name="reason" class="form-control form-control-sm erp-input select2" required>
                            <option value="">Select reason</option>
                            <option value="Damage" {{ $main->reason == 'Damage' ? 'selected' : '' }}>Damage</option>
                            <option value="Lost" {{ $main->reason == 'Lost' ? 'selected' : '' }}>Lost</option>
                            <option value="Sample" {{ $main->reason == 'Sample' ? 'selected' : '' }}>Sample</option>
                            <option value="Wrong Entry" {{ $main->reason == 'Wrong Entry' ? 'selected' : '' }}>Wrong Entry</option>
                            <option value="Other" {{ $main->reason == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="erp-label">Remarks / notes</label>
                        <textarea name="remarks" class="form-control form-control-sm erp-input" rows="2" placeholder="Optional audit details...">{{ $main->remarks }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="erp-card p-2 text-right">
                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn-erp btn-erp-secondary mr-2">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary" id="submit_btn">
                    <i class="fas fa-save mr-1"></i> Update Stock Disposal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        let itemType = "{{ $main->item_type }}";
        let domesticItems = [];

        @if($main->item_type === 'domestic')
            // Pre-load domestic items
            @foreach($main->items->groupBy(function($item) {
                return $item->domesticInventory->product_id . '-' . $item->domesticInventory->size_set_id . '-' . $item->domesticInventory->color_id . '-' . $item->domesticInventory->rack->storeroom_id;
            }) as $key => $groupedItems)
                @php 
                    $first = $groupedItems->first();
                    $inventory = $first->domesticInventory;
                @endphp
                domesticItems.push({
                    warehouse_id: "{{ $inventory->rack->storeroom_id }}",
                    warehouse_name: "{{ $inventory->rack->storeroom->name }}",
                    product_id: "{{ $inventory->product_id }}",
                    product_name: "{{ $inventory->product->design_number }}",
                    size_set_id: "{{ $inventory->size_set_id }}",
                    size_name: "{{ $inventory->sizeSet->name }}",
                    color_id: "{{ $inventory->color_id }}",
                    color_name: "{{ $inventory->color->name }}",
                    quantity: {{ $groupedItems->sum('quantity') }}
                });
            @endforeach
            renderDomesticList();
        @endif

        let currentFabricIds = {!! json_encode($main->items->pluck('fabricReceiptDetail.fabric_id')->unique()->values()) !!};
        let currentRollIds = {!! json_encode($main->items->pluck('item_id')->values()) !!};
        let currentRollQty = {!! json_encode($main->items->pluck('quantity', 'item_id')) !!};

        // --- Fabric Logic ---
        $('#fabric_warehouse').change(function(e, isInitial = false) {
            let whId = $(this).val();
            if (whId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-fabrics') }}", {
                    warehouse_id: whId, 
                    disposal_id: "{{ $main->id }}"
                }, function(data) {
                    let options = '<option value="">Select fabric</option>';
                    data.forEach(f => {
                        let selected = currentFabricIds.includes(f.id.toString()) || currentFabricIds.includes(f.id) ? 'selected' : '';
                        options += `<option value="${f.id}" ${selected}>${f.name}</option>`;
                    });
                    $('#fabric_id').html(options).prop('disabled', false);
                    if (isInitial) $('#fabric_id').trigger('change');
                });
            }
        });

        if (itemType === 'fabric') {
            $('#fabric_warehouse').trigger('change', [true]);
        }

        $('#fabric_id').change(function() {
            let fabricIds = $(this).val();
            let whId = $('#fabric_warehouse').val();
            if (fabricIds && fabricIds.length > 0 && whId) {
                $('#rollsBody').html('<tr><td colspan="4" class="text-center py-4 text-muted">Loading rolls...</td></tr>');
                $.get("{{ route('admin.inventory.stock_disposal.get-rolls') }}", {
                    warehouse_id: whId, 
                    fabric_ids: fabricIds,
                    disposal_id: "{{ $main->id }}"
                }, function(data) {
                    let rows = '';
                    if (data.length > 0) {
                        data.forEach(r => {
                            let checked = currentRollIds.includes(r.id.toString()) || currentRollIds.includes(r.id) ? "checked" : "";
                            let originalQty = currentRollQty[r.id] ? parseFloat(currentRollQty[r.id]) : 0;
                            let totalAvailable = parseFloat(r.remaining_quantity) + originalQty;
                            let inputVal = checked ? originalQty : totalAvailable;

                            rows += `<tr>
                                <td class="text-center"><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${r.id}" value="${r.id}" ${checked}><label for="roll_${r.id}" class="custom-control-label"></label></div></td>
                                <td><span class="badge-erp-status badge-erp-info font-weight-bold">${r.roll_number}</span><div class="small text-muted">${r.fabric.name}</div></td>
                                <td><strong>${totalAvailable}</strong> mtr</td>
                                <td><input type="number" name="roll_qty[${r.id}]" class="form-control form-control-sm erp-input" max="${totalAvailable}" min="0.01" step="0.01" value="${inputVal}"></td>
                            </tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="4" class="text-center py-4 text-warning">No rolls found</td></tr>';
                    }
                    $('#rollsBody').html(rows);
                });
            }
        });

        $('#selectAllRolls').change(function() {
            $('.roll-check').prop('checked', $(this).is(':checked'));
        });

        // --- Domestic Logic ---
        $('#product_id').on('change', function() {
            let pId = $(this).val();
            $('#size_set_id').empty().append('<option value="">Select size</option>').prop('disabled', true).trigger('change');
            if (pId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-product-details') }}", { product_id: pId }, function(res) {
                    if (res.success) {
                        res.size_sets.forEach(s => $('#size_set_id').append(`<option value="${s.id}">${s.name}</option>`));
                        $('#size_set_id').prop('disabled', false).trigger('change');
                    }
                });
            }
        });

        $('#size_set_id').on('change', function() {
            let sId = $(this).val();
            let pId = $('#product_id').val();
            $('#color_id').empty().append('<option value="">Select color</option>').prop('disabled', true).trigger('change');
            if (sId && pId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-size-colors') }}", { product_id: pId, size_set_id: sId }, function(res) {
                    if (res.success) {
                        res.colors.forEach(c => $('#color_id').append(`<option value="${c.id}">${c.name}</option>`));
                        $('#color_id').prop('disabled', false).trigger('change');
                    }
                });
            }
        });

        $('#domestic_warehouse, #product_id, #size_set_id, #color_id').on('change', function() {
            let whId = $('#domestic_warehouse').val();
            let pId = $('#product_id').val();
            let sId = $('#size_set_id').val();
            let cId = $('#color_id').val();
            if (whId && pId && sId && cId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-domestic-stock') }}", {
                    warehouse_id: whId, product_id: pId, size_set_id: sId, color_id: cId
                }, function(response) {
                    $('#available_qty_display').html(response.available + ' <small class="text-muted">Boxes</small>');
                    $('#dispose_qty').attr('max', response.available);
                });
            }
        });

        $('#add_domestic_item').click(function() {
            let whId = $('#domestic_warehouse').val();
            let whName = $('#domestic_warehouse option:selected').text();
            let pId = $('#product_id').val();
            let pName = $('#product_id option:selected').text();
            let sId = $('#size_set_id').val();
            let sName = $('#size_set_id option:selected').text();
            let cId = $('#color_id').val();
            let cName = $('#color_id option:selected').text();
            let qty = parseFloat($('#dispose_qty').val());

            if (!whId || !pId || !sId || !cId || !qty || qty <= 0) return Swal.fire('Error', 'Invalid details', 'error');

            let exists = domesticItems.find(item => item.product_id == pId && item.size_set_id == sId && item.color_id == cId && item.warehouse_id == whId);
            if (exists) return Swal.fire('Warning', 'Already in list', 'warning');

            domesticItems.push({ 
                warehouse_id: whId, warehouse_name: whName,
                product_id: pId, product_name: pName,
                size_set_id: sId, size_name: sName,
                color_id: cId, color_name: cName,
                quantity: qty 
            });
            renderDomesticList();
            $('#dispose_qty').val('');
        });

        function renderDomesticList() {
            let rows = '';
            domesticItems.forEach((item, index) => {
                rows += `<tr>
                    <td>${item.product_name}</td>
                    <td>${item.warehouse_name}</td>
                    <td>${item.size_name} / ${item.color_name}</td>
                    <td class="text-center font-weight-bold">${item.quantity} Boxes</td>
                    <td class="text-center"><button type="button" class="erp-action-btn erp-btn-delete" onclick="removeDomesticItem(${index})"><i class="fas fa-trash"></i></button></td>
                </tr>`;
            });
            $('#domesticItemsBody').html(rows || '<tr><td colspan="5" class="text-center py-4 text-muted">No items</td></tr>');
        }

        window.removeDomesticItem = function(index) {
            domesticItems.splice(index, 1);
            renderDomesticList();
        };

        $('#disposalForm').submit(function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            if (itemType === 'domestic') formData.append('domestic_items', JSON.stringify(domesticItems));

            Swal.fire({
                title: 'Update Disposal?',
                text: "This will restore old stock and apply new deductions.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#05421c',
                confirmButtonText: 'Yes, Update'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/inventory/stock-disposal/update') }}/{{ $main->id }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Updated', res.message, 'success').then(() => {
                                    window.location.href = "{{ route('admin.inventory.stock_disposal.index') }}";
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
