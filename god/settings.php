<?php
// god/settings.php — Gestión de configuración en base de datos (Neon compatible)
header('Content-Type: application/json');
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/config.php';
$conn = require_once __DIR__ . '/../config/db.php';

// Asegurarse que existe la tabla settings
try {
    $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    $serial = ($driver === 'pgsql') ? 'SERIAL PRIMARY KEY' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $conn->exec("CREATE TABLE IF NOT EXISTS settings (
        id    $serial,
        key   VARCHAR(100) UNIQUE NOT NULL,
        value TEXT NOT NULL
    )");
}
catch (Exception $e) {
}

// Leer un setting
function getSetting($conn, string $key, $default = null)
{
    try {
        $stmt = $conn->prepare("SELECT value FROM settings WHERE key = :key");
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['value'] : $default;
    }
    catch (Exception $e) {
        return $default;
    }
}

// Escribir un setting (upsert)
function setSetting($conn, string $key, $value)
{
    try {
        $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'pgsql') {
            $sql = "INSERT INTO settings (key, value) VALUES (:key, :value)
                    ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value";
        }
        else {
            $sql = "INSERT INTO settings (key, value) VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE value = VALUES(value)";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute(['key' => $key, 'value' => $value]);
        return true;
    }
    catch (Exception $e) {
        return false;
    }
}

$action = $_GET['action'] ?? '';

// GET redirect status
if ($action === 'get_redirect') {
    $val = getSetting($conn, 'redirect_enabled', '1');
    echo json_encode(['status' => 'success', 'enabled' => ($val === '1')]);
    exit;
}

// SET redirect status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'set_redirect') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['enabled'])) {
        $val = $input['enabled'] ? '1' : '0';
        setSetting($conn, 'redirect_enabled', $val);
        echo json_encode(['status' => 'success', 'enabled' => (bool)$input['enabled']]);
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Missing enabled param']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
