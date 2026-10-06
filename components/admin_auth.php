
<?php
session_start();

function checkAccess($allowed_roles) {
    if (!isset($_SESSION['admin_role']) || !in_array($_SESSION['admin_role'], $allowed_roles)) {
        header('location:dashboard.php'); // Redirect to dashboard if access is not allowed
        exit;
    }
}
?>
