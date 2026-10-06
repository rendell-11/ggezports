<?php

include '../components/connect.php';

session_start();

// Check if either the courier or the admin is logged in
if (!isset($_SESSION['courier_id']) && !isset($_SESSION['admin_id'])) {
    header('location: courier_login.php'); // Redirect to login if not logged in
    exit;
}

// Initialize variables
$completed_deliveries = [];

// Check if the user is an admin
if (isset($_SESSION['admin_id'])) {
    // For admins, get all completed deliveries from all couriers
    try {
        $query = $conn->prepare("
            SELECT dr.*, o.name AS customer_name, o.total_price, o.address AS delivery_address, c.driver_name AS courier_name
            FROM delivery_reports dr
            JOIN orders o ON dr.order_id = o.id
            JOIN courier c ON dr.courier_id = c.id
        ");
        $query->execute();
        $completed_deliveries = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // If the user is a courier, fetch deliveries only for the logged-in courier
    $courier_id = $_SESSION['courier_id'];
    try {
        $query = $conn->prepare("
            SELECT dr.*, o.name AS customer_name, o.total_price, o.address AS delivery_address 
            FROM delivery_reports dr
            JOIN orders o ON dr.order_id = o.id
            WHERE dr.courier_id = ?
        ");
        $query->execute([$courier_id]);
        $completed_deliveries = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle order deletion (available to both couriers and admins)
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    // Delete from delivery_history
    $delete_delivery = $conn->prepare("DELETE FROM delivery_history WHERE order_id = ?");
    $delete_delivery->execute([$order_id]);

    // Optionally, delete from delivery_reports as well
    $delete_report = $conn->prepare("DELETE FROM delivery_reports WHERE order_id = ?");
    $delete_report->execute([$order_id]);

    // Redirect to delivery_reports.php after deletion
    header('location: delivery_reports.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Reports</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
        }
        .container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
            max-width: 1200px;
            margin: 0 auto;
        }
        table {
            width: 100%;
            color: #ffffff;
            border-color: #333;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 15px;
            text-align: center;
            border: 1px solid #333;
            font-size: 1.8em;
        }
        table th {
            background-color: #333333;
            color: #ffffff;
        }
        table td {
            background-color: #2c2c2c;
        }
        .table-hover tbody tr:hover {
            background-color: #444444;
        }
        .btn-danger {
            background-color: #d9534f;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .btn-danger:hover {
            background-color: #c9302c;
            color: #ffffff;
        }
        h2 {
            color: #ffffff;
            font-size: 2em;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="contacts">
        <h1 class="heading">Order Status</h1>

        <div class="container mt-5">
            <h2>Delivery Reports</h2>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Total Price</th>
                            <th>Delivery Address</th>
                            <th>Delivery Status</th>
                            <th>Delivered On</th>
                            <?php if (isset($_SESSION['admin_id'])) : ?>
                                <th>Courier Name</th> <!-- Admin can view courier name -->
                            <?php endif; ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($completed_deliveries) > 0) : ?>
                            <?php foreach ($completed_deliveries as $delivery) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($delivery['order_id']) ?></td>
                                    <td><?= htmlspecialchars($delivery['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($delivery['total_price']) ?></td>
                                    <td><?= htmlspecialchars($delivery['delivery_address']) ?></td>
                                    <td><?= htmlspecialchars($delivery['delivery_status']) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($delivery['delivered_on']))) ?></td>
                                    <?php if (isset($_SESSION['admin_id'])) : ?>
                                        <td><?= htmlspecialchars($delivery['courier_name']) ?></td> <!-- Displaying courier name -->
                                    <?php endif; ?>
                                    <td>
                                        <form method="post" action="">
                                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($delivery['order_id']) ?>">
                                            <button type="submit" name="delete_order" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8" class="text-center">No completed deliveries found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script src="../js/admin_script.js"></script>

</body>

</html>
