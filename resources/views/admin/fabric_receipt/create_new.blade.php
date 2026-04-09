@extends('admin.layouts.app')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* layout: table on left, image preview on right */
        .flex-row {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .left-col {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .right-col {
            flex: 0 0 40%;
            max-width: 40%;
        }

        .image-preview-box {
            border: 1px dashed #dcdcdc;
            border-radius: 6px;
            padding: 10px;
            background: #fff;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .image-preview-box img {
            max-width: 100%;
            max-height: 420px;
            object-fit: contain;
            border-radius: 4px;
        }

        .ocr-progress {
            margin-top: 8px;
        }

        .parse-note {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }

        .btn-parse {
            margin-top: 8px;
            width: 100%;
        }

        @media(max-width: 992px) {
            .flex-row {
                flex-direction: column;
            }

            .right-col {
                max-width: 100%;
                width: 100%;
            }
        }

        .zoom-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            max-height: 420px;
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

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <h1 class="text-center">Add Fabric Shipment Receipt</h1>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default p-3">
                    <form id="fabric-receipt-form" action="{{ route('admin.fabric_receipt.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label>Purchase Order (Optional)</label>
                                <select name="purchase_order_id" id="po-select" class="form-control select2"
                                    style="width: 100%;">
                                    <option value="">-- No PO (Create New) --</option>
                                    @foreach($purchase_orders as $po)
                                        <option value="{{$po->id}}" data-vendor="{{$po->vendor_id}}">{{$po->sku}}
                                            {{$po->vendor ? '(' . $po->vendor->name . ')' : ''}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Warehouse</label>
                                <select name="master_fabric_warehouse_id" class="form-control select2" style="width: 100%;"
                                    required>
                                    @foreach($cutting_units as $single_data)
                                        <option value="{{$single_data->id}}"
                                            {{old('master_fabric_warehouse_id') == $single_data->id ? 'selected' : ''}}>
                                            {{$single_data->cutting_master_name}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Vendor</label>
                                <select name="vendor_id" id="vendor-select" class="form-control select2"
                                    style="width: 100%;" required>
                                    <option value="">-- Select vendor --</option>
                                    @foreach($vendors as $single_data)
                                        <option value="{{$single_data->id}}" {{old('vendor_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="datetime">Date</label>

                                <input type="date" name="time" id="" class="form-control" placeholder="Select date"
                                    value="{{ old('time') ?? date('Y-m-d') }}">

                            </div>

                            <div class="col-md-4">
                                <label for="received_by">Received By</label>
                                <input type="text" name="received_by" id="received_by" class="form-control"
                                    placeholder="Enter received by">
                            </div>

                            <div class="col-md-4">
                                <label for="bill_no">Bill No</label>
                                <input type="text" name="bill_no" id="bill_no" class="form-control"
                                    placeholder="Enter bill number">
                                <span id="bill_no_error" class="text-danger" style="display: none;">Bill Number already
                                    exists!</span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <label>Challan Slip</label>

                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" id="challan-input" name="challan_photo" accept="image/*,.pdf"
                                            class="custom-file-input">

                                        <label class="custom-file-label" for="challan-input">
                                            Choose file
                                        </label>
                                    </div>

                                    <div class=" ml-3">
                                        <button type="button" id="view-challan" class="btn btn-info btn-lm px-3" disabled>
                                            View
                                        </button>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-1">
                                    Allowed: JPG, PNG, PDF
                                </small>
                            </div>

                        </div>
                        <div class="row mb-3">

                            <div class="col-md-3 mt-2">
                                <label>Amount</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control"
                                    placeholder="Enter amount" required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>GST %</label>
                                <input type="number" step="0.01" name="gst_percentage" id="gst_percentage"
                                    class="form-control" placeholder="GST %" required>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>GST Amount</label>
                                <input type="number" step="0.01" name="gst_amount" id="gst_amount" class="form-control">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Other Charges</label>
                                <input type="number" step="0.01" name="other_charges" id="other_charges"
                                    class="form-control" placeholder="Other Charges">
                            </div>

                            <div class="col-md-3 mt-2">
                                <label>Total Amount</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" readonly>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="checkbox" id="round_off" checked> 
                                            <small class="ml-1">Round</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 mt-2">
                                <label for="total_roll">Total Roll</label>
                                <input type="number" name="total_roll" id="total_roll" class="form-control"
                                    placeholder="Enter total roll" value="1" min="1" max="100">
                            </div>
                            <div class="col-md-3 mt-2">
                                <label for="total_meter">Total Meter</label>
                                <input type="number" name="total_meter" id="total_meter" class="form-control"
                                    placeholder="Total Meter" min="0" step="0.01">
                                <small class="text-danger d-none" id="total-meter-error">
                                    Total Meter must be equal to sum of selected fabric meters
                                </small>
                            </div>

                        </div>
                        <div class="flex-row flex-column">

                            <!-- Header -->
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Select Fabrics</h5>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-center align-middle mb-0"
                                    id="fabric-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width:40%;">Fabric</th>
                                            <th style="width:15%;">Rolls</th>
                                            <th style="width:25%;">Price (per meter)</th>
                                            <th style="width:10%;">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="fabric-body">
                                        <tr data-row="1" id="fabric-row">
                                            <td>
                                                <select name="rolls[1][fabric_id]"
                                                    class="form-control select2 fabric-id-select" data-row="1">
                                                    <option value="">Select Fabric</option>
                                                    @foreach ($fabrics as $single_data)
                                                        <option value="{{ $single_data->id }}">
                                                            {{ $single_data->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            <td>
                                                <input type="number" class="form-control" name="rolls[1][roll]" min="1"
                                                    placeholder="Rolls">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control meter" name="rolls[1][meter]"
                                                    data-row="1" min="0" step="0.01" placeholder="Price per meter">
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-primary btn-sm px-3" id="add-row">
                                                    + Add
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>


                        <div class="flex-row mt-3">
                            <div class="left-col">
                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Roll Details</h5>
                                    <!-- <small class="text-muted">Choose fabric, enter roll & meter. You can parse from challan image.</small> -->
                                </div>

                                <div class="table-responsive" id="roll_details">
                                    <table class="table table-bordered table-striped text-center align-middle mb-2"
                                        id="fabric-table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 5%;">#</th>
                                                <th style="min-width: 45%;">Fabric</th>
                                                <th style="width:20%;">Price (per meter)</th>
                                                <th style="width:20%;">Roll No</th>
                                                <th style="width:20%;">Meter</th>
                                                <th style="width:20%;">Amount</th>
                                                <th style="width: 5%;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="roll-details-body">

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="submit" id="submit-btn" class="btn btn-success">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
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

                /* =========================
                1️⃣ FORM LEVEL DUPLICATE CHECK
                ========================== */
                let duplicateFound = false;

                $('.roll-no').each(function () {
                    if (this !== input[0] && $(this).val().trim() === rollNo) {
                        duplicateFound = true;
                        return false; // break loop
                    }
                });

                if (duplicateFound) {
                    alert('Duplicate Roll / Lot No in current form');
                    input.val('').focus();
                    return; // 🚫 DB check stop
                }

                $.ajax({
                    url: "{{ route('admin.fabric_receipt.check-roll-no') }}",
                    type: "POST",
                    data: {
                        roll_no: rollNo,
                        warehouse_id: $('#master_fabric_warehouse_id').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        if (res.exists) {
                            alert('Roll No already exists');
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
                                                <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
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
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
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
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-roll-detail">
                                                            <i class="fas fa-times"></i>
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
@endsection