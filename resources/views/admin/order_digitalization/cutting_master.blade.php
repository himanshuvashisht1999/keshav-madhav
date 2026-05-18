@extends('admin.layouts.app')

@section('content')
    <style>
        .action-btn-group .btn {
            margin-right: 6px;
        }

        .action-btn-group .btn.active {
            background-color: green;
            color: #fff;
            padding: 10px;
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

        .lot-inline {
            display: flex;
            align-items: center;
            gap: 15px
        }

        .lot-input-wrapper {
            background: #f8f9fa;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 10px
        }

        .lot-input-label {
            font-weight: 900;
            font-size: 18px
        }

        .lot-input {
            flex: 1;
            padding: 12px;
            font-size: 20px;
            font-weight: 700;
            border: 2px dashed #28a745;
            border-radius: 6px;
            text-align: center
        }

        #st_table th,
        #st_table td {
            white-space: nowrap;
        }

        #st_table td button {
            padding: 3px 8px;
        }

        .skip-btn {
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .rotate-btn {
            top: 10px;
            left: 10px;
            z-index: 10;
        }

        .slip-image {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        .image-wrapper {
            width: 100%;
            height: 75vh;
            /* image ko space mile */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .image-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transform-origin: center center;
        }

        .sticky-wrapper {
            position: -webkit-sticky;
            position: sticky;
            top: 80px;
            /* Space from top */
            z-index: 100;
        }
    </style>
    <style>
        /* default look */
        .action-btn {
            color: #fff;
            border: none;
        }

        /* individual colors */
        .btn-rolls {
            background: #6f42c1;
        }

        /* Purple */
        .btn-time {
            background: #20c997;
        }

        /* Teal/Green */
        .btn-stitching {
            background: #fd7e14;
        }

        /* Orange */
        .btn-printing {
            background: #17a2b8;
        }

        /* Cyan */
        .btn-emb {
            background: #e83e8c;
        }

        /* Pink */

        /* highlight when active */
        .action-btn.active {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, .08);
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
                <h2 class="mb-3">Production Slip – Cutting Master
                    @if($cutting_slip && $cutting_slip->status == 1)
                        <span class="badge badge-warning" style="font-size: 14px; vertical-align: middle;">Partially
                            Digitized</span>
                    @endif
                </h2>



                {{-- ACTION BUTTONS --}}
                <div class="action-btn-group mb-3">
                    <button class="btn action-btn btn-rolls active" data-target="rolls">
                        Rolls Allot
                    </button>

                    <button class="btn action-btn btn-stitching" data-target="stitching">
                        Send to Stitching
                    </button>

                    <button class="btn action-btn btn-printing" data-target="printing">
                        Send to Printing
                    </button>

                    @if(request('is_skip') == 1)
                        <!-- <a href="{{ route('admin.order_digitalization.cutting-master') }}"
                                                                                                                                                                                                                        class="btn btn-secondary">
                                                                                                                                                                                                                            View Normal Slips
                                                                                                                                                                                                                        </a> -->


                    @else
                        <!-- <a href="{{ route('admin.order_digitalization.cutting-master', ['is_skip' => 1]) }}"
                                                                                                                                                                                                                        class="btn btn-secondary">
                                                                                                                                                                                                                            View Skipped Slips
                                                                                                                                                                                                                        </a> -->
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

                                        <form method="POST" id="rollAssignForm"
                                            action="{{ route('admin.order_digitalization.store-rolls-assign') }}">
                                            @csrf
                                            <input type="hidden" name="is_final" class="is_final_input">
                                            <input type="hidden" name="production_slip_digitization_id"
                                                value="{{ $cutting_slip->id ?? '' }}">

                                            <!-- <label>(Date - {{ getformatDateTime($cutting_slip->created_at) }})</label> -->

                                            <input type="hidden" name="slip_create_date_time"
                                                value="{{ $cutting_slip->created_at ?? '' }}">

                                            {{-- LOT --}}
                                            <div class="card p-2 mt-3 border">
                                                <label class="font-weight-bold">
                                                    Production Date & Time <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="production_datetime"
                                                    class="form-control datetime-picker" placeholder="Select date & time"
                                                    required>
                                            </div>
                                            <div class="card p-2 mt-3 border">
                                                <label class="font-weight-bold">Select CMPO (Assignment) <span class="text-muted small">(Optional)</span></label>
                                                <select id="select_cmpo" name="cmpo_id" class="form-control mb-2 select2">
                                                    <option value="">Direct Selection / Normal</option>
                                                    @foreach($assignments as $assignment)
                                                        <option value="{{ $assignment->id }}">
                                                            CMPO-{{ $assignment->id }} ({{ $assignment->orderMain->sku ?? '-' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="card p-2 mt-3 border">
                                                <label>Order No *</label>
                                                <select id="select_order_no" name="select_order_no"
                                                    class="form-control mb-2 select2" required>
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
                                                <!-- <input type="text" id="lot_no" class="lot-input" placeholder="Enter Lot Number"
                                                                                                                                                            inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                                                                                                                                                            required> -->
                                                <input type="text" id="lot_no" class="lot-input" placeholder="Enter Lot Number"
                                                    required>
                                            </div>
                                            <small class="text-danger" id="err_lot_no"></small>

                                            {{-- CUTTING MASTER --}}
                                            <input type="hidden" name="to_master_unit" value="">


                                            {{-- ADD ROLL --}}
                                            <div class="card p-2 mt-3 border">
                                                <label>Design No</label>
                                                <select id="design_id" class="form-control mb-2 select2" name="design_id"
                                                    required>
                                                    <option value="">Select Design No</option>
                                                </select>

                                            </div>
                                            <div class="card p-2 mt-2 border bg-light">
                                                <div><strong>Fabric :</strong> <span id="show_fabric">—</span></div>
                                                <div><strong>Color :</strong> <span id="show_color">—</span></div>
                                                <div><strong>Pattern :</strong> <span id="show_pattern">—</span></div>
                                                <div><strong>Fitting :</strong> <span id="show_fitting">—</span></div>
                                                <div><strong>Cutting Master :</strong> <span id="show_cutting_master">—</span>
                                                </div>
                                                <div><strong>Size Set :</strong> <span id="show_size_set">—</span></div>
                                                <div><strong>Total Order:</strong> <span id="show_total_order_pcs">—</span>
                                                </div>
                                            </div>
                                            {{-- TOTAL ROLL & TOTAL METER --}}
                                            <select id="roll_no" class="d-none">
                                                <option value="">Select Roll No</option>
                                            </select>
                                            <div class="card p-2 mt-3 border">
                                                <h6>Add Roll</h6>
                                                


                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Total Rolls *</label>
                                                        <input type="number" id="total_rolls" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Total Meter *</label>
                                                        <input type="number" id="total_meter" class="form-control" step="any"
                                                            required>
                                                        <small class="text-danger" id="err_total_meter"></small>
                                                    </div>
                                                </div>



                                                <!-- Dynamic Roll Rows -->
                                                <div id="roll_rows" class="mt-3"></div>

                                                <!-- SIZE ALLOCATION UI (UNCHANGED) -->
                                                <!-- <div id="size_allocations" class="mt-3 p-2 border rounded bg-white">
                                                                                                                                                                                                                                                    <label class="mb-2">Size Wise Quantity</label>
                                                                                                                                                                                                                                                    <div id="size_inputs_container"></div>
                                                                                                                                                                                                                                                </div> -->
                                                <div id="size_allocations" class="mt-3 p-2 border rounded bg-white">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label class="mb-0 font-weight-bold">Size Wise Quantity</label>

                                                        <!-- ✅ TOTAL PIECES INPUT -->
                                                        <div style="width:180px">
                                                            <span><strong>Total Piece</strong></span>
                                                            <input type="number" id="total_pieces"
                                                                class="form-control form-control-sm" placeholder="Total Pieces"
                                                                required>
                                                        </div>
                                                    </div>

                                                    <div id="size_inputs_container"></div>
                                                </div>

                                            </div>




                                            <div class="row mt-4">

                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,0)"
                                                        class="btn btn-outline-success btn-lg w-100 shadow-sm border-2">
                                                        <i class="fas fa-plus-circle mr-2"></i> Save & Add More
                                                    </button>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,1)"
                                                        class="btn btn-success btn-lg w-100 shadow-sm">
                                                        <i class="fas fa-check-double mr-2"></i> Final Submission
                                                    </button>
                                                </div>

                                            </div>

                                        </form>
                                    </div>



                                    {{-- STITCHING FORM --}}
                                    <div class="form-section" id="form-stitching">
                                        <h5 class="mb-4 text-primary font-weight-bold">Send to Stitching</h5>

                                        <form method="POST" id="stitchingForm"
                                            action="{{ route('admin.order_digitalization.store-stitching') }}">
                                            @csrf
                                            <input type="hidden" name="is_final" class="is_final_input">
                                            <input type="hidden" name="production_slip_digitization_id"
                                                value="{{ $cutting_slip->id ?? '' }}">
                                            <input type="hidden" name="to_stage_id" value="4">{{-- Stitching stage ID --}}

                                            {{-- Stitching Unit --}}
                                            <div class="form-group">
                                                <label class="font-weight-bold">
                                                    Stitching Date & Time <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="production_datetime"
                                                    class="form-control datetime-picker" placeholder="Select date & time"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label class="font-weight-bold">Select Stitching Unit</label>
                                                <select name="to_stage_unit_id" class="form-control select2" required>
                                                    <option value="">Select Stitching Unit</option>
                                                    @if(isset($stitching_units))
                                                        @foreach($stitching_units as $unit)
                                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->masterFabricWarehouse->cutting_master_name ?? 'Unknown Warehouse' }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Select Lot No *</label>
                                                <select name="lot_no" id="stitching_lot_no"
                                                    class="form-control select2 lot-selector" data-tab="stitching" required>
                                                    <option value="">Select Lot No</option>
                                                    @foreach($lots_stitching as $lot_no)
                                                        <option value="{{ $lot_no }}">{{ $lot_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Lot Details Display --}}
                                            <div id="stitching_lot_details" class="lot-details-container" style="display:none;">
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Remarks</label>
                                                <textarea name="remarks" class="form-control" rows="2"
                                                    placeholder="Optional remarks..."></textarea>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,0)"
                                                        class="btn btn-outline-success btn-lg w-100 shadow-sm border-2">
                                                        <i class="fas fa-plus-circle mr-2"></i> Save & Next
                                                    </button>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,1)"
                                                        class="btn btn-success btn-lg w-100 shadow-sm">
                                                        <i class="fas fa-check-double mr-2"></i> Final Submission
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    {{-- PRINTING FORM --}}
                                    <div class="form-section" id="form-printing">
                                        <h5 class="mb-4 text-primary font-weight-bold">Send to Printing</h5>

                                        <form method="POST" id="printingForm"
                                            action="{{ route('admin.order_digitalization.store-printing') }}">
                                            @csrf
                                            <input type="hidden" name="is_final" class="is_final_input">
                                            <input type="hidden" name="production_slip_digitization_id"
                                                value="{{ $cutting_slip->id ?? '' }}">
                                            <input type="hidden" name="to_stage_id" value="1">{{-- Printing stage ID --}}

                                            {{-- Printing Unit --}}
                                            <div class="form-group">
                                                <label class="font-weight-bold">
                                                    Printing Date & Time <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="production_datetime"
                                                    class="form-control datetime-picker" placeholder="Select date & time"
                                                    required>
                                            </div>
                                            <div class="form-group">
                                                <label class="font-weight-bold">Select Printing Unit</label>
                                                <select name="to_stage_unit_id" class="form-control select2" required>
                                                    <option value="">Select Printing Unit</option>
                                                    @if(isset($printing_units))
                                                        @foreach($printing_units as $unit)
                                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->masterFabricWarehouse->cutting_master_name ?? 'Unknown Warehouse' }})</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Select Lot No *</label>
                                                <select name="lot_no" id="printing_lot_no"
                                                    class="form-control select2 lot-selector" data-tab="printing" required>
                                                    <option value="">Select Lot No</option>
                                                    @foreach($lots_printing as $lot_no)
                                                        <option value="{{ $lot_no }}">{{ $lot_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Lot Details Display --}}
                                            <div id="printing_lot_details" class="lot-details-container" style="display:none;">
                                            </div>

                                            <div class="form-group">
                                                <label class="font-weight-bold">Remarks</label>
                                                <textarea name="remarks" class="form-control" rows="2"
                                                    placeholder="Optional remarks..."></textarea>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,0)"
                                                        class="btn btn-outline-info btn-lg w-100 shadow-sm border-2">
                                                        <i class="fas fa-plus-circle mr-2"></i> Save & Next
                                                    </button>
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <button type="submit" onclick="setFinal(this,1)"
                                                        class="btn btn-info btn-lg w-100 shadow-sm">
                                                        <i class="fas fa-check-double mr-2"></i> Final Submission
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>






                                </div>
                            </div>
                        </div>

                        {{-- RIGHT IMAGE PANEL --}}
                        <div class="col-md-5">
                            <div class="sticky-wrapper">
                                <div class="card p-3 border position-relative slip-panel">

                                    <!-- SKIP BUTTON OVER IMAGE -->
                                    <form action="{{ route('admin.order_digitalization.skip') }}" method="POST"
                                        class="position-absolute skip-btn">
                                        @csrf
                                        <input type="hidden" name="production_slip_digitization_id"
                                            value="{{ $cutting_slip->id ?? '' }}">
                                        {{-- <button type="submit" class="btn btn-danger btn-sm">
                                            Skip Slip
                                        </button> --}}
                                    </form>
                                    <button type="button" class="btn btn-primary btn-sm position-absolute rotate-btn"
                                        onclick="rotateImage()">
                                        Rotate ↻
                                    </button>
                                    {{-- <img id="slipImage"
                                        src="{{ asset('assets/production_slips/' . ($cutting_slip->slip_file ?? '')) }}"
                                        class="img-fluid rounded slip-image" ondblclick="openImageInNewTab(this)"> --}}
                                    <div class="image-wrapper">
                                        <img id="slipImage"
                                            src="{{ asset('assets/production_slips/' . ($cutting_slip->slip_file ?? '')) }}"
                                            class="slip-image" ondblclick="openImageInNewTab(this)">
                                    </div>
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
                                    </div> {{-- End sticky-wrapper --}}
                                </div> {{-- End col-md-5 --}}
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
        const USED_LOTS = @json($used_lots);
        let isAutoFillingSizes = false;
        let currentRatioMap = {}; // ✅ Store ratio map dynamically

        $(document).ready(function () {
            $('#lot_no').on('input', function () {

                const lotNo = $(this).val().trim();
                const errorBox = $('#err_lot_no');

                if (!lotNo) {
                    errorBox.text('');
                    $('#submit').prop('disabled', false);
                    return;
                }

                // if (USED_LOTS.includes(Number(lotNo))) {
                if (USED_LOTS.includes((lotNo))) {
                    errorBox.text('❌ This lot number is already used');
                    $('#submit').prop('disabled', true);
                } else {
                    errorBox.text('');
                    $('#submit').prop('disabled', false);
                }
            });

        });
    </script>

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
        $(function () {

            $('#rollAssignForm').on('submit', function (e) {

                let meterValid = true;
                let totalMeter = parseFloat($('#total_meter').val()) || 0;
                let usedMeter = 0;

                $('.roll-meter').each(function () {
                    usedMeter += parseFloat($(this).val()) || 0;
                });

                if (Math.abs(usedMeter - totalMeter) > 0.01) {
                    e.preventDefault();
                    $('#err_total_meter').text(
                        `Meter mismatch. Used ${usedMeter.toFixed(2)}, Required ${totalMeter.toFixed(2)}`
                    );
                    return false;
                }

                $('.roll-meter').each(function () {
                    let max = parseFloat($(this).data('max')) || 0;
                    let val = parseFloat($(this).val()) || 0;

                    if (val > max) {
                        meterValid = false;
                    }
                });

                let sizeValid = true;

                // $('.size-qty-input').each(function () {
                //     let pending = parseInt($(this).data('pending')) || 0;
                //     let val = parseInt($(this).val()) || 0;

                //     if (val > pending) {
                //         sizeValid = false;
                //     }
                // });

                // if (!sizeValid) {
                //     e.preventDefault();
                //     alert('Size quantity exceeds pending limit.');
                //     return false;
                // }

                if (!meterValid) {
                    e.preventDefault();
                    alert('One or more rolls exceed available meter.');
                    return false;
                }

                // ✅ update lot number for ALL rows
                let lotNo = $('#lot_no').val();
                $('input[name="lot_no_list[]"]').each(function () {
                    $(this).val(lotNo);
                });

                // ✅ update size JSON for ALL rows
                let sizeJson = getSizeJson();
                $('.size-json').each(function () {
                    $(this).val(sizeJson);
                });

            });



            function clearErrors() {
                $('.text-danger').text('');
            }







        });
        let selectedRollMeters = {}; // track used meters per roll

        $(document).ready(function () {
            const ordersData = @json($orders);
            let lastDesignId = null;
            let prefillApplied = false;
            // console.log(ordersData);
            // ... (rest of order select logic same) ...

            // AUTO-FILL LOGIC
            const PRE_FILLED_ORDER_ID = "{{ $preFilledOrderId ?? '' }}";
            const PRE_FILLED_DESIGN_ID = "{{ $preFilledDesignId ?? '' }}";

            // We'll trigger this at the end of the script to ensure handlers are registered

            $('#select_cmpo').on('change', function() {
                let assignmentId = $(this).val();
                if (!assignmentId) return;

                $.ajax({
                    url: "{{ route('admin.order_digitalization.assignment-details') }}",
                    method: 'GET',
                    data: { assignment_id: assignmentId },
                    success: function(res) {
                        if (res.status == 1) {
                            // 1. Set Order
                            $('#select_order_no').val(res.order_main_id).trigger('change');
                            
                            // 2. Set Design (Wait for designs to load then set it)
                            // Since select_order_no trigger('change') is async-ish with its handler
                            setTimeout(() => {
                                $('#design_id').val(res.order_product_set_id).trigger('change');
                            }, 500);
                        }
                    }
                });
            });

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
                $('#roll_rows').show();
                $('#roll_rows').empty();

                // reset UI
                $('#show_fabric,#show_color,#show_pattern,#show_fitting,#show_cutting_master,#show_size_set').text('—');
                if (lastDesignId !== designSetId) {
                    $('#size_inputs_container').empty();
                    lastDesignId = designSetId;
                }

                let rollSelect = $('#roll_no');
                rollSelect.empty().append('<option value="">Select Roll No</option>');

                if (!designSetId || !orderId) return;

                let order = ordersData.find(o => o.id === orderId);
                if (!order || !order.order_product_sets) return;

                let set = order.order_product_sets.find(s => s.id === designSetId);
                if (!set) return;
                // console.log(set.size_set_name);
                const total_quantity = set ? set.total_quantity : 0;
                /* --------------------
                   SHOW BASIC INFO
                -------------------- */
                /* Calculate Assigned Quantity for this master */
                let assignedQty = 0;
                if (set.order_cutting_stages && set.order_cutting_stages.length > 0) {
                    set.order_cutting_stages.forEach(osc => {
                        assignedQty += parseFloat(osc.quantity) || 0;
                    });
                } else {
                    assignedQty = set.total_quantity;
                }

                const firstOsc = (set.order_cutting_stages && set.order_cutting_stages.length > 0) ? set.order_cutting_stages[0] : null;

                $('#show_fabric').text(set.fabric_names || firstOsc?.fabric_names || '—');
                $('#show_color').text(set.colors?.name || '—');
                $('#show_pattern').text(set.master_design_pattern?.name || firstOsc?.pattern?.name || '—');
                $('#show_fitting').text(set.master_product_fitting?.name || firstOsc?.master_fitting?.name || '—');
                $('#show_cutting_master').text(set.stage_master_unit?.name || firstOsc?.cutting_master?.name || '—');
                $('#show_size_set').text(set.size_set_name || '—');
                $('#show_total_order_pcs').text(assignedQty + ' pcs' || '—');

                // Enforce max pieces for digitalization
                // $('#total_pieces').attr('max', assignedQty);
                // $('#total_pieces').val(''); // Clear on change

                // ✅ CALCULATE RATIO MAP
                currentRatioMap = {};
                if (set.size_measurement && set.size_measurement.size_group) {
                    let sizes = set.size_measurement.size_group.split(',').map(s => s.trim());
                    sizes.forEach(s => {
                        currentRatioMap[s] = (currentRatioMap[s] || 0) + 1;
                    });
                }

                /* --------------------
                   LOAD SIZE INPUTS
                -------------------- */
                if (set.product_set_details) {
                    set.product_set_details.forEach(detail => {
                        let remaining = detail.remaining_lot_allocated;
                        let size = detail.size;
                        $('#size_inputs_container').append(`
                                                                                                                        <div class="row mb-2 align-items-center">
                                                                                                                            <div class="col-4">
                                                                                                                                <span class="font-weight-bold">${size}</span> <br>
                                                                                                                                {{-- <small class="text-muted">(Pending: ${remaining})</small>  --}}
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
                        setTimeout(() => {
                            autoFillSizesFromTotal();
                        }, 0);
                    });
                }

                /* --------------------
                   LOAD ALL ROLLS FROM ALL ASSIGNED FABRICS
                -------------------- */
                rollSelect = $('#roll_no');
                rollSelect.empty().append('<option value="">Select Roll No</option>');

                if (set.assigned_fabrics && set.assigned_fabrics.length > 0) {
                    set.assigned_fabrics.forEach(fabric => {
                        if (fabric.receipt_details) {
                            fabric.receipt_details.forEach(detail => {
                                if (parseFloat(detail.remaining_quantity) <= 0) return;

                                rollSelect.append(
                                    $('<option>', {
                                        value: detail.id,
                                        text: `[${fabric.name}] Roll ${detail.roll_number} (${detail.remaining_quantity} m)`
                                    })
                                    .attr('data-meter', detail.remaining_quantity)
                                    .attr('data-roll', detail.roll_number)
                                );
                            });
                        }
                    });
                }
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

            $('#total_rolls').on('input', function () {

                let total = parseInt($(this).val()) || 0;
                let container = $('#roll_rows');
                container.empty();

                if (total <= 0) return;

                // ✅ SAFETY CHECK: rolls must exist
                if ($('#roll_no option').length < 1) {
                    alert('Please select Design first');
                    $(this).val('');
                    return;
                }

                let rollCount = $('#roll_no option').length - 1; // exclude placeholder
                $('#total_rolls').attr('max', rollCount);
                if (total > rollCount) {
                    alert(`Only ${rollCount} rolls available for selected design.`);
                    $(this).val('');
                    return;
                }
                // ✅ BUILD optionsHtml AT THE RIGHT TIME
                let optionsHtml = '';
                $('#roll_no option').each(function () {
                    optionsHtml += `<option 
                                                                                                                    value="${this.value}"
                                                                                                                    data-meter="${$(this).attr('data-meter')}"
                                                                                                                    data-roll="${$(this).attr('data-roll')}">
                                                                                                                    ${$(this).text()}
                                                                                                                </option>`;
                });

                // // Trigger Auto-Select if available
                // if (PRE_FILLED_ORDER_ID) {
                //     // Check if value actually exists in dropdown (it should if ordersData matches)
                //     if ($('#select_order_no option[value="' + PRE_FILLED_ORDER_ID + '"]').length > 0) {
                //         $('#select_order_no').val(PRE_FILLED_ORDER_ID).trigger('change');

                //         if (PRE_FILLED_DESIGN_ID) {
                //             // The change event above repopulates #design_id synchronously
                //             $('#design_id').val(PRE_FILLED_DESIGN_ID).trigger('change');
                //         }
                //     }
                // }

                for (let i = 1; i <= total; i++) {
                    let lotNo = $('#lot_no').val();

                    container.append(`
                                                                                                                    <div class="row mb-2 roll-row">
                                                                                                                        <div class="col-md-6">
                                                                                                                            <label>Roll No (${i}) *</label>
                                                                                                                            <select name="roll_no_list[]" class="form-control select2 roll-select" required>
                                                                                                                                ${optionsHtml}
                                                                                                                            </select>
                                                                                                                        </div>

                                                                                                                        <div class="col-md-6">
                                                                                                                            <label>Meter Used *</label>
                                                                                                                            <input type="number"
                                                                                                                                name="meter_list[]"
                                                                                                                                class="form-control roll-meter"
                                                                                                                                disabled
                                                                                                                                placeholder="Select roll first"  step="any">
                                                                                                                        </div>

                                                                                                                        <input type="hidden" name="lot_no_list[]" value="${lotNo}">
                                                                                                                        <input type="hidden" name="size_details[]" class="size-json">
                                                                                                                    </div>
                                                                                                                `);
                }

                $('.select2').select2();
            });






        });
    </script>
    <script>
        /* ✅ GLOBAL FUNCTION – ACCESSIBLE EVERYWHERE */
        function getSizeJson() {
            let sizeDetails = [];

            $('.size-qty-input').each(function () {
                let qty = parseFloat($(this).val()) || 0;
                if (qty > 0) {
                    sizeDetails.push({
                        detail_id: $(this).data('detail-id'),
                        size: $(this).data('size'),
                        qty: qty
                    });
                }
            });

            return JSON.stringify(sizeDetails);
        }
    </script>


    {{-- Lot Details Fetching Script --}}
    <script>
        $(document).ready(function () {
            // Handle lot selection for all tabs
            $('.lot-selector').on('change', function () {
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
                    success: function (data) {
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
                    error: function () {
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
                        sizeModalRows += `
                                                                                                            <tr>
                                                                                                                <td class="font-weight-bold text-primary">${size}</td>
                                                                                                                <td>${qty} pcs</td>
                                                                                                            </tr>
                                                                                                        `;
                    }
                }

                const uniqueId = `lot_${data.lot_no.replace(/[^a-zA-Z0-9]/g, '_')}`;

                const html = `
                                                                                                                <!-- Simplified Lot Details Card -->
                                                                                                                <div class="card border-primary shadow-sm mb-3 lot-details-card">
                                                                                                                    <div class="card-header bg-gradient-primary text-white p-2 d-flex justify-content-between align-items-center">
                                                                                                                        <div class="d-flex align-items-center">
                                                                                                                            <i class="fas fa-box mr-2"></i>
                                                                                                                            <strong>Lot: ${data.lot_no}</strong>
                                                                                                                        </div>
                                                                                                                        <span class="badge badge-light">${totalPieces} pcs</span>
                                                                                                                    </div>
                                                                                                                    <div class="card-body p-0">
                                                                                                                        <div class="table-responsive">
                                                                                                                            <table class="table table-sm table-bordered mb-0" style="font-size: 0.9rem;">
                                                                                                                                <thead class="bg-light text-center">
                                                                                                                                    <tr>
                                                                                                                                        <th style="width: 40%">Size</th>
                                                                                                                                        <th style="width: 60%">Quantity</th>
                                                                                                                                    </tr>
                                                                                                                                </thead>
                                                                                                                                <tbody class="text-center">
                                                                                                                                    ${sizeModalRows || '<tr><td colspan="2" class="text-muted">No size data</td></tr>'}
                                                                                                                                </tbody>
                                                                                                                            </table>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            `;

                container.html(html).show();
            }

            function refreshRollSelections() {
                let selectedRolls = [];
                $('.roll-select').each(function () {
                    let val = $(this).val();
                    if (val) selectedRolls.push(val);
                });

                $('.roll-select').each(function () {
                    let currentSelect = $(this);
                    let currentVal = currentSelect.val();

                    currentSelect.find('option').each(function () {
                        let optionVal = $(this).val();
                        if (!optionVal) return;

                        if (selectedRolls.includes(optionVal) && optionVal !== currentVal) {
                            $(this).prop('disabled', true);
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });
                    // Refresh select2 UI if it exists
                    if (currentSelect.hasClass('select2-hidden-accessible')) {
                        currentSelect.select2();
                    }
                });
            }

            $(document).on('select2:select', '.roll-select', function (e) {

                // IMPORTANT: get the REAL <option> element
                let selectedOption = e.params.data.element;

                let maxMeter = parseFloat(
                    selectedOption.getAttribute('data-meter')
                ) || 0;

                let meterInput = $(this).closest('.roll-row').find('.roll-meter');

                if (!maxMeter || maxMeter <= 0) {
                    meterInput.prop('disabled', true);
                    meterInput.val('');
                    return;
                }

                meterInput.prop('disabled', false);
                meterInput.attr('max', maxMeter);
                meterInput.data('max', maxMeter);

                // Auto-fill logic
                let totalMeter = parseFloat($('#total_meter').val()) || 0;
                let usedMeterSoFar = 0;
                $('.roll-meter').not(meterInput).each(function () {
                    usedMeterSoFar += parseFloat($(this).val()) || 0;
                });

                let remainingNeeded = Math.max(0, totalMeter - usedMeterSoFar);
                let fillValue = Math.min(maxMeter, remainingNeeded);

                if (fillValue > 0) {
                    meterInput.val(fillValue);
                } else {
                    meterInput.val('');
                }

                refreshRollSelections();
            });

        });
    </script>

    <script>


        $(document).on('input', '.roll-meter', function () {
            let max = parseFloat($(this).data('max')) || 0;
            let val = parseFloat($(this).val()) || 0;

            if (!max || max <= 0) {
                $(this).val('');
                alert('Please select a roll first');
                return;
            }

            if (val > max) {
                $(this).val(max);
                alert(`Max allowed meter for this roll is ${max}`);
            }
        });

        $(document).on('input', '.size-qty-input', function () {

            // ❌ Ignore updates triggered by auto-fill
            if (isAutoFillingSizes) return;

            let total = 0;

            $('.size-qty-input').each(function () {
                total += parseInt($(this).val()) || 0;
            });

            $('#total_pieces').val(total);
        });
    </script>
    <script>
        $(document).on('input', '#total_pieces', function () {
            // let max = parseInt($(this).attr('max')) || 0;
            // let val = parseInt($(this).val()) || 0;

            // if (max > 0 && val > max) {
            //     $(this).val(max);
            //     alert(`Max allowed pieces for this assignment is ${max}`);
            // }
            autoFillSizesFromTotal();
        });
        setTimeout(() => {
            autoFillSizesFromTotal();
        }, 0);
    </script>
    <script>
        function autoFillSizesFromTotal() {
            let totalPieces = parseInt($('#total_pieces').val()) || 0;
            let sizeInputs = $('.size-qty-input');

            if (totalPieces <= 0 || sizeInputs.length === 0) return;

            isAutoFillingSizes = true;

            // ✅ Dynamic ratio calculation
            let ratioSum = 0;
            let targetRatios = [];

            sizeInputs.each(function () {
                let size = $(this).data('size').toString();
                let ratio = currentRatioMap[size] || 1;
                targetRatios.push(ratio);
                ratioSum += ratio;
            });

            let allocatedCount = 0;
            let fractionalParts = [];

            sizeInputs.each(function (index) {
                let ratio = targetRatios[index];
                let exactShare = (ratio / ratioSum) * totalPieces;
                let intShare = Math.floor(exactShare);

                $(this).val(intShare);
                allocatedCount += intShare;

                fractionalParts.push({
                    index: index,
                    fraction: exactShare - intShare
                });
            });

            // Distribute remainders based on highest fractional part (Largest Remainder Method)
            let remainder = totalPieces - allocatedCount;
            fractionalParts.sort((a, b) => b.fraction - a.fraction);

            for (let i = 0; i < remainder; i++) {
                let targetIndex = fractionalParts[i].index;
                let input = $(sizeInputs[targetIndex]);
                input.val(parseInt(input.val()) + 1);
            }

            isAutoFillingSizes = false;
        }
    </script>

    <script>
        $(document).ready(function () {
            @if(isset($cutting_slip) && $cutting_slip->save_type)
                const saveType = "{{ $cutting_slip->save_type }}";
                let target = null;

                if (saveType == "1") target = "rolls";
                else if (saveType == "2") target = "printing";
                else if (saveType == "3") target = "stitching";

                if (target) {
                    $(`.action-btn[data-target="${target}"]`).trigger('click');
                }
            @endif
                                                                                        });
    </script>

    <script>
        let rotation = 0;

        function rotateImage() {
            rotation += 90;
            document.getElementById('slipImage').style.transform =
                `rotate(${rotation}deg)`;
        }

        function openImageInNewTab(img) {
            window.open(img.src, '_blank');
        }
        function setFinal(btn, val) {
            $(btn).closest('form').find('.is_final_input').val(val);
        }
    </script>


@endsection