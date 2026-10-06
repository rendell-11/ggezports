<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Handle order removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    if (!empty($user_id)) {
        // Delete all canceled orders for the user
        $delete_all_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ? AND (payment_status = 'canceled' OR payment_status = 'pendingCancellation')");
        if ($delete_all_orders->execute([$user_id])) {
            header('Location: cancel_order.php'); // Refresh the page
            exit;
        } else {
            echo "<p class='error'>Failed to delete all orders. Please try again.</p>";
        }
    } else {
        echo "<p class='error'>Invalid user ID. Please log in.</p>";
    }
}



// For cancellation
if (isset($_POST['update_payment_status'])) {
    $order_id = $_POST['order_id'];
    $new_payment_status = $_POST['payment_status'];

    // Update the payment status in the orders table
    $update_payment_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
    $update_payment_status->execute([$new_payment_status, $order_id]);

    header('location:orders.php'); // Refresh the page after updating
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cancelled</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
        .btn-status {
            background-color: red;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            font-size: 1.2em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        .btn-status:hover {
            background-color: #8B0000;
        }

        .orders-table {
            display: grid;
            gap: 20px;
        }

        .order-card {
            background-color: #333333;
            color: #ffffff;
            border: 1px solid #444444;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.4);
            font-size: 1.2em;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 100%;
            font-size: 1.3em;
        }

        .product-image img {
            width: 100px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .status-text {
            color: #dddddd;
            font-size: 1.1em;
        }

        .btn-remove {
        background-color: red;
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        font-size: 13px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 15px;
    }

    .btn-remove:hover {
        background-color: #e5533d;
    }

    .btn-remove-all {
    background-color: #ff0000;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-remove-all:hover {
    background-color: #cc0000;
}


    </style>
</head>
<body>


<?php include 'components/user_header.php'; ?>

<!-- navigation_buttons.php -->
<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>

    <a href="shop.php" id="next-btn-link">
    <button class="nav-button" id="next-btn">
        Next <i class="fas fa-arrow-right"></i>
    </button>
</a>
</div>


<section class="orders">
    <h1 class="heading">
        Cancelled Orders
        <form method="POST" style="display:inline-block; float:right;" onsubmit="return confirm('Are you sure you want to delete all cancelled orders?');">
            <button type="submit" name="delete_all" class="btn-remove" style="background-color: red; padding: 8px 16px; font-size: 14px; margin-left: 10px;">
                Delete All
            </button>
        </form>
    </h1>

   
   <?php
      if ($user_id == '') {
         echo '<p class="empty">Please login to see your orders</p>';
      } else {
         // Cancel
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND (payment_status = 'canceled' OR payment_status = 'pendingCancellation')");
         $select_orders->execute([$user_id]);
         
         if ($select_orders->rowCount() > 0) {
            echo '<div class="orders-table">';
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
   ?>
   
    <div class="order-card">
        
      <div class="order-info">
         <!-- Display product image from the orders table -->
         <div class="product-image">
            <img src="uploaded_img/<?= htmlspecialchars($fetch_orders['image']); ?>" alt="Product Image">
         </div>
   
   <!-- ORDER DETAILS -->
      <div><strong>Placed on:</strong> <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></div>
         <div><strong>Name:</strong> <span><?= htmlspecialchars($fetch_orders['name']); ?></span></div>
         <div><strong>Email:</strong> <span><?= htmlspecialchars($fetch_orders['email']); ?></span></div>
         <div><strong>Number:</strong> <span><?= htmlspecialchars($fetch_orders['number']); ?></span></div>
         <div><strong>Address:</strong> <span><?= htmlspecialchars($fetch_orders['address']); ?></span></div>
         <div><strong>Payment method:</strong> <span><?= htmlspecialchars($fetch_orders['method']); ?></span></div>
         <div><strong>Your orders:</strong> <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></div>
         <div><strong>Total price:</strong> <span>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span></div>
         <div><strong>Order status :</strong><span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') { echo 'red'; } else { echo 'red'; }; ?>"><?= $fetch_orders['payment_status']; ?></span> Your Order has been cancelled </div>
      </div>
      
     
      <form method="POST" onsubmit="return confirm('Are you sure you want to remove this order?');">
    <input type="hidden" name="remove_order_id" value="<?= $fetch_orders['id']; ?>">
    <button type="submit" class="btn-remove">Remove</button>
</form>

      
      </div>
   <?php
            }
           
         } else {
            echo '<p class="empty">No cancelled orders!</p>';
         }
      }
   ?>
</section>


<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
