@extends('layouts.unit')

@section('title', 'Assignment Details')

@section('header_icon')
    <a href="javascript:history.back()" style="color: white; margin-right: 10px;">
        <i class="fas fa-arrow-left"></i>
    </a>
@endsection

@section('header_right')
    <a href="{{ route('unit.assignments.download', ['type' => $type, 'id' => $header['id'] ?? $transaction->id]) }}" style="color: white; font-size: 20px;">
        <i class="fas fa-file-download"></i>
    </a>
@endsection

@push('styles')
    <style>
        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .card-header-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: rgba(102, 126, 234, 0.1);
            padding: 8px 16px;
            border-bottom-left-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }

        .info-value {
            font-size: 14px;
            color: #1f2937;
            font-weight: 600;
        }

        .info-full {
            grid-column: span 2;
        }

        /* Breakdown Table */
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .breakdown-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 12px 0;
            border-bottom: 2px solid #f3f4f6;
        }

        .breakdown-table td {
            padding: 12px 0;
            font-size: 14px;
            color: #1f2937;
            font-weight: 500;
            border-bottom: 1px solid #f9fafb;
        }

        .size-badge {
            background: #eff6ff;
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
        }

        /* Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-download {
            background: #f3f4f6;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .btn-upload {
            background: var(--bg-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        /* Camera Section */
        .camera-box {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 20px;
            padding: 40px 20px;
            text-align: center;
            margin-top: 20px;
        }

        .camera-icon {
            font-size: 40px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .camera-text {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }

        #preview {
            width: 100%;
            border-radius: 16px;
            display: none;
            margin-top: 15px;
        }
    </style>
@endpush

@section('content')
    <!-- PRIMARY DETAILS -->
    <div class="card">
        <div class="card-header-badge">#{{ $header['id'] }}</div>
        @if($isRework)
            <div
                style="position: absolute; top: 0; left: 0; padding: 8px 16px; background: #e11d48; color: white; font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom-right-radius: 20px;">
                <i class="fas fa-exclamation-triangle mr-1"></i> REWORK TASK
            </div>
        @endif
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Production Info
        </div>

        <div class="info-grid">
            <div class="info-item">
                <!-- <span class="info-label">Order No</span> -->
                <!-- <span class="info-value">{{ $header['order_no'] }}</span> -->
                <span class="info-label">CMPO No</span>
                <span class="info-value">CMPO-{{ $header['id'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Total Pieces</span>
                <span class="info-value" style="color: #4f46e5; font-weight: 800; font-size: 16px;">{{ $header['total_pcs'] ?? 0 }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                @php
                    $isCompleted = !empty($transaction->image) || (isset($transaction->is_closed_for_unit) && $transaction->is_closed_for_unit == 1);
                    $endDate = $header['end_date'] ?? null;
                    $isDelayed = !$isCompleted && $endDate && now()->startOfDay() > \Carbon\Carbon::parse($endDate)->startOfDay();
                    
                    if ($isCompleted) {
                        $statusClass = 'color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; font-size: 12px; display: inline-block; width: max-content;';
                        $statusText = 'Completed';
                    } elseif ($isDelayed) {
                        $statusClass = 'color: #b91c1c; background: #fee2e2; padding: 2px 8px; border-radius: 6px; font-size: 12px; display: inline-block; width: max-content;';
                        $statusText = 'Delayed';
                    } else {
                        $statusClass = 'color: #a16207; background: #fef9c3; padding: 2px 8px; border-radius: 6px; font-size: 12px; display: inline-block; width: max-content;';
                        $statusText = 'Pending';
                    }
                @endphp
                <span class="info-value" style="{{ $statusClass }}">{{ $statusText }}</span>
            </div>

            <div class="info-item">
                <span class="info-label">Date</span>
                <span class="info-value">{{ $header['date'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Design No</span>
                <span class="info-value">{{ $header['design_no'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Lot No</span>
                <span class="info-value text-blue">{{ $header['lot_no'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fabric</span>
                <span class="info-value">{{ $header['fabric'] }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Color</span>
                <span class="info-value">{{ $header['color'] }}</span>
            </div>

            @if($type === 'cutting')
                <div class="info-item info-full">
                    <span class="info-label">Warehouse (Cutting Master)</span>
                    <span class="info-value">{{ $header['warehouse'] }} ({{ $header['unit_name'] }})</span>
                </div>
            @else
                <div class="info-item">
                    <span class="info-label">From Stage</span>
                    <span class="info-value">{{ $header['from_stage'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Sent By</span>
                    <span class="info-value">{{ $header['sent_by'] }}</span>
                </div>
            @endif

            <div class="info-item info-full">
                <span class="info-label">Pattern & Fitting</span>
                <span class="info-value">{{ $header['pattern'] }} | {{ $header['fitting'] }}</span>
            </div>
            <div class="info-item info-full">
                <span class="info-label">Belt</span>
                <span class="info-value">{{ $header['belt'] }}</span>
            </div>
        </div>

        @if(isset($header['start_date']) || isset($header['end_date']))
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #f1f5f9;">
            <div class="section-title">
                <i class="fas fa-clock"></i> Timing Info
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Start Date</span>
                    <span class="info-value">{{ $header['start_date'] ? date('d M Y, h:i A', strtotime($header['start_date'])) : '-' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Expected End</span>
                    <span class="info-value @if(!$header['complete_date'] && now() > $header['end_date']) text-danger @endif">
                        {{ $header['end_date'] ? date('d M Y, h:i A', strtotime($header['end_date'])) : '-' }}
                        @if(!$header['complete_date'] && now() > $header['end_date'])
                            <span style="font-size: 10px; color: #dc2626; font-weight: 800; text-transform: uppercase;">(Delayed)</span>
                        @endif
                    </span>
                </div>
                @if($header['complete_date'])
                <div class="info-item info-full">
                    <span class="info-label">Completed At</span>
                    <span class="info-value text-success">{{ date('d M Y, h:i A', strtotime($header['complete_date'])) }}</span>
                </div>
                @endif
            </div>
        @endif
    </div>

        @if($header['remark'] && $header['remark'] != '-')
            <div
                style="margin-top: 15px; padding: 12px; background: #fffbe6; border-radius: 12px; border: 1px solid #ffe58f; font-size: 13px; color: #856404;">
                <strong>Note:</strong> {{ $header['remark'] }}
            </div>
        @endif
    </div>




    @if(isset($transaction->productionSlipDigitization->slip_file))
        <div class="card">
            <div class="section-title">
                <i class="fas fa-image"></i> Previous Stage Slip
            </div>
            <div style="text-align: center;">
                <img src="{{ asset('assets/production_slips/' . $transaction->productionSlipDigitization->slip_file) }}" style="max-width: 100%; max-height: 400px; border-radius: 12px; border: 1px solid #e2e8f0; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                <div style="font-size: 11px; color: #64748b; margin-top: 8px;">Tap image to view full screen</div>
            </div>
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
                    style="background: #eef2ff; color: var(--primary); border: 2px solid #e0e7ff;">
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