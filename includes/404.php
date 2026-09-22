<?php
$title = '404 — Страница не найдена';
$description = 'Такой страницы нет.';
$styles = [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
  <?php $active = ''; ?>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>
    <section class="not-found">
      <h1 class="not-found__code">404</h1>
      <p class="not-found__text">Такой страницы нет. Возможно, она переехала или её никогда не было.</p>
      <p class="not-found__text">Если вы здесь из-за проблемы с сылками, то напишите issues.</p>
      <p><a href="/index.php">← На главную</a></p>
    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
