<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload Production Slip</title>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f4f6f9;
        }

        .container {
            max-width: 420px;
            margin: auto;
            padding: 20px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 4px 10px rgba(0,0,0,.08);
            text-align: center;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .camera-box {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 12px;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        video, img {
            width: 100%;
            border-radius: 6px;
            display: none;
        }

        canvas {
            display: none;
        }

        .btn {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            margin-bottom: 8px;
        }

        .btn-camera {
            background: #007bff;
            color: #fff;
        }

        .btn-capture {
            background: #17a2b8;
            color: #fff;
            display: none;
        }

        .btn-upload {
            background: #28a745;
            color: #fff;
            display: none;
        }

        .btn-retake {
            background: #ffc107;
            color: #000;
            display: none;
        }

        .unit-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
            text-align: left;
        }

        .unit-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }

        .unit-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 4px;
            color: #555;
        }

    </style>
</head>
<body>

<div class="container">
    <div class="card">

        <div class="unit-details">
            <div class="unit-title">{{ $data->name }}</div>

            <div class="unit-row">
                <span>📞 Phone</span>
                <strong>{{ $data->phone ?? '-' }}</strong>
            </div>

            <div class="unit-row">
                <span>🧵 Stage</span>
                <strong>{{ $data->masterStage->name ?? '-' }}</strong>
            </div>

            <div class="unit-row">
                <span>🏭 Warehouse</span>
                <strong>{{ $data->masterFabricWarehouse->cutting_master_name ?? '-' }}</strong>
            </div>
        </div>


        <h2>Take Photo & Upload</h2>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('submitProductionSlip') }}" method="POST">
            @csrf

            <input type="hidden" name="stage_master_unit_id" value="{{ $stage_master_unit_id }}">
            <input type="hidden" name="photo_data" id="photoData">
            <input type="hidden" name="type" value="2">

            <div class="camera-box">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
                <img id="preview">
                <span id="placeholder">Camera is off</span>
            </div>

            <button type="button" class="btn btn-camera" id="openCameraBtn">
                Open Camera
            </button>

            <button type="button" class="btn btn-capture" id="captureBtn">
                Take Photo
            </button>

            <button type="submit" class="btn btn-upload" id="uploadBtn">
                Upload Photo
            </button>

            <button type="button" class="btn btn-retake" id="retakeBtn">
                Retake Photo
            </button>

        </form>
    </div>
</div>

<script>
let stream = null;

function isMobile() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

function openCamera() {

    $('#openCameraBtn').hide();
    $('#placeholder').hide();

    // MOBILE
    if (isMobile()) {
        const input = $('<input>', {
            type: 'file',
            accept: 'image/*',
            capture: 'environment'
        });

        input.on('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (ev) {
                $('#preview').attr('src', ev.target.result).show();
                $('#uploadBtn').show();
                $('#retakeBtn').show();
                $('#photoData').val(ev.target.result);
            };
            reader.readAsDataURL(file);
        });

        input.trigger('click');
        return;
    }

    // DESKTOP
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (s) {
            stream = s;
            $('#video')[0].srcObject = stream;
            $('#video').show();
            $('#captureBtn').show();
        })
        .catch(() => alert('Camera permission denied'));
}

$(document).ready(function () {

    setTimeout(() => $('.alert-success').fadeOut(), 3000);

    // Open camera
    $('#openCameraBtn').on('click', function () {
        openCamera();
    });

    // Capture photo
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

    // RETAKE → DIRECT OPEN CAMERA
    $('#retakeBtn').on('click', function () {

        $('#preview').hide().attr('src', '');
        $('#photoData').val('');
        $('#uploadBtn').hide();
        $('#retakeBtn').hide();

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }

        openCamera(); // 🔥 direct reopen camera
    });

});
</script>

</body>
</html>
