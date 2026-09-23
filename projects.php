<?php
$title = 'Projects';
$description = 'Мои проекты.';
$styles = [];
$active = 'projects';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<h1 class="headline">Projects</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>
    <section class="page-stub">
      <p class="page-stub__text">
        Раздел в разработке. Скоро здесь появятся мои проекты.
      </p>
      <p class="page-stub__text">
        А пока — загляни в <a href="/blog/index.php">блог</a>.
      </p>
    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
