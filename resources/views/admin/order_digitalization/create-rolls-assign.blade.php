@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="mb-3">Fabric Rolls Assigning</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-right">
                @if(!empty($skip_slip_data))
                    <form action="{{ route('admin.order_digitalization.add-skip-slip') }}"
                        method="POST"
                        class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info mr-2">
                            Add Skip slips for Digitalization (Available - {{$skip_slip_data}} Slips)
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.order_digitalization.create-slips-production') }}" class="btn btn-success mr-2">
                    Slips Digitalization
                </a>
                @if(!empty($slip_data['from_stage']['master_stage_id']) && $slip_data['from_stage']['master_stage_id'] == 3 )
                <a href="{{ route('admin.order_digitalization.create-time-allocation') }}" class="btn btn-success mr-2">
                    Stage Time Allocation
                </a>
                @endif
                @if(!empty($slip_data))

                    <!-- SKIP FORM -->
                    <form action="{{ route('admin.order_digitalization.skip') }}"
                        method="POST"
                        class="d-inline">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn btn-secondary mr-2">
                            Skip
                        </button>
                    </form>

                    <!-- DELETE FORM -->
                    <form action="{{ route('admin.order_digitalization.delete-slip') }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirmDeleteSlip();">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn btn-danger mr-2">
                            Delete
                        </button>
                    </form>

                @endif
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">
                @if(!empty($slip_data))
                <form method="POST" id="rollAssignForm" action="{{ route('admin.order_digitalization.store-rolls-assign') }}">
                    @csrf

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">
                            <div class="card p-3 mb-3 border">

                                <label>Date - {{ getformatDateTime($slip_data['date_time']) }}</label>
                                <input type="hidden" name="slip_create_date_time"
                                       value="{{ $slip_data['date_time'] }}">

                                <label>Order No.</label>
                                <input type="text" id="order_no" class="form-control mb-2">

                                <!-- LOT NO -->
                                <div class="lot-input-wrapper my-3 lot-inline">
                                    <label class="lot-input-label">Lot No.</label>
                                    <input type="text" id="lot_no" class="lot-input"
                                           placeholder="Enter Lot Number"
                                           inputmode="numeric"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <small class="text-danger" id="err_lot_no"></small>

                                <!-- TO -->
                                <label>Cutting Master</label>
                                <select id="to_master_unit" class="form-control select2 mb-1">
                                    <option value="">Select Cutting Master</option>
                                    @foreach($cutting_units as $unit)
                                        <option value="{{ $unit['id'] }}">
                                            {{ $unit['cutting_master_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger" id="err_cutting_unit"></small>
                            </div>

                            <!-- ADD ROLL -->
                            <div class="card p-3 border">
                                <h5>Add Roll</h5>

                                <label>Roll No</label>
                                <input type="text" id="roll_no" class="form-control mb-1">
                                <small class="text-danger" id="err_roll_no"></small>

                                <label class="mt-2">Meter</label>
                                <input type="number" id="meter" class="form-control mb-1" step="0.01">
                                <small class="text-danger" id="err_meter"></small>

                                <button type="button" class="btn btn-primary mt-3 btn-block add-roll">
                                    + Add Roll
                                </button>
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">
                            <div class="card p-3 border">
                                <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}"
                                     class="img-fluid rounded">
                            </div>
                        </div>

                        <!-- TABLE -->
                        <div class="col-md-12 mt-3">
                            <div class="card p-3 border">
                                <h5>Added Rolls</h5>
                                <table class="table table-bordered" id="productList">
                                    <thead>
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Order No</th>
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
                        </div>

                    </div>

                    <!-- BUTTONS -->
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                            <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">

                            <button type="submit" id="submit" class="btn btn-success">
                                Submit
                            </button>

                        </div>
                    </div>

                </form>
                @else
                    <div class="alert alert-info text-center">
                        No Production Slips Available
                    </div>
                @endif
            </div>

        </div>
    </section>
</div>

<style>
.lot-inline{display:flex;align-items:center;gap:15px}
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px}
.lot-input-label{font-weight:900;font-size:18px}
.lot-input{flex:1;padding:12px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}
</style>

<script>
$(function(){
    $('#rollAssignForm').on('submit', function (e) {

        if ($('#productList tbody tr').not('#noDataRow').length === 0) {
            alert('Please add at least one design before submitting.');
            e.preventDefault();
            return false;
        }

        if ($.trim($('#order_no').val()) === '') {
            alert('Order number is mandatory.');
            $('#order_no').focus();
            e.preventDefault();
            return false;
        }
    });
    
    $('.select2').select2();
    let isSkip = false;

    function clearErrors(){
        $('.text-danger').text('');
    }

    $('#skipBtn').click(function(){
        isSkip = true;
        $('#skip_action').val(1);
        $('form').submit();
    });

    $('.add-roll').click(function(){

        clearErrors();

        let lotNo   = $('#lot_no').val().trim();
        let orderNo = $('#order_no').val().trim();
        let cutting = $('#to_master_unit').val();
        let cuttingText = $('#to_master_unit option:selected').text();
        let rollNo  = $('#roll_no').val().trim();
        let meter   = $('#meter').val();

        let valid = true;

        if(!lotNo){ $('#err_lot_no').text('Lot No is required'); valid=false; }
        if(!cutting){ $('#err_cutting_unit').text('Cutting Master required'); valid=false; }
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
                <td>${orderNo}<input type="hidden" name="order_no_list[]" value="${orderNo}"></td>
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

    // $('form').submit(function(e){
    //     if(isSkip) return true;

    //     if($('#productList tbody tr').not('#noDataRow').length === 0){
    //         alert('Please add at least one roll');
    //         e.preventDefault();
    //     }
    // });

});
</script>
@endsection
