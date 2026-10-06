<?php
require 'vendor/autoload.php';
include 'components/connect.php';

use PayPal\Api\{Payment, PaymentExecution};

$clientID = "AQDGKaM8vsJZ0U-FJwZI-yroBReDQuJE2AWIa2nwCyXv8Q8xbJ9lH3MNJitn66KpL2_W3blHSH3jnO0p";
$secretID = "YOUR_PAYPAL_SECRET";

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential($clientID, $secretID)
);

if (isset($_GET['paymentId']) && isset($_GET['PayerID']) && isset($_GET['user_id'])) {
    $paymentId = $_GET['paymentId'];
    $payerId = $_GET['PayerID'];
    $user_id = $_GET['user_id'];

    $payment = Payment::get($paymentId, $apiContext);

    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
        $result = $payment->execute($execution, $apiContext);

        if ($result->getState() == 'approved') {
            // Fetch cart details for the user
            $select_cart = $conn->prepare("
                SELECT c.*, p.image_01, d.discount_percentage
                FROM cart c
                JOIN products p ON c.pid = p.id
                LEFT JOIN discounts d ON p.category = d.category
                WHERE c.user_id = ?");
            $select_cart->execute([$user_id]);

            if ($select_cart->rowCount() > 0) {
                $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
                $total_products = count($cart_items);
                $grand_total = 0;
                $product_image = $cart_items[0]['image_01'] ?? ''; // Get the first product's image as a representative image

                foreach ($cart_items as $cart_item) {
                    // Apply discount
                    $discount_percentage = $cart_item['discount_percentage'] ?? 0;
                    $discounted_price = $cart_item['price'] * (1 - $discount_percentage / 100);
                    $grand_total += $discounted_price * $cart_item['quantity'];
                }

                // Get payer details
                $payerInfo = $result->getPayer()->getPayerInfo();
                $name = $payerInfo->getFirstName() . " " . $payerInfo->getLastName();
                $email = $payerInfo->getEmail();
                $phone = $payerInfo->getPhone(); // Fetch the phone number

                $address = $payerInfo->getShippingAddress()->getLine1() . ", " .
                    $payerInfo->getShippingAddress()->getCity() . ", " .
                    $payerInfo->getShippingAddress()->getState() . ", " .
                    $payerInfo->getShippingAddress()->getPostalCode();

                // Insert the order with the discounted total
                $insert_order = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
                $insert_order->execute([$user_id, $name, $phone ?? "N/A", $email, "PayPal", $address, $total_products, $grand_total, "Pending", $product_image]);

                // Get the order ID for reference
                $order_id = $conn->lastInsertId();

                // Deduct stock and clear the cart
                foreach ($cart_items as $cart_item) {
                    $pid = $cart_item['pid'];
                    $quantity = $cart_item['quantity'];

                    // Deduct stock
                    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $update_stock->execute([$quantity, $pid]);
                }

                // Clear the cart
                $clear_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $clear_cart->execute([$user_id]);

                // Redirect to orders page with success message
                echo "<script>alert('Payment successful! Your order ID is $order_id.'); window.location.href = 'orders.php';</script>";
            } else {
                echo "<script>alert('Cart is empty. No items to place an order.'); window.location.href = 'cart.php';</script>";
            }
        } else {
            echo "<script>alert('Payment not approved. Please try again.'); window.location.href = 'checkout.php';</script>";
        }
    } catch (Exception $ex) {
        die("Error processing payment: " . $ex->getMessage());
    }
} else {
    echo "<script>alert('Invalid payment details. Please try again.'); window.location.href = 'checkout.php';</script>";
}
?>
