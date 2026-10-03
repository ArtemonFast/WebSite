<?php
session_start();

$title = 'Versions';
$description = 'Предыдущие версии игры.';
$styles = [];
$active = 'games';

$slug = $_GET['slug'] ?? '';

if ($slug === '') {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/games.txt';
$game = null;

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry) && ($entry['slug'] ?? '') === $slug) {
            $game = $entry;
            break;
        }
    }
}

if ($game === null) {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/includes/404.php';
    exit;
}

$versions = $game['versions'] ?? [];
$title = 'Index of /' . $game['slug'] . '/versions/';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="shortcut icon" href="/Assets/images/icon.jpg">
  <style>
    * { box-sizing: border-box; }
    body {
      background-color: #ffffff;
      color: #000000;
      font-family: monospace;
      font-size: 14px;
      margin: 0;
      padding: 20px 30px;
      line-height: 1.6;
    }
    h1 {
      font-family: monospace;
      font-size: 18px;
      font-weight: bold;
      margin: 0 0 8px;
    }
    hr {
      border: none;
      border-top: 1px solid #000000;
      margin: 12px 0;
    }
    a {
      color: #0000ee;
      text-decoration: underline;
    }
    a:visited { color: #551a8b; }
    a:hover { color: #0000ff; background-color: #ffff00; }

    .tree {
      margin: 16px 0;
      font-size: 14px;
    }
    .tree__row {
      display: flex;
      align-items: baseline;
      gap: 12px;
      padding: 3px 0;
      white-space: nowrap;
      font-family: monospace;
    }
    .tree__prefix {
      color: #808080;
      flex-shrink: 0;
    }
    .tree__dir {
      color: #000000;
      font-weight: bold;
      flex-shrink: 0;
    }
    .tree__date {
      color: #808080;
      font-size: 13px;
      flex-shrink: 0;
    }
    .tree__files {
      display: inline-flex;
      gap: 6px;
      align-items: baseline;
    }
    .tree__sep {
      color: #808080;
    }
    .tree__link {
      color: #0000ee;
      text-decoration: underline;
    }
    .tree__link:hover {
      background-color: #ffff00;
      color: #0000ff;
    }

    .back {
      margin-top: 24px;
    }
    .empty {
      color: #808080;
      font-style: italic;
      margin: 20px 0;
    }
  </style>
</head>
<body>

  <h1>Index of /<?php echo htmlspecialchars($game['slug']); ?>/versions/</h1>

  <hr>

  <div class="tree">
    <div class="tree__row">
      <a class="tree__link" href="/games/game.php?slug=<?php echo urlencode($game['slug']); ?>">
        [DIR]  Parent Directory/
      </a>
    </div>

    <?php if (!empty($versions)): ?>
      <?php foreach ($versions as $v): ?>
        <?php
          $version = htmlspecialchars($v['version'] ?? '?');
          $vdate   = htmlspecialchars($v['date'] ?? '');
          $dl      = $v['downloads'] ?? [];
          $winUrl  = $dl['windows'] ?? '';
          $linUrl  = $dl['linux'] ?? '';
        ?>
        <div class="tree__row">
          <span class="tree__prefix">[DIR]  </span>
          <span class="tree__dir">v<?php echo $version; ?>/</span>
          <span class="tree__date"><?php echo $vdate; ?></span>
          <span class="tree__files">
            <?php if ($winUrl): ?>
              <a class="tree__link" href="<?php echo htmlspecialchars($winUrl); ?>">Windows</a>
            <?php endif; ?>
            <?php if ($winUrl && $linUrl): ?>
              <span class="tree__sep">·</span>
            <?php endif; ?>
            <?php if ($linUrl): ?>
              <a class="tree__link" href="<?php echo htmlspecialchars($linUrl); ?>">Linux</a>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty">Нет предыдущих версий.</p>
    <?php endif; ?>
  </div>

  <hr>

  <div class="back">
    <a href="/games/game.php?slug=<?php echo urlencode($game['slug']); ?>">
      ← Back to <?php echo htmlspecialchars($game['title']); ?>
    </a>
  </div>

  <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
