<?php
require 'vendor/autoload.php';
include 'components/connect.php';

use PayPal\Api\{Payer, Amount, Transaction, RedirectUrls, Payment};

if (!isset($_GET['user_id'])) {
    die("Invalid request: Missing parameters.");
}

$clientID = "AQDGKaM8vsJZ0U-FJwZI-yroBReDQuJE2AWIa2nwCyXv8Q8xbJ9lH3MNJitn66KpL2_W3blHSH3jnO0p";
$secretID = "YOUR_PAYPAL_SECRET";

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential($clientID, $secretID)
);

$user_id = $_GET['user_id'];

// Fetch cart items and calculate the total price after discount
$select_cart = $conn->prepare("
    SELECT c.*, p.price, p.image_01, d.discount_percentage
    FROM cart c
    JOIN products p ON c.pid = p.id
    LEFT JOIN discounts d ON p.category = d.category
    WHERE c.user_id = ?");
$select_cart->execute([$user_id]);

$cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
$items = [];  // Prepare an array to hold items for PayPal transaction

foreach ($cart_items as $cart_item) {
    // If there's a discount, apply it
    $discount_percentage = $cart_item['discount_percentage'] ?? 0;
    $discounted_price = $cart_item['price'] * (1 - $discount_percentage / 100);
    $item_total = $discounted_price * $cart_item['quantity'];
    
    // Add the discounted price to the total
    $total += $item_total;
    
    // Prepare item for PayPal (each item needs to be added separately to PayPal)
    $items[] = [
        "name" => $cart_item['name'],
        "sku" => $cart_item['pid'],
        "price" => number_format($discounted_price, 2),
        "currency" => "USD",
        "quantity" => $cart_item['quantity']
    ];
}

if ($total <= 0) {
    die("Invalid total amount after applying discount.");
}

// Prepare PayPal payment details
$payer = new Payer();
$payer->setPaymentMethod('paypal');

$amount = new Amount();
$amount->setTotal(number_format($total, 2));  // Ensure this is properly formatted as a float
$amount->setCurrency('USD');

$transaction = new Transaction();
$transaction->setAmount($amount);
$transaction->setDescription('Payment for your order.');
$transaction->setItemList(["items" => $items]);

$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl("http://localhost/ggezports/paypal_success.php?user_id=$user_id")
             ->setCancelUrl("http://localhost/ggezports/paypal_cancel.php");

$payment = new Payment();
$payment->setIntent('sale')
        ->setPayer($payer)
        ->setTransactions([$transaction])
        ->setRedirectUrls($redirectUrls);

try {
    $payment->create($apiContext);
    header("Location: " . $payment->getApprovalLink());
    exit();
} catch (Exception $ex) {
    die("Error: " . $ex->getMessage());
}
?>
