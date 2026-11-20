<?php
include 'components/connect.php';

session_start();

header('Content-Type: application/json'); // Ensure the response is JSON

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
} else {
   echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
   exit();
}

if (isset($_POST['cart_id']) && isset($_POST['qty'])) {
    $cart_id = $_POST['cart_id'];
    $new_qty = (int)$_POST['qty'];

    // Fetch the cart item and product stock
    $select_cart = $conn->prepare("SELECT c.pid, c.quantity, p.stock FROM `cart` c JOIN `products` p ON c.pid = p.id WHERE c.id = ? AND c.user_id = ?");
    $select_cart->execute([$cart_id, $user_id]);
    $cart_item = $select_cart->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $product_stock = $cart_item['stock'];
        $product_id = $cart_item['pid'];

        // Check if the requested quantity is within stock
        if ($new_qty > $product_stock) {
            echo json_encode(['status' => 'error', 'message' => 'Not enough stock available']);
            exit();
        }

        // Update the quantity in the cart
        $update_cart = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
        if ($update_cart->execute([$new_qty, $cart_id])) {
            // Return a success response
            echo json_encode(['status' => 'success', 'message' => 'Quantity updated']);
        } else {
            // Error updating quantity in the cart table
            echo json_encode(['status' => 'error', 'message' => 'Failed to update cart quantity']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
