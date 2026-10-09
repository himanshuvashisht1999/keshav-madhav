@extends('admin.layouts.app')
@section('content')

    <style>
        .image-preview-box {
            border: 1px dashed var(--erp-border);
            border-radius: 4px;
            padding: 8px;
            background: #fff;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .image-preview-box img {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 4px;
        }

        .zoom-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-height: 400px;
            border-radius: 4px;
            cursor: crosshair;
        }

        .zoom-container img {
            width: 100%;
            height: auto;
            transition: transform 0.1s ease-out;
            transform-origin: center center;
        }
    </style>

    <div class="content-wrapper erp-page p-2">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar">
            <div class="erp-header-title">
                <i class="fas fa-truck-loading text-primary"></i> Add Fabric Shipment Receipt
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <form id="fabric-receipt-form" action="{{ route('admin.fabric_receipt.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            <!-- Card 1: Shipment & Vendor Info -->
            <div class="erp-card mb-2">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-file-invoice"></i> Shipment & Vendor Details
                    </span>
                </div>
                <div class="erp-card-body p-2">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="erp-label">
                                <span>PO (Optional)</span>
                                <span>
                                    <a href="{{ route('admin.purchase_order.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshPoBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="purchase_order_id" id="po-select" class="form-control select2 erp-input" style="width: 100%;">
                                <option value="">-- No PO (Direct Receipt) --</option>
                                @foreach($purchase_orders as $po)
                                    <option value="{{$po->id}}" data-vendor="{{$po->vendor_id}}">{{$po->sku}}
                                        {{$po->vendor ? '(' . $po->vendor->name . ')' : ''}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="erp-label">
                                <span>Warehouse <span class="required">*</span></span>
                                <span>
                                    <a href="{{ route('admin.master.fabric_warehouse.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshWarehouseBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="master_fabric_warehouse_id" id="warehouse-select" class="form-control select2 erp-input" style="width: 100%;" required>
                                @foreach($cutting_units as $single_data)
                                    <option value="{{$single_data->id}}" {{old('master_fabric_warehouse_id') == $single_data->id ? 'selected' : ''}}>
                                        {{$single_data->cutting_master_name}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6 mb-2">
                            <label class="erp-label">
                                <span>Vendor <span class="required">*</span></span>
                                <span>
                                    <a href="{{ route('admin.master.vendor.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="vendor_id" id="vendor-select" class="form-control select2 erp-input" style="width: 100%;" required>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" {{old('vendor_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="erp-label">Receipt Date <span class="required">*</span></label>
                            <input type="date" name="time" class="form-control erp-input" value="{{ old('time') ?? date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="erp-label">Received By</label>
                            <input type="text" name="received_by" id="received_by" class="form-control erp-input" placeholder="Receiver Name">
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="erp-label">Bill No <span class="required">*</span></label>
                            <input type="text" name="bill_no" id="bill_no" class="form-control erp-input" placeholder="Enter Bill No" required>
                            <span id="bill_no_error" class="text-danger font-weight-bold" style="display: none; font-size: 10px;">Bill Number already exists!</span>
                        </div>

                        <div class="col-md-3 col-sm-6 mb-2">
                            <label class="erp-label">Challan Slip</label>
                            <div class="d-flex" style="gap: 4px;">
                                <input type="file" id="challan-input" name="challan_photo" accept="image/*,.pdf" class="form-control erp-input p-1" style="font-size: 10px;">
                                <button type="button" id="view-challan" class="btn-erp btn-erp-outline" style="white-space: nowrap;" disabled>
                                    View
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12 mb-1">
                            <label class="erp-label">Other Supporting Images (Optional)</label>
                            <input type="file" name="other_images[]" accept="image/*" class="form-control erp-input p-1" multiple style="font-size: 10px;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Financial & Valuation Totals -->
            <div class="erp-card mb-2">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-calculator"></i> Valuation & Meter Totals
                    </span>
                </div>
                <div class="erp-card-body p-2">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <label class="erp-label">Amount (Rs.) <span class="required">*</span></label>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control erp-input" placeholder="0.00" required>
                        </div>

                        <div class="col-md-1 col-sm-4 col-6 mb-2">
                            <label class="erp-label">GST % <span class="required">*</span></label>
                            <input type="number" step="0.01" name="gst_percentage" id="gst_percentage" class="form-control erp-input" placeholder="5" required>
                        </div>

                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <label class="erp-label">GST Amount (Rs.)</label>
                            <input type="number" step="0.01" name="gst_amount" id="gst_amount" class="form-control erp-input" placeholder="0.00">
                        </div>

                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <label class="erp-label">Other Charges (Rs.)</label>
                            <input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control erp-input" placeholder="0.00">
                        </div>

                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <label class="erp-label">Total Amount (Rs.)</label>
                            <div class="d-flex" style="gap: 4px;">
                                <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control erp-input font-weight-bold" readonly>
                                <label class="d-flex align-items-center mb-0 px-1 border rounded" style="font-size: 10px; cursor: pointer;">
                                    <input type="checkbox" id="round_off" checked>
                                    <span class="ml-1">Rnd</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-4 col-6 mb-2">
                            <label class="erp-label">Total Rolls</label>
                            <input type="number" name="total_roll" id="total_roll" class="form-control erp-input font-weight-bold" value="1" min="1" max="100">
                        </div>

                        <div class="col-md-2 col-sm-4 col-6 mb-2">
                            <label class="erp-label">Total Meter</label>
                            <input type="number" name="total_meter" id="total_meter" class="form-control erp-input font-weight-bold" placeholder="0.00" min="0" step="0.01">
                            <small class="text-danger font-weight-bold d-none" id="total-meter-error" style="font-size: 9.5px;">
                                Must equal sum of meters!
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Fabric Batches Input -->
            <div class="erp-card mb-2">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-boxes"></i> Select Fabric Batches
                    </span>
                    <span>
                        <a href="{{ route('admin.master.fabric.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New Fabric</a>
                        <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh refreshFabricBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                    </span>
                </div>
                <div class="table-responsive p-0">
                    <table class="erp-table table table-bordered mb-0" id="fabric-table">
                        <thead>
                            <tr>
                                <th style="min-width: 40%;">Fabric Item</th>
                                <th style="width: 15%;" class="text-center">Rolls Qty</th>
                                <th style="width: 25%;" class="text-right">Price per Meter (Rs.)</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="fabric-body">
                            <tr data-row="1" id="fabric-row">
                                <td>
                                    <select name="rolls[1][fabric_id]" class="form-control select2 fabric-id-select erp-input" data-row="1" style="width: 100%;">
                                        <option value="">-- Select Fabric --</option>
                                        @foreach ($fabrics as $single_data)
                                            <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" class="form-control erp-input text-center" name="rolls[1][roll]" min="1" placeholder="Rolls">
                                </td>
                                <td class="text-right">
                                    <input type="number" class="form-control erp-input text-right meter" name="rolls[1][meter]" data-row="1" min="0" step="0.01" placeholder="0.00">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-erp btn-erp-primary" id="add-row">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 4: Generated Roll Details Breakdown -->
            <div class="erp-card mb-2">
                <div class="erp-card-header py-1">
                    <span class="erp-card-title">
                        <i class="fas fa-scroll"></i> Generated Roll List Breakdown
                    </span>
                </div>
                <div class="table-responsive p-0" id="roll_details">
                    <table class="erp-table table table-bordered mb-0" id="roll-table">
                        <thead>
                            <tr>
                                <th style="width: 45px;" class="text-center">#</th>
                                <th>Fabric Item</th>
                                <th style="width: 130px;" class="text-right">Price / Mtr (Rs.)</th>
                                <th style="width: 140px;">Roll No</th>
                                <th style="width: 120px;" class="text-right">Meters</th>
                                <th style="width: 130px;" class="text-right">Amount (Rs.)</th>
                                <th style="width: 50px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="roll-details-body"></tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="d-flex justify-content-end align-items-center mt-2 mb-3">
                <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline mr-2">
                    Cancel
                </a>
                <button type="submit" id="submit-btn" class="btn-erp btn-erp-primary px-4 py-2" style="font-size: 13px;">
                    <i class="fas fa-save mr-1"></i> Save Fabric Shipment
                </button>
            </div>
        </form>
    </div>
    <!-- Challan Image Preview Modal -->
    <!-- Challan Preview Modal -->
    <div class="modal fade" id="challanPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title">Challan Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body text-center" style="overflow:auto;">

                    <!-- PDF Preview -->
                    <iframe id="challan-frame" style="width:100%; height:75vh; border:none; display:none;"></iframe>

                    <!-- Image Preview -->
                    <img id="challan-image" src=""
                        style="max-width:100%; transform:scale(1); transition:transform .2s; display:none;">
                </div>

                <!-- Footer -->
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="zoomOutChallan()">−</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="resetZoomChallan()">Reset</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="zoomInChallan()">+</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Combined script: vendor-change, add/remove rows, select2 init, challan preview + OCR -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.1.3/dist/tesseract.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#challan-input').on('change', function () {
                const file = this.files[0];
                if (!file) return;

                const url = URL.createObjectURL(file);
                const type = file.type;

                $('#view-challan').prop('disabled', false).data({
                    url: url,
                    type: type
                });
            });

            $(document).on('blur', '.roll-no', function (e) {
                if (e.type === 'keypress' && e.which !== 13) return;

                let rollNo = $(this).val().trim();
                let input = $(this);

                if (rollNo === '') return;

                let fabricId = $(this).closest('tr').find('input[name*="[fabric_id]"]').val();

                /* =========================
                1️⃣ FORM LEVEL DUPLICATE CHECK
                ========================== */
                let duplicateFound = false;

                $('.roll-no').each(function () {
                    if (this !== input[0] && $(this).val().trim() === rollNo) {
                        let otherFabricId = $(this).closest('tr').find('input[name*="[fabric_id]"]').val();
                        if (fabricId === otherFabricId) {
                            duplicateFound = true;
                            return false; // break loop
                        }
                    }
                });

                if (duplicateFound) {
                    alert('Duplicate Roll / Lot No for the same fabric in current form');
                    input.val('').focus();
                    return; // 🚫 DB check stop
                }

                $.ajax({
                    url: "{{ route('admin.fabric_receipt.check-roll-no') }}",
                    type: "POST",
                    data: {
                        roll_no: rollNo,
                        fabric_id: fabricId,
                        warehouse_id: $('#master_fabric_warehouse_id').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        if (res.exists) {
                            alert('Roll No already exists for this fabric');
                            input.val('').focus();
                        }
                    }
                });
            });

            $('#view-challan').on('click', function () {

                let url = $(this).data('url');
                let type = $(this).data('type');

                $('#challan-image, #challan-frame').hide();

                if (type.includes('pdf')) {
                    $('#challan-frame').attr('src', url).show();
                } else {
                    $('#challan-image').attr('src', url).show();
                }

                $('#challanPreviewModal').modal('show');
            });

            $(document).on('submit', '#fabric-receipt-form', function (e) {
                let totalMeter = parseFloat($('#total_meter').val()) || 0;
                let rollMeterSum = 0;

                $('#roll-details-body .roll-meter').each(function () {
                    let val = parseFloat($(this).val());
                    if (!isNaN(val)) {
                        rollMeterSum += val;
                    }
                });

                // 2 decimal safe comparison
                totalMeter = Number(totalMeter.toFixed(2));
                rollMeterSum = Number(rollMeterSum.toFixed(2));

                if (totalMeter !== rollMeterSum) {
                    e.preventDefault();

                    $('#total_meter').addClass('is-invalid');
                    $('#submit-btn').prop('disabled', false);
                    $('#fabric-receipt-form').data('submitted', false);
                    alert(
                        "Meter mismatch!\n\n" +
                        "Total Meter: " + totalMeter + "\n" +
                        "Selected Fabric Meter Sum: " + rollMeterSum
                    );
                    return false;
                } else {
                    $('#total-meter-error').addClass('d-none');
                    $('#total_meter').removeClass('is-invalid');
                }


                /* ======================
               ROLL VALIDATION
               ====================== */

                let totalRoll = parseInt($('#total_roll').val()) || 0;
                let addedRolls = $('#roll-details-body tr').length;

                if (totalRoll !== addedRolls) {
                    e.preventDefault();

                    $('#total_roll').addClass('is-invalid');
                    $('#submit-btn').prop('disabled', false);
                    $('#fabric-receipt-form').data('submitted', false);
                    alert(
                        "Roll mismatch!\n\n" +
                        "Total Roll: " + totalRoll + "\n" +
                        "Added Rolls: " + addedRolls
                    );
                    return false;
                } else {
                    $('#total_roll').removeClass('is-invalid');
                }

                let amountField = parseFloat($('#amount').val()) || 0;
                let rollAmountSum = 0;
                $('#roll-details-body .roll-amount').each(function () {
                    let val = parseFloat($(this).val());
                    if (!isNaN(val)) rollAmountSum += val;
                });
                amountField = Number(amountField.toFixed(2));
                rollAmountSum = Number(rollAmountSum.toFixed(2));

                if (amountField !== rollAmountSum) {
                    e.preventDefault();
                    $('#amount').addClass('is-invalid');
                    $('#submit-btn').prop('disabled', false);
                    $('#fabric-receipt-form').data('submitted', false);
                    alert("Amount mismatch!\n\nAmount: " + amountField + "\nRoll Amount Sum: " + rollAmountSum);
                    return false;
                }

                this.form.submit();
            });

            $(document).on('input change', '#fabric-receipt-form input, #fabric-receipt-form select', function () {
                $(this).removeClass('is-invalid');
                $('#submit-btn').prop('disabled', false);
                $('#fabric-receipt-form').data('submitted', false);
            });
            // ---------------------------
            // Initial fabrics list (server-provided for initially selected vendor)
            // ---------------------------
            var fabricsList = @json($fabrics->map(function ($f) {
                return ['id' => $f->id, 'name' => $f->name];
            })) || [];
            var fabricOptionsHtml = buildOptionsHtml(fabricsList);

            // helper to build options HTML from fabrics array
            function buildOptionsHtml(list) {
                var html = '<option value="">Select Fabric</option>';
                (list || []).forEach(function (f) {
                    var id = f.id || '';
                    var name = f.name || '';
                    html += '<option value="' + id + '">' + name + '</option>';
                });
                return html;
            }

            // update all .fabric-id-select selects with current fabricOptionsHtml
            function updateAllFabricSelects() {
                console.log("Updating selects with:", fabricOptionsHtml);
                $('.fabric-id-select').each(function () {
                    var $sel = $(this);
                    var prev = $sel.val();

                    // Destroy select2 if initialized to prevent bugs
                    if ($sel.hasClass('select2-hidden-accessible')) {
                        $sel.select2('destroy');
                    }

                    $sel.empty().html(fabricOptionsHtml);

                    // Re-initialize select2
                    $sel.select2({ width: '100%' });

                    // restore previous value if exists in new list
                    if (prev && $sel.find('option[value="' + prev + '"]').length) {
                        $sel.val(prev).trigger('change.select2');
                    } else {
                        $sel.val('').trigger('change.select2');
                    }
                });
            }

            // ---------------------------
            // Vendor change -> fetch fabrics
            // ---------------------------
            $(document).on('change', "#po-select", function () {
                var poId = $(this).val();
                var vendorSelect = $('#vendor-select');

                if (poId) {
                    var vendorId = $(this).find(':selected').data('vendor');
                    if (vendorId) {
                        vendorSelect.val(vendorId).trigger('change');
                        vendorSelect.prop('disabled', true);

                        // Add hidden field for vendor_id so it's submitted
                        if (!$('#hidden-vendor-id').length) {
                            $('<input>').attr({
                                type: 'hidden',
                                id: 'hidden-vendor-id',
                                name: 'vendor_id',
                                value: vendorId
                            }).appendTo('#fabric-receipt-form');
                        } else {
                            $('#hidden-vendor-id').val(vendorId);
                        }
                    }

                    // Fetch PO items (fabrics)
                    var url = "{{ route('admin.fabric_receipt.items', ['id' => 'PO_ID']) }}";
                    url = url.replace('PO_ID', poId);

                    $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json'
                    }).done(function (data) {
                        console.log("PO Fabrics Received:", data);
                        fabricsList = data || [];
                        fabricOptionsHtml = buildOptionsHtml(fabricsList);

                        // Small delay to ensure vendor update is processed
                        setTimeout(function () {
                            updateAllFabricSelects();
                        }, 200);

                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        console.error("Failed to fetch PO fabrics:", textStatus, errorThrown);
                    });

                } else {
                    vendorSelect.prop('disabled', false);
                    $('#hidden-vendor-id').remove();
                    vendorSelect.trigger('change');
                }
            });

            $(document).on('change', "select[name='vendor_id'], #vendor-select", function () {
                if ($('#po-select').val()) return; // If PO selected, don't trigger normal vendor change logic

                var vendorId = $(this).val();
                if (!vendorId) {
                    fabricsList = [];
                    fabricOptionsHtml = buildOptionsHtml(fabricsList);
                    updateAllFabricSelects();
                    return;
                }

                // Replace VENDOR_ID placeholder in the route string
                var urlTpl = "{{ route('admin.purchase_order.vendor_fabrics', ['vendor' => 'VENDOR_ID']) }}";
                var url = urlTpl.replace('VENDOR_ID', vendorId);

                $.ajax({
                    url: url,
                    method: 'GET',
                    dataType: 'json'
                }).done(function (data) {
                    // expecting data = [{id:.., name:..}, ...]
                    fabricsList = data || [];
                    fabricOptionsHtml = buildOptionsHtml(fabricsList);
                    updateAllFabricSelects();
                }).fail(function (xhr) {
                    console.error('Failed to load fabrics for vendor', vendorId, xhr);
                    // optional: show a toast / message
                    // fallback: keep existing list
                });
            });

            // Optionally trigger vendor change on load so it refreshes from server
            // Uncomment if you want fresh list on page load:
            // $("select[name='vendor_id'], #vendor-select").first().trigger('change');

            // ---------------------------
            // Add / Remove rows (use current fabricOptionsHtml)
            // ---------------------------
            var rowCount = $('#fabric-body tr').length || 1;

            function addRow(prefill) {
                rowCount++;
                prefill = prefill || {};
                var opts = fabricOptionsHtml || buildOptionsHtml(fabricsList);
                var newRow = `
                                            <tr data-row="${rowCount}">
                                                <td>
                                                    <select name="rolls[${rowCount}][fabric_sku]" class="form-control select2 fabric-sku" data-row="${rowCount}" required>
                                                        ${opts}
                                                    </select>
                                                    ${prefill.fabricName && !prefill.fabricId ? `<div style="font-size:12px;color:#666;margin-top:4px;">${escapeHtml(prefill.fabricName)}</div>` : ''}
                                                </td>
                                                <td><input type="number" name="rolls[${rowCount}][roll]" class="form-control" value="${prefill.roll || ''}" required></td>
                                                <td><input type="number" name="rolls[${rowCount}][meter]" class="form-control meter" data-row="${rowCount}" value="${prefill.meter || ''}" min="0" step="0.01" required></td>
                                                <td class="text-center align-middle"><button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                            </tr>
                                        `;
                $('#fabric-body').append(newRow);
                // init select2 for the newly added select
                $(`#fabric-body tr[data-row="${rowCount}"] .select2`).select2({ width: '100%' });
                // if prefill has fabricId, set it
                if (prefill.fabricId) {
                    $(`#fabric-body tr[data-row="${rowCount}"] select`).val(prefill.fabricId).trigger('change');
                }
            }

            // Add row button

            // Remove row (delegated)
            $(document).on('click', '.remove-row', function () {
                if ($('#fabric-body tr').length <= 1) {
                    // keep one row, just clear it
                    var $r = $('#fabric-body tr').first();
                    $r.find('select').val('').trigger('change');
                    $r.find('input').val('');
                    return;
                }
                $(this).closest('tr').remove();
            });

            // Clear auto-fill (keeps rows)
            $('#clear-filled').on('click', function () {
                $('#fabric-body tr').each(function () {
                    $(this).find('select').val('').trigger('change');
                    $(this).find('input').val('');
                });
            });

            // initialize select2 on existing selects
            $('.select2').select2({ width: '100%' });

            // ensure initial selects use server-provided fabrics
            updateAllFabricSelects();

            // Refresh Vendor
            $('#refreshVendorBtn').click(function () {
                let btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.purchase_order.all_vendors') }}", function (data) {
                    let select = $('select[name="vendor_id"]');
                    let currentVal = select.val();
                    select.empty();
                    select.append('<option value="">-- Select vendor --</option>');
                    data.forEach(function (v) {
                        select.append(`<option value="${v.id}">${v.name}</option>`);
                    });
                    if (currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

            // Refresh Fabric
            $(document).on('click', '.refreshFabricBtn', function () {
                let btn = $(this);
                let vendorId = $('select[name="vendor_id"]').val();
                let url = vendorId ? "{{ route('admin.purchase_order.vendor_fabrics', 'VID') }}".replace('VID', vendorId) : "{{ route('admin.purchase_order.all_fabrics') }}";
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON(url, function (data) {
                    fabricsList = data || [];
                    fabricOptionsHtml = buildOptionsHtml(fabricsList);
                    updateAllFabricSelects();
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

            // ---------------------------
            // Challan preview + OCR (Tesseract)
            // ---------------------------
            const challanInput = document.getElementById('challan-input');
            const challanPreview = document.getElementById('challan-preview');
            const btnParse = document.getElementById('btn-parse');
            const ocrProgress = document.getElementById('ocr-progress');
            const ocrStatus = document.getElementById('ocr-status');
            const ocrResultBox = document.getElementById('ocr-result');
            const btnStop = document.getElementById('btn-stop-ocr');

            let ocrWorker = null;
            let ocrCancelled = false;

            // preview image when selected
            if (challanInput) {
                challanInput.addEventListener('change', function (e) {
                    const file = this.files && this.files[0];
                    if (!file) {
                        challanPreview.src = "{{ asset('images/image-placeholder.png') }}";
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        challanPreview.src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Parse challan (OCR) button
            if (btnParse) {
                btnParse.addEventListener('click', async function () {
                    const file = challanInput.files && challanInput.files[0];
                    if (!file) {
                        alert('Please upload a challan image first.');
                        return;
                    }
                    // reset UI
                    ocrResultBox.innerHTML = '';
                    ocrProgress.style.display = 'flex';
                    ocrStatus.textContent = 'Initializing OCR...';
                    btnStop.style.display = 'inline-block';
                    ocrCancelled = false;

                    ocrWorker = Tesseract.createWorker({
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                ocrStatus.textContent = `Recognizing: ${(m.progress * 100).toFixed(0)}%`;
                            } else if (m.status) {
                                ocrStatus.textContent = m.status;
                            }
                        }
                    });

                    try {
                        await ocrWorker.load();
                        await ocrWorker.loadLanguage('eng');
                        await ocrWorker.initialize('eng');

                        const result = await ocrWorker.recognize(file, { tessedit_pageseg_mode: Tesseract.PSM.AUTO });
                        if (ocrCancelled) {
                            ocrStatus.textContent = 'OCR cancelled';
                            await ocrWorker.terminate();
                            btnStop.style.display = 'none';
                            ocrProgress.style.display = 'none';
                            return;
                        }

                        const text = result.data && result.data.text ? result.data.text : '';
                        ocrResultBox.innerHTML = `<pre style="white-space:pre-wrap;font-size:13px;">${escapeHtml(text)}</pre>`;
                        parseChallanText(text); // use current fabricsList for mapping
                        ocrStatus.textContent = 'OCR finished';
                        await ocrWorker.terminate();
                    } catch (err) {
                        console.error('OCR error', err);
                        ocrStatus.textContent = 'OCR error: ' + (err.message || err);
                        if (ocrWorker) { await ocrWorker.terminate(); }
                    } finally {
                        btnStop.style.display = 'none';
                        setTimeout(() => { ocrProgress.style.display = 'none'; }, 800);
                    }
                });
            }

            // Stop OCR
            if (btnStop) {
                btnStop.addEventListener('click', function () {
                    ocrCancelled = true;
                    if (ocrWorker) {
                        ocrWorker.terminate();
                    }
                    ocrStatus.textContent = 'Stopping...';
                    btnStop.style.display = 'none';
                });
            }

            // small helper: escape HTML
            function escapeHtml(unsafe) {
                if (!unsafe && unsafe !== 0) return '';
                return String(unsafe)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // parser (best-effort) - uses fabricsList to match names
            function parseChallanText(text) {
                if (!text || text.trim().length === 0) {
                    alert('No text found in image. Try a clearer photo or enter details manually.');
                    return;
                }

                const lines = text.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
                if (lines.length === 0) {
                    alert('No readable lines found on challan.');
                    return;
                }

                const parsed = [];
                const numbersIn = (s) => { return s.match(/(\d+(?:\.\d+)?)/g) || []; };
                // prepare lowercase fabric map
                const fabricNames = (fabricsList || []).map(f => ({ id: f.id, name: f.name, nameLower: (f.name || '').toLowerCase() }));

                lines.forEach(line => {
                    const lower = line.toLowerCase();
                    let matched = null; let longest = '';
                    fabricNames.forEach(f => {
                        if (f.nameLower && lower.indexOf(f.nameLower) !== -1) {
                            if (f.nameLower.length > longest.length) {
                                longest = f.nameLower; matched = f;
                            }
                        }
                    });

                    if (!matched) {
                        for (const f of fabricNames) {
                            const tokens = f.nameLower.split(/\s+/).filter(Boolean);
                            if (tokens.length && tokens.every(t => lower.indexOf(t) !== -1)) {
                                matched = f; break;
                            }
                        }
                    }

                    const nums = numbersIn(line);
                    let roll = '', meter = '';
                    const rollMatch = line.match(/roll(?:\s*(?:no|nr|#)?)?\s*[:\-]?\s*(\d+(?:\.\d+)?)/i);
                    const meterMatch = line.match(/m(?:eter|trs|trs\.|eters)?\s*[:\-]?\s*(\d+(?:\.\d+)?)/i) || line.match(/(\d+(?:\.\d+)?)\s*(m|meter|meters)\b/i);

                    if (rollMatch) roll = rollMatch[1];
                    if (meterMatch) meter = meterMatch[1];
                    if (!roll && !meter && nums.length) {
                        if (nums.length === 1) {
                            if (nums[0].indexOf('.') !== -1 || parseFloat(nums[0]) > 10) {
                                meter = nums[0];
                            } else { roll = nums[0]; }
                        } else if (nums.length >= 2) {
                            roll = nums[0]; meter = nums[1];
                        }
                    }

                    if (matched) {
                        parsed.push({ fabricId: matched.id, fabricName: matched.name, roll: roll, meter: meter });
                    } else {
                        if (numbersIn(line).length || line.length < 120) {
                            parsed.push({ fabricId: null, fabricName: line, roll: numbersIn(line)[0] || '', meter: numbersIn(line)[1] || '' });
                        }
                    }
                });

                if (parsed.length === 0) {
                    addRow({ fabricId: null, roll: '', meter: '' });
                    ocrResultBox.insertAdjacentHTML('beforeend', '<div><em>No structured items found — added an empty row for manual input.</em></div>');
                    return;
                }

                // Fill table with parsed results
                $('#fabric-body').empty();
                rowCount = 0;
                parsed.forEach(item => {
                    rowCount++;
                    var opts = buildOptionsHtml(fabricsList);
                    var rowHtml = `
                                                <tr data-row="${rowCount}">
                                                    <td>
                                                        <select name="rolls[${rowCount}][fabric_id]" class="form-control select2 fabric-id-select" data-row="${rowCount}" required>
                                                            ${opts}
                                                        </select>
                                                        ${item.fabricId ? '' : `<div style="font-size:12px;color:#666;margin-top:4px;">${escapeHtml(item.fabricName)}</div>`}
                                                    </td>
                                                    <td><input type="number" name="rolls[${rowCount}][roll]" class="form-control" value="${item.roll || ''}"></td>
                                                    <td><input type="number" name="rolls[${rowCount}][meter]" class="form-control meter" data-row="${rowCount}" value="${item.meter || ''}" min="0" step="0.01"></td>
                                                    <td class="text-center align-middle"><button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Delete"><i class="fas fa-trash-alt"></i></button></td>
                                                </tr>
                                            `;
                    $('#fabric-body').append(rowHtml);
                    if (item.fabricId) {
                        $(`#fabric-body tr[data-row="${rowCount}"] select`).val(item.fabricId).trigger('change');
                    }
                    $(`#fabric-body tr[data-row="${rowCount}"] .select2`).select2({ width: '100%' });
                });

                ocrResultBox.insertAdjacentHTML('beforeend', `<div><strong>Parsed ${parsed.length} items.</strong></div>`);
            }





            // $(document).on('input', '.meter', function () {
            //     let currentRow = parseInt($(this).closest('tr').data('row'));
            //     let meterVal = $(this).val();

            //     $('#fabric-body tr').each(function () {
            //         let row = parseInt($(this).data('row'));
            //         if (row > currentRow) {
            //             $(this).find('.meter').val(meterVal);
            //         }
            //     });
            // });

            // $(document).on('input', 'input[name^="rolls"][name$="[roll]"]', function () {
            //     let currentRow = parseInt($(this).closest('tr').data('row'));
            //     let startRoll = parseInt($(this).val());

            //     if (isNaN(startRoll)) return;

            //     let nextRoll = startRoll + 1;

            //     $('#fabric-body tr').each(function () {
            //         let row = parseInt($(this).data('row'));
            //         if (row > currentRow) {
            //             $(this).find('input[name^="rolls"][name$="[roll]"]').val(nextRoll);
            //             nextRoll++;
            //         }
            //     });
            // });

            /* ===============================
            ROLL DETAILS AUTO GENERATION
            ================================ */

            /* ===============================
            CALCULATE AMOUNT (price × meter)
            ================================ */

            function calculateRollAmounts() {

                let totalAmount = 0;
                let totalMeter = 0;

                $('#roll-details-body tr').each(function () {

                    let price = parseFloat($(this).find('.roll-price').val()) || 0;
                    let meter = parseFloat($(this).find('.roll-meter').val()) || 0;

                    let amount = price * meter;

                    $(this).find('.roll-amount').val(amount.toFixed(2));

                    totalAmount += amount;
                    totalMeter += meter;
                });

                calculateGST();
            }

            /* ===============================
            EVENTS
            ================================ */

            // $(document).on(
            //     'keyup change',
            //     '.fabric-sku, input[name$="[roll]"], input[name$="[meter]"]',
            //     rebuildRollDetailsTable
            // );

            $(document).on(
                'keyup change',
                '.roll-price, .roll-meter',
                calculateRollAmounts
            );

            // $(document).on('click', '#add-row, .remove-row', function () {
            //     setTimeout(rebuildRollDetailsTable, 100);
            // });

            // $(document).on('click', '#add-row', function () {

            //     // 1️⃣ Roll Details rebuild
            //     rebuildRollDetailsTable();

            //     // 2️⃣ Select Fabrics FIRST ROW RESET
            //     let $row = $('#fabric-body tr:first');

            //     $row.find('.fabric-sku').val('').trigger('change');
            //     $row.find('input[name$="[roll]"]').val('');
            //     $row.find('input[name$="[meter]"]').val('');

            // });
            $(document).on('click', '#add-row', function () {

                // 1️⃣ Current fabric ka roll details ADD karo
                appendCurrentRowToRollDetails();

                // 2️⃣ Select Fabric form RESET
                let $row = $('#fabric-body tr:first');

                $row.find('.fabric-sku').val('').trigger('change');
                $row.find('input[name$="[roll]"]').val('');
                $row.find('input[name$="[meter]"]').val('');
            });

            function appendCurrentRowToRollDetails() {

                let $row = $('#fabric-row');

                let fabricSelect = $row.find('.fabric-id-select');
                let fabricId = fabricSelect.val();
                let fabricName = fabricSelect.find('option:selected').text();

                let rolls = parseInt($row.find('input[name$="[roll]"]').val()) || 0;
                let price = parseFloat($row.find('input[name$="[meter]"]').val()) || 0;

                let maxAllowedRolls = parseInt($('#total_roll').val()) || 0;
                let existingRolls = $('#roll-details-body tr').length;

                if ((existingRolls + rolls) > maxAllowedRolls) {
                    alert(
                        'You can add only ' +
                        (maxAllowedRolls - existingRolls) +
                        ' more rolls'
                    );
                    return;
                }

                // if (existingRolls != maxAllowedRolls) {
                //     alert(
                //         'Total Roll is ' + maxAllowedRolls +
                //         ', but currently added ' + existingRolls + ' rolls.'
                //     );
                //     return;
                // }

                if (!fabricId || rolls <= 0) {
                    alert('Please select fabric and enter rolls');
                    return;
                }
                if (rolls > 100) {
                    alert('Maximum 100 rolls allowed at once');
                    return;
                }

                let tbody = $('#roll-details-body');
                let rowsHtml = '';

                for (let i = 0; i < rolls; i++) {

                    let rollIndex = existingRolls + i;

                    rowsHtml += `
                                                <tr>
                                                    <td>${rollIndex + 1}</td>
                                                    <td>
                                                        <span class="font-weight-bold">${fabricName}</span>
                                                        <input type="hidden"
                                                            name="roll_details[${rollIndex}][fabric_id]"
                                                            value="${fabricId}">
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            name="roll_details[${rollIndex}][price]"
                                                            class="form-control roll-price"
                                                            value="${price}"
                                                            step="0.01" tabindex="-1">
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            name="roll_details[${rollIndex}][roll_no]"
                                                            class="form-control roll-no"
                                                            placeholder="Roll No" tabindex="1">
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            name="roll_details[${rollIndex}][meter]"
                                                            class="form-control roll-meter"
                                                            step="0.01" tabindex="1">
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            class="form-control roll-amount"
                                                            readonly tabindex="-1">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="erp-action-btn erp-btn-delete remove-roll-detail" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            `;
                }
                tbody.append(rowsHtml);
                calculateRollAmounts();
            }

            $(document).on('click', '.remove-roll-detail', function () {
                $(this).closest('tr').remove();

                // Re-index sequence numbers AND input names to keep them sequential
                $('#roll-details-body tr').each(function (index) {
                    $(this).find('td:first').text(index + 1);

                    // Update names for backend consistency
                    $(this).find('input[name*="roll_details"]').each(function () {
                        let name = $(this).attr('name');
                        let newName = name.replace(/roll_details\[\d+\]/, 'roll_details[' + index + ']');
                        $(this).attr('name', newName);
                    });
                });

                calculateRollAmounts();
            });

        });


    </script>

    <script>
        let challanZoom = 1;

        function openChallanModal(src) {
            if (!src || src.includes('image-placeholder')) return;

            challanZoom = 1;
            const img = document.getElementById('challan-modal-image');
            img.src = src;
            img.style.transform = 'scale(1)';

            $('#challanPreviewModal').modal('show');
        }

        function zoomInChallan() {
            challanZoom += 0.2;
            applyChallanZoom();
        }

        function zoomOutChallan() {
            if (challanZoom > 0.4) {
                challanZoom -= 0.2;
                applyChallanZoom();
            }
        }

        function resetZoomChallan() {
            challanZoom = 1;
            applyChallanZoom();
        }

        function applyChallanZoom() {
            document.getElementById('challan-modal-image')
                .style.transform = `scale(${challanZoom})`;
        }


    </script>
    <script>
        const zoomContainer = document.querySelector('.zoom-container');
        const zoomImage = document.getElementById('challan-preview');

        if (zoomContainer && zoomImage) {

            zoomContainer.addEventListener('mousemove', function (e) {
                const rect = zoomContainer.getBoundingClientRect();

                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                zoomImage.style.transformOrigin = `${x}% ${y}%`;
                zoomImage.style.transform = 'scale(2.5)';
            });

            zoomContainer.addEventListener('mouseleave', function () {
                zoomImage.style.transformOrigin = 'center center';
                zoomImage.style.transform = 'scale(1)';
            });
        }
    </script>
    <script>
        function calculateTotal() {
            let amount = parseFloat($('#amount').val()) || 0;
            let gstAmt = parseFloat($('#gst_amount').val()) || 0;
            let otherCharges = parseFloat($('#other_charges').val()) || 0;
            let total = amount + gstAmt + otherCharges;

            if ($('#round_off').is(':checked')) {
                total = Math.round(total);
            }

            $('#total_amount').val(total.toFixed(2));
        }

        $(document).on('change', '#round_off', calculateTotal);

        $(document).on('input', '#amount, #gst_percentage', function() {
            let amount = parseFloat($('#amount').val()) || 0;
            let gstPercent = parseFloat($('#gst_percentage').val()) || 0;
            let gstAmt = (amount * gstPercent) / 100;
            $('#gst_amount').val(gstAmt.toFixed(2));
            calculateTotal();
        });

        $(document).on('input', '#gst_amount', function() {
            let amount = parseFloat($('#amount').val()) || 0;
            let gstAmt = parseFloat($('#gst_amount').val()) || 0;
            if (amount > 0) {
                let gstPercent = (gstAmt * 100) / amount;
                $('#gst_percentage').val(gstPercent.toFixed(2));
            }
            calculateTotal();
        });

        $(document).on('input', '#other_charges', calculateTotal);
    </script>



    <script>
        function calculateTotalRolls() {
            let totalRolls = $('#roll-details-body tr').length;
            $('#total_roll').val(totalRolls);
        }

        function calculateTotalMeters() {
            let totalMeter = 0;

            $('#roll-details-body .roll-meter').each(function () {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) {
                    totalMeter += val;
                }
            });

            $('#total_meter').val(totalMeter.toFixed(2));
        }
    </script>

    <script>
        $(document).on('change', '#bill_no', function () {
            let bill_no = $(this).val();
            let bill_no_error = $('#bill_no_error');
            let submit_btn = $('button[type="submit"]');

            if (bill_no) {
                $.ajax({
                    url: "{{ route('admin.fabric_receipt.check-bill-no') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        bill_no: bill_no
                    },
                    success: function (response) {
                        if (response.exists) {
                            bill_no_error.show();
                            submit_btn.attr('disabled', true);
                        } else {
                            bill_no_error.hide();
                            submit_btn.attr('disabled', false);
                        }
                    }
                });
            } else {
                bill_no_error.hide();
                submit_btn.attr('disabled', false);
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Refresh POs
            $('#refreshPoBtn').on('click', function() {
                var $btn = $(this);
                var $icon = $btn.find('i');
                $icon.addClass('fa-spin');
                $.getJSON("{{ route('admin.fabric_receipt.all_pending_pos') }}", function(data) {
                    var $select = $('#po-select');
                    var currentVal = $select.val();
                    $select.select2('destroy').empty();
                    $select.append('<option value="">-- No PO (Create New) --</option>');
                    $.each(data, function(key, value) {
                        var vendorName = value.vendor ? '(' + value.vendor.name + ')' : '';
                        $select.append('<option value="' + value.id + '" data-vendor="' + value.vendor_id + '">' + value.sku + ' ' + vendorName + '</option>');
                    });
                    $select.val(currentVal).select2({ theme: 'bootstrap4' });
                    $icon.removeClass('fa-spin');
                }).fail(function() {
                    $icon.removeClass('fa-spin');
                });
            });

            // Refresh Warehouses
            $('#refreshWarehouseBtn').on('click', function() {
                var $btn = $(this);
                var $icon = $btn.find('i');
                $icon.addClass('fa-spin');
                $.getJSON("{{ route('admin.fabric_receipt.all_warehouses') }}", function(data) {
                    var $select = $('#warehouse-select');
                    var currentVal = $select.val();
                    $select.select2('destroy').empty();
                    $.each(data, function(key, value) {
                        $select.append('<option value="' + value.id + '">' + value.cutting_master_name + '</option>');
                    });
                    $select.val(currentVal).select2({ theme: 'bootstrap4' });
                    $icon.removeClass('fa-spin');
                }).fail(function() {
                    $icon.removeClass('fa-spin');
                });
            });
        });
    </script>
@endsection