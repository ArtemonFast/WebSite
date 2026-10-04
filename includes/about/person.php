<p class="person__intro">
  Здесь я собрал то, что мне нравится: музыка, книги, фильмы и достижения.
  Каждый раздел — последние 10 записей. Хочешь больше — клик по «Все …».
</p>

<div class="windows-grid">

<?php
$filmsFile = $_SERVER['DOCUMENT_ROOT'] . '/data/films.txt';
$filmsAll = [];
$filmsCount = 0;

if (is_file($filmsFile)) {
    $lines = file($filmsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $filmsAll[] = $entry;
        }
    }
    $filmsAll = array_reverse($filmsAll);
    $filmsCount = count($filmsAll);
    $filmsAll = array_slice($filmsAll, 0, 3);
}
?>
<div class="win win--films">
  <div class="win__titlebar">
    <span class="win__title">films</span>
    <span class="win__close">×</span>
  </div>
  <div class="win__body">
    <?php if (!empty($filmsAll)): ?>
    <?php $num = 1; foreach ($filmsAll as $film): ?>
  <div class="film-preview">
    <span class="win__list-num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
    <span class="film-preview__title"><?php echo htmlspecialchars($film['title'] ?? ''); ?></span>
    <?php
      $meta = [];
      if (!empty($film['year'])) $meta[] = $film['year'];
      if (!empty($film['director'])) $meta[] = $film['director'];
      if (!empty($meta)):
    ?>
      <span class="film-preview__meta">— <?php echo htmlspecialchars(implode(' · ', $meta)); ?></span>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>
  </div>
  <div class="win__footer">
    <a href="/films.php">→ Все фильмы (<?php echo $filmsCount; ?>)</a>
  </div>
</div>

<?php
$musicFile = $_SERVER['DOCUMENT_ROOT'] . '/data/music.txt';
$musicAll = [];
$musicCount = 0;

if (is_file($musicFile)) {
    $lines = file($musicFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $musicAll[] = $entry;
        }
    }
    $musicAll = array_reverse($musicAll);
    $musicCount = count($musicAll);
    $musicAll = array_slice($musicAll, 0, 10);
}
?>
<div class="win win--music">
  <div class="win__titlebar">
    <span class="win__title">music</span>
    <span class="win__close">×</span>
  </div>
  <div class="win__body">
    <?php if (!empty($musicAll)): ?>
      <ul class="win__list">
<?php $num = 1; foreach ($musicAll as $track): ?>
  <li class="win__list-item">
    <span class="win__list-num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
    <a href="<?php echo htmlspecialchars($track['link'] ?? '#'); ?>"
       target="_blank" rel="noopener">
      <?php echo htmlspecialchars($track['artist'] ?? ''); ?>
      —
      <?php echo htmlspecialchars($track['title'] ?? ''); ?>
    </a>
  </li>
<?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>
  </div>
  <div class="win__footer">
    <a href="/music.php">→ Вся музыка (<?php echo $musicCount; ?>)</a>
  </div>
</div>

  <div class="win win--achievements">
    <div class="win__titlebar">
      <span class="win__title">achievements</span>
      <span class="win__close">×</span>
    </div>
    <div class="win__body">
      <p class="meta">Скоро тут будут мои достижения.</p>
    </div>
    <div class="win__footer">
      <a href="/achievements.php">→ Все достижения</a>
    </div>
  </div>

<?php
require $_SERVER['DOCUMENT_ROOT'] . '/includes/book_statuses.php';

$booksFile = $_SERVER['DOCUMENT_ROOT'] . '/data/books.txt';
$booksAll = [];
$booksCount = 0;

if (is_file($booksFile)) {
    $lines = file($booksFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $booksAll[] = $entry;
        }
    }
    $booksAll = array_reverse($booksAll);
    $booksCount = count($booksAll);
    $booksAll = array_slice($booksAll, 0, 10);
}
?>
<div class="win win--books">
  <div class="win__titlebar">
    <span class="win__title">books</span>
    <span class="win__close">×</span>
  </div>
  <div class="win__body">
    <?php if (!empty($booksAll)): ?>
      <ul class="win__list">
<?php $num = 1; foreach ($booksAll as $book): ?>
  <?php
    $st = $book['status'] ?? 'read';
    $stData = $BOOK_STATUSES[$st] ?? ['label' => $st, 'mark' => '?'];
  ?>
  <li class="win__list-item book book--<?php echo htmlspecialchars($st); ?>">
    <span class="win__list-num">[<?php echo str_pad($num++, 2, '0', STR_PAD_LEFT); ?>]</span>
    <span class="book__mark"><?php echo $stData['mark']; ?></span>
    <?php if (!empty($book['link'])): ?>
      <a href="<?php echo htmlspecialchars($book['link']); ?>"
         target="_blank" rel="noopener">
        <?php echo htmlspecialchars($book['title'] ?? ''); ?>
      </a>
    <?php else: ?>
      <span><?php echo htmlspecialchars($book['title'] ?? ''); ?></span>
    <?php endif; ?>
    <span class="book__author">— <?php echo htmlspecialchars($book['author'] ?? ''); ?></span>
  </li>
<?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="meta">Пока пусто.</p>
    <?php endif; ?>
  </div>
  <div class="win__footer">
    <a href="/books.php">→ Все книги (<?php echo $booksCount; ?>)</a>
  </div>
</div>

</div>
