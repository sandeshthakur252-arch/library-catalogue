<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION = [];
    $c = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $c['path'], $c['domain'], (bool)$c['secure'], true);
    session_destroy();
}
header('Location: index.php');
