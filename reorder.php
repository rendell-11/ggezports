<?php
// Include the necessary files
include 'components/connect.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get user ID from session

if (isset($_POST['reorder'])) {
    $order_id = $_POST['order_id']; // Get the order ID from the form

    // Fetch the ordered products from the 'orders' table
    $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ?");
    $select_order->execute([$order_id, $user_id]);
    
    if ($select_order->rowCount() > 0) {
        $order = $select_order->fetch(PDO::FETCH_ASSOC);
        $total_products = $order['total_products'];
        $order_items = json_decode($order['order_items'], true); // Assuming order items are stored as JSON in `order_items` column

        // Loop through the order items and insert them back into the cart
        foreach ($order_items as $item) {
            $product_id = $item['product_id'];
            $product_name = $item['name'];
            $product_price = $item['price'];
            $product_quantity = $item['quantity'];
            $product_image = $item['image'];

            // Insert each item into the cart
            $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_cart->execute([$user_id, $product_id, $product_name, $product_price, $product_quantity, $product_image]);

            // You can also update stock if needed, for example:
            // $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
            // $update_stock->execute([$product_quantity, $product_id]);
        }

        // Redirect back to the cart page or a confirmation page
        echo "<script>alert('Items have been added to your cart!'); window.location.href = 'cart.php';</script>";
    } else {
        echo "<script>alert('Invalid order!'); window.location.href = 'orders.php';</script>";
    }
}
?>
