<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification – Craftistry</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }

        .container { width: 100%; max-width: 520px; }

        .form-card {
            background: white; border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .logo { display: block; height: 36px; margin: 0 auto 24px; }
        h1 { font-size: 26px; font-weight: 600; color: #1a202c; text-align: center; margin-bottom: 6px; }
        .subtitle { text-align: center; color: #718096; font-size: 14px; margin-bottom: 28px; }

        .steps { display: flex; align-items: center; justify-content: center; margin-bottom: 32px; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .step-circle {
            width: 36px; height: 36px; border-radius: 50%;
            background: #e2e8f0; color: #a0aec0;
            font-weight: 600; font-size: 14px;
            display: flex; align-items: center; justify-content: center; transition: all .3s;
        }
        .step.active .step-circle { background: linear-gradient(135deg,#667eea,#764ba2); color: white; }
        .step.done   .step-circle { background: #48bb78; color: white; }
        .step-label { font-size: 11px; color: #a0aec0; font-weight: 500; white-space: nowrap; }
        .step.active .step-label { color: #667eea; }
        .step.done   .step-label { color: #48bb78; }
        .step-line { width: 48px; height: 2px; background: #e2e8f0; margin-bottom: 20px; }

        .alert {
            padding: 14px 16px; border-radius: 10px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 12px; font-size: 14px;
        }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        .section-label {
            font-size: 13px; font-weight: 600; color: #4a5568;
            margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
        }

        .upload-zone {
            border: 2px dashed #e2e8f0; border-radius: 12px; padding: 24px;
            text-align: center; cursor: pointer;
            transition: border-color .2s, background .2s;
            margin-bottom: 24px; position: relative;
        }
        .upload-zone:hover { border-color: #667eea; background: #f8f7ff; }
        .upload-zone.has-file { border-color: #48bb78; background: #f0fff4; }
        .upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .upload-icon { font-size: 2rem; color: #cbd5e0; margin-bottom: 8px; }
        .upload-zone.has-file .upload-icon { color: #48bb78; }
        .upload-text { font-size: 14px; color: #718096; }
        .upload-text strong { color: #4a5568; }
        #ic-preview {
            display: none; width: 100%; max-height: 160px;
            object-fit: cover; border-radius: 8px; margin-top: 12px;
        }

        .webcam-wrap {
            border-radius: 12px; overflow: hidden;
            background: #1a202c; margin-bottom: 16px;
            position: relative; aspect-ratio: 4/3;
        }
        #webcam {
            width: 100%; height: 100%; object-fit: cover;
            display: block; transform: scaleX(-1);
        }
        #selfie-preview {
            width: 100%; height: 100%; object-fit: cover;
            display: none; border-radius: 12px;
        }
        .webcam-overlay {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none;
        }
        .face-guide {
            width: 160px; height: 200px;
            border: 3px solid rgba(255,255,255,0.7);
            border-radius: 50%;
            box-shadow: 0 0 0 2000px rgba(0,0,0,0.35);
        }
        .cam-hint {
            position: absolute; bottom: 12px; left: 0; right: 0;
            text-align: center; font-size: 12px; color: rgba(255,255,255,0.8);
        }

        .webcam-controls { display: flex; gap: 10px; margin-bottom: 24px; }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; padding: 14px; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.4); }
        .btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: none; }

        .btn-secondary {
            flex: 1; background: transparent; color: #4a5568;
            border: 2px solid #e2e8f0; padding: 10px; border-radius: 8px;
            font-size: 14px; font-weight: 500; cursor: pointer;
            transition: border-color .2s, color .2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-family: 'Inter', sans-serif;
        }
        .btn-secondary:hover { border-color: #a0aec0; color: #1a202c; }

        .btn-capture {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; padding: 10px; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 6px;
            font-family: 'Inter', sans-serif; transition: opacity .2s;
        }
        .btn-capture:hover { opacity: .9; }

        .divider { height: 1px; background: #e2e8f0; margin: 24px 0; }

        .tips {
            background: #f8f7ff; border: 1px solid #e9d5ff;
            border-radius: 10px; padding: 14px 16px;
            margin-bottom: 20px; font-size: 13px; color: #5b21b6;
        }
        .tips ul { padding-left: 16px; margin-top: 6px; }
        .tips li { margin-bottom: 4px; }

        .help-text {
            text-align: center; font-size: 12px; color: #a0aec0;
            margin-top: 16px; line-height: 1.6;
        }

        @media (max-width: 480px) {
            .form-card { padding: 32px 20px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-card">

        <img src="{{ asset('images/Logo.png') }}" alt="Craftistry" class="logo">
        <h1>Identity Verification</h1>
        <p class="subtitle">We need to verify your identity to keep Craftistry safe</p>

        <div class="steps">
            <div class="step done">
                <div class="step-circle"><i class="bi bi-check"></i></div>
                <span class="step-label">Register</span>
            </div>
            <div class="step-line"></div>
            <div class="step done">
                <div class="step-circle"><i class="bi bi-check"></i></div>
                <span class="step-label">Email</span>
            </div>
            <div class="step-line"></div>
            <div class="step active">
                <div class="step-circle">3</div>
                <span class="step-label">KYC</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">4</div>
                <span class="step-label">Done</span>
            </div>
        </div>

        @if (session('kyc_failed'))
            <div class="alert alert-error">
                <i class="bi bi-x-circle-fill"></i>
                <div>
                    <strong>Verification failed</strong><br>
                    Face similarity: <strong>{{ session('kyc_similarity') }}%</strong>
                    (minimum required: <strong>80%</strong>)<br>
                    Please ensure your face is clearly visible and try again.
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="bi bi-x-circle-fill"></i>
                <div>
                    <strong>Verification failed</strong><br>
                    {{ $errors->first() }}
                    @if (session('kyc_similarity') !== null)
                        <br>Face similarity: <strong>{{ session('kyc_similarity') }}%</strong>
                        (minimum required: <strong>80%</strong>)
                    @endif
                </div>
            </div>
        @endif

        @if (auth()->user()->kyc_status === 'failed' && !session('kyc_failed') && !$errors->any())
            <div class="alert alert-warning">
                <i class="bi bi-arrow-repeat"></i>
                <div>
                    <strong>Previous verification failed</strong><br>
                    @if (auth()->user()->kyc_similarity !== null)
                        Last similarity score: <strong>{{ number_format(auth()->user()->kyc_similarity, 1) }}%</strong>
                        (minimum required: <strong>80%</strong>)<br>
                    @endif
                    Please try again with clearer, well-lit photos.
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('kyc.submit') }}"
              enctype="multipart/form-data" id="kycForm">
            @csrf

            {{-- Step 1: IC Upload --}}
            <div class="section-label">
                <i class="bi bi-credit-card-2-front" style="color:#667eea"></i>
                Step 1 — Upload your Malaysian IC (front side)
            </div>

            <div class="tips">
                <strong>Tips for IC photo:</strong>
                <ul>
                    <li>Make sure the photo on your IC is clearly visible</li>
                    <li>No glare or shadows on the IC</li>
                    <li>Place IC flat on a surface with good lighting</li>
                </ul>
            </div>

            <div class="upload-zone" id="uploadZone">
                <input type="file" name="ic_image" id="ic_image" accept="image/*" required>
                <div class="upload-icon"><i class="bi bi-credit-card"></i></div>
                <div class="upload-text">
                    <strong>Click to upload IC photo</strong><br>
                    JPG, PNG up to 10MB
                </div>
                <img id="ic-preview" alt="IC Preview">
            </div>

            <div class="divider"></div>

            {{-- Step 2: Selfie --}}
            <div class="section-label">
                <i class="bi bi-camera" style="color:#667eea"></i>
                Step 2 — Take a selfie
            </div>

            <div class="tips">
                <strong>Tips for selfie:</strong>
                <ul>
                    <li>Face the camera directly — no need to hold your IC</li>
                    <li>Align your face inside the oval guide</li>
                    <li>Good lighting, avoid dark or backlit areas</li>
                    <li>Remove glasses if possible, neutral expression</li>
                </ul>
            </div>

            <div class="webcam-wrap">
                <video id="webcam" autoplay playsinline muted></video>
                <canvas id="canvas" style="display:none"></canvas>
                <img id="selfie-preview" alt="Selfie">
                <div class="webcam-overlay" id="faceGuideOverlay">
                    <div class="face-guide"></div>
                </div>
                <div class="cam-hint" id="camHint">Align your face inside the oval</div>
            </div>

            <div class="webcam-controls">
                <button type="button" class="btn-secondary" id="retakeBtn" style="display:none">
                    <i class="bi bi-arrow-counterclockwise"></i> Retake
                </button>
                <button type="button" class="btn-capture" id="captureBtn">
                    <i class="bi bi-camera"></i> Capture Selfie
                </button>
            </div>

            <input type="file" name="selfie_image" id="selfie_image"
                   accept="image/jpeg" style="display:none">

            <button type="submit" class="btn-primary" id="submitBtn" disabled>
                <i class="bi bi-shield-check"></i>
                Verify My Identity
            </button>

        </form>

        <p class="help-text">
            <i class="bi bi-lock-fill"></i>
            Your data is encrypted and used only for identity verification.<br>
            We do not share your IC or photo with third parties.
        </p>

    </div>
</div>

<script>
(function () {
    const video         = document.getElementById('webcam');
    const canvas        = document.getElementById('canvas');
    const selfiePreview = document.getElementById('selfie-preview');
    const selfieInput   = document.getElementById('selfie_image');
    const captureBtn    = document.getElementById('captureBtn');
    const retakeBtn     = document.getElementById('retakeBtn');
    const submitBtn     = document.getElementById('submitBtn');
    const uploadZone    = document.getElementById('uploadZone');
    const icFileInput   = document.getElementById('ic_image');
    const icPreview     = document.getElementById('ic-preview');
    const overlay       = document.getElementById('faceGuideOverlay');
    const camHint       = document.getElementById('camHint');

    let stream     = null;
    let icDone     = false;
    let selfieDone = false;

    async function startWebcam() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            });
            video.srcObject = stream;
            video.style.display = 'block';
        } catch (e) {
            alert('Could not access camera. Please allow camera permission and reload.');
        }
    }

    startWebcam();

    icFileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        icPreview.src = URL.createObjectURL(file);
        icPreview.style.display = 'block';
        uploadZone.classList.add('has-file');
        uploadZone.querySelector('.upload-icon i').className = 'bi bi-check-circle-fill';
        uploadZone.querySelector('.upload-text').innerHTML = '<strong>' + file.name + '</strong>';
        icDone = true;
        checkReady();
    });

    captureBtn.addEventListener('click', function () {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);

        canvas.toBlob(function (blob) {
            const dt   = new DataTransfer();
            const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
            dt.items.add(file);
            selfieInput.files = dt.files;

            selfiePreview.src = URL.createObjectURL(blob);
            selfiePreview.style.display = 'block';
            video.style.display   = 'none';
            overlay.style.display = 'none';
            camHint.style.display = 'none';

            captureBtn.style.display = 'none';
            retakeBtn.style.display  = 'flex';

            if (stream) stream.getTracks().forEach(t => t.stop());

            selfieDone = true;
            checkReady();
        }, 'image/jpeg', 0.95);
    });

    retakeBtn.addEventListener('click', function () {
        selfiePreview.style.display = 'none';
        selfiePreview.src = '';
        overlay.style.display = 'flex';
        camHint.style.display = 'block';
        selfieInput.value = '';
        captureBtn.style.display = 'flex';
        retakeBtn.style.display  = 'none';
        selfieDone = false;
        checkReady();
        startWebcam();
    });

    function checkReady() {
        submitBtn.disabled = !(icDone && selfieDone);
    }

    document.getElementById('kycForm').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Verifying...';
    });
})();
</script>
</body>
</html>