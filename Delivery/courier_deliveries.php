<?php
include '../components/connect.php';
session_start();

// Check if the courier is logged in
if (!isset($_SESSION['courier_id'])) {
    header('location: courier_login.php'); // Redirect to login if not logged in
    exit;
}

$courier_id = $_SESSION['courier_id'];

// Get all pending orders that are not assigned to any rider yet
$query = $conn->prepare("SELECT * FROM orders WHERE payment_status = 'pending' AND id NOT IN (SELECT order_id FROM delivery_history WHERE courier_id = ?)");
$query->execute([$courier_id]);
$orders = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle delivery assignment
if (isset($_POST['assign_order'])) {
    $order_id = $_POST['order_id'];

    // Insert into delivery_history to assign the order to the rider
    $insert_delivery = $conn->prepare("INSERT INTO delivery_history (courier_id, order_id, delivery_address, delivery_status, delivered_on) VALUES (?, ?, ?, ?, NULL)");
    $insert_delivery->execute([$courier_id, $order_id, $_POST['delivery_address'], 'In Progress']);

    // Update order status
    $update_order = $conn->prepare("UPDATE orders SET payment_status = 'In Progress' WHERE id = ?");
    $update_order->execute([$order_id]);

    // Redirect to courier deliveries page
    header('location: courier_deliveries.php'); // Change this line to redirect to "courier_deliveries.php"
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Deliveries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/courier1.css">
    <script src="../js/darkmode.js"></script>
</head>
<body>
    
    <?php include '../components/courier_header_component.php'; ?>
    
    
    
<div class="main-content">
    <div class="profile-container">
    <div class="container mt-5">
        <h2>Available Deliveries</h2>
        
        
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Total Price</th>
                        <th>Placed On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['name']) ?></td>
                                <td><?= htmlspecialchars($order['address']) ?></td>
                                <td><?= htmlspecialchars($order['total_price']) ?></td>
                                <td><?= htmlspecialchars($order['placed_on']) ?></td>
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <input type="hidden" name="delivery_address" value="<?= htmlspecialchars($order['address']) ?>">
                                        <button type="submit" name="assign_order" class="btn btn-primary">Deliver</button>
                                    </form>
                                    
                            
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No pending orders available for delivery.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div> 
</div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
