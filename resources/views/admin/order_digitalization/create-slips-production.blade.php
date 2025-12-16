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
                    <button type="button" class="btn btn-secondary mr-2">Skip</button>
                    <button type="button" class="btn btn-primary mr-2">Rolls Digitalization</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteSlip()">Delete</button>
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
            <form method="POST" action="{{ route('admin.order_digitalization.store') }}">
            @csrf

            <div class="row">

                {{-- LEFT --}}
                <div class="col-md-6">

                    <div class="card p-3 mb-3 border">
                        <label>Date - {{ getformatDateTime($slip_data['date_time']) }}</label>
                        <input type="hidden" id="slip_create_date_time" name="slip_create_date_time" value="{{ $slip_data['date_time'] }}">
                        <label>Order No.</label>
                        <input type="text" id="order_no" class="form-control mb-2">
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
                            <input type="text" id="set_size" class="form-control mb-2">

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
                            + Add Design Number
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
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>

            </form>
            @else
                <div class="alert alert-info text-center">
                    No Production Slips Available for Digitalization
                </div>
            @endif

            </div>
        </div>
    </section>
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
                ${type === 'set' ? size : '-'}
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
        $('#set_size').val('');
        $('#set_qty').val('');

        $('#single_size').val('');
        $('#single_qty').val('');
    });

    $(document).on('click','.remove',function(){
        $(this).closest('tr').remove();
    });

});

function confirmDeleteSlip(){
    if(confirm('Are you sure you want to delete this slip?')){
        alert('Delete confirmed');
    }
}
</script>
@endsection
