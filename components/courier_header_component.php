<!-- Sidebar Section -->
<div class="sidebar">
    <h2>Courier Dashboard</h2>
    <ul>
        <li><a href="courier.php">Profile</a></li>
        <li><a href="courier_deliveries.php">Available Deliveries</a></li>
        <li><a href="my_deliveries.php">My Deliveries</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>

    <!-- Logout Button -->
    <div class="logout-container">
        <a href="login.php" class="logout-button">Logout</a>
    </div>

    <!-- Toggle Switch for Dark Mode -->
    <div class="toggle-container">
        <label class="toggle">
            <input type="checkbox" id="mode-toggle" onchange="toggleMode()">
            <span class="slider"></span>
        </label>
        <span class="ms-2"> Dark Mode</span>
    </div>
</div>

<!-- Add some CSS for the logout button -->
<style>
    .logout-container {
        margin-top: auto; /* Pushes the logout button to the bottom */
    }

    .logout-button {
        display: block;
        padding: 10px;
        text-align: center;
        background-color: #dc3545; /* Bootstrap danger color */
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 0; /* Adds space around the button */
    }

    .logout-button:hover {
        background-color: #c82333; /* Darker red on hover */
    }
</style>
