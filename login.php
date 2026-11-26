<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    if (login_attempt_allowed($username)) {
        $st = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
        $st->execute([$username]);
        $u = $st->fetch();
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
            clear_login_attempts($username);
            session_regenerate_id(true);                                  // prevents session fixation
            $_SESSION['uid'] = (int)$u['id']; $_SESSION['uname'] = $u['username'];
            flash('Welcome back, ' . $u['username'] . '.');
            header('Location: index.php'); exit;
        }
    }
    $error = 'Invalid username or password.';                         // same message for both cases
}
render('login.twig', ['error' => $error]);
