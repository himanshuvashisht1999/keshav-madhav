@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="mb-3">Production Slips Digitalization</h1>
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
                    <a href="{{ route('admin.order_digitalization.create-rolls-assign') }}" class="btn btn-primary mr-2">
                        Rolls Digitalization
                    </a>
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

            <hr class="mt-3 mb-0">
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card p-3 shadow-sm">

                @if(!empty($slip_data))
                    <div id="slip_digitalization" >
                        <form method="POST" action="{{ route('admin.order_digitalization.store-slip') }}">
                        @csrf

                        <div class="row">

                            {{-- LEFT --}}
                            <div class="col-md-6">

                                <div class="card p-3 mb-3 border">
                                    <label>Date - {{ getformatDateTime($slip_data['date_time']) }}</label>
                                    <input type="hidden" id="slip_create_date_time" name="slip_create_date_time" value="{{ $slip_data['date_time'] }}">
                                    <label>Order No.</label>
                                    <input type="text" id="order_no" name="order_no" class="form-control mb-2" required>
                                    {{-- LOT NO --}}
                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text" name="lot_no" class="lot-input"
                                            placeholder="Enter Lot Number"
                                            required inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>

                                    {{-- FROM --}}
                                    <label>From</label>
                                    <select class="form-control mb-2" id="from_stage" readonly>
                                        <option>
                                            {{ $slip_data['from_stage']['name'] }}
                                            ({{ $slip_data['from_stage']['master_stage_name'] }})
                                        </option>
                                    </select>

                                    {{-- FROM hidden --}}
                                    <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">
                                    <input type="hidden" id="from_stage_name" value="{{ $slip_data['from_stage']['master_stage_name'] }}">
                                    <input type="hidden" id="from_unit_id" value="{{ $slip_data['from_stage']['id'] }}">
                                    <input type="hidden" id="from_unit_name" value="{{ $slip_data['from_stage']['name'] }}">

                                    {{-- TO --}}
                                    <label>To</label>
                                    <select id="to_stage_main" class="form-control select2 mb-2">
                                        <option value="">Select Stage</option>
                                        @foreach($slip_data['unit_master_data'] as $unit)
                                            <option
                                                data-unit-id="{{ $unit['id'] }}"
                                                data-unit-name="{{ $unit['name'] }}"
                                                data-stage-id="{{ $unit['master_stage_id'] }}"
                                                data-stage-name="{{ $unit['master_stage_name'] }}">
                                                {{ $unit['name'] }} ({{ $unit['master_stage_name'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- ADD DESIGN --}}
                                <div class="card p-3 border">
                                    <h5>Add Design Number</h5>

                                    <label>Design No.</label>
                                    <input type="text" id="design_input" class="form-control mb-2">

                                    <label>Colour</label>
                                    <select id="colour_id" class="form-control mb-3">
                                        <option value="">Select Colour</option>
                                        @foreach($colours as $colour)
                                            <option value="{{ $colour->id }}">{{ $colour->sku }}</option>
                                        @endforeach
                                    </select>

                                    <label class="fw-bold mb-2">Size Type</label>
                                    <input type="hidden" id="size_type" value="set">

                                    <div class="size-toggle mb-2">
                                        <button type="button" class="size-btn active" data-type="set" data-target="setBox">
                                            Set Size
                                        </button>
                                        <button type="button" class="size-btn" data-type="single" data-target="singleBox">
                                            Individual Size
                                        </button>
                                    </div>

                                    <div id="setBox" class="size-box set-theme">
                                        <label>Set Size</label>
                                        {{-- <input type="hidden" id="set_size" class="form-control mb-2"> --}}
                                        <select class="form-control select2 mb-2 design-input" name="set_size" id="set_size">
                                            
                                            <option value="">Select</option>
                                            @foreach($product_size as $set_size)
                                                <option value="{{ $set_size->id }}">
                                                    {{ $set_size->set_size }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('set_size'))
                                            <span class="invalid-feedback d-block">
                                                {{ $errors->first('set_size') }}
                                            </span>
                                        @endif

                                        <label>Set Quantity</label>
                                        <input type="number" id="set_qty" class="form-control" min="1">
                                    </div>

                                    <div id="singleBox" class="size-box single-theme d-none">
                                        <label>Individual Size</label>
                                        <input type="text" id="single_size" class="form-control mb-2">

                                        <label>Individual Quantity</label>
                                        <input type="number" id="single_qty" class="form-control" min="1">
                                    </div>

                                    <button type="button" class="btn btn-primary addLot w-100 mt-3">
                                        + Add Another Set
                                    </button>
                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="col-md-6">
                                <div class="card p-3 border">
                                    <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}"
                                        class="img-fluid rounded">
                                </div>
                            </div>

                            {{-- TABLE --}}
                            <div class="col-md-12 mt-3">
                                <div class="card p-3 border">
                                    <table class="table table-bordered" id="productTable">
                                        <thead>
                                            <tr>
                                                <th>Lot</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Design</th>
                                                <th>Colour</th>
                                                <th>Size Type</th>
                                                <th>Set Size</th>
                                                <th>Set Qty</th>
                                                <th>Individual Size</th>
                                                <th>Individual Qty</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-right">
                                <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                                <input type="hidden" id="remark" name="remark">
                                <input type="hidden" id="hidden_allowed_time" name="allowed_time">
                                <input type="hidden" id="hidden_time_type" name="time_type">
                                <input type="hidden" id="hidden_allowed_till" name="allowed_till_datetime">
                                {{-- <button type="submit" class="btn btn-success">Submit</button> --}}
                                <button type="button" class="btn btn-success" id="openConfirmModal">
                                    Submit
                                </button>
                            </div>
                        </div>

                        </form>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        No Production Slips Available for Digitalization
                    </div>
                @endif

            </div>
        </div>
    </section>
</div>
<!-- CONFIRM SUBMIT MODAL -->
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Process Time Allowed</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    &times;
                </button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- TIME TYPE -->
                <div class="form-group">
                    <label class="font-weight-bold">Time Type</label>
                    <select name="time_type" id="time_type" class="form-control">
                        <option value="">Select Time Type</option>
                        <option value="hours">Hours</option>
                        <option value="days">Days</option>
                    </select>
                </div>

                <!-- ALLOWED TIME -->
                <div class="form-group">
                    <label class="font-weight-bold" id="allowed_time_label">
                        Allowed Time
                    </label>
                    <input type="number"
                           class="form-control"
                           id="allowed_time"
                           name="allowed_time"
                           placeholder="Enter Allowed Time"
                           min="1">
                </div>

                <!-- REMARK -->
                <div class="form-group">
                    <label class="font-weight-bold">Remarks</label>
                    <input type="text"
                           class="form-control"
                           id="final_remark"
                           name="final_remark"
                           placeholder="Enter remark (optional)">
                </div>
                <input type="hidden"
                           class="form-control"
                           id="till_allowed_time"
                           name="till_allowed_time">
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmFinalSubmit">
                    Confirm & Submit
                </button>
            </div>

        </div>
    </div>
</div>


{{-- STYLES --}}
<style>
.size-toggle{display:flex;gap:10px}
.size-btn{flex:1;padding:10px;border-radius:20px;border:2px solid #ccc;font-weight:700}
.size-btn.active[data-type="set"]{background:#0d6efd;color:#fff}
.size-btn.active[data-type="single"]{background:#198754;color:#fff}
.size-box{padding:10px;border-radius:8px}
.set-theme{border:3px solid #0d6efd}
.single-theme{border:3px solid #198754}

.lot-inline{display:flex;align-items:center;gap:15px}
.lot-input-wrapper{background:#f8f9fa;border:2px solid #28a745;border-radius:10px;padding:10px}
.lot-input-label{font-weight:900;font-size:18px}
.lot-input{flex:1;padding:12px;font-size:20px;font-weight:700;border:2px dashed #28a745;border-radius:6px;text-align:center}
</style>

{{-- JS --}}
<script>
$(function(){

    $('#openConfirmModal').on('click', function(){
        if($('#productTable tbody tr').length === 0){
            alert(' Please add at least one design before submitting.');
            return;
        }
        if($('#from_stage_id').val() === 3 && $('#order_no').val() === ''){
            alert('Order number is mandatory.');
            return;
        }
        $('#confirmSubmitModal').modal('show');
    });

    $('#time_type, #allowed_time').on('change keyup', function () {
        calculateAllowedTill();
    });

    // Final submit
    $('#confirmFinalSubmit').on('click', function(){
        calculateAllowedTill();
       // ✅ count only real data rows
        let dataRowCount = $('#productTable tbody tr')
            .not('#noDataRow')
            .length;

        if (dataRowCount === 0) {
            alert('Please add at least one design before submitting.');
            return;
        }

        let timeType     = $('#time_type').val();
        let allowedTime  = $('#allowed_time').val();
        let remark       = $('#final_remark').val().trim();

        if (!timeType) {
            alert('Please select Time Type (Hours / Days)');
            return;
        }

        if (!allowedTime || allowedTime <= 0) {
            alert('Please enter valid allowed time');
            return;
        }

        // ✅ copy modal values to hidden form fields
        $('#remark').val(remark);
        $('#hidden_allowed_time').val(allowedTime);
        $('#hidden_time_type').val(timeType);

        // ✅ submit
        $('#confirmSubmitModal').modal('hide');
        $('#slip_digitalization form').submit();
    });

    $('.select2').select2();

    $('.size-btn').click(function(){
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
        $('#size_type').val($(this).data('type'));
        $('.size-box').addClass('d-none');
        $('#'+$(this).data('target')).removeClass('d-none');
    });

    $('.addLot').click(function(){

        let lotNo = $('input[name="lot_no"]').val();

        let fromStageText = $('#from_stage option:selected').text();
        let fromStageId   = $('#from_stage_id').val();
        let fromStageName = $('#from_stage_name').val();
        let fromUnitId    = $('#from_unit_id').val();
        let fromUnitName  = $('#from_unit_name').val();

        let $to = $('#to_stage_main option:selected');
        let toUnitId    = $to.data('unit-id');
        let toUnitName  = $to.data('unit-name');
        let toStageId   = $to.data('stage-id');
        let toStageName = $to.data('stage-name');
        let toText      = $to.text();

        let design     = $('#design_input').val();
        let colourText = $('#colour_id option:selected').text();
        let colourVal  = $('#colour_id').val();

        let type = $('#size_type').val();
        let size = type==='set' ? $('#set_size').val() : $('#single_size').val();
        let qty  = type==='set' ? $('#set_qty').val()  : $('#single_qty').val();
        let set_sizeText = $('#set_size option:selected').text();
        if(!lotNo || !toUnitId || !design || !colourVal || !size || !qty){
            alert('Please fill all fields');
            return;
        }

        let setSize = type==='set'?size:'-';
        let setQty  = type==='set'?qty:'-';
        let indSize = type==='single'?size:'-';
        let indQty  = type==='single'?qty:'-';

        $('#productTable tbody').append(`
        <tr>
            <td>${lotNo}<input type="hidden" name="lot_no_list[]" value="${lotNo}"></td>

            <td>
                ${fromStageText}
                <input type="hidden" name="from_stage_id[]" value="${fromStageId}">
                <input type="hidden" name="from_stage_name[]" value="${fromStageName}">
                <input type="hidden" name="from_unit_id[]" value="${fromUnitId}">
                <input type="hidden" name="from_unit_name[]" value="${fromUnitName}">
            </td>

            <td>
                ${toText}
                <input type="hidden" name="to_stage_id[]" value="${toStageId}">
                <input type="hidden" name="to_stage_name[]" value="${toStageName}">
                <input type="hidden" name="to_unit_id[]" value="${toUnitId}">
                <input type="hidden" name="to_unit_name[]" value="${toUnitName}">
            </td>

            <td>${design}<input type="hidden" name="design[]" value="${design}"></td>
            <td>${colourText}<input type="hidden" name="colour_id[]" value="${colourVal}"></td>
            <td>${type}</td>
            <!-- SET SIZE -->
            <td>
                ${type === 'set' ? set_sizeText : '-'}
                <input type="hidden" name="set_size[]" value="${type === 'set' ? size : ''}">
            </td>

            <!-- SET QTY -->
            <td>
                ${type === 'set' ? qty : '-'}
                <input type="hidden" name="set_qty[]" value="${type === 'set' ? qty : ''}">
            </td>

            <!-- INDIVIDUAL SIZE -->
            <td>
                ${type === 'single' ? size : '-'}
                <input type="hidden" name="individual_size[]" value="${type === 'single' ? size : ''}">
            </td>

            <!-- INDIVIDUAL QTY -->
            <td>
                ${type === 'single' ? qty : '-'}
                <input type="hidden" name="individual_qty[]" value="${type === 'single' ? qty : ''}">
            </td>

            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
        </tr>
        `);
        $('#set_size').val(null).trigger('change');
        $('#set_qty').val('');

        $('#single_size').val('');
        $('#single_qty').val('');
    });

    $(document).on('click','.remove',function(){
        $(this).closest('tr').remove();
    });

});

function confirmDeleteSlip() {
    return confirm('Are you sure you want to delete this slip?');
}

function calculateAllowedTill() {
    let type  = $('#time_type').val();
    let value = parseInt($('#allowed_time').val());
    if (!type || !value || value <= 0) {
        $('#hidden_allowed_till').val('');
        return;
    }

    let now = new Date();

    if (type === 'hours') {
        now.setHours(now.getHours() + value);
    }

    if (type === 'days') {
        now.setDate(now.getDate() + value);
    }

    // format for backend: YYYY-MM-DD HH:mm:ss
    let formatted =
        now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + ' ' +
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0') + ':00';

    $('#hidden_allowed_till').val(formatted);
    $('#till_allowed_time').val(formatted);
    
}

</script>
@endsection
