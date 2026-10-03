<?php
// RSS — отдаём XML, никакого HTML
header('Content-Type: application/rss+xml; charset=utf-8');

$siteUrl = 'https://daf.dev'; // ← ЗАМЕНИ на свой будущий домен или оставь localhost

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/posts.txt';
$posts = [];

if (is_file($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $entry = json_decode($line, true);
        if (is_array($entry)) {
            $posts[] = $entry;
        }
    }
    $posts = array_reverse($posts);
    $posts = array_slice($posts, 0, 20); // только последние 20
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
  <channel>
    <title>DAF inc. — Blog</title>
    <link><?php echo htmlspecialchars($siteUrl); ?>/blog/</link>
    <description>Личный блог разработчика. Мысли, игры, код.</description>
    <language>ru</language>

    <?php foreach ($posts as $post): ?>
      <item>
        <title><?php echo htmlspecialchars($post['title']); ?></title>
        <link><?php echo htmlspecialchars($siteUrl); ?>/blog/post.php?slug=<?php echo urlencode($post['slug']); ?></link>
        <guid isPermaLink="false"><?php echo htmlspecialchars($post['slug']); ?></guid>
        <pubDate><?php echo date(DATE_RSS, strtotime($post['date'])); ?></pubDate>
        <description><?php echo htmlspecialchars($post['description']); ?></description>
      </item>
    <?php endforeach; ?>
  </channel>
</rss>
