<?php
include 'components/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = [];

// Fetch cart items and calculate grand total
$select_cart = $conn->prepare("SELECT c.*, p.image_01, p.stock, p.price, p.category FROM `cart` c JOIN `products` p ON c.pid = p.id WHERE c.user_id = ?");
$select_cart->execute([$user_id]);

$grand_total = 0;
$total_products = 0;
$cart_items = [];

while ($cart_item = $select_cart->fetch(PDO::FETCH_ASSOC)) {
    $grand_total += $cart_item['price'] * $cart_item['quantity'];
    $total_products++;
    $cart_items[] = $cart_item;
}

// Fetch user addresses
$select_addresses = $conn->prepare("SELECT * FROM `user_addresses` WHERE user_id = ?");
$select_addresses->execute([$user_id]);
$addresses = $select_addresses->fetchAll(PDO::FETCH_ASSOC);

// Apply discount logic
if (isset($_POST['apply_discount']) && !isset($_SESSION['discount_applied'])) {
    $discount_code = $_POST['discount_code'] ?? '';
    if ($discount_code) {
        // Fetch discount details
        $select_discount = $conn->prepare("SELECT * FROM `discounts` WHERE discount_name = ? AND NOW() BETWEEN start_date AND end_date");
        $select_discount->execute([$discount_code]);

        if ($discount = $select_discount->fetch(PDO::FETCH_ASSOC)) {
            $discount_percentage = $discount['discount_percentage'];
            $category = $discount['category'];

            // Check if any item in the cart belongs to the discount category
            $discount_applied = false;
            foreach ($cart_items as $cart_item) {
                if ($cart_item['category'] == $category) {
                    $discount_applied = true;
                    break;
                }
            }

            if ($discount_applied) {
                $discounted_total = $grand_total - ($grand_total * ($discount_percentage / 100));
                $_SESSION['discounted_total'] = $discounted_total;
                $_SESSION['discount_applied'] = true;
                $message[] = "Discount applied! You saved " . $discount_percentage . "%.";
            } else {
                $message[] = "No products in the cart match the discount category.";
            }
        } else {
            $message[] = "Invalid or expired discount code.";
        }
    }
}

// Remove discount logic
if (isset($_POST['remove_discount']) && isset($_SESSION['discount_applied'])) {
    unset($_SESSION['discounted_total']);
    unset($_SESSION['discount_applied']);
    $message[] = "Discount removed.";
}

if (isset($_POST['place_order'])) {
    $selected_address_id = $_POST['address'] ?? null;
    $method = $_POST['payment_method'] ?? '';

    if ($selected_address_id) {
        $address_query = $conn->prepare("SELECT * FROM `user_addresses` WHERE id = ?");
        $address_query->execute([$selected_address_id]);
        $selected_address = $address_query->fetch(PDO::FETCH_ASSOC);

        $address = implode(', ', [
            $selected_address['province'],
            $selected_address['city'],
            $selected_address['municipality'],
            $selected_address['barangay'],
            $selected_address['country'],
            $selected_address['postal_code']
        ]);

        $_SESSION['order_address'] = [
            'name' => $selected_address['name'],
            'number' => $selected_address['number'],
            'email' => $selected_address['email'],
            'address' => $address
        ];

        // Calculate the total price including discounts
        $order_total = isset($_SESSION['discounted_total']) && $method === 'cash on delivery' ? $_SESSION['discounted_total'] : $grand_total;
        $total_quantity = 0;
        
        foreach ($cart_items as $cart_item) {
            $total_quantity += $cart_item['quantity'];
        }

        if ($order_total > 0 && $total_products > 0) {
            if ($method === 'cash on delivery') {
                try {
                    // Loop through each cart item to create an order entry for each product
                    foreach ($cart_items as $cart_item) {
                        // Insert order for each product
                        $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', ?)");
                        $insert_order->execute([
                            $user_id,
                            $selected_address['name'],
                            $selected_address['number'],
                            $selected_address['email'],
                            $method,
                            $address,
                            $cart_item['quantity'],  // Single product quantity
                            $cart_item['price'] * $cart_item['quantity'],  // Product price for the order
                            $cart_item['image_01'] ?? ''
                        ]);

                        // Update stock for each product
                        $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
                        $update_stock->execute([$cart_item['quantity'], $cart_item['pid']]);
                    }

                    // Clear the cart after placing the order
                    $clear_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
                    $clear_cart->execute([$user_id]);

                    // Reset discount session
                    unset($_SESSION['discounted_total'], $_SESSION['discount_applied']);
                    echo "<script>alert('Order placed successfully!'); window.location.href = 'orders.php';</script>";
                } catch (PDOException $e) {
                    $message[] = "Order failed: " . $e->getMessage();
                }
            } elseif ($method === 'paypal') {

                
                // Clean PayPal total and redirect
                $paypal_total = number_format($grand_total, 2, '.', ''); // Format the number without commas
                header("Location: paypal_checkout.php?" . http_build_query([
                    'total' => $paypal_total,  // Cleaned total for PayPal
                    'user_id' => $user_id,
                    'cart_items' => json_encode($cart_items)
                ]));
                exit();
                

            } elseif ($method === 'credit card') {
                // Redirect to Stripe without discount
                header("Location: stripe_checkout.php?" . http_build_query([
                    'total' => $grand_total, // Original total without discount
                    'user_id' => $user_id
                ]));
                exit();
            } else {
                $message[] = "Invalid payment method selected.";
            }
        } else {
            $message[] = "Invalid total or empty cart.";
        }
    } else {
        $message[] = "Please select an address.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://js.stripe.com/v3/"></script>

    <style>
        .manage-address-button {
            text-align: left;
            margin-top: 15px;
        }

        .manage-btn {
            display: inline-block;
            width: auto;
            padding: 8px 16px;
            font-size: 14px;
            text-align: center;
            background-color: red;
            border-color: #6c757d;
            color: #fff;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .manage-btn:hover {
            background-color: black;
            color: white;
            border-color: #545b62;
        }
    </style>

</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="checkout-orders">
    <form method="POST">
        <h3>Your Orders</h3>
        <div class="display-orders">
            <?php if ($select_cart->rowCount() > 0) {
                foreach ($cart_items as $cart_item) {
                    echo "<div class='order-item'>";
                    echo "<img src='uploaded_img/{$cart_item['image_01']}' alt='{$cart_item['name']}' class='product-image'>";
                    echo "<p>{$cart_item['name']} <span>({$cart_item['quantity']} x $" . number_format($cart_item['price'], 2) . ")</span></p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Your cart is empty!</p>";
            } ?>
            <div class="grand-total">Total: <span>$<?= number_format($grand_total, 2) ?></span></div>

          <!-- Show Discounted Price if Discount is Applied -->
<?php if (isset($_SESSION['discounted_total'])): ?>
    <div class="discounted-price">
        Discounted Price: <span>$<?= number_format($_SESSION['discounted_total'], 2) ?></span>
    </div>
    <button type="submit" name="remove_discount" class="remove-discount-btn">Remove Discount</button>
<?php endif; ?>


        </div>

        <h3>Select Shipping Address</h3>
        <div class="address-list">
            <?php if ($select_addresses->rowCount() > 0) {
                foreach ($addresses as $address) { ?>
                    <div class="address-box">
                        <input type="radio" name="address" value="<?= $address['id']; ?>" >
                        <label>
                            <a>Name: </a><strong><?= $address['name']; ?></strong><br>
                            <a><strong>Email: </strong></a><?= "{$address['email']}"; ?><br>
                            <a><strong>Phone: </strong></a><?= "{$address['number']}"; ?><br>
                            <a><strong>Address: </strong></a><?= "{$address['province']}, {$address['city']}, {$address['municipality']}"; ?><br>
                            <?= "{$address['barangay']}, {$address['country']} - {$address['postal_code']}"; ?>
                        </label>
                    </div>
                <?php }
            } else {
                echo "<p>No saved addresses. Please add one in your profile.</p>";
            } ?>
        </div>

        <div class="manage-address-button">
            <a href="address.php" class="btn btn-secondary manage-btn">Manage Addresses</a>
        </div>

        <h3>Payment Details & Discounts</h3>
        <div class="flex">
            <div class="inputBox">
                <span>Payment Method:</span>
                <select name="payment_method" class="box">
                    <option value="cash on delivery">Cash on Delivery</option>
                    <option value="credit card">Credit Card</option>
                    <option value="paypal">PayPal</option>
                </select>

                <<span>Apply Discounts</span>
<div class="discount-form">
    <input type="text" name="discount_code" placeholder="Enter Discount Code" class="box">
    <input type="submit" name="apply_discount" value="Apply" class="btn apply-btn" <?= isset($_SESSION['discount_applied']) ? 'disabled' : '' ?>>
</div>


            </div>
        </div>

        <input type="submit" value="Place Order" name="place_order" class="btn" onclick="return confirm('Are you sure you want to place this order?');">
    </form>
</section>

<script>
    // Get all address boxes
    const addressBoxes = document.querySelectorAll('.address-box');

    // Loop through each address box and add a click event listener
    addressBoxes.forEach(addressBox => {
        addressBox.addEventListener('click', function() {
            // Find the radio button inside the clicked address box
            const radioButton = addressBox.querySelector('input[type="radio"]');
            
            // Check the radio button when the address box is clicked
            if (radioButton) {
                radioButton.checked = true;
            }
        });
    });
</script>




<?php include 'components/footer.php'; ?>
</body>
</html>
