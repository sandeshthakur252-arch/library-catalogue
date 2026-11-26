<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
require_login();
$book = ['title' => '', 'author' => '', 'genre_id' => 0, 'year' => '', 'isbn' => '', 'description' => ''];
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$book, $errors] = validate_book($_POST);
    if (!$errors) {
        try {
            $st = db()->prepare('INSERT INTO books (title, author, genre_id, year, isbn, description, created_by) VALUES (?,?,?,?,?,?,?)');
            $st->execute([$book['title'], $book['author'], $book['genre_id'], $book['year'], $book['isbn'] ?: null, $book['description'] ?: null, current_user()['id']]);
            flash('Book added.');
            header('Location: view.php?id=' . (int)db()->lastInsertId());
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { $errors[] = 'That ISBN already exists.'; } else { error_log($e->getMessage()); $errors[] = 'Could not save the book.'; }
        }
    }
}
render('book_form.twig', ['mode' => 'add', 'book' => $book, 'errors' => $errors, 'genres' => genres()]);
