<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit; // Prevent further code execution after redirect
}

// Insert New Courier Logic
if (isset($_POST['add_courier'])) {
    $driver_name = $_POST['driver_name'];
    $driver_name = filter_var($driver_name, FILTER_UNSAFE_RAW);
    $driver_contact = $_POST['driver_contact'];
    $driver_contact = filter_var($driver_contact, FILTER_UNSAFE_RAW);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = sha1($_POST['password']); // Hash the password
    $password = filter_var($password, FILTER_UNSAFE_RAW);
    $status = "active"; // Set default status to active

    $driver_image = $_FILES['driver_image']['name'];
    $image_size = $_FILES['driver_image']['size'];
    $image_tmp_name = $_FILES['driver_image']['tmp_name'];
    $image_folder = '../uploaded_img/' . $driver_image;

    // Validate image size
    if ($image_size > 2000000) {
        $message[] = 'Image size is too large!';
    } else {
        // Insert new courier into the database
        $insert_courier = $conn->prepare("INSERT INTO `courier` (driver_name, driver_contact, email, password, status, driver_image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($insert_courier->execute([$driver_name, $driver_contact, $email, $password, $status, $driver_image])) {
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Courier account created successfully!';
        } else {
            $message[] = 'Failed to create courier account!';
        }
    }
}

// Update Courier Account Logic
if (isset($_POST['update_courier'])) {
    $courier_id = $_POST['courier_id'];
    $driver_name = $_POST['driver_name'];
    $driver_name = filter_var($driver_name, FILTER_UNSAFE_RAW);
    $driver_contact = $_POST['driver_contact'];
    $driver_contact = filter_var($driver_contact, FILTER_UNSAFE_RAW);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $status = $_POST['status'];
    $status = filter_var($status, FFILTER_UNSAFE_RAW);

    $driver_image = $_FILES['driver_image']['name'];
    $image_size = $_FILES['driver_image']['size'];
    $image_tmp_name = $_FILES['driver_image']['tmp_name'];
    $image_folder = '../uploaded_img/' . $driver_image;

    // Prepare the SQL statement
    $update_courier = $conn->prepare("UPDATE `courier` SET driver_name = ?, driver_contact = ?, email = ?, status = ? WHERE id = ?");
    
    // Execute the update query
    if ($update_courier->execute([$driver_name, $driver_contact, $email, $status, $courier_id])) {
        if (!empty($driver_image)) { // Check if a new image is uploaded
            if ($image_size > 2000000) {
                $message[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                // Update the driver image in the database
                $update_image = $conn->prepare("UPDATE `courier` SET driver_image = ? WHERE id = ?");
                $update_image->execute([$driver_image, $courier_id]);
                $message[] = 'Courier account updated successfully!';
            }
        } else {
            $message[] = 'Courier account updated successfully!';
        }
    } else {
        $message[] = 'Failed to update courier account!';
    }
}

// Fetch Courier Accounts
$couriers = $conn->query("SELECT * FROM `courier`")->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Courier Account</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

  <style>
    .courier-form, .products-table {
        width: 90%;
        margin: 20px auto;
        background: #333;
        padding: 20px;
        border-radius: 10px;
        color: #fff;
    }

    .inputBox {
        margin: 15px 0;
    }

    .inputBox span {
        display: block;
        margin-bottom: 5px;
    }

    .inputBox input,
    .inputBox select {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 5px;
        background: #444;
        color: #fff;
    }

    .btn {
        background: #28a745;
        color: #fff;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn:hover {
        background: #218838;
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
        color: #fff; /* Change text color to white */
    }

    th {
        background-color: #444;
    }

    td {
        background-color: #555;
    }

    img.courier-img {
        width: 50px;
        height: auto;
    }

    .delete-btn {
        padding: 10px 15px;
        color: #fff;
        text-decoration: none;
        background-color: #dc3545;
        border-radius: 5px;
        transition: 0.3s;
    }

    .delete-btn:hover {
        opacity: 0.8;
    }

    .update-form {
        display: none;
        background: #444;
        padding: 15px;
        border-radius: 5px;
        margin-top: 10px;
    }

    .update-form .inputBox {
        margin: 10px 0;
    }

    .update-form .inputBox input {
        padding: 8px;
    }

    .update-form .btn {
        background: #007bff; /* Different color for update */
    }

    .update-form .btn:hover {
        background: #0056b3; /* Darker shade for hover */
    }
</style>

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="courier-form">
    <h1 class="heading">Create Courier Account</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <span>Driver Name (Required)</span>
                <input type="text" class="box" required maxlength="100" placeholder="Enter Driver Name" name="driver_name">
            </div>
            <div class="inputBox">
                <span>Driver Contact (Required)</span>
                <input type="text" class="box" required maxlength="15" placeholder="Enter Driver Contact" name="driver_contact">
            </div>
            <div class="inputBox">
                <span>Email (Required)</span>
                <input type="email" class="box" required placeholder="Enter Email" name="email">
            </div>
            <div class="inputBox">
                <span>Password (Required)</span>
                <input type="password" class="box" required placeholder="Enter Password" name="password">
            </div>
            <div class="inputBox">
                <span>Driver Image (Required)</span>
                <input type="file" name="driver_image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
        </div>
        <input type="submit" value="Create Courier" class="btn" name="add_courier">
    </form>
</section>

<section class="show-products">
    <h1 class="heading">Accounts Created</h1>
    <div class="products-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($couriers)): ?>
                    <?php foreach ($couriers as $courier): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($courier['id']); ?></td>
                            <td><?php echo htmlspecialchars($courier['driver_name']); ?></td>
                            <td><?php echo htmlspecialchars($courier['driver_contact']); ?></td>
                            <td><?php echo htmlspecialchars($courier['email']); ?></td>
                            <td><?php echo htmlspecialchars($courier['status']); ?></td>
                            <td>
                                <img src="../uploaded_img/<?php echo htmlspecialchars($courier['driver_image']); ?>" alt="Courier Image" class="courier-img">
                            </td>
                            <td>
                                <a href="delete_courier.php?id=<?php echo $courier['id']; ?>" class="delete-btn" onclick="return confirm('Delete This Courier Account?');">Delete</a>
                                <a href="#update-courier-<?php echo $courier['id']; ?>" class="btn" onclick="event.preventDefault(); document.getElementById('update-form-<?php echo $courier['id']; ?>').style.display='block';">Update</a>
                            </td>
                        </tr>

                        <!-- Update Form -->
                        <tr id="update-form-<?php echo $courier['id']; ?>" class="update-form">
                            <td colspan="7">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="courier_id" value="<?php echo $courier['id']; ?>">
                                    <div class="inputBox">
                                        <span>Driver Name</span>
                                        <input type="text" class="box" name="driver_name" value="<?php echo htmlspecialchars($courier['driver_name']); ?>" required>
                                    </div>
                                    <div class="inputBox">
                                        <span>Driver Contact</span>
                                        <input type="text" class="box" name="driver_contact" value="<?php echo htmlspecialchars($courier['driver_contact']); ?>" required>
                                    </div>
                                    <div class="inputBox">
                                        <span>Email</span>
                                        <input type="email" class="box" name="email" value="<?php echo htmlspecialchars($courier['email']); ?>" required>
                                    </div>
                                    <div class="inputBox">
                                        <span>Status</span>
                                        <input type="text" class="box" name="status" value="<?php echo htmlspecialchars($courier['status']); ?>" required>
                                    </div>
                                    <div class="inputBox">
                                        <span>Driver Image (Optional)</span>
                                        <input type="file" name="driver_image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
                                    </div>
                                    <input type="submit" value="Update Courier" class="btn" name="update_courier">
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No Courier Accounts Created Yet!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
