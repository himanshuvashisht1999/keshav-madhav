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

                                <label>Date</label>
                                <input type="text" class="form-control mb-2"
                                       value="{{ date('d/m/Y') }}" readonly>

                                <label>Order No (Optional)</label>
                                <input type="text" id="order_no" class="form-control mb-1">
                                <small class="text-danger error" id="err_order_no"></small>

                                <label>Lot No (Required)</label>
                                <input type="text" id="lot_no" class="form-control mb-1">
                                <small class="text-danger error" id="err_lot_no"></small>

                                <label>From (Cutting Master)</label>
                                <select id="cutting_unit" class="form-control select2 mb-1">
                                    <option value="">Select Cutting Master</option>
                                    @foreach($cutting_units as $unit)
                                        <option value="{{ $unit->id }}">
                                            {{ $unit->cutting_master_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger error" id="err_cutting_unit"></small>
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
                                <h6>Upload File (PDF / Image)</h6>

                                {{-- <input type="file"
                                       name="slip_file"
                                       id="slip_file"
                                       class="form-control mb-2"> --}}
                                <input type="hidden" name="slip_file_id" value="{{ $slip_img->id }}">
                                <input type="hidden" name="slip_file" value="{{ $slip_img->slip_file }}">
                                <img src="{{ asset('assets/production_slips/'.$slip_img->slip_file) }}"
                                class="w-100 mt-2"
                                style="border-radius:6px;">

                                <div id="pdfPreview" class="mt-2"></div>
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
            </div>

        </div>
    </section>
</div>

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
