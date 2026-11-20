<?php
include 'components/connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the delivery status for the selected order
if (!isset($_POST['order_id'])) {
    echo "Order not specified.";
    exit;
}

$order_id = $_POST['order_id'];
$query = $conn->prepare("
    SELECT d.*, o.placed_on 
    FROM delivery_history d 
    JOIN orders o ON d.order_id = o.id 
    WHERE d.order_id = ? 
    ORDER BY d.delivered_on ASC
");
$query->execute([$order_id]);
$deliveries = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Status</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #2b2b2b; /* Dark background */
            color: #fff; /* White text */
        }

        .status-container {
            margin: 20px auto;
            padding: 20px;
            background: #3a3a3a; /* Dark container background */
            border-radius: 10px;
            max-width: 600px;
            position: relative;
        }

        .status-header {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5em;
        }

        .status-step {
            display: flex;
            align-items: flex-start;
            position: relative;
            padding-left: 50px;
            margin-bottom: 30px;
        }

        .circle {
            width: 20px;
            height: 20px;
            background: #f0a500; /* Yellow color for the status circle */
            border-radius: 50%;
            position: absolute;
            left: 15px;
            top: 0;
            z-index: 1;
        }

        .completed-step .circle {
            background: #00c853; /* Green color for completed status */
        }

        .vertical-line {
            position: absolute;
            width: 3px;
            background: #f0a500; /* Yellow color for the line */
            top: 0;
            left: 25px;
            bottom: 0;
            z-index: 0;
        }

        .completed-step .vertical-line {
            background: #00c853; /* Green color for completed status */
        }

        .status-text {
            font-size: 2em;
            margin-left: 20px;
            padding-top: 5px;
        }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<!-- navigation_buttons.php -->
<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>
</div>


<section class="delivery-status">
    <div class="status-container">
        <h1 class="status-header">Delivery Status Timeline</h1>

        <?php if (empty($deliveries)): ?>
            <p>No delivery status updates available for this order.</p>
        <?php else: ?>
            <!-- Vertical line for the entire timeline -->
            <div class="vertical-line"></div>
            <?php foreach ($deliveries as $index => $delivery): ?>
                <div class="status-step <?= $delivery['delivery_status'] == 'Delivered' ? 'completed-step' : ''; ?>">
                    <div class="circle"></div>
                    <div class="status-text">
                        <p><?= htmlspecialchars($delivery['delivery_status']) ?> on <?= htmlspecialchars($delivery['delivered_on']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
