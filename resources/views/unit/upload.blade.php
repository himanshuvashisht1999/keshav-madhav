<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Upload Production Slip</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            --secondary: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.12);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        /* App Header */
        .app-header {
            background: var(--bg-gradient);
            padding: 16px 20px 20px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .unit-name-badge {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            text-decoration: none;
        }

        .icon-btn:active {
            transform: scale(0.9);
            background: rgba(255,255,255,0.3);
        }

        .unit-info-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .info-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
        }

        .info-card-icon {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .info-card-label {
            font-size: 11px;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .info-card-value {
            font-size: 13px;
            font-weight: 700;
        }

        /* Main Content */
        .app-content {
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert {
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        /* Camera Box */
        .camera-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: #f9fafb;
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        video, #preview {
            width: 100%;
            border-radius: 20px;
            display: none;
        }

        canvas {
            display: none;
        }

        #placeholder {
            text-align: center;
            color: #9ca3af;
        }

        .placeholder-icon {
            font-size: 60px;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .placeholder-text {
            font-size: 16px;
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 18px;
            font-size: 17px;
            font-weight: 700;
            border-radius: 16px;
            border: none;
            cursor: pointer;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            letter-spacing: 0.3px;
            transition: all 0.3s;
            box-shadow: var(--shadow);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: var(--bg-gradient);
            color: white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
        }

        .btn i {
            font-size: 20px;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 12px 20px 20px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-around;
            z-index: 1000;
            border-top: 1px solid #f3f4f6;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #9ca3af;
            transition: all 0.3s;
            padding: 8px 16px;
            border-radius: 12px;
        }

        .nav-item.active {
            color: var(--primary);
            background: rgba(102, 126, 234, 0.1);
        }

        .nav-item i {
            font-size: 22px;
        }

        .nav-label {
            font-size: 12px;
            font-weight: 600;
        }

        /* Animations */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* Mobile optimizations */
        @media (max-width: 480px) {
            .app-header {
                padding: 16px 16px 24px;
            }

            .app-content {
                padding: 16px;
            }

            .card {
                padding: 20px;
            }

            .unit-info-cards {
                gap: 10px;
            }

            .info-card {
                padding: 10px;
            }
        }

        /* Safe area for notched devices */
        @supports (padding: max(0px)) {
            .app-header {
                padding-top: max(20px, env(safe-area-inset-top));
            }

            .bottom-nav {
                padding-bottom: max(20px, env(safe-area-inset-bottom));
            }
        }
    </style>
</head>
<body>

<!-- App Header -->
<div class="app-header">
    <div class="header-top">
        <div class="unit-name-badge">
            <i class="fas fa-industry"></i>
            <span>{{ $data->name }}</span>
        </div>
        <div class="header-actions">
            <a href="{{ route('unit.history') }}" class="icon-btn">
                <i class="fas fa-history"></i>
            </a>
            <a href="{{ route('unit.logout') }}" class="icon-btn">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <div class="unit-info-cards">
        <div class="info-card">
            <div class="info-card-icon">📞</div>
            <div class="info-card-label">Phone</div>
            <div class="info-card-value">{{ substr($data->phone ?? '-', -4) }}</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">🧵</div>
            <div class="info-card-label">Stage</div>
            <div class="info-card-value">{{ Str::limit($data->masterStage->name ?? '-', 8) }}</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">🏭</div>
            <div class="info-card-label">Warehouse</div>
            <div class="info-card-value">{{ Str::limit($data->masterFabricWarehouse->cutting_master_name ?? '-', 8) }}</div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="app-content">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="card">
        <div class="card-title">
            <i class="fas fa-camera" style="color: var(--primary);"></i>
            Upload Production Slip
        </div>

        <form action="{{ route('unit.submit') }}" method="POST" id="uploadForm">
            @csrf
            <input type="hidden" name="stage_master_unit_id" value="{{ $stage_master_unit_id }}">
            <input type="hidden" name="photo_data" id="photoData">
            <input type="hidden" name="type" value="2">

            <div class="camera-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <img id="preview">
                <div id="placeholder">
                    <div class="placeholder-icon pulse">📷</div>
                    <div class="placeholder-text">Ready to capture</div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;" id="initialButtons">
                <button type="button" class="btn btn-primary" id="openCameraBtn" style="margin: 0;">
                    <i class="fas fa-camera"></i>
                    <span>Camera</span>
                </button>
                
                <button type="button" class="btn btn-primary" id="selectFileBtn" style="margin: 0; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);">
                    <i class="fas fa-image"></i>
                    <span>Gallery</span>
                </button>
            </div>

            <input type="file" id="fileInput" accept="image/*" style="display: none;">

            <button type="button" class="btn btn-secondary" id="captureBtn" style="display: none;">
                <i class="fas fa-camera"></i>
                <span>Capture Photo</span>
            </button>

            <button type="submit" class="btn btn-success" id="uploadBtn" style="display: none;">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Upload Slip</span>
            </button>

            <button type="button" class="btn btn-warning" id="retakeBtn" style="display: none;">
                <i class="fas fa-redo"></i>
                <span>Retake Photo</span>
            </button>
        </form>
    </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="{{ route('unit.dashboard') }}" class="nav-item active">
        <i class="fas fa-camera"></i>
        <span class="nav-label">Upload</span>
    </a>
    <a href="{{ route('unit.history') }}" class="nav-item">
        <i class="fas fa-history"></i>
        <span class="nav-label">History</span>
    </a>
    <a href="{{ route('unit.logout') }}" class="nav-item">
        <i class="fas fa-sign-out-alt"></i>
        <span class="nav-label">Logout</span>
    </a>
</div>

<script>
let stream = null;

function isMobile() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

function handleFileSelect(file) {
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (ev) {
        $('#preview').attr('src', ev.target.result).show();
        $('#placeholder').hide();
        $('#initialButtons').hide();
        $('#uploadBtn').show();
        $('#retakeBtn').show();
        $('#photoData').val(ev.target.result);
    };
    reader.readAsDataURL(file);
}

function openCamera() {
    $('#initialButtons').hide();
    $('#placeholder').hide();

    // MOBILE - Direct camera capture
    if (isMobile()) {
        const input = $('<input>', {
            type: 'file',
            accept: 'image/*',
            capture: 'environment'
        });

        input.on('change', function (e) {
            const file = e.target.files[0];
            if (!file) {
                $('#initialButtons').show();
                $('#placeholder').show();
                return;
            }

            handleFileSelect(file);
        });

        input.trigger('click');
        return;
    }

    // DESKTOP - Webcam stream
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment',
            width: { ideal: 1920 },
            height: { ideal: 1080 }
        } 
    })
        .then(function (s) {
            stream = s;
            $('#video')[0].srcObject = stream;
            $('#video').show();
            $('#captureBtn').show();
        })
        .catch(() => {
            alert('Camera permission denied');
            $('#initialButtons').show();
            $('#placeholder').show();
        });
}

$(document).ready(function () {
    // Auto-hide success alerts
    setTimeout(() => $('.alert-success').fadeOut(), 3000);

    // Open camera
    $('#openCameraBtn').on('click', openCamera);

    // Select file from gallery
    $('#selectFileBtn').on('click', function() {
        $('#fileInput').trigger('click');
    });

    // Handle file selection
    $('#fileInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelect(file);
        }
    });

    // Capture photo (desktop only)
    $('#captureBtn').on('click', function () {
        const video = $('#video')[0];
        const canvas = $('#canvas')[0];

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        $('#video').hide();
        $('#captureBtn').hide();
        $('#preview').attr('src', dataUrl).show();
        $('#uploadBtn').show();
        $('#retakeBtn').show();
        $('#photoData').val(dataUrl);

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    });

    // Retake photo
    $('#retakeBtn').on('click', function () {
        $('#preview').hide().attr('src', '');
        $('#photoData').val('');
        $('#uploadBtn').hide();
        $('#retakeBtn').hide();
        $('#video').hide();
        $('#captureBtn').hide();
        $('#fileInput').val('');

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        $('#initialButtons').show();
        $('#placeholder').show();
    });
});
</script>

</body>
</html>