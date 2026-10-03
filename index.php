<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/includes/statuses.php';

$title = 'Home';
$description = 'Личный сайт разработчика. Пишу про игры, мысли, код.';
$styles = [];
$active = 'home';
$theme = 'green';

$items = [];

// Посты
$postsFile = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
if (is_file($postsFile)) {
    $lines = file($postsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $items[] = [
                'type'  => 'blog',
                'date'  => $entry['date'] ?? '',
                'title' => $entry['title'] ?? '',
                'slug'  => $entry['slug'] ?? '',
            ];
        }
    }
}

// Игры
$gamesFile = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
if (is_file($gamesFile)) {
    $lines = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $items[] = [
                'type'   => 'game',
                'date'   => $entry['date'] ?? '',
                'title'  => $entry['title'] ?? '',
                'slug'   => $entry['slug'] ?? '',
                'status' => $entry['status'] ?? '',
            ];
        }
    }
}

// Свежие сверху
usort($items, function ($a, $b) {
    return strcmp($b['date'], $a['date']);
});
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>DAF inc.</h1>

    <hr>

    <nav class="nav-section">
      <a href="/blog/index.php">Блог</a>
      <a href="/games/index.php">Мои игры</a>
      <a href="/guestbook.php">Гостевая</a>
      <a href="/about.php">Обо мне</a>
    </nav>

    <hr>

    <p>Личный сайт разработчика.</p>
    <p>Пишу про игры, мысли, код.</p>

    <hr>

    <h2>Registry</h2>

    <?php if (!empty($items)): ?>
      <div class="registry">
        <?php foreach ($items as $item): ?>
          <?php
            $link = $item['type'] === 'blog'
              ? '/blog/post.php?slug=' . urlencode($item['slug'])
              : '/games/game.php?slug=' . urlencode($item['slug']);
          ?>
          <div class="registry__item">
            <time class="registry__date"><?php echo htmlspecialchars($item['date']); ?></time>
            <span class="registry__bullet">●</span>
            <span class="registry__type registry__type--<?php echo htmlspecialchars($item['type']); ?>">
              [<?php echo htmlspecialchars($item['type']); ?>]
            </span>
            <span class="registry__dash">-</span>
            <a class="registry__title" href="<?php echo $link; ?>">
              <?php echo htmlspecialchars($item['title']); ?>
            </a>
            <?php if ($item['type'] === 'game' && !empty($item['status'])): ?>
              <span class="registry__status">
                [<?php echo htmlspecialchars($STATUSES[$item['status']] ?? $item['status']); ?>]
              </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>

    <hr>
  </main>

</body>
</html>
