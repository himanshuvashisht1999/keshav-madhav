<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Unit Dashboard')</title>
    <link rel="stylesheet" href="{{asset('admin_assets/plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin_assets/plugins/bootstrap/css/bootstrap.min.css')}}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        :root {
            --primary: #667eea;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }

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
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
        }

        .app-content {
            padding: 20px;
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
            cursor: pointer;
            flex: 1;
        }

        .nav-item i {
            font-size: 20px;
        }

        .nav-item.active {
            color: var(--primary);
        }

        @media (max-width: 480px) {
            .app-header {
                padding-top: max(16px, env(safe-area-inset-top));
            }

            .bottom-nav {
                padding-bottom: max(24px, env(safe-area-inset-bottom));
            }
        }

        /* Camera Modal STYLES */
        .camera-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            display: none;
            align-items: flex-start;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .camera-modal {
            width: 100%;
            max-width: 420px;
            background: #0f172a;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.6);
            color: #e5e7eb;
        }

        .camera-modal-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }

        .camera-modal-title {
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .camera-modal-close {
            border: none;
            background: transparent;
            color: #9ca3af;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .camera-modal-body {
            padding: 12px;
            background: radial-gradient(circle at top, #111827 0, #020617 55%, #020617 100%);
        }

        .camera-modal-footer {
            padding: 12px;
            display: flex;
            gap: 10px;
            background: #020617;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }

        .btn-modal {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-modal:active {
            transform: scale(0.96);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .btn-modal-capture {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .btn-modal-secondary {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        #videoContainer {
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            background: #000;
            margin-bottom: 16px;
            max-height: 50vh;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        #videoElement {
            width: 100%;
            height: auto;
            min-height: 300px;
            max-height: 50vh;
            display: block;
            object-fit: cover;
        }

        #videoElement.mirrored {
            transform: scaleX(-1);
        }

        #canvasElement {
            display: none;
        }

        #modalPreview {
            width: 100%;
            height: auto;
            max-height: 50vh;
            border-radius: 20px;
            display: none;
            margin: 0 auto 16px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        #toggleCameraBtn {
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        #toggleCameraBtn:active {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0.9);
        }
    </style>
    @stack('styles')
    @php
        $stageId = session('unit_auth')['stage_id'] ?? 0;
        $uploadLabel = ($stageId == 3) ? 'Fabric' : 'Piece';
        $uploadIcon = ($stageId == 3) ? 'fa-cut' : 'fa-tshirt';
    @endphp
</head>

<body>

    <div class="app-header">
        <div class="header-top">
            <div class="page-title">
                @yield('header_icon') @yield('title')
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                @yield('header_right')
                <a href="{{ route('unit.lot.search') }}" style="color: white; font-size: 20px;" title="Track Lot">
                    <i class="fas fa-search"></i>
                </a>
                <a href="{{ route('unit.logout') }}" style="color: white; font-size: 20px;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="app-content">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="{{ route('unit.dashboard') }}"
            class="nav-item {{ request()->routeIs('unit.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}"
            class="nav-item {{ request()->routeIs('unit.assignments') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i>
            <span>Tasks</span>
        </a>
        <a href="javascript:void(0)" class="nav-item open-upload-modal"
            data-type="{{ (session('unit_auth')['stage_id'] ?? 0) == 3 ? '1' : '2' }}" data-product-set-id=""
            data-transaction-id="" data-transaction-type="production">
            <i class="fas fa-camera"></i>
            <span>Upload</span>
        </a>
        <a href="{{ route('unit.history') }}" class="nav-item {{ request()->routeIs('unit.history') ? 'active' : '' }}">
            <i class="fas fa-clock"></i>
            <span>History</span>
        </a>
    </div>

    <!-- Quick Upload Modal -->
    <div id="quickUploadModal" class="camera-modal-overlay">
        <div class="camera-modal">
            <div class="camera-modal-header">
                <div class="camera-modal-title">
                    <i class="fas {{ $uploadIcon }}" style="margin-right:6px;"></i> Capture {{ $uploadLabel }}
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <button type="button" id="toggleCameraBtn" title="Switch Camera">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="camera-modal-close" id="closeQuickModal">&times;</button>
                </div>
            </div>
            <div class="camera-modal-body">
                <form action="{{ route('unit.submit') }}" method="POST" id="quickUploadForm">
                    @csrf
                    <input type="hidden" name="stage_master_unit_id"
                        value="{{ \Illuminate\Support\Facades\Crypt::encryptString(session('unit_auth')['id']) }}">
                    <input type="hidden" name="type" id="modal_type" value="">
                    <input type="hidden" name="order_product_set_id" id="modal_product_set_id" value="">
                    <input type="hidden" name="transaction_id" id="modal_transaction_id" value="">
                    <input type="hidden" name="transaction_type" id="modal_transaction_type" value="">
                    @if($stageId == 3)
                        <div style="margin-bottom: 16px;">
                            <label class="camera-modal-title"
                                style="display:block; margin-bottom:10px; font-size:12px;">Select Slip Type:</label>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                <label
                                    style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
                                    <input type="radio" name="save_type" value="1" checked style="accent-color: #667eea;">
                                    <span style="font-size: 12px; font-weight: 600;">Rolls Allot</span>
                                </label>
                                <label
                                    style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
                                    <input type="radio" name="save_type" value="3" style="accent-color: #667eea;">
                                    <span style="font-size: 12px; font-weight: 600;">Stitching</span>
                                </label>
                                <label
                                    style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
                                    <input type="radio" name="save_type" value="2" style="accent-color: #667eea;">
                                    <span style="font-size: 12px; font-weight: 600;">Printing</span>
                                </label>
                            </div>
                        </div>
                    @endif
                    <input type="hidden" name="photo_data" id="quickPhotoData">
                    <input type="file" id="quickFileInput" accept="image/*" style="display: none;">

                    <div id="videoContainer">
                        <video id="videoElement" autoplay playsinline></video>
                        <canvas id="canvasElement"></canvas>
                    </div>

                    <img id="modalPreview" src="" alt="Preview">
                </form>
            </div>

            <div class="camera-modal-footer">
                <div id="modalInitialControls" style="display: flex; gap: 10px; width: 100%;">
                    <button type="button" class="btn-modal btn-modal-capture" id="captureQuickBtn">
                        <i class="fas fa-camera"></i> Capture
                    </button>
                    <button type="button" class="btn-modal btn-modal-secondary" id="browseQuickBtn">
                        <i class="fas fa-image"></i> Gallery
                    </button>
                </div>

                <div id="modalConfirmControls" style="display: none; gap: 10px; width: 100%;">
                    <button type="button" class="btn-modal btn-modal-capture" id="submitQuickBtn">
                        <i class="fas fa-check"></i> Submit
                    </button>
                    <button type="button" class="btn-modal btn-modal-secondary" id="retakeQuickBtn">
                        <i class="fas fa-redo"></i> Retake
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{asset('admin_assets/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick Upload Logic
            let stream = null;
            let currentFacingMode = 'environment';
            const modal = $('#quickUploadModal');
            const video = document.getElementById('videoElement');
            const canvas = document.getElementById('canvasElement');
            const preview = $('#modalPreview');
            const videoContainer = $('#videoContainer');
            const initialControls = $('#modalInitialControls');
            const confirmControls = $('#modalConfirmControls');

            function stopStream() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    stream = null;
                }
            }

            async function startStream() {
                stopStream();
                try {
                    const constraints = {
                        video: {
                            facingMode: { ideal: currentFacingMode },
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    };
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    video.srcObject = stream;

                    // Apply mirror effect if using front camera
                    if (currentFacingMode === 'user') {
                        $(video).addClass('mirrored');
                    } else {
                        $(video).removeClass('mirrored');
                    }

                    videoContainer.show();
                    preview.hide();
                    initialControls.show();
                    confirmControls.hide();
                } catch (err) {
                    console.error("Error accessing camera: ", err);
                    Swal.fire({
                        icon: 'info',
                        title: 'Camera Access',
                        text: 'Unable to access the ' + currentFacingMode + ' camera. You can try switching or use the Gallery option.',
                        confirmButtonText: 'OK'
                    });
                }
            }

            $('#toggleCameraBtn').on('click', function () {
                currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
                startStream();
            });

            $('.open-upload-modal').on('click', function () {
                const btn = $(this);
                $('#modal_type').val(btn.data('type'));
                $('#modal_product_set_id').val(btn.data('product-set-id'));
                $('#modal_transaction_id').val(btn.data('transaction-id'));
                $('#modal_transaction_type').val(btn.data('transaction-type'));

                modal.css('display', 'flex');
                startStream();
            });

            $('#closeQuickModal').on('click', function () {
                modal.hide();
                stopStream();
            });

            $('#captureQuickBtn').on('click', function () {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);

                $('#quickPhotoData').val(dataUrl);
                preview.attr('src', dataUrl).show();
                videoContainer.hide();
                initialControls.hide();
                confirmControls.css('display', 'flex');
                stopStream();
            });

            $('#browseQuickBtn').on('click', function () {
                $('#quickFileInput').trigger('click');
            });

            $('#quickFileInput').on('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        const dataUrl = event.target.result;
                        $('#quickPhotoData').val(dataUrl);
                        preview.attr('src', dataUrl).show();
                        videoContainer.hide();
                        initialControls.hide();
                        confirmControls.css('display', 'flex');
                        stopStream();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#retakeQuickBtn').on('click', function () {
                startStream();
            });

            $('#submitQuickBtn').on('click', function () {
                const btn = $(this);

                // If cutting master, sync radio selection to hidden type if needed
                if ($('input[name="save_type"]:checked').length > 0) {
                    $('#modal_type').val($('input[name="save_type"]:checked').val());
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                $('#quickUploadForm').submit();
            });

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    timer: 3000
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ $errors->first() }}'
                });
            @endif

            // Prevent double form submission in Unit portal
            $(document).on('click', 'form button:not(.allow-multiple-submit), form input[type="submit"]:not(.allow-multiple-submit), form input[type="button"]:not(.allow-multiple-submit)', function (e) {
                var $btn = $(this);
                var $form = $btn.closest('form');

                if ($form.hasClass('allow-multiple-submit')) {
                    return;
                }

                if ($form.length && $form[0].checkValidity && !$form[0].checkValidity()) {
                    return;
                }

                if ($btn.data('clicked') === true) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }

                $btn.data('clicked', true);
                var originalPointerEvents = $btn.css('pointer-events');
                $btn.css('pointer-events', 'none');

                setTimeout(function() {
                    $btn.prop('disabled', true);
                }, 10);

                setTimeout(function() {
                    $btn.data('clicked', false);
                    $btn.prop('disabled', false);
                    $btn.css('pointer-events', originalPointerEvents || 'auto');
                }, 5000);
            });

            $(document).on('submit', 'form', function (e) {
                var $form = $(this);

                if ($form.hasClass('allow-multiple-submit')) {
                    return;
                }

                if (this.checkValidity && !this.checkValidity()) {
                    return;
                }

                if ($form.data('submitted') === true) {
                    e.preventDefault();
                    return false;
                }

                $form.data('submitted', true);
                var $buttons = $form.find('button, input[type="submit"], input[type="button"]');
                $buttons.css('pointer-events', 'none');

                setTimeout(function() {
                    if (e.isDefaultPrevented()) {
                        $form.data('submitted', false);
                        $buttons.css('pointer-events', 'auto');
                    } else {
                        $buttons.prop('disabled', true);
                        
                        setTimeout(function() {
                            $form.data('submitted', false);
                            $buttons.prop('disabled', false);
                            $buttons.css('pointer-events', 'auto');
                        }, 5000);
                    }
                }, 10);
            });
        });
    </script>
    @stack('scripts')
</body>

</html>