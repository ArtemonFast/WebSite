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

$title = 'Новый пост';
$description = 'Публикация поста.';
$styles = [];
$active = '';
$theme = 'green';

$errors = [];
$form = [
    'slug'        => '',
    'title'       => '',
    'topic'       => 'thoughts',
    'description' => '',
    'body'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if (empty($errors)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
        if (is_file($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if (is_array($entry) && ($entry['slug'] ?? '') === $form['slug']) {
                    $errors[] = 'Такой slug уже занят. Выбери другой.';
                    break;
                }
            }
        }
    }

    if (empty($errors)) {
        if ($form['description'] === '') {
            $form['description'] = mb_substr($form['body'], 0, 150);
            if (mb_strlen($form['body']) > 150) {
                $form['description'] .= '…';
            }
        }

        $entry = [
            'slug'        => $form['slug'],
            'title'       => $form['title'],
            'date'        => date('Y-m-d'),
            'topic'       => $form['topic'],
            'description' => $form['description'],
            'body'        => $form['body'],
        ];

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt',
            json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );

        header('Location: /blog/');
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
    <h1>Новый пост</h1>

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

      <button type="submit">Опубликовать</button>
    </form>

    <hr>

    <p class="meta">
      <a href="/admin.php">← В админку</a>
    </p>
  </main>

</body>
</html>
