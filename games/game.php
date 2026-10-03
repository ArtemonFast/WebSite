<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/includes/statuses.php';

$isAdmin = !empty($_SESSION['admin']);

$title = 'Игра';
$description = '';
$styles = [];
$active = 'games';
$theme = 'green';

$slug = $_GET['slug'] ?? '';

if ($slug === '') {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
$game = null;
$gameLine = null;

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry) && ($entry['slug'] ?? '') === $slug) {
            $game = $entry;
            $gameLine = $line;
            break;
        }
    }
}

if ($game === null) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$title = $game['title'];
$description = $game['short'] ?? '';
$statusName = $STATUSES[$game['status']] ?? $game['status'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <p class="meta">
      <a href="/games/index.php">← Все игры</a>
    </p>

    <hr>

    <?php if (isset($_GET['saved'])): ?>
      <p class="flash">Игра сохранена.</p>
    <?php endif; ?>

    <h1><?php echo htmlspecialchars($game['title']); ?></h1>

    <p class="game-meta">
      <span class="games__status status-<?php echo htmlspecialchars($game['status']); ?>">
        [<?php echo htmlspecialchars($statusName); ?>]
      </span>
      <span class="games__date"><?php echo htmlspecialchars($game['date']); ?></span>
      <?php if ($isAdmin && $gameLine !== null): ?>
        &nbsp;·&nbsp;
        <a href="/admin-game-edit.php?id=<?php echo sha1($gameLine); ?>">✎ Редактировать</a>
      <?php endif; ?>
    </p>

    <hr>

    <div class="game-description">
      <?php echo nl2br(htmlspecialchars($game['description'] ?? '')); ?>
    </div>

    <?php if (!empty($game['screenshots'])): ?>
      <h2>Скриншоты</h2>
      <div class="game-screenshots">
        <?php foreach ($game['screenshots'] as $shot): ?>
          <a href="<?php echo htmlspecialchars($shot); ?>" target="_blank" rel="noopener">
            <img src="<?php echo htmlspecialchars($shot); ?>"
                 alt="Скриншот <?php echo htmlspecialchars($game['title']); ?>"
                 class="game-screenshots__img">
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($game['link'])): ?>
      <hr>
      <p class="game-download">
        → <a href="<?php echo htmlspecialchars($game['link']); ?>"
             target="_blank" rel="noopener">
          Скачать / Подробнее
        </a>
      </p>
    <?php endif; ?>

    <hr>

    <p class="meta">
      <a href="/games/index.php">← Все игры</a>
    </p>
  </main>

</body>
</html>
