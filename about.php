<?php
session_start();

$title = 'About me';
$description = 'Обо мне.';
$styles = [];
$active = 'about';
$theme = 'orange';
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

    <p>Привет! Меня зовут <strong>DAF</strong>.</p>

    <p>Я делаю игры и пишу код. Этот сайт — мой личный уголок в интернете:
       блог, список игр, гостевая книга. Всё вручную, без JS, на чистом PHP.</p>

    <p>Мне нравится эстетика старых терминалов, пиксельные шрифты и минимализм.
       Поэтому сайт выглядит так, а не иначе.</p>

    <hr>

    <h2>Контакты</h2>

    <ul class="about-links">
      <li>Email: <a href="mailto:you@example.com">you@example.com</a></li>
      <li>GitHub: <a href="https://github.com/" target="_blank" rel="noopener">github.com/твой-ник</a></li>
      <li>Steam: <a href="https://steamcommunity.com/" target="_blank" rel="noopener">steam/твой-ник</a></li>
    </ul>

    <hr>

    <h2>Технологии сайта</h2>

    <ul class="about-links">
      <li>HTML5 + CSS3 — вёрстка</li>
      <li>PHP — серверная логика</li>
      <li>JSON-файлы — хранение данных</li>
      <li>Ни строчки JavaScript</li>
    </ul>

    <hr>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>
  </main>

</body>
</html>
