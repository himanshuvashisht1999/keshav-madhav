@extends('admin.layouts.app')

@section('content')

<style>
    .rotate-btn {
        top: 10px;
        left: 10px;
        z-index: 10;
    }

    .slip-image {
        transition: transform 0.3s ease;
        cursor: zoom-in;
    }

    .image-wrapper {
        width: 100%;
        height: 75vh;              /* image ko space mile */
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .sticky-wrapper {
        position: -webkit-sticky;
        position: sticky;
        top: 80px;
        z-index: 100;
    }

    .image-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transform-origin: center center;
    }
</style>
<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-3">Production Slips Digitalization (Hand Slip)</h1>
                </div>
            </div>
          
            {{-- OPTIONAL HEADER ACTIONS (Skip/Limit etc) --}}
            <div class="row">
                <div class="col-12 text-right">
                    @if(!empty($slip_data))
                        <a href="{{ route('admin.uploaded-slips.show', $slip_data['id']) }}" target="_blank" class="btn btn-info mr-2">
                            <i class="fas fa-eye"></i> View Slip Details
                        </a>
                        <!-- <form action="{{ route('admin.order_digitalization.skip') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <button type="submit" class="btn btn-secondary mr-2">Skip</button>
                        </form> -->

                        <!-- <form action="{{ route('admin.order_digitalization.delete-slip') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this slip?');">
                            @csrf
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <button type="submit" class="btn btn-danger mr-2">Delete</button>
                        </form> -->
                    @endif
                </div>
            </div>
            <hr class="mt-3 mb-0">
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card p-3 shadow-sm">

                @if(!empty($slip_data))
                    <div id="slip_digitalization">
                        <form method="POST" action="{{ route('admin.order_digitalization.store-hand-slip') }}" id="handSlipForm">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">

                        <div class="row">
                            {{-- LEFT PANEL (Inputs) --}}
                            <div class="col-md-7"> <!-- 70% width style -->
                                <div class="card p-3 mb-3 border">
                                    <div class="d-flex justify-content-between">
                                         <label>Date: <strong>{{ getformatDateTime($slip_data['date_time']) }}</strong></label>
                                    </div>
                                   
                                    {{-- LOT INPUT --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Production Date & Time</label>
                                            <input type="text"
                                            name="production_datetime" class="form-control datetime-picker" placeholder="Select date & time"
                                            value="{{ old('production_datetime', session('last_production_datetime', $slip_data['last_production_datetime'] ?? '')) }}"
                                            required>
                                        </div>
                                        <div class="col-md-12 mt-2 mb-2">
                                            <label class="font-weight-bold">Select Lot No.</label>
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
                                        <div class="col-md-6 mt-2 mb-2">
                                            <label>Bill Number (Optional)</label>
                                            <input type="text" name="bill_number" class="form-control" placeholder="Enter Bill Number" value="{{ old('bill_number', $slip_data['bill_number'] ?? '') }}">
                                        </div>
                                        <div class="col-md-6 mt-2 mb-2">
                                            <label class="font-weight-bold text-dark">Total Pieces (Optional)</label>
                                            <input type="number" min="1" name="total_pieces" id="slip_total_pieces" class="form-control font-weight-bold text-primary" placeholder="Enter Total Pieces" value="{{ old('total_pieces', $slip_data['total_pieces'] ?? '') }}">
                                        </div>
                                        @if(!empty($slip_data['total_digitized_pieces']) || !empty($slip_data['total_pieces']))
                                            <div class="col-md-12 mt-2 mb-2">
                                                <div class="p-2 px-3 rounded border d-flex justify-content-between align-items-center flex-wrap shadow-xs" style="background-color: #f8fafc; border-color: #cbd5e1 !important;">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-layer-group text-primary mr-2" style="font-size: 15px;"></i>
                                                        <span class="text-dark font-weight-bold" style="font-size: 13px;">Digitized so far:</span>
                                                        <span class="badge badge-primary px-2 py-1 ml-2 font-weight-bold" style="font-size: 13px;">{{ $slip_data['total_digitized_pieces'] ?? 0 }} pcs</span>
                                                    </div>
                                                    @if(!empty($slip_data['total_pieces']))
                                                        <div class="d-flex align-items-center mt-1 mt-sm-0">
                                                            <span class="text-dark font-weight-bold mr-1" style="font-size: 13px;">Target:</span>
                                                            <span class="badge badge-secondary px-2 py-1 mr-2 font-weight-bold" style="font-size: 13px;">{{ $slip_data['total_pieces'] }} pcs</span>
                                                            @php $rem = (int)$slip_data['total_pieces'] - (int)($slip_data['total_digitized_pieces'] ?? 0); @endphp
                                                            @if($rem > 0)
                                                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 12px;"><i class="fas fa-hourglass-half mr-1"></i> {{ $rem }} pcs remaining</span>
                                                            @elseif($rem == 0)
                                                                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 12px;"><i class="fas fa-check mr-1"></i> Exact Match</span>
                                                            @else
                                                                <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 12px;"><i class="fas fa-exclamation-triangle mr-1"></i> {{ abs($rem) }} pcs excess</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                   

                                    {{-- MOVEMENT TYPE & STAGE INFO --}}
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="d-block">Movement Type</label>
                                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                                <label class="btn btn-outline-primary w-50 {{ (!isset($slip_data['last_movement_type']) || $slip_data['last_movement_type'] == 1) ? 'active' : '' }}">
                                                    <input type="radio" name="movement_type" value="1" id="type_regular" autocomplete="off" {{ (!isset($slip_data['last_movement_type']) || $slip_data['last_movement_type'] == 1) ? 'checked' : '' }}> 
                                                    <i class="fas fa-arrow-right mr-1"></i> Regular
                                                </label>
                                                <label class="btn btn-outline-danger w-50 {{ (isset($slip_data['last_movement_type']) && $slip_data['last_movement_type'] == 2) ? 'active' : '' }}">
                                                    <input type="radio" name="movement_type" value="2" id="type_damage" autocomplete="off" {{ (isset($slip_data['last_movement_type']) && $slip_data['last_movement_type'] == 2) ? 'checked' : '' }}> 
                                                    <i class="fas fa-undo mr-1"></i> Damage (Return)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>From Stage</label>
                                            <input type="text" class="form-control" value="{{ $slip_data['from_stage']['name'] }} ({{ $slip_data['from_stage']['master_stage_name'] }})" readonly>
                                            <input type="hidden" name="from_stage_id" value="{{ $slip_data['from_stage']['id'] }}"> 
                                            <input type="hidden" name="from_stage_id_ajax" value="{{ $slip_data['from_stage']['master_stage_id'] }}"> 
                                        </div>
                                        <div class="col-md-6">
                                            <label>To Stage</label>
                                            <select name="to_stage_id" class="form-control select2" id="to_stage_id" required>
                                                @foreach($slip_data['unit_master_data'] as $unit)
                                                    <option value="{{ $unit['id'] }}" {{ (isset($slip_data['last_to_stage_id']) && $slip_data['last_to_stage_id'] == $unit['id']) ? 'selected' : '' }}>{{ $unit['name'] }} ({{ $unit['master_stage_name'] }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- LOT DETAILS & INVENTORY (Dynamic) --}}
                                <div id="lotDetailsCard" class="card p-3 border d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <h5 class="text-primary mb-0 mr-3">Lot Inventory Details</h5>
                                            <button type="button" id="toggleBasicInfo" class="btn btn-xs btn-outline-info">
                                                <i class="fas fa-eye mr-1"></i> Show Details
                                            </button>
                                        </div>

                                        <div class="d-flex align-items-center gap-3">
                                            <!-- TOTAL PIECES -->
                                            <input type="number"
                                                id="totalPieces"
                                                class="form-control form-control-sm"
                                                style="width:140px"
                                                placeholder="Total Pieces">

                                            <!-- SEND ALL -->
                                            <div class="form-check ml-2">
                                                <input class="form-check-input" type="checkbox" id="sendAllQty">
                                                <label class="form-check-label fw-bold" for="sendAllQty">
                                                    Send All
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="basicInfo" class="mb-3 p-2 bg-light rounded" style="display:none;">
                                        <!-- Basic API info here -->
                                    </div>

                                    <table class="table table-bordered table-sm">
                                        <thead class="thead-light">
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
                                            <tr>
                                                <td colspan="2" class="text-right font-weight-bold">Total Moving:</td>
                                                <td class="font-weight-bold" id="totalMovingQty">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                {{-- ACTION BUTTONS --}}
                                <div class="mt-4 row">
                                    <input type="hidden" name="is_final" id="is_final_input" value="1">
                                    <div class="col-12 mb-3 mt-2 text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="sendWhatsapp" name="send_whatsapp" value="1">
                                            <label class="custom-control-label font-weight-bold text-success" for="sendWhatsapp">
                                                <i class="fab fa-whatsapp mr-1"></i> Send WhatsApp Message
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button type="submit" class="btn btn-success btn-lg w-100 font-weight-bold" onclick="$('#is_final_input').val(1)">
                                            <i class="fas fa-check-double mr-1"></i> Final Submission
                                        </button>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <button type="submit" class="btn btn-outline-success btn-lg w-100 font-weight-bold" onclick="$('#is_final_input').val(0)">
                                            <i class="fas fa-plus-circle mr-1"></i> Save & Add More
                                        </button>
                                    </div>
                                    
                                    <div class="col-12">
                                        <p class="text-muted small text-center mt-2">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Save & Add More</strong> keeps it pending. 
                                            <strong>Final Submission</strong> marks it as complete.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT PANEL (Image) --}}
                            <div class="col-md-5">
                                <div class="sticky-wrapper">
                                    <div class="card p-3 border text-center">
                                        <button type="button"
                                            class="btn btn-primary btn-sm position-absolute rotate-btn"
                                            onclick="rotateImage()">
                                            Rotate ↻
                                        </button>
                                        <div class="image-wrapper">
                                            <img id="slipImage"
                                                src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}" 
                                                class="slip-image"
                                                ondblclick="openImageInNewTab(this)">
                                        </div>
                                    </div>
                                </div>
                        </div> {{-- End row --}}

                        </form>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        <h4>No Production Slips Available</h4>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<style>
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px; display:flex; gap:10px; align-items: center;}
.lot-input-label{font-weight:900;font-size:18px; margin:0;}
.lot-input{flex:1;padding:10px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}
</style>

<script>
$(function(){
    $('.select2').select2();

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

        // if(!lotNo) {
        //     // alert('Please enter a Lot Number');
        //     return;
        // }

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
             html += `<div class="row">
                        <div class="col-md-6">
                            ${info.fabric_names && info.fabric_names.length > 0 ? `<div><strong>Fabric:</strong> ${info.fabric_names.join(', ')}</div>` : ''}
                            ${info.order_numbers && info.order_numbers.length > 0 ? `<div><strong>Orders:</strong> ${info.order_numbers.join(', ')}</div>` : ''}
                            ${info.design_numbers && info.design_numbers.length > 0 ? `<div><strong>Design:</strong> ${info.design_numbers.join(', ')}</div>` : ''}
                        </div>
                        <div class="col-md-6">
                            ${info.fitting_names && info.fitting_names.length > 0 ? `<div><strong>Fitting:</strong> ${info.fitting_names.join(', ')}</div>` : ''}
                            ${info.color_names && info.color_names.length > 0 ? `<div><strong>Color:</strong> ${info.color_names.join(', ')}</div>` : ''}
                            ${info.pattern_names && info.pattern_names.length > 0 ? `<div><strong>Pattern:</strong> ${info.pattern_names.join(', ')}</div>` : ''}
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-success"><strong>Total in Stage:</strong> ${info.total_inflow || 0}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-danger"><strong>Total Remaining:</strong> ${info.total_remaining || 0}</div>
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
                        <td class="font-weight-bold">${size}</td>
                        <td class="available-qty" data-qty="${qty}">${qty}</td>
                        <td>
                            <input type="number" name="sizes[${size}]" 
                                   class="form-control form-control-sm send-qty" 
                                   min="0" 
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
        // ✅ Fill all Send Qty with Available Qty
        $('#inventoryTableBody tr').each(function () {
            let availableQty = parseInt($(this).find('.available-qty').data('qty')) || 0;
            let input = $(this).find('.send-qty');

            input.val(availableQty);
            total += availableQty;
        });
    } else {
        // ❌ Clear all Send Qty
        $('.send-qty').val('');
        total = 0;
    }

    $('#totalMovingQty').text(total);
});
</script>
<script>
let isAutoFilling = false;

/* TOTAL PIECES ➜ SEND QTY */
$(document).on('input', '#totalPieces', function () {

    let totalPieces = parseInt($(this).val()) || 0;
    let rows = $('#inventoryTableBody tr');

    if (totalPieces <= 0 || rows.length === 0) return;

    isAutoFilling = true;
    $('#sendAllQty').prop('checked', false);

        // First, calculate total available pieces to establish fractions
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

        // We can't distribute more than we have (Disabled for flexibility)
        // totalPieces = Math.min(totalPieces, totalAvailable);

        // Calculate initial fair shares and remainders
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

        // Distribute remainder (Largest Remainder Method)
        let remainderToDistribute = totalPieces - allocatedCount;
        
        // Sort by highest fractional remainder first
        remains.sort((a, b) => b.fraction - a.fraction);

        for (let i = 0; i < remainderToDistribute; i++) {
            // Give 1 extra to the top remainders
            let targetIndex = remains[i % remains.length].index;
            let targetItem = proportions.find(p => p.index === targetIndex);
            targetItem.allocated += 1;
        }

        // Apply calculated allocations
        proportions.forEach(item => {
            // Apply bounds check (though math prevents exceeding maxQty initially)
            let safeQty = Math.min(item.allocated, item.maxQty);
            item.row.find('.send-qty').val(safeQty);
        });

        // Update UI
        updateTotalMoving();
        isAutoFilling = false;
});

/* SEND QTY ➜ TOTAL PIECES */
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
