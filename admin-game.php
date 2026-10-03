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
    'win_url'     => '',
    'win_size'    => '',
    'lin_url'     => '',
    'lin_size'    => '',
    'mirrors'     => '',
    'versions'    => '',
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

    // Уникальность slug
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

    // ===== Парсинг и сохранение =====
    if (empty($errors)) {
        // Скриншоты
        $screenshots = [];
        if ($form['screenshots'] !== '') {
            foreach (explode("\n", $form['screenshots']) as $line) {
                $line = trim($line);
                if ($line !== '') $screenshots[] = $line;
            }
        }

        // Mirrors: "name | url"
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

        // Versions: "version | date | win_url | lin_url"
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
        // Short по умолчанию
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
            'downloads'   => [
                'windows' => ['url' => $form['win_url'], 'size' => $form['win_size']],
                'linux'   => ['url' => $form['lin_url'], 'size' => $form['lin_size']],
            ],
            'mirrors'     => $mirrors,
            'versions'    => $versions,
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

      <hr>

      <h2>Скачать сейчас</h2>

      <label>
        Windows URL (можно пусто):
        <input type="text" name="win_url" value="<?php echo htmlspecialchars($form['win_url']); ?>">
      </label>

      <label>
        Windows размер:
        <input type="text" name="win_size" placeholder="45 MB" value="<?php echo htmlspecialchars($form['win_size']); ?>">
      </label>

      <label>
        Linux URL (можно пусто):
        <input type="text" name="lin_url" value="<?php echo htmlspecialchars($form['lin_url']); ?>">
      </label>

      <label>
        Linux размер:
        <input type="text" name="lin_size" placeholder="42 MB" value="<?php echo htmlspecialchars($form['lin_size']); ?>">
      </label>

      <hr>

      <h2>Зеркала</h2>
      <p class="meta">Формат: <code>название | url</code>, по одному на строку. Можно пусто.</p>

      <label>
        Зеркала:
        <textarea name="mirrors" rows="4" placeholder="itch.io | https://example.itch.io/game&#10;GitHub | https://github.com/user/repo"><?php echo htmlspecialchars($form['mirrors']); ?></textarea>
      </label>

      <hr>

      <h2>Предыдущие версии</h2>
      <p class="meta">Формат: <code>версия | дата | windows_url | linux_url</code>, по одному на строку. Пустые URL допустимы.</p>

      <label>
        Версии:
        <textarea name="versions" rows="4" placeholder="1.0.0 | 2026-03-01 | /files/game/v1.0.0/setup.exe | /files/game/v1.0.0/game.AppImage&#10;0.9.0 | 2026-01-15 | /files/game/v0.9.0/setup.exe | "><?php echo htmlspecialchars($form['versions']); ?></textarea>
      </label>

      <hr>

      <button type="submit">Опубликовать</button>
    </form>

    <hr>

    <p class="meta">
      <a href="/admin.php">← В админку</a>
    </p>
  </main>

</body>
</html>
