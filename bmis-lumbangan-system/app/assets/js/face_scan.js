/**
 * Face Scan & Liveness Verification
 * Uses face-api.js (vladmandic fork) for face detection, landmarks, and embeddings.
 *
 * Flow:
 *   Step 0 — Load models
 *   Step 1 — Position face (camera on)
 *   Step 2 — Blink twice (EAR liveness)
 *   Step 3 — Turn head left
 *   Step 4 — Turn head right
 *   Step 5 — Extract embedding, check duplicate, callback
 */

(function () {
    'use strict';

    // ─── CONFIG ───────────────────────────────────────────────────────────────
    const MODELS_PATH = (function () {
        // BASE_URL = http://host/Lumbangan_BMIS/bmis-lumbangan-system/app/
        // We want:  http://host/Lumbangan_BMIS/bmis-lumbangan-system/app/assets/js/face-api/models
        const meta = document.querySelector('meta[name="base-url"]');
        const base = meta ? meta.content.replace(/\/$/, '') : '';
        return base + '/assets/js/face-api/models';
    })();

    const BASE_URL_API = (function () {
        // BASE_URL ends in /app/ — we need the root project URL for the /api/ folder
        const meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            // Strip trailing /app or /app/
            return meta.content.replace(/\/app\/?$/, '');
        }
        return '';
    })();

    const EAR_BLINK_THRESHOLD = 0.22;   // below this → eye closed
    const EAR_OPEN_THRESHOLD  = 0.27;   // above this → eye open
    const HEAD_TURN_THRESHOLD = 0.28;   // nose offset ratio for left/right turn
    const BLINKS_REQUIRED     = 2;
    const LIVENESS_TIMEOUT_MS = 15000;

    // ─── STATE ────────────────────────────────────────────────────────────────
    let modelsLoaded = false;
    let cameraStream  = null;
    let detectionLoop = null;
    let livenessTimeout = null;
    let livenessTimeoutInterval = null;

    let pendingFormData = null;
    let onSuccessCallback = null;

    let blinkCount       = 0;
    let lastEyeState     = 'open';   // 'open' | 'closed'
    let blinkDebounceTimer = null;

    let capturedEmbedding  = null;
    let capturedFaceImage  = null;   // base64 JPEG of the face

    // ─── PUBLIC API ───────────────────────────────────────────────────────────
    window.openFaceScanModal = function (formData, onSuccess) {
        pendingFormData    = formData;
        onSuccessCallback  = onSuccess;
        capturedEmbedding  = null;
        capturedFaceImage  = null;

        const modal = document.getElementById('faceScanModal');
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        showFaceStep(0);
        resetStepIndicators();

        if (modelsLoaded) {
            // Models already loaded — skip straight to step 1
            startCamera().then(() => showFaceStep(1));
        } else {
            loadModels();
        }
    };

    window.closeFaceScanModal = function () {
        stopAll();
        const modal = document.getElementById('faceScanModal');
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    };

    window.startLivenessCheck = function () {
        showFaceStep(2);
        setStepIndicatorActive(2);
        blinkCount   = 0;
        lastEyeState = 'open';
        updateBlinkCircles();
        startLivenessTimeout('livenessTimeoutBar', () => {
            stopDetectionLoop();
            showStepError(2, 'No blink detected. Please try again.');
        });
        startBlinkDetection();
    };

    window.retryLiveness = function () {
        stopAll();
        blinkCount       = 0;
        lastEyeState     = 'open';
        capturedEmbedding = null;
        capturedFaceImage = null;
        resetStepIndicators();
        startCamera().then(() => {
            showFaceStep(1);
            clearStepErrors();
        });
    };

    // ─── MODEL LOADING ────────────────────────────────────────────────────────
    async function loadModels() {
        const loadBar    = document.getElementById('faceModelLoadBar');
        const loadStatus = document.getElementById('faceModelLoadStatus');
        const loadError  = document.getElementById('faceModelLoadError');

        const setProgress = (pct, msg) => {
            if (loadBar)    loadBar.style.width = pct + '%';
            if (loadStatus) loadStatus.textContent = msg;
        };

        try {
            setProgress(5, 'Loading face detector...');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_PATH);
            setProgress(40, 'Loading landmark detector...');
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_PATH);
            setProgress(75, 'Loading recognition model...');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_PATH);
            setProgress(100, 'Models loaded!');

            modelsLoaded = true;

            await new Promise(r => setTimeout(r, 400));
            await startCamera();
            showFaceStep(1);

        } catch (err) {
            console.error('[FaceScan] Model load error:', err);
            if (loadError) {
                loadError.style.display = 'block';
                loadError.querySelector('span').textContent =
                    'Failed to load face models. Check your connection and reload the page.';
            }
        }
    }

    // ─── CAMERA ───────────────────────────────────────────────────────────────
    async function startCamera() {
        stopCamera();
        const video = document.getElementById('faceScanVideo');
        if (!video) return;

        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } },
                audio: false
            });
            video.srcObject = cameraStream;
            await new Promise((res, rej) => {
                video.onloadedmetadata = res;
                video.onerror = rej;
            });
            video.play();

            // Ensure video is in step 1 container (re-parent if needed)
            moveVideoTo('faceScanVideoContainer');

        } catch (err) {
            console.error('[FaceScan] Camera error:', err);
            showStepError(1, 'Cannot access camera. Please allow camera permission and try again.');
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(t => t.stop());
            cameraStream = null;
        }
    }

    function moveVideoTo(containerId) {
        const video   = document.getElementById('faceScanVideo');
        const overlay = document.getElementById('faceScanOverlay');
        const target  = document.getElementById(containerId);
        if (video && target && !target.contains(video)) {
            target.innerHTML = '';
            target.appendChild(video);
            if (overlay) target.appendChild(overlay);
        }
    }

    // ─── STEP 1: FACE POSITIONING ─────────────────────────────────────────────
    function startFacePositionLoop() {
        stopDetectionLoop();
        const video = document.getElementById('faceScanVideo');
        const dot   = document.getElementById('faceDetectDot');
        const label = document.getElementById('faceDetectLabel');
        const btn   = document.getElementById('faceScanStartBtn');

        if (!video) return;

        detectionLoop = setInterval(async () => {
            if (!video.readyState || video.paused) return;
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                .withFaceLandmarks();

            if (detection) {
                if (dot)   { dot.style.background   = '#48bb78'; }
                if (label) { label.textContent = 'Face detected — click Start Verification'; }
                if (btn)   {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.background = '#667eea';
                }
            } else {
                if (dot)   { dot.style.background   = '#fc8181'; }
                if (label) { label.textContent = 'No face detected — center your face'; }
                if (btn)   {
                    btn.disabled = true;
                    btn.style.opacity = '0.5';
                }
            }
        }, 400);
    }

    // ─── STEP 2: BLINK DETECTION ─────────────────────────────────────────────
    function startBlinkDetection() {
        stopDetectionLoop();
        const video = document.getElementById('faceScanVideo');
        if (!video) return;

        detectionLoop = setInterval(async () => {
            if (!video.readyState || video.paused) return;
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                .withFaceLandmarks();

            if (!detection) return;

            const landmarks = detection.landmarks;
            const leftEAR   = getEAR(landmarks.getLeftEye());
            const rightEAR  = getEAR(landmarks.getRightEye());
            const avgEAR    = (leftEAR + rightEAR) / 2;

            const statusEl = document.getElementById('blinkStatus');

            if (avgEAR < EAR_BLINK_THRESHOLD && lastEyeState === 'open') {
                // Eye closed
                lastEyeState = 'closed';
                clearTimeout(blinkDebounceTimer);
                blinkDebounceTimer = setTimeout(() => {
                    if (lastEyeState === 'closed') {
                        // Confirmed blink
                    }
                }, 50);
            } else if (avgEAR > EAR_OPEN_THRESHOLD && lastEyeState === 'closed') {
                // Eye opened after being closed — count as a blink
                lastEyeState = 'open';
                blinkCount++;
                updateBlinkCircles();
                if (statusEl) statusEl.textContent = `Blink ${blinkCount} of ${BLINKS_REQUIRED} detected!`;

                if (blinkCount >= BLINKS_REQUIRED) {
                    stopLivenessTimeout();
                    stopDetectionLoop();
                    // Move to step 3
                    setTimeout(() => {
                        showFaceStep(3);
                        setStepIndicatorActive(3);
                        moveVideoTo('faceScanVideoContainer3');
                        startHeadTurnDetection('left');
                    }, 600);
                }
            }
        }, 80);
    }

    function updateBlinkCircles() {
        const c1 = document.getElementById('blinkCircle1');
        const c2 = document.getElementById('blinkCircle2');
        if (c1) {
            if (blinkCount >= 1) {
                c1.style.border = '3px solid #48bb78';
                c1.style.background = '#f0fff4';
                c1.textContent = '✓';
            } else {
                c1.style.border = '3px solid #e2e8f0';
                c1.style.background = 'white';
                c1.textContent = '👁';
            }
        }
        if (c2) {
            if (blinkCount >= 2) {
                c2.style.border = '3px solid #48bb78';
                c2.style.background = '#f0fff4';
                c2.textContent = '✓';
            } else {
                c2.style.border = '3px solid #e2e8f0';
                c2.style.background = 'white';
                c2.textContent = '👁';
            }
        }
    }

    // ─── STEPS 3 & 4: HEAD TURN DETECTION ─────────────────────────────────────
    function startHeadTurnDetection(direction) {
        stopDetectionLoop();
        const video     = document.getElementById('faceScanVideo');
        const barId     = direction === 'left' ? 'livenessTimeoutBar3' : 'livenessTimeoutBar4';
        const statusId  = direction === 'left' ? 'headTurnLeftStatus' : 'headTurnRightStatus';

        startLivenessTimeout(barId, () => {
            stopDetectionLoop();
            const stepNum = direction === 'left' ? 3 : 4;
            showStepError(stepNum, `Head turn ${direction} not detected. Please try again.`);
        });

        let turnDetected = false;

        detectionLoop = setInterval(async () => {
            if (!video || !video.readyState || video.paused) return;
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 }))
                .withFaceLandmarks();

            if (!detection) return;

            const landmarks  = detection.landmarks;
            const noseTip    = landmarks.getNose()[3]; // nose bridge middle
            const box        = detection.detection.box;
            const faceCenter = box.x + box.width / 2;
            const offset     = (noseTip.x - faceCenter) / box.width;

            const statusEl = document.getElementById(statusId);

            // NOTE: video is mirrored (transform: scaleX(-1)) visually but
            //       face-api reads raw pixels — left/right are from camera's perspective
            //       which is the mirror of the user's perspective.
            //       User turns LEFT → face moves RIGHT in raw camera → offset positive
            if (direction === 'left' && offset > HEAD_TURN_THRESHOLD) {
                if (statusEl) statusEl.textContent = '✓ Head turn left detected!';
                if (!turnDetected) {
                    turnDetected = true;
                    stopLivenessTimeout();
                    stopDetectionLoop();
                    setTimeout(() => {
                        showFaceStep(4);
                        setStepIndicatorActive(4);
                        moveVideoTo('faceScanVideoContainer4');
                        startHeadTurnDetection('right');
                    }, 600);
                }
            } else if (direction === 'right' && offset < -HEAD_TURN_THRESHOLD) {
                if (statusEl) statusEl.textContent = '✓ Head turn right detected!';
                if (!turnDetected) {
                    turnDetected = true;
                    stopLivenessTimeout();
                    stopDetectionLoop();
                    setTimeout(() => {
                        // Extract embedding and capture face image
                        extractEmbeddingAndFinish();
                    }, 600);
                }
            } else {
                if (statusEl) {
                    statusEl.textContent = direction === 'left'
                        ? 'Turn your head to the LEFT...'
                        : 'Turn your head to the RIGHT...';
                }
            }
        }, 100);
    }

    // ─── EMBEDDING EXTRACTION & DUPLICATE CHECK ───────────────────────────────
    async function extractEmbeddingAndFinish() {
        stopDetectionLoop();
        showFaceStep(5);
        setStepIndicatorActive(5);

        const video = document.getElementById('faceScanVideo');
        if (!video) {
            showFinalError('Camera not available.');
            return;
        }

        try {
            // ── Multi-frame averaging (5 frames, 120ms apart) ──────────────
            // Averaging multiple frames eliminates single-frame noise and
            // produces a stable representative embedding per person.
            const CAPTURE_FRAMES = 5;
            const descriptors    = [];
            let   lastDetection  = null;

            for (let f = 0; f < CAPTURE_FRAMES; f++) {
                const det = await faceapi
                    .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.35 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (det) { descriptors.push(Array.from(det.descriptor)); lastDetection = det; }
                if (f < CAPTURE_FRAMES - 1) await new Promise(r => setTimeout(r, 120));
            }

            if (descriptors.length === 0) {
                showFinalError('Could not detect face. Please try again.');
                return;
            }

            // Average all captured descriptors component-wise
            capturedEmbedding = new Array(128);
            for (let d = 0; d < 128; d++) {
                capturedEmbedding[d] = descriptors.reduce((s, desc) => s + desc[d], 0) / descriptors.length;
            }
            console.log(`[FaceScan] Averaged ${descriptors.length}/${CAPTURE_FRAMES} frames for embedding`);

            // Capture face snapshot while camera still active
            if (lastDetection) {
                capturedFaceImage = captureFaceSnapshot(video, lastDetection.detection.box);
            }

            // Stop camera before API call
            stopCamera();

            // Check for duplicates
            const resp = await fetch(BASE_URL_API + '/api/check_face_duplicate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ face_embedding: capturedEmbedding, sample_count: descriptors.length })
            });

            const data = await resp.json();

            if (!data.success) {
                showFinalError(data.message || 'Server error during face check.');
                return;
            }

            if (data.duplicate) {
                console.group('[FaceScan] ⚠ DUPLICATE detected');
                console.log('Distance     :', data.closest_dist);
                console.log('Reason       :', data.reason);
                console.log('Dyn threshold:', data.dynamic_threshold);
                console.log('Hard ceiling :', data.hard_ceiling);
                console.log('Hard floor   :', data.hard_floor);
                if (data.distances) console.table(data.distances);
                console.groupEnd();
                showDuplicateScreen();
                return;
            }

            console.group('[FaceScan] ✓ New face accepted');
            console.log('Closest dist :', data.closest_dist);
            console.log('Dyn threshold:', data.dynamic_threshold);
            console.log('Reason       :', data.reason);
            console.log('Compared     :', data.total_compared, 'stored faces');
            if (data.distances) console.table(data.distances);
            console.groupEnd();

            // All good — show success, pass data to callback
            showSuccessScreen();

            // Append face data to the registration formData
            if (pendingFormData) {
                pendingFormData.append('face_embedding', JSON.stringify(capturedEmbedding));
                if (capturedFaceImage) {
                    pendingFormData.append('face_image_b64', capturedFaceImage);
                }
            }

            setTimeout(() => {
                window.closeFaceScanModal();
                if (typeof onSuccessCallback === 'function') {
                    onSuccessCallback();
                }
            }, 1500);

        } catch (err) {
            console.error('[FaceScan] Embedding error:', err);
            showFinalError('An error occurred during face processing. Please try again.');
        }
    }

    function captureFaceSnapshot(video, box) {
        try {
            const canvas = document.createElement('canvas');
            const pad = 40;
            const x = Math.max(0, box.x - pad);
            const y = Math.max(0, box.y - pad);
            const w = Math.min(video.videoWidth  - x, box.width  + pad * 2);
            const h = Math.min(video.videoHeight - y, box.height + pad * 2);
            canvas.width  = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, x, y, w, h, 0, 0, w, h);
            return canvas.toDataURL('image/jpeg', 0.8);
        } catch (e) {
            return null;
        }
    }

    // ─── EAR CALCULATION ──────────────────────────────────────────────────────
    // Eye Aspect Ratio: EAR = (||p2-p6|| + ||p3-p5||) / (2 * ||p1-p4||)
    // faceapi eye landmarks: 6 points [p0 p1 p2 p3 p4 p5]
    function getEAR(eyePoints) {
        const dist = (a, b) => Math.sqrt(Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2));
        const vertical1 = dist(eyePoints[1], eyePoints[5]);
        const vertical2 = dist(eyePoints[2], eyePoints[4]);
        const horizontal = dist(eyePoints[0], eyePoints[3]);
        return (vertical1 + vertical2) / (2.0 * horizontal);
    }

    // ─── TIMEOUT BAR ──────────────────────────────────────────────────────────
    function startLivenessTimeout(barId, onExpire) {
        stopLivenessTimeout();
        const bar = document.getElementById(barId);
        if (bar) {
            bar.style.transition = 'none';
            bar.style.width = '100%';
        }
        const startTime = Date.now();
        livenessTimeoutInterval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const pct = Math.max(0, 100 - (elapsed / LIVENESS_TIMEOUT_MS) * 100);
            if (bar) {
                bar.style.transition = 'width 0.5s linear';
                bar.style.width = pct + '%';
            }
            if (elapsed >= LIVENESS_TIMEOUT_MS) {
                stopLivenessTimeout();
                onExpire();
            }
        }, 500);
    }

    function stopLivenessTimeout() {
        if (livenessTimeoutInterval) {
            clearInterval(livenessTimeoutInterval);
            livenessTimeoutInterval = null;
        }
        if (livenessTimeout) {
            clearTimeout(livenessTimeout);
            livenessTimeout = null;
        }
    }

    // ─── STEP DISPLAY ─────────────────────────────────────────────────────────
    function showFaceStep(n) {
        const steps = [0, 1, 2, 3, 4, 5];
        steps.forEach(i => {
            const el = document.getElementById('faceScanStep' + i);
            if (el) el.style.display = (i === n) ? 'block' : 'none';
        });

        // If going to step 1, start face position detection loop
        if (n === 1) {
            setTimeout(() => {
                moveVideoTo('faceScanVideoContainer');
                startFacePositionLoop();
            }, 200);
        }
    }

    function resetStepIndicators() {
        for (let i = 1; i <= 5; i++) {
            const dot = document.getElementById('faceDot' + i);
            if (dot) {
                dot.className = 'face-step-dot';
            }
        }
        setStepIndicatorActive(1);
    }

    function setStepIndicatorActive(step) {
        for (let i = 1; i <= 5; i++) {
            const dot = document.getElementById('faceDot' + i);
            if (!dot) continue;
            if (i < step) {
                dot.className = 'face-step-dot face-step-dot-done';
            } else if (i === step) {
                dot.className = 'face-step-dot face-step-dot-active';
            } else {
                dot.className = 'face-step-dot';
            }
        }
    }

    function showStepError(step, message) {
        const el = document.getElementById('faceScanStep' + step + 'Error');
        if (el) {
            el.style.display = 'block';
            const span = el.querySelector('span');
            if (span) span.textContent = message;
        }
    }

    function clearStepErrors() {
        for (let i = 1; i <= 5; i++) {
            const el = document.getElementById('faceScanStep' + i + 'Error');
            if (el) el.style.display = 'none';
        }
    }

    function showFinalError(message) {
        const proc = document.getElementById('faceScanProcessing');
        const err  = document.getElementById('faceScanError');
        const msg  = document.getElementById('faceScanErrorMsg');
        if (proc) proc.style.display = 'none';
        if (err)  err.style.display  = 'block';
        if (msg)  msg.textContent    = message;
    }

    function showDuplicateScreen() {
        const proc = document.getElementById('faceScanProcessing');
        const dup  = document.getElementById('faceScanDuplicate');
        if (proc) proc.style.display = 'none';
        if (dup)  dup.style.display  = 'block';
    }

    function showSuccessScreen() {
        setStepIndicatorActive(6); // all done
        for (let i = 1; i <= 5; i++) {
            const dot = document.getElementById('faceDot' + i);
            if (dot) dot.className = 'face-step-dot face-step-dot-done';
        }
        const proc = document.getElementById('faceScanProcessing');
        const succ = document.getElementById('faceScanSuccess');
        if (proc) proc.style.display = 'none';
        if (succ) succ.style.display = 'block';
    }

    // ─── CLEANUP ──────────────────────────────────────────────────────────────
    function stopDetectionLoop() {
        if (detectionLoop) {
            clearInterval(detectionLoop);
            detectionLoop = null;
        }
    }

    function stopAll() {
        stopDetectionLoop();
        stopLivenessTimeout();
        stopCamera();
    }

    // ─── EXPOSE ───────────────────────────────────────────────────────────────
    window.faceScanOpen  = window.openFaceScanModal;
    window.faceScanClose = window.closeFaceScanModal;

    // Share loaded-flag so the inline module can reuse already-loaded models
    window._faceApiModelsLoaded = false;
    const _origLoadModels = loadModels;
    window._ensureFaceModels = async function(onProgress) {
        if (window._faceApiModelsLoaded) return true;
        // Delegate to inner loadModels is not accessible; re-implement minimally here
        // (the outer loadModels sets modelsLoaded only in its closure)
        // We'll load directly and set a shared flag
        try {
            onProgress && onProgress(5, 'Loading face detector…');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_PATH);
            onProgress && onProgress(40, 'Loading landmark model…');
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_PATH);
            onProgress && onProgress(75, 'Loading recognition model…');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_PATH);
            onProgress && onProgress(100, 'Ready!');
            modelsLoaded = true; // also set inner flag
            window._faceApiModelsLoaded = true;
            return true;
        } catch (e) {
            return false;
        }
    };

})();

/* ═══════════════════════════════════════════════════════════════════════════
   INLINE face scan — runs INSIDE the login modal (no separate overlay)
   ═══════════════════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const MODELS_PATH = (function () {
        const meta = document.querySelector('meta[name="base-url"]');
        return (meta ? meta.content.replace(/\/$/, '') : '') + '/assets/js/face-api/models';
    })();

    const BASE_API = (function () {
        const meta = document.querySelector('meta[name="base-url"]');
        return meta ? meta.content.replace(/\/app\/?$/, '') : '';
    })();

    let DEBUG_EAR             = false;   // toggled by pressing Space 5× consecutively
    let DEBUG_FLOW            = false;   // toggled by pressing D     5× consecutively

    // Adaptive blink thresholds — derived from each user's own baseline EAR.
    // face-api landmarks only produce a ~5-8% EAR drop on a real blink
    // (NOT 35%+), so ratios must be very close to 1.0.
    const EAR_BLINK_RATIO     = 0.95;   // closed  = baseline × 0.95  (catches ~5% drop)
    const EAR_OPEN_RATIO      = 0.98;   // re-open = baseline × 0.98
    const EAR_FALLBACK_BLINK  = 0.20;   // fallback if calibration not yet done
    const EAR_FALLBACK_OPEN   = 0.25;
    const HEAD_TURN_THRESHOLD = 0.28;
    const BLINKS_REQUIRED     = 2;
    const TIMEOUT_MS          = 150000;
    const CALIBRATION_SAMPLES = 20;     // more samples = more stable baseline

    // ── State ─────────────────────────────────────────────────────────────────
    let _formData   = null;
    let _onSuccess  = null;
    let _stream     = null;
    let _loop       = null;
    let _timerIv    = null;
    let _blinkCount = 0;
    let _lastEye    = 'open';

    // Adaptive calibration state
    let _earSamples   = [];   // collected during position step
    let _baselineEAR  = null; // median of open-eye samples

    // Lighting & accessories warning state
    let _noFaceStreak = 0;    // consecutive frames with no face detected
    let _blinkThresh  = EAR_FALLBACK_BLINK;
    let _openThresh   = EAR_FALLBACK_OPEN;

    // Sub-steps of the camera phase
    const SUB = { POSITION: 'position', BLINK: 'blink', LEFT: 'left', RIGHT: 'right' };
    let _subStep = SUB.POSITION;

    // ── Public entry point ────────────────────────────────────────────────────
    window.openFaceScanInline = function (formData, onSuccess) {
        _formData  = formData;
        _onSuccess = onSuccess;

        // Swap login form for face scan panel
        const container = document.getElementById('loginContainer');
        const panel     = document.getElementById('faceScanInlinePanel');
        if (container) container.style.display = 'none';
        if (panel)     panel.style.display     = 'block';

        // Also hide the decorative sun & logo so they don't overlap
        const sun = document.querySelector('.modal-sun-wrapper');
        if (sun) sun.style.display = 'none';

        _resetDots();
        _showScreen('consent');
    };

    // ── Expose button callbacks ────────────────────────────────────────────────
    window.fsiStartCamera = async function () {
        // Check consent checkbox
        const cb = document.getElementById('fsiConsentCheck');
        const err = document.getElementById('fsiConsentErr');
        if (!cb || !cb.checked) {
            if (err) err.style.display = 'block';
            return;
        }
        if (err) err.style.display = 'none';

        _showScreen('loading');
        _dotActive(1);

        // Load models if needed
        const loadBar    = document.getElementById('fsiLoadBar');
        const loadStatus = document.getElementById('fsiLoadStatus');
        const loadErr    = document.getElementById('fsiLoadErr');

        const ok = await _ensureModels(function (pct, msg) {
            if (loadBar)    loadBar.style.width = pct + '%';
            if (loadStatus) loadStatus.textContent = msg;
        });

        if (!ok) {
            if (loadErr) {
                loadErr.style.display = 'block';
                loadErr.textContent = 'Failed to load face models. Please refresh the page and try again.';
            }
            return;
        }

        // Start camera
        const started = await _startCamera();
        if (!started) return;

        _subStep      = SUB.POSITION;
        _blinkCount   = 0;            // ← reset blink state so person 2 starts clean
        _lastEye      = 'open';
        _earSamples   = [];
        _baselineEAR  = null;
        _blinkThresh  = EAR_FALLBACK_BLINK;
        _openThresh   = EAR_FALLBACK_OPEN;
        _noFaceStreak = 0;
        _clearWarn();
        _dbgLog('Camera started, state reset for new user');
        _dbgSet('step', 'position');
        _showScreen('camera');
        _setCameraStep(SUB.POSITION);
        _startPositionLoop();
    };

    window.fsiCancel = function () {
        _stopAll();
        _restoreLoginForm();
    };

    window.fsiRetry = function () {
        _stopAll();
        _blinkCount  = 0;
        _lastEye     = 'open';
        _earSamples  = [];
        _baselineEAR = null;
        _blinkThresh = EAR_FALLBACK_BLINK;
        _openThresh  = EAR_FALLBACK_OPEN;
        _subStep     = SUB.POSITION;
        _noFaceStreak = 0;
        _clearWarn();
        _resetDots();
        _dotActive(1);
        _showScreen('camera');
        _setCameraStep(SUB.POSITION);
        _startCamera().then(ok => {
            if (ok) _startPositionLoop();
        });
    };

    // ── Model loading helper ──────────────────────────────────────────────────
    async function _ensureModels(onProgress) {
        if (window._faceApiModelsLoaded) { onProgress && onProgress(100, 'Ready!'); return true; }
        try {
            onProgress && onProgress(5,  'Loading face detector…');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_PATH);
            onProgress && onProgress(42, 'Loading landmark model…');
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_PATH);
            onProgress && onProgress(76, 'Loading recognition model…');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_PATH);
            onProgress && onProgress(100, 'Ready!');
            window._faceApiModelsLoaded = true;
            return true;
        } catch (e) {
            console.error('[FaceScanInline] models error:', e);
            return false;
        }
    }

    // ── Camera ────────────────────────────────────────────────────────────────
    async function _startCamera() {
        _stopCamera();
        const video = document.getElementById('fsiVideo');
        if (!video) return false;
        try {
            _stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } },
                audio: false
            });
            video.srcObject = _stream;
            await new Promise((res, rej) => { video.onloadedmetadata = res; video.onerror = rej; });
            video.play();
            return true;
        } catch (e) {
            console.error('[FaceScanInline] camera:', e);
            _showScreen('camError');
            _setCamErr('Cannot access camera. Please allow camera permission and try again.');
            return false;
        }
    }

    function _stopCamera() {
        if (_stream) { _stream.getTracks().forEach(t => t.stop()); _stream = null; }
        const v = document.getElementById('fsiVideo');
        if (v) { v.srcObject = null; }
    }

    // ── Key shortcuts: Space×5 = EAR debug | D×5 = Flow debug ───────────────────────
    (() => {
        // — Space ×5 toggles EAR debug panel —
        let _sc = 0, _st = null;
        // — D ×5 toggles Flow debug panel —
        let _dc = 0, _dt = null;

        function _flashToast(label, on) {
            const toast = document.createElement('div');
            toast.textContent = on ? `⚡ ${label} ON` : `⚡ ${label} OFF`;
            Object.assign(toast.style, {
                position:'fixed', bottom:'24px', left:'50%', transform:'translateX(-50%)',
                background: on ? '#2d3748' : '#718096',
                color:'#e2e8f0', padding:'6px 18px', borderRadius:'20px',
                fontSize:'12px', fontFamily:'monospace', zIndex:'99999',
                opacity:'1', transition:'opacity .4s ease'
            });
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; }, 1200);
            setTimeout(() => { toast.remove(); }, 1700);
        }

        document.addEventListener('keydown', e => {
            const panel = document.getElementById('faceScanInlinePanel');
            if (!panel || panel.style.display === 'none') { _sc = 0; _dc = 0; return; }

            if (e.code === 'Space') {
                e.preventDefault();
                _dc = 0; clearTimeout(_dt);
                _sc++;
                clearTimeout(_st);
                _st = setTimeout(() => { _sc = 0; }, 800);
                if (_sc >= 5) {
                    _sc = 0;
                    DEBUG_EAR = !DEBUG_EAR;
                    const dbg = document.getElementById('fsiDebugEar');
                    if (dbg) dbg.style.display = DEBUG_EAR ? 'block' : 'none';
                    _flashToast('EYE Debug', DEBUG_EAR);
                    console.log('[FaceScanInline] EYE Debug toggled:', DEBUG_EAR);
                }
            } else if (e.key === 'd' || e.key === 'D') {
                _sc = 0; clearTimeout(_st);
                _dc++;
                clearTimeout(_dt);
                _dt = setTimeout(() => { _dc = 0; }, 800);
                if (_dc >= 5) {
                    _dc = 0;
                    DEBUG_FLOW = !DEBUG_FLOW;
                    const fp = document.getElementById('fsiDbgFlow');
                    if (fp) fp.style.display = DEBUG_FLOW ? 'block' : 'none';
                    _flashToast('FLOW Debug', DEBUG_FLOW);
                    console.log('[FaceScanInline] FLOW Debug toggled:', DEBUG_FLOW);
                }
            } else {
                _sc = 0; _dc = 0;
                clearTimeout(_st); clearTimeout(_dt);
            }
        });
    })();

    // ── Flow debug helpers ─────────────────────────────────────────────────────────
    function _dbgSet(key, val) {
        if (!DEBUG_FLOW) return;
        const el = document.getElementById('dbgFlow_' + key);
        if (el) el.textContent = val;
    }
    function _dbgLog(msg) {
        const ts = new Date().toISOString().slice(11, 23);
        console.log(`[FlowDbg ${ts}] ${msg}`);
        if (!DEBUG_FLOW) return;
        const log = document.getElementById('dbgFlowLog');
        if (!log) return;
        const row = document.createElement('div');
        row.textContent = `${ts} ${msg}`;
        row.style.color = msg.startsWith('✗') ? '#f87171'
                        : msg.startsWith('⚠') ? '#fbbf24'
                        : msg.startsWith('✓') ? '#4ade80'
                        : '#c9d1d9';
        log.insertBefore(row, log.firstChild); // newest first
        // cap at 40 entries
        while (log.children.length > 40) log.removeChild(log.lastChild);
    }

    // ── Debug EAR overlay helper ──────────────────────────────────────────────
    function _debugEar(leftEar, rightEar) {
        const panel = document.getElementById('fsiDebugEar');
        if (!panel) return;
        if (!DEBUG_EAR) { panel.style.display = 'none'; return; }
        panel.style.display = 'block';

        const avg = (leftEar + rightEar) / 2;
        const elL    = document.getElementById('dbgEarL');
        const elR    = document.getElementById('dbgEarR');
        const elAvg  = document.getElementById('dbgEarAvg');
        const elFill = document.getElementById('dbgEarBarFill');
        const elState= document.getElementById('dbgEarState');
        const elBl   = document.getElementById('dbgEarBlinks');

        const fmt = v => v.toFixed(3);
        if (elL)   elL.textContent   = fmt(leftEar);
        if (elR)   elR.textContent   = fmt(rightEar);
        if (elAvg) {
            elAvg.textContent = fmt(avg);
            elAvg.style.color = avg < _blinkThresh ? '#fc8181'
                              : avg > _openThresh  ? '#68d391'
                              : '#f6ad55';
        }
        // Bar: map EAR 0–0.45 to 0–100%
        if (elFill) {
            const pct = Math.min(100, (avg / 0.45) * 100);
            elFill.style.width = pct + '%';
            elFill.style.background = avg < _blinkThresh ? '#fc8181'
                                    : avg > _openThresh  ? '#68d391'
                                    : '#f6ad55';
        }
        if (elState) {
            const closed = avg < _blinkThresh;
            elState.textContent  = closed ? 'CLOSED' : 'open';
            elState.style.color  = closed ? '#fc8181' : '#68d391';
        }
        if (elBl) elBl.textContent = _blinkCount;

        // Update adaptive threshold display
        const elBase = document.getElementById('dbgEarBase');
        const elBT   = document.getElementById('dbgEarBT');
        const elOT   = document.getElementById('dbgEarOT');
        if (elBase) elBase.textContent = _baselineEAR ? _baselineEAR.toFixed(3) : '...';
        if (elBT)   elBT.textContent   = _blinkThresh.toFixed(3);
        if (elOT)   elOT.textContent   = _openThresh.toFixed(3);
    }

    // ── Lighting & accessories warning helpers ─────────────────────────────
    /** Sample center 60% of the video frame and return average brightness 0-255 */
    function _getBrightness(video) {
        try {
            const c   = document.createElement('canvas');
            c.width   = 48; c.height = 48;
            const ctx = c.getContext('2d');
            const vw  = video.videoWidth  || 320;
            const vh  = video.videoHeight || 240;
            // Sample the face region (center-ish crop)
            ctx.drawImage(video, vw * 0.2, vh * 0.1, vw * 0.6, vh * 0.8, 0, 0, 48, 48);
            const d = ctx.getImageData(0, 0, 48, 48).data;
            let sum = 0;
            for (let i = 0; i < d.length; i += 4) {
                sum += d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114;
            }
            return sum / (48 * 48);
        } catch (e) { return 128; }
    }

    function _setWarn(msg) {
        const banner = document.getElementById('fsiWarnBanner');
        const text   = document.getElementById('fsiWarnText');
        if (!banner || !text) return;
        if (text.textContent === msg && banner.style.display !== 'none') return; // avoid flicker
        text.textContent = msg;
        banner.style.display = 'block';
        banner.style.animation = 'none';
        // Re-trigger animation
        void banner.offsetWidth;
        banner.style.animation = 'fadeInDown .25s ease';
    }

    function _clearWarn() {
        const banner = document.getElementById('fsiWarnBanner');
        if (banner) banner.style.display = 'none';
    }

    /**
     * Run on every detection frame.
     * - Checks video brightness for lighting warning.
     * - Checks face confidence for accessories warning.
     * - Tracks streak of missed detections for face-covering warning.
     */
    function _checkFrameWarnings(video, det) {
        const brightness = _getBrightness(video);
        if (brightness < 55) {
            _setWarn('💡 Too dark — please face a light source to continue verification.');
            return;
        }
        if (brightness > 245) {
            _setWarn('🌟 Overexposed — avoid a bright light directly behind the camera.');
            return;
        }
        if (det) {
            _noFaceStreak = 0;
            if (det.detection && det.detection.score < 0.42) {
                _setWarn('🕶️ Please remove glasses, masks, or anything covering your face.');
                return;
            }
        } else {
            _noFaceStreak++;
            if (_noFaceStreak >= 6) {
                _setWarn('🕶️ Face not detected — remove glasses, masks, or face coverings.');
                return;
            }
        }
        _clearWarn();
    }

    // ── Position detection loop ──────────────────────────────────────────────
    function _startPositionLoop() {
        _stopLoop();
        const video  = document.getElementById('fsiVideo');
        const status = document.getElementById('fsiStatus');
        const btn    = document.querySelector('#fsiCamera button[onclick="startLivenessCheck()"]');

        // Show or hide debug panel based on flag
        const panel = document.getElementById('fsiDebugEar');
        if (panel) panel.style.display = DEBUG_EAR ? 'block' : 'none';

        let facePresent = false;

        // Show manual "Start" button for position step
        _setCameraStep(SUB.POSITION);

        _loop = setInterval(async () => {
            if (!video || !video.readyState || video.paused) return;
            const det = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.35 }))
                .withFaceLandmarks();

            _checkFrameWarnings(video, det);

            if (det) {
                const lm = det.landmarks;
                const curEar = (_ear(lm.getLeftEye()) + _ear(lm.getRightEye())) / 2;

                // ── Adaptive calibration: collect open-eye EAR samples ──
                if (_earSamples.length < CALIBRATION_SAMPLES) {
                    _earSamples.push(curEar);
                    const pct = Math.round((_earSamples.length / CALIBRATION_SAMPLES) * 100);
                    if (status) status.textContent = `🔍 Calibrating eye baseline… ${pct}%`;
                } else if (!_baselineEAR) {
                    // Compute median of collected samples
                    const sorted = [..._earSamples].sort((a, b) => a - b);
                    _baselineEAR = sorted[Math.floor(sorted.length / 2)];
                    _blinkThresh = _baselineEAR * EAR_BLINK_RATIO;
                    _openThresh  = _baselineEAR * EAR_OPEN_RATIO;
                    _dbgLog(`✓ baseline calibrated: ${_baselineEAR.toFixed(3)}  blink<${_blinkThresh.toFixed(3)}  open>${_openThresh.toFixed(3)}`);
                    _dbgSet('step', 'position—calibrated');
                    console.log(`[FaceScanInline] baseline=${_baselineEAR.toFixed(3)} blink<${_blinkThresh.toFixed(3)} open>${_openThresh.toFixed(3)}`);
                }

                if (DEBUG_EAR) _debugEar(_ear(lm.getLeftEye()), _ear(lm.getRightEye()));
                if (_baselineEAR && status) status.textContent = '😊 Face calibrated — ready!';

                if (!facePresent && _baselineEAR) {
                    facePresent = true;
                    _dbgLog('✓ face+calibration ready, advancing to blink step in 0.8s');
                    // Auto-advance to blink step once face detected AND calibration complete
                    setTimeout(() => {
                        if (_subStep === SUB.POSITION && _baselineEAR) {
                            _stopLoop();
                            _subStep = SUB.BLINK;
                            _dotActive(2);
                            _blinkCount = 0;
                            _lastEye    = 'open';
                            _dbgSet('step', 'blink');
                            _dbgLog('→ blink loop started');
                            _setCameraStep(SUB.BLINK);
                            _updateBlinkCircles();
                            _startTimeout(() => {
                                _stopLoop();
                                _showScreen('camError');
                                _setCamErr('No blink detected within the time limit. Please try again.');
                            });
                            _startBlinkLoop();
                        }
                    }, 800);
                }
            } else {
                facePresent = false;
                if (DEBUG_EAR) {
                    const panel = document.getElementById('fsiDebugEar');
                    if (panel) panel.style.display = 'none';
                }
                if (status) status.textContent = '🔍 No face — center your face in the circle';
            }
        }, 350);
    }

    // ── Blink loop ────────────────────────────────────────────────────────────
    function _startBlinkLoop() {
        _stopLoop();
        const video = document.getElementById('fsiVideo');
        _loop = setInterval(async () => {
            if (!video || !video.readyState || video.paused) return;
            const det = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.35 }))
                .withFaceLandmarks();
            _checkFrameWarnings(video, det);
            if (!det) return;

            const lm     = det.landmarks;
            const leftEar  = _ear(lm.getLeftEye());
            const rightEar = _ear(lm.getRightEye());
            // Use the MINIMUM eye instead of average — if either eye closes it counts.
            // face-api often reads one eye more accurately than the other.
            const ear   = Math.min(leftEar, rightEar);
            const status = document.getElementById('fsiStatus');

            if (DEBUG_EAR) _debugEar(leftEar, rightEar);

            if (ear < _blinkThresh && _lastEye === 'open') {
                _lastEye = 'closed';
                _dbgLog(`↓ EAR closed: min=${ear.toFixed(3)} thresh=${_blinkThresh.toFixed(3)}`);
            } else if (ear > _openThresh && _lastEye === 'closed') {
                _lastEye = 'open';
                _blinkCount++;
                _dbgLog(`↑ blink #${_blinkCount} registered (EAR re-open: min=${ear.toFixed(3)} thresh=${_openThresh.toFixed(3)})`);
                _updateBlinkCircles();
                if (status) status.textContent = `Blink ${_blinkCount} of ${BLINKS_REQUIRED} ✓`;
                if (_blinkCount >= BLINKS_REQUIRED) {
                    _stopTimeout();
                    _stopLoop();
                    setTimeout(() => {
                        _subStep = SUB.LEFT;
                        _dotActive(3);
                        _dbgSet('step', 'head-left');
                        _dbgLog(`✓ ${BLINKS_REQUIRED} blinks done → head-left`);
                        _setCameraStep(SUB.LEFT);
                        _startTimeout(() => {
                            _stopLoop();
                            _showScreen('camError');
                            _setCamErr('Head turn left not detected. Please try again.');
                        });
                        _startHeadLoop(SUB.LEFT);
                    }, 600);
                }
            }
        }, 80);
    }

    // ── Head turn loop ────────────────────────────────────────────────────────
    function _startHeadLoop(direction) {
        _stopLoop();
        const video = document.getElementById('fsiVideo');
        let detected = false;
        _loop = setInterval(async () => {
            if (!video || !video.readyState || video.paused) return;
            const det = await faceapi
                .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.35 }))
                .withFaceLandmarks();
            _checkFrameWarnings(video, det);
            if (!det) return;

            const lm     = det.landmarks;
            const box    = det.detection.box;
            const nose   = lm.getNose()[3];
            const offset = (nose.x - (box.x + box.width / 2)) / box.width;
            const status = document.getElementById('fsiStatus');

            if (direction === SUB.LEFT && offset > HEAD_TURN_THRESHOLD && !detected) {
                detected = true;
                _stopTimeout(); _stopLoop();
                _dbgLog(`✓ head-left detected (offset=${offset.toFixed(3)})`);
                if (status) status.textContent = '✓ Turn left detected!';
                setTimeout(() => {
                    _subStep = SUB.RIGHT;
                    _dotActive(4);
                    _dbgSet('step', 'head-right');
                    _dbgLog('→ head-right loop started');
                    _setCameraStep(SUB.RIGHT);
                    _startTimeout(() => {
                        _stopLoop();
                        _showScreen('camError');
                        _setCamErr('Head turn right not detected. Please try again.');
                    });
                    _startHeadLoop(SUB.RIGHT);
                }, 600);
            } else if (direction === SUB.RIGHT && offset < -HEAD_TURN_THRESHOLD && !detected) {
                detected = true;
                _stopTimeout(); _stopLoop();
                _dbgLog(`✓ head-right detected (offset=${offset.toFixed(3)}) → extractAndFinish`);
                _dbgSet('step', 'extracting');
                if (status) status.textContent = '✓ Turn right detected!';
                setTimeout(() => _extractAndFinish(), 500);
            } else {
                if (status) status.textContent = direction === SUB.LEFT
                    ? '↩ Slowly turn your head to the LEFT'
                    : '↪ Slowly turn your head to the RIGHT';
            }
        }, 100);
    }

    // ── Extract embedding & check duplicate ──────────────────────────────────
    async function _extractAndFinish() {
        _stopLoop();
        _dotActive(5);

        const video = document.getElementById('fsiVideo');
        if (!video) { _showScreen('processing'); _showFinalErr('Camera not available.'); return; }

        try {
            // ── Multi-frame averaging (5 frames, 120ms apart) ──────────────
            // Averaging multiple frames eliminates single-frame noise and
            // produces a much more stable representative embedding per person,
            // making duplicate detection far more reliable.
            const CAPTURE_FRAMES = 5;
            const descriptors    = [];
            let   lastDet        = null;

            for (let f = 0; f < CAPTURE_FRAMES; f++) {
                const det = await faceapi
                    .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.35 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                if (det) { descriptors.push(Array.from(det.descriptor)); lastDet = det; }
                if (f < CAPTURE_FRAMES - 1) await new Promise(r => setTimeout(r, 120));
            }

            if (descriptors.length === 0) {
                _showScreen('processing');
                _showFinalErr('Could not detect face. Please retry.');
                return;
            }

            // Average all captured descriptors component-wise
            const embedding = new Array(128);
            for (let d = 0; d < 128; d++) {
                embedding[d] = descriptors.reduce((s, desc) => s + desc[d], 0) / descriptors.length;
            }
            console.log(`[FaceScanInline] Averaged ${descriptors.length}/${CAPTURE_FRAMES} frames for embedding`);

            // Snapshot while camera is still visible
            let snapshotB64 = null;
            try {
                if (lastDet) {
                    const c = document.createElement('canvas');
                    const b = lastDet.detection.box;
                    c.width  = b.width  + 80;
                    c.height = b.height + 80;
                    c.getContext('2d').drawImage(video, b.x - 40, b.y - 40, c.width, c.height, 0, 0, c.width, c.height);
                    snapshotB64 = c.toDataURL('image/jpeg', 0.85);
                }
            } catch (_) {}

            _stopCamera();
            _showScreen('processing');

            const apiUrl  = BASE_API + '/api/check_face_duplicate.php';
            let rawText   = '';
            let data      = null;
            try {
                const resp = await fetch(apiUrl, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify({ face_embedding: embedding, sample_count: descriptors.length })
                });
                rawText = await resp.text();
                data    = JSON.parse(rawText);
            } catch (fetchErr) {
                _dbgLog(`✗ fetch/parse error: ${fetchErr.message}  raw: ${rawText.slice(0,200)}`);
                console.error('[FaceScanInline] fetch/parse error:', fetchErr, '\nRaw:', rawText);
                _showFinalErr('Network error — open browser console (F12) for details.');
                return;
            }

            if (!data.success) { _showFinalErr(data.message || 'Server error.'); return; }

            if (data.duplicate) {
                console.group('[FaceScanInline] ⚠ DUPLICATE detected');
                console.log('Distance     :', data.closest_dist);
                console.log('Reason       :', data.reason);
                console.log('Dyn threshold:', data.dynamic_threshold);
                console.log('Hard ceiling :', data.hard_ceiling);
                console.log('Hard floor   :', data.hard_floor);
                if (data.distances) console.table(data.distances);
                console.groupEnd();
                _dbgLog(`⚠ DUPLICATE dist=${data.closest_dist} reason=${data.reason}`);
                _showScreen('duplicate');
                return;
            }

            console.group('[FaceScanInline] ✓ New face accepted');
            console.log('Closest dist :', data.closest_dist);
            console.log('Dyn threshold:', data.dynamic_threshold);
            console.log('Reason       :', data.reason);
            console.log('Compared     :', data.total_compared, 'stored faces');
            if (data.distances) console.table(data.distances);
            console.groupEnd();
            _dbgLog(`✓ new face  dist=${data.closest_dist}  thresh=${data.dynamic_threshold}  reason=${data.reason}`);

            // Success — append averaged face data to formData and call callback
            if (_formData) {
                _formData.set('face_embedding', JSON.stringify(embedding));
                if (snapshotB64) _formData.set('face_image_b64', snapshotB64);
            }

            _stopAll();
            _restoreLoginForm();

            if (typeof _onSuccess === 'function') _onSuccess();

        } catch (e) {
            console.error('[FaceScanInline]', e);
            _showFinalErr('An error occurred. Please try again.');
        }
    }

    // ── EAR ──────────────────────────────────────────────────────────────────
    function _ear(pts) {
        const d = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
        return (d(pts[1], pts[5]) + d(pts[2], pts[4])) / (2 * d(pts[0], pts[3]));
    }

    // ── Timeout bar ──────────────────────────────────────────────────────────
    function _startTimeout(onExpire) {
        _stopTimeout();
        const bar  = document.getElementById('fsiBar');
        const t0   = Date.now();
        if (bar) { bar.style.transition = 'none'; bar.style.width = '100%'; }
        _timerIv = setInterval(() => {
            const elapsed = Date.now() - t0;
            const pct = Math.max(0, 100 - (elapsed / TIMEOUT_MS) * 100);
            if (bar) { bar.style.transition = 'width .5s linear'; bar.style.width = pct + '%'; }
            if (elapsed >= TIMEOUT_MS) { _stopTimeout(); onExpire(); }
        }, 500);
    }
    function _stopTimeout() {
        if (_timerIv) { clearInterval(_timerIv); _timerIv = null; }
    }

    // ── Screen switcher ───────────────────────────────────────────────────────
    function _showScreen(name) {
        // name: 'consent' | 'loading' | 'camera' | 'camError' | 'processing' | 'duplicate' | 'fsiError'
        const ids = ['fsiConsent', 'fsiLoading', 'fsiCamera', 'fsiBlinkCircles',
                     'fsiProcessing', 'fsiDuplicate', 'fsiError'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        if (name === 'consent')    { _show('fsiConsent'); }
        if (name === 'loading')    { _show('fsiLoading'); }
        if (name === 'camera')     { _show('fsiCamera'); }
        if (name === 'camError')   { _show('fsiCamera'); _show('fsiCamErr'); }
        if (name === 'processing') {
            _show('fsiProcessing');
            _clearWarn(); // clear any active warning when moving to processing
        }
        if (name === 'duplicate')  { _show('fsiDuplicate'); }
        if (name === 'error')      { _show('fsiError'); }
    }

    function _show(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = (id === 'fsiBlinkCircles') ? 'flex' : 'block';
    }

    function _setCamErr(msg) {
        const err = document.getElementById('fsiCamErr');
        if (err) {
            err.style.display = 'block';
            const span = err.querySelector('span');
            if (span) span.textContent = msg;
        }
    }

    function _showFinalErr(msg) {
        _showScreen('error');
        const el = document.getElementById('fsiErrorMsg');
        if (el) el.textContent = msg;
    }

    // ── Camera step instructions ──────────────────────────────────────────────
    function _setCameraStep(sub) {
        const title    = document.getElementById('fsiTitle');
        const subtitle = document.getElementById('fsiSubtitle');
        const bc       = document.getElementById('fsiBlinkCircles');
        const status   = document.getElementById('fsiStatus');
        const bar      = document.getElementById('fsiBar');

        const map = {
            [SUB.POSITION]: ['<i class="fas fa-camera" style="color:#667eea;margin-right:7px;"></i>Position Your Face', 'Center your face in the circle in a well-lit area.', false, 'Searching for face…'],
            [SUB.BLINK]:    ['<i class="fas fa-eye" style="color:#667eea;margin-right:7px;"></i>Blink Twice',          'Slowly blink both eyes <strong>twice</strong>.',       true,  'Waiting for blink…'],
            [SUB.LEFT]:     ['<i class="fas fa-arrow-left" style="color:#667eea;margin-right:7px;"></i>Turn Head Left', 'Slowly turn your head to the <strong>left</strong>.',  false, 'Waiting for head turn…'],
            [SUB.RIGHT]:    ['<i class="fas fa-arrow-right" style="color:#667eea;margin-right:7px;"></i>Turn Head Right','Now turn your head to the <strong>right</strong>.',    false, 'Waiting for head turn…'],
        };

        const cfg = map[sub] || map[SUB.POSITION];
        if (title)    title.innerHTML    = cfg[0];
        if (subtitle) subtitle.innerHTML = cfg[1];
        if (bc)       bc.style.display   = cfg[2] ? 'flex' : 'none';
        if (status)   status.textContent = cfg[3];
        if (bar)      { bar.style.transition = 'none'; bar.style.width = '100%'; }

        // Hide error
        const err = document.getElementById('fsiCamErr');
        if (err) err.style.display = 'none';
    }

    // ── Blink circles ─────────────────────────────────────────────────────────
    function _updateBlinkCircles() {
        [['fsiBlink1', 1], ['fsiBlink2', 2]].forEach(([id, n]) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (_blinkCount >= n) {
                el.style.border = '3px solid #48bb78'; el.style.background = '#f0fff4';
                el.textContent = '✓';
            } else {
                el.style.border = '3px solid #e2e8f0'; el.style.background = 'white';
                el.textContent = '👁';
            }
        });
    }

    // ── Step dots ────────────────────────────────────────────────────────────
    function _resetDots() {
        for (let i = 1; i <= 5; i++) {
            const d = document.getElementById('fsiDot' + i);
            if (d) d.className = 'fsi-dot';
        }
        _dotActive(1);
    }
    function _dotActive(step) {
        for (let i = 1; i <= 5; i++) {
            const d = document.getElementById('fsiDot' + i);
            if (!d) continue;
            d.className = i < step ? 'fsi-dot fsi-dot-done' : (i === step ? 'fsi-dot fsi-dot-active' : 'fsi-dot');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function _stopLoop() { if (_loop) { clearInterval(_loop); _loop = null; } }
    function _stopAll()  { _stopLoop(); _stopTimeout(); _stopCamera(); }

    function _restoreLoginForm() {
        const container = document.getElementById('loginContainer');
        const panel     = document.getElementById('faceScanInlinePanel');
        const sun       = document.querySelector('.modal-sun-wrapper');
        if (panel)     panel.style.display     = 'none';
        if (container) container.style.display = '';
        if (sun)       sun.style.display       = '';
        // Reset consent checkbox
        const cb = document.getElementById('fsiConsentCheck');
        if (cb) cb.checked = false;
        _clearWarn();
    }

})();
