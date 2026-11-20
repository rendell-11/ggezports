<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

function table_has_column(PDO $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = ?
         AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

function fetch_count(PDO $conn, string $query, array $params = []): int
{
    $stmt = $conn->prepare($query);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

$admin_profile_stmt = $conn->prepare("SELECT name, admin_role FROM `admins` WHERE id = ?");
$admin_profile_stmt->execute([$admin_id]);
$admin_profile = $admin_profile_stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Admin', 'admin_role' => 'super_admin'];

$users_has_created_at = table_has_column($conn, 'users', 'created_at');
$products_has_created_at = table_has_column($conn, 'products', 'created_at');

$labels = [];
$sales_data = [];

for ($i = 9; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $labels[] = date('m-d', strtotime($date));

    $query = $conn->prepare("SELECT COALESCE(SUM(total_price), 0) FROM `orders` WHERE DATE(placed_on) = ?");
    $query->execute([$date]);
    $sales_data[] = (float) $query->fetchColumn();
}

$returning_customers_stmt = $conn->prepare(
    "SELECT user_id FROM `orders`
     GROUP BY user_id
     HAVING COUNT(id) > 1"
);
$returning_customers_stmt->execute();
$returning_customers = $returning_customers_stmt->rowCount();

$daily_new_customers = 0;
$weekly_new_customers = 0;
$products_added = fetch_count($conn, "SELECT COUNT(*) FROM `products`");

if ($users_has_created_at) {
    $daily_new_customers = fetch_count($conn, "SELECT COUNT(*) FROM `users` WHERE DATE(created_at) = CURDATE()");
    $weekly_new_customers = fetch_count(
        $conn,
        "SELECT COUNT(*) FROM `users` WHERE DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()"
    );
}

if ($products_has_created_at) {
    $products_added = fetch_count(
        $conn,
        "SELECT COUNT(*) FROM `products` WHERE DATE(created_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()"
    );
}

$low_stock_stmt = $conn->prepare("SELECT id, name, stock FROM `products` WHERE stock > 0 AND stock <= 5 ORDER BY stock ASC, id DESC");
$low_stock_stmt->execute();
$low_stock_products = $low_stock_stmt->fetchAll(PDO::FETCH_ASSOC);

$out_of_stock_stmt = $conn->prepare("SELECT id, name, stock FROM `products` WHERE stock = 0 ORDER BY id DESC");
$out_of_stock_stmt->execute();
$out_of_stock_products = $out_of_stock_stmt->fetchAll(PDO::FETCH_ASSOC);

$newest_users_stmt = $conn->prepare("SELECT id, name, email FROM `users` ORDER BY id DESC LIMIT 5");
$newest_users_stmt->execute();
$newest_users = $newest_users_stmt->fetchAll(PDO::FETCH_ASSOC);

$most_viewed_stmt = $conn->prepare(
    "SELECT products.id, products.name, products.image_01, COUNT(*) AS views
     FROM (
         SELECT pid FROM cart
         UNION ALL
         SELECT pid FROM wishlist
     ) AS combined
     JOIN products ON combined.pid = products.id
     GROUP BY combined.pid, products.id, products.name, products.image_01
     ORDER BY views DESC, products.id DESC
     LIMIT 5"
);
$most_viewed_stmt->execute();
$most_viewed_products = $most_viewed_stmt->fetchAll(PDO::FETCH_ASSOC);

$best_selling_stmt = $conn->prepare(
    "SELECT p.id, p.name, p.image_01, COALESCE(SUM(CASE WHEN o.payment_status IN ('completed', 'paid') THEN o.total_products ELSE 0 END), 0) AS total_sales
     FROM `products` p
     LEFT JOIN `orders` o ON o.id = p.id
     GROUP BY p.id, p.name, p.image_01
     ORDER BY total_sales DESC, p.id DESC
     LIMIT 5"
);
$best_selling_stmt->execute();
$best_selling_products = $best_selling_stmt->fetchAll(PDO::FETCH_ASSOC);

$new_products_query = $products_has_created_at
    ? "SELECT id, name, image_01, created_at FROM `products` ORDER BY created_at DESC LIMIT 5"
    : "SELECT id, name, image_01, NULL AS created_at FROM `products` ORDER BY id DESC LIMIT 5";
$new_products_stmt = $conn->prepare($new_products_query);
$new_products_stmt->execute();
$new_products = $new_products_stmt->fetchAll(PDO::FETCH_ASSOC);

$metric_cards = [
    ['icon' => 'fa-cube', 'label' => 'Total Products', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `products`")],
    ['icon' => 'fa-cart-shopping', 'label' => 'Pending Orders', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `orders` WHERE payment_status = 'pending'")],
    ['icon' => 'fa-circle-check', 'label' => 'Completed Orders', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `orders` WHERE payment_status = 'completed'")],
    ['icon' => 'fa-circle-plus', 'label' => 'Products Added', 'value' => $products_added],
    ['icon' => 'fa-users', 'label' => 'Customers', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `users`")],
    ['icon' => 'fa-user-shield', 'label' => 'Admin Users', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `admins`")],
    ['icon' => 'fa-comments', 'label' => 'New Messages', 'value' => fetch_count($conn, "SELECT COUNT(*) FROM `messages`")],
    ['icon' => 'fa-rotate', 'label' => 'Returning Customers', 'value' => $returning_customers],
    ['icon' => 'fa-user-plus', 'label' => 'Daily New Customers', 'value' => $daily_new_customers],
    ['icon' => 'fa-briefcase', 'label' => 'Weekly New Customers', 'value' => $weekly_new_customers],
];

$low_stock_count = count($low_stock_products);
$out_of_stock_count = count($out_of_stock_products);
$inventory_is_healthy = $low_stock_count === 0 && $out_of_stock_count === 0;
$inventory_summary = $inventory_is_healthy
    ? 'Inventory looks healthy. No urgent stock issues were found.'
    : 'Some products are running low on stock. Please restock soon.';
$inventory_detail = $inventory_is_healthy
    ? 'No products currently require immediate inventory attention.'
    : "{$low_stock_count} low-stock item(s) and {$out_of_stock_count} out-of-stock item(s) need review.";

$display_date = date('M d, Y');
$admin_role_label = ucwords(str_replace('_', ' ', $admin_profile['admin_role']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<main class="dashboard-shell">
    <section class="dashboard-hero">
        <div class="dashboard-hero__copy">
            <h1 class="dashboard-hero__title">ADMIN <span>DASHBOARD</span></h1>
            <p class="dashboard-hero__subtitle">Overview of your store performance and activity</p>
        </div>

        <div class="dashboard-hero__meta">
            <div class="dashboard-badge">
                <i class="fa-regular fa-calendar-days"></i>
                <span><?= htmlspecialchars($display_date); ?></span>
            </div>

            <div class="dashboard-badge dashboard-badge--admin">
                <span class="dashboard-badge__avatar"><i class="fa-regular fa-user"></i></span>
                <span class="dashboard-badge__admin-text">
                    <strong><?= htmlspecialchars($admin_profile['name']); ?></strong>
                    <small><?= htmlspecialchars($admin_role_label); ?></small>
                </span>
            </div>
        </div>
    </section>

    <section class="dashboard-stats">
        <?php foreach ($metric_cards as $metric): ?>
            <article class="dashboard-stat">
                <div class="dashboard-stat__icon">
                    <i class="fas <?= htmlspecialchars($metric['icon']); ?>"></i>
                </div>
                <div class="dashboard-stat__value"><?= htmlspecialchars((string) $metric['value']); ?></div>
                <div class="dashboard-stat__label"><?= htmlspecialchars($metric['label']); ?></div>
                <span class="dashboard-stat__accent"></span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="dashboard-alert<?= $inventory_is_healthy ? ' is-safe' : ''; ?>">
        <div class="dashboard-alert__icon">
            <i class="fas <?= $inventory_is_healthy ? 'fa-circle-check' : 'fa-triangle-exclamation'; ?>"></i>
        </div>
        <div class="dashboard-alert__copy">
            <h2>Inventory Alerts</h2>
            <p><?= htmlspecialchars($inventory_summary); ?></p>
            <small><?= htmlspecialchars($inventory_detail); ?></small>
        </div>
        <div class="dashboard-alert__chevron">
            <i class="fas fa-chevron-right"></i>
        </div>
    </section>

    <section class="dashboard-panels dashboard-panels--two">
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <h2><i class="fas fa-bolt"></i> Newest Users</h2>
            </div>
            <div class="dashboard-panel__body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($newest_users): ?>
                            <?php foreach ($newest_users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $user['id']); ?></td>
                                    <td><?= htmlspecialchars($user['name']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="dashboard-table__empty">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="../admin/users_accounts.php" class="dashboard-panel__footer">View All Users <i class="fas fa-chevron-right"></i></a>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <h2><i class="fas fa-bullseye"></i> Most Viewed Products</h2>
            </div>
            <div class="dashboard-panel__body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Image</th>
                            <th>Views</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($most_viewed_products): ?>
                            <?php foreach ($most_viewed_products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $product['id']); ?></td>
                                    <td><?= htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <img src="../uploaded_img/<?= htmlspecialchars($product['image_01']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="dashboard-thumb">
                                    </td>
                                    <td><?= htmlspecialchars((string) $product['views']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="dashboard-table__empty">No product activity found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="../admin/products.php" class="dashboard-panel__footer">View All Products <i class="fas fa-chevron-right"></i></a>
        </article>
    </section>

    <section class="dashboard-panel dashboard-panel--chart">
        <div class="dashboard-panel__header">
            <h2><i class="fas fa-chart-line"></i> Sales Overview</h2>
            <span class="dashboard-panel__filter">Last 10 Days</span>
        </div>
        <div class="dashboard-panel__body dashboard-panel__body--chart">
            <canvas id="salesChart"></canvas>
        </div>
    </section>

    <section class="dashboard-panels dashboard-panels--two">
        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <h2><i class="fas fa-trophy"></i> Best Selling Products</h2>
            </div>
            <div class="dashboard-panel__body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($best_selling_products): ?>
                            <?php foreach ($best_selling_products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $product['id']); ?></td>
                                    <td><?= htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <img src="../uploaded_img/<?= htmlspecialchars($product['image_01']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="dashboard-thumb">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="dashboard-table__empty">No best seller data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="../admin/products_reports.php" class="dashboard-panel__footer">View All Best Sellers <i class="fas fa-chevron-right"></i></a>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel__header">
                <h2><i class="fas fa-box-open"></i> New Products</h2>
            </div>
            <div class="dashboard-panel__body">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Image</th>
                            <th>Added On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($new_products): ?>
                            <?php foreach ($new_products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) $product['id']); ?></td>
                                    <td><?= htmlspecialchars($product['name']); ?></td>
                                    <td>
                                        <img src="../uploaded_img/<?= htmlspecialchars($product['image_01']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="dashboard-thumb">
                                    </td>
                                    <td>
                                        <?= $product['created_at'] ? htmlspecialchars(date('Y-m-d H:i', strtotime($product['created_at']))) : 'N/A'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="dashboard-table__empty">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a href="../admin/products.php" class="dashboard-panel__footer">View All New Products <i class="fas fa-chevron-right"></i></a>
        </article>
    </section>
</main>

<script>
    const salesCtx = document.getElementById('salesChart').getContext('2d');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels); ?>,
            datasets: [{
                label: 'Total Sales ($)',
                data: <?= json_encode($sales_data); ?>,
                borderColor: '#ff2c3c',
                backgroundColor: 'rgba(255, 44, 60, 0.15)',
                pointBackgroundColor: '#ff2c3c',
                pointBorderColor: '#ff2c3c',
                pointRadius: 2.5,
                pointHoverRadius: 4,
                borderWidth: 2,
                fill: false,
                tension: 0.35
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'center',
                    labels: {
                        color: '#d7d7d7',
                        boxWidth: 14,
                        boxHeight: 6,
                        usePointStyle: false
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255,255,255,0.06)'
                    },
                    ticks: {
                        color: '#909090'
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255,255,255,0.06)'
                    },
                    ticks: {
                        color: '#909090'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
