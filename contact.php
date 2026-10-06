<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
}

$subject_options = [
   'General Inquiry',
   'Product Support',
   'Order Help',
   'Visit the Store',
   'Account Assistance',
];

$support_channels = [
   [
      'icon' => 'fas fa-headset',
      'title' => 'Customer Support',
      'copy' => 'Get fast answers to product questions, account issues, and technical support.',
   ],
   [
      'icon' => 'fas fa-box-open',
      'title' => 'Order Help',
      'copy' => 'Need help with an order, shipping, or returns? We can help you track and resolve it.',
   ],
   [
      'icon' => 'fas fa-store',
      'title' => 'Visit the Store',
      'copy' => 'Have a question in person? Stop by our store or reach out for general inquiries.',
   ],
];

$contact_tips = [
   'Search our FAQ for quick answers to common questions.',
   'Include your order number if your message is about an order.',
   'Use the most relevant subject so we can route your message faster.',
   'We typically respond within 24 hours during business days.',
];

$explore_links = [
   ['label' => 'Orders', 'href' => 'orders.php', 'icon' => 'fas fa-box'],
   ['label' => 'Order History', 'href' => 'order_history.php', 'icon' => 'fas fa-clock-rotate-left'],
   ['label' => 'Profile', 'href' => 'profile.php', 'icon' => 'fas fa-user'],
   ['label' => 'FAQ', 'href' => 'contact.php', 'icon' => 'fas fa-circle-question'],
   ['label' => 'Support Center', 'href' => 'shop.php', 'icon' => 'fas fa-headset'],
];

$contact_subject = 'General Inquiry';

if (isset($_POST['send'])) {
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $contact_subject = isset($_POST['subject']) ? filter_var($_POST['subject'], FILTER_SANITIZE_STRING) : 'General Inquiry';
   if (!in_array($contact_subject, $subject_options, true)) {
      $contact_subject = 'General Inquiry';
   }
   $msg = $_POST['msg'];
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);
   $formatted_message = '[' . $contact_subject . '] ' . $msg;

   $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $formatted_message]);

   if ($select_message->rowCount() > 0) {
      $message[] = 'already sent message!';
   } else {
      $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $formatted_message]);

      $message[] = 'sent message successfully!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body class="contact-page">

<?php include 'components/user_header.php'; ?>

<main class="contact-shell">
   <section class="contact-hero">
      <div class="contact-hero-shell">
         <div class="contact-hero-copy">
            <span class="contact-kicker"><i class="fas fa-comment-dots"></i> We&apos;re Here to Help</span>
            <h1>Reach out for product questions, order support, or help choosing the right gear.</h1>
            <p>Our team is here to help with product support, order inquiries, and expert recommendations. Fill out the form or use the options below to get the assistance you need.</p>
            <div class="contact-hero-badges">
               <span class="contact-badge"><i class="fas fa-headset"></i> Product Support</span>
               <span class="contact-badge"><i class="fas fa-box-open"></i> Order Help</span>
               <span class="contact-badge"><i class="fas fa-store"></i> Visit Store</span>
            </div>
         </div>
         <div class="contact-hero-visual" aria-hidden="true">
            <div class="contact-hero-glow"></div>
            <img src="images/header-headphones.png" alt="">
         </div>
      </div>
   </section>

   <section class="contact-dashboard">
      <div class="contact-main-grid">
         <aside class="contact-support-column">
            <div class="contact-support-card">
               <?php foreach ($support_channels as $channel): ?>
                  <article class="contact-info-card">
                     <span class="contact-icon"><i class="<?= htmlspecialchars($channel['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span>
                     <div class="contact-info-copy">
                        <h3><?= htmlspecialchars($channel['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?= htmlspecialchars($channel['copy'], ENT_QUOTES, 'UTF-8'); ?></p>
                     </div>
                     <span class="contact-info-arrow"><i class="fas fa-angle-right"></i></span>
                  </article>
               <?php endforeach; ?>
            </div>

            <div class="contact-side-card">
               <span class="contact-kicker">Before You Send a Message</span>
               <ul class="contact-checklist">
                  <?php foreach ($contact_tips as $tip): ?>
                     <li><i class="fas fa-check-circle"></i><?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8'); ?></li>
                  <?php endforeach; ?>
               </ul>
            </div>
         </aside>

         <div class="contact-form-panel">
            <form action="" method="post" class="contact-form-card">
               <div class="contact-section-head">
                  <span class="contact-kicker"><i class="fas fa-envelope-open-text"></i> Send Us a Message</span>
                  <h2>Contact Us</h2>
                  <p>Fill out the form below and our team will get back to you as soon as possible.</p>
               </div>

               <div class="contact-field-grid">
                  <div class="contact-field">
                     <label for="contact_name">Name</label>
                     <input id="contact_name" type="text" name="name" placeholder="Enter your name" required maxlength="20" class="box" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                  </div>

                  <div class="contact-field">
                     <label for="contact_email">Email</label>
                     <input id="contact_email" type="email" name="email" placeholder="Enter your email" required maxlength="50" class="box" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                  </div>
               </div>

               <div class="contact-field">
                  <label for="contact_number">Phone Number</label>
                  <input id="contact_number" type="tel" name="number" placeholder="Enter your phone number" required maxlength="12" inputmode="numeric" class="box" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)" value="<?= isset($_POST['number']) ? htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8') : ''; ?>">
               </div>

               <div class="contact-field">
                  <label for="contact_subject">Subject</label>
                  <select id="contact_subject" name="subject" class="box">
                     <?php foreach ($subject_options as $subject_option): ?>
                        <option value="<?= htmlspecialchars($subject_option, ENT_QUOTES, 'UTF-8'); ?>" <?= $contact_subject === $subject_option ? 'selected' : ''; ?>>
                           <?= htmlspecialchars($subject_option, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>

               <div class="contact-field">
                  <label for="contact_message">Message</label>
                  <textarea id="contact_message" name="msg" class="box" placeholder="Type your message here..." cols="30" rows="10" required maxlength="430"><?= isset($_POST['msg']) ? htmlspecialchars($_POST['msg'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
               </div>

               <button type="submit" name="send" class="btn contact-submit-btn">
                  <i class="fas fa-paper-plane"></i>
                  <span>Send Message</span>
               </button>
            </form>

            <div class="contact-explore-card">
               <div class="contact-explore-copy">
                  <span class="contact-kicker">Explore More</span>
                  <h3>Explore more store pages</h3>
               </div>
               <div class="contact-side-actions">
                  <?php foreach ($explore_links as $link): ?>
                     <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="contact-link-btn">
                        <i class="<?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        <span><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                     </a>
                  <?php endforeach; ?>
               </div>
            </div>
         </div>
      </div>
   </section>

   <section class="contact-location">
      <div class="contact-location-card">
         <div class="location-copy">
            <span class="contact-kicker"><i class="fas fa-map-marker-alt"></i> Our Location</span>
            <h3>GG-EZ Esports Store</h3>
            <ul class="contact-location-list">
               <li><i class="fas fa-location-dot"></i><span>123 Gamer Way, Esports City, NY 10001</span></li>
               <li><i class="fas fa-phone"></i><span>+1 (555) 123-4567</span></li>
               <li><i class="fas fa-envelope"></i><span>support@ggezports.com</span></li>
               <li><i class="fas fa-clock"></i><span>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM</span></li>
            </ul>
            <div class="location-actions">
               <a href="https://www.google.com/maps/search/123+Gamer+Way+Esports+City+NY+10001" target="_blank" rel="noopener noreferrer" class="btn">
                  <i class="fas fa-location-arrow"></i>
                  <span>Get Directions</span>
               </a>
               <a href="about.php" class="contact-link-btn">Learn More</a>
            </div>
         </div>

         <div class="map-shell">
            <iframe src="https://www.google.com/maps/d/u/0/embed?mid=1BsdbZW7KBz__yD45Rmm7K0aF9hUXImI&ehbc=2E312F" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="GG-EZ Esports location map"></iframe>
         </div>
      </div>
   </section>
</main>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
