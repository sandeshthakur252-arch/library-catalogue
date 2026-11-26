<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$errors = []; $username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $pw = (string)($_POST['password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) { $errors[] = 'Username: 3-30 letters, numbers or underscores.'; }
    if (strlen($pw) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
    if ($pw !== (string)($_POST['password2'] ?? '')) { $errors[] = 'Passwords do not match.'; }
    if (!$errors) {
        try {
            $st = db()->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $st->execute([$username, password_hash($pw, PASSWORD_DEFAULT)]);   // bcrypt, salted
            flash('Account created. Please log in.');
            header('Location: login.php'); exit;
        } catch (PDOException $e) {
            $errors[] = $e->getCode() === '23000' ? 'That username is taken.' : 'Could not create account.';
        }
    }
}
render('register.twig', ['errors' => $errors, 'username' => $username]);
