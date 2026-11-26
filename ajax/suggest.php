<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) < 2 || mb_strlen($q) > 50) { echo '[]'; exit; }
$like = addcslashes($q, '%_\\') . '%';          // prefix match
$st = db()->prepare('(SELECT title AS label, \'Title\' AS kind FROM books WHERE title LIKE :a LIMIT 5)
                     UNION (SELECT DISTINCT author, \'Author\' FROM books WHERE author LIKE :b LIMIT 5)');
$st->execute([':a' => $like, ':b' => $like]);
echo json_encode($st->fetchAll(), JSON_UNESCAPED_UNICODE);
