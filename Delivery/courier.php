<?php
include '../components/connect.php';
session_start();

// Check if the courier is logged in
if (!isset($_SESSION['courier_id'])) {
    die("You are not logged in.");
}

// Get the courier ID from the session
$courier_id = $_SESSION['courier_id']; 

// Fetch courier details
$query = $conn->prepare("SELECT * FROM courier WHERE id = ?");
$query->execute([$courier_id]);
$courier = $query->fetch(PDO::FETCH_ASSOC);

// Check if courier data was found
if (!$courier) {
    die("Courier with ID {$courier_id} not found.");
}

// Fetch delivery history with customer name and placed_on date
$history_query = $conn->prepare("
    SELECT dh.*, o.name AS customer_name, o.address AS delivery_address, o.placed_on 
    FROM delivery_history dh
    INNER JOIN orders o ON dh.order_id = o.id
    WHERE dh.courier_id = ?
");
$history_query->execute([$courier_id]);
$delivery_history = $history_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Profile</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/courier1.css">
    <script src="../js/darkmode.js"></script>

    <style>
        /* Floating button styles */
        .floating-button {
            position: fixed;
            bottom: 20px;
            left: 330px; /* Change 'right' to 'left' */
            width: 60px;
            height: 60px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            font-size: 24px;
            text-decoration: none;
        }
    </style>
</head>
<body>
<?php include '../components/courier_header_component.php'; ?>

    <!-- Main Content Section -->
    <div class="main-content">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <!-- Courier's Profile Image -->
                    <img src="../uploaded_img/<?php echo htmlspecialchars($courier['driver_image'] ?? 'default.jpg'); ?>" alt="Courier's Profile Picture">
                    
                    <!-- Courier's Basic Info -->
                    <div class="details">
                        <h1><?php echo htmlspecialchars($courier['driver_name']); ?></h1>
                        <p>ID: <?php echo htmlspecialchars($courier['id']); ?></p>
                        <p>Email: <?php echo htmlspecialchars($courier['email']); ?></p>
                        <p>Phone: <?php echo htmlspecialchars($courier['driver_contact']); ?></p>
                    </div>

                    <!-- Courier Status -->
                    <div class="status">
                        <span class="status-badge"><?php echo htmlspecialchars($courier['status']); ?></span>
                    </div>
                </div>
                
                <!-- Delivery History Section -->
                <!-- <h3>Delivery History</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Delivery Address</th>
                            <th>Status</th>
                            <th>Placed On</th> New header for Placed On date
                        </tr>
                    </thead> -->
                    <tbody>
                        <?php if (count($delivery_history) > 0): ?>
                            <?php foreach ($delivery_history as $history): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($history['id']); ?></td>
                                    <td><?php echo htmlspecialchars($history['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($history['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($history['delivery_address']); ?></td>
                                    <td><?php echo htmlspecialchars($history['delivery_status']); ?></td>
                                    <td>
                                        <?php 
                                            // Format and display the placed_on date
                                            if (!empty($history['placed_on'])) {
                                                echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($history['placed_on']))); 
                                            } else {
                                                echo 'N/A'; // Placeholder if no date is available
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>

        <!-- Floating button to view my deliveries -->
        <a href="my_deliveries.php" class="floating-button" title="View My Deliveries">+</a>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
