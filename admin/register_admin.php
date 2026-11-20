<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $cpass = sha1($_POST['cpass']);
    $admin_role = $_POST['role']; // Get the selected role
 
    $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
    $select_admin->execute([$name]);
 
    if ($select_admin->rowCount() > 0) {
        $message[] = 'Username Already Exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'Confirm Password Not Matched!';
        } else {
            $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password, admin_role) VALUES(?, ?, ?)");
            $insert_admin->execute([$name, $pass, $admin_role]);
            $message[] = 'New Admin Registered Successfully!';
        }
    }
 }
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register Admin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/adminstyle.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

<form action="" method="post">
    <h3>Register Now</h3>
    <input type="text" name="name" required placeholder="Enter Your Username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
    <input type="password" name="pass" required placeholder="Enter Your Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
    <input type="password" name="cpass" required placeholder="Confirm Your Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
    
    <!-- Role Selection -->
    <select name="role" class="box" required>
        <option value="super_admin">Super Admin</option>
        <option value="order_admin">Order Admin</option>
        <option value="delivery_admin">Delivery Admin</option>
        <option value="reports_admin">Reports Admin</option>
    </select>
    
    <input type="submit" value="register now" class="btn" name="submit">
</form>


</section>

<script src="../js/admin_script.js"></script>
   
</body>
</html>