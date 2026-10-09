@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="erp-page p-2">

        <!-- HEADER BAR -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title d-flex align-items-center flex-wrap">
                <i class="fas fa-scroll mr-2 text-success"></i>
                <span>Fabric Rolls Assigning</span>
                @if(!empty($slip_data))
                    <span class="badge ml-2 px-2 py-1 font-weight-bold" style="background:#edf7e4; color:#05421c; border:1px solid #c3e6cb; font-size:12px;">
                        Slip #{{ $slip_data['id'] }}
                    </span>
                @endif
            </div>
            <div class="erp-header-actions d-flex align-items-center flex-wrap" style="gap: 6px;">
                @if(!empty($skip_slip_data))
                    <form action="{{ route('admin.order_digitalization.add-skip-slip') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn-erp btn-erp-outline btn-sm">
                            <i class="fas fa-redo mr-1"></i> Add Skipped Slips ({{$skip_slip_data}})
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.order_digitalization.create-slips-production') }}" class="btn-erp btn-erp-outline btn-sm">
                    <i class="fas fa-file-signature mr-1"></i> Slips Digitalization
                </a>
                @if(!empty($slip_data['from_stage']['master_stage_id']) && $slip_data['from_stage']['master_stage_id'] == 3 )
                    <a href="{{ route('admin.order_digitalization.create-time-allocation') }}" class="btn-erp btn-erp-outline btn-sm">
                        <i class="fas fa-clock mr-1"></i> Stage Time Allocation
                    </a>
                @endif
                @if(!empty($slip_data))
                    <form action="{{ route('admin.order_digitalization.skip') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn-erp btn-erp-outline btn-sm">
                            <i class="fas fa-forward mr-1"></i> Skip
                        </button>
                    </form>
                    <form action="{{ route('admin.order_digitalization.delete-slip') }}" method="POST" class="d-inline m-0" onsubmit="return confirmDeleteSlip();">
                        @csrf
                        <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                        <button type="submit" class="btn-erp btn-erp-danger btn-sm">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(!empty($slip_data))
            <form method="POST" id="rollAssignForm" action="{{ route('admin.order_digitalization.store-rolls-assign') }}">
                @csrf

                <div class="row">
                    <!-- LEFT PANEL -->
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="erp-card bg-white p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle mr-2 text-success"></i>
                                    <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Slip & Lot Information</h6>
                                </div>
                                <span class="badge badge-light border text-xs">
                                    Date: {{ getformatDateTime($slip_data['date_time']) }}
                                </span>
                            </div>

                            <input type="hidden" name="slip_create_date_time" value="{{ $slip_data['date_time'] }}">

                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Order No.</label>
                                <input type="text" id="order_no" class="form-control erp-input" placeholder="Enter Order Number">
                            </div>

                            <!-- LOT NO -->
                            <div class="form-group mb-2">
                                <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Lot Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-layer-group text-muted"></i></span>
                                    </div>
                                    <input type="text" id="lot_no" class="form-control erp-input border-left-0 font-weight-bold"
                                           placeholder="Enter Lot Number"
                                           inputmode="numeric"
                                           oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <small class="text-danger" id="err_lot_no"></small>
                            </div>

                            <!-- CUTTING MASTER -->
                            <div class="form-group mb-0">
                                <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Cutting Master <span class="text-danger">*</span></label>
                                <select id="to_master_unit" class="form-control select2 mb-1" style="width: 100%;">
                                    <option value="">Select Cutting Master</option>
                                    @foreach($cutting_units as $unit)
                                        <option value="{{ $unit['id'] }}">
                                            {{ $unit['cutting_master_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger" id="err_cutting_unit"></small>
                            </div>
                        </div>

                        <!-- ADD ROLL CARD -->
                        <div class="erp-card bg-white p-3">
                            <div class="d-flex align-items-center pb-2 mb-3 border-bottom">
                                <i class="fas fa-plus-circle mr-2 text-success"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Add Roll Item</h6>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Roll No <span class="text-danger">*</span></label>
                                    <input type="text" id="roll_no" class="form-control erp-input" placeholder="e.g. 101">
                                    <small class="text-danger" id="err_roll_no"></small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="text-xs font-weight-bold text-muted text-uppercase mb-1">Meter <span class="text-danger">*</span></label>
                                    <input type="number" id="meter" class="form-control erp-input font-weight-bold text-success" step="0.01" placeholder="0.00">
                                    <small class="text-danger" id="err_meter"></small>
                                </div>
                            </div>

                            <button type="button" class="btn-erp btn-erp-primary btn-block add-roll mt-2 py-2">
                                <i class="fas fa-plus mr-1"></i> Add Roll to List
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: SLIP IMAGE -->
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="erp-card bg-white p-3 text-center" style="min-height: 100%;">
                            <div class="d-flex align-items-center pb-2 mb-3 border-bottom">
                                <i class="fas fa-image mr-2 text-success"></i>
                                <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Attached Slip Document</h6>
                            </div>
                            <div class="p-2 bg-light rounded border text-center">
                                <img src="{{ asset('assets/production_slips/'.$slip_data['slip_file']) }}"
                                     class="img-fluid rounded" style="max-height: 380px; object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    <!-- ADDED ROLLS TABLE -->
                    <div class="col-12 mb-3">
                        <div class="erp-card bg-white p-3">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-list mr-2 text-success"></i>
                                    <h6 class="mb-0 font-weight-bold" style="color: #05421c;">Assigned Rolls Queue</h6>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table erp-table table-hover mb-0" id="productList">
                                    <thead>
                                        <tr>
                                            <th>Lot No</th>
                                            <th>Order No</th>
                                            <th>Cutting Master</th>
                                            <th>Roll No</th>
                                            <th>Meter</th>
                                            <th style="width: 80px;" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="noDataRow">
                                            <td colspan="6" class="text-center text-muted py-3">
                                                <i class="fas fa-inbox mr-1"></i> No rolls added yet
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end align-items-center mt-3 pt-3 border-top">
                                <input type="hidden" name="production_slip_digitization_id" value="{{ $slip_data['id'] }}">
                                <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">

                                <button type="submit" id="submit" class="btn-erp btn-erp-primary px-4 py-2">
                                    <i class="fas fa-check-double mr-1"></i> Submit Rolls Assignment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        @else
            <div class="erp-card bg-white p-5 text-center">
                <i class="fas fa-file-invoice text-muted mb-3" style="font-size: 40px;"></i>
                <h5 class="font-weight-bold text-dark">No Production Slips Available</h5>
                <p class="text-muted">There are currently no slips available for fabric roll assigning.</p>
                <a href="{{ route('admin.uploaded-slips.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Return to Slips List
                </a>
            </div>
        @endif

    </div>
</div>

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
    
    if($.fn.select2) {
        $('.select2').select2();
    }
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
                <td class="font-weight-bold align-middle">${lotNo}<input type="hidden" name="lot_no_list[]" value="${lotNo}"></td>
                <td class="align-middle">${orderNo}<input type="hidden" name="order_no_list[]" value="${orderNo}"></td>
                <td class="align-middle">${cuttingText}<input type="hidden" name="cutting_unit_list[]" value="${cutting}"></td>
                <td class="align-middle"><span class="badge badge-light border">Roll #${rollNo}</span><input type="hidden" name="roll_no_list[]" value="${rollNo}"></td>
                <td class="font-weight-bold text-success align-middle">${meter} m<input type="hidden" name="meter_list[]" value="${meter}"></td>
                <td class="text-center align-middle">
                    <button type="button" class="erp-action-btn erp-btn-delete remove-row" title="Remove Roll">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `);

        $('#roll_no,#meter').val('');
    });

    $(document).on('click','.remove-row',function(){
        $(this).closest('tr').remove();
        if($('#productList tbody tr').length === 0){
            $('#productList tbody').html(`
                <tr id="noDataRow">
                    <td colspan="6" class="text-center text-muted py-3">
                        <i class="fas fa-inbox mr-1"></i> No rolls added yet
                    </td>
                </tr>
            `);
        }
    });

});
</script>
@endsection
