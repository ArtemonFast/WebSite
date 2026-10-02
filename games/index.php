<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/includes/statuses.php';

$title = 'My Games';
$description = 'Мои игры и прототипы.';
$styles = [];
$active = 'games';
$theme = 'green';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
$games = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $games[] = $entry;
        }
    }
    $games = array_reverse($games);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>My Games</h1>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>

    <hr>

    <?php if (!empty($games)): ?>
      <ul class="games">
        <?php foreach ($games as $game): ?>
          <li class="games__item">
            <a class="games__link" href="/games/game.php?slug=<?php echo urlencode($game['slug']); ?>">
              <?php echo htmlspecialchars($game['title']); ?>
            </a>
            <span class="games__status status-<?php echo htmlspecialchars($game['status']); ?>">
              [<?php echo htmlspecialchars($STATUSES[$game['status']] ?? $game['status']); ?>]
            </span>
            <span class="games__date"><?php echo htmlspecialchars($game['date']); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="meta">Игр пока нет.</p>
    <?php endif; ?>

    <hr>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>
  </main>

</body>
</html>
