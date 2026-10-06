<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit; // Prevent further code execution after redirect
}

// Check if an ID is set for deletion
if (isset($_GET['id'])) {
    $courier_id = $_GET['id'];

    // Prepare the delete statement
    $delete_courier = $conn->prepare("DELETE FROM `courier` WHERE id = ?");
    if ($delete_courier->execute([$courier_id])) {
        // If the delete is successful, redirect back to the courier management page
        header('location:admin_courier_account.php?message=Courier account deleted successfully!');
    } else {
        // If the delete fails, you can set an error message
        header('location:admin_courier_account.php?message=Failed to delete courier account!');
    }
} else {
    // If no ID is set, redirect back with an error message
    header('location:admin_courier_account.php?message=No courier ID provided!');
}
?>
