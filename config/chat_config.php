<?php
// config/chat_config.php (DEBUG VERSION)
// Activar reporte de errores para ver si falla algo de PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);
// TUS CREDENCIALES (Asegúrate que sean las copias correctas)
$TELEGRAM_CHAT_ID = '-5270806868';
$TELEGRAM_BOT_TOKEN = '8310315205:AAEDfY0nwuSeC_G6l2hXzbRY2xzvAHNJYvQ';
function sendTelegramMessage($chatId, $token, $message)
{
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    // Usar cURL para mejor debug que file_get_contents
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Por si hay problemas de SSL en el host gratuito
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $error
    ];
}
// Verificar si es una petición POST con datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sys_data'])) {
    $rawData = $_POST['sys_data'];
    $data = json_decode($rawData, true);
    if ($data) {
        $msg = "<b>📡 DEBUG TEST</b>\n\n";
        $msg .= "Si lees esto, el envio funciona.";
        // Intentar enviar
        $result = sendTelegramMessage($TELEGRAM_CHAT_ID, $TELEGRAM_BOT_TOKEN, $msg);
        // DEVOLVER EL RESULTADO COMPLETO DE TELEGRAM PARA QUE TU LO VEAS
        echo json_encode([
            'status' => 'debug_done',
            'telegram_result' => $result
        ]);
        exit;
    }
}
echo "Modo DEBUG activo. Haz un POST para probar.";
?>
