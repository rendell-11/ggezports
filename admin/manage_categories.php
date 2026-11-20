<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit;
}

if (isset($_GET['archive'])) {
    $archive_id = $_GET['archive'];

    $fetch_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $fetch_product->execute([$archive_id]);
    $product_data = $fetch_product->fetch(PDO::FETCH_ASSOC);

    $archive_product = $conn->prepare("INSERT INTO `archived_products` 
    (product_id, name, details, price, category, stock, image_01, image_02, image_03, archive_at) 
    SELECT id, name, details, price, category, stock, image_01, image_02, image_03, NOW() 
    FROM `products` WHERE id = ?");
    
    if ($archive_product->execute([$archive_id])) {
        $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
        $delete_product->execute([$archive_id]);

        header('location:manage_categories.php');
        exit;
    } else {
        print_r($archive_product->errorInfo());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>

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
        h2.sub-heading {
            font-size: 2rem;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            margin-bottom: 20px;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #444;
            font-size: 16px;
        }
        th {
            background-color: #444;
        }
        td {
            background-color: #555;
        }
        .option-btn, .delete-btn {
            padding: 10px 15px;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .option-btn {
            background-color: #28a745;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .option-btn:hover, .delete-btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="table-container">
    <h1 class="heading">Manage Categories</h1>



    <?php
    $categories = ['Laptop', 'Console', 'Lifestyle', 'Headphones', 'Charger', 'Accessories'];
    foreach ($categories as $category) {
        echo "<h2 class='sub-heading'>{$category}</h2>";
        echo "<table>";
        echo "<thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Details</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
              </thead>
              <tbody>";

        $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
        $select_products->execute([$category]);

        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                        <td>" . htmlspecialchars($fetch_products['id']) . "</td>
                        <td>" . htmlspecialchars($fetch_products['name']) . "</td>
                        <td>" . htmlspecialchars($fetch_products['details']) . "</td>
                        <td>$" . htmlspecialchars($fetch_products['price']) . "</td>
                        <td>" . htmlspecialchars($fetch_products['stock']) . "</td>
                        <td>
                            <a href='update_product.php?update=" . $fetch_products['id'] . "' class='option-btn'>Update</a>
                            <a href='manage_categories.php?archive=" . $fetch_products['id'] . "' class='delete-btn' onclick='return confirm(\"Archive this product?\");'>Archive</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No products found in this category.</td></tr>";
        }

        echo "</tbody></table>";
    }
    ?>
</section>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
