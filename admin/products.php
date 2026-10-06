<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit; // Prevent further code execution after redirect
}

if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);
    $details = $_POST['details'];
    $details = filter_var($details, FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $category = filter_var($category, FILTER_SANITIZE_STRING);
    $stock = $_POST['stock'];
    $stock = filter_var($stock, FILTER_SANITIZE_NUMBER_INT);

    $image_01 = $_FILES['image_01']['name'];
    $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
    $image_size_01 = $_FILES['image_01']['size'];
    $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
    $image_folder_01 = '../uploaded_img/' . $image_01;

    $image_02 = $_FILES['image_02']['name'];
    $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
    $image_size_02 = $_FILES['image_02']['size'];
    $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
    $image_folder_02 = '../uploaded_img/' . $image_02;

    $image_03 = $_FILES['image_03']['name'];
    $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
    $image_size_03 = $_FILES['image_03']['size'];
    $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
    $image_folder_03 = '../uploaded_img/' . $image_03;

    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_products->execute([$name]);

    if ($select_products->rowCount() > 0) {
        $message[] = 'Product name already exists!';
    } else {
        $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, category, stock, image_01, image_02, image_03) VALUES(?,?,?,?,?,?,?,?)");
        $insert_products->execute([$name, $details, $price, $category, $stock, $image_01, $image_02, $image_03]);

        if ($insert_products) {
            if ($image_size_01 > 2000000 || $image_size_02 > 2000000 || $image_size_03 > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name_01, $image_folder_01);
                move_uploaded_file($image_tmp_name_02, $image_folder_02);
                move_uploaded_file($image_tmp_name_03, $image_folder_03);
                $message[] = 'New product added!';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $delete_product_image->execute([$delete_id]);
    $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
    unlink('../uploaded_img/' . $fetch_delete_image['image_01']);
    unlink('../uploaded_img/' . $fetch_delete_image['image_02']);
    unlink('../uploaded_img/' . $fetch_delete_image['image_03']);
    $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_product->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
    $delete_wishlist->execute([$delete_id]);
    header('location:products.php');
    exit; // Prevent further code execution after redirect
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

    <style>
        .products-table {
            width: 90%;
            margin: 20px auto;
            background: #333;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #444;
            font-size: 18px;
        }

        th {
            background-color: #444;
        }

        td {
            background-color: #555;
        }

        img.product-img {
            width: 100px;
            height: auto;
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

<section class="add-products">
    <h1 class="heading">Add Product</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <span>Product Name (Required)</span>
                <input type="text" class="box" required maxlength="100" placeholder="Enter Product Name" name="name">
            </div>
            <div class="inputBox">
                <span>Product Price (Required)</span>
                <input type="number" min="0" class="box" required max="9999999999" placeholder="Enter Product Price" onkeypress="if(this.value.length == 10) return false;" name="price">
            </div>
            <div class="inputBox">
                <span>Product Category (Required)</span>
                <select name="category" class="box" required>
                    <option value="">Select Category</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Console">Console</option>
                    <option value="Lifestyle">Lifestyle</option>
                    <option value="Headphones">Headphones</option>
                    <option value="Charger">Charger</option>
                    <option value="Accessories">Accessories</option>
                </select>
            </div>
            <div class="inputBox">
                <span>Available Stock (Required)</span>
                <input type="number" min="0" class="box" required placeholder="Enter Available Stock" name="stock">
            </div>
            <div class="inputBox">
                <span>Image 01 (Required)</span>
                <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Image 02 (Required)</span>
                <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Image 03 (Required)</span>
                <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Product Details (Required)</span>
                <textarea name="details" placeholder="Enter Product Details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
            </div>
        </div>
        <input type="submit" value="Add Product" class="btn" name="add_product">
    </form>
</section>

<section class="show-products">
    <h1 class="heading">Products Added</h1>
    <div class="products-table">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $select_products = $conn->prepare("SELECT * FROM `products`");
                $select_products->execute();
                if ($select_products->rowCount() > 0) {
                    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr>
    <td><img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="" class="product-img"></td>
    <td><?= htmlspecialchars($fetch_products['id']); ?></td>
    <td><?= htmlspecialchars($fetch_products['name']); ?></td>
    <td><?= htmlspecialchars($fetch_products['category']); ?></td>
    <td>$<?= htmlspecialchars($fetch_products['price']); ?></td>
    <td><?= htmlspecialchars($fetch_products['stock']); ?></td>
    <td><?= htmlspecialchars($fetch_products['details']); ?></td>
    <td>
        <a href="update_product.php?update=<?= htmlspecialchars($fetch_products['id']); ?>" class="option-btn">Update</a>
    </td>
</tr>

                        <?php
                    }
                } else {
                    echo '<tr><td colspan="8" style="text-align: center;">No Products Added Yet!</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
