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
            <h1 class="text-center">Edit Fabric Shipment Receipt</h1>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default p-3">
                <form id="fabric-receipt-form" action="{{ route('admin.fabric_receipt.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="receipt_id" value="{{$data->id}}">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-flex justify-content-between align-items-center mb-1">
                                <span>Add Fabric Shipment Receipt Of Warehouse</span>
                                <span class="action-links">
                                    <a href="{{ route('admin.master.fabric_warehouse.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="text-info" id="refreshWarehouseBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="master_fabric_warehouse_id" id="warehouse-select" class="form-control select2" style="width: 100%;" required>
                                @foreach($cutting_units as $single_data)
                                <option value="{{$single_data->id}}" {{$data->master_fabric_warehouse_id == $single_data->id ? 'selected' : ''}}>{{$single_data->cutting_master_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="d-flex justify-content-between align-items-center mb-1">
                                <span>Vendor</span>
                                <span class="action-links">
                                    <a href="{{ route('admin.master.vendor.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="text-info" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="vendor_id" id="vendor-select" class="form-control select2" style="width: 100%;" required>
                                <option value="">-- Select vendor --</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" {{$data->vendor_id == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="datetime">Date</label>
                            <input type="date" name="time" id="" value="{{$data->time}}" class="form-control">
                            
                        </div>

                        <div class="col-md-4">
                            <label for="received_by">Received By</label>
                            <input type="text" name="received_by" id="received_by" class="form-control" placeholder="Enter received by" value="{{$data->received_by}}">
                        </div>

                        <div class="col-md-4">
                            <label for="bill_no">Bill No</label>
                            <input type="text" name="bill_no" id="bill_no" class="form-control" placeholder="Enter bill number" value="{{$data->bill_no}}">
                            <span id="bill_no_error" class="text-danger" style="display: none;">Bill Number already exists!</span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label>Challan Slip</label>

                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file"
                                        id="challan-input"
                                        name="challan_photo"
                                        accept="image/*,.pdf"
                                        class="custom-file-input">

                                    <label class="custom-file-label" for="challan-input">
                                        Choose file
                                    </label>
                                </div>

                                <div class=" ml-3">
                                    <button type="button"
                                            id="view-challan"
                                            class="btn btn-info btn-lm px-3"
                                            {{ $data->challan_photo ? '' : 'disabled' }}
                                            data-url="{{ $data->challan_photo }}"
                                            data-type="{{ str_contains($data->challan_photo, '.pdf') ? 'application/pdf' : 'image/jpeg' }}">
                                        View
                                    </button>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-1">
                                Allowed: JPG, PNG, PDF
                            </small>
                        </div>

                        <div class="col-md-6 mt-2">
                            <label>Other Images</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="other_images[]" accept="image/*" class="custom-file-input" multiple>
                                    <label class="custom-file-label">Choose files</label>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">
                                You can select multiple images to upload.
                            </small>

                            @if(isset($data->other_images) && $data->other_images->count() > 0)
                                <div class="mt-3">
                                    <label>Existing Other Images</label>
                                    <div class="d-flex flex-wrap" style="gap: 10px;">
                                        @foreach($data->other_images as $otherImage)
                                            <div class="position-relative" style="width: 100px; height: 100px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                                                <a href="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" target="_blank">
                                                    <img src="{{ asset('assets/receipts/other-images/' . $otherImage->image) }}" alt="Other Image" style="width: 100%; height: 100%; object-fit: cover;">
                                                </a>
                                                <a href="{{ route('admin.fabric_receipt.delete_other_image', $otherImage->id) }}" class="btn btn-sm btn-danger position-absolute" style="top: 2px; right: 2px; padding: 2px 5px; font-size: 10px;" onclick="return confirm('Are you sure you want to delete this image?')"><i class="fas fa-times"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                    <div class="row mb-3">
                        
                        <div class="col-md-3 mt-2">
                            <label>Amount</label>
                            <input type="number" step="0.01"
                                name="amount"
                                id="amount"
                                class="form-control"
                                placeholder="Enter amount" 
                                value="{{$data->amount}}"
                                required>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>GST %</label>
                            <input type="number" step="0.01"
                                name="gst_percentage"
                                id="gst_percentage"
                                class="form-control"
                                placeholder="GST %" 
                                value="{{$data->gst_percentage}}"
                                required>
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>GST Amount</label>
                            <input type="number" step="0.01"
                                name="gst_amount"
                                id="gst_amount"
                                class="form-control"
                                value="{{$data->gst_amount}}">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Other Charges</label>
                            <input type="number" step="0.01"
                                name="other_charges"
                                id="other_charges"
                                class="form-control"
                                value="{{$data->other_charges ?? 0.00}}"
                                placeholder="Other Charges">
                        </div>

                        <div class="col-md-3 mt-2">
                            <label>Total Amount</label>
                            <div class="input-group">
                                <input type="number" step="0.01"
                                    name="total_amount"
                                    id="total_amount"
                                    class="form-control"
                                    value="{{$data->total_amount}}"
                                    readonly>
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
                            <input type="number" name="total_roll" id="total_roll" class="form-control" placeholder="Enter total roll" value="{{$data->roll}}" min="1" max="100">
                        </div>
                        <div class="col-md-3 mt-2">
                            <label for="total_meter">Total Meter</label>
                            <input type="number"
                                name="total_meter"
                                id="total_meter"
                                class="form-control"
                                placeholder="Total Meter"
                                value="{{$data->total_meter ?? 0.00}}"
                                min="0"
                                step="0.01"
                                >
                            <small class="text-danger d-none" id="total-meter-error">
                                Total Meter must be equal to sum of selected fabric meters
                            </small>
                        </div>

                    </div>

                    <!-- Layout: Left (Table Add row), Right (OCR) -->
                    <div class="flex-row">
                        <div class="left-col">
                             <div class="flex-row flex-column">
    
                                    <!-- Header -->
                                    <div class="mb-2 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Select Fabrics</h5>
                                        <span class="action-links">
                                            <a href="{{ route('admin.master.fabric.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                            <a href="javascript:void(0)" class="text-info refreshFabricBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                    </div>

                                    <!-- Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-center align-middle mb-0" id="fabric-table">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="min-width:40%;">Fabric</th>
                                                    <th style="width:15%;">Rolls</th>
                                                    <th style="width:25%;">Price (per meter)</th>
                                                    <th style="width:10%;">Action</th>
                                                </tr>
                                            </thead>

                                            <tbody id="fabric-body">
                                                <tr data-row="1">
                                                    <td>
                                                        <select name="rolls[1][fabric_sku]"
                                                                class="form-control select2 fabric-sku"
                                                                data-row="1">
                                                            <option value="">Select Fabric</option>
                                                            @foreach ($fabrics as $single_data)
                                                                <option value="{{ $single_data->id }}">
                                                                    {{ $single_data->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            class="form-control"
                                                            name="rolls[1][roll]"
                                                            min="1"
                                                            placeholder="Rolls"
                                                            >
                                                    </td>

                                                    <td>
                                                        <input type="number"
                                                            class="form-control meter"
                                                            name="rolls[1][meter]"
                                                            data-row="1"
                                                            min="0"
                                                            step="0.01"
                                                            placeholder="Price per meter"
                                                            >
                                                    </td>

                                                    <td class="text-center">
                                                        <button type="button"
                                                                class="btn btn-primary btn-sm px-3"
                                                                id="add-row">
                                                            + Add
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        </div>

                        <div class="right-col d-none" id="ocr-sidebar11">
                             <div class="image-preview-box mt-4">
                                <div class="zoom-container">
                                    <img id="challan-preview" src="{{ asset('images/image-placeholder.png') }}" alt="Challan Slip">
                                </div>

                                <div id="ocr-progress" class="ocr-progress w-100 align-items-center" style="display:none; gap:10px;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <small id="ocr-status" class="text-primary font-weight-bold">Processing...</small>
                                    <button type="button" class="btn btn-xs btn-danger ml-auto" id="btn-stop-ocr">Stop</button>
                                </div>

                                <button type="button" class="btn btn-primary btn-sm btn-parse" id="btn-parse">
                                    <i class="fas fa-magic mr-1"></i> Auto-parse from Image (OCR)
                                </button>
                                <p class="parse-note">Click to extract fabric names, rolls & meters</p>

                                <div id="ocr-result" class="mt-2 w-100" style="max-height:100px; overflow-y:auto; border-top:1px solid #eee; padding-top:5px;"></div>
                            </div>
                        </div>
                    </div>
                

                    <div class="flex-row mt-3">
                        <div class="left-col">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Roll Details</h5>
                            </div>

                            <div class="table-responsive" id="roll_details">
                                <table class="table table-bordered table-striped text-center align-middle mb-2" id="fabric-table-details">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 5%;">#</th>
                                            <th style="min-width: 45%;">Fabric</th>
                                            <th style="width:20%;">Price (per meter)</th>
                                            <th style="width:20%;">Roll No</th>
                                            <th style="width:20%;">Meter</th>
                                            <th style="width:20%;">Amount</th>
                                            <th style="width:5%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="roll-details-body">
                                        @foreach($data->details as $index => $detail)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <span class="font-weight-bold">{{ $detail->fabric->name ?? '-' }}</span>
                                                <input type="hidden" name="roll_details[{{$index}}][detail_id]" value="{{$detail->id}}">
                                                <input type="hidden" name="roll_details[{{$index}}][fabric_id]" value="{{$detail->fabric_id}}">
                                            </td>
                                            <td>
                                                <input type="number" name="roll_details[{{$index}}][price]" class="form-control roll-price" value="{{$detail->price_per_meter}}" step="0.01" tabindex="-1" required >
                                            </td>
                                            <td>
                                                <input type="text" name="roll_details[{{$index}}][roll_no]" class="form-control roll-no" value="{{$detail->roll_number}}" placeholder="Roll No" tabindex="1" required>
                                            </td>
                                            <td>
                                                <input type="number" name="roll_details[{{$index}}][meter]" class="form-control roll-meter" value="{{$detail->meter}}" step="0.01" tabindex="1" required >
                                            </td>
                                            <td>
                                                <input type="number" class="form-control roll-amount" value="{{ $detail->price_per_meter * $detail->meter }}" readonly tabindex="-1">
                                            </td>
                                            <td>
                                                @if($detail->meter == $detail->remaining_quantity)
                                                    <button type="button" class="btn btn-danger btn-sm remove-detail-row"><i class="fas fa-trash"></i></button>
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
                    </div>
                    
                    <div class="mt-3 text-right">
                        <button type="submit" id="submit-btn" class="btn btn-success">Update Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
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
                    <td>${rollIndex + 1}</td>
                    <td>
                        <span class="font-weight-bold">${fabricName}</span>
                        <input type="hidden" name="roll_details[${rollIndex}][fabric_id]" value="${fabricId}">
                    </td>
                    <td><input type="number" name="roll_details[${rollIndex}][price]" class="form-control roll-price" value="${price}" step="0.01" tabindex="-1" required></td>
                    <td><input type="text" name="roll_details[${rollIndex}][roll_no]" class="form-control roll-no" placeholder="Roll No" tabindex="1" required></td>
                    <td><input type="number" name="roll_details[${rollIndex}][meter]" class="form-control roll-meter" step="0.01" tabindex="1" required ></td>
                    <td><input type="number" class="form-control roll-amount" readonly tabindex="-1"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-detail-row"><i class="fas fa-trash"></i></button></td>
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
