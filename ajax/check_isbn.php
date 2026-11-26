<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$isbn = strtoupper(trim((string)($_GET['isbn'] ?? '')));
$id = (int)($_GET['id'] ?? 0);
if (!preg_match('/^[0-9X-]{10,17}$/', $isbn)) { echo json_encode(['valid' => false, 'available' => false]); exit; }
$st = db()->prepare('SELECT COUNT(*) FROM books WHERE isbn = ? AND id <> ?');
$st->execute([$isbn, $id]);
echo json_encode(['valid' => true, 'available' => (int)$st->fetchColumn() === 0]);
