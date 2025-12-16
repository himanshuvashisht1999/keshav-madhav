@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-center">Fabric Rolls Assigning</h1>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card p-3 shadow-sm">
                @if(!empty($slip_data))
                    <form method="POST"
                        action="{{ route('admin.order_digitalization.store') }}"
                        enctype="multipart/form-data">
                        @csrf

                        <!-- SKIP FLAG -->
                        <input type="hidden" name="skip_action" id="skip_action" value="0">

                        <div class="row">

                            <!-- LEFT -->
                            <div class="col-md-6">
                                <div class="card p-3 mb-3 border">

                                    <label>Date - {{ getformatDateTime($slip_data['date_time']) }}</label>
                                    <input type="hidden" id="slip_create_date_time" name="slip_create_date_time" value="{{ $slip_data['date_time'] }}">

                                    <label>Order No (Optional)</label>
                                    <input type="text" id="order_no" name="order_no" class="form-control mb-2">

                                    <div class="lot-input-wrapper my-3 lot-inline">
                                        <label class="lot-input-label">Lot No.</label>
                                        <input type="text" name="lot_no" class="lot-input"
                                            placeholder="Enter Lot Number"
                                            required inputmode="numeric"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>

                                    {{-- FROM --}}
                                    {{-- <label>From</label>
                                    <select class="form-control mb-2" id="from_stage" readonly>
                                        <option>
                                            {{ $slip_data['from_stage']['name'] }}
                                            ({{ $slip_data['from_stage']['master_stage_name'] }})
                                        </option>
                                    </select>

                                    {{-- FROM hidden --}}
                                    {{-- <input type="hidden" id="from_stage_id" value="{{ $slip_data['from_stage']['master_stage_id'] }}">
                                    <input type="hidden" id="from_stage_name" value="{{ $slip_data['from_stage']['master_stage_name'] }}">
                                    <input type="hidden" id="from_unit_id" value="{{ $slip_data['from_stage']['id'] }}">
                                    <input type="hidden" id="from_unit_name" value="{{ $slip_data['from_stage']['name'] }}">  --}}

                                    {{-- TO --}}
                                    <label>To</label>
                                    <select id="to_master_unit" name="to_master_unit" class="form-control select2 mb-2">
                                        <option value="">Select Stage</option>
                                        @foreach($cutting_units as $unit)
                                            <option data-unit-id="{{ $unit['id'] }}">
                                                {{ $unit['cutting_master_name'] }} 
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- ADD ROLL -->
                                <div class="card p-3 border">
                                    <h5>Add Roll</h5>

                                    <label>Roll No</label>
                                    <input type="text" id="roll_no" class="form-control mb-1">
                                    <small class="text-danger error" id="err_roll_no"></small>

                                    <label class="mt-2">Meter</label>
                                    <input type="number" id="meter" class="form-control mb-1" step="0.01">
                                    <small class="text-danger error" id="err_meter"></small>

                                    <button type="button"
                                            class="btn btn-primary mt-3 btn-block add-roll">
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
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <!-- BUTTONS -->
                        <div class="row mt-3">
                            <div class="col-6">
                                <button type="button" id="skipBtn" class="btn btn-secondary">
                                    Skip
                                </button>
                            </div>
                            <div class="col-6 text-right">
                                <button type="submit" class="btn btn-success">
                                    Submit
                                </button>
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
<!-- ================= JS ================= -->
<script>
$(document).ready(function () {

    $('.select2').select2();

    let isSkip = false;

    function clearErrors() {
        $('.error').text('');
    }

    /* ================= FILE PREVIEW ================= */
    $('#slip_file').on('change', function (e) {

        let file = e.target.files[0];
        if (!file) return;

        $('#previewImg').hide().attr('src','');
        $('#pdfPreview').html('');

        if (file.type.startsWith('image/')) {
            let reader = new FileReader();
            reader.onload = () => $('#previewImg').attr('src', reader.result).show();
            reader.readAsDataURL(file);
        }
        else if (file.type === 'application/pdf') {
            let url = URL.createObjectURL(file);
            $('#pdfPreview').html(`
                <embed src="${url}" type="application/pdf"
                       width="100%" height="500px">
            `);
        }
        else {
            alert('Only PDF or Image allowed');
            $(this).val('');
        }
    });

    /* ================= SKIP ================= */
    $('#skipBtn').on('click', function () {
        isSkip = true;
        $('#skip_action').val(1);
        $('form').submit();
    });

    /* ================= ADD ROLL ================= */
    $('.add-roll').on('click', function () {

        clearErrors();

        let orderNo = $('#order_no').val().trim();
        let lotNo   = $('#lot_no').val().trim();
        let cutting = $('#cutting_unit').val();
        let cuttingText = $('#cutting_unit option:selected').text();
        let rollNo  = $('#roll_no').val().trim();
        let meter   = $('#meter').val();

        let valid = true;

        if (!lotNo) {
            $('#err_lot_no').text('Lot No is required');
            valid = false;
        }
        if (!cutting) {
            $('#err_cutting_unit').text('Cutting Master is required');
            valid = false;
        }
        if (!rollNo) {
            $('#err_roll_no').text('Roll No is required');
            valid = false;
        }
        if (!meter || meter <= 0) {
            $('#err_meter').text('Meter must be greater than 0');
            valid = false;
        }

        // Duplicate Roll Check
        $('input[name="roll_no_list[]"]').each(function () {
            if ($(this).val() === rollNo) {
                $('#err_roll_no').text('Roll No already added');
                valid = false;
            }
        });

        if (!valid) return;

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

        $('#roll_no').val('');
        $('#meter').val('');
    });

    /* ================= REMOVE ROW ================= */
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    });

    /* ================= SUBMIT VALIDATION ================= */
    $('form').on('submit', function (e) {

        // IF SKIP → NO VALIDATION
        if (isSkip) {
            return true;
        }

        if ($('#productList tbody tr').length === 0) {
            alert('Please add at least one roll');
            e.preventDefault();
            return false;
        }
    });

});
</script>
@endsection
