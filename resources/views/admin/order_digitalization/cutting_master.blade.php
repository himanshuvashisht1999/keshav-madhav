@extends('admin.layouts.app')

@section('content')
<style>
    .action-btn-group .btn {
        margin-right: 6px;
    }

    .action-btn-group .btn.active {
        background-color: #007bff;
        color: #fff;
    }

    .form-section {
        display: none;
    }

    .form-section.active {
        display: block;
    }

    .slip-image {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 6px;
    }

    .card-title {
        font-weight: 600;
    }

    .lot-inline{display:flex;align-items:center;gap:15px}
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px}
.lot-input-label{font-weight:900;font-size:18px}
.lot-input{flex:1;padding:12px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}

#st_table th,
#st_table td{
    white-space: nowrap;
}

#st_table td button{
    padding: 3px 8px;
}
</style>
<style>
    /* default look */
.action-btn {
    color: #fff;
    border: none;
}

/* individual colors */
.btn-rolls { background:#6f42c1; }        /* Purple */
.btn-time { background:#20c997; }         /* Teal/Green */
.btn-stitching { background:#fd7e14; }    /* Orange */
.btn-printing { background:#17a2b8; }     /* Cyan */
.btn-emb { background:#e83e8c; }          /* Pink */

/* highlight when active */
.action-btn.active {
    box-shadow: 0 0 0 3px rgba(0,0,0,.08);
    filter: brightness(1.05);
}

/* hover */
.action-btn:hover {
    filter: brightness(1.1);
}

</style>

<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid text-center">
            <h2 class="mb-3">Production Slip – Cutting Master</h2>

            

            {{-- ACTION BUTTONS --}}
            <div class="action-btn-group mb-3">
                <button class="btn action-btn btn-rolls active" data-target="rolls">
                    Rolls Allot
                </button>

                <button class="btn action-btn btn-time" data-target="time">
                    Time Allocation
                </button>

                <button class="btn action-btn btn-stitching" data-target="stitching">
                    Send to Stitching
                </button>

                <button class="btn action-btn btn-printing" data-target="printing">
                    Send to Printing
                </button>

                @if(request('is_skip') == 1)
                <a href="{{ route('admin.order_digitalization.cutting-master') }}"
                class="btn btn-secondary">
                    View Normal Slips
                </a>

                
                @else
                <a href="{{ route('admin.order_digitalization.cutting-master', ['is_skip' => 1]) }}"
                class="btn btn-secondary">
                    View Skipped Slips
                </a>
                @endif

                    
            </div>
        </div>
    </section>

    {{-- MAIN CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                {{-- LEFT FORM PANEL --}}
                @if($cutting_slip)
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body">

                            {{-- ROLLS ALLOT FORM --}}
                            <div class="form-section active" id="form-rolls">

                                <!-- <h5 class="card-title mb-3">Fabric Rolls Assigning</h5> -->

                                <form method="POST"
                                    id="rollAssignForm"
                                    action="{{ route('admin.order_digitalization.store-rolls-assign') }}">
                                    @csrf

                                    <!-- <label>(Date - {{ getformatDateTime($cutting_slip->created_at) }})</label> -->

                                    <input type="hidden"
                                        name="slip_create_date_time"
                                        value="{{ $cutting_slip->created_at ?? '' }}">

                                    {{-- LOT --}}
                                    <div class="card p-2 mt-3 border">
                                        <label>Order No *</label>
                                        <select id="select_order_no" name="select_order_no" class="form-control mb-2 select2">
                                            <option value="">Select Order No</option>
                                            @foreach($orders as $order)
                                                <option value="{{ $order->id }}">
                                                    {{ $order->sku}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text"
                                            id="lot_no"
                                            class="lot-input"
                                            placeholder="Enter Lot Number"
                                            inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <small class="text-danger" id="err_lot_no"></small>

                                    {{-- CUTTING MASTER --}}
                                    <input type="hidden" name="to_master_unit" value="">
                                    

                                    {{-- ADD ROLL --}}
                                    <div class="card p-2 mt-3 border">
                                        <label>Design No</label>
                                        <select id="design_id" class="form-control mb-2 select2">
                                            <option value="">Select Design No</option>
                                        </select>
                                        
                                    </div>
                                    <div class="card p-2 mt-2 border bg-light">
                                        <div><strong>Fabric :</strong> <span id="show_fabric">—</span></div>
                                        <div><strong>Color :</strong> <span id="show_color">—</span></div>
                                        <div><strong>Pattern :</strong> <span id="show_pattern">—</span></div>
                                        <div><strong>Fitting :</strong> <span id="show_fitting">—</span></div>
                                        <div><strong>Cutting Master :</strong> <span id="show_cutting_master">—</span></div>
                                    </div>
                                    <div class="card p-2 mt-3 border">
                                        <h6>Add Roll</h6>
                                        <label>Roll No</label>
                                        <select id="roll_no" class="form-control mb-2 select2">
                                            <option value="">Select Roll No</option>
                                            
                                        </select>
                                        
                                        <small class="text-danger" id="err_roll_no"></small>

                                        <label class="mt-2">Total Meter</label>
                                        <input type="number" id="meter" class="form-control mb-1">
                                        
                                        <!-- SIZE ALLOCATION UI -->
                                        <div id="size_allocations" class="mt-3 p-2 border rounded bg-white">
                                            <label class="mb-2">Size Wise Quantity</label>
                                            <div id="size_inputs_container"></div>
                                        </div>

                                        <div id="roll_cutting_details"></div>
                                        <small class="text-danger" id="err_meter"></small>

                                        <button type="button"
                                                class="btn btn-primary mt-3 w-100 add-roll">
                                            + Add Roll
                                        </button>
                                    </div>

                                    {{-- ADDED ROLLS TABLE --}}
                                    <div class="card p-2 mt-3 border">
                                        <h6>Added Rolls</h6>

                                        <table class="table table-bordered" id="productList">
                                            <thead>
                                                <tr>
                                                    <th>Lot No</th>
                                                    <th>Cutting Master</th>
                                                    <th>Roll No</th>
                                                    <th>Meter</th>
                                                    <th>Sizes</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr id="noDataRow">
                                                    <td colspan="6" class="text-center text-muted">
                                                        No rolls added yet
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <input type="hidden"
                                        name="production_slip_digitization_id"
                                        value="{{ $cutting_slip->id ?? '' }}">

                                    <button type="submit"
                                            id="submit"
                                            class="btn btn-success w-100 mt-3">
                                        Submit
                                    </button>

                                </form>
                            </div>


                            {{-- TIME ALLOCATION FORM --}}
                            <div class="form-section" id="form-time">
                                <h5 class="mb-4 text-success font-weight-bold">Time Allocation</h5>
                                
                                <form method="POST" id="timeAllocationForm" action="{{ route('admin.order_digitalization.store-time-allocation') }}">
                                    @csrf
                                    
                                    {{-- Lot Number Selection --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Select Lot No *</label>
                                        <select name="lot_no" id="time_lot_no" class="form-control select2 lot-selector" data-tab="time" required>
                                            <option value="">Select Lot No</option>
                                            @foreach($available_lots as $lot)
                                                <option value="{{ $lot }}">{{ $lot }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Select the lot number for time allocation</small>
                                    </div>

                                    {{-- Lot Details Display --}}
                                    <div id="time_lot_details" class="lot-details-container" style="display:none;"></div>

                                    {{-- Start Date Time --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Start Date & Time *</label>
                                        <input type="datetime-local" 
                                               name="start_date_time" 
                                               class="form-control" 
                                               required
                                               value="{{ date('Y-m-d\TH:i') }}">
                                        <small class="text-muted">Production start date and time</small>
                                    </div>

                                    {{-- Production Stages Time Allocation --}}
                                    <div class="card border-success mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 text-success">
                                                <i class="fas fa-clock mr-2"></i>Stage-wise Time Allocation
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            @if($production_stages->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th width="10%">#</th>
                                                                <th width="50%">Stage Name</th>
                                                                <th width="40%">Time (Days)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($production_stages as $index => $stage)
                                                                <tr>
                                                                    <td class="text-center font-weight-bold">{{ $stage->sequence ?? ($index + 1) }}</td>
                                                                    <td>
                                                                        <strong>{{ $stage->name }}</strong>
                                                                    </td>
                                                                    <td>
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="number" 
                                                                                   name="stages[{{ $stage->id }}]" 
                                                                                   class="form-control" 
                                                                                   step="0.5" 
                                                                                   min="0"
                                                                                   placeholder="0.5, 1, 2..."
                                                                                   required>
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text">days</span>
                                                                            </div>
                                                                        </div>
                                                                        <small class="text-muted">Enter 0.5 for half day (4 hrs), 1 for full day (8 hrs)</small>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="alert alert-warning mb-0">
                                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                                    No production stages found. Please configure stages first.
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Remarks --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                                    </div>

                                    {{-- Submit Button --}}
                                    @if($production_stages->count() > 0)
                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-save mr-2"></i> Save Time Allocation
                                        </button>
                                    @endif
                                </form>
                            </div>
                            {{-- STITCHING FORM --}}
                            <div class="form-section" id="form-stitching">
                                <h5 class="mb-4 text-primary font-weight-bold">Send to Stitching</h5>
                                
                                <form method="POST" id="stitchingForm" action="{{ route('admin.order_digitalization.store-stitching') }}">
                                    @csrf
                                    <input type="hidden" name="production_slip_digitization_id" value="{{ $cutting_slip->id ?? '' }}">
                                    <input type="hidden" name="to_stage_id" value="4">{{-- Stitching stage ID --}}
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Select Lot No *</label>
                                        <select name="lot_no" id="stitching_lot_no" class="form-control select2 lot-selector" data-tab="stitching" required>
                                            <option value="">Select Lot No</option>
                                            @foreach($lots_stitching as $lot_no)
                                                <option value="{{ $lot_no }}">{{ $lot_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Lot Details Display --}}
                                    <div id="stitching_lot_details" class="lot-details-container" style="display:none;"></div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-paper-plane mr-2"></i> Send to Stitching
                                    </button>
                                </form>
                            </div>
                            {{-- PRINTING FORM --}}
                            <div class="form-section" id="form-printing">
                                <h5 class="mb-4 text-primary font-weight-bold">Send to Printing</h5>
                                
                                <form method="POST" id="printingForm" action="{{ route('admin.order_digitalization.store-printing') }}">
                                    @csrf
                                    <input type="hidden" name="production_slip_digitization_id" value="{{ $cutting_slip->id ?? '' }}">
                                    <input type="hidden" name="to_stage_id" value="1">{{-- Printing stage ID --}}
                                    
                                    <div class="form-group">
                                        <label class="font-weight-bold">Select Lot No *</label>
                                        <select name="lot_no" id="printing_lot_no" class="form-control select2 lot-selector" data-tab="printing" required>
                                            <option value="">Select Lot No</option>
                                            @foreach($lots_printing as $lot_no)
                                                <option value="{{ $lot_no }}">{{ $lot_no }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Lot Details Display --}}
                                    <div id="printing_lot_details" class="lot-details-container" style="display:none;"></div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-print mr-2"></i> Send to Printing
                                    </button>
                                </form>
                            </div>




                            

                        </div>
                    </div>
                </div>

                {{-- RIGHT IMAGE PANEL --}}
                <div class="col-md-5">
                    

                    <div class="card p-3 border position-relative slip-panel">

                        <!-- SKIP BUTTON OVER IMAGE -->
                        <form action="{{ route('admin.order_digitalization.skip') }}"
                            method="POST"
                            class="position-absolute skip-btn">
                            @csrf
                            <input type="hidden"
                                name="production_slip_digitization_id"
                                value="{{ $cutting_slip->id ?? '' }}">
                            <button type="submit" class="btn btn-danger btn-sm">
                                Skip Slip
                            </button>
                        </form>

                        <img src="{{ asset('assets/production_slips/'.($cutting_slip->slip_file ?? '')) }}"
                            class="img-fluid rounded">

                    </div>

                    {{-- Time Allocation Info Panel --}}
                    <div class="card p-4 border time-panel" style="display: none;">
                        <div class="text-center">
                            <i class="fas fa-clock fa-4x text-success mb-3"></i>
                            <h4 class="text-success font-weight-bold">Time Allocation</h4>
                            <p class="text-muted mb-4">
                                Define time allocation for production stages without requiring a slip upload.
                            </p>
                            
                            <div class="alert alert-info text-left">
                                <h6 class="font-weight-bold">
                                    <i class="fas fa-info-circle mr-2"></i>How it works:
                                </h6>
                                <ul class="mb-0 pl-3">
                                    <li>Select a lot number from available lots</li>
                                    <li>Set the production start date and time</li>
                                    <li>Define time (in days) for each production stage</li>
                                    <li>System calculates expected completion dates</li>
                                </ul>
                            </div>

                            <div class="card bg-light border-0 mt-3">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-dark">Working Hours</h6>
                                    <p class="mb-1"><strong>9:00 AM - 5:00 PM</strong></p>
                                    <p class="text-muted small mb-0">8 hours per day</p>
                                </div>
                            </div>

                            <div class="card bg-light border-0 mt-2">
                                <div class="card-body">
                                    <h6 class="font-weight-bold text-dark">Time Units</h6>
                                    <p class="mb-1">0.5 days = <strong>4 hours</strong> (Half day)</p>
                                    <p class="mb-0">1 day = <strong>8 hours</strong> (Full day)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                @else
                <div class="col-md-12">
                    <div class="alert alert-info text-center">
                        No Production Slip Available
                    </div>
                </div>

                @endif

            </div>
        </div>
    </section>

</div>

{{-- SCRIPT --}}
<script>
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function () {

            document.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            document.querySelectorAll('.form-section').forEach(f => f.classList.remove('active'));

            document.getElementById('form-' + this.dataset.target).classList.add('active');

            // Toggle right panel based on tab
            const slipPanel = document.querySelector('.slip-panel');
            const timePanel = document.querySelector('.time-panel');
            
            if (this.dataset.target === 'time') {
                // Show time info panel, hide slip
                if (slipPanel) slipPanel.style.display = 'none';
                if (timePanel) timePanel.style.display = 'block';
            } else {
                // Show slip panel, hide time info
                if (slipPanel) slipPanel.style.display = 'block';
                if (timePanel) timePanel.style.display = 'none';
            }
        });
    });
</script>
<script>
$(function(){

    $('#rollAssignForm').on('submit', function (e) {

        if ($('#productList tbody tr').not('#noDataRow').length === 0) {
            alert('Please add at least one roll before submitting.');
            e.preventDefault();
            return false;
        }
    });

    function clearErrors(){
        $('.text-danger').text('');
    }

    $('.add-roll').click(function () {

        // clear previous errors
        $('#err_lot_no,#err_roll_no,#err_meter').text('');

        let lotNo = $('#lot_no').val().trim();
        let rollSelect = $('#roll_no');
        let selectedOption = rollSelect.find(':selected');

        let rollId = selectedOption.val();
        let rollText = selectedOption.text();
        let availableMeter = parseFloat(selectedOption.data('meter')) || 0;

        let meter = parseFloat($('#meter').val()) || 0;

        let cutting = $('input[name="to_master_unit"]').val();
        let cuttingText = $('#show_cutting_master').text() || '—';

        let valid = true;

        /* --------------------
        BASIC VALIDATIONS
        -------------------- */
        if (!lotNo) {
            $('#err_lot_no').text('Lot No is required');
            valid = false;
        }

        if (!rollId) {
            $('#err_roll_no').text('Roll No is required');
            valid = false;
        }

        if (meter <= 0) {
            $('#err_meter').text('Meter must be greater than 0');
            valid = false;
        }

        if (!valid) return;

        /* --------------------
        METER VALIDATION
        -------------------- */
        let alreadyUsed = selectedRollMeters[rollId] || 0;
        let remaining = availableMeter - alreadyUsed;

        if (meter > remaining) {
            $('#err_meter').text(`Only ${remaining} meter remaining for this roll`);
            return;
        }

        /* --------------------
        SIZE VALIDATION & COLLECTION
        -------------------- */
        let sizeDetails = [];
        let sizeSummary = [];
        let totalQty = 0;

        $('.size-qty-input').each(function() {
            let qty = parseFloat($(this).val()) || 0;
            if (qty > 0) {
                let size = $(this).data('size');
                let detailId = $(this).data('detail-id');
                let pending = parseFloat($(this).data('pending')) || 0;

                // Optional: Validate if qty > pending (if strict check needed)
                // if(qty > pending) { alert('Qty exceeds pending requirement'); valid=false; return false; }

                sizeDetails.push({
                    detail_id: detailId,
                    size: size,
                    qty: qty
                });
                sizeSummary.push(`${size}:${qty}`);
                totalQty += qty;
            }
        });

        // Uncomment if you want to enforce at least one size quantity
        // if (totalQty <= 0) { alert('Please enter quantity for at least one size'); return; }

        if (!valid) return;

        /* --------------------
        ADD ROW
        -------------------- */
        $('#noDataRow').remove();

        // Convert sizeDetails object to JSON string for backend
        let sizeJson = JSON.stringify(sizeDetails);
        let sizeDisplay = sizeSummary.join(', ') || '—';

        $('#productList tbody').append(`
            <tr data-roll-id="${rollId}" data-meter="${meter}">
                <td>${lotNo}
                    <input type="hidden" name="lot_no_list[]" value="${lotNo}">
                </td>

                <td>${cuttingText}
                    <input type="hidden" name="cutting_unit_list[]" value="${cutting}">
                </td>

                <td>${rollText}
                    <input type="hidden" name="roll_no_list[]" value="${rollId}">
                </td>

                <td>${meter}
                    <input type="hidden" name="meter_list[]" value="${meter}">
                    <input type="hidden" name="size_details[]" value='${sizeJson}'>
                </td>
                
                <td>${sizeDisplay}</td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                </td>
            </tr>
        `);

        /* --------------------
        TRACK USED METER
        -------------------- */
        selectedRollMeters[rollId] = alreadyUsed + meter;

        /* --------------------
        RESET INPUTS
        -------------------- */
        $('#roll_no').val('').trigger('change');
        $('#meter').val('');
        $('.size-qty-input').val(''); // Clear size inputs
    });


    $(document).on('click', '.remove-row', function () {

        let row = $(this).closest('tr');
        let rollId = row.data('roll-id');
        let meter = parseFloat(row.data('meter')) || 0;

        if (rollId && selectedRollMeters[rollId]) {
            selectedRollMeters[rollId] -= meter;

            if (selectedRollMeters[rollId] <= 0) {
                delete selectedRollMeters[rollId];
            }
        }

        row.remove();

        if ($('#productList tbody tr').length === 0) {
            $('#productList tbody').html(`
                <tr id="noDataRow">
                    <td colspan="6" class="text-center text-muted">
                        No rolls added yet
                    </td>
                </tr>
            `);
        }
    });


});
let selectedRollMeters = {}; // track used meters per roll

$(document).ready(function () {
    const ordersData = @json($orders);
    
    // ... (rest of order select logic same) ...

    $('#select_order_no').on('change', function () {
        
        let orderId = parseInt($(this).val());
        
        let designSelect = $('#design_id');

        designSelect.empty()
            .append('<option value="">Select Design No</option>');

        if (!orderId) return;

        let order = ordersData.find(o => o.id === orderId);
        
        if (!order || !order.order_product_sets) return;

        order.order_product_sets.forEach(set => {
            designSelect.append(
                `<option value="${set.id}">
                    ${set.design_number}
                </option>`
            );
        });
        designSelect.trigger('change');
    });

    $('#design_id').on('change', function () {

        let designSetId = parseInt($(this).val());
        let orderId = parseInt($('#select_order_no').val());

        // reset UI
        $('#show_fabric,#show_color,#show_pattern,#show_fitting,#show_cutting_master').text('—');
        $('#size_inputs_container').empty(); // Clear sizes

        let rollSelect = $('#roll_no');
        rollSelect.empty().append('<option value="">Select Roll No</option>');

        if (!designSetId || !orderId) return;

        let order = ordersData.find(o => o.id === orderId);
        if (!order || !order.order_product_sets) return;

        let set = order.order_product_sets.find(s => s.id === designSetId);
        if (!set) return;

        /* --------------------
           SHOW BASIC INFO
        -------------------- */
        $('#show_fabric').text(set.fabric?.name ?? '—');
        $('#show_color').text(set.colors?.name ?? '—');
        $('#show_pattern').text(set.master_design_pattern?.name ?? '—');
        $('#show_fitting').text(set.master_product_fitting?.name ?? '—');
        $('#show_cutting_master').text(set.stage_master_unit?.name ?? '—');

        /* --------------------
           LOAD SIZE INPUTS
        -------------------- */
        if (set.product_set_details) {
            set.product_set_details.forEach(detail => {
                let remaining = detail.remaining_lot_allocated;
                let size = detail.size;
                if(remaining > 0) {
                    $('#size_inputs_container').append(`
                        <div class="row mb-2 align-items-center">
                            <div class="col-4">
                                <span class="font-weight-bold">${size}</span> <br>
                                <small class="text-muted">(Pending: ${remaining})</small>
                            </div>
                            <div class="col-8">
                                <input type="number" 
                                    class="form-control size-qty-input" 
                                    data-detail-id="${detail.id}"
                                    data-size="${size}"
                                    data-pending="${remaining}"
                                    placeholder="Qty">
                            </div>
                        </div>
                    `);
                }
            });
        }

        /* --------------------
           LOAD ROLL NUMBERS
        -------------------- */
        if (!set.fabric || !set.fabric.receipt_details) {
            rollSelect.trigger('change');
            return;
        }

        set.fabric.receipt_details.forEach(detail => {

            // OPTIONAL: skip empty rolls
            if (parseFloat(detail.remaining_quantity) <= 0) return;

            rollSelect.append(`
                <option 
                    value="${detail.id}"
                    data-meter="${detail.remaining_quantity}"
                    data-roll="${detail.roll_number}">
                    Roll ${detail.roll_number} (${detail.remaining_quantity} m)
                </option>
            `);
        });

        rollSelect.trigger('change'); // refresh select2
    });

    
    // ... (rest of event listeners same) ...
    $('#roll_no').on('change', function () {
        let selected = $(this).find(':selected');
        let availableMeter = parseFloat(selected.data('meter')) || 0;

        $('#meter').val('');
        $('#meter').attr('max', availableMeter);
    });

    $('#meter').on('input', function () {
        let max = parseFloat($(this).attr('max')) || 0;
        let val = parseFloat($(this).val()) || 0;

        if (val > max) {
            $(this).val(max);
            $('#err_meter').text(`Max allowed meter is ${max}`);
        } else {
            $('#err_meter').text('');
        }
    });


});
</script>

{{-- Lot Details Fetching Script --}}
<script>
$(document).ready(function() {
    // Handle lot selection for all tabs
    $('.lot-selector').on('change', function() {
        const lotNo = $(this).val();
        const tab = $(this).data('tab');
        const detailsContainer = $(`#${tab}_lot_details`);
        
        if (!lotNo) {
            detailsContainer.hide().html('');
            return;
        }

        // Show loading
        detailsContainer.html(`
            <div class="text-center py-3">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-2">Loading lot details...</p>
            </div>
        `).show();

        // Fetch lot details
        $.ajax({
            url: '{{ route("admin.order_digitalization.get-lot-details-for-display") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                lot_no: lotNo
            },
            success: function(data) {
                if (data) {
                    displayLotDetails(data, detailsContainer);
                } else {
                    detailsContainer.html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No details found for this lot.
                        </div>
                    `);
                }
            },
            error: function() {
                detailsContainer.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-2"></i>
                        Error loading lot details. Please try again.
                    </div>
                `);
            }
        });
    });

    function displayLotDetails(data, container) {
        // Calculate total pieces
        let totalPieces = 0;
        let sizeCount = 0;
        if (data.size_wise_quantities && Object.keys(data.size_wise_quantities).length > 0) {
            sizeCount = Object.keys(data.size_wise_quantities).length;
            for (const qty of Object.values(data.size_wise_quantities)) {
                totalPieces += parseInt(qty);
            }
        }

        // Create fabric badges
        let fabricBadges = '';
        if (data.fabric_names && data.fabric_names.length > 0) {
            fabricBadges = data.fabric_names.map(name => 
                `<span class="badge badge-soft-info mr-1 mb-1">${name}</span>`
            ).join('');
        } else {
            fabricBadges = '<span class="text-muted small">No fabric data</span>';
        }

        // Create order badges
        let orderBadges = '';
        if (data.order_numbers && data.order_numbers.length > 0) {
            orderBadges = data.order_numbers.map(sku => 
                `<span class="badge badge-soft-primary mr-1 mb-1">${sku}</span>`
            ).join('');
        } else {
            orderBadges = '<span class="text-muted small">No order data</span>';
        }

        // Create size details for modal
        let sizeModalRows = '';
        if (data.size_wise_quantities && Object.keys(data.size_wise_quantities).length > 0) {
            for (const [size, qty] of Object.entries(data.size_wise_quantities)) {
                const percentage = (qty / totalPieces * 100).toFixed(1);
                sizeModalRows += `
                    <tr>
                        <td class="font-weight-bold">${size}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: ${percentage}%" 
                                     aria-valuenow="${qty}" aria-valuemin="0" aria-valuemax="${totalPieces}">
                                    ${qty} pcs
                                </div>
                            </div>
                        </td>
                        <td class="text-right">${percentage}%</td>
                    </tr>
                `;
            }
        }

        const uniqueId = `lot_${data.lot_no.replace(/[^a-zA-Z0-9]/g, '_')}`;

        const html = `
            <!-- Collapsible Lot Details Card -->
            <div class="card border-primary shadow-sm mb-3 lot-details-card">
                <!-- Collapsed Header (Always Visible) -->
                <div class="card-header bg-gradient-primary text-white p-2 cursor-pointer" 
                     data-toggle="collapse" data-target="#${uniqueId}" 
                     aria-expanded="false" aria-controls="${uniqueId}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-box mr-2"></i>
                            <strong>Lot: ${data.lot_no}</strong>
                            <span class="ml-3 badge badge-light">${data.total_rolls} Roll${data.total_rolls > 1 ? 's' : ''}</span>
                            <span class="ml-2 badge badge-light">${data.total_meters}m</span>
                            <span class="ml-2 badge badge-light">${totalPieces} pcs</span>
                        </div>
                        <i class="fas fa-chevron-down toggle-icon"></i>
                    </div>
                </div>

                <!-- Expandable Details -->
                <div id="${uniqueId}" class="collapse">
                    <div class="card-body p-3">
                        <!-- Quick Info Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-cut text-primary mr-2"></i>
                                    <strong>${data.cutting_master.name}</strong>
                                    <div class="text-muted small ml-4">${data.cutting_master.warehouse}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                                    <span class="small">${data.cutting_master.address}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Fabric & Orders -->
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <i class="fas fa-tshirt text-info mr-2"></i>
                                    <strong class="small">Fabric:</strong>
                                </div>
                                <div class="ml-4">${fabricBadges}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <i class="fas fa-file-alt text-success mr-2"></i>
                                    <strong class="small">Orders:</strong>
                                </div>
                                <div class="ml-4">${orderBadges}</div>
                            </div>
                        </div>

                        <!-- Size Details Button -->
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    data-toggle="modal" data-target="#sizeModal_${uniqueId}">
                                <i class="fas fa-ruler mr-2"></i>
                                View ${sizeCount} Size${sizeCount > 1 ? 's' : ''} Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Size Details Modal -->
            <div class="modal fade" id="sizeModal_${uniqueId}" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-ruler mr-2"></i>
                                Size-wise Quantities - Lot ${data.lot_no}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="20%">Size</th>
                                            <th width="60%">Quantity</th>
                                            <th width="20%" class="text-right">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${sizeModalRows || '<tr><td colspan="3" class="text-center text-muted">No size data</td></tr>'}
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        <tr>
                                            <td>Total</td>
                                            <td>${totalPieces} pieces</td>
                                            <td class="text-right">100%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.html(html).show();

        // Add rotation animation to chevron
        $(`[data-target="#${uniqueId}"]`).on('click', function() {
            $(this).find('.toggle-icon').toggleClass('fa-rotate-180');
        });
    }
});
</script>


@endsection
