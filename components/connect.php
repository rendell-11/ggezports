<?php

try {
    $db_name = 'mysql:host=localhost;dbname=shop_db;charset=utf8';
    $user_name = 'root';
    $user_password = '';

    $conn = new PDO($db_name, $user_name, $user_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
