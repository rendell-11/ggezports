<?php
include 'components/connect.php';
session_start();

if (!isset($_SESSION['stripe_order_data']) || !isset($_SESSION['user_id']) || !isset($_SESSION['order_address'])) {
    header('location:checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_data = $_SESSION['stripe_order_data'];
$cart_items = $order_data['cart_items'];
$grand_total = $order_data['grand_total']; // This will now be the discounted total if applied

// Retrieve the address details from session
$address_data = $_SESSION['order_address'];
$name = $address_data['name'];
$number = $address_data['number'];
$email = $address_data['email'];
$address = $address_data['address'];

foreach ($cart_items as $cart_item) {
    $first_image = $cart_item['image_01'] ?? '';
    $total_products = $cart_item['quantity']; // Total quantity for the specific product

    // Insert the order into the orders table
    $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, image) 
        VALUES (?, ?, ?, ?, 'credit card', ?, ?, ?, NOW(), 'Pending', ?)");
    $insert_order->execute([$user_id, $name, $number, $email, $address, $total_products, $cart_item['price'] * $cart_item['quantity'], $first_image]);

    $order_id = $conn->lastInsertId();

    // Update stock for each item in the cart
    $pid = $cart_item['pid'];
    $quantity = $cart_item['quantity'];

    $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
    $update_stock->execute([$quantity, $pid]);
}

// Clear the cart
$clear_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
$clear_cart->execute([$user_id]);

// Remove session data
unset($_SESSION['stripe_order_data'], $_SESSION['order_address'], $_SESSION['discounted_total'], $_SESSION['discount_applied']);

// Redirect to orders page
header('location:orders.php?success=true');
exit();


?>
