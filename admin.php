<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$title = 'Admin';
$description = 'Админка.';
$styles = [];
$active = '';

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

// Выход
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
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

  <main>
    <section class="guestbook">
      <h1 class="guestbook__title">Admin</h1>
      <hr>

      <?php if (!empty($_SESSION['admin'])): ?>
        <p class="guestbook__ok">Ты вошёл как админ.</p>
        <p><a href="/admin.php?logout=1">Выйти</a></p>
        <p><a href="/admin-post.php">→ Написать пост</a></p>
        <p><a href="/guestbook.php">→ К гостевой</a></p>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="guestbook__errors">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <form method="post" class="guestbook__form">
          <label>
            Пароль:
            <input type="password" name="password" required autofocus>
          </label>
          <button type="submit">Войти</button>
        </form>
      <?php endif; ?>
    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
