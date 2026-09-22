<?php
$title = 'Blog programmer';
$description = 'It\'s my blog. I write here my thoughts and my reaction on news.';
$styles = ['/blog/blog.css'];
?>
<!DOCTYPE html>
<?php $active = 'blog'; ?>
<html lang="en">
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
<body>
  <h1 class="headline">Blog Programmer</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>
  <main>
    <section class="posts">
      <article class="post">
        <a class="post__link" href="posts/first-post.php">
          <h2 class="post__title">Почему я решил сделать свой сайт</h2>
          <p class="post__desc">Первый пост. Рассказываю, зачем мне это, чего хочу и почему без JS.</p>
          <time class="post__date" datetime="2025-01-15">15 января 2025</time>
        </a>
    </section>
  </main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</footer>
</body>
</html>
