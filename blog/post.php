<?php
session_start();

$title = 'Пост';
$description = '';
$styles = ['/blog/blog.css'];
$active = 'blog';

$slug = $_GET['slug'] ?? '';

if ($slug === '') {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$post = null;

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry) && ($entry['slug'] ?? '') === $slug) {
            $post = $entry;
            break;
        }
    }
}

if ($post === null) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$title = $post['title'];
$description = $post['description'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>
    <article class="post-full">
      <h1 class="post-full__title"><?php echo htmlspecialchars($post['title']); ?></h1>
      <time class="post-full__date" datetime="<?php echo htmlspecialchars($post['date']); ?>">
        <?php echo htmlspecialchars($post['date']); ?>
      </time>
      <hr>
      <div class="post-full__body">
        <?php echo nl2br(htmlspecialchars($post['body'])); ?>
      </div>
      <hr>
      <p><a href="/blog/index.php">← Назад к списку постов</a></p>
    </article>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
