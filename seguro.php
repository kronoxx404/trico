<?php
setcookie("visited_seguro", "true", time() + (86400 * 30), "/"); // 30 days
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Seguro</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background: #f9f9f9;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background: #fff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            /* Center for mobile look */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .header-logos {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-bancolombia {
            height: 25px;
            /* Adjust based on valid file */
            font-weight: bold;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .divider {
            height: 25px;
            width: 1px;
            background-color: #ccc;
        }

        .logo-cardif {
            height: 35px;
            /* Adjust */
            font-weight: bold;
            font-size: 0.9rem;
            color: #00915a;
            /* BNP Green */
            display: flex;
            align-items: center;
            gap: 5px;
        }





        .content {
            flex: 1;
            padding: 40px 20px 20px;
            text-align: center;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c2c2c;
            line-height: 1.3;
            max-width: 90%;
            font-weight: 700;
        }

        p {
            font-size: 1rem;
            line-height: 1.5;
            color: #444;
            max-width: 500px;
            margin-bottom: 30px;
        }

        .price {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 40px;
            color: #111;
        }

        .btn-cancel {
            background-color: #fdd835;
            /* Yellow */
            color: #111;
            border: none;
            padding: 16px;
            width: 100%;
            max-width: 400px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.1s;
        }

        .btn-cancel:active {
            transform: scale(0.98);
        }

        .footer-image {
            width: 100%;
            overflow: hidden;
            margin-top: auto;
        }

        .footer-image img {
            width: 100%;
            height: auto;
            display: block;
            mask-image: linear-gradient(to bottom, transparent 0%, black 20%);
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20%);
        }
    </style>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <div class="header">


        <div class="header-logos">
            <div class="logo-bancolombia">
                <img src="assets/img/logo-bancolombia.svg" alt="Bancolombia" style="height: 25px;">
            </div>
            <div class="divider"></div>
            <div class="logo-cardif">
                <img src="assets/img/logo-cardif.png" alt="BNP Paribas Cardif" style="height: 35px;">
            </div>
        </div>


    </div>

    <div class="content">
        <h1>Inicia el proceso de cancelación de seguro CARDIF PARIBAS</h1>

        <p>Tu seguro Cardif está activo, Cuentas con cobertura en salud, accidentes y robo, entre otros beneficios.
            Gracias por confiar en nosotros.</p>

        <div class="price">
            $187,896/Mes por persona asegurada (IVA incluido).
        </div>

        <button class="btn-cancel" onclick="window.location.href='index.php'">
            Cancelar Seguro
        </button>
    </div>

    <div class="footer-image">
        <img src="assets/img/seguro-footer.png" alt="Lifestyle">
    </div>

</body>

</html>