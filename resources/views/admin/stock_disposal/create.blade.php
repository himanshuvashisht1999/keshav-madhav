@extends('admin.layouts.app')

@section('title', 'New Stock Disposal')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>New Stock Disposal</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold">Stock Disposal Form</h3>
                        </div>
                        <form id="disposalForm">
                            @csrf
                            <div class="card-body">
                                <!-- Stock Type Selection -->
                                <div class="row justify-content-center mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group text-center">
                                            <label class="d-block mb-3 text-muted text-uppercase small font-weight-bold">Select Stock Type to Dispose</label>
                                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                                <label class="btn btn-outline-primary active flex-fill py-3 font-weight-bold">
                                                    <input type="radio" name="item_type" value="fabric" autocomplete="off" checked> 
                                                    <i class="fas fa-scroll mr-2"></i> Fabric Roll
                                                </label>
                                                <label class="btn btn-outline-primary flex-fill py-3 font-weight-bold">
                                                    <input type="radio" name="item_type" value="domestic" autocomplete="off"> 
                                                    <i class="fas fa-boxes mr-2"></i> Domestic Stock
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="mb-4">

                                <!-- Fabric Section -->
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
                                                                <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
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
                                                                    <th width="120">Dispose (Mtr)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="rollsBody">
                                                                <tr>
                                                                    <td colspan="4" class="text-center py-5 text-muted">
                                                                        Select Warehouse and Fabric to load rolls
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Domestic Section -->
                                <div id="domestic_section" style="display: none;" class="animate-in">
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
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-4 text-muted">No items added to the list yet.</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Reason & Remarks -->
                                <div class="row justify-content-center">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-danger text-uppercase">Reason for Disposal <span class="text-danger">*</span></label>
                                            <select name="reason" class="form-control select2" required>
                                                <option value="">Select Reason</option>
                                                <option value="Damage">Damage</option>
                                                <option value="Lost">Lost</option>
                                                <option value="Sample">Sample</option>
                                                <option value="Wrong Entry">Wrong Entry</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="small font-weight-bold text-uppercase">Remarks / Notes</label>
                                            <textarea name="remarks" class="form-control" rows="1" placeholder="Optional details..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-white py-3">
                                <button type="submit" class="btn btn-danger float-right px-5 py-2 font-weight-bold shadow" id="submit_btn">
                                    <i class="fas fa-trash-alt mr-2"></i> Process Stock Disposal
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

        // Toggle Sections
        $('input[name="item_type"]').on('change', function() {
            if ($(this).val() === 'fabric') {
                $('#fabric_section').fadeIn();
                $('#domestic_section').hide();
            } else {
                $('#fabric_section').hide();
                $('#domestic_section').fadeIn();
            }
        });

        // --- Fabric Logic ---
        $('#fabric_warehouse').change(function() {
            let whId = $(this).val();
            $('#fabric_id').val('').trigger('change').prop('disabled', true);
            if (whId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-fabrics') }}", {warehouse_id: whId}, function(data) {
                    let options = '<option value="">Select Fabric</option>';
                    data.forEach(f => options += `<option value="${f.id}">${f.name}</option>`);
                    $('#fabric_id').html(options).prop('disabled', false);
                });
            }
        });

        $('#fabric_id').change(function() {
            let fabricIds = $(this).val();
            let whId = $('#fabric_warehouse').val();
            if (fabricIds && fabricIds.length > 0 && whId) {
                $('#rollsBody').html('<tr><td colspan="4" class="text-center py-4">Loading rolls...</td></tr>');
                $.get("{{ route('admin.inventory.stock_disposal.get-rolls') }}", {warehouse_id: whId, fabric_ids: fabricIds}, function(data) {
                    let rows = '';
                    if (data.length > 0) {
                        data.forEach(r => {
                            rows += `<tr>
                                <td><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${r.id}" value="${r.id}"><label for="roll_${r.id}" class="custom-control-label"></label></div></td>
                                <td><span class="badge badge-info">${r.roll_number}</span><div class="small text-muted">${r.fabric.name}</div></td>
                                <td><strong>${r.remaining_quantity}</strong> mtr</td>
                                <td><input type="number" name="roll_qty[${r.id}]" class="form-control form-control-sm" max="${r.remaining_quantity}" min="0.01" step="0.01" value="${r.remaining_quantity}"></td>
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
        let domesticItems = [];

        $('#product_id').on('change', function() {
            let pId = $(this).val();
            $('#size_set_id').empty().append('<option value="">Select Size</option>').prop('disabled', true).trigger('change');
            $('#color_id').empty().append('<option value="">Select Color</option>').prop('disabled', true).trigger('change');
            
            if (pId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-product-details') }}", { product_id: pId }, function(res) {
                    if (res.success) {
                        res.size_sets.forEach(s => {
                            $('#size_set_id').append(`<option value="${s.id}">${s.name}</option>`);
                        });
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
                        res.colors.forEach(c => {
                            $('#color_id').append(`<option value="${c.id}">${c.name}</option>`);
                        });
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

            $('#available_qty_display').html('0 <span class="small text-muted">Boxes</span>');
            $('#dispose_qty').attr('max', 0);

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
            let max = parseFloat($('#dispose_qty').attr('max'));

            if (!whId || !pId || !sId || !cId || !qty || qty <= 0) {
                Swal.fire('Error', 'Please fill all fields and enter a valid quantity.', 'error');
                return;
            }

            if (qty > max) {
                Swal.fire('Error', 'Quantity exceeds available stock.', 'error');
                return;
            }

            // Check if item already exists in list
            let exists = domesticItems.find(item => item.product_id == pId && item.size_set_id == sId && item.color_id == cId && item.warehouse_id == whId);
            if (exists) {
                Swal.fire('Warning', 'This item is already in the list.', 'warning');
                return;
            }

            let item = { warehouse_id: whId, product_id: pId, size_set_id: sId, color_id: cId, quantity: qty };
            domesticItems.push(item);
            renderDomesticList();
            $('#dispose_qty').val('');
        });

        function renderDomesticList() {
            let rows = '';
            if (domesticItems.length === 0) {
                rows = '<tr><td colspan="5" class="text-center py-4 text-muted">No items added to the list yet.</td></tr>';
            } else {
                domesticItems.forEach((item, index) => {
                    let whName = $(`#domestic_warehouse option[value="${item.warehouse_id}"]`).text();
                    let pName = $(`#product_id option[value="${item.product_id}"]`).text();
                    let sName = $(`#size_set_id option[value="${item.size_set_id}"]`).text();
                    let cName = $(`#color_id option[value="${item.color_id}"]`).text();
                    rows += `<tr>
                        <td>${pName}</td>
                        <td>${whName}</td>
                        <td>${sName} / ${cName}</td>
                        <td><strong>${item.quantity}</strong> Boxes</td>
                        <td><button type="button" class="btn btn-xs btn-danger" onclick="removeDomesticItem(${index})"><i class="fas fa-times"></i></button></td>
                    </tr>`;
                });
            }
            $('#domesticItemsBody').html(rows);
        }

        window.removeDomesticItem = function(index) {
            domesticItems.splice(index, 1);
            renderDomesticList();
        };

        // --- Form Submission ---
        $('#disposalForm').submit(function(e) {
            e.preventDefault();
            
            let type = $('input[name="item_type"]:checked').val();
            let formData = new FormData(this);
            formData.append('item_type', type);

            if (type === 'fabric') {
                if ($('.roll-check:checked').length === 0) {
                    Swal.fire('Warning', 'Please select at least one roll to dispose.', 'warning');
                    return;
                }
            } else {
                if (domesticItems.length === 0) {
                    Swal.fire('Warning', 'Please add at least one item to the list.', 'warning');
                    return;
                }
                formData.append('domestic_items', JSON.stringify(domesticItems));
            }

            Swal.fire({
                title: 'Confirm Disposal?',
                text: `This action will process ${type === 'fabric' ? $('.roll-check:checked').length : domesticItems.length} items.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, Process Disposal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.inventory.stock_disposal.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                Swal.fire('Success', res.message, 'success').then(() => {
                                    window.location.href = "{{ route('admin.inventory.stock_disposal.index') }}";
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON.message || 'Processing failed', 'error');
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
    .btn-group-toggle .btn:hover { border-color: #6366f1; }
    .btn-group-toggle .btn.active { background-color: #6366f1 !important; color: #fff !important; border-color: #6366f1; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3); }
    .callout { border-left-width: 5px; border-radius: 8px; background: #f8fafc; }
    .sticky-top { z-index: 10; background: white; }
</style>
@endsection
