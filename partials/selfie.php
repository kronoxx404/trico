<?php
// partials/selfie.php
?>
<style>
    /* Estilos Base Tema Oscuro */
    body.cc-view {
        background-color: #2b2b2b !important;
        background-image: url('assets/img/auth-trazo.svg') !important;
        background-size: cover !important;
        background-position: center top -50px !important;
        background-repeat: no-repeat !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }

    /* Ocultar elementos heredados */
    .cc-view .login-container,
    .cc-view .info-banner,
    padding: 0;
    height: 100%;
    width: 100%;
    overflow: hidden;
    background-color: #000;
    font-family: 'Segoe UI',
    system-ui,
    -apple-system,
    sans-serif;
    }

    #camera-container {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #000;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* Espejo para selfie natural */
        transform: scaleX(-1);
    }

    /* Muestra la cámara "real" */
    #canvas-output {
        display: none;
    }

    /* Overlay general para UI sobre el video */
    .overlay-ui {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 40px 20px;
        box-sizing: border-box;
        z-index: 10;
        pointer-events: none;
        /* Dejar pasar clicks al video si fuera necesario */
    }

    .top-bar {
        width: 100%;
        text-align: center;
        margin-top: 20px;
    }

    .logo-img {
        height: 40px;
        opacity: 0.9;
    }

    /* El círculo de detección */
    .scan-region {
        position: relative;
        width: 280px;
        height: 380px;
        /* Más ovalado para cara */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* SVG Ring */
    .progress-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 320px;
        /* Un poco más grande que el óvalo visual */
        height: 420px;
    }

    .progress-ring__circle {
        transition: stroke-dashoffset 0.1s;
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }

    /* Mensaje de estado flotante */
    .status-pill {
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        backdrop-filter: blur(5px);
        margin-bottom: 20px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        background-color: #ff4444;
        /* Rojo por defecto (sin rostro/inestable) */
        border-radius: 50%;
        transition: background-color 0.3s;
    }

    .status-dot.active {
        background-color: #00e676;
        /* Verde cuando detecta bien */
    }

    .status-dot.processing {
        background-color: #ffeb3b;
        /* Amarillo procesando */
    }

    /* Botones y controles inferiores */
    .controls {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        pointer-events: auto;
        /* Reactivar clicks */
        margin-bottom: 30px;
    }

    .instruction-text {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        text-align: center;
        max-width: 80%;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    .btn-manual {
        background: transparent;
        border: 2px solid rgba(255, 255, 255, 0.5);
        color: white;
        padding: 10px 25px;
        border-radius: 50px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.3s;
        display: none;
        /* Oculto por defecto */
    }

    .btn-manual:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #fff;
    }

    /* Ocultar elementos visuales 'viejos' pero mantenerlos si es necesario la logica */
    #capture-btn {
        display: none;
    }

    #btn-retake {
        display: none;
    }

    #btn-confirm {
        display: none;
    }
</style>

<!-- Scripts necesarios -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

<div id="camera-container">
    <video id="video" autoplay muted playsinline></video>

    <!-- Capa de UI superpuesta -->
    <div class="overlay-ui">
        <div class="top-bar">
            <img src="assets/img/soyyo-logo.png" alt="Logo" class="logo-img">
        </div>

        <div class="scan-region">
            <!-- SVG para el círculo de progreso -->
            <svg class="progress-ring" viewBox="0 0 320 420">
                <!-- Fondo del anillo (gris/blanco tenue) -->
                <ellipse cx="160" cy="210" rx="140" ry="190" stroke="rgba(255,255,255,0.3)" stroke-width="4" fill="none"
                    stroke-dasharray="10 5" />

                <!-- Anillo de progreso (Verde) -->
                <!-- perimeter approx: 2 * PI * sqrt((rx^2 + ry^2)/2) roughly -->
                <!-- Using path for more control or simple ellipse with stroke-dasharray -->
                <path id="progress-path" d="M160,20 A140,190 0 1,1 160,400 A140,190 0 1,1 160,20" stroke="#00e676"
                    stroke-width="6" fill="none" stroke-dasharray="1050" stroke-dashoffset="1050"
                    stroke-linecap="round" />
            </svg>

            <div class="status-pill">
                <div class="status-dot" id="status-dot"></div>
                <span id="status-text">Cargando modelos...</span>
            </div>
        </div>

        <div class="controls">
            <p class="instruction-text" id="instruction">
                Por favor, ubica tu rostro dentro del marco y mantente quieto.
            </p>
            <button class="btn-manual" id="btn-force-capture">Capturar Manualmente</button>
        </div>
    </div>
</div>

<!-- Forms ocultos legacy para mantener compatibilidad con el backend -->
<form id="fotoForm" action="modules/api/procesar_selfie.php" method="POST" style="display:none;">
    <input type="hidden" name="image" id="imageInput">
    <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id'] ?? ''; ?>">
</form>

<script>
    const video = document.getElementById('video');
    const statusText = document.getElementById('status-text');
    const statusDot = document.getElementById('status-dot');
    const progressPath = document.getElementById('progress-path');
    const instruction = document.getElementById('instruction');
    const btnForceCapture = document.getElementById('btn-force-capture');

    // Estado del sistema
    let isModelLoaded = false;
    let isDetecting = false;
    let detectionScore = 0;
    const REQUIRED_SCORE = 0.85; // Confianza mínima
    const STABILITY_FRAMES = 30; // ~1-1.5 segundos a 30fps
    let stableFrames = 0;

    // Configuración del óvalo
    const ovalPerimeter = 1050; // Aproximado para el path
    progressPath.style.strokeDasharray = `${ovalPerimeter}`;
    progressPath.style.strokeDashoffset = `${ovalPerimeter}`;

    // Cargar modelos
    // Usamos CDN de weights compatible
    const MODEL_URL = 'https://cdn.jsdelivr.net/gh/cgarciagl/face-api.js@0.22.2/weights/';

    async function loadModels() {
        try {
            statusText.innerText = "Iniciando cámara...";
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            // await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL); // Opcional, para landmarks si queremos precisión
            isModelLoaded = true;
            startVideo();
        } catch (err) {
            console.error("Error loading models:", err);
            statusText.innerText = "Error cargando IA. Usa captura manual.";
            showManualCapture();
        }
    }

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
            .then(stream => {
                video.srcObject = stream;

                // Mostrar Preview
                const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
                preview.src = dataUrl;

                video.style.display = 'none';
                preview.style.display = 'block';

                btnCapture.style.display = 'none';
                btnRetake.style.display = 'flex';
                btnSend.style.display = 'flex';
            });

        // Repetir
        btnRetake.addEventListener('click', () => {
            video.style.display = 'block';
            preview.style.display = 'none';
            btnCapture.style.display = 'flex';
            btnRetake.style.display = 'none';
            btnSend.style.display = 'none';
        });

        // Enviar
        btnSend.addEventListener('click', async () => {
            // Loading state
            btnSend.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
            btnSend.disabled = true;
            btnRetake.disabled = true;

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            try {
                const formData = new FormData();
                formData.append('selfie', dataUrl); // Base64 string
                formData.append('cliente_id', '<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>');

                const response = await fetch('modules/api/procesar_selfie.php', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.href = 'index.php?status=espera&id=<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>';
                } else {
                    alert("Error al enviar. Intenta nuevamente.");
                    btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar';
                    btnSend.disabled = false;
                    btnRetake.disabled = false;
                }
            } catch (e) {
                console.error(e);
                alert("Error de conexión.");
                btnSend.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar';
                btnSend.disabled = false;
                btnRetake.disabled = false;
            }
        });

        // Init
        startCamera();
    });
</script>