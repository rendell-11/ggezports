<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>About</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="about-page">

<?php include 'components/user_header.php'; ?>

<main class="about-storefront">
<section class="about-hero">
   <div class="about-hero-grid">
      <div class="about-copy">
         <span class="about-kicker">About GG-EZ Esports</span>
         <h1>Built for players who want cleaner gear, smoother performance, and a store that feels easy to use.</h1>
         <p>GG-EZ Esports Peripherals focuses on practical, high-quality gaming equipment that helps every player build a better setup without the clutter. We care about comfort, performance, and a shopping experience that stays simple from browsing to checkout.</p>
         <div class="about-hero-actions">
            <a href="shop.php" class="btn"><i class="fas fa-bag-shopping"></i><span>Shop Products</span></a>
            <a href="contact.php" class="about-link-btn"><i class="fas fa-headset"></i><span>Contact Us</span></a>
         </div>
      </div>

      <div class="about-hero-card">
         <div class="brand-spotlight">
            <div class="brand-logo-wrap">
               <img src="images/logo.png" alt="GG-EZ Esports logo">
            </div>
            <div class="brand-copy">
               <span class="about-kicker">Good Game, Easy Wins</span>
               <p>Our goal is to help gamers find reliable peripherals and accessories that feel good, look clean, and perform when it matters most.</p>
            </div>
         </div>
         <div class="about-stat-grid">
            <article class="about-stat">
               <strong>Quality-first</strong>
               <span>Curated gear built for everyday play.</span>
            </article>
            <article class="about-stat">
               <strong>Player-focused</strong>
               <span>Comfort and usability stay at the center.</span>
            </article>
            <article class="about-stat">
               <strong>Easy shopping</strong>
               <span>Cleaner browsing, checkout, and order tracking.</span>
            </article>
         </div>
      </div>
   </div>
 </section>

<section class="about-highlights">
   <div class="about-section-head">
      <span class="about-kicker">Why Players Choose Us</span>
      <h2 class="heading">What We Focus On</h2>
      <p>We keep the brand promise simple: useful gaming gear, thoughtful design, and a store experience that stays organized and easy to explore.</p>
   </div>

   <div class="about-highlight-grid">
      <article class="about-highlight-card">
         <span class="about-icon"><i class="fas fa-mouse"></i></span>
         <h3>Purposeful products</h3>
         <p>We aim to offer peripherals and accessories that support both competitive sessions and everyday setup upgrades.</p>
      </article>

      <article class="about-highlight-card">
         <span class="about-icon"><i class="fas fa-bolt"></i></span>
         <h3>Reliable performance</h3>
         <p>From response time to ergonomics, we value gear that helps players stay focused and comfortable for longer.</p>
      </article>

      <article class="about-highlight-card">
         <span class="about-icon"><i class="fas fa-users"></i></span>
         <h3>Community mindset</h3>
         <p>We want the store to feel approachable for new players while still serving serious gamers looking for dependable picks.</p>
      </article>
   </div>
</section>

<section class="about-story">
   <div class="about-story-grid">
      <div class="about-story-panel">
         <span class="about-kicker">Our Mission</span>
         <h2>Why choose us?</h2>
         <p>At GG-EZ Esports Peripherals, our mission is to empower gamers of all skill levels by providing high-quality, innovative, and affordable gaming accessories. We are committed to enhancing the gaming experience, fostering community, and helping players reach their full potential.</p>
         <p>We continue pushing toward better technology, better design, and a more polished online shopping experience so every visit to the store feels organized, clear, and worth coming back to.</p>
      </div>

      <div class="about-story-panel is-accent">
         <span class="about-kicker">What You Can Expect</span>
         <ul class="about-checklist">
            <li><i class="fas fa-check-circle"></i> Curated gaming products for practical setup upgrades</li>
            <li><i class="fas fa-check-circle"></i> Cleaner navigation across shopping, profile, and order pages</li>
            <li><i class="fas fa-check-circle"></i> A growing brand centered on accessibility, comfort, and performance</li>
         </ul>
         <a href="contact.php" class="btn"><i class="fas fa-comment-dots"></i><span>Talk to Us</span></a>
      </div>
   </div>
</section>

<section class="reviews about-reviews">
   <div class="about-section-head">
      <span class="about-kicker">Customer Feedback</span>
      <h2 class="heading">What Players Say</h2>
      <p>Real impressions from customers who have used GG-EZ peripherals and accessories in their own setups.</p>
   </div>

   <div class="swiper reviews-slider">
      <div class="swiper-wrapper">
         <div class="swiper-slide slide">
            <img src="images/pic-1.png" alt="James">
            <p>"The mouse I got from GG-EZ Esports is a game-changer. The sleek design caught my eye, and the performance sealed the deal."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>James</h3>
         </div>

         <div class="swiper-slide slide">
            <img src="images/pic-2.png" alt="Panotes">
            <p>"GG-EZ Esports nailed it with their peripherals. I got their mouse and headset, and the quality and comfort improved my overall gaming experience."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Panotes</h3>
         </div>

         <div class="swiper-slide slide">
            <img src="images/pic-3.png" alt="Jose Kervin">
            <p>"GG-EZ Esports' commitment to ergonomic design is easy to notice. My new keyboard feels great to use every day."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Jose Kervin</h3>
         </div>

         <div class="swiper-slide slide">
            <img src="images/pic-4.png" alt="Angeles">
            <p>"From competitive multiplayer to rhythm games, I enjoy having peripherals that keep up. GG-EZ Esports delivered quality gear that feels dependable."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Angeles</h3>
         </div>

         <div class="swiper-slide slide">
            <img src="images/pic-5.png" alt="Cedrix">
            <p>"GG-EZ Esports really knows how to combine tech and design. My new keyboard looks clean and feels excellent to type on."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Cedrix</h3>
         </div>

         <div class="swiper-slide slide">
            <img src="images/pic-6.png" alt="Verdadero">
            <p>"GG-EZ Esports peripherals are worth checking out for serious gamers. The products feel responsive, and the improved website experience is a big plus."</p>
            <div class="stars">
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star"></i>
               <i class="fas fa-star-half-alt"></i>
            </div>
            <h3>Verdadero</h3>
         </div>
      </div>

      <div class="swiper-pagination about-reviews-pagination"></div>
   </div>
</section>

<section class="about-location">
   <div class="about-section-head">
      <span class="about-kicker">Visit Us</span>
      <h2 class="heading">Our Location</h2>
      <p>Find the store faster with the map below, or reach out directly if you need help before visiting.</p>
   </div>

   <div class="about-location-card">
      <div class="location-copy">
         <h3>GG-EZ Esports Store</h3>
         <p>Whether you are browsing for your next setup upgrade or checking in about an order, we want the trip from online browsing to real-world support to feel just as smooth.</p>
         <div class="location-actions">
            <a href="contact.php" class="btn"><i class="fas fa-life-ring"></i><span>Contact Support</span></a>
            <a href="shop.php" class="about-link-btn"><i class="fas fa-arrow-right"></i><span>Browse Shop</span></a>
         </div>
      </div>

      <div class="map-shell">
         <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1BsdbZW7KBz__yD45Rmm7K0aF9hUXImI&ehbc=2E312F" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="GG-EZ Esports location map"></iframe>
      </div>
   </div>
</section>

<section class="about-links">
   <div class="about-links-card">
      <div>
         <span class="about-kicker">Keep Exploring</span>
         <h2>Ready for the next stop?</h2>
         <p>Jump back, continue shopping, or move ahead to your order history in one click.</p>
      </div>
      <div class="about-links-actions">
         <button type="button" class="nav-button" onclick="history.back()">Back</button>
         <a href="shop.php" class="nav-button">Shop</a>
         <a href="order_history.php" class="nav-button nav-button-primary">Order History</a>
      </div>
   </div>
</section>
</main>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
const aboutReviewsSlider = new Swiper('.reviews-slider', {
   loop: true,
   spaceBetween: 20,
   grabCursor: true,
   pagination: {
      el: '.about-reviews-pagination',
      clickable: true,
   },
   breakpoints: {
      0: {
         slidesPerView: 1,
      },
      768: {
         slidesPerView: 2,
      },
      991: {
         slidesPerView: 3,
      },
   },
});
</script>

</body>
</html>
