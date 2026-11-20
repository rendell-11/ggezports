<?php
include '../components/connect.php';
session_start();

// Check if the courier is logged in
if (!isset($_SESSION['courier_id'])) {
    die("You are not logged in.");
}

// Get the courier ID from the session
$courier_id = $_SESSION['courier_id']; 

// Fetch courier details for displaying current information
$query = $conn->prepare("SELECT * FROM courier WHERE id = ?");
$query->execute([$courier_id]);
$courier = $query->fetch(PDO::FETCH_ASSOC);

// Check if courier data was found
if (!$courier) {
    die("Courier with ID {$courier_id} not found.");
}

// Handle form submission for personal info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personal_info'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Update courier personal information
    $update_query = $conn->prepare("UPDATE courier SET driver_name = ?, email = ?, driver_contact = ? WHERE id = ?");
    $update_query->execute([$name, $email, $phone, $courier_id]);

    // Optionally, set a success message
    $success_message = "Personal information updated successfully!";
}

// Handle form submission for password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    // Verify current password
    if (password_verify($current_password, $courier['password'])) {
        // Check if new password and confirm password match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            // Update password in database
            $update_password_query = $conn->prepare("UPDATE courier SET password = ? WHERE id = ?");
            $update_password_query->execute([$hashed_password, $courier_id]);

            // Optionally, set a success message
            $success_message_password = "Password updated successfully!";
        } else {
            $error_message_password = "New password and confirm password do not match.";
        }
    } else {
        $error_message_password = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courier Dashboard - Settings</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/setting.css">
    <script src="../js/darkmode.js"></script>
</head>

<body>
<?php include '../components/courier_header_component.php'; ?>

    <!-- Main Content Section -->
    <div class="main-content">
        <div class="settings-container">
            <div class="settings-card">
                <div class="settings-header">
                    <h1>Settings</h1>
                </div>

                <!-- Display success/error messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message_password)): ?>
                    <div class="alert alert-danger"><?php echo $error_message_password; ?></div>
                <?php endif; ?>
                <?php if (isset($success_message_password)): ?>
                    <div class="alert alert-success"><?php echo $success_message_password; ?></div>
                <?php endif; ?>

                <!-- Personal Information Section -->
                <div class="personal-info-section mt-4">
                    <h2>Personal Information</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($courier['driver_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($courier['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($courier['driver_contact']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update_personal_info">Update Personal Info</button>
                    </form>
                </div>

                <!-- Security Settings Section -->
                <div class="security-settings-section mt-5">
                    <h2>Security Settings</h2>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current-password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current-password" name="current-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new-password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new-password" name="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm-password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- JavaScript for Dark Mode Toggle -->
</body>
</html>
