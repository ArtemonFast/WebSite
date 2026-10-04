<?php
session_start();

$title = 'Music';
$description = 'Вся музыка, которую я слушаю.';
$styles = ['/about.css'];
$active = 'about';
$theme = 'orange';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/music.txt';
$tracks = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $tracks[] = $entry;
        }
    }
    $tracks = array_reverse($tracks);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>Music</h1>

    <p class="meta">
      <a href="/about.php?tab=person">← Личность</a>
    </p>

    <hr>

    <p class="person__intro">
      Вся музыка, которую я слушаю. Всего: <?php echo count($tracks); ?>.
    </p>

    <?php if (!empty($tracks)): ?>
      <ul class="full-list">
<?php $num = 1; foreach ($tracks as $track): ?>
  <li class="full-list__item">
    <span class="full-list__num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
    <a href="<?php echo htmlspecialchars($track['link'] ?? '#'); ?>"
       target="_blank" rel="noopener">
      <?php echo htmlspecialchars($track['artist'] ?? ''); ?>
      —
      <?php echo htmlspecialchars($track['title'] ?? ''); ?>
    </a>
    <?php if (!empty($track['note'])): ?>
      <span class="full-list__note"><?php echo htmlspecialchars($track['note']); ?></span>
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
