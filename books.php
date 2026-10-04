<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/includes/book_statuses.php';

$isAdmin = !empty($_SESSION['admin']);

$title = 'Books';
$description = 'Книги, которые я читал или читаю.';
$styles = ['/about.css'];
$active = 'about';
$theme = 'orange';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/books.txt';
$books = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
    $entry = json_decode($line, true);
    if (is_array($entry)) {
        $entry['_id'] = sha1($line);
        $books[] = $entry;
    }
}
    $books = array_reverse($books);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>Books</h1>

    <p class="meta">
      <a href="/about.php?tab=person">← Личность</a>
    </p>

    <hr>

    <p class="person__intro">
      Книги, которые я читал, читаю или хочу прочитать. Всего: <?php echo count($books); ?>.
    </p>

    <?php if (!empty($books)): ?>
    <?php if (isset($_GET['saved'])): ?>
  <p class="flash">Книга сохранена.</p>
<?php endif; ?>
      <ul class="full-list">
<?php $num = 1; foreach ($books as $book): ?>
  <?php
    $st = $book['status'] ?? 'read';
    $stData = $BOOK_STATUSES[$st] ?? ['label' => $st, 'mark' => '?'];
  ?>
  <li class="full-list__item book book--<?php echo htmlspecialchars($st); ?>">
    <span class="full-list__num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
    <span class="book__mark"><?php echo $stData['mark']; ?></span>
    <?php if (!empty($book['link'])): ?>
      <a href="<?php echo htmlspecialchars($book['link']); ?>"
         target="_blank" rel="noopener">
        <?php echo htmlspecialchars($book['title'] ?? ''); ?>
      </a>
    <?php else: ?>
      <span><?php echo htmlspecialchars($book['title'] ?? ''); ?></span>
    <?php endif; ?>
    <span class="book__author">— <?php echo htmlspecialchars($book['author'] ?? ''); ?></span>
<?php if ($isAdmin && !empty($book['_id'])): ?>
  &nbsp;·&nbsp;
  <a class="edit-link" href="/admin-books-edit.php?id=<?php echo htmlspecialchars($book['_id']); ?>">✎</a>
<?php endif; ?>
    <?php if (!empty($book['note'])): ?>
      <span class="full-list__note"><?php echo htmlspecialchars($book['note']); ?></span>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>

    <hr>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
  </main>

</body>
</html>
