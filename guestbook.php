<?php
$title = 'Guestbook';
$description = 'Гостевая книга.';
$styles = [];
$active = 'guestbook';

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

        header('Location: /guestbook.php?ok=1');
        exit;
    }
}

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/messages.txt';
$messages = [];
if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $messages[] = $entry;
        }
    }
    $messages = array_reverse($messages);
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
      <h1 class="guestbook__title">Guestbook</h1>
      <hr>

      <?php if (isset($_GET['ok'])): ?>
        <p class="guestbook__ok">Сообщение сохранено!</p>
      <?php endif; ?>

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

      <hr>

      <?php if (!empty($messages)): ?>
        <h2>Сообщения (<?php echo count($messages); ?>)</h2>
        <div class="guestbook__messages">
          <?php foreach ($messages as $msg): ?>
            <article class="guestbook__msg">
              <header class="guestbook__msg-head">
                <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                <time class="guestbook__msg-time"><?php echo htmlspecialchars($msg['time']); ?></time>
              </header>
              <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="guestbook__empty">Пока сообщений нет. Будь первым!</p>
      <?php endif; ?>

    </section>
  </main>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
