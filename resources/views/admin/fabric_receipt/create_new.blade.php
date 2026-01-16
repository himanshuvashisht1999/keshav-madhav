@extends('admin.layouts.app')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* layout: table on left, image preview on right */
    .flex-row {
        display:flex;
        gap:20px;
        align-items:flex-start;
    }
    .left-col {
        flex: 0 0 60%;
        max-width: 60%;
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
        display:flex;
        flex-direction:column;
        align-items:center;
    }
    .image-preview-box img {
        max-width:100%;
        max-height:420px;
        object-fit:contain;
        border-radius:4px;
    }
    .ocr-progress {
        margin-top:8px;
    }
    .parse-note {
        font-size: 13px;
        color:#666;
        margin-top:6px;
    }
    .btn-parse {
        margin-top:8px;
        width:100%;
    }

    @media(max-width: 992px){
        .flex-row { flex-direction: column; }
        .right-col { max-width:100%; width:100%; }
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
                <form id="fabric-receipt-form" action="{{ route('admin.fabric_receipt.store') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Add Fabric Shipment Receipt Of Warehouse</label>
                            <select name="master_fabric_warehouse_id" class="form-control select2" style="width: 100%;" required>
                                @foreach($cutting_units as $single_data)
                                <option value="{{$single_data->id}}" {{old('master_fabric_warehouse_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->cutting_master_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Vendor</label>
                            <select name="vendor_id" id="vendor-select" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select vendor --</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" {{old('vendor_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="datetime">Date & Time</label>
                            <!-- <input type="datetime-local" name="time" id="datetime" class="form-control" value="{{ now()->setTimezone('Asia/Kolkata')->format('Y-m-d\TH:i') }}"> -->

                            <input type="text"
                                id="datetime"
                                class="form-control"
                                placeholder="Select date & time">

                            <input type="hidden"
                                name="time"
                                id="datetime_hidden">
                        </div>

                        <div class="col-md-6">
                            <label for="received_by">Received By</label>
                            <input type="text" name="received_by" id="received_by" class="form-control" placeholder="Enter received by">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Amount</label>
                            <input type="number" step="0.01"
                                name="amount"
                                id="amount"
                                class="form-control"
                                placeholder="Enter amount">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>GST %</label>
                            <input type="number" step="0.01"
                                name="gst_percentage"
                                id="gst_percentage"
                                class="form-control"
                                placeholder="GST %">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>GST Amount</label>
                            <input type="number" step="0.01"
                                name="gst_amount"
                                id="gst_amount"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Total Amount</label>
                            <input type="number" step="0.01"
                                name="total_amount"
                                id="total_amount"
                                class="form-control"
                                readonly>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label for="total_roll">Total Roll</label>
                            <input type="number" name="total_roll" id="total_roll" class="form-control" placeholder="Enter total roll" value="1">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label for="total_meter">Total Meter</label>
                            <input type="number"
                                name="total_meter"
                                id="total_meter"
                                class="form-control"
                                placeholder="Enter total meter"
                                min="0"
                                step="0.01"
                                required>

                            <small class="text-danger d-none" id="total-meter-error">
                                Total Meter must be equal to sum of selected fabric meters
                            </small>
                        </div>

                    </div>

                    <div class="flex-row mt-3">
                        <div class="left-col">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Select Fabrics</h5>
                                <!-- <small class="text-muted">Choose fabric, enter roll & meter. You can parse from challan image.</small> -->
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-center align-middle mb-2" id="fabric-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width: 45%;">Fabric</th>
                                            <th style="width:20%;">Roll No</th>
                                            <th style="width:20%;">Meter</th>
                                            <th style="width:10%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fabric-body">
                                        {{-- initial single row --}}
                                        <tr data-row="1">
                                            <td>
                                                <select name="rolls[1][fabric_sku]" class="form-control select2 fabric-sku" data-row="1" required>
                                                    <option value="">Select Fabric</option>
                                                    @foreach ($fabrics as $single_data)
                                                        <option value="{{ $single_data->id }}">{{ $single_data->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" class="form-control" name="rolls[1][roll]" required></td>
                                            <td><input type="number" class="form-control meter" name="rolls[1][meter]" data-row="1" min="0" step="0.01" required></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Add More</button>
                                <!-- <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-filled">Clear Auto-Fill</button> -->
                            </div>
                            


                            <div class="mt-3 text-right">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </div>

                        <div class="right-col">
                            <label for="challan_photo">Challan Photo (upload)</label>
                            <div class="image-preview-box">
                                <input type="file" accept="image/*" id="challan-input" name="challan_photo" class="form-control mb-2" />
                                <!-- <img id="challan-preview" src="{{ asset('images/image-placeholder.png') }}" alt="Challan preview" onclick="openChallanModal(this.src)" style="cursor:pointer;" /> -->
                                <div class="zoom-container">
                                    <img id="challan-preview"
                                        src="{{ asset('images/image-placeholder.png') }}"
                                        alt="Challan preview" onclick="openChallanModal(this.src)">
                                </div>
                                <div class="ocr-progress" id="ocr-progress" style="display:none;">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <small id="ocr-status" style="margin-left:8px;">Scanning image...</small>
                                </div>

                                <!-- <button type="button" id="btn-parse" class="btn btn-parse btn-primary btn-sm">Parse Challan (OCR)</button>
                                <button type="button" id="btn-stop-ocr" class="btn btn-danger btn-sm" style="display:none; margin-top:6px;">Stop OCR</button>

                                <div class="parse-note">
                                    Tip: use a clear photo (good lighting). OCR may not be perfect — always review the filled rows.
                                </div>

                                <div class="mt-2" id="ocr-result" style="width:100%; overflow:auto; max-height:160px;"></div> -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<!-- Challan Image Preview Modal -->
<div class="modal fade" id="challanPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Challan Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body text-center" style="overflow:auto;">
                <img id="challan-modal-image"
                     src=""
                     style="max-width:100%; transform:scale(1); transition:transform .2s;">
            </div>

            <div class="modal-footer justify-content-center">
                <button class="btn btn-secondary btn-sm" onclick="zoomOutChallan()">−</button>
                <button class="btn btn-secondary btn-sm" onclick="resetZoomChallan()">Reset</button>
                <button class="btn btn-secondary btn-sm" onclick="zoomInChallan()">+</button>
            </div>
        </div>
    </div>
</div>

<!-- Combined script: vendor-change, add/remove rows, select2 init, challan preview + OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.1.3/dist/tesseract.min.js"></script>
<script>
$(document).ready(function() {

    $(document).on('submit', '#fabric-receipt-form', function (e) {

        let totalMeter = parseFloat($('#total_meter').val()) || 0;
        let fabricMeterSum = 0;

        $('.meter').each(function () {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) {
                fabricMeterSum += val;
            }
        });

        // 2 decimal safe comparison
        totalMeter = Number(totalMeter.toFixed(2));
        fabricMeterSum = Number(fabricMeterSum.toFixed(2));

        if (totalMeter !== fabricMeterSum) {
            e.preventDefault();

            $('#total-meter-error').removeClass('d-none');
            $('#total_meter').addClass('is-invalid');

            alert(
                "Meter mismatch!\n\n" +
                "Total Meter: " + totalMeter + "\n" +
                "Selected Fabric Meter Sum: " + fabricMeterSum
            );

            return false;
        } else {
            $('#total-meter-error').addClass('d-none');
            $('#total_meter').removeClass('is-invalid');
        }
    });

    // ---------------------------
    // Initial fabrics list (server-provided for initially selected vendor)
    // ---------------------------
    var fabricsList = @json($fabrics->map(function($f){ return ['id'=>$f->id,'name'=>$f->name]; })) || [];
    var fabricOptionsHtml = buildOptionsHtml(fabricsList);

    // helper to build options HTML from fabrics array
    function buildOptionsHtml(list) {
        var html = '<option value="">Select Fabric</option>';
        (list || []).forEach(function(f){
            var id = f.id || '';
            var name = f.name || '';
            html += '<option value="' + id + '">' + name + '</option>';
        });
        return html;
    }

    // update all .fabric-sku selects with current fabricOptionsHtml
    function updateAllFabricSelects() {
        $('.fabric-sku').each(function(){
            var $sel = $(this);
            var prev = $sel.val();
            $sel.html(fabricOptionsHtml);
            // restore previous value if exists in new list
            if (prev && $sel.find('option[value="' + prev + '"]').length) {
                $sel.val(prev);
            } else {
                $sel.val('');
            }
            // update select2 if initialized
            if ($sel.hasClass('select2-hidden-accessible')) {
                try { $sel.trigger('change.select2'); } catch(e) {}
            }
            $sel.trigger('change');
        });
    }

    // ---------------------------
    // Vendor change -> fetch fabrics
    // ---------------------------
    $(document).on('change', "select[name='vendor_id'], #vendor-select", function() {
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
        }).done(function(data) {
            // expecting data = [{id:.., name:..}, ...]
            fabricsList = data || [];
            fabricOptionsHtml = buildOptionsHtml(fabricsList);
            updateAllFabricSelects();
        }).fail(function(xhr){
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
                    ${ prefill.fabricName && !prefill.fabricId ? `<div style="font-size:12px;color:#666;margin-top:4px;">${escapeHtml(prefill.fabricName)}</div>` : '' }
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
    $('#add-row').off('click').on('click', function(){
        addRow();
    });

    // Remove row (delegated)
    $(document).on('click', '.remove-row', function(){
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
    $('#clear-filled').on('click', function(){
        $('#fabric-body tr').each(function(){
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
        challanInput.addEventListener('change', function(e){
            const file = this.files && this.files[0];
            if (!file) {
                challanPreview.src = "{{ asset('images/image-placeholder.png') }}";
                return;
            }
            const reader = new FileReader();
            reader.onload = function(ev){
                challanPreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    // Parse challan (OCR) button
    if (btnParse) {
        btnParse.addEventListener('click', async function(){
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
                        ocrStatus.textContent = `Recognizing: ${(m.progress*100).toFixed(0)}%`;
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
                setTimeout(()=> { ocrProgress.style.display = 'none'; }, 800);
            }
        });
    }

    // Stop OCR
    if (btnStop) {
        btnStop.addEventListener('click', function(){
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
        const fabricNames = (fabricsList || []).map(f => ({ id: f.id, name: f.name, nameLower: (f.name||'').toLowerCase() }));

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
                    parsed.push({ fabricId: null, fabricName: line, roll: numbersIn(line)[0]||'', meter: numbersIn(line)[1]||'' });
                }
            }
        });

        if (parsed.length === 0) {
            addRow({ fabricId: null, roll:'', meter:'' });
            ocrResultBox.insertAdjacentHTML('beforeend','<div><em>No structured items found — added an empty row for manual input.</em></div>');
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
                        <select name="rolls[${rowCount}][fabric_sku]" class="form-control select2 fabric-sku" data-row="${rowCount}" required>
                            ${opts}
                        </select>
                        ${ item.fabricId ? '' : `<div style="font-size:12px;color:#666;margin-top:4px;">${escapeHtml(item.fabricName)}</div>` }
                    </td>
                    <td><input type="number" name="rolls[${rowCount}][roll]" class="form-control" value="${ item.roll || '' }"></td>
                    <td><input type="number" name="rolls[${rowCount}][meter]" class="form-control meter" data-row="${rowCount}" value="${ item.meter || '' }" min="0" step="0.01"></td>
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

    $('#total_roll').on('change keyup', function () {
        let total = parseInt($(this).val()) || 1;
        console.log('----',total);
        if (total < 1) total = 1;

        let currentRows = $('#fabric-body tr').length;

        // ADD rows if needed
        if (total > currentRows) {
            let toAdd = total - currentRows;
            for (let i = 0; i < toAdd; i++) {
                addRow();
            }
        }

        // REMOVE extra rows if needed (from bottom)
        if (total < currentRows) {
            let toRemove = currentRows - total;
            for (let i = 0; i < toRemove; i++) {
                $('#fabric-body tr:last').remove();
                rowCount--;
            }
        }
    });

    $(document).on('change', '.fabric-sku', function () {
        let currentRow = parseInt($(this).closest('tr').data('row'));
        let selectedFabric = $(this).val();

        $('#fabric-body tr').each(function () {
            let row = parseInt($(this).data('row'));
            if (row > currentRow) {
                $(this).find('.fabric-sku')
                    .val(selectedFabric)
                    .trigger('change.select2');
            }
        });
    });

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
function calculateGST() {
    let amount = parseFloat($('#amount').val()) || 0;
    let gst = parseFloat($('#gst_percentage').val()) || 0;

    let gstAmount = (amount * gst) / 100;
    let total = amount + gstAmount;

    $('#gst_amount').val(gstAmount.toFixed(2));
    $('#total_amount').val(total.toFixed(2));
}

$(document).on('input', '#amount, #gst_percentage', calculateGST);
</script>
<script>
flatpickr("#datetime", {
    enableTime: true,
    dateFormat: "d M Y, h:i K",      // what user sees (example: 7 Jan 2025, 10:30 AM)
    altInput: false,
    time_24hr: false,
    defaultDate: "{{ now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i') }}",

    onChange: function(selectedDates, dateStr, instance) {
        // store ISO format for DB
        const formatted = flatpickr.formatDate(selectedDates[0], "Y-m-d H:i");
        document.getElementById("datetime_hidden").value = formatted;
    }
});

// set hidden field on load
document.getElementById("datetime_hidden").value =
    flatpickr.formatDate(new Date("{{ now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i') }}"), "Y-m-d H:i");
</script>

@endsection
