<?php
session_start();

$isAdmin = !empty($_SESSION['admin']);

$title = 'Пост';
$description = '';
$styles = ['/blog/blog.css'];
$active = 'blog';
$theme = 'green';

$slug = $_GET['slug'] ?? '';

if ($slug === '') {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$post = null;
$postLine = null;

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry) && ($entry['slug'] ?? '') === $slug) {
            $post = $entry;
            $postLine = $line;
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
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <p class="meta">
      <a href="/blog/index.php">← Все посты</a>
    </p>

    <hr>

    <?php if (isset($_GET['saved'])): ?>
      <p class="flash">Пост сохранён.</p>
    <?php endif; ?>

    <article class="post-full">
      <h1 class="post-full__title"><?php echo htmlspecialchars($post['title']); ?></h1>

      <p class="meta">
        <time class="post-full__date" datetime="<?php echo htmlspecialchars($post['date']); ?>">
          <?php echo htmlspecialchars($post['date']); ?>
        </time>
        <?php if ($isAdmin): ?>
          &nbsp;·&nbsp;<a href="/admin-post-edit.php?id=<?php echo sha1($postLine); ?>">✎ Редактировать</a>
        <?php endif; ?>
      </p>

      <hr>

      <div class="post-full__body">
        <?php echo nl2br(htmlspecialchars($post['body'])); ?>
      </div>

      <hr>

      <p class="meta">
        <a href="/blog/index.php">← Все посты</a>
      </p>
    </article>
  </main>

</body>
</html>
