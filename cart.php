<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

// Handle cart item deletion
if (isset($_POST['delete'])) {
   $cart_id = $_POST['cart_id'];

   // Fetch the cart item
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE id = ? AND user_id = ?");
   $check_cart->execute([$cart_id, $user_id]);

   if ($check_cart->rowCount() > 0) {
       $cart_item = $check_cart->fetch(PDO::FETCH_ASSOC);
       $product_id = $cart_item['pid'];
       $quantity = $cart_item['quantity'];

       // Restore the specific stock for the product being deleted
       $restore_stock = $conn->prepare("UPDATE `products` SET stock = stock + ? WHERE id = ?");
       $restore_stock->execute([$quantity, $product_id]);

       // Now delete the item from the cart
       $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
       $delete_cart_item->execute([$cart_id]);

       $message[] = 'Item removed from cart and stock restored!';
   } else {
       $message[] = 'Item not found in cart.';
   }
}

// Handle "Remove all items" action
if (isset($_GET['delete_all'])) {
    // Delete all items from the user's cart
    $delete_all_items = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_all_items->execute([$user_id]);

    $message[] = 'All items removed from cart!';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- jQuery CDN -->
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

<section class="products shopping-cart">

   <h3 class="heading">shopping cart</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT c.*, p.stock FROM `cart` c JOIN `products` p ON c.pid = p.id WHERE c.user_id = ?");
      $select_cart->execute([$user_id]);
      if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="flex">
         <div class="price">$<?= $fetch_cart['price']; ?></div>
         <input type="number" name="qty" class="qty" data-cart-id="<?= $fetch_cart['id']; ?>" min="1" max="<?= $fetch_cart['stock']; ?>" value="<?= isset($fetch_cart['quantity']) ? $fetch_cart['quantity'] : 1; ?>">
      </div>
      <div class="sub-total"> Subtotal : <span>$<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?></span> </div>
      <input type="submit" value="remove item" onclick="return confirm('Delete This From Cart?');" class="delete-btn" name="delete">
   </form>
   <?php
   $grand_total += $sub_total;
      }
   }else{
      echo '<p class="empty">Your Cart Is Empty</p>';
   }
   ?>
   </div>

   <div class="cart-total">
      <p>Total : <span>$<?= $grand_total; ?></span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('delete all from cart?');">Remove all items</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>">Proceed To Checkout</a>
   </div>

</section>

<?php include 'components/footer.php'; ?>


<!-- FOR STOCK INCREASING AND DECREASING -->
<script>
    $(document).ready(function() {
        // Trigger AJAX when the quantity changes
        $('.qty').on('input', function() {
            var cart_id = $(this).data('cart-id'); // Get cart ID
            var new_qty = parseInt($(this).val()); // Get the new quantity
            var max_stock = parseInt($(this).attr('max')); // Get max stock for validation

            // Validate new quantity
            if (isNaN(new_qty) || new_qty < 1 || new_qty > max_stock) {
                alert('Please enter a valid quantity (1 - ' + max_stock + ')');
                $(this).val(1); // Reset to 1 if invalid
                return;
            }

            // Send the AJAX request to update the quantity
            $.ajax({
                url: 'update_quantity.php',
                type: 'POST',
                data: {
                    cart_id: cart_id,
                    qty: new_qty
                },
                success: function(response) {
                    console.log(response); // Log the server response to debug

                    // Handle error response here
                    if (response.status === 'error') {
                        alert(response.message); // Show error message from server
                    }
                    // Optionally refresh cart totals or update UI here
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText); // Log the entire response for debugging
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    });
</script>

</body>
</html>
