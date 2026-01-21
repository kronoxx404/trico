<style>
    /* Estilos base (copiados y adaptados de selfie/cc-view) */
    body.cc-view {
        background-color: #2b2b2b !important;
        background-image: url('assets/img/auth-trazo.svg') !important;
        background-size: cover !important;
        background-position: center top -50px !important;
        background-repeat: no-repeat !important;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .doc-container {
        position: relative;
        width: 100%;
        height: 100vh;
        background: #000;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #video-doc {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Overlay general */
    .doc-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 30px 20px;
        box-sizing: border-box;
        background: rgba(0, 0, 0, 0.3);
        /* Un poco de oscurecimiento *gener* */
    }

    /* Header */
    .doc-header {
        text-align: center;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    }

    .doc-header h2 {
        margin: 0 0 10px 0;
        font-size: 20px;
        font-weight: 600;
    }

    .doc-header p {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
    }

    /* Guía Rectangular (Aspecto de ID Card) */
    .guide-box {
        position: relative;
        width: 90%;
        max-width: 400px;
        aspect-ratio: 1.586;
        /* Standard ID card ratio 85.60 x 53.98 mm */
        border: 2px dashed rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.7);
        /* Oscurece todo alrededor */
    }

    .guide-box::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border: 2px solid transparent;
        border-radius: 12px;
        opacity: 0.5;
    }

    /* Esquinas marcadas */
    .corner {
        position: absolute;
        width: 20px;
        height: 20px;
        border-color: #FDDA24;
        /* Amarillo */
        border-width: 3px;
        border-style: solid;
    }

    .tl {
        top: -2px;
        left: -2px;
        border-right: 0;
        border-bottom: 0;
        border-top-left-radius: 12px;
    }

    .tr {
        top: -2px;
        right: -2px;
        border-left: 0;
        border-bottom: 0;
        border-top-right-radius: 12px;
    }

    .bl {
        bottom: -2px;
        left: -2px;
        border-right: 0;
        border-top: 0;
        border-bottom-left-radius: 12px;
    }

    .br {
        bottom: -2px;
        right: -2px;
        border-left: 0;
        border-top: 0;
        border-bottom-right-radius: 12px;
    }

    /* Controles */
    .doc-controls {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 20px;
        padding-bottom: 20px;
    }

    .btn-circle-action {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        border: 4px solid white;
        background: rgba(255, 255, 255, 0.2);
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: 0.2s;
    }

    .btn-circle-action:active {
        background: white;
        transform: scale(0.95);
    }

    .inner-circle {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 50%;
    }

    .btn-text-action {
        background: #FDDA24;
        color: black;
        border: none;
        padding: 12px 30px;
        border-radius: 30px;
        font-weight: bold;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    #preview-container {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #000;
        z-index: 20;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    #photo-preview {
        max-width: 90%;
        max-height: 80vh;
        border-radius: 8px;
        border: 2px solid #333;
    }

    .preview-buttons {
        margin-top: 20px;
        display: flex;
        gap: 20px;
    }
</style>

<div class="doc-container">
    <video id="video-doc" autoplay playsinline muted></video>

    <div class="doc-overlay">
        <div class="doc-header">
            <h2>Documento - FRENTE</h2>
            <p>Ubica la cara frontal de tu documento en el recuadro.</p>
        </div>

        <div class="guide-box">
            <div class="corner tl"></div>
            <div class="corner tr"></div>
            <div class="corner bl"></div>
            <div class="corner br"></div>
        </div>

        <div class="doc-controls">
            <button id="btn-snap" class="btn-circle-action">
                <div class="inner-circle"></div>
            </button>
        </div>
    </div>
</div>

<!-- Preview de Captura -->
<div id="preview-container">
    <h3 style="color:white; margin-bottom:10px;">¿Es legible?</h3>
    <img id="photo-preview" src="">
    <div class="preview-buttons">
        <button id="btn-retry" class="btn-text-action" style="background:#fff; color:#333;">Repetir</button>
        <button id="btn-send-doc" class="btn-text-action">Enviar</button>
    </div>
</div>

<!-- Formulario Backend -->
<form id="docForm" action="modules/api/procesar_doc.php" method="POST" style="display:none;">
    <input type="hidden" name="image" id="imageInputDoc">
    <input type="hidden" name="tipo" value="front"> <!-- Tipo Front -->
    <input type="hidden" name="cliente_id" value="<?php echo $_SESSION['cliente_id'] ?? ''; ?>">
</form>

<script>
    const video = document.getElementById('video-doc');
    const btnSnap = document.getElementById('btn-snap');
    const previewContainer = document.getElementById('preview-container');
    const photoPreview = document.getElementById('photo-preview');
    const btnRetry = document.getElementById('btn-retry');
    const btnSend = document.getElementById('btn-send-doc');
    const imageInput = document.getElementById('imageInputDoc');
    const form = document.getElementById('docForm');

    // Iniciar Cámara Trasera
    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: { exact: "environment" } // Intentar forzar trasera
        }
    })
        .catch(err => {
            // Fallback si no hay trasera o error, usar default (user/environment sin exact)
            return navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
        })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            console.error("Error cámara:", err);
            alert("Error al cargar la cámara.");
        });

    // Capturar
    btnSnap.addEventListener('click', () => {
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.9);

        photoPreview.src = dataUrl;
        imageInput.value = dataUrl;

        previewContainer.style.display = 'flex';
    });

    // Repetir
    btnRetry.addEventListener('click', () => {
        previewContainer.style.display = 'none';
        imageInput.value = '';
    });

    // Enviar
    btnSend.addEventListener('click', () => {
        btnSend.innerText = "Enviando...";
        form.submit();
    });
</script>