<?php
// Cargar configuración global si no está cargada
if (!isset($config) || !is_array($config)) {
    $config = require __DIR__ . '/config.php';
}

$host = $config['db_host'];
$port = $config['db_port'];
$db_name = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

// Detectar Driver
$driver = 'mysql'; // Default

// Si hay DATABASE_URL en entorno (Render), intentar deducir driver del scheme
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    if (isset($url['scheme']) && $url['scheme'] === 'postgres') {
        $driver = 'pgsql';
    }
} elseif ($port == '5432') {
    // Fallback: si el puerto es 5432, asumir Postgres
    $driver = 'pgsql';
}

// Construir DSN
if ($driver === 'pgsql') {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
}

try {
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // Para compatibilidad con código que espera $pdo
    $pdo = $conn;
    return $pdo;

} catch (PDOException $e) {
    // En producción, loguear error y salir
    error_log("DB Connection failed: " . $e->getMessage());
    die("Error connecting to the database.");
}
?>