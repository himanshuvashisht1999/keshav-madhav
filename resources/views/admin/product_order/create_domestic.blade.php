@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12 text-center">
                        <h1>Create Domestic Order</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-11">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <form action="{{ route('admin.sales_order.store_domestic') }}" method="POST">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Select Design (Optional)</label>
                                                <select name="production_goods_id" id="production_goods_id"
                                                    class="form-control select2">
                                                    <option value="">-- Use Default Domestic Design --</option>
                                                    @foreach($products as $prod)
                                                        <option value="{{ $prod->id }}">{{ $prod->design_number }}
                                                            ({{ $prod->name_of_garment }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="mb-0 font-weight-bold">Select Size Set</label>
                                                    <a href="javascript:void(0)" id="openCustomSizeBtn"
                                                        class="text-primary font-weight-bold"
                                                        style="display:none; font-size:13px; text-decoration:underline;">
                                                        Update Ratio
                                                    </a>
                                                </div>
                                                <select name="set_size_id" id="set_size_id" class="form-control select2"
                                                    required>
                                                    <option value="">Select Size Set</option>
                                                    @foreach($sizes as $size)
                                                        <option value="{{ $size->id }}" data-set-group="{{ $size->size_group }}"
                                                            data-pcs="{{ $size->no_of_pcs }}">
                                                            {{ $size->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div id="custom_ratio_info"
                                                    class="mt-1 small text-success font-weight-bold"></div>
                                                <input type="hidden" id="size_set_hidden" name="size_set_hidden">
                                                <input type="hidden" id="no_of_pcs_hidden" name="no_of_pcs_hidden">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-4">
                                                <label class="font-weight-bold">Quantity (Sets)</label>
                                                <input type="number" name="product_quantity" class="form-control" min="1"
                                                    required placeholder="Enter number of sets">
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h5 class="mb-4 text-primary font-weight-bold">Assign to Cutting Master (Optional)</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Warehouse</label>
                                                <select id="warehouse_id" class="form-control select2"
                                                    onchange="warehouseChange(this.value)">
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($cutting_units as $w)
                                                        <option value="{{ $w['id'] }}">{{ $w['warehouse_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Cutting Master</label>
                                                <select name="master_cutting_id" id="master_cutting_id"
                                                    class="form-control select2">
                                                    <option value="">Select Cutting Master</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Fabric</label>
                                                <select name="fabric_id" id="fabric_id" class="form-control select2">
                                                    <option value="">Select Fabric</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label>Fitting</label>
                                                        <select name="master_fitting_id" id="master_fitting_id"
                                                            class="form-control select2">
                                                            <option value="">Select Fitting</option>
                                                            @foreach($fittings as $fitting)
                                                                <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label>Product Style</label>
                                                        <select name="master_pattern_id" id="master_pattern_id"
                                                            class="form-control select2">
                                                            <option value="">Select Pattern</option>
                                                            @foreach($patterns as $pattern)
                                                                <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h5 class="mb-4 text-primary font-weight-bold">Printing Preferences</h5>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label>Printing Required?</label>
                                                <select name="is_printing" id="is_printing" class="form-control" onchange="togglePrinting(this.value)">
                                                    <option value="no">No</option>
                                                    <option value="yes">Yes</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3" id="printing_unit_group" style="display:none;">
                                                <label>Printing & Embroidery Unit</label>
                                                <select name="printing_unit_id" id="printing_unit_id" class="form-control select2">
                                                    <option value="">Select Printing Unit</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label>Remark</label>
                                        <textarea name="remark" class="form-control" rows="2"
                                            placeholder="Any special instructions..."></textarea>
                                    </div>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
                                            <i class="fas fa-check-circle mr-2"></i> Create & Assign Order
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- PHP DATA TO JS -->
    <script>
        const warehouses = Object.values(@json($cutting_units));
        const printing_warehouses = Object.values(@json($printing_units ?? []));
    </script>

    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });
            
            printingWarehouseChange();

            // Show/Hide Update Ratio button
            $('#set_size_id').on('change', function () {
                if ($(this).val()) {
                    $('#openCustomSizeBtn').show();
                } else {
                    $('#openCustomSizeBtn').hide();
                }
                // Clear previous custom info if selection changes
                $('#custom_ratio_info').text('');
                $('#size_set_hidden').val('');
                $('#no_of_pcs_hidden').val('');
            });

            // Open Modal
            $('#openCustomSizeBtn').on('click', function () {
                openModal();
            });
        });

        let sizeCounts = {};
        let currentSetSizeOption = null;

        function openModal() {
            let select = $('#set_size_id');
            let option = select.find(':selected');
            if (!option.val()) return;

            let setGroup = option.data('set-group') || "";
            let sizeName = option.text();

            $('#size_name_display').text(sizeName);

            // Initialize sizeCounts from the group string (e.g. "18,19,20")
            sizeCounts = {};
            if (setGroup) {
                setGroup.toString().split(',').forEach(size => {
                    size = size.trim();
                    if (size) {
                        sizeCounts[size] = (sizeCounts[size] || 0) + 1;
                    }
                });
            }

            renderSizes();
            $('#sizeModal').css('display', 'flex');
        }

        function closeModal() {
            $('#sizeModal').hide();
        }

        function changeCount(size, change) {
            sizeCounts[size] = (sizeCounts[size] || 0) + change;
            if (sizeCounts[size] < 0) sizeCounts[size] = 0;
            renderSizes();
        }

        function renderSizes() {
            let container = $('#sizeListContainer');
            container.empty();

            let allSizes = [];
            Object.keys(sizeCounts).sort((a, b) => a - b).forEach(size => {
                let count = sizeCounts[size];
                for (let i = 0; i < count; i++) allSizes.push(size);

                container.append(`
                <div class="size-editor-row">
                    <strong>Size: ${size}</strong>
                    <div class="counter-controls">
                        <button type="button" onclick="changeCount('${size}', -1)">-</button>
                        <span>${count}</span>
                        <button type="button" onclick="changeCount('${size}', 1)">+</button>
                    </div>
                </div>
            `);
            });

            $('#proposedRatioText').text(allSizes.join(','));
        }

        function saveCustomRatio() {
            let finalGroup = $('#proposedRatioText').text();
            if (!finalGroup || finalGroup === '—') {
                alert("Please specify at least one size.");
                return;
            }

            let select = $('#set_size_id');
            let option = select.find(':selected');
            let originalGroup = option.data('set-group');

            // If no change, just close
            if (finalGroup === originalGroup) {
                closeModal();
                return;
            }

            $.ajax({
                url: "{{ route('admin.sales_order.saveCustomSetSize') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    customer_id: 0, // Placeholder for Domestic (SnapKid DM handled in service)
                    set_size_id: select.val(),
                    set_size_name: option.text().trim(),
                    finalGroup: finalGroup,
                    design_id: 0 // Placeholder for Domestic
                },
                success: function (res) {
                    if (res.new_size_group) {
                        $('#custom_ratio_info').text("Custom Ratio Applied: (" + res.new_size_group + ")");
                        $('#size_set_hidden').val(res.new_size_set_id);
                        $('#no_of_pcs_hidden').val(res.no_of_pcs);
                        closeModal();
                    }
                },
                error: function (xhr) {
                    alert("Error saving custom ratio. Please try again.");
                    console.error(xhr.responseText);
                }
            });
        }

        // Cascaded Cutting Master Loading (Exactly like index-order-set)
        function warehouseChange(warehouse_id) {
            let cuttingSelect = $('#master_cutting_id');
            cuttingSelect.empty().append('<option value="">Select Cutting Master</option>');

            let warehouse = warehouses.find(w => w.id == warehouse_id);

            if (warehouse && warehouse.cutting_units) {
                warehouse.cutting_units.forEach(unit => {
                    cuttingSelect.append(
                        `<option value="${unit.id}">${unit.name}</option>`
                    );
                });
            }

            cuttingSelect.trigger('change.select2');

            // Fetch fabrics based on warehouse
            let fabricSelect = $('#fabric_id');
            fabricSelect.empty().append('<option value="">Loading Fabrics...</option>');
            fabricSelect.trigger('change.select2');
            
            $.ajax({
                url: "{{ route('admin.product_order.getFabricsByWarehouse') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    warehouse_id: warehouse_id
                },
                success: function (res) {
                    fabricSelect.empty().append('<option value="">Select Fabric</option>');
                    res.forEach(fabric => {
                        let remaining = fabric.receipt_details_sum_remaining_quantity ? parseFloat(fabric.receipt_details_sum_remaining_quantity).toFixed(2) : '0.00';
                        fabricSelect.append(
                            `<option value="${fabric.id}">${fabric.name} (${remaining} meter)</option>`
                        );
                    });
                    fabricSelect.trigger('change.select2');
                },
                error: function () {
                    fabricSelect.empty().append('<option value="">Select Fabric</option>');
                    fabricSelect.trigger('change.select2');
                }
            });
        }

        function printingWarehouseChange() {
            let printingSelect = $('#printing_unit_id');
            printingSelect.empty();
            printingSelect.append('<option value="">Select Printing Unit</option>');

            printing_warehouses.forEach(warehouse => {
                if (warehouse.printing_units) {
                    warehouse.printing_units.forEach(unit => {
                        printingSelect.append(
                            `<option value="${unit.id}">${unit.name} (${warehouse.warehouse_name})</option>`
                        );
                    });
                }
            });

            printingSelect.trigger('change.select2');
        }

        function togglePrinting(val) {
            if (val === 'yes') {
                $('#printing_unit_group').show();
                $('#printing_unit_id').prop('required', true);
            } else {
                $('#printing_unit_group').hide();
                $('#printing_unit_id').prop('required', false).val('').trigger('change');
            }
        }
    </script>
    <!-- CUSTOM SIZE MODAL -->
    <div class="modal" id="sizeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update Ratio</h4>
                <button type="button" class="close" onclick="closeModal()"
                    style="border:none; background:none; font-size:24px;">&times;</button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Size Name:</strong>
                    <span id="size_name_display" class="text-primary"></span>
                </div>
                <div id="sizeListContainer"></div>
                <div class="mt-3 p-2 bg-light border rounded">
                    <strong>Proposed Ratio:</strong>
                    <code id="proposedRatioText" class="d-block mt-1">—</code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomRatio()">Save Changes</button>
            </div>
        </div>
    </div>

    <style>
        #sizeModal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #sizeModal .modal-content {
            background: #fff;
            width: 400px !important;
            padding: 0;
            border-radius: 8px;
            overflow: hidden;
        }

        #sizeModal .modal-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #sizeModal .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        #sizeModal .modal-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: right;
        }

        .size-editor-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 8px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }

        .counter-controls button {
            width: 30px;
            height: 30px;
            border: none;
            background: #007bff;
            color: #fff;
            border-radius: 4px;
            font-weight: bold;
        }

        .counter-controls span {
            display: inline-block;
            width: 30px;
            text-align: center;
            font-weight: bold;
        }
    </style>

@endsection