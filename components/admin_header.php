<?php
include_once 'connect.php';


// Define $admin_id from session
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;

// Display any messages if available
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($msg) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<?php include '../components/admin_side_navigation.php'; ?>

<header class="header">

    <section class="flex">
        <form action="../components/search.php" method="GET" class="search-form">
            <input type="text" name="query" placeholder="Search admin pages..." autocomplete="off" required>
            <button type="submit" class="fas fa-search"></button>
        </form>

        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="profile">
            <?php
            if ($admin_id) {
                // Use prepared statement with PDO
                $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
                $select_profile->execute([$admin_id]);
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

                if ($fetch_profile) {
                    echo '<p>' . htmlspecialchars($fetch_profile['name']) . '</p>';
                } else {
                    echo '<p>Admin not found</p>';
                }
            } else {
                echo '<p>Please log in</p>';
            }
            ?>
            
            <div class="flex-btn">
                <a href="../admin/register_admin.php" class="option-btn">register</a>
                <a href="../admin/admin_login.php" class="option-btn">login</a>
            </div>
            
        </div>

    </section>

</header>

<style>
    .header .flex {
        justify-content: flex-end;
    }

    .search-form {
        margin-left: auto;
    }
</style>
