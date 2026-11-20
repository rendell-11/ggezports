<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    // User is already logged in, redirect to home
    header("Location: home.php");
    exit();
}

// Handle regular login
if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = sha1(filter_var($_POST['pass'], FILTER_SANITIZE_STRING));

    $select_user = $conn->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $select_user->execute([$email, $pass]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $_SESSION['user_id'] = $row['id'];
        header('Location: home.php');
        exit();
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
   <title>Login</title>

   <!-- Font Awesome & Custom CSS -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
       /* Google Login Button Styles */
       .google-login {
           background-color: white; /* Set background to white */
           color: #4285F4; /* Set text color to Google blue */
           border: 1px solid #4285F4; /* Add a border */
           padding: 10px 20px; /* Adjust padding */
           border-radius: 5px; /* Add rounded corners */
           display: inline-flex; /* Align icon and text */
           align-items: center; /* Center vertically */
           justify-content: center; /* Center horizontally */
           text-decoration: none; /* Remove underline */
           font-weight: bold; /* Make text bold */
           font-size: 16px; /* Set font size */
           transition: background-color 0.3s, color 0.3s; /* Smooth transition */
           width: 100%; /* Make it full width */
           max-width: 300px; /* Limit max width */
           margin: 10px auto; /* Center the button */
       }

       .google-login i {
           margin-right: 8px; /* Space between icon and text */
           font-size: 18px; /* Set icon size */
       }

       .google-login:hover {
           background-color: #f1f1f1; /* Hover effect */
           color: #4285F4; /* Keep text color */
       }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <form action="" method="post">
      <h3>Login Now</h3>
      <input type="email" name="email" required placeholder="Email" maxlength="50" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Login Now" class="btn" name="submit">
      <p>Don't have an account?</p>
      <a href="user_register.php" class="option-btn">Register Now</a>
      <br><br>
      <!-- Google Login Button -->
      <a href="google_login.php" class="btn google-login" style="text-align: center;">
          <i class="fab fa-google"></i> Login with Google
      </a>
   </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
