<?php

include '../components/connect.php';


// Initialize message array
$message = [];

// Handle admin login request for popup form
if (isset($_POST['admin_login'])) {
    // Sanitize user input
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // Prepare and execute the query to find the admin
    $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
    $select_admin->execute([$name, $pass]);
    $row = $select_admin->fetch(PDO::FETCH_ASSOC);

    // Check if the admin was found and verify the password
    if ($select_admin->rowCount() > 0) {
        $_SESSION['admin_id'] = $row['id']; // Store the admin ID in session
        header('Location: dashboard.php'); // Redirect to the dashboard
        exit(); // Exit to ensure no further code is executed
    } else {
        $message[] = 'Incorrect username or password!'; // Display error message
    }
}

// Display any messages if available, only if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        /* Styling for login popup */
        .login-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
            z-index: 1000;
            width: 400px;
            max-width: 90%;
            color: black;
        }
        .popup-content {
            position: relative;
        }
        .close {
            position: absolute;
            top: 0px;
            left: 100%;
            cursor: pointer;
            font-size: 28px;
        }
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 999;
        }
        .login-popup input[type="text"],
        .login-popup input[type="password"] {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 18px;
        }
        .login-popup .btn {
            width: 100%;
            padding: 15px;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 18px;
            border-radius: 4px;
        }
        .login-popup .btn:hover {
            background: #555;
        }
    </style>
</head>
<body>

<header class="header">
    <section class="flex">
        <!-- Insert side login component -->
        <?php include '../components/admin_side_login.php'; ?>

        <!-- Search Bar -->
        <form action="" method="GET" class="search-form">
            <!-- <input type="text" name="query" placeholder="Search..." required>
            <button type="submit" class="fas fa-search"></button> -->
        </form>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="profile">
            <?php
            // Check if admin is logged in
            if (isset($_SESSION['admin_id'])) {
                // Admin is logged in, but no message will be shown
            } else {
                // If not logged in, show the login button
                echo '<p>Please login to continue.</p>';
            }
            ?>
            <div class="flex-btn">
                <a href="javascript:void(0);" onclick="openLoginPopup()" class="option-btn">Login</a>
            </div>
        </div>
    </section>
</header>



<!-- Popup overlay and login form -->
<div id="popupOverlay" class="popup-overlay"></div>
<div id="loginPopup" class="login-popup">
    <div class="popup-content">
        <span class="close" onclick="closeLoginPopup()">&times;</span>
        <h2>Admin Login</h2>
        <form action="" method="POST">
            <input type="text" name="name" placeholder="Username" required>
            <input type="password" name="pass" placeholder="Password" required>
            <button type="submit" name="admin_login" class="btn">Login</button>
        </form>
    </div>
</div>

<script>
    // Open the login popup
    function openLoginPopup() {
        document.getElementById('loginPopup').style.display = 'block';
        document.getElementById('popupOverlay').style.display = 'block';
    }

    // Close the login popup
    function closeLoginPopup() {
        document.getElementById('loginPopup').style.display = 'none';
        document.getElementById('popupOverlay').style.display = 'none';
    }

    // Close the popup if overlay is clicked
    document.getElementById('popupOverlay').onclick = function() {
        closeLoginPopup();
    }
</script>

</body>
</html>
