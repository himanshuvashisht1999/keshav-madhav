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
                                            @foreach($cutting_master_orders as $order)
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
                                    <!-- <label>Cutting Master ({{$master_fabric_warehouse->cutting_master_name}})</label> -->
                                    <input type="hidden" name="to_master_unit" value="{{$master_fabric_warehouse->id}}">
                                    

                                    {{-- ADD ROLL --}}
                                    <div class="card p-2 mt-3 border">
                                        <label>Design No</label>
                                        <select id="design_id" class="form-control mb-2 select2">
                                            <option value="">Select Design No</option>
                                        </select>
                                        <label>Fabric</label>
                                        <select id="fabric" class="form-control mb-2 select2">
                                            <option value="">Select Fabric</option>
                                        </select>
                                    </div>
                                    <div class="card p-2 mt-3 border">
                                        <h6>Add Roll</h6>
                                        <label>Roll No</label>
                                        <select id="roll_no" class="form-control mb-2 select2">
                                            <option value="">Select Roll No</option>
                                            
                                        </select>
                                        {{-- <input type="text" id="roll_no" class="form-control mb-1"> --}}
                                        <small class="text-danger" id="err_roll_no"></small>

                                        <label class="mt-2">Total Meter</label>
                                        <input type="number" id="meter" class="form-control mb-1" step="0.01" readonly>
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
                                <!-- <h5 class="card-title mb-3">Stage Wise Time Allocation</h5> -->

                                <form method="POST"
                                    action="{{ route('admin.order_digitalization.store-time-allocation') }}">
                                    @csrf

                                    <!-- <label>(Date - {{ getformatDateTime($cutting_slip->created_at) }})</label> -->

                                    <input type="hidden"
                                        name="slip_create_date_time"
                                        value="{{ $cutting_slip->created_at }}">

                                    <div class="form-group mt-2">
                                        <label>Start Date & Time</label>
                                        <input type="datetime-local" name="start_date_time" class="form-control">
                                    </div>

                                    {{-- LOT NO --}}
                                    

                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text" name="lot_no" class="lot-input"
                                            placeholder="Enter Lot Number"
                                            required inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <small class="text-danger" id="err_lot_no"></small>

                                    <div class="row align-items-center mb-2">

                                        <div class="col-md-6">
                                            <label class="fw-bold">Stage Name</label>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="fw-bold">Time (in Days)</label>
                                        </div>

                                        @foreach($stages as $stage_data)
                                            <div class="col-md-6 mb-1">
                                                <label>{{ $stage_data->masterStage?->name }}</label>
                                            </div>

                                            <div class="col-md-6 mb-1">
                                                <input type="number"
                                                    class="form-control bg-light"
                                                    placeholder="Enter allowed days"
                                                    name="stages[{{ $stage_data->master_stage_id }}]"
                                                    min="0.5"
                                                    step="0.5"
                                                    required>
                                            </div>

                                            <!-- <div class="col-12"><hr></div> -->
                                        @endforeach
                                    </div>

                                    <input type="hidden"
                                        name="production_slip_digitization_id"
                                        value="{{ $cutting_slip->id }}">


                                    <button type="submit" class="btn btn-success w-100">
                                        Submit Allocation
                                    </button>

                                </form>
                                
                            </div>
                            {{-- STITCHING FORM --}}
                            <div class="form-section" id="form-stitching">


                                <form method="POST" id="stitchingForm" action="{{ route('admin.order_digitalization.store-slip') }}">
                                    @csrf

                                    
                                    <input type="hidden" name="slip_create_date_time" value="{{ $cutting_slip->created_at }}">

                                    {{-- ORDER NO --}}
                                    <div class="form-group mt-2">
                                        <label>Order No *</label>
                                        {{-- <input type="text" id="st_order_no" name="order_no" class="form-control" required> --}}
                                        <select id="st_order_no" name="order_no" class="form-control select2" required>
                                            <option value="">Select Order</option>
                                            @foreach($order_numbers as $order_number)
                                                <option value="{{ $order_number }}">
                                                    {{ $order_number}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- FROM --}}
                                    <input type="hidden" id="from_stage_id" value="3">
                                    <input type="hidden" id="from_stage_name" value="Cutting">
                                    <input type="hidden" id="from_unit_id" value="{{ $cutting_data->id }}">
                                    <input type="hidden" id="from_unit_name" value="{{ $cutting_data->name }}">

                                    {{-- TO = STITCHING --}}
                                    <input type="hidden" id="to_stage_id" value="4">
                                    <input type="hidden" id="to_stage_name" value="Stitching">
                                    <input type="hidden" id="to_unit_id" value="{{ $stiching_to_data?->id }}">
                                    <input type="hidden" id="to_unit_name" value="{{ $stiching_to_data?->name }}">

                                    {{-- LOT --}}
                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text"
                                            id="st_lot_no"
                                            class="lot-input"
                                            placeholder="Enter Lot Number"
                                            inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>

                                    {{-- ADD DESIGN --}}
                                    <div class="card p-3 border">
                                        <h6>Add Design</h6>

                                        <label>Design *</label>
                                        <select id="st_design" class="form-control mb-2">
                                            <option value="">Select Design</option>
                                            @foreach($designs as $d)
                                                <option value="{{ $d->design_number }}">{{ $d->design_number }}</option>
                                            @endforeach
                                        </select>

                                        <label>Colour *</label>
                                        <select id="st_colour_id" class="form-control mb-2">
                                            <option value="">Select Colour</option>
                                            @foreach($colours as $c)
                                                <option value="{{ $c->id }}">{{ $c->sku }}</option>
                                            @endforeach
                                        </select>

                                        {{-- ALWAYS SET SIZE --}}
                                        <label>Set Size *</label>
                                        <select id="st_set_size" class="form-control mb-2">
                                            <option value="">Select Set</option>
                                            @foreach($product_size as $ps)
                                                <option value="{{ $ps->id }}" data-no-of-pcs="{{ $ps->no_of_pcs }}">
                                                    {{ $ps->set_size }} ({{ $ps->size_group }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <label>Set Quantity *</label>
                                        <input type="number" id="st_set_qty" class="form-control" min="1">

                                        <button type="button" class="btn btn-primary w-100 mt-2" id="st_addRow">
                                            + Add Row
                                        </button>
                                    </div>

                                    {{-- TABLE --}}
                                    <div class="card p-3 mt-3 border">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm align-middle" id="st_table">

                                                <thead>
                                                    <tr>
                                                        <th>Lot</th>
                                                        <th>Order No</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Design</th>
                                                        <th>Colour</th>
                                                        <th>Set Size</th>
                                                        <th>Set Qty</th>
                                                        <th>Total Qty</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <input type="hidden" name="production_slip_digitization_id" value="{{ $cutting_slip->id }}">
                                    <input type="hidden" name="remark" value="">

                                    <button class="btn btn-warning w-100 mt-3">
                                        Submit to Stitching
                                    </button>

                                </form>

                            </div>
                            {{-- PRINTING FORM --}}
                            <div class="form-section" id="form-printing">

                                <!-- <h5 class="card-title mb-3">Send to Printing</h5> -->

                                <form method="POST" id="printingForm" action="{{ route('admin.order_digitalization.store-slip') }}">
                                    @csrf

                                    <!-- <label>(Date - {{ getformatDateTime($cutting_slip->created_at) }})</label> -->
                                    <input type="hidden" name="slip_create_date_time" value="{{ $cutting_slip->created_at }}">

                                    {{-- FROM --}}
                                    <input type="hidden" id="pr_from_stage_id" value="3">
                                    <input type="hidden" id="pr_from_stage_name" value="Cutting">
                                    <input type="hidden" id="pr_from_unit_id" value="{{ $cutting_data->id }}">
                                    <input type="hidden" id="pr_from_unit_name" value="{{ $cutting_data->name }}">

                                    {{-- TO PRINTING --}}
                                    <input type="hidden" id="pr_to_stage_id" value="1">
                                    <input type="hidden" id="pr_to_stage_name" value="Printing">
                                    <input type="hidden" id="pr_to_unit_id" value="{{ $printing_to_data?->id }}">
                                    <input type="hidden" id="pr_to_unit_name" value="{{ $printing_to_data?->name }}">

                                    {{-- LOT --}}
                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text" id="pr_lot_no" class="lot-input"
                                            inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>

                                    {{-- ADD DESIGN --}}
                                    <div class="card p-3 border">
                                        <h6>Add Design</h6>

                                        <label>Design *</label>
                                        <select id="pr_design" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($designs as $d)
                                                <option value="{{ $d->design_number }}">{{ $d->design_number }}</option>
                                            @endforeach
                                        </select>

                                        <label>Colour *</label>
                                        <select id="pr_colour_id" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($colours as $c)
                                                <option value="{{ $c->id }}">{{ $c->sku }}</option>
                                            @endforeach
                                        </select>

                                        <label>Set Size *</label>
                                        <select id="pr_set_size" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($product_size as $ps)
                                                <option value="{{ $ps->id }}" data-no-of-pcs="{{ $ps->no_of_pcs }}">
                                                    {{ $ps->set_size }} ({{ $ps->size_group }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <label>Set Quantity *</label>
                                        <input type="number" id="pr_set_qty" class="form-control" min="1">

                                        <button type="button" class="btn btn-primary w-100 mt-2" id="pr_addRow">
                                            + Add Row
                                        </button>
                                    </div>

                                    <div class="card p-3 mt-3 border">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" id="pr_table">
                                                <thead>
                                                    <tr>
                                                        <th>Lot</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Design</th>
                                                        <th>Colour</th>
                                                        <th>Set Size</th>
                                                        <th>Set Qty</th>
                                                        <th>Total Qty</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <input type="hidden" name="production_slip_digitization_id" value="{{ $cutting_slip->id }}">
                                    <input type="hidden" name="remark" value="">

                                    <button class="btn btn-info w-100 mt-3">
                                        Submit to Printing
                                    </button>

                                </form>
                            </div>

                            <div class="form-section" id="form-embroidery">

                                <!-- <h5 class="card-title mb-3">Send to Embroidery</h5> -->

                                <form method="POST" id="embroideryForm" action="{{ route('admin.order_digitalization.store-slip') }}">
                                    @csrf

                                    <!-- <label>(Date - {{ getformatDateTime($cutting_slip->created_at) }})</label> -->
                                    <input type="hidden" name="slip_create_date_time" value="{{ $cutting_slip->created_at }}">

                                    {{-- FROM --}}
                                    <input type="hidden" id="em_from_stage_id" value="3">
                                    <input type="hidden" id="em_from_stage_name" value="Cutting">
                                    <input type="hidden" id="em_from_unit_id" value="{{ $cutting_data->id }}">
                                    <input type="hidden" id="em_from_unit_name" value="{{ $cutting_data->name }}">

                                    {{-- TO --}}
                                    <input type="hidden" id="em_to_stage_id" value="2">
                                    <input type="hidden" id="em_to_stage_name" value="Embroidery">
                                    <input type="hidden" id="em_to_unit_id" value="{{ $embroidery_to_data?->id }}">
                                    <input type="hidden" id="em_to_unit_name" value="{{ $embroidery_to_data?->name }}">

                                    {{-- LOT --}}
                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text" id="em_lot_no" class="lot-input"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>

                                    {{-- ADD DESIGN --}}
                                    <div class="card p-3 border">
                                        <h6>Add Design</h6>

                                        <label>Design *</label>
                                        <select id="em_design" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($designs as $d)
                                                <option value="{{ $d->design_number }}">{{ $d->design_number }}</option>
                                            @endforeach
                                        </select>

                                        <label>Colour *</label>
                                        <select id="em_colour_id" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($colours as $c)
                                                <option value="{{ $c->id }}">{{ $c->sku }}</option>
                                            @endforeach
                                        </select>

                                        <label>Set Size *</label>
                                        <select id="em_set_size" class="form-control mb-2">
                                            <option value="">Select</option>
                                            @foreach($product_size as $ps)
                                                <option value="{{ $ps->id }}" data-no-of-pcs="{{ $ps->no_of_pcs }}">
                                                    {{ $ps->set_size }} ({{ $ps->size_group }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <label>Set Quantity *</label>
                                        <input type="number" id="em_set_qty" class="form-control" min="1">

                                        <button type="button" class="btn btn-primary w-100 mt-2" id="em_addRow">
                                            + Add Row
                                        </button>
                                    </div>

                                    <div class="card p-3 mt-3 border">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" id="em_table">
                                                <thead>
                                                    <tr>
                                                        <th>Lot</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Design</th>
                                                        <th>Colour</th>
                                                        <th>Set Size</th>
                                                        <th>Set Qty</th>
                                                        <th>Total Qty</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <input type="hidden" name="production_slip_digitization_id" value="{{ $cutting_slip->id }}">
                                    <input type="hidden" name="remark" value="">

                                    <button class="btn btn-primary w-100 mt-3">
                                        Submit to Embroidery
                                    </button>

                                </form>
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

    $('.add-roll').click(function(){

        clearErrors();

        let lotNo   = $('#lot_no').val().trim();
        let cutting = $('input[name="to_master_unit"]').val();
        let cuttingText = "{{isset($master_fabric_warehouse) ? $master_fabric_warehouse->cutting_master_name : ''}}";
        let rollNo  = $('#roll_no').val().trim();
        let meter   = $('#meter').val();

        let valid = true;

        if(!lotNo){ $('#err_lot_no').text('Lot No is required'); valid=false; }
        if(!rollNo){ $('#err_roll_no').text('Roll No required'); valid=false; }
        if(!meter || meter <= 0){ $('#err_meter').text('Meter must be > 0'); valid=false; }

        $('input[name="roll_no_list[]"]').each(function(){
            if($(this).val() === rollNo){
                $('#err_roll_no').text('Roll already added');
                valid=false;
            }
        });

        if(!valid) return;

        $('#noDataRow').remove();

        $('#productList tbody').append(`
            <tr>
                <td>${lotNo}<input type="hidden" name="lot_no_list[]" value="${lotNo}"></td>
                <td>${cuttingText}<input type="hidden" name="cutting_unit_list[]" value="${cutting}"></td>
                <td>${rollNo}<input type="hidden" name="roll_no_list[]" value="${rollNo}"></td>
                <td>${meter}<input type="hidden" name="meter_list[]" value="${meter}"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
            </tr>
        `);

        $('#roll_no,#meter').val('');
    });

    $(document).on('click','.remove-row',function(){
        $(this).closest('tr').remove();

        if($('#productList tbody tr').length === 0){
            $('#productList tbody').html(`
                <tr id="noDataRow">
                    <td colspan="6" class="text-center text-muted">No rolls added yet</td>
                </tr>
            `);
        }
    });

});
</script>


<script>
    // --- STITCHING LOGIC ---
$(function () {

    $('#st_addRow').click(function(){

        let lot      = $('#st_lot_no').val();
        let orderNo  = $('#st_order_no').val();
        let design   = $('#st_design').val();
        let designText = $('#st_design option:selected').text();
        let colour   = $('#st_colour_id').val();
        let colourText = $('#st_colour_id option:selected').text();
        let setSize  = $('#st_set_size').val();
        let setSizeText = $('#st_set_size option:selected').text();
        let qty      = $('#st_set_qty').val();
        let pcs      = $('#st_set_size').find(':selected').data('no-of-pcs') ?? 1;

        let totalQty = qty * pcs;

        if(!lot || !orderNo || !design || !colour || !setSize || !qty){
            alert('Please fill all fields.');
            return;
        }

        $('#st_table tbody').append(`
            <tr>

                <td>${lot}
                    <input type="hidden" name="lot_no_list[]" value="${lot}">
                </td>

                <td>${orderNo}
                    <input type="hidden" name="order_no" value="${orderNo}">
                </td>

                <td>Cutting
                    <input type="hidden" name="from_stage_id[]" value="${$('#from_stage_id').val()}">
                    <input type="hidden" name="from_stage_name[]" value="${$('#from_stage_name').val()}">
                    <input type="hidden" name="from_unit_id[]" value="${$('#from_unit_id').val()}">
                    <input type="hidden" name="from_unit_name[]" value="${$('#from_unit_name').val()}">
                </td>

                <td>Stitching
                    <input type="hidden" name="to_stage_id[]" value="${$('#to_stage_id').val()}">
                    <input type="hidden" name="to_stage_name[]" value="${$('#to_stage_name').val()}">
                    <input type="hidden" name="to_unit_id[]" value="${$('#to_unit_id').val()}">
                    <input type="hidden" name="to_unit_name[]" value="${$('#to_unit_name').val()}">
                </td>

                <td>${designText}
                    <input type="hidden" name="design[]" value="${design}">
                </td>

                <td>${colourText}
                    <input type="hidden" name="colour_id[]" value="${colour}">
                </td>

                <td>${setSizeText}
                    <input type="hidden" name="set_size[]" value="${setSize}">
                </td>

                <td>${qty}
                    <input type="hidden" name="set_qty[]" value="${qty}">
                </td>

                <td>${totalQty}

                    <input type="hidden" name="individual_size[]" value="">
                    <input type="hidden" name="individual_qty[]" value="">
                </td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm st_remove">X</button>
                </td>

            </tr>
        `);


        $('#st_design').val('');
        $('#st_colour_id').val('');
        $('#st_set_size').val('');
        $('#st_set_qty').val('');
    });

    $(document).on('click','.st_remove',function(){
        $(this).closest('tr').remove();
    });


    $(document).on('change','#select_order_no',function(){
        let main_order_id = $(this).val();
        $('#design_id').html('<option value="">Loading...</option>');
        $('#roll_no').html('<option value="">Select Roll No</option>');
        $('#size_group').html('<option value="">Select Size Group</option>');

        if (!main_order_id) return;

        $.ajax({
            url: "{{ route('admin.order_digitalization.order-designs') }}",
            type: "GET",
            data: { main_order_id: main_order_id },
            success: function (response) {
                console.log(response);
                // let options = '<option value="">Select Design No</option>';
                // designs.forEach(function (design) {
                //     options += `<option value="${design}">${design}</option>`;
                // });

                // $('#design_id').html(options);
            }
        });
    });


});

</script>

<script>
    $('#pr_addRow').click(function(){

    let lot = $('#pr_lot_no').val();
    let design = $('#pr_design').val();
    let colour = $('#pr_colour_id').val();
    let set = $('#pr_set_size').val();
    let qty = $('#pr_set_qty').val();
    let pcs = $('#pr_set_size').find(':selected').data('no-of-pcs') ?? 1;
    let total = qty * pcs;

    if(!lot || !design || !colour || !set || !qty){
        alert('Please fill all fields.');
        return;
    }

    $('#pr_table tbody').append(`
        <tr>

            <td>${lot}
                <input type="hidden" name="lot_no_list[]" value="${lot}">
            </td>

            <td>Cutting
                <input type="hidden" name="from_stage_id[]" value="${$('#pr_from_stage_id').val()}">
                <input type="hidden" name="from_stage_name[]" value="${$('#pr_from_stage_name').val()}">
                <input type="hidden" name="from_unit_id[]" value="${$('#pr_from_unit_id').val()}">
                <input type="hidden" name="from_unit_name[]" value="${$('#pr_from_unit_name').val()}">
            </td>

            <td>Printing
                <input type="hidden" name="to_stage_id[]" value="${$('#pr_to_stage_id').val()}">
                <input type="hidden" name="to_stage_name[]" value="${$('#pr_to_stage_name').val()}">
                <input type="hidden" name="to_unit_id[]" value="${$('#pr_to_unit_id').val()}">
                <input type="hidden" name="to_unit_name[]" value="${$('#pr_to_unit_name').val()}">
            </td>

            <td>${$('#pr_design option:selected').text()}
                <input type="hidden" name="design[]" value="${design}">
            </td>

            <td>${$('#pr_colour_id option:selected').text()}
                <input type="hidden" name="colour_id[]" value="${colour}">
            </td>

            <td>${$('#pr_set_size option:selected').text()}
                <input type="hidden" name="set_size[]" value="${set}">
            </td>

            <td>${qty}
                <input type="hidden" name="set_qty[]" value="${qty}">
            </td>

            <td>${total}
                <input type="hidden" name="individual_size[]" value="">
                <input type="hidden" name="individual_qty[]" value="">
            </td>

            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>

        </tr>
    `);

    $('#pr_design').val('');
    $('#pr_colour_id').val('');
    $('#pr_set_size').val('');
    $('#pr_set_qty').val('');
});

</script>

<script>
    $('#em_addRow').click(function(){

        let lot = $('#em_lot_no').val();
        let design = $('#em_design').val();
        let colour = $('#em_colour_id').val();
        let set = $('#em_set_size').val();
        let qty = $('#em_set_qty').val();
        let pcs = $('#em_set_size').find(':selected').data('no-of-pcs') ?? 1;
        let total = qty * pcs;

        if(!lot || !design || !colour || !set || !qty){
            alert('Please fill all fields.');
            return;
        }

        $('#em_table tbody').append(`
            <tr>

                <td>${lot}
                    <input type="hidden" name="lot_no_list[]" value="${lot}">
                </td>

                <td>Cutting
                    <input type="hidden" name="from_stage_id[]" value="${$('#em_from_stage_id').val()}">
                    <input type="hidden" name="from_stage_name[]" value="${$('#em_from_stage_name').val()}">
                    <input type="hidden" name="from_unit_id[]" value="${$('#em_from_unit_id').val()}">
                    <input type="hidden" name="from_unit_name[]" value="${$('#em_from_unit_name').val()}">
                </td>

                <td>Embroidery
                    <input type="hidden" name="to_stage_id[]" value="${$('#em_to_stage_id').val()}">
                    <input type="hidden" name="to_stage_name[]" value="${$('#em_to_stage_name').val()}">
                    <input type="hidden" name="to_unit_id[]" value="${$('#em_to_unit_id').val()}">
                    <input type="hidden" name="to_unit_name[]" value="${$('#em_to_unit_name').val()}">
                </td>

                <td>${$('#em_design option:selected').text()}
                    <input type="hidden" name="design[]" value="${design}">
                </td>

                <td>${$('#em_colour_id option:selected').text()}
                    <input type="hidden" name="colour_id[]" value="${colour}">
                </td>

                <td>${$('#em_set_size option:selected').text()}
                    <input type="hidden" name="set_size[]" value="${set}">
                </td>

                <td>${qty}
                    <input type="hidden" name="set_qty[]" value="${qty}">
                </td>

                <td>${total}
                    <input type="hidden" name="individual_size[]" value="">
                    <input type="hidden" name="individual_qty[]" value="">
                </td>

                <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>

            </tr>
        `);

        $('#em_design').val('');
        $('#em_colour_id').val('');
        $('#em_set_size').val('');
        $('#em_set_qty').val('');
    });

</script>
@endsection
