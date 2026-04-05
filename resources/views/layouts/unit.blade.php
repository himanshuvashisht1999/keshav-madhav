<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Unit Dashboard')</title>
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
            background: rgba(15, 23, 42, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
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
            gap: 8px;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-modal-capture {
            background: var(--bg-gradient);
            color: white;
        }

        .btn-modal-secondary {
            background: #1f2937;
            color: #e5e7eb;
        }
        
        #modalPreview {
            width: 100%;
            border-radius: 16px;
            display: none;
            margin-bottom: 12px;
        }
        
        #videoContainer {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            margin-bottom: 12px;
        }
        
        #videoElement {
            width: 100%;
            display: block;
        }

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
        <a href="{{ route('unit.dashboard') }}" class="nav-item {{ request()->routeIs('unit.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('unit.assignments') }}" class="nav-item {{ request()->routeIs('unit.assignments') ? 'active' : '' }}">
            <i class="fas fa-clipboard-list"></i>
            <span>Tasks</span>
        </a>
        <a href="javascript:void(0)" class="nav-item open-upload-modal" 
           data-type="{{ (session('unit_auth')['stage_id'] ?? 0) == 3 ? '1' : '2' }}" 
           data-product-set-id="" data-transaction-id="" data-transaction-type="production">
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
                <button type="button" class="camera-modal-close" id="closeQuickModal">&times;</button>
            </div>
            <div class="camera-modal-body">
                <form action="{{ route('unit.submit') }}" method="POST" id="quickUploadForm">
                    @csrf
                    <input type="hidden" name="stage_master_unit_id" value="{{ \Illuminate\Support\Facades\Crypt::encryptString(session('unit_auth')['id']) }}">
                    <input type="hidden" name="type" id="modal_type" value="">
                    <input type="hidden" name="order_product_set_id" id="modal_product_set_id" value="">
                    <input type="hidden" name="transaction_id" id="modal_transaction_id" value="">
                    <input type="hidden" name="transaction_type" id="modal_transaction_type" value="">
                    @if($stageId == 3)
                    <div style="margin-bottom: 16px;">
                        <label class="camera-modal-title" style="display:block; margin-bottom:10px; font-size:12px;">Select Slip Type:</label>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <label style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
                                <input type="radio" name="save_type" value="1" checked style="accent-color: #667eea;">
                                <span style="font-size: 12px; font-weight: 600;">Rolls Allot</span>
                            </label>
                            <label style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
                                <input type="radio" name="save_type" value="3" style="accent-color: #667eea;">
                                <span style="font-size: 12px; font-weight: 600;">Stitching</span>
                            </label>
                            <label style="flex: 1; min-width: 100px; background: #1e293b; padding: 10px; border-radius: 10px; display: flex; align-items: center; gap: 8px; cursor: pointer; border: 1px solid #334155;">
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Quick Upload Logic
            let stream = null;
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
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: 'environment' } 
                    });
                    video.srcObject = stream;
                    videoContainer.show();
                    preview.hide();
                    initialControls.show();
                    confirmControls.hide();
                } catch (err) {
                    console.error("Error accessing camera: ", err);
                    alert("Unable to access camera. Please use the Gallery option.");
                }
            }

            $('.open-upload-modal').on('click', function() {
                const btn = $(this);
                $('#modal_type').val(btn.data('type'));
                $('#modal_product_set_id').val(btn.data('product-set-id'));
                $('#modal_transaction_id').val(btn.data('transaction-id'));
                $('#modal_transaction_type').val(btn.data('transaction-type'));
                
                modal.css('display', 'flex');
                startStream();
            });

            $('#closeQuickModal').on('click', function() {
                modal.hide();
                stopStream();
            });

            $('#captureQuickBtn').on('click', function() {
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

            $('#browseQuickBtn').on('click', function() {
                $('#quickFileInput').trigger('click');
            });

            $('#quickFileInput').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
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

            $('#retakeQuickBtn').on('click', function() {
                startStream();
            });

            $('#submitQuickBtn').on('click', function() {
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
        });
    </script>
    @stack('scripts')
</body>
</html>
