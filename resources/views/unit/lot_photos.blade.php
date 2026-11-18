@extends('layouts.unit')

@section('title', 'Lot Photo')

@push('styles')
<link rel="stylesheet" href="{{ asset('admin_assets/plugins/select2/css/select2.min.css') }}">
<style>
    /* lot dropdown (Select2) in the app's own look, big enough for a thumb */
    .lpp-lot .select2-container { width: 100% !important; }
    .lpp-lot .select2-container .select2-selection--single { height: 48px; border: 2px solid var(--u-green); border-radius: 6px; }
    .lpp-lot .select2-container .select2-selection__rendered { line-height: 44px; font-size: 18px; font-weight: 800; color: var(--u-green); padding-left: 12px; }
    .lpp-lot .select2-container .select2-selection__placeholder { color: var(--u-muted); font-weight: 600; }
    .lpp-lot .select2-container .select2-selection__arrow { height: 44px; right: 6px; }
    .select2-dropdown { border: 2px solid var(--u-green); }
    .select2-search--dropdown .select2-search__field { height: 42px; font-size: 16px; border: 1px solid var(--u-line); border-radius: 6px; }
    .select2-results__option { padding: 10px 12px; font-size: 16px; font-weight: 700; }
    .select2-results__group { font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; color: var(--u-muted); background: var(--u-soft); }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--u-green); color: #fff; }
    .select2-container--default .select2-results > .select2-results__options { max-height: 46vh; }

    .lpp-drop { border: 2px dashed #cbd5c0; border-radius: 6px; background: #fff; padding: 26px 12px; text-align: center; color: var(--u-muted); font-size: 14px; font-weight: 600; }
    .lpp-drop i { display: block; font-size: 34px; color: var(--u-green); margin-bottom: 6px; }
    .lpp-preview { display: none; width: 100%; max-height: 420px; object-fit: contain; background: #f8fafc; border: 1px solid var(--u-line); border-radius: 6px; }
    .lpp-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
    .lpp-actions .xl-btn { height: 52px; font-size: 16px; }
    .lpp-none { text-align: center; color: var(--u-muted); font-size: 13px; padding: 14px 8px; }

    /* live camera popup (uses the layout's camera-modal look, like Send Slip) */
    .lpp-video-wrap { width: 100%; border-radius: 16px; overflow: hidden; background: #000; }
    .lpp-video-wrap video { display: block; width: 100%; min-height: 200px; max-height: 60vh; object-fit: contain; background: #000; }
    .lpp-video-wrap video.mirrored { transform: scaleX(-1); }
    .lpp-cam-switch { border: none; background: rgba(255, 255, 255, 0.1); color: #fff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    #lppCamCapture:disabled { opacity: 0.55; }
</style>
@endpush

@section('content')
    {{-- 1. the lot: only lots that were sent to this login, lots still in work first --}}
    <div class="xl-head" style="margin-top: 0;">
        <h3><i class="fas fa-box"></i> Select Lot</h3>
        <span class="xl-sub">{{ $lots->count() }} lot(s)</span>
    </div>
    <div class="xl-panel lpp-lot">
        @if($lots->isEmpty())
            <div class="lpp-none">No lot has been sent to you yet.</div>
        @else
            <select id="lppLot" aria-label="Lot number">
                <option value=""></option>
                @foreach(['In work' => true, 'Finished' => false] as $groupLabel => $inWork)
                    @php $groupLots = $lots->where('in_work', $inWork); @endphp
                    @if($groupLots->count())
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($groupLots as $l)
                                <option value="{{ $l->lot_no }}" {{ $selected && $selected->lot_no === $l->lot_no ? 'selected' : '' }}>{{ $l->lot_no }}{{ $l->design_no ? ' · ' . $l->design_no : '' }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                @endforeach
            </select>
        @endif
    </div>

    @if($selected)
        {{-- 2. the photo: take it, look at it, submit. After submit this page opens again with the same lot. --}}
        <div class="xl-head">
            <h3><i class="fas fa-camera"></i> Photo for Lot {{ $selected->lot_no }}</h3>
            @if($selected->design_no)
                <span class="xl-sub">Design {{ $selected->design_no }}</span>
            @endif
        </div>
        <div class="xl-panel">
            <form method="POST" action="{{ route('unit.lot-photos.store') }}" id="lppForm">
                @csrf
                <input type="hidden" name="lot_no" value="{{ $selected->lot_no }}">
                <input type="hidden" name="photo_data" id="lppPhotoData">

                <div class="lpp-drop" id="lppDrop"><i class="fas fa-camera"></i> Take a photo of this lot</div>
                <img class="lpp-preview" id="lppPreview" alt="Photo to send">

                <div class="lpp-actions" id="lppPick">
                    <button type="button" class="xl-btn allow-multiple-submit" id="lppCameraBtn"><i class="fas fa-camera"></i> Take Photo</button>
                    <button type="button" class="xl-btn xl-btn-outline allow-multiple-submit" id="lppGalleryBtn"><i class="fas fa-image"></i> Gallery</button>
                </div>
                <div class="lpp-actions" id="lppSend" style="display: none;">
                    <button type="submit" class="xl-btn" id="lppSubmitBtn"><i class="fas fa-check-circle"></i> Submit</button>
                    <button type="button" class="xl-btn xl-btn-danger allow-multiple-submit" id="lppRetakeBtn"><i class="fas fa-redo"></i> Retake</button>
                </div>
            </form>
            {{-- lppCameraInput is only the fallback (the phone's own camera app) when the live camera is not available --}}
            <input type="file" id="lppCameraInput" accept="image/*" capture="environment" style="display: none;">
            <input type="file" id="lppGalleryInput" accept="image/*" style="display: none;">
        </div>

        {{-- "Take Photo" opens the live camera here, the same way Send Slip does --}}
        <div id="lppCamModal" class="camera-modal-overlay">
            <div class="camera-modal">
                <div class="camera-modal-header">
                    <div class="camera-modal-title">
                        <i class="fas fa-camera" style="margin-right: 6px;"></i> Photo of Lot {{ $selected->lot_no }}
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button type="button" class="lpp-cam-switch" id="lppCamSwitch" title="Switch Camera"><i class="fas fa-sync-alt"></i></button>
                        <button type="button" class="camera-modal-close" id="lppCamClose">&times;</button>
                    </div>
                </div>
                <div class="camera-modal-body">
                    <div class="lpp-video-wrap">
                        <video id="lppVideo" autoplay playsinline muted></video>
                    </div>
                </div>
                <div class="camera-modal-footer">
                    <button type="button" class="btn-modal btn-modal-capture" id="lppCamCapture" disabled><i class="fas fa-camera"></i> Capture</button>
                    <button type="button" class="btn-modal btn-modal-secondary" id="lppCamCancel"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </div>
        </div>

        {{-- 3. what has been sent for this lot (this login can delete its own photos) --}}
        @include('unit.partials.lot_photos', ['lotPhotos' => $photos, 'myUnitId' => $unit->id, 'lotPhotoTitle' => 'Photos of This Lot', 'lotPhotoVisible' => true])
    @elseif($lots->isNotEmpty())
        <div class="lpp-none"><i class="fas fa-arrow-up"></i> Select a lot, then take its photo.</div>
    @endif
@endsection

@push('scripts')
<script src="{{ asset('admin_assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(function () {
        // ----- lot dropdown: type a lot number to find it; choosing a lot opens this page for that lot
        var $lot = $('#lppLot');
        if ($lot.length) {
            $lot.select2({ placeholder: 'Select lot', width: '100%' });
            $lot.on('change', function () {
                var lot = $(this).val();
                window.location = @json(route('unit.lot-photos')) + (lot ? '?lot_no=' + encodeURIComponent(lot) : '');
            });
        }

        if (!document.getElementById('lppForm')) return;

        // ----- photo: the phone makes it smaller (longest side 1600px, JPEG) before it is sent
        var MAX_SIDE = 1600;

        function loadImage(file) {
            if (window.createImageBitmap) {
                return createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () { return loadImageTag(file); });
            }
            return loadImageTag(file);
        }
        function loadImageTag(file) {
            return new Promise(function (resolve, reject) {
                var url = URL.createObjectURL(file);
                var img = new Image();
                img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
                img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('not an image')); };
                img.src = url;
            });
        }
        // src = a picture or the live camera video; w,h = its real size
        function drawJpeg(src, w, h) {
            var scale = Math.min(1, MAX_SIDE / Math.max(w, h));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(w * scale);
            canvas.height = Math.round(h * scale);
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(src, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.8);
        }
        function toJpeg(file) {
            return loadImage(file).then(function (src) {
                if (!src.width || !src.height) throw new Error('empty image');
                var dataUrl = drawJpeg(src, src.width, src.height);
                if (src.close) src.close();
                return dataUrl;
            });
        }

        function showPhoto(dataUrl) {
            $('#lppPhotoData').val(dataUrl);
            $('#lppDrop').hide();
            $('#lppPreview').attr('src', dataUrl).show();
            $('#lppPick').hide();
            $('#lppSend').css('display', 'grid');
        }
        function clearPhoto() {
            $('#lppPhotoData').val('');
            $('#lppPreview').hide().removeAttr('src');
            $('#lppDrop').show();
            $('#lppSend').hide();
            $('#lppPick').css('display', 'grid');
        }

        // ----- live camera: Take Photo opens it, Capture takes the picture
        var camModal = $('#lppCamModal');
        var camVideo = document.getElementById('lppVideo');
        var camStream = null;
        var camFacing = 'environment';   // back camera first
        var lastFromCamera = false;

        function stopCam() {
            if (camStream) {
                camStream.getTracks().forEach(function (t) { t.stop(); });
                camStream = null;
            }
            camVideo.srcObject = null;
            $('#lppCamCapture').prop('disabled', true);
        }
        function closeCam() {
            camModal.hide();
            stopCam();
        }
        function startCam() {
            stopCam();
            return navigator.mediaDevices.getUserMedia({
                audio: false,
                video: { facingMode: { ideal: camFacing }, width: { ideal: 1920 }, height: { ideal: 1080 } }
            }).then(function (stream) {
                if (!camModal.is(':visible')) {   // closed while the browser was asking for permission
                    stream.getTracks().forEach(function (t) { t.stop(); });
                    return;
                }
                camStream = stream;
                camVideo.srcObject = stream;
                $(camVideo).toggleClass('mirrored', camFacing === 'user');
            });
        }
        function camFailed() {
            closeCam();
            Swal.fire({ icon: 'info', title: 'Camera', text: 'The camera could not be opened. Please allow the camera, or use Gallery.' });
        }
        function openCam() {
            // no live camera in this browser (very old phone, or the page is not on https): use the phone's own camera app
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                $('#lppCameraInput').trigger('click');
                return;
            }
            camModal.css('display', 'flex');
            startCam().catch(camFailed);
        }
        // Capture works once the camera picture has a size
        camVideo.addEventListener('loadedmetadata', function () {
            $('#lppCamCapture').prop('disabled', !(camVideo.videoWidth && camVideo.videoHeight));
        });
        $('#lppCamSwitch').on('click', function () {
            camFacing = camFacing === 'environment' ? 'user' : 'environment';
            startCam().catch(camFailed);
        });
        $('#lppCamClose, #lppCamCancel').on('click', closeCam);
        $('#lppCamCapture').on('click', function () {
            var w = camVideo.videoWidth, h = camVideo.videoHeight;
            if (!w || !h) return;
            var dataUrl = drawJpeg(camVideo, w, h);
            closeCam();
            lastFromCamera = true;
            showPhoto(dataUrl);
        });
        window.addEventListener('pagehide', stopCam);   // leaving the page switches the camera off

        function onFile(input) {
            var file = input.files && input.files[0];
            input.value = ''; // the same photo can be picked again after Retake
            if (!file) return;
            lastFromCamera = false;
            $('#lppDrop').html('<i class="fas fa-spinner fa-spin"></i> Preparing photo...');
            toJpeg(file).then(showPhoto).catch(function () {
                $('#lppDrop').html('<i class="fas fa-camera"></i> Take a photo of this lot');
                Swal.fire({ icon: 'error', title: 'Error', text: 'This file is not a photo. Please take the photo again.' });
            });
        }

        $('#lppCameraBtn').on('click', openCam);
        $('#lppGalleryBtn').on('click', function () { $('#lppGalleryInput').trigger('click'); });
        $('#lppCameraInput, #lppGalleryInput').on('change', function () { onFile(this); });
        $('#lppRetakeBtn').on('click', function () {
            clearPhoto();
            $('#lppDrop').html('<i class="fas fa-camera"></i> Take a photo of this lot');
            if (lastFromCamera) openCam();   // a camera photo: straight back to the camera
        });

        $('#lppForm').on('submit', function (e) {
            if (!$('#lppPhotoData').val()) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Please take a photo first.' });
                return false;
            }
            $('#lppSubmitBtn').html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        });
    });
</script>
@endpush
