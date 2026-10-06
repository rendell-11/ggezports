<?php
include '../components/connect.php';
session_start();

// Check if the courier is logged in
if (!isset($_SESSION['courier_id'])) {
    header('location: courier_login.php'); // Redirect to login if not logged in
    exit;
}

$courier_id = $_SESSION['courier_id'];

// Get all completed deliveries for the logged-in courier, including the delivery address
$query = $conn->prepare("
    SELECT dr.*, o.name AS customer_name, o.total_price, o.address AS delivery_address 
    FROM delivery_reports dr
    JOIN orders o ON dr.order_id = o.id
    WHERE dr.courier_id = ?
");
$query->execute([$courier_id]);
$completed_deliveries = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle order deletion
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    // Delete from delivery_history
    $delete_delivery = $conn->prepare("DELETE FROM delivery_history WHERE order_id = ? AND courier_id = ?");
    $delete_delivery->execute([$order_id, $courier_id]);

    // Optionally, delete from delivery_reports as well
    $delete_report = $conn->prepare("DELETE FROM delivery_reports WHERE order_id = ? AND courier_id = ?");
    $delete_report->execute([$order_id, $courier_id]);

    // Redirect to reports.php after deletion
    header('location: delivery_reports.php'); // Change this line if you want to stay on the same page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/courier1.css">
    <script src="../js/darkmode.js"></script>
</head>

<body>
    <?php include '../components/courier_header_component.php'; ?>

    <div class="container mt-5">
        <h2>Delivery Reports</h2>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Total Price</th>
                        <th>Delivery Address</th> <!-- New column for Delivery Address -->
                        <th>Delivery Status</th>
                        <th>Delivered On</th>
                        <th>Action</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($completed_deliveries) > 0): ?>
                        <?php foreach ($completed_deliveries as $delivery): ?>
                            <tr>
                                <td><?= htmlspecialchars($delivery['order_id']) ?></td>
                                <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                <td><?= htmlspecialchars($delivery['total_price']) ?></td>
                                <td><?= htmlspecialchars($delivery['delivery_address']) ?></td> <!-- Displaying Delivery Address -->
                                <td><?= htmlspecialchars($delivery['delivery_status']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($delivery['delivered_on']))) ?></td> <!-- Formatting delivered_on -->
                                <td>
                                    <form method="post" action="">
                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($delivery['order_id']) ?>">
                                        <button type="submit" name="delete_order" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">You have no completed deliveries.</td> <!-- Adjusted colspan -->
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
