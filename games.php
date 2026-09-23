<?php
$title = 'My games';
$description = 'Игры моего производства.';
$styles = [];
$active = 'games';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<h1 class="headline">Games</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>
    <section class="page-stub">
      <p class="page-stub__text">
        Раздел в разработке. Скоро здесь появятся информация о моих играх.
      </p>
      <p class="page-stub__text">
        А пока — загляни в <a href="/blog/index.php">блог</a>.
      </p>
    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
