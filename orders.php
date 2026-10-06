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
   <title>Orders</title>

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

        .modal-dialog-centered {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Larger modal size */
        .modal-lg {
            max-width: 400px; /* Adjust this width as needed */
            max-height: 400px;
        }

        /* Modal title and body text */
        .modal-title {
            font-family: Arial, sans-serif;
            font-size: 3em; /* Larger font size */
            font-weight: bold;
            color: #333; /* Darker text color */
        }

        .modal-body label,
        .modal-body select,
        .modal-body input {
            font-family: Arial, sans-serif;
            font-size: 1.5em; /* Slightly larger font size */
            color: #555;
        }

        .modal-footer .btn {
            font-family: Arial, sans-serif;
            font-size: 1.1em;
            font-weight: bold;
        }

        /* Style for the 'Other Reason' input field */
        .modal-body #otherReasonField label,
        .modal-body #otherReasonField input {
            font-family: Arial, sans-serif;
            font-size: 1em;
        }

    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- Navigation Buttons -->
<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <!-- <a href=""></a> -->
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>

    <a href="to_ship.php" id="next-btn-link">
        <button class="nav-button" id="next-btn">
            Next <i class="fas fa-arrow-right"></i>
        </button>
    </a>
</div>

<section class="orders">
   <h1 class="heading">Pending Orders</h1>

   <?php
      if ($user_id == '') {
         echo '<p class="empty">Please login to see your orders</p>';
      } else {
         // Select only pending orders
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND payment_status = 'pending'");
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

         <!-- Order details -->
         <div><strong>Placed on:</strong> <span><?= htmlspecialchars($fetch_orders['placed_on']); ?></span></div>
         <div><strong>Name:</strong> <span><?= htmlspecialchars($fetch_orders['name']); ?></span></div>
         <div><strong>Email:</strong> <span><?= htmlspecialchars($fetch_orders['email']); ?></span></div>
         <div><strong>Number:</strong> <span><?= htmlspecialchars($fetch_orders['number']); ?></span></div>
         <div><strong>Address:</strong> <span><?= htmlspecialchars($fetch_orders['address']); ?></span></div>
         <div><strong>Payment method:</strong> <span><?= htmlspecialchars($fetch_orders['method']); ?></span></div>
         <div><strong>Your orders:</strong> <span><?= htmlspecialchars($fetch_orders['total_products']); ?></span></div>
         <div><strong>Total price:</strong> <span>$<?= htmlspecialchars($fetch_orders['total_price']); ?>/-</span></div>
         <div><strong>Order status:</strong> <span style="color:<?= $fetch_orders['payment_status'] == 'pending' ? 'red' : 'green'; ?>"><?= htmlspecialchars($fetch_orders['payment_status']); ?></span></div>
      </div>

    <!-- Cancel Order Button with Dropdown Options -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cancelModal-<?= $fetch_orders['id']; ?>">
                Cancel Order
            </button>
            <!-- Modal Structure -->
            <div class="modal fade" id="cancelModal-<?= $fetch_orders['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel-<?= $fetch_orders['id']; ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel-<?= $fetch_orders['id']; ?>">Reason for Cancellation</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="post">
                                <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                                <!-- Dropdown for cancellation reasons -->
                                <label for="reason-<?= $fetch_orders['id']; ?>">Select a reason:</label>
                                <select name="payment_status" id="reason-<?= $fetch_orders['id']; ?>" class="form-control" required>
                                    <option value="pendingCancellation" <?= ($fetch_orders['payment_status'] == 'pendingCancellation') ? 'selected' : '' ?>>Changed my mind</option>
                                    <option value="pendingCancellation" <?= ($fetch_orders['payment_status'] == 'pendingCancellation') ? 'selected' : '' ?>>Found a better price</option>
                                    <option value="pendingCancellation" <?= ($fetch_orders['payment_status'] == 'pendingCancellation') ? 'selected' : '' ?>>Order taking too long</option>
                                    <option value="pendingCancellation" <?= ($fetch_orders['payment_status'] == 'pendingCancellation') ? 'selected' : '' ?>>Product not needed anymore</option>
                                    <option value="pendingCancellation" <?= ($fetch_orders['payment_status'] == 'pendingCancellation') ? 'selected' : '' ?>>Other</option>
                                </select>
                                
                                <!-- Optional field if "Other" is chosen -->
                                <div class="form-group mt-3" id="otherReasonField-<?= $fetch_orders['id']; ?>" style="display: none;">
                                    <label for="other_reason">Please specify:</label>
                                    <input type="text" name="other_reason" class="form-control">
                                </div>

                                <button type="submit" name="update_payment_status" class="btn btn-danger mt-3">Confirm Cancellation</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

   </div>
   <?php
            }
            echo '</div>'; // Close orders table
         } else {
            echo '<p class="empty">No pending orders!</p>';
         }
      }
   ?>
</section>



<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>


<script>
    document.getElementById('reason-<?= $fetch_orders['id']; ?>').addEventListener('change', function() {
        const otherField = document.getElementById('otherReasonField-<?= $fetch_orders['id']; ?>');
        if (this.value === 'Other') {
            otherField.style.display = 'block';
        } else {
            otherField.style.display = 'none';
        }
    });
</script>



</body>
</html>
