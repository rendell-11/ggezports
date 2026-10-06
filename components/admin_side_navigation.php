<?php
include_once 'connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('Location: ../admin/admin_login.php');
    exit;
}

$select_profile = $conn->prepare("SELECT admin_role FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
$admin_role = $fetch_profile['admin_role'] ?? 'order_admin';
$current_page = basename($_SERVER['PHP_SELF'] ?? '');

function adminNavIsActive(array $pages, string $current_page): bool
{
    return in_array($current_page, $pages, true);
}

function renderAdminNavLink(string $href, string $label, string $icon, bool $is_active = false): void
{
    $active_class = $is_active ? ' is-active' : '';
    ?>
    <a href="<?= htmlspecialchars($href); ?>" class="admin-sidebar__link<?= $active_class; ?>">
        <span class="admin-sidebar__icon"><i class="<?= htmlspecialchars($icon); ?>"></i></span>
        <span class="admin-sidebar__label"><?= htmlspecialchars($label); ?></span>
    </a>
    <?php
}

function renderAdminNavDropdown(string $id, string $label, string $icon, array $items, bool $is_open = false): void
{
    $group_class = $is_open ? ' is-open' : '';
    $button_class = $is_open ? ' is-active' : '';
    ?>
    <div class="admin-sidebar__group<?= $group_class; ?>">
        <button
            type="button"
            class="admin-sidebar__link admin-sidebar__toggle<?= $button_class; ?>"
            data-target="<?= htmlspecialchars($id); ?>"
            aria-expanded="<?= $is_open ? 'true' : 'false'; ?>"
        >
            <span class="admin-sidebar__icon"><i class="<?= htmlspecialchars($icon); ?>"></i></span>
            <span class="admin-sidebar__label"><?= htmlspecialchars($label); ?></span>
            <span class="admin-sidebar__caret"><i class="fas fa-chevron-down"></i></span>
        </button>

        <div id="<?= htmlspecialchars($id); ?>" class="admin-sidebar__submenu"<?= $is_open ? '' : ' hidden'; ?>>
            <?php foreach ($items as $item): ?>
                <a href="<?= htmlspecialchars($item['href']); ?>" class="admin-sidebar__sublink<?= !empty($item['active']) ? ' is-active' : ''; ?>">
                    <span class="admin-sidebar__subicon"><i class="<?= htmlspecialchars($item['icon']); ?>"></i></span>
                    <span><?= htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderSideNav(string $admin_role, string $current_page): void
{
    $dashboard_active = adminNavIsActive(['dashboard.php'], $current_page);
    $order_active = adminNavIsActive(['placed_orders.php', 'cancelled_order.php'], $current_page);
    $admins_active = adminNavIsActive(['admin_accounts.php', 'register_admin.php'], $current_page);
    $activity_active = adminNavIsActive(['activity_logs.php'], $current_page);
    $users_active = adminNavIsActive(['users_accounts.php'], $current_page);
    $marketing_active = adminNavIsActive(['admin_promotion.php', 'discounts.php'], $current_page);
    $delivery_active = adminNavIsActive(['order_status.php', 'admin_courier_account.php'], $current_page);
    $messages_active = adminNavIsActive(['messages.php', 'user_rating_message.php'], $current_page);
    $categories_active = adminNavIsActive(['products.php', 'manage_categories.php', 'archive_product.php', 'update_product.php'], $current_page);
    $reports_active = adminNavIsActive(['products_reports.php', 'generate_report.php'], $current_page);
    $profile_active = adminNavIsActive(['update_profile.php'], $current_page);
    ?>

    <div class="admin-sidebar-overlay" data-admin-sidebar-overlay></div>

    <aside id="adminSidebar" class="admin-sidebar" aria-label="Admin sidebar">
        <div class="admin-sidebar__frame">
            <button type="button" class="admin-sidebar__close" data-admin-sidebar-close aria-label="Close sidebar">
                <i class="fas fa-xmark"></i>
            </button>

            <a href="../admin/dashboard.php" class="admin-sidebar__brand">
                <img src="../images/new.png" alt="GG-EZ Esports" class="admin-sidebar__brand-logo">
                <span class="admin-sidebar__brand-panel">ADMIN PANEL</span>
            </a>

            <nav class="admin-sidebar__nav">
                <?php renderAdminNavLink('../admin/dashboard.php', 'Dashboard', 'fas fa-house', $dashboard_active); ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'order_admin') : ?>
                    <?php
                    renderAdminNavDropdown(
                        'orderManagementDropdown',
                        'Order Management',
                        'fas fa-box-open',
                        [
                            [
                                'href' => '../admin/placed_orders.php',
                                'label' => 'Orders',
                                'icon' => 'fas fa-bag-shopping',
                                'active' => adminNavIsActive(['placed_orders.php'], $current_page),
                            ],
                        ],
                        $order_active
                    );
                    ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin') : ?>
                    <?php renderAdminNavLink('../admin/admin_accounts.php', 'Admins', 'fas fa-user-gear', $admins_active); ?>
                    <?php renderAdminNavLink('../admin/activity_logs.php', 'Activity Logs', 'fas fa-clipboard-list', $activity_active); ?>
                    <?php renderAdminNavLink('../admin/users_accounts.php', 'Users', 'fas fa-user-plus', $users_active); ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'reports_admin') : ?>
                    <?php
                    renderAdminNavDropdown(
                        'marketingDropdown',
                        'Marketing and Promotions',
                        'fas fa-bullhorn',
                        [
                            [
                                'href' => '../admin/admin_promotion.php',
                                'label' => 'Promotions',
                                'icon' => 'fas fa-tag',
                                'active' => adminNavIsActive(['admin_promotion.php'], $current_page),
                            ],
                            [
                                'href' => '../admin/discounts.php',
                                'label' => 'Discounts',
                                'icon' => 'fas fa-percent',
                                'active' => adminNavIsActive(['discounts.php'], $current_page),
                            ],
                        ],
                        $marketing_active
                    );
                    ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'delivery_admin') : ?>
                    <?php
                    renderAdminNavDropdown(
                        'deliveryDropdown',
                        'Delivery Management',
                        'fas fa-truck-fast',
                        [
                            [
                                'href' => '../admin/order_status.php',
                                'label' => 'Order Status',
                                'icon' => 'fas fa-list-check',
                                'active' => adminNavIsActive(['order_status.php'], $current_page),
                            ],
                            [
                                'href' => '../Delivery/login.php',
                                'label' => 'Courier Panel',
                                'icon' => 'fas fa-truck',
                                'active' => false,
                            ],
                            [
                                'href' => '../admin/admin_courier_account.php',
                                'label' => 'Create Courier Account',
                                'icon' => 'fas fa-user-plus',
                                'active' => adminNavIsActive(['admin_courier_account.php'], $current_page),
                            ],
                        ],
                        $delivery_active
                    );
                    ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'reports_admin') : ?>
                    <?php
                    renderAdminNavDropdown(
                        'messagesDropdown',
                        'Messages',
                        'fas fa-envelope',
                        [
                            [
                                'href' => '../admin/messages.php',
                                'label' => 'Feedback',
                                'icon' => 'fas fa-comments',
                                'active' => adminNavIsActive(['messages.php'], $current_page),
                            ],
                            [
                                'href' => '../admin/user_rating_message.php',
                                'label' => 'User Rating',
                                'icon' => 'fas fa-star',
                                'active' => adminNavIsActive(['user_rating_message.php'], $current_page),
                            ],
                        ],
                        $messages_active
                    );
                    ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'order_admin') : ?>
                    <?php
                    renderAdminNavDropdown(
                        'categoryDropdown',
                        'Categories',
                        'fas fa-table-cells-large',
                        [
                            [
                                'href' => '../admin/products.php',
                                'label' => 'Manage Products',
                                'icon' => 'fas fa-box',
                                'active' => adminNavIsActive(['products.php', 'update_product.php'], $current_page),
                            ],
                            [
                                'href' => '../admin/manage_categories.php',
                                'label' => 'Manage Categories',
                                'icon' => 'fas fa-tags',
                                'active' => adminNavIsActive(['manage_categories.php'], $current_page),
                            ],
                            [
                                'href' => '../admin/archive_product.php',
                                'label' => 'Archive Products',
                                'icon' => 'fas fa-box-archive',
                                'active' => adminNavIsActive(['archive_product.php'], $current_page),
                            ],
                        ],
                        $categories_active
                    );
                    ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin' || $admin_role === 'reports_admin') : ?>
                    <?php renderAdminNavLink('../admin/products_reports.php', 'Products Reports', 'fas fa-chart-line', $reports_active); ?>
                <?php endif; ?>

                <?php if ($admin_role === 'super_admin') : ?>
                    <?php renderAdminNavLink('../admin/update_profile.php', 'Update Profile', 'fas fa-user-pen', $profile_active); ?>
                <?php endif; ?>
            </nav>

            <a href="../components/admin_logout.php" class="admin-sidebar__logout" onclick="return confirm('logout from the website?');">
                <span class="admin-sidebar__icon"><i class="fas fa-right-from-bracket"></i></span>
                <span class="admin-sidebar__label">Logout</span>
            </a>
        </div>
    </aside>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap');

        :root {
            --admin-sidebar-width: 34rem;
            --admin-sidebar-gap: 2.4rem;
            --admin-sidebar-bg: rgba(10, 10, 12, 0.98);
            --admin-sidebar-card: rgba(19, 20, 24, 0.98);
            --admin-sidebar-border: rgba(255, 58, 58, 0.44);
            --admin-sidebar-text: #f3f3f3;
            --admin-sidebar-muted: #cfcfcf;
            --admin-sidebar-glow: rgba(255, 52, 52, 0.25);
            --admin-sidebar-glow-strong: rgba(255, 52, 52, 0.48);
        }

        body {
            padding-left: calc(var(--admin-sidebar-width) + 4rem);
            padding-right: 2.4rem;
            overflow-x: hidden;
        }

        .header {
            background: rgba(9, 10, 13, 0.82);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(16px);
            box-shadow: 0 1.2rem 2.8rem rgba(0, 0, 0, 0.22);
        }

        .header .flex {
            max-width: none;
            gap: 1.6rem;
            padding: 1.6rem 2rem;
        }

        .search-form {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-left: auto;
            padding: 0.6rem 0.8rem 0.6rem 1.4rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.4rem;
            background: rgba(255, 255, 255, 0.04);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        .search-form input {
            width: 24rem;
            padding: 0.8rem 0;
            border: none;
            background: transparent;
            color: #ffffff;
            font-size: 1.5rem;
        }

        .search-form input::placeholder {
            color: rgba(255, 255, 255, 0.54);
        }

        .search-form button {
            width: 3.8rem;
            height: 3.8rem;
            border: none;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ff3737 0%, #b10e1a 100%);
            color: #ffffff;
            font-size: 1.4rem;
            cursor: pointer;
            box-shadow: 0 0.8rem 1.8rem rgba(255, 55, 55, 0.28);
        }

        .icons {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .icons #user-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4.2rem;
            height: 4.2rem;
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .icons #menu-btn {
            display: none;
        }

        .header .flex .profile {
            top: calc(100% + 1rem);
            right: 2rem;
            width: 28rem;
            background: #111317;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 2rem 3rem rgba(0, 0, 0, 0.3);
        }

        .admin-sidebar {
            position: fixed;
            top: 2.4rem;
            left: 2.4rem;
            bottom: 2.4rem;
            width: var(--admin-sidebar-width);
            z-index: 1300;
        }

        .admin-sidebar__frame {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 2rem 1.5rem 1.4rem;
            border: 1px solid var(--admin-sidebar-border);
            border-radius: 2rem;
            background:
                radial-gradient(circle at top left, rgba(255, 32, 32, 0.2), transparent 24rem),
                linear-gradient(180deg, rgba(16, 16, 18, 0.98), rgba(8, 8, 10, 0.98));
            box-shadow:
                0 0 0 1px rgba(255, 47, 47, 0.08),
                0 2.6rem 5rem rgba(0, 0, 0, 0.45),
                inset 0 0 3rem rgba(255, 26, 26, 0.05);
            overflow: hidden;
        }

        .admin-sidebar__frame::before,
        .admin-sidebar__frame::after {
            content: '';
            position: absolute;
            pointer-events: none;
        }

        .admin-sidebar__frame::before {
            inset: 0;
            background:
                linear-gradient(135deg, rgba(255, 48, 48, 0.12) 0%, transparent 22%),
                linear-gradient(145deg, transparent 58%, rgba(255, 48, 48, 0.08) 59%, transparent 61%),
                linear-gradient(145deg, transparent 68%, rgba(255, 48, 48, 0.05) 69%, transparent 71%);
            opacity: 0.9;
        }

        .admin-sidebar__frame::after {
            top: 0;
            right: -6rem;
            width: 20rem;
            height: 20rem;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 43, 43, 0.12), transparent 70%);
            filter: blur(1rem);
        }

        .admin-sidebar__close {
            display: none;
            align-self: flex-end;
            width: 4rem;
            height: 4rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.2rem;
            background: rgba(255, 255, 255, 0.04);
            color: #ffffff;
            cursor: pointer;
        }

        .admin-sidebar__brand,
        .admin-sidebar__link,
        .admin-sidebar__toggle,
        .admin-sidebar__sublink,
        .admin-sidebar__logout {
            position: relative;
            z-index: 1;
        }

        .admin-sidebar__brand {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 0.8rem 1.1rem 1.2rem 1.1rem;
            text-decoration: none;
        }

        .admin-sidebar__brand-logo {
            width: 19.8rem;
            max-width: 100%;
            filter: drop-shadow(0 0 1.8rem rgba(255, 54, 54, 0.3));
        }

        .admin-sidebar__brand-panel {
            color: #ff5050;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            margin-left: 0.9rem;
        }

        .admin-sidebar__nav {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            gap: 1rem;
            margin-top: 0.4rem;
            min-height: 0;
            padding: 0 0.6rem 0 0.1rem;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 58, 58, 0.6) transparent;
        }

        .admin-sidebar__group {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .admin-sidebar__link,
        .admin-sidebar__toggle,
        .admin-sidebar__logout {
            display: flex;
            align-items: center;
            gap: 1.4rem;
            width: 100%;
            min-height: 6.2rem;
            padding: 0 1.7rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 1.2rem;
            background: linear-gradient(180deg, rgba(20, 21, 25, 0.98), rgba(13, 14, 17, 0.98));
            color: var(--admin-sidebar-text);
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.65rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            text-decoration: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .admin-sidebar__toggle {
            cursor: pointer;
        }

        .admin-sidebar__link:hover,
        .admin-sidebar__toggle:hover,
        .admin-sidebar__sublink:hover,
        .admin-sidebar__logout:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 81, 81, 0.45);
            box-shadow: 0 1.4rem 2.4rem rgba(0, 0, 0, 0.28);
        }

        .admin-sidebar__link.is-active,
        .admin-sidebar__toggle.is-active,
        .admin-sidebar__logout {
            border-color: rgba(255, 67, 67, 0.62);
            background: linear-gradient(90deg, rgba(111, 16, 18, 0.98) 0%, rgba(52, 15, 19, 0.98) 46%, rgba(19, 20, 24, 0.98) 100%);
            box-shadow:
                0 0 0 1px rgba(255, 48, 48, 0.14),
                0 0 3rem rgba(255, 43, 43, 0.14),
                inset 0 0 2.4rem rgba(255, 38, 38, 0.12);
        }

        .admin-sidebar__link.is-active::before,
        .admin-sidebar__toggle.is-active::before {
            content: '';
            position: absolute;
            left: 0.4rem;
            top: 0.7rem;
            bottom: 0.7rem;
            width: 0.5rem;
            border-radius: 999px;
            background: linear-gradient(180deg, #ff7171 0%, #ff2121 100%);
            box-shadow: 0 0 1.2rem var(--admin-sidebar-glow-strong);
        }

        .admin-sidebar__icon,
        .admin-sidebar__subicon,
        .admin-sidebar__caret {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .admin-sidebar__icon {
            width: 2.6rem;
            font-size: 1.9rem;
            color: #ffffff;
            text-shadow: 0 0 1rem rgba(255, 68, 68, 0.18);
        }

        .admin-sidebar__label {
            flex: 1 1 auto;
            white-space: normal;
            line-height: 1.12;
        }

        .admin-sidebar__caret {
            margin-left: auto;
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.8);
            transition: transform 0.2s ease;
        }

        .admin-sidebar__group.is-open .admin-sidebar__caret {
            transform: rotate(180deg);
        }

        .admin-sidebar__submenu {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            padding-left: 1.6rem;
        }

        .admin-sidebar__sublink {
            display: flex;
            align-items: center;
            gap: 1rem;
            min-height: 4.6rem;
            padding: 0 1.4rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.03);
            color: var(--admin-sidebar-muted);
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.45rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .admin-sidebar__sublink.is-active {
            border-color: rgba(255, 72, 72, 0.4);
            background: linear-gradient(90deg, rgba(92, 16, 18, 0.7), rgba(33, 16, 21, 0.85));
            color: #ffffff;
        }

        .admin-sidebar__subicon {
            width: 1.8rem;
            font-size: 1.25rem;
            color: #ff6666;
        }

        .admin-sidebar__logout {
            margin-top: 1.4rem;
            justify-content: flex-start;
            background: linear-gradient(180deg, #ff242f 0%, #a70618 100%);
            border-color: rgba(255, 102, 102, 0.72);
            box-shadow:
                0 1.6rem 3rem rgba(120, 0, 0, 0.35),
                inset 0 0 1.8rem rgba(255, 255, 255, 0.08);
        }

        .admin-sidebar__logout .admin-sidebar__icon {
            text-shadow: none;
        }

        .admin-sidebar-overlay {
            display: none;
        }

        @media (max-width: 960px) {
            body {
                padding-left: 0;
                padding-right: 0;
            }

            .header .flex {
                padding: 1.4rem 1.6rem;
                gap: 1rem;
            }

            .search-form {
                flex: 1 1 auto;
                margin-left: 0;
            }

            .search-form input {
                width: 100%;
            }

            .admin-sidebar-overlay {
                position: fixed;
                inset: 0;
                z-index: 1250;
                background: rgba(0, 0, 0, 0.62);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
                display: block;
            }

            .admin-sidebar-overlay.is-visible {
                opacity: 1;
                pointer-events: auto;
            }

            .admin-sidebar {
                top: 1.2rem;
                left: 1.2rem;
                bottom: 1.2rem;
                width: min(var(--admin-sidebar-width), calc(100vw - 2.4rem));
                transform: translateX(calc(-100% - 2.4rem));
                transition: transform 0.28s ease;
            }

            .admin-sidebar.is-open {
                transform: translateX(0);
            }

            .admin-sidebar__close {
                display: inline-flex;
            }

            .icons #menu-btn {
                display: inline-flex !important;
            }
        }

        @media (max-width: 640px) {
            .search-form {
                min-width: 0;
            }

            .search-form input {
                width: 100%;
                min-width: 0;
            }

            .header .flex .profile {
                right: 1rem;
                width: calc(100vw - 2rem);
                max-width: 28rem;
            }
        }
    </style>

    <script>
        (function () {
            const initializeAdminSidebar = function () {
                const sidebar = document.getElementById('adminSidebar');
                const overlay = document.querySelector('[data-admin-sidebar-overlay]');
                const closeButton = document.querySelector('[data-admin-sidebar-close]');
                const menuButton = document.getElementById('menu-btn');
                const toggles = document.querySelectorAll('.admin-sidebar__toggle');

                if (!sidebar) {
                    return;
                }

                const setSidebarState = function (isOpen) {
                    if (window.innerWidth > 960) {
                        sidebar.classList.remove('is-open');
                        overlay?.classList.remove('is-visible');
                        return;
                    }

                    sidebar.classList.toggle('is-open', isOpen);
                    overlay?.classList.toggle('is-visible', isOpen);
                };

                menuButton?.addEventListener('click', function () {
                    if (window.innerWidth > 960) {
                        return;
                    }

                    const nextState = !sidebar.classList.contains('is-open');
                    setSidebarState(nextState);
                });

                closeButton?.addEventListener('click', function () {
                    setSidebarState(false);
                });

                overlay?.addEventListener('click', function () {
                    setSidebarState(false);
                });

                toggles.forEach(function (toggle) {
                    toggle.addEventListener('click', function () {
                        const targetId = toggle.getAttribute('data-target');
                        const target = targetId ? document.getElementById(targetId) : null;

                        if (!target) {
                            return;
                        }

                        const group = toggle.closest('.admin-sidebar__group');
                        const shouldOpen = target.hasAttribute('hidden');

                        target.toggleAttribute('hidden');
                        toggle.classList.toggle('is-active', shouldOpen);
                        toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                        group?.classList.toggle('is-open', shouldOpen);
                    });
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth > 960) {
                        setSidebarState(false);
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeAdminSidebar, { once: true });
                return;
            }

            initializeAdminSidebar();
        }());
    </script>
    <?php
}

renderSideNav($admin_role, $current_page);
