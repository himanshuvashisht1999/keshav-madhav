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
            <i class="fas fa-truck-loading text-primary"></i> Edit Fabric Shipment: 
            <span class="erp-badge-yellow ml-2 px-2 py-0 rounded">{{ $data->shipment_id }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.fabric_receipt.view', ['id' => $data->id]) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <form id="fabric-receipt-form" action="{{ route('admin.fabric_receipt.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="receipt_id" value="{{$data->id}}">

        <!-- Card 1: Shipment & Vendor Info -->
        <div class="erp-card mb-2">
            <div class="erp-card-header py-1">
                <span class="erp-card-title">
                    <i class="fas fa-file-invoice"></i> Shipment & Vendor Details
                </span>
            </div>
            <div class="erp-card-body p-2">
                <div class="row">
                    <div class="col-md-6 col-sm-6 mb-2">
                        <label class="erp-label">
                            <span>Warehouse <span class="required">*</span></span>
                            <span>
                                <a href="{{ route('admin.master.fabric_warehouse.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New"><i class="fas fa-plus"></i> New</a>
                                <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshWarehouseBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                            </span>
                        </label>
                        <select name="master_fabric_warehouse_id" id="warehouse-select" class="form-control select2 erp-input" style="width: 100%;" required>
                            @foreach($cutting_units as $single_data)
                                <option value="{{$single_data->id}}" {{$data->master_fabric_warehouse_id == $single_data->id ? 'selected' : ''}}>
                                    {{$single_data->cutting_master_name}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-sm-6 mb-2">
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
                                <option value="{{$single_data->id}}" {{$data->vendor_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">Receipt Date <span class="required">*</span></label>
                        <input type="date" name="time" value="{{$data->time}}" class="form-control erp-input" required>
                    </div>

                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">Received By</label>
                        <input type="text" name="received_by" id="received_by" class="form-control erp-input" placeholder="Receiver Name" value="{{$data->received_by}}">
                    </div>

                    <div class="col-md-4 col-sm-6 mb-2">
                        <label class="erp-label">Bill No <span class="required">*</span></label>
                        <input type="text" name="bill_no" id="bill_no" class="form-control erp-input" placeholder="Enter Bill No" value="{{$data->bill_no}}" required>
                        <span id="bill_no_error" class="text-danger font-weight-bold" style="display: none; font-size: 10px;">Bill Number already exists!</span>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="erp-label">Challan Slip</label>
                        <div class="d-flex" style="gap: 4px;">
                            <input type="file" id="challan-input" name="challan_photo" accept="image/*,.pdf" class="form-control erp-input p-1" style="font-size: 10px;">
                            <button type="button" id="view-challan" class="btn-erp btn-erp-outline" style="white-space: nowrap;"
                                    {{ $data->challan_photo ? '' : 'disabled' }}
                                    data-url="{{ $data->challan_photo }}"
                                    data-type="{{ str_contains($data->challan_photo, '.pdf') ? 'application/pdf' : 'image/jpeg' }}">
                                View Current
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="erp-label">Other Images (Upload More)</label>
                        <input type="file" name="other_images[]" accept="image/*" class="form-control erp-input p-1" multiple style="font-size: 10px;">
                    </div>

                    @if(isset($data->other_images) && $data->other_images->count() > 0)
                        <div class="col-12 mb-2">
                            <label class="erp-label">Existing Supporting Images</label>
                            <div class="d-flex flex-wrap" style="gap: 8px;">
                                @foreach($data->other_images as $otherImage)
                                    <div class="position-relative" style="width: 60px; height: 60px; border: 1px solid var(--erp-border); border-radius: 4px; overflow: hidden;">
                                        <a href="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" target="_blank">
                                            <img src="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" alt="Img" style="width: 100%; height: 100%; object-fit: cover;">
                                        </a>
                                        <a href="{{ route('admin.fabric_receipt.delete_other_image', $otherImage->id) }}" class="btn-erp btn-erp-danger position-absolute p-0 text-center" style="top: 1px; right: 1px; width: 16px; height: 16px; font-size: 9px; line-height: 16px;" onclick="return confirm('Delete this image?')">&times;</a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control erp-input" value="{{$data->amount}}" required>
                    </div>

                    <div class="col-md-1 col-sm-4 col-6 mb-2">
                        <label class="erp-label">GST % <span class="required">*</span></label>
                        <input type="number" step="0.01" name="gst_percentage" id="gst_percentage" class="form-control erp-input" value="{{$data->gst_percentage}}" required>
                    </div>

                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <label class="erp-label">GST Amount (Rs.)</label>
                        <input type="number" step="0.01" name="gst_amount" id="gst_amount" class="form-control erp-input" value="{{$data->gst_amount}}">
                    </div>

                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <label class="erp-label">Other Charges (Rs.)</label>
                        <input type="number" step="0.01" name="other_charges" id="other_charges" class="form-control erp-input" value="{{$data->other_charges ?? 0.00}}">
                    </div>

                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <label class="erp-label">Total Amount (Rs.)</label>
                        <div class="d-flex" style="gap: 4px;">
                            <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control erp-input font-weight-bold" value="{{$data->total_amount}}" readonly>
                            <label class="d-flex align-items-center mb-0 px-1 border rounded" style="font-size: 10px; cursor: pointer;">
                                <input type="checkbox" id="round_off" checked>
                                <span class="ml-1">Rnd</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-md-1 col-sm-4 col-6 mb-2">
                        <label class="erp-label">Total Rolls</label>
                        <input type="number" name="total_roll" id="total_roll" class="form-control erp-input font-weight-bold" value="{{$data->roll}}" min="1" max="100">
                    </div>

                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <label class="erp-label">Total Meter</label>
                        <input type="number" name="total_meter" id="total_meter" class="form-control erp-input font-weight-bold" value="{{$data->total_meter ?? 0.00}}" min="0" step="0.01">
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
                        <tr data-row="1">
                            <td>
                                <select name="rolls[1][fabric_sku]" class="form-control select2 fabric-sku erp-input" data-row="1" style="width: 100%;">
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
                <table class="erp-table table table-bordered mb-0" id="fabric-table-details">
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
                    <tbody id="roll-details-body">
                        @foreach($data->details as $index => $detail)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <b>{{ $detail->fabric->name ?? '-' }}</b>
                                <input type="hidden" name="roll_details[{{$index}}][detail_id]" value="{{$detail->id}}">
                                <input type="hidden" name="roll_details[{{$index}}][fabric_id]" value="{{$detail->fabric_id}}">
                            </td>
                            <td class="text-right">
                                <input type="number" name="roll_details[{{$index}}][price]" class="form-control erp-input text-right roll-price" value="{{$detail->price_per_meter}}" step="0.01" tabindex="-1" required>
                            </td>
                            <td>
                                <input type="text" name="roll_details[{{$index}}][roll_no]" class="form-control erp-input roll-no font-weight-bold" value="{{$detail->roll_number}}" placeholder="Roll No" tabindex="1" required>
                            </td>
                            <td class="text-right">
                                <input type="number" name="roll_details[{{$index}}][meter]" class="form-control erp-input text-right roll-meter font-weight-bold" value="{{$detail->meter}}" step="0.01" tabindex="1" required>
                            </td>
                            <td class="text-right">
                                <input type="number" class="form-control erp-input text-right roll-amount" value="{{ $detail->price_per_meter * $detail->meter }}" readonly tabindex="-1">
                            </td>
                            <td class="text-center align-middle">
                                @if($detail->meter == $detail->remaining_quantity)
                                    <button type="button" class="erp-action-btn erp-btn-delete remove-detail-row" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @else
                                    <span class="badge badge-warning" title="Consumed in production">Used</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Action -->
        <div class="d-flex justify-content-end align-items-center mt-2 mb-3">
            <a href="{{ route('admin.fabric_receipt.index') }}" class="btn-erp btn-erp-outline mr-2">
                Cancel
            </a>
            <button type="submit" id="submit-btn" class="btn-erp btn-erp-primary px-4 py-2" style="font-size: 13px;">
                <i class="fas fa-save mr-1"></i> Update Fabric Shipment
            </button>
        </div>
    </form>
</div>
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
                <iframe id="challan-frame"
                        style="width:100%; height:75vh; border:none; display:none;"></iframe>

                <!-- Image Preview -->
                <img id="challan-image"
                     src=""
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


<!-- Combined script -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.1.3/dist/tesseract.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ width: '100%' });

    // Show/Hide OCR sidebar based on Challan Slip
    $('#challan-input').on('change', function () {
        const file = this.files[0];
        if (file) {
            $('#ocr-sidebar').removeClass('d-none');
            const url = URL.createObjectURL(file);
            const type = file.type;
            $('#view-challan').prop('disabled', false).data({ url: url, type: type });
            
            // Preview in sidebar
            const reader = new FileReader();
            reader.onload = function(ev){
                $('#challan-preview').attr('src', ev.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    if ("{{$data->challan_photo}}" && !src_placeholder("{{$data->challan_photo}}")) {
         $('#ocr-sidebar').removeClass('d-none');
         $('#challan-preview').attr('src', "{{$data->challan_photo}}");
    }

    function src_placeholder(src) {
        return src.includes('image-placeholder.png');
    }

    $(document).on('blur', '.roll-no', function (e) {
        let rollNo = $(this).val().trim();
        let input = $(this);
        if (rollNo === '') return;
        
        let fabricId = $(this).closest('tr').find('input[name*="[fabric_id]"]').val();

        let duplicateFound = false;
        $('.roll-no').each(function () {
            if (this !== input[0] && $(this).val().trim() === rollNo) {
                let otherFabricId = $(this).closest('tr').find('input[name*="[fabric_id]"]').val();
                if (fabricId === otherFabricId) {
                    duplicateFound = true;
                    return false;
                }
            }
        });

        if (duplicateFound) {
            alert('Duplicate Roll / Lot No for the same fabric in current form');
            input.val('').focus();
            return;
        }

        $.ajax({
            url: "{{ route('admin.fabric_receipt.check-roll-no') }}",
            type: "POST",
            data: {
                roll_no: rollNo,
                fabric_id: fabricId,
                warehouse_id: $('#master_fabric_warehouse_id').val(),
                receipt_id: "{{$data->id}}",
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
        if (type.includes('pdf')) { $('#challan-frame').attr('src', url).show(); }
        else { $('#challan-image').attr('src', url).show(); }
        $('#challanPreviewModal').modal('show');
    });

    $(document).on('submit', '#fabric-receipt-form', function (e) {
        let totalMeter = parseFloat($('#total_meter').val()) || 0;
        let rollMeterSum = 0;
        $('#roll-details-body .roll-meter').each(function () {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) rollMeterSum += val;
        });
        totalMeter = Number(totalMeter.toFixed(2));
        rollMeterSum = Number(rollMeterSum.toFixed(2));

        if (totalMeter !== rollMeterSum) {
            e.preventDefault();
            $('#total-meter-error').removeClass('d-none');
            $('#total_meter').addClass('is-invalid');
            $('#submit-btn').prop('disabled', false);
            $('#fabric-receipt-form').data('submitted', false);
            alert("Meter mismatch!\n\nTotal Meter: " + totalMeter + "\nSelected Fabric Meter Sum: " + rollMeterSum);
            return false;
        }

        let totalRoll = parseInt($('#total_roll').val()) || 0;
        let addedRolls = $('#roll-details-body tr').length;
        if (totalRoll !== addedRolls) {
            e.preventDefault();
            $('#total_roll').addClass('is-invalid');
            $('#submit-btn').prop('disabled', false);
            $('#fabric-receipt-form').data('submitted', false);
            alert("Roll mismatch!\n\nTotal Roll: " + totalRoll + "\nAdded Rolls: " + addedRolls);
            return false;
        }

        let invalidRow = false;
        $('#roll-details-body tr').each(function() {
            let p = parseFloat($(this).find('.roll-price').val()) || 0;
            let m = parseFloat($(this).find('.roll-meter').val()) || 0;
            if (p <= 0 || m <= 0) {
                invalidRow = true;
                $(this).find('.roll-price, .roll-meter').addClass('is-invalid');
            }
        });
        // if (invalidRow) {
        //     e.preventDefault();
        //     $('#submit-btn').prop('disabled', false);
        //     $('#fabric-receipt-form').data('submitted', false);
        //     alert("Price and Meter must be greater than 0 for all rolls.");
        //     return false;
        // }

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
    });

    $(document).on('input change', '#fabric-receipt-form input, #fabric-receipt-form select', function () {
        $(this).removeClass('is-invalid');
        $('#submit-btn').prop('disabled', false);
        $('#fabric-receipt-form').data('submitted', false);
    });

    // Fabric Change Fetch logic (from create_new)
    var fabricsList = @json($fabrics->map(function($f){ return ['id'=>$f->id,'name'=>$f->name]; })) || [];
    var fabricOptionsHtml = buildOptionsHtml(fabricsList);

    function buildOptionsHtml(list) {
        var html = '<option value="">Select Fabric</option>';
        (list || []).forEach(function(f){ html += '<option value="' + (f.id || '') + '">' + (f.name || '') + '</option>'; });
        return html;
    }

    function updateAllFabricSelects() {
        $('.fabric-sku').each(function(){
            var $sel = $(this);
            var prev = $sel.val();
            $sel.html(fabricOptionsHtml);
            if (prev && $sel.find('option[value="' + prev + '"]').length) $sel.val(prev);
            else $sel.val('');
            if ($sel.hasClass('select2-hidden-accessible')) { try { $sel.trigger('change.select2'); } catch(e) {} }
            $sel.trigger('change');
        });
    }

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

    $(document).on('change', "#vendor-select", function() {
        var vendorId = $(this).val();
        if (!vendorId) { fabricsList = []; fabricOptionsHtml = buildOptionsHtml(fabricsList); updateAllFabricSelects(); return; }
        var urlTpl = "{{ route('admin.purchase_order.vendor_fabrics', ['vendor' => 'VENDOR_ID']) }}";
        var url = urlTpl.replace('VENDOR_ID', vendorId);
        $.ajax({ url: url, method: 'GET', dataType: 'json' }).done(function(data) {
            fabricsList = data || []; fabricOptionsHtml = buildOptionsHtml(fabricsList); updateAllFabricSelects();
        });
    });

    updateAllFabricSelects();

    // Add Row logic
    $(document).on('click', '#add-row', function () {
        appendCurrentRowToRollDetails();
        let $row = $('#fabric-body tr:first');
        $row.find('.fabric-sku').val('').trigger('change');
        $row.find('input[name$="[roll]"]').val('');
        $row.find('input[name$="[meter]"]').val('');
    });

    function appendCurrentRowToRollDetails() {
        let $row = $('#fabric-body tr:first');
        let fabricSelect = $row.find('.fabric-sku');
        let fabricId   = fabricSelect.val();
        let fabricName = fabricSelect.find('option:selected').text();
        let rolls = parseInt($row.find('input[name$="[roll]"]').val()) || 0;
        let price = parseFloat($row.find('input[name$="[meter]"]').val()) || 0;
        let maxAllowedRolls = parseInt($('#total_roll').val()) || 0;
        let existingRolls = $('#roll-details-body tr').length;

        if ((existingRolls + rolls) > maxAllowedRolls) {
            alert('You can add only ' + (maxAllowedRolls - existingRolls) + ' more rolls');
            return;
        }

        if (!fabricId || rolls <= 0) { alert('Please select fabric and enter rolls'); return; }
        
        let tbody = $('#roll-details-body');
        let rowsHtml = '';
        for (let i = 0; i < rolls; i++) {
            let rollIndex = existingRolls + i;
            rowsHtml += `
                <tr>
                    <td class="text-center align-middle">${rollIndex + 1}</td>
                    <td>
                        <span class="font-weight-bold">${fabricName}</span>
                        <input type="hidden" name="roll_details[${rollIndex}][fabric_id]" value="${fabricId}">
                    </td>
                    <td class="text-right"><input type="number" name="roll_details[${rollIndex}][price]" class="form-control erp-input text-right roll-price" value="${price}" step="0.01" tabindex="-1" required></td>
                    <td><input type="text" name="roll_details[${rollIndex}][roll_no]" class="form-control erp-input roll-no font-weight-bold" placeholder="Roll No" tabindex="1" required></td>
                    <td class="text-right"><input type="number" name="roll_details[${rollIndex}][meter]" class="form-control erp-input text-right roll-meter font-weight-bold" step="0.01" tabindex="1" required></td>
                    <td class="text-right"><input type="number" class="form-control erp-input text-right roll-amount" readonly tabindex="-1"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="erp-action-btn erp-btn-delete remove-detail-row" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        }
        tbody.append(rowsHtml);
        calculateRollAmounts();
    }

    $(document).on('click', '.remove-detail-row', function() {
        $(this).closest('tr').remove();
        calculateRollAmounts();
        updateRowNumbers();
    });

    function updateRowNumbers() {
        $('#roll-details-body tr').each(function(idx) {
            $(this).find('td:first').text(idx + 1);
        });
    }

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
    }

    function calculateTotalRolls() { $('#total_roll').val($('#roll-details-body tr').length); }
    function calculateTotalMeters() {
        let tm = 0;
        $('#roll-details-body .roll-meter').each(function() { tm += (parseFloat($(this).val()) || 0); });
        $('#total_meter').val(tm.toFixed(2));
    }

    $(document).on('keyup change', '.roll-price, .roll-meter', calculateRollAmounts);

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

    

    // OCR Logic
    $('#btn-parse').on('click', async function(){
        const file = document.getElementById('challan-input').files[0] || await fileFromUrl($('#challan-preview').attr('src'));
        if (!file) { alert('No challan image.'); return; }
        $('#ocr-progress').show();
        let worker = Tesseract.createWorker({ logger: m => $('#ocr-status').text(m.status + (m.progress ? ': ' + (m.progress*100).toFixed(0)+'%' : '')) });
        try {
            await worker.load(); await worker.loadLanguage('eng'); await worker.initialize('eng');
            const result = await worker.recognize(file);
            $('#ocr-result').html('<pre>' + result.data.text + '</pre>');
            // parse logic would go here, same as create_new
        } finally { await worker.terminate(); $('#ocr-progress').hide(); }
    });

    async function fileFromUrl(url) {
        if (!url || url.includes('placeholder')) return null;
        const res = await fetch(url);
        const blob = await res.blob();
        return new File([blob], 'challan.jpg', { type: blob.type });
    }

    function calculateGST() {
        let amount = parseFloat($('#amount').val()) || 0;
        let gstPercent = parseFloat($('#gst_percentage').val()) || 0;
        let gstAmt = (amount * gstPercent) / 100;
        $('#gst_amount').val(gstAmt.toFixed(2));
        calculateTotal();
    }

    // Calculate totals on load
    calculateRollAmounts();
    calculateTotalMeters();
    calculateTotalRolls();
    calculateTotal();
});

let challanZoom = 1;
function zoomInChallan() { challanZoom += 0.2; applyChallanZoom(); }
function zoomOutChallan() { if (challanZoom > 0.4) { challanZoom -= 0.2; applyChallanZoom(); } }
function resetZoomChallan() { challanZoom = 1; applyChallanZoom(); }
function applyChallanZoom() { document.getElementById('challan-image').style.transform = `scale(${challanZoom})`; }

</script>

<script>
$(document).on('change', '#bill_no', function () {
    let bill_no = $(this).val();
    let receipt_id = "{{ $data->id }}";
    let bill_no_error = $('#bill_no_error');
    let submit_btn = $('button[type="submit"]');

    if (bill_no) {
        $.ajax({
            url: "{{ route('admin.fabric_receipt.check-bill-no') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                bill_no: bill_no,
                receipt_id: receipt_id
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
