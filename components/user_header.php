<?php
   if (session_status() === PHP_SESSION_NONE) {
      session_start();
   }
  
   include 'components/connect.php';

   $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
   $current_page = basename($_SERVER['PHP_SELF'] ?? '');
   $total_wishlist_counts = 0;
   $total_cart_counts = 0;
   $fetch_profile = null;

   if ($user_id) {
      $count_wishlist_items = $conn->prepare("SELECT COUNT(*) FROM `wishlist` WHERE user_id = ?");
      $count_wishlist_items->execute([$user_id]);
      $total_wishlist_counts = (int) $count_wishlist_items->fetchColumn();

      $count_cart_items = $conn->prepare("SELECT COUNT(*) FROM `cart` WHERE user_id = ?");
      $count_cart_items->execute([$user_id]);
      $total_cart_counts = (int) $count_cart_items->fetchColumn();

      $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
      $select_profile->execute([$user_id]);
      $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC) ?: null;
   }

   if (isset($message)) {
      foreach ($message as $msg) {
         echo '
         <div class="message">
            <span>'.$msg.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">

      <a href="home.php" class="logo" aria-label="GG-EZ Esports home">
         <img src="images/new.png" alt="GG-EZ Esports" class="logo-image">
      </a>

      <nav class="navbar">
         <a href="home.php" class="<?= $current_page === 'home.php' ? 'active' : ''; ?>">Home</a>
         <a href="about.php" class="<?= $current_page === 'about.php' ? 'active' : ''; ?>">About</a>
         <a href="shop.php" class="<?= $current_page === 'shop.php' ? 'active' : ''; ?>">Shop</a>
         <a href="contact.php" class="<?= $current_page === 'contact.php' ? 'active' : ''; ?>">Contact</a>
      </nav>

      <div class="icons">
         <button id="menu-btn" class="icon-btn menu-btn fas fa-bars" type="button" aria-label="Open menu"></button>
         <a href="search_page.php" class="icon-btn" aria-label="Search">
            <i class="fas fa-search"></i>
         </a>
         <a href="wishlist.php" class="icon-btn has-badge" aria-label="Wishlist">
            <i class="fas fa-heart"></i>
            <span class="icon-count"><?= $total_wishlist_counts; ?></span>
         </a>
         <a href="cart.php" class="icon-btn has-badge" aria-label="Cart">
            <i class="fas fa-shopping-cart"></i>
            <span class="icon-count"><?= $total_cart_counts; ?></span>
         </a>
         <button id="user-btn" class="icon-btn profile-toggle" type="button" aria-label="Open account menu">
            <i class="fas fa-user"></i>
         </button>
      </div>

      <div class="profile">
         <?php if ($fetch_profile): ?>
            <div class="profile-card">
               <span class="profile-label">Signed in as</span>
               <p><?= htmlspecialchars($fetch_profile["name"], ENT_QUOTES, 'UTF-8'); ?></p>
               <div class="profile-links">
                  <a href="profile.php" class="btn">View Profile</a>
                  <a href="update_user.php" class="option-btn">Update Profile</a>
               </div>
               <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('Logout from the website?');">Logout</a>
            </div>
         <?php else: ?>
            <div class="profile-card">
               <span class="profile-label">Welcome</span>
               <p>Please login or register to start shopping.</p>
               <div class="flex-btn">
                  <a href="user_login.php" class="btn">Login</a>
                  <a href="user_register.php" class="option-btn">Register</a>
               </div>
            </div>
         <?php endif; ?>
      </div>

   </section>

</header>
