<?php
session_start();

$title = 'Home';
$description = 'Личный сайт разработчика. Пишу про игры, мысли, код.';
$styles = [];
$active = 'home';
$theme = 'green'; // 'green' | 'orange' | 'purple'

// Последние посты
$postsFile = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$recentPosts = [];
if (is_file($postsFile)) {
    $lines = file($postsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $all = [];
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $all[] = $entry;
        }
    }
    $all = array_reverse($all);
    $recentPosts = array_slice($all, 0, 3);
}

// Последние игры
$gamesFile = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
$recentGames = [];
if (is_file($gamesFile)) {
    $lines = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $all = [];
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $all[] = $entry;
        }
    }
    $all = array_reverse($all);
    $recentGames = array_slice($all, 0, 3);
}
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

    <?php if (!empty($recentPosts)): ?>
      <h2>Последнее из блога</h2>
      <div class="recent">
        <?php foreach ($recentPosts as $p): ?>
          <div class="recent__item">
            <a href="/blog/post.php?slug=<?php echo urlencode($p['slug']); ?>">
              <?php echo htmlspecialchars($p['title']); ?>
            </a>
            <span class="recent__date"><?php echo htmlspecialchars($p['date']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($recentGames)): ?>
      <h2>Последние игры</h2>
      <div class="recent">
        <?php foreach ($recentGames as $g): ?>
          <div class="recent__item">
            <a href="/games/game.php?slug=<?php echo urlencode($g['slug']); ?>">
              <?php echo htmlspecialchars($g['title']); ?>
            </a>
            <span class="recent__date"><?php echo htmlspecialchars($g['status'] ?? ''); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($recentPosts) && empty($recentGames)): ?>
      <p class="meta">Пока пусто. Скоро будет контент.</p>
    <?php endif; ?>
  </main>

</body>
</html>
