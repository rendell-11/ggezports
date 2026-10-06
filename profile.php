<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:user_login.php');
   exit();
}

include 'components/wishlist_cart.php';

$user_name = '';
$user_email = '';

if ($user_id != '') {
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_name = $row['name'];
        $user_email = $row['email'];
    }
}

$order_status = [
    'toPay' => true,
    'toShip' => false,
    'toReceive' => false,
    'toComplete' => false
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/profile.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<?php include 'components/button.php'; ?>
<div class="navigation-buttons">
    <button class="nav-button" id="prev-btn" onclick="history.back()">
        <i class="fas fa-arrow-left"></i> Back
    </button>

    <a href="shop.php" id="next-btn-link">
    <button class="nav-button" id="next-btn">
        Next <i class="fas fa-arrow-right"></i>
    </button>
</a>

</div>

<section class="profile">
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-img">
            <a href="address_editor.php">
            <img src="images/image.png" alt="Avatar">
            </a>
        </div>
        <div class="profile-info">
            <div>Name: <span><?php echo htmlspecialchars($user_name); ?></span></div>
            <div>Email:<span><?php echo htmlspecialchars($user_email); ?></span></div>
        </div>
        
    </div>
    
    
    <div class="profile-orders">

            <div class="icon-text <?php echo $order_status['toPay']? : '';?>">
            <a href="orders.php">
            <div class="icons">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <span>To pay</span>
            </a>
        </div>
        
        
        <div class="icon-text  to-ship <?php echo $order_status['toShip']? : '';?>">
           <a href="to_ship.php">
        <div class="icons">
                <i class="fas fa-truck"></i>
            </div>
            <span>To ship</span>
            </a>
        </div>
        
        
        <div class="icon-text ">
            <a href="cancel_order.php">
            <div class="icons">
                <i class="fas fa-close"></i>
            </div>
            <span>Cancelled</span>
            </a>
        </div>
        
        <div class="icon-text to-complete <?php echo $order_status['toComplete'] ? : ''; ?>">
           <a href="order_history.php">
        <div class="icons">
                <i class="fas fa-check-circle"></i>
            </div>
            <span>Completed</span>
            </a> 
        </div>

        <div class="icon-text to-complete <?php echo $order_status['toComplete'] ? : ''; ?>">
           <a href="return.php">
        <div class="icons">
                <i class="fas fa-check-circle"></i>
            </div>
            <span>Return / Refund</span>
            </a> 
        </div>
        
        <!-- <div class="icon-text to-receive <?php echo $order_status['toReceive'] ?: '';?>">
            <a href="return_refund.php">
        <div class="icons">
                <i class="fas fa-box"></i>
                </div>
                <span>Return / Refund</span>
                </a>
        </div> -->
        
        
        
    </div>
    
</div>

</section>

</div>


<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>