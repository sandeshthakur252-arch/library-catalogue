<?php
declare(strict_types=1);
require __DIR__ . '/config.php';

// ---- Minimal PSR-4 autoloader (Twig + polyfills, no Composer needed on the server) ----
spl_autoload_register(function (string $class): void {
    $map = [
        'Twig\\' => 'vendor/twig/src/',
        'Symfony\\Polyfill\\Mbstring\\' => 'vendor/polyfill-mbstring/',
        'Symfony\\Polyfill\\Ctype\\' => 'vendor/polyfill-ctype/',
    ];
    foreach ($map as $prefix => $dir) {
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
            $file = __DIR__ . '/' . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) { require $file; }
            return;
        }
    }
});
require_once __DIR__ . '/vendor/deprecation-contracts/function.php';
require_once __DIR__ . '/vendor/polyfill-mbstring/bootstrap.php';
require_once __DIR__ . '/vendor/polyfill-ctype/bootstrap.php';

// ---- Error handling: log details, show nothing sensitive ----
ini_set('display_errors', '0');
set_exception_handler(function (Throwable $e): void {
    error_log((string)$e);
    if (!headers_sent()) { http_response_code(500); }
    exit('Something went wrong. Please try again later.');
});

// ---- Secure session + headers ----
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) { ini_set('session.cookie_secure', '1'); }
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

// ---- Database (PDO, real prepared statements) ----
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('Database connection failed.');   // no internal details leaked
        }
    }
    return $pdo;
}

// ---- CSRF ----
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    $t = $_POST['csrf'] ?? '';
    $s = $_SESSION['csrf'] ?? '';
    if ($s === '' || !is_string($t) || !hash_equals($s, $t)) {   // empty session token must never match
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

// ---- Auth / flash ----
function current_user(): ?array {
    return isset($_SESSION['uid']) ? ['id' => $_SESSION['uid'], 'username' => $_SESSION['uname']] : null;
}
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type]; }
function require_login(): void {
    if (!current_user()) { flash('Please log in first.', 'error'); header('Location: login.php'); exit; }
}

function login_attempt_allowed(string $username): bool {
    $pdo = db();
    $pdo->exec('DELETE FROM login_attempts WHERE window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY) LIMIT 100');

    $key = hash('sha256', strtolower(trim($username)) . "\0" . (string)($_SERVER['REMOTE_ADDR'] ?? ''));
    $st = $pdo->prepare('SELECT locked_until FROM login_attempts WHERE bucket_key = ? AND locked_until > UTC_TIMESTAMP()');
    $st->execute([$key]);
    if ($st->fetchColumn() !== false) { return false; }

    $st = $pdo->prepare(
        'INSERT INTO login_attempts (bucket_key, attempts, window_started_at)
         VALUES (?, 1, UTC_TIMESTAMP())
         ON DUPLICATE KEY UPDATE
           attempts = IF(window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), 1, attempts + 1),
           locked_until = IF(
             window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE),
             NULL,
             IF(locked_until > UTC_TIMESTAMP(), locked_until,
               IF(attempts >= 5, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), NULL)
             )
           ),
           window_started_at = IF(window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP(), window_started_at)'
    );
    $st->execute([$key]);

    $st = $pdo->prepare('SELECT attempts FROM login_attempts WHERE bucket_key = ?');
    $st->execute([$key]);
    return (int)$st->fetchColumn() <= 5;
}

function clear_login_attempts(string $username): void {
    $key = hash('sha256', strtolower(trim($username)) . "\0" . (string)($_SERVER['REMOTE_ADDR'] ?? ''));
    $st = db()->prepare('DELETE FROM login_attempts WHERE bucket_key = ?');
    $st->execute([$key]);
}

// ---- Authorisation: only the creator may change a book; ownerless books are read-only ----
function can_modify($createdBy): bool {
    $u = current_user();
    return $u !== null && $createdBy !== null && (int)$createdBy === (int)$u['id'];
}

// ---- Twig template engine (autoescape ON: all output is HTML-escaped by default) ----
function render(string $tpl, array $vars = []): void {
    static $twig = null;
    if ($twig === null) {
        $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates'), [
            'autoescape' => 'html', 'cache' => false,
        ]);
        $twig->addFunction(new \Twig\TwigFunction('csrf_token', 'csrf_token'));
    }
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    echo $twig->render($tpl, $vars + ['user' => current_user(), 'flash' => $flash]);
}

// ---- Book validation (server side is the source of truth) ----
function genres(): array { return db()->query('SELECT id, name FROM genres ORDER BY name')->fetchAll(); }

function validate_book(array $in): array {
    $b = [
        'title' => trim((string)($in['title'] ?? '')),
        'author' => trim((string)($in['author'] ?? '')),
        'genre_id' => (int)($in['genre_id'] ?? 0),
        'year' => (string)($in['year'] ?? ''),
        'isbn' => strtoupper(trim((string)($in['isbn'] ?? ''))),
        'description' => trim((string)($in['description'] ?? '')),
    ];
    $e = [];
    if ($b['title'] === '' || mb_strlen($b['title']) > 200) { $e[] = 'Title is required (max 200 characters).'; }
    if ($b['author'] === '' || mb_strlen($b['author']) > 120) { $e[] = 'Author is required (max 120 characters).'; }
    $st = db()->prepare('SELECT COUNT(*) FROM genres WHERE id = ?');
    $st->execute([$b['genre_id']]);
    if (!$st->fetchColumn()) { $e[] = 'Please choose a valid genre.'; }
    $y = filter_var($b['year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => (int)date('Y') + 1]]);
    if ($y === false) { $e[] = 'Year must be a number between 1000 and next year.'; } else { $b['year'] = $y; }
    if ($b['isbn'] !== '' && !preg_match('/^[0-9X-]{10,17}$/', $b['isbn'])) { $e[] = 'ISBN must be 10-17 characters: digits, X or hyphens.'; }
    if (mb_strlen($b['description']) > 2000) { $e[] = 'Description is too long (max 2000).'; }
    return [$b, $e];
}
