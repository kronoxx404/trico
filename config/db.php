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

// ── Detectar Driver ─────────────────────────────────────────
$driver = 'mysql'; // Default

$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    $url = parse_url($databaseUrl);
    if (isset($url['scheme']) && in_array($url['scheme'], ['postgres', 'postgresql'])) {
        $driver = 'pgsql';
    }
}

// Fallback: puerto 5432 → Postgres
if ($port == '5432') {
    $driver = 'pgsql';
}

// ── Construir DSN ───────────────────────────────────────────
if ($driver === 'pgsql') {
    // Workaround Neon SNI: extraer endpoint ID del hostname
    // Hostname: ep-jolly-rice-aizz7hvg-pooler.c-4.us-east-1.aws.neon.tech
    // Endpoint: ep-jolly-rice-aizz7hvg-pooler
    $endpointId = explode('.', $host)[0];

    // Estrategia 1: via variable de entorno PGOPTIONS (libpq la lee)
    putenv("PGOPTIONS=-c endpoint={$endpointId}");
    $_ENV['PGOPTIONS'] = "-c endpoint={$endpointId}";

    // Estrategia 2: incluir endpoint en las options del DSN
    // PDO pgsql acepta el string de options directamente para libpq
    $dsn = "pgsql:host={$host};port={$port};dbname={$db_name};sslmode=require;options=endpoint={$endpointId}";

}
else {
    $dsn = "mysql:host={$host};port={$port};dbname={$db_name};charset=utf8mb4";
}

// ── Conectar ────────────────────────────────────────────────
try {
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    $pdo = $conn;
    return $pdo;

}
catch (PDOException $e) {
    $maskedPass = substr($pass, 0, 3) . '***';
    error_log("DB Connection Failed!");
    error_log("Driver: $driver | Host: $host | Port: $port | DB: $db_name");
    error_log("Error: " . $e->getMessage());
    die("Error connecting to the database: " . $e->getMessage());
}