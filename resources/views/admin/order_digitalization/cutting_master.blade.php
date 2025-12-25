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
</style>

<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid text-center">
            <h2 class="mb-3">Production Slip – Cutting Master</h2>

            {{-- ACTION BUTTONS --}}
            <div class="action-btn-group mb-3">
                <button class="btn btn-outline-primary action-btn active" data-target="rolls">
                    Rolls Allot
                </button>
                <button class="btn btn-outline-primary action-btn" data-target="time">
                    Time Allocation
                </button>
                <button class="btn btn-outline-primary action-btn" data-target="stitching">
                    Send to Stitching
                </button>
                <button class="btn btn-outline-primary action-btn" data-target="printing">
                    Send to Printing
                </button>
                <button class="btn btn-outline-primary action-btn" data-target="embroidery">
                    Send to Embroidery
                </button>
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

                                <h5 class="card-title mb-3">Fabric Rolls Assigning</h5>

                                <form method="POST"
                                    id="rollAssignForm"
                                    action="{{ route('admin.order_digitalization.store-rolls-assign') }}">
                                    @csrf

                                    <label>Date - {{ getformatDateTime($cutting_slip->created_at) }}</label>

                                    <input type="hidden"
                                        name="slip_create_date_time"
                                        value="{{ $cutting_slip->created_at }}">

                                    {{-- LOT --}}
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
                                    <label>Cutting Master ({{$master_fabric_warehouse->cutting_master_name}})</label>
                                    <input type="hidden" name="to_master_unit" value="{{$master_fabric_warehouse->id}}">
                                    

                                    {{-- ADD ROLL --}}
                                    <div class="card p-2 mt-3 border">
                                        <h6>Add Roll</h6>

                                        <label>Roll No</label>
                                        <input type="text" id="roll_no" class="form-control mb-1">
                                        <small class="text-danger" id="err_roll_no"></small>

                                        <label class="mt-2">Meter</label>
                                        <input type="number" id="meter" class="form-control mb-1" step="0.01">
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
                                <h5 class="card-title mb-3">Stage Wise Time Allocation</h5>

                                <form method="POST"
                                    action="{{ route('admin.order_digitalization.store-time-allocation') }}">
                                    @csrf

                                    <label>Date - {{ getformatDateTime($cutting_slip->created_at) }}</label>

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

                            

                        </div>
                    </div>
                </div>

                {{-- RIGHT IMAGE PANEL --}}
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body text-center">
                            <img
                                src="{{ asset('assets/production_slips/'.$cutting_slip->slip_file) }}"
                                class="slip-image"
                                alt="Production Slip">
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

@endsection
