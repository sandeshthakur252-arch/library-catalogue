<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$st = db()->prepare('SELECT * FROM books WHERE id = ?');
$st->execute([$id]);
$book = $st->fetch();
if (!$book) { http_response_code(404); render('error.twig', ['message' => 'Book not found.']); exit; }
if (!can_modify($book['created_by'])) { http_response_code(403); render('error.twig', ['message' => 'You can only edit books you added.']); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$clean, $errors] = validate_book($_POST);
    $book = $clean + ['id' => $id];
    if (!$errors) {
        try {
            $up = db()->prepare('UPDATE books SET title=?, author=?, genre_id=?, year=?, isbn=?, description=? WHERE id=?');
            $up->execute([$clean['title'], $clean['author'], $clean['genre_id'], $clean['year'], $clean['isbn'] ?: null, $clean['description'] ?: null, $id]);
            flash('Book updated.');
            header('Location: view.php?id=' . $id);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { $errors[] = 'That ISBN already exists.'; } else { error_log($e->getMessage()); $errors[] = 'Could not update the book.'; }
        }
    }
}
render('book_form.twig', ['mode' => 'edit', 'book' => $book, 'errors' => $errors, 'genres' => genres()]);
