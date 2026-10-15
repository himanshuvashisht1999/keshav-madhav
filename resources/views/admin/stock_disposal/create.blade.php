@extends('admin.layouts.app')

@section('title', 'New Stock Disposal')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-trash-alt text-primary mr-2"></i> New Stock Disposal
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to History
                </a>
            </div>
        </div>

        <form id="disposalForm">
            @csrf
            
            <!-- Stock Type Selection Card -->
            <div class="erp-card mb-2 p-3 text-center">
                <label class="erp-label mb-2 font-weight-bold" style="font-size: 0.9rem;">Select Stock Type to Dispose</label>
                <div class="d-flex justify-content-center">
                    {{-- no data-toggle="buttons": Bootstrap blocks clicks on labels without the .btn class; the script below switches the sections --}}
                    <div class="btn-group" style="max-width: 450px; width: 100%;">
                        <label class="btn-erp btn-erp-primary flex-fill py-2" style="cursor: pointer;">
                            <input type="radio" name="item_type" value="fabric" autocomplete="off" checked> 
                            <i class="fas fa-scroll mr-2"></i> Fabric Roll
                        </label>
                        <label class="btn-erp btn-erp-outline flex-fill py-2 ml-2" style="cursor: pointer;">
                            <input type="radio" name="item_type" value="domestic" autocomplete="off"> 
                            <i class="fas fa-boxes mr-2"></i> Domestic Stock
                        </label>
                    </div>
                </div>
            </div>

            <!-- Fabric Section -->
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
                                        <option value="{{ $wh->id }}">{{ $wh->cutting_master_name }}</option>
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
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                Select warehouse and fabric to load rolls.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Domestic Section -->
            <div id="domestic_section" style="display: none;">
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
                            <tbody id="domesticItemsBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No items added to the list yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
                            <option value="Damage">Damage</option>
                            <option value="Lost">Lost</option>
                            <option value="Sample">Sample</option>
                            <option value="Wrong Entry">Wrong Entry</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="erp-label">Remarks / notes</label>
                        <textarea name="remarks" class="form-control form-control-sm erp-input" rows="2" placeholder="Optional audit details..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="erp-card p-2 text-right">
                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn-erp btn-erp-secondary mr-2">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-danger" id="submit_btn">
                    <i class="fas fa-trash-alt mr-1"></i> Process Stock Disposal
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

        // Toggle Sections
        $('input[name="item_type"]').on('change', function() {
            if ($(this).val() === 'fabric') {
                $('#fabric_section').show();
                $('#domestic_section').hide();
                $(this).closest('label').addClass('btn-erp-primary').removeClass('btn-erp-outline');
                $('input[name="item_type"][value="domestic"]').closest('label').addClass('btn-erp-outline').removeClass('btn-erp-primary');
            } else {
                $('#fabric_section').hide();
                $('#domestic_section').show();
                $(this).closest('label').addClass('btn-erp-primary').removeClass('btn-erp-outline');
                $('input[name="item_type"][value="fabric"]').closest('label').addClass('btn-erp-outline').removeClass('btn-erp-primary');
            }
        });

        // --- Fabric Logic ---
        $('#fabric_warehouse').change(function() {
            let whId = $(this).val();
            $('#fabric_id').val('').trigger('change').prop('disabled', true);
            if (whId) {
                $.get("{{ route('admin.inventory.stock_disposal.get-fabrics') }}", {warehouse_id: whId}, function(data) {
                    let options = '<option value="">Select fabric</option>';
                    data.forEach(f => options += `<option value="${f.id}">${f.name}</option>`);
                    $('#fabric_id').html(options).prop('disabled', false);
                });
            }
        });

        $('#fabric_id').change(function() {
            let fabricIds = $(this).val();
            let whId = $('#fabric_warehouse').val();
            if (fabricIds && fabricIds.length > 0 && whId) {
                $('#rollsBody').html('<tr><td colspan="4" class="text-center py-4 text-muted">Loading rolls...</td></tr>');
                $.get("{{ route('admin.inventory.stock_disposal.get-rolls') }}", {warehouse_id: whId, fabric_ids: fabricIds}, function(data) {
                    let rows = '';
                    if (data.length > 0) {
                        data.forEach(r => {
                            rows += `<tr>
                                <td class="text-center"><div class="custom-control custom-checkbox"><input class="custom-control-input roll-check" type="checkbox" name="roll_ids[]" id="roll_${r.id}" value="${r.id}"><label for="roll_${r.id}" class="custom-control-label"></label></div></td>
                                <td><span class="badge-erp-status badge-erp-info font-weight-bold">${r.roll_number}</span><div class="small text-muted">${r.fabric.name}</div></td>
                                <td><strong>${r.remaining_quantity}</strong> mtr</td>
                                <td><input type="number" name="roll_qty[${r.id}]" class="form-control form-control-sm erp-input" max="${r.remaining_quantity}" min="0.01" step="0.01" value="${r.remaining_quantity}"></td>
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
            $('#size_set_id').empty().append('<option value="">Select size</option>').prop('disabled', true).trigger('change');
            $('#color_id').empty().append('<option value="">Select color</option>').prop('disabled', true).trigger('change');
            
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
            $('#color_id').empty().append('<option value="">Select color</option>').prop('disabled', true).trigger('change');

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

            $('#available_qty_display').html('0 <small class="text-muted">Boxes</small>');
            $('#dispose_qty').attr('max', 0);

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
            let pId = $('#product_id').val();
            let sId = $('#size_set_id').val();
            let cId = $('#color_id').val();
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
                        <td class="text-center font-weight-bold">${item.quantity} Boxes</td>
                        <td class="text-center"><button type="button" class="erp-action-btn erp-btn-delete" onclick="removeDomesticItem(${index})"><i class="fas fa-trash"></i></button></td>
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
                confirmButtonColor: '#dc2626',
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
@endsection
