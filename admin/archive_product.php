<?php
include '../components/connect.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

if (isset($_GET['restore'])) {
    $restore_id = $_GET['restore'];

    $restore_product = $conn->prepare("INSERT INTO `products` (name, details, price, category, stock, image_01, image_02, image_03, created_at) 
    SELECT name, details, price, category, stock, image_01, image_02, image_03, NOW() FROM `archived_products` WHERE id = ?");
    $restore_product->execute([$restore_id]);

    $delete_archive = $conn->prepare("DELETE FROM `archived_products` WHERE id = ?");
    $delete_archive->execute([$restore_id]);

    header('location:archive_product.php');
    exit;
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    <style>
        body {
            background-color: #2c2c2c;
            color: #fff;
        }
        .table-container {
            width: 90%;
            margin: 20px auto;
            background: #333;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #444;
        }
        td {
            background-color: #555;
        }
        .restore-btn {
            padding: 10px 15px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .restore-btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="table-container">
    <h1 class="heading">Archived Products</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Details</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
$select_archived = $conn->prepare("SELECT * FROM `archived_products`");
$select_archived->execute();

if ($select_archived->rowCount() > 0) {
    while ($fetch_archived = $select_archived->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>" . htmlspecialchars($fetch_archived['id']) . "</td>
                <td>" . htmlspecialchars($fetch_archived['name']) . "</td>
                <td>" . htmlspecialchars($fetch_archived['details']) . "</td>
                <td>$" . htmlspecialchars($fetch_archived['price']) . "</td>
                <td>" . htmlspecialchars($fetch_archived['stock']) . "</td>
                <td>" . htmlspecialchars($fetch_archived['category']) . "</td>
                <td>
                    <a href='archive_product.php?restore=" . $fetch_archived['id'] . "' class='restore-btn' onclick='return confirm(\"Restore this product?\");'>Restore</a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7'>No archived products found.</td></tr>";
}
?>

        </tbody>
    </table>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
