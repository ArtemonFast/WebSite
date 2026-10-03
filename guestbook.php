<?php
session_start();

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$isAdmin = !empty($_SESSION['admin']);

$title = 'Guestbook';
$description = 'Гостевая книга.';
$styles = [];
$active = 'guestbook';
$theme = 'green';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/messages.txt';

$errors = [];
$name = '';
$message = '';
$honeypot = '';

// ===== УДАЛЕНИЕ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!$isAdmin) {
        http_response_code(403);
        exit('Нет доступа.');
    }
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        http_response_code(403);
        exit('CSRF-токен неверный.');
    }

    $idToDelete = $_POST['delete_id'];

    if (is_file($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $kept = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $entry = json_decode($line, true);
            if (!is_array($entry)) continue;

            $entryId = $entry['id'] ?? '';
            $entryParent = $entry['parent_id'] ?? null;

            // Удаляем само сообщение И все его ответы
            if ($entryId === $idToDelete) continue;
            if ($entryParent === $idToDelete) continue;

            $kept[] = $line;
        }
        file_put_contents($file, implode("\n", $kept) . (empty($kept) ? '' : "\n"), LOCK_EX);
    }

    header('Location: /guestbook.php');
    exit;
}

// ===== ДОБАВЛЕНИЕ =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_id'])) {
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $parentId = $_POST['parent_id'] ?? '';
    
    $honeypot = trim($_POST['website'] ?? '');

    // Honeypot: если поле заполнено — это бот
    if ($honeypot !== '') {
      // Тихо притворяемся, что всё ок — бот не поймёт, что его раскусили
      header('Location: /guestbook.php?ok=1');
      exit;
    }

    if ($name === '') {
        $errors[] = 'Имя не может быть пустым.';
    } elseif (mb_strlen($name) > 50) {
        $errors[] = 'Имя слишком длинное (максимум 50).';
    }

    if ($message === '') {
        $errors[] = 'Сообщение не может быть пустым.';
    } elseif (mb_strlen($message) > 1000) {
        $errors[] = 'Сообщение слишком длинное (максимум 1000).';
    }

    // Проверка parent_id — должен существовать
    if ($parentId !== '') {
        $parentExists = false;
        if (is_file($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if (is_array($entry) && ($entry['id'] ?? '') === $parentId) {
                    $parentExists = true;
                    break;
                }
            }
        }
        if (!$parentExists) {
            $parentId = '';
        }
    }

    if (empty($errors)) {
        $entry = [
            'id'        => bin2hex(random_bytes(8)),
            'parent_id' => $parentId === '' ? null : $parentId,
            'name'      => $name,
            'message'   => $message,
            'time'      => date('Y-m-d H:i:s'),
        ];
        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

        header('Location: /guestbook.php?ok=1');
        exit;
    }
}

// ===== ЧТЕНИЕ =====
$all = [];
if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry) && isset($entry['id'])) {
            $all[] = $entry;
        }
    }
}

$parents = [];
$children = [];
foreach ($all as $msg) {
    $pid = $msg['parent_id'] ?? null;
    if ($pid === null) {
        $parents[] = $msg;
    } else {
        $children[$pid][] = $msg;
    }
}
$parents = array_reverse($parents);

// Ответ на кого?
$replyTo = $_GET['reply_to'] ?? '';
$replyTarget = null;
if ($replyTo !== '') {
    foreach ($all as $msg) {
        if (($msg['id'] ?? '') === $replyTo) {
            $replyTarget = $msg;
            break;
        }
    }
    if ($replyTarget === null) {
        $replyTo = '';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/head.php'; ?>
</head>
<body class="theme-<?php echo $theme; ?>">

  <main class="container">
    <h1>Guestbook</h1>

    <p class="meta">
      <a href="/">← На главную</a>
      <?php if ($isAdmin): ?>
        &nbsp;·&nbsp; <a href="/admin.php?logout=1">Выйти</a>
      <?php endif; ?>
    </p>

    <hr>

    <?php if (isset($_GET['ok'])): ?>
      <p class="flash">Сообщение сохранено.</p>
    <?php endif; ?>

    <?php if ($replyTarget !== null): ?>
      <p class="flash">
        Отвечаешь на сообщение от
        <strong><?php echo htmlspecialchars($replyTarget['name']); ?></strong>.
        &nbsp;<a href="/guestbook.php">Отменить</a>
      </p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="form-errors">
        <strong>Ошибки:</strong>
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" class="form">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
      <input type="hidden" name="parent_id" value="<?php echo htmlspecialchars($replyTo); ?>">

      <div class="honeypot" aria-hidden="true">
        <label>
          Website:
          <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
        </label>
      </div>

      <label>
        Имя:
        <input type="text" name="name" required value="<?php echo htmlspecialchars($name); ?>">
      </label>

      <label>
        Сообщение:
        <textarea name="message" rows="4" required><?php echo htmlspecialchars($message); ?></textarea>
      </label>

      <button type="submit"><?php echo $replyTarget !== null ? 'Ответить' : 'Оставить сообщение'; ?></button>
    </form>

    <hr>

    <?php if (!empty($parents)): ?>
      <h2>Сообщения (<?php echo count($all); ?>)</h2>

      <div class="messages">
        <?php foreach ($parents as $parent): ?>
          <article class="message">
            <header class="message__head">
              <strong class="message__name"><?php echo htmlspecialchars($parent['name']); ?></strong>
              <time class="message__time"><?php echo htmlspecialchars($parent['time']); ?></time>
            </header>
            <div class="message__body">
              <?php echo nl2br(htmlspecialchars($parent['message'])); ?>
            </div>

            <div class="message__actions">
              <a class="message__reply"
                 href="/guestbook.php?reply_to=<?php echo urlencode($parent['id']); ?>">↩ Ответить</a>
              <?php if ($isAdmin): ?>
                <form method="post" class="message__delete">
                  <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($parent['id']); ?>">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                  <button type="submit">Удалить</button>
                </form>
              <?php endif; ?>
            </div>

            <?php if (!empty($children[$parent['id']])): ?>
              <div class="message__replies">
                <?php foreach ($children[$parent['id']] as $child): ?>
                  <article class="message message--reply">
                    <header class="message__head">
                      <strong class="message__name"><?php echo htmlspecialchars($child['name']); ?></strong>
                      <time class="message__time"><?php echo htmlspecialchars($child['time']); ?></time>
                    </header>
                    <div class="message__body">
                      <?php echo nl2br(htmlspecialchars($child['message'])); ?>
                    </div>

                    <?php if ($isAdmin): ?>
                      <div class="message__actions">
                        <form method="post" class="message__delete">
                          <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($child['id']); ?>">
                          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf']); ?>">
                          <button type="submit">Удалить</button>
                        </form>
                      </div>
                    <?php endif; ?>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="meta">Пока сообщений нет. Будь первым!</p>
    <?php endif; ?>

    <hr>

    <p class="meta">
      <a href="/">← На главную</a>
    </p>
  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
  </main>

</body>
</html>
