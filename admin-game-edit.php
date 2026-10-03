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

$title = 'Редактирование игры';
$description = 'Редактирование.';
$styles = [];
$active = '';
$theme = 'green';

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
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

// ===== Предзаполнение =====

// Скриншоты
$screenshotsText = '';
if (!empty($original['screenshots']) && is_array($original['screenshots'])) {
    $screenshotsText = implode("\n", $original['screenshots']);
}

// Mirrors
$mirrorsText = '';
if (!empty($original['mirrors']) && is_array($original['mirrors'])) {
    $lines = [];
    foreach ($original['mirrors'] as $m) {
        $lines[] = ($m['name'] ?? '') . ' | ' . ($m['url'] ?? '');
    }
    $mirrorsText = implode("\n", $lines);
}

// Versions
$versionsText = '';
if (!empty($original['versions']) && is_array($original['versions'])) {
    $lines = [];
    foreach ($original['versions'] as $v) {
        $dl = $v['downloads'] ?? [];
        $lines[] = ($v['version'] ?? '') . ' | '
                 . ($v['date'] ?? '') . ' | '
                 . ($dl['windows'] ?? '') . ' | '
                 . ($dl['linux'] ?? '');
    }
    $versionsText = implode("\n", $lines);
}

$form = [
    'slug'        => $original['slug'] ?? '',
    'title'       => $original['title'] ?? '',
    'status'      => $original['status'] ?? 'in-dev',
    'short'       => $original['short'] ?? '',
    'description' => $original['description'] ?? '',
    'screenshots' => $screenshotsText,
    'win_url'     => $original['downloads']['windows']['url'] ?? '',
    'win_size'    => $original['downloads']['windows']['size'] ?? '',
    'lin_url'     => $original['downloads']['linux']['url'] ?? '',
    'lin_size'    => $original['downloads']['linux']['size'] ?? '',
    'mirrors'     => $mirrorsText,
    'versions'    => $versionsText,
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

    // ===== Валидация =====
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

    // Slug-уникальность (если изменился)
    if (empty($errors) && $form['slug'] !== ($original['slug'] ?? '')) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (is_array($entry) && ($entry['slug'] ?? '') === $form['slug']) {
                $errors[] = 'Такой slug уже занят другой игрой.';
                break;
            }
        }
    }

    // ===== Парсинг =====
    if (empty($errors)) {
        // Скриншоты
        $screenshots = [];
        if ($form['screenshots'] !== '') {
            foreach (explode("\n", $form['screenshots']) as $line) {
                $line = trim($line);
                if ($line !== '') $screenshots[] = $line;
            }
        }

        // Mirrors
        $mirrors = [];
        if ($form['mirrors'] !== '') {
            foreach (explode("\n", $form['mirrors']) as $i => $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                    $errors[] = 'Миррор в строке ' . ($i + 1) . ': нужно "название | url".';
                    continue;
                }
                $mirrors[] = ['name' => $parts[0], 'url' => $parts[1]];
            }
        }

        // Versions
        $versions = [];
        if ($form['versions'] !== '') {
            foreach (explode("\n", $form['versions']) as $i => $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) < 4) {
                    $errors[] = 'Версия в строке ' . ($i + 1) . ': нужно "version | date | win_url | lin_url".';
                    continue;
                }
                $versions[] = [
                    'version'   => $parts[0],
                    'date'      => $parts[1],
                    'downloads' => [
                        'windows' => $parts[2],
                        'linux'   => $parts[3],
                    ],
                ];
            }
        }
    }

    if (empty($errors)) {
        if ($form['short'] === '') {
            $form['short'] = mb_substr($form['description'], 0, 150);
            if (mb_strlen($form['description']) > 150) {
                $form['short'] .= '…';
            }
        }

        $newEntry = [
            'slug'        => $form['slug'],
            'title'       => $form['title'],
            'date'        => $original['date'],
            'status'      => $form['status'],
            'short'       => $form['short'],
            'description' => $form['description'],
            'screenshots' => $screenshots,
            'downloads'   => [
                'windows' => ['url' => $form['win_url'], 'size' => $form['win_size']],
                'linux'   => ['url' => $form['lin_url'], 'size' => $form['lin_size']],
            ],
            'mirrors'     => $mirrors,
            'versions'    => $versions,
        ];

        $newLine = json_encode($newEntry, JSON_UNESCAPED_UNICODE);

        $allLines = file($file, FILE_IGNORE_NEW_LINES);
        $allLines[$lineIndex] = $newLine;

        file_put_contents($file, implode("\n", $allLines) . "\n", LOCK_EX);

        header('Location: /games/game.php?slug=' . urlencode($form['slug']) . '&saved=1');
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
    <h1>Редактирование игры</h1>

    <p class="meta">
      <a href="/games/game.php?slug=<?php echo urlencode($original['slug']); ?>">← К игре</a>
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
        Короткое описание:
        <textarea name="short" rows="2"><?php echo htmlspecialchars($form['short']); ?></textarea>
      </label>

      <label>
        Полное описание:
        <textarea name="description" rows="10" required><?php echo htmlspecialchars($form['description']); ?></textarea>
      </label>

      <label>
        Скриншоты (по одному пути на строку):
        <textarea name="screenshots" rows="4"><?php echo htmlspecialchars($form['screenshots']); ?></textarea>
      </label>

      <hr>

      <h2>Скачать сейчас</h2>

      <label>
        Windows URL:
        <input type="text" name="win_url" value="<?php echo htmlspecialchars($form['win_url']); ?>">
      </label>

      <label>
        Windows размер:
        <input type="text" name="win_size" placeholder="45 MB" value="<?php echo htmlspecialchars($form['win_size']); ?>">
      </label>

      <label>
        Linux URL:
        <input type="text" name="lin_url" value="<?php echo htmlspecialchars($form['lin_url']); ?>">
      </label>

      <label>
        Linux размер:
        <input type="text" name="lin_size" placeholder="42 MB" value="<?php echo htmlspecialchars($form['lin_size']); ?>">
      </label>

      <hr>

      <h2>Зеркала</h2>
      <p class="meta">Формат: <code>название | url</code>, по одному на строку.</p>

      <label>
        Зеркала:
        <textarea name="mirrors" rows="4"><?php echo htmlspecialchars($form['mirrors']); ?></textarea>
      </label>

      <hr>

      <h2>Предыдущие версии</h2>
      <p class="meta">Формат: <code>версия | дата | windows_url | linux_url</code>.</p>

      <label>
        Версии:
        <textarea name="versions" rows="4"><?php echo htmlspecialchars($form['versions']); ?></textarea>
      </label>

      <hr>

      <button type="submit">Сохранить</button>
    </form>

    <hr>

    <p class="meta">
      <a href="/games/game.php?slug=<?php echo urlencode($original['slug']); ?>">← К игре</a>
      &nbsp;·&nbsp;
      <a href="/admin.php">В админку</a>
    </p>
  </main>

</body>
</html>
