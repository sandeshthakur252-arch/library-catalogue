<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Use the delete button.'); }  // never delete via GET
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$own = db()->prepare('SELECT created_by FROM books WHERE id = ?');
$own->execute([$id]);
$row = $own->fetch();
if ($row && !can_modify($row['created_by'])) { http_response_code(403); exit('You can only delete books you added.'); }
$st = db()->prepare('DELETE FROM books WHERE id = ?');
$st->execute([$id]);
flash($st->rowCount() ? 'Book deleted.' : 'Book not found.', $st->rowCount() ? 'ok' : 'error');
header('Location: index.php');
