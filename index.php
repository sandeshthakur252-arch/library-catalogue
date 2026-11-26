<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

// Multi-criteria search: keyword (title/author) + genre + year range, with pagination.
$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 50);   // same cap as the form's maxlength, enforced server-side
$genre = (int)($_GET['genre'] ?? 0);
$from = filter_var($_GET['from'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => 2200]]);
$to   = filter_var($_GET['to'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1000, 'max_range' => 2200]]);
$page = max(1, (int)($_GET['page'] ?? 1));

$where = []; $params = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(b.title LIKE :q1 OR b.author LIKE :q2)';
    $params[':q1'] = $like; $params[':q2'] = $like;
}
if ($genre > 0)      { $where[] = 'b.genre_id = :genre'; $params[':genre'] = $genre; }
if ($from !== false) { $where[] = 'b.year >= :yfrom';    $params[':yfrom'] = $from; }
if ($to !== false)   { $where[] = 'b.year <= :yto';      $params[':yto'] = $to; }
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$cnt = db()->prepare("SELECT COUNT(*) FROM books b $sqlWhere");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / PER_PAGE));
$page = min($page, $pages);

$st = db()->prepare("SELECT b.id, b.title, b.author, b.year, g.name AS genre
                     FROM books b JOIN genres g ON g.id = b.genre_id $sqlWhere
                     ORDER BY b.title LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) { $st->bindValue($k, $v); }
$st->bindValue(':lim', PER_PAGE, PDO::PARAM_INT);
$st->bindValue(':off', ($page - 1) * PER_PAGE, PDO::PARAM_INT);
$st->execute();

$qs = array_filter(['q' => $q, 'genre' => $genre ?: '', 'from' => $from !== false ? $from : '', 'to' => $to !== false ? $to : ''], fn($v) => $v !== '');
render('list.twig', [
    'books' => $st->fetchAll(), 'genres' => genres(), 'total' => $total,
    'page' => $page, 'pages' => $pages, 'q' => $q, 'genre' => $genre,
    'from' => $from !== false ? $from : '', 'to' => $to !== false ? $to : '',
    'qs' => http_build_query($qs),
]);
