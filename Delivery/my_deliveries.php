<?php
include '../components/connect.php';
session_start();

// Check if the courier is logged in
if (!isset($_SESSION['courier_id'])) {
    header('location: courier_login.php'); // Redirect to login if not logged in
    exit;
}

$courier_id = $_SESSION['courier_id'];

// Get all deliveries assigned to the logged-in courier
$query = $conn->prepare("
    SELECT dh.*, o.name AS customer_name, o.total_price, o.placed_on, o.payment_status
    FROM delivery_history dh
    JOIN orders o ON dh.order_id = o.id
    WHERE dh.courier_id = ?
");
$query->execute([$courier_id]);
$my_deliveries = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle delivery removal
if (isset($_POST['remove_delivery'])) {
    $order_id = $_POST['order_id'];

    // Delete from delivery_history
    $delete_delivery = $conn->prepare("DELETE FROM delivery_history WHERE courier_id = ? AND order_id = ?");
    $delete_delivery->execute([$courier_id, $order_id]);

    // Update order status
    $update_order = $conn->prepare("UPDATE orders SET payment_status = 'pending' WHERE id = ?");
    $update_order->execute([$order_id]);

    header('location: my_deliveries.php'); // Refresh the page
    exit;
}

// Handle status updates
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $delivery_status = $_POST['delivery_status'];

    // Update delivery status in delivery_history
    $update_delivery_status = $conn->prepare("UPDATE delivery_history SET delivery_status = ? WHERE courier_id = ? AND order_id = ?");
    $update_delivery_status->execute([$delivery_status, $courier_id, $order_id]);

    header('location: my_deliveries.php'); // Refresh the page
    exit;
}

// Handle marking delivery as done
if (isset($_POST['mark_done'])) {
    $order_id = $_POST['order_id'];

    // Insert the order into the reports table
    $insert_report = $conn->prepare("INSERT INTO delivery_reports (order_id, courier_id, delivery_status, delivered_on) SELECT order_id, courier_id, delivery_status, NOW() FROM delivery_history WHERE courier_id = ? AND order_id = ?");
    $insert_report->execute([$courier_id, $order_id]);

    // Remove the order from delivery_history
    $delete_delivery = $conn->prepare("DELETE FROM delivery_history WHERE courier_id = ? AND order_id = ?");
    $delete_delivery->execute([$courier_id, $order_id]);

    header('location: my_deliveries.php'); // Refresh the page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Deliveries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/courier1.css">
    <script src="../js/darkmode.js"></script>
    
    <style>
    .logout-container {
        margin-top: auto; /* Pushes the logout button to the bottom */
    }

    .logout-button {
        display: block;
        padding: 10px;
        text-align: center;
        background-color: #dc3545; /* Bootstrap danger color */
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 0; /* Adds space around the button */
    }

    .logout-button:hover {
        background-color: #c82333; /* Darker red on hover */
    }
    
    
</style>
    
</head>

<body>

    
    <!-- Sidebar Section -->
<div class="sidebar">
    <h2>Courier Dashboard</h2>
    <ul>
        <li><a href="courier.php">Profile</a></li>
        <li><a href="courier_deliveries.php">Available Deliveries</a></li>
        <li><a href="my_deliveries.php">My Deliveries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>

    <!-- Logout Button -->
    <div class="logout-container">
        <a href="login.php" class="logout-button">Logout</a>
    </div>

    <!-- Toggle Switch for Dark Mode -->
    <div class="toggle-container">
        <label class="toggle">
            <input type="checkbox" id="mode-toggle" onchange="toggleMode()">
            <span class="slider"></span>
        </label>
        <span class="ms-2"> Dark Mode</span>
    </div>
</div>
<div class="main-content">
    <div class="profile-container">
    <div class="container mt-5">
        <h2>My Deliveries</h2>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Delivery Address</th> <!-- Added Delivery Address -->
                        <th>Total Price</th>
                        <th>Placed On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($my_deliveries) > 0): ?>
                        <?php foreach ($my_deliveries as $delivery): ?>
                            <tr>
                                <td><?= htmlspecialchars($delivery['order_id']) ?></td>
                                <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                <td><?= htmlspecialchars($delivery['delivery_address']) ?></td> <!-- Display Delivery Address -->
                                <td><?= htmlspecialchars($delivery['total_price']) ?></td>
                                <td><?= htmlspecialchars($delivery['placed_on']) ?></td>
                                <td><?= htmlspecialchars($delivery['delivery_status']) ?></td>
                                <td>
    <form method="post" action="">
        <input type="hidden" name="order_id" value="<?= $delivery['order_id'] ?>">

        <!-- Dropdown for Delivery Status -->
        <select name="delivery_status" class="form-select">
            <option value="Pending" <?= ($delivery['delivery_status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
            <option value="Shipped" <?= ($delivery['delivery_status'] === 'Shipped') ? 'selected' : '' ?>>Shipped</option>
            <option value="Delivered" <?= ($delivery['delivery_status'] === 'Delivered') ? 'selected' : '' ?>>Delivered</option>
            <option value="Canceled" <?= ($delivery['delivery_status'] === 'Canceled') ? 'selected' : '' ?>>Canceled</option>
        </select>

        <button type="submit" name="update_status" class="btn btn-success">Update Status</button>
        <button type="submit" name="remove_delivery" class="btn btn-danger">Remove</button>
        
        <!-- Done button, only enabled if the delivery is marked as "Delivered" or "Canceled" -->
        <?php if ($delivery['delivery_status'] === 'Delivered' || $delivery['delivery_status'] === 'Canceled'): ?>
            <button type="submit" name="mark_done" class="btn btn-primary">Done</button>
        <?php else: ?>
            <button type="button" class="btn btn-secondary" disabled>Done</button>
        <?php endif; ?>
    </form>
</td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">You have no assigned deliveries.</td>
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