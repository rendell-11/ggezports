<?php
require 'vendor/autoload.php';
include 'components/connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

\Stripe\Stripe::setApiKey('YOUR_STRIPE_SECRET_KEY');

$user_id = $_SESSION['user_id'];
$select_cart = $conn->prepare("SELECT c.*, p.image_01, p.stock, p.price FROM `cart` c JOIN `products` p ON c.pid = p.id WHERE c.user_id = ?");
$select_cart->execute([$user_id]);

$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

if (count($cart_items) > 0) {
    // Calculate total or discounted total
    $grand_total = array_reduce($cart_items, function ($carry, $item) {
        return $carry + ($item['price'] * $item['quantity']);
    }, 0);

    $final_total = isset($_SESSION['discounted_total']) ? $_SESSION['discounted_total'] : $grand_total;

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Order Total'],
                    'unit_amount' => $final_total * 100, // Pass discounted total (in cents)
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost/ggezports/stripe_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/ggezports/checkout.php?canceled=true',
        ]);

        // Store session data for order processing
        $_SESSION['stripe_order_data'] = [
            'cart_items' => $cart_items,
            'grand_total' => $final_total, // Store discounted total if applied
        ];

        header("Location: " . $session->url);
        exit();
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    die("Your cart is empty.");
}

?>
