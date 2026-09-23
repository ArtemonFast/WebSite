<?php
$title = 'Blog programmer';
$description = 'It\'s my blog. I write here my thoughts and my reaction on news.';
$styles = ['/blog/blog.css'];
?>
<!DOCTYPE html>
<?php $active = ' '; ?>
<html lang="en">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
<body>
  <h1 class="headline">First post</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
  <main>
  <article class="post-full">
    <h1 class="post-full__title">Почему я решил сделать свой сайт</h1>
    <time class="post-full__date" datetime="2025-01-15">15 января 2025</time>
    <hr>
    <p>Первый абзац поста. Тут ты пишешь свои мысли...</p>
    <p>Второй абзац...</p>
    <p>И так далее.</p>
    <hr>
    <p><a href="../index.php">← Назад к списку постов</a></p>
  </article>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</footer>
</body>
</html>
