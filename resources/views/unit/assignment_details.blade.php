<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Assignment Details</title>

    <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --success: #10b981;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 90px;
        }

        /* Header */
        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 24px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
            border-top: 1px solid #f3f4f6;
            z-index: 1000;
        }

        .nav-item {
            text-decoration: none;
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            font-size: 11px;
            transition: all 0.3s;
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            text-decoration: none;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        /* Content */
        .app-content {
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
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

        /* Price Section */
        .price-card {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            position: relative;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .price-row:last-child {
            border-bottom: none;
            margin-top: 10px;
            padding-top: 15px;
        }

        .price-label {
            font-size: 14px;
            opacity: 0.7;
        }

        .price-value {
            font-size: 16px;
            font-weight: 600;
        }

        .price-total {
            font-size: 20px;
            font-weight: 800;
            color: #fbbf24;
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
</head>

<body>

    <div class="app-header">
        <div class="header-left">
            <a href="{{ route('unit.assignments') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="page-title">Details</div>
        </div>
        <a href="{{ route('unit.logout') }}" style="color: white; font-size: 20px;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>

    <div class="app-content">

        <!-- PRIMARY DETAILS -->
        <div class="card">
            <div class="card-header-badge">#{{ $header['id'] }}</div>
            <div class="section-title">
                <i class="fas fa-info-circle"></i> Production Info
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Order No</span>
                    <span class="info-value">{{ $header['order_no'] }}</span>
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
            </div>

            @if($header['remark'] && $header['remark'] != '-')
                <div
                    style="margin-top: 15px; padding: 12px; background: #fffbe6; border-radius: 12px; border: 1px solid #ffe58f; font-size: 13px; color: #856404;">
                    <strong>Note:</strong> {{ $header['remark'] }}
                </div>
            @endif
        </div>


        <!-- BREAKDOWN -->
        <div class="card">
            <div class="section-title">
                <i class="fas fa-list-ul"></i> Quantity Breakdown
            </div>

            <table class="breakdown-table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Color</th>
                        <th style="text-align: right;">PCS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sizeData as $row)
                        <tr>
                            <td><span class="size-badge">{{ $row['size'] }}</span></td>
                            <td>{{ $row['color'] }}</td>
                            <td style="text-align: right; font-weight: 700;">{{ $row['pcs'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f9fafb; font-weight: 800;">
                        <td colspan="2" style="padding: 15px 12px;">TOTAL QUANTITY</td>
                        <td style="text-align: right; padding: 15px 12px; color: var(--primary); font-size: 18px;">
                            {{ $header['total_pcs'] }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ACTIONS -->
        <div style="margin-bottom: 20px;">
            @if($type === 'cutting')
                <a href="{{ route('unit.download.cmpo', ['id' => $header['id']]) }}" class="btn-action btn-download"
                    style="margin-bottom: 12px;">
                    <i class="fas fa-file-pdf"></i> Download Manufacturing Slip
                </a>
            @elseif(isset($transaction->production_slip_digitization_id))
                <a href="{{ route('unit.download.slip', ['id' => $transaction->production_slip_digitization_id]) }}"
                    class="btn-action btn-download" style="margin-bottom: 12px;">
                    <i class="fas fa-file-pdf"></i> Download Incoming Slip
                </a>
            @endif
        </div>

        <!-- UPLOAD SECTION -->
        <div class="card" style="border: 2px solid var(--primary); background: #fdfdff;">
            <div class="section-title">
                <i class="fas fa-cloud-upload-alt"></i> Upload Output Slip
            </div>

            @if(session('success'))
                <div
                    style="padding: 12px; background: #d1fae5; color: #065f46; border-radius: 12px; margin-bottom: 15px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    style="padding: 12px; background: #fee2e2; color: #991b1b; border-radius: 12px; margin-bottom: 15px; font-size: 14px; font-weight: 600;">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form action="{{ route('unit.submit') }}" method="POST" id="uploadForm">
                @csrf
                <input type="hidden" name="stage_master_unit_id" value="{{ $encrypted_unit_id }}">
                <input type="hidden" name="type" value="{{ $type === 'cutting' ? '1' : '2' }}">
                <input type="hidden" name="order_product_set_id" value="{{ $header['id'] ?? '' }}">
                <input type="hidden" name="photo_data" id="photoData">

                <div id="placeholder" class="camera-box" style="margin-bottom: 20px;">
                    <div class="camera-icon">📸</div>
                    <div class="camera-text">Capture photograph or select file to upload</div>
                </div>

                <div id="videoContainer" style="display: none; margin-bottom: 20px;">
                    <video id="video" autoplay playsinline
                        style="width: 100%; border-radius: 16px; background: #000;"></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <button type="button" class="btn-action btn-upload" id="captureBtn" style="margin-top: 10px;">
                        <i class="fas fa-camera"></i> Capture Now
                    </button>
                </div>

                <img id="preview" src="" style="width: 100%; border-radius: 16px; display: none; margin-bottom: 20px;">

                <div id="initialBtns"
                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px;">
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
                    <button type="button" class="btn-action" id="retakeBtn"
                        style="background: #fff1f2; color: #e11d48;">
                        <i class="fas fa-redo"></i> Retake / Cancel
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let stream = null;

        function isMobile() {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        }

        function handleImage(dataUrl) {
            $('#placeholder').hide();
            $('#preview').attr('src', dataUrl).show();
            $('#photoData').val(dataUrl);
            $('#initialBtns').hide();
            $('#videoContainer').hide();
            $('#afterCaptureBtns').show();

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
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
                            reader.onload = e => handleImage(e.target.result);
                            reader.readAsDataURL(file);
                        }
                    });
                    input.trigger('click');
                } else {
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                        .then(s => {
                            stream = s;
                            $('#video')[0].srcObject = stream;
                            $('#placeholder').hide();
                            $('#videoContainer').show();
                        })
                        .catch(err => alert('Camera access denied'));
                }
            });

            $('#captureBtn').on('click', function () {
                const video = $('#video')[0];
                const canvas = $('#canvas')[0];
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                handleImage(canvas.toDataURL('image/jpeg', 0.9));
            });

            $('#selectFileBtn').on('click', () => $('#fileInput').trigger('click'));
            $('#fileInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = e => handleImage(e.target.result);
                    reader.readAsDataURL(file);
                }
            });

            $('#retakeBtn').on('click', function () {
                $('#preview').hide();
                $('#afterCaptureBtns').hide();
                $('#initialBtns').show();
                $('#videoContainer').hide();
                $('#placeholder').show();
                $('#photoData').val('');
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            });

            // Form validation before submit
            $('#uploadForm').on('submit', function (e) {
                const photoData = $('#photoData').val();
                if (!photoData || photoData.trim() === '') {
                    e.preventDefault();
                    alert('⚠️ Please capture or select a photo before submitting!');
                    return false;
                }
            });
        });
    </script>
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="{{ route('unit.dashboard') }}" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}" class="nav-item active">
            <i class="fas fa-clipboard-list"></i>
            <span>Tasks</span>
        </a>
        <a href="{{ route('unit.history') }}" class="nav-item">
            <i class="fas fa-clock"></i>
            <span>History</span>
        </a>
    </div>
</body>

</html>