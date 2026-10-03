<?php
session_start();

$title = 'About me';
$description = 'Обо мне.';
$styles = ['/about.css'];
$active = 'about';
$theme = 'orange';

$validTabs = ['main', 'person', 'links'];
$tab = $_GET['tab'] ?? 'main';
if (!in_array($tab, $validTabs, true)) {
    $tab = 'main';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>About me</h1>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>

    <hr>

    <nav class="tabs">
      <a class="tabs__link <?php echo $tab === 'main' ? 'active' : ''; ?>"
         href="/about.php">Обо мне</a>
      <a class="tabs__link <?php echo $tab === 'person' ? 'active' : ''; ?>"
         href="/about.php?tab=person">Личность</a>
      <a class="tabs__link <?php echo $tab === 'links' ? 'active' : ''; ?>"
         href="/about.php?tab=links">Ссылки</a>
    </nav>

    <hr>

    <?php if ($tab === 'main'): ?>
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/about/main.php'; ?>
    <?php elseif ($tab === 'person'): ?>
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/about/person.php'; ?>
    <?php elseif ($tab === 'links'): ?>
      <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/about/links.php'; ?>
    <?php endif; ?>

    <hr>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
  </main>

</body>
</html>
