<?php

session_start();
include '../components/connect.php';

if (isset($_POST['submit'])) {

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $role = $_POST['role']; // Get the selected role

    $update_profile = $conn->prepare("UPDATE `admins` SET name = ?, admin_role = ? WHERE id = ?");
    $update_profile->execute([$name, $role, $admin_id]);

    $empty_pass = 'da39a3ee5e6b4b0d3255bfef95601890afd80709'; // Empty hash for comparison
    $prev_pass = $fetch_profile['password']; // Fetch previous hashed password
    $old_pass = sha1($_POST['old_pass']);
    $old_pass = filter_var($old_pass, FILTER_SANITIZE_STRING);
    $new_pass = sha1($_POST['new_pass']);
    $new_pass = filter_var($new_pass, FILTER_SANITIZE_STRING);
    $confirm_pass = sha1($_POST['confirm_pass']);
    $confirm_pass = filter_var($confirm_pass, FILTER_SANITIZE_STRING);

    if ($old_pass == $empty_pass) {
        $message[] = 'Please Enter Old Password!';
    } elseif ($old_pass != $prev_pass) {
        $message[] = 'Old Password Not Matched!';
    } elseif ($new_pass != $confirm_pass) {
        $message[] = 'Confirm Password Not Matched!';
    } else {
        if ($new_pass != $empty_pass) {
            $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
            $update_admin_pass->execute([$new_pass, $admin_id]); // Update with new hashed password
            $message[] = 'Password Updated Successfully!';
        } else {
            $message[] = 'Please Enter A New Password!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="../css/adminstyle.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">
   <form action="" method="post">
      <h3>Update Profile</h3>
      <input type="hidden" name="prev_pass" value="<?= isset($fetch_profile['password']) ? $fetch_profile['password'] : ''; ?>">
      <input type="text" name="name" value="<?= isset($fetch_profile['name']) ? $fetch_profile['name'] : ''; ?>" required placeholder="Enter Your Username" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      
      <!-- Role Selection -->
      <select name="role" class="box" required>
         <option value="super_admin" <?= isset($fetch_profile['admin_role']) && $fetch_profile['admin_role'] == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
         <option value="order_admin" <?= isset($fetch_profile['admin_role']) && $fetch_profile['admin_role'] == 'order_admin' ? 'selected' : ''; ?>>Order Admin</option>
         <option value="delivery_admin" <?= isset($fetch_profile['admin_role']) && $fetch_profile['admin_role'] == 'delivery_admin' ? 'selected' : ''; ?>>Delivery Admin</option>
         <option value="reports_admin" <?= isset($fetch_profile['admin_role']) && $fetch_profile['admin_role'] == 'reports_admin' ? 'selected' : ''; ?>>Reports Admin</option>
      </select>

      <input type="password" name="old_pass" placeholder="Enter Old Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="Enter New Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_pass" placeholder="Confirm New Password" maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="update now" class="btn" name="submit">
   </form>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
