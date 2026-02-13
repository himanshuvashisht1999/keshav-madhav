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
                    <a href="{{ route('admin.agent-orders.show', $order->id) }}"
                        class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Details
                    </a>
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
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle mr-1"></i> Field auto-focuses; simply scan or type
                                            and press Enter.
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
                                                            S: {{ $group['size_set_name'] }}
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
                            <div class="card-footer bg-white text-right">
                                <p class="small text-muted mb-3">Totals and prices are automatically updated as you scan
                                    actual box quantities.</p>
                                <form action="{{ route('admin.agent-orders.dispatch', $order->id) }}" method="POST"
                                    onsubmit="return confirm('Ensure all items are scanned. Proceed?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg px-5 shadow rounded-pill">
                                        <i class="fas fa-truck mr-2"></i> COMPLETE DISPATCH
                                    </button>
                                </form>
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
                                        <div class="list-group-item" id="history_{{ $box->packing_box_id }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="font-weight-bold">Box #{{ $box->box_no }}</div>
                                                    <small class="text-muted d-block">{{ $box->product_name }}</small>
                                                    <small class="text-muted">Design: {{ $box->design_number }} | Qty:
                                                        {{ $box->quantity }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger undo-btn"
                                                    data-id="{{ $box->packing_box_id }}" title="Undo this scan">
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

            // Refocus if focus is lost (except if scanning is already happening)
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.input-group').length) {
                    barcodeInput.focus();
                }
            });

            barcodeInput.on('keypress', function (e) {
                if (e.which == 13) { // Enter key
                    const barcode = $(this).val().trim();
                    if (barcode) {
                        processScan(barcode);
                    }
                    $(this).val('');
                }
            });

            function processScan(barcode) {
                setLoadingStatus(barcode);

                $.ajax({
                    url: "{{ route('admin.agent-orders.process-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        barcode: barcode
                    },
                    success: function (response) {
                        barcodeInput.focus(); // Ensure focus after AJAX
                        if (response.success) {
                            setSuccessStatus(response.message);
                            updateUI(response.variation_key, barcode);
                            playBeep(true);
                        } else {
                            setErrorStatus(response.message);
                            playBeep(false);
                        }
                    },
                    error: function () {
                        barcodeInput.focus();
                        setErrorStatus('Server error occurred.');
                        playBeep(false);
                    }
                });
            }

            // Remove Scan Logic
            $(document).on('click', '.undo-btn', function () {
                const boxId = $(this).data('id');
                const btn = $(this);

                if (!confirm('Are you sure you want to remove this scan?')) return;

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: "{{ route('admin.agent-orders.remove-scan', $order->id) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        packing_box_id: boxId
                    },
                    success: function (response) {
                        if (response.success) {
                            setSuccessStatus("Scan removed successfully");
                            revertUI(response.variation_key, boxId);
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

            function updateUI(key, barcode) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);

                let current = parseInt(countSpan.text());
                const totalText = countSpan.next().text().replace('/ ', '');
                const total = parseInt(totalText);

                current++;
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

                // Append to history (we don't have all details here, but we can fetch them or just show barcode)
                $('#no_scans_msg').hide();
                const historyItem = `
                            <div class="list-group-item animate__animated animate__fadeInDown" id="history_${barcode}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="font-weight-bold">Box # ${barcode}</div>
                                        <small class="text-white bg-success px-2 rounded small">Just Scanned</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger undo-btn" 
                                            data-id="${barcode}" 
                                            title="Undo this scan">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                $('#scan_history').prepend(historyItem);
            }

            function revertUI(key, boxId) {
                const countSpan = $(`#count_${key}`);
                const progressBar = $(`#progress_bar_${key}`);
                const statusIcon = $(`#status_icon_${key}`);
                const row = $(`#row_${key}`);

                let current = parseInt(countSpan.text());
                const total = parseInt(countSpan.next().text().replace('/ ', ''));

                current = Math.max(0, current - 1);
                countSpan.text(current);

                const percentage = (current / total) * 100;
                progressBar.css('width', percentage + '%');

                if (current < total) {
                    progressBar.removeClass('bg-success').addClass('bg-primary pulse');
                    statusIcon.html('<i class="fas fa-clock text-warning fa-lg"></i>');
                    row.removeClass('bg-light-success');
                }

                $(`#history_${CSS.escape(boxId)}`).addClass('animate__animated animate__fadeOutRight').fadeOut(function () {
                    $(this).remove();
                    if ($('#scan_history .list-group-item').length === 0) {
                        $('#no_scans_msg').show();
                    }
                });
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
        });
    </script>
@endpush