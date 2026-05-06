@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="m-0 font-weight-bold text-dark">Dispatch Scanning</h1>
                        <p class="text-muted mb-0">Order #ORD-{{ $order->id }} | {{ $order->shop_name }}</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="d-inline mr-2">
                            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" data-toggle="modal" data-target="#dispatchModal">
                                <i class="fas fa-shipping-fast mr-1"></i> DISPATCH NOW
                            </button>
                        </div>
                        <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                            class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Details
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- SCANNING AREA -->
                    <div class="col-md-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body bg-light p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <label for="barcode_input" class="h5 mb-3">Scan QR, Barcode or Enter Box #</label>
                                        <div class="input-group input-group-lg shadow-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0">
                                                    <i class="fas fa-qrcode text-primary"></i>
                                                </span>
                                            </div>
                                            <input type="text" id="barcode_input"
                                                class="form-control form-control-lg border-left-0"
                                                placeholder="Ready for input..." autofocus autocomplete="off">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary px-4" type="button" id="submit_barcode">
                                                    <i class="fas fa-search-plus mr-1"></i> SCAN
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle mr-1 text-primary"></i>
                                            Scan a barcode or QR code. Each scan assigns <strong>one available box</strong>
                                            of that type from inventory.
                                            The same barcode/QR applies to all boxes of the same design, color &amp; size.
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div id="scan_status"
                                            class="alert alert-secondary border-secondary mb-0 h-100 d-flex align-items-center justify-content-center">
                                            <div class="text-center text-white">
                                                <i class="fas fa-qrcode fa-2x mb-2 opacity-50"></i>
                                                <div class="font-weight-bold">Ready to Scan</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PROGRESS LIST & HISTORY -->
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Order Requirements</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light text-muted small text-uppercase">
                                            <tr>
                                                <th>Product Details</th>
                                                <th class="text-center" width="200">Progress</th>
                                                <th class="text-center" width="150">Count</th>
                                                <th class="text-right" width="100">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($groupedItems as $key => $group)
                                                <tr id="row_{{ $key }}"
                                                    class="variation-row {{ $group['scanned'] == $group['required'] ? 'bg-light-success' : '' }}">
                                                    <td>
                                                        <div class="font-weight-bold">{{ $group['product_name'] }}</div>
                                                        <small class="text-muted">
                                                            D: {{ $group['design_number'] }} | C: {{ $group['color_name'] }} |
                                                            S: {{ $group['size_set_name'] }} | P: {{ $group['pattern_name'] }} |
                                                            F: {{ $group['fitting_name'] }}
                                                            <br>
                                                            <span class="badge badge-light border mt-1">WH: {{ $group['warehouse_name'] }}</span>
                                                            <span class="badge badge-light border mt-1">Rack: {{ $group['rack_name'] }}</span>
                                                            <span class="badge badge-info mt-1">Barcode:
                                                                {{ $group['barcode'] }}</span>
                                                        </small>
                                                    </td>
                                                    <td class="vertical-align-middle">
                                                        <div class="progress rounded-pill shadow-sm" style="height: 10px;">
                                                            <div id="progress_bar_{{ $key }}"
                                                                class="progress-bar progress-bar-striped progress-bar-animated {{ $group['scanned'] == $group['required'] ? 'bg-success' : 'bg-primary' }}"
                                                                role="progressbar"
                                                                style="width: {{ ($group['scanned'] / $group['required']) * 100 }}%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span id="count_{{ $key }}" class="h5 font-weight-bold mb-0">
                                                            {{ $group['scanned'] }}
                                                        </span>
                                                        <span class="text-muted">/ {{ $group['required'] }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <div id="status_icon_{{ $key }}">
                                                            @if($group['scanned'] == $group['required'])
                                                                <i class="fas fa-check-circle text-success fa-lg"></i>
                                                            @else
                                                                <i class="fas fa-clock text-warning fa-lg"></i>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- RECENT SCANS HISTORY -->
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-secondary text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-history mr-2"></i>Recent Scans</h3>
                            </div>
                            <div class="card-body p-0">
                                <div id="scan_history" class="list-group list-group-flush"
                                    style="max-height: 600px; overflow-y: auto;">
                                    @forelse($scannedBoxes as $box)
                                        @php $boxKey = "{$box->product_id}_{$box->color_id}_{$box->size_set_id}"; @endphp
                                        <div class="list-group-item" id="history_{{ $boxKey }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="font-weight-bold">Scanned: {{ $box->design_number }}</div>
                                                    <small class="text-muted d-block">{{ $box->product_name }}</small>
                                                    <small class="text-muted">Color: {{ $box->color_name }} | Count:
                                                        {{ $box->scanned_box_qty }} / {{ $box->box_qty }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                                                    data-id="{{ $box->box_no }}" data-barcode="{{ $box->barcode }}"
                                                    title="Undo one scan">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div id="no_scans_msg" class="p-4 text-center text-muted">
                                            <i class="fas fa-barcode fa-3x mb-3 opacity-25"></i>
                                            <p>No boxes scanned yet.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Dispatch Configuration Modal -->
    <div class="modal fade" id="dispatchModal" tabindex="-1" role="dialog" aria-labelledby="dispatchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title font-weight-bold" id="dispatchModalLabel"><i class="fas fa-shipping-fast mr-2"></i> Configure Dispatch</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="dispatchForm" action="{{ route('admin.agent-orders.dispatch-selected') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_ids[]" value="{{ $order->id }}">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Dispatch Date</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-calendar-alt text-success"></i></span>
                                </div>
                                <input type="datetime-local" class="form-control border-left-0" id="dispatch_date" name="dispatch_date" value="{{ date('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Subtotal Amount</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-primary"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_total_amount" name="total_amount" value="{{ $order->grand_total }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Extra Discount</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-minus-circle text-danger"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_discount_amount" name="discount_amount" value="0">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-muted small text-uppercase">Other Charges</label>
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-plus-circle text-info"></i></span>
                                </div>
                                <input type="number" step="0.01" class="form-control border-left-0" id="modal_other_charges" name="other_charges" value="0">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">GST %</label>
                                    <div class="input-group shadow-sm">
                                        <input type="number" step="any" class="form-control" id="modal_gst_percentage" name="gst_percentage" value="{{ $order->gst_percentage ?? 5 }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white"><i class="fas fa-percentage text-secondary"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-muted small text-uppercase">GST Amount</label>
                                    <div class="input-group shadow-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-rupee-sign text-muted"></i></span>
                                        </div>
                                        <input type="number" step="any" class="form-control border-left-0" id="modal_gst_amount_input" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="bg-light p-3 rounded-lg text-center shadow-sm border">
                            <h6 class="text-muted text-uppercase mb-1 small font-weight-bold">Final Grand Total</h6>
                            <h3 class="mb-0 text-success font-weight-bold" id="modal_grand_total_display">₹0.00</h3>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-outline-secondary px-4 mr-2" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <button type="submit" class="btn btn-success px-5 font-weight-bold" style="border-radius: 8px;">CONFIRM DISPATCH</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.05);
        }

        .vertical-align-middle {
            vertical-align: middle !important;
        }

        .pulse {
            animation: pulse-animation 1s infinite;
        }

        @keyframes pulse-animation {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }
    </style>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const barcodeInput = $('#barcode_input');
            const scanStatus = $('#scan_status');
            const orderId = "{{ $order->id }}";

            // Auto-focus on load
            barcodeInput.focus();

            // Aggressive refocus and global input capture
            // If user types/scans anywhere on the page, send it to the input field
            $(document).on('keydown', function (e) {
                console.log('Global KeyDown:', e.key, 'code:', e.which);

                // Ignore if we're already in an input field (could be another field we added later)
                if ($(e.target).is('input, textarea, select')) return;

                // Ignore function keys, modifier keys, etc.
                if (e.ctrlKey || e.altKey || e.metaKey || e.which < 32 || e.which > 126) return;

                console.log('Redirecting focus to barcode input...');
                barcodeInput.focus();
            });

            // Refocus if focus is lost on click
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.input-group, .undo-btn, button, a').length) {
                    barcodeInput.focus();
                }
            });

            let scanTimer;
            // barcodeInput.on('input', function () {
            //     const val = $(this).val().trim();
            //     console.log('Input Received:', val);
            //     $('.input-group-text i').addClass('fa-spin text-warning').removeClass('text-primary');
            //     setTimeout(() => {
            //         $('.input-group-text i').removeClass('fa-spin text-warning').addClass('text-primary');
            //     }, 100);

            //     // Auto-submit after 400ms of no typing (useful for scanners that don't send Enter)
            //     clearTimeout(scanTimer);
            //     scanTimer = setTimeout(function () {
            //         if (val.length >= 4) { // Trigger only if length is 4 or more
            //             console.log('Auto-submitting due to inactivity timeout (400ms)...');
            //             triggerScan();
            //         }
            //     }, 400);
            // });

            barcodeInput.on('paste', function (e) {
                setTimeout(() => {
                    triggerScan();
                }, 100); // wait for paste to complete
            });

            barcodeInput.on('keydown', function (e) {
                console.log('Input KeyDown:', e.key, 'code:', e.which);
                // Handle Enter (13), Line Feed (10), or Tab (9)
                if (e.which == 13 || e.which == 10 || e.which == 9) {
                    e.preventDefault();
                    console.log('Manual Submit Key Detected:', e.key);
                    clearTimeout(scanTimer);
                    triggerScan();
                }
            });

            $('#submit_barcode').on('click', function () {
                console.log('Scan Button Clicked');
                clearTimeout(scanTimer);
                triggerScan();
            });

            function triggerScan() {
                const barcode = barcodeInput.val().trim();
                console.log('--- TRIGGERING SCAN ---');
                console.log('Raw Val:', barcode);
                if (barcode) {
                    processScan(barcode);
                } else {
                    console.warn('Scan canceled: Input is empty');
                }
                barcodeInput.val('');
                barcodeInput.focus();
            }

            function processScan(barcode) {
                setLoadingStatus(barcode);
                console.log('AJAX Start: processing barcode', barcode);

                $.ajax({
                    url: "{{ route('admin.agent-orders.process-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function (response) {
                        console.log('AJAX Success:', response);
                        barcodeInput.focus(); // Ensure focus after AJAX
                        if (response.success) {
                            setSuccessStatus(response.message);
                            updateUI(response.variation_key, response);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            playBeep(false);
                        }
                    },
                    error: function (xhr) {
                        console.error('AJAX Error:', xhr.status, xhr.responseText);
                        barcodeInput.focus();
                        setErrorStatus('Server error occurred.');
                        playBeep(false);
                    }
                });
            }

            // Remove Scan Logic
            $(document).on('click', '.undo-btn', function () {
                const boxNo = $(this).data('id');
                const btn = $(this);

                if (!confirm('Are you sure you want to remove this scan?')) return;

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.agent-orders.remove-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: $(this).data('barcode')
                    },
                    success: function (response) {
                        if (response.success) {
                            setSuccessStatus("Scan removed successfully");
                            revertUI(response.variation_key, boxNo, response);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                        }
                    },
                    error: function () {
                        setErrorStatus('Server error occurred.');
                        btn.prop('disabled', false).html('<i class="fas fa-undo"></i>');
                    }
                });
            });

            function updateUI(key, response) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);

                let current = response.scanned;
                const total = response.required;

                countSpan.text(current);

                const percentage = (current / total) * 100;
                progressBar.css('width', percentage + '%');

                if (current >= total) {
                    progressBar.removeClass('bg-primary pulse').addClass('bg-success');
                    statusIcon.html('<i class="fas fa-check-circle text-success fa-lg"></i>');
                    row.addClass('bg-light-success');
                } else {
                    progressBar.addClass('pulse');
                }

                // Update OR Append to history
                const boxNo = response.variation_key; // Use key for stable tracking per design
                const productName = response.product_name || '';
                const designNumber = response.design_number || '';
                const color = response.color_name || '';

                $('#no_scans_msg').hide();
                const existingHistory = $(`#history_${boxNo}`);

                const historyHTML = `
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <div class="font-weight-bold">Scanned: ${designNumber}</div>
                                                                        <small class="text-muted d-block">${productName}</small>
                                                                        <small class="text-muted">${color} | Count: ${current} / ${total}</small>
                                                                        <div class="mt-1"><small class="text-white bg-success px-2 rounded">Just Scanned</small></div>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                                                                        data-id="${boxNo}" data-barcode="${response.barcode}" title="Undo one scan">
                                                                        <i class="fas fa-undo"></i>
                                                                    </button>
                                                                </div>
                                            `;

                if (existingHistory.length) {
                    existingHistory.html(historyHTML).addClass('animate__animated animate__pulse');
                    setTimeout(() => existingHistory.removeClass('animate__animated animate__pulse'), 1000);
                } else {
                    const historyItem = `<div class="list-group-item animate__animated animate__fadeInDown" id="history_${boxNo}">${historyHTML}</div>`;
                    $('#scan_history').prepend(historyItem);
                }
            }

            function revertUI(key, boxNo, response) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);

                let current = response.scanned;
                const total = parseInt(countSpan.next().text().replace('/ ', ''));

                countSpan.text(current);

                const percentage = (current / total) * 100;
                progressBar.css('width', percentage + '%');

                if (current < total) {
                    progressBar.removeClass('bg-success').addClass('bg-primary pulse');
                    statusIcon.html('<i class="fas fa-clock text-warning fa-lg"></i>');
                    row.removeClass('bg-light-success');
                }

                // If scanned becomes 0, we can remove the history item
                if (current === 0) {
                    $(`#history_${CSS.escape(boxNo)}`).addClass('animate__animated animate__fadeOutRight').fadeOut(function () {
                        $(this).remove();
                        if ($('#scan_history .list-group-item').length === 0) {
                            $('#no_scans_msg').show();
                        }
                    });
                } else {
                    // Update the sidebar text too if it exists
                    const existingHistory = $(`#history_${boxNo}`);
                    if (existingHistory.length) {
                        existingHistory.find('.text-muted:last').text(`${response.color_name || ''} | Count: ${response.scanned} / ${response.required}`);
                        existingHistory.addClass('animate__animated animate__shakeY');
                        setTimeout(() => existingHistory.removeClass('animate__animated animate__shakeY'), 1000);
                    }
                }
            }

            function setLoadingStatus(barcode) {
                scanStatus.removeClass('alert-secondary alert-success alert-danger').addClass('alert-info');
                scanStatus.html(`
                                                                    <div class="text-center">
                                                                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                                                        <div>Processing: <strong>${barcode}</strong></div>
                                                                    </div>
                                                                `);
            }

            function setSuccessStatus(message) {
                scanStatus.removeClass('alert-info alert-danger alert-secondary').addClass('alert-success border-success');
                scanStatus.html(`
                                                                    <div class="text-center animate__animated animate__fadeIn">
                                                                        <i class="fas fa-check-circle fa-2x mb-2 text-white"></i>
                                                                        <div class="h5 font-weight-bold text-white mb-0">${message}</div>
                                                                    </div>
                                                                `);

                // Reset status to "Ready" after 3 seconds
                setTimeout(() => {
                    if (scanStatus.hasClass('alert-success')) {
                        resetToReady();
                    }
                }, 3000);
            }

            function setErrorStatus(message) {
                scanStatus.removeClass('alert-info alert-success alert-secondary').addClass('alert-danger border-danger');
                scanStatus.html(`
                                                                    <div class="text-center animate__animated animate__shakeX">
                                                                        <i class="fas fa-exclamation-circle fa-2x mb-2 text-white"></i>
                                                                        <div class="h5 font-weight-bold text-white mb-0">${message}</div>
                                                                    </div>
                                                                `);
            }

            function resetToReady() {
                scanStatus.removeClass('alert-success alert-danger alert-info').addClass('alert-secondary border-secondary');
                scanStatus.html(`
                                                                    <div class="text-center">
                                                                        <i class="fas fa-qrcode fa-2x mb-2 text-white opacity-50"></i>
                                                                        <div class="font-weight-bold text-white">Ready to Scan</div>
                                                                    </div>
                                                                `);
            }

            function playBeep(success) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(success ? 880 : 220, ctx.currentTime);

                    gain.gain.setValueAtTime(0.1, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start();
                    osc.stop(ctx.currentTime + 0.1);
                } catch (e) {
                    console.log('Audio feedback failed', e);
                }
            }

            // Modal Calculation Logic
            function calculateDispatch(source) {
                const totalAmount = parseFloat($('#modal_total_amount').val()) || 0;
                const discountAmount = parseFloat($('#modal_discount_amount').val()) || 0;
                const otherCharges = parseFloat($('#modal_other_charges').val()) || 0;
                const taxableAmount = totalAmount - discountAmount;

                let gstPercentage = parseFloat($('#modal_gst_percentage').val()) || 0;
                let gstAmount = parseFloat($('#modal_gst_amount_input').val()) || 0;

                if (source === 'percentage') {
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#modal_gst_amount_input').val(gstAmount.toFixed(2));
                } else if (source === 'amount') {
                    if (taxableAmount > 0) {
                        gstPercentage = (gstAmount / taxableAmount) * 100;
                        $('#modal_gst_percentage').val(gstPercentage.toFixed(6));
                    } else {
                        $('#modal_gst_percentage').val(0);
                    }
                } else {
                    gstAmount = taxableAmount * (gstPercentage / 100);
                    $('#modal_gst_amount_input').val(gstAmount.toFixed(2));
                }

                const grandTotal = taxableAmount + gstAmount + otherCharges;
                $('#modal_grand_total_display').text('₹' + grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            }

            $('#modal_gst_percentage').on('input', function() { calculateDispatch('percentage'); });
            $('#modal_gst_amount_input').on('input', function() { calculateDispatch('amount'); });
            $('#modal_total_amount, #modal_discount_amount, #modal_other_charges').on('input', function() { calculateDispatch('default'); });

            $('#dispatchModal').on('show.bs.modal', function() {
                calculateDispatch('default');
            });
        });
    </script>
@endpush