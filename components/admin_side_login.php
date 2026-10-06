<?php
function renderSideNav() {
    ?>
    
    <div id="sideNav" class="sidenav">
    <header class="header">
        <section class="flex">
            <a href="../admin/admin_login.php" class="logo" style="bottom: 100px;">GGadmin<span>Panel</span></a>
        </section>
    </header>

    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="../admin/admin_login.php" class="dashboard"><i class="fas fa-home"></i> Dashboard</a>
    <a><i class="fas fa-shopping-cart"></i> Orders</a>
    <a><i class="fas fa-users-cog"></i> Admins</a>
    <a><i class="fas fa-users"></i> Users</a>

    <!-- Messages Dropdown -->
    <a href="javascript:void(0)" onclick="toggleMessagesDropdown()" class="dropdown-btn">
        <i class="fas fa-comments"></i> Messages <i class="fas fa-caret-down" style="margin-left: auto;"></i>
    </a>
    <div id="messagesDropdown" class="dropdown-container" style="display: none;">
        <a><i class="fas fa-inbox"></i> Feedback</a>
        <a><i class="fas fa-star"></i> User Rating</a>
    </div>

    <!-- Category Dropdown -->
    <a href="javascript:void(0)" onclick="toggleCategoryDropdown()" class="dropdown-btn">
        <i class="fas fa-tags"></i> Category <i class="fas fa-caret-down" style="margin-left: auto;"></i>
    </a>
    <div id="categoryDropdown" class="dropdown-container" style="display: none;">
        <a><i class="fas fa-plus"></i> Add Products</a>
        <a><i class="fas fa-edit"></i> Manage Categories</a>
    </div>

    <!-- Delivery Management Dropdown -->
    <a href="javascript:void(0)" onclick="toggleDeliveryDropdown()" class="dropdown-btn">
        <i class="fas fa-shipping-fast"></i> Delivery Management <i class="fas fa-caret-down" style="margin-left: auto;"></i>
    </a>
    <div id="deliveryDropdown" class="dropdown-container" style="display: none;">
        <a><i class="fas fa-tasks"></i> Order Status</a>
        <a><i class="fas fa-truck"></i> Delivery Management</a>
        <a><i class="fas fa-user-plus"></i> Create Courier Account</a>
    </div>

    <a><i class="fas fa-chart-line"></i> Products Reports</a>
    <a><i class="fas fa-user-edit"></i> Update Profile</a>
</div>

<style>
    .sidenav {
        height: 100%;
        width: 250px; /* Set the width of the side navigation */
        position: fixed; /* Stay in place */
        z-index: 1; /* Stay on top */
        top: 0;
        left: -250px; /* Hide the side nav off-screen */
        background-color: #111; /* Black background color */
        overflow-x: hidden; /* Disable horizontal scroll */
        transition: 0.5s; /* Transition effect */
        padding-top: 0px; /* Adjust padding for space at the top */
    }

    .sidenav a {
        padding: 8px 8px 8px 32px; /* Padding for links */
        text-decoration: none; /* Remove underline */
        font-size: 20px; /* Smaller font size */
        color: grey; /* Set text color to grey */
        display: flex; /* Make the links appear as flex elements */
        align-items: center; /* Center items vertically */
        transition: 0.3s; /* Transition effect */
    }

    .sidenav a i {
        margin-right: 10px; /* Space between icon and text */
        font-size: 24px; /* Icon size */
        color: grey; /* Set icon color to grey */
    }

    
    /* Specific style for the Dashboard link */
    .sidenav a.dashboard {
        color: white; /* Set Dashboard link color to white */
    }

    /* Keep icon color white for Dashboard */
    .sidenav a.dashboard i {
        color: white; /* Set icon color for Dashboard link */
    }

    .closebtn {
        position: absolute; /* Position the close button */
        right: 25px; /* Place the close button to the right */
        font-size: 36px; /* Font size for the close button */
        margin-left: 50px; /* Margin on the left */
    }

    .dropdown-btn {
        cursor: pointer;
    }

    .dropdown-container {
        background-color: #333; /* Darker background for dropdown links */
        padding-left: 20px; /* Indent dropdown links */
    }

    .dropdown-container a {
        font-size: 18px; /* Smaller font size for dropdown links */
        padding: 6px 8px 6px 32px;
    }
</style>


    <script>
        // Function to open the side navigation
        function openNav() {
            document.getElementById("sideNav").style.left = "0"; // Show the side nav
            document.getElementById("openBtn").style.display = "none"; // Hide the open button
        }

        // Function to close the side navigation
        function closeNav() {
            document.getElementById("sideNav").style.left = "-250px"; // Hide the side nav
            document.getElementById("openBtn").style.display = "block"; // Show the open button
        }

        // Function to toggle the messages dropdown
        function toggleMessagesDropdown() {
            var dropdown = document.getElementById("messagesDropdown");
            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
        }

        // Function to toggle the category dropdown
        function toggleCategoryDropdown() {
            var dropdown = document.getElementById("categoryDropdown");
            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
        }

        // Function to toggle the delivery dropdown
        function toggleDeliveryDropdown() {
            var dropdown = document.getElementById("deliveryDropdown");
            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
        }

        // Automatically open the side navigation when the page loads
        window.onload = function() {
            openNav(); // Opens the side nav initially
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-k6RqeWeci5ZR/Lv4MR0sA0FfDOM57wtqR3ErN47B3H2v09qU7N4bb5G8h14fS4" crossorigin="anonymous">
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Side Navigation Menu</title>
</head>
<body>

<?php renderSideNav(); // Call the side navigation menu function ?>

</body>
</html>
