<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('location:login.php');
    exit;
}

if ($_SESSION['admin_role'] !== $required_role && $_SESSION['admin_role'] !== 'Super Admin') {
    header('location:dashboard.php');
    exit;
}
