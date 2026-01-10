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

                <button class="btn action-btn btn-emb" data-target="embroidery">
                    Send to Embroidery
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
                <div class="col-md-5">
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
                                        value="{{ $cutting_slip->created_at }}">

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
                                        value="{{ $cutting_slip->id }}">

                                    <button type="submit"
                                            id="submit"
                                            class="btn btn-success w-100 mt-3">
                                        Submit
                                    </button>

                                </form>
                            </div>


                            {{-- TIME ALLOCATION FORM --}}
                            <div class="form-section" id="form-time">
                                
                                
                            </div>
                            {{-- STITCHING FORM --}}
                            <div class="form-section" id="form-stitching">


                                

                            </div>
                            {{-- PRINTING FORM --}}
                            <div class="form-section" id="form-printing">

                                <!-- <h5 class="card-title mb-3">Send to Printing</h5> -->

                                
                            </div>

                            <div class="form-section" id="form-embroidery">

                                <!-- <h5 class="card-title mb-3">Send to Embroidery</h5> -->

                                
                            </div>




                            

                        </div>
                    </div>
                </div>

                {{-- RIGHT IMAGE PANEL --}}
                <div class="col-md-7">
                    

                    <div class="card p-3 border position-relative">

                        <!-- SKIP BUTTON OVER IMAGE -->
                        <form action="{{ route('admin.order_digitalization.skip') }}"
                            method="POST"
                            class="position-absolute skip-btn">
                            @csrf
                            <input type="hidden"
                                name="production_slip_digitization_id"
                                value="{{ $cutting_slip->id}}">
                            <button type="submit" class="btn btn-danger btn-sm">
                                Skip Slip
                            </button>
                        </form>

                        <img src="{{ asset('assets/production_slips/'.$cutting_slip->slip_file) }}"
                            class="img-fluid rounded">

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
        ADD ROW
        -------------------- */
        $('#noDataRow').remove();

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
                </td>

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


@endsection
