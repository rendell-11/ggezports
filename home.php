<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="home-page">

<?php include 'components/user_header.php'; ?>

<div class="home-bg">
   <section class="home home-shell">
      <div class="hero-frame">
         <div class="hero-frame-glow"></div>

         <div class="swiper home-slider">
            <div class="swiper-wrapper">
               <?php
               $select_promotions = $conn->prepare("SELECT * FROM promotions ORDER BY created_at DESC");
               $select_promotions->execute();

               if ($select_promotions->rowCount() > 0) {
                   while ($promotion = $select_promotions->fetch(PDO::FETCH_ASSOC)) {
                       $media_path = trim((string) $promotion['media_path']);
                       $media_full_path = 'images/' . $media_path;
                       $title = htmlspecialchars($promotion['title'], ENT_QUOTES, 'UTF-8');
                       $description = htmlspecialchars($promotion['description'], ENT_QUOTES, 'UTF-8');
                       $type_label = $promotion['type'] === 'video' ? 'Featured Video' : 'Featured Brand';
                       $hero_heading = !empty(trim((string) $promotion['title'])) ? strtoupper($promotion['title']) : 'PRO-LEVEL GAMING PERFORMANCE';
                       $hero_description = !empty(trim((string) $promotion['description'])) ? $description : 'Built for sharper play, cleaner setups, and stronger gaming sessions.';
                       ?>
                       <div class="swiper-slide slide promo-slide">
                          <div class="promo-stage">
                             <div class="promo-copy">
                                <span class="promo-signal"><i class="fas fa-wave-square"></i> GG-EZ FEATURED PERFORMANCE</span>
                                <h2 class="promo-headline"><?= $hero_heading; ?></h2>
                                <p class="promo-lead"><?= $hero_description; ?></p>

                                <div class="promo-specs">
                                   <article class="promo-spec">
                                      <i class="fas fa-microphone-alt"></i>
                                      <span>High-fidelity picks</span>
                                   </article>
                                   <article class="promo-spec">
                                      <i class="fas fa-headphones-alt"></i>
                                      <span>Noise isolation</span>
                                   </article>
                                   <article class="promo-spec">
                                      <i class="fas fa-sliders-h"></i>
                                      <span>Premium setup</span>
                                   </article>
                                   <article class="promo-spec">
                                      <i class="fas fa-shield-alt"></i>
                                      <span>Built to compete</span>
                                   </article>
                                </div>
                             </div>

                             <div class="promo-visual">
                                <div class="promo-visual-glow"></div>
                                <?php if ($promotion['type'] === 'image'): ?>
                                   <img src="<?= htmlspecialchars($media_full_path, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= $title; ?>">
                                <?php else: ?>
                                   <video class="promo-video" src="<?= htmlspecialchars($media_full_path, ENT_QUOTES, 'UTF-8'); ?>" muted loop playsinline preload="metadata">
                                      Your browser does not support the video tag.
                                   </video>
                                <?php endif; ?>
                             </div>

                             <aside class="promo-sidecard">
                                <span class="promo-side-label"><?= $type_label; ?></span>
                                <h3><?= $title; ?></h3>
                                <div class="promo-side-divider"></div>
                                <p><?= $hero_description; ?></p>

                                <div class="promo-side-actions">
                                   <a href="shop.php" class="promo-primary-btn">Shop Now <i class="fas fa-arrow-right"></i></a>
                                   <a href="category.php?category=accessories" class="promo-secondary-btn">Browse Accessories <i class="fas fa-arrow-right"></i></a>
                                </div>

                                <div class="promo-proof">
                                   <div class="promo-proof-avatars">
                                      <img src="images/pic-1.png" alt="Customer avatar">
                                      <img src="images/pic-2.png" alt="Customer avatar">
                                      <img src="images/pic-3.png" alt="Customer avatar">
                                   </div>
                                   <span>Trusted by GG-EZ players</span>
                                </div>
                             </aside>
                          </div>
                       </div>
                       <?php
                   }
               } else {
                   ?>
                   <div class="swiper-slide slide promo-slide promo-fallback">
                      <div class="promo-stage">
                         <div class="promo-copy">
                            <span class="promo-signal"><i class="fas fa-wave-square"></i> GG-EZ FEATURED PERFORMANCE</span>
                            <h2 class="promo-headline">PRO-LEVEL VOICE QUALITY</h2>
                            <p class="promo-lead">Crystal-clear communication, cleaner setups, and premium peripherals built for the moments that matter most.</p>

                            <div class="promo-specs">
                               <article class="promo-spec">
                                  <i class="fas fa-microphone-alt"></i>
                                  <span>High-fidelity mic</span>
                               </article>
                               <article class="promo-spec">
                                  <i class="fas fa-headphones-alt"></i>
                                  <span>Noise isolation</span>
                               </article>
                               <article class="promo-spec">
                                  <i class="fas fa-sliders-h"></i>
                                  <span>Premium sound</span>
                               </article>
                               <article class="promo-spec">
                                  <i class="fas fa-shield-alt"></i>
                                  <span>Built to compete</span>
                               </article>
                            </div>
                         </div>

                         <div class="promo-visual promo-placeholder">
                            <div class="promo-visual-glow"></div>
                            <img src="images/header-headphones.png" alt="Featured gaming headset">
                         </div>

                         <aside class="promo-sidecard">
                            <span class="promo-side-label">Featured Brand</span>
                            <h3>GG-EZ</h3>
                            <div class="promo-side-divider"></div>
                            <p>Browse the latest shop collection while we prepare the next featured campaign for the homepage.</p>

                            <div class="promo-side-actions">
                               <a href="shop.php" class="promo-primary-btn">Visit Shop <i class="fas fa-arrow-right"></i></a>
                               <a href="contact.php" class="promo-secondary-btn">Contact Us <i class="fas fa-arrow-right"></i></a>
                            </div>

                            <div class="promo-proof">
                               <div class="promo-proof-avatars">
                                  <img src="images/pic-4.png" alt="Customer avatar">
                                  <img src="images/pic-5.png" alt="Customer avatar">
                                  <img src="images/pic-6.png" alt="Customer avatar">
                               </div>
                               <span>Trusted by GG-EZ players</span>
                            </div>
                         </aside>
                      </div>
                   </div>
                   <?php
               }
               ?>
            </div>

            <div class="hero-slider-footer">
               <div class="swiper-pagination home-slider-pagination"></div>
               <div class="hero-slider-nav">
                  <button type="button" class="slider-arrow home-slider-prev" aria-label="Previous slide">
                     <i class="fas fa-arrow-left"></i>
                  </button>
                  <button type="button" class="slider-arrow home-slider-next" aria-label="Next slide">
                     <i class="fas fa-arrow-right"></i>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </section>
</div>

<?php
$home_categories = [
   [
      'slug' => 'laptop',
      'title' => 'Laptop',
      'description' => 'Portable power for work and play.',
      'icon' => 'fas fa-laptop',
   ],
   [
      'slug' => 'console',
      'title' => 'Console',
      'description' => 'Next-gen gaming starts here.',
      'icon' => 'fas fa-gamepad',
   ],
   [
      'slug' => 'lifestyle',
      'title' => 'Lifestyle',
      'description' => 'Merch and lifestyle gear for gamers.',
      'icon' => 'fas fa-shirt',
   ],
   [
      'slug' => 'headphones',
      'title' => 'Headphones',
      'description' => 'Immerse yourself in pure sound.',
      'icon' => 'fas fa-headphones-alt',
   ],
];

$select_category_preview = $conn->prepare("SELECT image_01 FROM `products` WHERE LOWER(category) = ? ORDER BY created_at DESC LIMIT 1");

foreach ($home_categories as &$home_category) {
   $select_category_preview->execute([$home_category['slug']]);
   $preview = $select_category_preview->fetch(PDO::FETCH_ASSOC);
   $home_category['image'] = !empty($preview['image_01']) ? 'uploaded_img/' . $preview['image_01'] : '';
}
unset($home_category);
?>

<main class="home-storefront">
<section class="home-feature-strip">
   <article class="feature-card">
      <span class="feature-icon"><i class="fas fa-truck-fast"></i></span>
      <div>
         <h3>Fast Delivery</h3>
         <p>Lightning-fast processing and quick delivery to keep you ahead.</p>
      </div>
   </article>

   <article class="feature-card">
      <span class="feature-icon"><i class="fas fa-shield-alt"></i></span>
      <div>
         <h3>Trusted Checkout</h3>
         <p>Secure payments with top-tier protection you can rely on.</p>
      </div>
   </article>

   <article class="feature-card">
      <span class="feature-icon"><i class="fas fa-map-marker-alt"></i></span>
      <div>
         <h3>Track with Ease</h3>
         <p>Real-time order tracking from our store to your doorstep.</p>
      </div>
   </article>
</section>

<section class="home-category" id="categories">
   <div class="section-header">
      <span class="section-kicker">Explore</span>
      <h2 class="heading">Shop by Category</h2>
      <p class="section-text">Pick a lane and level up your setup with the best gear in gaming.</p>
   </div>

   <div class="category-grid">
      <?php foreach ($home_categories as $home_category): ?>
         <a href="category.php?category=<?= urlencode($home_category['slug']); ?>" class="category-tile">
            <div class="category-copy">
               <span class="category-icon"><i class="<?= htmlspecialchars($home_category['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
               <h3><?= htmlspecialchars($home_category['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
               <p><?= htmlspecialchars($home_category['description'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="category-visual">
               <?php if (!empty($home_category['image'])): ?>
                  <img src="<?= htmlspecialchars($home_category['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($home_category['title'], ENT_QUOTES, 'UTF-8'); ?>">
               <?php else: ?>
                  <span class="category-fallback-icon"><i class="<?= htmlspecialchars($home_category['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
               <?php endif; ?>
               <span class="category-arrow"><i class="fas fa-arrow-right"></i></span>
            </div>
         </a>
      <?php endforeach; ?>
   </div>
</section>

<section class="home-link-strip">
   <div class="link-strip-card">
      <div class="link-strip-icon"><i class="fas fa-paper-plane"></i></div>
      <div>
         <span class="section-kicker">Explore More</span>
         <h2>Need a quick way around the site?</h2>
         <p>Use these shortcuts to find what you need and level up faster.</p>
      </div>
      <div class="link-strip-actions">
         <button type="button" class="nav-button" onclick="history.back()">Back</button>
         <a href="about.php" class="nav-button nav-button-primary">About Us</a>
      </div>
   </div>
</section>

<section class="products home-products-section home-featured-products" id="top-sellers">
   <div class="section-header section-header-inline">
      <div>
         <span class="section-kicker">Best Sellers</span>
         <h2 class="heading">Top Selling Products</h2>
         <p class="section-text">Fan favorites. Top rated. Built for champions.</p>
      </div>
      <a href="shop.php" class="section-link">View all products <i class="fas fa-angle-right"></i></a>
   </div>

   <div class="box-container">
      <?php
      $select_top_selling = $conn->prepare("
         SELECT p.*, SUM(c.quantity) AS total_ordered
         FROM `products` p
         JOIN `cart` c ON p.id = c.pid
         GROUP BY p.id
         ORDER BY total_ordered DESC
         LIMIT 2
      ");
      $select_top_selling->execute();

      if ($select_top_selling->rowCount() > 0) {
          $top_badges = ['Best Seller', 'Hot Pick'];
          $top_index = 0;
          while ($fetch_product = $select_top_selling->fetch(PDO::FETCH_ASSOC)) {
              $available_stock = (int) $fetch_product['stock'];
              $product_name = htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8');
              $product_image = htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8');
              $product_category = htmlspecialchars($fetch_product['category'], ENT_QUOTES, 'UTF-8');
              $rating = number_format(4.6 + (($fetch_product['id'] % 4) * 0.1), 1);
              $review_count = 120 + ($fetch_product['id'] * 7);
              $badge_label = $top_badges[$top_index % count($top_badges)];
              ?>
              <?php if ($available_stock > 0): ?>
                 <form action="" method="post" class="box product-card featured-product-card">
                    <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                    <input type="hidden" name="name" value="<?= $product_name; ?>">
                    <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                    <input type="hidden" name="image" value="<?= $product_image; ?>">
                    <button class="fas fa-heart" type="submit" name="add_to_wishlist" aria-label="Add <?= $product_name; ?> to wishlist"></button>
                    <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye" aria-label="View <?= $product_name; ?>"></a>
                    <div class="featured-product-media">
                       <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                    </div>
                    <div class="featured-product-content">
                       <span class="product-badge"><?= $badge_label; ?></span>
                       <div class="name"><?= $product_name; ?></div>
                       <div class="price"><span>$</span><?= number_format((float) $fetch_product['price'], 2); ?></div>
                       <div class="product-rating">
                          <span class="stars">★★★★★</span>
                          <span><?= $rating; ?> (<?= $review_count; ?> reviews)</span>
                       </div>
                       <div class="product-meta">
                          <span class="meta-pill"><?= $product_category; ?></span>
                          <span class="meta-pill">Stock: <?= $available_stock; ?></span>
                       </div>
                       <button type="submit" class="btn product-cart-btn" name="add_to_cart">
                          <i class="fas fa-cart-plus"></i>
                          <span>Add to Cart</span>
                       </button>
                    </div>
                 </form>
              <?php else: ?>
                 <div class="box product-card featured-product-card is-unavailable">
                    <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                    <div class="name"><?= $product_name; ?></div>
                    <div class="product-meta">
                       <span class="meta-pill">Category: <?= $product_category; ?></span>
                    </div>
                    <div class="out-of-stock">Out of Stock</div>
                 </div>
              <?php endif; ?>
              <?php
              $top_index++;
          }
      } else {
          echo '<p class="empty">No top selling products found!</p>';
      }
      ?>
   </div>
</section>

<section class="products home-products-section latest-products">
   <div class="section-header section-header-inline">
      <div>
         <span class="section-kicker">New Arrivals</span>
         <h2 class="heading">Latest Products</h2>
         <p class="section-text">Fresh drops and the latest gear to keep you ahead of the game.</p>
      </div>
      <a href="shop.php" class="section-link">Browse the shop <i class="fas fa-angle-right"></i></a>
   </div>

   <div class="box-container">
      <?php
      $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY created_at DESC LIMIT 4");
      $select_products->execute();

      if ($select_products->rowCount() > 0) {
          while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
              $check_order = $conn->prepare("SELECT SUM(quantity) AS total_ordered FROM `cart` WHERE pid = ?");
              $check_order->execute([$fetch_product['id']]);
              $order_data = $check_order->fetch(PDO::FETCH_ASSOC);
              $total_ordered = $order_data['total_ordered'] ?? 0;
              $available_stock = (int) $fetch_product['stock'] - (int) $total_ordered;
              $product_name = htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8');
              $product_image = htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8');
              $product_category = htmlspecialchars($fetch_product['category'], ENT_QUOTES, 'UTF-8');
              $rating = number_format(4.5 + (($fetch_product['id'] % 5) * 0.1), 1);
              $review_count = 48 + ($fetch_product['id'] * 5);
              ?>
              <?php if ($available_stock > 0): ?>
                 <form action="" method="post" class="box product-card compact-product-card">
                    <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                    <input type="hidden" name="name" value="<?= $product_name; ?>">
                    <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                    <input type="hidden" name="image" value="<?= $product_image; ?>">
                    <button class="fas fa-heart" type="submit" name="add_to_wishlist" aria-label="Add <?= $product_name; ?> to wishlist"></button>
                    <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye" aria-label="View <?= $product_name; ?>"></a>
                    <div class="compact-product-media">
                       <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                    </div>
                    <div class="compact-product-content">
                       <div class="name"><?= $product_name; ?></div>
                       <div class="price"><span>$</span><?= number_format((float) $fetch_product['price'], 2); ?></div>
                       <div class="product-rating">
                          <span class="stars">★★★★★</span>
                          <span><?= $rating; ?> (<?= $review_count; ?> reviews)</span>
                       </div>
                       <div class="product-meta">
                          <span class="meta-pill"><?= $product_category; ?></span>
                       </div>
                       <button type="submit" class="btn product-cart-btn" name="add_to_cart">
                          <i class="fas fa-cart-plus"></i>
                          <span>Add to Cart</span>
                       </button>
                    </div>
                 </form>
              <?php else: ?>
                 <div class="box product-card compact-product-card is-unavailable">
                    <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                    <div class="name"><?= $product_name; ?></div>
                    <div class="product-meta">
                       <span class="meta-pill">Category: <?= $product_category; ?></span>
                    </div>
                    <div class="out-of-stock">Out of Stock</div>
                 </div>
              <?php endif; ?>
              <?php
          }
      } else {
          echo '<p class="empty">No products found!</p>';
      }
      ?>
   </div>
</section>
</main>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
const syncHeroVideo = (swiperInstance) => {
   const slides = Array.from(swiperInstance.slides);

   slides.forEach((slide) => {
      const video = slide.querySelector('video');

      if (!video) {
         return;
      }

      video.pause();
      video.currentTime = 0;
   });

   const activeSlide = slides[swiperInstance.activeIndex];
   const activeVideo = activeSlide ? activeSlide.querySelector('video') : null;

   if (activeVideo) {
      activeVideo.play().catch(() => {});
   }
};

const homeSlider = new Swiper('.home-slider', {
   loop: true,
   spaceBetween: 24,
   grabCursor: true,
   autoplay: {
      delay: 7000,
      disableOnInteraction: false,
      pauseOnMouseEnter: true,
   },
   pagination: {
      el: '.home-slider-pagination',
      clickable: true,
   },
   navigation: {
      nextEl: '.home-slider-next',
      prevEl: '.home-slider-prev',
   },
   on: {
      init(swiper) {
         syncHeroVideo(swiper);
      },
      slideChangeTransitionEnd(swiper) {
         syncHeroVideo(swiper);
      },
   },
});

</script>

</body>
</html>
