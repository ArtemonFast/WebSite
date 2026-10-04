<?php
session_start();

$isAdmin = !empty($_SESSION['admin']);

$title = 'Films';
$description = 'Мои фидбеки про фильмы.';
$styles = ['/about.css'];
$active = 'about';
$theme = 'orange';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/films.txt';
$films = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $entry['_id'] = sha1($line);
            $films[] = $entry;
        }
    }
    $films = array_reverse($films);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>Films</h1>

    <p class="meta">
      <a href="/about.php?tab=person">← Личность</a>
    </p>

    <hr>

    <?php if (isset($_GET['saved'])): ?>
      <p class="flash">Фильм сохранён.</p>
    <?php endif; ?>

    <?php if (!empty($films)): ?>
      <?php $num = 1; foreach ($films as $film): ?>
        <article class="film">
          <div class="film__head">
            <span class="full-list__num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
            <h2 class="film__title"><?php echo htmlspecialchars($film['title'] ?? ''); ?></h2>
          </div>
          <div class="film__meta">
            <?php
              $meta = [];
              if (!empty($film['year'])) $meta[] = $film['year'];
              if (!empty($film['director'])) $meta[] = $film['director'];
              if (!empty($film['date_watched'])) $meta[] = $film['date_watched'];
              echo htmlspecialchars(implode(' · ', $meta));
            ?>
            <?php if ($isAdmin && !empty($film['_id'])): ?>
              &nbsp;·&nbsp;
              <a class="edit-link" href="/admin-films-edit.php?id=<?php echo htmlspecialchars($film['_id']); ?>">✎</a>
            <?php endif; ?>
          </div>
          <?php if (!empty($film['feedback'])): ?>
            <div class="film__feedback">
              <?php echo nl2br(htmlspecialchars($film['feedback'])); ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>

    <hr>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
  </main>

</body>
</html>
