<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAF inc. - Blog</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../blog.css">
    <link rel="shortcut icon" href="../../Assets/images/icon.jpg" type="image/x-icon">
</head>
<body>
  <header class="head">
    <h1 class="headline">First Post</h1>
   <div class="head__container">
     <nav class="head__nav">
       <p style="font-size: 30px"> DAF inc. </p>
       <ul class="head__list">
         <li>
           <a class="head__list-link" href="../../index.php">Home</a>
         </li>
         <li>
           <a class="head__list-link" href="/">Projects</a>
         </li>
         <li>
           <a class="head__list-link" href="/">My Games</a>
         </li>
         <li>
           <a class="head__list-link" href="../index.php">Blog</a>
         </li>
         <li>
           <a class="head__list-link" href="/">Guestbook</a>
         </li>
       </ul>
           <a class="head__list-link" href="/">About me</a>
     </nav>
   </div>
  </header>
  <main>
  <article class="post-full">
    <h1 class="post-full__title">Почему я решил сделать свой сайт</h1>
    <time class="post-full__date" datetime="2025-01-15">15 января 2025</time>
    <hr>
    <p>Первый абзац поста. Тут ты пишешь свои мысли...</p>
    <p>Второй абзац...</p>
    <p>И так далее.</p>
    <hr>
    <p><a href="../index.php">← Назад к списку постов</a></p>
  </article>
</main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</footer>
</body>
</html>
