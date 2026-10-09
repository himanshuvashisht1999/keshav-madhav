@extends('admin.layouts.app')

@section('content')

<style>
    .rotate-btn {
        top: 12px;
        left: 12px;
        z-index: 10;
    }

    .slip-image {
        transition: transform 0.3s ease;
        cursor: zoom-in;
    }

    .image-wrapper {
        width: 100%;
        height: 75vh;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
        border-radius: 6px;
    }

    .sticky-wrapper {
        position: -webkit-sticky;
        position: sticky;
        top: 75px;
        z-index: 99;
    }

    .image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transform-origin: center center;
    }

    /* Movement Type Toggle Buttons */
    .btn-movement-toggle {
        font-weight: 600;
        font-size: 13px;
        padding: 8px 14px;
        border: 1px solid #c3e6cb;
        color: #4b5563;
        background: #fff;
        transition: all 0.2s ease;
    }
    .btn-movement-toggle:hover {
        background: #f1f8ed;
        color: #05421c;
    }
    .btn-movement-toggle.active {
        background: #05421c !important;
        color: #fff !important;
        border-color: #05421c !important;
        box-shadow: 0 2px 4px rgba(5,66,28,0.2);
    }
    .btn-movement-toggle.btn-damage.active {
        background: #b91c1c !important;
        color: #fff !important;
        border-color: #b91c1c !important;
        box-shadow: 0 2px 4px rgba(185,28,28,0.2);
    }
</style>

<div class="content-wrapper">
    <div class="erp-page p-2">

        <!-- HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title d-flex align-items-center">
                <i class="fas fa-file-signature mr-2 text-success"></i>
                <span>Production Slip Digitalization</span>
                <span class="badge ml-2 px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb; font-size:12px;">
                    Hand Slip
                </span>
                @if(!empty($slip_data))
                    <span class="badge badge-light border ml-2 text-xs font-weight-normal text-muted">
                        Slip #{{ $slip_data['id'] }}
                    </span>
                @endif
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Slips
                </a>
                @if(!empty($slip_data))
                    <a href="{{ route('admin.uploaded-slips.show', $slip_data['id']) }}" target="_blank" class="btn-erp btn-erp-outline ml-2">
                        <i class="fas fa-eye mr-1"></i> View Slip Details
                    </a>
                @endif
            </div>
        </div>

        @if(!empty($slip_data))
            <div id="slip_digitalization">
                <form method="POST" action="{{ route('admin.order_digitalization.store-hand-slip') }}" id="handSlipForm">
                    @csrf
                    <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">

                    <div class="row">

                        {{-- LEFT PANEL (Inputs) --}}
                        <div class="col-lg-7 col-md-12 mb-3">
                            <div class="erp-card bg-white p-3">

                                <!-- TOP SLIP META RIBBON -->
                                <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-day mr-2" style="color: #05421c;"></i>
                                        <span class="text-xs text-muted text-uppercase font-weight-bold mr-1">Slip Upload Date:</span>
                                        <strong class="text-dark font-weight-bold" style="font-size: 13px;">{{ getformatDateTime($slip_data['date_time']) }}</strong>
                                    </div>
                                    <span class="badge badge-light border text-xs text-muted">Hand Slip Mode</span>
                                </div>

                                {{-- LOT INPUT & BASIC DETAILS --}}
                                <div class="row">
                                    <div class="col-md-12 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                            Production Date & Time <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0"><i class="far fa-clock text-muted"></i></span>
                                            </div>
                                            <input type="text"
                                                name="production_datetime" 
                                                class="form-control datetime-picker erp-input border-left-0" 
                                                placeholder="Select date & time"
                                                value="{{ old('production_datetime', session('last_production_datetime', $slip_data['last_production_datetime'] ?? '')) }}"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                            Select Lot No. <span class="text-danger">*</span>
                                        </label>
                                        <select name="lot_no" id="lot_no_input" class="form-control select2" style="width: 100%;">
                                            <option value="">Select Lot</option>
                                            @if(isset($available_lots) && count($available_lots) > 0)
                                                @foreach($available_lots as $lot)
                                                    <option value="{{ $lot->lot_no }}">{{ $lot->lot_no }}</option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No available lots found for this stage</option>
                                            @endif
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Bill Number (Optional)</label>
                                        <input type="text" name="bill_number" class="form-control erp-input" placeholder="Enter Bill Number" value="{{ old('bill_number', $slip_data['bill_number'] ?? '') }}">
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">
                                            Total Pieces <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" min="1" name="total_pieces" id="slip_total_pieces" 
                                               class="form-control erp-input font-weight-bold" 
                                               style="color: #05421c; font-size: 15px;"
                                               placeholder="Enter Total Pieces" 
                                               value="{{ old('total_pieces', $slip_data['total_pieces'] ?? '') }}" 
                                               required>
                                    </div>

                                    @if(!empty($slip_data['total_digitized_pieces']) || !empty($slip_data['total_pieces']))
                                        <div class="col-md-12 mb-2">
                                            <div class="p-2 px-3 rounded border d-flex justify-content-between align-items-center flex-wrap" style="background-color: #fafdf8; border-color: #c3e6cb !important;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-layer-group mr-2" style="color: #05421c; font-size: 14px;"></i>
                                                    <span class="text-dark font-weight-bold" style="font-size: 12px;">Digitized so far:</span>
                                                    <span class="badge px-2 py-1 ml-2 font-weight-bold" style="background:#05421c; color:#fff; font-size: 12px;">
                                                        {{ $slip_data['total_digitized_pieces'] ?? 0 }} pcs
                                                    </span>
                                                </div>
                                                @if(!empty($slip_data['total_pieces']))
                                                    <div class="d-flex align-items-center mt-1 mt-sm-0">
                                                        <span class="text-muted font-weight-bold mr-1" style="font-size: 12px;">Target:</span>
                                                        <span class="badge badge-light border px-2 py-1 mr-2 font-weight-bold" style="font-size: 12px;">{{ $slip_data['total_pieces'] }} pcs</span>
                                                        @php $rem = (int)$slip_data['total_pieces'] - (int)($slip_data['total_digitized_pieces'] ?? 0); @endphp
                                                        @if($rem > 0)
                                                            <span class="badge px-2 py-1 font-weight-bold" style="background:#fff3cd; color:#856404; border:1px solid #ffeeba; font-size: 11px;">
                                                                <i class="fas fa-hourglass-half mr-1"></i> {{ $rem }} pcs remaining
                                                            </span>
                                                        @elseif($rem == 0)
                                                            <span class="badge px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb; font-size: 11px;">
                                                                <i class="fas fa-check mr-1"></i> Exact Match
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                                <i class="fas fa-exclamation-triangle mr-1"></i> {{ abs($rem) }} pcs excess
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- MOVEMENT TYPE & STAGE INFO --}}
                                <div class="row mt-2">
                                    <div class="col-md-12 mb-3">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1 d-block">Movement Type</label>
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label class="btn btn-movement-toggle w-50 {{ (!isset($slip_data['last_movement_type']) || $slip_data['last_movement_type'] == 1) ? 'active' : '' }}">
                                                <input type="radio" name="movement_type" value="1" id="type_regular" autocomplete="off" {{ (!isset($slip_data['last_movement_type']) || $slip_data['last_movement_type'] == 1) ? 'checked' : '' }}> 
                                                <i class="fas fa-arrow-right mr-1"></i> Regular Movement
                                            </label>
                                            <label class="btn btn-movement-toggle btn-damage w-50 {{ (isset($slip_data['last_movement_type']) && $slip_data['last_movement_type'] == 2) ? 'active' : '' }}">
                                                <input type="radio" name="movement_type" value="2" id="type_damage" autocomplete="off" {{ (isset($slip_data['last_movement_type']) && $slip_data['last_movement_type'] == 2) ? 'checked' : '' }}> 
                                                <i class="fas fa-undo mr-1"></i> Damage (Return)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">From Stage / Unit</label>
                                        <input type="text" class="form-control erp-input bg-light" value="{{ $slip_data['from_stage']['name'] }} ({{ $slip_data['from_stage']['master_stage_name'] }})" readonly>
                                        <input type="hidden" name="from_stage_id" value="{{ $slip_data['from_stage']['id'] }}"> 
                                        <input type="hidden" name="from_stage_id_ajax" value="{{ $slip_data['from_stage']['master_stage_id'] }}"> 
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">To Stage / Unit <span class="text-danger">*</span></label>
                                        <select name="to_stage_id" class="form-control select2" id="to_stage_id" required>
                                            @foreach($slip_data['unit_master_data'] as $unit)
                                                <option value="{{ $unit['id'] }}" {{ (isset($slip_data['last_to_stage_id']) && $slip_data['last_to_stage_id'] == $unit['id']) ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['master_stage_name'] }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- LOT DETAILS & INVENTORY (Dynamic) --}}
                                <div id="lotDetailsCard" class="erp-card p-3 mt-3 border d-none" style="background: #fafdf8; border-color: #c3e6cb !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-boxes mr-2" style="color: #05421c;"></i>
                                            <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Lot Inventory Details</h6>
                                            <button type="button" id="toggleBasicInfo" class="btn-erp btn-erp-outline btn-xs ml-3 py-1 px-2" style="font-size: 11px;">
                                                <i class="fas fa-eye mr-1"></i> Show Details
                                            </button>
                                        </div>

                                        <div class="d-flex align-items-center" style="gap: 10px;">
                                            <!-- TOTAL PIECES -->
                                            <input type="number"
                                                id="totalPieces"
                                                class="form-control form-control-sm text-center font-weight-bold"
                                                style="width: 130px; border: 1px solid #c3e6cb;"
                                                placeholder="Auto-Fill Pieces">

                                            <!-- SEND ALL -->
                                            <div class="custom-control custom-checkbox ml-2">
                                                <input class="custom-control-input" type="checkbox" id="sendAllQty">
                                                <label class="custom-control-label font-weight-bold text-xs" for="sendAllQty" style="color: #05421c; cursor: pointer;">
                                                    Send All
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="basicInfo" class="mb-3 p-2 rounded" style="display:none; background: #fff; border: 1px solid #c3e6cb;">
                                        <!-- Basic API info here -->
                                    </div>

                                    <div class="table-responsive bg-white rounded border">
                                        <table class="table erp-table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Size</th>
                                                    <th>Available Qty</th>
                                                    <th style="width: 150px;">Send Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody id="inventoryTableBody">
                                                <!-- Valid Sizes Loaded Here -->
                                            </tbody>
                                            <tfoot>
                                                <tr style="background: #edf7e4;">
                                                    <td colspan="2" class="text-right font-weight-bold" style="color: #05421c;">Total Moving Pieces:</td>
                                                    <td class="font-weight-bold" id="totalMovingQty" style="color: #05421c; font-size: 14px;">0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                {{-- ACTION BUTTONS --}}
                                <div class="mt-4 row">
                                    <input type="hidden" name="is_final" id="is_final_input" value="1">
                                    
                                    <div class="col-12 mb-3 text-center">
                                        <div class="custom-control custom-switch d-inline-block">
                                            <input type="checkbox" class="custom-control-input" id="sendWhatsapp" name="send_whatsapp" value="1">
                                            <label class="custom-control-label font-weight-bold" for="sendWhatsapp" style="color: #05421c; cursor: pointer;">
                                                <i class="fab fa-whatsapp mr-1 text-success"></i> Send WhatsApp Message Notification
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <button type="submit" class="btn-erp btn-erp-primary btn-lg w-100 font-weight-bold text-uppercase py-2" onclick="$('#is_final_input').val(1)">
                                            <i class="fas fa-check-double mr-1"></i> Final Submission
                                        </button>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button type="submit" class="btn-erp btn-erp-outline btn-lg w-100 font-weight-bold text-uppercase py-2" onclick="$('#is_final_input').val(0)">
                                            <i class="fas fa-plus-circle mr-1"></i> Save & Add More
                                        </button>
                                    </div>
                                    
                                    <div class="col-12">
                                        <p class="text-muted small text-center mt-2 mb-0">
                                            <i class="fas fa-info-circle mr-1 text-muted"></i>
                                            <strong>Save & Add More</strong> saves this session and keeps slip pending. 
                                            <strong>Final Submission</strong> marks this slip complete.
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- RIGHT PANEL (Slip Image) --}}
                        <div class="col-lg-5 col-md-12 mb-3">
                            <div class="sticky-wrapper">
                                <div class="erp-card bg-white p-3 text-center position-relative">
                                    <button type="button"
                                        class="btn-erp btn-erp-primary btn-sm position-absolute rotate-btn"
                                        onclick="rotateImage()"
                                        title="Rotate Image 90 Degrees">
                                        <i class="fas fa-redo-alt mr-1"></i> Rotate ↻
                                    </button>
                                    <div class="image-wrapper mt-4">
                                        <img id="slipImage"
                                            src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}" 
                                            class="slip-image"
                                            ondblclick="openImageInNewTab(this)"
                                            title="Double click to open image in new tab">
                                    </div>
                                    <div class="text-muted text-xs mt-2">
                                        <i class="fas fa-search-plus mr-1"></i> Double-click image to view full resolution in a new tab
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> {{-- End row --}}

                </form>
            </div>
        @else
            <div class="erp-card bg-white p-5 text-center">
                <i class="fas fa-file-invoice text-muted mb-3" style="font-size: 40px;"></i>
                <h5 class="font-weight-bold text-dark">No Production Slips Available</h5>
                <p class="text-muted">There are currently no active slips pending digitalization.</p>
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Return to Slips List
                </a>
            </div>
        @endif

    </div>
</div>

<script>
$(function(){
    if($.fn.select2) {
        $('.select2').select2();
    }

    // Trigger fetch on dropdown change
    $('#lot_no_input').on('change', function(){
        fetchLotDetails();
    });

    // Toggle Movement Type logic
    $('input[name="movement_type"]').on('change', function(){
        fetchLotDetails();
    });

    function fetchLotDetails() {
        let lotNo = $('#lot_no_input').val();
        let fromStageId = $('input[name="from_stage_id_ajax"]').val();
        let movementType = $('input[name="movement_type"]:checked').val();

        $.ajax({
            url: "{{ route('admin.order_digitalization.get-lot-details-for-hand-slip') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                lot_no: lotNo,
                from_stage_id: fromStageId,
                movement_type: movementType,
                production_slip_digitization_id: $('input[name="production_slip_digitization_id"]').val()
            },
            success: function(response){
                if(response.inventory && Object.keys(response.inventory).length > 0) {
                    renderInventory(response.inventory);
                    renderBasicInfo(response.basic_info);
                    $('#lotDetailsCard').removeClass('d-none');
                } else {
                    $('#lotDetailsCard').addClass('d-none');
                    if($('#lot_no_input').val()) {
                        alert('No inventory found for this Lot at current stage.');
                    }
                }

                if(response.available_units) {
                    updateToStage(response.available_units);
                }
            },
            error: function(xhr){
                console.error('Error fetching details.');
            }
        });
    }

    function updateToStage(units) {
        let $toStage = $('#to_stage_id');
        let currentVal = $toStage.val() || "{{ $slip_data['last_to_stage_id'] ?? '' }}";
        $toStage.empty();

        if (units && units.length > 0) {
            $.each(units, function (index, unit) {
                let selected = (unit.id == currentVal) ? 'selected' : '';
                $toStage.append(`
                    <option value="${unit.id}" ${selected}>
                        ${unit.name} (${unit.master_stage_name})
                    </option>
                `);
            });
        }
        $toStage.trigger('change.select2');
    }

    function renderBasicInfo(info) {
        let html = '';
        if(info) {
             html += `<div class="row text-xs">
                        <div class="col-md-6 mb-1">
                            ${info.fabric_names && info.fabric_names.length > 0 ? `<div><span class="text-muted text-uppercase">Fabric:</span> <strong>${info.fabric_names.join(', ')}</strong></div>` : ''}
                            ${info.order_numbers && info.order_numbers.length > 0 ? `<div><span class="text-muted text-uppercase">Orders:</span> <strong>${info.order_numbers.join(', ')}</strong></div>` : ''}
                            ${info.design_numbers && info.design_numbers.length > 0 ? `<div><span class="text-muted text-uppercase">Design:</span> <strong>${info.design_numbers.join(', ')}</strong></div>` : ''}
                        </div>
                        <div class="col-md-6 mb-1">
                            ${info.fitting_names && info.fitting_names.length > 0 ? `<div><span class="text-muted text-uppercase">Fitting:</span> <strong>${info.fitting_names.join(', ')}</strong></div>` : ''}
                            ${info.color_names && info.color_names.length > 0 ? `<div><span class="text-muted text-uppercase">Color:</span> <strong>${info.color_names.join(', ')}</strong></div>` : ''}
                            ${info.pattern_names && info.pattern_names.length > 0 ? `<div><span class="text-muted text-uppercase">Pattern:</span> <strong>${info.pattern_names.join(', ')}</strong></div>` : ''}
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row text-xs">
                        <div class="col-md-6">
                            <div style="color: #05421c;"><strong>Total in Stage:</strong> <span class="badge px-2 py-1" style="background:#edf7e4; color:#05421c;">${info.total_inflow || 0}</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-danger"><strong>Total Remaining:</strong> <span class="badge badge-light border text-danger px-2 py-1">${info.total_remaining || 0}</span></div>
                        </div>
                    </div>`;
        }
        $('#basicInfo').html(html);
    }

    $(document).on('click', '#toggleBasicInfo', function(){
        let $info = $('#basicInfo');
        if($info.is(':visible')){
            $info.slideUp();
            $(this).html('<i class="fas fa-eye mr-1"></i> Show Details');
        } else {
            $info.slideDown();
            $(this).html('<i class="fas fa-eye-slash mr-1"></i> Hide Details');
        }
    });

    function renderInventory(inventory) { 
        let tbody = $('#inventoryTableBody');
        tbody.empty();

        $.each(inventory, function(size, qty){
            if(qty > 0) {
                tbody.append(`
                    <tr>
                        <td class="font-weight-bold align-middle">${size}</td>
                        <td class="available-qty align-middle" data-qty="${qty}">
                            <span class="badge badge-light border px-2 py-1">${qty}</span>
                        </td>
                        <td class="align-middle">
                            <input type="number" name="sizes[${size}]" 
                                   class="form-control form-control-sm send-qty text-center font-weight-bold" 
                                   min="0" 
                                   style="border: 1px solid #c3e6cb;"
                                   placeholder="0">
                        </td>
                    </tr>
                `);
            }
        });
    }

    $(document).on('input', '.send-qty', function(){
        $('#sendAllQty').prop('checked', false);
        let total = 0;
        $('.send-qty').each(function(){
            total += parseInt($(this).val()) || 0;
        });
        $('#totalMovingQty').text(total);
    });

    $('#handSlipForm').on('submit', function(e){
        let totalRaw = $('#totalMovingQty').text();
        let total = parseInt(totalRaw) || 0;
        if(total <= 0) {
            alert('Please enter quantity to move.');
            e.preventDefault();
        }
    });

});
</script>
<script>
$(document).on('change', '#sendAllQty', function () {
    let total = 0;

    if ($(this).is(':checked')) {
        // Fill all Send Qty with Available Qty
        $('#inventoryTableBody tr').each(function () {
            let availableQty = parseInt($(this).find('.available-qty').data('qty')) || 0;
            let input = $(this).find('.send-qty');

            input.val(availableQty);
            total += availableQty;
        });
    } else {
        // Clear all Send Qty
        $('.send-qty').val('');
        total = 0;
    }

    $('#totalMovingQty').text(total);
});
</script>
<script>
let isAutoFilling = false;

/* TOTAL PIECES -> SEND QTY */
$(document).on('input', '#totalPieces', function () {

    let totalPieces = parseInt($(this).val()) || 0;
    let rows = $('#inventoryTableBody tr');

    if (totalPieces <= 0 || rows.length === 0) return;

    isAutoFilling = true;
    $('#sendAllQty').prop('checked', false);

    let totalAvailable = 0;
    let proportions = [];
    
    rows.each(function (index) {
        let maxQty = parseInt($(this).find('.available-qty').data('qty')) || 0;
        totalAvailable += maxQty;
        proportions.push({
            index: index,
            maxQty: maxQty,
            row: $(this)
        });
    });
    
    if (totalAvailable === 0) {
        isAutoFilling = false;
        return;
    }

    let allocatedCount = 0;
    let remains = [];

    proportions.forEach(item => {
        let exactShare = (item.maxQty / totalAvailable) * totalPieces;
        let intShare = Math.floor(exactShare);
        let fractionalPart = exactShare - intShare;

        item.allocated = intShare;
        allocatedCount += intShare;

        remains.push({
            index: item.index,
            fraction: fractionalPart,
            maxQty: item.maxQty
        });
    });

    let remainderToDistribute = totalPieces - allocatedCount;
    remains.sort((a, b) => b.fraction - a.fraction);

    for (let i = 0; i < remainderToDistribute; i++) {
        let targetIndex = remains[i % remains.length].index;
        let targetItem = proportions.find(p => p.index === targetIndex);
        targetItem.allocated += 1;
    }

    proportions.forEach(item => {
        let safeQty = Math.min(item.allocated, item.maxQty);
        item.row.find('.send-qty').val(safeQty);
    });

    updateTotalMoving();
    isAutoFilling = false;
});

/* SEND QTY -> TOTAL PIECES */
$(document).on('input', '.send-qty', function () {

    if (isAutoFilling) return;

    $('#sendAllQty').prop('checked', false);
    updateTotalMoving();

    let total = 0;
    $('.send-qty').each(function () {
        total += parseInt($(this).val()) || 0;
    });

    $('#totalPieces').val(total);
});

/* SEND ALL */
$(document).on('change', '#sendAllQty', function () {

    let total = 0;
    isAutoFilling = true;

    if ($(this).is(':checked')) {
        $('#inventoryTableBody tr').each(function () {
            let qty = parseInt($(this).find('.available-qty').data('qty')) || 0;
            $(this).find('.send-qty').val(qty);
            total += qty;
        });
    } else {
        $('.send-qty').val('');
        total = 0;
    }

    $('#totalMovingQty').text(total);
    $('#totalPieces').val(total);

    isAutoFilling = false;
});

/* HELPER */ 
function updateTotalMoving() {
    let total = 0;
    $('.send-qty').each(function () {
        total += parseInt($(this).val()) || 0;
    });
    $('#totalMovingQty').text(total);
}
</script>
<script>
    let rotation = 0;

    function rotateImage() {
        rotation += 90;
        document.getElementById('slipImage').style.transform =
            `rotate(${rotation}deg)`;
    }

    function openImageInNewTab(img) {
        window.open(img.src, '_blank');
    }
</script>
@endsection
