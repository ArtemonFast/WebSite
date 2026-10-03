<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = '404 — Не найдено';
$description = 'Такой страницы нет.';
$styles = [];
$theme = 'green';
$active = '';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>404</h1>

    <hr>

    <p>Такой страницы нет.</p>
    <p>Возможно, она переехала, либо её никогда не было.</p>

    <hr>

    <p class="meta">
      <a href="/">← На главную</a>
      &nbsp;·&nbsp;
      <a href="/blog/index.php">Блог</a>
      &nbsp;·&nbsp;
      <a href="/games/index.php">Игры</a>
    </p>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
  </main>

</body>
</html>
