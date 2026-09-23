  <header class="head">
   <div class="head__container">
     <nav class="head__nav">
       <p style="font-size: 30px"> DAF inc. </p>
       <ul class="head__list">
         <li>
           <a class="head__list-link <?php echo $active === 'home' ? 'active' : ''; ?>" href="/index.php">Home</a>
         </li>
         <li>
           <a class="head__list-link <?php echo $active === 'projects' ? 'active' : ''; ?>" href="/projects.php">Projects</a>
         </li>
         <li>
           <a class="head__list-link <?php echo $active === 'games' ? 'active' : ''; ?>" href="/games.php">My Games</a>
         </li>
         <li>
           <a class="head__list-link <?php echo $active === 'blog' ? 'active' : ''; ?>" href="/blog/index.php">Blog</a>
         </li>
         <li>
           <a class="head__list-link <?php echo $active === 'guestbook' ? 'active' : ''; ?>" href="/guestbook.php">Guestbook</a>
         </li>
       </ul>
           <a class="head__list-link <?php echo $active === 'me' ? 'active' : ''; ?>" href="/about.php">About me</a>
     </nav>
   </div>
  </header>
