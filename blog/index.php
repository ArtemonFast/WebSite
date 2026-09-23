<?php
session_start();

$title = 'Blog';
$description = 'It\'s my blog. I write here my thoughts and my reaction on news.';
$styles = ['/blog/blog.css'];
$active = 'blog';

// Читаем посты
$file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$posts = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $posts[] = $entry;
        }
    }
    $posts = array_reverse($posts);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
    <h1 class="headline">Blog programmer</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>

    <section class="posts">
      <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
          <article class="post">
            <a class="post__link" href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>">
              <h2 class="post__title"><?php echo htmlspecialchars($post['title']); ?></h2>
              <p class="post__desc"><?php echo htmlspecialchars($post['description']); ?></p>
              <time class="post__date" datetime="<?php echo htmlspecialchars($post['date']); ?>">
                <?php echo htmlspecialchars($post['date']); ?>
              </time>
            </a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Постов пока нет.</p>
      <?php endif; ?>
    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
