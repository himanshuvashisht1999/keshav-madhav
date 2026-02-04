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
                <h2 class="mb-3">Production Slip – Cutting Master</h2>



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
                                                <label>Order No *</label>
                                                <select id="select_order_no" name="select_order_no"
                                                    class="form-control mb-2 select2">
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
                                                <input type="text" id="lot_no" class="lot-input" placeholder="Enter Lot Number"
                                                    inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                            </div>
                                            <small class="text-danger" id="err_lot_no"></small>

                                            {{-- CUTTING MASTER --}}
                                            <input type="hidden" name="to_master_unit" value="">


                                            {{-- ADD ROLL --}}
                                            <div class="card p-2 mt-3 border">
                                                <label>Design No</label>
                                                <select id="design_id" class="form-control mb-2 select2" name="design_id">
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
                                                        <input type="number" id="total_rolls" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Total Meter *</label>
                                                        <input type="number" id="total_meter" class="form-control">
                                                    </div>
                                                </div>

                                                <small class="text-danger" id="err_total_meter"></small>

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
                                                                class="form-control form-control-sm" placeholder="Total Pieces">
                                                        </div>
                                                    </div>

                                                    <div id="size_inputs_container"></div>
                                                </div>

                                            </div>




                                            <input type="hidden" name="production_slip_digitization_id"
                                                value="{{ $cutting_slip->id ?? '' }}">

                                            <button type="submit" id="submit" class="btn btn-success w-100 mt-3">
                                                Submit
                                            </button>

                                        </form>
                                    </div>



                                    {{-- STITCHING FORM --}}
                                    <div class="form-section" id="form-stitching">
                                        <h5 class="mb-4 text-primary font-weight-bold">Send to Stitching</h5>

                                        <form method="POST" id="stitchingForm"
                                            action="{{ route('admin.order_digitalization.store-stitching') }}">
                                            @csrf
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
                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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

                                            <button type="submit" class="btn btn-success btn-lg w-100">
                                                <i class="fas fa-paper-plane mr-2"></i> Send to Stitching
                                            </button>
                                        </form>
                                    </div>
                                    {{-- PRINTING FORM --}}
                                    <div class="form-section" id="form-printing">
                                        <h5 class="mb-4 text-primary font-weight-bold">Send to Printing</h5>

                                        <form method="POST" id="printingForm"
                                            action="{{ route('admin.order_digitalization.store-printing') }}">
                                            @csrf
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
                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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

                                            <button type="submit" class="btn btn-info btn-lg w-100">
                                                <i class="fas fa-print mr-2"></i> Send to Printing
                                            </button>
                                        </form>
                                    </div>






                                </div>
                            </div>
                        </div>

                        {{-- RIGHT IMAGE PANEL --}}
                        <div class="col-md-5">


                            <div class="card p-3 border position-relative slip-panel">

                                <!-- SKIP BUTTON OVER IMAGE -->
                                <form action="{{ route('admin.order_digitalization.skip') }}" method="POST"
                                    class="position-absolute skip-btn">
                                    @csrf
                                    <input type="hidden" name="production_slip_digitization_id"
                                        value="{{ $cutting_slip->id ?? '' }}">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Skip Slip
                                    </button>
                                </form>

                                <img src="{{ asset('assets/production_slips/' . ($cutting_slip->slip_file ?? '')) }}"
                                    class="img-fluid rounded">

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
        const USED_LOTS = @json($used_lots);
        let isAutoFillingSizes = false;

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

                if (usedMeter !== totalMeter) {
                    e.preventDefault();
                    $('#err_total_meter').text(
                        `Meter mismatch. Used ${usedMeter}, Required ${totalMeter}`
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
            // console.log(ordersData);
            // ... (rest of order select logic same) ...

            // AUTO-FILL LOGIC
            const PRE_FILLED_ORDER_ID = "{{ $preFilledOrderId ?? '' }}";
            const PRE_FILLED_DESIGN_ID = "{{ $preFilledDesignId ?? '' }}";

            // We'll trigger this at the end of the script to ensure handlers are registered

            $('#select_order_no').on('change', function () {

                let orderId = parseInt($(this).val());

                let designSelect = $('#design_id');

                designSelect.empty()
                    .append('<option value="">Select Design No</option>');

                if (!orderId) return;

                let order = ordersData.find(o => o.id === orderId);

                if (!order || !order.order_product_sets) return;

                order.order_product_sets.forEach(set => {
                    // Check if any detail has remaining quantity
                    let hasRemaining = false;

                    if (set.product_set_details && set.product_set_details.length > 0) {
                        // Check if at least one size has remaining quantity > 0
                        hasRemaining = set.product_set_details.some(detail => (parseFloat(detail.remaining_lot_allocated) || 0) > 0);
                    } else {
                        // If no details are present, fallback to showing it (or filtering it out based on exact requirement).
                        // User said: "if there is no any size quanity left then sizes are not coming".
                        // Assuming empty details means no sizes to allocate, so safer to hide, OR could mean fresh order.
                        // Given the context of "completed", assuming we only hide if we KNOW it's 0.
                        // However, usually detailed data is populated. If empty, safe to assume nothing to allocate?
                        // Let's assume if no details, we show it (safe default) or check if logic implies "completed" means details exist with 0.
                        // Let's assume hasRemaining is false if no details are found unless we know otherwise.
                        // Actually, if a set has NO details, how can we allocate? We can't. So hiding seems correct.
                        hasRemaining = false;
                    }

                    if (hasRemaining) {
                        designSelect.append(
                            `<option value="${set.id}">
                                ${set.design_number}
                            </option>`
                        );
                    }
                });
                designSelect.trigger('change');
            });

            $('#design_id').on('change', function () {

                let designSetId = parseInt($(this).val());
                let orderId = parseInt($('#select_order_no').val());
                $('#roll_rows').show();
                $('#roll_rows').empty();

                // reset UI
                $('#show_fabric,#show_color,#show_pattern,#show_fitting,#show_cutting_master').text('—');
                $('#size_inputs_container').empty(); // Clear sizes

                let rollSelect = $('#roll_no');
                rollSelect.empty().append('<option value="">Select Roll No</option>');

                if (!designSetId || !orderId) return;

                let order = ordersData.find(o => o.id === orderId);
                if (!order || !order.order_product_sets) return;

                let set = order.order_product_sets.find(s => s.id === designSetId);
                if (!set) return;

                const sum_total = order.order_product_sets.reduce((acc, item) => {
                    return acc + item.total_quantity;
                }, 0);
                /* --------------------
                   SHOW BASIC INFO
                -------------------- */
                $('#show_fabric').text(set.fabric?.name ?? '—');
                $('#show_color').text(set.colors?.name ?? '—');
                $('#show_pattern').text(set.master_design_pattern?.name ?? '—');
                $('#show_fitting').text(set.master_product_fitting?.name ?? '—');
                $('#show_cutting_master').text(set.stage_master_unit?.name ?? '—');
                $('#show_total_order_pcs').text(sum_total + ' pcs' ?? '—');

                /* --------------------
                   LOAD SIZE INPUTS
                -------------------- */
                if (set.product_set_details) {
                    set.product_set_details.forEach(detail => {
                        let remaining = detail.remaining_lot_allocated;
                        let size = detail.size;
                        if (remaining > 0) {
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
                        }
                    });
                }

                /* --------------------
                   LOAD ROLL NUMBERS
                -------------------- */
                if (!set.fabric || !set.fabric.receipt_details) {
                    rollSelect.trigger('change');
                    return;
                }

                set.fabric.receipt_details.forEach(detail => {

                    if (parseFloat(detail.remaining_quantity) <= 0) return;

                    rollSelect.append(
                        $('<option>', {
                            value: detail.id,
                            text: `Roll ${detail.roll_number} (${detail.remaining_quantity} m)`
                        })
                            .attr('data-meter', detail.remaining_quantity)
                            .attr('data-roll', detail.roll_number)
                    );
                });

                rollSelect.trigger('change'); // refresh select2
            });


            // ... (rest of event listeners same) ...
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
                if ($('#roll_no option').length <= 1) {
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

                // Trigger Auto-Select if available
                if (PRE_FILLED_ORDER_ID) {
                    // Check if value actually exists in dropdown (it should if ordersData matches)
                    if ($('#select_order_no option[value="' + PRE_FILLED_ORDER_ID + '"]').length > 0) {
                        $('#select_order_no').val(PRE_FILLED_ORDER_ID).trigger('change');

                        if (PRE_FILLED_DESIGN_ID) {
                            // The change event above repopulates #design_id synchronously
                            $('#design_id').val(PRE_FILLED_DESIGN_ID).trigger('change');
                        }
                    }
                }

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
                                    placeholder="Select roll first">
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
                        const percentage = (qty / totalPieces * 100).toFixed(1);
                        sizeModalRows += `
                            <tr>
                                <td class="font-weight-bold">${size}</td>
                                <td>

                                            ${qty} pcs
                                </td>
                            </tr>
                        `;
                    }
                }

                const uniqueId = `lot_${data.lot_no.replace(/[^a-zA-Z0-9]/g, '_')}`;

                const html = `
                    <!-- Collapsible Lot Details Card -->
                    <div class="card border-primary shadow-sm mb-3 lot-details-card">
                        <!-- Collapsed Header (Always Visible) -->
                        <div class="card-header bg-gradient-primary text-white p-2 cursor-pointer" 
                             data-toggle="collapse" data-target="#${uniqueId}" 
                             aria-expanded="false" aria-controls="${uniqueId}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-box mr-2"></i>
                                    <strong>Lot: ${data.lot_no}</strong>
                                    <span class="ml-3 badge badge-light">${data.total_rolls} Roll${data.total_rolls > 1 ? 's' : ''}</span>
                                    <span class="ml-2 badge badge-light">${data.total_meters}m</span>
                                    <span class="ml-2 badge badge-light">${totalPieces} pcs</span>
                                </div>
                                <i class="fas fa-chevron-down toggle-icon"></i>
                            </div>
                        </div>

                        <!-- Expandable Details -->
                        <div id="${uniqueId}" class="collapse">
                            <div class="card-body p-3">
                                <!-- Quick Info Row -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="fas fa-cut text-primary mr-2"></i>
                                            <strong>${data.cutting_master.name}</strong>
                                            <div class="text-muted small ml-4">${data.cutting_master.warehouse}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                                            <span class="small">${data.cutting_master.address}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fabric & Orders -->
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="fas fa-tshirt text-info mr-2"></i>
                                            <strong class="small">Fabric:</strong>
                                        </div>
                                        <div class="ml-4">${fabricBadges}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="fas fa-file-alt text-success mr-2"></i>
                                            <strong class="small">Orders:</strong>
                                        </div>
                                        <div class="ml-4">${orderBadges}</div>
                                    </div>
                                </div>

                                <!-- Size Details Button -->
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-toggle="modal" data-target="#sizeModal_${uniqueId}">
                                        <i class="fas fa-ruler mr-2"></i>
                                        View ${sizeCount} Size${sizeCount > 1 ? 's' : ''} Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Size Details Modal -->
                    <div class="modal fade" id="sizeModal_${uniqueId}" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-ruler mr-2"></i>
                                        Size-wise Quantities - Lot ${data.lot_no}
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th width="20%">Size</th>
                                                    <th width="60%">Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${sizeModalRows || '<tr><td colspan="3" class="text-center text-muted">No size data</td></tr>'}
                                            </tbody>
                                            <tfoot class="bg-light font-weight-bold">
                                                <tr>
                                                    <td>Total</td>
                                                    <td>${totalPieces} pieces</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                container.html(html).show();

                // Add rotation animation to chevron
                $(`[data-target="#${uniqueId}"]`).on('click', function () {
                    $(this).find('.toggle-icon').toggleClass('fa-rotate-180');
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
                meterInput.val('');
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

            let sizeCount = sizeInputs.length;
            let baseQty = Math.floor(totalPieces / sizeCount);
            let remainder = totalPieces % sizeCount;

            sizeInputs.each(function (index) {
                let qty = baseQty;
                if (index < remainder) qty += 1;
                $(this).val(qty);
            });

            isAutoFillingSizes = false;
        }
    </script>


@endsection