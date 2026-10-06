<?php
include '../components/connect.php';
session_start();

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $pass = sha1(filter_var($_POST['pass'], FILTER_SANITIZE_STRING));

    $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ? AND password = ?");
    $select_admin->execute([$name, $pass]);
    $row = $select_admin->fetch(PDO::FETCH_ASSOC);

    if ($select_admin->rowCount() > 0) {
        $_SESSION['admin_id'] = $row['id'];
        $_SESSION['admin_role'] = $row['admin_role']; // Ensure you are using 'admin_role' here
        header('location:dashboard.php');
        exit;
    } else {
        $message[] = 'Incorrect username or password!';
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        .login-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #2d2d2d;
            color: #ffffff;
            text-align: center;
            font-size: 2.5rem;
            padding: 3rem;
            border-radius: 20px;
            width: 400px;
            margin: 15vh auto;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s;
        }
        .login-message:hover { transform: scale(1.08); }
        .login-message .icon {
            font-size: 4rem;
            color: #1e90ff;
            margin-top: 2rem;
            cursor: pointer;
        }
        .popup-overlay, .login-popup {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 30%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }
        .login-popup {
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            width: 400px;
            border-radius: 10px;
        }
        .login-popup input, .login-popup button {
            width: 100%; padding: 10px; margin: 10px 0;
        }
        .close-btn {
            float: right; cursor: pointer; font-size: 18px;
        }
    </style>
</head>
<body>

<?php include '../components/admin_login_header.php'; ?>

<div class="login-message" onclick="openLoginPopup()">
    <div>Please Login</div>
    <p>Click the icon to log in to your admin account</p>
    <i class="fas fa-user-shield icon"></i>
</div>

<div id="popupOverlay" class="popup-overlay"></div>
<div id="loginPopup" class="login-popup">
    <span class="close-btn" onclick="closeLoginPopup()">×</span>
    <h2>Admin Login</h2>
    <form action="" method="POST">
        <input type="text" name="name" placeholder="Username" required>
        <input type="password" name="pass" placeholder="Password" required>
        <button type="submit" name="submit">Login</button>
    </form>
</div>

<script>
    function openLoginPopup() {
        document.getElementById('loginPopup').style.display = 'block';
        document.getElementById('popupOverlay').style.display = 'block';
    }
    function closeLoginPopup() {
        document.getElementById('loginPopup').style.display = 'none';
        document.getElementById('popupOverlay').style.display = 'none';
    }
    document.getElementById('popupOverlay').onclick = closeLoginPopup;
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>
