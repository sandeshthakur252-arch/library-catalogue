<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $_SESSION['fails'] = $_SESSION['fails'] ?? 0;
    if ($_SESSION['fails'] >= 5) { sleep(2); }                       // basic brute-force slow-down
    $st = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
    $st->execute([trim((string)($_POST['username'] ?? ''))]);
    $u = $st->fetch();
    if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
        session_regenerate_id(true);                                  // prevents session fixation
        $_SESSION['uid'] = (int)$u['id']; $_SESSION['uname'] = $u['username']; $_SESSION['fails'] = 0;
        flash('Welcome back, ' . $u['username'] . '.');
        header('Location: index.php'); exit;
    }
    $_SESSION['fails']++;
    $error = 'Invalid username or password.';                         // same message for both cases
}
render('login.twig', ['error' => $error]);
