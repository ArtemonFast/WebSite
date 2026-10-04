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

$title = 'Новая книга';
$description = 'Добавление книги.';
$styles = [];
$active = '';
$theme = 'orange';

$errors = [];
$form = [
    'author' => '',
    'title'  => '',
    'status' => 'read',
    'date'   => '',
    'link'   => '',
    'note'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $entry = [
            'author' => $form['author'],
            'title'  => $form['title'],
            'status' => $form['status'],
            'date'   => $form['date'],
            'rating' => 0,
            'link'   => $form['link'],
            'note'   => $form['note'],
        ];

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/data/books.txt',
            json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );

        header('Location: /books.php');
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
    <h1>Новая книга</h1>

    <p class="meta">
      <a href="/admin.php">← В админку</a>
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

      <button type="submit">Добавить</button>
    </form>

    <hr>

    <p class="meta">
      <a href="/admin.php">← В админку</a>
    </p>
  </main>

</body>
</html>
