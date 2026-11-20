<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:admin_login.php');
    exit;
}

function logActivity(int $admin_id, string $action): void
{
    global $conn;

    $timestamp = date('Y-m-d H:i:s');
    $insert_log = $conn->prepare("INSERT INTO `activity_logs` (admin_id, action, timestamp) VALUES (?, ?, ?)");
    $insert_log->execute([$admin_id, $action, $timestamp]);
}

function adminRoleLabel(string $role): string
{
    return ucwords(str_replace('_', ' ', $role));
}

function adminRoleClass(string $role): string
{
    return match ($role) {
        'super_admin' => 'is-super',
        'order_admin' => 'is-order',
        'delivery_admin' => 'is-delivery',
        'reports_admin' => 'is-reports',
        default => 'is-default',
    };
}

logActivity($admin_id, 'Admin viewed activity logs');

if (isset($_GET['manage_order'])) {
    logActivity($admin_id, 'Admin managed orders');
}

if (isset($_GET['manage_discount'])) {
    logActivity($admin_id, 'Admin managed discounts');
}

if (isset($_GET['manage_admin'])) {
    logActivity($admin_id, 'Admin managed admin profiles');
}

if (isset($_GET['manage_user'])) {
    logActivity($admin_id, 'Admin managed user profiles');
}

if (isset($_GET['manage_delivery'])) {
    logActivity($admin_id, 'Admin managed delivery settings');
}

if (isset($_GET['manage_message'])) {
    logActivity($admin_id, 'Admin sent or viewed messages');
}

if (isset($_GET['manage_categories'])) {
    logActivity($admin_id, 'Admin managed product categories');
}

if (isset($_GET['view_reports'])) {
    logActivity($admin_id, 'Admin viewed product reports');
}

if (isset($_POST['update_profile'])) {
    logActivity($admin_id, 'Admin updated profile');
}

$search = trim($_GET['search'] ?? '');
$admin_filter = trim($_GET['admin'] ?? '');

$profile_stmt = $conn->prepare("SELECT name, admin_role FROM `admins` WHERE id = ?");
$profile_stmt->execute([$admin_id]);
$current_admin = $profile_stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Admin', 'admin_role' => 'super_admin'];

$admin_options_stmt = $conn->prepare("SELECT id, name FROM `admins` ORDER BY name ASC");
$admin_options_stmt->execute();
$admin_options = $admin_options_stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "
    SELECT activity_logs.*, admins.name, admins.admin_role
    FROM `activity_logs`
    INNER JOIN `admins` ON activity_logs.admin_id = admins.id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $query .= " AND (admins.name LIKE ? OR activity_logs.action LIKE ? OR activity_logs.timestamp LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($admin_filter !== '') {
    $query .= " AND activity_logs.admin_id = ?";
    $params[] = (int) $admin_filter;
}

$query .= " ORDER BY activity_logs.timestamp DESC";

$select_logs = $conn->prepare($query);
$select_logs->execute($params);
$logs = $select_logs->fetchAll(PDO::FETCH_ASSOC);

$display_date = date('M d, Y');
$current_admin_role = adminRoleLabel($current_admin['admin_role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap');

        .activity-page {
            max-width: 1600px;
            margin: 2.4rem auto 3.2rem;
            padding: 0 2rem;
        }

        .activity-shell {
            position: relative;
            padding: 2.4rem;
            border: 1px solid rgba(255, 62, 62, 0.28);
            border-radius: 2.2rem;
            background:
                linear-gradient(180deg, rgba(13, 13, 15, 0.97), rgba(8, 8, 10, 0.97)),
                #0b0b0d;
            box-shadow:
                0 2rem 5rem rgba(0, 0, 0, 0.42),
                inset 0 0 0 1px rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .activity-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(135deg, rgba(255, 44, 60, 0.08) 0%, transparent 18%),
                linear-gradient(145deg, transparent 70%, rgba(255, 44, 60, 0.06) 71%, transparent 72%);
        }

        .activity-top,
        .activity-toolbar,
        .activity-grid {
            position: relative;
            z-index: 1;
        }

        .activity-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.8rem;
            margin-bottom: 1.8rem;
        }

        .activity-title {
            margin: 0;
            font-family: 'Rajdhani', sans-serif;
            font-size: 4rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #f3f3f3;
            text-transform: uppercase;
        }

        .activity-title span {
            color: #ff2c3c;
        }

        .activity-subtitle {
            margin-top: 0.6rem;
            font-size: 1.45rem;
            color: #a0a0a0;
        }

        .activity-meta {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            flex-wrap: wrap;
        }

        .activity-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            min-height: 4.8rem;
            padding: 0 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.2rem;
            background: rgba(18, 18, 21, 0.94);
            color: #d6d6d6;
            font-size: 1.35rem;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .activity-chip i {
            color: #ff2c3c;
        }

        .activity-chip--admin {
            padding-right: 1.8rem;
            border-color: rgba(255, 44, 60, 0.45);
        }

        .activity-chip__avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border: 1px solid rgba(255, 44, 60, 0.4);
            border-radius: 999px;
            background: rgba(255, 44, 60, 0.08);
        }

        .activity-chip__text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .activity-chip__text strong {
            color: #f4f4f4;
            font-size: 1.35rem;
        }

        .activity-chip__text small {
            color: #9c9c9c;
            font-size: 1.15rem;
        }

        .activity-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.8rem;
        }

        .activity-filter-form {
            display: flex;
            align-items: stretch;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .activity-search,
        .activity-admin-filter {
            display: flex;
            align-items: center;
            min-height: 4.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            background: rgba(22, 22, 24, 0.95);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .activity-search {
            min-width: 0;
            width: 38rem;
            padding: 0 1.4rem;
            gap: 1rem;
        }

        .activity-search i,
        .activity-admin-filter i {
            color: #9d9d9d;
            font-size: 1.35rem;
        }

        .activity-search input,
        .activity-admin-filter select {
            width: 100%;
            background: transparent;
            color: #f3f3f3;
            font-size: 1.35rem;
        }

        .activity-search input::placeholder {
            color: #8c8c8c;
        }

        .activity-admin-filter {
            width: 22rem;
            padding: 0 1.3rem;
            gap: 0.9rem;
        }

        .activity-admin-filter select option {
            color: #111111;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.6rem;
        }

        .activity-card {
            display: flex;
            flex-direction: column;
            min-height: 22rem;
            padding: 1.8rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.6rem;
            background: linear-gradient(180deg, rgba(18, 18, 21, 0.97), rgba(12, 12, 15, 0.97));
            box-shadow:
                0 1.6rem 3rem rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.02);
        }

        .activity-card__inner {
            display: flex;
            gap: 1.6rem;
            align-items: flex-start;
        }

        .activity-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 5.4rem;
            height: 5.4rem;
            border: 1px solid rgba(255, 44, 60, 0.45);
            border-radius: 999px;
            background: rgba(255, 44, 60, 0.06);
            color: #ff3a4a;
            font-size: 2rem;
            box-shadow: 0 0 1.8rem rgba(255, 44, 60, 0.1);
        }

        .activity-card__content {
            flex: 1 1 auto;
        }

        .activity-card__line {
            font-size: 1.45rem;
            line-height: 1.55;
            color: #d4d4d4;
        }

        .activity-card__line strong {
            color: #9c9c9c;
            font-weight: 500;
        }

        .activity-card__line span {
            color: #ff303f;
            font-weight: 700;
        }

        .activity-card__role {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 3.4rem;
            margin-top: 1rem;
            padding: 0 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.9rem;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: lowercase;
            width: fit-content;
        }

        .activity-card__role i {
            font-size: 1.1rem;
        }

        .activity-card__role.is-super {
            color: #ff707a;
            border-color: rgba(255, 44, 60, 0.32);
            background: rgba(255, 44, 60, 0.07);
        }

        .activity-card__role.is-order {
            color: #ffb85c;
            border-color: rgba(255, 184, 92, 0.26);
            background: rgba(255, 184, 92, 0.07);
        }

        .activity-card__role.is-delivery {
            color: #6fb1ff;
            border-color: rgba(111, 177, 255, 0.28);
            background: rgba(111, 177, 255, 0.08);
        }

        .activity-card__role.is-reports {
            color: #b77dff;
            border-color: rgba(183, 125, 255, 0.28);
            background: rgba(183, 125, 255, 0.08);
        }

        .activity-card__role.is-default {
            color: #c8c8c8;
            border-color: rgba(255, 255, 255, 0.16);
            background: rgba(255, 255, 255, 0.05);
        }

        .activity-card__action {
            margin-top: auto;
            padding-top: 1.6rem;
            font-size: 1.35rem;
            line-height: 1.65;
            color: #efefef;
        }

        .activity-card__time {
            margin-top: 1.2rem;
            font-size: 1.22rem;
            color: #969696;
        }

        .activity-empty {
            grid-column: 1 / -1;
            padding: 3.2rem 2rem;
            border: 1px dashed rgba(255, 255, 255, 0.14);
            border-radius: 1.4rem;
            text-align: center;
            color: #9b9b9b;
            font-size: 1.5rem;
        }

        @media (max-width: 1180px) {
            .activity-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 820px) {
            .activity-shell {
                padding: 1.8rem;
            }

            .activity-top,
            .activity-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .activity-filter-form {
                width: 100%;
            }

            .activity-search,
            .activity-admin-filter {
                width: 100%;
            }

            .activity-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 520px) {
            .activity-page {
                padding: 0 1.2rem;
            }

            .activity-shell {
                padding: 1.5rem;
                border-radius: 1.6rem;
            }

            .activity-title {
                font-size: 3rem;
            }

            .activity-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<main class="activity-page">
    <section class="activity-shell">
        <div class="activity-top">
            <div>
                <h1 class="activity-title">Activity <span>Logs</span></h1>
                <p class="activity-subtitle">Track administrator actions, updates, and system activity</p>
            </div>

            <div class="activity-meta">
                <div class="activity-chip">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span><?= htmlspecialchars($display_date); ?></span>
                </div>

                <div class="activity-chip activity-chip--admin">
                    <span class="activity-chip__avatar"><i class="fa-regular fa-user"></i></span>
                    <span class="activity-chip__text">
                        <strong><?= htmlspecialchars($current_admin['name']); ?></strong>
                        <small><?= htmlspecialchars($current_admin_role); ?></small>
                    </span>
                </div>
            </div>
        </div>

        <div class="activity-toolbar">
            <form action="activity_logs.php" method="GET" class="activity-filter-form">
                <label class="activity-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search activity...">
                </label>

                <label class="activity-admin-filter">
                    <i class="fas fa-user-shield"></i>
                    <select name="admin" onchange="this.form.submit()">
                        <option value="">All Admins</option>
                        <?php foreach ($admin_options as $admin_option): ?>
                            <option value="<?= htmlspecialchars((string) $admin_option['id']); ?>" <?= $admin_filter === (string) $admin_option['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($admin_option['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>
        </div>

        <div class="activity-grid">
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <article class="activity-card">
                        <div class="activity-card__inner">
                            <div class="activity-card__icon">
                                <i class="fas fa-user-clock"></i>
                            </div>

                            <div class="activity-card__content">
                                <div class="activity-card__line"><strong>Admin ID :</strong> <span><?= htmlspecialchars((string) $log['admin_id']); ?></span></div>
                                <div class="activity-card__line"><strong>Admin Name :</strong> <span><?= htmlspecialchars($log['name']); ?></span></div>
                                <div class="activity-card__role <?= adminRoleClass($log['admin_role']); ?>">
                                    <i class="fas fa-shield"></i>
                                    <span><?= htmlspecialchars($log['admin_role']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="activity-card__action"><?= htmlspecialchars($log['action']); ?></div>
                        <div class="activity-card__time"><?= htmlspecialchars($log['timestamp']); ?></div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="activity-empty">No activity logs matched your current filter.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="../js/admin_script.js"></script>

</body>
</html>
