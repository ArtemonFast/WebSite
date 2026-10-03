<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/topics.php';

if (empty($_SESSION['admin'])) {
    header('Location: /admin.php');
    exit;
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$title = 'Редактирование поста';
$description = 'Редактирование.';
$styles = [];
$active = '';
$theme = 'green';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$errors = [];

// Отпечаток исходной строки
$editId = $_POST['edit_id'] ?? $_GET['id'] ?? '';

// Ищем исходный пост по отпечатку
$originalLine = null;
$original = null;
$lineIndex = null;

if (is_file($file) && $editId !== '') {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $i => $line) {
        if (sha1($line) === $editId) {
            $originalLine = $line;
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
    'slug'        => $original['slug'] ?? '',
    'title'       => $original['title'] ?? '',
    'topic'       => $original['topic'] ?? 'thoughts',
    'description' => $original['description'] ?? '',
    'body'        => $original['body'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        http_response_code(403);
        exit('CSRF-токен неверный.');
    }

    $form['slug']        = trim($_POST['slug'] ?? '');
    $form['title']       = trim($_POST['title'] ?? '');
    $form['topic']       = trim($_POST['topic'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['body']        = trim($_POST['body'] ?? '');

    // Валидация
    if ($form['slug'] === '') {
        $errors[] = 'Slug не может быть пустым.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $form['slug'])) {
        $errors[] = 'Slug: только латиница, цифры и дефисы.';
    } elseif (mb_strlen($form['slug']) > 100) {
        $errors[] = 'Slug слишком длинный (максимум 100).';
    }

    if ($form['title'] === '') {
        $errors[] = 'Заголовок не может быть пустым.';
    } elseif (mb_strlen($form['title']) > 200) {
        $errors[] = 'Заголовок слишком длинный (максимум 200).';
    }

    if (!isset($TOPICS[$form['topic']])) {
        $errors[] = 'Неизвестная тема.';
    }

    if (mb_strlen($form['description']) > 500) {
        $errors[] = 'Описание слишком длинное (максимум 500).';
    }

    if ($form['body'] === '') {
        $errors[] = 'Тело поста не может быть пустым.';
    } elseif (mb_strlen($form['body']) > 20000) {
        $errors[] = 'Тело поста слишком длинное (максимум 20000).';
    }

    // Проверка slug — если он изменился, не должен совпасть с другими
    if (empty($errors) && $form['slug'] !== ($original['slug'] ?? '')) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (is_array($entry) && ($entry['slug'] ?? '') === $form['slug']) {
                $errors[] = 'Такой slug уже занят другим постом.';
                break;
            }
        }
    }

    if (empty($errors)) {
        // Автозаполнение description
        if ($form['description'] === '') {
            $form['description'] = mb_substr($form['body'], 0, 150);
            if (mb_strlen($form['body']) > 150) {
                $form['description'] .= '…';
            }
        }

        $newEntry = [
            'slug'        => $form['slug'],
            'title'       => $form['title'],
            'date'        => $original['date'], // дата не меняется
            'topic'       => $form['topic'],
            'description' => $form['description'],
            'body'        => $form['body'],
        ];

        $newLine = json_encode($newEntry, JSON_UNESCAPED_UNICODE);

        // Читаем все строки, заменяем нужную
        $allLines = file($file, FILE_IGNORE_NEW_LINES);
        $allLines[$lineIndex] = $newLine;

        file_put_contents($file, implode("\n", $allLines) . "\n", LOCK_EX);

        header('Location: /blog/post.php?slug=' . urlencode($form['slug']) . '&saved=1');
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
    <h1>Редактирование поста</h1>

    <p class="meta">
      <a href="/blog/post.php?slug=<?php echo urlencode($original['slug']); ?>">← К посту</a>
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
        Slug (латиница, цифры, дефисы):
        <input type="text" name="slug" required value="<?php echo htmlspecialchars($form['slug']); ?>">
      </label>

      <label>
        Заголовок:
        <input type="text" name="title" required value="<?php echo htmlspecialchars($form['title']); ?>">
      </label>

      <label>
        Тема:
        <select name="topic">
          <?php foreach ($TOPICS as $key => $name): ?>
            <option value="<?php echo htmlspecialchars($key); ?>"
              <?php echo $form['topic'] === $key ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Описание (если пусто — возьмём из тела):
        <textarea name="description" rows="2"><?php echo htmlspecialchars($form['description']); ?></textarea>
      </label>

      <label>
        Тело поста:
        <textarea name="body" rows="15" required><?php echo htmlspecialchars($form['body']); ?></textarea>
      </label>

      <button type="submit">Сохранить</button>
    </form>

    <hr>
    <p class="meta">
      <a href="/blog/post.php?slug=<?php echo urlencode($original['slug']); ?>">← К посту</a>
    </p>
  </main>

</body>
</html>
