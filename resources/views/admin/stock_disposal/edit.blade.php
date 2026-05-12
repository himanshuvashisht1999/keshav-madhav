@extends('admin.layouts.app')

@section('title', 'Edit Stock Disposal')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Stock Disposal</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-warning card-outline shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold">Update Disposal: {{ $main->disposal_no }}</h3>
                        </div>
                        <form id="disposalForm">
                            @csrf
                            <div class="card-body">
                                <!-- Stock Type Selection (Disabled in Edit) -->
                                <div class="row justify-content-center mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group text-center">
                                            <label class="d-block mb-3 text-muted text-uppercase small font-weight-bold">Stock Type (Cannot be changed)</label>
                                            <div class="btn-group d-flex">
                                                <button type="button" class="btn btn-{{ $main->item_type === 'fabric' ? 'primary' : 'outline-secondary' }} flex-fill py-3 font-weight-bold disabled">
                                                    <i class="fas fa-scroll mr-2"></i> Fabric Roll
                                                </button>
                                                <button type="button" class="btn btn-{{ $main->item_type === 'domestic' ? 'primary' : 'outline-secondary' }} flex-fill py-3 font-weight-bold disabled">
                                                    <i class="fas fa-boxes mr-2"></i> Domestic Stock
                                                </button>
                                            </div>
                                            <input type="hidden" name="item_type" value="{{ $main->item_type }}">
                                        </div>
                                    </div>
                                </div>

                                <hr class="mb-4">

                                <!-- Fabric Section -->
                                @if($main->item_type === 'fabric')
                                <div id="fabric_section" class="animate-in">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="card bg-light border-0 shadow-sm mb-3">
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Warehouse <span class="text-danger">*</span></label>
                                                        <select id="fabric_warehouse" name="warehouse_id" class="form-control select2">
                                                            <option value="">Select Warehouse</option>
                                                            @foreach($fabricWarehouses as $wh)
                                                                <option value="{{ $wh->id }}" {{ optional($main->items->first())->fabricReceiptDetail->master_fabric_warehouse_id == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group mt-3">
                                                        <label class="small font-weight-bold">Fabric <span class="text-danger">*</span></label>
                                                        <select id="fabric_id" class="form-control select2" multiple disabled>
                                                            <option value="">Select Fabric</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 font-weight-bold">Select Rolls to Dispose</h6>
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" id="selectAllRolls">
                                                        <label for="selectAllRolls" class="custom-control-label small">Select All</label>
                                                    </div>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                        <table class="table table-hover table-striped mb-0">
                                                            <thead class="bg-light sticky-top">
                                                                <tr>
                                                                    <th width="40">#</th>
                                                                    <th>Roll No</th>
                                                                    <th>Remaining (Mtr)</th>
                                                                    <th>Barcode</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="rollsBody">
                                                                @foreach($main->items as $item)
                                                                    @php $r = $item->fabricReceiptDetail; @endphp
                                                                    <tr>
                                                                        <td><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_{{ $r->id }}" value="{{ $r->id }}" checked><label for="roll_{{ $r->id }}" class="custom-control-label"></label></div></td>
                                                                        <td><span class="badge badge-info">{{ $r->roll_number }}</span><div class="small text-muted">{{ $r->fabric->name }}</div></td>
                                                                        <td><strong>{{ $r->remaining_quantity + $item->quantity }}</strong> mtr <small class="text-success">(Incl. current disposal)</small></td>
                                                                        <td><small>{{ $r->barcode }}</small></td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Domestic Section -->
                                @if($main->item_type === 'domestic')
                                <div id="domestic_section" class="animate-in">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Warehouse <span class="text-danger">*</span></label>
                                                <select id="domestic_warehouse" name="warehouse_id" class="form-control select2">
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($storerooms as $wh)
                                                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Design No <span class="text-danger">*</span></label>
                                                <select id="product_id" name="product_id" class="form-control select2">
                                                    <option value="">Select Design</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}">{{ $p->design_number }} ({{ $p->series->name ?? '' }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Size Set <span class="text-danger">*</span></label>
                                                <select id="size_set_id" name="size_set_id" class="form-control select2" disabled>
                                                    <option value="">Select Size</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Color <span class="text-danger">*</span></label>
                                                <select id="color_id" name="color_id" class="form-control select2" disabled>
                                                    <option value="">Select Color</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="callout callout-info py-2">
                                                <span class="text-muted small">Available Stock:</span>
                                                <h4 class="mb-0 font-weight-bold" id="available_qty_display">0 <span class="small text-muted">Boxes</span></h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="small font-weight-bold">Quantity to Dispose <span class="text-danger">*</span></label>
                                                <input type="number" id="dispose_qty" class="form-control" placeholder="Enter boxes..." min="1" step="1">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group pt-4">
                                                <button type="button" class="btn btn-outline-primary btn-block font-weight-bold" id="add_domestic_item">
                                                    <i class="fas fa-plus mr-1"></i> Add to List
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-header bg-white">
                                                    <h6 class="mb-0 font-weight-bold">Items Added for Disposal</h6>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover mb-0">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th>Product/Design</th>
                                                                    <th>Warehouse</th>
                                                                    <th>Size/Color</th>
                                                                    <th>Quantity</th>
                                                                    <th width="50">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="domesticItemsBody">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <hr class="my-4">

                                <!-- Reason & Remarks -->
                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-danger text-uppercase">Reason for Disposal <span class="text-danger">*</span></label>
                                            <select name="reason" class="form-control select2" required>
                                                <option value="">Select Reason</option>
                                                <option value="Damage" {{ $main->reason == 'Damage' ? 'selected' : '' }}>Damage</option>
                                                <option value="Lost" {{ $main->reason == 'Lost' ? 'selected' : '' }}>Lost</option>
                                                <option value="Sample" {{ $main->reason == 'Sample' ? 'selected' : '' }}>Sample</option>
                                                <option value="Wrong Entry" {{ $main->reason == 'Wrong Entry' ? 'selected' : '' }}>Wrong Entry</option>
                                                <option value="Other" {{ $main->reason == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-uppercase">Remarks / Notes</label>
                                            <textarea name="remarks" class="form-control" rows="1" placeholder="Optional details...">{{ $main->remarks }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-3">
                                <button type="submit" class="btn btn-warning float-right px-5 py-2 font-weight-bold shadow" id="submit_btn">
                                    <i class="fas fa-save mr-2"></i> Update Stock Disposal
                                </button>
                                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn btn-default py-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

        // --- Fabric Logic ---
        $('#fabric_warehouse').change(function(e, isInitial = false) {
            let whId = $(this).val();
            if (whId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-fabrics') }}", {
                    warehouse_id: whId, 
                    disposal_id: "{{ $main->id }}"
                }, function(data) {
                    let options = '<option value="">Select Fabric</option>';
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
                $('#rollsBody').html('<tr><td colspan="4" class="text-center py-4">Loading rolls...</td></tr>');
                $.get("{{ route('admin.inventory.stock_disposal.get-rolls') }}", {
                    warehouse_id: whId, 
                    fabric_ids: fabricIds,
                    disposal_id: "{{ $main->id }}"
                }, function(data) {
                    let rows = '';
                    if (data.length > 0) {
                        data.forEach(r => {
                            let checked = currentRollIds.includes(r.id.toString()) || currentRollIds.includes(r.id) ? "checked" : "";

                            rows += `<tr>
                                <td><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${r.id}" value="${r.id}" ${checked}><label for="roll_${r.id}" class="custom-control-label"></label></div></td>
                                <td><span class="badge badge-info">${r.roll_number}</span><div class="small text-muted">${r.fabric.name}</div></td>
                                <td><strong>${r.remaining_quantity}</strong> mtr</td>
                                <td><small>${r.barcode}</small></td>
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
            $('#size_set_id').empty().append('<option value="">Select Size</option>').prop('disabled', true).trigger('change');
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
            $('#color_id').empty().append('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
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
                    $('#available_qty_display').html(response.available + ' <span class="small text-muted">Boxes</span>');
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
                    <td><strong>${item.quantity}</strong> Boxes</td>
                    <td><button type="button" class="btn btn-xs btn-danger" onclick="removeDomesticItem(${index})"><i class="fas fa-times"></i></button></td>
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
<style>
    .animate-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .btn-group-toggle .btn { border-radius: 10px !important; margin: 0 5px; border: 2px solid transparent; transition: all 0.2s; }
    .btn-group-toggle .btn.active { background-color: #6366f1 !important; color: #fff !important; }
    .callout { border-left-width: 5px; border-radius: 8px; background: #f8fafc; }
    .sticky-top { z-index: 10; background: white; }
</style>
@endsection
