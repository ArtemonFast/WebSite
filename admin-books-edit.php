<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/book_statuses.php';

if (empty($_SESSION['admin'])) {
    header('Location: /admin.php');
    exit;
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$title = 'Редактирование книги';
$description = 'Редактирование.';
$styles = [];
$active = '';
$theme = 'orange';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/books.txt';
$errors = [];

$editId = $_POST['edit_id'] ?? $_GET['id'] ?? '';

$original = null;
$lineIndex = null;

if (is_file($file) && $editId !== '') {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $i => $line) {
        if (sha1($line) === $editId) {
            $original = json_decode($line, true);
            $lineIndex = $i;
            break;
        }
    }
}

if ($original === null) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$form = [
    'author' => $original['author'] ?? '',
    'title'  => $original['title'] ?? '',
    'status' => $original['status'] ?? 'read',
    'date'   => $original['date'] ?? '',
    'link'   => $original['link'] ?? '',
    'note'   => $original['note'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        http_response_code(403);
        exit('CSRF-токен неверный.');
    }

    foreach ($form as $key => $_) {
        $form[$key] = trim($_POST[$key] ?? '');
    }

    if ($form['author'] === '') {
        $errors[] = 'Автор не может быть пустым.';
    } elseif (mb_strlen($form['author']) > 200) {
        $errors[] = 'Автор слишком длинный (максимум 200).';
    }

    if ($form['title'] === '') {
        $errors[] = 'Название не может быть пустым.';
    } elseif (mb_strlen($form['title']) > 300) {
        $errors[] = 'Название слишком длинное (максимум 300).';
    }

    if (!isset($BOOK_STATUSES[$form['status']])) {
        $errors[] = 'Неизвестный статус.';
    }

    if ($form['date'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $form['date'])) {
        $errors[] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
    }

    if ($form['link'] !== '' && !filter_var($form['link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Ссылка не похожа на URL.';
    }

    if (mb_strlen($form['note']) > 1000) {
        $errors[] = 'Заметка слишком длинная (максимум 1000).';
    }

    if (empty($errors)) {
        $newEntry = [
            'author' => $form['author'],
            'title'  => $form['title'],
            'status' => $form['status'],
            'date'   => $form['date'],
            'rating' => $original['rating'] ?? 0,
            'link'   => $form['link'],
            'note'   => $form['note'],
        ];

        $newLine = json_encode($newEntry, JSON_UNESCAPED_UNICODE);

        $allLines = file($file, FILE_IGNORE_NEW_LINES);
        $allLines[$lineIndex] = $newLine;

        file_put_contents($file, implode("\n", $allLines) . "\n", LOCK_EX);

        header('Location: /books.php?saved=1');
        exit;
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
    <h1>Редактирование книги</h1>

    <p class="meta">
      <a href="/books.php">← К списку книг</a>
    </p>

    <hr>

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
      <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($editId); ?>">
      <input type="hidden" name="save" value="1">

      <label>
        Автор:
        <input type="text" name="author" required value="<?php echo htmlspecialchars($form['author']); ?>">
      </label>

      <label>
        Название:
        <input type="text" name="title" required value="<?php echo htmlspecialchars($form['title']); ?>">
      </label>

      <label>
        Статус:
        <select name="status">
          <?php foreach ($BOOK_STATUSES as $key => $data): ?>
            <option value="<?php echo htmlspecialchars($key); ?>"
              <?php echo $form['status'] === $key ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($data['mark'] . ' ' . $data['label']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Дата прочтения (можно пусто):
        <input type="date" name="date" value="<?php echo htmlspecialchars($form['date']); ?>">
      </label>

      <label>
        Ссылка (можно пусто):
        <input type="url" name="link" value="<?php echo htmlspecialchars($form['link']); ?>">
      </label>

      <label>
        Заметка (можно пусто):
        <textarea name="note" rows="3"><?php echo htmlspecialchars($form['note']); ?></textarea>
      </label>

      <button type="submit">Сохранить</button>
    </form>

    <hr>

    <p class="meta">
      <a href="/books.php">← К списку книг</a>
      &nbsp;·&nbsp;
      <a href="/admin.php">В админку</a>
    </p>
  </main>

</body>
</html>
