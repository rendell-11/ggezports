<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $order_id = $_POST['cancel_order_id'];

    // Delete the order from the orders table
    $cancel_order = $conn->prepare("DELETE FROM `orders` WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
    $cancel_order->execute([$order_id, $user_id]);

    // Redirect to refresh the page
    header('Location: orders.php');
    exit;
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
   <title>To Ship</title>

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
        

    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>

    <a href="to_receive.php" id="next-btn-link">
    <button class="nav-button" id="next-btn">
        Next <i class="fas fa-arrow-right"></i>
    </button>
</a>

</div>
<section class="To ship">
<h1 class="heading">To Ship</h1>


    <!-- <div class="box-container"> -->

<?php
if ($user_id == '') {
    echo '<p class="empty">Please login to see your orders</p>';
} else {
    // Update the SQL query to select orders with 'Payment Approved' or 'to receive' status
    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND (payment_status = 'payment approved' OR payment_status = 'to receive') ORDER BY `placed_on` DESC");
    $select_orders->execute([$user_id]);

    if ($select_orders->rowCount() > 0) {
        echo '<div class="orders-table">';
        while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
?>
    <div class="order-card">
        <div class="order-info">
            <!-- Display product image -->
            <div class="product-image">
                <img src="uploaded_img/<?= htmlspecialchars($fetch_orders['image']); ?>" alt="Product Image">
            </div>

            <!-- ORDER DETAILS -->
            <p>Placed on: <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></p>
            <p>Name: <span><?= htmlspecialchars($fetch_orders['name']); ?></span></p>
            <p>Email: <span><?= htmlspecialchars($fetch_orders['email']); ?></span></p>
            <p>Number: <span><?= htmlspecialchars($fetch_orders['number']); ?></span></p>
            <p>Address: <span><?= htmlspecialchars($fetch_orders['address']); ?></span></p>
            <p>Payment method: <span><?= htmlspecialchars($fetch_orders['method']); ?></span></p>
            <p>Your orders: <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></p>
            <p>Total price: <span>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span></p>
            
            <!-- Order Status -->
            <p>Order Status: 
    <span style="color: 
    <?php 
        if ($fetch_orders['payment_status'] == 'payment approved') { 
            echo 'green'; 
        } elseif ($fetch_orders['payment_status'] == 'to receive') { 
            echo 'green'; 
        } else { 
            echo 'black'; 
        } 
    ?>">
    <?php 
        if ($fetch_orders['payment_status'] == 'payment approved') {
            echo "Payment Approved - Your seller is preparing to ship your order";
        } elseif ($fetch_orders['payment_status'] == 'to receive') {
            echo "Your order is out for delivery";
        }
    ?>
    </span>
</p>

        </div>
    </div>
<?php
        }
        echo '</div>';
    } else {
        echo '<p class="empty">No orders to display!</p>';
    }
}
?>


    
    </section>

    </div>
    <?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>