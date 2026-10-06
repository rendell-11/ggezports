<?php

include 'components/connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

include 'components/wishlist_cart.php';

$category_options = [
    'Laptop',
    'Console',
    'Lifestyle',
    'Headphones',
    'Charger',
    'Accessories',
];

function shop_category_icon(string $category): string
{
    $icons = [
        'Laptop' => 'fas fa-laptop',
        'Console' => 'fas fa-gamepad',
        'Lifestyle' => 'fas fa-shirt',
        'Headphones' => 'fas fa-headphones-alt',
        'Charger' => 'fas fa-bolt',
        'Accessories' => 'fas fa-box-open',
    ];

    return $icons[$category] ?? 'fas fa-tag';
}

function shop_category_badge(string $category): string
{
    $badges = [
        'Laptop' => '240Hz',
        'Console' => 'Plug & Play',
        'Lifestyle' => 'Daily Gear',
        'Headphones' => '7.1 Sound',
        'Charger' => 'Fast Charge',
        'Accessories' => 'Pro Kit',
    ];

    return $badges[$category] ?? 'Featured';
}

function shop_category_summary(string $category): string
{
    $summaries = [
        'Laptop' => 'High-performance gaming machine',
        'Console' => 'Ready for instant play sessions',
        'Lifestyle' => 'Gaming-inspired everyday carry',
        'Headphones' => 'Immersive audio for every match',
        'Charger' => 'Reliable power for your setup',
        'Accessories' => 'Essential upgrades for your battlestation',
    ];

    return $summaries[$category] ?? 'Fresh gear for your setup';
}

function shop_card_badge(string $category, string $name, string $details): string
{
    $source = strtoupper($name . ' ' . $details);

    if (preg_match('/\b\d{2,4}\s?W\b/', $source, $matches)) {
        return strtoupper(str_replace(' ', '', $matches[0]));
    }

    if (strpos($source, 'GAN') !== false) {
        return 'GaN';
    }

    if (strpos($source, 'RGB') !== false) {
        return 'RGB';
    }

    if (strpos($source, '7.1') !== false) {
        return '7.1';
    }

    return shop_category_badge($category);
}

function shop_card_subtitle(string $category, string $details): string
{
    $clean_details = trim(preg_replace('/\s+/', ' ', $details));

    if ($clean_details !== '') {
        return strlen($clean_details) > 48 ? substr($clean_details, 0, 45) . '...' : $clean_details;
    }

    return shop_category_summary($category);
}

$min_price = isset($_GET['min_price']) ? max(0, (float) $_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) && (float) $_GET['max_price'] > 0 ? (float) $_GET['max_price'] : 999999;
$selected_categories = isset($_GET['category']) && is_array($_GET['category']) ? array_values(array_intersect($category_options, $_GET['category'])) : [];
$has_custom_max = isset($_GET['max_price']) && (float) $_GET['max_price'] > 0;
$active_filter_count = ($min_price > 0 ? 1 : 0) + ($has_custom_max ? 1 : 0) + count($selected_categories);
$filter_status_text = $active_filter_count === 0
    ? 'No filters applied'
    : $active_filter_count . ' filter' . ($active_filter_count === 1 ? '' : 's') . ' applied';

$query = "SELECT * FROM `products` WHERE `price` BETWEEN :min_price AND :max_price";
$params = [
    ':min_price' => $min_price,
    ':max_price' => $max_price,
];

if (!empty($selected_categories)) {
    $category_placeholders = [];

    foreach ($selected_categories as $i => $category) {
        $placeholder = ":category_$i";
        $category_placeholders[] = $placeholder;
        $params[$placeholder] = $category;
    }

    $query .= " AND `category` IN (" . implode(',', $category_placeholders) . ")";
}

$query .= " ORDER BY created_at DESC";

$select_products = $conn->prepare($query);
$select_products->execute($params);
$product_count = $select_products->rowCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="shop-page">

<?php include 'components/user_header.php'; ?>

<section class="shop-catalog-shell">
    <aside class="shop-filter-panel">
        <div class="shop-filter-card">
            <div class="shop-filter-head">
                <div class="shop-filter-title">
                    <span class="shop-filter-title-icon"><i class="fas fa-filter"></i></span>
                    <h2>Filters</h2>
                </div>
                <p>Fine-tune your catalog view with price limits and category picks.</p>
            </div>

            <form action="" method="get" class="shop-filter-form">
                <div class="filter-group">
                    <label for="min_price">Minimum Price</label>
                    <input id="min_price" type="number" name="min_price" placeholder="0" value="<?= htmlspecialchars((string) ($min_price > 0 ? $min_price : ''), ENT_QUOTES, 'UTF-8'); ?>" min="0">
                </div>

                <div class="filter-group">
                    <label for="max_price">Maximum Price</label>
                    <input id="max_price" type="number" name="max_price" placeholder="No limit" value="<?= htmlspecialchars((string) ($has_custom_max ? $max_price : ''), ENT_QUOTES, 'UTF-8'); ?>" min="0">
                </div>

                <div class="filter-group">
                    <span class="filter-label">Categories</span>
                    <div class="category-filter-grid">
                        <?php foreach ($category_options as $category): ?>
                            <label class="category-chip">
                                <input type="checkbox" name="category[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" <?= in_array($category, $selected_categories, true) ? 'checked' : ''; ?>>
                                <span class="category-chip-shell">
                                    <i class="<?= htmlspecialchars(shop_category_icon($category), ENT_QUOTES, 'UTF-8'); ?>"></i>
                                    <span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="shop-filter-actions">
                    <button type="submit" class="btn shop-apply-btn">
                        <i class="fas fa-filter"></i>
                        <span>Apply Filters</span>
                    </button>
                    <a href="shop.php" class="shop-reset-btn">
                        <i class="fas fa-rotate-left"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>
    </aside>

    <div class="shop-products">
        <div class="shop-products-head">
            <div class="shop-products-copy">
                <span class="shop-kicker"><i class="fas fa-bolt"></i> Product Catalog</span>
                <h1 class="heading">Latest Products</h1>
                <p>Showing products based on your current filter settings.</p>
            </div>
            <div class="shop-products-status">
                <span class="shop-filter-status <?= $active_filter_count === 0 ? 'is-muted' : ''; ?>">
                    <i class="fas fa-filter"></i>
                    <span><?= htmlspecialchars($filter_status_text, ENT_QUOTES, 'UTF-8'); ?></span>
                </span>
                <span class="shop-results-count">
                    <?= $product_count; ?> item<?= $product_count === 1 ? '' : 's'; ?>
                </span>
            </div>
        </div>

        <?php if ($active_filter_count > 0): ?>
            <div class="shop-active-filters">
                <?php if ($min_price > 0): ?>
                    <span class="shop-filter-pill">Min: $<?= number_format($min_price, 0); ?></span>
                <?php endif; ?>
                <?php if ($has_custom_max): ?>
                    <span class="shop-filter-pill">Max: $<?= number_format($max_price, 0); ?></span>
                <?php endif; ?>
                <?php foreach ($selected_categories as $category): ?>
                    <span class="shop-filter-pill"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="products shop-products-grid">
            <div class="box-container">
                <?php
                if ($product_count > 0) {
                    while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                        $check_order = $conn->prepare("SELECT SUM(quantity) AS total_ordered FROM `cart` WHERE pid = ?");
                        $check_order->execute([$fetch_product['id']]);
                        $order_data = $check_order->fetch(PDO::FETCH_ASSOC);
                        $total_ordered = $order_data['total_ordered'] ?? 0;
                        $available_stock = (int) $fetch_product['stock'] - (int) $total_ordered;
                        $product_name = htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8');
                        $product_image = htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8');
                        $product_category = htmlspecialchars($fetch_product['category'], ENT_QUOTES, 'UTF-8');
                        $product_details = (string) $fetch_product['details'];
                        $product_badge = htmlspecialchars(shop_card_badge($fetch_product['category'], $fetch_product['name'], $product_details), ENT_QUOTES, 'UTF-8');
                        $product_subtitle = htmlspecialchars(shop_card_subtitle($fetch_product['category'], $product_details), ENT_QUOTES, 'UTF-8');
                        ?>
                        <?php if ($available_stock > 0): ?>
                            <form action="" method="post" class="box shop-product-card">
                                <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                                <input type="hidden" name="name" value="<?= $product_name; ?>">
                                <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                                <input type="hidden" name="image" value="<?= $product_image; ?>">
                                <button class="fas fa-heart" type="submit" name="add_to_wishlist" aria-label="Add <?= $product_name; ?> to wishlist"></button>
                                <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye" aria-label="View <?= $product_name; ?>"></a>
                                <div class="shop-card-badge-row">
                                    <span class="shop-card-badge"><?= $product_badge; ?></span>
                                </div>
                                <div class="shop-card-media">
                                    <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                                </div>
                                <div class="shop-card-content">
                                    <div class="name"><?= $product_name; ?></div>
                                    <p class="shop-card-subtitle"><?= $product_subtitle; ?></p>
                                    <div class="price"><span>$</span><?= number_format((float) $fetch_product['price'], 2); ?></div>
                                    <div class="shop-product-meta">
                                        <span class="shop-meta-pill is-stock">Stock: <?= number_format($available_stock); ?></span>
                                        <span class="shop-meta-pill">Category: <?= $product_category; ?></span>
                                    </div>
                                    <button type="submit" class="btn shop-card-cart-btn" name="add_to_cart">
                                        <i class="fas fa-cart-plus"></i>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="box shop-product-card is-unavailable">
                                <div class="shop-card-badge-row">
                                    <span class="shop-card-badge">Sold Out</span>
                                </div>
                                <div class="shop-card-media">
                                    <img src="uploaded_img/<?= $product_image; ?>" alt="<?= $product_name; ?>">
                                </div>
                                <div class="shop-card-content">
                                    <div class="name"><?= $product_name; ?></div>
                                    <p class="shop-card-subtitle"><?= $product_subtitle; ?></p>
                                    <div class="shop-product-meta">
                                        <span class="shop-meta-pill">Category: <?= $product_category; ?></span>
                                    </div>
                                    <div class="out-of-stock">Out of Stock</div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php
                    }
                } else {
                    echo '<p class="empty">No products found!</p>';
                }
                ?>
            </div>
        </section>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
