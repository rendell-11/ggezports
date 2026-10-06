<?php
include 'components/connect.php'; 
session_start();

// Check user session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('location:user_login.php');
    exit();
}

// Add address
if (isset($_POST['add_address'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $province = filter_var($_POST['province'], FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $municipality = filter_var($_POST['municipality'], FILTER_SANITIZE_STRING);
    $barangay = filter_var($_POST['barangay'], FILTER_SANITIZE_STRING);
    $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
    $postal_code = filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING);

    $query = "INSERT INTO user_addresses (user_id, name, number, email, province, city, municipality, barangay, country, postal_code) 
              VALUES (:user_id, :name, :number, :email, :province, :city, :municipality, :barangay, :country, :postal_code)";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':name' => $name,
        ':number' => $number,
        ':email' => $email,
        ':province' => $province,
        ':city' => $city,
        ':municipality' => $municipality,
        ':barangay' => $barangay,
        ':country' => $country,
        ':postal_code' => $postal_code
    ]);

    // Redirect to avoid form resubmission on refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Delete address
if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':id' => $id,
        ':user_id' => $user_id
    ]);
}

// Fetch addresses
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update user profile
if (isset($_POST['submit'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $user_id]);

    $prev_pass = $_POST['prev_pass'];
    $old_pass = sha1($_POST['old_pass']);
    $new_pass = sha1($_POST['new_pass']);
    $cpass = sha1($_POST['cpass']);

    if (!empty($_POST['old_pass']) && $old_pass !== $prev_pass) {
        $message[] = 'Old password does not match!';
    } elseif ($new_pass !== $cpass) {
        $message[] = 'New password and confirm password do not match!';
    } elseif (!empty($_POST['new_pass']) && $old_pass === $prev_pass) {
        $update_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_password->execute([$new_pass, $user_id]);
        $message[] = 'Password updated successfully!';
    }
}


// Handle address update
if (isset($_POST['update_address'])) {
    $id = $_POST['id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $province = filter_var($_POST['province'], FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $municipality = filter_var($_POST['municipality'], FILTER_SANITIZE_STRING);
    $barangay = filter_var($_POST['barangay'], FILTER_SANITIZE_STRING);
    $country = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
    $postal_code = filter_var($_POST['postal_code'], FILTER_SANITIZE_STRING);

    $query = "UPDATE user_addresses SET name = :name, number = :number, email = :email, province = :province, city = :city, 
              municipality = :municipality, barangay = :barangay, country = :country, postal_code = :postal_code WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':number' => $number,
        ':email' => $email,
        ':province' => $province,
        ':city' => $city,
        ':municipality' => $municipality,
        ':barangay' => $barangay,
        ':country' => $country,
        ':postal_code' => $postal_code
    ]);

    // Redirect to avoid form resubmission on refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile & Addresses</title>

    <!-- Font Awesome & Custom Styles -->
    <link rel="stylesheet" href="css/address.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
       /* Popup Form Styles */
       .popup-form {
            display: none; /* Initially hidden */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Overlay effect */
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-form form {
            background-color: #f2f2f2; /* Same as 'Add New Address' background */
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 100%;
        }

        .popup-form input[type="text"],
        .popup-form input[type="email"],
        .popup-form input[type="password"] {
            background-color: #f5f5f5; /* Light grey background for textboxes */
            border: 1px solid #ccc;
            color: black;
            padding: 10px;
            margin: 10px 0;
            width: calc(50% - 20px); /* Adjust width for a two-column design */
        }

        .popup-form .form-grid {
            display: flex;
            gap: 20px;
        }

        .popup-form .form-column {
            flex: 1;
        }

        .popup-form .btn {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .popup-form .btn:hover {
            background-color: #218838;
        }

        .popup-form .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
            margin-top: 10px;
            width: 100%;
        }
/* Address Buttons Layout */
.address-buttons {
    display: flex;
    justify-content: space-between; /* Position buttons at left and right */
    gap: 10px; /* Space between buttons */
}

.update-btn {
    background-color: #28a745; /* Green background */
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 4px;
    width: auto;
}

.update-btn:hover {
    background-color: #218838; /* Darker green on hover */
}

.delete-btn {
    background-color: red;
    color: white;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 4px;
    width: auto;
    text-align: center;
    display: inline-block;
}

.delete-btn:hover {
    background-color: #c82333; /* Darker red on hover */
}
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>
<!-- Update Profile Form -->
<section class="form-container">
    <form action="" method="post">
        <h3>Update Profile</h3>
        <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">
        <input type="text" name="name" placeholder="Enter your name" required class="box" value="<?= $fetch_profile['name']; ?>">
        <input type="email" name="email" placeholder="Enter your email" required class="box" value="<?= $fetch_profile['email']; ?>">
        <input type="password" name="old_pass" placeholder="Enter your old password" class="box">
        <input type="password" name="new_pass" placeholder="Enter your new password" class="box">
        <input type="password" name="cpass" placeholder="Confirm your new password" class="box">
        <input type="submit" value="Update Now" class="btn" name="submit">
    </form>
</section>

<!-- Add New Address Form -->
<section class="form-container">
    <form action="" method="POST">
        <h3>Add New Address</h3>
        <div class="form-grid">
            <div class="form-column">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="text" name="number" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="province" placeholder="Province" required>
            </div>
            <div class="form-column">
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="municipality" placeholder="Municipality" required>
                <input type="text" name="barangay" placeholder="Barangay" required>
                <input type="text" name="country" placeholder="Country" required>
            </div>
        </div>
        <input type="text" name="postal_code" placeholder="Postal Code" required class="full-width">
        <input type="submit" value="Add Address" name="add_address" class="btn">
    </form>
</section>

<!-- Display Addresses -->
<section class="address-list">
    <h3>Your Addresses</h3>
    <div class="address-container">
        <?php foreach ($addresses as $address) { ?>
            <div class="address-box">
                <p><strong>Name:</strong> <?= htmlspecialchars($address['name']) ?></p>
                <p><strong>Number:</strong> <?= htmlspecialchars($address['number']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($address['email']) ?></p>
                <p><strong>Province:</strong> <?= htmlspecialchars($address['province']) ?></p>
                <p><strong>City:</strong> <?= htmlspecialchars($address['city']) ?></p>
                <p><strong>Municipality:</strong> <?= htmlspecialchars($address['municipality']) ?></p>
                <p><strong>Barangay:</strong> <?= htmlspecialchars($address['barangay']) ?></p>
                <p><strong>Country:</strong> <?= htmlspecialchars($address['country']) ?></p>
                <p><strong>Postal Code:</strong> <?= htmlspecialchars($address['postal_code']) ?></p>
                <div class="address-buttons">
                    <button type="button" class="update-btn" onclick="openUpdateForm('<?= $address['id'] ?>', '<?= addslashes($address['name']) ?>', '<?= addslashes($address['number']) ?>', '<?= addslashes($address['email']) ?>', '<?= addslashes($address['province']) ?>', '<?= addslashes($address['city']) ?>', '<?= addslashes($address['municipality']) ?>', '<?= addslashes($address['barangay']) ?>', '<?= addslashes($address['country']) ?>', '<?= addslashes($address['postal_code']) ?>')">Update</button>
                    <a href="address_editor.php?delete=<?= $address['id'] ?>" class="delete-btn" onclick="return confirm('Delete this address?')">Remove</a>
                </div>
            </div>
        <?php } ?>
    </div>
</section>


<!-- Popup Update Form -->
<div class="popup-form" id="updateAddressPopup">
    <form action="" method="POST">
        <h3>Update Address</h3>
        <input type="hidden" name="id" id="addressId">
        <input type="text" name="name" id="addressName" placeholder="Full Name" required>
        <input type="text" name="number" id="addressNumber" placeholder="Phone Number" required>
        <input type="email" name="email" id="addressEmail" placeholder="Email" required>
        <input type="text" name="province" id="addressProvince" placeholder="Province" required>
        <input type="text" name="city" id="addressCity" placeholder="City" required>
        <input type="text" name="municipality" id="addressMunicipality" placeholder="Municipality" required>
        <input type="text" name="barangay" id="addressBarangay" placeholder="Barangay" required>
        <input type="text" name="country" id="addressCountry" placeholder="Country" required>
        <input type="text" name="postal_code" id="addressPostalCode" placeholder="Postal Code" required>
        <input type="submit" value="Update Address" name="update_address" class="btn">
        <button type="button" class="close-btn" onclick="closeUpdateForm()">Close</button>
    </form>
</div>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
// Open the update form in a modal
function openUpdateForm(id, name, number, email, province, city, municipality, barangay, country, postal_code) {
    document.getElementById('updateAddressPopup').style.display = 'flex';
    document.getElementById('addressId').value = id;
    document.getElementById('addressName').value = name;
    document.getElementById('addressNumber').value = number;
    document.getElementById('addressEmail').value = email;
    document.getElementById('addressProvince').value = province;
    document.getElementById('addressCity').value = city;
    document.getElementById('addressMunicipality').value = municipality;
    document.getElementById('addressBarangay').value = barangay;
    document.getElementById('addressCountry').value = country;
    document.getElementById('addressPostalCode').value = postal_code;
}

// Close the update form
function closeUpdateForm() {
    document.getElementById('updateAddressPopup').style.display = 'none';
}
</script>

</body>
</html>
