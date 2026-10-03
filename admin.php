<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$title = 'Admin';
$description = 'Админка.';
$styles = [];
$active = '';
$theme = 'green';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin'] = true;
        header('Location: /admin.php');
        exit;
    }

    $error = 'Неверный пароль.';
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>Admin</h1>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>

    <hr>

    <?php if (!empty($_SESSION['admin'])): ?>
      <p class="flash">Ты вошёл как админ.</p>

      <ul class="admin-menu">
        <li><a href="/admin-post.php">→ Написать пост</a></li>
        <li><a href="/admin-game.php">→ Добавить игру</a></li>
        <li><a href="/blog/index.php">→ Блог</a></li>
        <li><a href="/games/index.php">→ Игры</a></li>
        <li><a href="/guestbook.php">→ Гостевая</a></li>
        <li><a href="/admin.php?logout=1">→ Выйти</a></li>
      </ul>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="form-errors">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="post" class="form">
        <label>
          Пароль:
          <input type="password" name="password" required autofocus>
        </label>
        <button type="submit">Войти</button>
      </form>
    <?php endif; ?>

    <hr>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>
  </main>

</body>
</html>
