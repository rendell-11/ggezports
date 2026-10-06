<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
    $delete_user->execute([$delete_id]);
    $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
    $delete_orders->execute([$delete_id]);
    $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
    $delete_messages->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart->execute([$delete_id]);
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
    $delete_wishlist->execute([$delete_id]);
    header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Accounts</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

    <style>
        body {
            background-color: #2c2c2c; /* Dark background */
            color: #ffffff; /* White text */
            font-family: Arial, sans-serif; /* Font family */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th {
            background-color: #3a3a3a; /* Darker background for header */
            text-align: center; /* Center text in header */
            padding: 15px; /* Increased padding for header */
            font-size: 18px; /* Increased font size for header */
        }

        td {
            background-color: #444; /* Dark background for table cells */
            padding: 15px; /* Increased padding for larger text */
            text-align: left; /* Default text alignment for cells */
            border: 1px solid #555; /* Darker border color */
            font-size: 18px; /* Increased text size */
        }

        tr:nth-child(even) td {
            background-color: #3a3a3a; /* Dark background for even rows */
        }

        .delete-btn {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px; /* Smaller font size for the delete button */
            padding: 5px 8px; /* Smaller padding for the delete button */
            border: 1px solid red; /* Add border to the delete button */
            border-radius: 4px; /* Rounded corners for delete button */
            transition: background-color 0.3s; /* Transition effect */
        }

        .delete-btn:hover {
            background-color: red; /* Change background on hover */
            color: white; /* Change text color on hover */
        }

        .empty {
            text-align: center; /* Center align for empty message */
            font-size: 20px; /* Larger font size for empty message */
        }

        .refresh-btn {
    float: right; /* Align to the right */
    background-color: red; /* Red background for the refresh button */
    color: white; /* White text */
    border: none; /* No border */
    border-radius: 4px; /* Rounded corners */
    padding: 10px 15px; /* Padding */
    font-size: 16px; /* Font size */
    cursor: pointer; /* Pointer cursor */
    transition: background-color 0.3s; /* Transition effect */
    margin-bottom: 20px; /* Add space below the button */
}

.refresh-btn:hover {
    background-color: darkred; /* Darker red on hover */
}

    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

    <h1 class="heading">User Accounts</h1>

    <button class="refresh-btn" onclick="window.location.reload();">Refresh</button>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $select_accounts = $conn->prepare("SELECT * FROM `users`");
            $select_accounts->execute();
            if ($select_accounts->rowCount() > 0) {
                while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?= $fetch_accounts['id']; ?></td>
                <td><?= $fetch_accounts['name']; ?></td>
                <td><?= $fetch_accounts['email']; ?></td>
                <td><a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Delete This Account? The User Related Information Will Also Be Deleted!')" class="delete-btn">Delete</a></td>
            </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="4" class="empty">No Accounts Available!</td></tr>';
            }
            ?>
        </tbody>
    </table>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
