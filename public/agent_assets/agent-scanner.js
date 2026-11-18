/**
 * Camera scanner of the sales agent app (Create Order, Edit Order, Offline order).
 *
 * Built on html5-qrcode 2.3.8 (public/assets/js/html5-qrcode.min.js - load it first). The labels we print are small:
 * a Code128 line barcode (or a QR code) of short codes such as F3LP, D17S71C46, SMP-..., BX-....
 * The library's default settings could not read them (12 Nov 2026: tablet camera open, nothing ever read), so:
 * - a wide scan box (a line barcode is wide and short) and only the two formats we print;
 * - a sharper camera picture, continuous focus and 2x camera zoom when the camera allows it, so a label held about
 *   15 cm away (where the camera can focus) still looks big enough;
 * - html5-qrcode reads the box shrunk to its size on screen (about 400 px, too few for thin bars), so the same box is
 *   also read at full camera size, by the phone's own barcode reader when it has one (Android Chrome), else ZXing;
 * - a tip under the camera after a few seconds without a read; one result per opening; closing while the camera
 *   is still starting is safe.
 *
 * Usage:
 *   const cam = AgentScanner.create('reader', { onScan: function (text) {...}, onError: function (err) {...} });
 *   cam.start();   // when the scanner box opens
 *   cam.stop();    // when it closes
 */
(function (window, document) {
    'use strict';

    var HINT_AFTER_MS = 7000;
    var FULL_SIZE_EVERY_MS = 200;
    var MAX_READ_WIDTH = 1000;   // px of camera picture read per try (enough for a 3 cm barcode filling a third of the box)
    // Parts of the scan box (from its centre) read in turn, smallest first: ZXing misses a label when blue denim shows
    // beside it in the same picture, and a small centre part favours the code being aimed at over a neighbouring tag.
    var BOX_PARTS = [0.2, 0.35, 0.55, 0.8, 1];
    var WANTED_ZOOM = 2;
    var CAMERA_SIZE = { width: { ideal: 1280 }, height: { ideal: 960 } };
    var HINT_TEXT = 'Not reading? Hold the label about 15 cm from the camera in good light, with the whole barcode '
        + 'inside the box. Or type the code printed under the barcode below.';

    /** Scan box inside a picture of w x h: 90% of the width, at most 70% of the height and 0.62 of its own width. */
    function boxFor(w, h) {
        var bw = Math.floor(w * 0.9);
        var bh = Math.floor(Math.min(h * 0.7, bw * 0.62));
        return { width: Math.max(bw, 50), height: Math.max(bh, 50) };
    }

    function libraryFormats() {
        var F = window.Html5QrcodeSupportedFormats;
        return F ? [F.QR_CODE, F.CODE_128] : undefined;
    }

    function create(elementId, options) {
        var opts = options || {};
        var scanner = null;     // html5-qrcode object while the camera starts or runs
        var starting = null;    // promise: true once the camera runs, false when it could not start
        var stopping = null;    // promise of the last stop (a new start waits for it)
        var finished = false;   // a code was read in this opening
        var timers = [];
        var hintEl = null;
        var fullSizeReader = null;

        function later(fn, ms, repeat) {
            timers.push({ id: repeat ? setInterval(fn, ms) : setTimeout(fn, ms), repeat: !!repeat });
        }

        function clearTimers() {
            timers.forEach(function (t) { if (t.repeat) clearInterval(t.id); else clearTimeout(t.id); });
            timers = [];
        }

        function showHint(show) {
            if (opts.hint === false) return;
            if (!hintEl && show) {
                var reader = document.getElementById(elementId);
                if (!reader || !reader.parentNode) return;
                hintEl = document.createElement('div');
                hintEl.className = 'alert alert-warning small py-2 px-3 mt-2 mb-0 text-left agent-scan-hint';
                hintEl.setAttribute('role', 'status');
                hintEl.textContent = HINT_TEXT;
                reader.parentNode.insertBefore(hintEl, reader.nextSibling);
            }
            if (hintEl) hintEl.style.display = show ? '' : 'none';
        }

        function found(text) {
            if (finished || text === undefined || text === null || String(text).trim() === '') return;
            finished = true;
            stop();
            if (opts.onScan) opts.onScan(String(text).trim());
        }

        function fail(err) {
            if (opts.onError) opts.onError(err);
        }

        /** Continuous focus and 2x zoom, when this camera has them (most Android phones and tablets). */
        function improveCamera(s) {
            var caps;
            try { caps = s.getRunningTrackCapabilities(); } catch (e) { return; }
            if (!caps) return;
            var wanted = {};
            if (caps.focusMode && caps.focusMode.indexOf && caps.focusMode.indexOf('continuous') !== -1) {
                wanted.focusMode = 'continuous';
            }
            if (caps.zoom && typeof caps.zoom.max === 'number' && caps.zoom.max > 1) {
                wanted.zoom = Math.max(caps.zoom.min || 1, Math.min(caps.zoom.max, WANTED_ZOOM));
            }
            if (!Object.keys(wanted).length) return;
            // applyConstraints replaces the earlier set, so the picture size is asked for again
            try {
                Promise.resolve(s.applyVideoConstraints({ width: CAMERA_SIZE.width, height: CAMERA_SIZE.height, advanced: [wanted] }))
                    .catch(function () {});
            } catch (e) { /* camera refused: keep the picture as it is */ }
        }

        /** A second html5-qrcode object that never opens the camera: it only reads pictures we give it. */
        function boxReader() {
            if (fullSizeReader) return fullSizeReader;
            try {
                var host = document.createElement('div');
                host.id = 'agent-scan-fullsize-' + elementId;
                host.style.display = 'none';
                document.body.appendChild(host);
                fullSizeReader = new window.Html5Qrcode(host.id, {
                    verbose: false,
                    formatsToSupport: libraryFormats(),
                    experimentalFeatures: { useBarCodeDetectorIfSupported: true }
                });
            } catch (e) {
                fullSizeReader = null;
            }
            return fullSizeReader;
        }

        /** Text of the code in the canvas (the phone's own reader when it has one, else ZXing); rejects when none. */
        function decodeCanvas(reader, canvas) {
            if (reader.qrcode && typeof reader.qrcode.decodeRobustlyAsync === 'function') {
                return reader.qrcode.decodeRobustlyAsync(canvas).then(function (r) { return r && r.text; });
            }
            return new Promise(function (resolve) { canvas.toBlob(resolve, 'image/png'); }).then(function (blob) {
                return reader.scanFile(new File([blob], 'scan.png', { type: 'image/png' }), false);
            });
        }

        /** html5-qrcode reads the box shrunk to its size on screen; read the same box at full camera size as well. */
        function readBoxFullSize(s) {
            var video = document.querySelector('#' + elementId + ' video');
            var reader = video ? boxReader() : null;
            if (!reader) return;
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var busy = false;
            var turn = 0;
            later(function () {
                if (busy || finished || scanner !== s || video.readyState < 2 || !video.videoWidth) return;
                var vw = video.videoWidth, vh = video.videoHeight, box = boxFor(vw, vh);
                var part = BOX_PARTS[turn++ % BOX_PARTS.length];
                var cw = Math.round(box.width * part), ch = Math.round(box.height * Math.max(part, 0.4));
                var scale = Math.min(1, MAX_READ_WIDTH / cw);
                canvas.width = Math.round(cw * scale);
                canvas.height = Math.round(ch * scale);
                ctx.drawImage(video, (vw - cw) / 2, (vh - ch) / 2, cw, ch, 0, 0, canvas.width, canvas.height);
                busy = true;
                decodeCanvas(reader, canvas).then(function (text) {
                    busy = false;
                    if (scanner === s) found(text);
                }, function () { busy = false; });
            }, FULL_SIZE_EVERY_MS, true);
        }

        function start() {
            if (scanner) return starting;
            if (typeof window.Html5Qrcode === 'undefined') {
                fail('Scanner library not loaded');
                return Promise.resolve(false);
            }
            finished = false;
            showHint(false);
            var s;
            try {
                s = new window.Html5Qrcode(elementId, {
                    verbose: false,
                    formatsToSupport: libraryFormats(),
                    experimentalFeatures: { useBarCodeDetectorIfSupported: true }
                });
            } catch (e) {
                fail(e);
                return Promise.resolve(false);
            }
            scanner = s;
            starting = Promise.resolve(stopping).then(function () {
                if (scanner !== s) return false;   // closed before the camera was asked for
                return s.start({ facingMode: 'environment' }, {
                    fps: 10,
                    qrbox: boxFor,
                    disableFlip: true,
                    videoConstraints: {
                        facingMode: 'environment',
                        width: CAMERA_SIZE.width,
                        height: CAMERA_SIZE.height,
                        advanced: [{ focusMode: 'continuous' }]
                    }
                }, found, function () { /* no code in this picture */ }).then(function () {
                    if (scanner !== s) return true;    // closed while the camera was starting: stop() ends it
                    improveCamera(s);
                    readBoxFullSize(s);
                    later(function () { if (scanner === s && !finished) showHint(true); }, HINT_AFTER_MS);
                    return true;
                });
            }).catch(function (err) {
                if (scanner === s) {          // no message when the box was already closed
                    scanner = null;
                    starting = null;
                    fail(err);
                }
                return false;
            });
            return starting;
        }

        function stop() {
            clearTimers();
            showHint(false);
            var s = scanner, started = starting;
            scanner = null;
            starting = null;
            if (!s) return stopping || Promise.resolve();
            stopping = Promise.resolve(started).then(function (running) {
                if (!running) return;
                return s.stop().then(function () { try { s.clear(); } catch (e) { /* already cleared */ } });
            }).catch(function () { /* camera already closed */ });
            return stopping;
        }

        return { start: start, stop: stop };
    }

    window.AgentScanner = { create: create, boxFor: boxFor };
})(window, document);
