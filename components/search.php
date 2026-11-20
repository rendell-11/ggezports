<?php
include '../components/connect.php';
session_start();

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query) {
    // Store all the results
    $results = [];

    // Array of tables and columns to search through
    // Each table is also linked to a specific page
    // $searchableTables = [
    //     'admins' => ['name', 'admins_accounts.php'],
    //     'messages' => ['name', 'email', 'message', 'messages.php'],
    //     'orders' => ['name', 'email', 'address', 'placed_orders.php'],
    //     'products' => ['name', 'details', 'products.php'],
    //     'users' => ['name', 'email', 'user_accounts.php']
    // ];

    // Mapping specific search terms to their respective admin pages
    $pageRedirects = [
        'admin login' => '../admin/admin_login.php',
        'login' => '../admin/admin_login.php',
        'dashboard' => '../admin/dashboard.php',
        'Dashboard' => '../admin/dashboard.php',
        'home' => '../admin/dashboard.php',
        'messages' => '../admin/messages.php',
        'placed orders' => '../admin/placed_orders.php',
        'orders' => '../admin/placed_orders.php',
        'products reports' => '../admin/products_reports.php',
        'products' => '../admin/products.php',
        'reports' => '../admin/products_reports.php',
        'report' => '../admin/products_reports.php',
        'admins' => '../admin/register_admin.php',
        'register' => '../admin/register_admin.php',
        'update product' => '../admin/update_product.php',
        'update profile' => '../admin/update_profile.php',
        'user accounts' => '../admin/users_accounts.php',
        'users' => '../admin/users_accounts.php',
        'admins account' => '../admin/admins_accounts.php',
        'update' => '../admin/update_profile.php',
        'admins update' => '../admin/update_profile.php'
    ];

    // Check if the query matches any predefined terms
    $lowercaseQuery = strtolower($query);
    if (array_key_exists($lowercaseQuery, $pageRedirects)) {
        header("Location: " . $pageRedirects[$lowercaseQuery]);
        exit; // Stop further execution
    }

    // Search through tables
    // foreach ($searchableTables as $table => $columns) {
    //     // The last element in the array is the page link
    //     $pageLink = array_pop($columns);

    //     foreach ($columns as $column) {
    //         $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$column` LIKE ?");
    //         $stmt->execute(['%' . $query . '%']);
    //         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //             $results[] = [
    //                 'table' => $table,
    //                 'data' => $row,
    //                 'page' => $pageLink
    //             ];
    //         }
    //     }
    // }

    // Display the results
    if (empty($results)) {
        echo "<p>No results found for '<b>" . htmlspecialchars($query) . "</b>'</p>";
    } else {
        echo "<h2>Search Results for '<b>" . htmlspecialchars($query) . "</b>':</h2>";
        foreach ($results as $result) {
            echo "<div class='result-item'>";
            echo "<h3>Table: " . htmlspecialchars($result['table']) . "</h3>";
            foreach ($result['data'] as $key => $value) {
                echo "<p><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</p>";
            }
            echo "<a href='" . htmlspecialchars($result['page']) . "'>View related page</a>";
            echo "</div><hr>";
        }
    }
} else {
    echo "<p>Please enter a search term.</p>";
}
?>
