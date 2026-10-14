@extends('layouts.unit')

@section('title', 'Assignment Details')

@section('header_icon')
    <a href="javascript:history.back()" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@section('header_right')
    <a href="{{ route('unit.assignments.download', ['type' => $type, 'id' => $header['id'] ?? $transaction->id]) }}" target="_blank" download="Assignment_{{ $header['id'] ?? $transaction->id }}.pdf" style="color: white; font-size: 20px;">
        <i class="fas fa-file-download"></i>
    </a>
@endsection

@push('styles')
    <style>
        .ad-rework { background: #e11d48; color: #fff; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; padding: 8px 12px; border-radius: 6px; margin-bottom: 10px; }
        .ad-lot { font-size: 16px; font-weight: 800; color: var(--u-green); }
        .ad-pcs { font-size: 16px; font-weight: 800; color: var(--u-green); }
        .ad-note { background: #fffbd6; border: 1px solid #f3e27a; border-radius: 6px; padding: 9px 12px; font-size: 13px; color: #6b5a00; margin-top: 10px; }
        .ad-prev { text-align: center; }
        .ad-prev img { max-width: 100%; max-height: 420px; border: 1px solid var(--u-line); border-radius: 4px; cursor: pointer; }

        /* hidden upload form + desktop camera popup (kept for the existing camera code) */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-upload { background: var(--u-green); color: white; }
        .camera-box { background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px; padding: 40px 20px; text-align: center; margin-top: 20px; }
        .camera-icon { font-size: 40px; color: #9ca3af; margin-bottom: 10px; }
        .camera-text { font-size: 14px; color: #6b7280; font-weight: 500; }
        #preview { width: 100%; border-radius: 8px; display: none; margin-top: 15px; }
    </style>
@endpush

@section('content')
    @php
        $isCompleted = !empty($transaction->image) || (isset($transaction->is_closed_for_unit) && $transaction->is_closed_for_unit == 1);
        $endDate = $header['end_date'] ?? null;
        $completeDate = $header['complete_date'] ?? null;
        $isDelayed = !$isCompleted && $endDate && now()->startOfDay() > \Carbon\Carbon::parse($endDate)->startOfDay();
        $endLate = !$completeDate && $endDate && now() > \Carbon\Carbon::parse($endDate);
        $fmt = fn($v) => fmod((float) $v, 1) == 0.0 ? number_format((float) $v) : rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
        $sizeTotal = collect($sizeData)->sum('pcs');
    @endphp

    @if($isRework)
        <div class="ad-rework"><i class="fas fa-exclamation-triangle"></i> Rework Task</div>
    @endif

    <!-- PRODUCTION INFO -->
    <div class="xl-head">
        <h3><i class="fas fa-info-circle"></i> CMPO-{{ $header['id'] }}</h3>
        @if($isCompleted)
            <span class="xl-pill xl-pill-done">Completed</span>
        @elseif($isDelayed)
            <span class="xl-pill xl-pill-late">Delayed</span>
        @else
            <span class="xl-pill xl-pill-open">Pending</span>
        @endif
    </div>
    <div class="xl-wrap">
        <table class="xl-kv">
            <tbody>
                <tr><th>Lot No</th><td class="ad-lot">{{ $header['lot_no'] }}</td></tr>
                <tr><th>Design No</th><td class="xl-strong">{{ $header['design_no'] }}</td></tr>
                <tr><th>Total Pieces</th><td class="ad-pcs">{{ $fmt($header['total_pcs'] ?? 0) }}</td></tr>
                <tr><th>Size Set</th><td>{{ $header['size_set'] ?? '-' }}</td></tr>
                <tr><th>Date</th><td>{{ $header['date'] }}</td></tr>
                <tr><th>Fabric</th><td>{{ $header['fabric'] }}</td></tr>
                <tr><th>Color</th><td>{{ $header['color'] }}</td></tr>
                <tr><th>Pattern</th><td>{{ $header['pattern'] }}</td></tr>
                <tr><th>Fitting</th><td>{{ $header['fitting'] }}</td></tr>
                <tr><th>Belt</th><td>{{ $header['belt'] }}</td></tr>
                @if($type === 'cutting')
                    <tr><th>Cutting Master</th><td>{{ $header['warehouse'] }} ({{ $header['unit_name'] }})</td></tr>
                @else
                    <tr><th>From Stage</th><td>{{ $header['from_stage'] }}</td></tr>
                    <tr><th>Sent By</th><td>{{ $header['sent_by'] }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($header['remark'] && $header['remark'] != '-')
        <div class="ad-note"><strong>Note:</strong> {{ $header['remark'] }}</div>
    @endif

    @if(isset($header['start_date']) || isset($header['end_date']))
        <div class="xl-head">
            <h3><i class="fas fa-clock"></i> Timing</h3>
        </div>
        <div class="xl-wrap">
            <table class="xl-kv">
                <tbody>
                    <tr><th>Start Date</th><td>{{ $header['start_date'] ? date('d M Y, h:i A', strtotime($header['start_date'])) : '-' }}</td></tr>
                    <tr>
                        <th>Expected End</th>
                        <td class="{{ $endLate ? 'xl-late' : '' }}">
                            {{ $endDate ? date('d M Y, h:i A', strtotime($endDate)) : '-' }}
                            @if($endLate)
                                <span class="xl-pill xl-pill-late">Delayed</span>
                            @endif
                        </td>
                    </tr>
                    @if($completeDate)
                        <tr><th>Completed At</th><td class="xl-strong" style="color: #166534;">{{ date('d M Y, h:i A', strtotime($completeDate)) }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($sizeData))
        <div class="xl-head">
            <h3><i class="fas fa-ruler-horizontal"></i> Size Breakdown</h3>
        </div>
        <div class="xl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Size</th>
                        <th>Color</th>
                        <th class="xl-num">Pieces</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sizeData as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="xl-strong">{{ $row['size'] }}</td>
                            <td>{{ $row['color'] ?? '-' }}</td>
                            <td class="xl-num">{{ $fmt($row['pcs']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="2">Total</td>
                        <td class="xl-num">{{ $fmt($sizeTotal) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    @if(isset($transaction->productionSlipDigitization->slip_file))
        <div class="xl-head">
            <h3><i class="fas fa-image"></i> Previous Stage Slip</h3>
        </div>
        <div class="xl-panel ad-prev">
            <img src="{{ asset('assets/production_slips/' . $transaction->productionSlipDigitization->slip_file) }}" alt="Previous stage slip" onclick="window.open(this.src, '_blank')"
                 onerror="this.style.display='none'; this.nextElementSibling.textContent='Slip image file not found.';">
            <div class="xl-sub" style="margin-top: 6px;">Tap image to view full screen</div>
        </div>
    @endif

    <!-- ACTIONS -->
    {{-- Download moved to header icon --}}

    <!-- UPLOAD SECTION -->
    <div class="card" style="border: 2px solid var(--primary); background: #fdfdff;display:none;">
        <div class="section-title">
            <i class="fas fa-cloud-upload-alt"></i> Upload Output Slip
        </div>

        <form action="{{ route('unit.submit') }}" method="POST" id="uploadForm">
            @csrf
            <input type="hidden" name="stage_master_unit_id" value="{{ $encrypted_unit_id }}">
            <input type="hidden" name="type" value="{{ $isRework ? 'rework' : ($type === 'cutting' ? '1' : '2') }}">
            <input type="hidden" name="order_product_set_id" value="{{ $header['id'] ?? '' }}">
            <input type="hidden" name="transaction_id" value="{{ $transaction->id ?? '' }}">
            <input type="hidden" name="transaction_type" value="{{ $type }}">
            <input type="hidden" name="photo_data" id="photoData">

            @if($type === 'cutting')
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Next
                            Stage</label>
                        <select name="to_stage_id" class="btn-action"
                            style="width: 100%; background: white; color: #1e293b; border: 1.5px solid #cbd5e1; text-align: left; padding: 10px;"
                            required>
                            <option value="">Select Next Stage</option>
                            @foreach($nextStages as $ns)
                                <option value="{{ $ns->id }}">{{ $ns->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Assign Lot
                            No</label>
                        <input type="text" name="lot_no" class="btn-action"
                            style="width: 100%; background: white; color: #1e293b; border: 1.5px solid #cbd5e1; text-align: left; padding: 10px;"
                            placeholder="e.g. 601" required>
                    </div>
                </div>
            @endif

            <div id="placeholder" class="camera-box" style="margin-bottom: 20px;">
                <div class="camera-icon">📸</div>
                <div class="camera-text">Capture photograph or select file to upload</div>
            </div>

            <img id="preview" src="" style="width: 100%; border-radius: 16px; display: none; margin-bottom: 20px;">

            <div id="initialBtns" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px;">
                <button type="button" class="btn-action" id="openCameraBtn"
                    style="background: #eaf4e2; color: var(--primary); border: 2px solid #eaf4e2;">
                    <i class="fas fa-camera"></i> Camera
                </button>
                <button type="button" class="btn-action" id="selectFileBtn"
                    style="background: #ecfdf5; color: #059669; border: 2px solid #d1fae5;">
                    <i class="fas fa-image"></i> Gallery
                </button>
            </div>
            <input type="file" id="fileInput" accept="image/*" style="display: none;">

            <div id="afterCaptureBtns" style="display: none; margin-top: 20px;">
                <button type="submit" class="btn-action btn-upload" style="margin-bottom: 12px;">
                    <i class="fas fa-check-circle"></i> Submit Document
                </button>
                <button type="button" class="btn-action" id="retakeBtn" style="background: #fff1f2; color: #e11d48;">
                    <i class="fas fa-redo"></i> Retake / Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Camera Modal for Desktop Only (Legacy Support) -->
    <div id="pageCameraModal" class="camera-modal-overlay" style="display: none;">
        <div class="camera-modal">
            <div class="camera-modal-header">
                <div class="camera-modal-title">
                    <i class="fas fa-camera" style="margin-right:6px;"></i> Capture Output Slip
                </div>
                <button type="button" class="camera-modal-close" id="closePageCameraModal">&times;</button>
            </div>
            <div class="camera-modal-body">
                <div id="pageVideoContainer" style="margin-bottom: 12px; display:none;">
                    <video id="pageVideo" autoplay playsinline
                        style="width: 100%; border-radius: 16px; background: #000;"></video>
                    <canvas id="pageCanvas" style="display: none;"></canvas>
                </div>
                <img id="pageModalPreview" src=""
                    style="width: 100%; border-radius: 16px; display: none; margin-bottom: 12px;">
            </div>
            <div class="camera-modal-footer">
                <div id="pageCameraLiveControls" style="display:flex; gap:10px; width:100%;">
                    <button type="button" class="btn-action btn-upload" id="pageCaptureBtn">
                        <i class="fas fa-camera"></i> Capture
                    </button>
                    <button type="button" class="btn-action" id="cancelPageCameraBtn"
                        style="background:#111827; color:#e5e7eb; border:1px solid #4b5563;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>

                <div id="pageCameraCapturedControls" style="display:none; gap:10px; width:100%;">
                    <button type="button" class="btn-action btn-upload" id="submitFromPageModalBtn">
                        <i class="fas fa-check-circle"></i> Submit
                    </button>
                    <button type="button" class="btn-action" id="retakeFromPageModalBtn"
                        style="background:#fff1f2; color:#e11d48;">
                        <i class="fas fa-redo"></i> Retake
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let localStream = null;

        function isMobile() {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        }

        function handleLocalImage(dataUrl) {
            $('#placeholder').hide();
            $('#preview').attr('src', dataUrl).show();
            $('#photoData').val(dataUrl);
            $('#initialBtns').hide();
            $('#afterCaptureBtns').show();
            $('#pageCameraModal').hide();
            stopLocalStream();
        }

        function stopLocalStream() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
        }

        $(document).ready(function () {
            $('#openCameraBtn').on('click', function () {
                if (isMobile()) {
                    const input = $('<input>', { type: 'file', accept: 'image/*', capture: 'environment' });
                    input.on('change', function (e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => handleLocalImage(e.target.result);
                            reader.readAsDataURL(file);
                        }
                    });
                    input.trigger('click');
                } else {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                        .then(s => {
                            localStream = s;
                            $('#pageVideo')[0].srcObject = localStream;
                            $('#pageCameraModal').show();
                            $('#pageVideoContainer').show();
                            $('#pageCameraLiveControls').css('display', 'flex');
                            $('#pageCameraCapturedControls').hide();
                            $('#pageModalPreview').hide();
                        })
                        .catch(err => alert('Camera access denied'));
                }
            });

            $('#pageCaptureBtn').on('click', function () {
                const video = $('#pageVideo')[0];
                const canvas = $('#pageCanvas')[0];
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                handleLocalImage(dataUrl);
                $('#pageModalPreview').attr('src', dataUrl).show();
                $('#pageVideoContainer').hide();
                $('#pageCameraLiveControls').hide();
                $('#pageCameraCapturedControls').css('display', 'flex');
            });

            $('#selectFileBtn').on('click', () => $('#fileInput').trigger('click'));
            $('#fileInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => handleLocalImage(e.target.result);
                    reader.readAsDataURL(file);
                }
            });

            $('#retakeBtn').on('click', function () {
                $('#preview').hide();
                $('#afterCaptureBtns').hide();
                $('#initialBtns').show();
                $('#placeholder').show();
                $('#photoData').val('');
                stopLocalStream();
            });

            $('#closePageCameraModal, #cancelPageCameraBtn').on('click', () => {
                $('#pageCameraModal').hide();
                stopLocalStream();
            });

            $('#submitFromPageModalBtn').on('click', () => $('#uploadForm').submit());
            $('#retakeFromPageModalBtn').on('click', () => $('#openCameraBtn').click());

            $('#uploadForm').on('submit', function (e) {
                if (!$('#photoData').val()) {
                    e.preventDefault();
                    alert('⚠️ Please capture or select a photo before submitting!');
                }
            });
        });
    </script>
@endpush