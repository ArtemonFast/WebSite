<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/statuses.php';

if (empty($_SESSION['admin'])) {
    header('Location: /admin.php');
    exit;
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$title = 'Новая игра';
$description = 'Публикация игры.';
$styles = [];
$active = '';
$theme = 'green';

$errors = [];
$form = [
    'slug'        => '',
    'title'       => '',
    'status'      => 'in-dev',
    'short'       => '',
    'description' => '',
    'screenshots' => '',
    'link'        => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        http_response_code(403);
        exit('CSRF-токен неверный.');
    }

    $form['slug']        = trim($_POST['slug'] ?? '');
    $form['title']       = trim($_POST['title'] ?? '');
    $form['status']      = trim($_POST['status'] ?? '');
    $form['short']       = trim($_POST['short'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['screenshots'] = trim($_POST['screenshots'] ?? '');
    $form['link']        = trim($_POST['link'] ?? '');

    if ($form['slug'] === '') {
        $errors[] = 'Slug не может быть пустым.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $form['slug'])) {
        $errors[] = 'Slug: только латиница, цифры и дефисы.';
    } elseif (mb_strlen($form['slug']) > 100) {
        $errors[] = 'Slug слишком длинный (максимум 100).';
    }

    if ($form['title'] === '') {
        $errors[] = 'Название не может быть пустым.';
    } elseif (mb_strlen($form['title']) > 200) {
        $errors[] = 'Название слишком длинное (максимум 200).';
    }

    if (!isset($STATUSES[$form['status']])) {
        $errors[] = 'Неизвестный статус.';
    }

    if (mb_strlen($form['short']) > 300) {
        $errors[] = 'Короткое описание слишком длинное (максимум 300).';
    }

    if ($form['description'] === '') {
        $errors[] = 'Описание не может быть пустым.';
    } elseif (mb_strlen($form['description']) > 20000) {
        $errors[] = 'Описание слишком длинное (максимум 20000).';
    }

    if ($form['link'] !== '' && !filter_var($form['link'], FILTER_VALIDATE_URL)) {
        $errors[] = 'Ссылка не похожа на URL.';
    }

    if (empty($errors)) {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
        if (is_file($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if (is_array($entry) && ($entry['slug'] ?? '') === $form['slug']) {
                    $errors[] = 'Такой slug уже занят.';
                    break;
                }
            }
        }
    }

    if (empty($errors)) {
        $screenshots = [];
        if ($form['screenshots'] !== '') {
            foreach (explode("\n", $form['screenshots']) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $screenshots[] = $line;
                }
            }
        }

        if ($form['short'] === '') {
            $form['short'] = mb_substr($form['description'], 0, 150);
            if (mb_strlen($form['description']) > 150) {
                $form['short'] .= '…';
            }
        }

        $entry = [
            'slug'        => $form['slug'],
            'title'       => $form['title'],
            'date'        => date('Y-m-d'),
            'status'      => $form['status'],
            'short'       => $form['short'],
            'description' => $form['description'],
            'screenshots' => $screenshots,
            'link'        => $form['link'],
        ];

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt',
            json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );

        header('Location: /games/');
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
    <h1>Новая игра</h1>

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
        Название:
        <input type="text" name="title" required value="<?php echo htmlspecialchars($form['title']); ?>">
      </label>

      <label>
        Статус:
        <select name="status">
          <?php foreach ($STATUSES as $key => $name): ?>
            <option value="<?php echo htmlspecialchars($key); ?>"
              <?php echo $form['status'] === $key ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Короткое описание (если пусто — возьмём из описания):
        <textarea name="short" rows="2"><?php echo htmlspecialchars($form['short']); ?></textarea>
      </label>

      <label>
        Полное описание:
        <textarea name="description" rows="10" required><?php echo htmlspecialchars($form['description']); ?></textarea>
      </label>

      <label>
        Скриншоты (по одному пути на строку, можно пусто):
        <textarea name="screenshots" rows="4" placeholder="/Assets/games/my-game/screen1.png&#10;/Assets/games/my-game/screen2.png"><?php echo htmlspecialchars($form['screenshots']); ?></textarea>
      </label>

      <label>
        Ссылка на скачивание (можно пусто):
        <input type="url" name="link" value="<?php echo htmlspecialchars($form['link']); ?>">
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
