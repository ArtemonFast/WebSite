<?php
$title = 'Guestbook';
$description = 'Гостевая книга.';
$styles = [];
$active = 'guestbook';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<h1 class="headline">Guestbook</h1>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php'; ?>

<main>
  <section class="guestbook">
    <h1 class="guestbook__title">Guestbook</h1>
    <hr>

    <?php
    $errors = [];
    $name = '';
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '') {
            $errors[] = 'Имя не может быть пустым.';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = 'Имя слишком длинное (максимум 50 символов).';
        }

        if ($message === '') {
            $errors[] = 'Сообщение не может быть пустым.';
        } elseif (mb_strlen($message) > 1000) {
            $errors[] = 'Сообщение слишком длинное (максимум 1000 символов).';
        }

        if (empty($errors)) {
    $entry = [
        'name'    => $name,
        'message' => $message,
        'time'    => date('Y-m-d H:i:s'),
    ];

    $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";

    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/data/messages.txt',
        $line,
        FILE_APPEND | LOCK_EX
    );

    echo '<p class="guestbook__ok">Сообщение сохранено!</p>';
}
    }
    ?>

    <?php if (!empty($errors)): ?>
      <div class="guestbook__errors">
        <strong>Ошибки:</strong>
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" class="guestbook__form">
      <label>
        Имя:
        <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>">
      </label>
      <label>
        Сообщение:
        <textarea name="message" rows="4" required><?php echo htmlspecialchars($message); ?></textarea>
      </label>
      <button type="submit">Оставить сообщение</button>
    </form>
  </section>
</main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
