<?php
include '../components/connect.php';

session_start();

if (isset($_POST['submit'])) {
    // Get the email and password from the form
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_UNSAFE_RAW);
    $pass = $_POST['password'];
    $pass = filter_var($pass, FILTER_UNSAFE_RAW);

    // Fetch the courier details using the provided email and password
    $select_courier = $conn->prepare("SELECT * FROM `courier` WHERE email = ? AND password = ?");
    $select_courier->execute([$email, $pass]);
    $row = $select_courier->fetch(PDO::FETCH_ASSOC);

    // Check if the courier exists and the password matches
    if ($select_courier->rowCount() > 0) {
        $_SESSION['courier_id'] = $row['id']; // Store the courier ID in session
        header('location: ../Delivery/courier.php'); // Redirect to the courier dashboard
        exit;
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin: 0;
        }

        .login-card {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.2);
            color: #333;
            max-width: 400px;
            width: 100%;
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #6a11cb;
        }

        .login-card .btn-primary {
            background: #6a11cb;
            border: none;
        }

        .login-card .btn-primary:hover {
            background: #2575fc;
        }

        .alert {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2>Courier Login</h2>
        <?php if (isset($message)): // Check for error message ?>
            <div class="alert alert-danger"><?php echo $message[0]; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
