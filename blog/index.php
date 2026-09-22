<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="It's my blog. I write here my thoughts and my reaction on news.">
    <title>DAF inc. - Blog</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="blog.css">
    <link rel="shortcut icon" href="../Assets/images/icon.jpg" type="image/x-icon">
</head>
<body>
  <header class="head">
    <h1 class="headline">Blog programmer</h1>
   <div class="head__container">
     <nav class="head__nav">
       <p style="font-size: 30px"> DAF inc. </p>
       <ul class="head__list">
         <li>
           <a class="head__list-link" href="../index.php">Home</a>
         </li>
         <li>
           <a class="head__list-link" href="#">Projects</a>
         </li>
         <li>
           <a class="head__list-link" href="#">My Games</a>
         </li>
         <li>
           <a class="head__list-link active" href="#">Blog</a>
         </li>
         <li>
           <a class="head__list-link" href="#">Guestbook</a>
         </li>
       </ul>
           <a class="head__list-link" href="#">About me</a>
     </nav>
   </div>
  </header>
  <main>
    <section class="posts">
      <article class="post">
        <a class="post__link" href="posts/first-post.php">
          <h2 class="post__title">Почему я решил сделать свой сайт</h2>
          <p class="post__desc">Первый пост. Рассказываю, зачем мне это, чего хочу и почему без JS.</p>
          <time class="post__date" datetime="2025-01-15">15 января 2025</time>
        </a>
    </section>
  </main>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</footer>
</body>
</html>
