<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
};

include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}

// Handle adding to cart
if(isset($_POST['add_to_cart'])){
   $pid = $_POST['pid'];
   $wishlist_id = $_POST['wishlist_id'];
   $name = $_POST['name'];
   $price = $_POST['price'];
   $image = $_POST['image'];

   // Check stock availability from products table
   $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
   $check_stock->execute([$pid]);
   $fetch_stock = $check_stock->fetch(PDO::FETCH_ASSOC);
   $qty = 1; // Set default quantity to 1

   if($qty > $fetch_stock['stock']){
      echo "<script>alert('Not enough stock available');</script>";
   } else {
      // Add to cart if stock is available
      $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND pid = ?");
      $check_cart->execute([$user_id, $pid]);

      if($check_cart->rowCount() > 0){
         // Update the quantity if the product already exists in the cart
         $update_cart = $conn->prepare("UPDATE `cart` SET quantity = quantity + ? WHERE user_id = ? AND pid = ?");
         $update_cart->execute([$qty, $user_id, $pid]);
      } else {
         // Insert new product into the cart
         $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
      }

      // Remove from wishlist after adding to cart
      $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
      $delete_wishlist_item->execute([$wishlist_id]);

      echo "<script>alert('Item added to cart');</script>";
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<!-- navigation_buttons.php -->
<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>

</div>


<section class="products">

   <h3 class="heading">Wishlist</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT w.*, p.stock, p.category FROM `wishlist` w JOIN `products` p ON w.pid = p.id WHERE w.user_id = ?");
      $select_wishlist->execute([$user_id]);
      
      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
            $grand_total += $fetch_wishlist['price'];  
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
      <input type="hidden" name="name" value="<?= $fetch_wishlist['name']; ?>">
      <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="image" value="<?= $fetch_wishlist['image']; ?>">
      <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="">
      <div class="name"><?= $fetch_wishlist['name']; ?></div>
      <div class="flex">
         <div class="price">$<?= number_format($fetch_wishlist['price'], 2); ?>/-</div>
      </div>
      <!-- Column for Stock and Category -->
      <div class="stock-category">
         <div class="stock" style="font-size: 18px; color: white;">Stock: <?= $fetch_wishlist['stock']; ?></div>
         <div class="category" style="font-size: 18px; color: white;">Category: <?= $fetch_wishlist['category']; ?></div>
      </div>
      <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
      <input type="submit" value="Delete Item" onclick="return confirm('Delete this from wishlist?');" class="delete-btn" name="delete">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">Your wishlist is empty</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>Total : <span>$<?= number_format($grand_total, 2); ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('Delete all from wishlist?');">Delete All Items</a>
   </div>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
