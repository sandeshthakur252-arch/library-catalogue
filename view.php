<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT b.*, g.name AS genre FROM books b JOIN genres g ON g.id = b.genre_id WHERE b.id = ?');
$st->execute([$id]);
$book = $st->fetch();
if (!$book) { http_response_code(404); render('error.twig', ['message' => 'Book not found.']); exit; }
render('view.twig', ['book' => $book, 'can_edit' => can_modify($book['created_by'])]);
