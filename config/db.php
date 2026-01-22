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
    // Render y otros proveedores suelen requerir SSL
    $dsn = "pgsql:host=$host;port=$port;dbname=$db_name;sslmode=require";
} else {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
}

// Debugging ONLY for setup_db.php
if (basename($_SERVER['PHP_SELF']) == 'setup_db.php') {
    echo "<div style='background:#eee; padding:10px; border:1px solid #ccc; margin-bottom:10px;'>";
    echo "<strong>🔍 Debug Info:</strong><br>";
    echo "Host: " . htmlspecialchars($host) . "<br>";
    echo "User: " . htmlspecialchars($user) . "<br>";
    echo "DB Name: " . htmlspecialchars($db_name) . "<br>";
    echo "Port: " . htmlspecialchars($port) . "<br>";
    echo "Driver: " . htmlspecialchars($driver) . "<br>";
    echo "</div>";
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
    // Log detallado para depuración (Ocultando password)
    $maskedPass = substr($pass, 0, 3) . '***';
    error_log("DB Connection Failed!");
    error_log("Driver: $driver");
    error_log("Host: $host");
    error_log("Port: $port");
    error_log("DB: $db_name");
    error_log("User: $user");
    error_log("Error Message: " . $e->getMessage());

    // Matar proceso
    // Matar proceso mostrando el error real para depuración
    die("Error connecting to the database: " . $e->getMessage());
}
?>